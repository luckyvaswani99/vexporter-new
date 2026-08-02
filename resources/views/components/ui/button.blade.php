@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'btn-primary text-white shadow-sm hover:shadow-xl hover:shadow-red-500/30',
        'outline' => 'bg-white text-brand-red border-2 border-brand-red hover:bg-brand-red hover:text-white',
        'dark' => 'bg-brand-dark text-white hover:bg-black',
        'white' => 'bg-white text-brand-dark hover:bg-brand-red hover:text-white',
        'ghost' => 'bg-white/10 backdrop-blur-sm text-white border border-white/30 hover:bg-white/20',
        'soft' => 'bg-red-50 text-brand-red hover:bg-red-100',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-lg',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition',
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-red focus-visible:ring-offset-2',
        'disabled:opacity-60 disabled:cursor-not-allowed',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
