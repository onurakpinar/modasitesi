@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-admin-page-header title="Dashboard" />

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="border border-stone-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Yayında</p>
            <p class="mt-2 text-3xl font-display text-stone-900">{{ $publishedCount }}</p>
        </div>
        <div class="border border-stone-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Taslak</p>
            <p class="mt-2 text-3xl font-display text-stone-900">{{ $draftCount }}</p>
        </div>
        <div class="border border-stone-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Kategori</p>
            <p class="mt-2 text-3xl font-display text-stone-900">{{ $categoryCount }}</p>
        </div>
        <div class="border border-stone-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Okunmamış Mesaj</p>
            <p class="mt-2 text-3xl font-display text-stone-900">{{ $unreadMessages }}</p>
        </div>
    </div>

    <section class="mt-8">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-medium text-stone-900">Editoryal Hazırlık</h2>
            <a href="{{ route('admin.content-briefs.index') }}" class="text-sm text-accent-700 hover:text-accent-800">İçerik Takvimi →</a>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="border border-stone-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Toplam Brief</p>
                <p class="mt-2 text-3xl font-display text-stone-900">{{ $briefTotalCount }}</p>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Hazırlanıyor</p>
                <p class="mt-2 text-3xl font-display text-amber-800">{{ $briefPreparingCount }}</p>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Kontrolde</p>
                <p class="mt-2 text-3xl font-display text-sky-800">{{ $briefReviewCount }}</p>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Tamamlandı</p>
                <p class="mt-2 text-3xl font-display text-emerald-800">{{ $briefCompletedCount }}</p>
            </div>
            <div class="border border-stone-200 bg-white p-5 sm:col-span-2 xl:col-span-1">
                <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Başvuru Hazır Yayın</p>
                <p class="mt-2 text-3xl font-display text-stone-900">{{ $applicationReadyPublishedCount }}</p>
                <p class="mt-2 text-xs text-stone-500">Kalite kurallarını karşılayan yayınlanmış içerik</p>
            </div>
        </div>
    </section>

    <section class="mt-8">
        <h2 class="text-lg font-medium text-stone-900">İçerik Kalite Özeti</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div class="border border-stone-200 bg-white p-5">
                <h3 class="text-sm font-medium text-emerald-800">Yayınlanmaya hazır taslaklar ({{ $readyDrafts->count() }})</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($readyDrafts->take(5) as $post)
                        <li><a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-accent-700">{{ $post->title }}</a></li>
                    @empty
                        <li class="text-stone-500">Hazır taslak yok.</li>
                    @endforelse
                </ul>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <h3 class="text-sm font-medium text-amber-800">Eksik bilgili taslaklar ({{ $incompleteDrafts->count() }})</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($incompleteDrafts->take(5) as $post)
                        <li><a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-accent-700">{{ $post->title }}</a></li>
                    @empty
                        <li class="text-stone-500">Eksik taslak yok.</li>
                    @endforelse
                </ul>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <h3 class="text-sm font-medium text-stone-800">Kapak görseli olmayan yazılar</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($missingCoverPosts as $post)
                        <li><a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-accent-700">{{ $post->title }}</a></li>
                    @empty
                        <li class="text-stone-500">Eksik kapak görseli yok.</li>
                    @endforelse
                </ul>
            </div>
            <div class="border border-stone-200 bg-white p-5">
                <h3 class="text-sm font-medium text-stone-800">900 kelimenin altındaki içerikler</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($lowWordPosts as $post)
                        <li>
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-accent-700">{{ $post->title }}</a>
                            <span class="text-stone-500">({{ \App\Support\PostQualityChecker::wordCount($post->body) }} kelime)</span>
                        </li>
                    @empty
                        <li class="text-stone-500">Kısa içerik yok.</li>
                    @endforelse
                </ul>
            </div>
            <div class="border border-stone-200 bg-white p-5 lg:col-span-2">
                <h3 class="text-sm font-medium text-stone-800">İnsan kontrolü bekleyen içerikler</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    @forelse ($pendingReviewPosts as $post)
                        <li><a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-accent-700">{{ $post->title }}</a></li>
                    @empty
                        <li class="text-stone-500">Bekleyen içerik yok.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>

    <section class="mt-8 border border-stone-200 bg-white">
        <div class="border-b border-stone-200 px-5 py-4">
            <h2 class="font-medium text-stone-900">Son Eklenen Yazılar</h2>
        </div>

        @if ($recentPosts->isEmpty())
            <p class="px-5 py-8 text-sm text-stone-500">Henüz yazı eklenmedi.</p>
        @else
            <div class="admin-table-scroll overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                        <tr>
                            <th class="px-5 py-3 font-medium">Başlık</th>
                            <th class="px-5 py-3 font-medium">Yazar</th>
                            <th class="px-5 py-3 font-medium">Kategori</th>
                            <th class="px-5 py-3 font-medium">Durum</th>
                            <th class="px-5 py-3 font-medium">Tarih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($recentPosts as $post)
                            <tr>
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="hover:text-accent-700">
                                        {{ $post->title }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-stone-600">{{ $post->author?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-stone-600">{{ $post->category?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-stone-600">{{ $post->status->label() }}</td>
                                <td class="px-5 py-3 text-stone-500">{{ $post->created_at->format('d.m.Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
