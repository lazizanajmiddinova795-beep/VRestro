<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Get paginated customers with filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllCustomers(array $filters): LengthAwarePaginator
    {
        $query = Customer::query();

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('phone', 'like', $search);
            });
        }

        $perPage = !empty($filters['per_page']) ? (int)$filters['per_page'] : 15;

        // Default sort by spent amount descending or name ascending
        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get customer by ID.
     *
     * @param int $id
     * @return Customer|null
     */
    public function getCustomerById(int $id): ?Customer
    {
        return Customer::find($id);
    }

    /**
     * Find customer by Phone.
     *
     * @param string $phone
     * @return Customer|null
     */
    public function getCustomerByPhone(string $phone): ?Customer
    {
        return Customer::where('phone', $phone)->first();
    }

    /**
     * Create customer.
     *
     * @param array $data
     * @return Customer
     */
    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Update customer.
     *
     * @param int $id
     * @param array $data
     * @return Customer
     */
    public function update(int $id, array $data): Customer
    {
        $customer = Customer::findOrFail($id);
        $customer->update($data);
        return $customer;
    }

    /**
     * Delete customer.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $customer = Customer::findOrFail($id);
        return $customer->delete();
    }

    /**
     * Get aggregate statistics for CRM dashboard.
     *
     * @return array
     */
    public function getCRMAnalytics(): array
    {
        $totalCustomers = Customer::count();
        $totalBonusBalance = Customer::sum('bonus_balance');
        $topCustomer = Customer::orderByDesc('total_spent_amount')->first();

        return [
            'total_customers' => $totalCustomers,
            'total_bonus_balance' => (float)$totalBonusBalance,
            'top_customer' => $topCustomer ? [
                'id' => $topCustomer->id,
                'name' => $topCustomer->name,
                'total_spent' => (float)$topCustomer->total_spent_amount
            ] : null
        ];
    }
}
