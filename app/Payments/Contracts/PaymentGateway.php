<?php

namespace App\Payments\Contracts;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SubOrder;
use App\Payments\DTOs\PaymentIntentResult;
use App\Payments\DTOs\PayoutResult;
use App\Payments\DTOs\RefundResult;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /**
     * Create a payment intent or order with the gateway.
     *
     * @param  array<string, mixed>  $options
     */
    public function createIntent(Order $order, array $options = []): PaymentIntentResult;

    /**
     * Capture or confirm an authorized payment.
     *
     * @param  array<string, mixed>  $payload
     */
    public function capture(Payment $payment, array $payload = []): bool;

    /**
     * Refund a paid transaction.
     */
    public function refund(Payment $payment, int $amount, ?string $reason = null): RefundResult;

    /**
     * Verify incoming webhook signature.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Transfer funds to vendor linked account or bank details.
     */
    public function transferToVendor(SubOrder $subOrder): PayoutResult;
}
