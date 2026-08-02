@props(['facets', 'sorts'])

@php
    $selectedVendors = (array) request()->query('vendor', []);
    $selectedCategories = (array) request()->query('category', []);
    $selectedCertifications = (array) request()->query('certification', []);
@endphp

<form method="GET" x-data="{ }" class="space-y-6">
    {{-- Keep the search term and sort when filters change. --}}
    @if (request()->filled('q'))
        <input type="hidden" name="q" value="{{ request()->query('q') }}">
    @endif
    @if (request()->filled('sort'))
        <input type="hidden" name="sort" value="{{ request()->query('sort') }}">
    @endif

    <div>
        <h3 class="font-bold text-brand-dark mb-3 text-sm uppercase tracking-wide">Price (USD)</h3>
        <div class="flex items-center gap-2">
            <input
                type="number" name="min_price" min="0" placeholder="{{ $facets['price']['min'] }}"
                value="{{ request()->query('min_price') }}"
                class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-brand-red focus:outline-none"
            >
            <span class="text-gray-300">–</span>
            <input
                type="number" name="max_price" min="0" placeholder="{{ $facets['price']['max'] }}"
                value="{{ request()->query('max_price') }}"
                class="w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-brand-red focus:outline-none"
            >
        </div>
    </div>

    @if ($facets['categories']->isNotEmpty())
        <div>
            <h3 class="font-bold text-brand-dark mb-3 text-sm uppercase tracking-wide">Category</h3>
            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @foreach ($facets['categories'] as $category)
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input
                            type="checkbox" name="category[]" value="{{ $category['slug'] }}"
                            @checked(in_array($category['slug'], $selectedCategories))
                            class="rounded border-gray-300 text-brand-red focus:ring-brand-red"
                        >
                        <span class="flex-1">{{ $category['name'] }}</span>
                        <span class="text-xs text-gray-400">{{ $category['count'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if ($facets['certifications']->isNotEmpty())
        <div>
            <h3 class="font-bold text-brand-dark mb-3 text-sm uppercase tracking-wide">Certification</h3>
            <div class="space-y-2">
                @foreach ($facets['certifications'] as $certification)
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input
                            type="checkbox" name="certification[]" value="{{ $certification['type'] }}"
                            @checked(in_array($certification['type'], $selectedCertifications))
                            class="rounded border-gray-300 text-brand-red focus:ring-brand-red"
                        >
                        <span class="flex-1">{{ $certification['type'] }}</span>
                        <span class="text-xs text-gray-400">{{ $certification['count'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    @if ($facets['vendors']->isNotEmpty())
        <div>
            <h3 class="font-bold text-brand-dark mb-3 text-sm uppercase tracking-wide">Vendor</h3>
            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @foreach ($facets['vendors'] as $vendor)
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input
                            type="checkbox" name="vendor[]" value="{{ $vendor['slug'] }}"
                            @checked(in_array($vendor['slug'], $selectedVendors))
                            class="rounded border-gray-300 text-brand-red focus:ring-brand-red"
                        >
                        <span class="flex-1 truncate">{{ $vendor['name'] }}</span>
                        <span class="text-xs text-gray-400">{{ $vendor['count'] }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h3 class="font-bold text-brand-dark mb-3 text-sm uppercase tracking-wide">Rating</h3>
        <div class="space-y-2">
            @foreach ([4.5, 4, 3.5] as $rating)
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input
                        type="radio" name="rating" value="{{ $rating }}"
                        @checked((float) request()->query('rating') === (float) $rating)
                        class="border-gray-300 text-brand-red focus:ring-brand-red"
                    >
                    <x-product.rating :rating="$rating" />
                    <span>&amp; up</span>
                </label>
            @endforeach
        </div>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
        <input
            type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))
            class="rounded border-gray-300 text-brand-red focus:ring-brand-red"
        >
        In stock only
    </label>

    <div class="flex gap-2 pt-2">
        <x-ui.button type="submit" size="sm" class="flex-1">Apply filters</x-ui.button>
        <x-ui.button :href="url()->current()" variant="outline" size="sm">Reset</x-ui.button>
    </div>
</form>
