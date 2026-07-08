<?php

namespace App\Http\Controllers;

use App\Services\WarehouseService;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected WarehouseService $warehouseService;
    protected WarehouseRepositoryInterface $warehouseRepository;

    public function __construct(
        WarehouseService $warehouseService,
        WarehouseRepositoryInterface $warehouseRepository
    ) {
        $this->warehouseService = $warehouseService;
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * Get list of paginated warehouse transactions with optional filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'type' => ['nullable', 'string', 'in:kirim,chiqim,inventarizatsiya'],
            'ingredient_id' => ['nullable', 'integer', 'exists:ingredients,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $transactions = $this->warehouseRepository->getTransactions($filters);

        return response()->json($transactions);
    }

    /**
     * Get historical audit timeline stream of a single ingredient.
     *
     * @param int $ingredientId
     * @return JsonResponse
     */
    public function timeline(int $ingredientId): JsonResponse
    {
        $timeline = $this->warehouseRepository->getIngredientAuditTimeline($ingredientId);

        return response()->json($timeline);
    }

    /**
     * Execute Stock-In (Kirim) transaction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function kirim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $transaction = $this->warehouseService->stockIn($request->user()->id, $data);

        return response()->json([
            'message' => 'Kirim hujjati muvaffaqiyatli saqlandi va ombor qoldig\'i yangilandi.',
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Execute Stock-Out (Chiqim) Spoilage transaction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function chiqim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $transaction = $this->warehouseService->stockOut($request->user()->id, $data);

        return response()->json([
            'message' => 'Chiqim hujjati muvaffaqiyatli saqlandi va ombor qoldig\'i kamaytirildi.',
            'transaction' => $transaction,
        ], 201);
    }

    /**
     * Execute Audit (Inventarizatsiya) reconcile transaction.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function inventarizatsiya(Request $request): JsonResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $transaction = $this->warehouseService->auditStock($request->user()->id, $data);

        return response()->json([
            'message' => 'Inventarizatsiya natijalari muvaffaqiyatli muvofiqlashtirildi.',
            'transaction' => $transaction,
        ], 201);
    }
}
