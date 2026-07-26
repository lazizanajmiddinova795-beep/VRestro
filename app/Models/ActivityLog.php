<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action_type',
        'module',
        'description',
        'meta',
        'ip_address',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity log entry.
     */
    public static function record(
        string $actionType,
        string $description,
        string $module = null,
        array  $meta = [],
        ?User  $user = null
    ): self {
        $actor = $user ?? auth()->user();

        return self::create([
            'user_id'     => $actor?->id,
            'user_name'   => $actor?->name,
            'user_role'   => $actor?->roles()->first()?->name ?? $actor?->role ?? null,
            'action_type' => $actionType,
            'module'      => $module,
            'description' => $description,
            'meta'        => $meta ?: null,
            'ip_address'  => request()->ip(),
        ]);
    }
}
