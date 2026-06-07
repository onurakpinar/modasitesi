<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\Ads\AdSenseValidator;
use App\Support\Ads\PageTemplates;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAdSenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'adsense_verification_enabled' => ['sometimes', 'boolean'],
            'adsense_ads_enabled' => ['sometimes', 'boolean'],
            'adsense_auto_ads_enabled' => ['sometimes', 'boolean'],
            'adsense_client_id' => ['nullable', 'string', 'max:32'],
            'adsense_publisher_id' => ['nullable', 'string', 'max:32'],
            'adsense_article_middle_slot' => ['nullable', 'string', 'max:16'],
            'adsense_article_bottom_slot' => ['nullable', 'string', 'max:16'],
            'certified_cmp_configured' => ['sometimes', 'boolean'],
            'privacy_policy_completed' => ['sometimes', 'boolean'],
            'cookie_policy_completed' => ['sometimes', 'boolean'],
            'contact_information_completed' => ['sometimes', 'boolean'],
            'editorial_information_completed' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $clientId = $this->input('adsense_client_id');
            if (filled($clientId) && ! AdSenseValidator::isValidClientId($clientId)) {
                $validator->errors()->add('adsense_client_id', 'Client ID yalnızca ca-pub- ile başlayan geçerli formatta olmalıdır.');
            }

            $publisherId = $this->input('adsense_publisher_id');
            if (filled($publisherId) && ! AdSenseValidator::isValidPublisherId($publisherId)) {
                $validator->errors()->add('adsense_publisher_id', 'Publisher ID yalnızca pub- ile başlayan geçerli formatta olmalıdır.');
            }

            foreach (['adsense_article_middle_slot' => 'Orta reklam slot', 'adsense_article_bottom_slot' => 'Alt reklam slot'] as $field => $label) {
                $value = $this->input($field);
                if (filled($value) && ! AdSenseValidator::isValidSlotId($value)) {
                    $validator->errors()->add($field, $label.' yalnızca 10 haneli sayısal formatta olmalıdır.');
                }
            }

            if ($this->boolean('adsense_ads_enabled') && ! $this->boolean('certified_cmp_configured')) {
                $validator->errors()->add('adsense_ads_enabled', 'Reklamlar, sertifikalı CMP yapılandırılmadan açılamaz.');
            }

            if ($this->boolean('privacy_policy_completed') && ! $this->isStaticPageReady('gizlilik-politikasi')) {
                $validator->errors()->add('privacy_policy_completed', 'Gizlilik politikası yayınlanmış ve placeholder içermeden doldurulmalıdır.');
            }

            if ($this->boolean('cookie_policy_completed') && ! $this->isStaticPageReady('cerez-politikasi')) {
                $validator->errors()->add('cookie_policy_completed', 'Çerez politikası yayınlanmış ve placeholder içermeden doldurulmalıdır.');
            }

            if ($this->boolean('editorial_information_completed') && ! $this->isStaticPageReady('yayin-ilkeleri')) {
                $validator->errors()->add('editorial_information_completed', 'Yayın ilkeleri sayfası yayınlanmış ve placeholder içermeden doldurulmalıdır.');
            }

            if ($this->boolean('contact_information_completed')) {
                if (filter_var(SiteSetting::get('contact_email'), FILTER_VALIDATE_EMAIL) === false) {
                    $validator->errors()->add('contact_information_completed', 'Geçerli iletişim e-postası site ayarlarında tanımlanmadan işaretlenemez.');
                }

                if (! $this->isStaticPageReady('iletisim')) {
                    $validator->errors()->add('contact_information_completed', 'İletişim sayfası yayınlanmış ve placeholder içermeden doldurulmalıdır.');
                }
            }
        });
    }

    private function isStaticPageReady(string $slug): bool
    {
        $page = Page::query()->where('slug', $slug)->first();

        return $page
            && $page->status === PageStatus::Published
            && PageTemplates::isPublicReady($page->body ?? '');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'adsense_client_id' => 'AdSense client ID',
            'adsense_publisher_id' => 'AdSense publisher ID',
            'adsense_article_middle_slot' => 'orta reklam slot ID',
            'adsense_article_bottom_slot' => 'alt reklam slot ID',
            'certified_cmp_configured' => 'sertifikalı CMP',
        ];
    }
}
