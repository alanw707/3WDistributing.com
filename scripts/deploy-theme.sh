#!/usr/bin/env bash
# Deploy the 3w-2025 theme to Hostinger (staging or production) over FTP/SFTP.
#
# Usage:
#   ./scripts/deploy-theme.sh [--target staging|production] [remote-theme-path]
#
# Optional environment variables:
#   THREEW_FTP_HOST         (default: 147.79.122.118)
#   THREEW_FTP_PORT         (default: 21 or 22 when using sftp)
#   THREEW_FTP_USER         (default: u659513315.thrwdiststaging)
#   THREEW_FTP_PASS         (default: will prompt if unset)
#   THREEW_REMOTE_THEME_DIR (default: wp-content/themes/3w-2025)
#   THREEW_DEPLOY_SCHEME    (ftp|ftps|sftp, default: ftps)
#   THREEW_SSL_VERIFY       (yes|no, default: yes)
#   THREEW_SSH_KEY_PATH     (path to private key used for sftp, optional)
#   THREEW_DEPLOY_TARGET    (staging|production, default: staging)
#
# Production overrides applied when --target production (fallbacks to staging values):
#   THREEW_PROD_FTP_HOST, THREEW_PROD_FTP_PORT, THREEW_PROD_FTP_USER,
#   THREEW_PROD_FTP_PASS, THREEW_PROD_REMOTE_THEME_DIR,
#   THREEW_PROD_DEPLOY_SCHEME, THREEW_PROD_SSL_VERIFY.
#
# Requirements: npm, lftp. The script will fail fast if either is missing.
set -euo pipefail

usage() {
  cat <<'EOF'
Usage: ./scripts/deploy-theme.sh [--target staging|production] [remote-theme-path]

Examples:
  ./scripts/deploy-theme.sh
  ./scripts/deploy-theme.sh --target production public_html/wp-content/themes/3w-2025

The optional remote-theme-path overrides the default for the selected target.
EOF
}

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
THEME_DIR="$REPO_ROOT/wp-content/themes/3w-2025"
TARGET="${THREEW_DEPLOY_TARGET:-staging}"
CUSTOM_REMOTE_DIR=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --target)
      [[ $# -lt 2 ]] && { echo "Missing value for --target" >&2; usage; exit 1; }
      TARGET="$2"
      shift 2
      ;;
    --target=*)
      TARGET="${1#*=}"
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      if [[ -z "$CUSTOM_REMOTE_DIR" ]]; then
        CUSTOM_REMOTE_DIR="$1"
        shift
      else
        echo "Unknown argument: $1" >&2
        usage
        exit 1
      fi
      ;;
  esac
done

TARGET=$(echo "${TARGET,,}" | sed 's/^stage$/staging/; s/^prod$/production/; s/^live$/production/')
if [[ "$TARGET" != "staging" && "$TARGET" != "production" ]]; then
  echo "Unsupported deploy target '$TARGET'. Use staging or production." >&2
  exit 1
fi

# Load optional environment overrides from .env so CI/local runs can stay hands-free.
if [[ -f "$REPO_ROOT/.env" ]]; then
  # shellcheck source=/dev/null
  set -a
  source "$REPO_ROOT/.env"
  set +a
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "npm is required. Install Node.js/npm before running this script." >&2
  exit 1
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "lftp is required. Install it via your package manager (e.g., sudo apt install lftp)." >&2
  exit 1
fi

FTP_HOST=${THREEW_FTP_HOST:-147.79.122.118}
DEPLOY_SCHEME=${THREEW_DEPLOY_SCHEME:-ftps}
SSL_VERIFY=${THREEW_SSL_VERIFY:-yes}
FTP_USER=${THREEW_FTP_USER:-u659513315.thrwdiststaging}
REMOTE_THEME_DIR=${THREEW_REMOTE_THEME_DIR:-wp-content/themes/3w-2025}
FTP_PASS=${THREEW_FTP_PASS:-}
SSH_KEY_PATH=${THREEW_SSH_KEY_PATH:-}
TARGET_LABEL="staging"

if [[ "$TARGET" == "production" ]]; then
  FTP_HOST=${THREEW_PROD_FTP_HOST:-$FTP_HOST}
  DEPLOY_SCHEME=${THREEW_PROD_DEPLOY_SCHEME:-$DEPLOY_SCHEME}
  SSL_VERIFY=${THREEW_PROD_SSL_VERIFY:-$SSL_VERIFY}
  FTP_USER=${THREEW_PROD_FTP_USER:-$FTP_USER}
  FTP_PASS=${THREEW_PROD_FTP_PASS:-$FTP_PASS}
  REMOTE_THEME_DIR=${THREEW_PROD_REMOTE_THEME_DIR:-$REMOTE_THEME_DIR}
  TARGET_LABEL="production"
fi

REMOTE_THEME_DIR=${CUSTOM_REMOTE_DIR:-$REMOTE_THEME_DIR}

case "$DEPLOY_SCHEME" in
  ftp)
    DEFAULT_PORT=21
    ;;
  ftps)
    DEFAULT_PORT=21
    ;;
  sftp)
    DEFAULT_PORT=22
    ;;
  *)
    echo "Unsupported THREEW_DEPLOY_SCHEME '$DEPLOY_SCHEME'. Use ftp, ftps, or sftp." >&2
    exit 1
    ;;
esac

FTP_PORT=${THREEW_FTP_PORT:-$DEFAULT_PORT}
if [[ "$TARGET" == "production" ]]; then
  FTP_PORT=${THREEW_PROD_FTP_PORT:-$FTP_PORT}
fi

LFTP_URI="${DEPLOY_SCHEME}://${FTP_HOST}:${FTP_PORT}"

FTP_SETUP_CMDS="set ssl:verify-certificate $SSL_VERIFY"

if [[ "$DEPLOY_SCHEME" == "sftp" ]]; then
  FTP_SETUP_CMDS+=$'\n'"set sftp:auto-confirm yes"
  CONNECT_PROGRAM="ssh -o BatchMode=yes"
  if [[ -n "$SSH_KEY_PATH" ]]; then
    if [[ ! -f "$SSH_KEY_PATH" ]]; then
      echo "SSH key not found at $SSH_KEY_PATH" >&2
      exit 1
    fi
    CONNECT_PROGRAM+=" -i $SSH_KEY_PATH"
  fi
  FTP_SETUP_CMDS+=$'\n'"set sftp:connect-program \"$CONNECT_PROGRAM\""
else
  FTP_SETUP_CMDS+=$'\n'"set ftp:passive-mode on"
  if [[ "$DEPLOY_SCHEME" == "ftps" ]]; then
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-force true"
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-protect-data true"
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-auth TLS"
  else
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-force false"
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-protect-data false"
  fi
fi

if [[ -z "$FTP_PASS" && ! ( "$DEPLOY_SCHEME" == "sftp" && -n "$SSH_KEY_PATH" ) ]]; then
  read -r -s -p "FTP password for ${FTP_USER}@${FTP_HOST}: " FTP_PASS
  echo
fi

if [[ ! -d "$THEME_DIR" ]]; then
  echo "Theme directory not found at $THEME_DIR" >&2
  exit 1
fi

echo "Deploy target: ${TARGET_LABEL}"
echo "Remote directory: ${REMOTE_THEME_DIR}"
echo "Protocol: ${DEPLOY_SCHEME^^} (${FTP_HOST}:${FTP_PORT})"

echo "Building theme assets..."
(
  cd "$THEME_DIR"
  npm run build
)

echo "Synchronising theme to ${TARGET_LABEL} via ${DEPLOY_SCHEME^^} (verify cert: ${SSL_VERIFY})..."
lftp -u "$FTP_USER","$FTP_PASS" "$LFTP_URI" <<LFTP_CMDS
$FTP_SETUP_CMDS
set xfer:clobber yes
lcd $THEME_DIR
mkdir -p "$REMOTE_THEME_DIR"
cd "$REMOTE_THEME_DIR"
mirror -R \
  --delete \
  --parallel=4 \
  --exclude-glob .git/ \
  --exclude-glob .github/ \
  --exclude-glob node_modules/ \
  --exclude-glob src/ \
  --exclude-glob scripts/ \
  --exclude-glob "*.map" \
  --exclude-glob "*.log" \
  --exclude-glob ".DS_Store" \
  --exclude-glob "*.md" \
  --exclude-glob "package-lock.json" \
  --exclude-glob "package.json" \
  --exclude-glob "composer.lock" \
  --exclude-glob "composer.json" \
  --exclude-glob ".*rc" \
  --exclude-glob "*.config.js" \
  ./ .
LFTP_CMDS

echo "Deployment complete. Clear any ${TARGET_LABEL} caches and verify the site."
