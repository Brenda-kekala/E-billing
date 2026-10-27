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
        ]);

        $data['user_id'] = auth()->id();
        Payment::create($data);

        return redirect()->route('dashboard')->with('status', 'Payment recorded.');
    }

    public function destroy(Payment $payment)
    {
        abort_if($payment->user_id !== auth()->id(), 403);
        $payment->delete();

        return redirect()->route('dashboard')->with('status', 'Payment deleted.');
    }
}