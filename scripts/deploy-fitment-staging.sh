#!/usr/bin/env bash
#
# Deploy fitment feature to staging and run import via REST API
#
# Usage: bash scripts/deploy-fitment-staging.sh
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

# Load environment variables
if [[ -f "${PROJECT_ROOT}/.env" ]]; then
  set -a
  source "${PROJECT_ROOT}/.env"
  set +a
fi

# Verify credentials
if [[ -z "${STAGE_WP_APP_USER:-}" || -z "${STAGE_WP_APP_PASSWORD:-}" ]]; then
  echo "❌ Error: STAGE_WP_APP_USER and STAGE_WP_APP_PASSWORD must be set in .env" >&2
  exit 1
fi

STAGE_BASE_URL="${STAGE_WP_BASE_URL:-https://staging.3wdistributing.com}"
AUTH_HEADER="Authorization: Basic $(printf '%s:%s' "${STAGE_WP_APP_USER}" "${STAGE_WP_APP_PASSWORD}" | base64)"

echo "🚀 Fitment Feature - Staging Deployment"
echo "========================================"
echo ""

# Step 1: Build theme assets
echo "📦 Step 1: Building theme assets..."
cd "${PROJECT_ROOT}/wp-content/themes/3w-2025"
npm run build
cd "${PROJECT_ROOT}"
echo "✅ Build complete"
echo ""

# Step 2: Deploy theme via FTP
echo "📤 Step 2: Deploying theme to staging..."
bash "${SCRIPT_DIR}/deploy-staging.sh"
echo "✅ Theme deployed"
echo ""

# Step 3: Upload product data
echo "📊 Step 3: Uploading product data (2.3MB)..."
lftp -u "${THREEW_FTP_USER:-u659513315.thrwdiststaging},${THREEW_FTP_PASS:-}" "ftp://${THREEW_FTP_HOST:-147.79.122.118}" <<EOF
set ftp:passive-mode on
set ssl:verify-certificate no
cd wp-content/themes/3w-2025
put "${PROJECT_ROOT}/woocommerce-products-all.json"
bye
EOF
echo "✅ Product data uploaded"
echo ""

# Step 4: Verify file uploaded
echo "🔍 Step 4: Verifying file upload..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
  "${STAGE_BASE_URL}/wp-content/themes/3w-2025/woocommerce-products-all.json")

if [[ "${HTTP_CODE}" == "200" ]]; then
  echo "✅ File verified: woocommerce-products-all.json accessible"
else
  echo "⚠️  Warning: File returned HTTP ${HTTP_CODE}"
fi
echo ""

# Step 5: Trigger import via REST API
echo "⚙️  Step 5: Triggering fitment import..."
echo "   This will import 5,648 products (~6-8 minutes)"
echo "   Progress will be shown below..."
echo ""

# Call the fitment import REST endpoint
IMPORT_RESPONSE=$(curl -sS -X POST \
  -H "${AUTH_HEADER}" \
  -H "Content-Type: application/json" \
  -d '{"source":"wp-content/themes/3w-2025/woocommerce-products-all.json"}' \
  "${STAGE_BASE_URL}/wp-json/threew/v1/fitment/import" 2>&1)

# Check if import was successful
if echo "${IMPORT_RESPONSE}" | jq -e '.success' > /dev/null 2>&1; then
  echo "✅ Import completed successfully!"
  echo ""
  echo "📊 Import Statistics:"
  echo "${IMPORT_RESPONSE}" | jq -r '.data | "   Processed: \(.processed)\n   Success: \(.success)\n   Skipped: \(.skipped)\n   Errors: \(.errors)"'
else
  echo "❌ Import failed or endpoint not available"
  echo "Response: ${IMPORT_RESPONSE}"
  echo ""
  echo "⚠️  The import REST endpoint may not be available yet."
  echo "   You'll need to run the import manually via hosting panel WP-CLI:"
  echo "   wp fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json"
  exit 1
fi
echo ""

# Step 6: Verify API endpoints
echo "🧪 Step 6: Verifying API endpoints..."
YEARS=$(curl -sS "${STAGE_BASE_URL}/wp-json/threew/v1/fitment/years")
YEAR_COUNT=$(echo "${YEARS}" | jq 'length')

if [[ "${YEAR_COUNT}" -gt 0 ]]; then
  echo "✅ API working - ${YEAR_COUNT} years available"
  echo "   Sample years: $(echo "${YEARS}" | jq -r '.[0:5] | join(", ")')"
else
  echo "❌ API returned no data"
  echo "   Response: ${YEARS}"
fi
echo ""

# Final summary
echo "🎉 Deployment Complete!"
echo "======================="
echo ""
echo "Next steps:"
echo "1. Visit: ${STAGE_BASE_URL}"
echo "2. Test fitment selector (Year → Make → Model → Trim)"
echo "3. Click 'Search Products' and verify redirect to shop"
echo ""
echo "API Endpoints:"
echo "- ${STAGE_BASE_URL}/wp-json/threew/v1/fitment/years"
echo "- ${STAGE_BASE_URL}/wp-json/threew/v1/fitment/makes?year=2024"
echo "- ${STAGE_BASE_URL}/wp-json/threew/v1/fitment/models?year=2024&make=BMW"
echo "- ${STAGE_BASE_URL}/wp-json/threew/v1/fitment/trims?year=2024&make=BMW&model=M5"
echo ""
