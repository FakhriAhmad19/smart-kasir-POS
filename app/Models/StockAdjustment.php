<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'app_stock_adjustments';

    protected $fillable = [
        'app_product_id',
        'app_user_id',
        'type',
        'quantity_before',
        'quantity_adjusted',
        'quantity_after',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'app_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'app_user_id');
    }
}
