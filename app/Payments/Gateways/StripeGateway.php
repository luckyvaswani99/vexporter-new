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

class StripeGateway implements PaymentGateway
{
    private string $secretKey;

    private string $publishableKey;

    private string $webhookSecret;

    public function __construct(array $config = [])
    {
        $this->secretKey = $config['secret_key'] ?? config('services.stripe.secret');
        $this->publishableKey = $config['publishable_key'] ?? config('services.stripe.key');
        $this->webhookSecret = $config['webhook_secret'] ?? config('services.stripe.webhook_secret');
    }

    public function createIntent(Order $order, array $options = []): PaymentIntentResult
    {
        $amount = $order->grand_total;
        $currency = strtolower($order->currency ?? 'usd');

        if ($this->secretKey === 'sk_test_dummy' || app()->environment('testing')) {
            $mockIntentId = 'pi_stripe_test_'.time().'_'.$order->id;
            $clientSecret = $mockIntentId.'_secret_mock';

            return new PaymentIntentResult(
                gateway: 'stripe',
                gatewayPaymentId: $mockIntentId,
                gatewayOrderId: null,
                clientSecret: $clientSecret,
                checkoutPayload: [
                    'publishableKey' => $this->publishableKey,
                    'clientSecret' => $clientSecret,
                    'amount' => $amount,
                    'currency' => $currency,
                ],
            );
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->post('https://api.stripe.com/v1/payment_intents', [
                    'amount' => $amount,
                    'currency' => $currency,
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'reference' => $order->reference,
                    ],
                    'automatic_payment_methods' => ['enabled' => 'true'],
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return new PaymentIntentResult(
                    gateway: 'stripe',
                    gatewayPaymentId: $data['id'],
                    gatewayOrderId: null,
                    clientSecret: $data['client_secret'] ?? null,
                    checkoutPayload: [
                        'publishableKey' => $this->publishableKey,
                        'clientSecret' => $data['client_secret'] ?? null,
                        'amount' => $amount,
                        'currency' => $currency,
                    ],
                );
            }

            return new PaymentIntentResult(
                gateway: 'stripe',
                gatewayPaymentId: null,
                gatewayOrderId: null,
                isSuccess: false,
                errorMessage: $response->json('error.message') ?? 'Failed to create Stripe payment intent.',
            );
        } catch (\Throwable $e) {
            Log::error('Stripe exception: '.$e->getMessage());

            return new PaymentIntentResult(
                gateway: 'stripe',
                gatewayPaymentId: null,
                gatewayOrderId: null,
                isSuccess: false,
                errorMessage: $e->getMessage(),
            );
        }
    }

    public function capture(Payment $payment, array $payload = []): bool
    {
        $intentId = $payload['payment_intent_id'] ?? $payment->gateway_payment_id;

        if (! $intentId) {
            return false;
        }

        if ($this->secretKey === 'sk_test_dummy' || app()->environment('testing')) {
            $payment->update([
                'gateway_payment_id' => $intentId,
                'status' => 'captured',
                'raw_response' => array_merge($payment->raw_response ?? [], $payload),
                'paid_at' => now(),
            ]);

            return true;
        }

        try {
            $response = Http::withToken($this->secretKey)->get("https://api.stripe.com/v1/payment_intents/{$intentId}");

            if ($response->successful() && ($response->json('status') === 'succeeded' || $response->json('status') === 'requires_capture')) {
                $payment->update([
                    'gateway_payment_id' => $intentId,
                    'status' => 'captured',
                    'raw_response' => $response->json(),
                    'paid_at' => now(),
                ]);

                return true;
            }
        } catch (\Throwable $e) {
            Log::error('Stripe capture exception: '.$e->getMessage());
        }

        return false;
    }

    public function refund(Payment $payment, int $amount, ?string $reason = null): RefundResult
    {
        if ($this->secretKey === 'sk_test_dummy' || app()->environment('testing')) {
            $mockRefundId = 're_stripe_test_'.time();

            return new RefundResult(
                isSuccess: true,
                gatewayRefundId: $mockRefundId,
                amount: $amount,
                status: 'processed',
                rawResponse: ['id' => $mockRefundId, 'status' => 'succeeded'],
            );
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->post('https://api.stripe.com/v1/refunds', [
                    'payment_intent' => $payment->gateway_payment_id,
                    'amount' => $amount,
                    'reason' => 'requested_by_customer',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return new RefundResult(
                    isSuccess: true,
                    gatewayRefundId: $data['id'] ?? null,
                    amount: $data['amount'] ?? $amount,
                    status: $data['status'] ?? 'succeeded',
                    rawResponse: $data,
                );
            }

            return new RefundResult(
                isSuccess: false,
                errorMessage: $response->json('error.message') ?? 'Stripe refund failed.',
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
        $signatureHeader = $request->header('Stripe-Signature');

        if (! $signatureHeader) {
            return false;
        }

        // Extract timestamp and v1 signature from Stripe header
        preg_match('/t=(\d+)/', $signatureHeader, $timeMatches);
        preg_match('/v1=([a-f0-9]+)/', $signatureHeader, $sigMatches);

        if (empty($timeMatches[1]) || empty($sigMatches[1])) {
            return false;
        }

        $signedPayload = $timeMatches[1].'.'.$request->getContent();
        $expectedSig = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($expectedSig, $sigMatches[1]);
    }

    public function transferToVendor(SubOrder $subOrder): PayoutResult
    {
        $vendor = $subOrder->vendor;
        $accountDetails = $vendor->payout_details ?? [];

        if (empty($accountDetails['stripe_account_id'])) {
            $transferId = 'tr_stripe_mock_'.time().'_'.$subOrder->id;

            return new PayoutResult(
                isSuccess: true,
                gatewayTransferId: $transferId,
                status: 'processed',
                rawResponse: ['id' => $transferId, 'note' => 'Mock Stripe Connect transfer'],
            );
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->asForm()
                ->post('https://api.stripe.com/v1/transfers', [
                    'amount' => $subOrder->vendor_payout_amount,
                    'currency' => strtolower($subOrder->order->currency ?? 'usd'),
                    'destination' => $accountDetails['stripe_account_id'],
                    'transfer_group' => $subOrder->order->reference,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return new PayoutResult(
                    isSuccess: true,
                    gatewayTransferId: $data['id'] ?? null,
                    status: 'processed',
                    rawResponse: $data,
                );
            }

            return new PayoutResult(
                isSuccess: false,
                errorMessage: $response->json('error.message') ?? 'Stripe Connect transfer failed.',
            );
        } catch (\Throwable $e) {
            return new PayoutResult(
                isSuccess: false,
                errorMessage: $e->getMessage(),
            );
        }
    }
}
