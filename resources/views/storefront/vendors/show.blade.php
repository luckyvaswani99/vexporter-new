<x-layouts.storefront :title="$vendor->name . ' — VEXPORTER vendor'" :description="$vendor->about">
    <section class="gradient-hero relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <div class="absolute top-10 right-10 w-72 h-72 bg-brand-red rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative z-10">
            <div class="flex flex-wrap items-center gap-6 text-white">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br {{ $vendor->avatar_gradient }} flex items-center justify-center text-3xl font-bold shadow-xl">
                    {{ $vendor->initial }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3 mb-1">
                        <h1 class="text-3xl font-extrabold font-display">{{ $vendor->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 bg-green-500/20 border border-green-400/30 text-green-300 text-xs font-semibold px-3 py-1 rounded-full">
                            <i class="fas fa-circle-check"></i> Verified vendor
                        </span>
                    </div>

                    <p class="text-gray-300 text-sm">
                        {{ $vendor->location }}
                        @if ($vendor->response_time_hours)
                            · replies in ~{{ $vendor->response_time_hours }}h
                        @endif
                        · {{ number_format($vendor->products_count) }} products
                    </p>

                    <div class="flex items-center gap-2 mt-3">
                        <x-product.rating :rating="$vendor->rating" :count="$vendor->reviews_count . ' reviews'" size="sm" />
                    </div>
                </div>

                <x-ui.button :href="route('rfq.create')" variant="ghost">
                    Request a quote <i class="fas fa-file-invoice"></i>
                </x-ui.button>
            </div>
        </div>
    </section>

    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 grid sm:grid-cols-3 gap-6">
            <div>
                <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Certifications</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse ($vendor->certifications as $certification)
                        <x-product.cert-chip :label="$certification" :bordered="true" />
                    @empty
                        <span class="text-sm text-gray-400">Pending publication</span>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Categories</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($categories as $category)
                        <a href="{{ route('categories.show', $category) }}" class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-md hover:bg-red-50 hover:text-brand-red transition">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <h2 class="text-xs uppercase tracking-wide text-gray-400 mb-2">Export credentials</h2>
                <p class="text-sm text-gray-600">
                    IEC {{ $vendor->iec_code ?? '—' }}<br>
                    GST {{ $vendor->gst_number ?? '—' }}
                </p>
            </div>
        </div>
    </div>

    @if ($vendor->about)
        <section class="py-8 bg-brand-light">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-gray-600 leading-relaxed max-w-3xl">{{ $vendor->about }}</p>
            </div>
        </section>
    @endif

    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <h2 class="text-2xl font-bold font-display text-brand-dark">Products</h2>

                <form method="GET" class="flex gap-2">
                    <input
                        type="search" name="q" value="{{ request()->query('q') }}" placeholder="Search this store..."
                        class="rounded-full border-2 border-gray-200 px-5 py-2.5 text-sm focus:border-brand-red focus:outline-none"
                    >
                    <x-ui.button type="submit" size="sm">Search</x-ui.button>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="bg-brand-light rounded-2xl py-16 text-center">
                    <p class="text-gray-500">This vendor has no live listings right now.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <x-product.card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $products->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
