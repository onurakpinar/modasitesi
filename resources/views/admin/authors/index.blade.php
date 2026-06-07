@extends('layouts.admin')

@section('title', 'Yazarlar')

@section('content')
    <x-admin-page-header title="Yazarlar" :action-url="route('admin.authors.create')" action-label="Yeni Yazar" />

    <div class="overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Ad</th>
                    <th class="px-5 py-3 font-medium">E-posta</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($authors as $author)
                    <tr>
                        <td class="px-5 py-3">{{ $author->name }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $author->email ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $author->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('admin.authors.edit', $author) }}" class="text-stone-700 hover:text-stone-900">Düzenle</a>
                            <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-stone-500">Yazar bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $authors->links() }}</div>
@endsection
