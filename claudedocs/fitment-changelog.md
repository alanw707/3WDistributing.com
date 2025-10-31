# Vehicle Fitment System - Changelog

All notable changes to the vehicle fitment system.

---

## [1.1.0] - 2025-10-31

### Changed
- **Shop URL Format**: Switched from filter parameters to search query format
  - **Before**: `?vehicle_year=2024&vehicle_make=BMW&vehicle_model=M5&vehicle_trim=G90&post_type=product`
  - **After**: `?s=BMW+M5+G90&post_type=product`
  - **Rationale**: Better compatibility with WooCommerce native search functionality
  - **File Modified**: `src/blocks/fitment-selector/view.js` (lines 605-639)

### Technical Details
```javascript
// Previous implementation
const params = new URLSearchParams({
    vehicle_year: year,
    vehicle_make: make,
    vehicle_model: model,
});
if (trim) {
    params.append('vehicle_trim', trim);
}

// New implementation
const searchParts = [make, model];
if (trim) {
    searchParts.push(trim);
}
const searchQuery = searchParts.join(' ');
targetUrl.searchParams.set('s', searchQuery);
```

---

## [1.0.0] - 2025-10-30

### Added - Phase 3: Testing & Data Collection
- **Playwright Scraping**: Automated product extraction from shop.3wdistributing.com
  - Successfully extracted 20 products from Akrapovic category (pages 1-2)
  - Created `scraped-products-sample.json` with complete product data
  - Documented extraction pattern in `scripts/scrape-shop-complete.js`

- **JSON Import Support**: New `--source=<file>` parameter
  - Added `fetch_from_json()` method to `ThreeW_Remote_Product_Fetcher`
  - Modified `fetch_products()` to accept optional source parameter
  - Updated CLI command documentation with JSON import examples

### Fixed
- **Critical Bug**: Array handling in inventory builder
  - **Issue**: Fatal error "Illegal offset type" when products have multiple models
  - **Cause**: Products like "BMW M3/M4 G80/G82" parse to `["M3 (G80)", "M4 (G82)"]`
  - **Solution**: Added loop to iterate through models array before processing years
  - **File**: `inc/fitment-import.php` (lines 573-615)
  - **Impact**: Enables proper handling of multi-model products

### Verified
- **API Endpoints**: All 4 REST endpoints tested and working
  - `/wp-json/threew/v1/fitment/years` → Returns `[2026, 2025, 2024]`
  - `/wp-json/threew/v1/fitment/makes?year=2024` → Returns `["BMW"]`
  - `/wp-json/threew/v1/fitment/models?year=2024&make=BMW` → Returns `["M5"]`
  - `/wp-json/threew/v1/fitment/trims?year=2024&make=BMW&model=M5` → Returns `["G90","G99"]`

- **Frontend Selector**: Complete cascade functionality verified
  - Year selection triggers make API call and populates dropdown
  - Make selection triggers model API call and populates dropdown
  - Model selection triggers trim API call and populates dropdown
  - Search button enables when required fields complete
  - Form submission generates correct shop URL

- **Import Success**: 100% success rate on test data
  - Processed 10 products with 0 errors
  - Inventory properly stored in wp_options table
  - Data structure validated: Year → Make → Model → [Trims]

### Testing Results
```bash
# Dry Run Test
wp fitment import --source=scraped-products-sample.json --limit=5 --dry-run
✅ Success: 5 products processed, 5 success, 0 skipped, 0 errors

# Actual Import Test
wp fitment import --source=scraped-products-sample.json --limit=10
✅ Success: 10 products processed, 10 success, 0 skipped, 0 errors
```

### Documentation
- Created `fitment-testing-notes.md` - Test results and findings
- Created `fitment-discovery-report.md` - Initial research and planning
- Updated `fitment-implementation-guide.md` - Added JSON import usage

---

## [0.2.0] - 2025-10-29

### Added - Phase 2: Frontend Integration
- **Fitment Selector Block**: React-based Gutenberg block
  - File: `src/blocks/fitment-selector/`
  - Interactive cascading dropdowns (Year → Make → Model → Trim)
  - Real-time API integration with loading states
  - localStorage persistence for user selections
  - Progress indicator with 4-step navigation
  - Keyboard accessibility (ARIA labels, focus management)
  - Error handling with user-friendly messages

- **Shop Integration**: Form submission handler
  - Constructs WooCommerce filter URL
  - Redirects to shop.3wdistributing.com with parameters
  - Preserves user selections across sessions

- **Build System**: WordPress scripts integration
  - Source: `src/blocks/fitment-selector/view.js`
  - Output: `build/view.js` (minified)
  - Dependencies: React, WordPress packages

### Technical Implementation
```javascript
// Cascading dropdown pattern
handleYearChange() → populateMakes()
handleMakeChange() → populateModels()
handleModelChange() → populateTrims()
handleSubmit() → Redirect to shop with filters
```

---

## [0.1.0] - 2025-10-28

### Added - Phase 1: Core Import System
- **Import Command**: WP-CLI command structure
  - Command: `wp fitment import [--limit=<number>] [--dry-run]`
  - Parameters: `--limit` for testing, `--dry-run` for validation
  - Output: Progress tracking, success/skip/error counts

- **Product Fetching**: Remote product scraping (placeholder)
  - Class: `ThreeW_Remote_Product_Fetcher`
  - Method: `fetch_products()` (to be implemented with real scraping)
  - Source: shop.3wdistributing.com product pages

- **Data Parsing**: Vehicle information extraction
  - Class: `ThreeW_Fitment_Parser`
  - Regex patterns for Make, Model, Year, Trim
  - Chassis code mapping (G90, G99, W463, W465, etc.)
  - Handles multiple formats and edge cases

- **Inventory Building**: Hierarchical data structure
  - Class: `ThreeW_Fitment_Inventory`
  - Structure: `Year → Make → Model → [Trims]`
  - Storage: WordPress option `threew_fitment_inventory_real`
  - Aggregates data from all processed products

- **REST API**: 4 endpoints for frontend consumption
  - File: `inc/fitment-api.php`
  - Namespace: `/wp-json/threew/v1/fitment/*`
  - Endpoints:
    1. `/years` - Get all available years
    2. `/makes?year=<year>` - Get makes for year
    3. `/models?year=<year>&make=<make>` - Get models
    4. `/trims?year=<year>&make=<make>&model=<model>` - Get trims

### File Structure
```
inc/
  fitment-import.php    (797 lines) - Core import logic
  fitment-api.php       - REST API endpoints

functions.php           - Includes fitment-import.php
```

### Parsing Examples
```php
// Input: "Akrapovic 2024+ BMW M5 (G90) Carbon Fiber Rear Trunk Lip"
// Output:
[
  'make' => 'BMW',
  'model' => 'M5',
  'year' => ['2024'],
  'trim' => ['G90']
]

// Input: "Akrapovic 2025 Mercedes-AMG G63 (W465) Evolution Line"
// Output:
[
  'make' => 'Mercedes-Benz',
  'model' => 'G63',
  'year' => ['2025'],
  'trim' => ['W465', 'G63', 'AMG']
]
```

---

## Known Issues

### Parsing Limitations
- Year ranges like "2020-2023" only capture first year
- Some chassis codes not in mapping table
- Complex trim variations may not parse correctly
- Special characters in model names need handling

### Performance Considerations
- Large imports (>1000 products) untested
- No incremental update capability (full rebuild each time)
- wp_options may not scale well for massive inventories

### Future Improvements Needed
- Automated scraping pipeline
- Better error recovery in frontend
- Caching layer for API responses
- Admin UI for managing inventory
- Backup and restore functionality

---

## Version Numbering

Format: `MAJOR.MINOR.PATCH`

- **MAJOR**: Breaking changes, major feature additions
- **MINOR**: New features, non-breaking changes
- **PATCH**: Bug fixes, minor improvements

**Current Version**: 1.1.0
- Major: 1 (Core system complete)
- Minor: 1 (Search query update)
- Patch: 0 (Initial minor release)
