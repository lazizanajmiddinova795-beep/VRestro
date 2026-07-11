<?php

namespace App\Services;

use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Contracts\IngredientRepositoryInterface;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;

class WarehouseService
{
    protected WarehouseRepositoryInterface $warehouseRepository;
    protected IngredientRepositoryInterface $ingredientRepository;
    protected NotificationService $notificationService;

    public function __construct(
        WarehouseRepositoryInterface $warehouseRepository,
        IngredientRepositoryInterface $ingredientRepository,
        NotificationService $notificationService
    ) {
        $this->warehouseRepository = $warehouseRepository;
        $this->ingredientRepository = $ingredientRepository;
        $this->notificationService = $notificationService;
    }

    /**
     * Store new Kirim (Stock In) transaction and update ingredient balances.
     *
     * @param int $userId
     * @param array $data
     * @return InventoryTransaction
     */
    public function stockIn(int $userId, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $transaction = $this->warehouseRepository->createTransaction([
                'user_id' => $userId,
                'type' => 'kirim',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $ingredient = $this->ingredientRepository->getIngredientById($item['ingredient_id']);

                if (!$ingredient) {
                    throw ValidationException::withMessages([
                        'items' => ["Masalliq #{$item['ingredient_id']} topilmadi."],
                    ]);
                }

                $oldQty = (float) $ingredient->quantity;
                $oldCost = (float) $ingredient->cost_price;
                $incomingQty = (float) $item['quantity'];
                $incomingPrice = (float) $item['unit_price'];

                if ($incomingQty <= 0 || $incomingPrice < 0) {
                    throw new \InvalidArgumentException("Kirim qilinayotgan tovar miqdori yoki narxi salbiy bo'lishi mumkin emas!");
                }

                $newQty = $oldQty + $incomingQty;

                // Moving Average Purchase Price Calculation
                if ($newQty > 0) {
                    $newCost = (($oldQty * $oldCost) + ($incomingQty * $incomingPrice)) / $newQty;
                } else {
                    $newCost = $incomingPrice;
                }

                // Update Ingredient quantity and average price
                $ingredient->update([
                    'quantity' => $newQty,
                    'cost_price' => round($newCost, 2),
                ]);

                // Create detailed transaction line item
                $this->warehouseRepository->createTransactionItem([
                    'transaction_id' => $transaction->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $incomingQty,
                    'unit_price' => $incomingPrice,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $newQty,
                ]);
            }

            Cache::forget('admin_dashboard_analytics');
            return $transaction;
        });
    }

    /**
     * Store manual Chiqim (Stock Out/Spoilage) transaction and decrement ingredient balances.
     *
     * @param int $userId
     * @param array $data
     * @return InventoryTransaction
     */
    public function stockOut(int $userId, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $transaction = $this->warehouseRepository->createTransaction([
                'user_id' => $userId,
                'type' => 'chiqim',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $ingredient = $this->ingredientRepository->getIngredientById($item['ingredient_id']);

                if (!$ingredient) {
                    throw ValidationException::withMessages([
                        'items' => ["Masalliq #{$item['ingredient_id']} topilmadi."],
                    ]);
                }

                $oldQty = (float) $ingredient->quantity;
                $outgoingQty = (float) $item['quantity'];

                if ($oldQty < $outgoingQty) {
                    throw ValidationException::withMessages([
                        'quantity' => ["Bazada yetarli masalliq yo'q ({$ingredient->name}). Mavjud: {$oldQty} {$ingredient->unit}, so'ralgan: {$outgoingQty} {$ingredient->unit}"],
                    ]);
                }

                $newQty = $oldQty - $outgoingQty;

                // Update Ingredient quantity
                $ingredient->update(['quantity' => $newQty]);

                // Create detailed transaction line item
                $this->warehouseRepository->createTransactionItem([
                    'transaction_id' => $transaction->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $outgoingQty,
                    'unit_price' => null,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $newQty,
                ]);
                // Reconcile and check low stock threshold
                if ($newQty <= (float) $ingredient->low_stock_threshold) {
                    $this->notificationService->sendNotification(
                        'low_stock',
                        "Ombor qoldig'i kamaydi!",
                        "Masalliq: {$ingredient->name} qoldig'i belgilangan me'yordan kamaydi. Qoldiq: {$newQty} {$ingredient->unit} (Me'yor: {$ingredient->low_stock_threshold} {$ingredient->unit})",
                        ['ingredient_id' => $ingredient->id, 'current_stock' => $newQty]
                    );
                }
            }

            Cache::forget('admin_dashboard_analytics');
            return $transaction;
        });
    }

    /**
     * Audit Stock (Inventarizatsiya) and reconcile physical quantities with database.
     *
     * @param int $userId
     * @param array $data
     * @return InventoryTransaction
     */
    public function auditStock(int $userId, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($userId, $data) {
            $transaction = $this->warehouseRepository->createTransaction([
                'user_id' => $userId,
                'type' => 'inventarizatsiya',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $ingredient = $this->ingredientRepository->getIngredientById($item['ingredient_id']);

                if (!$ingredient) {
                    throw ValidationException::withMessages([
                        'items' => ["Masalliq #{$item['ingredient_id']} topilmadi."],
                    ]);
                }

                $oldQty = (float) $ingredient->quantity;
                $auditedQty = (float) $item['quantity'];
                $diff = $auditedQty - $oldQty; // Delta adjustment (positive or negative)

                // Reconcile quantity directly
                $ingredient->update(['quantity' => $auditedQty]);

                // Create detailed transaction line item
                $this->warehouseRepository->createTransactionItem([
                    'transaction_id' => $transaction->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $diff, // The difference delta
                    'unit_price' => null,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $auditedQty,
                ]);
                // Reconcile and check low stock threshold
                if ($auditedQty <= (float) $ingredient->low_stock_threshold) {
                    $this->notificationService->sendNotification(
                        'low_stock',
                        "Ombor qoldig'i kamaydi!",
                        "Masalliq: {$ingredient->name} qoldig'i belgilangan me'yordan kamaydi. Qoldiq: {$auditedQty} {$ingredient->unit} (Me'yor: {$ingredient->low_stock_threshold} {$ingredient->unit})",
                        ['ingredient_id' => $ingredient->id, 'current_stock' => $auditedQty]
                    );
                }
            }

            Cache::forget('admin_dashboard_analytics');
            return $transaction;
        });
    }
}
