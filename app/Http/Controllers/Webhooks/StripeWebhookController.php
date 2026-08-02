<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\WebhookEvent;
use App\Payments\Gateways\StripeGateway;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeGateway $gateway,
        private EscrowService $escrowService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $this->gateway->verifyWebhook($request)) {
            return response()->json(['error' => 'Invalid webhook signature'], 400);
        }

        $payload = $request->all();
        $eventId = $payload['id'] ?? md5($request->getContent());
        $eventType = $payload['type'] ?? 'unknown';

        $webhookEvent = WebhookEvent::where('gateway', 'stripe')
            ->where('event_id', $eventId)
            ->first();

        if ($webhookEvent && $webhookEvent->processed_at) {
            return response()->json(['status' => 'already_processed']);
        }

        if (! $webhookEvent) {
            $webhookEvent = WebhookEvent::create([
                'gateway' => 'stripe',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $payload,
            ]);
        }

        try {
            switch ($eventType) {
                case 'payment_intent.succeeded':
                    $object = $payload['data']['object'] ?? [];
                    $intentId = $object['id'] ?? null;

                    $payment = Payment::where('gateway_payment_id', $intentId)->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'captured',
                            'raw_response' => $payload,
                            'paid_at' => now(),
                        ]);

                        $this->escrowService->hold($payment->order);
                    }
                    break;

                case 'payment_intent.payment_failed':
                    $object = $payload['data']['object'] ?? [];
                    $intentId = $object['id'] ?? null;

                    $payment = Payment::where('gateway_payment_id', $intentId)->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'failed',
                            'raw_response' => $payload,
                        ]);
                    }
                    break;

                case 'charge.refunded':
                    $object = $payload['data']['object'] ?? [];
                    $intentId = $object['payment_intent'] ?? null;
                    $refundsData = $object['refunds']['data'] ?? [];
                    $refundObj = end($refundsData) ?: [];

                    $payment = Payment::where('gateway_payment_id', $intentId)->first();

                    if ($payment) {
                        Refund::create([
                            'payment_id' => $payment->id,
                            'amount' => $refundObj['amount'] ?? $payment->amount,
                            'reason' => 'Processed via Stripe webhook',
                            'gateway_refund_id' => $refundObj['id'] ?? null,
                            'status' => 'processed',
                        ]);

                        $payment->update(['status' => 'refunded']);
                        $payment->order->update(['payment_status' => Order::PAYMENT_REFUNDED]);
                    }
                    break;
            }

            $webhookEvent->update(['processed_at' => now()]);

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
