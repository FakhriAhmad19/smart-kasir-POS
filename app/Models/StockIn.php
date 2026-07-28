<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIn extends Model
{
    use HasFactory;

    protected $table = 'app_stock_ins';

    protected $fillable = [
        'app_product_id',
        'app_user_id',
        'supplier_name',
        'quantity',
        'purchase_price',
        'total_price',
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
