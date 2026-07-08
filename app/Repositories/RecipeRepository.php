<?php

namespace App\Repositories;

use App\Models\Recipe;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecipeRepository implements RecipeRepositoryInterface
{
    /**
     * Get full recipe formula for a specific food.
     *
     * @param int $foodId
     * @return Collection
     */
    public function getRecipeForFood(int $foodId): Collection
    {
        return Recipe::with('ingredient')
            ->where('food_id', $foodId)
            ->get();
    }

    /**
     * Sync recipes details for a dish.
     *
     * @param int $foodId
     * @param array $ingredients
     * @return void
     */
    public function syncRecipe(int $foodId, array $ingredients): void
    {
        DB::transaction(function () use ($foodId, $ingredients) {
            // Delete existing ingredients configuration
            Recipe::where('food_id', $foodId)->delete();

            // Insert new ones
            foreach ($ingredients as $ing) {
                Recipe::create([
                    'food_id' => $foodId,
                    'ingredient_id' => $ing['ingredient_id'],
                    'quantity_required' => $ing['quantity_required'],
                ]);
            }
        });
    }

    /**
     * Calculate maximum cookable portions of a food.
     *
     * @param int $foodId
     * @return int|null
     */
    public function calculatePortionCapacity(int $foodId): ?int
    {
        $recipes = Recipe::with('ingredient')->where('food_id', $foodId)->get();

        if ($recipes->isEmpty()) {
            return null; // Unlimited or not defined
        }

        $minPortions = null;

        foreach ($recipes as $recipe) {
            $req = (float) $recipe->quantity_required;
            $stock = (float) ($recipe->ingredient->quantity ?? 0);

            if ($req <= 0) {
                continue;
            }

            $portions = floor($stock / $req);

            if ($minPortions === null || $portions < $minPortions) {
                $minPortions = (int) $portions;
            }
        }

        return $minPortions;
    }
}
