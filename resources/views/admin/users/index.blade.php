@extends('layouts.admin')

@section('title', 'Kullanıcılar')

@section('content')
    <x-admin-page-header title="Kullanıcılar" :action-url="route('admin.users.create')" action-label="Yeni Kullanıcı" />

    <div class="admin-table-scroll overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Ad</th>
                    <th class="px-5 py-3 font-medium">E-posta</th>
                    <th class="px-5 py-3 font-medium">Rol</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-3">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $user->email }}</td>
                        <td class="px-5 py-3">{{ $user->role->label() }}</td>
                        <td class="px-5 py-3">{{ $user->is_active ? 'Aktif' : 'Pasif' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-stone-700 hover:text-stone-900">Düzenle</a>
                            @if ($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800">Sil</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500">Kullanıcı bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection
