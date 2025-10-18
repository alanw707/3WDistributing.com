<?php
/**
 * Theme header.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$utility_menu = wp_nav_menu(
	[
		'theme_location' => 'utility',
		'fallback_cb'    => false,
		'container'      => false,
		'menu_class'     => 'threew-header__utility-menu',
		'depth'          => 1,
		'echo'           => false,
	]
);
?>
<header class="threew-header" data-nav-open="false">
	<div class="threew-header__utility">
		<p class="threew-header__utility-text">
			Call <a href="tel:17024306622">702.430.6622</a> · Mon–Fri 8am–5pm PT
		</p>
		<?php if ( $utility_menu ) : ?>
		<nav class="threew-header__utility-nav threew-header__utility-nav--inline" aria-label="<?php esc_attr_e( 'Utility menu', 'threew-2025' ); ?>">
			<?php echo $utility_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</nav>
		<?php endif; ?>
	</div>

	<div class="threew-header__primary">
		<div class="threew-header__primary-inner">
			<div class="threew-header__brand-line">
				<div class="threew-header__branding">
					<?php
					if ( has_custom_logo() ) {
						the_custom_logo();
					} else {
						printf(
							'<a class="threew-header__site-title" href="%1$s">%2$s</a>',
							esc_url( home_url( '/' ) ),
							esc_html( get_bloginfo( 'name' ) )
						);
					}
					?>
				</div>
				<button class="threew-header__toggle" type="button" aria-expanded="false" aria-controls="threew-header-drawer">
					<span class="threew-header__toggle-icon" aria-hidden="true">
						<span></span>
						<span></span>
						<span></span>
					</span>
					<span class="threew-header__toggle-label"><?php esc_html_e( 'Menu', 'threew-2025' ); ?></span>
				</button>
			</div>

			<div class="threew-header__drawer" id="threew-header-drawer">
				<nav class="threew-header__nav" aria-label="<?php esc_attr_e( 'Primary menu', 'threew-2025' ); ?>">
					<?php
					wp_nav_menu(
						[
							'theme_location' => 'primary',
							'fallback_cb'    => false,
							'container'      => false,
							'menu_class'     => 'threew-header__menu',
							'depth'          => 2,
						]
					);
					?>
				</nav>

				<form role="search" method="get" class="wp-block-search wp-block-search__button-inside threew-header__search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<label class="wp-block-search__label screen-reader-text" for="threew-header-search">
						<?php esc_html_e( 'Search for:', 'threew-2025' ); ?>
					</label>
					<div class="wp-block-search__inside-wrapper">
						<input
							id="threew-header-search"
							class="wp-block-search__input"
							type="search"
							name="s"
							placeholder="<?php esc_attr_e( 'Search for products', 'threew-2025' ); ?>"
							aria-label="<?php esc_attr_e( 'Search products', 'threew-2025' ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
						/>
						<button type="submit" class="wp-block-search__button" aria-label="<?php esc_attr_e( 'Search', 'threew-2025' ); ?>">
							<?php esc_html_e( 'Search', 'threew-2025' ); ?>
						</button>
					</div>
				</form>

				<div class="threew-header__actions">
					<a class="threew-header__dealer-link" href="<?php echo esc_url( home_url( '/dealer' ) ); ?>">
						<?php esc_html_e( 'Dealer Portal', 'threew-2025' ); ?>
					</a>
					<a class="wp-block-button__link wp-element-button is-style-outline" href="<?php echo esc_url( home_url( '/wishlist' ) ); ?>">
						<?php esc_html_e( 'Wishlist', 'threew-2025' ); ?>
					</a>
					<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/cart' ) ); ?>">
						<?php esc_html_e( 'Cart', 'threew-2025' ); ?>
					</a>
				</div>

				<?php if ( $utility_menu ) : ?>
				<nav class="threew-header__utility-nav threew-header__utility-nav--drawer" aria-label="<?php esc_attr_e( 'Utility menu', 'threew-2025' ); ?>">
					<?php echo $utility_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</nav>
				<?php endif; ?>
			</div>
		</div>
		<div class="threew-header__overlay" aria-hidden="true"></div>
	</div>
</header>
