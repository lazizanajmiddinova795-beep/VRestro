<?php

namespace App\Repositories;

use App\Models\Category;
use App\Models\Food;
use App\Repositories\Contracts\MenuRepositoryInterface;
use Illuminate\Support\Collection;

class MenuRepository implements MenuRepositoryInterface
{
    // --- Category Actions ---

    /**
     * Get all categories with count of associated foods.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return Category::withCount('foods')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get category by ID.
     *
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Create a category.
     *
     * @param array $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category.
     *
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function updateCategory(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }

    // --- Food Actions ---

    /**
     * Get all foods with optional filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllFoods(array $filters): Collection
    {
        $query = Food::with('category');

        // Filter by Category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Search by name or description
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by availability
        if (isset($filters['is_available'])) {
            $query->where('is_available', (bool) $filters['is_available']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get food by ID.
     *
     * @param int $id
     * @return Food|null
     */
    public function getFoodById(int $id): ?Food
    {
        return Food::with('category')->find($id);
    }

    /**
     * Create a food item.
     *
     * @param array $data
     * @return Food
     */
    public function createFood(array $data): Food
    {
        return Food::create($data);
    }

    /**
     * Update a food item.
     *
     * @param int $id
     * @param array $data
     * @return Food
     */
    public function updateFood(int $id, array $data): Food
    {
        $food = Food::findOrFail($id);
        $food->update($data);
        return $food;
    }

    /**
     * Delete a food item.
     *
     * @param int $id
     * @return bool
     */
    public function deleteFood(int $id): bool
    {
        $food = Food::findOrFail($id);
        return $food->delete();
    }
}
