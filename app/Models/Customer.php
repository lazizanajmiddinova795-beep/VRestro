<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'bonus_balance',
        'total_orders_count',
        'total_spent_amount',
    ];

    protected $casts = [
        'bonus_balance' => 'decimal:2',
        'total_orders_count' => 'integer',
        'total_spent_amount' => 'decimal:2',
    ];
}
