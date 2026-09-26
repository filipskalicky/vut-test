#!/usr/bin/env bash
# Export Docker MariaDB schema + data into database/dump.sql.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${ROOT}/database/dump.sql"

mkdir -p "${ROOT}/database"

if ! docker compose -f "${ROOT}/docker-compose.yml" ps --status running --format '{{.Name}}' | grep -qx 'aktuality-db'; then
    echo "MariaDB kontejner neběží. Spusťte: docker compose up -d" >&2
    exit 1
fi

{
    echo "-- Aktuality database dump"
    echo "-- Generated: $(date -Iseconds)"
    echo "-- Import this file in phpMyAdmin (or mysql < dump.sql)."
    echo ""
    echo "SET NAMES utf8mb4;"
    echo "SET FOREIGN_KEY_CHECKS = 0;"
    echo ""
} > "${OUT}"

docker compose -f "${ROOT}/docker-compose.yml" exec -T db \
    mariadb-dump \
        --user=aktuality \
        --password=aktuality \
        --databases aktuality \
        --default-character-set=utf8mb4 \
        --skip-comments \
        --routines \
        --single-transaction \
    >> "${OUT}"

echo "SET FOREIGN_KEY_CHECKS = 1;" >> "${OUT}"

echo "Zapsáno: ${OUT}"
