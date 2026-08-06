<?php

namespace App\Traits;

use App\Models\Branch;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    /**
     * Boot the BelongsToBranch trait.
     *
     * Registers the BranchScope global scope and automatically sets
     * branch_id on newly created records based on the authenticated user.
     */
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (!$model->branch_id && auth()->check()) {
                $user = auth()->user();
                if ($user && !$user->is_superadmin && $user->branch_id) {
                    $model->branch_id = $user->branch_id;
                }
            }
        });
    }

    /**
     * Get the branch that owns this record.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
