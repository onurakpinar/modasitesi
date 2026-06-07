<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sayfa bulunamadı — {{ config('site.name') }}</title>
    <x-site-head :entries="['resources/css/app.css']" />
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 text-center">
        <p class="text-xs font-medium uppercase tracking-[0.25em] text-stone-500">404</p>
        <h1 class="mt-4 font-display text-3xl text-stone-900 sm:text-4xl">Sayfa bulunamadı</h1>
        <p class="mt-4 max-w-md text-sm leading-relaxed text-stone-600">
            Aradığınız sayfa taşınmış, kaldırılmış veya hiç var olmamış olabilir.
        </p>
        <a
            href="{{ url('/') }}"
            class="mt-8 inline-flex items-center border border-stone-900 px-6 py-3 text-sm font-medium uppercase tracking-widest text-stone-900 hover:bg-stone-900 hover:text-white"
        >
            Ana sayfaya dön
        </a>
    </div>
</body>
</html>
