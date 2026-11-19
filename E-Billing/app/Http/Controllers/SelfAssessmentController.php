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
        // 18 adds up all income for the year
        $expenseTotal = Expense::where('user_id', $userId)->whereYear('expense_date', $year)->sum('amount');
        // 20 adds all year expenses
        $profit = max($gross - $expenseTotal, 0);
        // 22 this means the gross income, expense subtracted to get the taxable income and can never go below zero

        // Apply the tax bands
        $remaining = $profit; // money waiting to be taxed. Original income
        $grossTax = 0; // running total tax so far starting at 0
        $breakdown = []; // an empty list filled by the foreach loop and becomes portion of profit, rate and tax on screen 
        foreach (config('tax.bands') as $band) {  // reads band list and takes one band at a time starting with the 10%
            if ($remaining <= 0) { //if no money is left loop quits early
                break;
            }
            $slice = $band['limit'] === null ? $remaining : min($remaining, $band['limit']);
            $bandTax = $slice * $band['rate'];
            $breakdown[] = ['slice' => $slice, 'rate' => $band['rate'], 'tax' => $bandTax];
            $grossTax += $bandTax;
            $remaining -= $slice; 
        }
        // 29-38 This reads the list tax band in config/tax.php and does the tax work. 
        // foreach is the loop, so when a number changes in config/tax it will use the new number with no change in controller

        $relief = min(config('tax.personal_relief'), $grossTax);
        $netTax = round($grossTax - $relief, 2);
        // 42-43 says that every user gets 28,800 discount P/A(personal relief) but you cant get a refund if your tax bill is small
        // example if yout tax is 2000 it will automatically be 0 because of the relief but you wont get the rest of the money it gets lost.

        // Deadlines
        $filingDeadline = $year >= 2026
            ? Carbon::create($year + 1, 4, 30)
            : Carbon::create($year + 1, 6, 30);

        $instalments = [];
        if ($netTax > config('tax.instalment_threshold')) {
            foreach ([4, 6, 9, 12] as $m) { // when a users yearly tax  is above 40Gs you dont wait
            // and pay it all at once, you pay in 4 equal piece during the year, 20th of 4, 6, 9, 12.
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
            ); // if their is an tax payment overdue user pays 2000 even if overdue calculated is less or pays 5% of the tax 
        }

        // Comparison with Turnover Tax
        $totTax = round($gross * config('tax.tot_rate'), 2); //compares TOT and self assess and checks whether income is between 1M $ 25M
        //and check what user would pay under TOT
        $totEligible = $gross >= config('tax.tot_min') && $gross <= config('tax.tot_max');

        $expenseTotal = 0;

        return view('self-assessment.index', compact(
            'year', 'gross', 'expenseTotal', 'profit', 'breakdown', 'grossTax', 'relief',
            'netTax', 'filingDeadline', 'instalments', 'latePenalty', 'totTax',
            'totEligible'
        ));
    }

    public function storeExpense(Request $request) //Checks expense input, adds users number and saves it
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

    public function destroyExpense(Expense $expense) // when user wnats to delete expense this part checks
    //if it yours and then deletes it
    {
        abort_if($expense->user_id !== auth()->id(), 403);
        $expense->delete();

        return redirect()->route('sa.index')->with('status', 'Expense deleted.');
    }
}