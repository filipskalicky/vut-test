# Struktura

Lokálně: prohlížeč → Apache v Dockeru → CodeIgniter 4 → MariaDB.

Frontend se skládá na hostu: `npm run build` zapíše hashed soubory do `public/build`.

## Repozitář

```text
app/            CodeIgniter (controllery, modely, views, config, helpery)
public/         lokální document root
  index.php
  build/        výstup Vite
  .htaccess
resources/      CSS a JS před buildem
writable/       cache, logs, session, uploads
docker/         Dockerfile a Apache
deploy/         index.php a .htaccess pro release
bin/            build-release.sh, db-dump.sh
docs/
tests/
.env.example
.env.production.example
docker-compose.yml
```

`public/index.php` načítá `../app`. Spark a migrace běží v kontejneru `app`.

## Release

Výstup `./bin/build-release.sh` se nahrává do složky domény (ta je document root).

```text
index.php       načte www/index.php
.htaccess
www/            obsah public/
build/          stejné assety jako www/build/ (URL /build/…)
favicon*, og-image.png, site.webmanifest
app/
vendor/
writable/       cache, logs, session, uploads
.env
README.md
.env.production.example
```

`robots.txt` a `sitemap.xml` servíruje CodeIgniter, v releasu jako soubory nejsou.
