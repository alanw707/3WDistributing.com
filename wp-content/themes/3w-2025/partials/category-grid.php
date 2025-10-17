<?php
/**
 * Category grid partial.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = [
	[
		'slug'        => 'lighting',
		'title'       => 'Lighting',
		'description' => 'LED headlamps, fog, halos',
		'link'        => '/shop/lighting',
		'cta'         => 'Shop lighting →',
	],
	[
		'slug'        => 'aero',
		'title'       => 'Aero & Body Kits',
		'description' => 'Splitters, diffusers, spoilers',
		'link'        => '/shop/aero',
		'cta'         => 'Shop aero →',
	],
	[
		'slug'        => 'performance',
		'title'       => 'Engine & Performance',
		'description' => 'Intakes, ECU, exhaust',
		'link'        => '/shop/performance',
		'cta'         => 'Shop performance →',
	],
	[
		'slug'        => 'wheels',
		'title'       => 'Wheels & Suspension',
		'description' => 'Forged wheels, coilovers',
		'link'        => '/shop/wheels',
		'cta'         => 'Shop wheels →',
	],
	[
		'slug'        => 'interior',
		'title'       => 'Interior & Tech',
		'description' => 'Carbon trim, digital clusters',
		'link'        => '/shop/interior',
		'cta'         => 'Shop interior →',
	],
	[
		'slug'        => 'clearance',
		'title'       => 'Clearance',
		'description' => 'Limited inventory deals',
		'link'        => '/shop/clearance',
		'cta'         => 'Shop clearance →',
	],
];
?>

<section class="threew-category-grid">
	<?php foreach ( $categories as $category ) : ?>
		<article class="threew-category-grid__tile threew-category-grid__tile--<?php echo esc_attr( $category['slug'] ); ?>">
			<a
				class="threew-category-grid__link"
				href="<?php echo esc_url( home_url( $category['link'] ) ); ?>"
				aria-label="<?php echo esc_attr( sprintf( 'Browse %s products', $category['title'] ) ); ?>"
			>
				<div class="threew-category-grid__content">
					<h3><?php echo esc_html( $category['title'] ); ?></h3>
					<p class="threew-category-grid__summary has-body-md-font-size">
						<?php echo esc_html( $category['description'] ); ?>
					</p>
					<span class="threew-category-grid__cta has-body-sm-font-size">
						<?php echo esc_html( $category['cta'] ); ?>
					</span>
				</div>
			</a>
		</article>
	<?php endforeach; ?>
</section>
