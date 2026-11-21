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
        'voided_quantity',
        'purchase_price',
        'selling_price',
        'modifiers',
        'modifier_total',
        'notes',
        'station',
        'ticket_status',
        'voided_at',
        'voided_by',
        'void_reason',
        'subtotal',
    ];

    public const TICKET_PENDING = 'pending';
    public const TICKET_PREPARING = 'preparing';
    public const TICKET_READY = 'ready';

    public const TICKET_STATUSES = [
        self::TICKET_PENDING => 'Menunggu',
        self::TICKET_PREPARING => 'Dikerjakan',
        self::TICKET_READY => 'Siap',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'modifiers' => 'array',
        'modifier_total' => 'decimal:2',
        'quantity' => 'integer',
        'voided_quantity' => 'integer',
        'voided_at' => 'datetime',
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
