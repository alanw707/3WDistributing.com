# Quick Staging Deployment Checklist

**Target**: staging.3wdistributing.com
**Method**: WP-CLI REST API (No SSH Required!)
**Time**: ~20-30 minutes

## Pre-Flight

- [ ] WP-CLI installed locally: `wp --version`
- [ ] Credentials ready: `cat .env | grep STAGE_WP`
- [ ] Product data ready: `ls -lh woocommerce-products-all.json`
- [ ] Test connection: `wp @staging option get home --prompt=password`

**Password when prompted**: Use `STAGE_WP_APP_PASSWORD` from your `.env` file

## Deploy (Execute in Order)

### 1. Build & Deploy Theme (5-8 min)
```bash
# Build assets
cd wp-content/themes/3w-2025
npm run build
cd ../../..

# Deploy to staging
bash scripts/deploy-theme.sh
```

### 2. Upload Product Data (1-2 min)
```bash
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118 <<EOF
set ftp:passive-mode on
set ssl:verify-certificate no
cd wp-content/themes/3w-2025
put woocommerce-products-all.json
bye
EOF
```

**Verify**:
```bash
curl -I https://staging.3wdistributing.com/wp-content/themes/3w-2025/woocommerce-products-all.json
```

### 3. Test Import (1 min)
```bash
# Dry run with 10 products
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --limit=10 \
  --dry-run \
  --user=content@auto-blog.com \
  --prompt=password
```

**Password**: Use `STAGE_WP_APP_PASSWORD` from your `.env` file

### 4. Full Import (6-8 min)
```bash
# Import all 5,648 products (2,598 with fitment data)
wp @staging fitment import \
  --source=wp-content/themes/3w-2025/woocommerce-products-all.json \
  --user=content@auto-blog.com \
  --prompt=password
```

### 5. Verify (5-10 min)
```bash
# Test API
curl "https://staging.3wdistributing.com/wp-json/threew/v1/fitment/years" | jq '.'

# Test frontend
open https://staging.3wdistributing.com
```

## Validation Checklist

- [ ] API returns years: `[2026, 2025, 2024, ..., 2000]`
- [ ] Frontend selector cascades: Year → Make → Model → Trim
- [ ] Search button generates URL: `?s=BMW+M5+G90&post_type=product`
- [ ] Shop shows relevant results
- [ ] No JavaScript console errors
- [ ] Mobile responsive works
- [ ] Import stats: 2,598 products with fitment data

## One-Liner Commands

**Complete deployment**:
```bash
cd wp-content/themes/3w-2025 && npm run build && cd ../../.. && bash scripts/deploy-theme.sh
```

**Upload + Import**:
```bash
lftp -u u659513315.thrwdiststaging ftp://147.79.122.118 -e "set ftp:passive-mode on; set ssl:verify-certificate no; cd wp-content/themes/3w-2025; put woocommerce-products-all.json; bye" && wp @staging fitment import --source=wp-content/themes/3w-2025/woocommerce-products-all.json --user=content@auto-blog.com --prompt=password
```

## Troubleshooting

**Build fails**: `cd wp-content/themes/3w-2025 && npm install && npm run build`

**Auth fails**: Verify password in `.env` → `cat .env | grep STAGE_WP_APP_PASSWORD`

**Import fails**: Check JSON uploaded → `curl -I https://staging.3wdistributing.com/wp-content/themes/3w-2025/woocommerce-products-all.json`

**API empty**: Re-run import or check theme active → `wp @staging theme list --user=content@auto-blog.com --prompt=password`

## Quick Tips

**Avoid password prompts**:
```bash
alias wp-staging='wp @staging --user=content@auto-blog.com --prompt=password'
wp-staging fitment import --source=...
```

**Check import status**:
```bash
wp @staging option get threew_fitment_inventory_real --format=json --user=content@auto-blog.com --prompt=password | jq 'keys'
```

## Full Guide

See: `claudedocs/fitment-staging-deployment-wpcli-rest.md` for detailed instructions and troubleshooting.

---

**Ready to start?** Run step 1! ⬆️
