# 3W 2025 Theme Scaffold

## Scripts
```bash
npm install           # install dependencies
npm run dev           # start live reload via @wordpress/scripts
npm run build         # production build (minified via PostCSS + cssnano)
npm run lint:css      # stylelint + CSS line-budget enforcement
npm run lint:js       # @wordpress/scripts linting
```

## Structure
- `assets/`: Built CSS/JS copied after `npm run build`.
- `src/`: Source JS/CSS for bundling.
- `templates/`, `parts/`, `patterns/`: Block templates and patterns.
- `theme.json`: Design tokens aligned with design system documents.

## Notes
- The theme now includes a Fitment Selector custom block located under `src/blocks/fitment-selector`.
- Compiled assets are emitted to `build/` and automatically enqueued on front-end + editor.
- Block patterns reside in `patterns/` and can be inserted via the block inserter under the \"3W\" pattern categories.

## CSS Authoring Guardrails
- Author styles in component-, pattern-, or template-scoped files; avoid dumping updates into a single global stylesheet.
- `npm run lint:css` fails if any source stylesheet exceeds 500 lines (`CSS_MAX_LINES` env var can override).
- Production builds run through Autoprefixer and cssnano so shipped CSS stays minified.
