<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WaiterOrderController extends Controller
{
    /**
     * Submit an order from the waiter mobile app (supports creation and appending to existing).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'table_id' => ['required', 'exists:tables,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_id' => ['required', 'exists:foods,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.size_name' => ['nullable', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $waiter = $request->user();
        $tableId = $data['table_id'];

        return DB::transaction(function () use ($tableId, $data, $waiter) {
            $table = Table::findOrFail($tableId);

            // 1. Double check Stop-list constraint in database
            foreach ($data['items'] as $item) {
                $food = Food::findOrFail($item['food_id']);
                if (!$food->is_available) {
                    throw ValidationException::withMessages([
                        'items' => ["Ushbu taom oshxonada tugagan! (" . $food->name . ")"]
                    ]);
                }
            }

            // 2. Identify active order
            $order = Order::where('table_id', $tableId)
                ->whereIn('status', ['new', 'cooking', 'ready', 'delivered'])
                ->first();

            $isNew = false;
            if (!$order) {
                $isNew = true;
                // Generate sequential order number: ORD-YYYYMMDD-SEQ
                $dateStr = now()->format('Ymd');
                $dailyCount = Order::whereDate('created_at', now()->toDateString())->count();
                $orderNumber = 'ORD-' . $dateStr . '-' . str_pad($dailyCount + 1, 3, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'order_number' => $orderNumber,
                    'table_id' => $tableId,
                    'waiter_id' => $waiter->id,
                    'total_amount' => 0,
                    'status' => 'new'
                ]);

                // Update table status to occupied
                $table->update(['status' => 'occupied']);
            }

            // 3. Write items and calculate update amounts
            $totalAmount = floatval($order->total_amount);

            foreach ($data['items'] as $item) {
                $food = Food::findOrFail($item['food_id']);
                
                // Add or increment items
                OrderItem::create([
                    'order_id' => $order->id,
                    'food_id' => $food->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'], // use the configured variant/size price
                    'notes' => $item['notes'] ?? null,
                    'size_name' => $item['size_name'] ?? null,
                    'status' => 'pending'
                ]);

                $totalAmount += floatval($item['price']) * intval($item['quantity']);
            }

            $order->update(['total_amount' => $totalAmount]);

            return response()->json([
                'success' => true,
                'message' => $isNew ? 'Yangi buyurtma yaratildi.' : 'Taomlar buyurtmaga qo\'shildi.',
                'order' => $order
            ]);
        });
    }

    /**
     * Get active orders for the authenticated waiter to track production lifecycle status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activeStatus(Request $request): JsonResponse
    {
        $waiter = $request->user();

        $activeOrders = Order::with(['table', 'items.food'])
            ->where('waiter_id', $waiter->id)
            ->whereIn('status', ['new', 'cooking', 'ready', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($activeOrders);
    }
    /**
     * Cancel a specific pending item from the order (Waiter safeguard).
     */
    public function cancelItem(Request $request, int $itemId): JsonResponse
    {
        return DB::transaction(function () use ($itemId) {
            $item = OrderItem::lockForUpdate()->findOrFail($itemId);

            if ($item->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Faqat kutilayotgan (pending) taomlarni bekor qilish mumkin.'
                ], 403);
            }

            $item->update(['status' => 'cancelled']);

            // Recalculate order total amount
            $order = Order::with('items')->lockForUpdate()->findOrFail($item->order_id);
            $activeItems = $order->items->filter(fn($i) => $i->status !== 'cancelled');

            if ($activeItems->isEmpty()) {
                $order->update(['status' => 'cancelled']);
                if ($order->table_id) {
                    Table::where('id', $order->table_id)->update(['status' => 'empty']);
                }
            } else {
                $newTotal = $activeItems->reduce(fn($sum, $i) => $sum + ($i->price * $i->quantity), 0);
                
                // Add service charge if set
                $serviceChargeRate = (float) app(\App\Repositories\Contracts\SettingRepositoryInterface::class)->getByKey('service_charge_rate');
                if ($serviceChargeRate > 0) {
                    $newTotal += $newTotal * ($serviceChargeRate / 100);
                }
                $order->update(['total_amount' => $newTotal]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Taom muvaffaqiyatli bekor qilindi.'
            ]);
        });
    }

    /**
     * Void the entire active/cooking order (Cashier safeguard with inventory rollback & waste logging).
     */
    public function voidOrder(Request $request, int $orderId): JsonResponse
    {
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($orderId, $data, $request) {
            $order = Order::with(['items.food.recipes.ingredient'])->lockForUpdate()->findOrFail($orderId);

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Buyurtma allaqachon bekor qilingan.'
                ], 422);
            }

            // Create inventory transaction waste logs if any item was already cooking or ready
            $needsWasteLog = $order->items->contains(fn($item) => in_array($item->status, ['cooking', 'ready']));

            if ($needsWasteLog) {
                // Log consumption accurately in inventory transactions
                $transaction = \App\Models\InventoryTransaction::create([
                    'user_id' => $request->user()->id,
                    'type' => 'out',
                    'notes' => 'Spilage/Waste: ' . $data['cancellation_reason'] . ' (Order #' . $order->order_number . ')',
                ]);

                foreach ($order->items as $item) {
                    if (in_array($item->status, ['cooking', 'ready']) && $item->food && $item->food->recipes) {
                        foreach ($item->food->recipes as $recipe) {
                            $ingredient = $recipe->ingredient;
                            if ($ingredient) {
                                $multiplier = 1.0;
                                if ($item->size_name && !empty($item->food->sizes)) {
                                    $matchedSize = collect($item->food->sizes)->firstWhere('name', $item->size_name);
                                    if ($matchedSize && isset($matchedSize['recipe_multiplier'])) {
                                        $multiplier = (float) $matchedSize['recipe_multiplier'];
                                    }
                                }

                                $qtyRequired = (float) ($recipe->quantity_required * $item->quantity * $multiplier);
                                $oldQty = (float) $ingredient->quantity;
                                $newQty = $oldQty - $qtyRequired;

                                // Deduct from ingredients table
                                $ingredient->update(['quantity' => $newQty]);

                                // Create Transaction Item details
                                \App\Models\InventoryTransactionItem::create([
                                    'transaction_id' => $transaction->id,
                                    'ingredient_id' => $ingredient->id,
                                    'quantity' => $qtyRequired,
                                    'unit_price' => $ingredient->unit_price ?? 0,
                                    'old_quantity' => $oldQty,
                                    'new_quantity' => $newQty,
                                ]);
                            }
                        }
                    }
                }
            }

            // Update order and item statuses
            $order->update(['status' => 'cancelled']);
            OrderItem::where('order_id', $orderId)->update(['status' => 'cancelled']);

            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'empty']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Buyurtma to\'liq bekor qilindi, stollar xaritasi yangilandi.'
            ]);
        });
    }
}
