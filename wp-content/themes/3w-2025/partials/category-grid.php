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
		'links'       => [
			[
				'label'  => 'Shop lighting →',
				'search' => 'lighting',
			],
		],
	],
	[
		'slug'        => 'aero',
		'title'       => 'Aero & Body Kits',
		'description' => 'Splitters, diffusers, spoilers',
		'links'       => [
			[
				'label'  => 'Shop aero →',
				'search' => 'aero',
			],
		],
	],
	[
		'slug'        => 'performance',
		'title'       => 'Engine & Performance',
		'description' => 'Intakes, ECU, exhaust',
		'links'       => [
			[
				'label'  => 'Shop performance →',
				'search' => 'performance',
			],
		],
	],
	[
		'slug'        => 'wheels',
		'title'       => 'Wheels & Suspension',
		'description' => 'Forged wheels, coilovers',
		'links'       => [
			[
				'label'  => 'Shop wheels →',
				'search' => 'wheels',
			],
			[
				'label'  => 'Shop suspension →',
				'search' => 'suspension',
			],
		],
	],
	[
		'slug'        => 'interior',
		'title'       => 'Interior & Tech',
		'description' => 'Carbon trim, digital clusters',
		'links'       => [
			[
				'label'  => 'Shop interior →',
				'search' => 'interior',
			],
		],
	],
	[
		'slug'        => 'clearance',
		'title'       => 'Clearance',
		'description' => 'Limited inventory deals',
		'links'       => [
			[
				'label'  => 'Shop clearance →',
				'search' => 'clearance',
			],
		],
	],
];
?>

<section class="threew-category-grid">
	<?php foreach ( $categories as $category ) : ?>
		<?php
		$links = $category['links'] ?? [];
		if ( empty( $links ) ) {
			continue;
		}
		?>
		<article class="threew-category-grid__tile threew-category-grid__tile--<?php echo esc_attr( $category['slug'] ); ?>">
			<div class="threew-category-grid__inner">
				<h3><?php echo esc_html( $category['title'] ); ?></h3>
				<p class="threew-category-grid__summary has-body-md-font-size">
					<?php echo esc_html( $category['description'] ); ?>
				</p>
				<div class="threew-category-grid__cta-group">
					<?php foreach ( $links as $link ) :
						$href = threew_2025_get_shop_url(
							$link['search'] ?? '',
							$link['query_args'] ?? [],
							$link['path'] ?? ''
						);
						?>
						<a class="threew-category-grid__cta has-body-sm-font-size" href="<?php echo esc_url( $href ); ?>">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</article>
	<?php endforeach; ?>
</section>
