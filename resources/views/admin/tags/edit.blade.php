@extends('layouts.admin')

@section('title', 'Etiket Düzenle')

@section('content')
    <x-admin-page-header title="Etiket Düzenle" />
    <form method="POST" action="{{ route('admin.tags.update', $tag) }}" class="max-w-xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.tags._form')
        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Güncelle</button>
    </form>
@endsection
