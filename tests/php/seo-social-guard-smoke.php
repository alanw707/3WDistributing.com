<?php
/**
 * Smoke test: the theme defers social tags to Rank Math when it is active.
 * Run: php tests/php/seo-social-guard-smoke.php
 */

define( 'ABSPATH', __DIR__ );

function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function get_bloginfo( $key = '' ) { return '3W Distributing'; }
function __( $text, $domain = null ) { return $text; }
function home_url( $path = '' ) { return 'https://www.3wdistributing.com' . $path; }
function get_theme_file_uri( $path = '' ) { return 'https://www.3wdistributing.com/wp-content/themes/3w-2025/' . $path; }
function get_theme_file_path( $path = '' ) { return __DIR__ . '/missing/' . $path; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url_raw( $value ) { return $value; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function is_front_page() { return true; }
function is_singular( $type = null ) { return false; }
function is_single() { return false; }
function get_the_author() { return ''; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/' ; }
function get_option( $name, $default = false ) { return $default; }
function update_option( ...$args ) { return true; }
function add_rewrite_rule( ...$args ) {}
function get_permalink( $id = 0 ) { return 'https://www.3wdistributing.com/'; }
function get_posts( $args = [] ) { return []; }
function get_the_title( $id = 0 ) { return 'Title'; }

// Rank Math active: the theme must not emit Open Graph at all.
define( 'RANK_MATH_VERSION', '1.0.280' );
require __DIR__ . '/../../wp-content/themes/3w-2025/inc/seo-class.php';

$seo = new ThreeW_SEO();

ob_start();
$seo->output_open_graph();
$og = ob_get_clean();

ob_start();
$seo->output_twitter_cards();
$tw = ob_get_clean();

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: $message\n" );
		exit( 1 );
	}
}

check( '' === trim( $og ), 'theme emits no Open Graph tags when Rank Math is active' );
check( ! str_contains( $og, 'og:title' ), 'no duplicate og:title' );
check( ! str_contains( $tw, 'twitter:title' ), 'no duplicate twitter:title' );
check( ! str_contains( $tw, 'twitter:card' ), 'no duplicate twitter:card' );
check( ! str_contains( $tw, 'twitter:image' ), 'no duplicate twitter:image' );
check( str_contains( $tw, 'twitter:site' ), 'twitter:site retained (Rank Math does not emit it)' );
check( str_contains( $tw, 'twitter:creator' ), 'twitter:creator retained' );

echo "PASS: theme defers Open Graph and Twitter card tags to Rank Math, keeps handles\n";
