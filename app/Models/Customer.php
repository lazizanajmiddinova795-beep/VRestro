<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
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
