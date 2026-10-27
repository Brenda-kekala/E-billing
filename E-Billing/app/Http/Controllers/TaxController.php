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

        $month = $request->input('month', now()->format('Y-m'));
        [$year, $m] = explode('-', $month);

        $query = Income::where('user_id', auth()->id());

        $monthlyTotal = (clone $query)
            ->whereYear('income_date', $year)
            ->whereMonth('income_date', $m)
            ->sum('amount');

        $annualTotal = (clone $query)
            ->whereYear('income_date', $year)
            ->sum('amount');

        $rate = config('tax.tot_rate');
        $taxDue = round($monthlyTotal * $rate, 2);
        $dueDate = Carbon::create($year, $m, 1)->addMonth()->day(20);

        if ($annualTotal < config('tax.tot_min')) {
            $eligibility = 'below';
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