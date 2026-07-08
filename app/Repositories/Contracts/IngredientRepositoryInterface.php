<?php

namespace App\Repositories\Contracts;

use App\Models\Ingredient;
use Illuminate\Support\Collection;

interface IngredientRepositoryInterface
{
    public function getAllIngredients(array $filters): Collection;
    public function getIngredientById(int $id): ?Ingredient;
    public function getIngredientBySku(string $sku): ?Ingredient;
    public function create(array $data): Ingredient;
    public function update(int $id, array $data): Ingredient;
    public function delete(int $id): bool;

    // Analytics Helper contract definitions
    public function getLowStockCount(): int;
    public function getTotalInventoryValue(): float;
}
