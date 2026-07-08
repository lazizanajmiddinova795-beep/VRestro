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
        // Cache aggregate stats for 30 seconds to speed up loads, while keeping data fresh
        return Cache::remember('admin_dashboard_analytics', 30, function () {
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
        Cache::forget('admin_dashboard_analytics');
    }
}
