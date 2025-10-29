# WP-CLI Blog Importers

Scaffolding for migrating production blog content into staging or the local Docker site using `wp` commands executed via `docker compose run -- wp-cli`.

## Prerequisites

- WordPress Docker stack running (`docker compose up -d` from project root).
- Valid application passwords exported in `.env`:
  - `PROD_WP_APP_USER`, `PROD_WP_APP_PASSWORD`
  - `STAGE_WP_APP_USER`, `STAGE_WP_APP_PASSWORD`
- `jq` available locally (used for parsing REST JSON).

## Scripts

- `import-posts.sh`: Fetch posts modified after a timestamp, ensure categories/tags exist locally, import featured images, and create or update posts on the local container.

All scripts support `--target=local|staging` (default local). Targets reuse staging credentials for local imports.
Each run writes logs and response artifacts under `logs/blog-import/wp-cli/`.

Pagination tip: supply `--page N` alongside `--limit` to step through older posts when a single request would exceed the 60-second CLI window.

## Usage

Dry run against local container:

```bash
set -a; source .env
./scripts/blog-import/wp-cli/import-posts.sh \
  --modified-after 2025-10-01T00:00:00Z \
  --limit 10 \
  --dry-run
```

When ready to write (local container only for now):

```bash
set -a; source .env
./scripts/blog-import/wp-cli/import-posts.sh \
--modified-after 2025-10-01T00:00:00Z \
--limit 10 \
--target staging
```

## Roadmap

- Handle gallery/embedded media rewrites inside post content.
- Extend commands to support staging execution (remote WP-CLI or API proxy).
- Review transformations for Supabase-hosted assets and embedded videos.
