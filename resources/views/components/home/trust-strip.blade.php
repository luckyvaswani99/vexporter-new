@php
    $badges = setting('home.trust.items', []);
@endphp

@if ($badges)
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($badges as $badge)
                    @php $tone = \App\Support\Homepage::tone($badge['tone'] ?? null); @endphp

                    <div class="flex items-center gap-3 justify-center">
                        <div class="w-12 h-12 {{ $tone['bg'] }} rounded-full flex items-center justify-center shrink-0">
                            <i class="fas {{ $badge['icon'] ?? 'fa-circle-check' }} {{ $tone['text'] }} text-xl"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm">{{ $badge['title'] ?? '' }}</p>
                            <p class="text-xs text-gray-500">{{ $badge['subtitle'] ?? '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
