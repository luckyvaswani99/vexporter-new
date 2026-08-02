@props([
    'id' => null,
    // Either read the copy from a homepage settings group, or pass it in
    // directly — the product page reuses this block for related products.
    'group' => null,
    'eyebrow' => null,
    'eyebrowIcon' => null,
    'title' => null,
    'subtitle' => null,
    'ctaLabel' => null,
    'tone' => 'red',
    'ctaUrl',
    'products',
    'background' => 'bg-white',
])

@php
    $copy = $group ? setting("home.{$group}", []) : [];

    $eyebrow ??= $copy['eyebrow'] ?? null;
    $eyebrowIcon ??= $copy['eyebrow_icon'] ?? null;
    $title ??= $copy['title'] ?? '';
    $subtitle ??= $copy['subtitle'] ?? null;
    $ctaLabel ??= $copy['cta_label'] ?? null;
@endphp

<section @if ($id) id="{{ $id }}" @endif class="py-20 {{ $background }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-14 gap-6">
            <x-ui.section-heading
                :eyebrow="$eyebrow"
                :eyebrow-icon="$eyebrowIcon"
                :tone="$tone"
                :title="$title"
                :subtitle="$subtitle"
                align="left"
            />

            @if ($ctaLabel)
                <a href="{{ $ctaUrl }}" class="inline-flex items-center gap-2 text-brand-red font-semibold hover:gap-3 transition-all whitespace-nowrap">
                    {{ $ctaLabel }} <i class="fas fa-arrow-right text-sm"></i>
                </a>
            @endif
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($products as $product)
                <x-product.card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
