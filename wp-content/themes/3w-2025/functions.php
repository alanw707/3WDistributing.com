<?php
/**
 * 3W 2025 Theme bootstrap
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('THREEW_SHOP_BASE_URL')) {
    define('THREEW_SHOP_BASE_URL', 'https://shop.3wdistributing.com/');
}

// reCAPTCHA keys should be set via environment variables or WordPress options
// Do not hardcode keys here for security reasons

function threew_get_env_value($key) {
    $sources = [
        static function ($key) {
            $value = getenv($key);
            return $value === false ? null : $value;
        },
        static function ($key) {
            return isset($_ENV[$key]) ? $_ENV[$key] : null;
        },
        static function ($key) {
            return isset($_SERVER[$key]) ? $_SERVER[$key] : null;
        },
    ];

    foreach ($sources as $source) {
        $value = $source($key);
        if ($value !== null && $value !== '') {
            return trim((string) $value);
        }
    }

    return '';
}

/**
 * Attempt to reuse keys stored via Contact Form 7's integration panel.
 */
function threew_get_cf7_recaptcha_credentials() {
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $cached = ['', ''];

    if (defined('WPCF7_RECAPTCHA_SITEKEY') && defined('WPCF7_RECAPTCHA_SECRET')
        && WPCF7_RECAPTCHA_SITEKEY && WPCF7_RECAPTCHA_SECRET) {
        $cached = [
            trim((string) WPCF7_RECAPTCHA_SITEKEY),
            trim((string) WPCF7_RECAPTCHA_SECRET),
        ];
        return $cached;
    }

    if (!class_exists('WPCF7')) {
        return $cached;
    }

    $integration = WPCF7::get_option('recaptcha');
    if (!is_array($integration)) {
        return $cached;
    }

    foreach ($integration as $site_key => $secret_key) {
        $site_key = is_string($site_key) ? trim($site_key) : '';
        $secret_key = is_string($secret_key) ? trim($secret_key) : '';

        if ($site_key !== '' && $secret_key !== '') {
            $cached = [$site_key, $secret_key];
            break;
        }
    }

    return $cached;
}

function threew_get_recaptcha_site_key() {
    $sources = [
        (defined('THREEW_RECAPTCHA_SITE_KEY') && THREEW_RECAPTCHA_SITE_KEY) ? THREEW_RECAPTCHA_SITE_KEY : '',
        threew_get_env_value('THREEW_RECAPTCHA_SITE_KEY'),
        get_option('threew_recaptcha_site_key', ''),
    ];

    foreach ($sources as $value) {
        if (!is_string($value)) {
            continue;
        }

        $value = trim($value);
        if ($value !== '') {
            return $value;
        }
    }

    // Fallback to Contact Form 7 credentials
    $cf7 = threew_get_cf7_recaptcha_credentials();
    if (isset($cf7[0]) && $cf7[0]) {
        return $cf7[0];
    }

    // Ultimate fallback to hardcoded production key
    return 'YOUR_RECAPTCHA_SITE_KEY';
}

function threew_get_recaptcha_secret_key() {
    $sources = [
        (defined('THREEW_RECAPTCHA_SECRET_KEY') && THREEW_RECAPTCHA_SECRET_KEY) ? THREEW_RECAPTCHA_SECRET_KEY : '',
        threew_get_env_value('THREEW_RECAPTCHA_SECRET_KEY'),
        get_option('threew_recaptcha_secret_key', ''),
    ];

    foreach ($sources as $value) {
        if (!is_string($value)) {
            continue;
        }

        $value = trim($value);
        if ($value !== '') {
            return $value;
        }
    }

    // Fallback to Contact Form 7 credentials
    $cf7 = threew_get_cf7_recaptcha_credentials();
    if (isset($cf7[1]) && $cf7[1]) {
        return $cf7[1];
    }

    // Ultimate fallback to hardcoded production secret key
    return 'YOUR_RECAPTCHA_SECRET_KEY';
}

/**
 * Build the canonical shop URL, optionally seeded with catalog filters.
 *
 * @param string|array $search     Optional search term or array of terms.
 * @param array        $query_args Additional query parameters to merge.
 * @param string       $path       Optional path (e.g. product-category slug).
 *
 * @return string Fully-qualified shop URL.
 */
function threew_2025_get_shop_url($search = '', array $query_args = [], $path = '') {
    $base = rtrim(THREEW_SHOP_BASE_URL, '/') . '/';

    if ($path !== '') {
        $path = ltrim($path, '/');
        $base .= $path;
        if (substr($base, -1) !== '/') {
            $base .= '/';
        }
    }

    if (is_array($search)) {
        $search = array_filter(array_map('trim', $search));
        $search = array_map(static function ($term) {
            if ($term === '') {
                return $term;
            }

            if ($term[0] === '"' || $term[0] === "'" || strpos($term, ' ') === false) {
                return $term;
            }

            return '"' . $term . '"';
        }, $search);
        $search = implode(' ', $search);
    }

    $params = [];

    if ($search !== '' && $search !== null) {
        $params['s'] = $search;
        $params['post_type'] = 'product';
    }

    if (!empty($query_args)) {
        $params = array_merge($params, $query_args);
        if (!array_key_exists('post_type', $params)) {
            $params['post_type'] = 'product';
        }
    }

    if (empty($params)) {
        return $base;
    }

    return add_query_arg($params, $base);
}

/**
 * Theme setup.
 */
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('wp-block-styles');
    add_theme_support('woocommerce');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('align-wide');
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'threew-2025'),
        'utility' => __('Utility Navigation', 'threew-2025'),
    ]);
});

add_action('admin_init', function () {
    register_setting(
        'general',
        'threew_recaptcha_site_key',
        [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]
    );

    register_setting(
        'general',
        'threew_recaptcha_secret_key',
        [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]
    );

    add_settings_field(
        'threew_recaptcha_site_key',
        __('reCAPTCHA Site Key', 'threew-2025'),
        function () {
            if (defined('THREEW_RECAPTCHA_SITE_KEY') && THREEW_RECAPTCHA_SITE_KEY) {
                echo '<p>' . esc_html__('Configured via wp-config.php constant.', 'threew-2025') . '</p>';
                return;
            }

            $value = esc_attr(get_option('threew_recaptcha_site_key', ''));
            echo '<input type="text" id="threew_recaptcha_site_key" name="threew_recaptcha_site_key" value="' . $value . '" class="regular-text" />';
            echo '<p class="description">' . esc_html__('Create keys in Google reCAPTCHA v2 (checkbox) and paste the site key here.', 'threew-2025') . '</p>';
        },
        'general',
        'default'
    );

    add_settings_field(
        'threew_recaptcha_secret_key',
        __('reCAPTCHA Secret Key', 'threew-2025'),
        function () {
            if (defined('THREEW_RECAPTCHA_SECRET_KEY') && THREEW_RECAPTCHA_SECRET_KEY) {
                echo '<p>' . esc_html__('Configured via wp-config.php constant.', 'threew-2025') . '</p>';
                return;
            }

            $value = esc_attr(get_option('threew_recaptcha_secret_key', ''));
            echo '<input type="text" id="threew_recaptcha_secret_key" name="threew_recaptcha_secret_key" value="' . $value . '" class="regular-text" />';
            echo '<p class="description">' . esc_html__('Paste the matching secret key. These values are used on the About page contact form.', 'threew-2025') . '</p>';
        },
        'general',
        'default'
    );
});

/**
 * Helper to read asset manifest generated by @wordpress/scripts.
 */
function threew_2025_get_asset() {
    static $asset = null;

    if ($asset === null) {
        $asset_path = get_theme_file_path('build/index.asset.php');
        if (file_exists($asset_path)) {
            $asset = include $asset_path;
        } else {
            $asset = false;
        }
    }

    return $asset;
}

/**
 * Enqueue shared styles for both editor and frontend.
 */
add_action('enqueue_block_assets', function () {
    $style_path = get_theme_file_path('build/style-index.css');
    if (file_exists($style_path)) {
        wp_enqueue_style(
            'threew-2025-style',
            get_theme_file_uri('build/style-index.css'),
            [],
            filemtime($style_path)
        );
    }
});

/**
 * Enqueue theme scripts (frontend).
 */
add_action('wp_enqueue_scripts', function () {
    $asset = threew_2025_get_asset();
    $script_path = get_theme_file_path('build/index.js');
    $style_path  = get_theme_file_path('build/index.css');

    if ($asset && file_exists($script_path)) {
        wp_enqueue_script(
            'threew-2025-frontend',
            get_theme_file_uri('build/index.js'),
            $asset['dependencies'],
            $asset['version'],
            true
        );
    }

    if (file_exists($style_path)) {
        wp_enqueue_style(
            'threew-2025-frontend',
            get_theme_file_uri('build/index.css'),
            ['threew-2025-style'],
            filemtime($style_path)
        );
    }

    $recaptcha_site_key = threew_get_recaptcha_site_key();
    if ($recaptcha_site_key && (is_page_template('page-about.php') || is_page('about-us') || is_page('about'))) {
        wp_enqueue_script(
            'google-recaptcha',
            'https://www.google.com/recaptcha/api.js',
            [],
            null,
            true
        );
        wp_script_add_data('google-recaptcha', 'async', true);
        wp_script_add_data('google-recaptcha', 'defer', true);

        $inline = <<<JS
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.threew-contact-form');
    if (!form || form.querySelector('.g-recaptcha')) {
        return;
    }

    var wrapper = document.createElement('div');
    wrapper.className = 'threew-form-field threew-form-field--full threew-form-field--captcha';

    var captcha = document.createElement('div');
    captcha.className = 'g-recaptcha';
    captcha.setAttribute('data-sitekey', '{$recaptcha_site_key}');

    wrapper.appendChild(captcha);

    var submit = form.querySelector('.threew-form-submit');
    if (submit && submit.parentNode) {
        submit.parentNode.insertBefore(wrapper, submit);
    } else {
        form.appendChild(wrapper);
    }
});
JS;
        wp_add_inline_script('google-recaptcha', $inline);
    }

    // Enqueue fitment selector view script for frontend interactivity
    $fitment_view_asset_path = get_theme_file_path('build/view.asset.php');
    $fitment_view_path = get_theme_file_path('build/view.js');

    if (file_exists($fitment_view_path) && file_exists($fitment_view_asset_path)) {
        $fitment_view_asset = include $fitment_view_asset_path;
        wp_enqueue_script(
            'threew-fitment-view',
            get_theme_file_uri('build/view.js'),
            $fitment_view_asset['dependencies'],
            $fitment_view_asset['version'],
            true
        );

        wp_localize_script(
            'threew-fitment-view',
            'threewShopConfig',
            [
                'baseUrl' => threew_2025_get_shop_url(),
            ]
        );
    }
});

/**
 * Enqueue block editor assets.
 */
add_action('enqueue_block_editor_assets', function () {
    $asset = threew_2025_get_asset();
    $script_path = get_theme_file_path('build/index.js');

    if ($asset && file_exists($script_path)) {
        wp_enqueue_script(
            'threew-2025-editor',
            get_theme_file_uri('build/index.js'),
            array_merge($asset['dependencies'], ['wp-edit-post']),
            $asset['version'],
            true
        );
    }
});

/**
 * Include fitment API endpoints
 */
require_once get_theme_file_path('inc/fitment-api.php');

/**
 * Include fitment import system
 */
require_once get_theme_file_path('inc/fitment-import.php');

/**
 * Include SEO functionality
 */
require_once get_theme_file_path('inc/seo-class.php');

/**
 * Configure PHPMailer to use Mailpit for local development.
 */
add_action('phpmailer_init', function ($phpmailer) {
	// Configure SMTP for Docker environment (detect by checking if mailpit host is accessible)
	$use_mailpit = false;

	// Check if we're in Docker by looking for mailpit service
	if (gethostbyname('mailpit') !== 'mailpit') {
		$use_mailpit = true;
	}

	if ($use_mailpit) {
		$phpmailer->isSMTP();
		$phpmailer->Host = 'mailpit';
		$phpmailer->Port = 1025;
		$phpmailer->SMTPAuth = false;
		$phpmailer->SMTPAutoTLS = false;
		$phpmailer->SMTPSecure = '';
		$phpmailer->From = 'noreply@3wdistributing.com';
		$phpmailer->FromName = '3W Distribution';
	}
}, 10, 1);

/**
 * Handle contact form submission from About page.
 */
add_action('admin_post_nopriv_threew_contact_form_submit', 'threew_handle_contact_form');
add_action('admin_post_threew_contact_form_submit', 'threew_handle_contact_form');

function threew_verify_recaptcha_response($token) {
	$secret = threew_get_recaptcha_secret_key();
	if ($secret === '' || $secret === null) {
		return true;
	}

	if (empty($token)) {
		return false;
	}

	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		[
			'timeout' => 10,
			'body'    => [
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
			],
		]
	);

	if (is_wp_error($response)) {
		return false;
	}

	$body = wp_remote_retrieve_body($response);
	$data = json_decode($body, true);

	return is_array($data) && !empty($data['success']);
}

function threew_handle_contact_form() {
	// Verify nonce
	if (!isset($_POST['threew_contact_nonce']) || !wp_verify_nonce($_POST['threew_contact_nonce'], 'threew_contact_form')) {
		wp_die('Security check failed');
	}

	// Sanitize form data
	$name    = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
	$email   = isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '';
	$phone   = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
	$subject = isset($_POST['contact_subject']) ? sanitize_text_field($_POST['contact_subject']) : 'Contact Form Submission';
	$message = isset($_POST['contact_message']) ? sanitize_textarea_field($_POST['contact_message']) : '';
	$recaptcha_token = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';

	// Basic validation
	if (empty($name) || empty($email) || empty($message)) {
		wp_redirect(add_query_arg('contact_status', 'error', wp_get_referer()) . '#contact');
		exit;
	}

	if (!threew_verify_recaptcha_response($recaptcha_token)) {
		wp_redirect(add_query_arg('contact_status', 'captcha', wp_get_referer()) . '#contact');
		exit;
	}

	// Prepare email
	$to = get_option('admin_email');
	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $name . ' <' . $email . '>',
		'Reply-To: ' . $email,
	];

	$email_subject = '[3W Distribution] ' . $subject;
	$email_body = sprintf(
		'<h2>New Contact Form Submission</h2>
		<p><strong>Name:</strong> %s</p>
		<p><strong>Email:</strong> %s</p>
		<p><strong>Phone:</strong> %s</p>
		<p><strong>Subject:</strong> %s</p>
		<p><strong>Message:</strong></p>
		<p>%s</p>',
		esc_html($name),
		esc_html($email),
		esc_html($phone ?: 'Not provided'),
		esc_html($subject),
		nl2br(esc_html($message))
	);

	// In local development, skip email and just show success
	if (defined('WP_DEBUG') && WP_DEBUG) {
		// Log the form submission for local testing
		error_log(sprintf(
			'Contact form submitted - Name: %s, Email: %s, Phone: %s, Subject: %s',
			$name,
			$email,
			$phone,
			$subject
		));

		// Always show success in local dev
		wp_redirect(add_query_arg('contact_status', 'success', wp_get_referer()) . '#contact');
		exit;
	}

	// Send email (only in staging/production)
	$sent = wp_mail($to, $email_subject, $email_body, $headers);

	// Redirect with status
	if ($sent) {
		wp_redirect(add_query_arg('contact_status', 'success', wp_get_referer()) . '#contact');
	} else {
		wp_redirect(add_query_arg('contact_status', 'error', wp_get_referer()) . '#contact');
	}
	exit;
}

/**
 * Flush rewrite rules on theme activation
 */
add_action('after_switch_theme', function() {
	flush_rewrite_rules();
	// Set a transient to show admin notice
	set_transient('threew_seo_activated', true, 30);
});

/**
 * Admin notice for SEO activation
 */
add_action('admin_notices', function() {
	if (get_transient('threew_seo_activated')) {
		delete_transient('threew_seo_activated');
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>SEO Features Activated!</strong></p>
			<p>The 3W Distributing theme now includes comprehensive SEO features:</p>
			<ul style="list-style: disc; margin-left: 20px;">
				<li>Meta descriptions and Open Graph tags</li>
				<li>Schema.org structured data (Organization, Products, Articles, Breadcrumbs)</li>
				<li>XML Sitemap at <code><?php echo home_url('/sitemap.xml'); ?></code></li>
				<li>Automatic canonical URLs</li>
				<li>Image alt text optimization</li>
			</ul>
			<p><strong>Next Steps:</strong></p>
			<ol style="list-style: decimal; margin-left: 20px;">
				<li>Go to <strong>Settings → Permalinks</strong> and click "Save Changes" to flush permalinks</li>
				<li>Update organization details in <code>inc/seo-class.php</code> (phone, address, social links)</li>
				<li>Test your sitemap: <a href="<?php echo home_url('/sitemap.xml'); ?>" target="_blank"><?php echo home_url('/sitemap.xml'); ?></a></li>
				<li>Submit sitemap to Google Search Console and Bing Webmaster Tools</li>
			</ol>
		</div>
		<?php
	}
});

add_action('template_redirect', function () {
	if (isset($_GET['threew_probe_global']) && $_GET['threew_probe_global'] === '1') {
		header('Content-Type: text/plain; charset=utf-8');
		echo isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
		exit;
	}
}, 999);

add_action('template_redirect', function () {
	if (isset($_GET['threew_debug_key']) && $_GET['threew_debug_key'] === '1') {
		header('Content-Type: text/plain; charset=utf-8');
		echo 'site_key=' . threew_get_recaptcha_site_key();
		exit;
	}
}, 998);

add_action('init', function () {
	$already_purged = get_option('threew_about_cache_purged', false);
	if ($already_purged) {
		return;
	}

	$about_url = home_url('/about-us/');

	if (class_exists('LiteSpeed_Cache_API')) {
		LiteSpeed_Cache_API::purge($about_url);
	} else {
		do_action('litespeed_purge_url', $about_url);
	}

	update_option('threew_about_cache_purged', 1, false);
});

function threew_is_legacy_sitemap_request() {
	if (! isset($_SERVER['REQUEST_URI'])) {
		return false;
	}

	$path = strtok(wp_unslash($_SERVER['REQUEST_URI']), '?');
	if ($path === false || $path === '') {
		return false;
	}

	$path = rtrim($path, '/');
	return substr($path, -strlen('sitemap_index.xml')) === 'sitemap_index.xml';
}

add_action('init', function () {
	if (threew_is_legacy_sitemap_request()) {
		wp_redirect(home_url('/sitemap.xml/'), 301, 'ThreeW Sitemap Redirect');
		exit;
	}
}, 0);
