<?php

namespace App\Console\Commands;

use App\Support\Deployment\ReadinessStatus;
use App\Support\Deployment\SiteReadinessChecker;
use Illuminate\Console\Command;

class SiteReadinessCommand extends Command
{
    protected $signature = 'site:readiness';

    protected $description = 'Üretim öncesi site hazırlık kontrollerini çalıştırır';

    public function handle(SiteReadinessChecker $checker): int
    {
        $this->info('ModaPusula — Üretim Hazırlık Raporu');
        $this->newLine();

        $pass = 0;
        $warning = 0;
        $fail = 0;

        foreach ($checker->checks() as $check) {
            $color = match ($check['status']) {
                ReadinessStatus::Pass => 'green',
                ReadinessStatus::Warning => 'yellow',
                ReadinessStatus::Fail => 'red',
            };

            $this->line(sprintf(
                '[<%s>%s</>] %s — %s',
                $color,
                $check['status']->value,
                $check['label'],
                $check['detail']
            ));

            match ($check['status']) {
                ReadinessStatus::Pass => $pass++,
                ReadinessStatus::Warning => $warning++,
                ReadinessStatus::Fail => $fail++,
            };
        }

        $this->newLine();
        $this->line("Özet: {$pass} PASS, {$warning} WARNING, {$fail} FAIL");
        $this->comment('Not: 20 yayın ve 4 kategori eşikleri proje içi kalite barajıdır; Google tarafından açıklanmış resmi sayısal koşullar değildir.');

        if ($fail > 0) {
            $this->error('Üretim için kritik eksikler var.');

            return self::FAILURE;
        }

        if ($warning > 0) {
            $this->warn('Uyarılar mevcut; deploy öncesi gözden geçirin.');
        } else {
            $this->info('Tüm kontroller geçti.');
        }

        return self::SUCCESS;
    }
}
