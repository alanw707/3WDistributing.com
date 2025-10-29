# Blog Import Backlog

- **Verify API Access**
  - [ ] Confirm REST API endpoints accessible on production and staging.
  - [ ] Ensure application passwords are enabled on both environments.

- **Credentials & Security**
  - [ ] Create/read-only application password on production (posts/media).
  - [ ] Create/write application password on staging (posts/media/taxonomies).
  - [ ] Store credentials securely in `.env` and secret manager.

- **Data Inventory**
  - [ ] Export list of post types, counts, statuses from production.
  - [ ] Inventory categories/tags with slug mappings.
  - [ ] Audit required post meta (ACF fields, SEO meta, canonical URLs).
  - [ ] Identify media usage (featured images, galleries, embeds).

- **Tooling Decision**
  - [ ] Select import tooling (Python script, WP-CLI REST, or Node client).
  - [ ] Draft architecture for retry logic, logging, and incremental sync.

- **Staging Preparation**
  - [ ] Backup staging database and uploads.
  - [ ] Create placeholder authors/users if mapping needed.
  - [ ] Validate disk space and uploads permissions.
  - [ ] Pre-create critical categories/tags if not imported automatically.

- **Script Implementation**
  - [ ] Implement production fetcher (paged requests, rate limiting).
  - [ ] Implement staging media uploader with featured/attachment mapping.
  - [ ] Implement taxonomy sync (categories/tags creation/update).
  - [ ] Implement post creation/update with meta + SEO fields.
  - [ ] Add configuration for incremental runs (date filter, ID filter).

- **Testing**
  - [ ] Dry-run import on 3–5 posts; confirm output.
  - [ ] Validate Gutenberg block rendering, media, links.
  - [ ] Check taxonomy mapping and author attribution.

- **Full Import & Verification**
  - [ ] Execute full import.
  - [ ] Spot-check posts randomly on staging.
  - [ ] Run staging sitemap/SEO checks.
  - [ ] Clear caches; notify stakeholders.

- **Documentation**
  - [ ] Document script usage (env vars, CLI arguments, logging).
  - [ ] Capture troubleshooting tips and rollback steps.
  - [ ] Update project backlog with follow-up tasks.
