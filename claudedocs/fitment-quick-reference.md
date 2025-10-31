# Vehicle Fitment System - Quick Reference

Fast reference guide for common operations and commands.

---

## Quick Links

- **Progress Report**: `claudedocs/fitment-progress-report.md`
- **Backlog**: `claudedocs/fitment-backlog.md`
- **Changelog**: `claudedocs/fitment-changelog.md`
- **Implementation Guide**: `claudedocs/fitment-implementation-guide.md`
- **Testing Notes**: `claudedocs/fitment-testing-notes.md`

---

## Common Commands

### Import from JSON File
```bash
# Dry run (preview only, no changes)
docker compose exec wordpress wp --allow-root fitment import \
  --source=scraped-products-sample.json \
  --limit=10 \
  --dry-run

# Actual import
docker compose exec wordpress wp --allow-root fitment import \
  --source=scraped-products-sample.json \
  --limit=50

# Full import (all products in file)
docker compose exec wordpress wp --allow-root fitment import \
  --source=scraped-products-all.json
```

### Check Inventory Data
```bash
# View all years
curl http://localhost:8080/wp-json/threew/v1/fitment/years | jq .

# View makes for specific year
curl "http://localhost:8080/wp-json/threew/v1/fitment/makes?year=2024" | jq .

# View models for year + make
curl "http://localhost:8080/wp-json/threew/v1/fitment/models?year=2024&make=BMW" | jq .

# View trims for year + make + model
curl "http://localhost:8080/wp-json/threew/v1/fitment/trims?year=2024&make=BMW&model=M5" | jq .

# View raw inventory from database
docker compose exec wordpress wp --allow-root option get threew_fitment_inventory_real --format=json | jq .
```

### Rebuild Frontend JavaScript
```bash
cd wp-content/themes/3w-2025
npm run build

# Or watch mode for development
npm run start
```

### Clear Cache
```bash
# Clear WordPress object cache
docker compose exec wordpress wp --allow-root cache flush

# Clear fitment inventory cache
docker compose exec wordpress wp --allow-root option delete threew_fitment_inventory_real
```

---

## File Locations

### Core Files
```
wp-content/themes/3w-2025/
├── inc/
│   ├── fitment-import.php    # Import system (797 lines)
│   └── fitment-api.php        # REST API endpoints
├── src/blocks/fitment-selector/
│   ├── view.js                # Frontend JavaScript (688 lines)
│   ├── save.js                # Block save function
│   ├── edit.js                # Block editor UI
│   └── style.css              # Frontend styles
└── build/
    ├── view.js                # Compiled JavaScript
    └── view.asset.php         # Asset dependencies
```

### Documentation
```
claudedocs/
├── fitment-progress-report.md      # Current status and achievements
├── fitment-backlog.md              # Future tasks and roadmap
├── fitment-changelog.md            # Version history
├── fitment-implementation-guide.md # Technical documentation
├── fitment-testing-notes.md        # Test results
└── fitment-quick-reference.md      # This file
```

### Data Files
```
/
├── scraped-products-sample.json    # 20 products (test data)
├── scraped-products-page1.json     # Page 1 data
└── scripts/
    └── scrape-shop-complete.js     # Scraping documentation
```

---

## API Endpoints

### Base URL
```
http://localhost:8080/wp-json/threew/v1/fitment/
```

### Endpoints

**1. Get Years**
```bash
GET /years
Response: ["2026", "2025", "2024"]
```

**2. Get Makes**
```bash
GET /makes?year=2024
Response: ["BMW", "Mercedes-Benz", "Porsche"]
```

**3. Get Models**
```bash
GET /models?year=2024&make=BMW
Response: ["M5", "M3", "M4"]
```

**4. Get Trims**
```bash
GET /trims?year=2024&make=BMW&model=M5
Response: ["G90", "G99", "Competition"]
```

---

## Troubleshooting

### Problem: Import fails with "No products found"
**Solution**:
```bash
# Verify JSON file exists and is valid
cat scraped-products-sample.json | jq .

# Check file permissions
ls -la scraped-products-sample.json
```

### Problem: API returns empty arrays
**Solution**:
```bash
# Check if inventory exists in database
docker compose exec wordpress wp --allow-root option get threew_fitment_inventory_real

# If empty, re-run import
docker compose exec wordpress wp --allow-root fitment import --source=scraped-products-sample.json
```

### Problem: Frontend doesn't show dropdowns
**Solution**:
```bash
# Rebuild JavaScript
cd wp-content/themes/3w-2025
npm run build

# Clear browser cache
# Hard refresh: Ctrl+Shift+R (Linux/Windows) or Cmd+Shift+R (Mac)

# Check browser console for JavaScript errors
```

### Problem: "Illegal offset type" error
**Solution**: Already fixed in version 1.0.0. If you see this error:
```bash
# Update fitment-import.php to latest version
# The fix is in lines 573-615 (models array handling)
```

### Problem: Search results on shop site aren't relevant
**Solution**:
```bash
# Test search URL manually
# Visit: https://shop.3wdistributing.com/?s=BMW+M5+G90&post_type=product

# If results are poor, may need to:
# 1. Add more search terms (year, brand)
# 2. Implement custom WooCommerce search filter
# 3. Use product attributes instead of search query
```

---

## Development Workflow

### Making Changes to Frontend
```bash
# 1. Edit source file
nano src/blocks/fitment-selector/view.js

# 2. Rebuild
npm run build

# 3. Test in browser
# Visit: http://localhost:8080

# 4. Clear cache if needed
docker compose exec wordpress wp --allow-root cache flush
```

### Making Changes to Import System
```bash
# 1. Edit PHP file
nano inc/fitment-import.php

# 2. Test with dry run
docker compose exec wordpress wp --allow-root fitment import --limit=5 --dry-run

# 3. Run actual import
docker compose exec wordpress wp --allow-root fitment import --limit=10

# 4. Verify results
curl http://localhost:8080/wp-json/threew/v1/fitment/years | jq .
```

### Adding New Parsing Patterns
```bash
# 1. Add pattern to fitment-import.php
# Location: ThreeW_Fitment_Parser::parse_vehicle_data()

# 2. Add test cases
# 3. Run import on sample data
# 4. Verify parsing accuracy
```

---

## Data Flow Diagram

```
User selects vehicle on www.3wdistributing.com
  ↓
Year dropdown populated from API
  ↓
User selects 2024
  ↓
Make dropdown populated: GET /makes?year=2024
  ↓
User selects BMW
  ↓
Model dropdown populated: GET /models?year=2024&make=BMW
  ↓
User selects M5
  ↓
Trim dropdown populated: GET /trims?year=2024&make=BMW&model=M5
  ↓
User selects G90
  ↓
User clicks "Search Parts"
  ↓
Redirect to: https://shop.3wdistributing.com/?s=BMW+M5+G90&post_type=product
  ↓
Shop shows matching products
```

---

## Testing Checklist

### After Making Changes

- [ ] Run `npm run build` if frontend changed
- [ ] Clear WordPress cache
- [ ] Hard refresh browser (Ctrl+Shift+R)
- [ ] Test year dropdown populates
- [ ] Test make dropdown populates after year selection
- [ ] Test model dropdown populates after make selection
- [ ] Test trim dropdown populates after model selection
- [ ] Test search button enables when required fields complete
- [ ] Test redirect URL is correct
- [ ] Test on mobile viewport
- [ ] Check browser console for errors
- [ ] Verify API responses are correct

### Before Deployment

- [ ] All tests passing
- [ ] No console errors
- [ ] Documentation updated
- [ ] Changelog updated
- [ ] Backup created
- [ ] Deployment plan reviewed

---

## Performance Tips

### Optimize API Response Time
```php
// Already implemented in fitment-api.php:
// - 1-hour object cache
// - Efficient array operations
// - No database queries per request
```

### Optimize Frontend Load Time
```javascript
// Already implemented in view.js:
// - Lazy loading of dropdowns
// - Local storage caching
// - Debounced API calls
```

### Optimize Import Speed
```bash
# Process in batches
wp fitment import --source=products.json --limit=100
# Wait for completion, then repeat for next batch
```

---

## Getting Help

### Check Logs
```bash
# WordPress debug log
docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log

# PHP error log
docker compose logs wordpress | grep -i error

# JavaScript errors
# Open browser DevTools → Console tab
```

### Verify Environment
```bash
# Check Docker status
docker compose ps

# Check WordPress
docker compose exec wordpress wp --allow-root core version

# Check WP-CLI
docker compose exec wordpress wp --allow-root --version

# Check theme
docker compose exec wordpress wp --allow-root theme status 3w-2025
```

### Documentation
- Implementation Guide: Complete technical documentation
- Testing Notes: Test results and validation
- Progress Report: Current status and achievements
- Backlog: Future enhancements and tasks

---

## Version Information

**Current Version**: 1.1.0 (October 31, 2025)

**Components**:
- Import System: 1.0.0
- REST API: 1.0.0
- Frontend Selector: 1.1.0 (search query update)

**Compatibility**:
- WordPress: 6.4+
- WooCommerce: 8.0+
- PHP: 8.0+
- Node.js: 18+ (for building)
