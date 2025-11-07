# Fitment Staging Deployment - WP-CLI REST API Method

**Target**: staging.3wdistributing.com
**Method**: WP-CLI Remote Execution via REST API (No SSH Required)
**Estimated Time**: 20-30 minutes

## Overview

This deployment uses WP-CLI's REST API feature to run commands remotely from your local machine. No SSH access required!

**Advantages**:
- ✅ No SSH needed - runs from local machine
- ✅ Uses WordPress Application Passwords (already configured)
- ✅ Same WP-CLI commands as local/SSH
- ✅ Secure authentication via HTTPS

## Prerequisites

### 1. Verify WP-CLI Installed Locally
```bash
wp --version
# Should show: WP-CLI 2.x or higher
```

If not installed:
```bash
# macOS/Linux
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

### 2. Verify Configuration
```bash
# Check staging alias exists
cat wp-cli.yml
# Should show @staging configuration

# Check credentials in .env
cat .env | grep STAGE_WP
# Should show:
# STAGE_WP_APP_USER=content@auto-blog.com
# STAGE_WP_APP_PASSWORD=<your_staging_password>
```

### 3. Test Remote Connection
```bash
# Test WP-CLI REST API access (will prompt for password)
wp @staging option get home --prompt=password
# Enter password from .env: $STAGE_WP_APP_PASSWORD
# Should return: https://staging.3wdistributing.com
```

**✅ If the test succeeds, you're ready to deploy!**

## Deployment Steps

### Step 1: Build Theme Assets (2-3 min)

```bash
cd wp-content/themes/3w-2025
npm run build
cd ../../..
```

**Verify**:
```bash
ls -lh wp-content/themes/3w-2025/build/view.js
# Should show compiled JavaScript
```

### Step 2: Deploy Theme via FTP (3-5 min)

```bash
bash scripts/deploy-theme.sh
```

**Expected output**:
```
Building theme assets...
Synchronising theme to staging via FTP...
Deployment complete.
```

**What gets deployed**:
- ✅ `inc/fitment-import.php`
- ✅ `inc/fitment-api.php`
- ✅ `functions.php`
- ✅ `build/view.js`
- ✅ All theme files

### Step 3: Upload Product Data (1-2 min)

```bash
# Upload via FTP
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118 <<EOF
set ftp:passive-mode on
set ssl:verify-certificate no
cd wp-content/themes/3w-2025
put woocommerce-products-all.json
bye
EOF
```

**Verify upload**:
```bash
curl -I https://staging.3wdistributing.com/wp-content/themes/3w-2025/woocommerce-products-all.json
# Should return: HTTP/2 200
```

### Step 4: Test Import with Small Batch (1 min)

**Set password as environment variable** (so we don't get prompted repeatedly):
```bash
export STAGE_PASSWORD="$STAGE_WP_APP_PASSWORD"  # From your .env file
```

**Run dry-run test** (10 products, no changes):
```bash
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --limit=10 \
  --dry-run \
  --user=content@auto-blog.com \
  --prompt=password
```

When prompted for password, use `$STAGE_WP_APP_PASSWORD` from your `.env` file

**Expected output**:
```
Creating vehicle attributes...
Success: Attributes created
Fetching products from JSON file: woocommerce-products-all.json
Loaded 5648 products from JSON
Processing 10 products...
[DRY RUN] Would process: Product Name
[DRY RUN] Would process: Product Name
...
Success: Dry run complete!
```

**If dry-run looks good, test actual import** (10 products):
```bash
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --limit=10 \
  --user=content@auto-blog.com \
  --prompt=password
```

**Test API immediately**:
```bash
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years"
# Should return: [years array]
```

### Step 5: Full Import (6-8 min)

**If test successful, import all products**:
```bash
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --user=content@auto-blog.com \
  --prompt=password
```

**Expected output**:
```
Creating vehicle attributes...
Success: Attributes created
Fetching products from JSON file: woocommerce-products-all.json
Source: WooCommerce REST API
Total products in file: 5648
Loaded 5648 products from JSON
Processing 5648 products...
Building fitment inventory...
Success: Inventory built and saved

Import Statistics:
  Processed: 2598
  Success:   2598
  Skipped:   3050
  Errors:    0
Success: Import complete!
```

**⏱️ Duration**: ~6-8 minutes

### Step 6: Verify Deployment (5-10 min)

#### Test REST API Endpoints

```bash
# Years
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years" | jq '.'
# Expected: [2026, 2025, 2024, ..., 2000]

# Makes for 2024
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/makes?year=2024" | jq '.'
# Expected: ["BMW"]

# Models for 2024 BMW
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/models?year=2024&make=BMW" | jq '.'
# Expected: ["M5"]

# Trims for 2024 BMW M5
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/trims?year=2024&make=BMW&model=M5" | jq '.'
# Expected: ["G90", "G99"]
```

#### Test Frontend Selector

1. Open: https://staging.3wdistributing.com
2. Find page with fitment selector block
3. Test cascade:
   - Select Year → Makes populate ✅
   - Select Make → Models populate ✅
   - Select Model → Trims populate ✅
   - Select Trim → Search button enables ✅
4. Click "Search Products" ✅
5. Verify redirects to: `https://shop.3wdistributing.com/?s=BMW+M5+G90&post_type=product` ✅

#### Check Import Stats

```bash
# View inventory data
wp @staging option get threew_fitment_inventory_real \
  --format=json \
  --user=content@auto-blog.com \
  --prompt=password | jq 'keys'

# Should show years: ["2000", "2001", ..., "2026"]
```

## Validation Checklist

### API Validation ✅
- [ ] Years endpoint returns 26 years (2000-2026)
- [ ] Makes endpoint filters by year correctly
- [ ] Models endpoint filters by year+make correctly
- [ ] Trims endpoint filters by year+make+model correctly
- [ ] All responses < 200ms

### Frontend Validation ✅
- [ ] Year dropdown populates on page load
- [ ] Make dropdown enables after year selection
- [ ] Model dropdown enables after make selection
- [ ] Trim dropdown enables after model selection
- [ ] Search button enables when all required fields filled
- [ ] Search URL correctly formatted with vehicle data
- [ ] No JavaScript console errors

### Data Quality ✅
- [ ] 2,598 products with fitment data imported
- [ ] 4 makes: BMW, Ferrari, Mercedes-Benz, Tesla
- [ ] 6 models represented
- [ ] 14 trim levels available
- [ ] No duplicate entries

## Troubleshooting

### Issue: WP-CLI REST API authentication fails

**Symptoms**:
```
Error: Error: Invalid username or password.
```

**Solution**:
```bash
# Verify credentials in .env
cat .env | grep STAGE_WP

# Test WordPress Application Password in browser
curl -u "content@auto-blog.com:$STAGE_WP_APP_PASSWORD" \
  "https://staging.3wdistributing.com/wp-json/wp/v2/users/me"
# Should return user data

# Regenerate application password if needed
# Go to: staging.3wdistributing.com/wp-admin → Users → Profile → Application Passwords
```

### Issue: "fitment" command not found

**Symptoms**:
```
Error: 'fitment' is not a registered wp command.
```

**Solution**:
```bash
# Verify theme deployed correctly
wp @staging theme list --user=content@auto-blog.com --prompt=password
# Should show 3w-2025 as active

# Check functions.php loaded
wp @staging eval "echo defined('THREEW_FITMENT_IMPORT') ? 'loaded' : 'not loaded';" \
  --user=content@auto-blog.com --prompt=password
```

### Issue: JSON file not found

**Symptoms**:
```
Error: JSON file not found: wp-content/themes/3w-2025/woocommerce-products-all.json
```

**Solution**:
```bash
# Verify file uploaded
curl -I https://staging.3wdistributing.com/wp-content/themes/3w-2025/woocommerce-products-all.json

# If 404, re-upload via FTP (see Step 3)

# Or upload via WordPress Media Library:
# 1. Login to staging.3wdistributing.com/wp-admin
# 2. Media → Add New
# 3. Upload woocommerce-products-all.json
# 4. Get file URL and use in import command
```

### Issue: Import timeout

**Symptoms**:
```
Error: The site timed out while processing the import.
```

**Solution**:
```bash
# Import in smaller batches
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --limit=500 \
  --user=content@auto-blog.com \
  --prompt=password

# Run multiple times until all imported
# Or increase PHP max_execution_time on staging
```

## Alternative: Password in Environment Variable

To avoid password prompts, use environment variable:

```bash
# Set password once
export WP_CLI_CONFIG_PATH=wp-cli.yml
export WP_CLI_CONTEXT_@staging_password="$STAGE_WP_APP_PASSWORD"

# Now run commands without --prompt flag
wp @staging fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json
```

**Or create alias**:
```bash
# Add to ~/.bashrc or ~/.zshrc
alias wp-staging='wp @staging --user=content@auto-blog.com --prompt=password'

# Usage
wp-staging fitment import --source=...
wp-staging option get threew_fitment_inventory_real
```

## Rollback Procedure

If deployment causes issues:

```bash
# 1. Delete fitment inventory
wp @staging option delete threew_fitment_inventory_real \
  --user=content@auto-blog.com --prompt=password

# 2. Delete vehicle attributes
wp @staging wc product_attribute delete pa_vehicle_year --force \
  --user=content@auto-blog.com --prompt=password
wp @staging wc product_attribute delete pa_vehicle_make --force \
  --user=content@auto-blog.com --prompt=password
wp @staging wc product_attribute delete pa_vehicle_model --force \
  --user=content@auto-blog.com --prompt=password
wp @staging wc product_attribute delete pa_vehicle_trim --force \
  --user=content@auto-blog.com --prompt=password

# 3. Re-deploy previous theme version
git checkout <previous-commit>
bash scripts/deploy-theme.sh
```

## Quick Command Reference

```bash
# Build & deploy
cd wp-content/themes/3w-2025 && npm run build && cd ../../.. && bash scripts/deploy-theme.sh

# Upload data
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118 -e "set ftp:passive-mode on; set ssl:verify-certificate no; cd wp-content/themes/3w-2025; put woocommerce-products-all.json; bye"

# Test import
wp @staging fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json --limit=10 --dry-run --user=content@auto-blog.com --prompt=password

# Full import
wp @staging fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json --user=content@auto-blog.com --prompt=password

# Test API
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years" | jq '.'
```

## Success Metrics

✅ **Deployment successful when**:
- All 5,648 products processed
- 2,598 products with fitment data imported
- API endpoints return correct data
- Frontend selector works completely
- Search redirects correctly to shop
- No JavaScript errors
- Mobile responsive

---

**Ready to deploy?** Follow the steps in order and you'll be done in ~20-30 minutes!
