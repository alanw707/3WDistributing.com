# SEO Quick Reference Guide
## 3W Distributing - Developer Cheat Sheet

---

## 🚀 Quick Start (5 Minutes)

### 1. After Installation
```bash
# Go to WordPress Admin
# Settings → Permalinks → Click "Save Changes"
```

### 2. Update Organization Details
```bash
# Edit: wp-content/themes/3w-2025/inc/seo-class.php
# Lines 33-51: Update phone, address, social links
```

### 3. Test Everything
```bash
# Visit these URLs:
https://www.3wdistributing.com/sitemap.xml
https://www.3wdistributing.com/robots.txt

# View page source on homepage:
# Look for: <!-- SEO Meta Tags -->
```

---

## 📋 Files Modified/Created

```
✅ wp-content/themes/3w-2025/inc/seo-class.php     # New - Main SEO class
✅ wp-content/themes/3w-2025/functions.php         # Modified - Include SEO
✅ robots.txt                                       # New - Crawler directives
✅ claudedocs/SEO_IMPLEMENTATION.md                # New - Full docs
✅ claudedocs/SEO_QUICK_REFERENCE.md               # New - This file
```

---

## 🎯 What's Implemented

| Feature | Status | Where |
|---------|--------|-------|
| Meta Descriptions | ✅ | Auto-generated from content |
| Open Graph Tags | ✅ | Facebook/LinkedIn sharing |
| Twitter Cards | ✅ | X/Twitter sharing |
| Schema: Organization | ✅ | Homepage only |
| Schema: Article | ✅ | Blog posts |
| Schema: Product | ✅ | WooCommerce products |
| Schema: Breadcrumb | ✅ | All pages except homepage |
| XML Sitemap | ✅ | `/sitemap.xml` |
| Robots.txt | ✅ | `/robots.txt` |
| Canonical URLs | ✅ | All pages |
| Image Alt Text | ✅ | Auto-generated if missing |

---

## 🔧 Common Tasks

### Add Social Media Links

**File:** `inc/seo-class.php` (Line ~47)

```php
'sameAs' => [
    'https://www.facebook.com/3wdistributing',
    'https://twitter.com/3wdistributing',
    'https://www.linkedin.com/company/3wdistributing',
    'https://www.instagram.com/3wdistributing'
]
```

### Add Twitter Handle

**File:** `inc/seo-class.php` (Line ~155)

```php
// Uncomment these lines:
echo '<meta name="twitter:site" content="@3WDistributing">' . "\n";
echo '<meta name="twitter:creator" content="@3WDistributing">' . "\n";
```

### Update Company Phone

**File:** `inc/seo-class.php` (Line ~38)

```php
'telephone' => '+1-555-123-4567', // ← Change this
```

### Update Company Address

**File:** `inc/seo-class.php` (Lines ~40-46)

```php
'address' => [
    '@type' => 'PostalAddress',
    'streetAddress' => '123 Main Street',
    'addressLocality' => 'Los Angeles',
    'addressRegion' => 'CA',
    'postalCode' => '90001',
    'addressCountry' => 'US'
]
```

---

## 🧪 Testing Checklist

### Basic Tests (Do These First)
- [ ] Visit `/sitemap.xml` - Should see XML, not 404
- [ ] Visit `/robots.txt` - Should see text directives
- [ ] View source on homepage - Should see meta tags
- [ ] View source on blog post - Should see Article schema
- [ ] View source on product - Should see Product schema

### Advanced Tests (Use Tools)
- [ ] [Google Rich Results Test](https://search.google.com/test/rich-results)
- [ ] [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [ ] [Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [ ] [Schema Validator](https://validator.schema.org/)

---

## 🐛 Troubleshooting

### Sitemap 404 Error
```bash
# Solution:
# WP Admin → Settings → Permalinks → Save Changes
# Wait 5 minutes, try again
```

### Meta Tags Not Showing
```bash
# Check:
1. Clear browser cache (Ctrl+F5)
2. Disable caching plugins temporarily
3. Check wp-content/debug.log for errors
4. Verify wp_head() in header.php
```

### Schema Errors
```bash
# Check:
1. Image must be 1200x630px minimum
2. All URLs must be absolute (https://...)
3. Required fields must have values
4. Use schema validator to see specific errors
```

### Open Graph Image Not Loading
```bash
# Solution:
1. Set featured image on post/page
2. Image minimum: 200x200px
3. Recommended: 1200x630px
4. Format: JPG or PNG
5. Facebook debugger → Scrape Again
```

---

## 📊 Monitoring

### Weekly Checks
```bash
1. Google Search Console → Coverage
   - Check for new errors
   - Review indexing status

2. Google Search Console → Sitemaps
   - Verify sitemap processed
   - Check submission date

3. Traffic Review
   - Google Analytics → Acquisition → Organic Search
   - Compare week-over-week
```

### Monthly Reviews
```bash
1. Top Pages Performance
   - Search Console → Performance
   - Sort by impressions
   - Review CTR for top 20 pages

2. Schema Status
   - Search Console → Enhancements
   - Check for errors/warnings

3. Content Optimization
   - Update meta descriptions for low CTR pages
   - Add/improve category descriptions
```

---

## 🔑 Key URLs

### Your Site
- Sitemap: `https://www.3wdistributing.com/sitemap.xml`
- Robots: `https://www.3wdistributing.com/robots.txt`

### Testing Tools
- **Rich Results**: https://search.google.com/test/rich-results
- **Schema Validator**: https://validator.schema.org/
- **Facebook Debugger**: https://developers.facebook.com/tools/debug/
- **Twitter Validator**: https://cards-dev.twitter.com/validator
- **Page Speed**: https://pagespeed.web.dev/

### Search Console
- **Google**: https://search.google.com/search-console
- **Bing**: https://www.bing.com/webmasters

---

## 📝 Content Best Practices

### Blog Posts
```
✅ Add featured image (1200x630px)
✅ Write custom excerpt (150-160 chars)
✅ Use categories and tags
✅ Add alt text to all images
✅ Include internal links
✅ 300+ words for better ranking
```

### Products
```
✅ Unique descriptions (300+ words)
✅ Featured image (1200x1200px)
✅ Fill in SKU field
✅ Set proper stock status
✅ Add product categories
✅ Include specifications
```

### Pages
```
✅ Custom excerpt for meta description
✅ Featured image for Open Graph
✅ Descriptive page titles
✅ Proper heading structure (H1, H2, H3)
✅ Internal links to related content
```

### Categories
```
✅ Write description (150-300 words)
✅ Include relevant keywords
✅ Explain what content users find
✅ Update regularly
```

---

## 🎨 Image Optimization

### Dimensions
```
Blog Posts:    1200 x 630px  (16:9 ratio)
Products:      1200 x 1200px (1:1 ratio)
Open Graph:    1200 x 630px  (minimum)
```

### File Naming
```
✅ brake-pads-ceramic-2024.jpg
✅ red-floor-mats-universal.jpg

❌ IMG_12345.jpg
❌ photo.jpg
```

### Alt Text
```
✅ "Ceramic brake pads for 2024 Honda Civic"
✅ "Red universal floor mats set of 4"

❌ "Image"
❌ "Photo"
```

---

## 🚨 Common Mistakes to Avoid

### ❌ DON'T
- Install multiple SEO plugins (conflicts)
- Leave featured images missing
- Use generic image filenames
- Duplicate meta descriptions
- Ignore schema errors
- Skip permalink flush after install

### ✅ DO
- Write unique meta descriptions
- Optimize all images
- Test before deploying
- Monitor Search Console weekly
- Update organization details
- Submit sitemap to search engines

---

## 📞 Support

### Documentation
- **Full Docs**: `claudedocs/SEO_IMPLEMENTATION.md`
- **This Guide**: `claudedocs/SEO_QUICK_REFERENCE.md`

### WordPress Codex
- Meta Tags: https://codex.wordpress.org/Meta_Tags_in_WordPress
- SEO: https://codex.wordpress.org/Search_Engine_Optimization

### Schema.org
- Docs: https://schema.org/docs/documents.html
- Validator: https://validator.schema.org/

---

## ⚡ Performance Tips

### Speed Optimization
```
1. Use WebP images (faster loading)
2. Enable caching plugin
3. Minify CSS/JS
4. Use CDN for assets
5. Enable lazy loading (built into WP 5.5+)
```

### SEO Performance
```
1. Update content regularly
2. Add internal links
3. Improve page speed
4. Get backlinks from reputable sites
5. Create quality content consistently
```

---

## 📈 Expected Results

### Week 1-2
- Sitemaps indexed by Google
- Rich snippets start appearing
- Better social sharing previews

### Month 1
- 10-20% increase in indexed pages
- Improved click-through rate (CTR)
- Better positioning for brand keywords

### Month 3
- 30-50% increase in organic traffic
- Rich snippets for products/articles
- Higher average search position

### Month 6+
- Established authority in niche
- Consistent organic growth
- Strong featured snippet presence

---

**Last Updated:** 2025-11-03
**Version:** 1.0
**Maintained By:** Development Team
