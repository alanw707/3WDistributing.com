<?php
/**
 * SEO intro section.
 *
 * @package 3w-2025
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$seo_faq_entries = function_exists( 'threew_2025_get_front_page_faq_entries' ) ? threew_2025_get_front_page_faq_entries() : [];
?>

<section class="threew-seo-intro threew-seo-intro--collapsible" aria-labelledby="threew-seo-heading">
    <div class="threew-seo-intro__inner">
        <details class="threew-seo-accordion" data-threew-seo-accordion>
            <summary class="threew-seo-accordion__summary">
                <h2 id="threew-seo-heading" class="threew-seo-intro__title">
                    <?php
                    echo wp_kses(
						__( '3W Distributing <span class="threew-seo-title-nowrap">#1 BRABUS Dealer in North America</span>', 'threew-2025' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    );
                    ?>
                </h2>
                <span class="threew-seo-accordion__hint">
                    <?php esc_html_e( 'About our expertise', 'threew-2025' ); ?>
                </span>
                <span class="threew-seo-accordion__chevron" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </span>
            </summary>
            <div class="threew-seo-accordion__content" id="threew-seo-content">
                <div class="threew-seo-intro__copy">
                    <p><?php esc_html_e( 'For over a decade, 3W Distributing has been a trusted BRABUS dealer for North America, helping Mercedes-Benz, AMG, G-Class, Porsche, BMW, Lamborghini, and exotic car owners source genuine BRABUS parts, luxury aftermarket tuning components, aero kits, wheels, exhaust systems, and complete custom build packages.', 'threew-2025' ); ?></p>

                    <?php foreach ( $seo_faq_entries as $faq_entry ) : ?>
                        <h3><?php echo esc_html( $faq_entry['question'] ); ?></h3>
                        <p><?php echo esc_html( $faq_entry['answer'] ); ?></p>
                    <?php endforeach; ?>

                    <p><?php esc_html_e( 'When you choose 3W Distributing LLC, you’re choosing a team that understands BRABUS fitment, Mercedes-Benz performance upgrades, luxury tuning parts, and exotic car customization. Whether you need one authentic replacement component or a complete upgrade package, we help you transform your vehicle with confidence.', 'threew-2025' ); ?></p>
                    <p class="threew-seo-intro__cta">Don’t settle for generic aftermarket parts—contact 3W Distributing LLC for BRABUS parts, luxury performance upgrades, and expert tuning support.</p>
                </div>
            </div>
        </details>
    </div>
</section>
