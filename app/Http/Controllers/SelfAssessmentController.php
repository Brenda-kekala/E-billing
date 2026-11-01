<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SelfAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['year' => 'nullable|digits:4']);
        $year = (int) $request->input('year', now()->year);
        $userId = auth()->id();

        $gross = Income::where('user_id', $userId)->whereYear('income_date', $year)->sum('amount');
        $expenseTotal = Expense::where('user_id', $userId)->whereYear('expense_date', $year)->sum('amount');
        $expenses = Expense::where('user_id', $userId)
           ->whereYear('expense_date', $year)
           ->orderBy('expense_date', 'desc')
           ->get(); 
        $profit = max($gross - $expenseTotal, 0);

        // Apply the tax bands
        $remaining = $profit;
        $grossTax = 0;
        $breakdown = [];
        foreach (config('tax.bands') as $band) {
            if ($remaining <= 0) {
                break;
            }
            $slice = $band['limit'] === null ? $remaining : min($remaining, $band['limit']);
            $bandTax = $slice * $band['rate'];
            $breakdown[] = ['slice' => $slice, 'rate' => $band['rate'], 'tax' => $bandTax];
            $grossTax += $bandTax;
            $remaining -= $slice;
        }

        $relief = min(config('tax.personal_relief'), $grossTax);
        $netTax = round($grossTax - $relief, 2);

        // Deadlines
        $filingDeadline = $year >= 2026
            ? Carbon::create($year + 1, 4, 30)
            : Carbon::create($year + 1, 6, 30);

        $instalments = [];
        if ($netTax > config('tax.instalment_threshold')) {
            foreach ([4, 6, 9, 12] as $m) {
                $instalments[] = [
                    'due'    => Carbon::create($year, $m, 20),
                    'amount' => round($netTax / 4, 2),
                ];
            }
        }

        $latePenalty = 0;
        if ($netTax > 0 && now()->gt($filingDeadline->copy()->endOfDay())) {
            $latePenalty = max(
                config('tax.sa_late_filing_min'),
                $netTax * config('tax.sa_late_filing_rate')
            );
        }

        // Comparison with Turnover Tax
        $totTax = round($gross * config('tax.tot_rate'), 2);
        $totEligible = $gross >= config('tax.tot_min') && $gross <= config('tax.tot_max');

    

        return view('self-assessment.index', compact(
            'year', 'gross', 'expenseTotal', 'profit', 'breakdown', 'grossTax', 'relief',
            'netTax', 'filingDeadline', 'instalments', 'latePenalty', 'totTax',
            'totEligible', 'expenses'
        ));
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'expense_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id();
        Expense::create($data);

        return redirect()->route('sa.index', ['year' => date('Y', strtotime($data['expense_date']))])
            ->with('status', 'Expense recorded.');
    }

    public function destroyExpense(Expense $expense)
    {
        abort_if($expense->user_id !== auth()->id(), 403);
        $expense->delete();

        return redirect()->route('sa.index')->with('status', 'Expense deleted.');
    }
}