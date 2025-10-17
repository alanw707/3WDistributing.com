#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="${PROJECT_ROOT}/wp-content/themes/3w-2025"
SERVICE_NAME="wordpress"
REMOTE_THEME_DIR="/var/www/html/wp-content/themes/3w-2025"
REMOTE_TMP_DIR="/tmp/3w-theme-sync"
SKIP_BUILD=0

usage() {
  cat <<'USAGE' >&2
Usage: scripts/theme-sync.sh [--skip-build]

Syncs the 3W theme into the running docker container by packaging local assets
and streaming them into the wordpress service. Run from the repo root.
USAGE
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --skip-build)
      SKIP_BUILD=1
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "[sync] Unknown option: $1" >&2
      usage
      exit 1
      ;;
  esac
done

if [[ ! -d "${THEME_DIR}" ]]; then
  echo "[sync] Theme directory not found at ${THEME_DIR}" >&2
  exit 1
fi

CONTAINER_ID="$(docker compose ps -q "${SERVICE_NAME}" || true)"
if [[ -z "${CONTAINER_ID}" ]]; then
  echo "[sync] No running container for service '${SERVICE_NAME}'. Start the stack with 'docker compose up -d'." >&2
  exit 1
fi

if [[ "${SKIP_BUILD}" -eq 0 ]]; then
  echo "[sync] Building theme assets…"
  (cd "${THEME_DIR}" && npm run build)
fi

echo "[sync] Preparing container staging directory…"
docker compose exec -T "${SERVICE_NAME}" bash -lc "rm -rf '${REMOTE_TMP_DIR}' && mkdir -p '${REMOTE_TMP_DIR}'"

echo "[sync] Streaming theme files into container…"
tar -czf - \
  --exclude=node_modules \
  --exclude=.git \
  --exclude=.DS_Store \
  --exclude=.cache \
  --exclude=test-results \
  --exclude='*.map' \
  -C "${THEME_DIR}" . \
  | docker compose exec -T "${SERVICE_NAME}" tar -xzf - -C "${REMOTE_TMP_DIR}"

echo "[sync] Copying into ${REMOTE_THEME_DIR} …"
docker compose exec -T "${SERVICE_NAME}" bash -lc "mkdir -p '${REMOTE_THEME_DIR}' && cp -a '${REMOTE_TMP_DIR}/.' '${REMOTE_THEME_DIR}/'"

echo "[sync] Cleaning up staging directory…"
docker compose exec -T "${SERVICE_NAME}" rm -rf "${REMOTE_TMP_DIR}"

echo "[sync] Theme sync complete."
