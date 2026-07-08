<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    /**
     * Get total revenue (completed/paid orders) for a specific date.
     *
     * @param string $date (Y-m-d format)
     * @return float
     */
    public function getRevenueForDate(string $date): float;

    /**
     * Get counts of orders by status today.
     *
     * @return array
     */
    public function getOrderStatusCountsToday(): array;

    /**
     * Get number of orders currently cooking.
     *
     * @return int
     */
    public function getKitchenLoadCount(): int;

    /**
     * Get total expenses for a specific date.
     *
     * @param string $date (Y-m-d format)
     * @return float
     */
    public function getExpensesForDate(string $date): float;

    /**
     * Get top 5 selling foods with quantities sold.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopSellingFoods(int $limit = 5): Collection;

    /**
     * Get the latest orders stream.
     *
     * @param int $limit
     * @return Collection
     */
    public function getLiveOrdersStream(int $limit = 5): Collection;

    /**
     * Get sales and expenses statistics for the last 7 days.
     *
     * @return Collection
     */
    public function getWeeklySalesStats(): Collection;
}
