<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'ingredient_id',
        'quantity',
        'unit_price',
        'old_quantity',
        'new_quantity',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'ingredient_id' => 'integer',
        'quantity' => 'float',
        'unit_price' => 'decimal:2',
        'old_quantity' => 'float',
        'new_quantity' => 'float',
    ];

    /**
     * Get the master transaction header.
     *
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class, 'transaction_id');
    }

    /**
     * Get the ingredient associated with this transaction line.
     *
     * @return BelongsTo
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
