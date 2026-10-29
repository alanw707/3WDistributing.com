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
function get_theme_file_path( $path = '' ) { return __DIR__ . '/../../wp-content/themes/3w-2025/' . $path; }
function has_post_thumbnail( $post = null ) { return $GLOBALS['mock_thumbnail'] ?? false; }
function get_post_thumbnail_id( $post = null ) { return 123; }
function wp_get_attachment_image_src( $id, $size = 'full' ) { return [ 'https://example.com/featured.jpg', 1200, 800, true ]; }
function get_post_meta( $id, $key, $single = false ) { return 'Featured alt'; }
function get_theme_mod( $name, $default = false ) { return $default; }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_url_raw( $value ) { return $value; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function is_front_page() { return true; }
function is_singular( $type = null ) { return $GLOBALS['mock_singular'] ?? false; }
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

check( ! str_contains( $og, 'og:title' ), 'no duplicate og:title' );
check( ! str_contains( $og, 'og:description' ), 'no duplicate og:description' );
check( ! str_contains( $og, 'og:url' ), 'no duplicate og:url' );
check( substr_count( $og, 'property="og:image"' ) === 1, 'default og:image supplied when Rank Math has none' );
check( str_contains( $og, 'social-card-default.jpg' ), 'default card used' );
check( ! str_contains( $tw, 'twitter:title' ), 'no duplicate twitter:title' );
check( ! str_contains( $tw, 'twitter:card' ), 'no duplicate twitter:card' );
check( substr_count( $tw, 'name="twitter:image"' ) === 1, 'default twitter:image supplied' );
check( str_contains( $tw, 'twitter:site' ), 'twitter:site retained (Rank Math does not emit it)' );
check( str_contains( $tw, 'twitter:creator' ), 'twitter:creator retained' );

// With a featured image, Rank Math supplies it, so the theme must stay silent.
$GLOBALS['mock_singular'] = true;
$GLOBALS['mock_thumbnail'] = true;

ob_start();
$seo->output_open_graph();
$og2 = ob_get_clean();

ob_start();
$seo->output_twitter_cards();
$tw2 = ob_get_clean();

check( ! str_contains( $og2, 'og:image' ), 'no duplicate og:image when a featured image exists' );
check( ! str_contains( $tw2, 'twitter:image' ), 'no duplicate twitter:image when a featured image exists' );
check( str_contains( $tw2, 'twitter:site' ), 'handles still emitted with a featured image' );

echo "PASS: social tags defer to Rank Math, default image only where it has none\n";
