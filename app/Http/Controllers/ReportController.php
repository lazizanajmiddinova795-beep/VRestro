<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Parse date filters helper.
     */
    protected function getDates(Request $request): array
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Defaults to current month if dates are omitted
        if (!$startDate) {
            $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
        } else {
            $startDate = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        }

        if (!$endDate) {
            $endDate = Carbon::now()->endOfMonth()->toDateTimeString();
        } else {
            $endDate = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
        }

        return [$startDate, $endDate];
    }

    /**
     * Get Sales report.
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $report = $this->reportService->getSalesReport($start, $end);

        return response()->json($report);
    }

    /**
     * Get Menu Performance report.
     */
    public function getMenuReport(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $report = $this->reportService->getMenuReport($start, $end);

        return response()->json($report);
    }

    /**
     * Get Inventory depletion report.
     */
    public function getInventoryReport(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $report = $this->reportService->getInventoryReport($start, $end);

        return response()->json($report);
    }

    /**
     * Get Staff Performance KPI report.
     */
    public function getStaffReport(Request $request): JsonResponse
    {
        [$start, $end] = $this->getDates($request);
        $report = $this->reportService->getStaffReport($start, $end);

        return response()->json($report);
    }
}
