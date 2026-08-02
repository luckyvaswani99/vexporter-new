@props(['vendors', 'totalVendors' => null])

<section id="vendors" class="py-20 bg-brand-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.section-heading
            :eyebrow="setting('home.vendors.eyebrow')"
            :title="setting('home.vendors.title')"
            :subtitle="setting('home.vendors.subtitle')"
            class="mb-14"
        />

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($vendors as $vendor)
                <x-vendor.card :vendor="$vendor" />
            @endforeach
        </div>

        @if ($ctaLabel = setting('home.vendors.cta_label'))
            <div class="text-center mt-10">
                <x-ui.button :href="route('vendors.index')" variant="outline" size="md" class="!px-8 !py-3.5">
                    {{ str_replace(':count', $totalVendors ? number_format($totalVendors) . '+' : '', $ctaLabel) }}
                    <i class="fas fa-arrow-right"></i>
                </x-ui.button>
            </div>
        @endif
    </div>
</section>
