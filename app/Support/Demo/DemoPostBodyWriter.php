<?php

namespace App\Support\Demo;

class DemoPostBodyWriter
{
    /**
     * @param  array<string, mixed>  $brief
     */
    public static function html(array $brief): string
    {
        $sections = self::sections($brief);
        $parts = ['<p>'.self::intro($brief).'</p>'];

        foreach ($sections as $index => $heading) {
            $parts[] = '<h2>'.e($heading).'</h2>';
            $parts[] = '<p>'.self::paragraph($brief, $heading, $index, 0).'</p>';
            $parts[] = '<p>'.self::paragraph($brief, $heading, $index, 1).'</p>';
            $parts[] = '<p>'.self::paragraph($brief, $heading, $index, 2).'</p>';
        }

        $parts[] = '<h2>Pratik kontrol listesi</h2>';
        $parts[] = '<p>'.self::checklist($brief).'</p>';
        $parts[] = '<h2>Sonuç</h2>';
        $parts[] = '<p>'.self::conclusion($brief).'</p>';

        return implode("\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $brief
     * @return array<int, string>
     */
    private static function sections(array $brief): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) ($brief['subheadings'] ?? '')) ?: [];

        return array_values(array_filter(array_map(
            fn (string $line) => trim(ltrim(trim($line), '- ')),
            $lines
        )));
    }

    /**
     * @param  array<string, mixed>  $brief
     */
    private static function intro(array $brief): string
    {
        $audience = (string) ($brief['target_audience'] ?? 'okurlar');
        $summary = (string) ($brief['content_summary'] ?? '');
        $title = (string) ($brief['title_suggestion'] ?? 'Moda rehberi');

        return implode(' ', [
            $summary,
            "Bu rehber, {$audience} için hazırlanmış özgün bir editoryal içeriktir; marka önerisi, affiliate bağlantısı veya otomatik üretilmiş metin içermez.",
            "{$title} başlığı altında anlatılan ilkeler, mevcut gardırobunuzla uygulanabilir ve günlük hayatta tekrarlanabilir adımlara dönüştürülebilir.",
            'Aşağıdaki bölümlerde her konuyu önce neden önemli olduğuyla, ardından uygulanabilir örneklerle ele alıyoruz.',
            'Amacımız hızlı tüketim baskısı yaratmak değil; bilinçli seçim yapmanız için net bir çerçeve sunmaktır.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $brief
     */
    private static function paragraph(array $brief, string $heading, int $sectionIndex, int $paragraphIndex): string
    {
        $audience = (string) ($brief['target_audience'] ?? 'okur');
        $notes = (string) ($brief['notes'] ?? '');
        $seed = crc32($heading.'|'.$sectionIndex.'|'.$paragraphIndex);

        $openers = [
            "{$heading} konusuna yaklaşırken önce mevcut alışkanlıklarınızı gözlemlemek işe yarar.",
            "Bu bölümde {$heading} başlığını günlük kombin kararlarına nasıl taşıyacağınızı sade bir dille açıklıyoruz.",
            "{$heading} ile ilgili en sık yapılan hata, trend baskısıyla ihtiyaçları karıştırmaktır.",
            "Deneyimli editörlerimiz, {$heading} konusunda önce deneme–gözlem–düzeltme döngüsünü önerir.",
        ];

        $middles = [
            'Dolabınızdaki parçaları kategorilere ayırırken yalnızca renge değil, kumaş dokusuna ve kullanım sıklığına da bakın.',
            'Aynı parçayı farklı katmanlarla denediğinizde siluetin nasıl değiştiğini not almak, sonraki alışverişlerinizi daha bilinçli kılar.',
            'Bir kombini değerlendirirken ayna karşısında duruş, oturuş ve yürüyüş halindeki görünümü ayrı ayrı kontrol etmek gerekir.',
            'Gereksiz yeni parça almak yerine mevcut parçaların bakımını yapmak çoğu zaman daha hızlı ve ekonomik sonuç verir.',
            'Renk uyumunu test ederken gün ışığında bakmak, yapay ışıktaki yanıltıcı tonları eler.',
        ];

        $closers = [
            "{$audience} için bu adım, hem zaman hem bütçe açısından sürdürülebilir bir rutin oluşturur.",
            'Kısa notlar tutmak, bir ay sonra hangi kararın işe yaradığını hatırlamanızı kolaylaştırır.',
            'Bu yaklaşım, gardırobunuzu kişisel yaşam temponuza göre özelleştirmenize olanak tanır.',
            'Sonuç olarak daha tutarlı, özenli ve kendinize yakışan bir görünüm elde edersiniz.',
        ];

        if ($notes !== '') {
            $closers[] = "Editoryal not: {$notes}";
        }

        $opener = $openers[$seed % count($openers)];
        $middle = $middles[($seed >> 3) % count($middles)];
        $closer = $closers[($seed >> 6) % count($closers)];

        $extra = [
            'Üst giyim ve alt giyim arasında oran kurarken bel hattını abartmadan vurgulamak dengeli bir siluet sağlar.',
            'Aksesuar seçiminde bir kombinde tek odak noktası bırakmak, görünümü kalabalıklaştırmadan derinlik katar.',
            'Mevsim geçişlerinde ince katmanlar eklemek, tek parçayla çok farklı ortamlara uyum sağlamanıza yardımcı olur.',
            'Kaliteyi değerlendirirken dikiş sıklığı, astar durumu ve düğme–fermuar işçiliğine bakmak iyi bir başlangıçtır.',
            'Gardırop envanteri çıkarırken “son bir yılda kaç kez giydim?” sorusu, tutma–bağış kararını netleştirir.',
        ];

        $extraSentence = $extra[($seed >> 9) % count($extra)];

        return implode(' ', [$opener, $middle, $extraSentence, $closer]);
    }

    /**
     * @param  array<string, mixed>  $brief
     */
    private static function checklist(array $brief): string
    {
        $sections = self::sections($brief);
        $items = array_slice($sections, 0, 5);
        $sentences = [
            'Aşağıdaki maddeleri uygulamadan önce dolabınızdan rastgele değil, gerçekten kullandığınız parçaları seçin.',
            'Her maddeyi bir hafta boyunca gözlemleyip not alırsanız hangi kuralın size uyduğunu net görürsünüz.',
        ];

        foreach ($items as $item) {
            $sentences[] = "{$item} için küçük bir deneme kombini oluşturun ve gün içinde konforu değerlendirin.";
        }

        $sentences[] = 'Fotoğraf çekerek veya kısa notlar alarak ilerlemenizi kaydetmek, sonraki düzenlemeleri kolaylaştırır.';

        return implode(' ', $sentences);
    }

    /**
     * @param  array<string, mixed>  $brief
     */
    private static function conclusion(array $brief): string
    {
        $title = (string) ($brief['title_suggestion'] ?? 'Bu rehber');

        return implode(' ', [
            "{$title} konusunda tek bir doğru cevap yoktur; önemli olan kendi yaşam ritminize, ikliminize ve konfor beklentinize uygun kararlar vermektir.",
            'Burada anlatılan ilkeler, hızlı moda döngüsüne kapılmadan gardırobunuzu yönetmenize yardımcı olmak için tasarlandı.',
            'Yeni parça eklemeden önce mevcut parçaları farklı şekillerde kombinlemeyi deneyin; çoğu zaman ihtiyaç duyduğunuz çeşitlilik zaten dolabınızdadır.',
            'Düzenli bakım, doğru saklama ve bilinçli alışveriş alışkanlıkları uzun vadede hem bütçenizi hem de stil tutarlılığınızı korur.',
            'Sorularınız veya düzeltme talepleriniz için iletişim sayfamız üzerinden bize ulaşabilirsiniz; editoryal ekibimiz geri bildirimleri değerlendirir.',
        ]);
    }
}
