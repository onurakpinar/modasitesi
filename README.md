# ModaPusula

Türkçe moda yayın sitesi. Google AdSense başvurusuna uygun, sade ve hızlı bir içerik platformu.

## Gereksinimler

- PHP 8.3+
- Composer 2.x
- Node.js 20+
- MySQL 8+ (üretim) veya SQLite (yerel geliştirme)

## Lokal kurulum

```bash
# Bağımlılıklar
composer install
npm install

# Ortam dosyası
cp .env.example .env
php artisan key:generate

# .env içinde veritabanı ayarlarını düzenleyin (MySQL örneği):
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=modapusula
# DB_USERNAME=root
# DB_PASSWORD=

# Veritabanı
php artisan migrate

# Depolama bağlantısı
php artisan storage:link

# Frontend derlemesi
npm run build

# Geliştirme sunucusu
php artisan serve
```

Geliştirme sırasında Vite için ayrı bir terminalde:

```bash
npm run dev
```

## Yönetim paneli

Halka açık kayıt yoktur. İlk yöneticiyi oluşturun:

```bash
php artisan admin:create
```

Ardından `/admin/login` adresinden giriş yapın.

Roller:
- **super_admin** — Tüm yetkiler, kullanıcı yönetimi dahil
- **editor** — İçerik yönetimi (kullanıcı ekleyemez/silemez)

## Testler

```bash
php artisan test
```

## Sağlık kontrolü

```bash
curl http://localhost:8000/health
```

Örnek yanıt:

```json
{
  "status": "ok",
  "app": "ModaPusula",
  "environment": "local",
  "timestamp": "2026-06-06T14:00:00+03:00"
}
```

## Teknoloji

- Laravel 13.x
- Blade + Tailwind CSS 4 + Vite
- Alpine.js (minimal etkileşimler)
- PHPUnit

## Lisans

MIT
