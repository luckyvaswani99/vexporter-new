<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative bg-gradient-to-r from-brand-red to-red-700 rounded-3xl p-10 sm:p-16 text-white overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-black/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/3" aria-hidden="true"></div>

            <div class="relative z-10 text-center max-w-3xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-extrabold font-display mb-5">{{ setting('home.vendor_cta.title') }}</h2>

                @if ($subtitle = setting('home.vendor_cta.subtitle'))
                    <p class="text-red-100 text-lg mb-8">{{ $subtitle }}</p>
                @endif

                <div class="flex flex-wrap justify-center gap-4">
                    @if ($label = setting('home.vendor_cta.primary_label'))
                        <x-ui.button :href="setting('home.vendor_cta.primary_url', '#')" variant="white" size="lg" class="font-bold">
                            {{ $label }} <i class="fas fa-arrow-right"></i>
                        </x-ui.button>
                    @endif

                    @if ($label = setting('home.vendor_cta.secondary_label'))
                        <x-ui.button :href="setting('home.vendor_cta.secondary_url', '#')" variant="ghost" size="lg" class="font-bold">
                            {{ $label }}
                        </x-ui.button>
                    @endif
                </div>

                @if ($bullets = setting('home.vendor_cta.bullets', []))
                    <div class="flex flex-wrap items-center justify-center gap-8 mt-10 text-sm">
                        @foreach ($bullets as $bullet)
                            <span class="flex items-center gap-2">
                                <i class="fas fa-circle-check"></i>
                                {{ str_replace(':commission', (string) (int) config('vexporter.commission_percent'), (string) $bullet) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
