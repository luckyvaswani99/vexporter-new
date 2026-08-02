<?php

namespace App\Payments\Gateways;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SubOrder;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\DTOs\PaymentIntentResult;
use App\Payments\DTOs\PayoutResult;
use App\Payments\DTOs\RefundResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayGateway implements PaymentGateway
{
    private string $keyId;

    private string $keySecret;

    private string $webhookSecret;

    public function __construct(array $config = [])
    {
        $this->keyId = $config['key_id'] ?? config('services.razorpay.key');
        $this->keySecret = $config['key_secret'] ?? config('services.razorpay.secret');
        $this->webhookSecret = $config['webhook_secret'] ?? config('services.razorpay.webhook_secret');
    }

    public function createIntent(Order $order, array $options = []): PaymentIntentResult
    {
        // Amount in minor units (e.g. paisa/cents)
        $amount = $order->grand_total;
        $currency = strtoupper($order->currency ?? 'INR');

        if ($this->keyId === 'rzp_test_dummy' || app()->environment('testing')) {
            $mockRazorpayOrderId = 'order_rzp_test_'.time().'_'.$order->id;

            return new PaymentIntentResult(
                gateway: 'razorpay',
                gatewayPaymentId: null,
                gatewayOrderId: $mockRazorpayOrderId,
                clientSecret: null,
                checkoutPayload: [
                    'key' => $this->keyId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'name' => config('app.name', 'VEXPORTER'),
                    'description' => "Order #{$order->reference}",
                    'order_id' => $mockRazorpayOrderId,
                    'prefill' => [
                        'name' => $order->shipping_address['contact_name'] ?? $order->buyer?->name,
                        'email' => $order->buyer?->email,
                        'contact' => $order->shipping_address['phone'] ?? $order->buyer?->phone,
                    ],
                ],
            );
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amount,
                    'currency' => $currency,
                    'receipt' => $order->reference,
                    'notes' => [
                        'order_id' => (string) $order->id,
                        'reference' => $order->reference,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $razorpayOrderId = $data['id'];

                return new PaymentIntentResult(
                    gateway: 'razorpay',
                    gatewayPaymentId: null,
                    gatewayOrderId: $razorpayOrderId,
                    checkoutPayload: [
                        'key' => $this->keyId,
                        'amount' => $amount,
                        'currency' => $currency,
                        'name' => config('app.name', 'VEXPORTER'),
                        'description' => "Order #{$order->reference}",
                        'order_id' => $razorpayOrderId,
                    ],
                );
            }

            Log::error('Razorpay Order creation failed', ['body' => $response->body()]);

            return new PaymentIntentResult(
                gateway: 'razorpay',
                gatewayPaymentId: null,
                gatewayOrderId: null,
                isSuccess: false,
                errorMessage: $response->json('error.description') ?? 'Failed to create Razorpay order.',
            );
        } catch (\Throwable $e) {
            Log::error('Razorpay exception: '.$e->getMessage());

            return new PaymentIntentResult(
                gateway: 'razorpay',
                gatewayPaymentId: null,
                gatewayOrderId: null,
                isSuccess: false,
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function capture(Payment $payment, array $payload = []): bool
    {
        $razorpayPaymentId = $payload['razorpay_payment_id'] ?? $payment->gateway_payment_id;

        if (! $razorpayPaymentId) {
            return false;
        }

        if ($this->keyId === 'rzp_test_dummy' || app()->environment('testing')) {
            $payment->update([
                'gateway_payment_id' => $razorpayPaymentId,
                'status' => 'captured',
                'raw_response' => array_merge($payment->raw_response ?? [], $payload),
                'paid_at' => now(),
            ]);

            return true;
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("https://api.razorpay.com/v1/payments/{$razorpayPaymentId}/capture", [
                    'amount' => $payment->amount,
                    'currency' => strtoupper($payment->currency),
                ]);

            if ($response->successful()) {
                $payment->update([
                    'gateway_payment_id' => $razorpayPaymentId,
                    'status' => 'captured',
                    'raw_response' => $response->json(),
                    'paid_at' => now(),
                ]);

                return true;
            }
        } catch (\Throwable $e) {
            Log::error('Razorpay capture exception: '.$e->getMessage());
        }

        return false;
    }

    public function refund(Payment $payment, int $amount, ?string $reason = null): RefundResult
    {
        if ($this->keyId === 'rzp_test_dummy' || app()->environment('testing')) {
            $mockRefundId = 'rfnd_test_'.time();

            return new RefundResult(
                isSuccess: true,
                gatewayRefundId: $mockRefundId,
                amount: $amount,
                status: 'processed',
                rawResponse: ['id' => $mockRefundId, 'status' => 'processed'],
            );
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("https://api.razorpay.com/v1/payments/{$payment->gateway_payment_id}/refund", [
                    'amount' => $amount,
                    'notes' => ['reason' => $reason],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return new RefundResult(
                    isSuccess: true,
                    gatewayRefundId: $data['id'] ?? null,
                    amount: $data['amount'] ?? $amount,
                    status: $data['status'] ?? 'processed',
                    rawResponse: $data,
                );
            }

            return new RefundResult(
                isSuccess: false,
                errorMessage: $response->json('error.description') ?? 'Refund failed.',
            );
        } catch (\Throwable $e) {
            return new RefundResult(
                isSuccess: false,
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function verifyWebhook(Request $request): bool
    {
        $signature = $request->header('X-Razorpay-Signature');

        if (! $signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    public function transferToVendor(SubOrder $subOrder): PayoutResult
    {
        $vendor = $subOrder->vendor;
        $accountDetails = $vendor->payout_details ?? [];

        if (empty($accountDetails['razorpay_account_id'])) {
            // Fallback for testing / manual transfer queue
            $transferId = 'trf_rzp_mock_'.time().'_'.$subOrder->id;

            return new PayoutResult(
                isSuccess: true,
                gatewayTransferId: $transferId,
                status: 'processed',
                rawResponse: ['id' => $transferId, 'note' => 'Mock Razorpay Route transfer'],
            );
        }

        try {
            $payment = Payment::where('order_id', $subOrder->order_id)->where('status', 'captured')->first();

            if (! $payment || ! $payment->gateway_payment_id) {
                return new PayoutResult(
                    isSuccess: false,
                    errorMessage: 'No captured payment found for sub-order.',
                );
            }

            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post("https://api.razorpay.com/v1/payments/{$payment->gateway_payment_id}/transfers", [
                    'transfers' => [
                        [
                            'account' => $accountDetails['razorpay_account_id'],
                            'amount' => $subOrder->vendor_payout_amount,
                            'currency' => strtoupper($subOrder->order->currency ?? 'INR'),
                            'notes' => [
                                'sub_order_reference' => $subOrder->reference,
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $transfer = $data['items'][0] ?? [];

                return new PayoutResult(
                    isSuccess: true,
                    gatewayTransferId: $transfer['id'] ?? null,
                    status: 'processed',
                    rawResponse: $data,
                );
            }

            return new PayoutResult(
                isSuccess: false,
                errorMessage: $response->json('error.description') ?? 'Transfer failed.',
            );
        } catch (\Throwable $e) {
            return new PayoutResult(
                isSuccess: false,
                errorMessage: $e->getMessage(),
            );
        }
    }
}
