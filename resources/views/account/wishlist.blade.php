<x-layouts.storefront title="My wishlist — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-extrabold font-display text-brand-dark">Saved products</h1>
                <a href="{{ route('account.dashboard') }}" class="text-sm font-semibold text-brand-red hover:underline">Back to account</a>
            </div>

            @if ($products->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                    <i class="far fa-heart text-3xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 mb-5">Nothing saved yet — tap the heart on any product to keep it here.</p>
                    <x-ui.button :href="route('verticals.show', 'pharma')" size="sm">Browse products</x-ui.button>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($products as $product)
                        <x-product.card :product="$product" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
