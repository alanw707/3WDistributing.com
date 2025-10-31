# Vehicle Fitment Discovery Report
**Date**: 2025-10-30
**Site**: shop.3wdistributing.com
**Analysis Method**: Frontend scraping + WooCommerce structure analysis

## Executive Summary

**Finding**: No dedicated vehicle fitment attributes exist. Vehicle data is embedded in WooCommerce **categories**, **tags**, and **product titles**.

**Recommendation**: Create structured vehicle attributes (`pa_vehicle_make`, `pa_vehicle_model`, `pa_vehicle_year`, `pa_vehicle_trim`) and populate via import script.

---

## Current Data Structure

### 1. WooCommerce Categories
Products use hierarchical categories mixing:
- **Brands**: Brabus, Mansory, Akrapovic, Eventuri, Vorsteiner
- **Vehicle Makes**: BMW, Mercedes, Ferrari, Tesla, Porsche
- **Vehicle Models**: M5, G-Wagon, Model 3, F12 Berlinetta
- **Chassis Codes**: W463, W463A, W465, G90, G99
- **Part Types**: Exterior, Interior, Power & Sound

#### Example Category Hierarchies:
```
Akrapovic → BMW → M5 (G90/G99)
Brabus → G-Wagon → GWagon W465 → GWagon W465 Exterior
Eventuri → BMW
Vorsteiner → Model 3
```

### 2. Product Tags
Tags include vehicle-specific identifiers:
- `BMW M5`, `BMW G90`, `BMW G99`
- `Brabus W465`
- Year information sometimes included

### 3. Product Titles
Titles contain fitment data:
- **Year**: "2024+ BMW M5", "2025 Mercedes-AMG G63"
- **Model**: "BMW M5 (G90,G99)", "Mercedes W463A G63 AMG"
- **Chassis**: "(G90,G99)", "W465", "W463A"

#### Example Titles:
```
"Akrapovic 2024+ BMW M5 (G90,G99) Evolution Catback Exhaust"
"Eventuri Mercedes W463A G63 AMG Black Carbon Intake System"
"Brabus Widestar Conversion Kit for Mercedes Benz G-Class W463"
"Vorsteiner Volta Aero Carbon Fiber Decklid Spoiler Tesla Model 3"
```

### 4. Data Layer (JavaScript)
Product data exposed in frontend includes:
```javascript
{
  "id": "123369",
  "name": "Akrapovic 2024+ BMW M5 (G90,G99) Evolution Catback Exhaust",
  "category": ["Akrapovic", "BMW", "M5 (G90/G99)"]
}
```

---

## Vehicle Coverage Analysis

### Makes Identified:
- BMW
- Mercedes-Benz / Mercedes
- Ferrari
- Tesla
- Porsche
- Audi (mentioned in homepage meta)

### Models Identified:
**BMW**:
- M5 (G90/G99 chassis)
- Z8 (E52)
- M4
- G9X M5

**Mercedes**:
- G-Wagon / G-Class (W463, W463A, W465 generations)
- G63 / G65 AMG
- AMG models

**Ferrari**:
- F12 Berlinetta

**Tesla**:
- Model 3

### Year Ranges:
- **Explicit**: "2024+", "2025", "1999-2018"
- **Implied**: Via chassis codes (W463: 1999-2018, W463A/W465: 2018+)

### Chassis/Trim Codes:
- **BMW**: G90, G99, G9X, E52
- **Mercedes**: W463, W463A, W465
- **Years**: Sometimes in category names like "W463 (1999-2018)"

---

## Gap Analysis

### ❌ **Missing**: Structured Vehicle Attributes
- No `pa_vehicle_make` attribute
- No `pa_vehicle_model` attribute
- No `pa_vehicle_year` attribute
- No `pa_vehicle_trim` attribute

### ✅ **Exists**: Unstructured Vehicle Data
- Categories contain vehicle info but mixed with brands/part types
- Tags have some vehicle identifiers
- Product titles reliably contain vehicle fitment
- Data extractable but needs parsing

### ⚠️ **Challenges**:
1. **Inconsistent Formatting**:
   - "BMW M5 (G90,G99)" vs "M5 (G90/G99)"
   - "W463A" vs "W463-A"
   - "2024+" vs "2024-2025"

2. **Mixed Hierarchies**:
   - Categories mix brand, make, model, and part type
   - No standard structure across brands

3. **Year Ambiguity**:
   - "2024+" doesn't specify end year
   - Chassis codes imply years but not explicitly stated
   - Legacy notation: "W463 (1999-2018)"

---

## Extraction Strategy

### Data Sources (Priority Order):
1. **Product Title** (most reliable, always has vehicle info)
2. **Product Categories** (hierarchical but mixed)
3. **Product Tags** (supplementary)
4. **Product Description** (fallback, less structured)

### Parsing Patterns:

#### Year Extraction:
```regex
\b(20\d{2})[+\-]?\b          # 2024, 2024+, 2024-
\b(20\d{2})-(20\d{2})\b      # 2024-2025
\((\d{4})-(\d{4})\)          # (1999-2018)
```

#### Make Extraction:
```
Known makes: BMW, Mercedes, Ferrari, Tesla, Porsche, Audi
Match from categories or title
```

#### Model Extraction:
```
BMW: M5, M4, Z8, etc.
Mercedes: G-Wagon, G-Class, G63, G65
Ferrari: F12 Berlinetta
Tesla: Model 3
Extract from category hierarchy or title
```

#### Chassis/Trim Extraction:
```regex
\(([A-Z]\d+[A-Z]?(?:,\s?[A-Z]\d+[A-Z]?)*)\)  # (G90,G99) or (G90/G99)
\b(W\d{3}[A-Z]?)\b                            # W463, W463A, W465
\b(E\d+)\b                                     # E52
```

---

## Implementation Recommendation

### ✅ **Scenario C: Create New Vehicle Attributes**

**Rationale**: No existing vehicle attributes to leverage. Best to create clean, structured taxonomy.

### Proposed Attributes:
```php
pa_vehicle_make   → BMW, Mercedes-Benz, Ferrari, Tesla, Porsche
pa_vehicle_model  → M5, G-Wagon, Model 3, F12 Berlinetta
pa_vehicle_year   → 2024, 2025, 2024-2025, 1999-2018
pa_vehicle_trim   → G90, G99, W463, W463A, AMG, G63, Z8
```

### Storage Approach:
1. **Primary**: WooCommerce Product Attributes (pa_vehicle_*)
2. **Cache**: wp_options for fitment selector performance
3. **Relationship**: Product → Multiple vehicle terms (one product can fit multiple vehicles)

### Import Script Tasks:
1. Fetch all products from WooCommerce API
2. Parse title, categories, tags for vehicle data
3. Extract Make, Model, Year, Trim using regex patterns
4. Create/assign product attributes
5. Store aggregated fitment data in wp_options for selector
6. Generate validation report (unparseable products)

---

## Sample Data Extraction

### Product: "Akrapovic 2024+ BMW M5 (G90,G99) Evolution Catback Exhaust"

**Extracted**:
- Make: BMW
- Model: M5
- Year: 2024+ (treat as 2024-2025+)
- Trim: G90, G99 (two trim options)

**Attributes Created**:
```php
pa_vehicle_make   → BMW
pa_vehicle_model  → M5
pa_vehicle_year   → 2024, 2025
pa_vehicle_trim   → G90, G99
```

### Product: "Brabus Widestar for Mercedes Benz G-Class W463 for G63/G65"

**Extracted**:
- Make: Mercedes-Benz
- Model: G-Class (G-Wagon)
- Year: 1999-2018 (from W463 lookup table)
- Trim: W463, G63, G65

**Attributes Created**:
```php
pa_vehicle_make   → Mercedes-Benz
pa_vehicle_model  → G-Class
pa_vehicle_year   → 1999-2018 (expanded to individual years)
pa_vehicle_trim   → W463, G63, G65
```

---

## Next Steps

### Phase 2A: Create Import Script
1. **File**: `inc/fitment-import.php`
2. **WP-CLI Command**: `wp fitment import`
3. **Functions**:
   - Fetch products from WooCommerce API (with authentication)
   - Parse vehicle data from titles/categories/tags
   - Create/populate WooCommerce attributes
   - Store aggregated data in wp_options
   - Generate validation report

### Phase 2B: Modify API Endpoint
1. **File**: `inc/fitment-api.php`
2. **Update**: `get_fitment_inventory()` function
3. **Logic**: Query products by vehicle attributes, return structured JSON

### Phase 2C: Testing
1. Run import on 10 sample products
2. Verify attributes created correctly
3. Test fitment selector with real data
4. Validate cascading dropdowns

---

## Chassis Code Lookup Table

```php
$chassis_years = [
    // Mercedes G-Wagon
    'W463'  => ['start' => 1990, 'end' => 2018],
    'W463A' => ['start' => 2018, 'end' => 2024],
    'W465'  => ['start' => 2024, 'end' => null], // current

    // BMW M5
    'G90'   => ['start' => 2024, 'end' => null], // current
    'G99'   => ['start' => 2024, 'end' => null], // current (wagon)
    'G9X'   => ['start' => 2024, 'end' => null], // platform code
    'E52'   => ['start' => 2000, 'end' => 2003], // Z8
];
```

---

## Estimated Product Coverage

Based on frontend sampling:
- **Total Products**: 500+ (estimated from site navigation)
- **With Vehicle Fitment**: ~95% (aftermarket parts nature)
- **Parseable Titles**: ~90% (consistent format)
- **Manual Review Needed**: ~10% (edge cases, typos, non-standard formats)

---

## Conclusion

**Current State**: Vehicle fitment data exists but unstructured
**Required Work**: Create attributes + import script to parse and structure
**Complexity**: Moderate (parsing logic needed, but data is present)
**KISS Compliance**: ✅ Use native WooCommerce attributes, no custom tables

**Ready to proceed with Phase 2: Implementation**
