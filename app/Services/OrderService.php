<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Food;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected RecipeService $recipeService;
    protected NotificationService $notificationService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        RecipeService $recipeService,
        NotificationService $notificationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->recipeService = $recipeService;
        $this->notificationService = $notificationService;
    }

    /**
     * Create a new order with items.
     *
     * @param array $data
     * @return Order
     * @throws \Exception
     */
    public function createOrder(array $data): Order
    {
        DB::beginTransaction();

        try {
            $tableId = $data['table_id'] ?? null;
            $order = null;

            // 1. Check if table has an active order
            if ($tableId) {
                $order = Order::where('table_id', $tableId)
                    ->whereIn('status', ['new', 'cooking', 'ready', 'delivered'])
                    ->doesntHave('payments')
                    ->first();
            }

            // 2. Create Order Container if no active order exists
            if (!$order) {
                $orderNumber = $this->orderRepository->generateOrderNumber();
                $order = $this->orderRepository->create([
                    'order_number' => $orderNumber,
                    'table_id' => $tableId,
                    'waiter_id' => $data['waiter_id'] ?? null,
                    'order_type' => $data['order_type'] ?? 'dine_in',
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'status' => 'new',
                    'total_amount' => 0,
                ]);

                if ($tableId) {
                    \App\Models\Table::where('id', $tableId)->update(['status' => 'occupied']);
                }
            }

            $totalAmount = (float) $order->total_amount;

            // 3. Attach Items
            foreach ($data['items'] as $item) {
                $food = Food::findOrFail($item['food_id']);

                if (!$food->is_available) {
                    throw ValidationException::withMessages([
                        'items' => ["{$food->name} hozirda mavjud emas."],
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $sizeName = $item['size_name'] ?? null;
                $priceSnapshot = (float) $food->price;

                if ($sizeName && !empty($food->sizes)) {
                    $matchedSize = collect($food->sizes)->firstWhere('name', $sizeName);
                    if ($matchedSize) {
                        $priceSnapshot = (float) $matchedSize['price'];
                    }
                }

                // Determine status for this item
                $itemStatus = $food->is_bar_item ? 'ready' : 'pending';

                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $food->id,
                    'quantity' => $quantity,
                    'price' => $priceSnapshot,
                    'notes' => $item['notes'] ?? null,
                    'size_name' => $sizeName,
                    'status' => $itemStatus,
                ]);
            }

            // 4. Recalculate Total Amount cleanly from ALL non-cancelled order items
            $order->load('items');
            $activeItems = $order->items->filter(fn($i) => $i->status !== 'cancelled');
            $rawTotal = $activeItems->reduce(fn($sum, $i) => $sum + ($i->price * $i->quantity), 0);

            $serviceChargeRate = (float) app(\App\Repositories\Contracts\SettingRepositoryInterface::class)->getByKey('service_charge_rate');
            $totalAmount = $rawTotal;
            if ($serviceChargeRate > 0) {
                $totalAmount += $rawTotal * ($serviceChargeRate / 100);
            }

            $order->update(['total_amount' => $totalAmount]);

            // Check if all items are bar items (ready). If so, update order to ready instantly
            $allKitchenItemsReady = $order->items()
                ->whereHas('food', function($q) {
                    $q->where('is_bar_item', false);
                })
                ->where('status', '!=', 'ready')
                ->doesntExist();

            if ($allKitchenItemsReady) {
                $order->update(['status' => 'ready']);
            }

            // Dispatch alert
            $this->notificationService->sendNotification(
                'new_order',
                "Yangi buyurtma keldi!",
                "Buyurtma #{$order->order_number} yaratildi. Stol: " . ($order->table?->table_number ?? 'Olib ketish') . ", Jami: " . number_format($totalAmount, 0, '.', ' ') . " UZS",
                ['order_id' => $order->id]
            );

            // 5. Fire real-time events / log hooks
            $this->triggerOrderDispatchedHook($order);

            // 6. Clear dashboard caching
            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $order->load(['table', 'waiter', 'items.food']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order status with strict transition validation.
     *
     * @param int $orderId
     * @param string $newStatus
     * @return Order
     * @throws ValidationException
     */
    public function updateStatus(int $orderId, string $newStatus): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->orderRepository->getOrderById($orderId);

            if (!$order) {
                throw ValidationException::withMessages([
                    'order' => ['Buyurtma topilmadi.'],
                ]);
            }

            $currentStatus = $order->status;

            // Validate status transitions
            $this->validateTransition($currentStatus, $newStatus);

            // Deduct stocks if transitioning to cooking
            if ($newStatus === 'cooking') {
                $this->recipeService->deductStockForOrder($orderId);
            }

            $order->update(['status' => $newStatus]);

            if ($newStatus === 'cancelled') {
                $this->notificationService->sendNotification(
                    'order_cancelled',
                    "Buyurtma bekor qilindi!",
                    "Buyurtma #{$order->order_number} bekor qilindi.",
                    ['order_id' => $order->id]
                );
            }

            // Clear dashboard caching
            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel an active order.
     *
     * @param int $orderId
     * @return Order
     */
    public function cancelOrder(int $orderId, ?string $reason = null): Order
    {
        $order = $this->orderRepository->getOrderById($orderId);
        if (!$order) {
            throw ValidationException::withMessages([
                'order' => ["Buyurtma topilmadi."],
            ]);
        }

        // If order has any item status that is cooking or ready, enforce reason logging
        $hasCookingOrReady = $order->items()->whereIn('status', ['cooking', 'ready'])->exists();
        if ($hasCookingOrReady && (empty($reason) || strlen($reason) < 5)) {
            throw ValidationException::withMessages([
                'cancellation_reason' => ["Tayyorlanayotgan yoki tayyor bo'lgan buyurtmani bekor qilish uchun sabab ko'rsatilishi shart (kamida 5 ta belgi)."],
            ]);
        }

        $order->update([
            'cancellation_reason' => $reason
        ]);

        return $this->updateStatus($orderId, 'cancelled');
    }

    /**
     * Validate transitions: new -> cooking -> ready -> delivered.
     * Any active state can move to 'cancelled'.
     *
     * @param string $current
     * @param string $new
     * @return void
     * @throws ValidationException
     */
    protected function validateTransition(string $current, string $new): void
    {
        if ($current === $new) {
            return;
        }

        // Final states cannot transition
        if (in_array($current, ['delivered', 'cancelled'])) {
            throw ValidationException::withMessages([
                'status' => ["Yopilgan buyurtma statusini o'zgartirib bo'lmaydi."],
            ]);
        }

        // Anything active can be cancelled
        if ($new === 'cancelled') {
            return;
        }

        $valid = false;
        switch ($current) {
            case 'new':
                $valid = ($new === 'cooking');
                break;
            case 'cooking':
                $valid = ($new === 'ready');
                break;
            case 'ready':
                $valid = ($new === 'delivered');
                break;
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'status' => ["Statusni '{$current}' dan '{$new}' holatiga o'zgartirib bo'lmaydi."],
            ]);
        }
    }

    /**
     * Abstract hook for warehouse inventory calculations.
     *
     * @param Order $order
     * @return void
     */
    protected function triggerOrderDispatchedHook(Order $order): void
    {
        // Log information as hook trigger for Phase 4 Recipes/Stock management
        Log::info("OrderDispatched: Order #{$order->order_number} successfully registered. Preparing inventory deduction triggers.");
    }
}
