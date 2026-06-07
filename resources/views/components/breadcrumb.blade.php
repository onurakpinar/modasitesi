@props(['items' => []])

@if (count($items) > 0)
    <nav aria-label="Breadcrumb" class="mb-8 text-sm text-stone-500">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-2">
                    @if ($index > 0)
                        <span aria-hidden="true" class="text-stone-300">/</span>
                    @endif

                    @if (! empty($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="hover:text-accent-700">{{ $item['label'] }}</a>
                    @else
                        <span class="text-stone-700" @if($index === count($items) - 1) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
