<x-layouts.storefront title="Verified vendors — VEXPORTER" description="Browse KYC-verified Indian manufacturers and exporters across pharma, solar and general trade.">
    <div class="bg-brand-light border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <x-nav.breadcrumbs :items="[['label' => 'Vendors']]" class="mb-4" />

            <h1 class="text-3xl font-extrabold font-display text-brand-dark mb-2">Verified vendors</h1>
            <p class="text-gray-500 max-w-2xl">
                Every vendor is KYC-checked with certifications verified before their store goes live.
            </p>
        </div>
    </div>

    <section class="py-10 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-8">
                <input
                    type="search" name="q" value="{{ request()->query('q') }}" placeholder="Search vendors..."
                    class="rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-brand-red focus:outline-none"
                >

                <select name="vertical" class="rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                    <option value="">All verticals</option>
                    @foreach ($verticals as $vertical)
                        <option value="{{ $vertical->slug }}" @selected(request()->query('vertical') === $vertical->slug)>{{ $vertical->name }}</option>
                    @endforeach
                </select>

                <select name="certification" class="rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                    <option value="">Any certification</option>
                    @foreach ($certifications as $certification)
                        <option value="{{ $certification }}" @selected(request()->query('certification') === $certification)>{{ $certification }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <select name="country" class="flex-1 rounded-xl border-2 border-gray-200 px-4 py-3 text-sm focus:border-brand-red focus:outline-none">
                        <option value="">All countries</option>
                        @foreach ($countries as $code => $name)
                            <option value="{{ $code }}" @selected(request()->query('country') === $code)>{{ $name }}</option>
                        @endforeach
                    </select>

                    <x-ui.button type="submit" size="sm" class="!rounded-xl">Filter</x-ui.button>
                </div>
            </form>

            @if ($vendors->isEmpty())
                <div class="bg-brand-light rounded-2xl py-20 text-center">
                    <i class="fas fa-store-slash text-4xl text-gray-200 mb-4"></i>
                    <p class="text-gray-500">No vendors match those filters yet.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($vendors as $vendor)
                        <x-vendor.card :vendor="$vendor" />
                    @endforeach
                </div>

                <div class="mt-10">{{ $vendors->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
