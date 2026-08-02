<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\PaymentManager;
use App\Services\EscrowService;
use App\Support\PaymentMethods;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentManager $paymentManager,
        private EscrowService $escrowService,
    ) {}

    public function show(Request $request, Order $order): View|RedirectResponse
    {
        abort_unless($request->user()->can('view', $order), 403);

        if ($order->payment_status === Order::PAYMENT_ESCROW_HELD || $order->payment_status === Order::PAYMENT_RELEASED) {
            return redirect()->route('checkout.confirmation', $order);
        }

        $order->load(['subOrders.vendor', 'subOrders.items']);

        return view('storefront.payment', [
            'order' => $order,
            'methods' => PaymentMethods::enabled(),
            'bankDetails' => $this->bankDetailLabels(),
            'recommendedGateway' => PaymentMethods::recommendedFor($order->currency),
        ]);
    }

    public function process(Request $request, Order $order): JsonResponse
    {
        abort_unless($request->user()->can('view', $order), 403);

        $request->validate([
            // A method the admin has switched off must not be chargeable, even
            // if a stale page still shows its button.
            'gateway' => ['required', Rule::in(PaymentMethods::enabledGateways())],
        ]);

        $gatewayName = $request->input('gateway');
        $gateway = $this->paymentManager->driver($gatewayName);

        $intentResult = $gateway->createIntent($order);

        if (! $intentResult->isSuccess) {
            return response()->json([
                'success' => false,
                'message' => $intentResult->errorMessage ?? 'Failed to initialize payment gateway.',
            ], 422);
        }

        Payment::create([
            'order_id' => $order->id,
            'gateway' => $gatewayName,
            'gateway_payment_id' => $intentResult->gatewayPaymentId,
            'gateway_order_id' => $intentResult->gatewayOrderId,
            'amount' => $order->grand_total,
            'currency' => strtoupper($order->currency ?? 'USD'),
            'status' => 'created',
            'raw_response' => $intentResult->checkoutPayload,
        ]);

        return response()->json([
            'success' => true,
            'gateway' => $gatewayName,
            'intent' => $intentResult,
        ]);
    }

    public function complete(Request $request, Order $order): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()->can('view', $order), 403);

        $validated = $request->validate([
            'gateway' => ['required', Rule::in(PaymentMethods::enabledGateways())],
        ]);

        $gatewayName = $validated['gateway'];
        $gateway = $this->paymentManager->driver($gatewayName);

        $payment = Payment::where('order_id', $order->id)
            ->where('gateway', $gatewayName)
            ->latest()
            ->first();

        if (! $payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway' => $gatewayName,
                'amount' => $order->grand_total,
                'currency' => strtoupper($order->currency ?? 'USD'),
                'status' => 'created',
            ]);
        }

        $payload = $request->all();
        $captured = $gateway->capture($payment, $payload);

        if ($captured || $gatewayName === 'bank_transfer') {
            $this->escrowService->hold($order);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'redirect' => route('checkout.confirmation', $order)]);
            }

            return redirect()->route('checkout.confirmation', $order)->with('status', 'Payment completed successfully!');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Payment authorization failed.'], 422);
        }

        return redirect()->route('payment.show', $order)->with('error', 'Payment verification failed. Please try again.');
    }

    public function proforma(Request $request, Order $order): View
    {
        abort_unless($request->user()->can('view', $order), 403);

        $order->load(['subOrders.vendor', 'subOrders.items', 'buyer']);

        return view('storefront.proforma', [
            'order' => $order,
            'bankDetails' => $this->bankDetailLabels(),
        ]);
    }

    /**
     * Admin-entered wire details, keyed by the label the buyer sees.
     *
     * @return array<string, string>
     */
    private function bankDetailLabels(): array
    {
        $labels = [
            'beneficiary' => 'Beneficiary',
            'bank_name' => 'Bank',
            'branch' => 'Branch',
            'account_number' => 'Account no.',
            'swift' => 'SWIFT / BIC',
            'ifsc' => 'IFSC',
            'notes' => 'Notes',
        ];

        $bank = PaymentMethods::bankDetails();

        return collect($labels)
            ->filter(fn (string $label, string $key): bool => isset($bank[$key]))
            ->mapWithKeys(fn (string $label, string $key): array => [$label => (string) $bank[$key]])
            ->all();
    }
}
