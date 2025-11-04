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

class ThreeW_SEO {

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
     * Initialize SEO functionality
     */
    public function __construct() {
        $this->site_name = get_bloginfo('name');
        $this->site_description = get_bloginfo('description');

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
        add_action('wp_head', [$this, 'output_breadcrumb_schema'], 6);

        // Content-specific schema
        add_action('wp_head', [$this, 'output_content_schema'], 7);

        // Image alt text filter
        add_filter('the_content', [$this, 'add_missing_image_alt'], 20);

        // Sitemap generation
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

        // Robots meta
        if (is_search() || is_404()) {
            echo '<meta name="robots" content="noindex,follow">' . "\n";
        } else {
            echo '<meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">' . "\n";
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

        if (!empty($this->organization_data['address']['streetAddress'])) {
            $schema['address'] = $this->organization_data['address'];
        }

        if (!empty($this->organization_data['sameAs'])) {
            $schema['sameAs'] = $this->organization_data['sameAs'];
        }

        // Add opening hours (Mon-Fri 8am-5pm PT)
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

        if (is_home() || is_front_page()) {
            return $this->site_description ?: 'Welcome to 3W Distributing - Your trusted source for automotive parts and accessories';
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
     * Register sitemap endpoints
     */
    public function register_sitemap_endpoints() {
        add_rewrite_rule('^sitemap\.xml$', 'index.php?threew_sitemap=1', 'top');
        add_rewrite_rule('^sitemap-([^/]+)\.xml$', 'index.php?threew_sitemap=$matches[1]', 'top');

        add_filter('query_vars', function($vars) {
            $vars[] = 'threew_sitemap';
            return $vars;
        });

        add_action('template_redirect', [$this, 'handle_sitemap_request']);
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
