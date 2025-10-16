# Repository Guidelines

## Project Structure & Module Organization
- `docs/` — living design system artifacts (style guide, backlog, UX notes). Treat these as the source of truth before touching theme code.
- `wp-content/themes/3w-2025/` — custom block theme. Source files live in `src/`, compiled assets land in `build/`, and reusable patterns/template parts sit in `patterns/`, `parts/`, and `templates/`.
- `scripts/` — helper shell scripts for WordPress bootstrap and data tasks. Run them from the repository root unless instructed otherwise.

## Build, Test, and Development Commands
- `docker compose up -d` — launches the local WordPress + MySQL stack. Run from the repo root.
- `npm run dev` (inside `wp-content/themes/3w-2025/`) — starts the @wordpress/scripts watcher for block/theme assets.
- `npm run build` — produces minified CSS/JS via PostCSS (autoprefixer + cssnano) and webpack.
- `npm run lint:css` / `npm run lint:js` — stylelint with 500-line guardrails and WordPress ESLint presets; both must pass before committing.

## Coding Style & Naming Conventions
- Write CSS in modular files per component/pattern; BEM-style class names (`block__element--modifier`) in lowercase kebab-case.
- Keep CSS files under 500 lines; the lint pipeline fails when the limit is exceeded. Override with `CSS_MAX_LINES` only for migrations.
- Use WordPress presets (`--wp--preset--*`) or theme tokens (`--threew-*`) for colors, spacing, and typography.
- Prefer modern ES modules and follow the defaults provided by `@wordpress/scripts` for block registration and JSX.

## Testing Guidelines
- Manual smoke tests: visit `http://localhost:8080/` after rebuilding assets and confirm block patterns render without console errors.
- Playwright is the preferred end-to-end harness for regression checks (fitment selector states, navigation interactions). Add new specs under `tests/e2e/` (create directory if absent) and run them before shipping major UI work.
- When introducing data integrations, add unit tests alongside the block or utility in `src/` using Jest (supported via @wordpress/scripts).

## Commit & Pull Request Guidelines
- Use imperative, present-tense commit subject lines (e.g., “Add CSS guardrail tooling”) and keep bodies focused on rationale or follow-up work.
- Before opening a PR: run `npm run lint:css`, `npm run lint:js`, and `npm run build`; attach screenshots or terminal output for significant UI or tooling changes.
- Reference related backlog items or GitHub issues in the PR description, and note any manual verification steps (e.g., “Playwright smoke suite green”).
