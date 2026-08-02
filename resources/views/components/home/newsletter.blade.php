<section class="py-16 bg-brand-dark">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">{{ setting('home.newsletter.title') }}</h2>
                <p class="text-gray-400">{{ setting('home.newsletter.subtitle') }}</p>
            </div>

            <form
                x-data="newsletterForm('{{ route('newsletter.store') }}')"
                @submit.prevent="submit()"
                class="flex gap-3"
            >
                <label for="newsletter-email" class="sr-only">Email address</label>
                <input
                    id="newsletter-email"
                    type="email"
                    x-model="email"
                    required
                    placeholder="{{ setting('home.newsletter.placeholder') }}"
                    class="flex-1 px-5 py-4 rounded-xl bg-white/10 border border-white/10 text-white placeholder-gray-400 focus:outline-none focus:border-brand-red transition"
                >

                <button
                    type="submit"
                    :disabled="busy"
                    class="btn-primary text-white px-8 py-4 rounded-xl font-semibold hover:shadow-lg transition whitespace-nowrap disabled:opacity-60"
                >
                    <span x-show="! busy">{{ setting('home.newsletter.button_label') }}</span>
                    <span x-show="busy" x-cloak><i class="fas fa-circle-notch fa-spin"></i></span>
                </button>
            </form>
        </div>
    </div>
</section>
