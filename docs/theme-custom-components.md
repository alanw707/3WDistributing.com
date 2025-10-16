# Custom Blocks & Components — 3w-2025

| Component | Type | Purpose | Implementation Notes |
| --- | --- | --- | --- |
| Fitment Selector | Custom block (React) | Multi-step Year/Make/Model/Trim filter with API integration. | Use REST endpoint or external fitment API; provide inline validation and saved vehicle state. |
| Brand Carousel | Block variation / custom block | Sliding strip of partner logos with autoplay + manual controls. | Leverage Swiper.js or Splide packaged for WP. |
| Testimonial Slider | Block variation | Rotating testimonials with vehicle info and star rating. | Data via CPT `testimonials`; custom block reads from REST. |
| Spec Highlights | Block style | Horizontal list of spec capsules (HP gain, weight savings). | Variation of `group` block with theme style. |
| Bundle Showcase | Query loop variation | Display curated bundles (custom post type) with hero image + CTA. | Provide block pattern hooking into CPT query. |
| Support CTA | Reusable block | Inline chat/support card with icons and contact options. | Provide contact method toggles. |
| Blog Sticky TOC | Sidebar component | Auto-generates table of contents for long-form articles. | Use JS to parse headings; degrade gracefully.
| Mini Cart | Frontend component | Slide-out cart for quick view and quantity edits. | Extend WooCommerce block; ensure accessible focus trapping. |

Dependencies to evaluate: `@wordpress/create-block`, `@wordpress/scripts`, Swiper/Splide for sliders, WP data store for saved vehicle.
