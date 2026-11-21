<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A physical table in the cafe. Named CafeTable because "Table" clashes
 * with too many framework concepts.
 */
class CafeTable extends Model
{
    protected $table = 'app_tables';

    protected $fillable = [
        'name',
        'area',
        'capacity',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'app_table_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('area')->orderBy('name');
    }
}
