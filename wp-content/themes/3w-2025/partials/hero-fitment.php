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
				<div class="threew-hero__badge">
					<svg class="threew-hero__badge-icon" viewBox="0 0 32 32" role="img" aria-hidden="true">
						<path d="M4 20h24" />
						<path d="M8 12h18" />
						<path d="M12 4h12" />
					</svg>
					<span><?php esc_html_e( 'Track-proven since 2014', 'threew-2025' ); ?></span>
				</div>

					<p class="threew-hero__eyebrow has-secondary-color has-text-color has-body-sm-font-size">
						<?php esc_html_e( 'Luxury aftermarket specialists', 'threew-2025' ); ?>
					</p>

					<h1 id="threew-hero-title" class="has-surface-alt-color has-text-color has-display-xl-font-size">
						<?php esc_html_e( 'Elevate your vehicle with curated upgrades', 'threew-2025' ); ?>
					</h1>

					<p class="threew-hero__lede has-surface-alt-color has-text-color has-body-lg-font-size">
						<?php esc_html_e( 'Discover track-proven aero, wheels, exhaust, and interior enhancements tailored to coveted marques.', 'threew-2025' ); ?>
					</p>

					<div class="threew-hero__supporting" aria-label="<?php esc_attr_e( '3W hero supporting promises', 'threew-2025' ); ?>">
						<div class="threew-hero__supporting-item" data-hero-support>
							<span class="threew-hero__supporting-icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 12h16" />
									<path d="M9 7l5 5-5 5" />
								</svg>
							</span>
							<div class="threew-hero__supporting-copy">
								<p class="threew-hero__supporting-title">
									<?php esc_html_e( 'Concierge spec verification', 'threew-2025' ); ?>
								</p>
								<p class="threew-hero__supporting-sub">
									<?php esc_html_e( 'Master technicians confirm compatibility before parts leave our warehouse.', 'threew-2025' ); ?>
								</p>
							</div>
						</div>
						<div class="threew-hero__supporting-item" data-hero-support>
							<span class="threew-hero__supporting-icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
									<rect x="3" y="3" width="7" height="7" rx="1.5" />
									<rect x="14" y="3" width="7" height="7" rx="1.5" />
									<rect x="3" y="14" width="7" height="7" rx="1.5" />
							</svg>
							</span>
							<div class="threew-hero__supporting-copy">
								<p class="threew-hero__supporting-title">
									<?php esc_html_e( 'Curated performance catalog', 'threew-2025' ); ?>
								</p>
								<p class="threew-hero__supporting-sub">
									<?php esc_html_e( 'Hand-selected aero, forged wheels, exhaust, and interior upgrades for late-model exotics.', 'threew-2025' ); ?>
								</p>
							</div>
						</div>
						<div class="threew-hero__supporting-item" data-hero-support>
							<span class="threew-hero__supporting-icon" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
									<path d="M4 4h16v16H4z" />
									<path d="M8 4v4h8V4" />
									<path d="M8 14h.01" />
								<path d="M12 14h.01" />
								<path d="M16 14h.01" />
							</svg>
							</span>
							<div class="threew-hero__supporting-copy">
								<p class="threew-hero__supporting-title">
									<?php esc_html_e( 'Garage profiles & build history', 'threew-2025' ); ?>
								</p>
								<p class="threew-hero__supporting-sub">
									<?php esc_html_e( 'Save vehicle details and revisit curated configurations anytime.', 'threew-2025' ); ?>
								</p>
							</div>
						</div>
					</div>

				<div class="threew-hero__actions">
					<a class="threew-hero__cta-btn" href="<?php echo esc_url( home_url( '/shop' ) ); ?>">
						<?php esc_html_e( 'Browse performance catalog', 'threew-2025' ); ?>
					</a>
				</div>
			</div>

			<div class="threew-hero__panel">
				<div class="threew-fitment-card" aria-label="<?php esc_attr_e( 'Vehicle fitment selector', 'threew-2025' ); ?>">
					<div class="threew-fitment-card__body">
						<?php
						if ( function_exists( 'ThreeW\Fitment\render_fitment_selector' ) ) {
							echo ThreeW\Fitment\render_fitment_selector(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							?>
							<div class="threew-fitment-placeholder">
								<div class="threew-fitment-placeholder__meta">
									<span class="threew-fitment-placeholder__eyebrow">
										<?php esc_html_e( 'Fitment selector coming online', 'threew-2025' ); ?>
									</span>
									<p>
										<?php esc_html_e( 'We\'re calibrating the selector. Send your build sheet and our advisors will match components in under 24 hours.', 'threew-2025' ); ?>
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

					<ul class="threew-fitment-card__meta" aria-label="<?php esc_attr_e( 'Fitment quick facts', 'threew-2025' ); ?>">
						<li>
							<strong>1,200+</strong>
							<span><?php esc_html_e( 'Verified combinations ready to ship', 'threew-2025' ); ?></span>
						</li>
						<li>
							<strong>48 hr</strong>
							<span><?php esc_html_e( 'Average processing time before dispatch', 'threew-2025' ); ?></span>
						</li>
						<li>
							<strong>80+</strong>
							<span><?php esc_html_e( 'Performance partners on the line card', 'threew-2025' ); ?></span>
						</li>
					</ul>

					<ul class="threew-fitment-inline-assurances" aria-label="<?php esc_attr_e( 'Service highlights', 'threew-2025' ); ?>">
						<li>
							<span class="threew-fitment-inline-title"><?php esc_html_e( '48 hr dispatch', 'threew-2025' ); ?></span>
							<span class="threew-fitment-inline-sub"><?php esc_html_e( 'Average processing window', 'threew-2025' ); ?></span>
						</li>
						<li>
							<span class="threew-fitment-inline-title"><?php esc_html_e( 'Compatibility assured', 'threew-2025' ); ?></span>
							<span class="threew-fitment-inline-sub"><?php esc_html_e( 'Verified against factory data', 'threew-2025' ); ?></span>
						</li>
						<li>
							<span class="threew-fitment-inline-title"><?php esc_html_e( 'Concierge advisors', 'threew-2025' ); ?></span>
							<span class="threew-fitment-inline-sub"><?php esc_html_e( 'Direct line to our technicians', 'threew-2025' ); ?></span>
						</li>
					</ul>

					<footer class="threew-fitment-card__footer">
						<a class="threew-fitment-card__link" href="<?php echo esc_url( home_url( '/shop?search=part-number' ) ); ?>">
							<?php esc_html_e( 'Jump to part-number search', 'threew-2025' ); ?>
						</a>
					</footer>
				</div>
			</div>
		</div>
	</div>

	<div class="threew-hero__trust-lane" role="list" aria-label="<?php esc_attr_e( 'Fitment trust commitments', 'threew-2025' ); ?>">
</section>
