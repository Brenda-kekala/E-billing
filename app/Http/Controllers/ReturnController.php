<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\TaxReturn;
use App\Services\ItaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    private function isTotUser(): bool
    {
        return in_array(auth()->user()->tax_regime, [null, '', 'tot'], true);
        // if users regime is empty or TOT true otherwise false. Returns apply for TOT only
    }

    public function index(ItaxService $itax)
    //Page listing the last 6 months and their filing status
    {
        $userId = auth()->id();
        $rate = config('tax.tot_rate');

        $returns = TaxReturn::where('user_id', $userId)
            ->where('regime', 'tot')
            ->get()
            ->map(fn ($r) => $itax->refreshStatus($r))
            ->keyBy('period');

        $rows = [];
        for ($i = 0; $i < 6; $i++) {
            $start = now()->startOfMonth()->subMonths($i);

            $income = Income::where('user_id', $userId)
                ->whereYear('income_date', $start->year)
                ->whereMonth('income_date', $start->month)
                ->sum('amount');

            $rows[] = [
                'period' => $start->format('Y-m'),
                'label'  => $start->format('F Y'),
                'income' => $income,
                'tax'    => round($income * $rate, 2),
                'due'    => $start->copy()->addMonth()->day(20),
                'ended'  => $i > 0,                 // current month hasn't ended yet
                'return' => $returns->get($start->format('Y-m')),
            ];
        }

        $isTot = $this->isTotUser();

        return view('returns.index', compact('rows', 'isTot'));
    }

    // Files a return for one month
    public function file(Request $request, ItaxService $itax)
    {
        $data = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
        ]);

        if (! $this->isTotUser()) {
            return back()->withErrors(['period' => 'Monthly returns are for Turnover Tax users only.']);
        }

        $userId = auth()->id();
        $start = Carbon::createFromFormat('Y-m-d', $data['period'] . '-01')->startOfMonth();

        if ($start->gte(now()->startOfMonth())) { //month must have ended
            return back()->withErrors(['period' => 'You can only file a return after the month has ended.']);
        }

        if (TaxReturn::where('user_id', $userId)->where('regime', 'tot')->where('period', $data['period'])->exists()) {
            return back()->withErrors(['period' => 'A return for this month has already been filed.']); // return for the same user and month must not exist
        }

        $income = Income::where('user_id', $userId)
            ->whereYear('income_date', $start->year)
            ->whereMonth('income_date', $start->month)
            ->sum('amount');

        $tax = round($income * config('tax.tot_rate'), 2);

        $due = $start->copy()->addMonth()->day(20)->endOfDay(); 
        // Due on the 20th of the following month
        $penalty = now()->gt($due) ? config('tax.late_filing_fee', 1000) : 0;

        $ack = $itax->submit([
            'user_id' => $userId,
            'period'  => $data['period'],
            'tax'     => $tax,
        ]);

        $return = TaxReturn::create([
            'user_id'            => $userId,
            'regime'             => 'tot',
            'period'             => $data['period'],
            'income'             => $income,
            'tax'                => $tax,
            'is_nil'             => $income <= 0,
            'penalty'            => $penalty,
            'status'             => $ack['status'],
            'acknowledgement_no' => $ack['acknowledgement_no'],
            'filed_at'           => now(),
        ]);

        return redirect()->route('returns.show', $return)
            ->with('status', 'Return filed successfully. Acknowledgement: ' . $return->acknowledgement_no);
    }

    // Tracking page for one return
    public function show(TaxReturn $taxReturn, ItaxService $itax)
    {
        abort_unless($taxReturn->user_id === auth()->id(), 403); //security check. It doesnt allow snother user to type another
        //users return number in the URL and web to read it.

        $taxReturn = $itax->refreshStatus($taxReturn);

        return view('returns.show', ['return' => $taxReturn]);
    }
}

