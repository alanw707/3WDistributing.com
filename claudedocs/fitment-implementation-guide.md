# Vehicle Fitment Implementation Guide

Complete implementation of vehicle fitment system for 3W Distributing.

## Overview

This system extracts vehicle compatibility data from shop.3wdistributing.com products and populates the fitment selector on the main site.

### Key Features
- ✅ **KISS Compliant**: Uses native WooCommerce attributes, no custom tables
- ✅ **Automated Parsing**: Extracts Make/Model/Year/Trim from product titles
- ✅ **WP-CLI Command**: `wp fitment import` for easy execution
- ✅ **Performance Optimized**: Caches data in wp_options with 1-hour cache
- ✅ **Dry Run Support**: Test before importing with `--dry-run` flag

---

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                 shop.3wdistributing.com                 │
│                                                         │
│  Products with vehicle data in:                        │
│  - Titles: "2024+ BMW M5 (G90,G99) Exhaust"           │
│  - Categories: Akrapovic → BMW → M5 (G90/G99)         │
│  - Tags: BMW M5, BMW G90, BMW G99                     │
└─────────────────────────────────────────────────────────┘
                          │
                          │ WP-CLI: wp fitment import
                          ↓
┌─────────────────────────────────────────────────────────┐
│          ThreeW_Vehicle_Parser (Parser)                 │
│                                                         │
│  Regex patterns extract:                               │
│  - Make: BMW, Mercedes-Benz, Ferrari, Tesla           │
│  - Model: M5, G-Wagon, Model 3                        │
│  - Year: 2024, 2025, 2024-2025, 1999-2018             │
│  - Trim: G90, G99, W463, AMG                          │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│      ThreeW_Attribute_Manager (WooCommerce)             │
│                                                         │
│  Creates/assigns product attributes:                   │
│  - pa_vehicle_make                                     │
│  - pa_vehicle_model                                    │
│  - pa_vehicle_year                                     │
│  - pa_vehicle_trim                                     │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│    ThreeW_Fitment_Inventory (Aggregation)              │
│                                                         │
│  Builds nested structure:                              │
│  {                                                      │
│    "2024": {                                           │
│      "BMW": {                                          │
│        "M5": ["G90", "G99"]                           │
│      }                                                  │
│    }                                                    │
│  }                                                      │
│                                                         │
│  Saves to: wp_options['threew_fitment_inventory_real'] │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│         REST API Endpoints (inc/fitment-api.php)        │
│                                                         │
│  /wp-json/threew/v1/fitment/years                      │
│  /wp-json/threew/v1/fitment/makes?year=2024            │
│  /wp-json/threew/v1/fitment/models?year=2024&make=BMW  │
│  /wp-json/threew/v1/fitment/trims?...                  │
└─────────────────────────────────────────────────────────┘
                          │
                          ↓
┌─────────────────────────────────────────────────────────┐
│         Frontend Fitment Selector (view.js)             │
│                                                         │
│  Year → Make → Model → Trim cascading dropdowns        │
│  localStorage persistence                              │
│  Shop URL generation with filters                      │
└─────────────────────────────────────────────────────────┘
```

---

## Files Created/Modified

### New Files

**`inc/fitment-import.php`** (530 lines)
- `ThreeW_Vehicle_Parser` - Parses product titles for vehicle data
- `ThreeW_Attribute_Manager` - Creates WooCommerce attributes
- `ThreeW_Fitment_Inventory` - Builds aggregated inventory
- `ThreeW_Fitment_CLI` - WP-CLI commands

**`test-fitment-import.sh`**
- Test script for dry-run and import validation

**`claudedocs/fitment-discovery-report.md`**
- Complete analysis of shop structure

### Modified Files

**`inc/fitment-api.php`** (line 203-233)
- Updated `get_fitment_inventory()` to check wp_options first
- Falls back to sample data if no real inventory exists

**`functions.php`** (line 203-206)
- Added `require_once` for fitment-import.php

**`.env`** (line 20-23)
- Added shop site credentials

---

## Usage

### 1. Test with Dry Run (Local)

```bash
# Navigate to project
cd /home/alanw/projects/3WDistributing.com

# Run test script
./test-fitment-import.sh
```

This will:
1. Verify WP-CLI is working
2. Run dry-run on 10 products
3. Show what would be imported
4. Prompt before actual import

### 2. Import Fitment Data

#### Option A: Limited Test Import
```bash
wp fitment import --limit=10
```

#### Option B: Full Import (All Products)
```bash
wp fitment import
```

#### Option C: Dry Run (No Changes)
```bash
wp fitment import --dry-run
wp fitment import --limit=10 --dry-run
```

### 3. Clear Fitment Data

```bash
wp fitment clear
```

⚠️ **Warning**: This removes all vehicle attributes from products and clears inventory.

---

## WP-CLI Commands Reference

### Import Command
```bash
wp fitment import [--limit=<number>] [--dry-run]
```

**Options**:
- `--limit=<number>` - Process only N products (default: all)
- `--dry-run` - Preview what would be imported without making changes

**Output**:
```
Creating vehicle attributes...
✓ Attributes created
Processing 500 products...
Importing fitment data ████████████████████ 100%
Building fitment inventory...
✓ Inventory built and saved

Import Statistics:
  Processed: 500
  Success:   475
  Skipped:   20  (no vehicle data found)
  Errors:    5

✓ Import complete!
```

### Clear Command
```bash
wp fitment clear
```

Removes all vehicle attributes and inventory data.

---

## Data Parsing Logic

### Make Extraction
```php
Known makes: BMW, Mercedes-Benz, Ferrari, Tesla, Porsche, Audi, Lamborghini
Priority: Title → Categories
```

**Examples**:
- "Akrapovic 2024+ **BMW** M5" → BMW
- Category: Eventuri → **Ferrari** → BMW

### Model Extraction
```php
Patterns:
- BMW: M5, M4, Z8
- Mercedes: G-Wagon, G-Class, G63, G65
- Ferrari: F12 Berlinetta
- Tesla: Model 3
```

**Examples**:
- "BMW **M5** (G90,G99)" → M5
- "Mercedes **G-Wagon** W463" → G-Wagon
- "Tesla **Model 3** Spoiler" → Model 3

### Year Extraction
```php
Patterns:
- Single: \b(20\d{2})\b          // 2024
- Open:   \b(20\d{2})\+\b        // 2024+
- Range:  \b(20\d{2})-(20\d{2})\b // 2024-2025
- Legacy: \((\d{4})-(\d{4})\)    // (1999-2018)
```

**Examples**:
- "**2024+** BMW M5" → [2024, 2025, 2026]
- "**2024-2025** Mercedes G63" → [2024, 2025]
- Category: "W463 **(1999-2018)**" → [1999...2018]

### Trim Extraction
```php
Patterns:
- Chassis: \(([A-Z]\d+[A-Z]?(?:[,/]\s*[A-Z]\d+[A-Z]?)*)\)
- Standalone: \b([WEF]\d{2,3}[A-Z]?|G\d{2}[A-Z]?)\b
- AMG: \bAMG\b
```

**Examples**:
- "BMW M5 **(G90,G99)**" → [G90, G99]
- "Mercedes **W463A** G63" → [W463A]
- "Mercedes **AMG** GT" → [AMG]

---

## Chassis Code Mapping

Built-in chassis code to year mapping:

```php
'W463'  => 1990-2018  // Mercedes G-Wagon (classic)
'W463A' => 2018-2024  // Mercedes G-Wagon (new gen)
'W465'  => 2024+      // Mercedes G-Wagon (latest)
'G90'   => 2024+      // BMW M5 sedan
'G99'   => 2024+      // BMW M5 wagon
'G9X'   => 2024+      // BMW M5 platform
'E52'   => 2000-2003  // BMW Z8
'F90'   => 2018-2024  // BMW M5 (previous gen)
'E60'   => 2005-2010  // BMW M5 (older gen)
```

---

## Validation & Testing

### 1. Verify Attributes Created
```bash
wp wc product_attribute list
```

Expected output:
```
+----+-------------------+-----------------+
| id | name              | slug            |
+----+-------------------+-----------------+
| 1  | Vehicle Make      | pa_vehicle_make |
| 2  | Vehicle Model     | pa_vehicle_model|
| 3  | Vehicle Year      | pa_vehicle_year |
| 4  | Vehicle Trim      | pa_vehicle_trim |
+----+-------------------+-----------------+
```

### 2. Check Product Attributes
```bash
# Find products with vehicle make
wp post list --post_type=product \
  --tax_query='pa_vehicle_make,EXISTS' \
  --fields=ID,post_title \
  --format=table
```

### 3. Test API Endpoints
```bash
# Get years
curl http://localhost:8080/wp-json/threew/v1/fitment/years | jq '.'

# Get makes for 2024
curl "http://localhost:8080/wp-json/threew/v1/fitment/makes?year=2024" | jq '.'

# Get models for 2024 BMW
curl "http://localhost:8080/wp-json/threew/v1/fitment/models?year=2024&make=BMW" | jq '.'
```

### 4. Verify Frontend Selector
1. Visit http://localhost:8080
2. Locate fitment selector on homepage
3. Select Year → Make → Model → Trim
4. Click "Shop Now" - should redirect to shop with filters

---

## Deployment

### Local Development
```bash
# 1. Test with dry run
wp fitment import --limit=10 --dry-run

# 2. Import small batch
wp fitment import --limit=10

# 3. Verify frontend works
# Visit http://localhost:8080 and test selector
```

### Staging Deployment
```bash
# 1. Deploy theme with new files
bash scripts/deploy-staging.sh

# 2. SSH to staging
ssh staging-server

# 3. Run import
cd /path/to/wordpress
wp fitment import --limit=50 --dry-run  # Test first
wp fitment import                        # Full import

# 4. Test selector at staging.3wdistributing.com
```

### Production Deployment
```bash
# 1. Backup database
wp db export backup-before-fitment.sql

# 2. Deploy theme
bash scripts/deploy-production.sh

# 3. Run import
wp fitment import --dry-run  # Final validation
wp fitment import            # Production import

# 4. Verify www.3wdistributing.com fitment selector
```

---

## Performance Considerations

### Caching Strategy
1. **WP Object Cache**: 1 hour TTL for inventory
2. **wp_options**: Persistent storage for inventory
3. **Clear Cache**: Automatically cleared on import

### Optimization Tips
```bash
# For large catalogs (1000+ products):
# 1. Run during low-traffic hours
# 2. Consider batching:
wp fitment import --limit=100  # Batch 1
wp fitment import --limit=100 --offset=100  # Batch 2 (future)

# 3. Monitor memory:
wp --allow-root fitment import --limit=1000
```

---

## Troubleshooting

### Issue: "wp fitment" command not found
**Solution**:
1. Verify `inc/fitment-import.php` exists
2. Check `functions.php` includes the file
3. Restart PHP-FPM if using Docker

### Issue: No vehicle data extracted
**Possible Causes**:
- Product titles don't match patterns
- Products are drafts (only published products imported)

**Debug**:
```bash
# Check a specific product manually
wp post get 123456 --format=json | jq '.post_title'

# Run with single product for debugging
wp eval 'var_dump(ThreeW_Vehicle_Parser::parse_product(wc_get_product(123456)));'
```

### Issue: Import shows "Skipped: X"
**Explanation**: Products without recognizable vehicle data in titles are skipped

**Check skipped products**:
```bash
wp fitment import --limit=10 --dry-run | grep "Skipped"
```

### Issue: Attributes created but not showing in selector
**Solution**:
1. Clear cache: `wp cache flush`
2. Check wp_options: `wp option get threew_fitment_inventory_real`
3. Rebuild inventory: `wp fitment import` (re-runs inventory build)

---

## Future Enhancements

### Phase 3 (Optional)
1. **Manual Override UI**: Admin interface to correct parsing errors
2. **Product Meta Sync**: Sync attributes to shop.3wdistributing.com
3. **Incremental Updates**: Only import changed products
4. **API Integration**: Fetch products via WooCommerce REST API instead of internal queries
5. **Advanced Parsing**: Handle edge cases like "Fits all 2020-2024 BMW M-Series"

---

## KISS Compliance Checklist

✅ **Uses Native WooCommerce Features**
- Product attributes (pa_vehicle_*)
- Built-in taxonomy system
- WP Object Cache

✅ **No Custom Database Tables**
- wp_options for aggregated data
- Standard WooCommerce taxonomies

✅ **Simple Execution**
- Single WP-CLI command
- Dry-run testing
- Clear statistics

✅ **Minimal Dependencies**
- No external APIs required
- No additional plugins
- Pure WordPress/WooCommerce

---

## Summary

The vehicle fitment system is now fully implemented and ready for testing. The KISS approach ensures:

1. **Simplicity**: Uses native WooCommerce, no custom complexity
2. **Reliability**: Proven WordPress patterns, well-tested tools
3. **Maintainability**: Clear code, comprehensive documentation
4. **Performance**: Efficient caching, optimized queries

**Next Step**: Run `./test-fitment-import.sh` to verify everything works!
