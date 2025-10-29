<?php
/**
 * Default single post template.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="threew-single">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'threew-single__article' ); ?>>
				<header class="threew-single__hero">
					<?php
					$categories = get_the_category();
					if ( $categories ) :
						?>
						<div class="threew-single__category">
							<?php foreach ( $categories as $category ) : ?>
								<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<h1 class="threew-single__title has-display-lg-font-size">
						<?php the_title(); ?>
					</h1>

					<div class="threew-single__meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
							<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
						</time>
						<span aria-hidden="true">·</span>
						<span><?php echo esc_html( get_the_author() ); ?></span>
					</div>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="threew-single__media">
							<?php the_post_thumbnail( 'large' ); ?>
						</figure>
					<?php endif; ?>
				</header>

				<div class="threew-single__content">
					<?php
					the_content();

					wp_link_pages(
						[
							'before' => '<nav class="threew-single__pagination" aria-label="' . esc_attr__( 'Article pagination', 'threew-2025' ) . '">',
							'after'  => '</nav>',
						]
					);
					?>
				</div>

				<footer class="threew-single__footer">
					<?php
					$post_tags = get_the_tags();

					if ( $post_tags ) :
						?>
						<div class="threew-single__tags">
							<span><?php esc_html_e( 'Filed under:', 'threew-2025' ); ?></span>
							<ul>
								<?php foreach ( $post_tags as $tag ) : ?>
									<li>
										<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
											<?php echo esc_html( $tag->name ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</footer>
			</article>

			<?php
			$navigation = get_the_post_navigation(
				[
					'prev_text' => '<span class="threew-single__nav-label">' . esc_html__( 'Previous', 'threew-2025' ) . '</span><span class="threew-single__nav-title">%title</span>',
					'next_text' => '<span class="threew-single__nav-label">' . esc_html__( 'Next', 'threew-2025' ) . '</span><span class="threew-single__nav-title">%title</span>',
					'screen_reader_text' => esc_html__( 'Post navigation', 'threew-2025' ),
				]
			);

			if ( $navigation ) :
				?>
				<nav class="threew-single__nav" aria-label="<?php esc_attr_e( 'Post navigation', 'threew-2025' ); ?>">
					<?php echo wp_kses_post( $navigation ); ?>
				</nav>
			<?php endif; ?>
			<?php
		endwhile;
	endif;
	?>
</main>

<?php
get_footer();
