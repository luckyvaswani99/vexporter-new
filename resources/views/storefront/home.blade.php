<x-layouts.storefront :title="setting('home.seo.meta_title')" :description="setting('home.seo.meta_description')">
    @foreach (\App\Support\Homepage::orderedSections() as $section)
        @switch ($section)
            @case ('hero')
                <x-home.hero :stats="$heroStats" />
                @break

            @case ('trust')
                <x-home.trust-strip />
                @break

            @case ('categories')
                <x-home.category-trio :verticals="$verticals" />
                @break

            @case ('pharma')
                <x-home.vertical-showcase
                    id="pharma"
                    group="pharma"
                    :cta-url="route('verticals.show', 'pharma')"
                    :products="$pharmaProducts"
                />
                @break

            @case ('solar')
                <x-home.vertical-showcase
                    id="solar"
                    group="solar"
                    tone="orange"
                    :cta-url="route('verticals.show', 'solar')"
                    :products="$solarProducts"
                    background="bg-brand-light"
                />
                @break

            @case ('deal')
                @if ($flashDeal)
                    <x-home.deal-banner :deal="$flashDeal" />
                @endif
                @break

            @case ('vendors')
                <x-home.vendor-strip :vendors="$topVendors" :total-vendors="$totalVendors" />
                @break

            @case ('why')
                <x-home.why-us :analytics="$analytics" />
                @break

            @case ('testimonials')
                <x-home.testimonials :testimonials="$testimonials" />
                @break

            @case ('vendor_cta')
                <x-home.vendor-cta />
                @break

            @case ('newsletter')
                <x-home.newsletter />
                @break
        @endswitch
    @endforeach
</x-layouts.storefront>
