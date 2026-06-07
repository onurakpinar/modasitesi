@extends('layouts.admin')

@section('title', 'Kategori Düzenle')

@section('content')
    <x-admin-page-header title="Kategori Düzenle" />

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-2xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.categories._form')
        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white hover:bg-stone-800">Güncelle</button>
    </form>
@endsection
