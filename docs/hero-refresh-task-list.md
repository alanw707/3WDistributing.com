# Hero Refresh Task List

> Derived from the strategic notes in [hero-motorsport-redesign-plan.md](hero-motorsport-redesign-plan.md) and outstanding items in [homepage-refresh-checklist.md](homepage-refresh-checklist.md). Use this as the single source of truth for day-to-day hero implementation work.

## Completed Baseline
- [x] Rebalanced hero grid widths and spacing across breakpoints.
- [x] Embedded the production fitment selector block and verified cascade/localStorage.
- [x] Integrated motorsport hero photography with parallax/particle animation.
- [x] Added the advanced search shortcut beside the primary CTA.
- [x] Cleared the legacy vertical trust badges to make room for the new lane redesign.

## Visual & Layout Enhancements
- [x] Add a low-opacity carbon fiber or asphalt texture overlay behind the hero vehicle.
- [x] Introduce an angled cyan light streak or light-bar element sweeping behind the fitment card (CSS pseudo-element animation).
- [x] Tighten vertical spacing between badge, headline, lede, and assurance pills for a motorsport poster feel.

## Fitment Selector Enhancements
- [x] Restyle select controls as glass-morphism pills with animated border glow on focus/hover.
- [x] Add a progress indicator (Year → Trim) across the selector header using the Orbitron numeric token.
- [x] Animate selector entrance/state changes (opacity/translate) while providing `prefers-reduced-motion` fallbacks.

## Trust & Support Lane
- [x] Rebuild the trust lane as a single horizontal band beneath the hero, aligning copy with UX requirements (shipping speed, fitment guarantee, concierge contact).
- [x] Add a “Talk to a Fitment Specialist” CTA pill with a gentle pulse every ~15s that respects `prefers-reduced-motion`.
- [x] Retain quick access to the advanced part-number search within the redesigned lane.

## Typography & Content
- [x] Apply the Orbitron numeric typography token to hero stats and confirm copy stays within 60–70 character widths.
- [x] Validate headline and lede content against SEO brief and update messaging if needed once layout locks.

## Motion & Micro-Interactions
- [x] Extend existing parallax keyframes with subtle horizontal drift to mimic track-side pans.
- [x] Add scroll-triggered reveal animations for the supporting assurance pills.
- [x] Ensure all new animations provide keyboard focus states and reduced-motion alternatives.

## QA & Documentation
- [ ] Run an accessibility audit (axe, keyboard navigation, screen reader checks, skip link).
- [ ] Confirm 44px touch targets and WCAG AA contrast across hero interactions.
- [ ] Execute responsive QA at 1920, 1440, 1366, 1024, 768, 414 widths for hero + selector.
- [ ] Capture before/after screenshots or screen recordings to document animation updates.
- [ ] Update block pattern documentation/theme tokens if design tweaks alter the system palette or typography.
- [ ] Share a summary of changes and the checklist status with stakeholders for sign-off.
- [ ] After implementation, run `npm run build` and `npx playwright test` (homepage smoke) before release.

## Implementation Notes
- Prototype CSS in `src/styles/sections/hero.css` with temporary flags while iterating.
- Coordinate JavaScript changes with the fitment selector module before altering markup.
- Log progress back into [homepage-refresh-checklist.md](homepage-refresh-checklist.md) as modules beyond the hero begin development.
