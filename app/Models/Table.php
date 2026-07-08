<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $fillable = [
        'table_number',
        'capacity',
        'status',
        'qr_code_token',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get the orders associated with the table.
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}
