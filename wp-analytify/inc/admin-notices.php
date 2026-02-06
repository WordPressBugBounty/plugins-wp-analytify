<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable
/**
 * Admin Notices Component for WP Analytify
 *
 * This file contains all admin notice and promotion related functions
 * that were previously in the main plugin file.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Notices Component Class
 */
class Analytify_Admin_Notices {

	/**
	 * Main plugin instance
	 *
	 * @var WP_Analytify|null
	 */
	private $analytify;

	/**
	 * Constructor
	 *
	 * @version 7.0.5
	 * @param WP_Analytify|null $analytify Main plugin instance.
	 */
	public function __construct( $analytify = null ) {
		$this->analytify = $analytify;
	}

	/**
	 * Display pro update notice
	 *
	 * @version 7.0.5
	 * @return void
	 */
	public function pro_update_notice() {
		if ( defined( 'ANALYTIFY_PRO_VERSION' ) && version_compare( ANALYTIFY_PRO_VERSION, '6.0.0', '<' ) ) {
			$class   = 'wp-analytify-danger';
			$message = sprintf( // translators: Update notice.
				esc_html__( '%1$sNote:%2$s Please update to the latest Analytify Pro version to manage all modules/addons from %3$s here %4$s.', 'wp-analytify' ),
				'<b>',
				'</b>',
				'<a href="' . esc_url( admin_url( 'admin.php?page=analytify-addons' ) ) . '">',
				'</a>'
			);
			analytify_notice( $message, $class );
		}
	}

	/**
	 * Display GA4 update notice
	 *
	 * @version 7.0.5
	 * @return void
	 */
	public function addons_ga4_update_notice() {
		// Use instance property if available, otherwise fall back to global.
		$analytify = $this->analytify;
		if ( ! $analytify ) {
			global $wp_analytify;
			$analytify = $wp_analytify;
		}

		// Check if GA4 mode is enabled.
		$is_ga4 = false;
		if ( $analytify ) {
			// Use reflection to access protected property.
			if ( property_exists( $analytify, 'is_reporting_in_ga4' ) ) {
				$reflection = new ReflectionClass( $analytify );
				$property   = $reflection->getProperty( 'is_reporting_in_ga4' );
				$property->setAccessible( true );
				$is_ga4 = $property->getValue( $analytify );
			} else {
				// Property doesn't exist, check alternative methods.
				$is_ga4 = get_option( 'analytify_ga4_mode', false );
			}
		}

		if ( $analytify && ! $is_ga4 ) {
			$class   = 'wp-analytify-danger';
			$message = sprintf( // translators: GA4 update notice.
				esc_html__( '%1$sAttention:%2$s Switch to GA4 (Google Analytics 4), Your current version of Google Analytics (UA) is outdated and no longer tracks data. %3$sFollow the guide%4$s.', 'wp-analytify' ),
				'<b>',
				'</b>',
				'<a href="https://analytify.io/doc/switch-to-ga4/?utm_source=plugin-notices" target="_blank">',
				'</a>'
			);
			analytify_notice( $message, $class );
		}

		$analytify_pages = array(
			'toplevel_page_analytify-dashboard',
			'analytify_page_analytify-settings',
			'analytify_page_analytify-goals',
			'analytify_page_analytify-woocommerce',
			'analytify_page_analytify-authors',
			'edd-dashboard',
			'dashboard',
			'analytify_page_analytify-dimensions',
			'analytify_page_analytify-campaigns',
			'analytify_page_analytify-addons',
		);
		$current_screen  = get_current_screen() ? get_current_screen()->base : '';
		if ( in_array( $current_screen, $analytify_pages, true ) ) {

			$addons_update_todo = WPANALYTIFY_Utils::get_addons_to_upgmdate();
			if ( ! empty( $addons_update_todo ) ) {
				$class   = 'wp-analytify-danger';
				$message = sprintf( // translators: Update addons.
					esc_html__( '%1$sNotice:%2$s Please update the following plugins to make them work with the Analytify 5.0.0 smoothly. %3$s', 'wp-analytify' ),
					'<b>',
					'</b>',
					'<br>' . implode( '<br>', $addons_update_todo )
				);
				analytify_notice( $message, $class );
			}
		}
	}

	/**
	 * Display cache clear notice
	 *
	 * @version 7.0.5
	 * @return void
	 */
	public function analytify_cache_clear_notice() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameter for display purposes
		if ( ! isset( $_GET['analytify-cache'] ) ) {
			return;
		}

		$notice_message = esc_html__( 'Analytify statistics refreshed', 'wp-analytify' );
		$class          = 'wp-analytify-success wp-analytify-refresh-stats';

		analytify_notice( $notice_message, $class );
	}

	/**
	 * Dismiss rank math notice
	 *
	 * @version 7.0.5
	 * @return void
	 */
	public function analytify_dismiss_rank_math_notice() {
		// Check user capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to perform this action.', 'wp-analytify' ) );
			return;
		}

		// Verify nonce for security.
		if ( ! check_ajax_referer( 'analytify_dismiss_rank_math_notice', 'nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'wp-analytify' ) );
			return;
		}

		update_option( 'analytify_show_rank_math_notice', false );
		wp_send_json_success();
	}

	/**
	 * Display admin notice
	 *
	 * @version 7.0.5
	 * @return void
	 */
	public function analytify_admin_notice() {
		// Check if current page is analytify dashboard.
		if ( WPANALYTIFY_Utils::is_current_page( 'analytify-dashboard' ) ) {
			// Check if the notice should be displayed by fetching the option.
			$show_notice = get_option( 'analytify_show_rank_math_notice', true );

			if ( $show_notice && is_plugin_active( 'seo-by-rank-math-pro/rank-math-pro.php' ) ) {
				add_option( 'analytify_show_rank_math_notice', true );
				$rank_math_analytics_options = get_option( 'rank_math_google_analytic_options' );

				if ( is_array( $rank_math_analytics_options ) && isset( $rank_math_analytics_options['local_ga_js'] ) && $rank_math_analytics_options['local_ga_js'] ) {
					$screen = get_current_screen();
					// Check if the current page is related to Rank Math or Analytify.
					if ( $screen && ( strpos( $screen->id, 'analytify' ) !== false ) ) {
						echo '<div id="message" class="notice notice-warning is-dismissible analytify-rank-math-notice">
								<p>' . wp_kses(
							sprintf( // translators: Rank match notice.
								__( 'Kindly note that Rank Math Self-Hosted Analytics JS File Feature is available in %1$s Analytify %2$s as well. We recommend using Analytify for this functionality.', 'wp-analytify' ),
								'<a style="text-decoration:none" href="' . esc_url( menu_page_url( 'analytify-settings', false ) ) . '#wp-analytify-advanced">',
								'</a>'
							),
							array(
								'a' => array(
									'href'  => array(),
									'style' => array(),
								),
							)
						) . '</p></div>'; ?>
						<script type="text/javascript">
							(function($) {
								$(document).on('click', '.analytify-rank-math-notice .notice-dismiss', function() {
									$.post(ajaxurl, {
										action: 'analytify_dismiss_rank_math_notice',
										nonce: '<?php echo esc_js( wp_create_nonce( 'analytify_dismiss_rank_math_notice' ) ); ?>'
									});
								});
							})(jQuery);
						</script>
						<?php
					} elseif ( $screen && strpos( $screen->id, 'rank-math' ) !== false ) {
						echo '<div id="message" class="rank-math-notice notice is-dismissible">
								<p>' . wp_kses(
							sprintf( // translators: Rank match notice.
								__( 'Kindly note that Rank Math Self-Hosted Analytics JS File Feature is available in %1$s Analytify %2$s as well. We recommend using Analytify for this functionality.', 'wp-analytify' ),
								'<a style="text-decoration:none" href="' . esc_url( menu_page_url( 'analytify-settings', false ) ) . '#wp-analytify-advanced">',
								'</a>'
							),
							array(
								'a' => array(
									'href'  => array(),
									'style' => array(),
								),
							)
						) . '</p></div>';
						?>
						<script type="text/javascript">
							(function($) {
								$(document).on('click', '.rank-math-notice .notice-dismiss', function() {
									$.post(ajaxurl, {
										action: 'analytify_dismiss_rank_math_notice',
										nonce: '<?php echo esc_js( wp_create_nonce( 'analytify_dismiss_rank_math_notice' ) ); ?>'
									});
								});
							})(jQuery);
						</script>
						<?php
					}
				}
			}
		}
	}
}
