<?php
/**
 * Front page template.
 *
 * @package 3w-2025
 */

get_header();
?>

<main class="threew-front-page">
	<?php
	get_template_part( 'partials/hero-fitment' );
	get_template_part( 'partials/seo-intro' );
	get_template_part( 'partials/trust-strip' );
	get_template_part( 'partials/category-grid' );
	get_template_part( 'partials/blog-featured' );
	?>
</main>

<?php
get_footer();
