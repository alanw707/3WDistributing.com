<?php
/**
 * Focused SEO remediation for the site's highest-visibility BRABUS guides.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the selected guide metadata keyed by post slug.
 *
 * @return array<string, array{title: string, description: string}>
 */
function threew_2025_get_price_guide_seo() {
	return [
		'price-of-g-wagon-brabus' => [
			'title'       => '2026 BRABUS G-Wagon Price: G63, 700, 800 & 900 Costs',
			'description' => 'Compare 2026 BRABUS G-Wagon prices for the G63, 700, 800 and 900, plus upgrade costs and guidance from 3W Distributing.',
		],
		'g63-brabus-price' => [
			'title'       => '2026 Mercedes-AMG G63 BRABUS Price & Upgrade Guide',
			'description' => 'See 2026 Mercedes-AMG G63 BRABUS pricing, upgrade options and genuine parts guidance from 3W Distributing, a premier North American BRABUS dealer.',
		],
	];
}

/**
 * Consolidate overlapping price articles into two authoritative hubs.
 */
add_action( 'template_redirect', function () {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$redirects = [
		'brabus-g-wagon-price'          => '/price-of-g-wagon-brabus/',
		'brabus-price'                  => '/price-of-g-wagon-brabus/',
		'mercedes-benz-g63-brabus-price' => '/g63-brabus-price/',
	];
	$slug      = get_post_field( 'post_name', get_queried_object_id() );

	if ( isset( $redirects[ $slug ] ) ) {
		wp_safe_redirect( home_url( $redirects[ $slug ] ), 301, 'ThreeW SEO Consolidation' );
		exit;
	}
}, 1 );

/**
 * Keep document, Open Graph and visible article titles aligned.
 */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ! is_singular( 'post' ) ) {
		return $title;
	}

	$map  = threew_2025_get_price_guide_seo();
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	return isset( $map[ $slug ] ) ? $map[ $slug ]['title'] . ' - ' . get_bloginfo( 'name' ) : $title;
}, 20 );

add_filter( 'the_title', function ( $title, $post_id ) {
	if ( ! is_singular( 'post' ) || (int) $post_id !== get_queried_object_id() ) {
		return $title;
	}

	$map  = threew_2025_get_price_guide_seo();
	$slug = get_post_field( 'post_name', $post_id );
	return isset( $map[ $slug ] ) ? $map[ $slug ]['title'] : $title;
}, 20, 2 );

add_filter( 'get_the_excerpt', function ( $excerpt, $post ) {
	$map  = threew_2025_get_price_guide_seo();
	$slug = $post instanceof WP_Post ? $post->post_name : '';
	return isset( $map[ $slug ] ) ? $map[ $slug ]['description'] : $excerpt;
}, 20, 2 );

/**
 * Add a direct, measurable commercial path without changing editorial copy.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$map  = threew_2025_get_price_guide_seo();
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	if ( ! isset( $map[ $slug ] ) ) {
		return $content;
	}

	$campaign = rawurlencode( str_replace( '-', '_', $slug ) );
	$category = add_query_arg(
		[
			'utm_source'   => '3w_main',
			'utm_medium'   => 'editorial',
			'utm_campaign' => $campaign,
		],
		'https://shop.3wdistributing.com/product-category/brabus/'
	);
	$contact = add_query_arg(
		[
			'utm_source'   => '3w_main',
			'utm_medium'   => 'editorial',
			'utm_campaign' => $campaign,
		],
		'https://shop.3wdistributing.com/contact-us/'
	);

	$cta  = '<aside class="threew-commercial-cta" data-threew-commercial-cta aria-label="BRABUS parts and fitment assistance">';
	$cta .= '<h2>' . esc_html__( 'Ready to build your G-Wagon?', 'threew-2025' ) . '</h2>';
	$cta .= '<p>' . esc_html__( 'Browse genuine BRABUS upgrades or contact 3W Distributing for pricing, fitment and availability help before ordering.', 'threew-2025' ) . '</p>';
	$cta .= '<div class="threew-commercial-cta__actions">';
	$cta .= '<a href="' . esc_url( $category ) . '">' . esc_html__( 'Shop BRABUS upgrades', 'threew-2025' ) . '</a>';
	$cta .= '<a href="' . esc_url( $contact ) . '">' . esc_html__( 'Request fitment help', 'threew-2025' ) . '</a>';
	$cta .= '<a href="tel:+17024306622">' . esc_html__( 'Call (702) 430-6622', 'threew-2025' ) . '</a>';
	$cta .= '</div></aside>';

	return $content . $cta;
}, 30 );
