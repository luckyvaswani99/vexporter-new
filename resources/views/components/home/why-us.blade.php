@props(['analytics'])

@php
    $reasons = setting('home.why.reasons', []);
    $showPanel = (bool) setting('home.why.show_panel', true);
@endphp

<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid gap-16 items-center {{ $showPanel ? 'lg:grid-cols-2' : '' }}">
            <div class="section-reveal">
                @if ($eyebrow = setting('home.why.eyebrow'))
                    <span class="inline-block bg-brand-red/10 text-brand-red px-4 py-1.5 rounded-full text-sm font-semibold mb-4">{{ $eyebrow }}</span>
                @endif

                <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-brand-dark mb-6">{{ setting('home.why.title') }}</h2>

                @if ($body = setting('home.why.body'))
                    <p class="text-gray-500 mb-8 leading-relaxed">{{ $body }}</p>
                @endif

                <div class="space-y-5">
                    @foreach ($reasons as $reason)
                        @php $tone = \App\Support\Homepage::tone($reason['tone'] ?? null); @endphp

                        <div class="flex gap-4">
                            <div class="w-12 h-12 {{ $tone['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                                <i class="fas {{ $reason['icon'] ?? 'fa-circle-check' }} {{ $tone['text'] }} text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-brand-dark mb-1">{{ $reason['title'] ?? '' }}</h3>
                                <p class="text-sm text-gray-500">{{ $reason['body'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div @class(['relative hidden', 'lg:block' => $showPanel])>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-red/10 to-orange-500/10 rounded-3xl blur-2xl" aria-hidden="true"></div>

                <div class="relative bg-white rounded-3xl shadow-2xl p-8 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-lg">{{ setting('home.why.panel_title') }}</h3>
                        <span class="text-xs bg-green-50 text-green-600 px-3 py-1 rounded-full font-medium">Live</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        @foreach ($analytics->metrics as $metric)
                            <div class="bg-gray-50 rounded-xl p-4">
                                <p class="text-xs text-gray-500 mb-1">{{ $metric['label'] }}</p>
                                <p class="text-2xl font-bold text-brand-dark">{{ $metric['value'] }}</p>
                                <p class="text-xs text-green-600 mt-1"><i class="fas fa-arrow-up"></i> {{ $metric['delta'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-brand-dark rounded-xl p-4 text-white">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium">Recent Orders</span>
                            <span class="text-xs text-gray-400">Last 24h</span>
                        </div>

                        <div class="space-y-2">
                            @foreach ($analytics->recent_orders as $order)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-300">{{ $order['label'] }}</span>
                                    <span class="{{ $order['settled'] ? 'text-green-400' : 'text-yellow-400' }}">
                                        {{ $order['amount'] }}
                                        <i class="fas {{ $order['settled'] ? 'fa-circle-check' : 'fa-clock' }} text-xs ml-1"></i>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
