@props(['vendor'])

@php
    // Full class strings (never interpolated) so Tailwind's scanner sees them.
    $tagTones = [
        'blue' => 'bg-blue-50 text-blue-600',
        'orange' => 'bg-orange-50 text-orange-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'green' => 'bg-green-50 text-green-600',
        'red' => 'bg-red-50 text-brand-red',
        'gray' => 'bg-gray-100 text-gray-600',
    ];

    $tagClass = $tagTones[$vendor->tag_tone ?? 'gray'] ?? $tagTones['gray'];
@endphp

<article {{ $attributes->merge(['class' => 'vendor-card bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-xl transition']) }}>
    <div class="flex items-center gap-4 mb-4">
        <div class="w-16 h-16 bg-gradient-to-br {{ $vendor->avatar_gradient ?? 'from-gray-500 to-gray-700' }} rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shrink-0">
            {{ $vendor->initial ?? mb_substr($vendor->name, 0, 1) }}
        </div>
        <div class="min-w-0">
            <h3 class="font-bold text-brand-dark truncate">
                <a href="{{ route('vendors.show', $vendor->slug) }}" class="hover:text-brand-red transition">{{ $vendor->name }}</a>
            </h3>
            <p class="text-xs text-gray-500">{{ $vendor->location }}</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2 mb-4">
        @foreach ($vendor->tags as $tag)
            <span class="{{ $tagClass }} text-xs px-2.5 py-1 rounded-md font-medium">{{ $tag }}</span>
        @endforeach
    </div>

    <div class="flex items-center justify-between text-sm mb-4">
        <span class="text-gray-500">
            <i class="fas fa-box text-gray-300 mr-1"></i> {{ number_format($vendor->products_count) }} Products
        </span>
        <span class="text-yellow-500"><i class="fas fa-star"></i> {{ number_format($vendor->rating, 1) }}</span>
    </div>

    <div class="flex flex-wrap items-center gap-2 mb-4">
        @foreach ($vendor->certifications as $certification)
            <x-product.cert-chip :label="$certification" :bordered="true" />
        @endforeach
    </div>

    <x-ui.button :href="route('vendors.show', $vendor->slug)" variant="outline" size="sm" class="w-full !rounded-xl py-2.5">
        View Store
    </x-ui.button>
</article>
