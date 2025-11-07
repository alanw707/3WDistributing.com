<?php
/**
 * Vendor logo strip partial.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vendor_base_uri = trailingslashit( get_template_directory_uri() ) . 'images/';

$vendors = [
	[
		'name'  => 'ABT Sportsline',
		'logo'  => 'vendor-abt.png',
		'about' => 'Audi performance systems',
	],
	[
		'name'  => 'AC Schnitzer',
		'logo'  => 'vendor-ac-schnitzer.png',
		'about' => 'BMW tuning atelier',
	],
	[
		'name'  => 'Akrapovič',
		'logo'  => 'vendor-akrapovic.png',
		'about' => 'Titanium exhaust engineering',
	],
	[
		'name'  => 'ADRO',
		'logo'  => 'vendor-adro.svg',
		'about' => 'Aero programs & body kits',
	],
	[
		'name'  => 'Avant Garde',
		'logo'  => 'vendor-avant-garde.png',
		'about' => 'Forged wheel design',
	],
	[
		'name'  => 'AWE Tuning',
		'logo'  => 'vendor-awe.svg',
		'about' => 'Performance exhaust & intakes',
	],
	[
		'name'  => 'BBS',
		'logo'  => 'vendor-bbs.png',
		'about' => 'Motorsport wheel pioneers',
	],
	[
		'name'  => 'Brembo',
		'logo'  => 'vendor-brembo.png',
		'about' => 'Performance braking systems',
	],
	[
		'name'  => 'Brixton Forged',
		'logo'  => 'vendor-brixton.png',
		'about' => 'Concave forged wheels',
	],
	[
		'name'  => 'Capristo',
		'logo'  => 'vendor-capristo.png',
		'about' => 'Valved exhaust technology',
	],
	[
		'name'  => 'Dinan',
		'logo'  => 'vendor-dinan.png',
		'about' => 'BMW & MINI tuning',
	],
	[
		'name'  => 'Eventuri',
		'logo'  => 'vendor-eventuri.svg',
		'about' => 'Carbon intake systems',
	],
	[
		'name'  => 'FI Exhaust',
		'logo'  => 'vendor-fi-exhaust.png',
		'about' => 'Frequency Intelligent exhausts',
	],
	[
		'name'  => 'H&R',
		'logo'  => 'vendor-hr.png',
		'about' => 'Progressive spring systems',
	],
	[
		'name'  => 'OHLINS',
		'logo'  => 'vendor-ohlins.svg',
		'about' => 'Motorsport suspension systems',
	],
	[
		'name'  => 'Recaro',
		'logo'  => 'vendor-recaro.png',
		'about' => 'Sport seating and shells',
	],
	[
		'name'  => 'Startech',
		'logo'  => 'vendor-startech.png',
		'about' => 'Luxury SUV conversions',
	],
	[
		'name'  => 'TechArt',
		'logo'  => 'vendor-techart.png',
		'about' => 'Porsche aero & interior',
	],
	[
		'name'  => 'Brabus',
		'logo'  => 'vendor-brabus.png',
		'about' => 'Flagship Mercedes tuning',
	],
	[
		'name'  => 'Mansory',
		'logo'  => 'vendor-mansory.png',
		'about' => 'Coachbuilt supercars',
	],
	[
		'name'  => 'Urban Automotive',
		'logo'  => 'vendor-urban-automotive.svg',
		'about' => 'Luxury SUV personalization',
	],
];
?>

<section id="vendors" class="threew-vendor-strip has-carbon-texture" aria-labelledby="threew-vendor-strip-heading">
	<div class="threew-vendor-strip__inner">
		<p class="threew-vendor-strip__eyebrow has-body-sm-font-size">
			<?php esc_html_e( 'Trusted vendor network', 'threew-2025' ); ?>
		</p>

		<div class="threew-vendor-strip__headline">
			<h2 id="threew-vendor-strip-heading">
				<?php esc_html_e( 'Motorsport-grade partners at your fingertips', 'threew-2025' ); ?>
			</h2>
			<p class="has-body-md-font-size">
				<?php esc_html_e( 'We collaborate with the most respected tuners and manufacturers in the scene—each vetted for precision, durability, and track-proven results.', 'threew-2025' ); ?>
			</p>
		</div>

		<div class="threew-vendor-strip__marquee" aria-live="off" tabindex="0">
			<div class="threew-vendor-strip__track">
				<?php foreach ( $vendors as $vendor ) : ?>
					<?php
					$logo_alt = sprintf(
						/* translators: %s: Vendor name */
						__( '%s logo', 'threew-2025' ),
						$vendor['name']
					);
					?>
					<figure class="threew-vendor-strip__logo-card">
						<span class="threew-vendor-strip__logo-media" aria-hidden="true">
							<img
								src="<?php echo esc_url( $vendor_base_uri . $vendor['logo'] ); ?>"
								alt="<?php echo esc_attr( $logo_alt ); ?>"
								loading="lazy"
								decoding="async"
							/>
						</span>
						<figcaption class="screen-reader-text">
							<?php
							printf(
								/* translators: 1: Vendor name, 2: vendor focus area */
								esc_html__( '%1$s — %2$s', 'threew-2025' ),
								esc_html( $vendor['name'] ),
								esc_html( $vendor['about'] )
							);
							?>
						</figcaption>
					</figure>
				<?php endforeach; ?>

				<?php foreach ( $vendors as $vendor ) : ?>
					<figure class="threew-vendor-strip__logo-card" aria-hidden="true">
						<span class="threew-vendor-strip__logo-media">
							<img
								src="<?php echo esc_url( $vendor_base_uri . $vendor['logo'] ); ?>"
								alt=""
								loading="lazy"
								decoding="async"
							/>
						</span>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
