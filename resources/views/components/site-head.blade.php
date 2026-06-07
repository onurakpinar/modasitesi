@props([
    'entries' => ['resources/css/app.css', 'resources/js/app.js'],
    'withFonts' => true,
])

@if ($withFonts)
    @fonts
@endif
@vite($entries)
