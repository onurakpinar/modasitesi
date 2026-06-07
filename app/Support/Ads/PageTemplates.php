<?php

namespace App\Support\Ads;

use App\Support\Legal\LegalPlaceholders;

class PageTemplates
{
    public static function hasUnresolvedPlaceholders(string $body): bool
    {
        return LegalPlaceholders::hasBlockingPlaceholders($body);
    }

    public static function containsLoremIpsum(string $body): bool
    {
        return (bool) preg_match('/lorem\s+ipsum/i', strip_tags($body));
    }

    public static function isPublicReady(string $body): bool
    {
        $text = trim(strip_tags($body));

        if ($text === '') {
            return false;
        }

        if (self::hasUnresolvedPlaceholders($body)) {
            return false;
        }

        if (self::containsLoremIpsum($body)) {
            return false;
        }

        return true;
    }

    public static function renderBody(string $body): string
    {
        return LegalPlaceholders::render($body);
    }

    public static function privacyPolicy(): string
    {
        return <<<'HTML'
<p class="text-stone-600">Son güncelleme: [GUNCELLEME_TARIHI]</p>

<p>ModaPusula (&ldquo;biz&rdquo;, &ldquo;site&rdquo;) olarak gizliliğinize önem veriyoruz. Bu politika, hangi verileri topladığımızı, nasıl kullandığımızı ve haklarınızı açıklar.</p>

<h2>Topladığımız Veriler</h2>
<ul>
<li>İletişim formu aracılığıyla gönüllü olarak ilettiğiniz ad, e-posta ve mesaj içeriği.</li>
<li>Sitenin teknik olarak çalışması için işlenen log kayıtları (IP adresi, tarayıcı türü, ziyaret zamanı).</li>
<li>Çerezler aracılığıyla toplanan kullanım verileri (bkz. Çerez Politikası).</li>
</ul>

<h2>Verilerin Kullanımı</h2>
<p>Topladığımız verileri yalnızca size geri dönüş yapmak, siteyi geliştirmek ve yasal yükümlülüklerimizi yerine getirmek için kullanırız. Verilerinizi izniniz olmadan üçüncü taraflarla pazarlama amacıyla paylaşmayız.</p>

<h2>Çerezler ve Üçüncü Taraf Reklamcılık</h2>
<p>Sitemizde, Google dahil üçüncü taraf sağlayıcılar reklam yayınlamak için çerez kullanabilir. Google, DART çerezi gibi çerezleri kullanarak kullanıcılarımıza sitemize ve internetteki diğer sitelere yaptıkları ziyaretlere dayalı reklamlar sunabilir.</p>
<p>Kullanıcılar, kişiselleştirilmiş reklamları <a href="https://adssettings.google.com" rel="noopener noreferrer">Google Reklam Ayarları</a> sayfasından devre dışı bırakabilir. Ayrıca <a href="https://www.aboutads.info" rel="noopener noreferrer">aboutads.info</a> adresinden üçüncü taraf satıcıların çerez kullanımını yönetebilirsiniz.</p>

<h2>KVKK Kapsamındaki Haklarınız</h2>
<p>6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) uyarınca; verilerinize erişme, düzeltme, silinmesini talep etme ve işlenmesine itiraz etme haklarına sahipsiniz. Bu haklarınızı kullanmak için [E-POSTA_ADRESİ] adresinden bize ulaşabilirsiniz.</p>

<h2>İletişim</h2>
<p>Bu politikayla ilgili sorularınız için: [E-POSTA_ADRESİ] — [ŞİRKET_ADI], [ŞİRKET_ADRESİ].</p>
HTML;
    }

    public static function cookiePolicy(): string
    {
        return <<<'HTML'
<p class="text-stone-600">Son güncelleme: [GUNCELLEME_TARIHI]</p>

<p>Bu sitede deneyiminizi iyileştirmek ve reklam sunmak için çerezler kullanılır.</p>

<h2>Çerez Türleri</h2>
<ul>
<li><strong>Zorunlu çerezler:</strong> Sitenin temel işlevleri için gereklidir.</li>
<li><strong>Analitik çerezler:</strong> Ziyaretçi davranışını anonim olarak ölçmek için kullanılır.</li>
<li><strong>Reklam çerezleri:</strong> Google ve diğer üçüncü taraf reklam ağları, ilgi alanlarınıza uygun reklam göstermek için çerez kullanabilir.</li>
</ul>

<h2>Çerezleri Yönetme</h2>
<p>Tarayıcı ayarlarınızdan çerezleri silebilir veya engelleyebilirsiniz. Ancak bazı çerezleri devre dışı bırakmak sitenin bazı bölümlerinin düzgün çalışmamasına neden olabilir.</p>
<p>Kişiselleştirilmiş Google reklamlarını <a href="https://adssettings.google.com" rel="noopener noreferrer">adssettings.google.com</a> adresinden kapatabilirsiniz.</p>

<p>Daha fazla bilgi için <a href="/gizlilik-politikasi">Gizlilik Politikamızı</a> inceleyebilirsiniz.</p>
HTML;
    }

    public static function about(): string
    {
        return <<<'HTML'
<p>ModaPusula, moda ve stil üzerine özgün, uygulanabilir içerikler yayınlayan bağımsız bir editoryal yayındır. Amacımız hızlı tüketim baskısı yaratmadan, okuyucularımızın mevcut gardıroplarını daha bilinçli yönetmelerine yardımcı olmaktır.</p>

<p>İçeriklerimiz, moda ve stil alanında deneyimli editörlerimiz tarafından hazırlanır ve yayınlanmadan önce gözden geçirilir. Marka önerisi, affiliate bağlantısı veya ücretli yerleştirme içermez; tüm yazılar editoryal bağımsızlık ilkesiyle üretilir.</p>

<p>Geri bildirim, düzeltme talebi veya iş birliği için <a href="/iletisim">İletişim</a> sayfamızdan bize ulaşabilirsiniz.</p>

<p>ModaPusula [ŞİRKET_ADI] tarafından yayınlanmaktadır.</p>
HTML;
    }

    public static function contact(): string
    {
        return <<<'HTML'
<p>Sorularınız, düzeltme talepleriniz veya iş birliği önerileriniz için bize aşağıdaki form üzerinden veya e-posta ile ulaşabilirsiniz. Editoryal ekibimiz tüm geri bildirimleri değerlendirir.</p>
HTML;
    }

    public static function terms(): string
    {
        return <<<'HTML'
<h2>Kullanım Koşulları</h2>
<p>[SITE_ADI] sitesini kullanarak bu koşulları kabul etmiş sayılırsınız. Site içeriği bilgilendirme amaçlıdır; profesyonel danışmanlık yerine geçmez.</p>
<p>İçeriklerin izinsiz kopyalanması yasaktır. Kullanıcılar siteyi yürürlükteki mevzuata uygun şekilde kullanmalıdır.</p>
HTML;
    }

    public static function editorial(): string
    {
        return <<<'HTML'
<h2>Yayın İlkeleri</h2>
<p>[SITE_ADI] yalnızca özgün, insan editörleri tarafından kontrol edilmiş içerikler yayınlar.</p>
<ul>
<li>Yapay zekâ çıktıları tek başına yayınlanmaz.</li>
<li>Kaynak gösterimi ve doğruluk önceliklidir.</li>
<li>Reklam içerikten bağımsız değerlendirilir.</li>
</ul>
HTML;
    }

    public static function corrections(): string
    {
        return <<<'HTML'
<h2>Düzeltme Politikası</h2>
<p>Hatalı veya güncelliğini yitirmiş bilgileri [ILETISIM_EPOSTA] adresine bildirebilirsiniz. Doğrulanan düzeltmeler makul sürede yayına alınır.</p>
HTML;
    }

    /**
     * @return array<string, string>
     */
    public static function defaultBodies(): array
    {
        return [
            'hakkimizda' => self::about(),
            'iletisim' => self::contact(),
            'gizlilik-politikasi' => self::privacyPolicy(),
            'cerez-politikasi' => self::cookiePolicy(),
            'kullanim-kosullari' => self::terms(),
            'yayin-ilkeleri' => self::editorial(),
            'duzeltme-politikasi' => self::corrections(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaultMetaDescriptions(): array
    {
        return [
            'hakkimizda' => 'ModaPusula hakkında: bağımsız moda editoryal yayınımızın misyonu ve yayın ilkeleri.',
            'iletisim' => 'ModaPusula iletişim: sorularınız, düzeltme talepleri ve iş birliği önerileri için bize ulaşın.',
            'gizlilik-politikasi' => 'ModaPusula gizlilik politikası: kişisel verilerin toplanması, kullanımı ve KVKK haklarınız.',
            'cerez-politikasi' => 'ModaPusula çerez politikası: kullanılan çerez türleri ve tercihlerinizi nasıl yönetebileceğiniz.',
        ];
    }
}
