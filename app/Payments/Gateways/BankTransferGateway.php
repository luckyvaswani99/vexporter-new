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

class BankTransferGateway implements PaymentGateway
{
    public function createIntent(Order $order, array $options = []): PaymentIntentResult
    {
        $gatewayOrderId = 'tt_ref_'.time().'_'.$order->id;

        return new PaymentIntentResult(
            gateway: 'bank_transfer',
            gatewayPaymentId: null,
            gatewayOrderId: $gatewayOrderId,
            checkoutPayload: [
                'bank_name' => config('vexporter.bank_transfer.bank_name', 'HDFC Bank Ltd'),
                'account_number' => config('vexporter.bank_transfer.account_number', '50200012345678'),
                'swift_code' => config('vexporter.bank_transfer.swift_code', 'HDFCINBBXXX'),
                'ifsc_code' => config('vexporter.bank_transfer.ifsc_code', 'HDFC0001234'),
                'beneficiary_name' => config('vexporter.bank_transfer.beneficiary_name', 'VEXPORTER GLOBAL LTD'),
                'amount' => $order->grand_total,
                'currency' => strtoupper($order->currency ?? 'USD'),
                'payment_reference' => $order->reference,
            ],
        );
    }

    public function capture(Payment $payment, array $payload = []): bool
    {
        $payment->update([
            'status' => 'captured',
            'raw_response' => array_merge($payment->raw_response ?? [], $payload),
            'paid_at' => now(),
        ]);

        return true;
    }

    public function refund(Payment $payment, int $amount, ?string $reason = null): RefundResult
    {
        $refundId = 'ref_tt_'.time();

        return new RefundResult(
            isSuccess: true,
            gatewayRefundId: $refundId,
            amount: $amount,
            status: 'processed',
            rawResponse: ['note' => 'Manual Wire T/T refund recorded'],
        );
    }

    public function verifyWebhook(Request $request): bool
    {
        return true;
    }

    public function transferToVendor(SubOrder $subOrder): PayoutResult
    {
        $transferId = 'tt_payout_'.time().'_'.$subOrder->id;

        return new PayoutResult(
            isSuccess: true,
            gatewayTransferId: $transferId,
            status: 'pending',
            rawResponse: ['note' => 'Queued for manual bank transfer export (CSV)'],
        );
    }
}
