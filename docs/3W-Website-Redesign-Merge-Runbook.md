# 3W Distributing — Website Redesign & Merge Runbook

> **Goal:** Re‑design the site with a brand‑new custom theme, keep WooCommerce **product data**, introduce **new Brands & Categories**, and merge the two sites into one:
>
> - Live: **https://www.3wdistributing.com/**
> - Store: **https://www.3wdistributing.com/shop/**
> - Blog: **https://www.3wdistributing.com/blog/**
> - Staging: **https://stage.3wdistributing.com/** (all build & QA happen here)
>
> **Approach:** Stand up a **fresh WordPress + WooCommerce install**, design and build the new **block theme + blog experience** to completion, then import a cleansed export of the current **product catalog** and full **media library** before rebuilding taxonomies (Brands, Categories) and 301 redirecting the old **shop subdomain** to **/shop**.

---

## Table of Contents

- [1) Objectives & Non‑Goals](#1-objectives--non-goals)
- [2) Key Decisions](#2-key-decisions)
- [3) Information Architecture & URLs](#3-information-architecture--urls)
- [4) Environment Setup & Theme-First Workflow](#4-environment-setup--theme-first-workflow)
- [5) Design System & Theme Scaffold](#5-design-system--theme-scaffold)
- [6) Data Model Overhaul (Brands & Categories)](#6-data-model-overhaul-brands--categories)
- [7) Templates, Blocks & Patterns](#7-templates-blocks--patterns)
- [8) Content & Asset Migration](#8-content--asset-migration)
- [9) Performance, Security, Analytics](#9-performance-security-analytics)
- [10) SEO & Redirects](#10-seo--redirects)
- [11) QA Checklist (Staging)](#11-qa-checklist-staging)
- [12) Launch Steps](#12-launch-steps)
- [13) Post‑Launch Checks](#13-post-launch-checks)
- [14) Rollback Plan](#14-rollback-plan)
- [15) Scripts & Snippets](#15-scripts--snippets)
- [16) Acceptance Criteria](#16-acceptance-criteria)
- [17) Task Tracker (Copy/Paste to Issues)](#17-task-tracker-copypaste-to-issues)

---

## 1) Objectives & Non‑Goals

**Objectives**
- Ship a **new custom block theme** with a modern, fast, consistent UI.
- Consolidate to **one WordPress install** at the **www** domain.
- Keep **WooCommerce products** and associated **media assets**, and safely re‑introduce new **Brands** and **Categories**.
- Place the store under **/shop** and the blog under **/blog**.
- Preserve SEO via **301 redirects**, clean canonicalization, and fresh sitemaps.

**Non‑Goals**
- Reusing the old theme, layout, or CSS.
- Migrating the old marketing site’s theme wholesale (pages will be rebuilt or selectively imported).

---

## 2) Key Decisions

1) **Phase Order:** Stand up the new block theme (including the blog experience) on a clean staging instance **before** importing any legacy catalog data.  
   *Rationale: locks design decisions without legacy clutter, so catalog import only happens once.*

2) **Base System:** Start from a **clean WordPress + WooCommerce install** and, after theme sign-off, import the current **product catalog**.  
   *Rationale: avoids legacy corruption while preserving product IDs, specs, and media.*

3) **Theme Architecture:** Build a **block theme** (Full Site Editing) for WordPress ≥ **6.6**, PHP ≥ **8.2**.  
   - Centralize design tokens in `theme.json` (colors, fonts, spacing).  
   - Use patterns and template parts; keep CSS minimal and component-scoped.

4) **Stable Product URLs:**  
   - **Products:** `/shop/%product%/` (**no category in the product URL**)  
   - **Category archives:** `/shop/category/%term%/`  
   - **Brand archives:** `/shop/brands/%term%/`  
   - **Why:** you plan to change categories and add brands; category-less product URLs minimize redirect churn.

5) **Brand as a First-Class Taxonomy:** Create a custom taxonomy `product_brand` (public, REST, archive pages).

6) **Blog URLs:** `/blog/%postname%/`

---

## 3) Information Architecture & URLs

**Top Navigation**
- Shop (mega‑menu by Categories)  
- Brands  
- Solutions (Applications)  
- Resources (Blog, Guides)  
- About  
- Contact

**Shop Landing:** hero, featured categories, featured brands, value propositions, featured products.  
**Brands Landing:** `/brands` grid of brand logos → brand archives at `/shop/brands/{brand}/`.  
**Solutions Landing:** `/solutions` (Applications taxonomy, e.g., Warehouse, Parking Lot, Sports).

**URL Rules (recap)**
```
/shop/%product%/
/shop/category/%term%/
/shop/brands/%term%/
/blog/%postname%/
```

---

## 4) Environment Setup & Theme-First Workflow

*Goal: prep staging so the theme/blog can be designed without legacy clutter; hold catalog import until Section 4.3 once design is approved.*

**Source Data Prep (Legacy Shop)**
1) Take a fresh full backup (files + DB snapshot) before touching the legacy shop.  
2) Export all published products (simple + variations) including attributes, galleries, downloads:  
   - CLI: `wp wc product export --dir=exports --filename=products.csv --with=meta,variations,images`  
   - GUI fallback: WooCommerce → Products → Export (include columns for attributes, categories, tags).  
3) Grab the entire `/wp-content/uploads/` library (product shots, marketing assets, downloads) via rsync/SFTP so every attachment is available for import.  
4) Optional: export customers/orders only if you plan to re-import them later; otherwise archive for reference.

**Provision Clean Staging**
5) Provision a new staging instance (Hostinger staging or `stage.3w…`) with a blank database.  
   - Install **WordPress ≥ 6.6** and enable PHP **8.2**.  
   - Install and activate WooCommerce, but skip demo data.  
   - Run integrity checks:
     ```bash
     wp core verify-checksums
     wp db check
     ```
   - Configure **sandbox payments** and dev SMTP credentials before testing commerce flows.
   - Create the `3w-2025` theme scaffolding (empty templates, header/footer placeholders) so Section 5 work can proceed without catalog data.

**Catalog & Media Import (Post Theme Sign-Off)**
6) **Only after Section 5 is approved**, upload the exported product CSV and media archive to staging.  
   - Recreate `/wp-content/uploads/` structure, then run `wp media regenerate` (or Regenerate Thumbnails).  
   - Import products with attributes via WP All Import **or**:
     ```bash
     wp wc product import --file=exports/products.csv --mapping=includes/mappings/products.json --merge=true
     ```
   - Spot-check 5–10 products (specs, galleries, downloads) for fidelity.
   - Confirm a sampling of non-product assets (marketing PDFs, hero imagery) resolve correctly in the Media Library.
7) Assign all imported products to temporary holding categories (e.g., `Legacy Import`) until new taxonomies go live.

**Staging Guardrails**
8) **Discourage indexing**: Settings → Reading → *Discourage search engines*.  
9) Enforce basic auth if needed, otherwise keep staging password-less but noindexed.  
10) Update site URLs only if auto-generated links reference legacy hosts (expect minimal replacements because catalog was imported, not cloned).

---

## 5) Design System & Theme Scaffold

*Complete this section and secure stakeholder sign-off before triggering the catalog import steps in Section 4.3.*

**Design Tokens** (in `theme.json`)
- Typography (e.g., Inter 400/600/700), type scale (12–36px).  
- Color palette (Primary, Accent, Ink, Muted, Background).  
- Spacing scale (4px increments).  
- Layout width (content 1200px, wide 1400px).

**Theme Skeleton**
```
/wp-content/themes/3w-2025/
  style.css
  theme.json
  functions.php
  templates/       (page.html, single.html, archive.html, 404.html)
  parts/           (header.html, footer.html, megamenu.html)
  patterns/        (hero, product-grid, spec-table, brand-grid, CTA)
  woocommerce/     (minimal overrides if truly necessary)
  assets/          (built css/js)
```

**Build Tooling**
- PostCSS + Autoprefixer (no heavy frameworks).  
- Tailwind optional with strict purge/safelist to keep CSS small.  
- Don’t enqueue jQuery unless required.

---

## 6) Data Model Overhaul (Brands & Categories)

**Post-Import Baseline**
- Imported products land in the temporary `Legacy Import` category without brands.  
- Build a mapping spreadsheet (SKU → new category / brand) before running any bulk updates.  
- Use WP-CLI scripts (see §15) to assign products to the new structure in batches.

**Taxonomies**
- Keep WooCommerce `product_cat`, rebuild the hierarchy.  
- Add `product_brand` (non‑hierarchical).  
- Optional: `product_application` (hierarchical) for Solutions pages.

**Attributes (for filters/specs)**
- `wattage`, `lumens`, `input-voltage`, `cct`, `cri`, `ip-rating`, `mounting`, `dimming`, `dlc-listed`, etc.  
- Consistent slugs (e.g., `cct-4000k`), visible on product pages, and used for variations where relevant.

**Filters/Search**
- Lean: *WooCommerce Product Filters* (by WooCommerce).  
- Pro/scale: *FacetWP* or *ElasticPress* for larger catalogs.

---

## 7) Templates, Blocks & Patterns

**Header & Mega‑menu:** categories + top brands; sticky on mobile; accessible focus states.  
**/shop (landing):** hero → featured category tiles → brand strip → featured products grid.  
**Brand Archive:** `/shop/brands/{brand}/` header (logo + blurb) → product grid → resources.  
**Category Archive:** SEO intro → filter sidebar → grid → pagination.  
**Product Page:** gallery → key facts (Brand, DLC, Watts, Lumens) → CTA → tabs (Specs, Downloads, Q&A).  
**Blog:** `/blog` listing with clean cards, related posts, CTAs back into Shop.

Create reusable **patterns** for: hero, product grids, brand grid, spec table, CTA rows, download cards.

---

## 8) Content & Asset Migration

- Import or rebuild key marketing pages (Home, About, Contact, Solutions, Resources).  
- Standardize product images (e.g., 4:3), compress to WebP.  
- Audit and migrate legacy media assets (PDFs, spec sheets, hero imagery) into the new uploads library; update references in content/templates.
- Replace internal links to point to `/shop/...` and `/blog/...`.  
- Generate **brand logos** as SVG (sanitized) or high‑res PNG.

---

## 9) Performance, Security, Analytics

**Performance**
- LiteSpeed Cache (page & object cache, CSS/JS minify, Critical CSS, WebP).  
- Enable **Redis object cache** if available (big win for WooCommerce).

**Security**
- 2FA for admins, limit login attempts, disable XML‑RPC, least‑privilege roles.  
- Keep plugins lean; remove unused ones.

**Analytics**
- GA4 (optionally via GTM).  
- WooCommerce → Google Listings & Ads or a product feed plugin for Merchant Center.  
- Verify payment webhooks after launch.

---

## 10) SEO & Redirects

**Final URL Rules**
- Products: `/shop/%product%/`  
- Categories: `/shop/category/%term%/`  
- Brands: `/shop/brands/%term%/`  
- Blog: `/blog/%postname%/`

**Redirects**
1) **Subdomain → Subdirectory**  
   `shop.3wdistributing.com/*` → `www.3wdistributing.com/shop/*`

2) **Drop category from product URLs (if previously present):**  
   `/shop/{category}/{product}` → `/shop/{product}`

3) **Category renames:** old category path → new category path under `/shop/category/...`

4) **Sitemaps & GSC:** submit new sitemap after launch; keep both properties (shop subdomain & www) during transition.

**On‑Page**
- One SEO plugin (Yoast or RankMath).  
- Organization, Product, and Breadcrumb schema enabled.  
- Canonicals point to **new** URLs.

---

## 11) QA Checklist (Staging)

- **Catalog**
  - [ ] Category pages render; filters work; brand archives list correct products.
  - [ ] Search relevance is sensible (brand + spec terms).

- **Checkout**
  - [ ] Taxes, shipping, coupons.  
  - [ ] Test payment (sandbox) + transactional emails deliver to dev inbox.

- **Performance**
  - [ ] LCP < 2.5s on Home, Category, Product (mobile).  
  - [ ] Images optimized; no layout shifts.

- **Accessibility**
  - [ ] Headings hierarchy; alt text on product images.  
  - [ ] Keyboard navigation & focus states; color contrast passes.

- **Crawl/SEO**
  - [ ] No 404s; robots.txt & sitemap.xml valid.  
  - [ ] Canonicals correct; no stray `stage.` or `shop.` absolute links.

---

## 12) Launch Steps

1) **Freeze window** for product/order edits; take full backup (files + DB).  
2) **Push staging → live** (Hostinger “Push to Live” or Duplicator).  
3) **Search‑replace** staging URLs on live:
   ```bash
   wp search-replace 'https://stage.3wdistributing.com' 'https://www.3wdistributing.com' --all-tables --precise --recurse-objects --skip-columns=guid
   wp search-replace 'https://shop.3wdistributing.com' 'https://www.3wdistributing.com/shop' --all-tables --precise --recurse-objects --skip-columns=guid
   ```
4) **Permalink bases** (products under `/shop`):
   ```bash
   wp option update woocommerce_permalinks '{"product_base":"\/shop","category_base":"shop\/category","attribute_base":""}' --format=json
   wp rewrite flush --hard
   ```
5) **Re‑enable production**: indexing ON, live payment keys/webhooks, real SMTP, purge caches/CDN.  
6) **Activate 301s** (subdomain → subdirectory + category transforms).  
7) **Submit sitemap** in Search Console.

---

## 13) Post‑Launch Checks

- [ ] Crawl site (Screaming Frog); fix any 404s with Redirection plugin.  
- [ ] Verify GA4 & ecommerce events.  
- [ ] Verify Stripe/PayPal webhooks & a real low‑value order.  
- [ ] Monitor Search Console coverage + rich results.  
- [ ] Keep staging online (noindexed) for future updates.

---

## 14) Rollback Plan

If a critical issue appears within the first hours:
1) Re‑enable maintenance mode on **www**.  
2) Restore the last **full backup** (files + DB).  
3) Temporarily disable the new 301 rules to restore old paths.  
4) Fix the root cause on **staging**, retest, then re‑attempt launch.

---

## 15) Scripts & Snippets

### 15.1 Register Custom Taxonomies (Brands, Applications)
Create a plugin at `/wp-content/plugins/3w-taxonomies/3w-taxonomies.php`:

```php
<?php
/**
 * Plugin Name: 3W Custom Taxonomies
 * Version: 0.1
 */
add_action('init', function () {
  register_taxonomy('product_brand', ['product'], [
    'label' => 'Brands',
    'public' => true,
    'hierarchical' => false,
    'show_in_rest' => true,
    'rewrite' => ['slug' => 'shop/brands', 'with_front' => false],
  ]);

  register_taxonomy('product_application', ['product'], [
    'label' => 'Applications',
    'public' => true,
    'hierarchical' => true,
    'show_in_rest' => true,
    'rewrite' => ['slug' => 'shop/applications', 'with_front' => false],
  ]);
});
```

Activate the plugin on **staging**.

---

### 15.2 Import Categories & Brands from CSV

Create `terms.csv`:
```
taxonomy,term,slug,parent
product_cat,LED High Bays,led-high-bays,
product_cat,Area & Site Lighting,area-site-lighting,
product_cat,Wall Packs,wall-packs,
product_brand,Acuity Brands,acuity,
product_brand,Keystone,keystone,
product_brand,Philips,philips,
```

Importer `import-terms.php`:
```php
<?php
$rows = array_map('str_getcsv', file('terms.csv'));
$header = array_map('trim', array_shift($rows));
foreach ($rows as $r) {
  $r = array_combine($header, $r);
  $tax = $r['taxonomy']; $name = $r['term']; $slug = $r['slug']; $parent = 0;
  if (!empty($r['parent'])) {
    $p = get_term_by('slug', $r['parent'], $tax);
    $parent = $p ? $p->term_id : 0;
  }
  if (!term_exists($slug, $tax)) {
    wp_insert_term($name, $tax, ['slug'=>$slug, 'parent'=>$parent]);
  }
}
echo "Terms imported.\n";
```

Run:
```bash
wp eval-file import-terms.php
```

---

### 15.3 Remap Old Categories → New Categories on Products

Create `map.json`:
```json
{
  "old-high-bay": "led-high-bays",
  "site-lighting": "area-site-lighting",
  "wallpacks": "wall-packs"
}
```

Script `recat.php`:
```php
<?php
$map = json_decode(file_get_contents('map.json'), true);
$products = get_posts(['post_type'=>'product','posts_per_page'=>-1,'fields'=>'ids']);
foreach ($products as $pid) {
  $slugs = wp_get_post_terms($pid, 'product_cat', ['fields'=>'slugs']);
  $new = [];
  foreach ($slugs as $s) { $new[] = $map[$s] ?? $s; }
  $new = array_unique(array_filter($new));
  if ($new) wp_set_object_terms($pid, $new, 'product_cat', false);
}
echo "Re-categorized.\n";
```

Run:
```bash
wp eval-file recat.php
```

---

### 15.4 (Optional) Migrate Brand **Attribute** → **Taxonomy**

If you used a product attribute `pa_brand`, this maps it to the `product_brand` taxonomy:

```php
<?php
$old = 'pa_brand'; $new = 'product_brand';
$products = get_posts(['post_type'=>'product','posts_per_page'=>-1,'fields'=>'ids']);
foreach ($products as $pid) {
  $terms = wp_get_post_terms($pid, $old);
  if (is_wp_error($terms) || empty($terms)) continue;
  $new_ids = [];
  foreach ($terms as $t) {
    $exists = term_exists($t->slug, $new);
    $term_id = $exists ? $exists['term_id'] : wp_insert_term($t->name, $new, ['slug'=>$t->slug])['term_id'];
    $new_ids[] = (int)$term_id;
  }
  wp_set_object_terms($pid, $new_ids, $new, false);
}
echo "Done.\n";
```

Run:
```bash
wp eval-file migrate-brand.php
```

---

### 15.5 WooCommerce Permalink Settings (CLI)

```bash
# Products at /shop/%product%/
wp option update woocommerce_permalinks '{"product_base":"\/shop","category_base":"shop\/category","attribute_base":""}' --format=json
wp rewrite flush --hard
```

---

### 15.6 Redirects

**Apache (.htaccess at web root)**
```apache
RewriteEngine On
# shop subdomain to /shop
RewriteCond %{HTTP_HOST} ^shop\.3wdistributing\.com$ [NC]
RewriteRule ^(.*)$ https://www.3wdistributing.com/shop/$1 [R=301,L]

# old /shop/{category}/{product} -> /shop/{product}
RewriteRule ^shop/[^/]+/([^/]+)/?$ /shop/$1 [R=301,L]
```

**Nginx**
```nginx
# subdomain vhost
server {
  listen 80;
  listen 443 ssl http2;
  server_name shop.3wdistributing.com;
  return 301 https://www.3wdistributing.com/shop$request_uri;
}

# in the main www server block
location ~* ^/shop/[^/]+/([^/]+)/?$ {
  return 301 /shop/$1;
}
```

**Redirection CSV (specific mappings)**
```
/product-category/old-name/ , https://www.3wdistributing.com/shop/category/new-name/ , exact , 301
```

---

### 15.7 Robots.txt (Live)

```
User-agent: *
Disallow: /wp-admin/
Allow: /wp-admin/admin-ajax.php
Sitemap: https://www.3wdistributing.com/sitemap_index.xml
```

---

## 16) Acceptance Criteria

- **Design & Theme**
  - New block theme with `theme.json` tokens and custom patterns.
  - Consistent branding across Shop, Brands, Blog.
- **Data & UX**
  - Products at `/shop/{product}/` (no category in product URL).
  - Brands at `/shop/brands/{brand}/` with brand header + product grid.
  - Categories at `/shop/category/{cat}/` with working filters.
  - Media library restored (product galleries, downloads, marketing assets); spot checks confirm attachments resolve.
  - All products reassigned from temporary import buckets to the new brand/category structure; SKU counts match the legacy export.
- **SEO**
  - `shop.` subdomain 301s to `/shop/...`.
  - Old category paths 301 to new paths.
  - Sitemap submitted; canonicals correct; noindex disabled on live.
- **Performance & Stability**
  - LCP < 2.5s on Home, Category, Product (mobile).
  - Checkout works end‑to‑end; transactional emails deliver.
- **Operations**
  - Staging retained (noindexed), automated daily backups, object cache enabled.
  - Fresh production database seeded from clean staging export; `wp db check` returns OK.

---

## 17) Task Tracker (Copy/Paste to Issues)

**Discovery**
- [ ] Export plugin/theme lists from both sites
- [ ] Export URL inventories (WP‑CLI or crawler)
- [ ] Document Woo settings (gateways, shipping, taxes, webhooks)
- [ ] Full backups (files + DB)

**Staging Build — Phase 1 (Theme)**
- [ ] Export products + media from legacy shop (prep only; hold import until Phase 2)
- [ ] Provision fresh staging WordPress + WooCommerce install (blank catalog)
- [ ] Create `3w-2025` theme scaffold and configure blog templates
- [ ] Noindex staging; sandbox payments; dev SMTP
- [ ] Run `wp core verify-checksums` and `wp db check`

**Staging Build — Phase 2 (Catalog & Data)**
- [ ] Import product catalog + media; regenerate thumbnails; spot-check SKUs and attachments
- [ ] Assign imported products to temporary holding categories (Legacy Import)
- [ ] Verify sample media assets resolve inside new theme patterns

**Design & Theme**
- [ ] Finalize tokens (colors, fonts, spacing) in `theme.json`
- [ ] Build header/footer/mega‑menu patterns
- [ ] Build shop, brand, category, product templates
- [ ] Style Woo Block Cart/Checkout

**Data Model**
- [ ] Register `product_brand` (+ optional `product_application`) taxonomy
- [ ] Seed brands via CSV or script
- [ ] Build new category hierarchy
- [ ] Bulk remap products to new categories

**Content**
- [ ] Rebuild key pages (Home, About, Contact, Solutions, Resources)
- [ ] Set Blog to `/blog/` and add 3–5 seed posts
- [ ] Import legacy media assets (PDFs, hero imagery) and relink within content modules
- [ ] Optimize & standardize product images

**Performance & Security**
- [ ] Enable LiteSpeed Cache (and Redis object cache if available)
- [ ] Harden login/roles; disable XML‑RPC
- [ ] Enable daily backups

**SEO**
- [ ] Choose SEO plugin (Yoast/RankMath), enable Product & Breadcrumb schema
- [ ] Prepare redirect CSVs (category renames)
- [ ] Test wildcard redirects on staging vhost

**QA (Staging)**
- [ ] Catalog pages, filters, brand archives
- [ ] Verify no products remain in temporary import categories; SKU counts align with export log
- [ ] Sample media assets (downloads, hero images) load correctly site-wide
- [ ] Checkout w/ sandbox payments; transactional emails
- [ ] PSI spot‑checks; accessibility checks
- [ ] Crawl for 404s; fix internal links

**Launch**
- [ ] Freeze & backup
- [ ] Push staging → live
- [ ] Final search‑replace to `www.3w...`
- [ ] Run `wp db check` + product count spot check pre-cutover
- [ ] Re‑enable indexing; live payment keys; SMTP
- [ ] Activate 301s; submit sitemap

**Post‑Launch**
- [ ] Crawl for 404s; add missing redirects
- [ ] Verify GA4 ecommerce & webhooks
- [ ] Monitor Search Console for coverage & rich results
