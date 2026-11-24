<?php
/**
 * Permanent consolidations for pages that duplicate a stronger one.
 *
 * Each entry below was chosen from Search Console data: the source page carries
 * the same intent as the target and, in the price cases, the identical title.
 * Redirecting consolidates the signals instead of splitting them across URLs.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Source path => target path on this site.
 *
 * @return array<string,string>
 */
function threew_consolidation_redirects() {
	return array(
		'/brabus-g-wagon-price/'            => '/price-of-g-wagon-brabus/',
		'/brabus-price/'                    => '/price-of-g-wagon-brabus/',
		'/mercedes-benz-g63-brabus-price/'  => '/price-of-g-wagon-brabus/',
		'/car-accessories-online/'          => '/best-online-parts-store/',
		'/mercedes-mansory/'                => '/mansory-brabus/',
	);
}

/**
 * Match the current request against the map and redirect permanently.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return;
		}

		$request_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
		$request_path = '/' . ltrim( (string) $request_path, '/' );
		$request_path = trailingslashit( $request_path );

		$map = threew_consolidation_redirects();
		if ( ! isset( $map[ $request_path ] ) ) {
			return;
		}

		$target = home_url( $map[ $request_path ] );
		$query  = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
		if ( '' !== $query ) {
			$target = $target . '?' . $query;
		}

		wp_safe_redirect( $target, 301 );
		exit;
	},
	1
);

/**
 * Purge full-page cache once when the redirect map changes.
 */
add_action( 'init', function () {
	$cache_version = '2026-11-23-2';
	if ( get_option( 'threew_consolidation_cache_version' ) === $cache_version ) {
		return;
	}

	if ( class_exists( 'LiteSpeed_Cache_API' ) ) {
		LiteSpeed_Cache_API::purge_all();
	}

	update_option( 'threew_consolidation_cache_version', $cache_version );
} );
