<x-layouts.storefront :title="'Order ' . $order->reference">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-brand-red hover:underline">
                <i class="fas fa-arrow-left text-xs"></i> All orders
            </a>

            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 mt-4 mb-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-extrabold font-display text-brand-dark">{{ $order->reference }}</h1>
                        <p class="text-sm text-gray-500">Placed {{ $order->placed_at?->format('d M Y, H:i') }}</p>
                    </div>

                    <div class="text-right">
                        <p class="text-2xl font-extrabold text-brand-red">{{ $order->grand_total_label }}</p>
                        <p class="text-xs text-gray-500">
                            {{ str($order->status)->headline() }} · {{ str($order->payment_status)->headline() }}
                        </p>
                    </div>
                </div>

                <dl class="grid sm:grid-cols-3 gap-5 text-sm mt-6 pt-6 border-t border-gray-100">
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Incoterm</dt>
                        <dd class="font-semibold text-brand-dark">{{ $order->incoterm ?? '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Ship to</dt>
                        <dd class="text-gray-600">
                            {{ collect($order->shipping_address ?? [])->only(['contact_name', 'line1', 'city', 'country_code'])->filter()->implode(', ') }}
                        </dd>
                    </div>
                </dl>
            </div>

            @foreach ($order->subOrders as $subOrder)
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-gray-50 bg-brand-light/50">
                        <div>
                            <a href="{{ route('vendors.show', $subOrder->vendor) }}" class="font-semibold text-brand-dark hover:text-brand-red transition">
                                {{ $subOrder->vendor->name }}
                            </a>
                            <p class="text-xs text-gray-500">{{ $subOrder->reference }}</p>
                        </div>

                        <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                            {{ str($subOrder->status)->headline() }}
                        </span>
                    </div>

                    <div class="px-6 py-4">
                        @foreach ($subOrder->items as $item)
                            <div class="flex justify-between text-sm py-1.5">
                                <span class="text-gray-600">
                                    {{ $item->name_snapshot }}
                                    <span class="text-gray-400">× {{ number_format($item->qty) }} {{ $item->unit }}</span>
                                    @if ($item->batch_no)
                                        <span class="ml-2 text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">Batch {{ $item->batch_no }}</span>
                                    @endif
                                </span>
                                <span class="font-medium text-brand-dark">{{ \App\Support\Money::format($item->total, $order->currency) }}</span>
                            </div>
                        @endforeach
                    </div>

                    @if ($subOrder->shipments->isNotEmpty())
                        <div class="px-6 py-4 border-t border-gray-50 bg-brand-light/40">
                            <h3 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Shipments</h3>
                            @foreach ($subOrder->shipments as $shipment)
                                <p class="text-sm text-gray-600">
                                    <i class="fas fa-truck text-gray-300 mr-1"></i>
                                    {{ $shipment->carrier }} · {{ $shipment->tracking_no }}
                                    <span class="text-gray-400">({{ str($shipment->status)->headline() }})</span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    @if ($subOrder->statusHistory->isNotEmpty())
                        <div class="px-6 py-4 border-t border-gray-50">
                            <h3 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Timeline</h3>
                            <ol class="space-y-1.5">
                                @foreach ($subOrder->statusHistory as $entry)
                                    <li class="text-sm text-gray-500 flex items-start gap-2">
                                        <i class="fas fa-circle text-[5px] mt-2 text-brand-red"></i>
                                        <span>
                                            {{ str($entry->to_status)->headline() }}
                                            <span class="text-gray-400">— {{ $entry->created_at->format('d M Y, H:i') }}</span>
                                            @if ($entry->note)
                                                <span class="block text-xs text-gray-400">{{ $entry->note }}</span>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.storefront>
