@extends('layouts.admin')

@section('title', 'Yeni İçerik Briefi')

@section('content')
    <x-admin-page-header title="Yeni İçerik Briefi" />

    <form method="POST" action="{{ route('admin.content-briefs.store') }}" class="max-w-3xl border border-stone-200 bg-white p-6">
        @csrf
        @include('admin.content-briefs._form')
        <div class="mt-6 flex gap-3">
            <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
            <a href="{{ route('admin.content-briefs.index') }}" class="border border-stone-300 px-4 py-2 text-sm text-stone-700">İptal</a>
        </div>
    </form>
@endsection
