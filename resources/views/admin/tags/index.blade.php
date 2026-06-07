@extends('layouts.admin')

@section('title', 'Etiketler')

@section('content')
    <x-admin-page-header title="Etiketler" :action-url="route('admin.tags.create')" action-label="Yeni Etiket" />

    <div class="admin-table-scroll overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Ad</th>
                    <th class="px-5 py-3 font-medium">Slug</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($tags as $tag)
                    <tr>
                        <td class="px-5 py-3">{{ $tag->name }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $tag->slug }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('admin.tags.edit', $tag) }}" class="text-stone-700 hover:text-stone-900">Düzenle</a>
                            <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-5 py-8 text-center text-stone-500">Etiket bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tags->links() }}</div>
@endsection
