@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Sayfalama" class="flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex cursor-not-allowed items-center rounded border border-stone-200 px-4 py-2 text-sm text-stone-400">Önceki</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:border-stone-400">Önceki</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:border-stone-400">Sonraki</a>
            @else
                <span class="inline-flex cursor-not-allowed items-center rounded border border-stone-200 px-4 py-2 text-sm text-stone-400">Sonraki</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <p class="text-sm text-stone-600">
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                /
                <span class="font-medium">{{ $paginator->total() }}</span>
                sonuç
            </p>

            <div>
                <span class="relative z-0 inline-flex rounded shadow-sm">
                    @if ($paginator->onFirstPage())
                        <span class="inline-flex cursor-not-allowed items-center rounded-l border border-stone-200 px-3 py-2 text-sm text-stone-400" aria-disabled="true">&lsaquo;</span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-l border border-stone-300 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50" aria-label="Önceki sayfa">&lsaquo;</a>
                    @endif

                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="inline-flex items-center border border-stone-200 px-4 py-2 text-sm text-stone-500">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="inline-flex items-center border border-stone-300 bg-stone-100 px-4 py-2 text-sm font-medium text-stone-900" aria-current="page">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center border border-stone-300 px-4 py-2 text-sm text-stone-700 hover:bg-stone-50" aria-label="Sayfa {{ $page }}">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-r border border-stone-300 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50" aria-label="Sonraki sayfa">&rsaquo;</a>
                    @else
                        <span class="inline-flex cursor-not-allowed items-center rounded-r border border-stone-200 px-3 py-2 text-sm text-stone-400" aria-disabled="true">&rsaquo;</span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
