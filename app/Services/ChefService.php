<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ChefService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Fetch all active order items for the kitchen (status is pending or cooking).
     * Sorted strictly by creation timestamp (Oldest first).
     * Eager loads order, food, order.table, order.waiter.
     *
     * @return Collection
     */
    public function getActiveKitchenItems(): Collection
    {
        return OrderItem::with(['order.table', 'order.waiter', 'food'])
            ->whereIn('status', ['pending', 'cooking'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Update order item status safely inside a database transaction.
     * If all items in the parent order are ready, mark the order as ready.
     *
     * @param int $itemId
     * @param string $status
     * @return OrderItem
     * @throws \Exception
     */
    public function updateItemStatus(int $itemId, string $status): OrderItem
    {
        if (!in_array($status, ['pending', 'cooking', 'ready'])) {
            throw new \InvalidArgumentException("Noto'g'ri status yuborildi: {$status}");
        }

        return DB::transaction(function () use ($itemId, $status) {
            $item = OrderItem::with('order.items')->findOrFail($itemId);
            
            if ($status === 'ready' && $item->status !== 'cooking') {
                throw new \InvalidArgumentException("Taomni tayyor deb belgilash uchun, avval u tayyorlanayotgan (cooking) bo'lishi shart!");
            }
            
            if ($status === 'cooking' && $item->status !== 'pending') {
                throw new \InvalidArgumentException("Faqat yangi kutilayotgan (pending) taomlarni tayyorlashni boshlash mumkin!");
            }

            // Transition order item status
            $item->status = $status;
            $item->save();

            $order = $item->order;

            // If the order status was 'new' or 'cooking' and we started preparation
            if ($status === 'cooking' && $order->status === 'new') {
                $order->status = 'cooking';
                $order->save();
            }

            // Check if all items in this order are 'ready'
            $allItemsReady = $order->items->every(fn($orderItem) => $orderItem->status === 'ready');

            if ($allItemsReady) {
                $order->status = 'ready';
                $order->save();

                // Trigger background notification via Redis / Notification Service
                try {
                    $tableName = $order->table ? $order->table->table_number : 'Takeaway';
                    $this->notificationService->sendNotification(
                        'order_ready',
                        "Buyurtma tayyor",
                        "{$tableName} stoli uchun #{$order->order_number} buyurtmasi to'liq tayyor bo'ldi.",
                        [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'table_id' => $order->table_id,
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("KDS Notification Error: " . $e->getMessage());
                }
            }

            return $item;
        });
    }
}
