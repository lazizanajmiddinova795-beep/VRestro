<?php

namespace App\Services;

use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    protected ReportRepositoryInterface $reportRepository;

    public function __construct(ReportRepositoryInterface $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * Get Sales report daily breakdowns. Caches results for heavy custom/long ranges.
     */
    public function getSalesReport(string $startDate, string $endDate): array
    {
        $cacheKey = "sales_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            $data = $this->reportRepository->getSalesAnalytics($startDate, $endDate);

            // Structure summary totals
            $totalInvoiced = $data->sum('grand_total');
            $totalCash = $data->sum('cash_total');
            $totalCard = $data->sum('card_total');
            $totalQr = $data->sum('qr_total');
            $totalBonus = $data->sum('bonus_used');
            $totalDiscount = $data->sum('discount_total');

            return [
                'summary' => [
                    'grand_invoiced_income' => $totalInvoiced,
                    'cash_total' => $totalCash,
                    'card_total' => $totalCard,
                    'qr_total' => $totalQr,
                    'disbursed_discounts_total' => $totalDiscount,
                    'cashback_bonuses_used' => $totalBonus,
                    'net_income' => $totalInvoiced - $totalDiscount // Invoiced reflects subtotal minus discount or final payable. Let's return details.
                ],
                'daily_charts' => $data
            ];
        });
    }

    /**
     * Get Menu dish popularity report.
     */
    public function getMenuReport(string $startDate, string $endDate): array
    {
        $cacheKey = "menu_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            $popularity = $this->reportRepository->getFoodPopularity($startDate, $endDate);

            // Top 5 popular dishes
            $topSelling = $popularity->take(5)->values();

            // Least selling dishes (reversing the list)
            $leastSelling = $popularity->reverse()->take(5)->values();

            return [
                'top_selling' => $topSelling,
                'least_selling' => $leastSelling
            ];
        });
    }

    /**
     * Get Inventory depletion report.
     */
    public function getInventoryReport(string $startDate, string $endDate): array
    {
        $cacheKey = "inventory_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            $depletions = $this->reportRepository->getWarehouseConsumption($startDate, $endDate);

            return [
                'depletions' => $depletions->sortByDesc('total_consumed')->values()
            ];
        });
    }

    /**
     * Get Staff Performance KPI metrics.
     */
    public function getStaffReport(string $startDate, string $endDate): array
    {
        $cacheKey = "staff_report_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 600, function () use ($startDate, $endDate) {
            return $this->reportRepository->getStaffPerformance($startDate, $endDate);
        });
    }
}
