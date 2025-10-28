<?php
/**
 * Theme footer.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="threew-footer">
	<div class="threew-footer__top">
		<div class="threew-footer__col threew-footer__col--about">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				printf(
					'<a class="threew-footer__site-title" href="%1$s">%2$s</a>',
					esc_url( home_url( '/' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
			}
			?>
			<p class="threew-footer__about">
				Performance parts, lighting, and bespoke kits for premium builds. Trusted distributor for global tuning brands.
			</p>
		</div>

		<div class="threew-footer__col">
			<h6>Shop</h6>
			<ul>
				<li><a href="<?php echo esc_url( threew_2025_get_shop_url( 'lighting' ) ); ?>">Lighting</a></li>
				<li><a href="<?php echo esc_url( threew_2025_get_shop_url( 'aero' ) ); ?>">Aero &amp; Body Kits</a></li>
				<li><a href="<?php echo esc_url( threew_2025_get_shop_url( 'wheels' ) ); ?>">Wheels &amp; Suspension</a></li>
				<li><a href="<?php echo esc_url( threew_2025_get_shop_url( 'performance' ) ); ?>">Engine &amp; Performance</a></li>
			</ul>
		</div>

		<div class="threew-footer__col">
			<h6>Support</h6>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact &amp; Tickets</a></li>
				<li><a href="<?php echo esc_url( home_url( '/resources/install-guides' ) ); ?>">Install Guides</a></li>
				<li><a href="<?php echo esc_url( home_url( '/support/shipping-returns' ) ); ?>">Shipping &amp; Returns</a></li>
				<li><a href="<?php echo esc_url( home_url( '/support/warranty' ) ); ?>">Warranty</a></li>
			</ul>
		</div>

		<div class="threew-footer__col">
			<h6>Connect</h6>
			<p>
				702.430.6622<br>
				<a href="mailto:info@3wdistributing.com">info@3wdistributing.com</a><br>
				Mon–Fri 8am–5pm PT
			</p>
			<ul class="threew-footer__socials">
				<li><a href="https://www.instagram.com/" aria-label="Instagram">Instagram</a></li>
				<li><a href="https://www.youtube.com/" aria-label="YouTube">YouTube</a></li>
				<li><a href="https://www.facebook.com/" aria-label="Facebook">Facebook</a></li>
			</ul>
		</div>
	</div>

	<div class="threew-footer__bottom">
		<p>© <?php echo esc_html( date_i18n( 'Y' ) ); ?> 3W Distributing LLC. All rights reserved.</p>
		<p>
			<a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>">Privacy</a>
			·
			<a href="<?php echo esc_url( home_url( '/terms' ) ); ?>">Terms</a>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
