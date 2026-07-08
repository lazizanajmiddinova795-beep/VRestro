<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'quantity',
        'unit',
        'cost_price',
        'low_stock_threshold',
    ];

    protected $casts = [
        'quantity' => 'float',
        'cost_price' => 'decimal:2',
        'low_stock_threshold' => 'float',
    ];

    protected $appends = [
        'is_low_stock',
        'total_value',
    ];

    /**
     * Virtual attribute: Check if ingredient stock level is below threshold.
     *
     * @return bool
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    /**
     * Virtual attribute: Total monetary inventory value of this ingredient.
     *
     * @return float
     */
    public function getTotalValueAttribute(): float
    {
        return (float) ($this->quantity * $this->cost_price);
    }

    /**
     * Scope to query ingredients with stock levels below or equal to the low threshold.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    /**
     * Get the recipe entries (foods) referencing this raw ingredient.
     *
     * @return HasMany
     */
    public function recipes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Recipe::class, 'ingredient_id');
    }

    /**
     * Get the inventory transactions containing this ingredient.
     *
     * @return HasMany
     */
    public function inventoryTransactionItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryTransactionItem::class, 'ingredient_id');
    }
}
