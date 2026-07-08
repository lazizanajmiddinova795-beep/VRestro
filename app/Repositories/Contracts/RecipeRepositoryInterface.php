<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface RecipeRepositoryInterface
{
    /**
     * Get full recipe formula for a specific food.
     *
     * @param int $foodId
     * @return Collection
     */
    public function getRecipeForFood(int $foodId): Collection;

    /**
     * Sync recipes details for a dish.
     *
     * @param int $foodId
     * @param array $ingredients
     * @return void
     */
    public function syncRecipe(int $foodId, array $ingredients): void;

    /**
     * Calculate maximum cookable portions of a food.
     *
     * @param int $foodId
     * @return int|null (null if no ingredients defined)
     */
    public function calculatePortionCapacity(int $foodId): ?int;
}
