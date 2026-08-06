<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'amount',
        'description',
        'category',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
