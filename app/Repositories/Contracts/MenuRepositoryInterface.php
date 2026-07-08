<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Food;
use Illuminate\Support\Collection;

interface MenuRepositoryInterface
{
    // Category Operations
    public function getAllCategories(): Collection;
    public function getCategoryById(int $id): ?Category;
    public function createCategory(array $data): Category;
    public function updateCategory(int $id, array $data): Category;
    public function deleteCategory(int $id): bool;

    // Food Operations
    public function getAllFoods(array $filters): Collection;
    public function getFoodById(int $id): ?Food;
    public function createFood(array $data): Food;
    public function updateFood(int $id, array $data): Food;
    public function deleteFood(int $id): bool;
}
