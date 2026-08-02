<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">

    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-800 bg-brand-light antialiased min-h-screen">
    <div class="min-h-screen grid lg:grid-cols-2">
        <div class="hidden lg:flex gradient-hero relative overflow-hidden p-12 flex-col justify-between">
            <div class="absolute top-20 -left-10 w-72 h-72 bg-brand-red rounded-full blur-3xl opacity-20" aria-hidden="true"></div>
            <div class="absolute bottom-10 right-0 w-96 h-96 bg-blue-500 rounded-full blur-3xl opacity-20" aria-hidden="true"></div>

            <x-brand.logo :href="route('home')" variant="light" size="md" class="relative z-10" />

            <div class="relative z-10 text-white max-w-md">
                <h2 class="text-3xl font-extrabold font-display leading-tight mb-4">
                    Global Trade<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-orange-400">Made Simple</span>
                </h2>
                <p class="text-gray-300 leading-relaxed mb-8">
                    Verified vendors, escrow-protected payments and end-to-end logistics across pharma, solar and
                    general trade.
                </p>

                <div class="space-y-3 text-sm text-gray-300">
                    <p class="flex items-center gap-3"><i class="fas fa-circle-check text-green-400"></i> KYC-verified manufacturers only</p>
                    <p class="flex items-center gap-3"><i class="fas fa-circle-check text-green-400"></i> Funds held in escrow until delivery</p>
                    <p class="flex items-center gap-3"><i class="fas fa-circle-check text-green-400"></i> Export documentation handled for you</p>
                </div>
            </div>

            <p class="relative z-10 text-xs text-gray-400">&copy; {{ date('Y') }} VEXPORTER. Where The World Trades.</p>
        </div>

        <div class="flex items-center justify-center p-6 sm:p-12 bg-white">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8">
                    <x-brand.logo :href="route('home')" size="md" />
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>

    <x-ui.toast-host />
</body>
</html>
