# Pre-Deployment Setup

Quick setup to prepare for staging deployment.

## Install WP-CLI (if not installed)

### Check if installed:
```bash
wp --version
```

### Install on Linux/WSL:
```bash
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Verify
wp --version
```

### Install on macOS:
```bash
brew install wp-cli

# Or manually:
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp
```

## Test Connection to Staging

```bash
# Test WP-CLI REST API access
wp @staging option get home --prompt=password
```

**Password when prompted**: Use the value from your `.env` file: `STAGE_WP_APP_PASSWORD`

**Expected output**: `https://staging.3wdistributing.com`

✅ **If you see the URL, you're ready to deploy!**

❌ **If you get errors**, check:
- WP-CLI installed: `wp --version`
- wp-cli.yml exists: `cat wp-cli.yml`
- Credentials in .env: `cat .env | grep STAGE_WP`

## Quick Verification Checklist

- [ ] WP-CLI installed and working
- [ ] Can connect to staging via REST API
- [ ] Theme build works: `cd wp-content/themes/3w-2025 && npm run build`
- [ ] lftp installed: `which lftp` (for FTP upload)
- [ ] Product data ready: `ls -lh woocommerce-products-all.json`

**All checks pass?** → Proceed to [DEPLOY-STAGING.md](DEPLOY-STAGING.md)
