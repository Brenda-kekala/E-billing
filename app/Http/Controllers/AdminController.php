<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdminLog;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function index()
    {
        $rate = config('tax.tot_rate');

        $totalUsers = User::where('is_admin', false)->count();
        $totalIncome = Income::sum('amount');
        $totalFlagged = Income::where('flagged', true)->count();

        $users = User::where('is_admin', false)->get()->map(function ($user) use ($rate) {
            $income = Income::where('user_id', $user->id)->sum('amount');
            $flagged = Income::where('user_id', $user->id)->where('flagged', true)->count();
            $paid = Payment::where('user_id', $user->id)->sum('amount');

            return [
                'user' => $user,
                'income' => $income,
                'estimated_tax' => round($income * $rate, 2),
                'paid' => $paid,
                'flagged' => $flagged,
            ];
        });

        return view('admin.index', compact('totalUsers', 'totalIncome', 'totalFlagged', 'users'));
    }

    public function show(User $user)
    {
        $incomes = Income::where('user_id', $user->id)->orderBy('income_date', 'desc')->get();
        $payments = Payment::where('user_id', $user->id)->orderBy('paid_on', 'desc')->get();
        $totalIncome = $incomes->sum('amount');
        $totalPaid = $payments->sum('amount');
        $estimatedTax = round($totalIncome * config('tax.tot_rate'), 2);

        return view('admin.user', compact('user', 'incomes', 'payments', 'totalIncome', 'totalPaid', 'estimatedTax'));
    }

    public function toggleFlag(Request $request, Income $income)
{
    $data = $request->validate([
        'flag_note' => 'nullable|string|max:255',
    ]);

    $wasFlagged = $income->flagged;

    $income->update([
        'flagged' => ! $wasFlagged,
        'flag_note' => $wasFlagged ? null : ($data['flag_note'] ?? 'Flagged for review'),
    ]);

    AdminLog::create([
        'admin_id' => auth()->id(),
        'target_user_id' => $income->user_id,
        'action' => $wasFlagged ? 'unflagged_income' : 'flagged_income',
        'description' => $wasFlagged
            ? "Unflagged income #{$income->id}"
            : "Flagged income #{$income->id}" . (($data['flag_note'] ?? null) ? " — reason: {$data['flag_note']}" : ''),
    ]);

    return back()->with('status', 'Updated.');
}
    public function editUser(User $user)
{
    return view('admin.user-edit', compact('user'));
}

public function updateUser(Request $request, User $user)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        'occupation' => ['required', 'string', 'in:' . implode(',', array_keys(config('tax.occupations')))],
    ]);

    $user->update($data);

    AdminLog::create([
        'admin_id' => auth()->id(),
        'target_user_id' => $user->id,
        'action' => 'edited_user',
        'description' => "Updated profile for {$user->name}",
    ]);

    return redirect()->route('admin.user', $user)->with('status', 'Taxpayer record updated.');
}
public function exportCsv()
{
    $rate = config('tax.tot_rate');
    $users = User::where('is_admin', false)->get();

    $filename = 'taxpayers_' . now()->format('Y_m_d_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function () use ($users, $rate) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'Name', 'Email', 'Occupation', 'Total Income (KES)',
            'Estimated Tax (KES)', 'Paid (KES)', 'Flagged Entries',
        ]);

        foreach ($users as $user) {
            $income = Income::where('user_id', $user->id)->sum('amount');
            $flagged = Income::where('user_id', $user->id)->where('flagged', true)->count();
            $paid = Payment::where('user_id', $user->id)->sum('amount');

            fputcsv($file, [
                $user->name,
                $user->email,
                config('tax.occupations')[$user->occupation] ?? '—',
                number_format($income, 2),
                number_format($income * $rate, 2),
                number_format($paid, 2),
                $flagged,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportPdf()
{
    $rate = config('tax.tot_rate');

    $users = User::where('is_admin', false)->get()->map(function ($user) use ($rate) {
        $income = Income::where('user_id', $user->id)->sum('amount');
        $flagged = Income::where('user_id', $user->id)->where('flagged', true)->count();
        $paid = Payment::where('user_id', $user->id)->sum('amount');

        return [
            'user' => $user,
            'income' => $income,
            'estimated_tax' => round($income * $rate, 2),
            'paid' => $paid,
            'flagged' => $flagged,
        ];
    });

    $pdf = Pdf::loadView('admin.export-pdf', compact('users'));

    return $pdf->download('taxpayers_' . now()->format('Y_m_d_His') . '.pdf');
}
public function logs()
{
    $logs = AdminLog::with(['admin', 'targetUser'])->latest()->paginate(20);

    return view('admin.logs', compact('logs'));
}

public function toggleActive(User $user)
{
    $user->update(['is_active' => ! $user->is_active]);

    AdminLog::create([
        'admin_id' => auth()->id(),
        'target_user_id' => $user->id,
        'action' => $user->is_active ? 'activated_user' : 'deactivated_user',
        'description' => $user->is_active
            ? "Reactivated account for {$user->name}"
            : "Deactivated account for {$user->name}",
    ]);

    return back()->with('status', $user->is_active ? 'Account reactivated.' : 'Account deactivated.');
}

}