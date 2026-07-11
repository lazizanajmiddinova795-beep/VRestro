<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaiterController extends Controller
{
    /**
     * Get all tables with explicit status highlighting for the authenticated waiter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function tables(Request $request): JsonResponse
    {
        $waiter = $request->user();

        // Fetch all tables
        $tables = Table::orderBy('table_number')->get();

        // Fetch all active orders in restaurant
        $activeOrders = Order::whereIn('status', ['new', 'cooking', 'ready', 'delivered'])
            ->get()
            ->groupBy('table_id');

        $result = $tables->map(function ($table) use ($activeOrders, $waiter) {
            $tableOrders = $activeOrders->get($table->id);
            $activeOrder = $tableOrders ? $tableOrders->first() : null;

            $status = 'empty';
            $activeOrderId = null;

            if ($activeOrder) {
                if ($activeOrder->waiter_id === $waiter->id) {
                    $status = 'occupied_by_me';
                    $activeOrderId = $activeOrder->id;
                } else {
                    $status = 'occupied_by_other';
                }
            }

            return [
                'id' => $table->id,
                'table_number' => $table->table_number,
                'capacity' => $table->capacity,
                'status' => $status,
                'active_order_id' => $activeOrderId,
            ];
        });

        return response()->json($result);
    }

    /**
     * Get aggregate waiter daily KPI statistics for the profile tab.
     */
    public function dailyStats(Request $request): JsonResponse
    {
        $waiter = $request->user();
        $today = now()->toDateString();

        // 1. Total successfully closed (delivered) orders today
        $closedOrders = Order::where('waiter_id', $waiter->id)
            ->where('status', 'delivered')
            ->whereDate('created_at', $today)
            ->get();

        $totalOrdersCount = $closedOrders->count();

        // 2. Total sales amount
        $totalSalesAmount = $closedOrders->sum('total_amount');

        // 3. Count of active orders currently waiting for cashier final checkout
        $pendingCheckoutCount = Order::where('waiter_id', $waiter->id)
            ->whereIn('status', ['new', 'cooking', 'ready'])
            ->whereDate('created_at', $today)
            ->count();

        // 4. Earned bonus (reading dynamic setting, falling back to 5%)
        $settingService = app(\App\Repositories\Contracts\SettingRepositoryInterface::class);
        $bonusPercentage = floatval($settingService->getByKey('waiter_bonus_percentage') ?: 5);
        $earnedBonus = $totalSalesAmount * ($bonusPercentage / 100);

        return response()->json([
            'total_orders_count' => $totalOrdersCount,
            'total_sales_amount' => $totalSalesAmount,
            'pending_checkout_count' => $pendingCheckoutCount,
            'earned_bonus' => $earnedBonus
        ]);
    }
}
