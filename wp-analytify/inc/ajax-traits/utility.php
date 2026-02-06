<?php
/**
 * Utility AJAX Trait for WP Analytify
 *
 * @package WP_Analytify
 */

/**
 * Utility AJAX Trait.
 *
 * This trait contains utility AJAX functionality that was previously
 * in the WPANALYTIFY_AJAX class. It handles ratings, settings, opt-in/out,
 * and other utility functions.
 *
 * @since 8.0.0
 */
trait Analytify_AJAX_Utility {

	/**
	 * Triggered when clicking the rating footer.
	 *
	 * @since 1.2.4
	 * @return void
	 */
	public static function rated() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify-rated', 'nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}
		update_option( 'analytify_admin_footer_text_rated', 1 );
		wp_die( esc_html__( 'rated', 'wp-analytify' ) );
	}

	/**
	 * Fetch log for diagnostic information
	 *
	 * @return void
	 */
	public static function fetch_log() {

		// Verify nonce for security.
		check_ajax_referer( 'fetch-log', 'nonce' );

		// Check if the current user has sufficient permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized access', 403 );
		}

		try {
			ob_start();

			self::output_diagnostic_info();

			$output = ob_get_clean();

			if ( false === $output ) {
				$output = 'Error: Output buffer failed';
			}

			// Output as plain text for textarea display (not HTML), so don't HTML-escape.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Diagnostic output for textarea, escaping handled in print_settings_array
			echo $output;

		} catch ( Exception $e ) {
			// Log the error for debugging.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional logging for diagnostics.
			error_log( 'Analytify diagnostic error: ' . $e->getMessage() );
			echo 'Error generating diagnostic information: ' . esc_html( $e->getMessage() );
		}

		wp_die();
	}

	/**
	 * Outputs diagnostic info for debugging.
	 *
	 * Outputs useful diagnostic info text at the Diagnostic Info & Error Log
	 * section under the Help tab so the information can be viewed or
	 * downloaded and shared for debugging.
	 *
	 * If you would like to add additional diagnostic information use the
	 * `wpanalytify_diagnostic_info` action hook (see {@link https://developer.wordpress.org/reference/functions/add_action/}).
	 *
	 * <code>
	 * add_action( 'wpanalytify_diagnostic_info', 'my_diagnostic_info' ) {
	 *     echo "Additional Diagnostic Info: \r\n";
	 *     echo "...\r\n";
	 * }
	 * </code>
	 *
	 * @return void
	 */
	public static function output_diagnostic_info() {
		global $wpdb;
		$table_prefix        = $wpdb->base_prefix;
		$authentication_date = get_option( 'analytify_authentication_date' );

		echo "-- System Information --\r\n \r\n";

		echo 'site_url(): ';
		echo esc_html( site_url() );
		echo "\r\n";

		echo 'home_url(): ';
		echo esc_html( home_url() );
		echo "\r\n";

		echo 'WordPress: ';
		echo bloginfo( 'version' );
		if ( is_multisite() ) {
			echo ' Multisite';
		}
		echo "\r\n";

		echo 'Web Server: ';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Diagnostic output only.
		echo esc_html( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '' );
		echo "\r\n";

		echo 'PHP: ';
		if ( function_exists( 'phpversion' ) ) {
			echo esc_html( phpversion() );
		}
		echo "\r\n";

		echo 'MySQL: ';
		if ( empty( $wpdb->use_mysqli ) ) {
			// mysql_get_server_info is deprecated, use alternative.
			echo esc_html( 'Deprecated (mysql extension)' );
		} else {
			// phpcs:ignore WordPress.DB.RestrictedFunctions.mysql_mysqli_get_server_info -- Diagnostic output only.
			echo esc_html( mysqli_get_server_info( isset( $wpdb->dbh ) ? $wpdb->dbh : null ) );
		}
		echo "\r\n";

		echo 'ext/mysqli: ';
		echo empty( $wpdb->use_mysqli ) ? 'no' : 'yes';
		echo "\r\n";

		echo 'WP Memory Limit: ';
		echo esc_html( defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : 'Not defined' );
		echo "\r\n";

		echo 'Blocked External HTTP Requests: ';
		if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
			echo 'None';
		} else {
			$accessible_hosts = ( defined( 'WP_ACCESSIBLE_HOSTS' ) ) ? WP_ACCESSIBLE_HOSTS : '';

			if ( empty( $accessible_hosts ) ) {
				echo 'ALL';
			} else {
				echo 'Partially (Accessible Hosts: ' . esc_html( $accessible_hosts ) . ')';
			}
		}
		echo "\r\n";

		echo 'WP Locale: ';
		echo esc_html( get_locale() );
		echo "\r\n";

		echo 'DB Charset: ';
		echo esc_html( defined( 'DB_CHARSET' ) ? DB_CHARSET : 'Not defined' );
		echo "\r\n";

		$suhosin_limit = function_exists( 'ini_get' ) ? ini_get( 'suhosin.post.max_value_length' ) : false; // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found
		if ( function_exists( 'ini_get' ) && $suhosin_limit ) {
			echo 'Suhosin Post Max Value Length: ';
			echo esc_html( is_numeric( $suhosin_limit ) ? size_format( $suhosin_limit ) : $suhosin_limit );
			echo "\r\n";
		}

		$suhosin_limit = function_exists( 'ini_get' ) ? ini_get( 'suhosin.request.max_value_length' ) : false;
		if ( function_exists( 'ini_get' ) && $suhosin_limit ) {
			echo 'Suhosin Request Max Value Length: ';
			echo esc_html( is_numeric( $suhosin_limit ) ? size_format( $suhosin_limit ) : $suhosin_limit );
			echo "\r\n";
		}

		echo 'Debug Mode: ';
		echo esc_html( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No' );
		echo "\r\n";

		echo 'WP Max Upload Size: ';
		$upload_size = size_format( wp_max_upload_size() );
		echo esc_html( $upload_size ? $upload_size : '' );
		echo "\r\n";

		echo 'PHP Time Limit: ';
		if ( function_exists( 'ini_get' ) ) {
			$time_limit = ini_get( 'max_execution_time' );
			echo esc_html( $time_limit ? $time_limit : '' );
		}
		echo "\r\n";

		echo 'PHP Error Log: ';
		if ( function_exists( 'ini_get' ) ) {
			$error_log = ini_get( 'error_log' );
			echo esc_html( $error_log ? $error_log : '' );
		}
		echo "\r\n";

		echo 'fsockopen: ';
		if ( function_exists( 'fsockopen' ) ) {
			echo 'Enabled';
		} else {
			echo 'Disabled';
		}
		echo "\r\n";

		echo 'OpenSSL: ';
		if ( defined( 'OPENSSL_VERSION_TEXT' ) ) {
			echo esc_html( OPENSSL_VERSION_TEXT );
		} else {
			echo 'Disabled';
		}
		echo "\r\n";

		echo 'cURL: ';
		if ( function_exists( 'curl_init' ) ) {
			echo 'Enabled';
		} else {
			echo 'Disabled';
		}
		if ( function_exists( 'curl_version' ) ) {
			$_curl = curl_version();
			if ( is_array( $_curl ) ) {
				$curl_version = isset( $_curl['version'] ) ? $_curl['version'] : '';
				$curl_ssl     = isset( $_curl['ssl_version'] ) ? $_curl['ssl_version'] : '';
				echo ' (' . esc_html( $curl_version ) . ' ' . esc_html( $curl_ssl ) . ')';
			}
		}
		echo "\r\n";

		$theme_info = wp_get_theme();
		echo 'Active Theme Name: ' . esc_html( $theme_info->get( 'Name' ) ) . "\r\n";
		echo 'Active Theme Folder: ' . esc_html( basename( $theme_info->get_stylesheet_directory() ) ) . "\r\n";
		if ( $theme_info->get( 'Template' ) ) {
			echo 'Parent Theme Folder: ' . esc_html( $theme_info->get( 'Template' ) ) . "\r\n";
		}
		if ( ! file_exists( $theme_info->get_stylesheet_directory() ) ) {
			echo "WARNING: Active Theme Folder Not Found\r\n";
		}

		echo "\r\n";

		echo "-- Active Plugins --\r\n \r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$network_active_plugins = wp_get_active_network_plugins();
			if ( class_exists( 'WPANALYTIFY_Utils' ) && class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'remove_wp_plugin_dir' ) ) {
				$active_plugins = array_map( array( 'WPANALYTIFY_Utils', 'remove_wp_plugin_dir' ), $network_active_plugins );
			} else {
				// Fallback if method doesn't exist.
				$active_plugins = array_map( 'basename', $network_active_plugins );
			}
		}

		foreach ( $active_plugins as $plugin ) {
			$suffix = '';
			self::print_plugin_details( WP_PLUGIN_DIR . '/' . $plugin, $suffix );
		}

		$mu_plugins = wp_get_mu_plugins();
		if ( $mu_plugins ) {
			echo "\r\n";

			echo "-- Must-use Plugins --\r\n \r\n";

			foreach ( $mu_plugins as $mu_plugin ) {
				self::print_plugin_details( $mu_plugin );
			}
		}

		echo "\r\n";

		if ( class_exists( 'WP_Analytify_Pro_Base' ) ) {

			$analytify_active_modules = array();

			$analytify_modules = get_option( 'wp_analytify_modules' );

			foreach ( $analytify_modules as $module ) {
				if ( 'active' === $module['status'] ) {
					$analytify_active_modules[] = $module['title'];
				}
			}

			echo "-- Active Modules --\r\n \r\n";

			if ( $analytify_active_modules ) {
				foreach ( $analytify_active_modules as $analytify_module ) {
					printf( "%s \r\n", esc_html( $analytify_module ) );
				}
			} else {
				echo "- None - \r\n";
			}

			echo "\r\n";

		}

		if ( ! empty( $authentication_date ) ) {
			echo "-- Last Authenticated --\r\n \r\n";
			echo esc_html( $authentication_date ) . " \r\n";
			echo "\r\n";
		}

		echo "-- Analytify Profile Setting --\r\n \r\n";

		$analytify_profile = get_option( 'wp-analytify-profile' );

		if ( class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'print_settings_array' ) ) {
			WPANALYTIFY_Utils::print_settings_array( $analytify_profile );
		} else {
			echo "Settings array method not available\r\n";
		}

		echo "\r\n";

		echo "-- Analytify Front Setting --\r\n \r\n";

		echo "\r\n";

		echo "-- Analytify Admin Setting --\r\n \r\n";

		$analytify_admin = get_option( 'wp-analytify-admin' );

		if ( class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'print_settings_array' ) ) {
			WPANALYTIFY_Utils::print_settings_array( $analytify_admin );
		} else {
			echo "Settings array method not available\r\n";
		}

		echo "\r\n";

		echo "-- Analytify Dashboard Setting --\r\n \r\n";

		$analytify_dashboard = get_option( 'wp-analytify-dashboard' );

		if ( class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'print_settings_array' ) ) {
			WPANALYTIFY_Utils::print_settings_array( $analytify_dashboard );
		} else {
			echo "Settings array method not available\r\n";
		}

		echo "\r\n";

		do_action( 'analytify_settings_logs' );

		echo "\r\n";

		echo "-- Analytify Advance Setting --\r\n \r\n";

		$analytify_advance = get_option( 'wp-analytify-advanced' );
		// If keys not set, show default.
		if ( ! isset( $analytify_advance['user_advanced_keys'] ) || 'off' === $analytify_advance['user_advanced_keys'] ) {

			// set as array if its string.
			if ( ! is_array( $analytify_advance ) ) {
				$analytify_advance = array(); }

			$analytify_advance['client_id']     = defined( 'ANALYTIFY_CLIENTID' ) ? ANALYTIFY_CLIENTID : 'Not defined';
			$analytify_advance['client_secret'] = 'Hidden';
		}

		if ( class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'print_settings_array' ) ) {
			WPANALYTIFY_Utils::print_settings_array( $analytify_advance );
		} else {
			echo "Settings array method not available\r\n";
		}
	}

	/**
	 * Print plugin details for diagnostic information
	 *
	 * @param string $plugin_path Full path to the plugin file.
	 * @param string $suffix Additional suffix to append.
	 * @return void
	 */
	private static function print_plugin_details( $plugin_path, $suffix = '' ) {
		if ( ! file_exists( $plugin_path ) ) {
			echo 'Plugin file not found: ' . esc_html( basename( $plugin_path ) ) . "\r\n";
			return;
		}

		$plugin_data = get_plugin_data( $plugin_path );
		if ( ! empty( $plugin_data['Name'] ) ) {
			echo esc_html( $plugin_data['Name'] );
			if ( ! empty( $plugin_data['Version'] ) ) {
				echo ' ' . esc_html( $plugin_data['Version'] );
			}
			if ( ! empty( $suffix ) ) {
				echo ' ' . esc_html( $suffix );
			}
			echo "\r\n";
		} else {
			echo esc_html( basename( $plugin_path ) ) . "\r\n";
		}
	}

	/**
	 * Dismiss pointer.
	 *
	 * @return void
	 */
	public static function dismiss_pointer() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify-dismiss-pointer', 'nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}
		update_option( 'analytify_dismiss_pointer', 1 );
		wp_send_json_success();
	}

	/**
	 * Remove comparison gif
	 *
	 * @return void
	 */
	public static function remove_comparison_gif() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify-remove-comparison-gif', 'nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}
		update_option( 'analytify_remove_comparison_gif', 1 );
		wp_send_json_success();
	}

	/**
	 * Deactivate plugin
	 *
	 * @return void
	 */
	public static function deactivate() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify-deactivate', 'nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}

		$feedback            = isset( $_POST['feedback'] ) ? sanitize_textarea_field( wp_unslash( $_POST['feedback'] ) ) : '';
		$deactivation_reason = isset( $_POST['deactivation_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['deactivation_reason'] ) ) : '';

		$data = array(
			'feedback' => $feedback,
			'reason'   => $deactivation_reason,
			'url'      => home_url(),
			'email'    => get_option( 'admin_email' ),
			'plugin'   => 'wp-analytify',
		);

		wp_remote_post(
			'https://analytify.io/wp-json/analytify/v1/deactivate',
			array(
				'body'    => $data,
				'timeout' => 30,
			)
		);

		update_option( 'analytify_deactivation_reason', $deactivation_reason );
		update_option( 'analytify_deactivation_feedback', $feedback );

		wp_send_json_success();
	}

	/**
	 * Opt-in yes
	 *
	 * @return void
	 */
	public static function optin_yes() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify_optin_page_nonce', 'optin_yes_nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}

		$sdk_data = get_option( 'wpb_sdk_wp-analytify', array() );
		if ( ! is_array( $sdk_data ) ) {
			$sdk_data = array();
		}

		$sdk_data['communication']   = 'yes';
		$sdk_data['diagnostic_info'] = 'yes';
		$sdk_data['extensions']      = 'yes';

		$sdk_data_json = wp_json_encode( $sdk_data );
		update_option( 'wpb_sdk_wp-analytify', $sdk_data_json );

		update_site_option( '_analytify_optin', 'yes' );

		wp_send_json_success();
	}

	/**
	 * Opt-out yes
	 *
	 * @return void
	 */
	public static function optout_yes() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify_optin_page_nonce', 'optout_yes_nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}

		$sdk_data = get_option( 'wpb_sdk_wp-analytify', array() );
		if ( ! is_array( $sdk_data ) ) {
			$sdk_data = array();
		}

		$sdk_data['communication']   = 'no';
		$sdk_data['diagnostic_info'] = 'no';
		$sdk_data['extensions']      = 'no';

		$sdk_data_json = wp_json_encode( $sdk_data );
		update_option( 'wpb_sdk_wp-analytify', $sdk_data_json );

		update_site_option( '_analytify_optin', 'no' );

		wp_send_json_success();
	}

	/**
	 * Opt-in skip
	 *
	 * @return void
	 */
	public static function optin_skip() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'analytify_optin_page_nonce', 'optin_skip_nonce' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}

		update_site_option( '_analytify_optin', 'skip' );
		wp_send_json_success();
	}

	/**
	 * Create json file for export settings.
	 *
	 * @return void
	 */
	public static function export_settings() {
		// Check if the user has the required capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ), 403 );
		}

		check_ajax_referer( 'import-export', 'nonce' );

		$profile_settings = get_option( 'wp-analytify-profile' );
		// Remove authentication values.
		unset( $profile_settings['profile_for_posts'] );
		unset( $profile_settings['profile_for_dashboard'] );
		unset( $profile_settings['hide_profiles_list'] );

		$settings = array(
			'wp-analytify-profile'  => $profile_settings,
			'wp-analytify-admin'    => get_option( 'wp-analytify-admin' ),
			'wp-analytify-advanced' => get_option( 'wp-analytify-advanced' ),
			'wp-analytify-email'    => get_option( 'wp-analytify-email' ),
		);

		if ( class_exists( 'WP_Analytify_Pro_Base' ) ) {
			$settings['wp-analytify-dashboard']         = get_option( 'wp-analytify-dashboard' );
			$settings['wp-analytify-events-tracking']   = get_option( 'wp-analytify-events-tracking' );
			$settings['wp-analytify-custom-dimensions'] = get_option( 'wp-analytify-custom-dimensions' );
		}

		if ( class_exists( 'Analytify_Addon_Forms' ) ) {
			$settings['wp-analytify-forms'] = get_option( 'wp-analytify-forms' );
		}
		// JSON encode the sanitized settings.
		$settings = wp_json_encode( $settings );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON output should not be escaped.
		echo $settings;
		wp_die();
	}

	/**
	 * Transfer json file data to settings.
	 *
	 * @return void
	 */
	public static function import_settings() {
		check_ajax_referer( 'import-export', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'No cheating, huh!', 'wp-analytify' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- File upload for settings import, nonce verified.
		$imp_tmp_name = isset( $_FILES['file']['tmp_name'] ) ? $_FILES['file']['tmp_name'] : '';

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading local uploaded file for settings import.
		$file_content  = $imp_tmp_name ? file_get_contents( $imp_tmp_name ) : '';
		$settings_json = json_decode( $file_content ? $file_content : '', true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			echo 'failed';
		}

		foreach ( $settings_json as $setting => $value_array ) {
			$old_value_array = get_option( $setting );

			if ( ! empty( $value_array ) ) {
				if ( 'wp-analytify-profile' === $setting && ! empty( $old_value_array ) ) { // For profile tab settings update except authentication values.
					$old_value_array['install_ga_code']        = $value_array['install_ga_code'];
					$old_value_array['exclude_users_tracking'] = $value_array['exclude_users_tracking'];
					update_option( $setting, $old_value_array );
				} else { // Update whole settings tab array.
					update_option( $setting, $value_array );
				}
			}
		}

		echo 'success';
		wp_die();
	}
}
