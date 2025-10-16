# Import & Export Backlog

## Prerequisites (Staging)
- `wp core verify-checksums`
- `wp db check`
- Ensure WooCommerce is installed and active (`wp plugin list | grep woocommerce`; `wp plugin install woocommerce --activate` if missing)

## Legacy Shop Snapshot (Production)
- Full database backup
- Archive `/wp-content/uploads`

## Product Export (Production)
- `wp wc product export --dir=~/exports --filename=products.csv --with=meta,variations,images`
- Record export timestamp, SKU count (`wp wc product list --format=count`), and file location

## Media Sync
- Pull uploads to local: `rsync -av --progress -e "ssh -p 65002" user@prod_host:/path/to/wp-content/uploads/ ./uploads-prod/`
- Push uploads to staging: `rsync -av --progress -e "ssh -p 65002" ./uploads-prod/ u659513315@147.79.122.118:/home/u659513315/domains/staging.3wdistributing.com/public_html/wp-content/uploads/`

## Catalog Import (Staging)
- `wp wc product import --file=exports/products.csv --mapping=includes/mappings/products.json --merge=true` (or WP All Import Pro with equivalent mapping)
- `wp media regenerate --only-missing`
- `wp post list --post_type=attachment --format=count`
- Spot-check product galleries and marketing assets

## Post-Import Organization
- Place all products into temporary `Legacy Import` category
- Run scripts from runbook §15 to register new taxonomies and remap SKUs
- Verify SKU counts (`wp wc product list --format=count`)

## Sanity Checks
- Crawl staging for 404s (media/downloads)
- Confirm `/shop/{product}` URLs resolve
- `wp db check` post-import

## Documentation
- Update runbook with actual timestamps, commands used, and deviations
