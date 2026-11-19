<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);

        $month = $request->input('month', now()->format('Y-m')); //typed month
        [$year, $m] = explode('-', $month);
        // 15-16 says use the month picked, if no month is picked use this month then splt into year and month

        $query = Income::where('user_id', auth()->id());

        $monthlyTotal = (clone $query)
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $m)
            ->sum('amount');
            // 21-24 says add up all my income in that month

        $annualTotal = (clone $query)
            ->whereYear('income_date', $year)
            ->sum('amount');
            // this says add up all year income

        $rate = config('tax.tot_rate');
        $taxDue = round($monthlyTotal * $rate, 2);
        // 33 says work out the tax using 1.5% rate
        $dueDate = Carbon::create($year, $m, 1)->addMonth()->day(20);
        //35 works out the due date. It takes 1st of the month you picked because it cant say 31st becoz not all months are the same
        //, jumps the next month and sets the day to 20 so that income is due the next month   
        if ($annualTotal < config('tax.tot_min')) {
            $eligibility = 'below'; //eligibility compares annual total  with the range 1M-25M
        } elseif ($annualTotal > config('tax.tot_max')) {
            $eligibility = 'above';
        } else {
            $eligibility = 'within';
        }

        return view('tax.index', compact(
            'month', 'monthlyTotal', 'annualTotal', 'rate',
            'taxDue', 'dueDate', 'eligibility'
        ));
    }
}