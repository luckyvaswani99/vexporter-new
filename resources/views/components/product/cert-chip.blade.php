@props(['label', 'bordered' => false])

@php
    $tone = config('vexporter.certifications')[$label] ?? 'gray';

    $tones = [
        'green' => 'bg-green-50 text-green-600 border-green-200',
        'blue' => 'bg-blue-50 text-blue-600 border-blue-200',
        'yellow' => 'bg-yellow-50 text-yellow-600 border-yellow-200',
        'gray' => 'bg-gray-50 text-gray-600 border-gray-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'text-[10px] px-2 py-0.5 rounded-full font-medium ' . $tones[$tone] . ($bordered ? ' border font-bold' : '')]) }}>
    {{ $label }}
</span>
