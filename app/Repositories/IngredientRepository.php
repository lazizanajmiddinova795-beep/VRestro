<?php

namespace App\Repositories;

use App\Models\Ingredient;
use App\Repositories\Contracts\IngredientRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IngredientRepository implements IngredientRepositoryInterface
{
    /**
     * Get all ingredients based on filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllIngredients(array $filters): Collection
    {
        $query = Ingredient::query();

        // Search by name or SKU
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter only low stock items
        if (!empty($filters['low_stock']) && $filters['low_stock'] === 'true') {
            $query->lowStock();
        }

        // Ordering: name by default
        return $query->orderBy('name')->get();
    }

    /**
     * Get ingredient by ID.
     *
     * @param int $id
     * @return Ingredient|null
     */
    public function getIngredientById(int $id): ?Ingredient
    {
        return Ingredient::find($id);
    }

    /**
     * Get ingredient by SKU.
     *
     * @param string $sku
     * @return Ingredient|null
     */
    public function getIngredientBySku(string $sku): ?Ingredient
    {
        return Ingredient::where('sku', $sku)->first();
    }

    /**
     * Create a new ingredient.
     *
     * @param array $data
     * @return Ingredient
     */
    public function create(array $data): Ingredient
    {
        return Ingredient::create($data);
    }

    /**
     * Update an ingredient.
     *
     * @param int $id
     * @param array $data
     * @return Ingredient
     */
    public function update(int $id, array $data): Ingredient
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($data);
        return $ingredient;
    }

    /**
     * Delete an ingredient.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $ingredient = Ingredient::findOrFail($id);
        return $ingredient->delete();
    }

    /**
     * Get low stock counts.
     *
     * @return int
     */
    public function getLowStockCount(): int
    {
        return (int) Ingredient::lowStock()->count();
    }

    /**
     * Sum quantity * cost_price.
     *
     * @return float
     */
    public function getTotalInventoryValue(): float
    {
        return (float) Ingredient::sum(DB::raw('quantity * cost_price'));
    }
}
