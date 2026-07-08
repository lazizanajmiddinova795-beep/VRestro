<?php

namespace App\Repositories\Contracts;

use App\Models\Table;
use Illuminate\Support\Collection;

interface TableRepositoryInterface
{
    public function getAllTables(array $filters): Collection;
    public function getTableById(int $id): ?Table;
    public function getTableByNumber(string $tableNumber): ?Table;
    public function getTableByQrToken(string $token): ?Table;
    public function create(array $data): Table;
    public function update(int $id, array $data): Table;
    public function delete(int $id): bool;
    
    /**
     * Check if a table has any active (unpaid/uncompleted) orders linked to it.
     * Active statuses: 'new', 'cooking', 'ready', 'delivered' (until paid/cancelled).
     *
     * @param int $tableId
     * @return bool
     */
    public function hasActiveOrders(int $tableId): bool;
}
