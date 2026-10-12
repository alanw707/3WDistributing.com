#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)
SCRIPT="$REPO_ROOT/scripts/deploy-file.sh"
DERIVE_PATH_SCRIPT="$REPO_ROOT/scripts/derive-newsletter-remote-path.sh"
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

mkdir -p "$TMP/bin"
cat > "$TMP/bin/lftp" <<'FAKE_LFTP'
#!/usr/bin/env bash
set -euo pipefail
did_put=false
while IFS= read -r line; do
  if [[ "$line" =~ ^get\ \"([^\"]+)\"\ -o\ \"([^\"]+)\"$ ]]; then
    destination=${BASH_REMATCH[2]}
    if [[ "$did_put" == true && -n "${FAKE_CORRUPT_READBACK_ONCE:-}" && -f "${FAKE_CORRUPT_STATE}" ]]; then
      printf 'corrupt read-back' > "$destination"
      rm -f "$FAKE_CORRUPT_STATE"
    else
      cp "$FAKE_REMOTE_STORE" "$destination"
    fi
  elif [[ "$line" =~ ^put\ \"([^\"]+)\"\ -o\ \"([^\"]+)\"$ ]]; then
    cp "${BASH_REMATCH[1]}" "$FAKE_REMOTE_STORE"
    did_put=true
    if [[ -n "${FAKE_FAIL_AFTER_PUT_ONCE:-}" && -f "${FAKE_FAIL_STATE}" ]]; then
      rm -f "$FAKE_FAIL_STATE"
      exit 42
    fi
  fi
done
FAKE_LFTP
chmod +x "$TMP/bin/lftp"

printf 'reviewed original' > "$TMP/remote.js"
printf 'reviewed replacement' > "$TMP/local.js"
ORIGINAL_HASH=$(sha256sum "$TMP/remote.js" | awk '{print $1}')
LOCAL_HASH=$(sha256sum "$TMP/local.js" | awk '{print $1}')

COMMON_ENV=(
  "PATH=$TMP/bin:$PATH"
  "THREEW_FTP_HOST=fake.invalid"
  "THREEW_FTP_USER=test-user"
  "THREEW_FTP_PASS=test-pass"
  "THREEW_DEPLOY_SCHEME=ftp"
  "FAKE_REMOTE_STORE=$TMP/remote.js"
  "FAKE_CORRUPT_STATE=$TMP/corrupt-once"
  "FAKE_FAIL_STATE=$TMP/fail-after-put-once"
)

run_deploy() {
  env "${COMMON_ENV[@]}" "$SCRIPT" \
    --target staging \
    --local-file "$TMP/local.js" \
    --remote-file public_html/wp-content/plugins/example/assets/file.js \
    --backup-file "$TMP/backup.js" \
    --verified-file "$TMP/verified.js" \
    --expected-remote-sha256 "$ORIGINAL_HASH" \
    --expected-remote-sha256 "$LOCAL_HASH"
}

# Successful bounded upload.
run_deploy
[[ $(sha256sum "$TMP/remote.js" | awk '{print $1}') == "$LOCAL_HASH" ]]

# Re-running against an already-deployed override is idempotent.
run_deploy
[[ $(sha256sum "$TMP/remote.js" | awk '{print $1}') == "$LOCAL_HASH" ]]

# Unknown remote bytes must fail before upload.
printf 'unreviewed remote edit' > "$TMP/remote.js"
if run_deploy >/dev/null 2>&1; then
  echo 'Expected unknown remote hash to fail' >&2
  exit 1
fi
[[ $(cat "$TMP/remote.js") == 'unreviewed remote edit' ]]

# Failed read-back verification must restore the reviewed backup.
printf 'reviewed original' > "$TMP/remote.js"
touch "$TMP/corrupt-once"
if FAKE_CORRUPT_READBACK_ONCE=1 run_deploy >/dev/null 2>&1; then
  echo 'Expected corrupt read-back to fail' >&2
  exit 1
fi
[[ $(sha256sum "$TMP/remote.js" | awk '{print $1}') == "$ORIGINAL_HASH" ]]

# A transport failure after upload must also restore the reviewed backup.
printf 'reviewed original' > "$TMP/remote.js"
touch "$TMP/fail-after-put-once"
if FAKE_FAIL_AFTER_PUT_ONCE=1 run_deploy >/dev/null 2>&1; then
  echo 'Expected transport failure after put to fail' >&2
  exit 1
fi
[[ $(sha256sum "$TMP/remote.js" | awk '{print $1}') == "$ORIGINAL_HASH" ]]

# Traversal-like remote paths must fail before lftp is called.
for unsafe_path in \
  public_html/wp-content/../file.js \
  public_html/wp-content/./file.js \
  public_html//wp-content/file.js; do
  if env "${COMMON_ENV[@]}" "$SCRIPT" \
    --target staging \
    --local-file "$TMP/local.js" \
    --remote-file "$unsafe_path" \
    --backup-file "$TMP/backup.js" \
    --verified-file "$TMP/verified.js" \
    --expected-remote-sha256 "$ORIGINAL_HASH" >/dev/null 2>&1; then
    echo "Expected unsafe remote path to fail: $unsafe_path" >&2
    exit 1
  fi
done

# The allowlist is mandatory even when every other argument is valid.
if env "${COMMON_ENV[@]}" "$SCRIPT" \
  --target staging \
  --local-file "$TMP/local.js" \
  --remote-file public_html/wp-content/plugins/example/assets/file.js \
  --backup-file "$TMP/backup.js" \
  --verified-file "$TMP/verified.js" >/dev/null 2>&1; then
  echo 'Expected a missing remote hash allowlist to fail' >&2
  exit 1
fi

# Theme paths may be relative, site-root relative or absolute.
[[ $("$DERIVE_PATH_SCRIPT" wp-content/themes/3w-2025) == 'wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js' ]]
[[ $("$DERIVE_PATH_SCRIPT" public_html/wp-content/themes/3w-2025/) == 'public_html/wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js' ]]
[[ $("$DERIVE_PATH_SCRIPT" /home/site/public_html/wp-content/themes/3w-2025) == '/home/site/public_html/wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js' ]]
[[ $("$DERIVE_PATH_SCRIPT" /wp-content/themes/3w-2025) == '/wp-content/plugins/3w-newsletter-popup/assets/newsletter-popup.js' ]]
for unsafe_theme_path in \
  themes/3w-2025 \
  wp-content/themes/porto \
  public_html/wp-content/../themes/3w-2025; do
  if "$DERIVE_PATH_SCRIPT" "$unsafe_theme_path" >/dev/null 2>&1; then
    echo "Expected invalid theme path to fail: $unsafe_theme_path" >&2
    exit 1
  fi
done

echo 'deploy-file safety tests passed'
