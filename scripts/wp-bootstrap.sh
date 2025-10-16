#!/usr/bin/env bash
set -euo pipefail

if [ ! -f .env ]; then
  echo "[bootstrap] Missing .env file. Copy .env.example and update credentials." >&2
  exit 1
fi

# shellcheck disable=SC2046
export $(grep -v '^#' .env | xargs)

SITE_URL=${SITE_URL:-http://localhost:8080}
SITE_TITLE=${SITE_TITLE:-"3W Distributing Local"}
ADMIN_USER=${ADMIN_USER:-admin}
ADMIN_PASSWORD=${ADMIN_PASSWORD:-admin}
ADMIN_EMAIL=${ADMIN_EMAIL:-admin@example.com}

if ! docker compose run --rm wpcli "wp core is-installed" >/dev/null 2>&1; then
  echo "[bootstrap] Installing WordPress core..."
  docker compose run --rm wpcli "wp core install \
    --url='${SITE_URL}' \
    --title='${SITE_TITLE}' \
    --admin_user='${ADMIN_USER}' \
    --admin_password='${ADMIN_PASSWORD}' \
    --admin_email='${ADMIN_EMAIL}' \
    --skip-email"

  echo "[bootstrap] Installing required plugins..."
  if ! docker compose run --rm wpcli "wp plugin install woocommerce --activate"; then
    echo "[bootstrap] WooCommerce install skipped (version constraint)." >&2
  fi
  if ! docker compose run --rm wpcli "wp plugin install classic-editor --activate"; then
    echo "[bootstrap] Classic Editor install failed." >&2
  fi
  if ! docker compose run --rm wpcli "wp plugin install advanced-custom-fields --activate"; then
    echo "[bootstrap] ACF install failed." >&2
  fi

  echo "[bootstrap] Activating 3W 2025 theme scaffold..."
  docker compose run --rm wpcli "wp theme activate 3w-2025"
else
  echo "[bootstrap] WordPress already installed. Skipping core install."
fi

echo "[bootstrap] Flushing rewrite rules..."
docker compose run --rm wpcli "wp rewrite flush --hard"

echo "[bootstrap] Done."
