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
						__( '3W Distributing <span class="threew-seo-title-nowrap">Your Premier BRABUS Dealer</span>', 'threew-2025' ),
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
                    <p><?php esc_html_e( 'For over a decade, we’ve set the standard in the BRABUS and luxury automotive industry by delivering top-tier expertise, unparalleled customer service, and an extensive selection of premium tuning parts. Whether you’re fine-tuning your luxury car or pursuing a complete transformation, we make it easy to find everything you need in one secure and convenient location.', 'threew-2025' ); ?></p>

                    <?php foreach ( $seo_faq_entries as $faq_entry ) : ?>
                        <h3><?php echo esc_html( $faq_entry['question'] ); ?></h3>
                        <p><?php echo esc_html( $faq_entry['answer'] ); ?></p>
                    <?php endforeach; ?>

                    <p><?php esc_html_e( 'When you choose 3W Distribution LLC, you’re choosing a team that’s as passionate about luxury cars as you are. Whether you’re shopping for premium parts or looking for a complete upgrade, we’re here to help you transform your vehicle into something truly extraordinary.', 'threew-2025' ); ?></p>
                    <p class="threew-seo-intro__cta">Don’t settle for less—contact 3W Distribution LLC today and let us redefine your ride.</p>
                </div>
            </div>
        </details>
    </div>
</section>
