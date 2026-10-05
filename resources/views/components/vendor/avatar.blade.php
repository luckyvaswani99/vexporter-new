@props(['vendor', 'class' => 'w-12 h-12 rounded-xl text-lg'])

{{-- Uploaded store logo when there is one, otherwise the coloured initial. --}}
@if ($vendor->logo_url)
    <span {{ $attributes->merge(['class' => "$class bg-white border border-gray-100 overflow-hidden flex items-center justify-center shrink-0"]) }}>
        <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="w-full h-full object-contain" loading="lazy">
    </span>
@else
    <span {{ $attributes->merge(['class' => "$class bg-gradient-to-br ".($vendor->avatar_gradient ?? 'from-gray-500 to-gray-700').' text-white flex items-center justify-center font-bold shrink-0']) }}>
        {{ $vendor->initial ?? mb_substr($vendor->name, 0, 1) }}
    </span>
@endif
