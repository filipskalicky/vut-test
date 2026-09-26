# Nasazení

## Build

```bash
./bin/build-release.sh
```

Výstup: `dist/release-*.zip`. Do složky domény nahrát **obsah** archivu.

## Struktura na serveru

Složka domény je document root.

```text
index.php                 načte www/index.php
.htaccess
www/                      obsah public/
build/                    frontend (URL /build/…)
favicon*, og-image.png, site.webmanifest
app/
vendor/
writable/                 cache, logs, session, uploads
.env                      z .env.production.example
README.md
.env.production.example
```

## Server

1. PHP 8.2+
2. `.env` vedle `app/`: `app.baseURL` (s lomítkem na konci), `encryption.key`, MySQL
3. `php spark migrate` a `php spark db:seed DatabaseSeeder` (účet `test` / `test`)
4. `writable/` zapisovatelné (775/777)
