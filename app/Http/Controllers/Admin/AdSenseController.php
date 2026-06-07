<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdSenseRequest;
use App\Support\Ads\AdSenseReadinessChecker;
use App\Support\Ads\AdSenseValidator;
use App\Support\Ads\AdSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdSenseController extends Controller
{
    public function edit(AdSenseReadinessChecker $readiness): View
    {
        return view('admin.adsense.edit', [
            'settings' => AdSettings::allForAdmin(),
            'readinessChecks' => $readiness->checks(),
            'readinessPassed' => $readiness->allPassed(),
        ]);
    }

    public function update(UpdateAdSenseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        AdSettings::setBoolean('adsense_verification_enabled', $request->boolean('adsense_verification_enabled'));
        AdSettings::setBoolean('adsense_ads_enabled', $request->boolean('adsense_ads_enabled'));
        AdSettings::setBoolean('adsense_auto_ads_enabled', $request->boolean('adsense_auto_ads_enabled'));
        AdSettings::setBoolean('certified_cmp_configured', $request->boolean('certified_cmp_configured'));
        AdSettings::setBoolean('privacy_policy_completed', $request->boolean('privacy_policy_completed'));
        AdSettings::setBoolean('cookie_policy_completed', $request->boolean('cookie_policy_completed'));
        AdSettings::setBoolean('contact_information_completed', $request->boolean('contact_information_completed'));
        AdSettings::setBoolean('editorial_information_completed', $request->boolean('editorial_information_completed'));

        AdSettings::setString('adsense_client_id', $this->sanitizedClientId($validated['adsense_client_id'] ?? null));
        AdSettings::setString('adsense_publisher_id', $this->sanitizedPublisherId($validated['adsense_publisher_id'] ?? null));
        AdSettings::setString('adsense_article_middle_slot', $this->sanitizedSlotId($validated['adsense_article_middle_slot'] ?? null));
        AdSettings::setString('adsense_article_bottom_slot', $this->sanitizedSlotId($validated['adsense_article_bottom_slot'] ?? null));

        return redirect()
            ->route('admin.adsense.edit')
            ->with('success', 'AdSense ve gizlilik ayarları güncellendi.');
    }

    private function sanitizedClientId(?string $value): string
    {
        $value = trim((string) $value);

        return AdSenseValidator::isValidClientId($value) ? $value : '';
    }

    private function sanitizedPublisherId(?string $value): string
    {
        $value = trim((string) $value);

        return AdSenseValidator::isValidPublisherId($value) ? $value : '';
    }

    private function sanitizedSlotId(?string $value): string
    {
        $value = trim((string) $value);

        return AdSenseValidator::isValidSlotId($value) ? $value : '';
    }
}
