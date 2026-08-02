<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php($favicon = setting('brand.favicon'))
    <link rel="icon" href="{{ $favicon ? Storage::disk('public')->url($favicon) : asset('favicon.ico') }}">

    <x-seo.meta :title="$title ?? null" :description="$description ?? null" />
    <x-seo.json-ld :data="app(\App\Services\JsonLdGenerator::class)->organization()" />

    <script>
        window.VEXPORTER = @json($storefrontState ?? ['cart' => ['count' => 0, 'total' => '0.00'], 'wishlist' => []]);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="font-sans text-gray-800 bg-white antialiased">
    <a href="#content" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-[100] focus:rounded-lg focus:bg-brand-red focus:px-4 focus:py-2 focus:text-white">
        Skip to content
    </a>

    <x-nav.topbar />
    <x-nav.header />

    <main id="content">
        {{ $slot }}
    </main>

    <x-footer.main />
    <x-ui.toast-host />

    @stack('scripts')
</body>
</html>
