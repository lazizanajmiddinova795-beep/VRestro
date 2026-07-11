<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderService $orderService, OrderRepositoryInterface $orderRepository)
    {
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get list of orders with optional filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:new,cooking,ready,delivered,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $orders = $this->orderRepository->getAllOrders($filters);

        return response()->json($orders);
    }

    /**
     * Create a new order.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'table_id' => ['nullable', 'exists:tables,id'],
            'waiter_id' => ['nullable', 'exists:users,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.food_id' => ['required', 'exists:foods,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
            'items.*.size_name' => ['nullable', 'string', 'max:255'],
        ]);

        $order = $this->orderService->createOrder($data);

        return response()->json([
            'message' => 'Buyurtma muvaffaqiyatli yaratildi.',
            'order' => $order
        ], 201);
    }

    /**
     * View specific order details.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->getOrderById($id);

        if (!$order) {
            return response()->json(['message' => 'Buyurtma topilmadi.'], 404);
        }

        return response()->json($order);
    }

    /**
     * Update order status.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:cooking,ready,delivered,cancelled'],
        ]);

        $order = $this->orderService->updateStatus($id, $data['status']);

        return response()->json([
            'message' => 'Buyurtma statusi o\'zgartirildi.',
            'order' => $order
        ]);
    }

    /**
     * Cancel an order.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5'],
        ]);

        $order = $this->orderService->cancelOrder($id, $data['cancellation_reason']);

        return response()->json([
            'message' => 'Buyurtma bekor qilindi.',
            'order' => $order
        ]);
    }

    /**
     * Update print status for a specific order.
     */
    public function updatePrintStatus(int $id): JsonResponse
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->update([
            'is_printed' => true,
            'printed_at' => now(),
        ]);
        return response()->json([
            'message' => 'Buyurtma chop etilganligi belgilandi.',
            'order' => $order
        ]);
    }

    /**
     * Get print structured data for pre-check.
     */
    public function getPrintData(int $id): JsonResponse
    {
        $order = \App\Models\Order::with(['table', 'waiter', 'items.food'])->findOrFail($id);
        return response()->json($order);
    }
}
