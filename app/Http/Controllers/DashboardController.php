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
            $paid = Payment::where('user_id', $userId)
               ->where('period', $period)
               ->whereNotIn('status', ['pending', 'failed'])
               ->sum('amount');
        
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
       //its says open payment box and take only my payments and put the newest first  
        return view('dashboard', compact('rows', 'payments'));
      // this puts list in a bag and hands it to the page
        }

    public function updateRegime(\Illuminate\Http\Request $request)
{
    $request->validate([
        'tax_regime' => 'required|in:tot,self_assessment',
    ]);
    // Validate is the check and the slip must contain a choice either tot or self-assessment

    $request->user()->update(['tax_regime' => $request->tax_regime]);
    //this writes the choice onto the logged-in user and nobody can change someone elses
    return redirect()->route('dashboard')->with('status', 'Tax regime updated.');
    //this sends back to the dashboard with green Tax Refime updated message
}

}