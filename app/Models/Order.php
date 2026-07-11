<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'table_id',
        'waiter_id',
        'total_amount',
        'status',
        'discount_id',
        'discount_amount',
        'is_printed',
        'printed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'table_id' => 'integer',
        'waiter_id' => 'integer',
        'discount_id' => 'integer',
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
    ];

    /**
     * Get the table associated with the order.
     *
     * @return BelongsTo
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * Get the waiter who created the order.
     *
     * @return BelongsTo
     */
    public function waiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waiter_id');
    }

    /**
     * Get the items for the order.
     *
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Get the discount associated with the order.
     *
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }
}
