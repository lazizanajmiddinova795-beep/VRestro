<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'food_id',
        'quantity',
        'price',
        'notes',
        'size_name',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'order_id' => 'integer',
        'food_id' => 'integer',
    ];

    /**
     * Get the order that owns the item.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the food associated with the item.
     *
     * @return BelongsTo
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }
}
