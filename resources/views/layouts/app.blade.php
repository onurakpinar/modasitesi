<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @isset($seoMeta)
        <x-seo-head :meta="$seoMeta" />
    @else
        <title>@yield('title', $siteName ?? config('site.name'))</title>
        <meta name="description" content="@yield('meta_description', $siteShortDescription ?: ($siteTagline ?? config('site.tagline')))">
    @endisset

    @php
        $faviconUrl = \App\Support\MediaUrl::public($siteFavicon ?? null);
    @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    <x-site-head />
    <x-cookieyes.banner />
    <x-adsense.head />
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <a href="#main-content" class="skip-link">İçeriğe atla</a>

    <x-site-header />

    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>

    <x-site-footer />
</body>
</html>
