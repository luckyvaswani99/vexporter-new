@props([
    'title' => null,
    'description' => null,
    'url' => null,
    'image' => null,
    'type' => 'website',
])

@php
    $seo = app(\App\Services\SeoService::class)->generate($title, $description, $url, $image, $type);
@endphp

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
<meta property="og:title" content="{{ $seo['og']['title'] }}">
<meta property="og:description" content="{{ $seo['og']['description'] }}">
<meta property="og:url" content="{{ $seo['og']['url'] }}">
<meta property="og:image" content="{{ $seo['og']['image'] }}">
<meta property="og:type" content="{{ $seo['og']['type'] }}">

{{-- Twitter Cards --}}
<meta name="twitter:card" content="{{ $seo['twitter']['card'] }}">
<meta name="twitter:title" content="{{ $seo['twitter']['title'] }}">
<meta name="twitter:description" content="{{ $seo['twitter']['description'] }}">
<meta name="twitter:image" content="{{ $seo['twitter']['image'] }}">
