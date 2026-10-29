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
 * Purge stale full-page cache once when this remediation version ships.
 */
add_action( 'init', function () {
	$cache_version = '2026-10-29-9';
	if ( get_option( 'threew_seo_remediation_cache_version' ) === $cache_version ) {
		return;
	}

	// This release changes head tags on every page, not just the guides, so the
	// whole cache has to be invalidated once. Later releases can purge narrowly.
	if ( class_exists( 'LiteSpeed_Cache_API' ) ) {
		LiteSpeed_Cache_API::purge_all();
	} else {
		do_action( 'litespeed_purge_all' );
	}

	update_option( 'threew_seo_remediation_cache_version', $cache_version, false );
}, 1 );

/**
 * Return the selected guide metadata keyed by post slug.
 *
 * @return array<string, array{title: string, description: string}>
 */
function threew_2025_get_price_guide_seo() {
	return [
		'price-of-g-wagon-brabus' => [
			'title'       => 'BRABUS G-Wagon Price Guide: G63, 700, 800 & 900',
			'description' => 'Explore BRABUS G-Wagon vehicle price estimates and G63, 700, 800 and 900 upgrades. Contact 3W Distributing for current parts pricing and fitment.',
		],
		'g63-brabus-price' => [
			'title'       => 'Mercedes-AMG G63 BRABUS Price & Upgrade Guide',
			'description' => 'Explore Mercedes-AMG G63 BRABUS vehicle price estimates and upgrade options. Browse genuine G-Wagon parts or ask 3W Distributing for current pricing and fitment.',
		],
	];
}

/**
 * Contextual product links for the price guides.
 *
 * Anchors are phrases that already appear in the article copy, so linking them
 * adds a commercial path without rewriting editorial content.
 *
 * @return array<string, array<int, array{anchor: string, path: string, utm_content: string, occurrence?: int}>>
 */
function threew_2025_get_price_guide_product_links() {
	return [
		'price-of-g-wagon-brabus' => [
			[
				'anchor'      => 'illuminated logos',
				'path'        => '/product/brabus-w465-illuminated-grille-emblem/',
				'utm_content' => 'illuminated_grille_emblem',
			],
			[
				'anchor'      => 'forged wheels',
				'path'        => '/product/brabus-monoblock-r-platinum-edition-5-double-spoke-rims-forged-anthracite-titan-polished-for-the-mercedes-benz-g-class-w463/',
				'utm_content' => 'monoblock_r_23',
			],
			[
				'anchor'      => 'carbon fiber body kits',
				'path'        => '/product/brabus-w465-widestar-kit-carbon-fiber-package-1/',
				'utm_content' => 'widestar_pkg1',
			],
		],
		'g63-brabus-price'        => [
			[
				'anchor'      => 'Widestar body kit',
				'path'        => '/product/brabus-w465-widestar-kit-carbon-fiber-package-1/',
				'utm_content' => 'widestar_pkg1',
				'occurrence'  => 2,
			],
			[
				'anchor'      => 'forged wheels up to 23 inches',
				'path'        => '/product/brabus-monoblock-r-platinum-edition-5-double-spoke-rims-forged-anthracite-titan-polished-for-the-mercedes-benz-g-class-w463/',
				'utm_content' => 'monoblock_r_23',
			],
			[
				'anchor'      => 'carbon fiber aerodynamic elements',
				'path'        => '/product/brabus-carbon-fiber-body-trims-for-g-wagon/',
				'utm_content' => 'carbon_body_trims',
			],
		],
	];
}

/**
 * Wrap the nth plain-text occurrence of an anchor in a link.
 *
 * @param string $content    Post content.
 * @param string $anchor     Existing phrase to link.
 * @param string $url        Destination URL.
 * @param int    $occurrence One-based occurrence to target.
 * @return string
 */
function threew_2025_link_nth_anchor( $content, $anchor, $url, $occurrence = 1 ) {
	if ( '' === $anchor ) {
		return $content;
	}

	$length = strlen( $anchor );
	$offset = 0;
	$found  = 0;

	while ( false !== ( $position = strpos( $content, $anchor, $offset ) ) ) {
		++$found;

		if ( $found === (int) $occurrence ) {
			$before = substr( $content, 0, $position );

			// Leave the copy alone if this phrase is already inside a link.
			if ( preg_match( '~<a\b[^>]*>[^<]*$~i', $before ) ) {
				return $content;
			}

			$link = '<a href="' . esc_url( $url ) . '">' . $anchor . '</a>';

			return substr( $content, 0, $position ) . $link . substr( $content, $position + $length );
		}

		$offset = $position + $length;
	}

	return $content;
}

/**
 * Apply the guide product links, refusing to insert the same link twice.
 *
 * @param string $content  Post content.
 * @param string $slug     Post slug.
 * @param string $campaign UTM campaign value.
 * @return string
 */
function threew_2025_apply_price_guide_product_links( $content, $slug, $campaign ) {
	$links = threew_2025_get_price_guide_product_links();

	if ( empty( $links[ $slug ] ) ) {
		return $content;
	}

	foreach ( $links[ $slug ] as $link ) {
		if ( str_contains( $content, 'utm_content=' . $link['utm_content'] ) ) {
			continue;
		}

		$url = add_query_arg(
			[
				'utm_source'   => '3w_main',
				'utm_medium'   => 'editorial',
				'utm_campaign' => $campaign,
				'utm_content'  => $link['utm_content'],
			],
			'https://shop.3wdistributing.com' . $link['path']
		);

		$content = threew_2025_link_nth_anchor( $content, $link['anchor'], $url, $link['occurrence'] ?? 1 );
	}

	return $content;
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
 * Keep Rank Math's social output aligned with the guide metadata.
 *
 * Rank Math resolves og:title and twitter:title from stored post meta, so the
 * guides otherwise advertise the pre-remediation copy with a stale model year.
 * These run on Rank Math's per-tag filter, which is applied to the final value.
 *
 * @param string $content Tag content.
 * @return string
 */
function threew_2025_filter_guide_social_title( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}

	$map  = threew_2025_get_price_guide_seo();
	$slug = get_post_field( 'post_name', get_queried_object_id() );

	return isset( $map[ $slug ] ) ? $map[ $slug ]['title'] : $content;
}

/**
 * Same as above for the social description.
 *
 * @param string $content Tag content.
 * @return string
 */
function threew_2025_filter_guide_social_description( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}

	$map  = threew_2025_get_price_guide_seo();
	$slug = get_post_field( 'post_name', get_queried_object_id() );

	return isset( $map[ $slug ] ) ? $map[ $slug ]['description'] : $content;
}

add_filter( 'rank_math/opengraph/facebook/og_title', 'threew_2025_filter_guide_social_title', 20 );
add_filter( 'rank_math/opengraph/twitter/twitter_title', 'threew_2025_filter_guide_social_title', 20 );
add_filter( 'rank_math/opengraph/facebook/og_description', 'threew_2025_filter_guide_social_description', 20 );
add_filter( 'rank_math/opengraph/twitter/twitter_description', 'threew_2025_filter_guide_social_description', 20 );

/**
 * Supply the theme's social card when Rank Math found no image of its own.
 *
 * Rank Math calls its image filter with an empty value even when nothing was
 * found, so pages without a featured or content image would otherwise share
 * without any picture.
 *
 * @param string $url Image URL from Rank Math.
 * @return string
 */
function threew_2025_filter_social_image_url( $url ) {
	if ( '' !== trim( (string) $url ) ) {
		return $url;
	}

	return get_theme_file_uri( 'images/social-card-default.jpg' );
}

add_filter( 'rank_math/opengraph/facebook/image', 'threew_2025_filter_social_image_url', 20 );
add_filter( 'rank_math/opengraph/twitter/image', 'threew_2025_filter_social_image_url', 20 );

/**
 * Add the default social card before Rank Math runs its default-image step.
 *
 * The image filter alone is not enough: when a configured default image ID
 * fails to resolve, Rank Math returns from that step without ever calling
 * add_image(), so the filter never runs and the page ships with no og:image.
 * This action fires after the per-context image setters and before that step.
 *
 * @param object $image Rank Math OpenGraph image collector.
 * @return void
 */
function threew_2025_add_default_social_image( $image ) {
	if ( ! is_object( $image ) || ! method_exists( $image, 'has_images' ) || ! method_exists( $image, 'add_image' ) ) {
		return;
	}

	if ( $image->has_images() ) {
		return;
	}

	$image->add_image( get_theme_file_uri( 'images/social-card-default.jpg' ) );
}

add_action( 'rank_math/opengraph/facebook/add_additional_images', 'threew_2025_add_default_social_image', 20 );
add_action( 'rank_math/opengraph/twitter/add_additional_images', 'threew_2025_add_default_social_image', 20 );

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
	$content  = threew_2025_apply_price_guide_product_links( $content, $slug, $campaign );
	$category = add_query_arg(
		[
			'utm_source'   => '3w_main',
			'utm_medium'   => 'editorial',
			'utm_campaign' => $campaign,
		],
		'https://shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/'
	);
	$contact = add_query_arg(
		[
			'utm_source'   => '3w_main',
			'utm_medium'   => 'editorial',
			'utm_campaign' => $campaign,
		],
		'https://shop.3wdistributing.com/contact-us/'
	);

	$note = '<p class="threew-pricing-note"><strong>' . esc_html__( 'Pricing note:', 'threew-2025' ) . '</strong> ';
	$note .= esc_html__( 'Vehicle price figures in this guide are estimates from earlier model years, not current quotes. Contact us to confirm current BRABUS parts pricing, availability and fitment before ordering.', 'threew-2025' ) . '</p>';

	$cta  = '<aside class="threew-commercial-cta" data-threew-commercial-cta aria-label="BRABUS parts and fitment assistance">';
	$cta .= '<h2>' . esc_html__( 'Ready to build your G-Wagon?', 'threew-2025' ) . '</h2>';
	$cta .= '<p>' . esc_html__( 'Browse genuine BRABUS upgrades or contact 3W Distributing for pricing, fitment and availability help before ordering.', 'threew-2025' ) . '</p>';
	$cta .= '<div class="threew-commercial-cta__actions">';
	$cta .= '<a href="' . esc_url( $category ) . '">' . esc_html__( 'Shop BRABUS G-Wagon parts', 'threew-2025' ) . '</a>';
	$cta .= '<a href="' . esc_url( $contact ) . '">' . esc_html__( 'Request fitment help', 'threew-2025' ) . '</a>';
	$cta .= '<a href="tel:+17024306622">' . esc_html__( 'Call (702) 430-6622', 'threew-2025' ) . '</a>';
	$cta .= '</div></aside>';

	return $note . $content . $cta;
}, 30 );
