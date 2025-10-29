<?php
/**
 * Blog listing for the /blog landing page.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$paged = get_query_var( 'paged' );
if ( ! $paged ) {
	$paged = get_query_var( 'page' );
}
$paged            = $paged ? absint( $paged ) : 1;
$posts_per_page   = (int) get_option( 'posts_per_page', 10 );
$blog_page_query  = new WP_Query(
	[
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'paged'          => $paged,
		'posts_per_page' => $posts_per_page,
	]
);
?>

<main class="threew-blog-index threew-blog-index--catalog">
	<section class="threew-blog-index__hero">
		<p class="threew-front-page__blog-eyebrow has-secondary-color has-text-color has-body-sm-font-size">
			<?php esc_html_e( 'Knowledge base', 'threew-2025' ); ?>
		</p>

		<h2 class="has-display-lg-font-size">
			<?php esc_html_e( 'Insights from the 3W garage', 'threew-2025' ); ?>
		</h2>

		<p class="threew-blog-index__intro has-body-md-font-size">
			<?php esc_html_e( 'Browse installation walkthroughs, tuning deep dives, and brand spotlights designed to keep your build ahead of the grid.', 'threew-2025' ); ?>
		</p>
	</section>

	<?php if ( $blog_page_query->have_posts() ) : ?>
		<div class="threew-blog-grid">
			<?php
			while ( $blog_page_query->have_posts() ) :
				$blog_page_query->the_post();
				?>
				<article <?php post_class( 'threew-blog-card' ); ?>>
					<div class="threew-blog-card__inner">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="threew-blog-card__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large', [ 'alt' => esc_attr( get_the_title() ) ] ); ?>
							</a>
						<?php endif; ?>

						<div class="threew-blog-card__content">
							<?php
							$categories = get_the_category();
							if ( $categories ) :
								?>
								<div class="threew-blog-card__category">
									<?php foreach ( $categories as $category ) : ?>
										<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
											<?php echo esc_html( $category->name ); ?>
										</a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<h3 class="threew-blog-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>

							<p class="threew-blog-card__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 28, '…' ) ); ?>
							</p>

							<div class="threew-blog-card__meta">
								<time class="threew-blog-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
									<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
								</time>

								<a class="threew-blog-card__read-more" href="<?php the_permalink(); ?>">
									<?php esc_html_e( 'Read article', 'threew-2025' ); ?>
								</a>
							</div>
						</div>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php
		$pagination = paginate_links(
			[
				'total'     => $blog_page_query->max_num_pages,
				'current'   => $paged,
				'mid_size'  => 2,
				'prev_text' => __( 'Previous', 'threew-2025' ),
				'next_text' => __( 'Next', 'threew-2025' ),
				'type'      => 'list',
			]
		);

		if ( $pagination ) :
			?>
			<nav class="threew-blog-pagination" aria-label="<?php esc_attr_e( 'Blog navigation', 'threew-2025' ); ?>">
				<?php echo wp_kses_post( $pagination ); ?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<div class="threew-blog-grid">
			<article class="threew-blog-card threew-blog-card--empty">
				<div class="threew-blog-card__inner">
					<div class="threew-blog-card__content">
						<h3 class="threew-blog-card__title">
							<?php esc_html_e( 'No posts yet', 'threew-2025' ); ?>
						</h3>
						<p class="threew-blog-card__excerpt">
							<?php esc_html_e( 'Publish your first story to light up this feed.', 'threew-2025' ); ?>
						</p>
					</div>
				</div>
			</article>
		</div>
	<?php endif; ?>
</main>

<?php
wp_reset_postdata();
get_footer();
