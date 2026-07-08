<?php

namespace App\Repositories;

use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Support\Collection;

class TableRepository implements TableRepositoryInterface
{
    /**
     * Get all tables with filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllTables(array $filters): Collection
    {
        $query = Table::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Order by table number naturally (e.g. Stol 1, Stol 2, VIP 1)
        // We can sort using LENGTH and value, but simple sorting by table_number is standard
        return $query->orderBy('table_number')->get();
    }

    /**
     * Get table by ID.
     *
     * @param int $id
     * @return Table|null
     */
    public function getTableById(int $id): ?Table
    {
        return Table::find($id);
    }

    /**
     * Get table by Table Number.
     *
     * @param string $tableNumber
     * @return Table|null
     */
    public function getTableByNumber(string $tableNumber): ?Table
    {
        return Table::where('table_number', $tableNumber)->first();
    }

    /**
     * Get table by QR Code Token.
     *
     * @param string $token
     * @return Table|null
     */
    public function getTableByQrToken(string $token): ?Table
    {
        return Table::where('qr_code_token', $token)->first();
    }

    /**
     * Create table.
     *
     * @param array $data
     * @return Table
     */
    public function create(array $data): Table
    {
        return Table::create($data);
    }

    /**
     * Update table.
     *
     * @param int $id
     * @param array $data
     * @return Table
     */
    public function update(int $id, array $data): Table
    {
        $table = Table::findOrFail($id);
        $table->update($data);
        return $table;
    }

    /**
     * Delete table.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $table = Table::findOrFail($id);
        return $table->delete();
    }

    /**
     * Check if a table has any active (unpaid/uncompleted) orders linked to it.
     * Active statuses: 'new', 'cooking', 'ready', 'delivered'.
     *
     * @param int $tableId
     * @return bool
     */
    public function hasActiveOrders(int $tableId): bool
    {
        $table = Table::findOrFail($tableId);
        
        return $table->orders()
            ->whereIn('status', ['new', 'cooking', 'ready', 'delivered'])
            ->exists();
    }
}
