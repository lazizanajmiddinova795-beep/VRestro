<?php

namespace App\Services;

use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerService
{
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Onboard a new customer.
     *
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $data['bonus_balance'] = 0.00;
            $data['total_orders_count'] = 0;
            $data['total_spent_amount'] = 0.00;

            return $this->customerRepository->create($data);
        });
    }

    /**
     * Update customer basic details.
     *
     * @param int $id
     * @param array $data
     * @return Customer
     */
    public function updateCustomer(int $id, array $data): Customer
    {
        return DB::transaction(function () use ($id, $data) {
            // Keep numerical values intact
            unset($data['bonus_balance']);
            unset($data['total_orders_count']);
            unset($data['total_spent_amount']);

            return $this->customerRepository->update($id, $data);
        });
    }

    /**
     * Add bonus points to customer balance.
     *
     * @param int $id
     * @param float $amount
     * @return Customer
     * @throws ValidationException
     */
    public function addBonus(int $id, float $amount): Customer
    {
        return DB::transaction(function () use ($id, $amount) {
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Qo\'shiladigan bonus miqdori musbat bo\'lishi kerak.'],
                ]);
            }

            $customer = $this->customerRepository->getCustomerById($id);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer' => ['Mijoz topilmadi.'],
                ]);
            }

            $customer->bonus_balance += $amount;
            $customer->save();

            return $customer;
        });
    }

    /**
     * Deduct bonus points from customer balance.
     *
     * @param int $id
     * @param float $amount
     * @return Customer
     * @throws ValidationException
     */
    public function deductBonus(int $id, float $amount): Customer
    {
        return DB::transaction(function () use ($id, $amount) {
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => ['Yechib olinadigan bonus miqdori musbat bo\'lishi kerak.'],
                ]);
            }

            $customer = $this->customerRepository->getCustomerById($id);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer' => ['Mijoz topilmadi.'],
                ]);
            }

            if ($customer->bonus_balance < $amount) {
                throw ValidationException::withMessages([
                    'balance' => ['Hisobda yetarli mablag\' yo\'q. Joriy balans: ' . $customer->bonus_balance . ' UZS'],
                ]);
            }

            $customer->bonus_balance -= $amount;
            $customer->save();

            return $customer;
        });
    }

    /**
     * Adjust customer loyalty points manually (Admin only).
     *
     * @param int $id
     * @param float $newBalance
     * @return Customer
     * @throws ValidationException
     */
    public function adjustBalance(int $id, float $newBalance): Customer
    {
        return DB::transaction(function () use ($id, $newBalance) {
            if ($newBalance < 0) {
                throw ValidationException::withMessages([
                    'bonus_balance' => ['Bonus balansi noldan kam bo\'la olmaydi.'],
                ]);
            }

            $customer = $this->customerRepository->getCustomerById($id);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer' => ['Mijoz topilmadi.'],
                ]);
            }

            $customer->bonus_balance = $newBalance;
            $customer->save();

            return $customer;
        });
    }
}
