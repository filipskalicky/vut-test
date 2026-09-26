# Vývoj

Docker: PHP 8.2, Apache, MariaDB, Adminer. Frontend se staví na hostu (`npm`).

## Start

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php spark migrate
docker compose exec app php spark db:seed DatabaseSeeder
npm install
npm run build
```

- aplikace: [http://localhost:8080](http://localhost:8080)
- Adminer: [http://localhost:8081](http://localhost:8081) — server `db`, uživatel / heslo / databáze `aktuality`
- administrace: `test` / `test`

## Běžné příkazy

```bash
docker compose up -d
npm run dev
docker compose exec app php spark migrate
composer test
./bin/db-dump.sh
docker compose down
```

`npm run build` zapíše soubory do `public/build`. `npm run dev` je watch stejného buildu.

Spark pouštějte v kontejneru.

## Testy

```bash
composer test
```

Běží proti SQLite v paměti. Popis sad je v [tests/README.md](../tests/README.md).
