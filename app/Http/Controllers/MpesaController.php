<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; 

class MpesaController extends Controller
{
    public function pay(Request $request, MpesaService $mpesa)
    {
        $data = $request->validate([
            'phone'  => 'required|digits:12|starts_with:254',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|date_format:Y-m',
        ]);

        if ($data['period'] >= now()->format('Y-m')) {
            return back()
               ->withErrors(['period' => 'You cannot pay tax for a month until it has ended.'])
               ->withInput();
            //this rejects a user from bypassing the block current month in tax payment
        }

        $result = $mpesa->stkPush(
            $data['phone'],
            $data['amount'],
            'EBilling-' . $data['period'],
            'Turnover Tax payment'
        );

        if (($result['ResponseCode'] ?? null) !== '0') {
            return back()->with('status', 'Could not start M-Pesa payment: ' . ($result['errorMessage'] ?? 'unknown error'));
        }
        // if the ResponseCode is NOT 0, Safaricom rejected the request and you see "Could not start M-Pesa payment"
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
    // ---------- Simulated M-Pesa prompt (demo mode) ----------

    // Step 1: user clicks Pay, a pending payment is created
    public function simulate(Request $request)
    {
        $data = $request->validate([
            'phone'  => 'required|digits:12|starts_with:254',
            'amount' => 'required|numeric|min:1',
            'period' => 'required|date_format:Y-m',
        ]);

        if ($data['period'] >= now()->format('Y-m')) {
            return back()
               ->withErrors(['period' => 'You cannot pay tax for a month until it has ended.'])
               ->withInput();
            //this rejects a user from bypassing the block current month in tax payment
        }

        $masked = substr($data['phone'], 0, 6) . '***' . substr($data['phone'], -3);

        $payment = Payment::create([
            'user_id'             => auth()->id(),
            'period'              => $data['period'],
            'amount'              => $data['amount'],
            'paid_on'             => now(),
            'reference'           => 'M-Pesa (simulated) ' . $masked,
            'checkout_request_id' => 'SIM-' . Str::upper(Str::random(10)),
            'status'              => 'pending',
        ]);

        return redirect()->route('mpesa.prompt', $payment);
    }

    // Step 2: show the phone-style PIN screen
    public function prompt(Payment $payment)
    {
        abort_if($payment->user_id !== auth()->id(), 403);

        if ($payment->status !== 'pending') {
            return redirect()->route('mpesa.status', $payment);
        }

        return view('mpesa.prompt', compact('payment'));
    }

    // Step 3: user presses Send (completed) or Cancel (failed)
    public function confirm(Request $request, Payment $payment)
    {
        abort_if($payment->user_id !== auth()->id(), 403);

        if ($payment->status !== 'pending') {
            return redirect()->route('mpesa.status', $payment);
        }

        if ($request->input('action') === 'cancel') {
            $payment->update(['status' => 'failed']);

            return redirect()->route('mpesa.status', $payment)
                ->with('status', 'Request cancelled by user.');
        }

        // The PIN is only checked for format. It is never stored.
        $request->validate(['pin' => 'required|digits:4']);

        $payment->update([
            'status'        => 'completed',
            'mpesa_receipt' => 'SIM' . Str::upper(Str::random(7)),
            'paid_on'       => now(),
        ]);

        return redirect()->route('mpesa.status', $payment);
    }
}