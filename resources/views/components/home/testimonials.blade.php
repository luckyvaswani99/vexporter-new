@props(['testimonials'])

<section class="py-20 bg-brand-light">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-ui.section-heading
            :eyebrow="setting('home.testimonials.eyebrow')"
            :title="setting('home.testimonials.title')"
            class="mb-14"
        />

        <div class="grid md:grid-cols-3 gap-8">
            @foreach ($testimonials as $testimonial)
                <figure class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                    <x-product.rating :rating="$testimonial->rating" size="sm" class="mb-4" />

                    <blockquote class="text-gray-600 mb-6 leading-relaxed">"{{ $testimonial->body }}"</blockquote>

                    <figcaption class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br {{ $testimonial->avatar_gradient }} rounded-full flex items-center justify-center text-white font-bold shrink-0">
                            {{ $testimonial->initials }}
                        </div>
                        <div>
                            <p class="font-semibold text-brand-dark text-sm">{{ $testimonial->name }}</p>
                            <p class="text-xs text-gray-500">{{ $testimonial->designation }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
