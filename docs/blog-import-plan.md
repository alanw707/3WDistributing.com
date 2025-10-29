# Blog Import Plan

## 1. Source Inventory & Access
- Confirm production and staging WordPress versions, REST API availability, and that application passwords are enabled.
- Create/validate an application password for a dedicated migration account on staging with permissions to manage and publish posts, categories, and media.
- Create/validate an application password (or REST API token) on production with read scope for posts, terms, media, and meta.

## 2. Data Audit
- Pull an inventory of post types from production, including counts by status (`publish`, `draft`, etc.).
- Identify required taxonomies, categories, and tags; record slug/ID mappings.
- Document required post meta and SEO fields (ACF, Yoast, RankMath, etc.).
- Note featured media usage, embedded galleries, and block types/shortcodes that require special handling.

## 3. Migration Strategy
- Decide whether import is a one-time migration or a repeatable sync; define schedule.
- Choose tooling (Python + `requests`, Node + `@wordpress/api-fetch`, or WP-CLI `wp rest` commands).
- Define mapping/conversion rules: author mapping, taxonomy mapping, canonical URLs, and content rewrites (e.g., internal links).

## 4. Staging Preparation
- Snapshot/backup the staging database and wp-content/uploads.
- Ensure staging has appropriate authors/users for mapping; create placeholder users if necessary.
- Confirm write permissions and available disk space for media uploads.
- Create required categories/tags on staging if not imported automatically.

## 5. Implementation Tasks
- Build a REST client that:
  1. Authenticates against production API (using application password).
  2. Requests posts/media in paged batches.
  3. For each post, downloads featured image and attached media locally.
  4. Uploads media to staging (`/wp/v2/media`) with correct metadata.
  5. Creates or updates categories/tags on staging (`/wp/v2/categories`, `/wp/v2/tags`).
  6. Creates/updates posts on staging (`/wp/v2/posts`) including status, slug, excerpt, content, meta, SEO fields.
- Implement logging and retry/backoff for network errors.

## 6. Testing & Validation
- Run a dry-run with a small subset of posts; validate content, images, taxonomies on staging.
- QA Gutenberg block rendering, shortcodes, and internal links.
- Verify SEO/meta fields and canonical URLs.

## 7. Cutover / Sync Routine
- Execute the full import once validation passes.
- For incremental syncs, support filtering by `modified_after` date or post IDs.
- Document rollback strategy (use staging backup).

## 8. Documentation & Handoff
- Record environment variables and usage instructions for the import script.
- Store logs and mapping tables.
- Update team documentation/backlog with follow-up tasks or lessons learned.
