<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $table = 'app_modifier_groups';

    protected $fillable = [
        'name',
        'is_required',
        'min_select',
        'max_select',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_select' => 'integer',
        'max_select' => 'integer',
        'sort_order' => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(ModifierOption::class, 'app_modifier_group_id')->orderBy('sort_order')->orderBy('id');
    }

    public function activeOptions()
    {
        return $this->options()->where('is_active', true);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'app_product_modifier_group', 'app_modifier_group_id', 'app_product_id');
    }

    /** Minimum number of options the cashier must pick. */
    public function requiredMin(): int
    {
        return $this->is_required ? max(1, $this->min_select) : $this->min_select;
    }

    public function isSingleChoice(): bool
    {
        return $this->max_select === 1;
    }

    public function selectionLabel(): string
    {
        $min = $this->requiredMin();
        if ($this->isSingleChoice()) {
            return $min >= 1 ? 'Wajib pilih 1' : 'Opsional, maks. 1';
        }
        $max = $this->max_select ?: 'bebas';

        return $min >= 1 ? "Wajib pilih {$min}–{$max}" : "Opsional, maks. {$max}";
    }
}
