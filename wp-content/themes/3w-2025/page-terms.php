<?php
/**
 * Template Name: Terms & Conditions
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="threew-legal threew-legal--terms">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();

			$updated       = get_the_modified_time( get_option( 'date_format' ) );
			$highlights    = [
				[
					'title' => 'Built for Enthusiasts',
					'body'  => 'We curate motorsport-grade parts and match them with honest advice so your build performs the way you expect.',
				],
				[
					'title' => 'Transparency Builds Trust',
					'body'  => 'Clear payment, lead time, and return disclosures keep surprises off your invoice and out of your garage.',
				],
				[
					'title' => 'Shared Responsibility',
					'body'  => 'We provide accurate fitment data while you confirm compatibility with your local regulations and intended use.',
				],
			];
			$sections      = [
				[
					'id'     => 'agreement-overview',
					'title'  => 'Agreement Overview',
					'intro'  => 'By accessing 3wdistributing.com, placing an order, or interacting with our support team, you agree to the policies below plus any additional manufacturer terms that ship with your product.',
					'bullets' => [
						'We may update these Terms to reflect new regulations or fulfillment processes. The “Last updated” date above shows the latest revision.',
						'If a specific clause is found unenforceable, the remaining sections remain in effect.',
					],
				],
				[
					'id'    => 'orders-and-accounts',
					'title' => 'Orders & Accounts',
					'intro' => 'Accounts keep your build history, fitment preferences, and saved addresses in sync across the ecosystem.',
					'items' => [
						[
							'title' => 'Accuracy matters',
							'body'  => 'Please double-check fitment notes, wheel specs, and contact information before checkout. You are responsible for charges incurred under your login.',
						],
						[
							'title' => 'Fraud prevention',
							'body'  => 'We reserve the right to cancel or hold orders pending additional verification if suspicious activity is detected.',
						],
						[
							'title' => 'Communication',
							'body'  => 'Order updates and RMA instructions are delivered to the email on file. Keep it current so freight and concierge partners can reach you quickly.',
						],
					],
				],
				[
					'id'    => 'pricing-and-payment',
					'title' => 'Pricing & Payment',
					'intro' => 'We display prices in USD unless otherwise stated. Taxes are calculated during checkout using the shipping destination.',
					'items' => [
						[
							'title' => 'Payment options',
							'body'  => 'We accept major credit cards, mobile wallets, PayPal, Affirm, wire transfers for large builds, and approved wholesale terms.',
						],
						[
							'title' => 'Quotes and invoices',
							'body'  => 'Custom quotes are valid for 7 days unless parts go on manufacturer allocation. Deposits on special-order items are non-refundable once production begins.',
						],
						[
							'title' => 'Price changes',
							'body'  => 'We work hard to keep pricing accurate but reserve the right to correct mistakes or adjust for supplier increases before shipment.',
						],
					],
					'bullets' => [
						'You authorize us to collect the full amount when the order is placed unless split payments are mutually agreed to in writing.',
					],
				],
				[
					'id'    => 'shipping-and-delivery',
					'title' => 'Shipping & Delivery',
					'intro' => 'We partner with insured carriers that can handle everything from carbon aero kits to palletized engine builds.',
					'items' => [
						[
							'title' => 'Lead times',
							'body'  => 'In‑stock products usually ship within 1–2 business days. Custom fabrication or pre-order items include estimated ship windows on your invoice.',
						],
						[
							'title' => 'Freight inspection',
							'body'  => 'Inspect deliveries immediately. Note any visible damage on the bill of lading and contact us within 48 hours so we can initiate a carrier claim.',
						],
						[
							'title' => 'International shipments',
							'body'  => 'The recipient is responsible for duties, brokerage, and documentation required by the destination country.',
						],
					],
				],
				[
					'id'    => 'returns-and-warranties',
					'title' => 'Returns, Exchanges & Warranties',
					'intro' => 'We want you to be thrilled with every component. If something is off, we will coordinate the right remedy.',
					'items' => [
						[
							'title' => 'Standard returns',
							'body'  => 'Most unopened items can be returned within 30 days of delivery. Restocking fees may apply to special orders, electrical components, or items installed/used.',
						],
						[
							'title' => 'Manufacturer warranties',
							'body'  => 'We facilitate warranty submissions and share inspection findings, but the final determination is made by the manufacturer.',
						],
						[
							'title' => 'RMA process',
							'body'  => 'Request an RMA before shipping anything back. Returns without authorization may be refused.',
						],
					],
					'bullets' => [
						'Wheel/tire packages, custom paint, and ECU flashes are final sale unless defective.',
					],
				],
				[
					'id'      => 'fitment-and-usage',
					'title'   => 'Fitment Data & Usage',
					'intro'   => 'We rely on engineering partners, manufacturer data, and enthusiast testing to share fitment guidance.',
					'bullets' => [
						'Always confirm compliance with local emissions, ride height, and motorsport regulations before installing a component.',
						'Professional installation is recommended for safety-critical systems such as brakes, suspension, and forced induction.',
						'Performance modifications may void factory warranties; consult your OEM or dealer before altering the vehicle.',
					],
				],
				[
					'id'      => 'liability-and-law',
					'title'   => 'Liability, Disputes & Governing Law',
					'intro'   => 'Our goal is collaborative resolution, but we outline ground rules here in case disagreements arise.',
					'bullets' => [
						'3W Distributing is not liable for incidental or consequential damages resulting from improper installation, racing use, or misuse of products.',
						'Nevada state law governs these Terms and any disputes will be handled in Clark County, NV, unless both parties agree to remote arbitration.',
						'Please notify us in writing within 30 days of discovering an issue so we can investigate quickly.',
					],
				],
				[
					'id'      => 'updates-and-contact',
					'title'   => 'Policy Updates & Contact',
					'intro'   => 'We continuously refine logistics, payment partners, and support tooling. When we make a material change we will refresh this page and, when appropriate, notify recent customers by email.',
					'bullets' => [
						'Questions about these Terms can be sent to info@3wdistributing.com.',
						'You may also contact the support line published in the footer during business hours (Mon–Fri, 8 a.m.–5 p.m. PT).',
					],
				],
			];
			?>
			<section class="threew-legal__hero">
				<div class="threew-legal__hero-inner">
					<p class="threew-legal__eyebrow">Service Commitments</p>
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
					<p class="threew-legal__intro">These Terms spell out how we operate, what we promise every enthusiast, and what we need from you so builds stay on schedule.</p>

					<div class="threew-legal__summary-grid" aria-label="<?php esc_attr_e( 'Key service highlights', 'threew-2025' ); ?>">
						<?php foreach ( $highlights as $highlight ) : ?>
							<article class="threew-legal__summary-card">
								<h3><?php echo esc_html( $highlight['title'] ); ?></h3>
								<p><?php echo esc_html( $highlight['body'] ); ?></p>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</section>

			<section class="threew-legal__body" aria-label="<?php esc_attr_e( 'Terms and conditions details', 'threew-2025' ); ?>">
				<nav class="threew-legal__toc" aria-label="<?php esc_attr_e( 'Terms navigation', 'threew-2025' ); ?>">
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
							<ul class="threew-legal__bullets">
								<?php foreach ( $section['bullets'] as $bullet ) : ?>
									<li><?php echo esc_html( $bullet ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>

				<section class="threew-legal__cta" aria-label="<?php esc_attr_e( 'Terms contact', 'threew-2025' ); ?>">
					<div class="threew-legal__cta-card">
						<h2><?php esc_html_e( 'Let’s keep communication open', 'threew-2025' ); ?></h2>
						<p><?php esc_html_e( 'We are happy to walk through these Terms, clarify timelines, or review a build sheet before you commit.', 'threew-2025' ); ?></p>
						<div class="threew-legal__cta-actions">
							<a class="threew-legal__cta-link threew-legal__cta-link--primary" href="<?php echo esc_url( home_url( '/about-us/#contact' ) ); ?>">
								<?php esc_html_e( 'Chat With Support', 'threew-2025' ); ?>
							</a>
							<a class="threew-legal__cta-link" href="tel:17024306622">
								<?php esc_html_e( '702.430.6622', 'threew-2025' ); ?>
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
