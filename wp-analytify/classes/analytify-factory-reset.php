<?php

/**
 * Factory Reset Class for Analytify Plugin.
 *
 * This class is responsible for deleting settings added by the Analytify Plugin.
 */
class Analytify_Factory_Reset {

	/**
	 * Array to store the names of settings to be deleted.
	 *
	 * @var array
	 */
	public array $settings;

	/**
	 * Constructor method for initializing the class.
	 */
	public function __construct() {
		$this->settings = $this->get_all_settings();
	}

	/**
	 * Retrieve an array of settings to be deleted.
	 *
	 * @return array An array of setting names.
	 */
	private function get_all_settings() {

		$settings = array(
			'wp_analytify_modules',
			'wp-analytify-tracking',
			'wp-analytify-email',
			'wp-analytify-front',
			'wp-analytify-events-tracking',
			'wp-analytify-custom-dimensions',
			'wp-analytify-forms',
			'analytify_widget_date_differ',
			'wp-analytify-profile',
			'wp-analytify-admin',
			'wp-analytify-dashboard',
			'wp-analytify-advanced',
			'analytify_ua_code',
			'analytify_date_differ',
			'wp_analytify_review_dismiss_4_1_8',
			'wpanalytify_settings',
			'analytify_license_key',
			'analytify_license_status',
			'analytify_campaigns_license_status',
			'analytify_campaigns_license_key',
			'analytify_goals_license_status',
			'analytify_goals_license_key',
			'analytify_forms_license_status',
			'analytify_forms_license_key',
			'analytify_authors_license_status',
			'analytify_authors_license_key',
			'analytify_woo_license_status',
			'analytify_woo_license_key',
			'analytify_email_license_status',
			'analytify_email_license_key',
			'analytify-google-ads-tracking',
			'_analytify_optin',
			'analytify_cache_timeout',
			'analytify_csv_data',
			'analytify_active_date',
			'analytify_edd_license_status',
			'analytify_edd_license_key',
			'_transient_timeout_analytify_api_addons',
			'_transient_analytify_api_addons',
			'analytify_ga4_exceptions',
			'analytify-ga-properties-summery',
			'analytify-ga4-streams',
			'analytify_tracking_property_info',
			'analytify_reporting_property_info',
			'analytify_gtag_move_to_notice',
			'analytify_current_version',
			'analytify_logs_setup',
			'analytify_pro_default_settings',
			'analytify_pro_active_date',
			'analytify_pro_upgrade_routine',
			'analytify_pro_current_version',
			'WP_ANALYTIFY_PRO_PLUGIN_VERSION',
			'wp-analytify-google-optimize',
			'wp-analytify-license',
			'analytify_authentication_date',
			'WP_ANALYTIFY_PLUGIN_VERSION_OLD',
			'WP_ANALYTIFY_PRO_PLUGIN_VERSION_OLD',
			'analytify_default_settings',
			'analytify_free_upgrade_routine',
			'WP_ANALYTIFY_PLUGIN_VERSION',
			'wp_analytify_active_time',
			'wp-analytify-authentication',
			'wp-analytify-help',
			'WP_ANALYTIFY_NEW_LOGIN',
			'profiles_list_summary',
			'pa_google_token',
			'post_analytics_token',
		);

		return $settings;

	}

	/**
	 * Remove the specified settings.
	 */
	public function remove_settings() {
		foreach ( $this->settings as $setting ) {
			delete_option( $setting );
		}
	}

}
