@extends('layouts.admin')

@section('title', 'Yazılar')

@section('content')
    <x-admin-page-header title="Yazılar" :action-url="route('admin.posts.create')" action-label="Yeni Yazı" />

    <div class="admin-table-scroll overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Başlık</th>
                    <th class="px-5 py-3 font-medium">Yazar</th>
                    <th class="px-5 py-3 font-medium">Kategori</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($posts as $post)
                    <tr>
                        <td class="px-5 py-3">{{ $post->title }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $post->author?->name }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $post->category?->name }}</td>
                        <td class="px-5 py-3">{{ $post->status->label() }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="text-stone-700 hover:text-stone-900">Düzenle</a>
                            <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500">Yazı bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
@endsection
