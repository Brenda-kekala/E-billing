<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'user_id', 'period', 'amount', 'paid_on', 'reference',
    'checkout_request_id', 'mpesa_receipt', 'status',
];

}