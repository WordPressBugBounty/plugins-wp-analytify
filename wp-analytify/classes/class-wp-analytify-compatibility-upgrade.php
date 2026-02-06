<?php // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- Mixed structure is acceptable for this type of file
/**
 * Analytify Compatibility Upgrade Class
 *
 * This class handles compatibility upgrades for older versions of Analytify before 2.0,
 * ensuring smooth migration of settings and data structures.
 *
 * @package WP_Analytify
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Compatibility for older versions for Analytify before 2.0
 *
 * @since 2.0
 */
class WP_Analytify_Compatibility_Upgrade {

	/**
	 * Profile settings array.
	 *
	 * @var array<string, mixed>
	 */
	protected $profile_settings = array();
	/**
	 * Admin settings array.
	 *
	 * @var array<string, mixed>
	 */
	protected $admin_settings = array();
	/**
	 * Advanced settings array.
	 *
	 * @var array<string, mixed>
	 */
	protected $advanced_settings = array();
	/**
	 * Dashboard settings array.
	 *
	 * @var array<string, mixed>
	 */
	protected $dashboard_settings = array();


	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->upgrade_routine();
	}

	/**
	 * Run the upgrade routine.
	 *
	 * @return void
	 */
	public function upgrade_routine() {
		$this->profile_settings();
		$this->admin_settings();
		$this->advanced_settings();
		$this->dashboard_settings();
	}

	/**
	 * Upgrade profile settings.
	 *
	 * @return void
	 */
	public function profile_settings() {

		if ( get_option( 'pt_webprofile' ) ) {

			$this->profile_settings['profile_for_posts'] = get_option( 'pt_webprofile' );
			delete_option( 'pt_webprofile' );
		}

		if ( get_option( 'pt_webprofile_dashboard' ) ) {

			$this->profile_settings['profile_for_dashboard'] = get_option( 'pt_webprofile_dashboard' );
			delete_option( 'pt_webprofile_dashboard' );
		}

		if ( '1' === get_option( 'analytify_code' ) ) {

			$this->profile_settings['install_ga_code'] = 'on';
			delete_option( 'analytify_code' );
		} else {
			delete_option( 'analytify_code' );
		}

		if ( get_option( 'display_tracking_code' ) ) {

			$this->profile_settings['exclude_users_tracking'] = get_option( 'display_tracking_code' );
			delete_option( 'display_tracking_code' );
		}

		if ( ! empty( $this->profile_settings ) ) {
			update_option( 'wp-analytify-profile', $this->profile_settings );
		}
	}

	/**
	 * Upgrade admin settings.
	 *
	 * @return void
	 */
	public function admin_settings() {

		if ( '1' === get_option( 'post_analytics_disable_back' ) ) {

			$this->admin_settings['enable_back_end'] = 'on';
			delete_option( 'post_analytics_disable_back' );
		} else {
			delete_option( 'post_analytics_disable_back' );
		}

		if ( get_option( 'post_analytics_access_back' ) ) {

			$this->admin_settings['show_analytics_roles_back_end'] = get_option( 'post_analytics_access_back' );
			delete_option( 'post_analytics_access_back' );
		}

		if ( get_option( 'analytify_posts_stats' ) ) {

			$this->admin_settings['show_analytics_post_types_back_end'] = get_option( 'analytify_posts_stats' );
			delete_option( 'analytify_posts_stats' );
		}

		if ( get_option( 'post_analytics_settings_back' ) ) {

			$this->admin_settings['show_panels_back_end'] = get_option( 'post_analytics_settings_back' );
			delete_option( 'post_analytics_settings_back' );
		}

		if ( get_option( 'post_analytics_exclude_posts_back' ) ) {

			$this->admin_settings['exclude_pages_back_end'] = get_option( 'post_analytics_exclude_posts_back' );
			delete_option( 'post_analytics_exclude_posts_back' );
		}

		if ( ! empty( $this->admin_settings ) ) {
			update_option( 'wp-analytify-admin', $this->admin_settings );
		}
	}

	/**
	 * Upgrade advanced settings.
	 *
	 * @return void
	 */
	public function advanced_settings() {

		if ( get_option( 'post_analytics_exclude_posts' ) ) {

			$this->advanced_settings['exclude_pages_front_end'] = get_option( 'post_analytics_exclude_posts' );
			delete_option( 'post_analytics_exclude_posts' );
		}

		if ( get_option( 'post_analytics_exclude_categories' ) ) {

			$this->advanced_settings['exclude_categories_front_end'] = get_option( 'post_analytics_exclude_categories' );
			delete_option( 'post_analytics_exclude_categories' );
		}

		if ( get_option( 'post_analytics_exclude_tags' ) ) {

			$this->advanced_settings['exclude_tags_front_end'] = get_option( 'post_analytics_exclude_tags' );
			delete_option( 'post_analytics_exclude_tags' );
		}

		if ( get_option( 'post_analytics_exclude_custom_post_types' ) ) {

			$this->advanced_settings['exclude_custom_post_types_front_end'] = get_option( 'post_analytics_exclude_custom_post_types' );
			delete_option( 'post_analytics_exclude_custom_post_types' );
		}

		if ( get_option( 'post_analytics_exclude_roles' ) ) {

			$this->advanced_settings['exclude_roles_front_end'] = get_option( 'post_analytics_exclude_roles' );
			delete_option( 'post_analytics_exclude_roles' );
		}

		if ( get_option( 'post_analytics_exclude_ips' ) ) {

			$this->advanced_settings['exclude_ips_front_end'] = get_option( 'post_analytics_exclude_ips' );
			delete_option( 'post_analytics_exclude_ips' );
		}

		if ( get_option( 'post_analytics_exclude_domains' ) ) {

			$this->advanced_settings['exclude_domains_front_end'] = get_option( 'post_analytics_exclude_domains' );
			delete_option( 'post_analytics_exclude_domains' );
		}

		if ( get_option( 'post_analytics_exclude_terms' ) ) {

			$this->advanced_settings['exclude_terms_front_end'] = get_option( 'post_analytics_exclude_terms' );
			delete_option( 'post_analytics_exclude_terms' );
		}

		if ( get_option( 'post_analytics_exclude_author' ) ) {

			$this->advanced_settings['exclude_author_front_end'] = get_option( 'post_analytics_exclude_author' );
			delete_option( 'post_analytics_exclude_author' );
		}

		if ( get_option( 'post_analytics_exclude_date' ) ) {

			$this->advanced_settings['exclude_date_front_end'] = get_option( 'post_analytics_exclude_date' );
			delete_option( 'post_analytics_exclude_date' );
		}

		if ( get_option( 'post_analytics_exclude_meta' ) ) {

			$this->advanced_settings['exclude_meta_front_end'] = get_option( 'post_analytics_exclude_meta' );
			delete_option( 'post_analytics_exclude_meta' );
		}

		if ( get_option( 'post_analytics_exclude_taxonomies' ) ) {

			$this->advanced_settings['exclude_taxonomies_front_end'] = get_option( 'post_analytics_exclude_taxonomies' );
			delete_option( 'post_analytics_exclude_taxonomies' );
		}

		if ( get_option( 'post_analytics_exclude_roles_back' ) ) {

			$this->advanced_settings['exclude_roles_back_end'] = get_option( 'post_analytics_exclude_roles_back' );
			delete_option( 'post_analytics_exclude_roles_back' );
		}

		if ( get_option( 'post_analytics_exclude_ips_back' ) ) {

			$this->advanced_settings['exclude_ips_back_end'] = get_option( 'post_analytics_exclude_ips_back' );
			delete_option( 'post_analytics_exclude_ips_back' );
		}

		if ( get_option( 'post_analytics_exclude_domains_back' ) ) {

			$this->advanced_settings['exclude_domains_back_end'] = get_option( 'post_analytics_exclude_domains_back' );
			delete_option( 'post_analytics_exclude_domains_back' );
		}

		if ( get_option( 'post_analytics_exclude_terms_back' ) ) {

			$this->advanced_settings['exclude_terms_back_end'] = get_option( 'post_analytics_exclude_terms_back' );
			delete_option( 'post_analytics_exclude_terms_back' );
		}

		if ( get_option( 'post_analytics_exclude_author_back' ) ) {

			$this->advanced_settings['exclude_author_back_end'] = get_option( 'post_analytics_exclude_author_back' );
			delete_option( 'post_analytics_exclude_author_back' );
		}

		if ( get_option( 'post_analytics_exclude_date_back' ) ) {

			$this->advanced_settings['exclude_date_back_end'] = get_option( 'post_analytics_exclude_date_back' );
			delete_option( 'post_analytics_exclude_date_back' );
		}

		if ( get_option( 'post_analytics_exclude_meta_back' ) ) {

			$this->advanced_settings['exclude_meta_back_end'] = get_option( 'post_analytics_exclude_meta_back' );
			delete_option( 'post_analytics_exclude_meta_back' );
		}

		if ( get_option( 'post_analytics_exclude_taxonomies_back' ) ) {

			$this->advanced_settings['exclude_taxonomies_back_end'] = get_option( 'post_analytics_exclude_taxonomies_back' );
			delete_option( 'post_analytics_exclude_taxonomies_back' );
		}

		if ( ! empty( $this->advanced_settings ) ) {
			update_option( 'wp-analytify-advanced', $this->advanced_settings );
		}
	}

	/**
	 * Upgrade dashboard settings.
	 *
	 * @return void
	 */
	public function dashboard_settings() {

		$this->dashboard_settings['show_analytics_panels_dashboard'] = array(
			'show-real-time',
			'show-page-speed',
			'show-compare-stats',
			'show-overall-dashboard',
			'show-top-pages-dashboard',
			'show-geographic-dashboard',
			'show-system-stats',
			'show-keywords-dashboard',
			'show-social-dashboard',
			'show-referrer-dashboard',
			'show-page-stats-dashboard',
		);

		$this->dashboard_settings['show_analytics_roles_dashboard'] = array(
			'administrator',
		);
		update_option( 'wp-analytify-dashboard', $this->dashboard_settings );
	}
}

if ( ! get_option( 'analytify_free_upgrade_routine' ) ) {

	$WP_Analytify_Compatibility_Upgrade = new WP_Analytify_Compatibility_Upgrade(); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase -- Variable name is acceptable for this context
	update_option( 'analytify_free_upgrade_routine', 'done' );
}

/**
 * Register default modules.
 *
 * @return void
 */
function analytify_register_modules() { // phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed -- Mixed structure is acceptable for this type of file
	$default_modules = array(
		'events-tracking'     => array(
			'status'      => 'active',
			'slug'        => 'events-tracking',
			'page_slug'   => 'analytify-events',
			'title'       => __( 'Events Tracking', 'wp-analytify' ),
			'description' => __( 'This Add-on will track custom events in a unique and intuitive way which is very understandable even for non-technical WordPress users.', 'wp-analytify' ),
			'image'       => ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . 'assets/img/analytify-events-tracking.svg',
			'url'         => 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=addons&utm_campaign=pro-upgrade&utm_content=Events+Tracking',
		),
		'custom-dimensions'   => array(
			'status'      => 'active',
			'slug'        => 'custom-dimensions',
			'page_slug'   => 'analytify-dimensions',
			'title'       => __( 'Custom Dimensions', 'wp-analytify' ),
			'description' => __( 'With the Custom Dimensions addon you can view data which can be segmented and organized according to your businesses.', 'wp-analytify' ),
			'image'       => ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . 'assets/img/analytify-custom-dimensions.svg',
			'url'         => 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=addons&utm_campaign=pro-upgrade&utm_content=Custom+Dimensions',
		),
		'amp'                 => array(
			'status'      => false,
			'slug'        => 'amp',
			'page_slug'   => 'analytify-amp',
			'title'       => __( 'AMP', 'wp-analytify' ),
			'description' => __( 'Analytify\'s AMP Addon will enable accurate reporting and tracking of mobile visitors to your AMP pages.', 'wp-analytify' ),
			'image'       => ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . 'assets/img/analytify-google-amp.svg',
			'url'         => 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=addons&utm_campaign=pro-upgrade&utm_content=AMP',
		),
		'google-ads-tracking' => array(
			'status'      => false,
			'slug'        => 'google-ads-tracking',
			'page_slug'   => 'analytify-ads-tracking',
			'title'       => __( 'Google Ads Tracking', 'wp-analytify' ),
			'description' => __( 'This Addon Tracks Google Ads Conversions for Woocommerce and EDD.', 'wp-analytify' ),
			'image'       => ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . 'assets/img/google-ads-logo.png',
			'url'         => 'https://analytify.io/pricing?utm_source=analytify-lite&utm_medium=addons&utm_campaign=pro-upgrade&utm_content=Google+Ads',
		),
	);

	$analytify_modules = (array) get_option( 'wp_analytify_modules', array() );

	// Check if 'google-ads-tracking' is not in the array and the option does not exist.
	if ( ! array_key_exists( 'google-ads-tracking', $analytify_modules ) ) {
		$analytify_modules['google-ads-tracking'] = $default_modules['google-ads-tracking'];
	}

	// Merge default modules with existing ones, preserving existing settings.
	$analytify_modules = array_merge( $default_modules, $analytify_modules );

	update_option( 'wp_analytify_modules', $analytify_modules );

	// Backward compatibility support added.
	$analytify_admin_options = get_option( 'wp-analytify-admin' );

	if ( $analytify_admin_options && empty( $analytify_admin_options['enable_back_end'] ) ) {
		$analytify_admin_options['enable_back_end'] = 'on';
		unset( $analytify_admin_options['disable_back_end'] );
		update_option( 'wp-analytify-admin', $analytify_admin_options );
	}
}

add_action( 'wp_loaded', 'analytify_register_modules' );
