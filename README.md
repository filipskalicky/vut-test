# Aktuality

Veřejný přehled a administrace aktualit. CodeIgniter 4, PHP 8.2, MariaDB.

Přihlášení: **test / test**

## Struktura

| Cesta | Účel |
| --- | --- |
| `app/` | aplikace (controllery, modely, views, config) |
| `public/` | lokální document root (`index.php`, `build/`, favicony) |
| `resources/` | zdrojové CSS a JS (Vite) |
| `writable/` | cache, logy, session, uploady |
| `docker/` | image PHP/Apache |
| `deploy/` | vstupní soubory releasu |
| `bin/` | `build-release.sh`, `db-dump.sh` |
| `docs/` | vývoj, nasazení, struktura |
| `tests/` | PHPUnit |

## Spuštění

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php spark migrate
docker compose exec app php spark db:seed DatabaseSeeder
npm install
npm run build
```

[http://localhost:8080](http://localhost:8080)

```bash
composer test
./bin/db-dump.sh
./bin/build-release.sh
```

- [Vývoj](docs/INSTALL.md)
- [Nasazení](docs/DEPLOY.md)
- [Struktura](docs/ARCHITECTURE.md)
- [Testy](tests/README.md)
