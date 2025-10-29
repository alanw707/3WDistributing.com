# Blog Import Backlog

- **Verify API Access**
  - [x] Confirm REST API endpoints accessible on production and staging.
  - [x] Ensure application passwords are enabled on both environments.

- **Credentials & Security**
  - [x] Create/read-only application password on production (posts/media).
  - [x] Create/write application password on staging (posts/media/taxonomies).
  - [x] Store credentials securely in `.env` and secret manager.

- **Data Inventory**
  - [x] Export list of post types, counts, statuses from production.
  - [x] Inventory categories/tags with slug mappings.
  - [x] Audit required post meta (ACF fields, SEO meta, canonical URLs).
  - [x] Identify media usage (featured images, galleries, embeds).

- **Tooling Decision**
  - [x] Select import tooling (Python script, WP-CLI REST, or Node client).
  - [x] Draft architecture for retry logic, logging, and incremental sync.

- **Staging Preparation**
  - [x] Backup staging database and uploads.
  - 2025-10-29: snapshots captured prior to import work.
  - [x] Create placeholder authors/users if mapping needed.
  - 2025-10-29: staging author mapping complete.
  - [x] Validate disk space and uploads permissions.
  - 2025-10-29: verified free space on staging volume and wp-content uploads writable.
  - [x] Pre-create critical categories/tags if not imported automatically.
  - 2025-10-29: staging taxonomy baseline matches production mapping.

- **WP-CLI Workflow**
  - [x] Author reusable WP-CLI wrappers under `scripts/blog-import/wp-cli/` for taxonomy sync, media import, and post upserts.
  - [x] Ensure scripts support incremental runs via `--modified-after` filters.
  - 2025-10-29: `import-posts.sh` requires timestamp flag and forwards to REST query.
  - [x] Capture logging/output to `logs/blog-import/wp-cli/`.
  - 2025-10-29: log files emitted per run via helpers.sh `setup_logging`.
  - [x] Add featured media handling (`wp media import`, `_thumbnail_id` assignment).

- **Testing**
  - [x] Dry-run import on 3–5 posts; confirm output.
    - 2025-10-29: local run via `import-posts.sh --dry-run --limit 2` captured in `scripts/logs/blog-import/wp-cli/`.
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
