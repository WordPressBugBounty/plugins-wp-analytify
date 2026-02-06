<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable
/**
 * Profile Management Component for WP Analytify
 *
 * This file contains all profile settings and GA4 setup functions
 * that were previously in the main plugin file.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile Management Component Class
 */
class Analytify_Profile_Management {

	/**
	 * Main plugin instance
	 *
	 * @var WP_Analytify
	 */
	private $analytify;

	/**
	 * Constructor
	 *
	 * @version 7.0.5
	 * @param WP_Analytify $analytify Main plugin instance.
	 */
	public function __construct( $analytify ) {
		$this->analytify = $analytify;
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @return void
	 */
	private function init_hooks() {
		// Profile management hooks.
		add_action( 'update_option_wp-analytify-profile', array( $this, 'update_profiles_list_summary' ), 10, 2 );
		add_action( 'update_option_wp-analytify-advanced', array( $this, 'update_selected_profiles' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'update_profile_list_summary_on_update' ), 1 );
	}

	/**
	 * Update profiles list summary when profile option is updated
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @param mixed $old_value Old profile value.
	 * @param mixed $new_value New profile value.
	 * @return void
	 */
	public function update_profiles_list_summary( $old_value, $new_value ) {
		if ( isset( $new_value['profile_for_posts'] ) && ! empty( $new_value['profile_for_posts'] ) ) {
			$profile_id = $new_value['profile_for_posts'];

			// Get profile details and update summary.
			$profile_summary = $this->get_profile_summary( $profile_id );
			if ( $profile_summary ) {
				update_option( 'analytify_profile_summary', $profile_summary );
			}

			// Fetch GA4 streams for the selected property.
			$this->fetch_ga4_streams_for_property( $profile_id );
		}
	}

	/**
	 * Update selected profiles when advanced option is updated
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @param mixed $old_value Old advanced value.
	 * @param mixed $new_value New advanced value.
	 * @return void
	 */
	public function update_selected_profiles( $old_value, $new_value ) {
		if ( isset( $new_value['gtag_tracking_mode'] ) && 'gtag' === $new_value['gtag_tracking_mode'] ) {
			// Update tracking mode related settings.
			update_option( 'analytify_gtag_tracking_enabled', true );
		}

		// Update reporting property info when data stream is selected.
		if ( isset( $new_value['ga4_web_data_stream'] ) && ! empty( $new_value['ga4_web_data_stream'] ) ) {
			$this->update_reporting_property_info( $new_value['ga4_web_data_stream'] );
		}
	}

	/**
	 * Update profile list summary on plugin update
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @return void
	 */
	public function update_profile_list_summary_on_update() {
		$profile_id = get_option( 'pt_webprofile' );
		if ( $profile_id ) {
			$profile_summary = $this->get_profile_summary( $profile_id );
			if ( $profile_summary ) {
				update_option( 'analytify_profile_summary', $profile_summary );
			}
		}
	}

	/**
	 * Get profile summary information
	 *
	 * @version 7.0.5
	 * @param mixed $profile_id Profile ID.
	 * @return mixed
	 */
	private function get_profile_summary( $profile_id ) {
		// This would contain logic to get profile summary.
		// For now, return a basic structure.
		return array(
			'profile_id' => $profile_id,
			'updated_at' => current_time( 'mysql' ),
		);
	}

	/**
	 * Fetch GA4 streams for a specific property
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @param string $profile_id The profile ID (format: "property:property_id").
	 * @return void
	 */
	private function fetch_ga4_streams_for_property( $profile_id ) {
		// Extract property ID from profile format (property:property_id).
		$property_id = explode( ':', $profile_id )[1] ?? false;

		if ( ! $property_id ) {
			return;
		}

		// Check if we already have streams for this property.
		$existing_streams = get_option( 'analytify-ga4-streams', array() );
		if ( isset( $existing_streams[ $property_id ] ) && ! empty( $existing_streams[ $property_id ] ) ) {
			return;
		}

		// Initialize GA4 core to fetch streams.
		if ( isset( $GLOBALS['WP_ANALYTIFY'] ) && method_exists( $GLOBALS['WP_ANALYTIFY'], 'analytify_get_ga_streams' ) ) {
			$streams = $GLOBALS['WP_ANALYTIFY']->analytify_get_ga_streams( $property_id );

			if ( ! empty( $streams ) ) {
				// Store streams data.
				$existing_streams[ $property_id ] = $streams;
				update_option( 'analytify-ga4-streams', $existing_streams );
			}
		}
	}

	/**
	 * Update reporting property info when data stream is selected
	 *
	 * @since 7.0.5
	 * @version 7.0.5
	 * @param string $stream_id The selected stream ID.
	 * @return void
	 */
	private function update_reporting_property_info( $stream_id ) {
		// Get the current profile to find the property ID.
		$profile      = get_option( 'wp-analytify-profile' );
		$post_profile = isset( $profile['profile_for_posts'] ) ? $profile['profile_for_posts'] : '';
		$property_id  = explode( ':', $post_profile )[1] ?? false;

		if ( ! $property_id ) {
			return;
		}

		// Get streams data for this property.
		$streams_data = get_option( 'analytify-ga4-streams', array() );
		if ( ! isset( $streams_data[ $property_id ] ) || empty( $streams_data[ $property_id ] ) ) {
			return;
		}

		// Find the selected stream in the streams data.
		$selected_stream = null;
		foreach ( $streams_data[ $property_id ] as $stream ) {
			if ( isset( $stream['measurement_id'] ) && $stream['measurement_id'] === $stream_id ) {
				$selected_stream = $stream;
				break;
			}
		}

		if ( ! $selected_stream ) {
			return;
		}

		// Extract stream name from various possible fields.
		$stream_name = 'Unknown Stream';
		if ( isset( $selected_stream['stream_name'] ) && ! empty( $selected_stream['stream_name'] ) ) {
			$stream_name = $selected_stream['stream_name'];
		} elseif ( isset( $selected_stream['display_name'] ) && ! empty( $selected_stream['display_name'] ) ) {
			$stream_name = $selected_stream['display_name'];
		} elseif ( isset( $selected_stream['name'] ) && ! empty( $selected_stream['name'] ) ) {
			$stream_name = $selected_stream['name'];
		} elseif ( isset( $selected_stream['web_stream_data']['display_name'] ) && ! empty( $selected_stream['web_stream_data']['display_name'] ) ) {
			$stream_name = $selected_stream['web_stream_data']['display_name'];
		} else {
			// Fallback: use measurement ID or stream ID as name.
			$stream_name = $stream_id;
		}

		// Extract URL from various possible fields.
		$stream_url = '';
		if ( isset( $selected_stream['web_stream_data']['default_uri'] ) && ! empty( $selected_stream['web_stream_data']['default_uri'] ) ) {
			$stream_url = $selected_stream['web_stream_data']['default_uri'];
		} elseif ( isset( $selected_stream['url'] ) && ! empty( $selected_stream['url'] ) ) {
			$stream_url = $selected_stream['url'];
		} elseif ( isset( $selected_stream['web_stream_data']['site_url'] ) && ! empty( $selected_stream['web_stream_data']['site_url'] ) ) {
			$stream_url = $selected_stream['web_stream_data']['site_url'];
		}

		// Prepare reporting property info.
		$reporting_info = array(
			'property_id'    => $property_id,
			'stream_name'    => $stream_name,
			'measurement_id' => $stream_id,
			'url'            => $stream_url,
			'full_name'      => isset( $selected_stream['full_name'] ) ? $selected_stream['full_name'] : '',
		);

		// Update the reporting property info.
		update_option( 'analytify_reporting_property_info', $reporting_info );

		// Also update tracking property info for Search Console integration.
		update_option( 'analytify_tracking_property_info', $reporting_info );

		// Fetch and update measurement protocol secret for the selected stream.
		if ( ! empty( $reporting_info['full_name'] ) && isset( $GLOBALS['WP_ANALYTIFY'] ) && method_exists( $GLOBALS['WP_ANALYTIFY'], 'analytify_get_mp_secret' ) ) {
			$mp_secret_data = $GLOBALS['WP_ANALYTIFY']->analytify_get_mp_secret( $reporting_info['full_name'] );
			if ( ! empty( $mp_secret_data ) && isset( $mp_secret_data['secretValue'] ) ) {
				WPANALYTIFY_Utils::update_option( 'measurement_protocol_secret', 'wp-analytify-advanced', $mp_secret_data['secretValue'] );
			} elseif ( method_exists( $GLOBALS['WP_ANALYTIFY'], 'analytify_create_mp_secret' ) ) {
				// If no secret exists, try to create one.
				$created_secret = $GLOBALS['WP_ANALYTIFY']->analytify_create_mp_secret( $property_id, $reporting_info['full_name'], $stream_id );
				if ( ! empty( $created_secret ) && isset( $created_secret['secretValue'] ) ) {
					WPANALYTIFY_Utils::update_option( 'measurement_protocol_secret', 'wp-analytify-advanced', $created_secret['secretValue'] );
				}
			}
		}
	}
}
