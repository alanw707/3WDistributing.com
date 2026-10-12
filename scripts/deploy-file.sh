#!/usr/bin/env bash
# Upload exactly one file through the same FTP/FTPS/SFTP credentials used by the
# production theme deployment. The remote file is backed up and hash-checked
# before overwrite, then downloaded again to verify the deployed bytes.
set -euo pipefail

usage() {
  cat <<'EOF'
Usage: deploy-file.sh --target staging|production --local-file PATH --remote-file PATH \
  --backup-file PATH --verified-file PATH --expected-remote-sha256 HASH [...]
EOF
}

REPO_ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
TARGET="${THREEW_DEPLOY_TARGET:-staging}"
LOCAL_FILE=""
REMOTE_FILE=""
BACKUP_FILE=""
VERIFIED_FILE=""
EXPECTED_REMOTE_HASHES=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --target)
      TARGET=${2:-}
      shift 2
      ;;
    --local-file)
      LOCAL_FILE=${2:-}
      shift 2
      ;;
    --remote-file)
      REMOTE_FILE=${2:-}
      shift 2
      ;;
    --backup-file)
      BACKUP_FILE=${2:-}
      shift 2
      ;;
    --verified-file)
      VERIFIED_FILE=${2:-}
      shift 2
      ;;
    --expected-remote-sha256)
      EXPECTED_REMOTE_HASHES+=("${2:-}")
      shift 2
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 1
      ;;
  esac
done

TARGET=$(echo "${TARGET,,}" | sed 's/^stage$/staging/; s/^prod$/production/; s/^live$/production/')
if [[ "$TARGET" != "staging" && "$TARGET" != "production" ]]; then
  echo "Unsupported deploy target '$TARGET'. Use staging or production." >&2
  exit 1
fi

for value_name in LOCAL_FILE REMOTE_FILE BACKUP_FILE VERIFIED_FILE; do
  if [[ -z "${!value_name}" ]]; then
    echo "Missing required argument for ${value_name}" >&2
    usage >&2
    exit 1
  fi
done

if [[ ! -f "$LOCAL_FILE" ]]; then
  echo "Local deploy file not found: $LOCAL_FILE" >&2
  exit 1
fi

# These local paths are also interpolated into lftp commands. The deployment
# workflow uses repository and runner-temp paths that fit this conservative set.
for local_path in "$LOCAL_FILE" "$BACKUP_FILE" "$VERIFIED_FILE"; do
  if [[ ! "$local_path" =~ ^[A-Za-z0-9._/-]+$ ]]; then
    echo "Local path contains unsupported characters: $local_path" >&2
    exit 1
  fi
done

# Remote paths are interpolated into an lftp command stream. Limit the accepted
# alphabet so a caller cannot turn a path into another command.
if [[ ! "$REMOTE_FILE" =~ ^[A-Za-z0-9._/-]+$ || "$REMOTE_FILE" != */* || "$REMOTE_FILE" == *"//"* ]]; then
  echo "Remote file path contains unsupported characters: $REMOTE_FILE" >&2
  exit 1
fi

REMOTE_PATH_WITHOUT_ROOT=${REMOTE_FILE#/}
IFS='/' read -r -a REMOTE_PATH_SEGMENTS <<< "$REMOTE_PATH_WITHOUT_ROOT"
for path_segment in "${REMOTE_PATH_SEGMENTS[@]}"; do
  if [[ -z "$path_segment" || "$path_segment" == "." || "$path_segment" == ".." ]]; then
    echo "Remote file path contains an unsafe segment: $REMOTE_FILE" >&2
    exit 1
  fi
done

if [[ ${#EXPECTED_REMOTE_HASHES[@]} -eq 0 ]]; then
  echo "At least one --expected-remote-sha256 allowlist entry is required" >&2
  exit 1
fi
for hash in "${EXPECTED_REMOTE_HASHES[@]}"; do
  if [[ ! "$hash" =~ ^[0-9a-f]{64}$ ]]; then
    echo "Invalid expected SHA-256: $hash" >&2
    exit 1
  fi
done

if [[ -f "$REPO_ROOT/.env" ]]; then
  # shellcheck source=/dev/null
  set -a
  source "$REPO_ROOT/.env"
  set +a
fi

for command_name in lftp sha256sum; do
  if ! command -v "$command_name" >/dev/null 2>&1; then
    echo "$command_name is required" >&2
    exit 1
  fi
done

FTP_HOST=${THREEW_FTP_HOST:-147.79.122.118}
DEPLOY_SCHEME=${THREEW_DEPLOY_SCHEME:-ftps}
SSL_VERIFY=${THREEW_SSL_VERIFY:-yes}
FTP_USER=${THREEW_FTP_USER:-u659513315.thrwdiststaging}
FTP_PASS=${THREEW_FTP_PASS:-}
SSH_KEY_PATH=${THREEW_SSH_KEY_PATH:-}
if [[ -n "$SSH_KEY_PATH" && ! "$SSH_KEY_PATH" =~ ^[A-Za-z0-9._/-]+$ ]]; then
  echo "SSH key path contains unsupported characters" >&2
  exit 1
fi
TARGET_LABEL=staging

if [[ "$TARGET" == "production" ]]; then
  FTP_HOST=${THREEW_PROD_FTP_HOST:-$FTP_HOST}
  DEPLOY_SCHEME=${THREEW_PROD_DEPLOY_SCHEME:-$DEPLOY_SCHEME}
  SSL_VERIFY=${THREEW_PROD_SSL_VERIFY:-$SSL_VERIFY}
  FTP_USER=${THREEW_PROD_FTP_USER:-$FTP_USER}
  FTP_PASS=${THREEW_PROD_FTP_PASS:-$FTP_PASS}
  TARGET_LABEL=production
fi

case "$DEPLOY_SCHEME" in
  ftp|ftps) DEFAULT_PORT=21 ;;
  sftp) DEFAULT_PORT=22 ;;
  *)
    echo "Unsupported THREEW_DEPLOY_SCHEME '$DEPLOY_SCHEME'. Use ftp, ftps, or sftp." >&2
    exit 1
    ;;
esac

FTP_PORT=${THREEW_FTP_PORT:-$DEFAULT_PORT}
if [[ "$TARGET" == "production" ]]; then
  FTP_PORT=${THREEW_PROD_FTP_PORT:-$FTP_PORT}
fi

if [[ -z "$FTP_PASS" && ! ( "$DEPLOY_SCHEME" == "sftp" && -n "$SSH_KEY_PATH" ) ]]; then
  if [[ ! -t 0 ]]; then
    echo "No password or SFTP key supplied for ${TARGET_LABEL} deployment" >&2
    exit 1
  fi
  read -r -s -p "FTP password for ${FTP_USER}@${FTP_HOST}: " FTP_PASS
  echo
fi

LFTP_URI="${DEPLOY_SCHEME}://${FTP_HOST}:${FTP_PORT}"
FTP_SETUP_CMDS="set cmd:fail-exit yes
set ssl:verify-certificate $SSL_VERIFY
set xfer:clobber yes"

if [[ "$DEPLOY_SCHEME" == "sftp" ]]; then
  FTP_SETUP_CMDS+=$'\n'"set sftp:auto-confirm yes"
  CONNECT_PROGRAM="ssh -o BatchMode=yes"
  if [[ -n "$SSH_KEY_PATH" ]]; then
    [[ -f "$SSH_KEY_PATH" ]] || { echo "SSH key not found: $SSH_KEY_PATH" >&2; exit 1; }
    CONNECT_PROGRAM+=" -i $SSH_KEY_PATH"
  fi
  FTP_SETUP_CMDS+=$'\n'"set sftp:connect-program \"$CONNECT_PROGRAM\""
else
  FTP_SETUP_CMDS+=$'\n'"set ftp:passive-mode on"
  if [[ "$DEPLOY_SCHEME" == "ftps" ]]; then
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-force true
set ftp:ssl-protect-data true
set ftp:ssl-auth TLS"
  else
    FTP_SETUP_CMDS+=$'\n'"set ftp:ssl-force false
set ftp:ssl-protect-data false"
  fi
fi

mkdir -p "$(dirname "$BACKUP_FILE")" "$(dirname "$VERIFIED_FILE")"
rm -f "$BACKUP_FILE" "$VERIFIED_FILE"

echo "Backing up ${TARGET_LABEL}:${REMOTE_FILE}"
lftp -u "$FTP_USER","$FTP_PASS" "$LFTP_URI" <<LFTP_BACKUP
$FTP_SETUP_CMDS
get "$REMOTE_FILE" -o "$BACKUP_FILE"
LFTP_BACKUP

REMOTE_HASH=$(sha256sum "$BACKUP_FILE" | awk '{print $1}')
REMOTE_HASH_ALLOWED=false
for expected_hash in "${EXPECTED_REMOTE_HASHES[@]}"; do
  if [[ "$REMOTE_HASH" == "$expected_hash" ]]; then
    REMOTE_HASH_ALLOWED=true
    break
  fi
done
if [[ "$REMOTE_HASH_ALLOWED" != true ]]; then
  echo "Remote file hash $REMOTE_HASH is not in the reviewed allowlist; refusing overwrite" >&2
  exit 1
fi

restore_backup() {
  local rollback_verified="${VERIFIED_FILE}.rollback"
  rm -f "$rollback_verified"
  echo "Restoring backup to ${TARGET_LABEL}:${REMOTE_FILE}" >&2
  lftp -u "$FTP_USER","$FTP_PASS" "$LFTP_URI" <<LFTP_ROLLBACK
$FTP_SETUP_CMDS
put "$BACKUP_FILE" -o "$REMOTE_FILE"
get "$REMOTE_FILE" -o "$rollback_verified"
LFTP_ROLLBACK
  local rollback_hash
  rollback_hash=$(sha256sum "$rollback_verified" | awk '{print $1}')
  if [[ "$rollback_hash" != "$REMOTE_HASH" ]]; then
    echo "Rollback verification failed: expected $REMOTE_HASH, got $rollback_hash" >&2
    return 1
  fi
  echo "Rollback verified ($rollback_hash)" >&2
}

LOCAL_HASH=$(sha256sum "$LOCAL_FILE" | awk '{print $1}')
echo "Uploading one file to ${TARGET_LABEL}:${REMOTE_FILE}"
if ! lftp -u "$FTP_USER","$FTP_PASS" "$LFTP_URI" <<LFTP_UPLOAD
$FTP_SETUP_CMDS
put "$LOCAL_FILE" -o "$REMOTE_FILE"
get "$REMOTE_FILE" -o "$VERIFIED_FILE"
LFTP_UPLOAD
then
  echo "Upload or read-back failed; attempting automatic rollback" >&2
  if ! restore_backup; then
    echo "Automatic rollback also failed; use the preserved backup file for manual recovery" >&2
  fi
  exit 1
fi

VERIFIED_HASH=$(sha256sum "$VERIFIED_FILE" | awk '{print $1}')
if [[ "$VERIFIED_HASH" != "$LOCAL_HASH" ]]; then
  echo "Remote verification failed: expected $LOCAL_HASH, got $VERIFIED_HASH" >&2
  restore_backup
  exit 1
fi

echo "One-file deployment verified: $REMOTE_FILE ($VERIFIED_HASH)"
