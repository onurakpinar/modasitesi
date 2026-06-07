@extends('layouts.admin')

@section('title', 'Yeni Kullanıcı')

@section('content')
    <x-admin-page-header title="Yeni Kullanıcı" />
    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-2xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf
        @include('admin.users._form')
        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
    </form>
@endsection
