<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index()
    {
        $incomes = Income::where('user_id', auth()->id())
            ->orderBy('income_date', 'desc')
            ->get();

        return view('incomes.index', compact('incomes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'income_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $data['user_id'] = auth()->id();
        Income::create($data);

        return redirect()->route('incomes.index')->with('status', 'Income recorded.');
    }

    public function destroy(Income $income)
    {
        abort_if($income->user_id !== auth()->id(), 403);
        $income->delete();

        return redirect()->route('incomes.index')->with('status', 'Entry deleted.');
    }
}