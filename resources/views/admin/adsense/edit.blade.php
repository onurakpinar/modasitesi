@extends('layouts.admin')

@section('title', 'AdSense ve Gizlilik')

@section('content')
    <x-admin-page-header title="AdSense ve Gizlilik" />

  @if (session('success'))
        <p class="mb-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</p>
    @endif

    <section class="mb-10 border border-stone-200 bg-white p-6">
        <h2 class="font-display text-xl text-stone-900">AdSense Başvuru Hazırlık Kontrolü</h2>
        <p class="mt-2 text-sm text-stone-600">
            Bu kontrol listesi başvuru öncesi kalite eşiğidir. AdSense onayı garanti edilmez; yalnızca politikalara uygun hazırlığı değerlendirir.
        </p>

        <ul class="mt-6 space-y-3">
            @foreach ($readinessChecks as $check)
                <li class="flex items-start gap-3 text-sm">
                    <span class="mt-0.5 inline-flex size-5 shrink-0 items-center justify-center rounded-full {{ $check['passed'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $check['passed'] ? '✓' : '✗' }}
                    </span>
                    <div>
                        <p class="text-stone-800">{{ $check['label'] }}</p>
                        @if ($check['note'])
                            <p class="mt-1 text-xs text-stone-500">{{ $check['note'] }}</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        <p class="mt-6 text-sm {{ $readinessPassed ? 'text-emerald-700' : 'text-amber-700' }}">
            {{ $readinessPassed ? 'Tüm hazırlık maddeleri tamamlandı.' : 'Eksik maddeler var. Başvurudan önce tamamlayın.' }}
        </p>
    </section>

    <form method="POST" action="{{ route('admin.adsense.update') }}" class="max-w-3xl space-y-8">
        @csrf @method('PUT')

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">Doğrulama ve Kimlik</legend>
            <p class="text-sm text-stone-600">Doğrulama scripti site sahipliği için; reklam kutuları ayrı ayardır. CMP yapılandırılmadan reklamlar açılamaz.</p>

            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="hidden" name="adsense_verification_enabled" value="0">
                <input type="checkbox" name="adsense_verification_enabled" value="1" @checked(old('adsense_verification_enabled', $settings['adsense_verification_enabled']))>
                AdSense doğrulama scriptini head alanına ekle
            </label>

            <div>
                <label class="block text-sm font-medium text-stone-700">AdSense Client ID (ca-pub-)</label>
                <input type="text" name="adsense_client_id" value="{{ old('adsense_client_id', $settings['adsense_client_id']) }}" placeholder="ca-pub-XXXXXXXXXXXXXXXX" class="mt-1 w-full border border-stone-300 px-3 py-2 font-mono text-sm">
                @error('adsense_client_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Publisher ID (pub-)</label>
                <input type="text" name="adsense_publisher_id" value="{{ old('adsense_publisher_id', $settings['adsense_publisher_id']) }}" placeholder="pub-XXXXXXXXXXXXXXXX" class="mt-1 w-full border border-stone-300 px-3 py-2 font-mono text-sm">
                @error('adsense_publisher_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">Reklam Gösterimi</legend>
            <p class="text-sm text-stone-600">Yalnızca yayındaki, 700+ kelimelik yazı detay sayfalarında gösterilir. Slot ID'leri AdSense panelinden alınır.</p>

            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="hidden" name="certified_cmp_configured" value="0">
                <input type="checkbox" name="certified_cmp_configured" value="1" @checked(old('certified_cmp_configured', $settings['certified_cmp_configured']))>
                Google sertifikalı CMP yapılandırıldı (manuel doğrulama)
            </label>

            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="hidden" name="adsense_ads_enabled" value="0">
                <input type="checkbox" name="adsense_ads_enabled" value="1" @checked(old('adsense_ads_enabled', $settings['adsense_ads_enabled']))>
                Yazı detay sayfalarında reklam kutularını göster
            </label>

            <label class="flex items-center gap-2 text-sm text-stone-700">
                <input type="hidden" name="adsense_auto_ads_enabled" value="0">
                <input type="checkbox" name="adsense_auto_ads_enabled" value="1" @checked(old('adsense_auto_ads_enabled', $settings['adsense_auto_ads_enabled']))>
                Auto Ads (varsayılan kapalı)
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-stone-700">Yazı ortası slot ID</label>
                    <input type="text" name="adsense_article_middle_slot" value="{{ old('adsense_article_middle_slot', $settings['adsense_article_middle_slot']) }}" placeholder="1234567890" class="mt-1 w-full border border-stone-300 px-3 py-2 font-mono text-sm">
                    @error('adsense_article_middle_slot')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700">Yazı altı slot ID</label>
                    <input type="text" name="adsense_article_bottom_slot" value="{{ old('adsense_article_bottom_slot', $settings['adsense_article_bottom_slot']) }}" placeholder="1234567890" class="mt-1 w-full border border-stone-300 px-3 py-2 font-mono text-sm">
                    @error('adsense_article_bottom_slot')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </fieldset>

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">Gizlilik ve Kurumsal Hazırlık</legend>
            <p class="text-sm text-stone-600">İlgili sabit sayfaları doldurup yayınladıktan sonra işaretleyin. Eksik sayfalar ziyaretçiye açılmaz.</p>

            @foreach ([
                'privacy_policy_completed' => 'Gizlilik politikası tamamlandı',
                'cookie_policy_completed' => 'Çerez politikası tamamlandı',
                'contact_information_completed' => 'İletişim bilgileri tamamlandı',
                'editorial_information_completed' => 'Yayın ilkeleri tamamlandı',
            ] as $field => $label)
                <label class="flex items-center gap-2 text-sm text-stone-700">
                    <input type="hidden" name="{{ $field }}" value="0">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings[$field]))>
                    {{ $label }}
                </label>
            @endforeach
        </fieldset>

        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
    </form>
@endsection
