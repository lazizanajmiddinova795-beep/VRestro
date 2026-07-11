<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Food;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ChefStopListService
{
    /**
     * Get all registered foods grouped by Category.
     *
     * @return Collection
     */
    public function getKitchenMenuWithStatus(): Collection
    {
        return Category::with(['foods' => function ($query) {
            $query->orderBy('name', 'asc');
        }])->orderBy('name', 'asc')->get();
    }

    /**
     * Toggle food availability status securely inside a DB Transaction
     * and clear relevant cached datasets.
     *
     * @param int $foodId
     * @param bool $status
     * @return Food
     */
    public function toggleFoodAvailability(int $foodId, bool $status): Food
    {
        return DB::transaction(function () use ($foodId, $status) {
            $food = Food::findOrFail($foodId);
            $food->is_available = $status;
            $food->save();

            // Clear cache hooks to push immediate updates to Waiters and Cashiers
            Cache::forget('menu_categories');
            Cache::forget('admin_dashboard_analytics');

            return $food;
        });
    }
}
