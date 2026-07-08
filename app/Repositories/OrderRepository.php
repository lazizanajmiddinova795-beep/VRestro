<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get orders based on dynamic filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllOrders(array $filters): Collection
    {
        $query = Order::with(['table', 'waiter', 'items.food']);

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Default: newest first
        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get a specific order by ID with relations.
     *
     * @param int $id
     * @return Order|null
     */
    public function getOrderById(int $id): ?Order
    {
        return Order::with(['table', 'waiter', 'items.food'])->find($id);
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Generate the next sequential order number.
     *
     * @return string
     */
    public function generateOrderNumber(): string
    {
        $todayStr = now()->format('Ymd');
        
        $latestOrder = Order::where('order_number', 'like', "ORD-{$todayStr}-%")
            ->orderByDesc('order_number')
            ->first();

        if ($latestOrder) {
            // Extract the last 3 digits sequence number
            $parts = explode('-', $latestOrder->order_number);
            $seq = (int) end($parts);
            $nextSeq = str_pad($seq + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '001';
        }

        return "ORD-{$todayStr}-{$nextSeq}";
    }
}
