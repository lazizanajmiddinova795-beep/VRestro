<?php

namespace App\Repositories;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Get paginated inventory transactions based on filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getTransactions(array $filters): LengthAwarePaginator
    {
        $query = InventoryTransaction::with(['user', 'items.ingredient']);

        // Filter by transaction type
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Filter by date ranges
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Filter by ingredient_id
        if (!empty($filters['ingredient_id'])) {
            $ingId = $filters['ingredient_id'];
            $query->whereHas('items', function ($q) use ($ingId) {
                $q->where('ingredient_id', $ingId);
            });
        }

        // Orders: newest first
        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get historical audit timeline stream of a single ingredient.
     *
     * @param int $ingredientId
     * @return Collection
     */
    public function getIngredientAuditTimeline(int $ingredientId): Collection
    {
        return InventoryTransactionItem::with(['transaction.user'])
            ->where('ingredient_id', $ingredientId)
            ->latest()
            ->get();
    }

    /**
     * Create master transaction document log.
     *
     * @param array $data
     * @return InventoryTransaction
     */
    public function createTransaction(array $data): InventoryTransaction
    {
        return InventoryTransaction::create($data);
    }

    /**
     * Create detailed line item log.
     *
     * @param array $data
     * @return InventoryTransactionItem
     */
    public function createTransactionItem(array $data): InventoryTransactionItem
    {
        return InventoryTransactionItem::create($data);
    }
}
