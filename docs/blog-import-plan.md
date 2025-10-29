# Blog Import Plan

## 1. Source Inventory & Access
- Confirm production and staging WordPress versions, REST API availability, and that application passwords are enabled.
  - 2025-10-29 check: `https://www.3wdistributing.com/wp-json/` and `https://staging.3wdistributing.com/wp-json/` both return HTTP 200 with `authentication.application-passwords` endpoint metadata.
- Create/validate an application password for a dedicated migration account on staging with permissions to manage and publish posts, categories, and media.
  - 2025-10-29: staging password issued for `content@auto-blog.com`; store secret outside repo and load via `.env`.
- Create/validate an application password (or REST API token) on production with read scope for posts, terms, media, and meta.
  - Strip spaces from the 24-character application password before using it in HTTP Basic auth; keep the human-readable value in the secret store for reference.
  - 2025-10-29: production read-only password generated for `alanw707@gmail.com`; sanitized value lives in `.env` as `PROD_WP_APP_PASSWORD`.

## 2. Data Audit
- Pull an inventory of post types from production, including counts by status (`publish`, `draft`, etc.).
  - 2025-10-29 counts via REST headers: posts `publish=9`, pages `publish=16`, `avada_portfolio=256`, `avada_faq=0`, media library items `3116`.
- Identify required taxonomies, categories, and tags; record slug/ID mappings.
  - Categories: `uncategorized` (id 1, count 9), `wheels` (id 171, count 0); tags present but unused (ids 251–301 for wheels, Brabus, etc.).
- Document required post meta and SEO fields (ACF, Yoast, RankMath, etc.).
  - Public REST responses expose only default `_bbp_*` forum meta and empty `footnotes`; no Yoast/RankMath payload detected—confirm plugin requirements with stakeholders.
- Note featured media usage, embedded galleries, and block types/shortcodes that require special handling.
  - Recent posts reference Supabase-hosted images and `<div data-youtube-video>` wrappers; featured_media fields are `0` for sampled posts—plan to rewrite remote asset URLs and normalize embeds during import.

## 3. Migration Strategy
- Decide whether import is a one-time migration or a repeatable sync; define schedule.
- Choose tooling (Python + `requests`, Node + `@wordpress/api-fetch`, or WP-CLI `wp rest` commands).
  - Decision (2025-10-29 update): adopt a WP-CLI based pipeline executed via Docker (`docker compose run -- wp ...`) for imports; keep Node scaffold for optional prototyping only.
- Define mapping/conversion rules: author mapping, taxonomy mapping, canonical URLs, and content rewrites (e.g., internal links).
  - Architecture outline:
    - `wp-cli/import-posts.sh`: orchestrate post/media/taxonomy sync via `wp` commands inside Docker.
    - `wp-cli/lib/` scripts: helper shell functions for pagination, logging, and checksum comparisons.

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
  - 2025-10-29: scaffolded WP-CLI wrappers (`scripts/blog-import/wp-cli/`) for import flow; importer now syncs categories/tags, sets featured media via `wp media import`, and upserts posts on the local container with artifacts in `logs/blog-import/wp-cli/`. Gallery/embedded media handling still pending.
  - 2025-10-29: Local smoke run imported four production posts into the Docker site (`import-posts.sh --modified-after 2025-09-01T00:00:00Z`); see per-run logs in `scripts/logs/blog-import/wp-cli/`.

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
