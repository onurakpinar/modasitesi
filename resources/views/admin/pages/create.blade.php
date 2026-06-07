@extends('layouts.admin')

@section('title', 'Yeni Sayfa')

@section('content')
    <x-admin-page-header title="Yeni Sayfa" />
    <form method="POST" action="{{ route('admin.pages.store') }}" class="max-w-4xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf
        @include('admin.pages._form')
        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
    </form>
@endsection
