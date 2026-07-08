<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Support\Collection;

interface PaymentRepositoryInterface
{
    /**
     * Get all payments based on filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllPayments(array $filters): Collection;

    /**
     * Get a payment by ID.
     *
     * @param int $id
     * @return Payment|null
     */
    public function getPaymentById(int $id): ?Payment;

    /**
     * Create a new payment record.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment;

    /**
     * Get today's total revenue breakdown.
     *
     * @return array
     */
    public function getTodayRevenueBreakdown(): array;
}
