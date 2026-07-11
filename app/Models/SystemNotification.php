<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table = 'system_notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'is_read',
        'meta_data',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'meta_data' => 'array',
    ];
}
