<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaxReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $period;
    public $taxDue;
    public $dueDate;
    public $status;

    public function __construct($userName, $period, $taxDue, $dueDate, $status)
    {
        $this->userName = $userName;
        $this->period = $period;
        $this->taxDue = $taxDue;
        $this->dueDate = $dueDate;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject('Turnover Tax Reminder — ' . $this->period)
            ->view('emails.reminder');
    }
}