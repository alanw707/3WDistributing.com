<?php
/**
 * WooCommerce product archive template.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hero_title       = function_exists( 'woocommerce_page_title' ) ? woocommerce_page_title( false ) : get_the_archive_title();
$hero_description = term_description();

$filter_wrapper_attrs = wp_json_encode(
	[
		'className' => 'threew-product-filters__controls',
	]
);

$filter_reset_attrs = wp_json_encode(
	[
		'heading'          => '',
		'buttonLabel'      => __( 'Reset filters', 'threew-2025' ),
		'showFilterButton' => false,
	]
);

$filter_fitment_attrs = wp_json_encode(
	[
		'heading'      => __( 'Fitment', 'threew-2025' ),
		'displayStyle' => 'list',
		'showCounts'   => false,
	]
);

$filter_brand_attrs = wp_json_encode(
	[
		'heading'      => __( 'Brand', 'threew-2025' ),
		'displayStyle' => 'list',
		'showCounts'   => false,
	]
);

$filter_price_attrs = wp_json_encode(
	[
		'heading' => __( 'Price', 'threew-2025' ),
	]
);

$filter_stock_attrs = wp_json_encode(
	[
		'heading' => __( 'Availability', 'threew-2025' ),
	]
);

$product_query_attrs = wp_json_encode(
	[
		'query'         => [
			'perPage' => 12,
			'pages'   => 0,
			'offset'  => 0,
			'postType' => 'product',
			'order'   => 'desc',
			'orderBy' => 'date',
			'author'  => '',
			'search'  => '',
			'exclude' => [],
			'sticky'  => '',
			'inherit' => true,
		],
		'displayLayout' => [
			'type'    => 'grid',
			'columns' => 3,
		],
	]
);

$no_products_copy = esc_html__( 'No products found. Adjust your filters or check back soon.', 'threew-2025' );

$product_collection_markup = sprintf(
	'<!-- wp:woocommerce/product-collection-notices /-->' .
	'<!-- wp:group {"className":"threew-product-grid__toolbar","layout":{"type":"flex","justifyContent":"space-between","alignItems":"center","flexWrap":"wrap"}} -->' .
	'<div class="wp-block-group threew-product-grid__toolbar">' .
	'<!-- wp:woocommerce/product-collection-results-count /-->' .
	'<!-- wp:woocommerce/product-collection-sort-select /-->' .
	'</div>' .
	'<!-- /wp:group -->' .
	'<!-- wp:woocommerce/product-collection-active-filters {"className":"threew-product-grid__active"} /-->' .
	'<!-- wp:query %1$s -->' .
	'<div class="wp-block-query">' .
	'<!-- wp:post-template -->' .
	'<!-- wp:group {"className":"threew-product-card","layout":{"type":"constrained"}} -->' .
	'<div class="wp-block-group threew-product-card">' .
	'<!-- wp:woocommerce/product-image {"isDescendentOfQueryLoop":true} /-->' .
	'<!-- wp:woocommerce/product-title {"level":4,"isDescendentOfQueryLoop":true} /-->' .
	'<!-- wp:woocommerce/product-rating {"isDescendentOfQueryLoop":true} /-->' .
	'<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->' .
	'<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->' .
	'</div>' .
	'<!-- /wp:group -->' .
	'<!-- /wp:post-template -->' .
	'<!-- wp:query-no-results -->' .
	'<!-- wp:paragraph --><p>%2$s</p><!-- /wp:paragraph -->' .
	'<!-- /wp:query-no-results -->' .
	'<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->' .
	'<!-- wp:query-pagination-previous /-->' .
	'<!-- wp:query-pagination-numbers /-->' .
	'<!-- wp:query-pagination-next /-->' .
	'<!-- /wp:query-pagination -->' .
	'</div>' .
	'<!-- /wp:query -->',
	$product_query_attrs,
	$no_products_copy
);
?>

<main class="threew-shop">
	<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
		<div class="threew-shop__breadcrumbs">
			<?php woocommerce_breadcrumb(); ?>
		</div>
	<?php endif; ?>

	<section class="threew-shop__hero">
		<h2 class="wp-block-heading"><?php echo esc_html( $hero_title ); ?></h2>

		<?php if ( $hero_description ) : ?>
			<div class="threew-shop__hero-excerpt">
				<?php echo wp_kses_post( wpautop( $hero_description ) ); ?>
			</div>
		<?php endif; ?>

		<p class="threew-shop__hero-note">
			<?php esc_html_e( 'Select filters to match your fitment and explore curated performance parts.', 'threew-2025' ); ?>
		</p>
	</section>

	<div class="threew-shop__layout">
		<aside class="threew-product-filters">
			<h3 class="wp-block-heading">
				<?php esc_html_e( 'Filter Products', 'threew-2025' ); ?>
			</h3>

			<p class="threew-product-filters__intro">
				<?php esc_html_e( 'Dial in your vehicle fitment, favorite brands, and availability to surface the right parts instantly.', 'threew-2025' ); ?>
			</p>

			<?php
			if ( function_exists( 'do_blocks' ) ) {
				$filters_markup  = '<!-- wp:woocommerce/filter-wrapper ' . $filter_wrapper_attrs . ' -->';
				$filters_markup .= '<div class="wp-block-woocommerce-filter-wrapper threew-product-filters__controls">';
				$filters_markup .= '<!-- wp:woocommerce/product-filter-reset ' . $filter_reset_attrs . ' /-->';
				$filters_markup .= '<!-- wp:woocommerce/product-filter-attribute ' . $filter_fitment_attrs . ' /-->';
				$filters_markup .= '<!-- wp:woocommerce/product-filter-attribute ' . $filter_brand_attrs . ' /-->';
				$filters_markup .= '<!-- wp:woocommerce/product-filter-price ' . $filter_price_attrs . ' /-->';
				$filters_markup .= '<!-- wp:woocommerce/product-filter-stock-status ' . $filter_stock_attrs . ' /-->';
				$filters_markup .= '</div>';
				$filters_markup .= '<!-- /wp:woocommerce/filter-wrapper -->';

				echo do_blocks( $filters_markup );
			}
			?>
		</aside>

		<div class="threew-product-grid">
			<?php
			if ( function_exists( 'do_blocks' ) ) {
				echo do_blocks( $product_collection_markup );
			} elseif ( function_exists( 'woocommerce_content' ) ) {
				woocommerce_content();
			}
			?>
		</div>
	</div>

	<section class="threew-shop__seo">
		<h3 class="wp-block-heading">
			<?php esc_html_e( 'Performance Parts Expertise', 'threew-2025' ); ?>
		</h3>
		<p>
			<?php esc_html_e( '3W Distributing partners with leading brands to deliver proven upgrades backed by verified fitment data. Update this copy with category-specific SEO content to help shoppers find relevant builds faster.', 'threew-2025' ); ?>
		</p>
	</section>

	<section class="threew-shop__related">
		<h4 class="wp-block-heading">
			<?php esc_html_e( 'Related Categories', 'threew-2025' ); ?>
		</h4>
		<ul>
			<li><a href="#"><?php esc_html_e( 'Lighting Upgrades', 'threew-2025' ); ?></a></li>
			<li><a href="#"><?php esc_html_e( 'Aero & Body Styling', 'threew-2025' ); ?></a></li>
			<li><a href="#"><?php esc_html_e( 'Track-Ready Suspension', 'threew-2025' ); ?></a></li>
		</ul>
	</section>
</main>

<?php
get_footer();
