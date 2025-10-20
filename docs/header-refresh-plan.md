# Header Refresh Plan — Draft

## Goals
- Clarify hierarchy between utility bar, brand block, navigation, search, and commerce actions.
- Align header visuals with palette and spacing tokens defined in the theme style guide.
- Improve accessibility, focus handling, and responsive behavior across breakpoints.

## Layout Proposal
- **Utility Bar (desktop ≥64rem)**
  - Left cluster: hotline + hours text; right cluster: optional utility menu items (account, support, dealer login) using inline list.
  - Reserve center area for announcements/promo pill when needed, otherwise keep compact.
  - Convert to single column stack on narrow viewports; show menu items in drawer variant.
- **Primary Row**
  - Use three-column flex grid: `branding` (logo + title), `navigation`, `actions` (search + cart + optional login).
  - Set max width to `var(--threew-content-max-width)` with auto margins; use `gap` tokens only between columns.
  - Maintain sticky behavior but reduce vertical padding once user scrolls beyond 96px (existing compact state).
- **Branding Block**
  - Increase logo max height to 32px desktop while preserving compact shrink state (24px).
  - Keep site title optional; allow custom tagline slot beneath for campaigns (hidden by default).
- **Primary Navigation**
  - Align menu center with equalized spacing; prep for future mega menu trigger tiers.
  - Ensure list items share baseline alignment with search input.
- **Action Wing**
  - Stack search field above cart on mobile (≤48rem), inline on desktop with fixed basis ~280px for search.
  - Provide slot for `Sign In` link or icon button adjacent to cart.

## Token & Style Mapping
- Background gradient: replace ad hoc RGBA with `linear-gradient(180deg, var(--wp--preset--color--primary-dark) 0%, rgba(12, 30, 51, 0.92) 70%, rgba(12, 30, 51, 0.85) 100%)` and expose as `--threew-header-gradient` in `tokens.css`.
- Borders: use `color.border` token (or `rgba(255, 255, 255, 0.12)` alias) for dividers; avoid hard-coded hex.
- Spacing: add `--threew-header-padding-y` (default `clamp(0.75rem, 2vw, 1.15rem)`) and reuse `--threew-space-xs` only for drawer interior.
- Typography: utility bar uppercase meta using `var(--threew-font-size-meta)`; nav text `var(--threew-font-size-body)` for better legibility.
- Shadows: reuse `--threew-shadow-header` but allow compact modifier to add stronger blur (`box-shadow: 0 12px 32px rgba(12, 30, 51, 0.28)`).

## Interaction & Access
- Search: boost placeholder contrast to `rgba(238, 243, 255, 0.82)`; add focus outline `2px solid var(--wp--preset--color--secondary)` plus glow.
- Nav links: active/hover underline uses gradient accent; ensure focus-visible outline offset 4px for keyboard navigation.
- Cart CTA: introduce icon badge slot; default badge hidden, shows with count using `aria-live="polite"` span.
- Drawer: maintain body lock; add focus trap once mega menu work lands (separate ticket).

## Responsive Considerations
- ≤48rem: collapse utility bar content into drawer; hotline becomes tappable pill above brand line.
- 48–64rem: display hotline + search stacked; nav transitions to centered flex with smaller gaps.
- ≥80rem: increase nav gap slightly and cap search width to prevent oversizing on ultrawide.
- Compact state: shrink header padding, reduce search pill padding, scale cart badge down.

## Implementation Checklist
1. Update `tokens.css` with new header gradient and spacing variables.
2. Refactor `header.css` and `header-desktop.css` to adopt new layout primitives and token references.
3. Adjust markup in `header.php` if badge slot or tagline placeholder requires wrapping elements.
4. Refresh `header.js` if additional state management (badge, tagline toggles) introduced.
5. Validate visual changes at 375px, 768px, 1024px, 1440px; capture screenshots for docs.
6. Run `npm run lint:css`, `npm run lint:js`, and smoke test in local WP before merge.
