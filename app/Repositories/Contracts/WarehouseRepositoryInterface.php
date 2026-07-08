<?php

namespace App\Repositories\Contracts;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WarehouseRepositoryInterface
{
    /**
     * Get paginated inventory transactions based on filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getTransactions(array $filters): LengthAwarePaginator;

    /**
     * Get historical audit timeline stream of a single ingredient.
     *
     * @param int $ingredientId
     * @return Collection
     */
    public function getIngredientAuditTimeline(int $ingredientId): Collection;

    /**
     * Create master transaction document log.
     *
     * @param array $data
     * @return InventoryTransaction
     */
    public function createTransaction(array $data): InventoryTransaction;

    /**
     * Create detailed line item log.
     *
     * @param array $data
     * @return InventoryTransactionItem
     */
    public function createTransactionItem(array $data): InventoryTransactionItem;
}
