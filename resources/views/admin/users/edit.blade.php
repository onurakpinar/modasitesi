@extends('layouts.admin')

@section('title', 'Kullanıcı Düzenle')

@section('content')
    <x-admin-page-header title="Kullanıcı Düzenle" />
    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-2xl space-y-5 border border-stone-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.users._form')
        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Güncelle</button>
    </form>
@endsection
