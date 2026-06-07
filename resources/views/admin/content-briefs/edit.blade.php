@extends('layouts.admin')

@section('title', 'Brief Düzenle')

@section('content')
    <x-admin-page-header title="Brief Düzenle" />

    <form method="POST" action="{{ route('admin.content-briefs.update', $brief) }}" class="max-w-3xl border border-stone-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.content-briefs._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Güncelle</button>
            <a href="{{ route('admin.content-briefs.index') }}" class="border border-stone-300 px-4 py-2 text-sm text-stone-700">İptal</a>
        </div>
    </form>
@endsection
