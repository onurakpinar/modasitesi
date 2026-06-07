<footer class="mt-20 border-t border-stone-200 bg-white">
    <x-site-container class="max-w-6xl py-12 sm:py-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <p class="font-display text-xl text-stone-900">{{ $siteName ?? config('site.name') }}</p>
                <p class="mt-3 max-w-sm text-base leading-relaxed text-stone-600">
                    {{ $footerDescription ?: ($siteShortDescription ?: ($siteTagline ?? config('site.tagline'))) }}
                </p>

                @if (collect($socialLinks ?? [])->filter()->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-3">
                        @if (! empty($socialLinks['instagram']))
                            <a href="{{ $socialLinks['instagram'] }}" class="text-sm text-stone-600 hover:text-accent-700" rel="noopener noreferrer" target="_blank">Instagram</a>
                        @endif
                        @if (! empty($socialLinks['facebook']))
                            <a href="{{ $socialLinks['facebook'] }}" class="text-sm text-stone-600 hover:text-accent-700" rel="noopener noreferrer" target="_blank">Facebook</a>
                        @endif
                        @if (! empty($socialLinks['pinterest']))
                            <a href="{{ $socialLinks['pinterest'] }}" class="text-sm text-stone-600 hover:text-accent-700" rel="noopener noreferrer" target="_blank">Pinterest</a>
                        @endif
                        @if (! empty($socialLinks['twitter']))
                            <a href="{{ $socialLinks['twitter'] }}" class="text-sm text-stone-600 hover:text-accent-700" rel="noopener noreferrer" target="_blank">X</a>
                        @endif
                    </div>
                @endif
            </div>

            @if ($footerCategories->isNotEmpty())
                <div>
                    <h2 class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Kategoriler</h2>
                    <ul class="mt-4 space-y-2">
                        @foreach ($footerCategories as $category)
                            <li>
                                <a href="{{ route('categories.show', $category->slug) }}" class="text-base text-stone-700 hover:text-accent-700">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div>
                <h2 class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Sayfalar</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($staticPages as $page)
                        @php $pageRoute = \App\Support\PublicContent::staticPageRouteName($page->slug); @endphp
                        @if ($pageRoute)
                            <li>
                                <a href="{{ route($pageRoute) }}" class="text-base text-stone-700 hover:text-accent-700">
                                    {{ $page->title }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div>
                <h2 class="text-xs font-medium uppercase tracking-[0.2em] text-stone-500">Keşfet</h2>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('posts.index') }}" class="text-base text-stone-700 hover:text-accent-700">Tüm Yazılar</a></li>
                    <li><a href="{{ route('search') }}" class="text-base text-stone-700 hover:text-accent-700">Arama</a></li>
                </ul>
            </div>
        </div>

        <p class="mt-12 border-t border-stone-100 pt-8 text-sm text-stone-500">
            &copy; {{ now()->year }} {{ $siteName ?? config('site.name') }}. Tüm hakları saklıdır.
        </p>
    </x-site-container>
</footer>
