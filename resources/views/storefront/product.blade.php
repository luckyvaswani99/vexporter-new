<x-layouts.storefront
    :title="$product->name . ' — ' . $product->vendor->name . ' | VEXPORTER'"
    :description="\App\Support\Html::toText($product->short_description, 160)"
>
    @push('head')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => \App\Support\Html::toText($product->short_description, 300),
                'brand' => ['@type' => 'Organization', 'name' => $product->vendor->name],
                'aggregateRating' => $product->reviews_count > 0 ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => (float) $product->rating_cache,
                    'reviewCount' => $product->reviews_count,
                ] : null,
                'offers' => $canSeePrice ? [
                    '@type' => 'Offer',
                    'price' => $product->base_price / 100,
                    'priceCurrency' => $product->currency,
                    'availability' => $product->stock_qty > 0
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/PreOrder',
                ] : null,
            ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) !!}
        </script>
    @endpush

    <div class="bg-brand-light border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <x-nav.breadcrumbs :items="[
                ['label' => $product->category->vertical->name, 'url' => route('verticals.show', $product->category->vertical)],
                ['label' => $product->category->name, 'url' => route('categories.show', $product->category)],
                ['label' => $product->name],
            ]" />
        </div>
    </div>

    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12">
                {{-- Gallery --}}
                <div>
                    <div class="rounded-3xl bg-gradient-to-br {{ $product->image_gradient ?? 'from-gray-50 to-gray-100' }} h-[26rem] flex items-center justify-center relative overflow-hidden">
                        @if ($product->primary_image)
                            <img src="{{ asset('storage/'.$product->primary_image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas {{ $product->icon ?? 'fa-box' }} text-9xl {{ $product->icon_color ?? 'text-gray-200' }}"></i>
                        @endif

                        @if ($product->badge)
                            <x-product.badge :label="$product->badge" :tone="$product->badge_tone ?? 'red'" class="absolute top-5 left-5" />
                        @endif
                    </div>

                    @if ($product->certificates->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach ($product->certificates as $certificate)
                                <x-product.cert-chip :label="$certificate->type" :bordered="true" />
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Buy box --}}
                <div>
                    <p class="text-sm text-gray-400 mb-1">{{ $product->category->name }}</p>
                    <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-3">{{ $product->name }}</h1>

                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <x-product.rating :rating="$product->rating" :count="$product->reviews_count . ' reviews'" size="sm" />

                        @if ($product->sku)
                            <span class="text-xs text-gray-400">SKU {{ $product->sku }}</span>
                        @endif

                        <span class="text-xs {{ $product->stock_qty > 0 ? 'text-green-600' : 'text-orange-600' }}">
                            <i class="fas fa-circle text-[6px] align-middle"></i>
                            {{ $product->stock_qty > 0 ? 'In stock' : 'Made to order' }}
                        </span>
                    </div>

                    @if ($product->short_description)
                        <div class="prose-storefront text-gray-500 leading-relaxed mb-6">{!! $product->short_description !!}</div>
                    @endif

                    <div class="rounded-2xl border-2 border-gray-100 p-6 mb-6">
                        @if (! $canSeePrice)
                            <div class="flex items-start gap-3 mb-4">
                                <i class="fas fa-shield-halved text-brand-red mt-1"></i>
                                <div>
                                    <p class="font-semibold text-brand-dark">Licence required</p>
                                    <p class="text-sm text-gray-500">
                                        Pricing for this item is shared only with buyers whose drug licence has been
                                        verified. Request a quote and our compliance team will get in touch.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-end gap-3 mb-4">
                                <span class="text-4xl font-extrabold text-brand-red">{{ $product->price_label }}</span>
                                @if ($product->compare_at_label)
                                    <span class="text-lg text-gray-400 line-through">{{ $product->compare_at_label }}</span>
                                @endif
                                <span class="text-sm text-gray-500 pb-1">{{ $product->unit_label }}</span>
                            </div>

                            @if ($product->tierPrices->isNotEmpty())
                                <table class="w-full text-sm mb-5 border border-gray-100 rounded-xl overflow-hidden">
                                    <thead class="bg-brand-light text-gray-500 text-xs uppercase tracking-wide">
                                        <tr>
                                            <th class="text-left px-4 py-2 font-semibold">Quantity ({{ $product->unit }})</th>
                                            <th class="text-right px-4 py-2 font-semibold">Unit price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($product->tierPrices as $tier)
                                            <tr class="border-t border-gray-50">
                                                <td class="px-4 py-2 text-gray-600">{{ $tier->qty_label }}</td>
                                                <td class="px-4 py-2 text-right font-semibold text-brand-dark">{{ $tier->price_label }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @endif

                        <dl class="grid grid-cols-2 gap-4 text-sm mb-5">
                            <div>
                                <dt class="text-gray-400 text-xs uppercase tracking-wide">Minimum order</dt>
                                <dd class="font-semibold text-brand-dark">{{ number_format($product->moq) }} {{ $product->unit }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-400 text-xs uppercase tracking-wide">Lead time</dt>
                                <dd class="font-semibold text-brand-dark">{{ $product->lead_time_days ? $product->lead_time_days.' days' : 'On request' }}</dd>
                            </div>
                        </dl>

                        @if ($product->is_quote_only || ! $canSeePrice)
                            <x-ui.button :href="route('rfq.create', ['product' => $product->slug])" size="lg" class="w-full">
                                Request a quote <i class="fas fa-file-invoice"></i>
                            </x-ui.button>
                        @else
                            <div
                                x-data="buyBox({{ $product->id }}, {{ $product->moq }}, {{ max(1, $product->order_increment) }}, {{ Js::from($variantOptions) }})"
                            >
                                @if ($variantOptions)
                                    <fieldset class="mb-5">
                                        <legend class="text-gray-400 text-xs uppercase tracking-wide mb-2">Options</legend>

                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="option in variants" :key="option.id">
                                                <label
                                                    class="cursor-pointer rounded-full border-2 px-4 py-2 text-sm font-semibold transition"
                                                    :class="variantId === option.id
                                                        ? 'border-brand-red bg-brand-red/5 text-brand-red'
                                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                                >
                                                    <input type="radio" x-model.number="variantId" :value="option.id" name="variant" class="sr-only">
                                                    <span x-text="option.name"></span>
                                                </label>
                                            </template>
                                        </div>

                                        <p class="mt-3 text-sm text-gray-500" x-show="priceLabel" x-cloak>
                                            <span class="font-bold text-brand-red text-lg" x-text="priceLabel"></span>
                                            <span x-show="stockLabel" x-text="stockLabel" class="ml-2 text-xs uppercase tracking-wide"></span>
                                        </p>
                                    </fieldset>
                                @endif

                                <div class="flex flex-col sm:flex-row gap-3">
                                <div class="flex items-center rounded-full border-2 border-gray-200 overflow-hidden">
                                    <button type="button" @click="decrease()" class="px-4 py-3 text-gray-500 hover:text-brand-red" aria-label="Decrease quantity">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <input
                                        type="number" x-model.number="qty" @change="normalize()"
                                        class="w-20 text-center border-0 focus:ring-0 focus:outline-none text-sm font-semibold"
                                        aria-label="Quantity"
                                    >
                                    <button type="button" @click="increase()" class="px-4 py-3 text-gray-500 hover:text-brand-red" aria-label="Increase quantity">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="add()"
                                    :disabled="busy"
                                    class="btn-primary text-white flex-1 rounded-full px-8 py-4 font-semibold transition hover:shadow-xl hover:shadow-red-500/30 disabled:opacity-60"
                                >
                                    <span x-show="! busy"><i class="fas fa-cart-plus mr-1"></i> Add to cart</span>
                                    <span x-show="busy" x-cloak><i class="fas fa-circle-notch fa-spin"></i></span>
                                </button>
                                </div>
                            </div>

                            <a href="{{ route('rfq.create', ['product' => $product->slug]) }}" class="mt-3 inline-block text-sm font-semibold text-brand-red hover:underline">
                                Need a bulk price? Request a quote instead
                            </a>
                        @endif
                    </div>

                    {{-- Vendor --}}
                    <div class="rounded-2xl border border-gray-100 p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $product->vendor->avatar_gradient }} text-white flex items-center justify-center font-bold">
                            {{ $product->vendor->initial }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('vendors.show', $product->vendor) }}" class="font-bold text-brand-dark hover:text-brand-red transition">
                                {{ $product->vendor->name }}
                            </a>
                            <p class="text-xs text-gray-500">
                                {{ $product->vendor->location }}
                                @if ($product->vendor->response_time_hours)
                                    · replies in ~{{ $product->vendor->response_time_hours }}h
                                @endif
                            </p>
                        </div>
                        <x-ui.button :href="route('vendors.show', $product->vendor)" variant="outline" size="sm">View store</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tabs --}}
    <section class="py-10 bg-brand-light">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="{ tab: 'description' }" class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="flex flex-wrap border-b border-gray-100">
                    @foreach ([
                        'description' => 'Description',
                        'specs' => 'Specifications',
                        'documents' => 'Documents',
                        'shipping' => 'Shipping & payment',
                        'reviews' => 'Reviews',
                    ] as $key => $label)
                        <button
                            type="button"
                            @click="tab = '{{ $key }}'"
                            class="px-6 py-4 text-sm font-semibold transition"
                            :class="tab === '{{ $key }}' ? 'text-brand-red border-b-2 border-brand-red' : 'text-gray-500 hover:text-brand-dark'"
                        >{{ $label }}</button>
                    @endforeach
                </div>

                <div class="p-8">
                    <div x-show="tab === 'description'" class="prose-storefront max-w-none text-gray-600">
                        {!! $product->description ?: '<p>No description provided yet.</p>' !!}
                    </div>

                    <div x-show="tab === 'specs'" x-cloak>
                        @if ($product->attributeValues->isNotEmpty())
                            <dl class="grid sm:grid-cols-2 gap-x-10 gap-y-3">
                                @foreach ($product->attributeValues as $value)
                                    <div class="flex justify-between border-b border-gray-50 py-2">
                                        <dt class="text-gray-500 text-sm">{{ $value->attribute->label }}</dt>
                                        <dd class="font-medium text-brand-dark text-sm">{{ $value->display }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-gray-500">The vendor has not published detailed specifications for this item yet.</p>
                        @endif
                    </div>

                    <div x-show="tab === 'documents'" x-cloak>
                        @if ($product->documents->isNotEmpty())
                            <ul class="divide-y divide-gray-50">
                                @foreach ($product->documents as $document)
                                    <li class="flex items-center justify-between py-3">
                                        <span class="flex items-center gap-3 text-sm">
                                            <i class="fas fa-file-lines text-gray-300"></i>
                                            {{ $document->label ?? str($document->type)->headline() }}
                                        </span>
                                        @auth
                                            <a href="{{ route('documents.product', $document) }}" class="text-sm font-semibold text-brand-red hover:underline">Download</a>
                                        @else
                                            <a href="{{ route('login') }}" class="text-sm text-gray-400">Sign in to download</a>
                                        @endauth
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">COA, MSDS and datasheets are shared on request — ask the vendor through a quote request.</p>
                        @endif
                    </div>

                    <div x-show="tab === 'shipping'" x-cloak class="grid sm:grid-cols-2 gap-8 text-sm text-gray-600">
                        <div>
                            <h3 class="font-bold text-brand-dark mb-2">Shipping</h3>
                            <p class="mb-2">Ships from {{ $product->vendor->location }}.</p>
                            <p>Freight is quoted per destination at checkout. Incoterms supported: EXW, FOB, CIF, DDP.</p>
                        </div>
                        <div>
                            <h3 class="font-bold text-brand-dark mb-2">Payment</h3>
                            <p class="mb-2">Funds are held in escrow and released to the vendor after you confirm delivery.</p>
                            <p>Bank transfer (T/T) and card payments are supported for verified buyers.</p>
                        </div>
                    </div>

                    <div x-show="tab === 'reviews'" x-cloak>
                        @forelse ($product->reviews as $review)
                            <div class="border-b border-gray-50 py-4">
                                <div class="flex items-center gap-3 mb-1">
                                    <x-product.rating :rating="$review->rating" />
                                    <span class="text-sm font-semibold text-brand-dark">{{ $review->user->name }}</span>
                                    @if ($review->is_verified_purchase)
                                        <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded-full">Verified purchase</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600">{{ $review->body }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">No reviews yet — be the first buyer to review this product.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <x-home.vertical-showcase
            eyebrow="Related"
            title="Similar products"
            cta-label="Browse {{ $product->category->name }}"
            :cta-url="route('categories.show', $product->category)"
            :products="$related"
        />
    @endif

    @if ($fromVendor->isNotEmpty())
        <x-home.vertical-showcase
            eyebrow="Same vendor"
            title="More from {{ $product->vendor->name }}"
            cta-label="Visit store"
            :cta-url="route('vendors.show', $product->vendor)"
            :products="$fromVendor"
            background="bg-brand-light"
        />
    @endif
</x-layouts.storefront>
