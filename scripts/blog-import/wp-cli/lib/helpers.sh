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

# REST API helper for staging environment
wp_rest_staging() {
  local method="$1"
  local endpoint="$2"
  shift 2

  local staging_user staging_pass auth_header base_url
  staging_user="$(sanitize_password "${STAGE_WP_APP_USER:-}")"
  staging_pass="$(sanitize_password "${STAGE_WP_APP_PASSWORD:-}")"

  if [[ -z "${staging_user}" || -z "${staging_pass}" ]]; then
    echo "Error: STAGE_WP_APP_USER and STAGE_WP_APP_PASSWORD must be set" >&2
    return 1
  fi

  auth_header="Authorization: Basic $(printf '%s:%s' "${staging_user}" "${staging_pass}" | base64)"
  base_url="${STAGE_WP_BASE_URL:-https://staging.3wdistributing.com}"

  local url="${base_url}/wp-json/wp/v2/${endpoint}"
  local curl_args=(-sS -X "${method}" -H "${auth_header}" -H "Content-Type: application/json")

  # Add any additional arguments (data, query params, etc.)
  while [[ $# -gt 0 ]]; do
    curl_args+=("$1")
    shift
  done

  curl "${curl_args[@]}" "${url}"
}

# Helper to check if term exists by slug
wp_rest_get_term() {
  local taxonomy="$1"
  local slug="$2"
  wp_rest_staging "GET" "${taxonomy}?slug=${slug}&per_page=1"
}

# Helper to create a term
wp_rest_create_term() {
  local taxonomy="$1"
  local name="$2"
  local slug="$3"
  local description="${4:-}"

  local json_data
  json_data=$(jq -n \
    --arg name "${name}" \
    --arg slug "${slug}" \
    --arg desc "${description}" \
    '{name: $name, slug: $slug, description: $desc}')

  wp_rest_staging "POST" "${taxonomy}" -d "${json_data}"
}

# Helper to check if post exists by slug
wp_rest_get_post() {
  local slug="$1"
  wp_rest_staging "GET" "posts?slug=${slug}&per_page=1"
}

# Helper to create or update a post
wp_rest_upsert_post() {
  local post_id="$1"
  local json_data="$2"

  if [[ -z "${post_id}" || "${post_id}" == "0" ]]; then
    wp_rest_staging "POST" "posts" -d "${json_data}"
  else
    wp_rest_staging "PUT" "posts/${post_id}" -d "${json_data}"
  fi
}

# Helper to check if media exists by source URL
wp_rest_get_media_by_source() {
  local source_url="$1"
  # Search for media with matching source_url in meta
  wp_rest_staging "GET" "media?search=$(printf '%s' "${source_url}" | jq -sRr @uri)&per_page=100"
}

# Helper to create media from URL
wp_rest_create_media_from_url() {
  local source_url="$1"
  local title="$2"
  local alt_text="${3:-}"

  # Download media temporarily
  local temp_file
  temp_file="$(mktemp --suffix=.tmp)"
  if ! curl -sS -o "${temp_file}" "${source_url}"; then
    rm -f "${temp_file}"
    return 1
  fi

  # Get filename and mime type
  local filename
  filename="$(basename "${source_url}")"
  local mime_type
  mime_type="$(file -b --mime-type "${temp_file}")"

  # Upload using multipart/form-data
  local staging_user staging_pass auth_header base_url
  staging_user="$(sanitize_password "${STAGE_WP_APP_USER:-}")"
  staging_pass="$(sanitize_password "${STAGE_WP_APP_PASSWORD:-}")"
  auth_header="Authorization: Basic $(printf '%s:%s' "${staging_user}" "${staging_pass}" | base64)"
  base_url="${STAGE_WP_BASE_URL:-https://staging.3wdistributing.com}"

  local result
  result=$(curl -sS -X POST "${base_url}/wp-json/wp/v2/media" \
    -H "${auth_header}" \
    -H "Content-Disposition: attachment; filename=\"${filename}\"" \
    -H "Content-Type: ${mime_type}" \
    --data-binary "@${temp_file}")

  rm -f "${temp_file}"
  echo "${result}"
}

# Helper to update post meta
wp_rest_update_post_meta() {
  local post_id="$1"
  local meta_key="$2"
  local meta_value="$3"

  local json_data
  json_data=$(jq -n \
    --arg key "${meta_key}" \
    --arg value "${meta_value}" \
    '{meta: {($key): $value}}')

  wp_rest_staging "POST" "posts/${post_id}" -d "${json_data}"
}
