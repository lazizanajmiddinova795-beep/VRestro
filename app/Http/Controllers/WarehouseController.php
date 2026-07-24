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

    /**
     * Sell raw ingredients directly to customer with instant receipt generation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sellIngredient(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'payment_method' => ['required', 'string', 'in:cash,card,click,payme'],
            'customer_name' => ['nullable', 'string', 'max:150'],
        ]);

        $ingredient = \App\Models\Ingredient::findOrFail($data['ingredient_id']);

        if ($ingredient->quantity < $data['quantity']) {
            return response()->json([
                'message' => "Omborda yetarli masalliq yo'q! Mavjud qoldiq: {$ingredient->quantity} {$ingredient->unit}"
            ], 422);
        }

        $unitPrice = $ingredient->sell_price ?: $ingredient->cost_price;
        $totalAmount = round($unitPrice * $data['quantity'], 2);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Deduct warehouse stock
            $ingredient->quantity -= $data['quantity'];
            $ingredient->save();

            // Create inventory transaction record
            $transaction = \App\Models\InventoryTransaction::create([
                'reference_number' => 'SALE-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'type' => 'chiqim',
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'notes' => "Mijozga masalliq sotuvi: " . ($data['customer_name'] ?: 'Mijoz'),
            ]);

            \App\Models\InventoryTransactionItem::create([
                'inventory_transaction_id' => $transaction->id,
                'ingredient_id' => $ingredient->id,
                'quantity' => $data['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalAmount,
            ]);

            // Record in payments table
            \App\Models\Payment::create([
                'payment_number' => 'PAY-ING-' . strtoupper(\Illuminate\Support\Str::random(6)),
                'order_id' => null,
                'customer_id' => null,
                'amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'cashier_id' => $request->user()->id,
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Masalliq sotuvi muvaffaqiyatli amalga oshirildi!',
                'receipt' => [
                    'receipt_number' => $transaction->reference_number,
                    'ingredient_name' => $ingredient->name,
                    'quantity' => $data['quantity'],
                    'unit' => $ingredient->unit,
                    'unit_price' => $unitPrice,
                    'total_amount' => $totalAmount,
                    'customer_name' => $data['customer_name'] ?: 'Mijoz',
                    'cashier_name' => $request->user()->name,
                    'payment_method' => strtoupper($data['payment_method']),
                    'date' => now()->format('Y-m-d H:i:s'),
                ]
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Sotuvni amalga oshirishda xatolik: ' . $e->getMessage()], 500);
        }
    }
}
