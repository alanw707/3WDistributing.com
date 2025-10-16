#!/usr/bin/env bash
set -euo pipefail

if [ $# -eq 0 ]; then
  echo "Usage: scripts/xdebug-toggle.sh [off|debug|profile]" >&2
  exit 1
fi

TARGET=${1}
if [ ! -f .env ]; then
  echo "[xdebug] Missing .env. Copy .env.example first." >&2
  exit 1
fi

case "${TARGET}" in
  off)
    VALUE=""
    ;;
  debug)
    VALUE="debug,develop"
    ;;
  profile)
    VALUE="profile"
    ;;
  *)
    echo "[xdebug] Unknown mode '${TARGET}'. Use off|debug|profile." >&2
    exit 1
    ;;
esac

if grep -q '^XDEBUG_MODE=' .env; then
  sed -i.bak "s/^XDEBUG_MODE=.*/XDEBUG_MODE=${VALUE}/" .env
else
  echo "XDEBUG_MODE=${VALUE}" >> .env
fi

rm -f .env.bak

echo "[xdebug] XDEBUG_MODE set to '${VALUE}'. Run 'docker compose up -d --build wordpress' to apply."
