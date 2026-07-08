<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    /**
     * Get orders based on dynamic filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllOrders(array $filters): Collection;

    /**
     * Get a specific order by ID with relations.
     *
     * @param int $id
     * @return Order|null
     */
    public function getOrderById(int $id): ?Order;

    /**
     * Create a new order with basic parameters.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order;

    /**
     * Generate the next sequential order number.
     *
     * @return string
     */
    public function generateOrderNumber(): string;
}
