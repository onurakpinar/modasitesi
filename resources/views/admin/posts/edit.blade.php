@extends('layouts.admin')

@section('title', 'Yazı Düzenle')

@section('content')
    <x-admin-page-header title="Yazı Düzenle" />

    <div class="mb-4 flex flex-wrap gap-3 text-sm">
        <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="border border-stone-300 bg-white px-3 py-2 text-stone-700 hover:bg-stone-50">
            Ön izleme bağlantısı
        </a>
        @if ($post->status === \App\Enums\PostStatus::Published)
            <a href="{{ route('posts.show', $post->slug) }}" target="_blank" rel="noopener noreferrer" class="border border-stone-300 bg-white px-3 py-2 text-stone-700 hover:bg-stone-50">
                Canlı yazıyı aç
            </a>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="max-w-4xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.posts._form')
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Güncelle</button>
        </div>
    </form>

    @if ($post->revisions->isNotEmpty())
        <section class="mt-8 max-w-4xl border border-stone-200 bg-white p-6">
            <h2 class="text-lg font-medium text-stone-900">Revizyon Geçmişi</h2>
            <ul class="mt-4 divide-y divide-stone-100">
                @foreach ($post->revisions as $revision)
                    <li class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">
                        <div>
                            <p class="font-medium text-stone-800">{{ $revision->title }}</p>
                            <p class="text-stone-500">{{ $revision->created_at->format('d.m.Y H:i') }} · {{ $revision->user?->name ?? 'Sistem' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.posts.revisions.restore', [$post, $revision]) }}" onsubmit="return confirm('Bu sürüm geri yüklensin mi?')">
                            @csrf
                            <button type="submit" class="text-stone-700 hover:text-stone-900">Geri yükle</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
