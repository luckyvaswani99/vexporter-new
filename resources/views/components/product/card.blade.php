@props(['product'])

@php
    // `$product` is a Fluent today and an Eloquent model from Phase 2 onwards —
    // both expose the same property names, so this view does not change.
    $isQuoteOnly = (bool) ($product->is_quote_only ?? false);
@endphp

<article {{ $attributes->merge(['class' => 'product-card card-hover bg-white rounded-2xl border border-gray-100 overflow-hidden group']) }}>
    <div class="relative h-56 bg-gradient-to-br {{ $product->image_gradient ?? 'from-gray-50 to-gray-100' }} flex items-center justify-center overflow-hidden">
        <a href="{{ route('products.show', $product->slug) }}" class="absolute inset-0 flex items-center justify-center" aria-label="{{ $product->name }}">
            @if ($product->primary_image)
                <img src="{{ asset('storage/'.$product->primary_image) }}" alt="{{ $product->name }}" class="product-img w-full h-full object-cover" loading="lazy">
            @else
                <i class="fas {{ $product->icon ?? 'fa-box' }} text-6xl {{ $product->icon_color ?? 'text-gray-200' }} product-img"></i>
            @endif
        </a>

        @if ($product->badge ?? false)
            <x-product.badge :label="$product->badge" :tone="$product->badge_tone ?? 'red'" class="absolute top-3 left-3" />
        @endif

        <button
            x-data
            type="button"
            @click="$store.wishlist.toggle({{ (int) $product->id }})"
            class="absolute top-3 right-3 w-9 h-9 bg-white rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-brand-red transition opacity-0 group-hover:opacity-100 focus:opacity-100"
            :class="$store.wishlist.has({{ (int) $product->id }}) && 'text-brand-red opacity-100'"
            aria-label="Add {{ $product->name }} to wishlist"
        >
            <i class="fa-heart" :class="$store.wishlist.has({{ (int) $product->id }}) ? 'fas' : 'far'"></i>
        </button>

        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition">
            @if ($isQuoteOnly)
                <a
                    href="{{ route('rfq.create', ['product' => $product->slug]) }}"
                    class="block w-full text-center bg-white text-brand-dark text-sm font-semibold py-2 rounded-lg hover:bg-brand-red hover:text-white transition"
                >
                    Get Quote
                </a>
            @else
                <div x-data="addToCart({{ (int) $product->id }})">
                    <button
                        type="button"
                        @click="submit({{ (int) ($product->moq ?? 1) }})"
                        :disabled="busy"
                        class="w-full bg-white text-brand-dark text-sm font-semibold py-2 rounded-lg hover:bg-brand-red hover:text-white transition"
                    >
                        <span x-show="! busy">Add to Cart</span>
                        <span x-show="busy" x-cloak><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="p-5">
        <p class="text-xs text-gray-400 mb-1">{{ $product->category_label }}</p>

        <h3 class="font-semibold text-brand-dark mb-2 line-clamp-1">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-brand-red transition">{{ $product->name }}</a>
        </h3>

        <x-product.rating :rating="$product->rating" class="mb-3" />

        <x-product.price
            :price="$product->price_label"
            :compare-at="$product->compare_at_label"
            :unit="$product->unit_label"
        />

        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
            <span class="w-6 h-6 {{ $product->vendor_avatar_class ?? 'bg-gray-100' }} rounded-full flex items-center justify-center">
                <i class="fas fa-building text-[10px] {{ $product->vendor_icon_class ?? 'text-gray-500' }}"></i>
            </span>

            <a href="{{ route('vendors.show', $product->vendor_slug) }}" class="text-xs text-gray-500 hover:text-brand-red transition truncate">
                {{ $product->vendor_name }}
            </a>

            @if ($product->certification ?? false)
                <x-product.cert-chip :label="$product->certification" class="ml-auto shrink-0" />
            @endif
        </div>
    </div>
</article>
