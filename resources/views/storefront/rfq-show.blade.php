<x-layouts.storefront :title="$rfq->reference . ' — quote request'">
    <section class="py-12 bg-brand-light min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 mb-6">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-sm text-gray-400">{{ $rfq->reference }}</p>
                        <h1 class="text-2xl font-extrabold font-display text-brand-dark">{{ $rfq->title }}</h1>
                    </div>

                    <span @class([
                        'text-xs font-semibold px-3 py-1.5 rounded-full',
                        'bg-yellow-50 text-yellow-600' => $rfq->status === 'open',
                        'bg-blue-50 text-blue-600' => $rfq->status === 'quoted',
                        'bg-green-50 text-green-600' => in_array($rfq->status, ['accepted', 'converted']),
                        'bg-gray-100 text-gray-500' => in_array($rfq->status, ['expired', 'rejected']),
                    ])>{{ str($rfq->status)->headline() }}</span>
                </div>

                <p class="text-gray-600 mb-6">{{ $rfq->description }}</p>

                <dl class="grid sm:grid-cols-4 gap-5 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Quantity</dt>
                        <dd class="font-semibold text-brand-dark">{{ number_format($rfq->qty) }} {{ $rfq->unit }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Incoterm</dt>
                        <dd class="font-semibold text-brand-dark">{{ $rfq->incoterm }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Destination</dt>
                        <dd class="font-semibold text-brand-dark">{{ \App\Support\Countries::name($rfq->destination_country) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs uppercase tracking-wide mb-1">Target price</dt>
                        <dd class="font-semibold text-brand-dark">{{ $rfq->target_price ? \App\Support\Money::format($rfq->target_price, $rfq->currency) : '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-brand-dark">Quotes</h2>
                    <span class="text-sm text-gray-500">{{ $rfq->vendors->count() }} vendors invited</span>
                </div>

                @forelse ($rfq->quotes as $quote)
                    <div class="px-6 py-5 border-b border-gray-50 last:border-0">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-brand-dark">
                                    <a href="{{ route('vendors.show', $quote->vendor) }}" class="hover:text-brand-red transition">
                                        {{ $quote->vendor->name }}
                                    </a>

                                    @if ($quote->status === \App\Models\Quote::STATUS_ACCEPTED)
                                        <span class="ml-2 text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-semibold">Accepted</span>
                                    @elseif ($quote->status === \App\Models\Quote::STATUS_REVISED)
                                        <span class="ml-2 text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-semibold">Revised</span>
                                    @elseif ($quote->status === \App\Models\Quote::STATUS_REJECTED)
                                        <span class="ml-2 text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-semibold">Closed</span>
                                    @endif
                                </p>

                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $quote->reference }} · lead time {{ $quote->lead_time_days ?? '—' }} days
                                    · valid until {{ $quote->validity_until?->format('d M Y') ?? '—' }}
                                </p>

                                @if ($quote->payment_terms)
                                    <p class="text-xs text-gray-500 mt-1">Payment: {{ $quote->payment_terms }}</p>
                                @endif
                            </div>

                            <div class="text-right">
                                <p class="text-lg font-bold text-brand-red">{{ $quote->total_label }}</p>
                                <p class="text-xs text-gray-400">{{ $quote->incoterm }}</p>
                            </div>
                        </div>

                        <table class="w-full text-sm mt-4 border border-gray-100 rounded-xl overflow-hidden">
                            <tbody>
                                @foreach ($quote->items as $item)
                                    <tr class="border-b border-gray-50 last:border-0">
                                        <td class="px-4 py-2 text-gray-600">{{ $item->description }}</td>
                                        <td class="px-4 py-2 text-right text-gray-500">{{ number_format($item->qty) }} {{ $item->unit }}</td>
                                        <td class="px-4 py-2 text-right text-gray-500">{{ \App\Support\Money::format($item->unit_price, $quote->currency) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-brand-dark">{{ \App\Support\Money::format($item->total, $quote->currency) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-brand-light/60">
                                    <td class="px-4 py-2 text-gray-500" colspan="3">Freight &amp; duties</td>
                                    <td class="px-4 py-2 text-right text-gray-600">
                                        {{ \App\Support\Money::format($quote->shipping + $quote->tax, $quote->currency) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @if ($quote->notes)
                            <p class="text-sm text-gray-500 mt-3">{{ $quote->notes }}</p>
                        @endif

                        @if (in_array($quote->status, [\App\Models\Quote::STATUS_SENT, \App\Models\Quote::STATUS_REVISED], true) && ! $quote->isExpired())
                            <form method="POST" action="{{ route('account.quotes.accept', [$rfq, $quote]) }}" class="mt-4">
                                @csrf
                                <x-ui.button type="submit" size="sm">
                                    Accept this quote <i class="fas fa-check"></i>
                                </x-ui.button>
                                <span class="ml-3 text-xs text-gray-400">Creates a confirmed order with {{ $quote->vendor->name }}.</span>
                            </form>
                        @elseif ($quote->isExpired() && $quote->status !== \App\Models\Quote::STATUS_ACCEPTED)
                            <p class="mt-4 text-xs text-brand-red">This quote has expired — ask the vendor to revise it.</p>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <i class="fas fa-hourglass-half text-3xl text-gray-200 mb-3"></i>
                        <p class="text-gray-500">
                            No quotes yet. Vendors typically respond within one business day — we will email you as
                            soon as the first quote arrives.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                <a href="{{ route('account.rfqs') }}" class="text-sm font-semibold text-brand-red hover:underline">
                    <i class="fas fa-arrow-left text-xs"></i> All quote requests
                </a>
            </div>
        </div>
    </section>
</x-layouts.storefront>
