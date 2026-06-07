# ModaPusula — Final Üretim ve AdSense Hazırlık Raporu

**Denetim tarihi:** 6 Haziran 2026 (2. tur — tam yeniden test)  
**Kapsam:** Laravel moda yayın sitesi — production denetimi, AdSense politika uyumu, güvenlik, SEO, mobil uyum  
**Amaç:** Eksikleri kapatmak, politika risklerini azaltmak, yayına hazırlık — yeni özellik eklemek değil.

---

## Genel durum

| Alan | Durum |
|------|--------|
| Teknik altyapı (Laravel, Docker, testler, güvenlik katmanları) | **Hazır** |
| Otomatik testler | **212/212 geçti** |
| İçerik ve editoryal veri | **Eksik** (fresh seed sonrası 0 yayın) |
| Politika sayfaları | **Taslak** (yayınlanmamış) |
| Gerçek domain / HTTPS (yerel ortam) | **Yok** |
| AdSense başvurusu | **Henüz başvuru yapılmamalı** |

Teknik olarak site sağlam bir temele oturmuş durumda. Ancak **gerçek içerik, gerçek yazar profilleri, gerçek iletişim bilgileri, yayınlanmış politika sayfaları, üretim domain’i ve HTTPS** olmadan Google AdSense başvurusu yapılmamalıdır. Bu rapor sahte bilgi üretmeyi veya kontrol mekanizmalarını atlatmayı önermez.

---

## Tamamlanan özellikler

- Public moda yayın sitesi: ana sayfa, yazılar, kategori, etiket, yazar, arama, sabit sayfalar
- Admin paneli: yazı/kategori/etiket/yazar/sayfa yönetimi, medya, site ayarları, AdSense ayarları
- İçerik kalite kapıları: min. 900 kelime, excerpt/meta/kapak/alt metin, insan kontrolü ve özgünlük onayı
- İçerik takvimi (brief) modülü — admin-only, otomatik yayın yok
- SEO: sitemap.xml, rss.xml, robots.txt, canonical, JSON-LD, slug 301 yönlendirmeleri
- AdSense uyum katmanı: `AdEligibility`, CMP zorunluluğu, reklamlar yalnızca uygun yazı detayında
- Güvenlik: CSRF, rate limit, admin brute-force koruması, CSP ve güvenlik başlıkları, güvenli görsel yükleme
- Deploy: Dockerfile, Coolify rehberi, `/health` ve `/up`, `site:readiness`, `site:security-check`
- Otomatik test paketi: 212 test (AdSense, SEO, güvenlik, deploy, final denetim dahil)
- Tamamlanma bayrakları (gizlilik/çerez/iletişim) gerçek sayfa ve e-posta doğrulamasıyla korunuyor
- Sitemap/footer yalnızca placeholder’sız, public-ready sabit sayfaları listeler

---

## Teknik altyapı

### Çalıştırılan komutlar ve sonuçlar

| Komut | Sonuç |
|-------|--------|
| `composer validate` | **PASS** — `composer.json` geçerli |
| `composer install` | **PASS** |
| `php artisan migrate:fresh --seed` | **PASS** — CategorySeeder, PageSeeder, ContentBriefSeeder |
| `php artisan test` | **PASS** — 212 test, 1001 assertion |
| `php artisan route:list` | **PASS** — 79 route |
| `php artisan config:clear` | **PASS** |
| `php artisan cache:clear` | **PASS** |
| `php artisan view:clear` | **PASS** |
| `php artisan optimize` | **PASS** (not: sonrasında testlerde CSRF 419 riski — `optimize:clear` ile giderilir) |
| `npm install` | **PASS** |
| `npm run build` | **PASS** — Vite asset derlemesi tamam |
| `php artisan site:security-check` | **PASS** (exit 0) — yerel ortam uyarıları bilgi amaçlı |
| `php artisan site:readiness` | **FAIL** (exit 1) — 7 PASS, 8 WARNING, 5 FAIL (fresh seed beklenen eksikler) |

### Route özeti

- Public: `/`, `/yazilar`, `/kategori/{slug}`, `/yazi/{slug}`, `/etiket/{slug}`, `/yazar/{slug}`, `/arama`, 7 sabit sayfa, `/sitemap.xml`, `/rss.xml`, `/robots.txt`, `/ads.txt`, `/health`, `/up`
- Admin: `/admin/*` (login, dashboard, CRUD, ayarlar, AdSense)
- Toplam: **79 route**

### Public sayfa smoke testleri

`tests/Feature/FinalReadinessAuditTest` ile doğrulandı (yayınlı fixture verisiyle):

| URL | Beklenen | Sonuç |
|-----|----------|--------|
| `/` | 200 | **PASS** |
| `/yazilar` | 200 | **PASS** |
| `/kategori/{slug}` | 200 | **PASS** |
| `/yazi/{slug}` | 200 | **PASS** |
| `/etiket/{slug}` | 200 | **PASS** |
| `/yazar/{slug}` | 200 | **PASS** |
| `/arama?q=stil` | 200 | **PASS** |
| `/hakkimizda` … `/duzeltme-politikasi` | 200 | **PASS** (test fixture’da yayınlı) |
| `/sitemap.xml` | 200 | **PASS** |
| `/rss.xml` | 200 | **PASS** |
| `/robots.txt` | 200 | **PASS** |
| `/ads.txt` | 200 | **PASS** |
| `/health` | 200 | **PASS** |
| `/up` | 200 | **PASS** |
| Geçersiz URL | 404 | **PASS** |

**Not:** `migrate:fresh --seed` sonrası politika sayfaları varsayılan olarak **draft** kalır; canlı ortamda admin panelinden yayınlanmalıdır.

---

## Güvenlik durumu

| Madde | Durum | Not |
|-------|--------|-----|
| Admin yetkilendirme (rol tabanlı) | **PASS** | Süper admin / editör ayrımı |
| Brute-force koruması (`admin-login` rate limit) | **PASS** | Testlerle doğrulandı |
| CSRF (web middleware) | **PASS** | İletişim ve admin formları |
| XSS (Blade escape, CSP) | **PASS** | CSP `default-src` public route’larda |
| SQL injection (Eloquent) | **PASS** | Parametreli sorgular |
| Dosya yükleme güvenliği | **PASS** | `SecureImageUploader`, MIME/boyut kontrolü |
| Mass assignment (`$fillable` / `$guarded`) | **PASS** | Modellerde tanımlı |
| Güvenlik başlıkları | **PASS** | X-Content-Type-Options, Referrer-Policy, X-Frame-Options |
| CSP | **PASS** | Public sayfalarda aktif |
| Hassas veri logları | **PASS** | `/health` ortam bilgisi sızdırmıyor |
| Production debug ayarları | **WARNING** | Yerelde `APP_DEBUG=true` — üretimde `false` |
| Session güvenliği | **WARNING** | Yerelde `SESSION_SECURE_COOKIE=false` — üretimde `true` + HTTPS |

---

## SEO durumu

| Madde | Durum | Not |
|-------|--------|-----|
| Sitemap yalnızca public içerik | **PASS** | Taslak/zamanlanmış yazılar dahil değil |
| robots.txt crawler engeli | **PASS** | `/admin`, `/arama` disallow; içerik yolları allow |
| Canonical bağlantılar | **PASS** | `SeoBuilder` ile üretiliyor |
| Arama sayfası noindex | **PASS** | `noindex, follow` |
| Preview sayfası noindex | **PASS** | Preview layout ayrı |
| JSON-LD geçerliliği | **PASS** | Testlerle doğrulandı |
| Slug redirect 301 | **PASS** | Eski slug → yeni slug |
| Bozuk internal link | **PASS** | Menü route tabanlı; boş kategori nav’dan gizlenir |

---

## AdSense uyum durumu

### Politika risk taraması (kod + test)

| Risk | Durum |
|------|--------|
| Scraping / kopya içerik sistemi | **PASS** — Kod tabanında yok; editorial guide yasaklıyor |
| Otomatik yayın (spin/AI doğrudan yayın) | **PASS** — Brief modülü admin-only; scheduler yalnızca onaylı zamanlanmış yazılar |
| Lorem ipsum yayında | **PASS** — Fresh seed’de yayın yok; readiness kontrolü var |
| Boş sayfa | **WARNING** — Politika sayfaları draft; yayınlanmadan başvuru yapılmamalı |
| Under construction | **PASS** — Böyle bir sayfa yok |
| Reklam içerikten baskın | **PASS** — Max 2 slot, yalnızca uzun yazı detayında |
| Reklam kutusu içerik kartına benziyor | **PASS** — `.ad-slot` + “Advertisement” etiketi, aside semantiği |
| Reklam menü/buton yakını | **PASS** — Slotlar yazı gövdesi içinde, navigasyondan uzak |
| Click teşviki metinleri | **PASS** — `AdSenseComplianceTest` yasaklı metinleri kontrol eder |
| Pop-up / pop-under | **PASS** — Yok |
| Preview sayfasında reklam | **PASS** — Preview layout’ta AdSense yok |
| Arama sayfasında reklam | **PASS** — `AdEligibility` yazı detayı şart |
| Politika sayfalarında reklam | **PASS** — Reklamlar yalnızca `posts/show` |
| Hata sayfalarında reklam | **PASS** — 404 minimal layout, AdSense yok |
| Admin sayfalarında reklam | **PASS** — Admin layout’ta AdSense yok |
| CMP olmadan reklam açılması | **PASS** — `AdEligibility` + `AdSettings` üçlü kapı |
| ads.txt sahte publisher ID | **PASS** — ID yoksa yorum satırı; sahte satır üretilmez |
| Gizlilik politikası çerez açıklamaları | **WARNING** — Şablon mevcut; gerçek bilgilerle tamamlanıp yayınlanmalı |
| İletişim bilgisi sahte kabul | **PASS** — `contact_information_completed` bayrağı admin onayı gerektirir |

### Reklam gösterim koşulları (`AdEligibility`)

Reklamlar **yalnızca** şu koşulların tamamı sağlandığında açılır:

1. `APP_ENV=production`
2. AdSense reklamları admin’de açık
3. Sertifikalı CMP yapılandırılmış
4. Geçerli `adsense_client_id`
5. Yazı public görünür durumda
6. İçerik ≥ 900 kelime

---

## Eksik gerçek bilgiler

Aşağıdakiler **gerçek veri girilmeden** site AdSense başvurusuna uygun sayılmaz:

| Alan | Mevcut durum (fresh seed / yerel) |
|------|-----------------------------------|
| Site adı | Boş |
| Logo / marka | Tanımsız |
| İletişim e-postası | Boş |
| İşletme / yayıncı bilgisi | Politika şablonlarında placeholder |
| Yazar profilleri (biyografi, fotoğraf) | Yok |
| Yayınlanmış yazılar (≥20, ≥900 kelime) | 0 |
| İçerikli aktif kategoriler (≥4) | 0 |
| Politika sayfaları (yayınlı) | 7/7 draft |
| Production domain + HTTPS | Yerel `http://localhost:8000` |
| AdSense Publisher ID / Client ID | Girilmemiş |
| Sertifikalı CMP | Yapılandırılmamış |
| Süper yönetici hesabı | Yok (`admin:create` gerekli) |

---

## Admin panelinden doldurulması gereken alanlar

1. **Site Ayarları** — site adı, slogan, logo, favicon, sosyal bağlantılar
2. **İletişim** — gerçek e-posta, adres (varsa), iletişim formu alıcısı; “bilgiler tamamlandı” onayı
3. **Yazarlar** — en az bir gerçek yazar: ad, slug, kısa/uzun biyografi, profil fotoğrafı
4. **Kategoriler** — en az 4 aktif kategori, her birinde yayınlı yazı
5. **Yazılar** — en az 20 yayın: 900+ kelime, excerpt, meta title/description, kapak + alt metin, insan kontrolü ve özgünlük onayı işaretli
6. **Sabit sayfalar** — hakkımızda, iletişim, gizlilik, çerez, kullanım koşulları, yayın ilkeleri, düzeltme politikası: placeholder’ları gerçek bilgilerle değiştirip **yayınla**
7. **AdSense ayarları** — başvuru öncesi reklamları **kapalı** tutun; yalnızca doğrulama scripti (gerekirse) ve publisher bilgileri

---

## Google AdSense panelinde manuel yapılması gerekenler

1. [Google AdSense](https://www.google.com/adsense/) hesabı oluşturma / mevcut hesap
2. Site ekleme — **canlı HTTPS domain** ile
3. Site doğrulama (meta tag veya DNS — admin panelindeki doğrulama seçeneği)
4. `ads.txt` — onaylı Publisher ID admin’e girildikten sonra `/ads.txt` otomatik güncellenir
5. Reklam birimleri oluşturma (orta/alt slot ID’leri)
6. Politika incelemesi tamamlanana kadar reklamları sitede **açmayın**
7. Onay sonrası: CMP + reklam anahtarlarını admin’den etkinleştirin

---

## Sertifikalı CMP kurulumu

1. Google’ın [CMP partner listesinden](https://support.google.com/adsense/answer/13554116) sertifikalı bir sağlayıcı seçin (ör. Cookiebot, Quantcast Choice, iubenda vb.)
2. CMP script’ini siteye entegre edin — **kullanıcı onayı olmadan reklam yüklenmemeli**
3. Admin → AdSense → “Sertifikalı CMP yapılandırıldı” bayrağını yalnızca CMP canlı test edildikten sonra işaretleyin
4. Gizlilik ve çerez politikalarında CMP’nin topladığı verileri açıklayın

---

## ads.txt kurulumu

1. AdSense onayından sonra Publisher ID’nizi alın (`pub-XXXXXXXXXXXXXXXX`)
2. Admin panelinde `adsense_publisher_id` alanına girin
3. `https://alandiniz.com/ads.txt` adresini kontrol edin — şu formatta satır görünmeli:
   ```
   google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0
   ```
4. Publisher ID girilmeden sistem **sahte satır üretmez** (yalnızca açıklayıcı yorum döner) — bu kasıtlı güvenlik önlemidir

---

## Başvuru öncesi yapılmaması gerekenler

- Sahte yazar, sahte iletişim veya placeholder metinle “tamamlandı” bayraklarını işaretlemek
- 900 kelimenin altında veya kopya içerik yayınlamak
- CMP olmadan reklamları açmak
- Politika sayfalarını yayınlamadan AdSense başvurusu yapmak
- `APP_DEBUG=true` ile üretime çıkmak
- Scraping, spin veya otomatik içerik araçları kullanmak
- Reklam yerleşimini menü/buton yakınına taşımak veya “tıklayın” teşviki eklemek
- Onay öncesi agresif reklam formatları (pop-up, auto ads açık) kullanmak

---

## Production deploy adımları

```bash
# 1. Ortam
cp .env.example .env
# .env içinde: APP_ENV=production, APP_DEBUG=false, APP_URL=https://alandiniz.com
# SESSION_SECURE_COOKIE=true, LOG_LEVEL=warning
php artisan key:generate --show   # çıktıyı APP_KEY olarak kaydedin

# 2. Bağımlılıklar ve derleme
composer install --no-dev --optimize-autoloader
npm ci --ignore-scripts
npm run build

# 3. Veritabanı
php artisan migrate --force
php artisan db:seed --force          # isteğe bağlı: kategori ve sayfa şablonları
php artisan storage:link

# 4. İlk admin
php artisan admin:create

# 5. Önbellek (deploy sonrası)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 6. Zamanlanmış görevler (cron)
# * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1

# 7. Denetim
php artisan site:security-check
php artisan site:readiness
```

**Coolify / Docker:** `docs/deployment.md` — Dockerfile, port 8080, liveness `/up`, readiness `/health`.

**Deploy sonrası test:**

```bash
curl -sS https://alandiniz.com/health
curl -sS https://alandiniz.com/robots.txt
curl -sS https://alandiniz.com/ads.txt
php artisan test   # CI ortamında
```

---

## Mobil görünüm (360px — kod incelemesi)

| Bileşen | Durum | Not |
|---------|--------|-----|
| Header | **PASS** | `px-4`, mobil hamburger menü (`lg:hidden`) |
| Menü | **PASS** | Alpine.js ile açılır nav, escape ile kapanır |
| Yazı kartları | **PASS** | Responsive grid, `max-w-6xl` container |
| Yazı detay | **PASS** | `sm:py-14`, prose responsive |
| Görseller | **PASS** | `max-width: 100%`, lazy loading bileşenleri |
| Reklam alanları | **PASS** | `max-w-full overflow-hidden`, clearfix |
| Footer | **PASS** | Stack layout mobilde |
| İletişim formu | **PASS** | Full-width input’lar |
| Admin login | **PASS** | Responsive padding |
| Admin sidebar | **PASS** | `lg:pl-64`, mobil overlay sidebar |

Gerçek cihazda (iPhone SE / 360px Chrome DevTools) son bir görsel QA önerilir.

---

## PASS / WARNING / FAIL özeti

### PASS (teknik ve politika altyapısı)

- 212 otomatik test geçti
- Scraping / otomatik yayın / pop-up yok
- AdSense reklamları katı eligibility ile sınırlandırılmış
- CMP olmadan reklam yüklenmez
- Reklamlar preview, arama, politika, 404, admin sayfalarında yok
- ads.txt sahte ID üretmiyor
- CSRF, rate limit, CSP, güvenlik başlıkları aktif
- Sitemap/RSS/robots/canonical/JSON-LD/301 redirect çalışıyor
- Docker + health endpoint + scheduler altyapısı hazır
- İçerik kalite kapıları (900 kelime, meta, kapak, onay bayrakları) kodda mevcut

### WARNING

- Yerel ortam: `APP_ENV=local`, `APP_DEBUG=true`, HTTP (üretimde düzeltilmeli)
- `SESSION_SECURE_COOKIE=false` (üretimde `true`)
- Fresh seed: 0 yayın, 0 içerikli kategori (proje eşiği: 20 yazı, 4 kategori)
- Politika sayfaları draft
- Publisher ID ve CMP yapılandırılmamış (reklamlar kapalı — doğru)
- Logo tanımsız
- `php artisan optimize` sonrası test ortamında CSRF 419 — deploy/CI’da `optimize:clear` veya test öncesi temizlik

### FAIL (başvuru öncesi çözülmeli)

- Süper yönetici hesabı yok
- Gerçek site adı girilmemiş
- Geçerli iletişim e-postası yok
- Sabit politika sayfaları yayınlanmamış (7/7 draft)
- Gerçek yazar profili ve yayınlı içerik yok

---

## Bu denetimde düzeltilen hatalar

### 1. tur

1. **`SiteReadinessChecker::draftLeakDetail()`** — Taslak sızıntısı PASS mesajı düzeltildi.
2. **`FinalReadinessAuditTest`** — Public route smoke ve AdSense politika testleri eklendi.

### 2. tur (bu denetim)

1. **`tests/TestCase.php`** — `withoutVite()` ile manifest eksikliğinden kaynaklanan public/admin 500 hataları test ortamında giderildi; `SiteAssetsTest` gerçek Vite çıktısını `withVite()` ile doğrulamaya devam ediyor.
2. **`PublicContent::publishedStaticPages()`** — Placeholder içeren “yayınlı” sabit sayfalar artık sitemap ve footer’da listelenmiyor (404 URL riski kapatıldı).
3. **`UpdateAdSenseRequest`** — `privacy_policy_completed`, `cookie_policy_completed`, `contact_information_completed`, `editorial_information_completed` bayrakları; ilgili sayfa yayınlanmadan veya geçerli e-posta girilmeden işaretlenemez (sahte tamamlanma engeli).
4. **Yeni testler** — `test_placeholder_iceren_yayinli_sabit_sayfalar_sitemap_icinde_gorunmez`, `test_iletisim_bilgisi_eposta_olmadan_tamamlanamaz`.

---

## Değiştirilen dosyalar

| Dosya | Değişiklik |
|-------|------------|
| `app/Support/Deployment/SiteReadinessChecker.php` | Taslak sızıntısı PASS mesajı |
| `app/Support/PublicContent.php` | `publishedStaticPages()` isPublicReady filtresi |
| `app/Http/Requests/Admin/UpdateAdSenseRequest.php` | Tamamlanma bayrağı doğrulaması |
| `tests/TestCase.php` | Global `withoutVite()` |
| `tests/Feature/SiteAssetsTest.php` | `withVite()` override |
| `tests/Feature/Seo/SeoInfrastructureTest.php` | Placeholder sitemap testi |
| `tests/Feature/Ads/AdSenseModuleTest.php` | İletişim tamamlanma testi |
| `tests/Feature/FinalReadinessAuditTest.php` | Final denetim testleri |
| `docs/final-readiness-report.md` | Bu rapor |

---

## Sonuç

### Henüz başvuru yapılmamalı

Google AdSense başvurusu için site **teknik olarak iyi konumda** ancak **editoryal ve operasyonel gereksinimler karşılanmamış** durumda. Aşağıdakiler tamamlanmadan başvuru yapmayın:

1. Gerçek domain + HTTPS üretim ortamı
2. `php artisan admin:create` ile süper yönetici
3. Site adı ve gerçek iletişim bilgileri
4. 7 politika sayfasının gerçek bilgilerle doldurulup **yayınlanması**
5. En az 1 gerçek yazar profili
6. En az 20 kaliteli (≥900 kelime, onaylı) yayın ve 4 içerikli kategori
7. AdSense hesabı, ads.txt Publisher ID, onay sonrası CMP

Tüm maddeler tamamlandığında:

```bash
php artisan site:readiness
php artisan site:security-check
```

Her iki komut da kritik FAIL olmadan geçmeli; ardından AdSense panelinden site ekleme ve doğrulama adımlarına geçilebilir. Onay gelene kadar admin panelinde reklamları **kapalı** tutun.
