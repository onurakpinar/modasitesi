<?php

namespace App\Console\Commands;

use App\Support\Content\ContentQualityAuditor;
use Illuminate\Console\Command;

class AuditContentQualityCommand extends Command
{
    protected $signature = 'content:audit-quality
                            {--min-template=3 : Şablon cümle eşiği (kaç yazıda geçerse raporla)}';

    protected $description = 'Yazıların tekrar, lexical diversity ve şablon cümle denetimini raporlar (silme yapmaz)';

    public function handle(ContentQualityAuditor $auditor): int
    {
        $threshold = max(2, (int) $this->option('min-template'));
        $report = $auditor->audit($threshold);

        $this->newLine();
        $this->info('Yazı kalite denetimi');
        $this->line("Toplam yazı: {$report['summary']['total']} | İşaretli (ince/şablon): {$report['summary']['flagged']}");
        $this->newLine();

        $this->table(
            ['ID', 'Başlık', 'Durum', 'Kelime', 'Tekrar cümle', 'Lexical div.', 'Şablon hit', 'İşaretli'],
            collect($report['posts'])->map(fn (array $row) => [
                $row['id'],
                mb_strimwidth($row['title'], 0, 42, '…'),
                $row['status'],
                $row['word_count'],
                $row['duplicate_sentences'],
                number_format($row['lexical_diversity'], 3),
                $row['template_hits'],
                $row['flagged'] ? 'EVET' : '—',
            ])->all()
        );

        if ($report['template_sentences'] !== []) {
            $this->newLine();
            $this->info("Ortak şablon cümleler (≥{$threshold} yazıda aynen geçen)");

            $this->table(
                ['Yazı sayısı', 'Cümle'],
                collect($report['template_sentences'])->map(fn (array $row) => [
                    $row['count'],
                    mb_strimwidth($row['sentence'], 0, 100, '…'),
                ])->all()
            );
        } else {
            $this->newLine();
            $this->comment("≥{$threshold} yazıda ortak geçen şablon cümle bulunamadı.");
        }

        $flagged = collect($report['posts'])->where('flagged', true)->values();

        if ($flagged->isNotEmpty()) {
            $this->newLine();
            $this->warn('İnce/şablon olarak işaretlenen yazılar:');

            foreach ($flagged as $row) {
                $reasons = [];

                if ($row['duplicate_sentences'] > 0) {
                    $reasons[] = "iç tekrar: {$row['duplicate_sentences']}";
                }

                if ($row['lexical_diversity'] < 0.45) {
                    $reasons[] = 'düşük lexical diversity';
                }

                if ($row['template_hits'] > 0) {
                    $reasons[] = "şablon cümle: {$row['template_hits']}";
                }

                $this->line("  [{$row['id']}] {$row['title']} — ".implode(', ', $reasons));
            }
        }

        return self::SUCCESS;
    }
}
