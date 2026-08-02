<x-layouts.storefront title="My orders — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-extrabold font-display text-brand-dark">My orders</h1>
                <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-brand-red hover:underline">Back to account</a>
            </div>

            @forelse ($orders as $order)
                <a href="{{ route('account.orders.show', $order) }}" class="block bg-white rounded-2xl border border-gray-100 p-6 mb-4 hover:shadow-lg transition">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="font-bold text-brand-dark">{{ $order->reference }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $order->placed_at?->format('d M Y') }} ·
                                {{ $order->subOrders->count() }} vendor{{ $order->subOrders->count() === 1 ? '' : 's' }} ·
                                {{ $order->incoterm }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-lg font-bold text-brand-dark">{{ $order->grand_total_label }}</span>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                                {{ str($order->status)->headline() }}
                            </span>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                    <i class="fas fa-box-open text-3xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 mb-5">No orders yet.</p>
                    <x-ui.button :href="route('verticals.show', 'main-store')" size="sm">Start sourcing</x-ui.button>
                </div>
            @endforelse

            <div class="mt-8">{{ $orders->links() }}</div>
        </div>
    </section>
</x-layouts.storefront>
