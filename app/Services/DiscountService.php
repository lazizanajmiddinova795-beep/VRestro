<?php

namespace App\Services;

use App\Repositories\Contracts\DiscountRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Discount;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    protected DiscountRepositoryInterface $discountRepository;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(
        DiscountRepositoryInterface $discountRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->discountRepository = $discountRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create a new discount campaign.
     *
     * @param array $data
     * @return Discount
     */
    public function createDiscount(array $data): Discount
    {
        $discount = $this->discountRepository->create($data);
        Cache::forget('admin_dashboard_analytics');
        return $discount;
    }

    /**
     * Apply promocode to an open order.
     *
     * @param int $orderId
     * @param string $code
     * @return Order
     * @throws ValidationException
     */
    public function applyPromocodeToOrder(int $orderId, string $code): Order
    {
        return DB::transaction(function () use ($orderId, $code) {
            // Find order and lock for update
            $order = Order::lockForUpdate()->find($orderId);
            if (!$order) {
                throw ValidationException::withMessages([
                    'order' => ['Buyurtma topilmadi.'],
                ]);
            }

            // Verify order status
            if ($order->status === 'delivered') {
                throw ValidationException::withMessages([
                    'code' => ['Yetkazib berilgan (to\'langan) buyurtmaga chegirma qo\'llab bo\'lmaydi.'],
                ]);
            }

            if ($order->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'code' => ['Bekor qilingan buyurtmaga chegirma qo\'llab bo\'lmaydi.'],
                ]);
            }

            // Find discount by code
            $discount = $this->discountRepository->findByCode($code);
            if (!$discount) {
                throw ValidationException::withMessages([
                    'code' => ['Noto\'g\'ri promo-kod.'],
                ]);
            }

            // Check if active
            if (!$discount->is_active) {
                throw ValidationException::withMessages([
                    'code' => ['Ushbu promo-kod hozirda faol emas.'],
                ]);
            }

            // Check expiry / validity dates
            $now = now();
            if ($discount->starts_at && $discount->starts_at->isFuture()) {
                throw ValidationException::withMessages([
                    'code' => ['Ushbu promo-kodning amal qilish muddati hali boshlanmagan.'],
                ]);
            }

            if ($discount->expires_at && $discount->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'code' => ['Promo-kodning amal qilish muddati tugagan.'],
                ]);
            }

            // Calculate current items subtotal
            $subtotal = $order->items->sum(function ($item) {
                return (float)$item->quantity * (float)$item->price;
            });

            // Check minimum order amount requirement
            if ($subtotal < (float)$discount->min_order_amount) {
                throw ValidationException::withMessages([
                    'code' => ["Ushbu chegirma uchun minimal buyurtma summasi " . number_format($discount->min_order_amount, 0, '.', ' ') . " UZS bo'lishi kerak. Hozirgi summa: " . number_format($subtotal, 0, '.', ' ') . " UZS"],
                ]);
            }

            // Calculate discount amount
            $discountAmount = 0.00;
            if ($discount->type === 'percentage') {
                $discountAmount = $subtotal * ((float)$discount->value / 100);
            } else {
                $discountAmount = (float)$discount->value;
            }

            // Discount amount cannot exceed subtotal
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }

            // Update order
            $order->update([
                'discount_id' => $discount->id,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal - $discountAmount,
            ]);

            Cache::forget('admin_dashboard_analytics');

            return $order->load(['table', 'waiter', 'items.food', 'discount']);
        });
    }

    /**
     * Toggle manual status of a discount.
     *
     * @param int $id
     * @return Discount
     */
    public function toggleStatus(int $id): Discount
    {
        $discount = $this->discountRepository->findById($id);
        if (!$discount) {
            throw new \InvalidArgumentException("Chegirma topilmadi.");
        }

        $discount->update([
            'is_active' => !$discount->is_active
        ]);

        return $discount;
    }
}
