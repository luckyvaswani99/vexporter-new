@php
    $columns = setting('footer.columns', []);
    $socials = setting('footer.socials', []);
    $paymentIcons = setting('footer.payment_icons', []);
@endphp

<footer class="bg-[#111] text-gray-400 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
            <div class="lg:col-span-2">
                <x-brand.logo :href="route('home')" variant="light" size="sm" class="mb-5" />

                @if ($about = setting('footer.about'))
                    <p class="text-sm mb-5 max-w-sm leading-relaxed">{{ $about }}</p>
                @endif

                <div class="flex gap-3">
                    @foreach ($socials as $social)
                        <a
                            href="{{ $social['url'] ?? '#' }}"
                            class="w-10 h-10 bg-white/5 rounded-lg flex items-center justify-center hover:bg-brand-red hover:text-white transition"
                            aria-label="{{ $social['label'] ?? 'Social' }}"
                            rel="noopener"
                        >
                            <i class="fab {{ $social['icon'] ?? 'fa-link' }}"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            @foreach ($columns as $column)
                <div>
                    <h2 class="text-white font-semibold mb-4">{{ $column['heading'] ?? '' }}</h2>
                    <ul class="space-y-2.5 text-sm">
                        @foreach ($column['links'] ?? [] as $link)
                            <li>
                                <a href="{{ $link['url'] ?? '#' }}" class="hover:text-brand-red transition">{{ $link['label'] ?? '' }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-sm">{{ str_replace(':year', date('Y'), (string) setting('footer.copyright')) }}</p>

            <div class="flex items-center gap-6 text-sm">
                @foreach (setting('footer.legal_links', []) as $link)
                    <a href="{{ $link['url'] ?? '#' }}" class="hover:text-brand-red transition">{{ $link['label'] ?? '' }}</a>
                @endforeach
            </div>

            @if ($paymentIcons)
                <div class="flex items-center gap-3" aria-label="Accepted payment methods">
                    @foreach ($paymentIcons as $icon)
                        <i class="fab {{ $icon }} text-2xl text-white/30"></i>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</footer>
