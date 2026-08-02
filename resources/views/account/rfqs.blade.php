<x-layouts.storefront title="My quote requests — VEXPORTER">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
                <h1 class="text-3xl font-extrabold font-display text-brand-dark">Quote requests</h1>
                <x-ui.button :href="route('rfq.create')" size="sm">New request</x-ui.button>
            </div>

            @forelse ($rfqs as $rfq)
                <a href="{{ route('account.rfqs.show', $rfq) }}" class="block bg-white rounded-2xl border border-gray-100 p-6 mb-4 hover:shadow-lg transition">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="font-bold text-brand-dark">{{ $rfq->title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $rfq->reference }} · {{ number_format($rfq->qty) }} {{ $rfq->unit }} ·
                                {{ $rfq->vendors_count }} vendors invited
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-brand-dark">
                                {{ $rfq->quotes_count }} quote{{ $rfq->quotes_count === 1 ? '' : 's' }}
                            </span>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-600">
                                {{ str($rfq->status)->headline() }}
                            </span>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                    <i class="fas fa-file-invoice text-3xl text-gray-200 mb-3"></i>
                    <p class="text-gray-500 mb-5">No quote requests yet.</p>
                    <x-ui.button :href="route('rfq.create')" size="sm">Request a quote</x-ui.button>
                </div>
            @endforelse

            <div class="mt-8">{{ $rfqs->links() }}</div>
        </div>
    </section>
</x-layouts.storefront>
