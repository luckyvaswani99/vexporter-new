@props(['price', 'compareAt' => null, 'unit' => null, 'size' => 'lg'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-between']) }}>
    <div>
        <span class="text-{{ $size }} font-bold text-brand-red">{{ $price }}</span>

        @if ($compareAt)
            <span class="text-sm text-gray-400 line-through ml-1">{{ $compareAt }}</span>
        @endif
    </div>

    @if ($unit)
        <span class="text-xs text-gray-500">{{ $unit }}</span>
    @endif
</div>
