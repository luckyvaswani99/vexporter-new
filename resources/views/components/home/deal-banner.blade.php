@props(['deal'])

<section id="deals" class="py-20 bg-white relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-r from-brand-red/5 to-orange-500/5" aria-hidden="true"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="bg-gradient-to-r from-brand-dark to-gray-900 rounded-3xl p-8 sm:p-14 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-brand-red/20 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2" aria-hidden="true"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-orange-500/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2" aria-hidden="true"></div>

            <div class="grid lg:grid-cols-2 gap-10 items-center relative z-10">
                <div>
                    <p class="inline-flex items-center gap-2 bg-brand-red text-white px-4 py-1.5 rounded-full text-sm font-semibold mb-5">
                        <i class="fas fa-bolt animate-pulse"></i> Flash Deal
                    </p>

                    <h2 class="text-3xl sm:text-4xl font-extrabold font-display mb-4">{{ $deal->title }}</h2>
                    <p class="text-gray-300 mb-8 max-w-md">{{ $deal->description }}</p>

                    <div x-data="countdown('{{ $deal->ends_at_iso }}')" class="flex gap-4 mb-8">
                        @foreach ([['days', 'Days'], ['hours', 'Hours'], ['minutes', 'Mins'], ['seconds', 'Secs']] as [$key, $label])
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl px-6 py-4 text-center border border-white/10">
                                <p class="text-3xl font-bold {{ $key === 'seconds' ? 'text-brand-red' : '' }}" x-text="{{ $key }}">--</p>
                                <p class="text-xs text-gray-400 mt-1">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>

                    <x-ui.button :href="$deal->url" variant="white" size="lg">
                        Grab the Deal <i class="fas fa-arrow-right"></i>
                    </x-ui.button>
                </div>

                <div class="hidden lg:flex justify-center">
                    <div class="w-80 h-80 bg-white/5 rounded-full flex items-center justify-center border border-white/10 animate-pulse">
                        <div class="w-60 h-60 bg-white/10 rounded-full flex items-center justify-center border border-white/10">
                            <div class="text-center">
                                <p class="text-6xl font-extrabold text-white">{{ $deal->discount }}%</p>
                                <p class="text-xl text-gray-300 mt-2">OFF</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
