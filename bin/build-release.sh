#!/usr/bin/env bash
# Build a Vedos-ready zip: only files the running site needs.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STAMP="$(date +%Y%m%d-%H%M)"
DIST="${ROOT}/dist"
STAGE="${DIST}/release-${STAMP}"

cd "${ROOT}"

echo "→ npm ci && npm run build"
if [[ -f "${ROOT}/package-lock.json" ]]; then
    npm ci
else
    npm install
fi
npm run build

rm -rf "${STAGE}"
mkdir -p "${STAGE}/www" \
    "${STAGE}/writable/cache" \
    "${STAGE}/writable/logs" \
    "${STAGE}/writable/session" \
    "${STAGE}/writable/uploads"

echo "→ kopíruji soubory"
rsync -a --delete "${ROOT}/public/" "${STAGE}/www/"
rsync -a "${ROOT}/app/" "${STAGE}/app/"
# Vedos document root is the domain folder, not www/. Static URLs are /build/… and /favicon.svg.
rsync -a "${ROOT}/public/build/" "${STAGE}/build/"
cp "${ROOT}/public/favicon.ico" \
    "${ROOT}/public/favicon.svg" \
    "${ROOT}/public/favicon-32x32.png" \
    "${ROOT}/public/favicon-192x192.png" \
    "${ROOT}/public/favicon-512x512.png" \
    "${ROOT}/public/apple-touch-icon.png" \
    "${ROOT}/public/og-image.png" \
    "${ROOT}/public/site.webmanifest" \
    "${STAGE}/"
cp "${ROOT}/deploy/document-root.htaccess" "${STAGE}/.htaccess"
cp "${ROOT}/deploy/document-root-index.php" "${STAGE}/index.php"
cp "${ROOT}/composer.json" "${ROOT}/composer.lock" "${STAGE}/"
cp "${ROOT}/.env.production.example" "${STAGE}/"
cp "${ROOT}/README.md" "${STAGE}/"

if [[ -f "${ROOT}/writable/.htaccess" ]]; then
    cp "${ROOT}/writable/.htaccess" "${STAGE}/writable/"
fi
if [[ -f "${ROOT}/writable/index.html" ]]; then
    cp "${ROOT}/writable/index.html" "${STAGE}/writable/"
fi
for dir in cache logs session uploads; do
    if [[ -f "${ROOT}/writable/${dir}/index.html" ]]; then
        cp "${ROOT}/writable/${dir}/index.html" "${STAGE}/writable/${dir}/"
    fi
done

echo "→ composer install --no-dev"
composer install \
    --working-dir="${STAGE}" \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

rm -f "${STAGE}/.env" "${STAGE}/composer.json" "${STAGE}/composer.lock"

(
    cd "${DIST}"
    zip -r "release-${STAMP}.zip" "release-${STAMP}"
)

echo "Hotovo: ${DIST}/release-${STAMP}.zip"
echo "Nahrajte obsah archivu do složky domény (ta je na Vedosu document root)."
