# Vehicle Fitment System - Backlog & Roadmap

**Last Updated**: October 31, 2025
**Current Phase**: Phase 3 Complete - Ready for Scale

## Priority Levels
- 🔴 **P0**: Critical - Blocks production use
- 🟡 **P1**: High - Important for production quality
- 🟢 **P2**: Medium - Enhances user experience
- 🔵 **P3**: Low - Nice to have

---

## Immediate Backlog (Next Sprint)

### 🟡 P1: Scale Data Collection
**Task**: Import all ~299 products from Akrapovic category (30 pages)

**Current State**: Only 20 products imported (pages 1-2)

**Implementation**:
```bash
# Option 1: Manual Playwright scraping with MCP
# Loop through all 30 pages, extract products, save to JSON

# Option 2: Automated script
node scripts/scrape-shop-complete.js --pages 30 --output scraped-products-all.json

# Option 3: Import in batches
wp fitment import --source=scraped-products-page1-10.json
wp fitment import --source=scraped-products-page11-20.json
wp fitment import --source=scraped-products-page21-30.json
```

**Acceptance Criteria**:
- [ ] All 299 Akrapovic products scraped
- [ ] JSON file(s) validated
- [ ] Import runs successfully (100% success rate)
- [ ] API returns complete inventory
- [ ] Frontend selector shows all available options

**Estimated Effort**: 2-3 hours
**Risk**: Low (process proven with sample)

---

### 🟡 P1: Validate Search Results on Shop
**Task**: Verify search URLs produce relevant product results

**Current State**: URLs generated but results quality untested

**Testing Scenarios**:
1. Search: `BMW M5 G90` → Should show relevant BMW M5 G90 products
2. Search: `Mercedes-Benz G63 W465` → Should show G63 products
3. Search: `Porsche 911 GT3 992` → Should show 911 GT3 products
4. Edge cases: Special characters, long queries

**Acceptance Criteria**:
- [ ] 90%+ search results are relevant
- [ ] No broken/404 searches
- [ ] Results load within acceptable time (<3s)
- [ ] Mobile experience verified

**Estimated Effort**: 1-2 hours
**Risk**: Medium (may need query refinement)

---

### 🟢 P2: Expand to Additional Vendor Categories
**Task**: Import products from other vendor categories beyond Akrapovic

**Categories to Consider**:
- Brabus (flagship vendor)
- Mansory (high-value products)
- ABT Sportsline
- AC Schnitzer
- All vendors (comprehensive coverage)

**Implementation Strategy**:
```bash
# Scrape each category separately
node scripts/scrape-shop-complete.js --category brabus --output brabus-products.json
node scripts/scrape-shop-complete.js --category mansory --output mansory-products.json

# Import all categories
wp fitment import --source=brabus-products.json
wp fitment import --source=mansory-products.json
# ... etc
```

**Acceptance Criteria**:
- [ ] Scraping works for multiple categories
- [ ] Import handles diverse product naming patterns
- [ ] No duplicate entries in inventory
- [ ] Frontend shows expanded vehicle coverage

**Estimated Effort**: 4-6 hours (varies by category count)
**Risk**: Medium (different naming patterns per vendor)

---

## Short-Term Enhancements (1-2 Weeks)

### 🟢 P2: Parsing Pattern Improvements
**Task**: Enhance regex patterns for better accuracy

**Known Issues**:
- Some chassis codes not recognized (e.g., W213, C197)
- Year ranges like "2020-2023" only capture first year
- Trim variations not fully captured

**Improvements**:
```php
// Add more chassis code mappings
'W213' => 'W213',
'C197' => 'C197',
'R231' => 'R231',

// Handle year ranges
'2020-2023 BMW M3' → years: [2020, 2021, 2022, 2023]

// Better trim extraction
'Competition' → recognized as trim
'Performance Pack' → recognized as trim
```

**Acceptance Criteria**:
- [ ] Parsing accuracy increases to 95%+
- [ ] Year ranges handled correctly
- [ ] More chassis codes recognized
- [ ] Edge cases documented

**Estimated Effort**: 3-4 hours
**Risk**: Low (iterative improvements)

---

### 🟢 P2: Import Logging & Reporting
**Task**: Enhanced logging for import operations

**Features**:
- Detailed error reporting per product
- Skip reasons logged (missing data, parse failures)
- Summary statistics (success rate, common failures)
- Export log to file for review

**Implementation**:
```bash
wp fitment import --source=products.json --log=import-log.txt --verbose

# Output:
# ✅ Product 1: BMW M5 G90 - Success
# ⚠️  Product 2: Generic Product - Skipped: No vehicle data
# ❌ Product 3: Invalid Title - Error: Parse failed
#
# Summary:
# Total: 100
# Success: 85 (85%)
# Skipped: 10 (10%)
# Errors: 5 (5%)
```

**Acceptance Criteria**:
- [ ] All import events logged
- [ ] Errors include context (product name, reason)
- [ ] Summary stats automatically generated
- [ ] Logs saved to file

**Estimated Effort**: 2-3 hours
**Risk**: Low

---

### 🟢 P2: Frontend UX Improvements
**Task**: Polish user experience

**Enhancements**:
1. **Loading states**: Skeleton loaders for dropdowns
2. **Error messages**: User-friendly error notifications
3. **Empty states**: "No vehicles found" messaging
4. **Keyboard navigation**: Full keyboard support
5. **Mobile optimization**: Better touch targets
6. **Animations**: Smooth transitions between states

**Acceptance Criteria**:
- [ ] All states have visual feedback
- [ ] Keyboard navigation works completely
- [ ] Mobile experience improved
- [ ] Animations enhance (not distract)

**Estimated Effort**: 4-6 hours
**Risk**: Low

---

## Mid-Term Goals (1-2 Months)

### 🟡 P1: Automated Scraping Pipeline
**Task**: Scheduled data collection and import

**Architecture**:
```
Cron Job (daily)
  ↓
Scrape shop.3wdistributing.com
  ↓
Save to JSON with timestamp
  ↓
Run import automatically
  ↓
Notify on success/failure
```

**Implementation Options**:
1. **WordPress Cron**: Use WP cron system
2. **System Cron**: Linux cron job
3. **GitHub Actions**: Scheduled workflow
4. **External Service**: Zapier/n8n integration

**Acceptance Criteria**:
- [ ] Runs automatically on schedule
- [ ] Handles failures gracefully
- [ ] Sends notifications (email/Slack)
- [ ] Maintains history of imports

**Estimated Effort**: 8-10 hours
**Risk**: Medium (requires server access)

---

### 🟢 P2: Incremental Updates
**Task**: Update inventory without full rebuild

**Current Issue**: Every import rebuilds entire inventory

**Proposed Solution**:
```bash
# Detect changes since last import
wp fitment import --source=products.json --mode=incremental

# Only process:
# - New products
# - Updated products
# - Deleted products (mark as unavailable)
```

**Database Schema**:
```php
// Track last import timestamp per product
product_id => last_imported_at

// Compare with new data
if (product_exists && product_updated) {
  update_inventory();
} elseif (!product_exists) {
  add_to_inventory();
}
```

**Acceptance Criteria**:
- [ ] Imports complete faster (only process changes)
- [ ] Historical data preserved
- [ ] Change tracking works correctly
- [ ] No duplicate entries

**Estimated Effort**: 10-12 hours
**Risk**: Medium (requires schema changes)

---

### 🟢 P2: Product Availability Tracking
**Task**: Track which products are in stock vs out of stock

**Feature**: Filter vehicles by product availability

**Implementation**:
```json
{
  "2024": {
    "BMW": {
      "M5": {
        "G90": {
          "available": true,
          "product_count": 12,
          "products": ["akrapovic-exhaust", "carbon-spoiler", ...]
        }
      }
    }
  }
}
```

**Frontend Update**:
- Show "(3 products)" next to each option
- Disable/gray out unavailable options
- "Out of stock" indicator

**Acceptance Criteria**:
- [ ] Availability tracked per trim
- [ ] Frontend shows availability
- [ ] Updates when inventory changes
- [ ] User-friendly messaging

**Estimated Effort**: 6-8 hours
**Risk**: Medium (requires API changes)

---

## Long-Term Vision (3-6 Months)

### 🔵 P3: Analytics & Insights
**Goal**: Understand user behavior and improve selector

**Metrics to Track**:
- Most searched vehicles
- Completion rate (Year → Make → Model → Trim → Search)
- Drop-off points in funnel
- Search result click-through rate
- Time to complete selection

**Implementation**:
- Google Analytics events
- Custom dashboard
- A/B testing framework

**Estimated Effort**: 12-16 hours
**Risk**: Low

---

### 🔵 P3: Advanced Filtering
**Goal**: Multi-criteria search beyond just vehicle fitment

**Features**:
- Price range slider
- Category filters (Exhaust, Wheels, etc.)
- Brand filters
- Combine fitment + filters

**Example**:
```
Vehicle: 2024 BMW M5 G90
Category: Exhaust
Price: $1000 - $5000
Brand: Akrapovic, Capristo
```

**Estimated Effort**: 20-24 hours
**Risk**: High (complex UI/UX)

---

### 🔵 P3: Multi-Language Support
**Goal**: Support Spanish, French, German customers

**Requirements**:
- Translate dropdown labels
- Translate error messages
- Support localized vehicle names
- RTL layout for Arabic (if needed)

**Estimated Effort**: 10-12 hours
**Risk**: Medium (translation management)

---

### 🔵 P3: Garage Feature
**Goal**: Let users save multiple vehicles

**Features**:
- Save up to 5 vehicles
- Quick-switch between saved vehicles
- "My Garage" page
- Share garage with others

**Estimated Effort**: 24-30 hours
**Risk**: High (requires user accounts)

---

## Technical Debt

### Code Refactoring
- [ ] Extract parsing logic to separate class
- [ ] Add unit tests for parsing patterns
- [ ] Improve error handling in API endpoints
- [ ] Document all REST API endpoints (OpenAPI/Swagger)

### Performance Optimization
- [ ] Cache API responses (5-15 minutes)
- [ ] Lazy-load dropdown options
- [ ] Minify compiled JavaScript
- [ ] Image optimization for vendor logos

### Security Hardening
- [ ] Rate limiting on API endpoints
- [ ] Input sanitization for all parameters
- [ ] CSRF protection for forms
- [ ] Content Security Policy headers

---

## Maintenance Schedule

### Daily
- [ ] Monitor error logs
- [ ] Check API response times

### Weekly
- [ ] Review import success rates
- [ ] Validate data accuracy (spot checks)

### Monthly
- [ ] Update parsing patterns if needed
- [ ] Review and prune old logs
- [ ] Performance audit

### Quarterly
- [ ] Major version updates
- [ ] Architecture review
- [ ] User feedback integration

---

## Success Metrics

### Technical KPIs
- Import success rate: >95%
- API response time: <100ms (p95)
- Frontend load time: <2s
- Parsing accuracy: >95%

### User Experience KPIs
- Selection completion rate: >80%
- Time to complete: <30 seconds
- Error rate: <5%
- User satisfaction: >4/5 stars

### Business Impact
- Increased shop traffic from main site
- Better product discovery
- Reduced support inquiries about fitment
- Higher conversion rate on targeted searches

---

## Notes & Considerations

### Shop Site Integration
- Verify WooCommerce search works as expected
- May need to implement custom search logic
- Consider adding fitment filters to shop sidebar

### Data Quality
- Regular audits of parsing accuracy
- Feedback mechanism for incorrect data
- Manual override capability for edge cases

### Scalability
- Current approach handles <1000 products easily
- For 10,000+ products, consider:
  - Database table instead of wp_options
  - Elasticsearch for search
  - Caching layer (Redis)

### User Feedback
- Add "Report Issue" button
- Collect search queries that return no results
- Monitor drop-off points in funnel
