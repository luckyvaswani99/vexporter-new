<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([RoleSeeder::class, CatalogSeeder::class]);
});

it('verifies signature and processes razorpay payment captured webhook idempotently', function () {
    $buyer = User::factory()->create();
    $vendor = Vendor::factory()->create();
    $order = Order::create([
        'reference' => 'VX-2026-TEST01',
        'buyer_id' => $buyer->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'currency' => 'INR',
        'subtotal' => 50000,
        'shipping_total' => 0,
        'grand_total' => 50000,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'gateway' => 'razorpay',
        'gateway_order_id' => 'order_rzp_webhook_123',
        'amount' => 50000,
        'currency' => 'INR',
        'status' => 'created',
    ]);

    $webhookSecret = config('services.razorpay.webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', 'whsec_dummy'));

    $payload = [
        'event_id' => 'evt_rzp_capture_001',
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_rzp_cap_001',
                    'order_id' => 'order_rzp_webhook_123',
                    'amount' => 50000,
                    'status' => 'captured',
                ],
            ],
        ],
    ];

    $content = json_encode($payload);
    $signature = hash_hmac('sha256', $content, $webhookSecret);

    // 1. Initial Webhook Dispatch
    $response = $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $content);

    $response->assertOk()->assertJsonPath('status', 'ok');

    expect($payment->fresh()->status)->toBe('captured')
        ->and($order->fresh()->payment_status)->toBe(Order::PAYMENT_ESCROW_HELD);

    // 2. Replayed Webhook Dispatch (Idempotency)
    $replayedResponse = $this->call('POST', route('webhooks.razorpay'), [], [], [], [
        'HTTP_X-Razorpay-Signature' => $signature,
        'CONTENT_TYPE' => 'application/json',
    ], $content);

    $replayedResponse->assertOk()->assertJsonPath('status', 'already_processed');
});

it('verifies signature and processes stripe payment_intent.succeeded webhook', function () {
    $buyer = User::factory()->create();
    $order = Order::create([
        'reference' => 'VX-2026-TEST02',
        'buyer_id' => $buyer->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'currency' => 'USD',
        'subtotal' => 10000,
        'shipping_total' => 0,
        'grand_total' => 10000,
    ]);

    $payment = Payment::create([
        'order_id' => $order->id,
        'gateway' => 'stripe',
        'gateway_payment_id' => 'pi_stripe_webhook_789',
        'amount' => 10000,
        'currency' => 'USD',
        'status' => 'created',
    ]);

    $webhookSecret = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET', 'whsec_dummy'));
    $timestamp = time();

    $payload = [
        'id' => 'evt_stripe_succ_001',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_stripe_webhook_789',
                'status' => 'succeeded',
                'amount' => 10000,
            ],
        ],
    ];

    $content = json_encode($payload);
    $signedPayload = $timestamp.'.'.$content;
    $signature = hash_hmac('sha256', $signedPayload, $webhookSecret);
    $stripeSignatureHeader = "t={$timestamp},v1={$signature}";

    $response = $this->call('POST', route('webhooks.stripe'), [], [], [], [
        'HTTP_Stripe-Signature' => $stripeSignatureHeader,
        'CONTENT_TYPE' => 'application/json',
    ], $content);

    $response->assertOk()->assertJsonPath('status', 'ok');

    expect($payment->fresh()->status)->toBe('captured')
        ->and($order->fresh()->payment_status)->toBe(Order::PAYMENT_ESCROW_HELD);
});
