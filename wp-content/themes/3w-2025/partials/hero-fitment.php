<?php
/**
 * Hero Fitment partial.
 *
 * Renders the full-bleed hero with fitment selector and supporting assurances.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="threew-hero threew-hero--fullbleed alignwide" aria-labelledby="threew-hero-title">
	<div class="threew-hero__background" aria-hidden="true">
		<span class="threew-hero__background-photo"></span>
		<span class="threew-hero__background-texture"></span>
		<span class="threew-hero__background-grid"></span>
		<span class="threew-hero__background-glow threew-hero__background-glow--left"></span>
		<span class="threew-hero__background-glow threew-hero__background-glow--right"></span>
		<span class="threew-hero__background-orb"></span>
		<span class="threew-hero__background-streak threew-hero__background-streak--one"></span>
		<span class="threew-hero__background-streak threew-hero__background-streak--two"></span>
		<span class="threew-hero__background-trace"></span>
	</div>

	<div class="threew-hero__inner">
		<div class="threew-hero__layout">
			<div class="threew-hero__content">
				<p class="threew-hero__eyebrow has-secondary-color has-text-color has-body-sm-font-size">
					<?php esc_html_e( 'Precision aftermarket specialists', 'threew-2025' ); ?>
				</p>

				<h1 id="threew-hero-title" class="has-surface-alt-color has-text-color has-display-xl-font-size">
					<?php esc_html_e( 'Elevate your vehicle with curated upgrades', 'threew-2025' ); ?>
				</h1>

				<p class="threew-hero__lede has-surface-alt-color has-text-color has-body-lg-font-size">
					<?php esc_html_e( 'Discover track-proven aero, wheels, exhaust, and interior enhancements tailored to coveted marques.', 'threew-2025' ); ?>
				</p>

				<div class="threew-hero__actions">
					<a class="wp-block-button__link wp-element-button" href="#threew-fitment-selector">
						<span class="threew-hero__cta">
							<span class="threew-hero__cta-icon" aria-hidden="true">
								<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 10h12" />
									<path d="M11 6l5 4-5 4" />
								</svg>
							</span>
							<span><?php esc_html_e( 'Find Parts', 'threew-2025' ); ?></span>
						</span>
					</a>
					<a class="wp-block-button__link wp-element-button is-style-outline" href="<?php echo esc_url( threew_2025_get_shop_url() ); ?>">
						<?php esc_html_e( 'Browse Products', 'threew-2025' ); ?>
					</a>
				</div>

				<div class="threew-hero__metrics" aria-label="<?php esc_attr_e( '3W performance highlights', 'threew-2025' ); ?>">
					<div class="threew-hero__metric">
						<span class="threew-hero__metric-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
								<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
								<polyline points="22 4 12 14.01 9 11.01" />
							</svg>
						</span>
						<div class="threew-hero__metric-copy">
							<p class="threew-hero__metric-value has-surface-alt-color has-text-color">
								<?php esc_html_e( '1,200+', 'threew-2025' ); ?>
							</p>
							<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
								<?php esc_html_e( 'Ready-to-ship combos', 'threew-2025' ); ?>
							</p>
						</div>
					</div>
					<div class="threew-hero__metric">
						<span class="threew-hero__metric-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
								<circle cx="12" cy="12" r="10" />
								<polyline points="12 6 12 12 16 14" />
							</svg>
						</span>
						<div class="threew-hero__metric-copy">
							<p class="threew-hero__metric-value has-surface-alt-color has-text-color">
								<?php esc_html_e( '48 hr', 'threew-2025' ); ?>
							</p>
							<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
								<?php esc_html_e( 'Avg dispatch window', 'threew-2025' ); ?>
							</p>
						</div>
					</div>
					<div class="threew-hero__metric">
						<span class="threew-hero__metric-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
								<line x1="16.5" y1="9.4" x2="7.5" y2="4.21" />
								<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
								<polyline points="3.27 6.96 12 12.01 20.73 6.96" />
							</svg>
						</span>
						<div class="threew-hero__metric-copy">
							<p class="threew-hero__metric-value has-surface-alt-color has-text-color">
								<?php esc_html_e( '80+', 'threew-2025' ); ?>
							</p>
							<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
								<?php esc_html_e( 'Global brands onboard', 'threew-2025' ); ?>
							</p>
						</div>
					</div>
				</div>
			</div>

		<div class="threew-hero__panel">
			<div class="threew-hero__panel-shell" aria-label="<?php esc_attr_e( 'Vehicle fitment selector', 'threew-2025' ); ?>">
				<div id="threew-fitment-selector" class="threew-hero__panel-body">
					<?php
					if ( function_exists( 'ThreeW\Fitment\render_fitment_selector' ) ) {
							echo ThreeW\Fitment\render_fitment_selector(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							?>
							<div class="threew-fitment-placeholder">
								<div class="threew-fitment-placeholder__meta">
									<span class="threew-fitment-placeholder__eyebrow">
										<?php esc_html_e( 'Fitment selector demo', 'threew-2025' ); ?>
									</span>
									<p>
										<?php esc_html_e( 'Send your build sheet and our advisors will confirm fitment within 24 hours.', 'threew-2025' ); ?>
									</p>
								</div>
								<a class="threew-fitment-placeholder__cta" href="mailto:support@3wdistributing.com">
									<?php esc_html_e( 'Talk to a specialist →', 'threew-2025' ); ?>
								</a>
							</div>
							<?php
						}
						?>
					</div>

				</div>
			</div>
		</div>
	</div>
</section>
