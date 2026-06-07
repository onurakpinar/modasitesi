@extends('layouts.admin')

@section('title', 'Site Ayarları')

@section('content')
    <x-admin-page-header title="Site Ayarları" />

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-8">
        @csrf @method('PUT')

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">Genel</legend>

            <div>
                <label class="block text-sm font-medium text-stone-700">Site Adı</label>
                <input type="text" name="site_name" value="{{ old('site_name', $siteName) }}" required class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Slogan</label>
                <textarea name="site_tagline" rows="2" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('site_tagline', $siteTagline) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Site Kısa Açıklaması</label>
                <textarea name="site_short_description" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('site_short_description', $siteShortDescription) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Varsayılan Meta Başlık</label>
                <input type="text" name="default_meta_title" value="{{ old('default_meta_title', $defaultMetaTitle) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Varsayılan Meta Açıklama</label>
                <textarea name="default_meta_description" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('default_meta_description', $defaultMetaDescription) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Footer Açıklaması</label>
                <textarea name="footer_description" rows="3" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">{{ old('footer_description', $footerDescription) }}</textarea>
            </div>
        </fieldset>

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">Marka</legend>

            <div>
                <label class="block text-sm font-medium text-stone-700">Logo</label>
                @if ($siteLogo)
                    <p class="mt-1 text-xs text-stone-500">Mevcut: {{ $siteLogo }}</p>
                    <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remove_logo" value="1"> Logoyu kaldır
                    </label>
                @endif
                <input type="file" name="site_logo" accept="image/*" class="mt-2 w-full text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Open Graph Görseli</label>
                @if ($ogImage)
                    <p class="mt-1 text-xs text-stone-500">Mevcut: {{ $ogImage }}</p>
                    <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remove_og_image" value="1"> OG görselini kaldır
                    </label>
                @endif
                <input type="file" name="og_image" accept="image/*" class="mt-2 w-full text-sm">
                <p class="mt-1 text-xs text-stone-500">Sosyal paylaşımlarda kullanılır. Boş bırakılırsa logo tercih edilir.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Favicon</label>
                @if ($siteFavicon)
                    <p class="mt-1 text-xs text-stone-500">Mevcut: {{ $siteFavicon }}</p>
                    <label class="mt-2 flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remove_favicon" value="1"> Favicon'u kaldır
                    </label>
                @endif
                <input type="file" name="site_favicon" accept="image/*" class="mt-2 w-full text-sm">
            </div>
        </fieldset>

        <fieldset class="space-y-5 border border-stone-200 bg-white p-6">
            <legend class="px-1 text-sm font-medium text-stone-900">İletişim ve Sosyal Medya</legend>

            <div>
                <label class="block text-sm font-medium text-stone-700">İletişim E-postası</label>
                <input type="email" name="contact_email" value="{{ old('contact_email', $contactEmail) }}" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Instagram</label>
                <input type="url" name="social_instagram" value="{{ old('social_instagram', $socialInstagram) }}" placeholder="https://" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Facebook</label>
                <input type="url" name="social_facebook" value="{{ old('social_facebook', $socialFacebook) }}" placeholder="https://" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">Pinterest</label>
                <input type="url" name="social_pinterest" value="{{ old('social_pinterest', $socialPinterest) }}" placeholder="https://" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700">X (Twitter)</label>
                <input type="url" name="social_twitter" value="{{ old('social_twitter', $socialTwitter) }}" placeholder="https://" class="mt-1 w-full border border-stone-300 px-3 py-2 text-sm">
            </div>
        </fieldset>

        <button type="submit" class="border border-stone-900 bg-stone-900 px-4 py-2 text-sm text-white">Kaydet</button>
    </form>
@endsection
