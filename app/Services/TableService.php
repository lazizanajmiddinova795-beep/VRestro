<?php

namespace App\Services;

use App\Repositories\Contracts\TableRepositoryInterface;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TableService
{
    protected TableRepositoryInterface $tableRepository;

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    /**
     * Create a new table.
     *
     * @param array $data
     * @return Table
     */
    public function createTable(array $data): Table
    {
        return DB::transaction(function () use ($data) {
            // Generate QR token if empty
            if (empty($data['qr_code_token'])) {
                $data['qr_code_token'] = $this->generateUniqueQrToken();
            }

            $table = $this->tableRepository->create($data);

            Cache::forget('admin_dashboard_analytics');
            return $table;
        });
    }

    /**
     * Update table details.
     *
     * @param int $id
     * @param array $data
     * @return Table
     */
    public function updateTable(int $id, array $data): Table
    {
        return DB::transaction(function () use ($id, $data) {
            $table = $this->tableRepository->update($id, $data);

            Cache::forget('admin_dashboard_analytics');
            return $table;
        });
    }

    /**
     * Modify table status with validation.
     *
     * @param int $id
     * @param string $status ('empty', 'occupied', 'reserved')
     * @return Table
     * @throws ValidationException
     */
    public function changeStatus(int $id, string $status): Table
    {
        return DB::transaction(function () use ($id, $status) {
            $table = $this->tableRepository->getTableById($id);

            if (!$table) {
                throw ValidationException::withMessages([
                    'table' => ['Stol topilmadi.'],
                ]);
            }

            // If attempting to mark table empty, verify it doesn't have active orders
            if ($status === 'empty' && $this->tableRepository->hasActiveOrders($id)) {
                throw ValidationException::withMessages([
                    'status' => ['Stolda faol (yakunlanmagan) buyurtma bor. Avval uni yoping yoki bekor qiling.'],
                ]);
            }

            $table->update(['status' => $status]);

            Cache::forget('admin_dashboard_analytics');
            return $table;
        });
    }

    /**
     * Delete a table safely.
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteTable(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $table = $this->tableRepository->getTableById($id);

            if (!$table) {
                return false;
            }

            if ($table->status === 'occupied') {
                throw ValidationException::withMessages([
                    'status' => ['Band holatdagi stolni o\'chirib bo\'lmaydi.'],
                ]);
            }

            if ($this->tableRepository->hasActiveOrders($id)) {
                throw ValidationException::withMessages([
                    'orders' => ['Faol buyurtmalari mavjud bo\'lgan stolni o\'chirib bo\'lmaydi.'],
                ]);
            }

            $this->tableRepository->delete($id);

            Cache::forget('admin_dashboard_analytics');
            return true;
        });
    }

    /**
     * Generate unique token for tables.
     *
     * @return string
     */
    protected function generateUniqueQrToken(): string
    {
        do {
            $token = 'qr_table_' . Str::lower(Str::random(12));
        } while ($this->tableRepository->getTableByQrToken($token) !== null);

        return $token;
    }
}
