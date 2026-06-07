<footer class="mt-16 border-t border-stone-200 bg-white sm:mt-20">
    <x-site-container class="max-w-6xl py-10 sm:py-16">
        <div class="grid gap-6 sm:gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div class="md:col-span-2 lg:col-span-1">
                <p class="break-words font-display text-lg text-stone-900 sm:text-xl">{{ $siteName ?? config('site.name') }}</p>
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
                <x-footer-nav-section title="Kategoriler" section-id="categories">
                    <ul class="mt-0 space-y-2 pb-4 md:mt-4 md:pb-0">
                        @foreach ($footerCategories as $category)
                            <li>
                                <a href="{{ route('categories.show', $category->slug) }}" class="text-base text-stone-700 hover:text-accent-700">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </x-footer-nav-section>
            @endif

            <x-footer-nav-section title="Sayfalar" section-id="pages">
                <ul class="mt-0 space-y-2 pb-4 md:mt-4 md:pb-0">
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
            </x-footer-nav-section>

            <x-footer-nav-section title="Keşfet" section-id="explore">
                <ul class="mt-0 space-y-2 pb-4 md:mt-4 md:pb-0">
                    <li><a href="{{ route('posts.index') }}" class="text-base text-stone-700 hover:text-accent-700">Tüm Yazılar</a></li>
                    <li><a href="{{ route('search') }}" class="text-base text-stone-700 hover:text-accent-700">Arama</a></li>
                </ul>
            </x-footer-nav-section>
        </div>

        <p class="mt-8 border-t border-stone-100 pt-6 text-sm text-stone-500 sm:mt-12 sm:pt-8">
            &copy; {{ now()->year }} {{ $siteName ?? config('site.name') }}. Tüm hakları saklıdır.
        </p>
    </x-site-container>
</footer>
