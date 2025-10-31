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
				<li><a href="<?php echo esc_url( home_url( '/about-us/#contact' ) ); ?>">Contact Us</a></li>
				<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
				<li><a href="https://shop.3wdistributing.com/shipping-and-returns-policy/">Shipping &amp; Returns</a></li>
				<li><a href="https://shop.3wdistributing.com/my-account/">My Account</a></li>
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
				<li><a href="https://www.instagram.com/3wdistributing/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
					</svg>
					<span>Instagram</span>
				</a></li>
				<li><a href="https://www.youtube.com/@3wdistributing" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
					</svg>
					<span>YouTube</span>
				</a></li>
				<li><a href="https://www.facebook.com/3wdistributing/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
					</svg>
					<span>Facebook</span>
				</a></li>
				<li><a href="https://twitter.com/3wdistributing" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
					</svg>
					<span>X</span>
				</a></li>
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
