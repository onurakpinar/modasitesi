@extends('layouts.admin')

@section('title', 'Yeni Yazı')

@section('content')
    <x-admin-page-header title="Yeni Yazı" />

    <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="max-w-4xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf
        @include('admin.posts._form')
        <div class="flex flex-wrap gap-3">
            <button type="submit" name="intent" value="draft" class="border border-stone-300 bg-white px-4 py-2 text-sm text-stone-800 hover:bg-stone-50">Taslak Kaydet</button>
            <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
        </div>
    </form>
@endsection
