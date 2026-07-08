<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(
        PaymentService $paymentService,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->paymentService = $paymentService;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Get list of payments with optional filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:completed,refunded'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $payments = $this->paymentRepository->getAllPayments($filters);

        return response()->json($payments);
    }

    /**
     * Get today's revenue breakdown.
     *
     * @return JsonResponse
     */
    public function revenueBreakdown(): JsonResponse
    {
        $breakdown = $this->paymentRepository->getTodayRevenueBreakdown();

        return response()->json($breakdown);
    }

    /**
     * Process a payment (create payment record, complete order, update table & customer loyalty).
     *
     * @param ProcessPaymentRequest $request
     * @return JsonResponse
     */
    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        $payment = $this->paymentService->processPayment($request->validated());

        return response()->json([
            'message' => 'To\'lov muvaffaqiyatli amalga oshirildi.',
            'payment' => $payment
        ], 201);
    }

    /**
     * Refund a previously processed payment.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function refund(int $id): JsonResponse
    {
        $payment = $this->paymentService->refundPayment($id);

        return response()->json([
            'message' => 'To\'lov bekor qilindi (refunded).',
            'payment' => $payment
        ]);
    }
}
