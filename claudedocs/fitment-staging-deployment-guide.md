# Fitment Feature - Staging Deployment Guide

**Target Environment**: staging.3wdistributing.com
**Server**: 147.79.122.118 (Hostinger)
**Estimated Time**: 20-30 minutes

## Overview

This guide covers deploying the complete vehicle fitment system to staging, including:
- Theme PHP files (import system, REST API)
- Frontend JavaScript (fitment selector block)
- Product data import (5,648 products)
- Testing and validation

## Pre-Deployment Checklist

### 1. Verify Local Environment ✅
```bash
# All files should exist
ls -lh wp-content/themes/3w-2025/inc/fitment-import.php
ls -lh wp-content/themes/3w-2025/inc/fitment-api.php
ls -lh wp-content/themes/3w-2025/functions.php
ls -lh wp-content/themes/3w-2025/src/blocks/fitment-selector/view.js
ls -lh woocommerce-products-all.json
```

### 2. Verify Dependencies
```bash
# Check npm installed
npm --version

# Check lftp installed (for deployment)
lftp --version || sudo apt install lftp

# Check deployment script
ls -lh scripts/deploy-staging.sh
```

### 3. Environment Variables
Confirm `.env` contains:
```bash
THREEW_FTP_HOST=147.79.122.118
THREEW_FTP_USER=u659513315.thrwdiststaging
THREEW_FTP_PASS=(already configured)
THREEW_DEPLOY_SCHEME=ftp
THREEW_REMOTE_THEME_DIR=wp-content/themes/3w-2025
```

## Deployment Steps

### Step 1: Build Theme Assets

**What it does**: Compiles fitment-selector/view.js and other JavaScript assets

```bash
cd wp-content/themes/3w-2025
npm run build
```

**Expected output**:
```
> 3w-2025-theme@0.1.0 build
> wp-scripts build src/index.js src/blocks/fitment-selector/view.js

✔ Compiled successfully in X seconds
```

**Verify build**:
```bash
ls -lh build/view.js
# Should show compiled JavaScript file
```

### Step 2: Deploy Theme to Staging

**What it does**: Syncs theme files to staging via FTP

```bash
# From project root
bash scripts/deploy-staging.sh
```

**What gets deployed**:
- ✅ `inc/fitment-import.php` (import system)
- ✅ `inc/fitment-api.php` (REST API)
- ✅ `functions.php` (registration)
- ✅ `build/view.js` (compiled frontend)
- ✅ All other theme files

**What gets excluded**:
- ❌ `src/` (source files)
- ❌ `node_modules/`
- ❌ `.git/`, `.github/`
- ❌ Development files

**Expected output**:
```
Building theme assets...
Synchronising theme to staging via FTP...
Total: X files, X.X MB
Deployment complete. Clear any staging caches and verify the site.
```

**Duration**: 3-5 minutes

### Step 3: Upload Product Data

**Option A: Via FTP (Recommended)**

```bash
# Upload JSON file to staging
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118 <<EOF
set ftp:passive-mode on
set ssl:verify-certificate no
cd wp-content/themes/3w-2025
put woocommerce-products-all.json
bye
EOF
```

**Option B: Via WordPress Admin**
1. Login to staging.3wdistributing.com/wp-admin
2. Go to Media → Add New
3. Upload `woocommerce-products-all.json`
4. Note the file URL for import command

**Verify upload**:
```bash
# Check file exists on staging
curl -I https://staging.3wdistributing.com/wp-content/themes/3w-2025/woocommerce-products-all.json
# Should return 200 OK
```

### Step 4: Run Import on Staging

**⚠️ IMPORTANT: Verify SSH/WP-CLI Access First**

#### Check SSH Access

```bash
# Try to connect to staging
ssh u659513315.thrwdiststaging@147.79.122.118

# If successful, check WP-CLI
wp --info
```

#### Scenario A: SSH + WP-CLI Available (Ideal) ✅

```bash
# SSH to staging
ssh u659513315.thrwdiststaging@147.79.122.118

# Navigate to WordPress root
cd ~/public_html  # Or wherever WordPress is installed

# Test with small batch first
wp fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json --limit=10 --dry-run

# If dry-run looks good, import small batch
wp fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json --limit=10

# Check API works
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years"

# If test successful, import all products
wp fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json
```

**Duration**: 6-8 minutes for full import

#### Scenario B: No SSH Access (Alternative) ⚠️

If SSH is not available, you'll need to create a one-time import trigger:

**Create admin import trigger**:
```php
// Add to functions.php temporarily (remove after import)
add_action('admin_init', function() {
    if (isset($_GET['run_fitment_import']) && current_user_can('manage_options')) {
        $source = WP_CONTENT_DIR . '/themes/3w-2025/woocommerce-products-all.json';

        // Run import (simplified - use actual import class)
        $importer = new ThreeW_Fitment_Importer();
        $result = $importer->import_from_json($source);

        echo '<div class="notice notice-success"><p>Import complete!</p></div>';
        die();
    }
});
```

**Trigger import**:
```
https://staging.3wdistributing.com/wp-admin/?run_fitment_import=1
```

**⚠️ Security**: Remove trigger code after import!

### Step 5: Verify Deployment

#### Check REST API Endpoints

```bash
# Years endpoint
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years"
# Expected: [2026, 2025, 2024, ...]

# Makes for 2024
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/makes?year=2024"
# Expected: ["BMW"]

# Models for 2024 BMW
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/models?year=2024&make=BMW"
# Expected: ["M5"]

# Trims for 2024 BMW M5
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/trims?year=2024&make=BMW&model=M5"
# Expected: ["G90", "G99"]
```

#### Check Frontend Selector

1. Visit: https://staging.3wdistributing.com
2. Find page with fitment selector block
3. Test cascade:
   - Select Year → Makes populate
   - Select Make → Models populate
   - Select Model → Trims populate
   - Select Trim → Search button enables
4. Click "Search Products"
5. Verify redirects to: `https://shop.3wdistributing.com/?s=BMW+M5+G90&post_type=product`

#### Check Import Results

If you have SSH access:
```bash
# Check inventory data
wp option get threew_fitment_inventory_real --format=json | head -100

# Check import was successful
# Should show 2,598 products with fitment data
```

### Step 6: Test Search Results

1. Complete a vehicle selection on staging
2. Click "Search Products"
3. Verify shop.3wdistributing.com shows relevant results
4. Test multiple vehicle combinations:
   - 2024 BMW M5 G90
   - 2025 Mercedes-Benz G63
   - 2020 Tesla Model 3

## Post-Deployment Validation

### Functionality Checklist
- [ ] Year dropdown populates (26 years)
- [ ] Make dropdown enables after year selection
- [ ] Model dropdown enables after make selection
- [ ] Trim dropdown enables after model selection
- [ ] Search button enables when all required fields filled
- [ ] Search redirects to shop with correct query
- [ ] Shop shows relevant product results

### Performance Checks
- [ ] API endpoints respond < 200ms
- [ ] Frontend selector loads < 1 second
- [ ] No JavaScript errors in console
- [ ] Mobile responsive design works

### Data Quality Checks
- [ ] Years range: 2000-2026
- [ ] Makes: BMW, Ferrari, Mercedes-Benz, Tesla
- [ ] Product count: ~2,598 with fitment data
- [ ] No duplicate entries

## Troubleshooting

### Issue: Build fails
```bash
# Solution: Install dependencies
cd wp-content/themes/3w-2025
npm install
npm run build
```

### Issue: FTP deployment fails
```bash
# Check credentials
cat .env | grep THREEW_FTP

# Test FTP connection manually
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118
```

### Issue: API returns empty arrays
```bash
# Check if import ran successfully
# SSH to staging and check option:
wp option get threew_fitment_inventory_real

# If empty, re-run import:
wp fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json
```

### Issue: Frontend selector doesn't appear
1. Check theme is active on staging
2. Check JavaScript console for errors
3. Verify build/view.js was deployed
4. Clear WordPress and browser caches

### Issue: SSH access denied
**Alternatives**:
1. Contact hosting support for SSH access
2. Use WordPress admin import trigger (see Scenario B)
3. Use phpMyAdmin to manually insert option data
4. Use WP-CLI via hosting control panel if available

## Rollback Procedure

If deployment causes issues:

### Quick Rollback
```bash
# Re-deploy previous version
git checkout <previous-commit>
bash scripts/deploy-staging.sh
```

### Database Rollback
```bash
# SSH to staging
wp db export backup-before-rollback.sql

# Delete fitment option
wp option delete threew_fitment_inventory_real

# Delete vehicle attributes if needed
wp wc product_attribute delete pa_vehicle_year --force
wp wc product_attribute delete pa_vehicle_make --force
wp wc product_attribute delete pa_vehicle_model --force
wp wc product_attribute delete pa_vehicle_trim --force
```

## Production Deployment

After successful staging validation, production deployment follows same process:

**Key Differences**:
1. Different FTP credentials (production)
2. Different WordPress URL (www.3wdistributing.com)
3. **CRITICAL**: Backup production database first
4. Deploy during low-traffic hours
5. Monitor errors closely

**See**: `fitment-production-deployment-guide.md` (to be created)

## Success Criteria

✅ All checks pass:
- Theme deployed successfully
- Product data imported (2,598 products)
- API endpoints return correct data
- Frontend selector works completely
- Search redirects correctly
- Shop shows relevant results
- No JavaScript errors
- Mobile responsive

## Support Resources

- **Local Testing**: http://localhost:8080
- **Staging URL**: https://staging.3wdistributing.com
- **Shop URL**: https://shop.3wdistributing.com
- **Documentation**: `claudedocs/fitment-*.md`
- **Import System**: `wp-content/themes/3w-2025/inc/fitment-import.php`
- **REST API**: `wp-content/themes/3w-2025/inc/fitment-api.php`

## Timeline

| Step | Duration | Cumulative |
|------|----------|------------|
| Build Assets | 2-3 min | 2-3 min |
| FTP Deploy | 3-5 min | 5-8 min |
| Upload Data | 1-2 min | 6-10 min |
| Run Import | 6-8 min | 12-18 min |
| Test & Verify | 5-10 min | 17-28 min |

**Total**: 20-30 minutes

---

**Ready to deploy?** Start with Step 1: Build Theme Assets
