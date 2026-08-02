@if ($products->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 py-20 text-center">
        <i class="fas fa-box-open text-4xl text-gray-200 mb-4"></i>
        <h2 class="text-lg font-bold text-brand-dark mb-2">No products match those filters</h2>
        <p class="text-gray-500 mb-6">Try widening the price range or clearing a filter.</p>
        <x-ui.button :href="url()->current()" variant="outline" size="sm">Clear filters</x-ui.button>
    </div>
@else
    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($products as $product)
            <x-product.card :product="$product" />
        @endforeach
    </div>

    <div class="mt-10">
        {{ $products->links() }}
    </div>
@endif
