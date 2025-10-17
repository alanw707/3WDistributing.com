# Fitment Selector - Quick Start Guide

## 🚀 Adding to Your Site

### Option 1: Add to Existing Page (Recommended)

1. **Edit Homepage** (or any page)
   - Go to WordPress Admin → Pages → Edit "Home"
   - Click (+) to add block
   - Search for "Fitment Selector"
   - Insert block where desired

2. **Configure Settings** (optional)
   - Headline: "Select Your Vehicle" (default)
   - Subheadline: "Choose year, make, and model..." (default)
   - CTA Label: "Search Parts" (default)

3. **Save & Publish**
   - Block is immediately functional
   - No additional setup required

### Option 2: Use Hero Pattern

The fitment selector is included in the `hero-fitment` pattern:

1. WordPress Admin → Appearance → Patterns
2. Find "Hero with Fitment Selector"
3. Insert into page template or content area
4. Customize hero content as needed

## 📋 How It Works

### User Experience

1. **Year Selection**: User selects vehicle year (2022-2025)
2. **Make Selection**: Available makes populate based on year
3. **Model Selection**: Available models populate based on make
4. **Trim Selection**: Available trims populate (optional)
5. **Search Parts**: Click button → redirects to `/shop?vehicle_year=...`
6. **Persistence**: Vehicle saved in browser, loads on return

### Technical Flow

```
User visits page
    ↓
JavaScript loads fitment data from API
    ↓
User makes selections (Year → Make → Model → Trim)
    ↓
Selection saved to localStorage
    ↓
User clicks "Search Parts"
    ↓
Redirects to shop with fitment URL parameters
    ↓
WooCommerce shows compatible products (requires integration)
```

## 🔧 Testing Your Installation

### Quick Test

1. **Navigate to page with fitment selector**
2. **Open browser console** (F12)
3. **Watch for:**
   - No JavaScript errors
   - API calls to `/wp-json/threew/v1/fitment/years`
   - Success messages in console

4. **Test interaction:**
   - Select "2024" from Year dropdown
   - Verify Make dropdown becomes enabled with options
   - Select "BMW" from Make dropdown
   - Verify Model dropdown populates
   - Select "M4" from Model dropdown
   - Verify Trim dropdown populates
   - Verify "Search Parts" button becomes enabled

5. **Test persistence:**
   - Make a complete selection
   - Refresh the page
   - Verify your selections are still present

### Expected Behavior

**Initial State:**
- Year dropdown: Enabled with options (2022-2025)
- Make dropdown: Disabled, shows "Select"
- Model dropdown: Disabled, shows "Select"
- Trim dropdown: Disabled, shows "Select"
- Submit button: Disabled

**After Year Selection:**
- Make dropdown: Enabled with available makes
- Model/Trim: Still disabled
- Submit button: Still disabled

**After Make Selection:**
- Model dropdown: Enabled with available models
- Trim: Still disabled
- Submit button: Still disabled

**After Model Selection:**
- Trim dropdown: Enabled with available trims
- Submit button: ENABLED (trim is optional)

**After Trim Selection:**
- All selections visible
- Submit button: ENABLED
- Click redirects to `/shop?vehicle_year=...&vehicle_make=...&vehicle_model=...&vehicle_trim=...`

## 🐛 Troubleshooting

### Problem: Dropdowns Stay Disabled

**Cause:** JavaScript not loading or API not responding

**Solution:**
```bash
# Check browser console for errors
# Verify API endpoints:
curl http://localhost:8080/wp-json/threew/v1/fitment/years
# Should return: ["2025","2024","2023","2022"]
```

### Problem: "Failed to load" Error Message

**Cause:** REST API endpoint not accessible

**Solution:**
1. Verify `inc/fitment-api.php` exists
2. Check file permissions: `ls -la wp-content/themes/3w-2025/inc/`
3. Verify `functions.php` includes: `require_once get_theme_file_path('inc/fitment-api.php');`
4. Check WordPress REST API is working: `/wp-json/` should load

### Problem: Selections Don't Persist

**Cause:** localStorage not working or being cleared

**Solution:**
1. Check browser console for localStorage errors
2. Verify browser allows localStorage (not in private/incognito mode)
3. Check for browser extensions blocking storage

### Problem: No Vehicles Available

**Cause:** Sample data not loaded

**Solution:**
The default inventory includes:
- Years: 2022-2025
- Makes: Audi, BMW, Mercedes, Porsche, Lexus, Nissan, Chevrolet, Dodge
- 50+ models with trims

If empty, check `get_default_inventory()` in `inc/fitment-api.php`

## 📊 Current Sample Data

### Available Inventory (out of the box)

**2025:**
- Audi: RS7, Q8
- BMW: M4, X5
- Mercedes: AMG GT, C-Class

**2024:**
- Audi: RS6, RS7, Q8
- BMW: M3, M4, M5, X5
- Mercedes: C63, E63, G63, AMG GT
- Porsche: 911, Taycan, Macan

**2023:**
- Audi: RS6, RS7, Q8, R8
- BMW: M2, M3, M4, M5, X5, X6
- Lexus: IS500, LC500
- Mercedes: C63, E63, G63, AMG GT
- Nissan: GT-R, Z
- Porsche: 911, Taycan, Cayman, Macan

**2022:**
- Audi: RS6, RS7, Q8, R8
- BMW: M2, M3, M4, M5, M8
- Chevrolet: Corvette, Camaro
- Dodge: Challenger, Charger
- Mercedes: C63, E63, AMG GT
- Porsche: 911, Taycan, Cayman

**Total:** 100+ vehicle configurations

## 🔄 Replacing Sample Data

### Method 1: Use Filter Hook (Recommended)

Add to `functions.php` or custom plugin:

```php
add_filter('threew_fitment_inventory', function($inventory) {
    // Clear sample data
    $inventory = [];

    // Add your real inventory
    $inventory['2026'] = [
        'Tesla' => [
            'Model S' => ['Plaid', 'Long Range'],
            'Model 3' => ['Performance', 'Long Range']
        ]
    ];

    return $inventory;
});
```

### Method 2: Database Integration

Replace `get_default_inventory()` in `inc/fitment-api.php`:

```php
function get_fitment_inventory() {
    global $wpdb;

    $results = $wpdb->get_results("
        SELECT year, make, model, trim
        FROM {$wpdb->prefix}vehicle_fitments
        ORDER BY year DESC, make ASC, model ASC
    ");

    // Transform to nested array structure
    // ... implementation here

    return $inventory;
}
```

### Method 3: External API

```php
function get_fitment_inventory() {
    $cache_key = 'external_fitment_data';
    $data = get_transient($cache_key);

    if (false === $data) {
        $response = wp_remote_get('https://api.yourprovider.com/fitment');
        $data = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($cache_key, $data, HOUR_IN_SECONDS);
    }

    return $data;
}
```

## 🎨 Customizing Appearance

### Change Colors

Edit `src/blocks/fitment-selector/style.css`:

```css
/* Glassmorphism background */
.threew-fitment-block {
    background: rgba(15, 25, 40, 0.7); /* Dark blue */
}

/* Submit button gradient */
.threew-fitment-block__submit {
    background: linear-gradient(135deg,
        var(--wp--preset--color--secondary),
        var(--wp--preset--color--primary)
    );
}
```

### Change Button Text

In WordPress editor:
1. Select fitment selector block
2. Sidebar → Block Settings → CTA Label
3. Change from "Search Parts" to your text

Or programmatically in `block.json`:
```json
{
  "ctaLabel": {
    "type": "string",
    "default": "Find My Parts"
  }
}
```

### Add Custom Fields

Extend the selector in `view.js`:

```javascript
// Add engine size field
const engineSelect = document.createElement('select');
// ... populate options
this.container.querySelector('.threew-fitment-block__form').appendChild(engineField);
```

## 📈 Analytics Integration

Track fitment selections:

```javascript
// Add to your theme's JavaScript or GTM
window.addEventListener('threew-vehicle-selected', function(event) {
    const vehicle = event.detail;

    // Google Analytics 4
    gtag('event', 'vehicle_selection', {
        vehicle_year: vehicle.year,
        vehicle_make: vehicle.make,
        vehicle_model: vehicle.model,
        vehicle_trim: vehicle.trim
    });

    // Facebook Pixel
    fbq('trackCustom', 'VehicleSelection', {
        year: vehicle.year,
        make: vehicle.make,
        model: vehicle.model
    });
});
```

## 🔗 WooCommerce Integration (Coming Soon)

The fitment selector redirects to `/shop?vehicle_year=...&vehicle_make=...&vehicle_model=...`

**To complete integration**, add product filtering:

```php
// functions.php or custom plugin
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_shop()) {
        if (isset($_GET['vehicle_year'])) {
            $meta_query = [
                'relation' => 'AND',
                [
                    'key' => '_compatible_years',
                    'value' => sanitize_text_field($_GET['vehicle_year']),
                    'compare' => 'LIKE'
                ]
            ];

            if (isset($_GET['vehicle_make'])) {
                $meta_query[] = [
                    'key' => '_compatible_makes',
                    'value' => sanitize_text_field($_GET['vehicle_make']),
                    'compare' => 'LIKE'
                ];
            }

            $query->set('meta_query', $meta_query);
        }
    }
});
```

## 📞 Support

**Documentation:**
- Full implementation: [`phase-3-fitment-selector-completion.md`](phase-3-fitment-selector-completion.md)
- API reference: [`inc/fitment-api.php`](../wp-content/themes/3w-2025/inc/fitment-api.php)
- Frontend code: [`src/blocks/fitment-selector/view.js`](../wp-content/themes/3w-2025/src/blocks/fitment-selector/view.js)

**Common Issues:**
1. API returns 404 → Check REST API is enabled
2. Dropdowns stay disabled → Check browser console for errors
3. Data not loading → Verify file permissions on `inc/` directory
4. Styling broken → Run `npm run build` in theme directory

**Testing Checklist:**
- [ ] Block appears in WordPress editor
- [ ] Dropdowns populate with data
- [ ] Cascading selection works (Year → Make → Model → Trim)
- [ ] Submit button enables after Model selection
- [ ] Clicking submit redirects to shop with URL parameters
- [ ] Selections persist after page refresh
- [ ] Mobile responsive (full-width button <700px)
- [ ] Keyboard navigation works (Tab, Enter, Arrow keys)
- [ ] Screen reader announces dropdown changes

---

**Last Updated:** October 16, 2025
**Version:** 1.0.0
**Status:** Production Ready ✅
