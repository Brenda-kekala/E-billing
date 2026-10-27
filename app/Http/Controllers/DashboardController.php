<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $rate = config('tax.tot_rate');
        $rows = [];

        for ($i = 0; $i < 3; $i++) {
            $start = now()->startOfMonth()->subMonths($i);
            $period = $start->format('Y-m');

            $income = Income::where('user_id', $userId)
                ->whereYear('income_date', $start->year)
                ->whereMonth('income_date', $start->month)
                ->sum('amount');

            $tax = round($income * $rate, 2);
            $paid = Payment::where('user_id', $userId)->where('period', $period)->sum('amount');
            $balance = max($tax - $paid, 0);
            $due = $start->copy()->addMonth()->day(20);

            $penalty = 0;
            if ($tax == 0) {
                $status = 'No tax';
            } elseif ($balance == 0) {
                $status = 'Paid';
            } elseif (now()->gt($due->copy()->endOfDay())) {
                $status = 'Overdue';
                $monthsLate = max(1, $due->diffInMonths(now()) + 1);
                $penalty = config('tax.late_filing_fee') * $monthsLate
                    + $balance * config('tax.late_payment_penalty')
                    + $balance * config('tax.late_payment_interest') * $monthsLate;
            } else {
                $status = 'Due';
            }

            $rows[] = [
                'period'  => $start->format('F Y'),
                'income'  => $income,
                'tax'     => $tax,
                'paid'    => $paid,
                'balance' => $balance,
                'due'     => $due,
                'status'  => $status,
                'penalty' => round($penalty, 2),
            ];
        }

        $payments = Payment::where('user_id', $userId)->orderBy('paid_on', 'desc')->get();

        return view('dashboard', compact('rows', 'payments'));
    }

    public function updateRegime(\Illuminate\Http\Request $request)
{
    $request->validate([
        'tax_regime' => 'required|in:tot,self_assessment',
    ]);

    $request->user()->update(['tax_regime' => $request->tax_regime]);

    return redirect()->route('dashboard')->with('status', 'Tax regime updated.');
}

}