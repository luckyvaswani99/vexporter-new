<x-layouts.storefront :title="$heading . ' — VEXPORTER'">
    <section class="py-24 bg-brand-light">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="w-20 h-20 mx-auto mb-6 rounded-2xl gradient-hero flex items-center justify-center">
                <i class="fas fa-helmet-safety text-3xl text-white"></i>
            </div>

            <h1 class="text-3xl sm:text-4xl font-extrabold font-display text-brand-dark mb-4">{{ $heading }}</h1>

            <p class="text-gray-500 mb-8">
                {{ $note ?? 'This section is being built as part of the VEXPORTER rollout. The navigation is already wired up so nothing breaks while the page is finished.' }}
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <x-ui.button :href="route('home')">
                    <i class="fas fa-arrow-left"></i> Back to home
                </x-ui.button>

                <x-ui.button :href="route('contact')" variant="outline">Talk to sales</x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.storefront>
