@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
    <x-admin-page-header title="Kategoriler" :action-url="route('admin.categories.create')" action-label="Yeni Kategori" />

    @if ($categories->isNotEmpty())
        <form method="POST" action="{{ route('admin.categories.reorder') }}" class="mb-6 max-w-2xl border border-stone-200 bg-white p-6">
            @csrf @method('PATCH')
            <h2 class="text-sm font-medium text-stone-900">Kategori Sıralaması</h2>
            <p class="mt-1 text-sm text-stone-500">Üst menü ve footer'da görünen kategori sırası.</p>
            <ul class="mt-4 space-y-2">
                @foreach ($categories as $category)
                    <li class="flex items-center gap-3">
                        <input type="hidden" name="order[]" value="{{ $category->id }}">
                        <span class="flex-1 text-sm text-stone-800">{{ $category->name }}</span>
                        <div class="flex gap-1">
                            @if (! $loop->first)
                                <button type="button" onclick="moveCategory(this, -1)" class="border border-stone-300 px-2 py-1 text-xs text-stone-600 hover:bg-stone-50" aria-label="Yukarı">↑</button>
                            @endif
                            @if (! $loop->last)
                                <button type="button" onclick="moveCategory(this, 1)" class="border border-stone-300 px-2 py-1 text-xs text-stone-600 hover:bg-stone-50" aria-label="Aşağı">↓</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <button type="submit" class="mt-4 border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Sıralamayı Kaydet</button>
        </form>

        <script>
            function moveCategory(button, direction) {
                const item = button.closest('li');
                const list = item.parentElement;
                const sibling = direction < 0 ? item.previousElementSibling : item.nextElementSibling;
                if (!sibling) return;
                if (direction < 0) list.insertBefore(item, sibling);
                else list.insertBefore(sibling, item);
            }
        </script>
    @endif

    <div class="overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Ad</th>
                    <th class="px-5 py-3 font-medium">Slug</th>
                    <th class="px-5 py-3 font-medium">Sıra</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-5 py-3">{{ $category->name }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $category->slug }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $category->sort_order }}</td>
                        <td class="px-5 py-3">{{ $category->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-stone-700 hover:text-stone-900">Düzenle</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800">Sil</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500">Kategori bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
