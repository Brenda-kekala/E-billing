<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class MpesaController extends Controller
{
    public function pay(Request $request, MpesaService $mpesa)
    {
        $data = $request->validate([
            'phone'  => 'required|digits:12|starts_with:254',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|date_format:Y-m',
        ]);

        $result = $mpesa->stkPush(
            $data['phone'],
            $data['amount'],
            'EBilling-' . $data['period'],
            'Turnover Tax payment'
        );

        if (($result['ResponseCode'] ?? null) !== '0') {
            return back()->with('status', 'Could not start M-Pesa payment: ' . ($result['errorMessage'] ?? 'unknown error'));
        }
        // if the responseCode is 0 you see Could not start Mpesa Payment

        $payment = Payment::create([
            'user_id'             => auth()->id(),
            'period'              => $data['period'],
            'amount'              => $data['amount'],
            'paid_on'             => now(),
            'reference'           => 'M-Pesa',
            'checkout_request_id' => $result['CheckoutRequestID'],
            'status'              => 'pending',
        ]);

        return redirect()->route('mpesa.status', $payment)
            ->with('status', 'Payment request sent. On the sandbox, this usually confirms within a few seconds — click "Refresh status" below.');
        // 33-44 if Mpesa payment starts the app saves payment wit the status pending and then it opens the status page
            }



    public function status(Payment $payment, MpesaService $mpesa)
    {
        abort_if($payment->user_id !== auth()->id(), 403);

        if ($payment->status === 'pending' && $payment->checkout_request_id) {
            $result = $mpesa->queryStatus($payment->checkout_request_id);
            Log::info('M-Pesa query result', $result);
            $code = $result['ResultCode'] ?? null;

    
            if ($code === '0' || $code === 0) {
                $payment->update([
                    'status'        => 'completed',
                    'mpesa_receipt' => $result['MpesaReceiptNumber'] ?? ('SANDBOX' . now()->timestamp),
                    // Code=0 payment completed + RECEIPT
                 ]);
            } elseif ($code === '4999' || is_null($code)) {
                 // still processing on Safaricom's side — leave it pending so the user can refresh again
            } else {
                $payment->update(['status' => 'failed']);
            }    
        }

        return view('mpesa.status', compact('payment'));
    }
}