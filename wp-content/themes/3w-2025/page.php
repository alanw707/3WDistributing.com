<?php
/**
 * Default page template.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="threew-page">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'threew-page__article' ); ?>>
				<header class="threew-page__header">
					<h1 class="threew-page__title has-display-lg-font-size">
						<?php the_title(); ?>
					</h1>
				</header>

				<div class="threew-page__content">
					<?php
					the_content();

					wp_link_pages(
						[
							'before' => '<nav class="threew-page__pagination" aria-label="' . esc_attr__( 'Page navigation', 'threew-2025' ) . '">',
							'after'  => '</nav>',
						]
					);
					?>
				</div>
			</article>
			<?php
		endwhile;
	endif;
	?>
</main>

<?php
get_footer();
