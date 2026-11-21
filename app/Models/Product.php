<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'app_products';

    protected $fillable = [
        'app_category_id',
        'sku',
        'barcode',
        'name',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    /**
     * Public URL of the product photo, or null when no photo is set.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'app_category_id');
    }

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'app_product_modifier_group', 'app_product_id', 'app_modifier_group_id')
            ->withPivot('sort_order')
            ->orderBy('app_product_modifier_group.sort_order')
            ->orderBy('app_modifier_groups.sort_order');
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'app_product_id');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
