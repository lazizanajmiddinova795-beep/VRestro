<?php

namespace App\Services;

use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    protected PaymentRepositoryInterface $paymentRepository;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Process a new payment.
     *
     * @param array $data
     * @return Payment
     * @throws ValidationException
     */
    public function processPayment(array $data): Payment
    {
        // Fetch order
        $order = $this->orderRepository->getOrderById($data['order_id']);
        if (!$order) {
            throw ValidationException::withMessages([
                'order_id' => ['Buyurtma topilmadi.'],
            ]);
        }

        if ($order->status === 'delivered') {
            throw ValidationException::withMessages([
                'order_id' => ['Bu buyurtma allaqachon to\'langan.'],
            ]);
        }

        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'order_id' => ['Bekor qilingan buyurtma uchun to\'lov qabul qilib bo\'lmaydi.'],
            ]);
        }

        $totalAmount = (float) $order->total_amount;
        $cashAmount = (float) ($data['cash_amount'] ?? 0.00);
        $cardAmount = (float) ($data['card_amount'] ?? 0.00);
        $qrAmount = (float) ($data['qr_amount'] ?? 0.00);
        $bonusUsed = (float) ($data['bonus_used'] ?? 0.00);
        $paymentMethod = $data['payment_method'];

        // If mixed, ensure sum matches total
        if ($paymentMethod === 'mixed') {
            $sum = $cashAmount + $cardAmount + $qrAmount + $bonusUsed;
            if (abs($sum - $totalAmount) > 0.01) {
                throw ValidationException::withMessages([
                    'total_amount' => ["Kiritilgan summalar yig'indisi ({$sum}) buyurtma jami summasiga ({$totalAmount}) teng bo'lishi kerak."],
                ]);
            }
        } else {
            // For simple payment methods, set the appropriate amount
            $cashAmount = ($paymentMethod === 'cash') ? ($totalAmount - $bonusUsed) : 0.00;
            $cardAmount = ($paymentMethod === 'card') ? ($totalAmount - $bonusUsed) : 0.00;
            $qrAmount = ($paymentMethod === 'qr') ? ($totalAmount - $bonusUsed) : 0.00;

            if ($bonusUsed > $totalAmount) {
                throw ValidationException::withMessages([
                    'bonus_used' => ["Ishlatilgan bonus jami summadan oshib ketishi mumkin emas."],
                ]);
            }
        }

        // If customer is paying, check bonus balance
        $customerId = $data['customer_id'] ?? null;
        if ($customerId && $bonusUsed > 0) {
            $customer = Customer::find($customerId);
            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer_id' => ['Mijoz topilmadi.'],
                ]);
            }
            if ((float)$customer->bonus_balance < $bonusUsed) {
                throw ValidationException::withMessages([
                    'bonus_used' => ["Mijozda yetarli bonus mablag'i mavjud emas. Balans: {$customer->bonus_balance}"],
                ]);
            }
        }

        DB::beginTransaction();

        try {
            // 1. Create Payment
            $payment = $this->paymentRepository->create([
                'order_id' => $order->id,
                'customer_id' => $customerId,
                'total_amount' => $totalAmount,
                'payment_method' => $paymentMethod,
                'cash_amount' => $cashAmount,
                'card_amount' => $cardAmount,
                'qr_amount' => $qrAmount,
                'bonus_used' => $bonusUsed,
                'status' => 'completed',
            ]);

            // 2. Update Order status to delivered
            $order->update(['status' => 'delivered']);

            // 3. Free up table
            if ($order->table_id) {
                $table = Table::find($order->table_id);
                if ($table) {
                    $table->update(['status' => 'empty']);
                }
            }

            // 4. Update Customer loyalty points
            if ($customerId) {
                $customer = Customer::lockForUpdate()->find($customerId);
                if ($customer) {
                    // Deduct bonuses
                    if ($bonusUsed > 0) {
                        $customer->bonus_balance = (float)$customer->bonus_balance - $bonusUsed;
                    }

                    // Accumulate cashback (5% of the actual paid amount)
                    $paidAmount = $totalAmount - $bonusUsed;
                    $cashback = $paidAmount * 0.05;
                    $customer->bonus_balance = (float)$customer->bonus_balance + $cashback;

                    // Update spent and order count
                    $customer->total_orders_count = (int)$customer->total_orders_count + 1;
                    $customer->total_spent_amount = (float)$customer->total_spent_amount + $paidAmount;

                    $customer->save();
                }
            }

            // 5. Clear Cache
            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $payment->load(['order', 'customer']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Refund a payment.
     *
     * @param int $paymentId
     * @return Payment
     * @throws ValidationException
     */
    public function refundPayment(int $paymentId): Payment
    {
        $payment = $this->paymentRepository->getPaymentById($paymentId);
        if (!$payment) {
            throw ValidationException::withMessages([
                'payment' => ['To\'lov topilmadi.'],
            ]);
        }

        if ($payment->status === 'refunded') {
            throw ValidationException::withMessages([
                'payment' => ['Ushbu to\'lov allaqachon bekor qilingan (refunded).'],
            ]);
        }

        DB::beginTransaction();

        try {
            // 1. Update payment status to refunded
            $payment->update(['status' => 'refunded']);

            // 2. Rollback order status to ready (so it can be paid again or edited)
            $order = $payment->order;
            if ($order) {
                $order->update(['status' => 'ready']);

                // Reset table status to occupied if it exists
                if ($order->table_id) {
                    $table = Table::find($order->table_id);
                    if ($table) {
                        $table->update(['status' => 'occupied']);
                    }
                }
            }

            // 3. Reverse customer mutations
            if ($payment->customer_id) {
                $customer = Customer::lockForUpdate()->find($payment->customer_id);
                if ($customer) {
                    $bonusUsed = (float) $payment->bonus_used;
                    $totalAmount = (float) $payment->total_amount;
                    $paidAmount = $totalAmount - $bonusUsed;
                    $cashbackGiven = $paidAmount * 0.05;

                    // Subtract cashback given
                    $customer->bonus_balance = (float)$customer->bonus_balance - $cashbackGiven;

                    // Refund used bonuses
                    if ($bonusUsed > 0) {
                        $customer->bonus_balance = (float)$customer->bonus_balance + $bonusUsed;
                    }

                    // Decrement stats
                    $customer->total_orders_count = max(0, (int)$customer->total_orders_count - 1);
                    $customer->total_spent_amount = max(0, (float)$customer->total_spent_amount - $paidAmount);

                    $customer->save();
                }
            }

            // 4. Clear Cache
            Cache::forget('admin_dashboard_analytics');

            DB::commit();
            return $payment->load(['order', 'customer']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment refund failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
