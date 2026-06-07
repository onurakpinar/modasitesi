<?php

namespace App\Support\Ads;

class PageTemplates
{
    public static function hasUnresolvedPlaceholders(string $body): bool
    {
        return (bool) preg_match('/\[[A-ZÇĞİÖŞÜ0-9_\s]+\]/u', $body);
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

    public static function privacyPolicy(): string
    {
        return <<<'HTML'
<h2>Veri Sorumlusu</h2>
<p>[ISLETME_UNVANI] (“biz”, “site”), [SITE_URL] adresindeki [SITE_ADI] yayınını işletir. Bu gizlilik politikası, kişisel verilerinizin nasıl toplandığını, kullanıldığını ve korunduğunu açıklar.</p>
<p>İletişim: [ILETISIM_EPOSTA]</p>

<h2>Topladığımız Veriler</h2>
<p>İletişim formu aracılığıyla gönderdiğiniz ad, e-posta adresi ve mesaj içeriği; teknik loglar (IP adresi, tarayıcı bilgisi, erişim zamanı) ve çerez verileri işlenebilir.</p>

<h2>Çerezler</h2>
<p>Site işlevselliği ve tercihlerinizin hatırlanması için zorunlu çerezler kullanılabilir. Reklam ve analitik çerezleri yalnızca açık rızanız ve geçerli yasal gereklilikler çerçevesinde devreye alınır.</p>

<h2>Reklam ve Üçüncü Taraf Sağlayıcılar</h2>
<p>Google dahil üçüncü taraf sağlayıcılar, reklam sunumu sırasında çerez kullanabilir. Google’ın reklam çerezleri, kullanıcıların bu siteye ve diğer sitelere yaptığı ziyaretlere dayanarak kişiselleştirilmiş reklamlar sunmak için kullanılabilir.</p>
<p>Kullanıcılar, <a href="https://www.google.com/settings/ads" rel="noopener noreferrer">Google Reklam Ayarları</a> üzerinden kişiselleştirilmiş reklamları devre dışı bırakabilir. Ayrıca <a href="https://optout.aboutads.info/" rel="noopener noreferrer">aboutads.info</a> üzerinden üçüncü taraf sağlayıcıların çerez kullanımını yönetebilirsiniz.</p>
<p>Reklam teknolojisi ortakları ve üçüncü taraf sağlayıcılar hakkında ayrıntılı bilgi için Google’ın reklam politikalarını ve ilgili sağlayıcıların gizlilik bildirimlerini inceleyebilirsiniz.</p>

<h2>İletişim Formu Verileri</h2>
<p>İletişim formu ile gönderilen bilgiler yalnızca talebinize yanıt vermek amacıyla işlenir. Mesajlar yetkisiz erişime karşı korunur ve yalnızca yetkili ekip tarafından görüntülenir.</p>

<h2>Saklama ve Silme</h2>
<p>Veriler, işleme amacının gerektirdiği süre boyunca saklanır. Saklama süresi dolduğunda veya silme talebiniz üzerine veriler makul sürede silinir veya anonimleştirilir.</p>

<h2>Haklarınız</h2>
<p>KVKK ve ilgili mevzuat kapsamında erişim, düzeltme, silme ve itiraz haklarınızı [ILETISIM_EPOSTA] adresine yazarak kullanabilirsiniz.</p>

<h2>Güncellemeler</h2>
<p>Bu politika gerektiğinde güncellenebilir. Son güncelleme: [GUNCELLEME_TARIHI]</p>
HTML;
    }

    public static function cookiePolicy(): string
    {
        return <<<'HTML'
<h2>Çerez Nedir?</h2>
<p>Çerezler, tarayıcınıza kaydedilen küçük metin dosyalarıdır. Site deneyimini iyileştirmek ve yasal gerekliliklere uymak için kullanılabilir.</p>

<h2>Kullandığımız Çerez Türleri</h2>
<ul>
<li><strong>Zorunlu çerezler:</strong> Oturum ve güvenlik için gereklidir.</li>
<li><strong>Tercih çerezleri:</strong> Dil veya görünüm tercihlerinizi hatırlar.</li>
<li><strong>Reklam çerezleri:</strong> Yalnızca onayınız ve sertifikalı bir CMP yapılandırması sonrasında devreye alınır.</li>
</ul>

<h2>Üçüncü Taraf Çerezleri</h2>
<p>Google AdSense gibi üçüncü taraf sağlayıcılar, reklam sunumu sırasında çerez kullanabilir. Avrupa Ekonomik Alanı, Birleşik Krallık ve İsviçre ziyaretçileri için Google sertifikalı bir CMP kullanılmalıdır.</p>

<h2>Çerez Tercihlerinizi Yönetme</h2>
<p>Çerez tercihlerinizi tarayıcı ayarlarınızdan veya sitede sunulan onay aracından yönetebilirsiniz.</p>

<h2>İletişim</h2>
<p>Sorularınız için: [ILETISIM_EPOSTA]</p>
HTML;
    }

    public static function about(): string
    {
        return <<<'HTML'
<h2>Hakkımızda</h2>
<p>[SITE_ADI], moda ve stil üzerine özgün içerikler sunan bağımsız bir yayın platformudur.</p>
<p>İşletmeci: [ISLETME_UNVANI]</p>
<p>İletişim: [ILETISIM_EPOSTA]</p>
HTML;
    }

    public static function contact(): string
    {
        return <<<'HTML'
<h2>İletişim</h2>
<p>Genel sorularınız, iş birliği teklifleri ve düzeltme talepleri için bizimle iletişime geçebilirsiniz.</p>
<p>E-posta: [ILETISIM_EPOSTA]</p>
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
}
