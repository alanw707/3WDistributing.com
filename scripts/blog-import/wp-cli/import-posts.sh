#!/usr/bin/env bash
set -euo pipefail
trap 'log_info "Import aborted at line $LINENO"; exit 1' ERR

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/../../.." && pwd)"
source "${SCRIPT_DIR}/lib/helpers.sh"

if [[ -f "${PROJECT_ROOT}/.env" ]]; then
  set -a
  # shellcheck disable=SC1090
  source "${PROJECT_ROOT}/.env"
  set +a
fi

usage() {
  cat <<'EOF'
Usage: import-posts.sh [options]

Options:
  --modified-after ISO8601   Only import posts modified after the provided timestamp.
  --target local|staging     Destination environment (default: local).
  --limit N                  Limit number of posts processed (default: 20).
  --dry-run                  Do not create/update posts; fetch only.
EOF
}

MODIFIED_AFTER=""
TARGET="local"
LIMIT=20
DRY_RUN=false
PAGE=1

while [[ $# -gt 0 ]]; do
  case "$1" in
    --modified-after)
      MODIFIED_AFTER="$2"; shift 2 ;;
    --target)
      TARGET="$2"; shift 2 ;;
    --limit)
      LIMIT="$2"; shift 2 ;;
    --dry-run)
      DRY_RUN=true; shift ;;
    --page)
      PAGE="$2"; shift 2 ;;
    -h|--help)
      usage; exit 0 ;;
    *)
      echo "Unknown option: $1" >&2
      usage; exit 1 ;;
  esac
done

if [[ -z "${MODIFIED_AFTER}" ]]; then
  echo "Error: --modified-after is required until full backfill implemented." >&2
  exit 1
fi

setup_logging "import-posts"

log_info "Fetching posts modified after ${MODIFIED_AFTER} (limit ${LIMIT})"

PROD_USER="$(sanitize_password "${PROD_WP_APP_USER:-}")"
PROD_PASS="$(sanitize_password "${PROD_WP_APP_PASSWORD:-}")"

if [[ -z "${PROD_USER}" || -z "${PROD_PASS}" ]]; then
  echo "Missing production credentials; ensure PROD_WP_APP_USER and PROD_WP_APP_PASSWORD are set." >&2
  exit 1
fi

PROD_BASE_URL="${PROD_WP_BASE_URL:-https://www.3wdistributing.com}"

if [[ "${TARGET}" == "staging" ]]; then
  log_info "Target: staging (using REST API)"
  # Verify staging credentials are available
  if [[ -z "${STAGE_WP_APP_USER:-}" || -z "${STAGE_WP_APP_PASSWORD:-}" ]]; then
    echo "Missing staging credentials; ensure STAGE_WP_APP_USER and STAGE_WP_APP_PASSWORD are set." >&2
    exit 1
  fi
elif [[ "${TARGET}" != "local" ]]; then
  log_info "Unknown target '${TARGET}'; defaulting to local Docker environment."
  TARGET="local"
fi

if [[ "${TARGET}" == "local" ]]; then
  log_info "Target: local (using WP-CLI via Docker)"
fi

AUTH_HEADER="Authorization: Basic $(printf '%s:%s' "${PROD_USER}" "${PROD_PASS}" | base64)"

POSTS_JSON="$(mktemp)"
TEMP_FILES=("${POSTS_JSON}")
cleanup() {
  for file in "${TEMP_FILES[@]}"; do
    [[ -f "${file}" ]] && rm -f "${file}"
  done
}
trap cleanup EXIT

curl -sS "${PROD_BASE_URL}/wp-json/wp/v2/posts?per_page=${LIMIT}&after=${MODIFIED_AFTER}&orderby=modified&order=asc&page=${PAGE}" \
  -H "${AUTH_HEADER}" > "${POSTS_JSON}"

COUNT="$(jq 'length' "${POSTS_JSON}")"
if [[ "${COUNT}" -eq 0 ]]; then
  log_info "No posts matched filter."
  exit 0
fi

log_info "Fetched ${COUNT} posts"

ARTIFACT_PATH="${LOG_FILE%.log}.posts.json"
cp "${POSTS_JSON}" "${ARTIFACT_PATH}"
log_info "Stored response payload at ${ARTIFACT_PATH}"

if [[ "${DRY_RUN}" == "true" ]]; then
  log_info "Dry run enabled; skipping import."
  jq '.[].title.rendered' "${POSTS_JSON}"
  exit 0
fi

declare -A CATEGORY_MAP=()
declare -A TAG_SLUG_MAP=()
declare -A TAG_NAME_MAP=()
declare -A TAG_ID_MAP=()
declare -A MEDIA_MAP=()

sync_categories() {
  log_info "Syncing categories with target"
  local page=1
  while true; do
    local response
    response="$(mktemp)"
    TEMP_FILES+=("${response}")
    curl -sS "${PROD_BASE_URL}/wp-json/wp/v2/categories?per_page=100&page=${page}" \
      -H "${AUTH_HEADER}" > "${response}"
    local qty
    qty="$(jq 'length' "${response}")"
    if [[ "${qty}" -eq 0 ]]; then
      break
    fi
    while IFS= read -r category_row; do
      local prod_id slug name desc
      prod_id="$(jq -r '.id' <<<"${category_row}")"
      slug="$(jq -r '.slug' <<<"${category_row}")"
      name="$(jq -r '.name // empty' <<<"${category_row}")"
      desc="$(jq -r '.description // ""' <<<"${category_row}")"
      [[ -z "${name}" ]] && name="${slug}"
      local target_id

      if [[ "${TARGET}" == "staging" ]]; then
        # Use REST API for staging
        local existing_term
        existing_term="$(wp_rest_get_term "categories" "${slug}")"
        target_id="$(jq -r '.[0].id // empty' <<<"${existing_term}")"
        if [[ -z "${target_id}" ]]; then
          local created_term
          created_term="$(wp_rest_create_term "categories" "${name}" "${slug}" "${desc}")"
          target_id="$(jq -r '.id // empty' <<<"${created_term}")"
          log_info "Created category '${name}' (#${target_id})"
        fi
      else
        # Use WP-CLI for local
        target_id="$(wp_cli term get category "${slug}" --by=slug --field=term_id 2>/dev/null || true)"
        if [[ -z "${target_id}" ]]; then
          target_id="$(wp_cli term create category "${name}" --slug="${slug}" --description="${desc}" --porcelain)"
          log_info "Created category '${name}' (#${target_id})"
        fi
      fi

      CATEGORY_MAP["${prod_id}"]="${target_id}"
    done < <(jq -c '.[]' "${response}")
    ((page++))
  done
}

sync_tags() {
  log_info "Syncing tags with target"
  local page=1
  while true; do
    local response
    response="$(mktemp)"
    TEMP_FILES+=("${response}")
    curl -sS "${PROD_BASE_URL}/wp-json/wp/v2/tags?per_page=100&page=${page}" \
      -H "${AUTH_HEADER}" > "${response}"
    local qty
    qty="$(jq 'length' "${response}")"
    if [[ "${qty}" -eq 0 ]]; then
      break
    fi
    while IFS= read -r tag_row; do
      local prod_id slug name
      prod_id="$(jq -r '.id' <<<"${tag_row}")"
      slug="$(jq -r '.slug' <<<"${tag_row}")"
      name="$(jq -r '.name // empty' <<<"${tag_row}")"
      [[ -z "${name}" ]] && name="${slug}"
      local target_id

      if [[ "${TARGET}" == "staging" ]]; then
        # Use REST API for staging
        local existing_term
        existing_term="$(wp_rest_get_term "tags" "${slug}")"
        target_id="$(jq -r '.[0].id // empty' <<<"${existing_term}")"
        if [[ -z "${target_id}" ]]; then
          local created_term
          created_term="$(wp_rest_create_term "tags" "${name}" "${slug}" "")"
          target_id="$(jq -r '.id // empty' <<<"${created_term}")"
          log_info "Created tag '${name}' (#${target_id})"
        fi
      else
        # Use WP-CLI for local
        target_id="$(wp_cli term get post_tag "${slug}" --by=slug --field=term_id 2>/dev/null || true)"
        if [[ -z "${target_id}" ]]; then
          target_id="$(wp_cli term create post_tag "${name}" --slug="${slug}" --porcelain)"
          log_info "Created tag '${name}' (#${target_id})"
        fi
      fi

      TAG_SLUG_MAP["${prod_id}"]="${slug}"
      TAG_NAME_MAP["${prod_id}"]="${name}"
      TAG_ID_MAP["${prod_id}"]="${target_id}"
    done < <(jq -c '.[]' "${response}")
    ((page++))
  done
}

sync_categories
sync_tags

fetch_media() {
  local media_id="$1"
  if [[ -z "${media_id}" || "${media_id}" == "0" ]]; then
    return
  fi
  if [[ -n "${MEDIA_MAP[${media_id}]:-}" ]]; then
    echo "${MEDIA_MAP[${media_id}]}"
    return
  fi
  local response
  response="$(mktemp)"
  TEMP_FILES+=("${response}")
  curl -sS "${PROD_BASE_URL}/wp-json/wp/v2/media/${media_id}" \
    -H "${AUTH_HEADER}" > "${response}"
  local source_url
  source_url="$(jq -r '.source_url // empty' "${response}")"
  if [[ -z "${source_url}" ]]; then
    log_info "Warning: media ${media_id} missing source_url"
    return
  fi
  local title
  title="$(jq -r '.title.rendered // ""' "${response}")"
  local attachment_id

  if [[ "${TARGET}" == "staging" ]]; then
    # Use REST API for staging
    # First try to find existing media by checking source_url in filename
    local media_search
    media_search="$(wp_rest_get_media_by_source "${source_url}")"
    attachment_id="$(jq -r '.[0].id // empty' <<<"${media_search}")"

    if [[ -z "${attachment_id}" ]]; then
      # Create new media via REST API
      local created_media
      created_media="$(wp_rest_create_media_from_url "${source_url}" "${title}")"
      attachment_id="$(jq -r '.id // empty' <<<"${created_media}")"
      if [[ -n "${attachment_id}" ]]; then
        # Store source URL in meta for future lookups
        wp_rest_update_post_meta "${attachment_id}" "_import_source_url" "${source_url}" >/dev/null 2>&1
        log_info "Imported media ${source_url} (#${attachment_id})"
      else
        log_info "Warning: failed to import media ${source_url}"
      fi
    else
      log_info "Reusing media ${source_url} (#${attachment_id})"
    fi
  else
    # Use WP-CLI for local
    local existing_attachment
    existing_attachment="$(wp_cli post list --post_type=attachment --meta_key=_import_source_url --meta_value="${source_url}" --field=ID --format=ids | head -n1 | tr -d $'\r')"
    attachment_id="${existing_attachment}"
    if [[ -z "${attachment_id}" ]]; then
      attachment_id="$(wp_cli media import "${source_url}" --title="${title}" --porcelain | tail -n1 | tr -d $'\r')"
      if [[ -n "${attachment_id}" ]]; then
        wp_cli post meta update "${attachment_id}" _import_source_url "${source_url}" >/dev/null
        log_info "Imported media ${source_url} (#${attachment_id})"
      else
        log_info "Warning: failed to import media ${source_url}"
      fi
    else
      log_info "Reusing media ${source_url} (#${attachment_id})"
    fi
  fi

  if [[ -n "${attachment_id}" ]]; then
    MEDIA_MAP["${media_id}"]="${attachment_id}"
    echo "${attachment_id}"
  fi
}

set_featured_image() {
  local post_id="$1"
  local attachment_id="$2"
  if [[ -z "${attachment_id}" ]]; then
    return
  fi

  if [[ "${TARGET}" == "staging" ]]; then
    # Use REST API for staging - featured_media is a top-level property
    local json_data
    json_data=$(jq -n --arg media_id "${attachment_id}" '{featured_media: ($media_id | tonumber)}')
    wp_rest_staging "POST" "posts/${post_id}" -d "${json_data}" >/dev/null
  else
    # Use WP-CLI for local
    wp_cli post meta update "${post_id}" _thumbnail_id "${attachment_id}" >/dev/null
  fi
}

created_count=0
updated_count=0
processed_count=0

mapfile -t post_items < <(jq -c '.[]' "${POSTS_JSON}")

for post_row in "${post_items[@]}"; do
  slug="$(jq -r '.slug' <<<"${post_row}")"
  title="$(jq -r '.title.rendered // ""' <<<"${post_row}")"
  content="$(jq -r '.content.rendered // ""' <<<"${post_row}")"
  excerpt="$(jq -r '.excerpt.rendered // ""' <<<"${post_row}")"
  status="$(jq -r '.status // "draft"' <<<"${post_row}")"
  date_published="$(jq -r '.date // empty' <<<"${post_row}")"

  if [[ "${status}" == "closed" ]]; then
    status="publish"
  fi
  log_info "Processing post '${slug}' (status=${status})"
  ((processed_count+=1))

  featured_media_id="$(jq -r '.featured_media // 0' <<<"${post_row}")"

  mapfile -t prod_category_ids < <(jq -r '.categories[]?' <<<"${post_row}")
  mapfile -t prod_tag_ids < <(jq -r '.tags[]?' <<<"${post_row}")

  declare -a category_ids=()
  for prod_cat_id in "${prod_category_ids[@]}"; do
    target_cat_id="${CATEGORY_MAP[${prod_cat_id}]:-}"
    if [[ -n "${target_cat_id}" ]]; then
      category_ids+=("${target_cat_id}")
    else
      log_info "Warning: missing category mapping for ID ${prod_cat_id}"
    fi
  done

  declare -a tag_names=()
  declare -a tag_ids=()
  for prod_tag_id in "${prod_tag_ids[@]}"; do
    tag_name="${TAG_NAME_MAP[${prod_tag_id}]:-}"
    target_tag_id="${TAG_ID_MAP[${prod_tag_id}]:-}"
    if [[ -n "${tag_name}" ]]; then
      tag_names+=("${tag_name}")
    fi
    if [[ -n "${target_tag_id}" ]]; then
      tag_ids+=("${target_tag_id}")
    fi
    if [[ -z "${tag_name}" ]]; then
      log_info "Warning: missing tag mapping for ID ${prod_tag_id}"
    fi
  done

  existing_id=""
  post_id=""

  if [[ "${TARGET}" == "staging" ]]; then
    # Use REST API for staging
    existing_post="$(wp_rest_get_post "${slug}")"
    existing_id="$(jq -r '.[0].id // empty' <<<"${existing_post}")"

    # Build JSON data for post
    categories_json=""
    tags_json=""
    if (( ${#category_ids[@]} )); then
      categories_json="$(printf '%s\n' "${category_ids[@]}" | jq -R . | jq -s 'map(tonumber)')"
    else
      categories_json="[]"
    fi
    if (( ${#tag_ids[@]} )); then
      tags_json="$(printf '%s\n' "${tag_ids[@]}" | jq -R . | jq -s 'map(tonumber)')"
    else
      tags_json="[]"
    fi

    json_data=$(jq -n \
      --arg status "${status}" \
      --arg title "${title}" \
      --arg slug "${slug}" \
      --arg content "${content}" \
      --arg excerpt "${excerpt}" \
      --arg date "${date_published}" \
      --argjson categories "${categories_json}" \
      --argjson tags "${tags_json}" \
      '{
        status: $status,
        title: $title,
        slug: $slug,
        content: $content,
        excerpt: $excerpt,
        date: $date,
        categories: $categories,
        tags: $tags
      }' | jq 'with_entries(select(.value != "" and .value != null and .value != []))')

    result="$(wp_rest_upsert_post "${existing_id}" "${json_data}")"
    post_id="$(jq -r '.id // empty' <<<"${result}")"

    if [[ -z "${existing_id}" ]]; then
      log_info "Created post '${slug}' (#${post_id})"
      ((created_count+=1))
    else
      log_info "Updated post '${slug}' (#${post_id})"
      ((updated_count+=1))
    fi
  else
    # Use WP-CLI for local
    existing_id="$(wp_cli post list --post_type=post --name="${slug}" --field=ID --format=ids 2>/dev/null | head -n1 | tr -d '\r')"

    declare -a common_flags=()
    common_flags+=(--post_status="${status}")
    common_flags+=(--post_title="${title}")
    common_flags+=(--post_name="${slug}")
    if [[ -n "${date_published}" && "${date_published}" != "null" ]]; then
      common_flags+=(--post_date="${date_published}")
    fi
    if [[ -n "${content}" && "${content}" != "null" ]]; then
      common_flags+=(--post_content="${content}")
    fi
    if [[ -n "${excerpt}" && "${excerpt}" != "null" ]]; then
      common_flags+=(--post_excerpt="${excerpt}")
    fi
    if (( ${#category_ids[@]} )); then
      cat_csv="$(IFS=,; echo "${category_ids[*]}")"
      common_flags+=(--post_category="${cat_csv}")
    fi
    if (( ${#tag_names[@]} )); then
      tags_csv="$(IFS=,; echo "${tag_names[*]}")"
      common_flags+=(--tags_input="${tags_csv}")
    fi

    if [[ -z "${existing_id}" ]]; then
      create_args=(post create --post_type=post "${common_flags[@]}" --porcelain)
      new_id="$(wp_cli "${create_args[@]}")"
      log_info "Created post '${slug}' (#${new_id})"
      ((created_count+=1))
      post_id="${new_id}"
    else
      update_args=(post update "${existing_id}" "${common_flags[@]}")
      wp_cli "${update_args[@]}" >/dev/null
      log_info "Updated post '${slug}' (#${existing_id})"
      ((updated_count+=1))
      post_id="${existing_id}"
    fi
  fi

  if [[ "${featured_media_id}" -gt 0 ]]; then
    attachment_id="$(fetch_media "${featured_media_id}")"
    if [[ -n "${attachment_id}" ]]; then
      set_featured_image "${post_id}" "${attachment_id}"
      log_info "Set featured media (#${attachment_id}) for post '${slug}'"
    fi
  fi
done

log_info "Import complete — created ${created_count}, updated ${updated_count}."
log_info "Loop processed ${processed_count} posts from payload."
