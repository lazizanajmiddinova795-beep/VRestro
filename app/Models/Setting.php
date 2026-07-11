<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'type'
    ];

    /**
     * Get value with appropriate cast type.
     */
    public function getCastValueAttribute()
    {
        if (is_null($this->value)) {
            return null;
        }

        switch ($this->type) {
            case 'number':
                return (float) $this->value;
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'file':
                return asset(Storage::url($this->value));
            default:
                return $this->value;
        }
    }
}
