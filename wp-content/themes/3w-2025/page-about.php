<?php
/**
 * Template Name: About Us
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="threew-about-page">
	<!-- Hero Section -->
	<section class="threew-about-hero">
		<div class="threew-about-hero__inner">
			<p class="threew-about-hero__eyebrow">Your Partner in Performance</p>
			<h1 class="threew-about-hero__title">About Us</h1>
			<p class="threew-about-hero__subtitle">Premium aftermarket parts and accessories for enthusiasts who demand excellence</p>
		</div>
	</section>

	<!-- Mission Section -->
	<section class="threew-about-mission">
		<div class="threew-about-mission__inner">
			<div class="threew-about-mission__content">
				<p>At 3W Distribution, we're dedicated to providing you with the finest selection of premium aftermarket parts and accessories for your vehicle. We believe your vehicle is more than just a mode of transportation—it's a reflection of your passion for performance, style, and individuality.</p>
			</div>
		</div>
	</section>

	<!-- Core Values Section -->
	<section class="threew-about-values">
		<div class="threew-about-values__inner">
			<div class="threew-about-values__grid">
				<!-- Performance Value -->
				<article class="threew-value-card">
					<div class="threew-value-card__icon">
						<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M32 8C18.745 8 8 18.745 8 32C8 45.255 18.745 56 32 56C45.255 56 56 45.255 56 32C56 18.745 45.255 8 32 8ZM32 12C43.046 12 52 20.954 52 32C52 43.046 43.046 52 32 52C20.954 52 12 43.046 12 32C12 20.954 20.954 12 32 12Z" stroke="currentColor" stroke-width="2"/>
							<path d="M32 16C22.059 16 14 24.059 14 34C14 43.941 22.059 52 32 52C41.941 52 50 43.941 50 34C50 24.059 41.941 16 32 16Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M32 20L36 34H28L32 20Z" fill="currentColor"/>
							<circle cx="32" cy="34" r="3" fill="currentColor"/>
						</svg>
					</div>
					<h2 class="threew-value-card__title">Performance</h2>
					<p class="threew-value-card__description">We partner with top-tier manufacturers to bring you only the highest quality parts designed to enhance your vehicle's performance. From suspension upgrades to engine components, we never cut corners when it comes to quality.</p>
				</article>

				<!-- Customer Satisfaction Value -->
				<article class="threew-value-card">
					<div class="threew-value-card__icon">
						<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M32 8L38.472 24.528L56 31L38.472 37.472L32 54L25.528 37.472L8 31L25.528 24.528L32 8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
							<path d="M48 14L50.236 19.764L56 22L50.236 24.236L48 30L45.764 24.236L40 22L45.764 19.764L48 14Z" fill="currentColor"/>
							<path d="M16 40L18.236 45.764L24 48L18.236 50.236L16 56L13.764 50.236L8 48L13.764 45.764L16 40Z" fill="currentColor"/>
						</svg>
					</div>
					<h2 class="threew-value-card__title">Customer Satisfaction</h2>
					<p class="threew-value-card__description">We believe in fair pricing, friendly service, and a seamless shopping experience. Whether you're a seasoned enthusiast or just getting started, we're here to help you find the perfect parts for your build.</p>
				</article>

				<!-- Innovation Value -->
				<article class="threew-value-card">
					<div class="threew-value-card__icon">
						<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M32 12C26.477 12 22 16.477 22 22C22 25.5 23.5 28.5 26 30.5V38C26 39.105 26.895 40 28 40H36C37.105 40 38 39.105 38 38V30.5C40.5 28.5 42 25.5 42 22C42 16.477 37.523 12 32 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							<path d="M28 44H36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M28 48H36" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M30 52H34" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M32 22L32 28" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M28 24L30 26" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							<path d="M36 24L34 26" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<h2 class="threew-value-card__title">Innovation</h2>
					<p class="threew-value-card__description">We're always pushing the boundaries of what's possible, exploring new technologies and the most advanced parts on the market. Our goal is to keep you ahead of the curve with cutting-edge products that elevate your ride.</p>
				</article>
			</div>
		</div>
	</section>

	<!-- CTA and Contact Section -->
	<section class="threew-about-contact" id="contact">
		<div class="threew-about-contact__inner">
			<div class="threew-about-contact__cta">
				<p>We work tirelessly to earn your trust and loyalty, and we're constantly evolving to meet your needs.</p>
				<p><strong>Ready to elevate your ride? Contact us today and experience the 3W way!</strong></p>
			</div>

			<div class="threew-about-contact__form">
				<h2 class="threew-about-contact__form-title">Contact Us</h2>

				<?php if ( isset( $_GET['contact_status'] ) && $_GET['contact_status'] === 'success' ) : ?>
					<div class="threew-form-message threew-form-message--success">
						Thank you for your message! We'll get back to you soon.
					</div>
				<?php elseif ( isset( $_GET['contact_status'] ) && $_GET['contact_status'] === 'error' ) : ?>
					<div class="threew-form-message threew-form-message--error">
						There was an error sending your message. Please try again or contact us directly.
					</div>
				<?php elseif ( isset( $_GET['contact_status'] ) && $_GET['contact_status'] === 'captcha' ) : ?>
					<div class="threew-form-message threew-form-message--error">
						Please confirm you are not a robot and try again.
					</div>
				<?php endif; ?>

				<form class="threew-contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'threew_contact_form', 'threew_contact_nonce' ); ?>
					<input type="hidden" name="action" value="threew_contact_form_submit">

					<div class="threew-form-row">
						<div class="threew-form-field">
							<label for="contact-name">Name <span class="required">*</span></label>
							<input type="text" id="contact-name" name="contact_name" required>
						</div>

						<div class="threew-form-field">
							<label for="contact-email">Email <span class="required">*</span></label>
							<input type="email" id="contact-email" name="contact_email" required>
						</div>
					</div>

					<div class="threew-form-row">
						<div class="threew-form-field">
							<label for="contact-phone">Phone</label>
							<input type="tel" id="contact-phone" name="contact_phone">
						</div>

						<div class="threew-form-field">
							<label for="contact-subject">Subject</label>
							<input type="text" id="contact-subject" name="contact_subject">
						</div>
					</div>

					<div class="threew-form-field threew-form-field--full">
						<label for="contact-message">Message <span class="required">*</span></label>
						<textarea id="contact-message" name="contact_message" rows="6" required></textarea>
					</div>

					<?php if ( function_exists( 'threew_get_recaptcha_site_key' ) && threew_get_recaptcha_site_key() ) : ?>
						<div class="threew-form-field threew-form-field--full threew-form-field--captcha">
							<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( threew_get_recaptcha_site_key() ); ?>"></div>
						</div>
					<?php endif; ?>

					<div class="threew-form-submit">
						<button type="submit" class="threew-btn threew-btn--primary">Send Message</button>
					</div>
				</form>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
