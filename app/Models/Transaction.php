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
        'status',
        'paid_at',
        'app_user_id',
        'opened_by',
        'split_from_id',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
        'app_shift_id',
        'order_type',
        'online_platform',
        'app_table_id',
        'table_name',
        'customer_name',
        'queue_number',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'service_charge_amount',
        'final_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
        'notes',
    ];

    public const ORDER_TYPES = [
        'dine_in' => 'Dine-in',
        'take_away' => 'Take Away',
        'online' => 'Online',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const ONLINE_PLATFORMS = ['GoFood', 'GrabFood', 'ShopeeFood', 'Lainnya'];

    protected $casts = [
        'queue_number' => 'integer',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
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

    protected static function booted(): void
    {
        // Invariant: a paid transaction always has paid_at, otherwise it
        // would silently drop out of every date-filtered report.
        static::saving(function (Transaction $trx) {
            if (($trx->status ?? self::STATUS_PAID) === self::STATUS_PAID && ! $trx->paid_at) {
                $trx->paid_at = now();
            }
        });
    }

    /** Settled sales only — every revenue/report query must use this. */
    public function scopePaid($query)
    {
        return $query->where('app_transactions.status', self::STATUS_PAID);
    }

    public function scopeOpen($query)
    {
        return $query->where('app_transactions.status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function table()
    {
        return $this->belongsTo(CafeTable::class, 'app_table_id');
    }

    public function orderTypeLabel(): string
    {
        return self::ORDER_TYPES[$this->order_type] ?? $this->order_type;
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'app_transaction_id');
    }
}
