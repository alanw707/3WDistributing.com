# Local Development — Docker Stack

## Prerequisites
- Docker Desktop or Docker Engine ≥ 24
- Docker Compose Plugin ≥ 2.20
- Node.js (optional, for future build tooling)

## Setup Steps
1. Copy environment template:
   ```bash
   cp .env.example .env
   ```
2. Update credentials in `.env` and optionally set `XDEBUG_MODE`.
3. Start the stack:
   ```bash
   docker compose up -d
   ```
4. Run bootstrap script on first launch:
   ```bash
   bash scripts/wp-bootstrap.sh
   ```
5. Access services:
   - WordPress: http://localhost:8080
   - Mailpit UI: http://localhost:8025 (SMTP port 1025)
   - phpMyAdmin: http://localhost:8081 (use DB creds from `.env`)

## Command Cheatsheet
- **WP-CLI:**
  ```bash
  docker compose run --rm wpcli "wp plugin list"
  docker compose run --rm wpcli "wp theme list"
  docker compose run --rm wpcli "wp theme activate 3w-2025"
  ```
- **Stop stack:** `docker compose down`
- **Purge volumes:** `docker compose down -v`

## Theme Development
- Theme scaffold lives at `wp-content/themes/3w-2025` (mounted into container).
- Assets compile into `assets/app.css`, `assets/app.js`, `assets/editor.css`.
- Add block patterns to `patterns/` and template parts under `parts/`.
- Run `bash scripts/theme-sync.sh` to rebuild and stream the theme into the running WordPress container (`--skip-build` skips the rebuild step).

## Xdebug Toggle
```
bash scripts/xdebug-toggle.sh debug   # enable debug,develop
bash scripts/xdebug-toggle.sh off     # disable
```
Restart WordPress container after toggling.

## Database Admin & Mail Testing
- phpMyAdmin provides UI for inspecting the MySQL database.
- Mailpit captures outbound emails; configure WordPress SMTP to `mailpit:1025` in staging configuration.

## Known Notes
- WooCommerce currently requires WordPress 6.7+. If the plugin install step fails, update the WordPress image or install a compatible WooCommerce version manually after core upgrades.
- Media uploads require the `wp-content/uploads` directory on the host. The bootstrap process now ensures the container has permissions to create it.

---
Stack verified for iterative theme development prior to catalog import.
