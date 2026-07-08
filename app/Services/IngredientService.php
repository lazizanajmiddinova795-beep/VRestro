<?php

namespace App\Services;

use App\Repositories\Contracts\IngredientRepositoryInterface;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class IngredientService
{
    protected IngredientRepositoryInterface $ingredientRepository;

    public function __construct(IngredientRepositoryInterface $ingredientRepository)
    {
        $this->ingredientRepository = $ingredientRepository;
    }

    /**
     * Create a new ingredient.
     *
     * @param array $data
     * @return Ingredient
     */
    public function createIngredient(array $data): Ingredient
    {
        DB::beginTransaction();

        try {
            // Generate SKU if empty
            if (empty($data['sku'])) {
                $data['sku'] = $this->generateUniqueSku();
            }

            $ingredient = $this->ingredientRepository->create($data);

            // Reset cache if any
            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $ingredient;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an ingredient.
     *
     * @param int $id
     * @param array $data
     * @return Ingredient
     */
    public function updateIngredient(int $id, array $data): Ingredient
    {
        DB::beginTransaction();

        try {
            $ingredient = $this->ingredientRepository->update($id, $data);

            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $ingredient;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atomically adjust ingredient stock levels.
     *
     * @param int $id
     * @param float $amount
     * @param string $type ('add' | 'subtract')
     * @return Ingredient
     * @throws ValidationException
     */
    public function adjustStock(int $id, float $amount, string $type): Ingredient
    {
        DB::beginTransaction();

        try {
            $ingredient = $this->ingredientRepository->getIngredientById($id);

            if (!$ingredient) {
                throw ValidationException::withMessages([
                    'ingredient' => ['Masalliq topilmadi.'],
                ]);
            }

            $currentStock = $ingredient->quantity;

            if ($type === 'add') {
                $newStock = $currentStock + $amount;
            } elseif ($type === 'subtract') {
                if ($currentStock < $amount) {
                    throw ValidationException::withMessages([
                        'quantity' => ["Bazada yetarli masalliq yo'q. Mavjud: {$currentStock} {$ingredient->unit}"],
                    ]);
                }
                $newStock = $currentStock - $amount;
            } else {
                throw ValidationException::withMessages([
                    'type' => ['Noma\'lum o\'zgarish turi.'],
                ]);
            }

            $ingredient->update(['quantity' => $newStock]);

            // Logger hook for future warehouse ledger records
            Log::info("IngredientStockAdjusted: ID {$ingredient->id} ({$ingredient->name}) quantity altered by {$type}ing {$amount}. New balance: {$newStock} {$ingredient->unit}");

            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $ingredient;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an ingredient.
     *
     * @param int $id
     * @return bool
     */
    public function deleteIngredient(int $id): bool
    {
        DB::beginTransaction();

        try {
            $result = $this->ingredientRepository->delete($id);

            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate a unique SKU code.
     *
     * @return string
     */
    protected function generateUniqueSku(): string
    {
        do {
            $sku = 'ING-' . Str::upper(Str::random(5));
        } while ($this->ingredientRepository->getIngredientBySku($sku) !== null);

        return $sku;
    }
}
