<?php

namespace App\Services;

use App\Models\TaxReturn;
use Illuminate\Support\Str;


//  A real system would call the iTax API here.
//   We pretend to submit the return and simulate how the status progresses.
 
class ItaxService
{
    // "Sends" the return and gives back an acknowledgement number
    public function submit(array $data): array
    {
        return [
            'acknowledgement_no' => 'ACK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'status'             => 'submitted',
        ];
    }

    // Simulated real-time tracking: status depends on how long ago it was filed
    public function refreshStatus(TaxReturn $return): TaxReturn
    {
        if ($return->status === 'accepted') {
            return $return;
        }

        $seconds = now()->diffInSeconds($return->filed_at);

        if ($seconds >= 25) {
            $new = 'accepted';
        } elseif ($seconds >= 10) {
            $new = 'processing';
        } else {
            $new = 'submitted';
        }

        if ($new !== $return->status) {
            $return->update(['status' => $new]);
        }

        return $return;
    }
}