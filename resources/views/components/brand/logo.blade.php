@props([
    'variant' => 'dark',
    'size' => 'md',
    'withTagline' => null,
    'href' => null,
])

@php
    // Priority: the logo uploaded in Admin → Content → Header & Footer, then the
    // artwork dropped into public/images/brand, then the mark from the design.
    $uploaded = $variant === 'light'
        ? (setting('brand.logo_light') ?: setting('brand.logo_dark'))
        : setting('brand.logo_dark');

    $artwork = $uploaded ? Storage::disk('public')->url($uploaded) : null;

    if (! $artwork) {
        $file = $variant === 'light' ? 'logo-light' : 'logo-dark';
        $path = collect(['svg', 'png'])
            ->map(fn ($ext) => "images/brand/{$file}.{$ext}")
            ->first(fn ($path) => file_exists(public_path($path)));

        $artwork = $path ? asset($path) : null;
    }

    $heights = ['sm' => 'h-8', 'md' => 'h-11', 'lg' => 'h-14'];
    $markSizes = ['sm' => 'w-9 h-9 text-lg rounded-lg', 'md' => 'w-12 h-12 text-2xl rounded-xl', 'lg' => 'w-14 h-14 text-3xl rounded-xl'];
    $wordSizes = ['sm' => 'text-lg', 'md' => 'text-2xl', 'lg' => 'text-3xl'];

    $wordColor = $variant === 'light' ? 'text-white' : 'text-brand-dark';
    $showTagline = $withTagline ?? (bool) setting('brand.show_tagline', true);
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'flex items-center gap-3 group']) }}>
    @if ($artwork)
        <img
            src="{{ $artwork }}"
            alt="{{ config('app.name') }} — Where The World Trades"
            class="{{ $heights[$size] }} w-auto"
        >
    @else
        <div class="{{ $markSizes[$size] }} bg-brand-red flex items-center justify-center text-white font-bold font-display shadow-lg group-hover:scale-110 transition-transform">
            V
        </div>
        <div>
            <span class="block {{ $wordSizes[$size] }} font-extrabold font-display {{ $wordColor }} tracking-tight leading-none">VEXPORTER</span>
            @if ($showTagline)
                <span class="block text-[10px] font-semibold text-brand-red tracking-[0.2em] uppercase mt-0.5">Where The World Trades</span>
            @endif
        </div>
    @endif
</{{ $tag }}>
