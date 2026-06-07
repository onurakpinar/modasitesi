@props(['title', 'actionUrl' => null, 'actionLabel' => null])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <h1 class="font-display text-2xl text-stone-900">{{ $title }}</h1>

    @if ($actionUrl && $actionLabel)
        <a
            href="{{ $actionUrl }}"
            class="inline-flex items-center justify-center border border-stone-900 bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800"
        >
            {{ $actionLabel }}
        </a>
    @endif
</div>
