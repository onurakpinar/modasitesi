<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\SecureImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'siteName' => SiteSetting::get('site_name', config('site.name')),
            'siteTagline' => SiteSetting::get('site_tagline', config('site.tagline')),
            'siteShortDescription' => SiteSetting::get('site_short_description', ''),
            'footerDescription' => SiteSetting::get('footer_description', ''),
            'contactEmail' => SiteSetting::get('contact_email', ''),
            'socialInstagram' => SiteSetting::get('social_instagram', ''),
            'socialFacebook' => SiteSetting::get('social_facebook', ''),
            'socialPinterest' => SiteSetting::get('social_pinterest', ''),
            'socialTwitter' => SiteSetting::get('social_twitter', ''),
            'siteLogo' => SiteSetting::get('site_logo', ''),
            'siteFavicon' => SiteSetting::get('site_favicon', ''),
            'defaultMetaTitle' => SiteSetting::get('default_meta_title', ''),
            'defaultMetaDescription' => SiteSetting::get('default_meta_description', ''),
            'ogImage' => SiteSetting::get('og_image', ''),
        ]);
    }

    public function update(Request $request, SecureImageUploader $uploader): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:500'],
            'site_short_description' => ['nullable', 'string', 'max:1000'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_pinterest' => ['nullable', 'url', 'max:500'],
            'social_twitter' => ['nullable', 'url', 'max:500'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:500'],
            'site_logo' => ['nullable', File::image(allowSvg: false)->max(2048)],
            'site_favicon' => ['nullable', File::image(allowSvg: false)->max(512)],
            'og_image' => ['nullable', File::image(allowSvg: false)->max(2048)],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_favicon' => ['sometimes', 'boolean'],
            'remove_og_image' => ['sometimes', 'boolean'],
        ], [], [
            'site_name' => 'site adı',
            'site_tagline' => 'slogan',
            'site_short_description' => 'kısa açıklama',
            'footer_description' => 'footer açıklaması',
            'contact_email' => 'iletişim e-postası',
            'default_meta_title' => 'varsayılan meta başlık',
            'default_meta_description' => 'varsayılan meta açıklama',
            'site_logo' => 'logo',
            'site_favicon' => 'favicon',
            'og_image' => 'OG görseli',
        ]);

        SiteSetting::set('site_name', $validated['site_name'], 'general');
        SiteSetting::set('site_tagline', $validated['site_tagline'] ?? '', 'general');
        SiteSetting::set('site_short_description', $validated['site_short_description'] ?? '', 'general');
        SiteSetting::set('default_meta_title', $validated['default_meta_title'] ?? '', 'seo');
        SiteSetting::set('default_meta_description', $validated['default_meta_description'] ?? '', 'seo');
        SiteSetting::set('footer_description', $validated['footer_description'] ?? '', 'contact');
        SiteSetting::set('contact_email', $validated['contact_email'] ?? '', 'contact');
        SiteSetting::set('social_instagram', $validated['social_instagram'] ?? '', 'social');
        SiteSetting::set('social_facebook', $validated['social_facebook'] ?? '', 'social');
        SiteSetting::set('social_pinterest', $validated['social_pinterest'] ?? '', 'social');
        SiteSetting::set('social_twitter', $validated['social_twitter'] ?? '', 'social');

        if ($request->boolean('remove_logo')) {
            $this->deleteStoredFile(SiteSetting::get('site_logo'));
            SiteSetting::set('site_logo', '', 'branding');
        }

        if ($request->boolean('remove_favicon')) {
            $this->deleteStoredFile(SiteSetting::get('site_favicon'));
            SiteSetting::set('site_favicon', '', 'branding');
        }

        if ($request->boolean('remove_og_image')) {
            $this->deleteStoredFile(SiteSetting::get('og_image'));
            SiteSetting::set('og_image', '', 'seo');
        }

        if ($request->hasFile('site_logo')) {
            $this->deleteStoredFile(SiteSetting::get('site_logo'));
            SiteSetting::set('site_logo', $uploader->upload($request->file('site_logo'), 'branding', 1200), 'branding');
        }

        if ($request->hasFile('site_favicon')) {
            $this->deleteStoredFile(SiteSetting::get('site_favicon'));
            SiteSetting::set('site_favicon', $uploader->upload($request->file('site_favicon'), 'branding', 256), 'branding');
        }

        if ($request->hasFile('og_image')) {
            $this->deleteStoredFile(SiteSetting::get('og_image'));
            SiteSetting::set('og_image', $uploader->upload($request->file('og_image'), 'branding', 1200), 'seo');
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Site ayarları güncellendi.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

}
