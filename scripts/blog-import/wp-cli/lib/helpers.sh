#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../../.." && pwd)"
LOG_DIR="${PROJECT_ROOT}/logs/blog-import/wp-cli"
mkdir -p "${LOG_DIR}"

LOG_FILE=""

timestamp() {
  date -u +"%Y-%m-%dT%H:%M:%SZ"
}

log_info() {
  local msg="[$(timestamp)] $*"
  printf '%s\n' "${msg}" >&2
  if [[ -n "${LOG_FILE}" ]]; then
    printf '%s\n' "${msg}" >> "${LOG_FILE}"
  fi
}

setup_logging() {
  local name="$1"
  local ts
  ts="$(date -u +"%Y%m%d-%H%M%S")"
  LOG_FILE="${LOG_DIR}/${name}-${ts}.log"
  touch "${LOG_FILE}"
  log_info "Logging to ${LOG_FILE}"
}

wp_cli() {
  local cmd="wp --path=/var/www/html --allow-root"
  local arg
  for arg in "$@"; do
    cmd+=" $(printf '%q' "$arg")"
  done
  local raw
  raw=$(docker compose run --rm -T wpcli "$cmd" 2>&1)
  local status=$?
  local cleaned
  cleaned=$(printf '%s\n' "$raw" | sed -e '/^time=/d' -e '/^ Container /d')
  if [[ $status -eq 0 ]]; then
    printf '%s\n' "$cleaned"
  else
    printf '%s\n' "$cleaned" >&2
  fi
  return $status
}

sanitize_password() {
  echo "$1" | tr -d '[:space:]'
}
