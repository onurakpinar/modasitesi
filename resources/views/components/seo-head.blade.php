@props(['meta'])

@php
    use App\Support\Seo\SeoSettings;
@endphp

<title>{{ $meta->title }}</title>
<meta name="description" content="{{ $meta->description }}">
<link rel="canonical" href="{{ $meta->canonical }}">
<meta name="robots" content="{{ $meta->robots }}">

<meta property="og:type" content="website">
<meta property="og:locale" content="tr_TR">
<meta property="og:title" content="{{ $meta->ogTitle }}">
<meta property="og:description" content="{{ $meta->ogDescription }}">
<meta property="og:url" content="{{ $meta->canonical }}">
<meta property="og:site_name" content="{{ SeoSettings::siteName() }}">
@if ($meta->ogImage)
    <meta property="og:image" content="{{ $meta->ogImage }}">
@endif

<meta name="twitter:card" content="{{ $meta->twitterCard }}">
<meta name="twitter:title" content="{{ $meta->ogTitle }}">
<meta name="twitter:description" content="{{ $meta->ogDescription }}">
@if ($meta->ogImage)
    <meta name="twitter:image" content="{{ $meta->ogImage }}">
@endif

@foreach ($meta->jsonLd as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endforeach
