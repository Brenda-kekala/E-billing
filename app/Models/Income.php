<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Income extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'income_date',
        'amount',
        'description',
        'flagged',
        'flag_note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}