@php use App\Support\Money; @endphp

<div class="space-y-4 text-sm">
    <table class="w-full">
        <thead class="text-xs uppercase tracking-wide text-gray-400">
            <tr>
                <th class="text-left py-2">Item</th>
                <th class="text-right py-2">Qty</th>
                <th class="text-right py-2">Unit price</th>
                <th class="text-right py-2">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr class="border-t border-gray-100 dark:border-gray-700">
                    <td class="py-2">{{ $item->description }}</td>
                    <td class="py-2 text-right">{{ number_format($item->qty) }} {{ $item->unit }}</td>
                    <td class="py-2 text-right">{{ Money::format($item->unit_price, $quote->currency) }}</td>
                    <td class="py-2 text-right font-medium">{{ Money::format($item->total, $quote->currency) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <dl class="space-y-1 border-t border-gray-100 dark:border-gray-700 pt-3">
        <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>{{ Money::format($quote->subtotal, $quote->currency) }}</dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Freight</dt><dd>{{ Money::format($quote->shipping, $quote->currency) }}</dd></div>
        <div class="flex justify-between"><dt class="text-gray-500">Duties / tax</dt><dd>{{ Money::format($quote->tax, $quote->currency) }}</dd></div>
        <div class="flex justify-between font-bold text-base pt-2"><dt>Total</dt><dd>{{ Money::format($quote->total, $quote->currency) }}</dd></div>
    </dl>

    @if ($quote->payment_terms || $quote->notes)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-1">
            @if ($quote->payment_terms)
                <p><span class="text-gray-500">Payment terms:</span> {{ $quote->payment_terms }}</p>
            @endif
            @if ($quote->notes)
                <p class="text-gray-600 dark:text-gray-300">{{ $quote->notes }}</p>
            @endif
        </div>
    @endif
</div>
