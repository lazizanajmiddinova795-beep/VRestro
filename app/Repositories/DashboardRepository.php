<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    /**
     * Get total revenue (completed/paid orders) for a specific date.
     *
     * @param string $date (Y-m-d format)
     * @return float
     */
    public function getRevenueForDate(string $date): float
    {
        return (float) Order::where('status', 'delivered')
            ->whereDate('created_at', $date)
            ->sum('total_amount');
    }

    /**
     * Get counts of orders by status today.
     *
     * @return array
     */
    public function getOrderStatusCountsToday(): array
    {
        $today = now()->toDateString();
        
        $results = Order::whereDate('created_at', $today)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'new' => $results['new'] ?? 0,
            'cooking' => $results['cooking'] ?? 0,
            'ready' => $results['ready'] ?? 0,
            'delivered' => $results['delivered'] ?? 0,
            'cancelled' => $results['cancelled'] ?? 0,
            'total' => array_sum($results),
        ];
    }

    /**
     * Get number of orders currently cooking.
     *
     * @return int
     */
    public function getKitchenLoadCount(): int
    {
        return (int) Order::where('status', 'cooking')->count();
    }

    /**
     * Get total expenses for a specific date.
     *
     * @param string $date (Y-m-d format)
     * @return float
     */
    public function getExpensesForDate(string $date): float
    {
        return (float) Expense::whereDate('created_at', $date)->sum('amount');
    }

    /**
     * Get top 5 selling foods with quantities sold.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopSellingFoods(int $limit = 5): Collection
    {
        return OrderItem::join('foods', 'order_items.food_id', '=', 'foods.id')
            ->select(
                'foods.id as food_id',
                'foods.name as food_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered') // only count delivered orders
            ->groupBy('foods.id', 'foods.name')
            ->orderByDesc('quantity_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * Get the latest orders stream.
     *
     * @param int $limit
     * @return Collection
     */
    public function getLiveOrdersStream(int $limit = 5): Collection
    {
        return Order::with('waiter:id,name')
            ->select('id', 'table_id', 'waiter_id', 'total_amount', 'status', 'created_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sales and expenses statistics for the last 7 days.
     *
     * @return Collection
     */
    public function getWeeklySalesStats(): Collection
    {
        $startDate = now()->subDays(6)->toDateString();
        $endDate = now()->toDateString();

        // Query delivered orders grouped by date
        $orders = Order::where('status', 'delivered')
            ->whereDate('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as sales_amount')
            )
            ->groupBy('date')
            ->pluck('sales_amount', 'date')
            ->all();

        // Query expenses grouped by date
        $expenses = Expense::whereDate('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as expense_amount')
            )
            ->groupBy('date')
            ->pluck('expense_amount', 'date')
            ->all();

        // Build a complete 7 day collection to ensure no missing dates
        $stats = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $stats->push([
                'date' => $day,
                'day_name' => now()->subDays($i)->translatedFormat('l'), // local day name
                'sales' => (float) ($orders[$day] ?? 0),
                'expenses' => (float) ($expenses[$day] ?? 0),
            ]);
        }

        return $stats;
    }
}
