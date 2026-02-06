<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable
/**
 * Scripts & Styles Component for WP Analytify
 *
 * This file contains all script and style enqueuing functions
 * that were previously in the main plugin file.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scripts & Styles Component Class
 */
class Analytify_Scripts_Styles {

	/**
	 * Main plugin instance
	 *
	 * @var WP_Analytify
	 */
	private $analytify;

	/**
	 * Main plugin file path
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Constructor
	 *
	 * @param WP_Analytify $analytify Main plugin instance.
	 */
	public function __construct( $analytify ) {
		$this->analytify   = $analytify;
		$this->plugin_file = WP_ANALYTIFY_PLUGIN_DIR . '/wp-analytify.php';
	}

	/**
	 * Loading admin styles CSS for the plugin.
	 *
	 * @param  string $page loaded page name.
	 * @return void
	 */
	public function admin_styles( $page ) {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'admin-bar-style', plugins_url( 'assets/css/admin_bar_styles.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );

		// For Settings only.
		if ( 'analytify_page_analytify-settings' === $page || 'analytify_page_analytify-campaigns' === $page ) {
			wp_enqueue_style( 'jquery_tooltip', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), ANALYTIFY_VERSION );
		}

		// For Single Page/Post Stats.
		if ( 'analytify_page_analytify-settings' === $page || 'post.php' === $page || 'post-new.php' === $page ) {
			wp_enqueue_style( 'chosen', plugins_url( 'assets/css/chosen.min.css', $this->plugin_file ), array(), ANALYTIFY_VERSION ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is set
		}

		if ( false !== strpos( $page, 'analytify' ) || 'post.php' === $page || 'post-new.php' === $page || 'index.php' === $page ) {
			wp_enqueue_style( 'wp-analytify-style', plugins_url( 'assets/css/wp-analytify-style.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );
			wp_enqueue_style( 'wp-analytify-default-style', plugins_url( 'assets/css/styles.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );

			$conditional_style = '';

			// Filter dashboard header animation.
			if ( apply_filters( 'analytify_dashboard_head_animate', true ) ) {
				$conditional_style .= '
				.wpanalytify .graph {
					-webkit-animation: graph_animation 130s linear infinite;
					-moz-animation: graph_animation 130s linear infinite;
					-o-animation: graph_animation 130s linear infinite;
					animation: graph_animation 130s linear infinite;
				}
				.wpanalytify .graph:after {
					-webkit-animation: graph_animation 250s linear infinite;
					-moz-animation: graph_animation 250s linear infinite;
					-o-animation: graph_animation 250s linear infinite;
					animation: graph_animation 250s linear infinite;
				}';
			}

			// Add conditional style.
			wp_add_inline_style( 'wp-analytify-default-style', $conditional_style );
		}

		wp_enqueue_style( 'wp-analytify-utils-style', plugins_url( 'assets/css/utils.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );
		// For WP Pointer.
		if ( 1 !== (int) get_option( 'show_tracking_pointer_1' ) ) {
			wp_enqueue_style( 'wp-pointer' );
		}

		// Hide unnamed menu item (promo page) from Analytify submenu.
		$hide_submenu_style = '
		#toplevel_page_analytify-dashboard .wp-submenu li a[href*="analytify-promo"] {
			display: none !important;
		}';
		wp_add_inline_style( 'wp-analytify-utils-style', $hide_submenu_style );
	}

	/**
	 * Loading admin scripts JS for the plugin.
	 *
	 * @param string $page Current page.
	 * @return void
	 */
	public function admin_scripts( $page ) {

		wp_enqueue_script( 'wp-analytify-script-js', plugins_url( 'assets/js/wp-analytify.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally

		wp_localize_script(
			'wp-analytify-script-js',
			'wp_analytify_script',
			array(
				'url'              => esc_url_raw( rest_url( 'wp-analytify/v1/get_report/' ) ),
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'delimiter'        => WPANALYTIFY_Utils::get_delimiter(),
				'no_stats_message' => __( 'No activity during this period.', 'wp-analytify' ),
				'error_message'    => __( 'Something went wrong. Please try again later single.', 'wp-analytify' ),
			)
		);

		global $post_type;

		// For main page.
		if ( 'index.php' === $page || 'toplevel_page_analytify-dashboard' === $page || 'analytify_page_analytify-woocommerce' === $page || 'analytify_page_edd-dashboard' === $page || 'analytify_page_analytify-campaigns' === $page || 'analytify_page_analytify-goals' === $page || 'analytify_page_analytify-forms' === $page || 'analytify_page_analytify-dimensions' === $page || 'analytify_page_analytify-authors' === $page || 'analytify_page_analytify-events' === $page || 'analytify_page_analytify-forms' === $page || 'analytify_page_analytify-promo' === $page || in_array( $post_type, $this->analytify->settings->get_option( 'show_analytics_post_types_back_end', 'wp-analytify-admin', array() ), true ) ) {
			// Using WP's internal moment-js, after 4.2.1.

			/**
			 * Filter to force moment js to use the same timezone as the one set within WordPress.
			 * Default is false.
			 *
			 * Filter was remove after 4.2.1
			 *
			 * Example use: add_filter( 'analytify_set_moment_timezone_to_match_wp', '__return_true' );
			 */

			/* phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- Kept for reference
			// phpcs:disable
			$apply_timezone_match = apply_filters( 'analytify_set_moment_timezone_to_match_wp', false );
			$timezone = $apply_timezone_match ? WPANALYTIFY_Utils::timezone() : false;

			if ( $timezone ) {
				wp_enqueue_script( 'moment-timezone-with-data', plugins_url( 'assets/js/moment-timezone-with-data.min.js', __FILE__ ), array( 'jquery', 'moment' ), '0.5.34' );
			}

			wp_localize_script( 'moment', 'moment_analytify', array( 'timezone' => $timezone ) );
			// phpcs:enable
			*/

			wp_enqueue_script( 'pikaday-js', plugins_url( 'assets/js/pikaday.js', $this->plugin_file ), array( 'moment' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally
			wp_enqueue_script( 'analytify-dashboard-js', plugins_url( 'assets/js/wp-analytify-dashboard.js', $this->plugin_file ), array( 'pikaday-js' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally

			wp_localize_script(
				'analytify-dashboard-js',
				'analytify_dashboard',
				array(
					'i18n' => array(
						'previousMonth' => __( 'Previous Month', 'wp-analytify' ),
						'nextMonth'     => __( 'Next Month', 'wp-analytify' ),
						'months'        => array(
							__( 'January', 'wp-analytify' ),
							__( 'February', 'wp-analytify' ),
							__( 'March', 'wp-analytify' ),
							__( 'April', 'wp-analytify' ),
							__( 'May', 'wp-analytify' ),
							__( 'June', 'wp-analytify' ),
							__( 'July', 'wp-analytify' ),
							__( 'August', 'wp-analytify' ),
							__( 'September', 'wp-analytify' ),
							__( 'October', 'wp-analytify' ),
							__( 'November', 'wp-analytify' ),
							__( 'December', 'wp-analytify' ),
						),
						'weekdays'      => array(
							__( 'Sunday', 'wp-analytify' ),
							__( 'Monday', 'wp-analytify' ),
							__( 'Tuesday', 'wp-analytify' ),
							__( 'Wednesday', 'wp-analytify' ),
							__( 'Thursday', 'wp-analytify' ),
							__( 'Friday', 'wp-analytify' ),
							__( 'Saturday', 'wp-analytify' ),
						),
						'weekdaysShort' => array(
							__( 'Sun', 'wp-analytify' ),
							__( 'Mon', 'wp-analytify' ),
							__( 'Tue', 'wp-analytify' ),
							__( 'Wed', 'wp-analytify' ),
							__( 'Thu', 'wp-analytify' ),
							__( 'Fri', 'wp-analytify' ),
							__( 'Sat', 'wp-analytify' ),
						),
					),
				)
			);
		}

		// For dashboard only.
		$analytify_chart_pages = array( 'toplevel_page_analytify-dashboard', 'analytify_page_analytify-woocommerce', 'analytify_page_edd-dashboard', 'analytify_page_analytify-campaigns' );
		if ( in_array( $page, $analytify_chart_pages, true ) ) {
				// Enqueue the main JavaScript file.
			wp_enqueue_script( 'echarts-js', plugins_url( 'assets/js/echarts.min.js', $this->plugin_file ), array(), ANALYTIFY_VERSION, true );
			wp_enqueue_script( 'echarts-world-js', 'https://cdn.jsdelivr.net/npm/echarts-maps@1.1.0/world.min.js', array(), ANALYTIFY_VERSION, true );
		}

		// Main dashboard file that handles AJAX calls for core, also generates the template.
		if ( 'toplevel_page_analytify-dashboard' === $page ) {
			/**
			 * Tells the script to load the data via an ajax request on date change.
			 * Only load data via ajax if the Pro version is 5.0.0 or higher.
			 */
			$load_via_ajax = true;
			if ( defined( 'ANALYTIFY_PRO_VERSION' ) && 0 > version_compare( ANALYTIFY_PRO_VERSION, '5.0.0' ) ) {
				$load_via_ajax = false;
			}

			wp_enqueue_style( 'analytify-dashboard-core', plugins_url( 'assets/css/common-dashboard.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );
			// Code added by jawad for fixing.
			$rest_url = esc_url_raw( get_rest_url() );
			$api_url  = $rest_url . 'wp-analytify/v1/get_report/';

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameters for display purposes
			$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameters for display purposes
			$show = isset( $_GET['show'] ) ? sanitize_text_field( wp_unslash( $_GET['show'] ) ) : '';
			if ( class_exists( 'WP_Analytify_Pro_Base' ) && version_compare( ANALYTIFY_PRO_VERSION, '5.0.0' ) >= 0 && ! empty( $page ) && empty( $show ) && 'analytify-dashboard' === $page ) {
				wp_enqueue_script( 'analytify-stats-core', plugins_url( 'assets/js/stats-core.js', $this->plugin_file ), array( 'jquery', 'echarts-js', 'analytify-comp-chart' ), ANALYTIFY_VERSION, true );
				wp_enqueue_script( 'jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.min.js', array(), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- External script loaded in header
				wp_enqueue_script( 'html2canvas', 'https://html2canvas.hertzen.com/dist/html2canvas.js', array(), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- External script loaded in header

			} else {
				wp_enqueue_script( 'analytify-stats-core', plugins_url( 'assets/js/stats-core.js', $this->plugin_file ), array( 'jquery', 'echarts-js' ), ANALYTIFY_VERSION, true );
			}
			// Localize the GeoJSON file URL to use in JavaScript.
			wp_localize_script(
				'analytify-stats-core',
				'geoJsonData',
				array(
					'geoJsonUrl' => plugins_url( 'assets/js/geo.json', $this->plugin_file ),
				)
			);

			wp_localize_script(
				'analytify-stats-core',
				'analytify_stats_core',
				array(
					'url'              => $api_url,
					'delimiter'        => WPANALYTIFY_Utils::get_delimiter(),
					'ga_mode'          => WPANALYTIFY_Utils::get_ga_mode(),
					'ga4_report_url'   => WP_ANALYTIFY_FUNCTIONS::get_ga_report_url( WPANALYTIFY_Utils::get_reporting_property() ),
					'nonce'            => wp_create_nonce( 'wp_rest' ),
					'load_via_ajax'    => $load_via_ajax,
					'dist_js_url'      => plugins_url( 'assets/js/', $this->plugin_file ),
					'no_stats_message' => __( 'No activity during this period.', 'wp-analytify' ),
					'error_message'    => __( 'Something went wrong. Please try again.', 'wp-analytify' ),
				)
			);
		}

		// For Settings only.
		if ( 'analytify_page_analytify-settings' === $page ) {
			wp_enqueue_script( 'chosen-js', plugins_url( 'assets/js/chosen.jquery.min.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally
			wp_enqueue_script( 'analytify-settings-js', plugins_url( 'assets/js/wp-analytify-settings.js', $this->plugin_file ), array( 'jquery-ui-tooltip', 'jquery', 'chosen-js' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally
			wp_localize_script(
				'analytify-settings-js',
				'analytify_settings',
				array(
					'is_hide_profile' => $this->analytify->settings->get_option( 'hide_profiles_list', 'wp-analytify-profile', 'off' ),
					'is_authenticate' => (bool) get_option( 'pa_google_token' ),
					'ga_mode'         => WPANALYTIFY_Utils::get_ga_mode(),
				)
			);

			// Localize wpanalytify_data for settings script.
			// This must be done here to ensure the data is available when the script loads.
			$nonces_pre = array(
				'check_license'          => wp_create_nonce( 'check-license' ),
				'activate_license'       => wp_create_nonce( 'activate-license' ),
				'clear_log'              => wp_create_nonce( 'clear-log' ),
				'fetch_log'              => wp_create_nonce( 'fetch-log' ),
				'import_export'          => wp_create_nonce( 'import-export' ),
				'analytify_rated'        => wp_create_nonce( 'analytify-rated' ),
				'reactivate_license'     => wp_create_nonce( 'reactivate-license' ),
				'single_post_stats'      => wp_create_nonce( 'analytify-get-single-stats' ),
				'send_single_post_email' => wp_create_nonce( 'analytify-single-post-email' ),
			);

			$nonces = apply_filters( 'wpanalytify_nonces', $nonces_pre );

			$data_pre = array(
				'this_url'     => esc_html( addslashes( home_url() ) ),
				'is_multisite' => esc_html( is_multisite() ? 'true' : 'false' ),
				'nonces'       => $nonces,
			);

			$data = apply_filters( 'wpanalytify_data', $data_pre );

			// Backup the complete data before localization.
			// This is needed because the Pro plugin's filter uses array_merge which replaces
			// the entire nonces array instead of merging it, and other scripts may overwrite
			// wpanalytify_data after this script loads.
			//
			// Security Note: Nonces are meant to be in JavaScript (this is how WordPress AJAX works).
			// The security comes from server-side verification via check_ajax_referer() which:
			// - Verifies the nonce is valid for the action
			// - Checks the nonce is tied to the current user session
			// - Validates the HTTP referer
			// - Requires manage_options capability
			// The backup variable is just a copy of the same nonce already exposed in JavaScript.
			$settings_data_backup = $data;
			$backup_json          = wp_json_encode( $settings_data_backup );

			wp_localize_script( 'analytify-settings-js', 'wpanalytify_data', $data );

			// Add backup variable BEFORE the settings script loads.
			// This backup is used as a fallback if wpanalytify_data gets overwritten.
			wp_add_inline_script(
				'analytify-settings-js',
				'var wpanalytify_data_settings_backup = ' . $backup_json . ';',
				'before'
			);

			// Add restore script that runs immediately and on jQuery ready.
			// This ensures wpanalytify_data is restored if it gets overwritten by other scripts.
			add_action(
				'admin_footer',
				function () use ( $backup_json ) {
					$restore_script = '
					(function() {
						var backup = ' . $backup_json . ';
						function restoreIfNeeded() {
							if (typeof wpanalytify_data === "undefined" || !wpanalytify_data.nonces || !wpanalytify_data.nonces.fetch_log) {
								wpanalytify_data = backup;
								return true;
							}
							return false;
						}
						// Restore immediately when this script runs.
						restoreIfNeeded();
						// Also restore when DOM is ready (in case something overwrites it).
						if (document.readyState === "loading") {
							document.addEventListener("DOMContentLoaded", restoreIfNeeded);
						}
						// Also restore when jQuery is ready (in case something overwrites it).
						if (typeof jQuery !== "undefined") {
							jQuery(document).ready(restoreIfNeeded);
						}
					})();
					';

					// Output as standalone script to ensure it runs after all other scripts.
					echo '<script type="text/javascript">' . $restore_script . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is already escaped, script is intentional
				},
				999
			);
		}

		// Addons page script - Basic localization here, full localization with slugs happens in page-addons.php.
		if ( 'analytify_page_analytify-addons' === $page ) {
			wp_enqueue_script( 'analytify-addons-js', plugins_url( 'assets/js/wp-analytify-addons.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally.
			// Basic localization - page-addons.php will override with full data including allowed_slugs.
			wp_localize_script(
				'analytify-addons-js',
				'analytify_addons',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'addons' ),
					'allowed_slugs' => array(), // Will be populated by page-addons.php.
				)
			);
		}

		// For Single Page/Post Stats.
		if ( 'post.php' === $page || 'post-new.php' === $page ) {
			wp_enqueue_script( 'chosen-js', plugins_url( 'assets/js/chosen.jquery.min.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally
		}

		if ( 1 !== (int) get_option( 'show_tracking_pointer_1' ) ) {
			wp_enqueue_script( 'wp-pointer' );
		}

		wp_localize_script(
			'wp-analytify-script-js',
			'wpanalytify_strings',
			array(
				'enter_license_key'        => __( 'Please enter your license key.', 'wp-analytify' ),
				'register_license_problem' => __( 'A problem occurred when trying to register the license, please try again.', 'wp-analytify' ),
				'license_check_problem'    => __( 'A problem occurred when trying to check the license, please try again.', 'wp-analytify' ),
				'license_registered'       => __( 'Your license has been activated. You will now receive automatic updates and access to email support.', 'wp-analytify' ),
			)
		);

		$nonces = apply_filters(
			'wpanalytify_nonces',
			array(
				'check_license'          => wp_create_nonce( 'check-license' ),
				'activate_license'       => wp_create_nonce( 'activate-license' ),
				'clear_log'              => wp_create_nonce( 'clear-log' ),
				'fetch_log'              => wp_create_nonce( 'fetch-log' ),
				'import_export'          => wp_create_nonce( 'import-export' ),
				'analytify_rated'        => wp_create_nonce( 'analytify-rated' ),
				'reactivate_license'     => wp_create_nonce( 'reactivate-license' ),
				'single_post_stats'      => wp_create_nonce( 'analytify-get-single-stats' ),
				'send_single_post_email' => wp_create_nonce( 'analytify-single-post-email' ),
			)
		);

		$data = apply_filters(
			'wpanalytify_data',
			array(
				'this_url'     => esc_html( addslashes( home_url() ) ),
				'is_multisite' => esc_html( is_multisite() ? 'true' : 'false' ),
				'nonces'       => $nonces,
			)
		);

		// Fix: Only localize wpanalytify_data to wp-analytify-script-js if NOT on settings page.
		// Reason: wp-analytify-script-js doesn't use wpanalytify_data, but analytify-settings-js does.
		// If we localize to both scripts with the same variable name, the second one overwrites the first.
		// By skipping localization to wp-analytify-script-js on the settings page, we prevent the overwrite.
		if ( 'analytify_page_analytify-settings' !== $page ) {
			wp_localize_script( 'wp-analytify-script-js', 'wpanalytify_data', $data );
		}

		// Also ensure ajaxurl is available for settings script if it's loaded.
		if ( 'analytify_page_analytify-settings' === $page ) {
			add_action(
				'admin_footer',
				function () {
					// Ensure ajaxurl is available if not already set.
					if ( ! wp_script_is( 'analytify-settings-js', 'done' ) ) {
						wp_add_inline_script(
							'analytify-settings-js',
							'if (typeof ajaxurl === "undefined") { var ajaxurl = "' . esc_js( admin_url( 'admin-ajax.php' ) ) . '"; }',
							'before'
						);
					}
				},
				20
			);
		}

		// Print JS at footer.
	}

	/**
	 * Loading frontend scripts JS for the plugin.
	 *
	 * @return void
	 */
	public function front_scripts() {
		if ( 'on' === $this->analytify->settings->get_option( 'disable_front_end', 'wp-analytify-front', 'off' ) ) {
			return;
		}

		// Only enqueue if file exists to prevent 404 errors.
		$front_js_path = plugin_dir_path( $this->plugin_file ) . 'assets/js/front-analytics.js';
		if ( file_exists( $front_js_path ) ) {
			wp_enqueue_script( 'wp-analytify-front-js', plugins_url( 'assets/js/front-analytics.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter -- Script loaded in header intentionally

			wp_localize_script(
				'wp-analytify-front-js',
				'wp_analytify_front',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wp_analytify_front_nonce' ),
				)
			);
		}

		// Enqueue scroll depth script if enabled.
		if ( 'on' === $this->analytify->settings->get_option( 'depth_percentage', 'wp-analytify-advanced', 'off' ) ) {
			wp_enqueue_script( 'wp-analytify-scrolldepth', plugins_url( 'assets/js/scrolldepth.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, true );

			$ga_mode       = class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'get_ga_mode' ) ? WPANALYTIFY_Utils::get_ga_mode() : 'ga3';
			$tracking_mode = defined( 'WP_ANALYTIFY_TRACKING_MODE' ) ? WP_ANALYTIFY_TRACKING_MODE : 'gtag';

			// Only enqueue scroll depth for GA4 + gtag to avoid UA incompatibility.
			if ( 'ga4' === $ga_mode && 'gtag' === $tracking_mode ) {
				wp_enqueue_script( 'wp-analytify-scrolldepth', plugins_url( 'assets/js/scrolldepth.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, true );
				wp_localize_script(
					'wp-analytify-scrolldepth',
					'analytifyScroll',
					array(
						'tracking_mode' => $tracking_mode,
						'ga4_tracking'  => true,
						'permalink'     => get_permalink(),
					)
				);
			}
		}

		// Enqueue video tracking script if enabled.
		if ( 'on' === $this->analytify->settings->get_option( 'video_tracking', 'wp-analytify-advanced', 'off' ) ) {
			$ga_mode       = class_exists( 'WPANALYTIFY_Utils' ) && method_exists( 'WPANALYTIFY_Utils', 'get_ga_mode' ) ? WPANALYTIFY_Utils::get_ga_mode() : 'ga3';
			$tracking_mode = defined( 'WP_ANALYTIFY_TRACKING_MODE' ) ? WP_ANALYTIFY_TRACKING_MODE : 'gtag';

			// Only enqueue video tracking for GA4 + gtag to avoid UA incompatibility.
			if ( 'ga4' === $ga_mode && 'gtag' === $tracking_mode ) {
				$video_tracking_path = plugin_dir_path( $this->plugin_file ) . 'assets/js/video_tracking.js';
				if ( file_exists( $video_tracking_path ) ) {
					wp_enqueue_script( 'wp-analytify-video-tracking', plugins_url( 'assets/js/video_tracking.js', $this->plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, true );
					wp_localize_script(
						'wp-analytify-video-tracking',
						'analytifyVideo',
						array(
							'tracking_mode' => $tracking_mode,
							'ga4_tracking'  => true,
							'permalink'     => get_permalink(),
						)
					);
				}
			}
		}
	}

	/**
	 * Loading frontend styles CSS for the plugin.
	 *
	 * @return void
	 */
	public function front_styles() {
		if ( 'on' === $this->analytify->settings->get_option( 'disable_front_end', 'wp-analytify-front', 'off' ) ) {
			return;
		}

		// Only enqueue if file exists to prevent 404 errors.
		$front_css_path = plugin_dir_path( $this->plugin_file ) . 'assets/css/front-analytics.css';
		if ( file_exists( $front_css_path ) ) {
			wp_enqueue_style( 'wp-analytify-front-style', plugins_url( 'assets/css/front-analytics.css', $this->plugin_file ), array(), ANALYTIFY_VERSION );
		}
	}

	/**
	 * Add dashboard inline styles
	 *
	 * @return void
	 */
	public function add_dashboard_inline_styles() {
		$custom_css = $this->analytify->settings->get_option( 'custom_css_code', 'wp-analytify-advanced' );
		if ( ! empty( $custom_css ) ) {
			echo '<style type="text/css">' . wp_kses( $custom_css, array( 'style' => array() ) ) . '</style>';
		}
	}

	/**
	 * Add dashboard inline scripts
	 *
	 * @return void
	 */
	public function add_dashboard_inline_scripts() {
		$custom_js = $this->analytify->settings->get_option( 'custom_js_code', 'wp-analytify-advanced' );
		if ( ! empty( $custom_js ) ) {
			echo '<script type="text/javascript">' . wp_kses( $custom_js, array( 'script' => array() ) ) . '</script>';
		}

		// Move Analytify "refresh stats" notice below the Dashboard H1 (match native placement).
		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->id ) {
			?>
			<script type="text/javascript">
				(function($){
					$(function(){
						var $wrap   = $('.wrap').first();
						var $title  = $wrap.find('h1').first();
						var $notice = $('.wp-analytify-notification.wp-analytify-refresh-stats').first();

						if ($wrap.length && $title.length && $notice.length) {
							$notice.insertAfter($title);
						}
					});
				})(jQuery);
			</script>
			<?php
		}
	}
}
