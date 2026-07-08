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

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;
    protected RecipeService $recipeService;

    public function __construct(OrderRepositoryInterface $orderRepository, RecipeService $recipeService)
    {
        $this->orderRepository = $orderRepository;
        $this->recipeService = $recipeService;
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
            // 1. Generate Order Number
            $orderNumber = $this->orderRepository->generateOrderNumber();

            // 2. Create Order Container
            $order = $this->orderRepository->create([
                'order_number' => $orderNumber,
                'table_id' => $data['table_id'] ?? null,
                'waiter_id' => $data['waiter_id'] ?? null,
                'status' => 'new',
                'total_amount' => 0, // updated below
            ]);

            $totalAmount = 0;

            // 3. Attach Items
            foreach ($data['items'] as $item) {
                $food = Food::findOrFail($item['food_id']);

                if (!$food->is_available) {
                    throw ValidationException::withMessages([
                        'items' => ["{$food->name} hozirda mavjud emas."],
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $priceSnapshot = $food->price; // Price snapshot at the time of order
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $food->id,
                    'quantity' => $quantity,
                    'price' => $priceSnapshot,
                    'notes' => $item['notes'] ?? null,
                ]);

                $totalAmount += $quantity * $priceSnapshot;
            }

            // 4. Update Order Total
            $order->update(['total_amount' => $totalAmount]);

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
    public function cancelOrder(int $orderId): Order
    {
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
