<?php

namespace App\Http\Controllers;

use App\Services\ChefService;
use App\Services\ChefStopListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChefController extends Controller
{
    protected ChefService $chefService;
    protected ChefStopListService $chefStopListService;

    public function __construct(ChefService $chefService, ChefStopListService $chefStopListService)
    {
        $this->chefService = $chefService;
        $this->chefStopListService = $chefStopListService;
    }

    /**
     * Get all active kitchen order items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $items = $this->chefService->getActiveKitchenItems();
        return response()->json($items);
    }

    /**
     * Update status of a specific order item.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,cooking,ready'],
        ]);

        try {
            $updatedItem = $this->chefService->updateItemStatus($id, $data['status']);
            return response()->json([
                'success' => true,
                'message' => "Item statusi muvaffaqiyatli yangilandi.",
                'item' => $updatedItem
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Statusni yangilashda xatolik yuz berdi: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all registered foods grouped by Category.
     *
     * @return JsonResponse
     */
    public function getMenu(): JsonResponse
    {
        $menu = $this->chefStopListService->getKitchenMenuWithStatus();
        return response()->json($menu);
    }

    /**
     * Toggle availability status of a food.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function toggleFood(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'is_available' => ['required', 'boolean'],
        ]);

        try {
            $food = $this->chefStopListService->toggleFoodAvailability($id, $data['is_available']);
            return response()->json([
                'success' => true,
                'message' => "Taom holati muvaffaqiyatli o'zgartirildi.",
                'food' => $food
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Taom holatini o'zgartirishda xatolik yuz berdi: " . $e->getMessage()
            ], 500);
        }
    }
}
