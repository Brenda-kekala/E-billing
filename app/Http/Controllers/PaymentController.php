<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'period'    => 'required|date_format:Y-m',
            'amount'    => 'required|numeric|min:0.01',
            'paid_on'   => 'required|date',
            'reference' => 'nullable|string|max:100',
            // this checks the answers(validate) input by the user
        ]);

        if ($data['period'] >= now()->format('Y-m')) {
            return back()
               ->withErrors(['period' => 'You cannot pay tax for a month until it has ended.'])
               ->withInput();
            //this rejects a user from bypassing the block current month in tax payment
        }

        $data['user_id'] = auth()->id();
        // this stamps your number on the payment and cant save it under someones else
        Payment::create($data); 
        // this writes the row in the database

        return redirect()->route('dashboard')->with('status', 'Payment recorded.');
    }

    public function destroy(Payment $payment)
    {
        abort_if($payment->user_id !== auth()->id(), 403);
        // the above line allows the user to delete an input but the row isnt deleted, its soft-deleted and hidden
        $payment->delete();

        return redirect()->route('dashboard')->with('status', 'Payment deleted.');//   
        }
}