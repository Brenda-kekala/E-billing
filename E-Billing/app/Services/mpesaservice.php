<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MpesaService
{
    protected function baseUrl()
    {
        return config('mpesa.base_url');
    }

    public function getAccessToken()
    {
        return Cache::remember('mpesa_access_token', 3500, function () {
            $response = Http::withBasicAuth(
                config('mpesa.consumer_key'),
                config('mpesa.consumer_secret')
            )->get($this->baseUrl() . '/oauth/v1/generate?grant_type=client_credentials');

            return $response->json('access_token');
        });
    }

    protected function timestamp()
    {
        return now()->format('YmdHis');
    }

    protected function password($timestamp)
    {
        return base64_encode(config('mpesa.shortcode') . config('mpesa.passkey') . $timestamp);
    }

    public function stkPush($phone, $amount, $reference, $description)
    {
        $timestamp = $this->timestamp();

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl() . '/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => config('mpesa.shortcode'),
                'Password'          => $this->password($timestamp),
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => (int) $amount,
                'PartyA'            => $phone,
                'PartyB'            => config('mpesa.shortcode'),
                'PhoneNumber'       => $phone,
                'CallBackURL'       => config('mpesa.callback_url'),
                'AccountReference'  => $reference,
                'TransactionDesc'   => $description,
            ]);

        return $response->json();
    }

    public function queryStatus($checkoutRequestId)
    {
        $timestamp = $this->timestamp();

        $response = Http::withToken($this->getAccessToken())
            ->post($this->baseUrl() . '/mpesa/stkpushquery/v1/query', [
                'BusinessShortCode' => config('mpesa.shortcode'),
                'Password'          => $this->password($timestamp),
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ]);

        return $response->json();
    }
}