<x-layouts.storefront :title="'Order ' . $order->reference . ' — VEXPORTER'">
    <section class="py-14 bg-brand-light min-h-[70vh]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-gray-100 p-8 sm:p-12 text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center mx-auto mb-5">
                    <i class="fas fa-circle-check text-2xl"></i>
                </div>

                <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Order placed</h1>
                <p class="text-gray-500 mb-6">
                    Reference <span class="font-semibold text-brand-dark">{{ $order->reference }}</span> ·
                    {{ $order->subOrders->count() }} vendor{{ $order->subOrders->count() === 1 ? '' : 's' }}
                </p>

                <p class="text-sm text-gray-500 max-w-lg mx-auto">
                    Each vendor now confirms their part of the order. We will email your proforma invoice with escrow
                    payment instructions, and you can follow every shipment from your account.
                </p>

                <div class="flex flex-wrap justify-center gap-3 mt-8">
                    <x-ui.button :href="route('account.orders.show', $order)">View order</x-ui.button>
                    <x-ui.button :href="route('verticals.show', 'main-store')" variant="outline">Continue sourcing</x-ui.button>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                @foreach ($order->subOrders as $subOrder)
                    <div class="px-6 py-5 border-b border-gray-50 last:border-0">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <p class="font-semibold text-brand-dark">{{ $subOrder->vendor->name }}</p>
                            <span class="text-sm text-gray-500">{{ $subOrder->reference }}</span>
                        </div>

                        @foreach ($subOrder->items as $item)
                            <div class="flex justify-between text-sm py-1">
                                <span class="text-gray-600">{{ $item->name_snapshot }} <span class="text-gray-400">× {{ number_format($item->qty) }} {{ $item->unit }}</span></span>
                                <span class="font-medium text-brand-dark">{{ \App\Support\Money::format($item->total, $order->currency) }}</span>
                            </div>
                        @endforeach

                        <div class="flex justify-between text-sm mt-3 pt-3 border-t border-gray-50">
                            <span class="text-gray-500">Incl. freight {{ \App\Support\Money::format($subOrder->shipping_total, $order->currency) }}</span>
                            <span class="font-bold text-brand-dark">{{ \App\Support\Money::format($subOrder->total, $order->currency) }}</span>
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-between items-center px-6 py-5 bg-brand-light/60">
                    <span class="font-bold text-brand-dark">Order total</span>
                    <span class="text-xl font-extrabold text-brand-red">{{ $order->grand_total_label }}</span>
                </div>
            </div>
        </div>
    </section>
</x-layouts.storefront>
