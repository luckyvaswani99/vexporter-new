@props([
    'eyebrow' => null,
    'eyebrowIcon' => null,
    'title',
    'subtitle' => null,
    'align' => 'center',
    'tone' => 'red',
])

@php
    $tones = [
        'red' => 'bg-brand-red/10 text-brand-red',
        'orange' => 'bg-orange-50 text-orange-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'section-reveal ' . ($align === 'center' ? 'text-center mx-auto max-w-2xl' : '')]) }}>
    @if ($eyebrow)
        <span class="inline-flex items-center gap-2 {{ $tones[$tone] ?? $tones['red'] }} px-4 py-1.5 rounded-full text-sm font-semibold mb-4">
            @if ($eyebrowIcon)
                <i class="fas {{ $eyebrowIcon }}"></i>
            @endif
            {{ $eyebrow }}
        </span>
    @endif

    <h2 class="text-3xl sm:text-4xl font-extrabold font-display text-brand-dark mb-3">{{ $title }}</h2>

    @if ($subtitle)
        <p class="text-gray-500 leading-relaxed {{ $align === 'center' ? 'mx-auto' : 'max-w-xl' }}">{{ $subtitle }}</p>
    @endif
</div>
