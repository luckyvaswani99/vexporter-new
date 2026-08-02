@props(['rating' => 0, 'count' => null, 'size' => 'xs'])

@php
    $rating = (float) $rating;
    $full = (int) floor($rating);
    $half = ($rating - $full) >= 0.25 && ($rating - $full) < 0.75;
    $roundedUp = ($rating - $full) >= 0.75;
    $filled = $roundedUp ? $full + 1 : $full;
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1']) }}>
    <div class="flex text-yellow-400 text-{{ $size }}" role="img" aria-label="{{ number_format($rating, 1) }} out of 5">
        @for ($i = 1; $i <= 5; $i++)
            @if ($i <= $filled)
                <i class="fas fa-star"></i>
            @elseif ($i === $filled + 1 && $half)
                <i class="fas fa-star-half-alt"></i>
            @else
                <i class="far fa-star"></i>
            @endif
        @endfor
    </div>

    <span class="text-{{ $size }} text-gray-400">
        ({{ number_format($rating, 1) }}{{ $count ? ' · ' . $count : '' }})
    </span>
</div>
