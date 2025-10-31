<?php
/**
 * Fitment Selector REST API Endpoints
 *
 * Provides REST API endpoints for vehicle fitment data:
 * - /wp-json/threew/v1/fitment/years
 * - /wp-json/threew/v1/fitment/makes?year={year}
 * - /wp-json/threew/v1/fitment/models?year={year}&make={make}
 * - /wp-json/threew/v1/fitment/trims?year={year}&make={make}&model={model}
 *
 * @package 3W_2025
 */

namespace ThreeW\Fitment;

/**
 * Register REST API routes for fitment data
 */
function register_fitment_routes() {
	register_rest_route(
		'threew/v1',
		'/fitment/years',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_years',
			'permission_callback' => '__return_true', // Public endpoint
		)
	);

	register_rest_route(
		'threew/v1',
		'/fitment/makes',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_makes',
			'permission_callback' => '__return_true',
			'args'                => array(
				'year' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'threew/v1',
		'/fitment/models',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_models',
			'permission_callback' => '__return_true',
			'args'                => array(
				'year' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'make' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'threew/v1',
		'/fitment/trims',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_trims',
			'permission_callback' => '__return_true',
			'args'                => array(
				'year'  => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'make'  => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'model' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'threew/v1',
		'/fitment/import',
		array(
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\trigger_import',
			'permission_callback' => function() {
				return current_user_can( 'manage_options' );
			},
			'args'                => array(
				'source' => array(
					'required'          => false,
					'type'              => 'string',
					'default'           => 'wp-content/themes/3w-2025/woocommerce-products-all.json',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'limit'  => array(
					'required'          => false,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_fitment_routes' );

/**
 * Get available years from fitment inventory
 *
 * @return array List of years
 */
function get_years() {
	$inventory = get_fitment_inventory();
	$years     = array_keys( $inventory );

	// Sort years in descending order (newest first)
	rsort( $years );

	return rest_ensure_response( $years );
}

/**
 * Get available makes for a given year
 *
 * @param \WP_REST_Request $request Request object.
 * @return \WP_REST_Response|WP_Error Response object or error
 */
function get_makes( $request ) {
	$year      = $request->get_param( 'year' );
	$inventory = get_fitment_inventory();

	if ( ! isset( $inventory[ $year ] ) ) {
		return new \WP_Error(
			'invalid_year',
			__( 'Invalid year specified.', 'threew-2025' ),
			array( 'status' => 400 )
		);
	}

	$makes = array_keys( $inventory[ $year ] );

	// Sort alphabetically
	sort( $makes );

	return rest_ensure_response( $makes );
}

/**
 * Get available models for a given year and make
 *
 * @param \WP_REST_Request $request Request object.
 * @return \WP_REST_Response|WP_Error Response object or error
 */
function get_models( $request ) {
	$year      = $request->get_param( 'year' );
	$make      = $request->get_param( 'make' );
	$inventory = get_fitment_inventory();

	if ( ! isset( $inventory[ $year ][ $make ] ) ) {
		return new \WP_Error(
			'invalid_make',
			__( 'Invalid make specified for this year.', 'threew-2025' ),
			array( 'status' => 400 )
		);
	}

	$models = array_keys( $inventory[ $year ][ $make ] );

	// Sort alphabetically
	sort( $models );

	return rest_ensure_response( $models );
}

/**
 * Get available trims for a given year, make, and model
 *
 * @param \WP_REST_Request $request Request object.
 * @return \WP_REST_Response|WP_Error Response object or error
 */
function get_trims( $request ) {
	$year      = $request->get_param( 'year' );
	$make      = $request->get_param( 'make' );
	$model     = $request->get_param( 'model' );
	$inventory = get_fitment_inventory();

	if ( ! isset( $inventory[ $year ][ $make ][ $model ] ) ) {
		return new \WP_Error(
			'invalid_model',
			__( 'Invalid model specified for this year and make.', 'threew-2025' ),
			array( 'status' => 400 )
		);
	}

	$trims = $inventory[ $year ][ $make ][ $model ];

	// Sort alphabetically
	sort( $trims );

	return rest_ensure_response( $trims );
}

/**
 * Trigger fitment data import
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error on failure.
 */
function trigger_import( $request ) {
	// Check if WP-CLI is available
	if ( ! class_exists( 'WP_CLI' ) ) {
		return new \WP_Error(
			'wp_cli_unavailable',
			__( 'WP-CLI is not available on this system.', 'threew-2025' ),
			array( 'status' => 500 )
		);
	}

	$source = $request->get_param( 'source' );
	$limit  = $request->get_param( 'limit' );

	// Build command arguments
	$args = array(
		'source' => $source,
	);

	if ( $limit ) {
		$args['limit'] = $limit;
	}

	// Execute the import command
	try {
		ob_start();
		\WP_CLI::run_command( array( 'fitment', 'import' ), $args );
		$output = ob_get_clean();

		// Parse output for statistics (simplified)
		$stats = array(
			'processed' => 0,
			'success'   => 0,
			'skipped'   => 0,
			'errors'    => 0,
		);

		// Extract stats from output if available
		if ( preg_match( '/Processed: (\d+)/', $output, $matches ) ) {
			$stats['processed'] = (int) $matches[1];
		}
		if ( preg_match( '/Success: (\d+)/', $output, $matches ) ) {
			$stats['success'] = (int) $matches[1];
		}
		if ( preg_match( '/Skipped: (\d+)/', $output, $matches ) ) {
			$stats['skipped'] = (int) $matches[1];
		}
		if ( preg_match( '/Errors: (\d+)/', $output, $matches ) ) {
			$stats['errors'] = (int) $matches[1];
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Import completed successfully.', 'threew-2025' ),
				'data'    => $stats,
				'output'  => $output,
			)
		);
	} catch ( \Exception $e ) {
		return new \WP_Error(
			'import_failed',
			$e->getMessage(),
			array( 'status' => 500 )
		);
	}
}

/**
 * Get fitment inventory data
 *
 * Returns the complete vehicle fitment inventory.
 * In production, this should be replaced with database queries
 * or integration with a fitment data provider.
 *
 * @return array Nested array of year => make => model => trims
 */
function get_fitment_inventory() {
	// Check for cached inventory
	$cache_key = 'threew_fitment_inventory';
	$inventory = wp_cache_get( $cache_key );

	if ( false !== $inventory ) {
		return $inventory;
	}

	// Try to get real inventory from wp_options (populated by import script)
	$real_inventory = get_option( 'threew_fitment_inventory_real' );

	if ( ! empty( $real_inventory ) ) {
		$inventory = $real_inventory;
	} else {
		/**
		 * Filter fitment inventory data
		 *
		 * Allows external plugins or custom code to provide fitment data.
		 * This is the recommended integration point for fitment data providers.
		 *
		 * @param array $inventory Default inventory data
		 */
		$inventory = apply_filters( 'threew_fitment_inventory', get_default_inventory() );
	}

	// Cache for 1 hour
	wp_cache_set( $cache_key, $inventory, '', HOUR_IN_SECONDS );

	return $inventory;
}

/**
 * Get default fitment inventory (sample data)
 *
 * This is sample data for development. In production, replace with:
 * - Database queries
 * - Integration with fitment data provider (e.g., SEMA Data Co-op)
 * - Custom product metadata
 *
 * @return array Default inventory structure
 */
function get_default_inventory() {
	return array(
		'2025' => array(
			'Audi'       => array(
				'RS7' => array( 'Performance', 'Prestige' ),
				'Q8'  => array( 'S-Line', 'Base' ),
			),
			'BMW'        => array(
				'M4' => array( 'Competition', 'CS' ),
				'X5' => array( 'M Sport', 'Luxury' ),
			),
			'Mercedes'   => array(
				'AMG GT' => array( 'S', 'C', 'R' ),
				'C-Class' => array( 'C300', 'C43 AMG' ),
			),
		),
		'2024' => array(
			'Audi'       => array(
				'RS6'  => array( 'Avant', 'Performance' ),
				'RS7'  => array( 'Performance', 'Prestige' ),
				'Q8'   => array( 'S-Line', 'Base' ),
			),
			'BMW'        => array(
				'M3'  => array( 'Competition', 'CS' ),
				'M4'  => array( 'Competition', 'CS', 'CSL' ),
				'M5'  => array( 'Competition' ),
				'X5'  => array( 'M Sport', 'Luxury', 'M50i' ),
			),
			'Mercedes'   => array(
				'C63'     => array( 'S', 'Edition 1' ),
				'E63'     => array( 'S', 'Wagon' ),
				'G63'     => array( 'Standard' ),
				'AMG GT'  => array( 'S', 'C', 'R' ),
			),
			'Porsche'    => array(
				'911'    => array( 'Carrera', 'Carrera S', 'Turbo', 'GT3' ),
				'Taycan' => array( 'Turbo', 'Turbo S', 'GTS' ),
				'Macan'  => array( 'GTS', 'Turbo' ),
			),
		),
		'2023' => array(
			'Audi'       => array(
				'RS6'  => array( 'Avant', 'Performance' ),
				'RS7'  => array( 'Performance', 'Prestige' ),
				'Q8'   => array( 'S-Line', 'Base' ),
				'R8'   => array( 'V10', 'V10 Plus' ),
			),
			'BMW'        => array(
				'M2'  => array( 'Competition' ),
				'M3'  => array( 'Competition', 'CS' ),
				'M4'  => array( 'Competition', 'CS' ),
				'M5'  => array( 'Competition' ),
				'X5'  => array( 'M Sport', 'Luxury', 'M50i' ),
				'X6'  => array( 'M Sport', 'M50i' ),
			),
			'Lexus'      => array(
				'IS500'  => array( 'F SPORT Performance' ),
				'LC500'  => array( 'Base', 'Convertible' ),
			),
			'Mercedes'   => array(
				'C63'     => array( 'S', 'Edition 1' ),
				'E63'     => array( 'S', 'Wagon' ),
				'G63'     => array( 'Standard' ),
				'AMG GT'  => array( 'S', 'C', 'R', 'Black Series' ),
			),
			'Nissan'     => array(
				'GT-R'    => array( 'Premium', 'NISMO', 'Track Edition' ),
				'Z'       => array( 'Sport', 'Performance' ),
			),
			'Porsche'    => array(
				'911'     => array( 'Carrera', 'Carrera S', 'Turbo', 'GT3', 'GT3 RS' ),
				'Taycan'  => array( 'Turbo', 'Turbo S', 'GTS' ),
				'Cayman'  => array( 'GTS', 'GT4' ),
				'Macan'   => array( 'GTS', 'Turbo' ),
			),
		),
		'2022' => array(
			'Audi'       => array(
				'RS6'  => array( 'Avant' ),
				'RS7'  => array( 'Performance' ),
				'Q8'   => array( 'S-Line', 'Base' ),
				'R8'   => array( 'V10', 'V10 Plus' ),
			),
			'BMW'        => array(
				'M2'  => array( 'Competition' ),
				'M3'  => array( 'Competition' ),
				'M4'  => array( 'Competition' ),
				'M5'  => array( 'Competition' ),
				'M8'  => array( 'Competition', 'Gran Coupe' ),
			),
			'Chevrolet'  => array(
				'Corvette' => array( 'Stingray', 'Z06' ),
				'Camaro'   => array( 'SS', 'ZL1' ),
			),
			'Dodge'      => array(
				'Challenger' => array( 'R/T', 'Scat Pack', 'SRT Hellcat' ),
				'Charger'    => array( 'R/T', 'Scat Pack', 'SRT Hellcat' ),
			),
			'Mercedes'   => array(
				'C63'     => array( 'S' ),
				'E63'     => array( 'S', 'Wagon' ),
				'AMG GT'  => array( 'S', 'C', 'R' ),
			),
			'Porsche'    => array(
				'911'     => array( 'Carrera', 'Carrera S', 'Turbo', 'GT3' ),
				'Taycan'  => array( 'Turbo', 'Turbo S' ),
				'Cayman'  => array( 'GTS', 'GT4' ),
			),
		),
	);
}

/**
 * Clear fitment inventory cache
 *
 * Useful when inventory data is updated.
 * Can be called manually or hooked to product updates.
 */
function clear_fitment_cache() {
	wp_cache_delete( 'threew_fitment_inventory' );
}

/**
 * Render fitment selector block manually
 *
 * This function renders the fitment selector block markup
 * for use in PHP templates.
 *
 * @param array $attributes Block attributes.
 * @return string Block HTML.
 */
function render_fitment_selector( $attributes = array() ) {
	// Default attributes
	$defaults = array(
		'headline'    => __( 'Build your upgrade package', 'threew-2025' ),
		'subheadline' => __( 'Choose your vehicle to see in-stock performance parts.', 'threew-2025' ),
		'ctaLabel'    => __( 'Search parts', 'threew-2025' ),
	);

	$attributes = wp_parse_args( $attributes, $defaults );

	// Generate block HTML
	ob_start();
	?>
	<div class="wp-block-3w-fitment-selector threew-fitment-block">
		<div class="threew-fitment-block__copy">
			<h3 class="threew-fitment-block__headline"><?php echo esc_html( $attributes['headline'] ); ?></h3>
			<p class="threew-fitment-block__subheadline"><?php echo esc_html( $attributes['subheadline'] ); ?></p>
		</div>

		<nav class="threew-fitment-block__progress" aria-label="<?php esc_attr_e( 'Vehicle selection steps', 'threew-2025' ); ?>" aria-live="polite">
			<ol class="threew-fitment-progress__list">
				<li class="threew-fitment-progress__step is-active" data-fitment-step="year">
					<span class="threew-fitment-progress__index">1</span>
					<span class="threew-fitment-progress__label"><?php esc_html_e( 'Vehicle year', 'threew-2025' ); ?></span>
				</li>
				<li class="threew-fitment-progress__step" data-fitment-step="make">
					<span class="threew-fitment-progress__index">2</span>
					<span class="threew-fitment-progress__label"><?php esc_html_e( 'Manufacturer', 'threew-2025' ); ?></span>
				</li>
				<li class="threew-fitment-progress__step" data-fitment-step="model">
					<span class="threew-fitment-progress__index">3</span>
					<span class="threew-fitment-progress__label"><?php esc_html_e( 'Model', 'threew-2025' ); ?></span>
				</li>
				<li class="threew-fitment-progress__step" data-fitment-step="trim">
					<span class="threew-fitment-progress__index">4</span>
					<span class="threew-fitment-progress__label"><?php esc_html_e( 'Trim', 'threew-2025' ); ?></span>
				</li>
			</ol>
			<p class="threew-fitment-progress__status" data-fitment-step-status>
				<?php esc_html_e( 'Start with vehicle year', 'threew-2025' ); ?>
			</p>
		</nav>

		<div class="threew-fitment-block__form-shell">
			<form class="threew-fitment-block__form" data-fitment-interactive="pending" aria-describedby="threew-fitment-helper" novalidate>
				<div class="threew-fitment-block__fields">
					<div class="threew-fitment-block__field">
						<label for="threew-fitment-year"><?php esc_html_e( 'Year', 'threew-2025' ); ?></label>
						<select id="threew-fitment-year" disabled>
							<option value="" disabled selected><?php esc_html_e( 'Select', 'threew-2025' ); ?></option>
						</select>
					</div>
					<div class="threew-fitment-block__field">
						<label for="threew-fitment-make"><?php esc_html_e( 'Manufacturer', 'threew-2025' ); ?></label>
						<select id="threew-fitment-make" disabled>
							<option value="" disabled selected><?php esc_html_e( 'Select', 'threew-2025' ); ?></option>
						</select>
					</div>
					<div class="threew-fitment-block__field">
						<label for="threew-fitment-model"><?php esc_html_e( 'Model', 'threew-2025' ); ?></label>
						<select id="threew-fitment-model" disabled>
							<option value="" disabled selected><?php esc_html_e( 'Select', 'threew-2025' ); ?></option>
						</select>
					</div>
					<div class="threew-fitment-block__field">
						<label for="threew-fitment-trim"><?php esc_html_e( 'Trim', 'threew-2025' ); ?></label>
						<select id="threew-fitment-trim" disabled>
							<option value="" disabled selected><?php esc_html_e( 'Select', 'threew-2025' ); ?></option>
						</select>
					</div>
				</div>

				<div class="threew-fitment-block__actions">
					<button class="threew-fitment-block__submit" type="submit" disabled>
						<?php echo esc_html( $attributes['ctaLabel'] ); ?>
					</button>
					<button class="threew-fitment-block__reset" type="reset" disabled>
						<?php esc_html_e( 'Clear vehicle', 'threew-2025' ); ?>
					</button>
				</div>
			</form>
			<p class="threew-fitment-block__helper" id="threew-fitment-helper">
				<?php esc_html_e( 'Start with vehicle year, then follow manufacturer, model, and trim.', 'threew-2025' ); ?>
			</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
