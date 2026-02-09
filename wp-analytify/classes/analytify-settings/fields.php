<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing -- File doc comment exists
/**
 * Analytify Settings Fields Trait
 *
 * This trait manages the settings fields configuration and rendering.
 * It was created to separate field management logic from the main settings class,
 * providing a clean interface for defining and managing settings fields.
 *
 * PURPOSE:
 * - Defines settings field configurations
 * - Renders individual field types
 * - Manages field validation and sanitization
 * - Provides field-related utility methods
 *
 * @package WP_Analytify
 * @subpackage Settings
 * @since 8.0.0
 */

trait Analytify_Settings_Fields {
	/**
	 * Get settings fields configuration
	 *
	 * @version 7.0.5
	 * @return array<string, mixed>
	 */
	public function get_settings_fields() {
		$ga_properties_and_profiles = WP_ANALYTIFY_FUNCTIONS::fetch_ga_properties();

		$settings_fields = array(
			'wp-analytify-authentication' => array(
				array(
					'name'    => 'manual_ua_code',
					'label'   => __( 'GA Tracking ID', 'wp-analytify' ),
					'desc'    => wp_sprintf( '<p class="description">%s <code>%s</code> or <code>%s</code><br /> %s <code>%s</code>%s </p>', __( 'Manually add the Tracking ID that looks a like', 'wp-analytify' ), 'UA-XXXXXXXX-XX', 'G-XXXXXXXXXX', __( 'Our default tracking method is newly recommended', 'wp-analytify' ), __( 'Global Site Tag (gtag.js)', 'wp-analytify' ), __( 'by Google Analytics.', 'wp-analytify' ) ),
					'type'    => 'text',
					'default' => '',
				),
			),
			'wp-analytify-profile'        => array(
				array(
					'name'    => 'install_ga_code',
					'label'   => __( 'Install Google Analytics tracking code', 'wp-analytify' ),
					'desc'    => apply_filters( 'analytify_install_ga_text', __( 'Insert Google Analytics (GA) JavaScript code between the HEADER tags in your website. Uncheck this option if you have already inserted the GA code.', 'wp-analytify' ) ),
					'type'    => 'checkbox',
					'default' => 'on',
				),
				array(
					'name'    => 'exclude_users_tracking',
					'label'   => __( 'Exclude users from tracking', 'wp-analytify' ),
					'desc'    => __( 'Don\'t insert the tracking code for the above user roles.', 'wp-analytify' ),
					'type'    => 'chosen',
					'default' => array(),
					'options' => $this->get_current_roles(),
				),
				array(
					'name'    => 'profile_for_posts',
					'label'   => __( 'Profile for posts (Backend/Front-end)', 'wp-analytify' ),
					'desc'    => __( 'Select your Google Analytics website profile/stream for Analytify front-end/back-end statistics. <br /><strong>Note:</strong> GA4 properties from the above list.', 'wp-analytify' ),
					'type'    => 'select_profile',
					'default' => 'Choose profile for posts',
					'options' => $ga_properties_and_profiles,
					'size'    => '',
				),
				array(
					'name'    => 'profile_for_dashboard',
					'label'   => __( 'Profile for dashboard', 'wp-analytify' ),
					'desc'    => sprintf(
						/* translators: %s: documentation link */
						__(
							'Select your Google Analytics website profile/stream for Analytify dashboard statistics. Note: Not seeing new GA4 properties in the above list? See %s.',
							'wp-analytify'
						),
						'<a href="https://analytify.io/doc/how-to-integrate-analytify-with-google-analytics-4-ga4/" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'why and how to fix it', 'wp-analytify' ) .
						'</a>'
					),
					'type'    => 'select_profile',
					'default' => 'Choose profile for dashboard',
					'options' => $ga_properties_and_profiles,
				),
				array(
					'name'  => 'hide_profiles_list',
					'label' => __( 'Hide profiles list', 'wp-analytify' ),
					'desc'  => __( 'Hide the selection of profiles for the back-end/front-end dashboard and posts. You might want to do this so clients cannot see other profiles available.', 'wp-analytify' ),
					'type'  => 'checkbox',
				),
			),
			'wp-analytify-admin'          => array(
				array(
					'name'  => 'enable_back_end',
					'label' => __( 'Enable analytics under posts/pages (wp-admin)', 'wp-analytify' ),
					'desc'  => __( 'Disable if you don\'t want to load statistics on all pages by default.', 'wp-analytify' ),
					'type'  => 'checkbox',
				),
				array(
					'name'    => 'show_analytics_roles_back_end',
					'label'   => __( 'Display analytics to roles (posts & pages)', 'wp-analytify' ),
					'desc'    => __( 'Show analytics under posts and pages to the above selected user roles only.', 'wp-analytify' ),
					'type'    => 'chosen',
					'default' => array(),
					'options' => $this->get_current_roles(),
				),
				array(
					'name'    => 'show_analytics_post_types_back_end',
					'label'   => __( 'Analytics on post types', 'wp-analytify' ),
					// translators: %1$s is the opening link tag, %1$s is the closing link tag.
					'desc'    => class_exists( 'WP_Analytify_Pro' ) ? __( 'Show Analytics under the above post types only', 'wp-analytify' ) : sprintf( __( 'Show analytics below these post types only. Buy %1$sPremium%1$s version for Custom Post Types.', 'wp-analytify' ), '<a href="' . analytify_get_update_link() . '" target="_blank">', '</a>' ),
					'type'    => 'chosen',
					'default' => array(),
					'options' => $this->get_current_post_types(),
				),
				array(
					'name'    => 'show_panels_back_end',
					'label'   => __( 'Edit posts/pages analytics panels', 'wp-analytify' ),
					// translators: %1$s is the opening link tag, %2$s is the closing link tag.
					'desc'    => class_exists( 'WP_Analytify_Pro' ) ? __( 'Select which statistic panels you want to display under posts/pages.', 'wp-analytify' ) : sprintf( __( 'Select which statistic panels you want to display under posts/pages. Only "General Stats" will visible in Free Version. Buy %1$sPremium%2$s version to see the full statistics.', 'wp-analytify' ), '<a href="' . analytify_get_update_link() . '" target="_blank">', '</a>' ),
					'type'    => 'chosen',
					'default' => array(),
					'options' => array(
						'show-overall-dashboard'    => __( 'General Stats', 'wp-analytify' ),
						'show-geographic-dashboard' => __( 'Geographic Stats', 'wp-analytify' ),
						'show-system-stats'         => __( 'System Stats', 'wp-analytify' ),
						'show-keywords-dashboard'   => __( 'Keywords Stats', 'wp-analytify' ),
						'show-social-dashboard'     => __( 'Social Media Stats', 'wp-analytify' ),
						'show-referrer-dashboard'   => __( 'Referrers Stats', 'wp-analytify' ),
						'show-scroll-depth-stats'   => __( 'Scroll Depth', 'wp-analytify' ),
						'show-video-tracking-stats' => __( 'Video Tracking', 'wp-analytify' ),
						'show-what-happen-stats'    => __( 'Entrance Exits Stats', 'wp-analytify' ),
					),
				),
				array(
					'name'    => 'exclude_pages_back_end',
					'label'   => __( 'Exclude analytics on specific pages', 'wp-analytify' ),
					'desc'    => __( 'Enter a comma-separated list of the post/page ID\'s you do not want to display analytics for. For example: 21,44,66', 'wp-analytify' ),
					'type'    => 'text',
					'default' => '0',
				),
			),
			'wp-analytify-advanced'       => array(
				array(
					'name'    => 'ga4_web_data_stream',
					'label'   => __( 'Data Streams', 'wp-analytify' ),
					'desc'    => apply_filters( 'analytify_gtag_tracking_mode_text', __( 'Choose GA4 data/web stream to use for website tracking.', 'wp-analytify' ) ),
					'type'    => 'select_streams',
					'options' => WPANALYTIFY_Utils::fetch_ga4_streams(),
				),
				array(
					'name'  => 'measurement_protocol_secret',
					'label' => __( 'Measurement Protocol Secret', 'wp-analytify' ),
					'desc'  => __( 'Analytify creates measurement protocol secret itself, however you can also create your own secret key.', 'wp-analytify' ),
					'type'  => 'text',
					'class' => 'measurement_protocol_secret',
				),
				array(
					'name'  => 'user_advanced_keys',
					'label' => __( 'Setup Custom API keys?', 'wp-analytify' ),
					// translators: %1$s is line break, %2$s is Google Console link, %3$s is line break, %4$s is video guide link, %5$s is closing link tag.
					'desc'  => sprintf( __( 'It is highly recommended by Google to use your own API keys. %1$sYou need to create a Project in Google %2$s. %3$sHere is a short %4$svideo guide%5$s to get your own ClientID, Client Secret and Redirect URL and enter them in below inputs.', 'wp-analytify' ), '<br />', '<a target=\'_blank\' href=\'https://console.developers.google.com/project\'>Console</a>', '<br />', '<a target=\'_blank\' href=\'https://analytify.io/custom-api-keys-video\'>', '</a>' ),
					'type'  => 'checkbox',
					'class' => 'user_advanced_keys',
				),
				array(
					'name'              => 'client_id',
					'label'             => __( 'Client ID', 'wp-analytify' ),
					'desc'              => __( 'Your Client ID', 'wp-analytify' ),
					'type'              => 'text',
					'class'             => 'user_keys',
					'sanitize_callback' => 'trim',
				),
				array(
					'name'              => 'client_secret',
					'label'             => __( 'Client secret', 'wp-analytify' ),
					'desc'              => __( 'Your Client Secret', 'wp-analytify' ),
					'type'              => 'text',
					'class'             => 'user_keys',
					'sanitize_callback' => 'trim',
				),
				array(
					'name'              => 'redirect_uri',
					'label'             => __( 'Redirect URL', 'wp-analytify' ),
					// translators: %1$s is the admin URL.
					'desc'              => sprintf( __( '( Redirect URL is very important when you are using your own keys. Paste this into the above field: %1$s )', 'wp-analytify' ), '<b>' . admin_url( 'admin.php?page=analytify-settings' ) . '</b>' ),
					'type'              => 'text',
					'class'             => 'user_keys',
					'sanitize_callback' => 'trim',
				),
			),
		);

		// Dynamically append more advanced fields.
		$advance_setting_fields = array(
			array(
				'name'  => 'locally_host_analytics',
				'label' => __( 'Host Google Analytics Locally', 'wp-analytify' ),
				'desc'  => __( 'Hosting Google Analytics locally may improve your site speed and other core web vitals.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'track_user_id',
				'label' => __( 'Track User ID', 'wp-analytify' ),
				// translators: %1$s is the opening link tag, %2$s is the closing link tag.
				'desc'  => sprintf( __( 'Detailed information about Track User ID in Google Analytics can be found %1$shere%2$s.', 'wp-analytify' ), '<a href=\'https://support.google.com/analytics/answer/3123662\' target=\'_blank\'>', '</a>' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'depth_percentage',
				'label' => __( 'Scroll Depth', 'wp-analytify' ),
				'desc'  => __( 'Track page scroll depth percentage. This will help you figure out the most highlighted area of the page. Percentage events are fired at the 25%, 50%, 75%, and 100% scrolling points', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'video_tracking',
				'label' => __( 'Video Tracking', 'wp-analytify' ),
				'desc'  => __( 'Track embedded video interactions such as play, pause, and completion. This will help you understand user engagement with video content.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'demographic_interest_tracking',
				'label' => __( 'Demographic & Interest Tracking', 'wp-analytify' ),
				'desc'  => __( 'This allows you to view extra dimensions about users: Age, gender, affinity categories, in-market segments, etc.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => '404_page_track',
				'label' => __( 'Page Not Found (404)', 'wp-analytify' ),
				'desc'  => __( 'Track all 404 pages.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'javascript_error_track',
				'label' => __( 'JavaScript Errors', 'wp-analytify' ),
				'desc'  => __( 'Track all JavaScript errors.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'ajax_error_track',
				'label' => __( 'AJAX Errors', 'wp-analytify' ),
				'desc'  => __( 'Track all AJAX errors.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'linker_cross_domain_tracking',
				'label' => __( 'Setup Cross-domain Tracking', 'wp-analytify' ),
				// translators: %1$s is the code tag, %2$s is the opening link tag, %3$s is the closing link tag.
				'desc'  => sprintf( __( 'This will add the %1$s tag to your tracking code. Read this %2$sguide%3$s for more information.', 'wp-analytify' ), '<code>allowLinker:true</code>', '<a href=\'https:\/\/analytify.io/doc/setup-cross-domain-tracking-wordpress\' target=\'_blank\'>', '</a>' ),
				'type'  => 'checkbox',
				'class' => 'user_linker_tracking',
			),
			array(
				'name'              => 'linked_domain',
				'label'             => __( 'Domain', 'wp-analytify' ),
				'desc'              => __( 'All the linked domains separated by a comma', 'wp-analytify' ),
				'type'              => 'text',
				'class'             => 'linker_tracking',
				'sanitize_callback' => 'trim',
			),
			array(
				'name'    => 'enable_token_refresh_failure_email',
				'label'   => __( 'Reauthentication Email Alert', 'wp-analytify' ),
				'desc'    => __( 'Get an email notification when your site needs you to sign in again due to a Google error or revoked refresh token.', 'wp-analytify' ),
				'type'    => 'checkbox',
				'default' => 'off',
			),
			array(
				'name'  => 'uninstall_analytify_settings',
				'label' => __( 'Remove All Data On Uninstall', 'wp-analytify' ),
				'desc'  => __( 'Upon uninstall, this will remove all defined settings and their data from your website and reset them to default.', 'wp-analytify' ),
				'type'  => 'checkbox',
			),
			array(
				'name'  => 'custom_js_code',
				'label' => __( 'Custom JavaScript Code', 'wp-analytify' ),
				'desc'  => __( 'This will add inline tracking code before sending the pageview hit to Google Analytics.', 'wp-analytify' ),
				'type'  => 'textarea',
			),
			array(
				'name'  => 'custom_css_code',
				'label' => __( 'Custom CSS Code', 'wp-analytify' ),
				'desc'  => __( 'This will add inline css code in admin dashboard pages of Analytify', 'wp-analytify' ),
				'type'  => 'textarea',
			),
		);

		foreach ( $advance_setting_fields as $advance_setting_field ) {
			array_push( $settings_fields['wp-analytify-advanced'], $advance_setting_field );
		}

		// Filter for pro.
		$settings_fields = apply_filters( 'wp_analytify_pro_setting_fields', $settings_fields );
		return $settings_fields;
	}
}
