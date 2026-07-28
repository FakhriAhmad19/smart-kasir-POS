<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'app_transactions';

    protected $fillable = [
        'invoice_number',
        'app_user_id',
        'app_shift_id',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'app_user_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'app_shift_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'app_transaction_id');
    }
}
