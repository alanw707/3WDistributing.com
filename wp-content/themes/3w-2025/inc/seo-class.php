<?php
/**
 * SEO Utility Class for 3W Distribution
 *
 * Handles meta tags, Open Graph, Twitter Cards, Schema.org markup,
 * canonical URLs, and other SEO optimizations.
 *
 * @package ThreeW_2025
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('threew_2025_get_front_page_faq_entries')) {
    function threew_2025_get_front_page_faq_entries() {
        return [
            [
                'question' => __('Which tuning brands does 3W partner with?', 'threew-2025'),
                'answer' => __('At 3W, we work with the best in the industry, sourcing parts from leading automotive brands, including 1016 Industries, 463 Industries, ABT, BBS, Brabus, Capristo, Lumma Design, Mansory, StarTech, TechArt, and Vorsteiner. These trusted partners enable us to offer exclusive deals and cutting-edge products, so you can elevate your ride to perfection.', 'threew-2025'),
            ],
            [
                'question' => __('What services does 3W Distributing provide?', 'threew-2025'),
                'answer' => __('Your luxury car deserves more than standard care—it deserves craftsmanship. At 3W, we provide full in-house automotive services, from precision tuning to custom builds designed from the ground up. Our team of industry-leading mechanics and technicians are experts in the art of performance tuning and customization, ensuring your car doesn’t just stand out but truly reflects your vision.', 'threew-2025'),
            ],
            [
                'question' => __('How much experience does 3W Distributing have?', 'threew-2025'),
                'answer' => __('While we’ve proudly led the BRABUS market for over 10 years, our roots run deeper, with more than 20 years of experience in tuning and customizing luxury vehicles. Over these years, we’ve honed our craft and built strong, long-term relationships with the best manufacturers in the industry. Our dedication to quality, innovation, and customer satisfaction ensures you receive nothing but the finest service and products available.', 'threew-2025'),
            ],
        ];
    }
}

class ThreeW_SEO {
    private const SITEMAP_REWRITE_VERSION = 2;

    /**
     * Site name for consistent branding
     * @var string
     */
    private $site_name;

    /**
     * Site description
     * @var string
     */
    private $site_description;

    /**
     * Organization schema data
     * @var array
     */
    private $organization_data;

    /**
     * Home page document title
     * @var string
     */
    private $home_title;

    /**
     * Home page meta description
     * @var string
     */
    private $home_description;

    /**
     * Default social card image data
     * @var array|null
     */
    private $default_social_image;

    /**
     * Initialize SEO functionality
     */
    public function __construct() {
        $this->site_name = get_bloginfo('name');
        $this->site_description = get_bloginfo('description');
        $this->home_title = __('3W Distributing | Performance Parts, Lighting & Bespoke Kits', 'threew-2025');
        $this->home_description = __('Performance parts, lighting, and bespoke fitment expertise for BRABUS, Mansory, and luxury builds.', 'threew-2025');
        $this->default_social_image = $this->prepare_default_social_image();

        // Organization data for schema markup
        $this->organization_data = [
            'name' => '3W Distributing',
            'url' => home_url('/'),
            'logo' => get_theme_file_uri('assets/images/logo.png'),
            'description' => 'Performance parts, lighting, and bespoke kits for premium builds. Trusted distributor for global tuning brands.',
            'telephone' => '+1-702-430-6622',
            'email' => 'info@3wdistributing.com',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '', // Optional: Add physical address if public
                'addressLocality' => 'Las Vegas',
                'addressRegion' => 'NV',
                'postalCode' => '',
                'addressCountry' => 'US'
            ],
            'sameAs' => [
                'https://www.facebook.com/3wdistributing/',
                'https://twitter.com/3wdistributing',
                'https://www.instagram.com/3wdistributing/',
                'https://www.youtube.com/@3wdistributing'
            ]
        ];

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Meta tags and Open Graph
        add_action('wp_head', [$this, 'output_meta_tags'], 1);
        add_action('wp_head', [$this, 'output_open_graph'], 2);
        add_action('wp_head', [$this, 'output_twitter_cards'], 3);
        add_action('wp_head', [$this, 'output_canonical_url'], 4);

        // Schema.org markup
        add_action('wp_head', [$this, 'output_organization_schema'], 5);
        add_action('wp_head', [$this, 'output_website_schema'], 6);
        add_action('wp_head', [$this, 'output_site_navigation_schema'], 6);
        add_action('wp_head', [$this, 'output_breadcrumb_schema'], 7);

        // Content-specific + FAQ schema
        add_action('wp_head', [$this, 'output_content_schema'], 8);
        add_action('wp_head', [$this, 'output_faq_schema'], 9);

        // Filters
        add_filter('the_content', [$this, 'add_missing_image_alt'], 20);
        add_filter('wp_robots', [$this, 'filter_wp_robots']);
        add_filter('pre_get_document_title', [$this, 'filter_document_title']);

        // Sitemap generation
        add_action('init', [$this, 'redirect_legacy_sitemap_index'], 0);
        add_action('init', [$this, 'register_sitemap_endpoints']);
    }

    /**
     * Output meta tags
     */
    public function output_meta_tags() {
        $description = $this->get_meta_description();
        $keywords = $this->get_meta_keywords();

        echo "\n<!-- SEO Meta Tags -->\n";
        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";

        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }

        // Author meta for single posts
        if (is_single()) {
            $author_name = get_the_author();
            echo '<meta name="author" content="' . esc_attr($author_name) . '">' . "\n";
        }
    }

    /**
     * Output Open Graph tags
     */
    public function output_open_graph() {
        echo "\n<!-- Open Graph / Facebook -->\n";
        echo '<meta property="og:type" content="' . esc_attr($this->get_og_type()) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr($this->site_name) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($this->get_og_title()) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($this->get_meta_description()) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($this->get_canonical_url()) . '">' . "\n";

        $image = $this->get_og_image();
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image['url']) . '">' . "\n";
            if (isset($image['width'])) {
                echo '<meta property="og:image:width" content="' . esc_attr($image['width']) . '">' . "\n";
            }
            if (isset($image['height'])) {
                echo '<meta property="og:image:height" content="' . esc_attr($image['height']) . '">' . "\n";
            }
            if (isset($image['alt'])) {
                echo '<meta property="og:image:alt" content="' . esc_attr($image['alt']) . '">' . "\n";
            }
        }

        echo '<meta property="og:locale" content="en_US">' . "\n";

        if (is_single()) {
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
            $primary_category = get_the_category();
            if (!empty($primary_category)) {
                echo '<meta property="article:section" content="' . esc_attr($primary_category[0]->name) . '">' . "\n";
            }
            $tags = get_the_tags();
            if ($tags) {
                foreach ($tags as $tag) {
                    echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
                }
            }
        }
    }

    /**
     * Output Twitter Card tags
     */
    public function output_twitter_cards() {
        echo "\n<!-- Twitter Card -->\n";
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($this->get_og_title()) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($this->get_meta_description()) . '">' . "\n";

        $image = $this->get_og_image();
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image['url']) . '">' . "\n";
            if (isset($image['alt'])) {
                echo '<meta name="twitter:image:alt" content="' . esc_attr($image['alt']) . '">' . "\n";
            }
        }

        // Add Twitter handle
        echo '<meta name="twitter:site" content="@3wdistributing">' . "\n";
        echo '<meta name="twitter:creator" content="@3wdistributing">' . "\n";
    }

    /**
     * Output canonical URL
     */
    public function output_canonical_url() {
        $canonical = $this->get_canonical_url();
        echo "\n<!-- Canonical URL -->\n";
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

        // Pagination rel tags
        if (is_paged() || is_page()) {
            global $paged, $page;

            if ($paged > 1 || $page > 1) {
                $prev_link = $this->get_pagination_url('prev');
                if ($prev_link) {
                    echo '<link rel="prev" href="' . esc_url($prev_link) . '">' . "\n";
                }
            }

            $next_link = $this->get_pagination_url('next');
            if ($next_link) {
                echo '<link rel="next" href="' . esc_url($next_link) . '">' . "\n";
            }
        }
    }

    /**
     * Output Organization schema
     */
    public function output_organization_schema() {
        if (!is_front_page() && !is_home()) {
            return;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->organization_data['name'],
            'url' => $this->organization_data['url'],
            'logo' => $this->organization_data['logo'],
            'description' => $this->organization_data['description'],
        ];

        if (!empty($this->organization_data['telephone'])) {
            $schema['telephone'] = $this->organization_data['telephone'];
        }

        if (!empty($this->organization_data['email'])) {
            $schema['email'] = $this->organization_data['email'];
        }

        if (!empty($this->organization_data['sameAs'])) {
            $schema['sameAs'] = $this->organization_data['sameAs'];
        }

        if (!empty($this->organization_data['address']['addressLocality'])) {
            $schema['address'] = $this->organization_data['address'];
        }

        $schema['openingHoursSpecification'] = [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday'
            ],
            'opens' => '08:00',
            'closes' => '17:00',
            'timeZone' => 'America/Los_Angeles'
        ];

        echo "\n<!-- Organization Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output WebSite schema with search action for shop queries
     */
    public function output_website_schema() {
        if (!is_front_page() && !is_home()) {
            return;
        }

        $search_target = threew_2025_get_shop_url('{search_term_string}');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => home_url('/'),
            'name' => $this->site_name,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $search_target,
                'query-input' => 'required name=search_term_string',
            ],
        ];

        echo "\n<!-- WebSite Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output SiteNavigationElement schema to encourage sitelinks
     */
    public function output_site_navigation_schema() {
        $nav_items = $this->get_site_navigation_items();
        if (count($nav_items) < 2) {
            return;
        }

        $item_list = [];
        foreach ($nav_items as $index => $item) {
            $item_list[] = [
                '@type' => 'SiteNavigationElement',
                'position' => $index + 1,
                'name' => $item['name'],
                'url' => $item['url'],
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => $item_list,
        ];

        echo "\n<!-- Site Navigation Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output Breadcrumb schema
     */
    public function output_breadcrumb_schema() {
        if (is_front_page()) {
            return;
        }

        $breadcrumbs = $this->get_breadcrumbs();
        if (empty($breadcrumbs)) {
            return;
        }

        $items = [];
        foreach ($breadcrumbs as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['title'],
                'item' => $crumb['url']
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];

        echo "\n<!-- Breadcrumb Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output content-specific schema (Article, Product, etc.)
     */
    public function output_content_schema() {
        if (is_single() && get_post_type() === 'post') {
            $this->output_article_schema();
        } elseif (is_singular('product') && function_exists('wc_get_product')) {
            $this->output_product_schema();
        }
    }

    /**
     * Output FAQPage schema for front-page accordion copy
     */
    public function output_faq_schema() {
        if (!is_front_page() && !is_home()) {
            return;
        }

        $faqs = threew_2025_get_front_page_faq_entries();
        if (empty($faqs)) {
            return;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [],
        ];

        foreach ($faqs as $faq) {
            $schema['mainEntity'][] = [
                '@type' => 'Question',
                'name' => wp_strip_all_tags($faq['question']),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => wp_strip_all_tags($faq['answer']),
                ],
            ];
        }

        echo "\n<!-- FAQ Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output Article schema for blog posts
     */
    private function output_article_schema() {
        global $post;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => $this->get_excerpt(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author()
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->organization_data['name'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->organization_data['logo']
                ]
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink()
            ]
        ];

        $categories = get_the_category();
        if (!empty($categories)) {
            $schema['articleSection'] = $categories[0]->name;
        }

        $plain_content = wp_strip_all_tags(get_post_field('post_content', get_the_ID()));
        if (!empty($plain_content)) {
            $schema['wordCount'] = str_word_count($plain_content);
            $schema['articleBody'] = wp_html_excerpt($plain_content, 4000, '...');
        }

        // Add featured image if available
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_data = wp_get_attachment_image_src($image_id, 'full');

            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $image_data[0],
                'width' => $image_data[1],
                'height' => $image_data[2]
            ];
        }

        if (!isset($schema['image']) && $this->default_social_image) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $this->default_social_image['url'],
                'width' => $this->default_social_image['width'] ?? null,
                'height' => $this->default_social_image['height'] ?? null,
            ];
        }

        echo "\n<!-- Article Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Output Product schema for WooCommerce products
     */
    private function output_product_schema() {
        if (!function_exists('wc_get_product')) {
            return;
        }

        global $product;

        if (!$product) {
            $product = wc_get_product(get_the_ID());
        }

        if (!$product) {
            return;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->get_name(),
            'description' => wp_strip_all_tags($product->get_description()),
            'sku' => $product->get_sku(),
            'offers' => [
                '@type' => 'Offer',
                'url' => get_permalink(),
                'priceCurrency' => get_woocommerce_currency(),
                'price' => $product->get_price(),
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->organization_data['name']
                ]
            ]
        ];

        // Add product image
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            $schema['image'] = $image_data[0];
        }

        // Add brand if available
        $schema['brand'] = [
            '@type' => 'Brand',
            'name' => $this->organization_data['name']
        ];

        // Add aggregate rating if reviews exist
        $rating_count = $product->get_rating_count();
        if ($rating_count > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->get_average_rating(),
                'reviewCount' => $rating_count
            ];
        }

        echo "\n<!-- Product Schema -->\n";
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }

    /**
     * Get meta description
     */
    private function get_meta_description() {
        if (is_home() || is_front_page()) {
            return $this->home_description ?: ($this->site_description ?: 'Welcome to 3W Distributing - Your trusted source for automotive parts and accessories');
        }

        if (is_singular()) {
            // For single posts/pages, use excerpt or auto-generate
            $excerpt = $this->get_excerpt();
            if ($excerpt) {
                return $excerpt;
            }
        }

        if (is_category() || is_tag() || is_tax()) {
            $description = term_description();
            if ($description) {
                return wp_strip_all_tags($description);
            }

            $term = get_queried_object();
            return sprintf('Browse %s articles and products', $term->name);
        }

        if (is_author()) {
            $author = get_queried_object();
            return sprintf('Articles by %s', $author->display_name);
        }

        if (is_search()) {
            return sprintf('Search results for: %s', get_search_query());
        }

        if (is_404()) {
            return 'Page not found';
        }

        return $this->site_description;
    }

    /**
     * Get meta keywords (optional, less important for modern SEO)
     */
    private function get_meta_keywords() {
        if (is_singular()) {
            $tags = get_the_tags();
            if ($tags && !is_wp_error($tags)) {
                return implode(', ', wp_list_pluck($tags, 'name'));
            }
        }

        return '';
    }

    /**
     * Get Open Graph type
     */
    private function get_og_type() {
        if (is_singular('product')) {
            return 'product';
        }

        if (is_single()) {
            return 'article';
        }

        return 'website';
    }

    /**
     * Get Open Graph title
     */
    private function get_og_title() {
        if (is_front_page() || is_home()) {
            return $this->home_title;
        }

        if (is_singular()) {
            return get_the_title();
        }

        if (is_category() || is_tag() || is_tax()) {
            return single_term_title('', false);
        }

        if (is_author()) {
            $author = get_queried_object();
            return sprintf('Articles by %s', $author->display_name);
        }

        if (is_search()) {
            return sprintf('Search: %s', get_search_query());
        }

        return $this->site_name;
    }

    /**
     * Get Open Graph image
     */
    private function get_og_image() {
        $image = null;

        // For single posts/pages, use featured image
        if (is_singular() && has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);

            $image = [
                'url' => $image_data[0],
                'width' => $image_data[1],
                'height' => $image_data[2],
                'alt' => $image_alt ?: get_the_title()
            ];
        }

        if (!$image && $this->default_social_image) {
            $image = $this->default_social_image;
        }

        // Fallback to site logo or default image
        if (!$image) {
            $custom_logo_id = get_theme_mod('custom_logo');
            if ($custom_logo_id) {
                $logo_data = wp_get_attachment_image_src($custom_logo_id, 'full');
                $image = [
                    'url' => $logo_data[0],
                    'width' => $logo_data[1],
                    'height' => $logo_data[2],
                    'alt' => $this->site_name
                ];
            }
        }

        return $image;
    }

    /**
     * Build a reusable default social image payload
     */
    private function prepare_default_social_image() {
        $path = get_theme_file_path('images/social-card-default.jpg');
        if (!file_exists($path)) {
            return null;
        }

        $dimensions = @getimagesize($path);

        return [
            'url' => get_theme_file_uri('images/social-card-default.jpg'),
            'width' => $dimensions[0] ?? 1200,
            'height' => $dimensions[1] ?? 630,
            'alt' => __('3W Distributing performance build showcase', 'threew-2025'),
        ];
    }

    /**
     * Get canonical URL
     */
    private function get_canonical_url() {
        global $wp;

        if (is_singular()) {
            return get_permalink();
        }

        if (is_category() || is_tag() || is_tax()) {
            return get_term_link(get_queried_object());
        }

        if (is_author()) {
            return get_author_posts_url(get_queried_object_id());
        }

        return home_url(add_query_arg([], $wp->request));
    }

    /**
     * Get pagination URL (prev/next)
     */
    private function get_pagination_url($direction = 'next') {
        global $paged, $page;

        $current_page = max(1, $paged ?: $page);

        if ($direction === 'prev' && $current_page > 1) {
            return get_pagenum_link($current_page - 1);
        }

        if ($direction === 'next') {
            return get_pagenum_link($current_page + 1);
        }

        return null;
    }

    /**
     * Get breadcrumbs for schema
     */
    private function get_breadcrumbs() {
        $breadcrumbs = [];

        // Home
        $breadcrumbs[] = [
            'title' => 'Home',
            'url' => home_url('/')
        ];

        if (is_singular('post')) {
            $categories = get_the_category();
            if (!empty($categories)) {
                $category = $categories[0];
                $breadcrumbs[] = [
                    'title' => $category->name,
                    'url' => get_category_link($category->term_id)
                ];
            }

            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => get_permalink()
            ];
        } elseif (is_singular('product')) {
            $breadcrumbs[] = [
                'title' => 'Shop',
                'url' => threew_2025_get_shop_url()
            ];

            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => get_permalink()
            ];
        } elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $breadcrumbs[] = [
                'title' => $term->name,
                'url' => get_term_link($term)
            ];
        } elseif (is_page()) {
            $breadcrumbs[] = [
                'title' => get_the_title(),
                'url' => get_permalink()
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Build a list of top-level navigation items for schema output
     *
     * @return array[]
     */
    private function get_site_navigation_items() {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }

        $cached = [];
        $locations = get_nav_menu_locations();
        if (empty($locations)) {
            return $cached;
        }

        $menu_id = $locations['primary'] ?? reset($locations);
        if (empty($menu_id)) {
            return $cached;
        }

        $menu_items = wp_get_nav_menu_items($menu_id, ['update_post_term_cache' => false]);
        if (empty($menu_items)) {
            return $cached;
        }

        foreach ($menu_items as $item) {
            if ((int) $item->menu_item_parent !== 0) {
                continue;
            }

            $name = trim(wp_strip_all_tags($item->title));
            $url = esc_url_raw($item->url);

            if ($name === '' || $url === '') {
                continue;
            }

            $cached[] = [
                'name' => $name,
                'url' => $url,
            ];

            if (count($cached) >= 8) {
                break;
            }
        }

        return $cached;
    }

    /**
     * Get excerpt for meta description
     */
    private function get_excerpt() {
        if (has_excerpt()) {
            return wp_strip_all_tags(get_the_excerpt());
        }

        $content = get_the_content();
        $excerpt = wp_trim_words(wp_strip_all_tags($content), 30);

        return $excerpt;
    }

    /**
     * Add missing image alt attributes
     */
    public function add_missing_image_alt($content) {
        if (!preg_match_all('/<img[^>]+>/', $content, $matches)) {
            return $content;
        }

        foreach ($matches[0] as $img_tag) {
            if (strpos($img_tag, 'alt=') === false) {
                // Extract src to generate meaningful alt text
                preg_match('/src=["\']([^"\']+)["\']/', $img_tag, $src);
                $alt = '';

                if (isset($src[1])) {
                    $filename = basename($src[1]);
                    $alt = ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));
                }

                if (empty($alt)) {
                    $alt = get_the_title();
                }

                $new_img_tag = str_replace('<img', '<img alt="' . esc_attr($alt) . '"', $img_tag);
                $content = str_replace($img_tag, $new_img_tag, $content);
            }
        }

        return $content;
    }

    /**
     * Normalize robots directives via wp_robots filter
     */
    public function filter_wp_robots($robots) {
        if (is_search() || is_404()) {
            return [
                'noindex' => true,
                'follow' => true,
            ];
        }

        unset($robots['noindex'], $robots['nofollow']);
        $robots['follow'] = true;
        $robots['max-snippet'] = -1;
        $robots['max-image-preview'] = 'large';
        $robots['max-video-preview'] = -1;

        return $robots;
    }

    /**
     * Improve document titles for key templates
     */
    public function filter_document_title($title) {
        if (is_front_page() || is_home()) {
            return $this->home_title;
        }

        if (is_category() || is_tag() || is_tax()) {
            $term_title = single_term_title('', false);
            return trim($term_title) !== '' ? sprintf('%s | %s', $term_title, $this->site_name) : $title;
        }

        if (is_post_type_archive('product')) {
            $archive_title = post_type_archive_title('', false);
            if ($archive_title) {
                return sprintf('%s | %s', $archive_title, $this->site_name);
            }
        }

        return $title;
    }

    /**
     * Register sitemap endpoints
     */
    public function register_sitemap_endpoints() {
        if ($this->is_legacy_sitemap_request()) {
            $this->render_sitemap_index();
        }

        add_rewrite_rule('^sitemap\.xml$', 'index.php?threew_sitemap=1', 'top');
        add_rewrite_rule('^sitemap_index\.xml$', 'index.php?threew_sitemap=1', 'top');
        add_rewrite_rule('^sitemap-([^/]+)\.xml$', 'index.php?threew_sitemap=$matches[1]', 'top');

        add_filter('query_vars', function($vars) {
            $vars[] = 'threew_sitemap';
            return $vars;
        });

        add_filter('request', [$this, 'map_legacy_sitemap_request']);
        add_action('parse_request', [$this, 'maybe_handle_legacy_sitemap_parse'], 1);
        add_action('template_redirect', [$this, 'handle_legacy_sitemap_index'], 0);
        add_action('template_redirect', [$this, 'handle_sitemap_request']);
        add_filter('pre_handle_404', [$this, 'intercept_legacy_sitemap_404'], 10, 2);
        add_filter('template_include', [$this, 'maybe_override_sitemap_template'], 0);
        add_action('template_redirect', [$this, 'debug_sitemap_probe'], 0);

        $stored_version = (int) get_option('threew_sitemap_rewrite_version', 0);
        if ($stored_version < self::SITEMAP_REWRITE_VERSION && !wp_installing()) {
            flush_rewrite_rules(false);
            update_option('threew_sitemap_rewrite_version', self::SITEMAP_REWRITE_VERSION, false);
        }
    }

    public function handle_legacy_sitemap_index() {
        if (isset($_GET['threew_debug']) && $_GET['threew_debug'] === '1') {
            global $wp_query;
            header('Content-Type: application/json; charset=utf-8');
            echo wp_json_encode(
                [
                    'request_uri' => isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '',
                    'query_vars'  => $wp_query ? $wp_query->query_vars : [],
                ],
                JSON_PRETTY_PRINT
            );
            exit;
        }

        if (!empty(get_query_var('threew_sitemap'))) {
            return;
        }

        if (!$this->is_legacy_sitemap_request()) {
            return;
        }

        $this->render_sitemap_index();
    }

    public function redirect_legacy_sitemap_index() {
        if ($this->is_legacy_sitemap_request()) {
            wp_redirect(home_url('/sitemap.xml/'), 301, 'ThreeW Sitemap Redirect');
            exit;
        }
    }

    public function maybe_handle_legacy_sitemap_parse($wp) {
        if ($this->is_legacy_sitemap_request()) {
            $this->render_sitemap_index();
        }
    }

    public function intercept_legacy_sitemap_404($preempt, $wp_query) {
        if ($this->is_legacy_sitemap_request()) {
            $this->render_sitemap_index();
            return true;
        }

        return $preempt;
    }

    public function maybe_override_sitemap_template($template) {
        if ($this->is_legacy_sitemap_request()) {
            $this->render_sitemap_index();
        }

        return $template;
    }

    public function map_legacy_sitemap_request($query_vars) {
        $is_request = $this->is_legacy_sitemap_request()
            || (isset($query_vars['pagename']) && $query_vars['pagename'] === 'sitemap_index.xml')
            || (isset($query_vars['name']) && $query_vars['name'] === 'sitemap_index.xml')
            || (isset($query_vars['attachment']) && $query_vars['attachment'] === 'sitemap_index.xml');

        if ($is_request) {
            $query_vars['threew_sitemap'] = '1';
            unset($query_vars['pagename'], $query_vars['name'], $query_vars['attachment']);
        }

        if (isset($_GET['threew_debug']) && $_GET['threew_debug'] === '1') {
            header('X-ThreeW-Debug-QVars: ' . rawurlencode(json_encode($query_vars)));
        }

        return $query_vars;
    }

    private function is_legacy_sitemap_request() {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        if ($request_uri === '') {
            return false;
        }

        $path = strtok($request_uri, '?');
        if ($path === false || $path === '') {
            return false;
        }

        $path = rtrim($path, '/');
        if ($path === '') {
            return false;
        }

        return substr($path, -strlen('sitemap_index.xml')) === 'sitemap_index.xml';
    }

    private function render_sitemap_index() {
        header('X-ThreeW-Legacy-Sitemap: 1');
        header('Content-Type: application/xml; charset=utf-8');
        echo $this->generate_sitemap_index();
        exit;
    }

    public function debug_sitemap_probe() {
        if (isset($_GET['threew_probe']) && $_GET['threew_probe'] === '1') {
            global $wp_query;
            header('Content-Type: application/json; charset=utf-8');
            echo wp_json_encode(
                [
                    'request_uri' => isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '',
                    'query_vars'  => $wp_query ? $wp_query->query_vars : [],
                ],
                JSON_PRETTY_PRINT
            );
            exit;
        }
    }

    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_request() {
        $sitemap = get_query_var('threew_sitemap');

        if (!$sitemap) {
            return;
        }

        header('Content-Type: application/xml; charset=utf-8');

        if ($sitemap === '1') {
            // Main sitemap index
            echo $this->generate_sitemap_index();
        } else {
            // Individual sitemap
            echo $this->generate_sitemap($sitemap);
        }

        exit;
    }

    /**
     * Generate sitemap index
     */
    private function generate_sitemap_index() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $sitemaps = ['posts', 'pages', 'categories', 'products'];

        foreach ($sitemaps as $sitemap) {
            $xml .= '  <sitemap>' . "\n";
            $xml .= '    <loc>' . home_url("/sitemap-{$sitemap}.xml") . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('c') . '</lastmod>' . "\n";
            $xml .= '  </sitemap>' . "\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Generate individual sitemap
     */
    private function generate_sitemap($type) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        switch ($type) {
            case 'posts':
                $posts = get_posts(['numberposts' => -1, 'post_type' => 'post']);
                foreach ($posts as $post) {
                    $xml .= $this->generate_sitemap_entry(
                        get_permalink($post),
                        get_the_modified_date('c', $post),
                        '0.8',
                        'weekly'
                    );
                }
                break;

            case 'pages':
                $pages = get_pages();
                foreach ($pages as $page) {
                    $xml .= $this->generate_sitemap_entry(
                        get_permalink($page),
                        get_the_modified_date('c', $page),
                        '0.6',
                        'monthly'
                    );
                }
                break;

            case 'categories':
                $categories = get_categories();
                foreach ($categories as $category) {
                    $xml .= $this->generate_sitemap_entry(
                        get_category_link($category),
                        date('c'),
                        '0.5',
                        'weekly'
                    );
                }
                break;

            case 'products':
                if (function_exists('wc_get_products')) {
                    $products = wc_get_products(['limit' => -1]);
                    foreach ($products as $product) {
                        $xml .= $this->generate_sitemap_entry(
                            get_permalink($product->get_id()),
                            get_the_modified_date('c', $product->get_id()),
                            '0.9',
                            'daily'
                        );
                    }
                }
                break;
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate sitemap entry
     */
    private function generate_sitemap_entry($url, $lastmod, $priority, $changefreq) {
        $entry = '  <url>' . "\n";
        $entry .= '    <loc>' . esc_url($url) . '</loc>' . "\n";
        $entry .= '    <lastmod>' . $lastmod . '</lastmod>' . "\n";
        $entry .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        $entry .= '    <priority>' . $priority . '</priority>' . "\n";
        $entry .= '  </url>' . "\n";

        return $entry;
    }
}

// Initialize SEO class
function threew_seo_init() {
    return new ThreeW_SEO();
}

add_action('after_setup_theme', 'threew_seo_init', 20);
