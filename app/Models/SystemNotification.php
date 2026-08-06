<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $table = 'system_notifications';

    use BelongsToBranch;

    protected $fillable = [
        'branch_id',
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
