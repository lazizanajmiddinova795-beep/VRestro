<?php

namespace App\Services;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected DashboardRepositoryInterface $dashboardRepository;

    public function __construct(DashboardRepositoryInterface $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Get aggregated admin dashboard analytics.
     *
     * @return array
     */
    public function getAnalyticsData(): array
    {
        $branchId = request()->header('X-Branch-Id') ?? auth()->user()?->branch_id ?? 'global';
        $cacheKey = "admin_dashboard_analytics_{$branchId}";

        // Cache aggregate stats for 30 seconds to speed up loads, while keeping data fresh
        return Cache::remember($cacheKey, 30, function () {
            $today = now()->toDateString();
            $yesterday = now()->subDay()->toDateString();

            // 1. Daily Revenue (Today vs Yesterday)
            $revenueToday = $this->dashboardRepository->getRevenueForDate($today);
            $revenueYesterday = $this->dashboardRepository->getRevenueForDate($yesterday);
            
            $revenueChangePercent = 0.0;
            if ($revenueYesterday > 0) {
                $revenueChangePercent = round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100, 1);
            } elseif ($revenueToday > 0) {
                $revenueChangePercent = 100.0; // 100% increase if yesterday was 0 and today has sales
            }

            // 2. Active & Completed Orders Counts Today
            $orderCounts = $this->dashboardRepository->getOrderStatusCountsToday();

            // 3. Kitchen Load Count (cooking status)
            $kitchenLoad = $this->dashboardRepository->getKitchenLoadCount();

            // 4. Daily Expenses
            $expensesToday = $this->dashboardRepository->getExpensesForDate($today);

            // 5. Weekly Sales & Expenses Statistics
            $weeklyStats = $this->dashboardRepository->getWeeklySalesStats();

            // 6. Top Selling Items
            $topSellingItems = $this->dashboardRepository->getTopSellingFoods(5)->map(function ($item) {
                return [
                    'name' => $item->food_name,
                    'quantity' => (int) $item->quantity_sold,
                    'revenue' => (float) $item->revenue,
                ];
            });

            // 7. Live Orders Stream (always fetch fresh live data, but since it's inside cache closure, it shares 30s cache)
            $liveOrders = $this->dashboardRepository->getLiveOrdersStream(5)->map(function ($order) {
                return [
                    'id' => $order->id,
                    'table_id' => $order->table_id,
                    'waiter_name' => $order->waiter->name ?? 'Noma\'lum',
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('H:i'), // format as hours:minutes
                ];
            });

            // 8. Daily Sales Breakdown by Payment Method
            $dailySales = \App\Models\Payment::withoutGlobalScopes()
                ->whereDate('created_at', $today)
                ->where('status', 'completed')
                ->selectRaw("
                    COALESCE(SUM(cash_amount), 0) as cash_total,
                    COALESCE(SUM(card_amount), 0) as card_total,
                    COALESCE(SUM(qr_amount), 0) as qr_total,
                    COALESCE(SUM(total_amount), 0) as total,
                    COUNT(*) as count
                ")
                ->first();

            // 9. Cashier Activity Today — get from orders' waiter (who handled the order)
            $cashierActivity = \App\Models\Payment::withoutGlobalScopes()
                ->whereDate('payments.created_at', $today)
                ->where('payments.status', 'completed')
                ->join('orders', 'payments.order_id', '=', 'orders.id')
                ->join('users', 'orders.waiter_id', '=', 'users.id')
                ->selectRaw("
                    users.id as cashier_id,
                    users.name as cashier_name,
                    COUNT(*) as receipts_count,
                    COALESCE(SUM(payments.total_amount), 0) as total_amount,
                    MIN(payments.created_at) as first_payment,
                    MAX(payments.created_at) as last_payment
                ")
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total_amount')
                ->get()
                ->map(function ($c) {
                    return [
                        'cashier_id' => $c->cashier_id,
                        'cashier_name' => $c->cashier_name,
                        'receipts_count' => (int) $c->receipts_count,
                        'total_amount' => (float) $c->total_amount,
                        'first_payment' => $c->first_payment ? \Carbon\Carbon::parse($c->first_payment)->format('H:i') : null,
                        'last_payment' => $c->last_payment ? \Carbon\Carbon::parse($c->last_payment)->format('H:i') : null,
                    ];
                });

            return [
                'widgets' => [
                    'revenue' => [
                        'value' => $revenueToday,
                        'change_percent' => $revenueChangePercent,
                        'is_increase' => $revenueToday >= $revenueYesterday,
                    ],
                    'orders' => [
                        'active' => $orderCounts['new'] + $orderCounts['cooking'] + $orderCounts['ready'],
                        'completed' => $orderCounts['delivered'],
                        'total' => $orderCounts['total'],
                    ],
                    'kitchen_load' => $kitchenLoad,
                    'expenses' => $expensesToday,
                ],
                'daily_sales' => [
                    'cash' => (float) $dailySales->cash_total,
                    'card' => (float) $dailySales->card_total,
                    'qr' => (float) $dailySales->qr_total,
                    'total' => (float) $dailySales->total,
                    'count' => (int) $dailySales->count,
                ],
                'cashier_activity' => $cashierActivity,
                'charts' => [
                    'weekly' => $weeklyStats,
                ],
                'tables' => [
                    'top_selling' => $topSellingItems,
                    'live_orders' => $liveOrders,
                ]
            ];
        });
    }

    /**
     * Clear dashboard cache (invoke on new orders or status updates if needed).
     *
     * @return void
     */
    public function clearCache(): void
    {
        $branchId = request()->header('X-Branch-Id') ?? auth()->user()?->branch_id ?? 'global';
        Cache::forget("admin_dashboard_analytics_{$branchId}");
    }
}
