<?php

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorEeatSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            'elif.kaya@modapusula.test' => [
                'expertise' => 'Kadın modası ve gardırop planlama',
                'bio' => <<<'BIO'
Elif Kaya, ModaPusula'da kadın modası ve günlük stil rehberleri üzerine çalışan editördür. Gardırop planlaması, renk uyumu ve mevsim geçişlerinde uygulanabilir kombin önerileri sunar.

Yazılarında hızlı tüketim baskısı yerine mevcut parçalarla çalışılabilir kontrol listeleri ön plandadır. Her içerik yayın öncesi özgünlük onayı ve insan editörü kontrolünden geçer.
BIO,
            ],
            'deniz.arslan@modapusula.test' => [
                'expertise' => 'Erkek modası ve aksesuar',
                'bio' => <<<'BIO'
Deniz Arslan, erkek modası, iş stili ve aksesuar konularında editoryal içerik üretir. Casual kombinlerden dış giyim ve ayakkabı bakımına kadar günlük hayatta tekrarlanabilir öneriler yazar.

ModaPusula'daki yazıları marka veya affiliate yönlendirmesi içermez; okuyucunun kendi gardırobuna uygun kararlar almasını hedefler.
BIO,
            ],
            'meryem.aksoy@modapusula.test' => [
                'expertise' => 'Sürdürülebilir moda',
                'bio' => <<<'BIO'
Meryem Aksoy, sürdürülebilir moda ve bilinçli tüketim üzerine çalışan yazardır. Bakım rutinleri, sezon geçişleri ve ikinci el alışveriş gibi konularda şeffaf, kaynak gösterimine açık rehberler hazırlar.

Yazıları çevresel iddialarda abartılı vaatlerden kaçınır; okuyucuya uzun ömürlü parça seçimi ve mevcut gardırobun verimli kullanımı için pratik çerçeveler sunar.
BIO,
            ],
        ];

        foreach ($profiles as $email => $data) {
            Author::query()
                ->where('email', $email)
                ->update([
                    'bio' => trim($data['bio']),
                    'expertise' => $data['expertise'],
                ]);
        }

        $this->command?->info('Yazar biyografi ve uzmanlık alanları güncellendi.');
    }
}
