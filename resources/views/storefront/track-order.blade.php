<x-layouts.storefront title="Track Your Order & Shipment - VEXPORTER">
    <div class="bg-slate-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-extrabold font-display">Track Your Shipment</h1>
            <p class="text-slate-400 text-sm mt-2">Enter your Order Reference (e.g. VX-2026-000123) or Carrier Tracking / AWB Number</p>

            <form action="{{ route('track-order') }}" method="GET" class="max-w-xl mx-auto mt-6 flex flex-col sm:flex-row gap-3">
                <input type="text" name="query" value="{{ $query }}" placeholder="Order Ref or Tracking No." required
                    class="flex-1 px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-brand-red text-sm font-mono">
                <input type="email" name="email" value="{{ $email }}" placeholder="Buyer Email (Optional)"
                    class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-400 focus:outline-none focus:border-brand-red text-sm">
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-red hover:bg-brand-red-dk font-semibold text-white transition text-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Track
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($query && ! $order && $shipments->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-red-50 text-brand-red flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">No Shipment Found</h3>
                <p class="text-slate-500 text-sm mt-1">We couldn't find any shipment matching "<span class="font-mono font-semibold">{{ $query }}</span>". Please double-check your tracking reference or email address.</p>
            </div>
        @elseif($order)
            <div class="space-y-8">
                {{-- Order Header Card --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-400">Order Reference</span>
                        <h2 class="text-xl font-bold text-slate-900 font-mono">{{ $order->reference }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Placed on {{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                            {{ str($order->status)->headline() }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ str($order->payment_status)->headline() }}
                        </span>
                    </div>
                </div>

                {{-- Shipments Section --}}
                @foreach($order->subOrders as $subOrder)
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                            <div>
                                <span class="text-xs text-slate-400">Vendor Fulfillment</span>
                                <h3 class="font-bold text-slate-900">{{ $subOrder->vendor->name }}</h3>
                            </div>
                            <span class="text-sm font-semibold font-mono text-slate-700">{{ $subOrder->reference }}</span>
                        </div>

                        @if($subOrder->shipments->isNotEmpty())
                            @foreach($subOrder->shipments as $shipment)
                                <div class="mb-6 last:mb-0 bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-600">
                                        <div>Carrier: <strong class="text-slate-900">{{ $shipment->carrier }}</strong> ({{ $shipment->service }})</div>
                                        <div>Tracking No: <strong class="font-mono text-slate-900">{{ $shipment->tracking_no }}</strong></div>
                                        <div>Incoterm: <strong class="text-slate-900">{{ $shipment->incoterm ?? 'CIF' }}</strong></div>
                                    </div>

                                    {{-- Milestone Progress Bar --}}
                                    <div class="relative py-2">
                                        <div class="flex justify-between mb-1 text-xs font-semibold text-slate-700">
                                            <span :class="'{{ $shipment->status }}' !== 'pending' ? 'text-brand-red' : ''">Booked</span>
                                            <span :class="['in_transit', 'customs', 'out_for_delivery', 'delivered'].includes('{{ $shipment->status }}') ? 'text-brand-red' : ''">In Transit</span>
                                            <span :class="['customs', 'out_for_delivery', 'delivered'].includes('{{ $shipment->status }}') ? 'text-brand-red' : ''">Customs Clear</span>
                                            <span :class="'{{ $shipment->status }}' === 'delivered' ? 'text-emerald-600 font-bold' : ''">Delivered</span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-brand-red to-emerald-500 h-2 rounded-full transition-all duration-500"
                                                style="width: {{ match($shipment->status) { 'pending' => '25%', 'picked' => '40%', 'in_transit' => '60%', 'customs' => '75%', 'out_for_delivery' => '90%', 'delivered' => '100%', default => '15%' } }}"></div>
                                        </div>
                                    </div>

                                    {{-- Timeline Events --}}
                                    @if($shipment->events->isNotEmpty())
                                        <div class="border-t border-slate-200 pt-3">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tracking History</h4>
                                            <div class="space-y-2">
                                                @foreach($shipment->events as $event)
                                                    <div class="flex items-start gap-3 text-xs">
                                                        <span class="w-2 h-2 rounded-full bg-brand-red mt-1"></span>
                                                        <div class="flex-1">
                                                            <span class="font-semibold text-slate-800">{{ str($event->status)->headline() }}</span>
                                                            @if($event->location)<span class="text-slate-400"> • {{ $event->location }}</span>@endif
                                                            <p class="text-slate-500">{{ $event->description }}</p>
                                                        </div>
                                                        <span class="text-slate-400 font-mono">{{ $event->happened_at->format('d M, H:i') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-xs text-slate-500 italic bg-slate-50 p-4 rounded-xl text-center">
                                Vendor is processing items for dispatch. Tracking details will be generated once handed to carrier.
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 font-display">Track Cargo & Shipments</h3>
                <p class="text-slate-500 text-sm mt-1 max-w-md mx-auto">Enter your VEXPORTER order reference or carrier air waybill / bill of lading number above to get real-time status updates.</p>
            </div>
        @endif
    </div>
</x-layouts.storefront>
