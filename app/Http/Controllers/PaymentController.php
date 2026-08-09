<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
            'payment_method' => ['nullable', 'string', 'in:cash,card,click,payme'],
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

        ActivityLog::record(
            'payment_processed',
            "To'lov qabul qilindi — #{$payment->id} — " . number_format($payment->amount, 0, '.', ' ') . " so'm",
            'payments',
            ['payment_id' => $payment->id, 'amount' => $payment->amount, 'method' => $payment->payment_method]
        );

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

        ActivityLog::record(
            'payment_refunded',
            "To'lov #{$id} qaytarildi (refund)",
            'payments',
            ['payment_id' => $id]
        );

        return response()->json([
            'message' => 'To\'lov bekor qilindi (refunded).',
            'payment' => $payment
        ]);
    }

    /**
     * Update print status for a specific payment.
     */
    public function updatePrintStatus(int $id): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $payment->update([
            'is_printed' => true,
            'printed_at' => now(),
        ]);
        return response()->json([
            'message' => 'To\'lov cheki chop etilganligi belgilandi.',
            'payment' => $payment
        ]);
    }

    /**
     * Update an existing payment.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $data = $request->validate([
            'payment_method' => ['nullable', 'string'],
            'total_amount' => ['nullable', 'numeric'],
            'cash_amount' => ['nullable', 'numeric'],
            'card_amount' => ['nullable', 'numeric'],
            'qr_amount' => ['nullable', 'numeric'],
        ]);

        $payment->update(array_filter($data, fn($v) => !is_null($v)));

        ActivityLog::record(
            'payment_updated',
            "To'lov #{$id} tahrirlandi",
            'payments',
            ['payment_id' => $id, 'data' => $data]
        );

        return response()->json([
            'message' => 'To\'lov ma\'lumotlari yangilandi.',
            'payment' => $payment
        ]);
    }

    /**
     * Delete a payment record.
     */
    public function destroy(int $id): JsonResponse
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $payment->delete();

        ActivityLog::record(
            'payment_deleted',
            "To'lov #{$id} o'chirildi",
            'payments',
            ['payment_id' => $id]
        );

        return response()->json([
            'message' => 'To\'lov muvaffaqiyatli o\'chirildi.'
        ]);
    }
}
