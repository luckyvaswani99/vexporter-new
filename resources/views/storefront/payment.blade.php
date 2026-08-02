<x-layouts.bare title="Pay for Order #{{ $order->reference }}">
    <div class="min-h-screen bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">

            {{-- Header --}}
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="inline-block mb-4">
                    <x-brand.logo size="lg" />
                </a>
                <h1 class="text-2xl font-bold text-slate-900 font-display">Complete Payment</h1>
                <p class="text-sm text-slate-500 mt-1">Order Reference: <span class="font-mono font-semibold text-slate-800">{{ $order->reference }}</span></p>
            </div>

            {{-- Order Summary Box --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-400">Total Payable</span>
                        <div class="text-3xl font-extrabold text-brand-dark font-display">{{ $order->grand_total_label }}</div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fa-solid me-1 fa-clock"></i> Payment Pending
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($order->subOrders as $subOrder)
                        <div class="flex items-center justify-between text-sm py-1">
                            <div class="flex items-center gap-2 text-slate-700">
                                <i class="fa-solid fa-store text-slate-400 text-xs"></i>
                                <span class="font-medium">{{ $subOrder->vendor->name }}</span>
                                <span class="text-xs text-slate-400">({{ $subOrder->items->count() }} items)</span>
                            </div>
                            <span class="font-semibold text-slate-900">{{ $subOrder->total_label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment Selector & Forms --}}
            <div x-data="paymentForm('{{ $recommendedGateway }}')" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Select Payment Method</h2>

                <div class="grid grid-cols-1 sm:grid-cols-{{ min(3, max(1, count($methods))) }} gap-4 mb-6">
                    @foreach ($methods as $method)
                        <button type="button" @click="selectedGateway = '{{ $method['gateway'] }}'"
                            :class="selectedGateway === '{{ $method['gateway'] }}' ? 'border-brand-red ring-2 ring-brand-red/20 bg-red-50/20' : 'border-slate-200 hover:border-slate-300'"
                            class="p-4 rounded-xl border text-left transition relative flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $method['label'] }}</span>
                                @if ($method['icon'] ?? null)
                                    <i class="{{ $method['icon'] }} text-brand-red text-lg"></i>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500">{{ $method['blurb'] ?? '' }}</p>
                        </button>
                    @endforeach
                </div>

                {{-- Status Alert --}}
                <div x-show="errorMessage" x-cloak class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm border border-red-200">
                    <i class="fa-solid me-1 fa-circle-exclamation"></i> <span x-text="errorMessage"></span>
                </div>

                @foreach ($methods as $method)
                    @php($gateway = $method['gateway'])

                    <div x-show="selectedGateway === '{{ $gateway }}'" class="space-y-4" x-cloak>
                        @if ($gateway === 'bank_transfer')
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-sm text-emerald-900 space-y-2">
                                <p class="font-semibold"><i class="fa-solid fa-building-columns me-1"></i> Wire transfer details (SWIFT / T-T)</p>

                                @if ($bankDetails)
                                    <div class="text-xs space-y-1 font-mono bg-white p-3 rounded-lg border border-emerald-200">
                                        @foreach ($bankDetails as $label => $value)
                                            <div><strong>{{ $label }}:</strong> {{ $value }}</div>
                                        @endforeach
                                        <div><strong>Reference code:</strong> {{ $order->reference }}</div>
                                    </div>
                                @else
                                    <p class="text-xs">
                                        Our finance team will email the beneficiary account details along with your
                                        proforma invoice. Quote order reference <strong>{{ $order->reference }}</strong>
                                        on the transfer.
                                    </p>
                                @endif

                                @if ($method['panel_note'] ?? null)
                                    <p class="text-xs">{{ $method['panel_note'] }}</p>
                                @endif
                            </div>

                            <div class="flex gap-3">
                                <a href="{{ route('payment.proforma', $order) }}" target="_blank" class="flex-1 py-3 px-4 rounded-xl border border-slate-300 text-slate-700 font-semibold text-center hover:bg-slate-50 transition text-sm">
                                    <i class="fa-solid fa-file-pdf me-1"></i> View Proforma Invoice
                                </a>
                                <button type="button" @click="confirmBankTransfer()" :disabled="loading"
                                    class="flex-1 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-center transition text-sm flex items-center justify-center gap-2">
                                    <span x-show="!loading">Confirm Wire Transfer</span>
                                    <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
                                </button>
                            </div>
                        @else
                            @if ($method['panel_note'] ?? null)
                                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-600">
                                    <p><i class="fa-solid fa-shield-halved text-emerald-600 me-1"></i> {{ $method['panel_note'] }}</p>
                                </div>
                            @endif

                            <button type="button" @click="{{ $gateway === 'stripe' ? 'payWithStripe()' : 'payWithRazorpay()' }}" :disabled="loading"
                                class="w-full py-3.5 px-6 rounded-xl {{ $gateway === 'stripe' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gradient-to-r from-brand-red to-brand-red-dk' }} text-white font-semibold shadow-lg hover:shadow-xl transition flex items-center justify-center gap-2">
                                <span x-show="!loading">Pay {{ $order->grand_total_label }} via {{ $method['label'] }}</span>
                                <span x-show="loading"><i class="fa-solid fa-circle-notch fa-spin"></i> Processing...</span>
                            </button>
                        @endif
                    </div>
                @endforeach

                @if (! $methods)
                    <p class="p-4 rounded-xl bg-amber-50 border border-amber-100 text-sm text-amber-900">
                        No payment method is available right now. Please contact
                        <a href="mailto:{{ setting('header.email') }}" class="font-semibold underline">{{ setting('header.email') }}</a>
                        and we will arrange settlement for order {{ $order->reference }}.
                    </p>
                @endif
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paymentForm', (initialGateway) => ({
                selectedGateway: initialGateway,
                loading: false,
                errorMessage: '',

                async payWithRazorpay() {
                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        const res = await fetch('{{ route("payment.process", $order) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ gateway: 'razorpay' })
                        });

                        const data = await res.json();

                        if (!data.success) {
                            throw new Error(data.message || 'Initialization failed.');
                        }

                        const options = {
                            ...data.intent.checkoutPayload,
                            handler: async (response) => {
                                await this.completePayment('razorpay', response);
                            },
                            modal: {
                                ondismiss: () => {
                                    this.loading = false;
                                }
                            }
                        };

                        if (typeof Razorpay !== 'undefined') {
                            const rzp = new Razorpay(options);
                            rzp.open();
                        } else {
                            // Fallback simulation for test environment
                            await this.completePayment('razorpay', { razorpay_payment_id: 'pay_rzp_sim_' + Date.now() });
                        }
                    } catch (e) {
                        this.errorMessage = e.message;
                        this.loading = false;
                    }
                },

                async payWithStripe() {
                    this.loading = true;
                    this.errorMessage = '';

                    try {
                        const res = await fetch('{{ route("payment.process", $order) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ gateway: 'stripe' })
                        });

                        const data = await res.json();
                        if (!data.success) throw new Error(data.message || 'Stripe init failed.');

                        await this.completePayment('stripe', { payment_intent_id: data.intent.gatewayPaymentId });
                    } catch (e) {
                        this.errorMessage = e.message;
                        this.loading = false;
                    }
                },

                async confirmBankTransfer() {
                    this.loading = true;
                    await this.completePayment('bank_transfer', { transfer_reference: '{{ $order->reference }}' });
                },

                async completePayment(gateway, payload) {
                    try {
                        const res = await fetch('{{ route("payment.complete", $order) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ gateway, ...payload })
                        });

                        const data = await res.json();
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            throw new Error(data.message || 'Payment confirmation failed.');
                        }
                    } catch (e) {
                        this.errorMessage = e.message;
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-layouts.bare>
