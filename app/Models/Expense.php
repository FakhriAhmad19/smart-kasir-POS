<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'app_expenses';

    protected $fillable = [
        'app_user_id',
        'category',
        'amount',
        'notes',
        'expense_date',
    ];

    protected $casts = [
        'expense_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'app_user_id');
    }
}
