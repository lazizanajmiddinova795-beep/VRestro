<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'customer_id',
        'total_amount',
        'payment_method',
        'cash_amount',
        'card_amount',
        'qr_amount',
        'bonus_used',
        'status',
        'is_printed',
        'printed_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'card_amount' => 'decimal:2',
        'qr_amount' => 'decimal:2',
        'bonus_used' => 'decimal:2',
        'order_id' => 'integer',
        'customer_id' => 'integer',
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
    ];

    /**
     * Get the order associated with the payment.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Get the customer associated with the payment.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
