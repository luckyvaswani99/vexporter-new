@props(['stats' => []])

@php
    $tiles = setting('home.hero.tiles', []);
@endphp

<section class="gradient-hero relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute top-20 left-20 w-72 h-72 bg-brand-red rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-white">
                @if ($badge = setting('home.hero.badge'))
                    <p class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium mb-6 border border-white/20">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        {{ $badge }}
                    </p>
                @endif

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold font-display leading-tight mb-6">
                    {{ setting('home.hero.title_line_1') }}
                    @if ($highlight = setting('home.hero.title_line_2'))
                        <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-orange-400">{{ $highlight }}</span>
                    @endif
                </h1>

                @if ($subtitle = setting('home.hero.subtitle'))
                    <p class="text-gray-300 text-lg mb-8 max-w-lg leading-relaxed">{{ $subtitle }}</p>
                @endif

                <div class="flex flex-wrap gap-4">
                    @if ($label = setting('home.hero.primary_label'))
                        <x-ui.button :href="setting('home.hero.primary_url', '#')" size="lg">
                            {{ $label }} <i class="fas fa-arrow-right"></i>
                        </x-ui.button>
                    @endif

                    @if ($label = setting('home.hero.secondary_label'))
                        <x-ui.button :href="setting('home.hero.secondary_url', '#')" variant="ghost" size="lg">
                            {{ $label }}
                        </x-ui.button>
                    @endif
                </div>

                @if (setting('home.hero.show_stats', true) && $stats)
                    <div class="flex items-center gap-8 mt-10">
                        @foreach ($stats as $index => $stat)
                            @if ($index > 0)
                                <div class="w-px h-12 bg-white/20" aria-hidden="true"></div>
                            @endif
                            <div>
                                <p class="text-3xl font-bold text-white">{{ $stat['value'] }}</p>
                                <p class="text-gray-400 text-sm">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if (setting('home.hero.show_tiles', true) && $tiles)
                <div class="hidden lg:block relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-brand-red/20 to-orange-500/20 rounded-3xl blur-2xl" aria-hidden="true"></div>

                    <div class="relative bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 animate-float">
                        <div class="grid grid-cols-2 gap-4">
                            @foreach ($tiles as $index => $tile)
                                @php $tone = \App\Support\Homepage::tileTone($tile['tone'] ?? null); @endphp

                                <div class="bg-white rounded-2xl p-4 shadow-2xl {{ $index % 2 ? 'mt-8' : '' }}">
                                    <div class="h-32 bg-gradient-to-br {{ $tone['gradient'] }} rounded-xl mb-3 flex items-center justify-center">
                                        <i class="fas {{ $tile['icon'] ?? 'fa-box' }} text-4xl {{ $tone['text'] }}"></i>
                                    </div>
                                    <p class="font-semibold text-sm">{{ $tile['title'] ?? '' }}</p>
                                    <p class="text-brand-red font-bold">
                                        {{ $tile['price'] ?? '' }}
                                        @if ($tile['unit'] ?? null)
                                            <span class="text-gray-400 text-xs font-normal">{{ $tile['unit'] }}</span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
