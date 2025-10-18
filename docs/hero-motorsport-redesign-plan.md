# Hero Section Motorsport Refresh Plan

> Source alignment: [theme-style-guide.md](theme-style-guide.md), [theme-mood-board.md](theme-mood-board.md), [theme-ux-patterns.md](theme-ux-patterns.md), [homepage-refresh-checklist.md](homepage-refresh-checklist.md)  
> Implementation tasks tracked in [hero-refresh-task-list.md](hero-refresh-task-list.md).

## Current State Audit
- Layout is balanced, but the trust column reads as a vertical stack without strong visual anchoring—icons previously created noise instead of reinforcing the message.
- Background treatment already applies a gradient and parallax photo, yet it lacks the track-inspired textures and lighting cues called out in the mood board.
- Supporting promises and fitment selector are functional, though there’s minimal motion feedback beyond hover states.
- Hero typography follows uppercase Rajdhani but could benefit from finer hierarchy to separate the lead badge, headline, and supporting copy.
- Fitment form width now matches grid, but interactive affordances (focus/step progression) still feel static compared with motorsport dashboards.

## Experience Goals
1. **Motorsport Atmosphere:** Introduce lighting stripes, subtle carbon fiber / asphalt overlays, and dynamic gradients (per *theme-mood-board.md*).
2. **Dynamic Interaction:** Layer in motion primitives—ambient background animation, selector load states, and micro-interactions—while keeping performance mindful (*theme-ux-patterns.md*, *homepage-refresh-checklist.md*).
3. **Clarity & Focus:** Maintain concise hero messaging and supporting trust points from the UX content plan; avoid overcrowding while adding energy (*theme-style-guide.md* typography guidance).

## Implementation Roadmap

### 1. Visual Systems (Foundations)
- Add a masked carbon fiber texture overlay blended at low opacity behind the vehicle to echo the style guide imagery direction.
- Introduce an angled cyan light streak (CSS pseudo-element) sweeping behind the fitment card—timed, subtle loop.
- Tighten vertical spacing above the headline and between supporting bullets so the hero reads more like a motorsport poster.

### 2. Fitment Selector Enhancements
- Convert select fields into “glass” pill controls with animated border glow on focus.
- Add progress indicator (Year → Trim) across the top of the selector using the Orbitron numeric token for counts.
- Implement lightweight entrance animation (opacity/slide) when the selector loads to reinforce response.

### 3. Trust & Support Lane
- Restructure trust lane into a compact horizontal band beneath the hero, using iconography sparingly (simple glyphs or text-only chips) with motion underline on hover.
- Surface a “Talk to a Fitment Specialist” CTA pill that pulses gently every ~15s for attention without being distracting.

### 4. Motion & Micro-Interactions
- Enhance background parallax keyframes with slight horizontal drift to simulate track-side camera pans.
- Add scroll-triggered fade/translate for supporting promises so they “activate” as the hero enters viewport.
- Evaluate performance budget; prefer CSS `@keyframes` and `prefers-reduced-motion` fallbacks to meet accessibility requirements (*homepage-refresh-checklist.md* QA tasks).

### 5. Content & Accessibility Checks
- Reconfirm SEO intro copy placement below hero remains intact and adjust hero copy length to stay within 60–70 characters per *homepage-refresh-checklist.md*.
- Audit color contrast after introducing new overlays; ensure buttons and text meet WCAG AA on dark gradients.
- Document changes in Claudedocs once implemented, linking to updated theme tokens if any adjustments occur.

## Dependencies & Next Steps
1. Prototype visual/animation tweaks in `src/styles/sections/hero.css` with feature flags to iterate quickly.
2. Coordinate with JavaScript fitment logic before altering markup to maintain selector functionality.
3. After implementation, rerun `npm run build`, smoke test at 1920px/1366px/768px/414px, and execute Playwright smoke for the homepage.

> **Verification:** This plan keeps the SEO copy and fitment-first concept intact while extending motorsport storytelling and motion per the repository design system. Completion of each phase should be logged back into `docs/homepage-refresh-checklist.md` to show progress toward the broader homepage refresh.
