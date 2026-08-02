@php
    use App\Support\Countries;
    use App\Support\Money;
@endphp

<div class="space-y-4 text-sm">
    <p class="text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $rfq->description }}</p>

    <dl class="grid grid-cols-2 gap-4">
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Quantity</dt>
            <dd class="font-semibold">{{ number_format((int) $rfq->qty) }} {{ $rfq->unit }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Target price</dt>
            <dd class="font-semibold">{{ $rfq->target_price ? Money::format($rfq->target_price, $rfq->currency) : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Destination</dt>
            <dd class="font-semibold">{{ Countries::name($rfq->destination_country) }} · {{ $rfq->incoterm }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Needed by</dt>
            <dd class="font-semibold">{{ $rfq->delivery_by?->format('d M Y') ?? 'Flexible' }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Reference</dt>
            <dd class="font-semibold">{{ $rfq->reference }}</dd>
        </div>
        <div>
            <dt class="text-xs uppercase tracking-wide text-gray-400">Expires</dt>
            <dd class="font-semibold">{{ $rfq->expires_at?->format('d M Y') ?? '—' }}</dd>
        </div>
    </dl>

    @if ($rfq->product)
        <p class="text-xs text-gray-500">
            Requested against your listing: <span class="font-medium">{{ $rfq->product->name }}</span>
        </p>
    @endif
</div>
