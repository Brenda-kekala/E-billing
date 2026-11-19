<?php

namespace App\Console\Commands;

use App\Mail\TaxReminderMail;
use App\Models\Income;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaxReminders extends Command
{
    protected $signature = 'tax:remind';
    protected $description = 'Email users whose Turnover Tax is due or overdue for last month';

    public function handle()
    {
        $start = now()->startOfMonth()->subMonth();
        $period = $start->format('Y-m');
        $rate = config('tax.tot_rate');
        $dueDate = $start->copy()->addMonth()->day(20);

     $users = User::where('is_admin', false)
        ->where(function ($q) {
            $q->whereNull('tax_regime')->orWhere('tax_regime', 'tot');
        })
        ->get();
        $sent = 0;

        foreach ($users as $user) {
            $income = Income::where('user_id', $user->id)
                ->whereYear('income_date', $start->year)
                ->whereMonth('income_date', $start->month)
                ->sum('amount');

            if ($income <= 0) {
                continue;
            }

            $tax = round($income * $rate, 2);
            $paid = Payment::where('user_id', $user->id)->where('period', $period)->whereNotIn('status', ['pending', 'failed'])->sum('amount');
            $balance = max($tax - $paid, 0);

            if ($balance <= 0) {
                continue;
            }

            $status = now()->gt($dueDate->copy()->endOfDay()) ? 'Overdue' : 'Due';

            try {

                Mail::to($user->email)->send(new TaxReminderMail(
                      $user->name,
                      $start->format('F Y'),
                      $balance,
                      $dueDate->format('d M Y'),
                      $status
            ));

            $sent++;
            $this->line("Sent to {$user->email}");
        } catch (\Exception $e) {
            $this->error("Failed for {$user->email}: " . $e->getMessage());
        }

            sleep(15); // pause so Mailtrap's free plan doesn't block us
        }
        

        $this->info("Sent {$sent} reminder email(s) for {$period}.");
    }
}