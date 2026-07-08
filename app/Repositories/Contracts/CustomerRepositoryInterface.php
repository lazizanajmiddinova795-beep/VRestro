<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function getAllCustomers(array $filters): LengthAwarePaginator;
    public function getCustomerById(int $id): ?Customer;
    public function getCustomerByPhone(string $phone): ?Customer;
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
    public function delete(int $id): bool;
    
    /**
     * Get aggregate statistics for CRM dashboard.
     * Contains: total customers count, sum of all bonus_balances, top spent customer.
     *
     * @return array
     */
    public function getCRMAnalytics(): array;
}
