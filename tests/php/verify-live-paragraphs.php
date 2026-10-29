<?php
/**
 * Replays the real live article paragraphs through the link filter.
 * Run: php tests/php/verify-live-paragraphs.php <real-paragraphs.json>
 */
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {}
function add_filter( ...$args ) {}
function do_action( ...$args ) {}
function get_option( $name ) { return 'current'; }
function update_option( ...$args ) { return true; }
function get_page_by_path( ...$args ) { return null; }
function is_singular( $type ) { return true; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_post_field( $field, $id ) { return 'x'; }
function get_queried_object_id() { return 1; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function esc_url( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html__( $value, $domain ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
if ( ! class_exists( 'WP_Post' ) ) { class WP_Post {} }
require __DIR__ . '/../../wp-content/themes/3w-2025/inc/seo-remediation.php';

$file = $argv[1] ?? __DIR__ . '/../../reports/3w-retro-2026-10-29/real-paragraphs.json';
$data = json_decode( file_get_contents( $file ), true );
$fail = 0;

foreach ( $data as $slug => $paragraphs ) {
	$content  = implode( "\n", $paragraphs );
	$campaign = str_replace( '-', '_', $slug );
	$out      = threew_2025_apply_price_guide_product_links( $content, $slug, $campaign );

	echo "\n== $slug\n";
	foreach ( threew_2025_get_price_guide_product_links()[ $slug ] as $link ) {
		$needle = 'utm_content=' . $link['utm_content'];
		$count  = substr_count( $out, $needle );
		$ok     = 1 === $count;
		printf( "   %-28s occurrences=%d %s\n", $link['utm_content'], $count, $ok ? 'OK' : 'PROBLEM' );
		if ( ! $ok ) { $fail++; }
	}
	// No nesting or broken markup.
	if ( preg_match( '~<a\b[^>]*>[^<]*<a\b~i', $out ) ) { echo "   nested anchors detected\n"; $fail++; }
	if ( substr_count( $out, '<a ' ) !== count( threew_2025_get_price_guide_product_links()[ $slug ] ) ) {
		echo "   unexpected anchor count\n"; $fail++;
	}
	// Show one inserted link for eyeballing.
	if ( preg_match( '~<a href="[^"]*utm_content=widestar_pkg1[^"]*">[^<]*</a>~', $out, $m ) ) {
		echo '   sample: ' . $m[0] . "\n";
	}
}

echo "\n" . ( $fail ? "FAIL: $fail problem(s)\n" : "PASS: live paragraphs link correctly\n" );
exit( $fail ? 1 : 0 );
