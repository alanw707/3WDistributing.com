<?php
/**
 * Vehicle Fitment Import System
 *
 * Extracts vehicle fitment data from WooCommerce products and creates
 * structured product attributes for the fitment selector.
 *
 * @package ThreeW_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Remote Product Fetcher
 *
 * Fetches products from shop.3wdistributing.com via WooCommerce REST API
 */
class ThreeW_Remote_Product_Fetcher {

    private static $shop_url;
    private static $consumer_key;
    private static $consumer_secret;

    /**
     * Initialize with credentials from environment
     */
    public static function init() {
        self::$shop_url = getenv('SHOP_WP_BASE_URL') ?: 'https://shop.3wdistributing.com';
        self::$consumer_key = getenv('SHOP_WP_APP_USER');
        self::$consumer_secret = getenv('SHOP_WP_APP_PASSWORD');
    }

    /**
     * Fetch products from remote shop site via public pages or JSON file
     *
     * @param int    $limit  Number of products to fetch (0 = all)
     * @param string $source Optional JSON file path to read from
     * @return array Array of product data
     */
    public static function fetch_products($limit = 0, $source = null) {
        self::init();

        // If source JSON file is provided, read from it
        if (!empty($source)) {
            return self::fetch_from_json($source, $limit);
        }

        // Otherwise, scrape from public shop pages
        $products = [];
        $page = 1;
        $fetched = 0;

        WP_CLI::log('Scraping products from public shop pages...');

        do {
            $url = self::$shop_url . '/shop/page/' . $page . '/';

            $response = wp_remote_get($url, ['timeout' => 30]);

            if (is_wp_error($response)) {
                WP_CLI::warning('Failed to fetch page ' . $page . ': ' . $response->get_error_message());
                break;
            }

            $html = wp_remote_retrieve_body($response);

            // Extract product URLs from shop page
            preg_match_all('/<a[^>]+href="([^"]+\/product\/[^"]+)"[^>]*>/i', $html, $matches);

            if (empty($matches[1])) {
                break; // No more products found
            }

            $product_urls = array_unique($matches[1]);

            foreach ($product_urls as $product_url) {
                if ($limit > 0 && $fetched >= $limit) {
                    break 2;
                }

                // Fetch individual product page
                $product_data = self::fetch_product_page($product_url);

                if ($product_data) {
                    $products[] = $product_data;
                    $fetched++;
                }
            }

            $page++;

            // Safety limit: don't fetch more than 20 pages
            if ($page > 20) {
                break;
            }

        } while (true);

        WP_CLI::log("Fetched {$fetched} products");

        return $products;
    }

    /**
     * Fetch products from JSON file (scraped via Playwright/Chrome DevTools)
     *
     * @param string $file_path Path to JSON file
     * @param int    $limit     Optional limit
     * @return array Array of product data
     */
    private static function fetch_from_json($file_path, $limit = 0) {
        // Resolve path relative to theme root
        if (!file_exists($file_path)) {
            $theme_root = get_template_directory();
            $file_path = $theme_root . '/' . $file_path;
        }

        if (!file_exists($file_path)) {
            WP_CLI::error("JSON file not found: {$file_path}");
            return [];
        }

        WP_CLI::log("Reading products from JSON file: {$file_path}");

        $json = file_get_contents($file_path);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            WP_CLI::error('Invalid JSON file: ' . json_last_error_msg());
            return [];
        }

        // Extract products array from the JSON structure
        $products = isset($data['products']) ? $data['products'] : $data;

        // Show metadata if available
        if (isset($data['metadata'])) {
            WP_CLI::log('Source: ' . ($data['metadata']['source'] ?? 'unknown'));
            WP_CLI::log('Scraped at: ' . ($data['metadata']['scrapedAt'] ?? 'unknown'));
            WP_CLI::log('Total products in file: ' . ($data['metadata']['totalProducts'] ?? count($products)));
        }

        // Apply limit if specified
        if ($limit > 0 && count($products) > $limit) {
            $products = array_slice($products, 0, $limit);
            WP_CLI::log("Limited to {$limit} products");
        }

        // Convert JSON products to the expected format
        $converted = [];
        foreach ($products as $product) {
            $converted[] = [
                'id'         => isset($product['id']) ? $product['id'] : md5($product['url'] ?? uniqid()),
                'name'       => $product['name'] ?? '',
                'categories' => $product['categories'] ?? [],
                'tags'       => $product['tags'] ?? [],
            ];
        }

        WP_CLI::log("Loaded " . count($converted) . " products from JSON");

        return $converted;
    }

    /**
     * Fetch and parse individual product page
     *
     * @param string $url Product URL
     * @return array|null Product data or null if failed
     */
    private static function fetch_product_page($url) {
        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            return null;
        }

        $html = wp_remote_retrieve_body($response);

        // Extract product name
        preg_match('/<h1[^>]*class="[^"]*product_title[^"]*"[^>]*>([^<]+)<\/h1>/i', $html, $title_match);
        $name = isset($title_match[1]) ? trim($title_match[1]) : '';

        if (empty($name)) {
            return null;
        }

        // Extract categories
        preg_match_all('/<a[^>]+rel="tag"[^>]*>([^<]+)<\/a>/i', $html, $cat_matches);
        $categories = isset($cat_matches[1]) ? $cat_matches[1] : [];

        // Extract tags
        $tags = [];

        return [
            'id'         => md5($url), // Use URL hash as ID
            'name'       => html_entity_decode($name),
            'categories' => array_map('html_entity_decode', $categories),
            'tags'       => $tags,
        ];
    }

    /**
     * Convert remote product data to format compatible with parser
     *
     * @param array $remote_product Remote product data from API
     * @return object Product-like object for parser
     */
    public static function to_product_object($remote_product) {
        return (object) [
            'id'         => $remote_product['id'],
            'name'       => $remote_product['name'],
            'categories' => array_column($remote_product['categories'], 'name'),
            'tags'       => array_column($remote_product['tags'], 'name'),
        ];
    }
}

/**
 * Vehicle Data Parser
 *
 * Parses product titles, categories, and tags to extract vehicle information.
 */
class ThreeW_Vehicle_Parser {

    /**
     * Chassis code to year mapping
     */
    private static $chassis_years = [
        // Mercedes G-Wagon
        'W463'  => ['start' => 1990, 'end' => 2018],
        'W463A' => ['start' => 2018, 'end' => 2024],
        'W465'  => ['start' => 2024, 'end' => null],

        // BMW M5
        'G90'   => ['start' => 2024, 'end' => null],
        'G99'   => ['start' => 2024, 'end' => null],
        'G9X'   => ['start' => 2024, 'end' => null],
        'E52'   => ['start' => 2000, 'end' => 2003], // Z8
        'F90'   => ['start' => 2018, 'end' => 2024],
        'E60'   => ['start' => 2005, 'end' => 2010],
    ];

    /**
     * Known vehicle makes
     */
    private static $known_makes = [
        'BMW', 'Mercedes', 'Mercedes-Benz', 'Ferrari',
        'Tesla', 'Porsche', 'Audi', 'Lamborghini'
    ];

    /**
     * Parse vehicle data from product
     *
     * @param WC_Product|object $product WooCommerce product object or remote product data
     * @return array Vehicle data (make, model, year, trim)
     */
    public static function parse_product($product) {
        // Handle both local WC_Product and remote product objects
        if (is_object($product) && method_exists($product, 'get_name')) {
            // Local WooCommerce product
            $title = $product->get_name();
            $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
            $tags = wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']);
        } else {
            // Remote product object
            $title = $product->name;
            $categories = $product->categories;
            $tags = isset($product->tags) ? $product->tags : [];
        }

        return [
            'make'  => self::extract_make($title, $categories),
            'model' => self::extract_model($title, $categories),
            'year'  => self::extract_year($title, $categories),
            'trim'  => self::extract_trim($title, $categories),
        ];
    }

    /**
     * Extract vehicle make
     */
    private static function extract_make($title, $categories) {
        // Check title first
        foreach (self::$known_makes as $make) {
            if (stripos($title, $make) !== false) {
                return $make === 'Mercedes' ? 'Mercedes-Benz' : $make;
            }
        }

        // Check categories
        foreach ($categories as $cat) {
            foreach (self::$known_makes as $make) {
                if (stripos($cat, $make) !== false) {
                    return $make === 'Mercedes' ? 'Mercedes-Benz' : $make;
                }
            }
        }

        return null;
    }

    /**
     * Extract vehicle model
     */
    private static function extract_model($title, $categories) {
        $models = [];

        // BMW models
        if (preg_match('/\bM5\b/i', $title)) {
            $models[] = 'M5';
        }
        if (preg_match('/\bM4\b/i', $title)) {
            $models[] = 'M4';
        }
        if (preg_match('/\bZ8\b/i', $title)) {
            $models[] = 'Z8';
        }

        // Mercedes models
        if (preg_match('/\bG-?(?:Class|Wagon)\b/i', $title)) {
            $models[] = 'G-Wagon';
        }
        if (preg_match('/\bG63\b/i', $title)) {
            $models[] = 'G63';
        }
        if (preg_match('/\bG65\b/i', $title)) {
            $models[] = 'G65';
        }

        // Ferrari models
        if (preg_match('/\bF12\s*Berlinetta\b/i', $title)) {
            $models[] = 'F12 Berlinetta';
        }

        // Tesla models
        if (preg_match('/\bModel\s*3\b/i', $title)) {
            $models[] = 'Model 3';
        }

        // Check categories if no model found
        if (empty($models)) {
            foreach ($categories as $cat) {
                if (preg_match('/\b(M5|M4|Z8|G-?Wagon|G63|G65|Model 3)\b/i', $cat, $matches)) {
                    $models[] = $matches[1];
                }
            }
        }

        return !empty($models) ? array_unique($models) : null;
    }

    /**
     * Extract vehicle year(s)
     */
    private static function extract_year($title, $categories) {
        $years = [];

        // Match year patterns: 2024, 2024+, 2024-2025, (1999-2018)
        if (preg_match_all('/\b(20\d{2})(?:\+|(?:-(20\d{2})))?\b/', $title, $matches)) {
            foreach ($matches[1] as $i => $start_year) {
                if (!empty($matches[2][$i])) {
                    // Range: 2024-2025
                    $end_year = $matches[2][$i];
                    for ($y = (int)$start_year; $y <= (int)$end_year; $y++) {
                        $years[] = (string)$y;
                    }
                } else if (strpos($title, $start_year . '+') !== false) {
                    // Open-ended: 2024+
                    $current_year = (int)date('Y');
                    for ($y = (int)$start_year; $y <= $current_year + 1; $y++) {
                        $years[] = (string)$y;
                    }
                } else {
                    // Single year
                    $years[] = $start_year;
                }
            }
        }

        // Check for years in parentheses from categories (1999-2018)
        if (preg_match('/\((\d{4})-(\d{4})\)/', implode(' ', $categories), $matches)) {
            for ($y = (int)$matches[1]; $y <= (int)$matches[2]; $y++) {
                $years[] = (string)$y;
            }
        }

        return !empty($years) ? array_unique($years) : null;
    }

    /**
     * Extract vehicle trim/chassis codes
     */
    private static function extract_trim($title, $categories) {
        $trims = [];

        // Match chassis codes in parentheses: (G90,G99) or (G90/G99)
        if (preg_match('/\(([A-Z]\d+[A-Z]?(?:[,\/]\s*[A-Z]\d+[A-Z]?)*)\)/', $title, $matches)) {
            $codes = preg_split('/[,\/]\s*/', $matches[1]);
            $trims = array_merge($trims, $codes);
        }

        // Match standalone chassis codes: W463, W463A, W465, G90, E52
        if (preg_match_all('/\b([WEF]\d{2,3}[A-Z]?|G\d{2}[A-Z]?)\b/', $title, $matches)) {
            $trims = array_merge($trims, $matches[1]);
        }

        // Match AMG variants
        if (preg_match('/\bAMG\b/i', $title)) {
            $trims[] = 'AMG';
        }

        return !empty($trims) ? array_unique($trims) : null;
    }

    /**
     * Get years for chassis code
     */
    public static function get_chassis_years($chassis_code) {
        $chassis_code = strtoupper($chassis_code);

        if (isset(self::$chassis_years[$chassis_code])) {
            $range = self::$chassis_years[$chassis_code];
            $years = [];
            $end = $range['end'] ?? (int)date('Y') + 1;

            for ($y = $range['start']; $y <= $end; $y++) {
                $years[] = (string)$y;
            }

            return $years;
        }

        return null;
    }
}

/**
 * WooCommerce Attribute Manager
 *
 * Creates and manages product attributes for vehicle fitment.
 */
class ThreeW_Attribute_Manager {

    /**
     * Vehicle attribute slugs
     */
    private static $attributes = [
        'pa_vehicle_make',
        'pa_vehicle_model',
        'pa_vehicle_year',
        'pa_vehicle_trim',
    ];

    /**
     * Ensure vehicle attributes exist
     */
    public static function ensure_attributes() {
        $attribute_labels = [
            'pa_vehicle_make'  => 'Vehicle Make',
            'pa_vehicle_model' => 'Vehicle Model',
            'pa_vehicle_year'  => 'Vehicle Year',
            'pa_vehicle_trim'  => 'Vehicle Trim',
        ];

        foreach (self::$attributes as $slug) {
            if (!taxonomy_exists($slug)) {
                $label = $attribute_labels[$slug];

                wc_create_attribute([
                    'name'         => $label,
                    'slug'         => $slug,
                    'type'         => 'select',
                    'order_by'     => 'menu_order',
                    'has_archives' => false,
                ]);

                // Register taxonomy
                register_taxonomy($slug, ['product'], [
                    'hierarchical' => false,
                    'label'        => $label,
                    'query_var'    => true,
                    'rewrite'      => false,
                ]);
            }
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Assign vehicle attributes to product
     *
     * @param int   $product_id Product ID
     * @param array $vehicle_data Vehicle data from parser
     */
    public static function assign_attributes($product_id, $vehicle_data) {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }

        $attributes = [];

        // Make
        if (!empty($vehicle_data['make'])) {
            $attributes['pa_vehicle_make'] = [$vehicle_data['make']];
        }

        // Model (can be multiple)
        if (!empty($vehicle_data['model'])) {
            $models = is_array($vehicle_data['model']) ? $vehicle_data['model'] : [$vehicle_data['model']];
            $attributes['pa_vehicle_model'] = $models;
        }

        // Year (can be multiple)
        if (!empty($vehicle_data['year'])) {
            $years = is_array($vehicle_data['year']) ? $vehicle_data['year'] : [$vehicle_data['year']];
            $attributes['pa_vehicle_year'] = $years;
        }

        // Trim (can be multiple)
        if (!empty($vehicle_data['trim'])) {
            $trims = is_array($vehicle_data['trim']) ? $vehicle_data['trim'] : [$vehicle_data['trim']];
            $attributes['pa_vehicle_trim'] = $trims;
        }

        // Assign to product
        foreach ($attributes as $taxonomy => $terms) {
            $term_ids = [];

            foreach ($terms as $term_name) {
                $term = get_term_by('name', $term_name, $taxonomy);

                if (!$term) {
                    $result = wp_insert_term($term_name, $taxonomy);
                    if (!is_wp_error($result)) {
                        $term_ids[] = $result['term_id'];
                    }
                } else {
                    $term_ids[] = $term->term_id;
                }
            }

            if (!empty($term_ids)) {
                wp_set_object_terms($product_id, $term_ids, $taxonomy);
            }
        }

        return true;
    }
}

/**
 * Fitment Inventory Builder
 *
 * Builds aggregated fitment inventory for the selector.
 */
class ThreeW_Fitment_Inventory {

    /**
     * Build inventory from parsed vehicle data array
     *
     * @param array $all_vehicle_data Array of vehicle data arrays
     * @return array Nested inventory structure
     */
    public static function build_inventory_from_data($all_vehicle_data) {
        $inventory = [];

        foreach ($all_vehicle_data as $vehicle_data) {
            $make = $vehicle_data['make'];
            $models = is_array($vehicle_data['model']) ? $vehicle_data['model'] : [$vehicle_data['model']];
            $years = is_array($vehicle_data['year']) ? $vehicle_data['year'] : [$vehicle_data['year']];
            $trims = is_array($vehicle_data['trim']) ? $vehicle_data['trim'] : ($vehicle_data['trim'] ? [$vehicle_data['trim']] : []);

            // Skip if missing essential data
            if (empty($make) || empty($models) || empty($years)) {
                continue;
            }

            // Add to inventory for each model
            foreach ($models as $model) {
                if (empty($model)) {
                    continue;
                }

                // Add to inventory for each year
                foreach ($years as $year) {
                    if (empty($year)) {
                        continue;
                    }

                    // Initialize nested structure
                    if (!isset($inventory[$year])) {
                        $inventory[$year] = [];
                    }
                    if (!isset($inventory[$year][$make])) {
                        $inventory[$year][$make] = [];
                    }
                    if (!isset($inventory[$year][$make][$model])) {
                        $inventory[$year][$make][$model] = [];
                    }

                    // Merge trims
                    if (!empty($trims)) {
                        $inventory[$year][$make][$model] = array_unique(
                            array_merge($inventory[$year][$make][$model], $trims)
                        );
                    }
                }
            }
        }

        // Sort years descending
        krsort($inventory);

        return $inventory;
    }

    /**
     * Build inventory from product attributes (legacy method for local products)
     *
     * @return array Nested inventory structure
     */
    public static function build_inventory() {
        $inventory = [];

        // Get all vehicle years
        $years = get_terms([
            'taxonomy'   => 'pa_vehicle_year',
            'hide_empty' => true,
            'orderby'    => 'name',
            'order'      => 'DESC',
        ]);

        foreach ($years as $year_term) {
            $year = $year_term->name;

            // Get products for this year
            $year_products = get_posts([
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'tax_query'      => [
                    [
                        'taxonomy' => 'pa_vehicle_year',
                        'field'    => 'term_id',
                        'terms'    => $year_term->term_id,
                    ],
                ],
                'fields'         => 'ids',
            ]);

            if (empty($year_products)) {
                continue;
            }

            // Get makes for this year
            $makes = self::get_unique_attribute_values($year_products, 'pa_vehicle_make');

            foreach ($makes as $make) {
                // Get products for year + make
                $make_products = get_posts([
                    'post_type'      => 'product',
                    'posts_per_page' => -1,
                    'tax_query'      => [
                        'relation' => 'AND',
                        [
                            'taxonomy' => 'pa_vehicle_year',
                            'field'    => 'name',
                            'terms'    => $year,
                        ],
                        [
                            'taxonomy' => 'pa_vehicle_make',
                            'field'    => 'name',
                            'terms'    => $make,
                        ],
                    ],
                    'fields'         => 'ids',
                ]);

                $models = self::get_unique_attribute_values($make_products, 'pa_vehicle_model');

                foreach ($models as $model) {
                    // Get products for year + make + model
                    $model_products = get_posts([
                        'post_type'      => 'product',
                        'posts_per_page' => -1,
                        'tax_query'      => [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'pa_vehicle_year',
                                'field'    => 'name',
                                'terms'    => $year,
                            ],
                            [
                                'taxonomy' => 'pa_vehicle_make',
                                'field'    => 'name',
                                'terms'    => $make,
                            ],
                            [
                                'taxonomy' => 'pa_vehicle_model',
                                'field'    => 'name',
                                'terms'    => $model,
                            ],
                        ],
                        'fields'         => 'ids',
                    ]);

                    $trims = self::get_unique_attribute_values($model_products, 'pa_vehicle_trim');

                    // Add to inventory
                    if (!isset($inventory[$year])) {
                        $inventory[$year] = [];
                    }
                    if (!isset($inventory[$year][$make])) {
                        $inventory[$year][$make] = [];
                    }
                    if (!isset($inventory[$year][$make][$model])) {
                        $inventory[$year][$make][$model] = [];
                    }

                    $inventory[$year][$make][$model] = array_merge(
                        $inventory[$year][$make][$model],
                        $trims
                    );
                }
            }
        }

        return $inventory;
    }

    /**
     * Get unique attribute values from products
     */
    private static function get_unique_attribute_values($product_ids, $taxonomy) {
        $values = [];

        foreach ($product_ids as $product_id) {
            $terms = wp_get_post_terms($product_id, $taxonomy, ['fields' => 'names']);
            if (!is_wp_error($terms)) {
                $values = array_merge($values, $terms);
            }
        }

        return array_unique($values);
    }

    /**
     * Save inventory to wp_options
     */
    public static function save_inventory($inventory) {
        update_option('threew_fitment_inventory_real', $inventory);

        // Clear cache
        wp_cache_delete('threew_fitment_inventory');

        return true;
    }
}

/**
 * WP-CLI Command
 */
if (defined('WP_CLI') && WP_CLI) {

    /**
     * Fitment import commands
     */
    class ThreeW_Fitment_CLI {

        /**
         * Import vehicle fitment data from products
         *
         * ## OPTIONS
         *
         * [--limit=<number>]
         * : Limit number of products to process
         * ---
         * default: 0
         * ---
         *
         * [--dry-run]
         * : Run without making changes
         *
         * [--source=<file>]
         * : Read products from JSON file instead of scraping
         * ---
         * default: null
         * ---
         *
         * ## EXAMPLES
         *
         *     wp fitment import
         *     wp fitment import --limit=10 --dry-run
         *     wp fitment import --source=scraped-products-sample.json
         *     wp fitment import --source=scraped-products-all.json --limit=50
         *
         * @when after_wp_load
         */
        public function import($args, $assoc_args) {
            $limit = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : 0;
            $dry_run = isset($assoc_args['dry-run']);
            $source = isset($assoc_args['source']) ? $assoc_args['source'] : null;

            if ($dry_run) {
                WP_CLI::warning('DRY RUN MODE - No changes will be made');
            }

            // Ensure attributes exist
            if (!$dry_run) {
                WP_CLI::log('Creating vehicle attributes...');
                ThreeW_Attribute_Manager::ensure_attributes();
                WP_CLI::success('Attributes created');
            }

            // Fetch products from source (JSON file or remote shop site)
            if ($source) {
                WP_CLI::log("Fetching products from JSON file: {$source}");
            } else {
                WP_CLI::log('Fetching products from shop.3wdistributing.com...');
            }

            $remote_products = ThreeW_Remote_Product_Fetcher::fetch_products($limit, $source);
            $total = count($remote_products);

            if ($total === 0) {
                $error_msg = $source
                    ? "No products loaded from JSON file: {$source}"
                    : 'No products fetched from shop site. Check credentials in .env file.';
                WP_CLI::error($error_msg);
                return;
            }

            WP_CLI::log("Processing {$total} products...");

            $stats = [
                'processed' => 0,
                'success'   => 0,
                'skipped'   => 0,
                'errors'    => 0,
            ];

            $all_vehicle_data = []; // Store all parsed vehicle data

            $progress = \WP_CLI\Utils\make_progress_bar('Importing fitment data', $total);

            foreach ($remote_products as $remote_product) {
                // Convert to product object
                $product = ThreeW_Remote_Product_Fetcher::to_product_object($remote_product);

                // Parse vehicle data
                $vehicle_data = ThreeW_Vehicle_Parser::parse_product($product);

                // Skip if no vehicle data found
                if (empty($vehicle_data['make'])) {
                    $stats['skipped']++;
                    $progress->tick();
                    continue;
                }

                // Store vehicle data for inventory building
                $all_vehicle_data[] = $vehicle_data;
                $stats['success']++;
                $stats['processed']++;
                $progress->tick();
            }

            $progress->finish();

            // Build inventory from parsed data
            if (!$dry_run && !empty($all_vehicle_data)) {
                WP_CLI::log('Building fitment inventory...');
                $inventory = ThreeW_Fitment_Inventory::build_inventory_from_data($all_vehicle_data);
                ThreeW_Fitment_Inventory::save_inventory($inventory);
                WP_CLI::success('Inventory built and saved');
            }

            // Display stats
            WP_CLI::log('');
            WP_CLI::log('Import Statistics:');
            WP_CLI::log("  Processed: {$stats['processed']}");
            WP_CLI::log("  Success:   {$stats['success']}");
            WP_CLI::log("  Skipped:   {$stats['skipped']}");
            WP_CLI::log("  Errors:    {$stats['errors']}");

            if (!$dry_run) {
                WP_CLI::success('Import complete!');
            }
        }

        /**
         * Clear all vehicle fitment data
         *
         * ## EXAMPLES
         *
         *     wp fitment clear
         *
         * @when after_wp_load
         */
        public function clear($args, $assoc_args) {
            WP_CLI::confirm('This will remove all vehicle fitment data. Continue?');

            // Get all products with vehicle attributes
            $products = get_posts([
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'tax_query'      => [
                    'relation' => 'OR',
                    ['taxonomy' => 'pa_vehicle_make'],
                    ['taxonomy' => 'pa_vehicle_model'],
                    ['taxonomy' => 'pa_vehicle_year'],
                    ['taxonomy' => 'pa_vehicle_trim'],
                ],
            ]);

            $progress = \WP_CLI\Utils\make_progress_bar('Clearing fitment data', count($products));

            foreach ($products as $post) {
                wp_delete_object_term_relationships($post->ID, [
                    'pa_vehicle_make',
                    'pa_vehicle_model',
                    'pa_vehicle_year',
                    'pa_vehicle_trim',
                ]);
                $progress->tick();
            }

            $progress->finish();

            // Clear inventory
            delete_option('threew_fitment_inventory_real');
            wp_cache_delete('threew_fitment_inventory');

            WP_CLI::success('Fitment data cleared');
        }
    }

    WP_CLI::add_command('fitment', 'ThreeW_Fitment_CLI');
}
