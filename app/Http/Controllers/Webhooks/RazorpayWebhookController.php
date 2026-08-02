<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\WebhookEvent;
use App\Payments\Gateways\RazorpayGateway;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        private RazorpayGateway $gateway,
        private EscrowService $escrowService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        if (! $this->gateway->verifyWebhook($request)) {
            return response()->json(['error' => 'Invalid webhook signature'], 400);
        }

        $payload = $request->all();
        $eventId = $payload['event_id'] ?? $request->header('X-Razorpay-Event-Id') ?? md5($request->getContent());
        $eventType = $payload['event'] ?? 'unknown';

        $webhookEvent = WebhookEvent::where('gateway', 'razorpay')
            ->where('event_id', $eventId)
            ->first();

        if ($webhookEvent && $webhookEvent->processed_at) {
            return response()->json(['status' => 'already_processed']);
        }

        if (! $webhookEvent) {
            $webhookEvent = WebhookEvent::create([
                'gateway' => 'razorpay',
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $payload,
            ]);
        }

        try {
            switch ($eventType) {
                case 'payment.captured':
                case 'payment.authorized':
                    $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
                    $razorpayPaymentId = $paymentEntity['id'] ?? null;
                    $razorpayOrderId = $paymentEntity['order_id'] ?? null;

                    $payment = Payment::where('gateway_order_id', $razorpayOrderId)
                        ->orWhere('gateway_payment_id', $razorpayPaymentId)
                        ->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'captured',
                            'gateway_payment_id' => $razorpayPaymentId,
                            'raw_response' => $payload,
                            'paid_at' => now(),
                        ]);

                        $this->escrowService->hold($payment->order);
                    }
                    break;

                case 'payment.failed':
                    $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
                    $razorpayPaymentId = $paymentEntity['id'] ?? null;
                    $razorpayOrderId = $paymentEntity['order_id'] ?? null;

                    $payment = Payment::where('gateway_order_id', $razorpayOrderId)
                        ->orWhere('gateway_payment_id', $razorpayPaymentId)
                        ->first();

                    if ($payment) {
                        $payment->update([
                            'status' => 'failed',
                            'raw_response' => $payload,
                        ]);
                    }
                    break;

                case 'refund.processed':
                    $refundEntity = $payload['payload']['refund']['entity'] ?? [];
                    $razorpayPaymentId = $refundEntity['payment_id'] ?? null;
                    $refundId = $refundEntity['id'] ?? null;

                    $payment = Payment::where('gateway_payment_id', $razorpayPaymentId)->first();

                    if ($payment) {
                        Refund::create([
                            'payment_id' => $payment->id,
                            'amount' => $refundEntity['amount'] ?? $payment->amount,
                            'reason' => 'Processed via Razorpay webhook',
                            'gateway_refund_id' => $refundId,
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
            Log::error('Razorpay webhook processing error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
