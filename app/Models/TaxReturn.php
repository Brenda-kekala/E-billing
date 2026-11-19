<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxReturn extends Model
{
    protected $fillable = [
        'user_id', 'regime', 'period', 'income', 'tax', 'is_nil',
        'penalty', 'status', 'acknowledgement_no', 'filed_at',
    ];

    protected $casts = [
        'is_nil'   => 'boolean',
        'filed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}