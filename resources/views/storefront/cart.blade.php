<x-layouts.storefront title="Your cart — VEXPORTER">
    <section class="py-10 bg-brand-light min-h-[60vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-8">Your cart</h1>

            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($groups->isEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 py-20 text-center">
                    <i class="fas fa-cart-shopping text-4xl text-gray-200 mb-4"></i>
                    <h2 class="text-lg font-bold text-brand-dark mb-2">Your cart is empty</h2>
                    <p class="text-gray-500 mb-6">Browse the marketplace and add products from verified vendors.</p>
                    <x-ui.button :href="route('verticals.show', 'main-store')">Start sourcing</x-ui.button>
                </div>
            @else
                <div class="grid lg:grid-cols-3 gap-8" x-data>
                    <div class="lg:col-span-2 space-y-6">
                        @foreach ($groups as $group)
                            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 bg-brand-light/50">
                                    <a href="{{ route('vendors.show', $group['vendor']) }}" class="flex items-center gap-3 font-semibold text-brand-dark hover:text-brand-red transition">
                                        <span class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $group['vendor']->avatar_gradient }} text-white flex items-center justify-center text-xs font-bold">
                                            {{ $group['vendor']->initial }}
                                        </span>
                                        {{ $group['vendor']->name }}
                                    </a>

                                    <span class="text-sm text-gray-500">
                                        Subtotal <span class="font-bold text-brand-dark">{{ \App\Support\Money::format($group['subtotal'], $currency) }}</span>
                                    </span>
                                </div>

                                @foreach ($group['items'] as $item)
                                    <div class="flex flex-wrap items-center gap-4 px-6 py-5 border-b border-gray-50 last:border-0" x-data="{ qty: {{ $item->qty }}, busy: false }">
                                        <div class="w-16 h-16 rounded-xl bg-gradient-to-br {{ $item->snapshot['image_gradient'] ?? 'from-gray-50 to-gray-100' }} flex items-center justify-center shrink-0">
                                            <i class="fas {{ $item->snapshot['icon'] ?? 'fa-box' }} text-2xl {{ $item->snapshot['icon_color'] ?? 'text-gray-300' }}"></i>
                                        </div>

                                        <div class="flex-1 min-w-[12rem]">
                                            <a href="{{ route('products.show', $item->product) }}" class="font-semibold text-brand-dark hover:text-brand-red transition">
                                                {{ $item->snapshot['name'] ?? $item->product->name }}
                                            </a>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ \App\Support\Money::format($item->unit_price, $currency) }} {{ $item->product->unit_label }}
                                                · MOQ {{ number_format($item->product->moq) }}
                                            </p>
                                        </div>

                                        <div class="flex items-center rounded-full border-2 border-gray-200 overflow-hidden">
                                            <button
                                                type="button" class="px-3 py-2 text-gray-500 hover:text-brand-red" aria-label="Decrease"
                                                @click="qty = Math.max({{ $item->product->moq }}, qty - {{ max(1, $item->product->order_increment) }}); busy = true; await $store.cart.update({{ $item->id }}, qty); window.location.reload()"
                                            >
                                                <i class="fas fa-minus text-[10px]"></i>
                                            </button>
                                            <span class="w-12 text-center text-sm font-semibold" x-text="qty"></span>
                                            <button
                                                type="button" class="px-3 py-2 text-gray-500 hover:text-brand-red" aria-label="Increase"
                                                @click="qty = qty + {{ max(1, $item->product->order_increment) }}; busy = true; await $store.cart.update({{ $item->id }}, qty); window.location.reload()"
                                            >
                                                <i class="fas fa-plus text-[10px]"></i>
                                            </button>
                                        </div>

                                        <div class="w-28 text-right font-bold text-brand-dark">
                                            {{ \App\Support\Money::format($item->unit_price * $item->qty, $currency) }}
                                        </div>

                                        <button
                                            type="button"
                                            class="text-gray-300 hover:text-brand-red transition"
                                            aria-label="Remove item"
                                            @click="await $store.cart.remove({{ $item->id }}); window.location.reload()"
                                        >
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <aside>
                        <div class="bg-white rounded-2xl border border-gray-100 p-6 sticky top-28">
                            <h2 class="font-bold text-brand-dark mb-5">Order summary</h2>

                            <dl class="space-y-3 text-sm mb-5">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Subtotal</dt>
                                    <dd class="font-semibold text-brand-dark">{{ \App\Support\Money::format($subtotal, $currency) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Vendors</dt>
                                    <dd class="font-semibold text-brand-dark">{{ $groups->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Freight</dt>
                                    <dd class="text-gray-400">Quoted at checkout</dd>
                                </div>
                            </dl>

                            <p class="text-xs text-gray-400 mb-5">
                                Your order is split into one shipment per vendor. Payment is held in escrow until you
                                confirm delivery.
                            </p>

                            <x-ui.button :href="route('checkout')" size="lg" class="w-full">
                                Proceed to checkout <i class="fas fa-arrow-right"></i>
                            </x-ui.button>

                            <a href="{{ route('rfq.create') }}" class="block text-center mt-4 text-sm font-semibold text-brand-red hover:underline">
                                Need bulk pricing? Request a quote
                            </a>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
