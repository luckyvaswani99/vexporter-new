@props(['label', 'tone' => 'red'])

@php
    $tones = [
        'red' => 'bg-brand-red text-white',
        'green' => 'bg-green-500 text-white',
        'dark' => 'bg-brand-dark text-white',
        'orange' => 'bg-brand-accent text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'text-xs font-bold px-2.5 py-1 rounded-lg ' . ($tones[$tone] ?? $tones['red'])]) }}>
    {{ $label }}
</span>
