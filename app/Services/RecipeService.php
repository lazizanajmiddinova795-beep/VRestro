<?php

namespace App\Services;

use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Models\Order;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Collection;

class RecipeService
{
    protected RecipeRepositoryInterface $recipeRepository;

    public function __construct(RecipeRepositoryInterface $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * Get recipe for a food.
     *
     * @param int $foodId
     * @return Collection
     */
    public function getRecipeForFood(int $foodId): Collection
    {
        return $this->recipeRepository->getRecipeForFood($foodId);
    }

    /**
     * Save/Sync recipe composition for a dish.
     *
     * @param int $foodId
     * @param array $ingredients
     * @return void
     */
    public function saveRecipe(int $foodId, array $ingredients): void
    {
        DB::transaction(function () use ($foodId, $ingredients) {
            $this->recipeRepository->syncRecipe($foodId, $ingredients);
            
            // Clear cache
            Cache::forget('admin_dashboard_analytics');
        });
    }

    /**
     * Get Portions Capacity.
     *
     * @param int $foodId
     * @return int|null
     */
    public function calculatePortionCapacity(int $foodId): ?int
    {
        return $this->recipeRepository->calculatePortionCapacity($foodId);
    }

    /**
     * Deduct stock based on order item recipe compositions.
     * Must be run within a DB Transaction context.
     *
     * @param int $orderId
     * @return void
     * @throws ValidationException
     */
    public function deductStockForOrder(int $orderId): void
    {
        $order = Order::with(['items.food.recipes.ingredient'])->find($orderId);

        if (!$order) {
            return;
        }

        foreach ($order->items as $item) {
            $food = $item->food;
            if (!$food) {
                continue;
            }

            // Loop through ingredients in recipe
            foreach ($food->recipes as $recipe) {
                $ingredient = $recipe->ingredient;
                if (!$ingredient) {
                    continue;
                }

                $quantityNeeded = (float) ($recipe->quantity_required * $item->quantity);
                $availableStock = (float) $ingredient->quantity;

                if ($availableStock < $quantityNeeded) {
                    throw ValidationException::withMessages([
                        'stock' => ["Buyurtmani tayyorlashga masalliqlar etishmaydi. {$ingredient->name} yetarli emas. Talab qilinadi: {$quantityNeeded} {$ingredient->unit}, bazada bor: {$availableStock} {$ingredient->unit}"],
                    ]);
                }

                // Deduct stock
                $newStock = $availableStock - $quantityNeeded;
                $ingredient->update(['quantity' => $newStock]);

                // Check low stock threshold trigger
                if ($newStock <= (float) $ingredient->low_stock_threshold) {
                    Log::warning("LowStockAlert: Ingredient #{$ingredient->id} ({$ingredient->name}) stock fell below threshold. Balance: {$newStock} {$ingredient->unit} (Threshold: {$ingredient->low_stock_threshold} {$ingredient->unit})");
                }
            }
        }

        // Clear dashboard cache
        Cache::forget('admin_dashboard_analytics');
    }
}
