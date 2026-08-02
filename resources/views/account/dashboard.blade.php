<x-layouts.storefront title="My account — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-extrabold font-display text-brand-dark">Hello, {{ $user->name }}</h1>
                    <p class="text-gray-500">{{ $user->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="outline" size="sm">
                        <i class="fas fa-arrow-right-from-bracket"></i> Sign out
                    </x-ui.button>
                </form>
            </div>

            @if (! $user->hasVerifiedEmail())
                <div class="mb-6 rounded-2xl bg-yellow-50 border border-yellow-200 px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-triangle-exclamation mr-1"></i>
                        Verify your email address to unlock checkout and quote requests.
                    </p>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-yellow-900 hover:underline">Resend link</button>
                    </form>
                </div>
            @endif

            @if ($vendor)
                <div class="mb-8 rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $vendor->avatar_gradient }} text-white flex items-center justify-center font-bold text-lg">
                            {{ $vendor->initial }}
                        </div>
                        <div>
                            <p class="font-bold text-brand-dark">{{ $vendor->name }}</p>
                            <p class="text-sm text-gray-500">
                                Vendor account ·
                                <span @class([
                                    'font-semibold',
                                    'text-green-600' => $vendor->isApproved(),
                                    'text-yellow-600' => $vendor->status === 'pending',
                                    'text-brand-red' => in_array($vendor->status, ['rejected', 'suspended']),
                                ])>{{ ucfirst($vendor->status) }}</span>
                            </p>
                        </div>
                    </div>

                    <x-ui.button :href="route('vendor.onboarding.status')" variant="outline" size="sm">
                        View application
                    </x-ui.button>
                </div>
            @endif

            <div class="grid sm:grid-cols-3 gap-6 mb-8">
                @foreach ([
                    ['label' => 'Orders placed', 'value' => $stats['orders'], 'icon' => 'fa-box', 'tone' => 'bg-blue-50 text-blue-600'],
                    ['label' => 'Open quote requests', 'value' => $stats['open_rfqs'], 'icon' => 'fa-file-invoice', 'tone' => 'bg-orange-50 text-orange-600'],
                    ['label' => 'Saved products', 'value' => $stats['wishlist'], 'icon' => 'fa-heart', 'tone' => 'bg-red-50 text-brand-red'],
                ] as $stat)
                    <div class="bg-white rounded-2xl border border-gray-100 p-6 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl {{ $stat['tone'] }} flex items-center justify-center">
                            <i class="fas {{ $stat['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-brand-dark">{{ number_format($stat['value']) }}</p>
                            <p class="text-sm text-gray-500">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-brand-dark">Recent orders</h2>
                    <a href="{{ route('track-order') }}" class="text-sm font-semibold text-brand-red hover:underline">Track a shipment</a>
                </div>

                @forelse ($orders as $order)
                    <div class="px-6 py-4 border-b border-gray-50 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-brand-dark text-sm">{{ $order->reference }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $order->placed_at?->format('d M Y') }} · {{ $order->subOrders->count() }} vendor(s)
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-sm font-bold text-brand-dark">{{ $order->grand_total_label }}</span>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                                {{ str($order->status)->headline() }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-box-open text-3xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500 mb-4">No orders yet — your sourcing starts here.</p>
                        <x-ui.button :href="route('verticals.show', 'main-store')" size="sm">Browse the marketplace</x-ui.button>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.storefront>
