<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    /**
     * Apply the branch scope to a given Eloquent query builder.
     *
     * When a non-superadmin user is authenticated, this scope ensures
     * they only see records belonging to their branch OR global records
     * (where branch_id is NULL).
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // SuperAdmin sees everything, unauthenticated requests are not filtered
        if (!$user || $user->is_superadmin) {
            return;
        }

        if ($user->branch_id) {
            $builder->where(function ($q) use ($user, $model) {
                $q->where($model->getTable() . '.branch_id', $user->branch_id)
                  ->orWhereNull($model->getTable() . '.branch_id');
            });
        }
    }
}
