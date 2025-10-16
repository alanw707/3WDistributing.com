# 3W Distributing — Preliminary Style Guide

## Color Usage

| Token | Hex | Primary Uses |
| --- | --- | --- |
| `color.primary` | `#1D6BCD` | Primary buttons, active nav links, call-to-action backgrounds, accent underlines.
| `color.primary-dark` | `#0C1E33` | Header background, mega menu, footer, dark hero overlays.
| `color.secondary` | `#18B8D7` | Hover states, secondary buttons, badge borders, active filter indicators.
| `color.accent` | `#D84432` | Limited call-outs: promo pills, urgent alerts, sale tags.
| `color.surface` | `#F3F6FA` | Section backgrounds, blog listing cards, checkout summary panels.
| `color.surface-alt` | `#FFFFFF` | Card surfaces, product tiles, form inputs.
| `color.text-primary` | `#14181F` | Body text, headings on light surfaces.
| `color.text-inverse` | `#EEF3FF` | Text on dark backgrounds.
| `color.border` | `#2F3640` | Divider lines, card borders, filter outlines.

### Gradients
- **Hero Gradient:** `linear-gradient(135deg, #0C1E33 0%, #1D6BCD 60%, #18B8D7 100%)`
- **Category Hover:** `linear-gradient(180deg, rgba(12,30,51,0) 0%, rgba(29,107,205,0.85) 100%)`

## Typography
- **Display / Hero (`font.display`):** "Rajdhani", sans-serif (uppercase headings, fitment labels)
- **Body (`font.body`):** "Inter", sans-serif (paragraphs, navigation, product descriptions)
- **Numeric / Tech Accent (`font.numeric`):** "Orbitron", sans-serif (spec highlights, SKU call-outs)

### Type Scale (rem values)
| Role | Size | Line Height |
| --- | --- | --- |
| Display XL | 3.5rem | 1.05 |
| Display L | 2.75rem | 1.1 |
| H1 | 2.25rem | 1.15 |
| H2 | 1.75rem | 1.2 |
| H3 | 1.5rem | 1.3 |
| Body L | 1.125rem | 1.6 |
| Body M | 1rem | 1.65 |
| Small | 0.875rem | 1.6 |
| Caption | 0.75rem | 1.4 |

### Type Treatments
- Uppercase letter-spacing (`0.08em`) on hero headings for motorsport energy.
- Use numeric font only for spec modules or price highlights to avoid overload.
- Body copy maintains 60-70 character line width for blog readability.

## Imagery Treatment
- Convert hero imagery to high-contrast grade with blue highlight to tie into primary palette.
- Use carbon fiber or asphalt textures as subtle background overlays in dark sections.

## Iconography
- Outline icons with 2px stroke to match high-tech aesthetic.
- Provide hover states shifting from `#FFFFFF` to `#18B8D7` on dark backgrounds.

## CSS Authoring Rules
- Modularize styles by block, pattern, or component; avoid piling global declarations into a single file.
- Ensure the build pipeline outputs minified CSS assets for production.
- Split any stylesheet before it exceeds roughly 500–800 lines to keep files maintainable.

---
**Next:** Translate these tokens into `theme.json` once wireframes are approved.
