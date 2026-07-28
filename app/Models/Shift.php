<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'app_shifts';

    protected $fillable = [
        'app_user_id',
        'start_time',
        'end_time',
        'starting_cash',
        'total_cash_sales',
        'total_qris_sales',
        'expected_ending_cash',
        'actual_ending_cash',
        'cash_difference',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'app_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'app_shift_id');
    }
}
