<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Support\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * Get all payments based on filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllPayments(array $filters): Collection
    {
        $query = Payment::with(['order.table', 'order.waiter', 'order.items.food', 'customer']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Get a payment by ID.
     *
     * @param int $id
     * @return Payment|null
     */
    public function getPaymentById(int $id): ?Payment
    {
        return Payment::with(['order.table', 'order.waiter', 'order.items.food', 'customer'])->find($id);
    }

    /**
     * Create a new payment record.
     *
     * @param array $data
     * @return Payment
     */
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    /**
     * Get today's total revenue breakdown.
     *
     * @return array
     */
    public function getTodayRevenueBreakdown(): array
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        $payments = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$today, $tomorrow])
            ->get();

        $total = $payments->sum('total_amount');
        $cash = $payments->sum('cash_amount');
        $card = $payments->sum('card_amount');
        $qr = $payments->sum('qr_amount');
        $bonus = $payments->sum('bonus_used');

        return [
            'total_revenue' => $total,
            'cash_total' => $cash,
            'card_total' => $card,
            'qr_total' => $qr,
            'bonus_total' => $bonus,
        ];
    }
}
