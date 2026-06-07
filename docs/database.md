# Veritabanı (SQLite)

ModaPusula yalnızca **SQLite** kullanır. Ayrı bir veritabanı sunucusu gerekmez.

## Yerel geliştirme

```bash
touch database/database.sqlite
php artisan migrate --seed
```

`.env` ayarı:

```env
DB_CONNECTION=sqlite
# DB_DATABASE=   # boş bırakın → database/database.sqlite
```

Veritabanı dosyası: `database/database.sqlite` (repoya commit edilmez).

## Üretim (Coolify / Docker)

1. Ortam değişkeni: `DB_CONNECTION=sqlite`
2. **Kalıcı volume** bağlayın:
   - `/var/www/html/database` — `database.sqlite`
   - `/var/www/html/storage` — yüklenen dosyalar
3. İlk deploy: `RUN_MIGRATIONS=true` veya shell’den `php artisan migrate --force`

Container başlarken `docker/start.sh` dosya yoksa `database/database.sqlite` oluşturur.

## Yedekleme

```bash
cp database/database.sqlite "yedek-$(date +%F).sqlite"
```

Yedek dosyalarını güvenli depoda saklayın; repoya commit etmeyin.

## Yönetim

SQLite için phpMyAdmin veya MySQL gerekmez. Veritabanını incelemek için:

- [DB Browser for SQLite](https://sqlitebrowser.org/) (masaüstü)
- `sqlite3 database/database.sqlite` (CLI)
