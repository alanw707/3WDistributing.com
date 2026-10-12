#!/usr/bin/env bash
# Derive the live newsletter-popup asset from the configured 3w-2025 theme path.
set -euo pipefail

if [[ $# -ne 1 || -z "$1" ]]; then
  echo "Usage: derive-newsletter-remote-path.sh REMOTE_THEME_DIR" >&2
  exit 1
fi

THEME_DIR=${1%/}
if [[ ! "$THEME_DIR" =~ ^[A-Za-z0-9._/-]+$ || "$THEME_DIR" == *"//"* ]]; then
  echo "Remote theme directory contains unsupported characters" >&2
  exit 1
fi

PATH_WITHOUT_ROOT=${THEME_DIR#/}
IFS='/' read -r -a PATH_SEGMENTS <<< "$PATH_WITHOUT_ROOT"
for path_segment in "${PATH_SEGMENTS[@]}"; do
  if [[ -z "$path_segment" || "$path_segment" == "." || "$path_segment" == ".." ]]; then
    echo "Remote theme directory contains an unsafe segment" >&2
    exit 1
  fi
done

if [[ $(basename -- "$THEME_DIR") != "3w-2025" ]]; then
  echo "Remote theme directory must end in the 3w-2025 theme" >&2
  exit 1
fi
THEMES_DIR=$(dirname -- "$THEME_DIR")
if [[ $(basename -- "$THEMES_DIR") != "themes" ]]; then
  echo "Remote theme directory must be under a themes directory" >&2
  exit 1
fi
WP_CONTENT_DIR=$(dirname -- "$THEMES_DIR")
if [[ $(basename -- "$WP_CONTENT_DIR") != "wp-content" ]]; then
  echo "Remote theme directory must be under wp-content/themes" >&2
  exit 1
fi

SITE_ROOT=$(dirname -- "$WP_CONTENT_DIR")
case "$SITE_ROOT" in
  .) SITE_PREFIX="" ;;
  /) SITE_PREFIX="/" ;;
  *) SITE_PREFIX="${SITE_ROOT}/" ;;
esac
printf '%s%s\n' "$SITE_PREFIX" "wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js"
