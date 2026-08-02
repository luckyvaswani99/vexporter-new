@props(['verticals'])

<section id="main" class="py-20 bg-brand-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.section-heading
            :eyebrow="setting('home.categories.eyebrow')"
            :title="setting('home.categories.title')"
            :subtitle="setting('home.categories.subtitle')"
            class="mb-14"
        />

        <div class="grid md:grid-cols-3 gap-8">
            @foreach ($verticals as $vertical)
                <div class="category-card group bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl border border-gray-100">
                    <div class="h-52 {{ $vertical->gradient }} relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                            <i class="fas {{ $vertical->watermark_icon }} text-8xl text-white/10 group-hover:scale-110 transition-transform duration-700"></i>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-24 h-24 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                                <i class="fas {{ $vertical->icon }} text-4xl text-white"></i>
                            </div>
                        </div>
                        <p class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full">
                            {{ $vertical->products_label }}
                        </p>
                    </div>

                    <div class="p-7">
                        <h3 class="text-2xl font-bold text-brand-dark mb-2">{{ $vertical->name }}</h3>
                        <p class="text-gray-500 text-sm mb-5 leading-relaxed">{{ $vertical->tagline }}</p>

                        <div class="flex flex-wrap gap-2 mb-5">
                            @foreach ($vertical->chips as $chip)
                                <span class="{{ $vertical->chip_class }} text-xs px-3 py-1 rounded-full">{{ $chip }}</span>
                            @endforeach
                        </div>

                        <a href="{{ route('verticals.show', $vertical->slug) }}" class="inline-flex items-center gap-2 text-brand-red font-semibold hover:gap-3 transition-all">
                            Explore {{ $vertical->name }} <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
