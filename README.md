# ModaPusula

Türkçe moda yayın sitesi. Google AdSense başvurusuna uygun, sade ve hızlı bir içerik platformu.

## Gereksinimler

- PHP 8.3+ (SQLite eklentisi dahil)
- Composer 2.x
- Node.js 20+

## Lokal kurulum

```bash
# Bağımlılıklar
composer install
npm install

# Ortam dosyası
cp .env.example .env
php artisan key:generate

# Veritabanı (SQLite)
touch database/database.sqlite
php artisan migrate --seed

# Depolama bağlantısı
php artisan storage:link

# Frontend derlemesi
npm run build

# Geliştirme sunucusu
php artisan serve
```

Detay: [docs/database.md](docs/database.md)

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
curl http://127.0.0.1:8000/health
```

## Deploy

Coolify üzerinden Docker ile dağıtım: [docs/deployment.md](docs/deployment.md)
