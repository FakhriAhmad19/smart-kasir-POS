<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'app_transaction_details';

    protected $fillable = [
        'app_transaction_id',
        'app_product_id',
        'quantity',
        'purchase_price',
        'selling_price',
        'modifiers',
        'modifier_total',
        'notes',
        'station',
        'subtotal',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'modifiers' => 'array',
        'modifier_total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'app_transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'app_product_id');
    }
}
