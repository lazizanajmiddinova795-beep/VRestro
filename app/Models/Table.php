<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
        'table_number',
        'floor',
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
