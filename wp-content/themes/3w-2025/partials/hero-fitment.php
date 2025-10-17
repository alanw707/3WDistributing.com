<?php
/**
 * Hero Fitment partial.
 *
 * Renders the full-bleed hero with fitment selector and metrics.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="threew-hero threew-hero--fullbleed alignfull" aria-labelledby="threew-hero-title">
	<div class="threew-hero__background" aria-hidden="true">
		<span class="threew-hero__background-grid"></span>
		<span class="threew-hero__background-glow threew-hero__background-glow--left"></span>
		<span class="threew-hero__background-glow threew-hero__background-glow--right"></span>
		<span class="threew-hero__background-orb"></span>
		<span class="threew-hero__background-streak threew-hero__background-streak--one"></span>
		<span class="threew-hero__background-streak threew-hero__background-streak--two"></span>
		<span class="threew-hero__background-trace"></span>
	</div>

	<div class="threew-hero__inner">
		<div class="threew-hero__content">
			<div class="threew-hero__badge">
				<svg class="threew-hero__badge-icon" viewBox="0 0 32 32" role="img" aria-hidden="true">
					<path d="M4 20h24M8 12h18M12 4h12" />
				</svg>
				<span><?php esc_html_e( 'Track-Proven Since 2014', 'threew-2025' ); ?></span>
			</div>

			<p class="threew-hero__eyebrow has-secondary-color has-text-color has-body-sm-font-size">
				<?php esc_html_e( 'Motorsport-grade components', 'threew-2025' ); ?>
			</p>

			<h1 id="threew-hero-title" class="has-surface-alt-color has-text-color has-display-xl-font-size">
				<?php esc_html_e( 'Unlock Performance for Your Vehicle', 'threew-2025' ); ?>
			</h1>

			<p class="has-surface-alt-color has-text-color has-body-lg-font-size">
				<?php esc_html_e( 'Dial in your setup with curated lighting, aero, and tuning upgrades engineered for a perfect fit.', 'threew-2025' ); ?>
			</p>

			<div class="threew-hero__actions">
				<div class="wp-block-button is-style-fill">
					<a class="wp-block-button__link wp-element-button threew-hero__cta" href="#threew-fitment-selector">
						<span class="threew-hero__cta-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24">
								<path d="M4 12h16" />
								<path d="M14 6l6 6-6 6" />
							</svg>
						</span>
						<?php esc_html_e( 'Select your vehicle', 'threew-2025' ); ?>
					</a>
				</div>
				<div class="wp-block-button is-style-outline">
					<a class="wp-block-button__link wp-element-button threew-hero__cta" href="<?php echo esc_url( home_url( '/media/dyno-library' ) ); ?>">
						<span class="threew-hero__cta-icon" aria-hidden="true">
							<svg viewBox="0 0 24 24">
								<path d="M8 5v14l11-7z" />
							</svg>
						</span>
						<?php esc_html_e( 'Watch dyno runs', 'threew-2025' ); ?>
					</a>
				</div>
			</div>

			<div id="threew-fitment-selector" class="threew-hero__fitment">
				<?php
				// Render the fitment selector using our custom rendering function
				if ( function_exists( 'ThreeW\Fitment\render_fitment_selector' ) ) {
					echo ThreeW\Fitment\render_fitment_selector(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					// Fallback placeholder if function doesn't exist
					?>
					<div class="threew-fitment-placeholder">
						<div class="threew-fitment-placeholder__meta">
							<span class="threew-fitment-placeholder__eyebrow">
								<?php esc_html_e( 'Fitment selector coming online', 'threew-2025' ); ?>
							</span>
							<p>
								<?php esc_html_e( 'We\'re calibrating the selector. In the meantime, reach out with your build sheet and our advisors will match components in 24 hours.', 'threew-2025' ); ?>
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

			<div class="threew-hero__metrics">
				<div class="threew-hero__metric">
					<span class="threew-hero__metric-icon" aria-hidden="true">
						<svg viewBox="0 0 28 28">
							<path d="M4 18h6l3 4 3-4h8" />
							<path d="M6 10h12l4 6" />
						</svg>
					</span>
					<p class="threew-hero__metric-value has-surface-alt-color has-text-color">1,200+</p>
					<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
						<?php esc_html_e( 'Verified fitments', 'threew-2025' ); ?>
					</p>
				</div>
				<div class="threew-hero__metric">
					<span class="threew-hero__metric-icon" aria-hidden="true">
						<svg viewBox="0 0 28 28">
							<circle cx="14" cy="14" r="9" />
							<path d="M14 7v7l4 2" />
						</svg>
					</span>
					<p class="threew-hero__metric-value has-surface-alt-color has-text-color">48 hr</p>
					<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
						<?php esc_html_e( 'Average processing', 'threew-2025' ); ?>
					</p>
				</div>
				<div class="threew-hero__metric">
					<span class="threew-hero__metric-icon" aria-hidden="true">
						<svg viewBox="0 0 28 28">
							<path d="M6 21V9l8-4 8 4v12l-8 4z" />
							<path d="M6 9l8 5 8-5" />
						</svg>
					</span>
					<p class="threew-hero__metric-value has-surface-alt-color has-text-color">80+</p>
					<p class="threew-hero__metric-label has-surface-alt-color has-text-color has-body-sm-font-size">
						<?php esc_html_e( 'Global brands stocked', 'threew-2025' ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</section>
