<?php
/**
 * Smoke test for the consolidation redirect map.
 * Run: php tests/php/redirects-smoke.php
 */
define( 'ABSPATH', __DIR__ );
$GLOBALS['actions'] = [];
function add_action( $name, $callback, ...$args ) { $GLOBALS['actions'][ $name ][] = $callback; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function get_option( $name ) { return false; }
function update_option( $name, $value ) {}
function sanitize_text_field( $value ) { return $value; }
function wp_unslash( $value ) { return $value; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function home_url( $path = '' ) { return 'https://www.3wdistributing.com' . $path; }
function wp_safe_redirect( $url, $status = 302 ) {
	$GLOBALS['redirect'] = [ $url, $status ];
	throw new RuntimeException( 'redirect' );
}
require __DIR__ . '/../../wp-content/themes/3w-2025/inc/redirects.php';

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: $message\n" );
		exit( 1 );
	}
}

$map = threew_consolidation_redirects();
check( 5 === count( $map ), 'five consolidations' );
check( '/price-of-g-wagon-brabus/' === $map['/brabus-price/'], 'duplicate price page points at the guide' );
check( '/price-of-g-wagon-brabus/' === $map['/brabus-g-wagon-price/'], 'second duplicate price page points at the guide' );
check( '/mansory-brabus/' === $map['/mercedes-mansory/'], 'mansory duplicate points at the comparison page' );

$runner = $GLOBALS['actions']['template_redirect'][0];

// A mapped path redirects 301, preserving the query string.
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['QUERY_STRING']   = 'utm_source=test';
$_SERVER['REQUEST_URI']    = '/brabus-price/?utm_source=test';
$GLOBALS['redirect']       = null;
try {
	$runner();
} catch ( RuntimeException $e ) {
}
check( null !== $GLOBALS['redirect'], 'mapped path redirects' );
check( 301 === $GLOBALS['redirect'][1], 'redirect is permanent' );
check( 'https://www.3wdistributing.com/price-of-g-wagon-brabus/?utm_source=test' === $GLOBALS['redirect'][0], 'target and query preserved' );

// An unmapped path is untouched.
foreach ( [ '/about-us/', '/price-of-g-wagon-brabus/' ] as $path ) {
	$_SERVER['REQUEST_URI'] = $path;
	$GLOBALS['redirect']    = null;
	try {
		$runner();
	} catch ( RuntimeException $e ) {
	}
	check( null === $GLOBALS['redirect'], "no redirect for $path" );
}

// Non-GET requests are left alone.
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI']    = '/brabus-price/';
$GLOBALS['redirect']       = null;
try {
	$runner();
} catch ( RuntimeException $e ) {
}
check( null === $GLOBALS['redirect'], 'POST is not redirected' );

echo "PASS: consolidation redirects\n";
