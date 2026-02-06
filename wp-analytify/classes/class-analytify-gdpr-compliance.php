<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable for this plugin structure
/**
 * Analytify GDPR Compliance Class
 *
 * This class handles GDPR compliance functionality for the Analytify plugin,
 * including cookie consent integration and data privacy features.
 *
 * @package WP_Analytify
 * @since 1.0.0
 */

/**
 * Analytify GDPR Compliance Class
 *
 * @package WP_Analytify
 * @since 1.0.0
 */
class Class_Analytify_GDPR_Compliance {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function hooks() {

		// CookieYes | GDPR Cookie Consent & Compliance Notice (CCPA Ready) By WebToffee.
		add_filter( 'wt_cli_third_party_scripts', array( $this, 'cookie_law_info_blocking' ) );
		add_filter( 'wt_cli_plugin_integrations', array( $this, 'cookie_law_info_integration' ) );
		add_action( 'init', array( $this, 'cookie_law_info_add_settings' ), 9 );
	}

	/**
	 * Add Analytify in CookieYes blocking scripts settings.
	 *
	 * @return void
	 */
	public function cookie_law_info_add_settings() {

		global $wt_cli_integration_list;

		$wt_cli_integration_list['wp-analytify'] = array(
			'identifier'  => 'WP_Analytify',
			'label'       => 'Analytify - Google Analytics Dashboard',
			'status'      => 'yes',
			'description' => 'Google Analytics Dashboard Plugin for WordPress by Analytify',
			'category'    => 'analytics',
			'type'        => 1,
		);
	}

	/**
	 * Block scripts based on cookie consent.
	 *
	 * @param array $tags The script tags to process.
	 * @return array
	 */
	public function cookie_law_info_blocking( $tags ): array {

		try {
			global $wpdb;

			$script_table = $wpdb->prefix . 'cli_scripts';
			$status       = false;

			// Verify table exists using a prepared LIKE to avoid injection.
			$like_pattern = $script_table; // Derived from $wpdb->prefix, safe identifier.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for GDPR compliance check
			$table_found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like_pattern ) );

			if ( $table_found === $script_table ) {
				// Select from a known, prefixed table. Wrap identifier in backticks for safety.
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Direct query needed for GDPR compliance check
				$script_data = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM `%s`', $script_table ), ARRAY_A );

				foreach ( $script_data as $key => $data ) {
					if ( 'wp-analytify' === $data['cliscript_key'] && ( 'yes' === $data['cliscript_status'] || '1' === $data['cliscript_status'] ) ) {
						$status = true;
					}
				}
			}

			if ( $status ) {
				$tags['wp-analytify'] = array(
					'www.google-analytics.com/analytics.js',
					'www.googletagmanager.com/gtag/js',
					'wp-analytify/assets/js/scrolldepth.js',
					'wp-analytify/assets/js/video_tracking.js',
					'wp-analytify-forms/assets/js/tracking.js',
					'wp-analytify-pro/assets/js/script.js',
				);
			}
		} catch ( \Throwable $th ) {
			return $tags;
		}

		return $tags;
	}

	/**
	 * Add Analytify integration settings.
	 *
	 * @param array $integration The integration settings.
	 * @return array
	 */
	public function cookie_law_info_integration( $integration ): array {

		$integration['wp-analytify'] = array(
			'identifier'  => 'WP_Analytify',
			'label'       => 'Analytify - Google Analytics Dashboard',
			'status'      => 'yes',
			'description' => 'Google Analytics Dashboard Plugin for WordPress by Analytify',
			'category'    => 'analytics',
			'type'        => 1,
		);

		return $integration;
	}

	/**
	 * Check if GDPR plugins are blocing scripts.
	 *
	 * @return bool
	 */
	public static function is_gdpr_compliance_blocking() {

		// Cookie Notice & Compliance for GDPR / CCPA By Hu-manity.co.
		if ( function_exists( 'cn_cookies_accepted' ) && ! cn_cookies_accepted() ) {
			return true;
		}

		return false;
	}
}
