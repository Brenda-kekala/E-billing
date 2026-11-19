<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Payment;
use App\Models\TaxReturn;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdminLog;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function index()
    {
        // this  function is a get page with no inputs
        $rate = config('tax.tot_rate');

        $totalUsers = User::where('is_admin', false)->count(); //counts non admin users
        $totalIncome = Income::sum('amount'); //sums all income 
        $totalFlagged = Income::where('flagged', true)->count(); //counts flagged entries
        $totalReturns = TaxReturn::count();

        $users = User::where('is_admin', false)->get()->map(function ($user) use ($rate) {
            $income = Income::where('user_id', $user->id)->sum('amount');
            $flagged = Income::where('user_id', $user->id)->where('flagged', true)->count();
            $paid = Payment::where('user_id', $user->id)->sum('amount');

            // filing info
            $returnsFiled = TaxReturn::where('user_id', $user->id)->count();
            $lastReturn = TaxReturn::where('user_id', $user->id)->orderBy('period', 'desc')->first();

            return [
                'user' => $user,
                'income' => $income,
                'estimated_tax' => round($income * $rate, 2),
                'paid' => $paid,
                'flagged' => $flagged,
                'returns_filed' => $returnsFiled,
                'last_return' => $lastReturn,
            ];
        });
        // 19-34 builds a row with Tincome, no of  flagged entries, estimated tax, paid tax

        return view('admin.index', compact('totalUsers', 'totalIncome', 'totalFlagged', 'totalReturns', 'users'));
        // views admin/index.php with TUsers, TIncome, TFlagged and turns variables into array the view receives
    }

    public function show(User $user)
    {
        $incomes = Income::where('user_id', $user->id)->orderBy('income_date', 'desc')->get();
        $payments = Payment::where('user_id', $user->id)->orderBy('paid_on', 'desc')->get();
        $returns = TaxReturn::where('user_id', $user->id)->orderBy('period', 'desc')->get();
        $totalIncome = $incomes->sum('amount');
        $totalPaid = $payments->sum('amount');
        $estimatedTax = round($totalIncome * config('tax.tot_rate'), 2);

        return view('admin.user', compact('user', 'incomes', 'payments', 'returns', 'totalIncome', 'totalPaid', 'estimatedTax'));
        // 42-50 input is the user. this loads users income and payments from the newest, totals them and estimates the tax. Returns the view
    }

    public function toggleFlag(Request $request, Income $income)
{
    $data = $request->validate([
        'flag_note' => 'nullable|string|max:255',
        // the input is a form request with optional flag note(reason)and the income flagged
    ]);

    $wasFlagged = $income->flagged; //remembers flagged entry

    $income->update([ 
        'flagged' => ! $wasFlagged,  
        // ! means opposite, updated flagged becomes not flagged and vice versa and removes flag notewhen unflagged
        'flag_note' => $wasFlagged ? null : ($data['flag_note'] ?? 'Flagged for review'),
    ]);

    AdminLog::create([
        'admin_id' => auth()->id(),
        'target_user_id' => $income->user_id,
        'action' => $wasFlagged ? 'unflagged_income' : 'flagged_income',
        'description' => $wasFlagged
            ? "Unflagged income #{$income->id}"
            : "Flagged income #{$income->id}" . (($data['flag_note'] ?? null) ? " — reason: {$data['flag_note']}" : ''),
            // says who did what in the admin page
    ]);

    return back()->with('status', 'Updated.');
}
    public function editUser(User $user)
{
    return view('admin.user-edit', compact('user'));
    // returns the edit form view only
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
    // 88-105, saves the edit form and redirects to admin.users
}
public function exportCsv()
{
    // this function builds a file name with a timestamp and sets headers so browser downloads instaed of displaying
    // writes the haeader row and one row per taxpayer
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
            // fputcsv($file writes an array of data as formatted CSV row
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function exportPdf()
{
    // it builds user, occupation, income entries, estimated tax, paid tax, and flagged entries row 
    // and processes and displayes view admin.export-pdf as a PDF
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
    // this function loads related admin and user in advance. 
    //This prevents runnig one extra query per log row hence making the system faster. Thi means it takes one quick trip to DB to grab all extra info it need ahead of time. 
}

public function toggleActive(User $user)
{
    // suspends or reactivates an account
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
    // this function flips is-active and logs it. After update and user-is-active already holds the new value, the log texts and flashes a mesaage.
}
public function occupations()
{
    $rate = config('tax.tot_rate');
    $labels = config('tax.occupations');

    $users = User::where('is_admin', false)->get();

    // one query each, grouped by user (instead of a query per user)
    $incomeByUser = Income::selectRaw('user_id, SUM(amount) as total')
        ->groupBy('user_id')->pluck('total', 'user_id');

    $paidByUser = Payment::whereNotIn('status', ['pending', 'failed'])
        ->selectRaw('user_id, SUM(amount) as total')
        ->groupBy('user_id')->pluck('total', 'user_id');

    $groups = $users
        ->groupBy(fn ($u) => $u->occupation ?: 'unspecified')
        ->map(function ($members, $key) use ($labels, $rate, $incomeByUser, $paidByUser) {
            $income = $members->sum(fn ($u) => $incomeByUser[$u->id] ?? 0);
            $paid = $members->sum(fn ($u) => $paidByUser[$u->id] ?? 0);

            return [
                'label' => $labels[$key] ?? 'Not specified',
                'count' => $members->count(),
                'income' => $income,
                'average' => round($income / max($members->count(), 1), 2),
                'estimated_tax' => round($income * $rate, 2),
                'paid' => $paid,
                'members' => $members->map(fn ($u) => [
                    'user' => $u,
                    'income' => $incomeByUser[$u->id] ?? 0,
                ])->sortByDesc('income')->values(),
            ];
        })
        ->sortByDesc('income')
        ->values();

    $totalIncome = $groups->sum('income');

    return view('admin.occupations', compact('groups', 'totalIncome'));
}

}