@extends('layouts.admin')

@section('title', 'Mesaj Detayı')

@section('content')
    <x-admin-page-header title="Mesaj Detayı" />

    <div class="max-w-3xl space-y-4 border border-stone-200 bg-white p-6 text-sm">
        <div>
            <p class="text-xs uppercase tracking-widest text-stone-500">Gönderen</p>
            <p class="mt-1 font-medium">{{ $message->name }}</p>
            <p class="text-stone-600">{{ $message->email }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-widest text-stone-500">Konu</p>
            <p class="mt-1">{{ $message->subject }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-widest text-stone-500">Mesaj</p>
            <p class="mt-1 whitespace-pre-wrap leading-relaxed text-stone-700">{{ $message->message }}</p>
        </div>
        <div class="flex gap-4 pt-4 text-xs text-stone-500">
            <span>{{ $message->created_at->format('d.m.Y H:i') }}</span>
            @if ($message->ip_address)
                <span>IP: {{ $message->ip_address }}</span>
            @endif
        </div>
        <a href="{{ route('admin.contact-messages.index') }}" class="inline-block text-stone-700 hover:text-stone-900">← Listeye dön</a>
    </div>
@endsection
