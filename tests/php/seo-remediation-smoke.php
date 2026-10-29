<?php
/**
 * Isolated smoke test for guide metadata and the_content filter.
 * Run: php tests/php/seo-remediation-smoke.php
 */
define( 'ABSPATH', __DIR__ );
$GLOBALS['mock_slug'] = 'g63-brabus-price';
$GLOBALS['mock_post'] = true;
$GLOBALS['filters'] = [];
function add_action( ...$args ) {}
function add_filter( $name, $callback, ...$args ) { $GLOBALS['filters'][ $name ][] = $callback; }
function is_singular( $type ) { return $GLOBALS['mock_post'] && $type === 'post'; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_post_field( $field, $id ) { return $GLOBALS['mock_slug']; }
function get_queried_object_id() { return 1; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
require __DIR__ . '/../../wp-content/themes/3w-2025/inc/seo-remediation.php';
function check( $condition, $message ) {
	if ( ! $condition ) { fwrite( STDERR, "FAIL: $message\n" ); exit( 1 ); }
}
$seo = threew_2025_get_price_guide_seo();
foreach ( [ 'g63-brabus-price', 'price-of-g-wagon-brabus' ] as $slug ) {
	check( isset( $seo[ $slug ] ), "metadata for $slug" );
	check( ! str_contains( $seo[ $slug ]['title'], '2026' ), "unsupported model year removed from $slug title" );
	$GLOBALS['mock_slug'] = $slug;
	$content = $GLOBALS['filters']['the_content'][0]( '<p>Original editorial copy.</p>' );
	check( str_starts_with( $content, '<p class="threew-pricing-note">' ), "visible pricing context on $slug" );
	check( str_contains( $content, 'not current quotes' ), "qualified price claims on $slug" );
	check( str_contains( $content, '<p>Original editorial copy.</p>' ), "original article preserved on $slug" );
	check( str_contains( $content, 'shop.3wdistributing.com/product-category/brabus/brabus-mercedes/g-wagon/' ), "relevant shop category on $slug" );
	check( str_contains( $content, 'utm_campaign=' . str_replace( '-', '_', $slug ) ), "attribution campaign on $slug" );
	check( str_contains( $content, 'shop.3wdistributing.com/contact-us/' ), "fitment-contact path on $slug" );
	check( ! str_contains( $content, 'href="https://shop.3wdistributing.com/product-category/brabus/?' ), "broad category removed on $slug" );
}
$GLOBALS['mock_slug'] = 'unrelated-post';
check( $GLOBALS['filters']['the_content'][0]( '<p>Unrelated</p>' ) === '<p>Unrelated</p>', 'unrelated post untouched' );
$GLOBALS['mock_post'] = false;
$GLOBALS['mock_slug'] = 'g63-brabus-price';
check( $GLOBALS['filters']['the_content'][0]( '<p>Other content</p>' ) === '<p>Other content</p>', 'non-post untouched' );
echo "PASS: guide metadata, commercial links, attribution, disclaimer and negative scope\n";
