@extends('layouts.admin-guest')

@section('title', 'Giriş')

@section('content')
    <div class="border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-widest text-stone-500">Yönetim Paneli</p>
        <h1 class="mt-2 font-display text-2xl text-stone-900">Giriş Yap</h1>
        <p class="mt-2 text-sm text-stone-600">Yetkili hesabınızla devam edin.</p>

        @if ($errors->isNotEmpty())
            <div class="mt-6 border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-stone-700">E-posta</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="mt-1 block w-full border border-stone-300 px-3 py-2 text-sm focus:border-stone-900 focus:outline-none"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-stone-700">Parola</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    class="mt-1 block w-full border border-stone-300 px-3 py-2 text-sm focus:border-stone-900 focus:outline-none"
                >
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300">
                Beni hatırla
            </label>

            <button
                type="submit"
                class="w-full border border-stone-900 bg-stone-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-stone-800"
            >
                Giriş Yap
            </button>
        </form>
    </div>
@endsection
