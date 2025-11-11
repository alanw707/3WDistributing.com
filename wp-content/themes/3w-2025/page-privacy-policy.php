<?php
/**
 * Template Name: Privacy Policy
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="threew-legal threew-legal--privacy">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			$updated          = get_the_modified_time( get_option( 'date_format' ) );
			$contact_email    = sanitize_email( get_option( 'threew_about_contact_email', 'info@3wdistributing.com' ) );
			$contact_email    = $contact_email ?: 'info@3wdistributing.com';
			$highlights       = [
				[
					'title' => 'Data Minimization',
					'body'  => 'We only request details that help us process orders, create fitment quotes, and troubleshoot support requests.',
				],
				[
					'title' => 'Security First',
					'body'  => 'Checkout, account, and support systems use TLS encryption in transit plus role-based access protections in our admin tools.',
				],
				[
					'title' => 'You Stay in Control',
					'body'  => 'Update marketing preferences, ask for a data export, or request deletion at any time by contacting our support team.',
				],
			];
			$sections         = [
				[
					'id'     => 'information-we-collect',
					'title'  => 'Information We Collect',
					'intro'  => 'We capture the minimum amount of data required to fulfill orders, provide technical fitment guidance, and maintain account security.',
					'items'  => [
						[
							'title' => 'Details you share with us',
							'body'  => 'Contact information, billing and shipping details, vehicle and fitment notes, messages submitted through forms, and account credentials you create.',
						],
						[
							'title' => 'Automatic technical data',
							'body'  => 'IP address, device information, and anonymized analytics collected via cookies or tracking pixels to keep fraud away and optimize site performance.',
						],
						[
							'title' => '3rd party sources',
							'body'  => 'Updates from payment processors, shipping carriers, and authorized performance partners when they fulfill or service orders on our behalf.',
						],
					],
				],
				[
					'id'      => 'how-we-use-data',
					'title'   => 'How We Use Information',
					'intro'   => 'Every use case connects directly to the services you request or the responsibilities we carry as the merchant of record.',
					'bullets' => [
						'Process and confirm orders, send shipping updates, and manage warranty or return requests.',
						'Respond to fitment questions, tuning support, or concierge sourcing needs you submit through contact forms or live chat.',
						'Personalize merchandising, featured categories, and emails based on purchase history or garage preferences you opt into.',
						'Detect fraud, abuse, or platform misuse that could compromise the community or your account.',
						'Satisfy accounting, tax, and product compliance obligations required by law.',
					],
				],
				[
					'id'     => 'sharing-and-disclosure',
					'title'  => 'When We Share Your Data',
					'intro'  => 'We never sell customer data. We only share what is necessary to deliver the product or service you selected.',
					'items'  => [
						[
							'title' => 'Payments & financing',
							'body'  => 'Securely share billing data with PCI-compliant processors (Shopify Payments, PayPal, Affirm, etc.) strictly for transaction approval.',
						],
						[
							'title' => 'Logistics partners',
							'body'  => 'Provide delivery addresses and contact numbers to carriers so they can deliver oversized freight safely and on schedule.',
						],
						[
							'title' => 'Compliance & safety',
							'body'  => 'Share order histories when required to respond to recalls, product audits, chargebacks, or legal requests.',
						],
					],
				],
				[
					'id'     => 'your-choices',
					'title'  => 'Your Choices & Rights',
					'intro'  => 'You may access, update, or limit the personal data we store at any time.',
					'items'  => [
						[
							'title' => 'Access or corrections',
							'body'  => 'Request a copy of the personal data on file or flag inaccuracies and we will update them within 30 days.',
						],
						[
							'title' => 'Marketing preferences',
							'body'  => 'Unsubscribe directly from emails or contact support to adjust SMS, chat, or concierge outreach settings.',
						],
						[
							'title' => 'Deletion requests',
							'body'  => 'Ask us to remove your account and associated personal data unless we must retain records for law, fraud prevention, or open orders.',
						],
					],
					'bullets' => [
						'Most requests are completed within 7–10 business days. Complex archive exports can take up to 30 days.',
						'We will verify your identity before releasing or deleting sensitive information.',
					],
				],
				[
					'id'      => 'security-retention',
					'title'   => 'Security & Retention',
					'intro'   => 'We blend policy, process, and technology layers to defend your information.',
					'bullets' => [
						'Role-based access, MFA, and logging are enforced in all administrative systems.',
						'Sensitive payment data never touches our servers; it is tokenized and stored by the processor.',
						'Operational data is retained only as long as necessary to meet contractual, regulatory, or auditing requirements.',
						'We continuously monitor for unusual activity and partner with third-party security experts for periodic reviews.',
					],
				],
			];
			?>
			<section class="threew-legal__hero">
				<div class="threew-legal__hero-inner">
					<p class="threew-legal__eyebrow">Policy & Compliance</p>
					<h1 class="threew-legal__title"><?php the_title(); ?></h1>
					<p class="threew-legal__meta">
						<?php
						printf(
							/* translators: %s: Date string. */
							esc_html__( 'Last updated %s', 'threew-2025' ),
							esc_html( $updated )
						);
						?>
					</p>
					<p class="threew-legal__intro">We respect how much trust it takes to share your vehicle details, payment preferences, and build goals. This policy explains how 3W Distributing collects, uses, and defends that information.</p>

					<div class="threew-legal__summary-grid" aria-label="<?php esc_attr_e( 'Key privacy highlights', 'threew-2025' ); ?>">
						<?php foreach ( $highlights as $highlight ) : ?>
							<article class="threew-legal__summary-card">
								<h3><?php echo esc_html( $highlight['title'] ); ?></h3>
								<p><?php echo esc_html( $highlight['body'] ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="threew-legal__body" aria-label="<?php esc_attr_e( 'Privacy policy details', 'threew-2025' ); ?>">
				<nav class="threew-legal__toc" aria-label="<?php esc_attr_e( 'Privacy policy navigation', 'threew-2025' ); ?>">
					<p class="threew-legal__toc-title"><?php esc_html_e( 'Quick reference', 'threew-2025' ); ?></p>
					<ul class="threew-legal__toc-list">
						<?php foreach ( $sections as $section ) : ?>
							<li>
								<a href="<?php echo esc_url( '#' . $section['id'] ); ?>">
									<span>#</span><?php echo esc_html( $section['title'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>

				<?php foreach ( $sections as $index => $section ) : ?>
					<section id="<?php echo esc_attr( $section['id'] ); ?>" class="threew-legal__section" aria-labelledby="<?php echo esc_attr( $section['id'] . '-title' ); ?>">
						<div class="threew-legal__section-header">
							<p class="threew-legal__section-number">
								<?php
								printf(
									/* translators: %s: Section number. */
									esc_html__( 'Section %s', 'threew-2025' ),
									esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) )
								);
								?>
							</p>
							<h2 id="<?php echo esc_attr( $section['id'] . '-title' ); ?>" class="threew-legal__section-title"><?php echo esc_html( $section['title'] ); ?></h2>
							<p class="threew-legal__section-intro"><?php echo esc_html( $section['intro'] ); ?></p>
						</div>

						<?php if ( ! empty( $section['items'] ) ) : ?>
							<div class="threew-legal__grid">
								<?php foreach ( $section['items'] as $item ) : ?>
									<article class="threew-legal__card">
										<h3><?php echo esc_html( $item['title'] ); ?></h3>
										<p><?php echo esc_html( $item['body'] ); ?></p>
									</article>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $section['bullets'] ) ) : ?>
							<ul class="threew-legal__list">
								<?php foreach ( $section['bullets'] as $bullet ) : ?>
									<li><?php echo esc_html( $bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>

				<section class="threew-legal__cta" aria-label="<?php esc_attr_e( 'Privacy support', 'threew-2025' ); ?>">
					<div class="threew-legal__cta-card">
						<h2><?php esc_html_e( 'Need clarification or want to start a privacy request?', 'threew-2025' ); ?></h2>
						<p><?php esc_html_e( 'Email our compliance team or reach out through the About page contact form. Please include the email or order number tied to your request so we can verify the account quickly.', 'threew-2025' ); ?></p>
						<div class="threew-legal__cta-actions">
							<a class="threew-legal__cta-link threew-legal__cta-link--primary" href="<?php echo esc_url( home_url( '/about-us/#contact' ) ); ?>">
								<?php esc_html_e( 'Message Support', 'threew-2025' ); ?>
							</a>
							<a class="threew-legal__cta-link" href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>">
								<?php echo esc_html( $contact_email ); ?>
							</a>
						</div>
					</div>
				</section>
			</section>
			<?php
		endwhile;
	endif;
	?>
</main>

<?php
get_footer();
