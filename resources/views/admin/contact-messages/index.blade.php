@extends('layouts.admin')

@section('title', 'İletişim Mesajları')

@section('content')
    <x-admin-page-header title="İletişim Mesajları" />

    <div class="admin-table-scroll overflow-x-auto border border-stone-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-left text-stone-600">
                <tr>
                    <th class="px-5 py-3 font-medium">Gönderen</th>
                    <th class="px-5 py-3 font-medium">Konu</th>
                    <th class="px-5 py-3 font-medium">Tarih</th>
                    <th class="px-5 py-3 font-medium">Durum</th>
                    <th class="px-5 py-3 font-medium text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($messages as $message)
                    <tr class="{{ $message->isRead() ? '' : 'bg-stone-50' }}">
                        <td class="px-5 py-3">{{ $message->name }}</td>
                        <td class="px-5 py-3">{{ $message->subject }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $message->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-5 py-3">{{ $message->isRead() ? 'Okundu' : 'Yeni' }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="text-stone-700 hover:text-stone-900">Görüntüle</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500">Mesaj bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
@endsection
