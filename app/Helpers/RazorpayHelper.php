<?php

namespace App\Helpers;

use Razorpay\Api\Api;
use Exception;
use Illuminate\Support\Facades\Log;

class RazorpayHelper
{
    protected Api $razorpay;

    public function __construct()
    {
        $key    = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        
        if (!$key || !$secret) {
            throw new Exception('Razorpay keys are missing. Check .env and config/services.php');
        }

        $this->razorpay = new Api($key, $secret);
    }

    /**
     * Create a Razorpay order
     *
     * @param string $receipt
     * @param float $amount
     * @param string $currency
     * @return array
     * @throws Exception
     */
    public function createOrder(string $receipt, float $amount, string $currency = 'INR'): array
    {
        try {
            $orderData = [
                'receipt' => $receipt,
                'amount' => round($amount * 100, 2), // amount in paise
                'currency' => $currency,
                'payment_capture' => 1
            ];

            $razorpayOrder = $this->razorpay->order->create($orderData);

            return $razorpayOrder->toArray();
        } catch (Exception $e) {
            throw new Exception("Razorpay Order Creation Failed: Raz48 " . $e->getMessage());
        }
    }

    /**
     * Fetch a Razorpay payment by its ID
     */
    public function fetchPayment(string $paymentId): array
    {
        try {
            $payment = $this->razorpay->payment->fetch($paymentId);

            return $payment->toArray();
        } catch (\Throwable $e) {
            throw new Exception("Razorpay Order Creation Failed: Raz62 " . $e->getMessage());
        }
    }

    /**
     * Verify a payment signature
     *
     * @param string $razorpayOrderId
     * @param string $razorpayPaymentId
     * @param string $razorpaySignature
     * @return bool
     * @throws Exception
     */
    public function verifyPaymentSignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        try {
            $this->razorpay->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $razorpaySignature,
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception("Payment verification failed: Raz87" . $e->getMessage());
        }
    }

    /**
     * Refund a payment
     *
     * @param string $paymentId
     * @param float|null $amount
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function refundPayment(string $paymentId, ?float $amount = null, array $reason = []): array
    {
        try {
            $payment = $this->razorpay->payment->fetch($paymentId);

            $refundData = [
                'speed' => 'optimum',
                'notes' => $reason,
            ];

            if ($amount) {
                $refundData['amount'] = $amount * 100; // paise
            }

            $refund = $payment->refund($refundData);

            return $refund->toArray();
        } catch (Exception $e) {
            throw new Exception("Refund failed: Raz118" . $e->getMessage());
        }
    }

    /**
     * Fetch Order
     */
    public function fetchOrder(string $orderId): array
    {
        try {
            $orderData = $this->razorpay->order->fetch($orderId);

            return $orderData->toArray();
        } catch (\Throwable $th) {
            throw new Exception("Failed to fetch Razorpay Order: Raz132" . $th->getMessage());
        }
    }
}
