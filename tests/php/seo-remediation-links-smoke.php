<?php
/**
 * Isolated smoke test for the price-guide product links.
 * Run: php tests/php/seo-remediation-links-smoke.php
 */

define( 'ABSPATH', __DIR__ );

$GLOBALS['mock_slug']  = 'g63-brabus-price';
$GLOBALS['mock_post']  = true;
$GLOBALS['filters']    = [];

function add_action( ...$args ) {}
function add_filter( $name, $callback, ...$args ) { $GLOBALS['filters'][ $name ][] = $callback; }
function do_action( ...$args ) {}
function get_option( $name ) { return 'current'; }
function update_option( ...$args ) { return true; }
function get_page_by_path( ...$args ) { return null; }
function is_singular( $type ) { return $GLOBALS['mock_post'] && $type === 'post'; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_post_field( $field, $id ) { return $GLOBALS['mock_slug']; }
function get_queried_object_id() { return 1; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function get_theme_file_uri( $path = '' ) { return 'https://www.3wdistributing.com/wp-content/themes/3w-2025/' . $path; }

if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {}
}

require __DIR__ . '/../../wp-content/themes/3w-2025/inc/seo-remediation.php';

function check( $condition, $message ) {
	if ( ! $condition ) {
		fwrite( STDERR, "FAIL: $message\n" );
		exit( 1 );
	}
}

$g_wagon_copy = '<p>The attention to detail is obsessive, with custom upholstery, illuminated logos, and unique trim options.</p>'
	. '<p>Popular upgrades include forged wheels ($12,000-$20,000), carbon fiber body kits ($25,000+), and exclusive interior packages.</p>';

$g63_copy = '<p>Notable milestones include the introduction of the Brabus Widestar body kit in the 1980s.</p>'
	. '<p>Signature touches include the Widestar body kit, forged wheels up to 23 inches, and carbon fiber aerodynamic elements.</p>';

$filter = $GLOBALS['filters']['the_content'][0];

// G-Wagon guide: all three links, correct targets and attribution.
$GLOBALS['mock_slug'] = 'price-of-g-wagon-brabus';
$out = $filter( $g_wagon_copy );
check( substr_count( $out, 'utm_content=illuminated_grille_emblem' ) === 1, 'illuminated emblem link inserted once' );
check( substr_count( $out, 'utm_content=monoblock_r_23' ) === 1, 'wheel link inserted once' );
check( substr_count( $out, 'utm_content=widestar_pkg1' ) === 1, 'widestar link inserted once' );
check( str_contains( $out, 'utm_campaign=price_of_g_wagon_brabus' ), 'campaign matches slug' );
check( str_contains( $out, 'utm_source=3w_main' ) && str_contains( $out, 'utm_medium=editorial' ), 'source and medium retained' );
check( str_contains( $out, '>illuminated logos</a>' ), 'anchor text preserved' );
check( str_contains( $out, 'with custom upholstery, <a href=' ), 'link wraps the phrase in place' );
check( str_contains( $out, 'shop.3wdistributing.com/product/brabus-w465-widestar-kit-carbon-fiber-package-1/' ), 'widestar target correct' );

// Idempotency: a second pass must not add duplicates.
$out2 = $filter( $out );
check( substr_count( $out2, 'utm_content=monoblock_r_23' ) === 1, 'repeat render does not duplicate links' );

// G63 guide: second occurrence of the Widestar phrase is the one linked.
$GLOBALS['mock_slug'] = 'g63-brabus-price';
$out = $filter( $g63_copy );
check( substr_count( $out, 'utm_content=widestar_pkg1' ) === 1, 'widestar linked once on g63' );
$first = strpos( $out, 'milestones' );
$second = strpos( $out, 'Signature touches' );
$link_at = strpos( $out, 'utm_content=widestar_pkg1' );
check( $link_at > $second, 'design-paragraph occurrence linked, not the history mention' );
check( false === strpos( substr( $out, $first, $second - $first ), '<a href=' ), 'history paragraph left unlinked' );
check( substr_count( $out, 'utm_content=monoblock_r_23' ) === 1, 'wheels linked on g63' );
check( substr_count( $out, 'utm_content=carbon_body_trims' ) === 1, 'carbon trims linked on g63' );

// An anchor already inside a link is not wrapped again.
$GLOBALS['mock_slug'] = 'price-of-g-wagon-brabus';
$prelinked = '<p>Popular upgrades include <a href="https://example.com/">forged wheels</a> and more.</p>';
$out = $filter( $prelinked );
check( str_contains( $out, $prelinked ), 'already-linked anchor left intact' );
check( substr_count( $out, 'forged wheels</a>' ) === 1, 'no second wrap around a linked phrase' );
check( ! str_contains( $out, 'utm_content=monoblock_r_23' ), 'no duplicate wheel link injected' );

// Rank Math social tags must use the remediated guide metadata.
$social_hooks = [
	'rank_math/opengraph/facebook/og_title',
	'rank_math/opengraph/twitter/twitter_title',
	'rank_math/opengraph/facebook/og_description',
	'rank_math/opengraph/twitter/twitter_description',
];
foreach ( $social_hooks as $hook ) {
	check( isset( $GLOBALS['filters'][ $hook ][0] ), "rank math tag filter registered: $hook" );
}
$GLOBALS['mock_slug'] = 'g63-brabus-price';
$rm_title = $GLOBALS['filters']['rank_math/opengraph/facebook/og_title'][0]( 'Old raw title - Site' );
$rm_tw    = $GLOBALS['filters']['rank_math/opengraph/twitter/twitter_title'][0]( 'Old raw title - Site' );
$rm_desc  = $GLOBALS['filters']['rank_math/opengraph/facebook/og_description'][0]( 'Old raw description' );
check( $rm_title === 'Mercedes-AMG G63 BRABUS Price & Upgrade Guide', 'og:title uses remediated copy' );
check( $rm_tw === $rm_title, 'twitter:title matches og:title' );
check( str_contains( $rm_desc, 'price estimates' ), 'og:description uses remediated copy' );
check( ! str_contains( $rm_title, '2026' ) && ! str_contains( $rm_title, '2025' ), 'social title carries no stale year' );
$GLOBALS['mock_slug'] = 'unrelated-post';
check( $GLOBALS['filters']['rank_math/opengraph/facebook/og_title'][0]( 'Keep me' ) === 'Keep me', 'social title untouched elsewhere' );

// Default social image is only supplied when Rank Math found none.
$img_fallback = $GLOBALS['filters']['rank_math/opengraph/facebook/image'][0]( '' );
check( str_contains( $img_fallback, 'social-card-default.jpg' ), 'default card supplied when Rank Math has no image' );
$img_passthru = $GLOBALS['filters']['rank_math/opengraph/facebook/image'][0]( 'https://example.com/featured.jpg' );
check( $img_passthru === 'https://example.com/featured.jpg', 'Rank Math image left alone when present' );
check( isset( $GLOBALS['filters']['rank_math/opengraph/twitter/image'][0] ), 'twitter image fallback registered' );

// Default social card is added only when Rank Math collected no image.
class ThreeW_Fake_Image {
	public $images = [];
	public $added  = [];
	public function has_images() { return ! empty( $this->images ); }
	public function add_image( $url ) { $this->added[] = $url; $this->images[ $url ] = []; }
}

$empty_image = new ThreeW_Fake_Image();
threew_2025_add_default_social_image( $empty_image );
check( count( $empty_image->added ) === 1, 'default image added when Rank Math has none' );
check( str_contains( $empty_image->added[0], 'social-card-default.jpg' ), 'default card used for empty collector' );

$filled_image = new ThreeW_Fake_Image();
$filled_image->images['https://example.com/real.jpg'] = [];
threew_2025_add_default_social_image( $filled_image );
check( count( $filled_image->added ) === 0, 'existing image left alone' );

threew_2025_add_default_social_image( null );
check( true, 'non-object input ignored without fatal' );

// Negative scope.
$GLOBALS['mock_slug'] = 'unrelated-post';
$plain = '<p>Unrelated article mentioning forged wheels.</p>';
check( $filter( $plain ) === $plain, 'unrelated post untouched' );

$GLOBALS['mock_post'] = false;
$GLOBALS['mock_slug'] = 'price-of-g-wagon-brabus';
check( $filter( $g_wagon_copy ) === $g_wagon_copy, 'non-post untouched' );

echo "PASS: price-guide product links, attribution, occurrence targeting and idempotency\n";
