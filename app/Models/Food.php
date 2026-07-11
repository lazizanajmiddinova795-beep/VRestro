<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'image_path',
        'category_id',
        'is_available',
        'sizes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'category_id' => 'integer',
        'sizes' => 'array',
    ];

    protected $appends = [
        'image_url'
    ];

    /**
     * Get the virtual image_url property.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset(Storage::url($this->image_path));
        }
        return null;
    }

    /**
     * Generate slug from name on save.
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    /**
     * Get the category that owns the food.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the order items containing this food.
     *
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'food_id');
    }

    /**
     * Get the recipe entries (ingredients) configured for this food.
     *
     * @return HasMany
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'food_id');
    }
}
