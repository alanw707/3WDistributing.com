# WooCommerce Product Fetcher

Efficient product data collection from shop.3wdistributing.com using WooCommerce REST API.

## Overview

**Performance**: Fetches all 5,648 products in ~6-8 minutes (vs. 10-20 hours with scraping)

**Benefits**:
- ✅ Official WooCommerce API (stable, supported)
- ✅ Structured data with categories and tags
- ✅ No SSH access required
- ✅ Easily automatable
- ✅ Compatible with existing fitment import system

## Setup

### 1. API Credentials

Credentials are stored in `.env.shop` (already configured):
```bash
SHOP_WC_CONSUMER_KEY=ck_c8e5...
SHOP_WC_CONSUMER_SECRET=cs_207...
SHOP_BASE_URL=https://shop.3wdistributing.com
```

**Security**: This file is in `.gitignore` - credentials never committed to repo.

### 2. Verify Setup

Test with 2 pages (200 products):
```bash
node scripts/fetch-woocommerce-products.js --max-pages 2 --output test-wc-products.json
```

## Usage

### Fetch All Products (Recommended)

```bash
# Fetch all 5,648 products (~6-8 minutes)
node scripts/fetch-woocommerce-products.js

# Output: woocommerce-products-all.json
```

### Custom Options

```bash
# Custom output file
node scripts/fetch-woocommerce-products.js --output my-products.json

# Limit number of pages (for testing)
node scripts/fetch-woocommerce-products.js --max-pages 10

# Custom page size (max 100)
node scripts/fetch-woocommerce-products.js --per-page 50

# Combine options
node scripts/fetch-woocommerce-products.js \
  --output partial-products.json \
  --max-pages 20 \
  --per-page 50
```

## Import to Fitment System

After fetching products, run the fitment import:

```bash
# Import all products
wp fitment import --source=woocommerce-products-all.json

# Or import test data
wp fitment import --source=test-wc-products.json
```

## Output Format

The script generates JSON compatible with the fitment import system:

```json
{
  "metadata": {
    "fetchedAt": "2025-10-31T11:30:08.569Z",
    "source": "WooCommerce REST API",
    "baseUrl": "https://shop.3wdistributing.com",
    "totalProducts": 5648,
    "totalPages": 57,
    "note": "Complete product catalog"
  },
  "products": [
    {
      "name": "Product Name with Year/Make/Model/Trim",
      "categories": ["Vendor", "Make", "Model"],
      "tags": ["tag1", "tag2"],
      "url": "https://shop.3wdistributing.com/product/slug/"
    }
  ]
}
```

## Automation

### Manual Schedule
Run weekly or when new products are added:
```bash
node scripts/fetch-woocommerce-products.js && \
wp fitment import --source=woocommerce-products-all.json
```

### Future: Cron Job
```bash
# Run every Sunday at 2am
0 2 * * 0 cd /path/to/project && node scripts/fetch-woocommerce-products.js && wp fitment import --source=woocommerce-products-all.json
```

### Future: GitHub Actions
See `fitment-backlog.md` for automated workflow plans.

## Troubleshooting

### Authentication Error
```
❌ Error: HTTP 401: Unauthorized
```
**Solution**: Verify credentials in `.env.shop` are correct

### Rate Limiting
```
❌ Error: HTTP 429: Too Many Requests
```
**Solution**: Script includes 100ms delay between pages (should not occur)

### Network Timeout
```
❌ Error: ETIMEDOUT
```
**Solution**: Check internet connection, retry

### Invalid JSON
```
❌ Error: Failed to parse JSON
```
**Solution**: API may be down, check shop site status

## Performance Metrics

| Metric | Value |
|--------|-------|
| Total Products | 5,648 |
| Total Pages | 57 |
| Products per Page | 100 |
| Estimated Time | 6-8 minutes |
| Output Size | ~8-12 MB |
| Network Requests | 57 |

## Comparison: WooCommerce API vs. Scraping

| Method | Time | Reliability | Maintenance |
|--------|------|-------------|-------------|
| **WooCommerce API** | 6-8 min | ✅ High | ✅ Low |
| **Playwright Scraping** | 10-20 hrs | ⚠️ Medium | ❌ High |

**Recommendation**: Always use WooCommerce API for bulk operations.

## Next Steps

1. ✅ Test with 2 pages (200 products) - DONE
2. ⏳ Fetch all 5,648 products - READY
3. ⏳ Import to fitment system - AFTER STEP 2
4. ⏳ Verify search functionality - AFTER STEP 3

See `claudedocs/fitment-backlog.md` for detailed roadmap.
