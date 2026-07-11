<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ReportRepositoryInterface
{
    /**
     * Get sales analytics aggregated daily.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getSalesAnalytics(string $startDate, string $endDate): Collection;

    /**
     * Get food performance rankings (popular and least popular).
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getFoodPopularity(string $startDate, string $endDate): Collection;

    /**
     * Get ingredient consumption stats.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getWarehouseConsumption(string $startDate, string $endDate): Collection;

    /**
     * Get performance counts for Waiters and Chefs.
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getStaffPerformance(string $startDate, string $endDate): array;
}
