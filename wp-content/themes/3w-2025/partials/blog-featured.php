<?php
/**
 * Front-page blog teaser partial.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$blog_query = new WP_Query(
	[
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	]
);
?>

<section class="threew-front-page__blog">
	<p class="threew-front-page__blog-eyebrow has-secondary-color has-text-color has-body-sm-font-size">
		From the garage
	</p>

	<h2 class="has-display-lg-font-size">Latest tuning insights</h2>

	<p class="threew-front-page__blog-intro has-body-md-font-size">
		Install guides, build stories, and performance tips curated by the 3W team.
	</p>

	<div class="threew-blog-grid">
		<?php if ( $blog_query->have_posts() ) : ?>
			<?php
			while ( $blog_query->have_posts() ) :
				$blog_query->the_post();
				?>
				<article <?php post_class( 'threew-blog-card' ); ?>>
					<div class="threew-blog-card__inner">
						<?php if ( has_post_thumbnail() ) : ?>
							<a class="threew-blog-card__media" href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'large', [ 'alt' => get_the_title() ] ); ?>
							</a>
						<?php endif; ?>

						<div class="threew-blog-card__content">
							<div class="threew-blog-card__category">
								<?php
								$categories = get_the_category();
								foreach ( $categories as $category ) {
									printf(
										'<a href="%1$s">%2$s</a>',
										esc_url( get_category_link( $category->term_id ) ),
										esc_html( $category->name )
									);
								}
								?>
							</div>

							<h3 class="threew-blog-card__title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>

							<p class="threew-blog-card__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?>
							</p>

							<div class="threew-blog-card__meta">
								<time class="threew-blog-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
									<?php echo esc_html( get_the_date( 'M j, Y' ) ); ?>
								</time>
								<a class="threew-blog-card__read-more" href="<?php the_permalink(); ?>">
									Read article
								</a>
							</div>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		<?php else : ?>
			<div class="threew-blog-card threew-blog-card--empty">
				<div class="threew-blog-card__inner">
					<div class="threew-blog-card__content">
						<h3 class="threew-blog-card__title">Add your first post</h3>
						<p class="threew-blog-card__excerpt">
							Create a blog post to showcase installation guides and product spotlights here.
						</p>
						<div class="threew-blog-card__meta">
							<span class="threew-blog-card__read-more">Stay tuned</span>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
