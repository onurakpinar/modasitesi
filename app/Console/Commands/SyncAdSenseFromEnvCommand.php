<?php

namespace App\Console\Commands;

use App\Support\Ads\AdSettings;
use App\Support\Ads\AdSenseValidator;
use Illuminate\Console\Command;

class SyncAdSenseFromEnvCommand extends Command
{
    protected $signature = 'adsense:sync-env';

    protected $description = 'Ortam değişkenlerindeki AdSense ayarlarını site_settings tablosuna yazar';

    public function handle(): int
    {
        $clientId = config('adsense.client_id');
        $publisherId = config('adsense.publisher_id');

        if (is_string($clientId) && AdSenseValidator::isValidClientId($clientId)) {
            AdSettings::setString('adsense_client_id', $clientId);
            $this->line("Client ID: {$clientId}");
        }

        if (is_string($publisherId) && AdSenseValidator::isValidPublisherId($publisherId)) {
            AdSettings::setString('adsense_publisher_id', $publisherId);
            $this->line("Publisher ID: {$publisherId}");
        }

        if (config('adsense.verification_enabled')) {
            AdSettings::setBoolean('adsense_verification_enabled', true);
            $this->line('Doğrulama scripti: açık');
        }

        AdSettings::setBoolean('adsense_ads_enabled', (bool) config('adsense.ads_enabled'));
        AdSettings::setBoolean('adsense_auto_ads_enabled', (bool) config('adsense.auto_ads_enabled'));

        $this->info('AdSense ortam ayarları senkronize edildi.');

        return self::SUCCESS;
    }
}
