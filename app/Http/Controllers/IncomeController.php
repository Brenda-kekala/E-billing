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
            // This says get my income entries, newest first

        return view('incomes.index', compact('incomes'));
    }

    public function store(Request $request)  //saves a new entry
    {
        $data = $request->validate([
            'income_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $data['user_id'] = auth()->id();
        Income::create($data);
        //this checks the answers(real date, an amount above 0) then adds your number 
        // and saves the row and then you see income recorded 

        return redirect()->route('incomes.index')->with('status', 'Income recorded.');
    }

    public function destroy(Income $income) //delete an entry
    {
        abort_if($income->user_id !== auth()->id(), 403);
        $income->delete();
        // this is ownership protection.If an entry belongs to someone else stop with 403 Forbidden

        return redirect()->route('incomes.index')->with('status', 'Entry deleted.');
    }
}