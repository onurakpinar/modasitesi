# Deployment Rehberi

Bu belge ModaPusula Laravel uygulamasının üretim ortamına dağıtımını anlatır. Birincil hedef **Coolify** üzerinden container dağıtımıdır.

## Gereksinimler

- PHP 8.3+ (Docker imajında dahil)
- SQLite (PHP `pdo_sqlite` — Docker imajında dahil)
- Node.js 22+ (yalnızca asset derlemesi için; Docker build aşamasında çalışır)
- Composer 2
- HTTPS (Coolify veya ters vekil üzerinden)

---

## Lokal kurulum

```bash
cp .env.example .env
php artisan key:generate
composer install
npm install --ignore-scripts
npm run build
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Yerel geliştirmede `.env` içinde `APP_ENV=local` ve `APP_DEBUG=true` kullanın.

---

## Coolify üzerinde uygulama oluşturma

1. Coolify panelinde **New Resource → Application** seçin.
2. Git deposunu bağlayın (GitHub/GitLab).
3. **Build Pack**: **Dockerfile** (Nixpacks değil — aşağıdaki sorun giderme bölümüne bakın)
4. Dockerfile yolu: `Dockerfile` (kök dizin)
5. Port: **8080** (container içi dinleme portu)
6. **Liveness** health check path: `/up` (DB gerekmez; container ayağa kalkma kontrolü)
7. **Readiness** kontrolü (DB bağlandıktan sonra): `/health` → `200` / DB yoksa `503`
8. Health check port: `8080`

### SQLite ve kalıcı depolama

Uygulama **yalnızca SQLite** kullanır; ayrı MySQL servisi gerekmez.

1. Ortam değişkeni: `DB_CONNECTION=sqlite`
2. Coolify → **Persistent Storage** (volume) ekleyin:
   - `/var/www/html/database` — `database.sqlite` dosyası
   - `/var/www/html/storage` — yüklenen görseller ve önbellek
3. İlk deploy’da migration: `RUN_MIGRATIONS=true` veya shell’den `php artisan migrate --force`

Ayrıntılar: [database.md](database.md)

### İlk deploy kontrol listesi (zorunlu)

Deploy öncesi Coolify’da şunlar **mutlaka** tanımlı olmalı; aksi halde container başlar başlamaz kapanır ve health check düşer:

| Değişken | Örnek | Not |
|----------|-------|-----|
| `APP_KEY` | `base64:xxxx...` | **Zorunlu.** Yerelde: `php artisan key:generate --show` |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `APP_URL` | `https://alanadiniz.com` | Coolify FQDN veya özel domain |
| `DB_CONNECTION` | `sqlite` | Ayrı DB sunucusu yok |
| Persistent volume | `database`, `storage` | Veri deploy arasında kalır |
| Port (Coolify) | `8080` | Build → Ports / Network |

İlk kurulumda migration için geçici olarak `RUN_MIGRATIONS=true` verip bir deploy sonrası `false` yapabilirsiniz; veya container shell’den `php artisan migrate --force`.

### Environment variable tanımlama

Coolify → Application → Environment Variables bölümünde en az şunları tanımlayın:

| Değişken | Örnek | Not |
|----------|-------|-----|
| `APP_NAME` | Sitenizin adı | Gerçek marka adı |
| `APP_ENV` | `production` | |
| `APP_KEY` | `base64:...` | `php artisan key:generate --show` |
| `APP_DEBUG` | `false` | |
| `APP_URL` | `https://alanadiniz.com` | HTTPS zorunlu |
| `APP_TIMEZONE` | `Europe/Istanbul` | |
| `SESSION_SECURE_COOKIE` | `true` | |
| `LOG_LEVEL` | `warning` | |
| `DB_CONNECTION` | `sqlite` | |
| `CACHE_STORE` | `database` | |
| `SESSION_DRIVER` | `database` | |
| `QUEUE_CONNECTION` | `database` | |
| `RUN_MIGRATIONS` | `false` | Kontrollü migration için kapalı tutun |

Mail ayarları (`MAIL_*`) isteğe bağlıdır; iletişim formu veritabanına kayıt yapar.

**AdSense ayarları** ortam değişkeni yerine admin panelinden (`/admin/adsense`) yönetilir.

### Domain bağlama

1. Coolify → Domains → üretim alan adınızı ekleyin.
2. DNS’te A/AAAA veya CNAME kaydını Coolify sunucusuna yönlendirin.
3. Coolify otomatik Let’s Encrypt sertifikası üretir.

### HTTPS kontrolü

Deploy sonrası:

```bash
curl -I https://alanadiniz.com/health
```

`200` dönmeli. `APP_URL` değerinin `https://` ile başladığını doğrulayın.

---

## Deploy komutları

Coolify her push’ta Docker build çalıştırır. Build sırasında:

- `composer install --no-dev`
- `npm run build`
- PHP-FPM + Nginx imajı oluşturulur

Container başlarken `docker/start.sh`:

1. `APP_KEY` kontrolü (yoksa container başlamaz)
2. `php artisan storage:link`
3. `php artisan package:discover`
4. `php artisan optimize` (config/route/view/event cache)
5. `storage` ve `bootstrap/cache` izinleri (`www-data`)
6. Nginx + PHP-FPM başlatır

`RUN_MIGRATIONS=true` yalnızca bilinçli ilk kurulumda kullanılabilir; varsayılan `false`. Tekrar deploy veri silmez.

`.env` dosyası imaja **dahil edilmez**; tüm gizli değerler Coolify ortam değişkenlerinden gelir.

---

## Migration (kontrollü)

Her deploy sonrası otomatik migration **önerilmez**. Coolify “Execute Command” veya SSH ile:

```bash
php artisan migrate --force
```

Geri alma gerekirse:

```bash
php artisan migrate:rollback --step=1 --force
```

Önce veritabanı yedeği alın.

---

## Admin oluşturma

İlk süper yönetici:

```bash
php artisan admin:create \
  --name="Ad Soyad" \
  --email="yonetici@alanadiniz.com" \
  --password="guclu-parola-en-az-12-karakter"
```

Parolayı terminal geçmişine yazmamak için `--password` olmadan interaktif modu da kullanabilirsiniz.

---

## Storage link

Container her başlangıçta `storage:link` çalıştırır. Manuel gerektiğinde:

```bash
php artisan storage:link --force
```

`storage/app/public` dizininin yazılabilir olduğundan emin olun.

---

## Cache temizleme

Ayar değişikliği sonrası:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Scheduler (zamanlanmış yazılar)

İleri tarihli yazılar `posts:publish-scheduled` komutu ile yayına alınır. Saat dilimi: `Europe/Istanbul`.

### Coolify cron

Coolify → Application → **Scheduled Tasks** (veya sunucu cron):

```bash
* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1
```

Coolify container adını/komut yolunu ortamınıza göre ayarlayın. Alternatif olarak Coolify’ın “Run Command” özelliği ile her dakika:

```bash
php artisan schedule:run
```

Komutu doğrudan test etmek için:

```bash
php artisan posts:publish-scheduled
```

---

## Üretim hazırlık kontrolü

```bash
php artisan site:readiness
```

Her madde `PASS`, `WARNING` veya `FAIL` döner. `FAIL` varsa deploy tamamlanmış sayılmamalıdır.

Güvenlik kontrolü:

```bash
php artisan site:security-check
```

---

## Yedekleme önerisi

### Veritabanı

Volume üzerindeki `database/database.sqlite` dosyasını düzenli kopyalayın:

```bash
cp database/database.sqlite "yedek-$(date +%F).sqlite"
```

Yedek dosyalarını güvenli, şifreli depoda saklayın; repoya **commit etmeyin**.

### Dosyalar

- `storage/app/public` (yüklenen görseller)
- `.env` (ortam değişkenleri — güvenli kasada)

### Parola üretimi

```bash
openssl rand -base64 24
```

Üretim parolalarını repoya veya dokümana yazmayın.

---

## Rollback adımları

1. Coolify’da önceki başarılı deploy sürümüne **Rollback** yapın.
2. Migration geri alma gerekiyorsa yedekten restore veya `migrate:rollback`.
3. Cache temizleyin: `php artisan optimize:clear`
4. `/health` ve ana sayfayı doğrulayın.

---

## AdSense doğrulama ve reklamlar

### Doğrulama kodu (onay öncesi)

1. Admin → **AdSense ve Gizlilik**
2. **Doğrulama etkin** kutusunu işaretleyin
3. Google’dan aldığınız `ca-pub-...` client ID’yi girin
4. `APP_ENV=production` ve HTTPS gerekir

### Reklam gösterimi (onay sonrası)

1. Google sertifikalı **CMP** kurun (`docs/adsense-setup.md`)
2. Admin panelinde CMP onay kutusunu işaretleyin
3. Publisher ID ve slot ID’leri girin
4. Ancak onay sonrası **reklam kutularını** açın

Başvuru öncesinde reklam kutuları **kapalı** kalmalıdır (`site:readiness` bunu doğrular).

---

## Standart PHP hosting (kısa not)

Shared hosting veya VPS üzerinde Apache/Nginx + PHP-FPM kullanacaksanız:

1. `public/` dizinini web root yapın (document root).
2. `composer install --no-dev --optimize-autoloader`
3. `npm ci && npm run build` (yerelde veya CI’da)
4. `php artisan migrate --force`
5. `php artisan storage:link`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Cron: `* * * * * php /path/to/artisan schedule:run`
8. `storage/` ve `bootstrap/cache/` yazılabilir olmalı (755/775).

Coolify kullanımı önerilir; container izolasyonu ve HTTPS yönetimi daha az hataya açıktır.

---

## Sorun giderme

### `Request.php` syntax error (Nixpacks build)

Coolify logunda `Found application type: php` ve `composer install` sırasında `syntax error, unexpected token "{"` görüyorsanız **Nixpacks** kullanılıyordur; Laravel 13 için **PHP 8.3** gerekir.

**Çözüm (önerilen):**

1. Coolify → Application → **Configuration** → **Build**
2. **Build Pack** → `Dockerfile`
3. **Dockerfile Location** → `Dockerfile`
4. **Port** → `8080`
5. Yeniden deploy

**Alternatif:** Build Pack Nixpacks kalacaksa repodaki `nixpacks.toml` dosyası PHP 8.3 ve `--no-scripts` ile composer kurulumunu zorlar; yine de Dockerfile tercih edin (nginx + php-fpm + `start.sh`).

| Belirti | Olası çözüm |
|---------|-------------|
| Nixpacks + composer syntax error | Build Pack → Dockerfile, Port 8080 |
| Health check failed / container exits | `APP_KEY` tanımlı mı? Port 8080? Container loglarına bakın |
| `APP_KEY tanımlı değil` logu | Coolify env: `APP_KEY=base64:...` (`php artisan key:generate --show`) |
| 502/503 | `php artisan site:readiness`, DB bağlantısı, container logları |
| Görseller görünmüyor | `storage:link`, `storage/app/public` izinleri |
| CSS yok | `npm run build`, `public/build` dizini |
| Zamanlanmış yazı yayınlanmıyor | Cron / `schedule:run` |
| Health 503 | Veritabanı erişimi |
