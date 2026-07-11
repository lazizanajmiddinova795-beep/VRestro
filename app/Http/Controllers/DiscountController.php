<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountRequest;
use App\Repositories\Contracts\DiscountRepositoryInterface;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected DiscountService $discountService;
    protected DiscountRepositoryInterface $discountRepository;

    public function __construct(
        DiscountService $discountService,
        DiscountRepositoryInterface $discountRepository
    ) {
        $this->discountService = $discountService;
        $this->discountRepository = $discountRepository;
    }

    /**
     * Get list of all campaigns.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['is_active', 'search']);
        $discounts = $this->discountRepository->getAll($filters);

        return response()->json($discounts);
    }

    /**
     * Store a new discount.
     *
     * @param StoreDiscountRequest $request
     * @return JsonResponse
     */
    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $discount = $this->discountService->createDiscount($request->validated());

        return response()->json([
            'message' => 'Chegirma kampaniyasi muvaffaqiyatli yaratildi.',
            'discount' => $discount
        ], 201);
    }

    /**
     * Update a discount.
     *
     * @param StoreDiscountRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(StoreDiscountRequest $request, int $id): JsonResponse
    {
        $discount = $this->discountRepository->update($id, $request->validated());

        return response()->json([
            'message' => 'Chegirma kampaniyasi muvaffaqiyatli tahrirlandi.',
            'discount' => $discount
        ]);
    }

    /**
     * Toggle the active status of a discount.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $discount = $this->discountService->toggleStatus($id);

        return response()->json([
            'message' => 'Chegirma statusi o\'zgartirildi.',
            'discount' => $discount
        ]);
    }

    /**
     * Delete a discount.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->discountRepository->delete($id);

        return response()->json([
            'message' => 'Chegirma kampaniyasi o\'chirildi.'
        ]);
    }

    /**
     * Validate and apply a promocode to an open order.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'code' => ['required', 'string'],
        ]);

        $order = $this->discountService->applyPromocodeToOrder(
            (int)$data['order_id'],
            $data['code']
        );

        return response()->json([
            'message' => 'Promo-kod muvaffaqiyatli qo\'llanildi.',
            'order' => $order
        ]);
    }
}
