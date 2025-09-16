<?php
/**
 * 
 * General Class used to set the base of the plugin.
 * It holds the analytics wrappers and sdk calls to fetch the data from Google.
 *
 * 
 * @since 1.0.0
 *
 *  @package WP_Analytify
 */

// Global variables.
define( 'ANALYTIFY_LIB_PATH', dirname( __FILE__ ) . '/lib/' );
define( 'ANALYTIFY_ID', 'wp-analytify-options' );
define( 'ANALYTIFY_NICK', 'Analytify' );
define( 'ANALYTIFY_ROOT_PATH', dirname( __FILE__ ) );
define( 'ANALYTIFY_VERSION', '7.0.4' );
define( 'ANALYTIFY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANALYTIFY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'ANALYTIFY_LOCAL_DIR', WP_CONTENT_DIR . apply_filters( 'analytify_dir_to_host_analytics', '/uploads/analytify/' ) );

// Grab client ID and client secret from https://console.developers.google.com/ after creating a project.
if ( get_option( 'wpa_current_version' ) ) { // Pro Keys
	define( 'ANALYTIFY_CLIENTID', '707435375568-9lria1uirhitcit2bhfg0rgbi19smjhg.apps.googleusercontent.com' );
	define( 'ANALYTIFY_CLIENTSECRET', 'b9C77PiPSEvrJvCu_a3dzXoJ' );
} else { // Free Keys
	define( 'ANALYTIFY_CLIENTID', '958799092305-7p6jlsnmv1dn44a03ma00kmdrau2i31q.apps.googleusercontent.com' );
	define( 'ANALYTIFY_CLIENTSECRET', 'Mzs1ODgJTpjk8mzQ3mbrypD3' );
}

/**
 * Sample options to flush the variables if AUTH api is not working
 * Routine in progress
 */
//var_dump(get_option( 'pa_google_token' ));
//var_dump(get_option( 'post_analytics_token' ));
// delete_option( 'pa_google_token' );
// delete_option( 'analytify-ga-properties-summery' );
// delete_option( 'post_analytics_token' );
//delete_option( 'analytify_authentication_date' );


// Basic read & write scope.
define( 'ANALYTIFY_SCOPE', 'https://www.googleapis.com/auth/analytics.readonly https://www.googleapis.com/auth/analytics.edit' );
// Full read & write and extra.
define( 'ANALYTIFY_SCOPE_FULL', 'https://www.googleapis.com/auth/analytics.readonly https://www.googleapis.com/auth/analytics https://www.googleapis.com/auth/analytics.edit https://www.googleapis.com/auth/webmasters' );
define( 'ANALYTIFY_AUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth?');

define( 'ANALYTIFY_REDIRECT', 'https://analytify.io/api/' );
define( 'ANALYTIFY_DEV_KEY', 'AIzaSyDXjBezSlaVMPk8OEi8Vw5aFvteouXHZpI' );
define( 'ANALYTIFY_STORE_URL', 'https://analytify.io' );
define( 'ANALYTIFY_PRODUCT_NAME', 'Analytify WordPress Plugin' );

// Google Analytics Admin API base URL
define( 'ANALYTIFY_GA_ADMIN_API_BASE', 'https://analyticsadmin.googleapis.com/v1alpha' );

include_once ANALYTIFY_PLUGIN_DIR . '/classes/analytify-settings.php';
include_once ANALYTIFY_PLUGIN_DIR . '/classes/analytify-utils.php';
include_once ANALYTIFY_PLUGIN_DIR . '/classes/analytify-mp-ga4.php';
include_once ANALYTIFY_PLUGIN_DIR . '/classes/analytify-sanitize.php';
include_once ANALYTIFY_PLUGIN_DIR . '/classes/analytify-update-routine.php';


if ( ! class_exists( 'Analytify_General' ) ) {

	/**
	 * Analytify_General Class for Analytify.
	 */
	class Analytify_General {

		public $settings;
		public $service;
		public $client;
		public $token;

		protected $state_data;
		protected $transient_timeout;
		protected $load_settings;
		protected $plugin_base;
		protected $plugin_settings_base;
		protected $cache_timeout;

		private $exception;

		// exceptions for ga4
		private $ga4_exception;
		private $modules;
		protected $is_reporting_in_ga4;

		// User added client id.
		private $user_client_id;
		
		// User added client secret.
		private $user_client_secret;
		
		// Authentication date format
		private $auth_date_format;
		
		// Google token data
		private $google_token;
		
		// GA4 streams data
		private $ga4_streams;

		/**
		 * Constructor of analytify-general class.
		 */
		public function __construct() {
			$this->transient_timeout    = 60 * 60 * 12;
			$this->plugin_base          = 'admin.php?page=analytify-dashboard';
			$this->plugin_settings_base = 'admin.php?page=analytify-settings';
			$this->auth_date_format     = date('l jS F Y h:i:s A') . ' ' . date_default_timezone_get();
			if (isset($_GET['page']) && strpos($_GET['page'], 'analytify-settings') === 0) {
			$this->exception            = get_option( 'analytify_profile_exception' );
			$this->ga4_exception        = get_option( 'analytify_ga4_exceptions' );
				}
			$this->modules				= WPANALYTIFY_Utils::get_pro_modules();
			// Setup Settings.
			$this->settings = new WP_Analytify_Settings();
			
			$this->is_reporting_in_ga4  = 'ga4' === WPANALYTIFY_Utils::get_ga_mode() ? true : false;

			if ( $this->is_reporting_in_ga4 === true ) {
				// Rankmath Instant Indexing addon Compatibility.
				if( ( isset( $_GET['page'] ) && $_GET['page'] == 'instant-indexing' ) || strpos( wp_get_referer(), 'instant-indexing' ) !== false ){
					return;
				}

			if ( $this->settings->get_option( 'user_advanced_keys', 'wp-analytify-advanced', '' ) == 'on' ) {
				$this->user_client_id     = $this->settings->get_option( 'client_id' ,'wp-analytify-advanced' );
				$this->user_client_secret = $this->settings->get_option( 'client_secret' ,'wp-analytify-advanced' );
				//TODO
				// $this->client->setClientId( $this->user_client_id );
				// $this->client->setClientSecret( $this->user_client_secret );
				// $this->client->setRedirectUri( $this->settings->get_option( 'redirect_uri', 'wp-analytify-advanced' ) );
			}

			// $this->client->setScopes( ANALYTIFY_SCOPE );

			if ( $this->is_reporting_in_ga4 === true ) {

				try {
					// $this->service = new Google\Service\Analytics( $this->client );
					$this->analytify_pa_connect_v2();
				} catch ( Exception $e ) {
					// Show error message only for logged in users.
					if ( current_user_can( 'manage_options' ) ) {
						// translators: Error message for logged in users

						echo sprintf( esc_html__( '%1$s Oops, Something went wrong. %2$s %5$s %2$s %3$s Don\'t worry, This error message is only visible to Administrators. %4$s %2$s ', 'wp-analytify' ), '<br /><br />', '<br />', '<i>', '</i>', esc_textarea( $e->getMessage() ) );
					}
				} catch ( Exception $e ) {
					// Show error message only for logged in users.
					if ( current_user_can( 'manage_options' ) ) {
						// translators: Reset authentication error message
						echo sprintf( esc_html__( '%1$s Oops, Try to %2$s Reset %3$s Authentication. %4$s %7$s %4$s %5$s Don\'t worry, This error message is only visible to Administrators. %6$s %4$s', 'wp-analytify' ), '<br /><br />', '<a href=' . esc_url( admin_url( 'admin.php?page=analytify-settings&tab=authentication' ) ) . 'title="Reset">', '</a>', '<br />', '<i>', '</i>', esc_textarea( $e->getMessage() ) );
					}
				}

			}

			add_action( 'after_setup_theme', array( $this, 'set_cache_time' ) );

			$this->analytify_set_tracking_mode();
			
		}
	}
		/**
		 * Update authentication date with current timestamp.
		 *
		 * @since 7.0.0
		 */
		private function analytify_update_authentication_date() {
			$this->auth_date_format = date('l jS F Y h:i:s A') . ' ' . date_default_timezone_get();
			update_option('analytify_authentication_date', $this->auth_date_format);
		}

		/**
		 * Get Google token data from options.
		 *
		 * @since 7.0.0
		 * @return array|false Token data or false if not found
		 */
		private function analytify_get_google_token() {
			if (empty($this->google_token)) {
				$this->google_token = get_option('pa_google_token');
			}
			return $this->google_token;
		}

		/**
		 * Update Google token data in options and class variable.
		 *
		 * @since 7.0.0
		 * @param array $token_data Token data to save
		 */
		private function analytify_update_google_token($token_data) {
			$this->google_token = $token_data;
			update_option('pa_google_token', $token_data);
		}

		/**
		 * Get GA4 streams data from options.
		 *
		 * @since 7.0.0
		 * @return array GA4 streams data
		 */
		private function analytify_get_ga4_streams() {
			if (empty($this->ga4_streams)) {
				$this->ga4_streams = get_option('analytify-ga4-streams', array());
			}
			return $this->ga4_streams;
		}

		/**
		 * Check the tracking method.
		 *
		 * @return string ga/gtag
		 */
		public function analytify_set_tracking_mode() {
			if ( ! defined( 'ANALYTIFY_TRACKING_MODE' ) ) {
				define( 'ANALYTIFY_TRACKING_MODE', $this->settings->get_option( 'gtag_tracking_mode', 'wp-analytify-advanced', 'gtag' ) );
			}
		}

		/**
		 * Connect with Google Analytics API and get authentication token and save it.
		 *
		 * @since 6.0.0
		 * @version 7.0.0
		 *
		 * @return void
		 */
		public function analytify_pa_connect_v2() {
			
			// Retrieve the stored token data
			$token_data = $this->analytify_get_google_token();
			$auth_code = get_option('post_analytics_token');
			$refresh_token = isset($token_data['refresh_token']) ? $token_data['refresh_token'] : null;
			$expires_in = isset($token_data['expires_in']) ? $token_data['expires_in'] : 0;
			$token_time = isset($token_data['created_at']) ? $token_data['created_at'] : 0;

			// Check if the access token is still valid
			if (!empty($token_data) && (time() - $token_time) < $expires_in) {
				// Return the valid token
				return $token_data['access_token'];
			}
		
			// If the access token is expired, check if a refresh token is available
			if (isset($refresh_token)) {
				$access_token_data = $this->analytify_refresh_access_token($refresh_token);
				if ($access_token_data) {
					$this->token = $access_token_data['access_token'];
					return $access_token_data['access_token']; // Return the refreshed token
				} else {
					return false;
				}
			}
		
			// Fallback: Get a new token using the authorization code
			if (empty($auth_code)) {
				error_log('Error: Authorization code is empty.');
				return false;
			}
		
			try {
		
				$token_uri = 'https://oauth2.googleapis.com/token'; // Google token endpoint
				$token_data = array(
					'client_id' => ANALYTIFY_CLIENTID,
					'client_secret' => ANALYTIFY_CLIENTSECRET,
					'code' => $auth_code,
					'redirect_uri' => ANALYTIFY_REDIRECT,
					'grant_type' => 'authorization_code',
					'access_type' => 'offline',
				);
		
				$request_args = array(
					'body' => $token_data,
					'headers' => array('Referer' => ANALYTIFY_VERSION),
				);
		
				// Make POST request
				$response = wp_remote_post($token_uri, $request_args);
		
				// Check for errors in the response
				if (is_wp_error($response)) {
					error_log('Error: Failed to send token request.');
					return false;
				}
		
				// Retrieve response body
				$body = wp_remote_retrieve_body($response);
				$access_token_data = json_decode($body, true);

							// Check if the access token is present and valid
				if (isset($access_token_data['access_token'])) {
					$access_token = $access_token_data['access_token'];
			
					// Save the new token data along with the current time for token expiration checks
					$access_token_data['created_at'] = time();
					$this->analytify_update_google_token($access_token_data);
					$this->analytify_update_authentication_date();
			
					// Optionally store the token in the object if needed later
					$this->token = $access_token;
			
					// Return the new access token
					return $access_token;
				} else {
					error_log('Error: Access token not found in response.');
					return false;
				}
		
			} catch (Exception $e) {
				// Log the error instead of printing it to the screen
				error_log('Analytify (Error): ' . $e->getMessage());
				return false;
			}
		

		}
		

		/**
		 * Refreshes the access token using the provided refresh token.
		 *
		 * This function is responsible for obtaining a new access token
		 * by using the given refresh token. It is typically used when the
		 * current access token has expired and needs to be renewed.
		 * 
		 * @version 7.0.0
		 * 
		 * @param string $refresh_token The refresh token used to obtain a new access token.
		 * @return mixed The new access token or an error response if the refresh fails.
		 */
		public function analytify_refresh_access_token($refresh_token) {
		
			$token_uri = 'https://oauth2.googleapis.com/token';
			$token_request_data = array(
				'client_id' => ANALYTIFY_CLIENTID,
				'client_secret' => ANALYTIFY_CLIENTSECRET,
				'refresh_token' => $refresh_token,
				'grant_type' => 'refresh_token',
			);
		
			$request_args = array(
				'body' => $token_request_data,
				'headers' => array('Referer' => ANALYTIFY_VERSION),
			);
		
			$response = wp_remote_post($token_uri, $request_args);
		
			if (is_wp_error($response)) {
				error_log('Error: Failed to refresh access token.');
				return false;
			}
		
			$body = wp_remote_retrieve_body($response);
			$access_token_data = json_decode($body, true);
		
			if (isset($access_token_data['access_token'])) {
				error_log('New access token obtained via refresh token.');
			
				// Fetch the existing token data
				$existing_token_data = $this->analytify_get_google_token() ?: [];
			
				// Update the existing data with the new access token and timestamp, excluding refresh_token
				$updated_token_data = array_merge($existing_token_data, [
					'access_token' => $access_token_data['access_token'],
					'expires_in' => $access_token_data['expires_in'],
					'created_at' => time(),
				]);
			
				// Save the updated token data
				$this->analytify_update_google_token($updated_token_data);
			
				// Update authentication date
				$this->analytify_update_authentication_date();
			
				return $updated_token_data;
			} else {
				error_log('Error: Failed to retrieve new access token using refresh token.');
				return false;
			}
		}
		

		/**
		 * Connect with Google Analytics admin API.
		 * 
		 * @return AnalyticsAdminServiceClient
		 * @version 7.0.1
		 */
		private function analytify_connect_admin_api() {
		
			try {
				// Get a fresh access token using the refresh token.
				$token = $this->analytify_get_google_token();
				
				// Validate that token is an array and has the expected structure.
				if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
					error_log('Error: Invalid or missing Google Analytics token in analytify_list_ga4_web_properties.');
					return array();
				}
				
				$access_token = $token['access_token'];

		
				// Set the headers for the API request
				$headers = [
					"Authorization: Bearer $access_token",
					"Content-Type: application/json"
				];
		
				// Define the base API URL for Google Analytics Admin API
				$api_base_url = ANALYTIFY_GA_ADMIN_API_BASE;
		
		
				return [
					'api_base_url' => $api_base_url,
					'headers' => $headers,
				];
			} catch (Exception $e) {
				// Log the error message for debugging purposes
				error_log('Error connecting to Google Analytics Admin API: ' . $e->getMessage());
				return null;
			}
		}
		

		/**
		 * Create web stream for Analytify tracking in Google Analytics.
		 * Stream types: Google\Analytics\Admin\V1alpha\DataStream\DataStreamType
		 *
		 * @param string $property_id
		 * 
		 * @return array Measurement data.
		 * 
		 * @since 5.0.0
		 * @version 7.0.1
		 */
		public function analytify_create_ga_stream( $property_id ) {
			$analytify_ga4_streams = $this->analytify_get_ga4_streams();
		
			// Check if the stream already exists in the saved option
			if ( isset( $analytify_ga4_streams ) && isset( $analytify_ga4_streams[$property_id] ) && isset( $analytify_ga4_streams[$property_id]['measurement_id'] ) ) {
				return $analytify_ga4_streams[$property_id];
			}
		
			// Return if there is no property id given.
			if ( empty( $property_id ) ) {
				return;
			}

			$token = $this->analytify_get_google_token();
			
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				error_log('Error: Invalid or missing Google Analytics token in analytify_create_ga_stream.');
				return;
			}

			$access_token = $token['access_token']; // Method to retrieve your OAuth access token
			$url_list_streams = ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/dataStreams';
			$stream_name = 'Analytify - ' . get_site_url(); // Defined stream name for Analytify.
		
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			);
		
			$measurement_data = array();
		
			// Try to fetch existing data streams
			$response = wp_remote_get( $url_list_streams, $args );
		
			// Log the response for debugging
			$logger = analytify_get_logger();
			$logger->info( 'Fetching existing streams.', array( 'response' => $response ) );
		
			if ( is_wp_error( $response ) ) {
				$logger->error( 'Error fetching streams.', array( 'error_message' => $response->get_error_message(), 'source' => 'analytify_create_stream_errors' ) );
				return;
			}
		
			$body = wp_remote_retrieve_body( $response );
			$decoded_response = json_decode( $body, true );
			
			// Log the decoded response for debugging
			$logger->info( 'Decoded response from GA.', array( 'decoded_response' => $decoded_response ) );
		
			// Check if any existing streams match the Analytify stream
			if ( isset( $decoded_response['dataStreams'] ) ) {
				foreach ( $decoded_response['dataStreams'] as $stream ) {
					if ( isset( $stream['displayName'] ) && $stream_name === $stream['displayName'] ) {
						$web_stream = $stream;
		
						// Check if all required nested array elements exist
						if ( isset( $web_stream['name'] ) && 
							 isset( $web_stream['displayName'] ) && 
							 isset( $web_stream['webStreamData']['measurementId'] ) && 
							 isset( $web_stream['webStreamData']['defaultUri'] ) ) {
							
							$measurement_data = array(
								'full_name'      => $web_stream['name'],
								'property_id'    => $property_id,
								'stream_name'    => $web_stream['displayName'],
								'measurement_id' => $web_stream['webStreamData']['measurementId'],
								'url'            => $web_stream['webStreamData']['defaultUri'],
							);
		
							// Save stream info in the option
							if ( empty( $analytify_ga4_streams ) ) {
								$analytify_ga4_streams = array();
							}
							$analytify_ga4_streams[$property_id][$web_stream['webStreamData']['measurementId']] = $measurement_data;
							
							// Check if update_option is successful.
							$update_result = update_option('analytify-ga4-streams', $analytify_ga4_streams);
							if ( ! $update_result ) {
								$logger->error( 'Failed to update GA4 streams option.', array( 'source' => 'analytify_create_stream_errors' ) );
							}
		
							$logger->info( 'Stream found and saved.', array( 'stream_info' => $measurement_data ) );
							return $measurement_data;
						} else {
							$logger->warning( 'Stream found but missing required data fields.', array( 'stream' => $web_stream ) );
						}
					}
				}
			}
		
			// Log when no stream was found
			$logger->warning( 'No existing stream found.', array( 'property_id' => $property_id, 'stream_name' => $stream_name ) );
		
			// If no stream exists, create a new stream
			$url_create_stream = ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/dataStreams';
			$body = array(
				'type' => 'WEB_DATA_STREAM',
				'displayName' => $stream_name,
				'webStreamData' => array(
					'defaultUri' => get_site_url(),
				),
			);
		
			$args['method'] = 'POST';
			$args['body'] = json_encode( $body );
		
			$response = wp_remote_post( $url_create_stream, $args );
		
			// Log the response for debugging
			$logger->info( 'Creating a new stream.', array( 'response' => $response ) );
		
			if ( is_wp_error( $response ) ) {
				$logger->error( 'Error creating stream.', array( 'error_message' => $response->get_error_message(), 'source' => 'analytify_create_stream_errors' ) );
				return;
			}
		
			$body = wp_remote_retrieve_body( $response );
			$web_stream = json_decode( $body, true );
		
			// Log the created stream's response
			$logger->info( 'Created stream response.', array( 'web_stream' => $web_stream ) );
		
			if ( isset( $web_stream['name'] ) && 
				 isset( $web_stream['displayName'] ) && 
				 isset( $web_stream['webStreamData']['measurementId'] ) && 
				 isset( $web_stream['webStreamData']['defaultUri'] ) ) {
				
				$measurement_data = array(
					'full_name'      => $web_stream['name'],
					'property_id'    => $property_id,
					'stream_name'    => $web_stream['displayName'],
					'measurement_id' => $web_stream['webStreamData']['measurementId'],
					'url'            => $web_stream['webStreamData']['defaultUri'],
				);
	
				// Save stream info in the option
				if ( empty( $analytify_ga4_streams ) ) {
					$analytify_ga4_streams = array();
				}
				$analytify_ga4_streams[$property_id][$web_stream['webStreamData']['measurementId']] = $measurement_data;
				
				// Check if update_option is successful.
				$update_result = update_option('analytify-ga4-streams', $analytify_ga4_streams);
				if ( ! $update_result ) {
					$logger->error( 'Failed to update GA4 streams option.', array( 'source' => 'analytify_create_stream_errors' ) );
				}
	
				$logger->info( 'Stream created and saved.', array( 'measurement_data' => $measurement_data ) );
			} else {
				$logger->warning( 'Stream created but missing required data fields.', array( 'web_stream' => $web_stream ) );
			}
		
			return $measurement_data;
		}
		
		
		

		/**
		 * Fetches all the Google Analytics 4 data streams for a given property.
		 *
		 * @param string $property_id The ID of the property for which to fetch the data streams.
		 *
		 * @return array|false Array of data stream objects if found, otherwise false or empty array.
		 * @version 7.0.1
		 */
		public function analytify_get_ga_streams( $property_id ) {
			// If no property ID specified, return false.
			if ( empty( $property_id ) ) {
				$logger = analytify_get_logger();
				error_log( 'No property ID specified in analytify_get_ga_streams function.', array( 'source' => 'analytify_fetch_ga_streams' ) );
				return false;
			}
		
			// Get the access token for authentication.
			$token = $this->analytify_get_google_token();
			
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				$logger = analytify_get_logger();
				error_log( 'Error: Invalid or missing Google Analytics token in analytify_get_ga_streams.', array( 'source' => 'analytify_fetch_ga_streams' ) );
				return null;
			}
			
			$access_token = $token['access_token']; // Method to retrieve your OAuth access token.
			if ( empty( $access_token ) ) {
				$logger = analytify_get_logger();
				error_log( 'Failed to retrieve access token in analytify_get_ga_streams function.', array( 'source' => 'analytify_fetch_ga_streams' ) );
				return null;
			}
			
			// Prepare the request URL and headers.
			$url = ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/dataStreams';
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			);
		
			// Make the API call to list data streams.
			$response = wp_remote_get( $url, $args );
		
			// Check for errors in the response.
			if ( is_wp_error( $response ) ) {
				error_log( 'Error in wp_remote_get: ' . $response->get_error_message(), array( 'source' => 'analytify_fetch_ga_streams' ) );
				return null;
			}
		
			// Parse the response body.
			$body = wp_remote_retrieve_body( $response );
			$decoded_response = json_decode( $body, true );
		
			// Check for JSON parsing errors.
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				error_log( 'JSON decoding error: ' . json_last_error_msg(), array( 'source' => 'analytify_fetch_ga_streams' ) );
				return null;
			}
		
			// Check if streams are available.
			if ( isset( $decoded_response['dataStreams'] ) ) {
				$all_streams = array();
		
				foreach ( $decoded_response['dataStreams'] as $stream ) {
					// Only include web data streams with proper checks
					if ( isset( $stream['type'] ) && $stream['type'] === 'WEB_DATA_STREAM' &&
						 isset( $stream['name'] ) && 
						 isset( $stream['displayName'] ) && 
						 isset( $stream['webStreamData']['measurementId'] ) && 
						 isset( $stream['webStreamData']['defaultUri'] ) ) {
						
						$stream_data = array(
							'full_name'      => $stream['name'],
							'property_id'    => $property_id,
							'stream_name'    => $stream['displayName'],
							'measurement_id' => $stream['webStreamData']['measurementId'],
							'url'            => $stream['webStreamData']['defaultUri'],
						);
		
						// Add the current stream to the array of all streams.
						$all_streams[ $stream['webStreamData']['measurementId'] ] = $stream_data;
					}
				}
		
				// Save streams to the database for future reference.
				$ga4_streams = $this->analytify_get_ga4_streams();
				$ga4_streams[$property_id] = $all_streams;

				// Check if update_option is successful.
				$update_result = update_option( 'analytify-ga4-streams', $ga4_streams );
				if ( ! $update_result ) {
					error_log( 'Failed to update options for GA4 streams.' );
				}
		
				return $all_streams; // Return the list of streams.
			} else {
				error_log( 'No dataStreams found in the response for property ID: ' . $property_id, array( 'source' => 'analytify_fetch_ga_streams' ) );
			}
		
			return null; // Return null if no streams found.
		}
		
		

		/**
		 * Lookup for a single "GA4" MeasurementProtocolSecret.
		 *
		 * @param string $formattedName The name of the measurement protocol secret to lookup.
		 * @version 7.0.1
		 */
		public function analytify_get_mp_secret( $formattedName )
		{
			if (empty($formattedName)) {
				error_log("Error: formattedName is empty in analytify_get_mp_secret.");
				return;
			}

			$token = $this->analytify_get_google_token();
		
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				error_log("Error: Invalid or missing Google Analytics token in analytify_get_mp_secret.");
				return;
			}
			
			$access_token = $token['access_token'];

			if (empty($access_token)) {
				error_log("Error: Access token is missing in analytify_get_mp_secret.");
				return;
			}

			$url = ANALYTIFY_GA_ADMIN_API_BASE . "/$formattedName/measurementProtocolSecrets";

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			);

			$mp_secret_value = null;

			try {
				$response = wp_remote_get($url, $args);

				if (is_wp_error($response)) {
					error_log("Error in analytify_get_mp_secret: " . $response->get_error_message());
					return false;
				}

				$body = wp_remote_retrieve_body($response);
				$data = json_decode($body, true);

				if (isset($data['measurementProtocolSecrets']) && is_array($data['measurementProtocolSecrets'])) {
					foreach ($data['measurementProtocolSecrets'] as $secret) {
						if (isset($secret['secretValue'])) {
							$mp_secret_value = $secret['secretValue'];
							break;
						}
					}
				}
			} catch (Exception $e) {
				error_log("Error in analytify_get_mp_secret: " . $e->getMessage());
				return false;
			}

			if (is_null($mp_secret_value)) {
				error_log("Error: No Measurement Protocol Secret found for formattedName: $formattedName.");
			}

			return $mp_secret_value;
		}

		/**
		 * Create mp secret for given propert
		 * Checks if mp secret exists otherwise
		 * create newone if analytify stream exists.
		 * 
		 * @param string $property_id
		 * 
		 * @since 5.0.0
		 * @version 7.0.1
		 */
		public function analytify_create_mp_secret( $property_id, $stream_full_name, $display_name ) {
			$analytify_all_streams = $this->analytify_get_ga4_streams();
			$analytify_ga4_stream  = isset($analytify_all_streams[$property_id][$display_name]) ? $analytify_all_streams[$property_id][$display_name] : "";
		
			// Return the secret if it exists.
			if (isset($analytify_ga4_stream['analytify_mp_secret']) && $analytify_ga4_stream['analytify_mp_secret']) {
				error_log("MP Secret already exists for property ID: {$property_id}, display name: {$display_name}");
				return $analytify_ga4_stream['analytify_mp_secret'];
			} elseif (empty($analytify_ga4_stream['full_name'])) {
				error_log("Stream full name is empty for property ID: {$property_id}, display name: {$display_name}");
				return;
			}
		
			// Fetch the access token for making authorized API calls.
			$token = $this->analytify_get_google_token();
			
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				error_log("Error: Invalid or missing Google Analytics token for property ID: {$property_id}");
				return;
			}
			
			$access_token = $token['access_token'];
			if (!$access_token) {
				error_log("Access token is missing for property ID: {$property_id}");
				return;
			}
		
			// Step 1: Acknowledge user data collection if necessary.
			if (!isset($analytify_ga4_stream['mp_user_acknowledgement']) || true != $analytify_ga4_stream['mp_user_acknowledgement']) {
				try {
					$url = ANALYTIFY_GA_ADMIN_API_BASE . "/properties/{$property_id}:acknowledgeUserDataCollection";
					$acknowledgement = array(
						'acknowledgement' => 'I acknowledge that I have the necessary privacy disclosures and rights from my end users...',
					);
					$response = wp_remote_post($url, array(
						'method'  => 'POST',
						'headers' => array(
							'Authorization' => 'Bearer ' . $access_token,
							'Content-Type'  => 'application/json',
						),
						'body'    => wp_json_encode($acknowledgement),
					));
		
					if (is_wp_error($response)) {
						error_log("Error acknowledging user data collection: " . $response->get_error_message());
						return;
					}
		
					// If successful, update the acknowledgment in the option.
					$analytify_ga4_stream['mp_user_acknowledgement'] = true;
					$analytify_all_streams[$property_id][$display_name] = $analytify_ga4_stream;
					update_option('analytify-ga4-streams', $analytify_all_streams);
					WPANALYTIFY_Utils::remove_ga4_exception('mp_secret_exception');
					error_log("User data collection acknowledged for property ID: {$property_id}");
				} catch (Exception $e) {
					WPANALYTIFY_Utils::add_ga4_exception('mp_secret_exception', 'Acknowledgment error', $e->getMessage());
					error_log("Exception during user data acknowledgment: " . $e->getMessage());
					return;
				}
			}
		
			// Step 2: Create Measurement Protocol Secret.
			try {
				$url = ANALYTIFY_GA_ADMIN_API_BASE . "/{$stream_full_name}/measurementProtocolSecrets";
				$body = array(
					'displayName' => 'analytify_mp_secret',
				);
		
				$response = wp_remote_post($url, array(
					'method'  => 'POST',
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode($body),
				));
		
				if (is_wp_error($response)) {
					error_log("Error creating MP Secret: " . $response->get_error_message());
					return;
				}
		
				$response_body = json_decode(wp_remote_retrieve_body($response), true);
				if (isset($response_body['secretValue']) && !empty($response_body['secretValue'])) {
					$secret_value = $response_body['secretValue'];
					$analytify_ga4_stream['analytify_mp_secret'] = $secret_value;
					$analytify_all_streams[$property_id][$display_name] = $analytify_ga4_stream;
					update_option('analytify-ga4-streams', $analytify_all_streams);
					WPANALYTIFY_Utils::remove_ga4_exception('mp_secret_exception');
					error_log("MP Secret created successfully for property ID: {$property_id}, display name: {$display_name}");
		
					return $secret_value;
				} else {
					error_log("MP Secret creation response does not contain a secret value for property ID: {$property_id}");
				}
			} catch (Exception $e) {
				WPANALYTIFY_Utils::add_ga4_exception('mp_secret_exception', 'Creation error', $e->getMessage());
				error_log("Exception during MP Secret creation: " . $e->getMessage());
			}
		
			error_log("MP Secret creation failed for property ID: {$property_id}, display name: {$display_name}");
			return;
		}
		

		/**
		 * Retrieve and list Google Analytics accounts using the provided access token.
		 *
		 * This function interacts with the Google Analytics API to fetch a list of accounts
		 * associated with the given access token. It is used to display or process the accounts
		 * linked to the authenticated user.
		 * @since 7.0.0
		 * @version 7.0.3
		 * @param string $access_token The access token for authenticating with the Google Analytics API.
		 * @return array List of accounts.
		 */
		private function analytify_list_accounts( $access_token ) {
			// Early bail if access_token is empty
			if ( empty( $access_token ) ) {
				error_log( 'Error: Access token is empty in analytify_list_accounts.' );
				return array();
			}

			$url      = ANALYTIFY_GA_ADMIN_API_BASE . '/accounts';
			$accounts = array();
			$pageToken = '';

			do {
				// Build URL with pageToken if needed
				$request_url = $url;
				if ( ! empty( $pageToken ) ) {
					$request_url .= '?pageToken=' . urlencode( $pageToken );
				}

				$response = wp_remote_get( $request_url, array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				) );

				if ( is_wp_error( $response ) ) {
					error_log( 'Error fetching accounts: ' . $response->get_error_message() );
					return $accounts;
				}

				if ( ! is_array( $response ) ) {
					error_log( 'Error fetching accounts: Invalid response format. Raw response: ' . print_r( $response, true ) );
					return $accounts;
				}

				$raw_body = wp_remote_retrieve_body( $response );
				$body     = json_decode( $raw_body, true );

				if ( isset( $body['accounts'] ) && is_array( $body['accounts'] ) ) {
					$accounts = array_merge( $accounts, $body['accounts'] );
				}

				// If nextPageToken exists, loop again
				$pageToken = isset( $body['nextPageToken'] ) ? $body['nextPageToken'] : '';

			} while ( ! empty( $pageToken ) );

			return $accounts;
		}

		
		

		/**
		 * List properties for a given account using the provided access token.
		 *
		 * This function retrieves and lists the properties associated with a specific
		 * Google Analytics account. It requires an access token for authentication
		 * and the account name to identify the target account.
		 *
		 * @param string $access_token The access token for authenticating the API request.
		 * @param string $account_name The name of the Google Analytics account.
		 *
		 * @since 7.0.0
		 * @version 7.0.3
		 */
		private function analytify_list_properties( $access_token, $account_name ) {
			$url = ANALYTIFY_GA_ADMIN_API_BASE . '/properties?filter=parent:' . $account_name. '&pageSize=1000';

			$all_properties = array();
			$page_token     = '';

			do {
			$final_url = $url;
			if ( $page_token ) {
				$final_url .= '&pageToken=' . $page_token;
			}

			$response = wp_remote_get( $final_url, array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			));

			if ( is_wp_error( $response ) ) {
				error_log( 'Error fetching properties: ' . $response->get_error_message() );
				break;
			}

			if ( ! is_array( $response ) ) {
				error_log( 'Error fetching properties: Invalid response format' );
				break;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code !== 200 ) {
				error_log( 'Response Message: ' . wp_remote_retrieve_response_message( $response ) );
				error_log( 'Raw Body: ' . wp_remote_retrieve_body( $response ) );
				break;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $body['properties'] ) && is_array( $body['properties'] ) ) {
				$all_properties = array_merge( $all_properties, $body['properties'] );
			}

			$page_token = isset( $body['nextPageToken'] ) ? $body['nextPageToken'] : '';

			} while ( $page_token );

			if ( empty( $all_properties ) ) {
				error_log( 'Error: No properties found in response.' );
			}

			return $all_properties;
		}
	

		/**
		 * Retrieve Google Analytics properties for the authenticated user.
		 *
		 * @since 7.0.0
		 * @version 7.0.1
		 */
		public function analytify_get_ga_properties() {
			$token = $this->analytify_get_google_token();
			
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				echo sprintf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Error: Invalid or missing Google Analytics token. Please re-authenticate.', 'wp-analytify' ) );
				return array();
			}
			
			$access_token = $token['access_token'];
			
			if ( ! $access_token ) {
				echo sprintf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Error: Unable to authenticate with Google Analytics.', 'wp-analytify' ) );
				return array();
			}
		
			$accounts = array();
			try {
				if ( $this->get_ga4_exception() ) {
					WPANALYTIFY_Utils::handle_exceptions( $this->get_ga4_exception() );
				}
		
				if ( get_option( 'pa_google_token' ) != '' ) {
					$accounts = $this->analytify_list_accounts( $access_token );
				} else {
					echo sprintf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Notice: You must authenticate to access your web profiles.', 'wp-analytify' ) );
					return array();
				}
		
			} catch ( Exception $e ) {
				$error_message = $e->getMessage();
				$logger = analytify_get_logger();
				$logger->warning( $error_message, array( 'source' => 'analytify_analytify_get_ga_properties_errors' ) );
				return array();
			}
		
			$ga_properties = array();
		
			foreach ( $accounts as $account ) {
				$account_name = $account['name'];  // e.g., 'accounts/123456'
				$properties = $this->analytify_list_properties( $access_token, $account_name );
				$property_data = array();
		
				foreach ( $properties as $property ) {
					// Extract property ID in a similar way to the previous code
					$id = explode( '/', $property['name'] );
					$id = isset( $id[1] ) ? $id[1] : $property['name'];
		
					$property_data[] = array(
						'id' => $id,
						'name' => $property['name'],
						'display_name' => $property['displayName'],
					);
				}
		
				if ( $property_data ) {
					$ga_properties[ $account['displayName'] ] = $property_data;
				}
			}
		
			// If no error, delete the exception.
			WPANALYTIFY_Utils::remove_ga4_exception( 'fetch_ga4_profiles_exception' );
	
			return $ga_properties;
		}
		
		
		
		/**
		 * Fetch reports from Google Analytics Data API.
		 * @param string $name 'test-report-name' Its the key used to store reports in transient as cache.
		 * @param array $metrics {
		 *     'screenPageViews',
		 *	   'userEngagementDuration',
		 *	   'bounceRate',
		 * }
		 * @param array $date_range {
		 *     'start' => '30daysAgo', Format should be either YYYY-MM-DD, yesterday, today, or NdaysAgo where N is a positive integer
		 *     'end'   => 'yesterday', 
		 * }
		 * @param array $dimensions {
		 *     'pageTitle',
		 *     'pagePath'
		 * }
		 * @param array $order_by {
		 *     'type' => 'metric', Should be either 'metric' or 'dimension'.
		 *     'name' => 'screenPageViews', Name of the metric or dimension.
		 * }
		 * @param array $filters {
		 *     {
		 *          'type' => 'dimension', Should be either 'metric' or 'dimension'.
		 *          'name' => 'sourcePlatform', Name of the metric or dimension.
		 *          'match_type' => 5, (EXACT = 1; BEGINS_WITH = 2; ENDS_WITH = 3; CONTAINS = 4; FULL_REGEXP = 5; PARTIAL_REGEXP = 6;)
		 *          'value' => 'Linux', Value depending on match type.
		 *          'not_expression' => true, If a not expression i.e !=
		 *     },
		 *     {
		 *         ...
		 *     }
		 *     ...
		 * }
		 * @param integer array $limit Positive integer to limit report rows.
		 * 
		 * @return array {
		 * 	   'headers' => {
		 *         ...
		 * 	   },
		 * 	   'rows' => {
		 *         ...
		 * 	   }
		 * }
		 * @version 7.0.1
		 */
		public function get_reports( $name, $metrics, $date_range, $dimensions = array(), $order_by = array(), $filters = array(), $limit = 0, $cached = true ) {
			$property_id   = WPANALYTIFY_Utils::get_reporting_property();
		
			// Don't use cache if custom API keys are in use.
			if ( $this->settings->get_option( 'user_advanced_keys', 'wp-analytify-advanced' ) === 'on' ) {
				$cached = false;
			}
		
			// To override the caching.
			$cached = apply_filters( 'analytify_set_caching_to', $cached );
		
			if ( $cached ) {
				$cache_key = 'analytify_transient_' . md5( $name . $property_id . $date_range['start'] . $date_range['end'] );
				$report_cache = get_transient( $cache_key );
		
				if ( $report_cache ) {
					return $report_cache;
				}
			}
		
			$reports = array();
			$dimension_filters = array();


			// Default response array.
			$default_response = array(
				'headers'      => array(),
				'rows'         => array(),
				'error'        => array(),
				'aggregations' => array(),
			);
		
			try {
				// Main request body for the report.
				$request_body = array(
					'dateRanges' => array(
						array(
							'startDate' => isset( $date_range['start'] ) ? $date_range['start'] : 'today',
							'endDate'   => isset( $date_range['end'] ) ? $date_range['end'] : 'today',
						),
					),
					'metricAggregations' => array( 1 ) // TOTAL = 1; COUNT = 4; MINIMUM = 5; MAXIMUM = 6;
				);
		
				// Set metrics.
				if ( $metrics ) {
					$send_metrics = array();
					foreach ( $metrics as $value ) {
						$send_metrics[] = array( 'name' => $value );
					}
					$request_body['metrics'] = $send_metrics;
				}
		
				// Add dimensions.
				if ( $dimensions ) {
					$send_dimensions = array();
					foreach ( $dimensions as $value ) {
						$send_dimensions[] = array( 'name' => $value );
					}
					$request_body['dimensions'] = $send_dimensions;
				}
		
				// Order report by metric or dimension.
				if ( $order_by ) {
					$order_by_request = array();
					$is_desc = ( empty( $order_by['order'] ) || 'desc' !== $order_by['order'] ) ? false : true;
		
					if ( 'metric' === $order_by['type'] ) {
						$order_by_request = array(
							'metric' => array(
								'metric_name' => isset($order_by['name']) ? $order_by['name'] : ''
							),
							'desc' => $is_desc,
						);
					} else if ( 'dimension' === $order_by['type'] ) {
						$order_by_request = array(
							'dimension' => array(
								'dimension_name' => $order_by['name']
							),
							'desc' => $is_desc,
						);
					}
		
					$request_body['orderBys'] = array($order_by_request);
				}
		
				// Filters for the report.
				if ( $filters ) {
					$dimension_filters = array(); // Initialize an empty array for filters.
				
					foreach ( $filters['filters'] as $filter_data ) {
						if ( 'dimension' === $filter_data['type'] ) {
							if ( isset( $filter_data['not_expression'] ) && $filter_data['not_expression'] ) {
								// Handle 'not_expression' logic.
								$dimension_filters[] = array(
									'not_expression' => array(
										'filter' => array(
											'field_name'    => $filter_data['name'],
											'string_filter' => array(
												'match_type'     => $filter_data['match_type'],
												'value'          => $filter_data['value'],
												'case_sensitive' => true,
											)
										)
									)
								);
							} else {
								// Standard dimension filter.
								$dimension_filters[] = array(
									'filter' => array(
										'field_name'    => $filter_data['name'],
										'string_filter' => array(
											'match_type'     => $filter_data['match_type'],
											'value'          => $filter_data['value'],
											'case_sensitive' => true,
										)
									)
								);
							}
						} else if ( 'metric' === $filter_data['type'] ) {
							// TODO: Add metric filter handling here.
						}
					}
				
					if ( $dimension_filters ) {
						$group_type = ( isset( $filters['logic'] ) && 'OR' === $filters['logic'] ) ? 'or_group' : 'and_group';
		
						$dimension_filter_construct = array(
							$group_type => array(
								'expressions' => $dimension_filters
							)
						);
		
						$request_body['dimensionFilter'] = $dimension_filter_construct;
					}
				}
		
				// Set limit.
				if ( 0 < $limit ) {
					$request_body['limit'] = $limit;
				}
		
				// Get access token (this function should be implemented by you).
				$token = $this->analytify_get_google_token();
				
				// Validate that token is an array and has the expected structure.
				if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
					error_log('Error: Invalid or missing Google Analytics token in runReport.');
					return array();
				}
				
				$access_token = $token['access_token'];

		
				// Prepare the cURL request URL for GA4 API.
				$url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $property_id . ':runReport';
		
				// Send the request using wp_remote_post.
				$response = wp_remote_post( $url, array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
					'body'    => json_encode( $request_body ),
				));
		
				// Check for errors in the response.
				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message() );
				}
		
				// Parse the response body.
				$reports = json_decode( wp_remote_retrieve_body( $response ), true );
		
				// If the response doesn't contain rows, handle it accordingly.
				if ( !isset( $reports['rows'] ) ) {
					return $default_response;
				}
		
			} catch ( \Throwable $th ) {
				if ( is_callable( $th, 'getStatus' ) && is_callable( $th, 'getBasicMessage' ) ) {
					$default_response['error'] = array(
						'status'  => $th->getStatus(),
						'message' => $th->getBasicMessage(),
					);
				} else if ( method_exists( $th, 'getMessage' ) ) {
					$default_response['error'] = array(
						'status'  => 'Token Expired',
						'message' => $th->getMessage(),
					);
				}
		
				return $default_response;
			}
		
			// Format the reports using your existing function.
			$formatted_reports = $this->analytify_format_ga_reports( $reports );
		
			if ( empty( $formatted_reports ) ) {
				return $default_response;
			}
		
			// Cache the response if caching is enabled.
			if ( $cached ) {
				$this->analytify_handle_report_cache($cache_key, $formatted_reports, $name, $cached);
			}
		
			return $formatted_reports;
		}
		

		/**
		 * Fetch real time reports from Google Analytics Data API.
		 * @param string $name 'test-report-name' Its the key used to store reports in transient as cache.
		 * @param array $metrics {
		 *     'screenPageViews',
		 *	   'userEngagementDuration',
		 *	   'bounceRate',
		 * }
		 * @param array $dimensions {
		 *     'pageTitle',
		 *     'pagePath'
		 * }
		 * 
		 * @return array {
		 * 	   'headers' => {
		 *         ...
		 * 	   },
		 * 	   'rows' => {
		 *         ...
		 * 	   }
		 * }
		 * @version 7.0.1
		 */
		public function get_real_time_reports($metrics, $dimensions = array()) {
			$property_id = WPANALYTIFY_Utils::get_reporting_property();
			$reports = array();
		
			// Default response array.
			$default_response = array(
				'headers'      => array(),
				'rows'         => array(),
				'error'        => array(),
				'aggregations' => array(),
			);
		
			try {
				// Main request body for the report.
				$request_body = array(
					'property' => 'properties/' . $property_id,
				);
		
				// Set metrics.
				if ($metrics) {
					$send_metrics = array();
		
					foreach ($metrics as $value) {
						$send_metrics[] = array('name' => $value); // Replace Google Metric with plain array
					}
		
					$request_body['metrics'] = $send_metrics;
				}
		
				// Add dimensions.
				if ($dimensions) {
					$send_dimensions = array();
		
					foreach ($dimensions as $value) {
						$send_dimensions[] = array('name' => $value); // Replace Google Dimension with plain array
					}
		
					$request_body['dimensions'] = $send_dimensions;
				}
		
				// Prepare the cURL request.
				$url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $property_id . ':runRealtimeReport';

				$token = $this->analytify_get_google_token();
				
				// Validate that token is an array and has the expected structure.
				if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
					error_log('Error: Invalid or missing Google Analytics token in runRealtimeReport.');
					return array();
				}
				
				$access_token = $token['access_token'];
		
				$response = wp_remote_post($url, array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
					'body'    => json_encode($request_body),
				));
		
				// Check if the request was successful.
				if (is_wp_error($response)) {
					throw new Exception($response->get_error_message());
				}
		
				$reports = json_decode(wp_remote_retrieve_body($response), true); // Decode the response
		
			} catch (\Throwable $th) {
				$default_response['error'] = array(
					'status'  => '',
					'message' => '',
				);
		
				return $default_response;
			}
		
			if (!is_array($reports)) {
				if (isset($th) && method_exists($th, 'getMessage')) {
					$default_response['error'] = array(
						'status'  => 'Token Expired',
						'message' => $th->getMessage(),
					);
				}
				return $default_response;
			}
		
			if (empty($reports['rows'])) {
				return $default_response;
			}
		
			$formatted_reports = $this->analytify_format_ga_reports($reports);
		
			if (empty($formatted_reports)) {
				return $default_response;
			}
		
			return $formatted_reports;
		}
		

		/**
		 * List all dimensions present in current selected GA property.
		 *
		 * @return array
		 */
		public function analytify_list_dimensions() {

			$property_id = WPANALYTIFY_Utils::get_reporting_property();
			$dimensions = array();

			try {
				$admin_client = $this->analytify_connect_admin_api();
				$dimensions_paged_response = $admin_client->ListCustomDimensions( 'properties/' . $property_id );

				foreach ( $dimensions_paged_response->iteratePages() as $page ) {
					foreach ( $page as $element ) {
						$dimensions[] = $element->getParameterName();
					}
				}

			} catch ( \Throwable $th ) {
				return $dimensions;
			}

			return $dimensions;
		}

		/**
		 * List all custom dimensions that needs to be created in selected GA property.
		 *
		 * @return array ()
		 */
		public function analytify_list_dimensions_needs_creation() {

			$current_property_dimensions = $this->analytify_list_dimensions();
			$required_dimensions         = WPANALYTIFY_Utils::required_dimensions();

			if ( ! empty( $current_property_dimensions ) ) {
				foreach ( $required_dimensions as $key => &$dimension ) {
					if ( in_array( $dimension['parameter_name'], $current_property_dimensions, true ) ) {
						unset( $required_dimensions[ $key ] );
					}
				}
				return $required_dimensions;
			}

			return $required_dimensions;
		}

		/**
		 * Create custom dimensions with Admin API.
		 *
		 * @param string  $parameter_name Max length of 24 characters.
		 * @param string  $display_name Max length of 82 characters.
		 * @param integer $scope 0 = Undefined scope, 1 = Event, 2 = User.
		 * @param string  $description Max length of 150 characters.
		 * @param integer $property_id Reporting property ID to associate dimension, default is current reporting property.
		 * 
		 * @return array
		 * @version 7.0.1
		 */
		public function analytify_create_dimension( $parameter_name, $display_name, $scope, $description = '', $property_id = '' ) {

			// Get the property ID, if not provided
			$property_id = ! empty( $property_id ) ? $property_id : WPANALYTIFY_Utils::get_reporting_property();
			
			if ( empty( $property_id ) ) {
				return;
			}
		
			$token = $this->analytify_get_google_token();
		
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				error_log('Error: Invalid or missing Google Analytics token in analytify_create_custom_dimension.');
				return;
			}
			
			$access_token = $token['access_token']; // You should have a method to retrieve the access token
		
			$url = ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/customDimensions';
		
			$body = array(
				'parameterName' => $parameter_name,
				'displayName'   => $display_name,
				'scope'         => $scope,
				'description'   => ! empty( $description ) ? $description : 'Analytify custom dimension.',
			);
		
			$args = array(
				'method'    => 'POST',
				'headers'   => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'      => json_encode( $body ),
			);
		
			$return_response = array(
				'response' => 'created',
			);
		
			// Send the API request
			$response = wp_remote_post( $url, $args );
		
			// Handle the response
			if ( is_wp_error( $response ) ) {
				$logger = analytify_get_logger();
				$logger->warning( $response->get_error_message(), array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
				return array(
					'response' => 'failed',
					'message'  => $response->get_error_message(),
				);
			}
		
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( $response_code != 200 ) {
				$body = wp_remote_retrieve_body( $response );
				$decoded_body = json_decode( $body, true );
				$message = isset( $decoded_body['error']['message'] ) ? $decoded_body['error']['message'] : 'Unknown error';
		
				$logger = analytify_get_logger();
				$logger->warning( $message, array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
		
				return array(
					'response' => 'failed',
					'message'  => $message,
				);
			}
		
			// Return the response
			return $return_response;
		}
		

		/**
		 * Format reports data fetched from Google Analytics Data API.
		 *
		 * For references check folder for class definitions: lib\Google\vendor\google\analytics-data\src\V1beta
		 *
		 * @param $reports
		 * @return array
		 */
		public function analytify_format_ga_reports( $reports ) {
			$metric_header_data = array();
			$dimension_header_data = array();
			$aggregations = array();
			$rows = array();

			// Get metric headers.
			if (isset($reports['metricHeaders'])) {
				foreach ( $reports['metricHeaders'] as $metric_header ) {
					$metric_header_data[] = $metric_header['name'];
				}
			}
		
			// Get dimension headers.
			if (isset($reports['dimensionHeaders'])) {
				foreach ( $reports['dimensionHeaders'] as $dimension_header ) {
					$dimension_header_data[] = $dimension_header['name'];
				}
			}
		
			$headers = array_merge( $metric_header_data, $dimension_header_data );
		
			// Bind metrics and dimensions to rows.
			if (isset($reports['rows'])) {
				foreach ( $reports['rows'] as $row ) {
					$metric_data = array();
					$dimension_data = array();
		
					// Process metric values.
					if (isset($row['metricValues'])) {
						$index_metric = 0;
						foreach ( $row['metricValues'] as $value ) {
							$metric_data[$metric_header_data[$index_metric]] = $value['value'];
							$index_metric++;
						}
					}
		
					// Process dimension values.
					if (isset($row['dimensionValues'])) {
						$index_dimension = 0;
						foreach ( $row['dimensionValues'] as $value ) {
							$dimension_data[$dimension_header_data[$index_dimension]] = $value['value'];
							$index_dimension++;
						}
					}
		
					// Combine metric and dimension data.
					$rows[] = array_merge( $metric_data, $dimension_data );
				}
			}
		
			// Get metric aggregations (totals).
			if (isset($reports['totals'])) {
				foreach ( $reports['totals'] as $total ) {
					$index_metric = 0;
		
					if (isset($total['metricValues'])) {
						foreach ( $total['metricValues'] as $value ) {
							$aggregations[$metric_header_data[$index_metric]] = $value['value'];
							$index_metric++;
						}
					}
				}
			}
		
			// Format and return the data.
			$formatted_data = array(
				'headers'      => $headers,
				'rows'         => $rows,
				'aggregations' => $aggregations
			);
		
			return $formatted_data;
		}
		
		/**
		 * Get a fresh access token.
		 *
		 * @since 7.0.0
		 */
		public function analytify_get_fresh_access_token() {
			// Load the token from your storage
			$auth_token = $this->client->getAccessToken();
		
			// Extract the created time and expires_in value
			$created_time = $auth_token['created'];
			$expires_in = $auth_token['expires_in'];
		
			// Get the current time
			$current_time = time();
		
			// Check if the token has expired
			if (($created_time + $expires_in) < $current_time) {
				// Token has expired, refresh it
				if ($this->client->isAccessTokenExpired()) {
					$this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
		
					// Save the new token to your storage
					$new_token = $this->client->getAccessToken();
				}
			}
		
			// Return the access token (fresh or existing)
			$auth_token = $this->client->getAccessToken();
			return $auth_token['access_token'];
		}

		/**
		 * Query the search console api and return the response.
		 * Since SC can have two types of domain properties.
		 * We will first go with the sc-domain prefix with property
		 * if it fails we will use the second domain type using 'https://'
		 * 
		 * @param $dates array
		 * @param $limit limit
		 * 
		 * @since 5.0.0
		 * @version 7.0.1
		 */
		public function get_search_console_stats($transient_name, $dates = [], $limit = 10) {
			$response = [
				'error' => []
			];
			$logger = analytify_get_logger();
			$token = $this->analytify_get_google_token();
			
			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				error_log('Error: Invalid or missing Google Analytics token in set_stream_enhanced_measurement.');
				return [ 'error' => ['Invalid or missing Google Analytics token.'] ];
			}
			
			$access_token = $token['access_token'];

		
			$tracking_stream_info = get_option('analytify_tracking_property_info');
			try {
				$stream_url = (isset($tracking_stream_info['url']) && !empty($tracking_stream_info['url'])) ? $tracking_stream_info['url'] : null;
			} catch (\Throwable $th) {
				$logger->warning("Error Fetching Stream URL: " . $th->getMessage(), ['source' => 'analytify_fetch_stream_url']);
				if (empty($stream_url)) {
					$response['error'] = [
						'status' => 'No Stats Available',
						'message' => __("No URL found for the selected stream", 'wp-analytify')
					];
					return $response;
				}
			}
		
			$domain_stream_url_filtered = preg_replace("/^(https?:\/\/)?(www\.)?([^\/]+)(\/.*)?$/i", "$3", $stream_url);
			$domain_stream_url = "sc-domain:$domain_stream_url_filtered";
		
			$urls = [
				$domain_stream_url,
				'https://' . preg_replace("(^(https?:\/\/([wW]{3}\.)?)?)", "", $domain_stream_url_filtered),
				'https://' . preg_replace("(^(https?:\/\/([wW]{3}\.)?)?)", "www.", $domain_stream_url_filtered),
				'http://' . preg_replace("(^(https?:\/\/([wW]{3}\.)?)?)", "", $domain_stream_url_filtered),
				'http://' . preg_replace("(^(https?:\/\/([wW]{3}\.)?)?)", "www.", $domain_stream_url_filtered)
			];
		
			$base_url = "https://www.googleapis.com/webmasters/v3/sites/";
			$start_date = isset($dates['start']) ? $dates['start'] : 'yesterday';
			$end_date = isset($dates['end']) ? $dates['end'] : 'today';
		
			foreach ($urls as $url) {
				try {
					// Prepare the Search Analytics API query
					$query_data = [
						"startDate" => $start_date,
						"endDate" => $end_date,
						"dimensions" => ["query"],
						"rowLimit" => $limit,
					];
		
					// Set up cURL request to the Search Console API
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $base_url . urlencode($url) . "/searchAnalytics/query");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, [
						'Authorization: Bearer ' . $access_token,
						'Content-Type: application/json'
					]);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_data));
		
					// Execute the cURL request
					$result = curl_exec($ch);

					$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
					// Handle the response
					if ($http_code === 200) {
						$response['response'] = json_decode($result, true);
		
						// Clear error if successful
						unset($response['error']);
						curl_close($ch);
						return $response;
					} else {
						$logger->warning("Error querying $url: HTTP code $http_code", ['source' => 'analytify_fetch_search_console_stats']);
					}
		
					curl_close($ch);
				} catch (\Throwable $th) {
					// Log error with context
					$logger->warning("Error querying $url: " . $th->getMessage(), ['source' => 'analytify_fetch_search_console_stats']);
		
					// Continue to next URL if error is transient
					continue;
				}
			}
		
			// Set error response if all URLs failed
			$response['error'] = [
				'status'  => "No Stats Available for $domain_stream_url_filtered",
				'message' => __("Analytify gets GA4 Keyword stats from Search Console. Make sure you've verified and have owner access to your site in Search Console.", 'wp-analytify'),
			];
		
			return $response;
		}
		
		


		/**
		 * This function grabs the data from Google Analytics for individual posts/pages.
		 *
		 * @param string $metrics
		 * @param string $start_date
		 * @param string $end_date
		 * @param boolean $dimensions
		 * @param boolean $sort
		 * @param boolean $filter
		 * @param boolean $limit
		 * @param string $name
		 * @return void
		 */
		public function pa_get_analytics( $metrics, $start_date, $end_date, $dimensions = false, $sort = false, $filter = false, $limit = false, $name = ''  ) {

			if ( $this->is_reporting_in_ga4 ) {
				return;
			}

			try {
				$this->service = new Analytify_Google_Service_Analytics( $this->client );
				$params        = array();

				if ( $dimensions ) {
					$params['dimensions'] = $dimensions;
				}

				if ( $sort ) {
					$params['sort'] = $sort;
				}

				if ( $filter ) {
					$params['filters'] = $filter;
				}

				if ( $limit ) {
					$params['max-results'] = $limit;
				}

				$profile_id = $this->settings->get_option( 'profile_for_posts', 'wp-analytify-profile' );

				if ( ! $profile_id ) {
					return false;
				}

				$transient_key = 'analytify_transient_';
				$cache_result  = get_transient( $transient_key . md5( $name . $profile_id . $start_date . $end_date . $filter ) );

				// TODO: remove this hard coded setting

				$is_custom_api = $this->settings->get_option( 'user_advanced_keys', 'wp-analytify-advanced' );

				if ( 'on' !== $is_custom_api ) {
					// If exception, return if the cache result else return the error.
					if ( $exception = get_transient( 'analytify_quota_exception' ) ) {
						return $this->tackle_exception( $exception, $cache_result );
					}
				}

				// If custom keys set. Fetch fresh result always.
				if ( 'on' === $is_custom_api || $cache_result === false ) {
					$result = $this->service->data_ga->get( 'ga:' . $profile_id, $start_date, $end_date, $metrics, $params );
					set_transient( $transient_key . md5( $name . $profile_id . $start_date . $end_date . $filter ) , $result, $this->get_cache_time() );
					return $result;

				} else {
					return $cache_result;
				}
			} catch ( Analytify_Google_Service_Exception $e ) {
				// Show error message only for logged in users.
			if ( current_user_can( 'manage_options' ) ) {
					echo "<div class='wp_analytify_error_msg'>";
					// translators: Error message for logged in users
					echo sprintf( esc_html__( '%1$s Oops, Something went wrong. %2$s %5$s %2$s %3$s Don\'t worry, This error message is only visible to Administrators. %4$s %2$s ', 'wp-analytify' ), '<br /><br />', '<br />', '<i>', '</i>', esc_html( $e->getMessage() ) );
					echo "</div>";
				}
			} catch ( Analytify_Google_Auth_Exception $e ) {
				// Show error message only for logged in users.
				if ( current_user_can( 'manage_options' ) ) {
					echo "<div class='wp_analytify_error_msg'>";
					// translators: Reset authentication error message
					echo sprintf( esc_html__( '%1$s Oops, Try to %3$s Reset %4$s Authentication. %2$s %7$s %2$s %5$s Don\'t worry, This error message is only visible to Administrators. %6$s %2$s', 'wp-analytify' ), '<br /><br />', '<br />', '<a href=' . esc_url( admin_url( 'admin.php?page=analytify-settings&tab=authentication' ) ) . ' title="Reset">', '</a>', '<i>', '</i>', esc_textarea( $e->getMessage() ) );
					echo "</div>";
				}
			} catch ( Analytify_Google_IO_Exception $e ) {
				// Show error message only for logged in users.
				if ( current_user_can( 'manage_options' ) ) {
					echo "<div class='wp_analytify_error_msg'>";
					// translators: Error message
					echo sprintf( esc_html__( '%1$s Oops! %2$s %5$s %2$s %3$s Don\'t worry, This error message is only visible to Administrators. %4$s %2$s', 'wp-analytify' ), '<br /><br />', '<br />', '<i>', '</i>', esc_html( $e->getMessage() ) );
					echo "</div>";
				}
			}
		}

		// TODO: Mock Function to resist GA3 removal conflicts.
		public function pa_get_analytics_dashboard( $metrics, $start_date, $end_date, $dimensions = false, $sort = false, $filter = false, $limit = false, $name = '' ) {
			if ( $this->is_reporting_in_ga4 ) {
				return null;
			}
			return false;
		}

		// TODO: Mock Function to resist GA3 removal conflicts.
		public function pa_get_analytics_dashboard_via_rest( $metrics, $start_date, $end_date, $dimensions = false, $sort = false, $filter = false, $limit = false, $name = '' ) {
			if ( $this->is_reporting_in_ga4 ) {
				return null;
			}
			return false;
		}

		/**
		 * This function grabs the data from Google Analytics For dashboard.
		 *
		 * @param string $profile    Google Analytic Profile Id.
		 * @param string $metrics    Metrics.
		 * @param string $start_date Start date of stats.
		 * @param string $end_date   End date of stats.
		 * @param string $dimensions Dimensions.
		 * @param string $sort       Sort.
		 * @param string $filter     Filter.
		 * @param string $limit      How many stats to show.
		 * 
		 * @return array Return array of stats
		 */
		public function analytify_get_analytics( $profile, $metrics, $start_date, $end_date, $dimensions = false, $sort = false, $filter = false, $limit = false ) {

			if ( $this->is_reporting_in_ga4 ) {
				return null;
			}
			try {
				$this->service = new Analytify_Google_Service_Analytics( $this->client );
				$params = array();

				if ( $dimensions ) {
					$params['dimensions'] = $dimensions;
				}
				if ( $sort ) {
					$params['sort'] = $sort;
				}
				if ( $filter ) {
					$params['filters'] = $filter;
				}
				if ( $limit ) {
					$params['max-results'] = $limit;
				}

				if ( 'single' == $profile ) {
					$profile_id = $this->settings->get_option( 'profile_for_posts', 'wp-analytify-profile' );
				} else {
					$profile_id = $this->settings->get_option( 'profile_for_dashboard', 'wp-analytify-profile' );
				}

				if ( ! $profile_id ) {
					return false;
				}

				return $this->service->data_ga->get( 'ga:' . $profile_id, $start_date, $end_date, $metrics, $params );
			} catch ( Analytify_Google_Service_Exception $e ) {
				// Show error message only for logged in users.
				if ( current_user_can( 'manage_options' ) ) {
					// translators: Error message
					echo sprintf( esc_html__( '%1$s Oops, Something went wrong. %2$s %5$s %2$s %3$s Don\'t worry, This error message is only visible to Administrators. %4$s %2$s', 'wp-analytify' ), '<br /><br />', '<br />', '<i>', '</i>', esc_textarea( $e->getMessage() ) );
				}
			} catch ( Analytify_Google_Auth_Exception $e ) {
				// Show error message only for logged in users.
				if ( current_user_can( 'manage_options' ) ) {
					// translators: Error message
					echo sprintf( esc_html__( '%1$s Oops, Try to %3$s Reset %4$s Authentication. %2$s %7$s %2$s %5$s Don\'t worry, This error message is only visible to Administrators. %6$s %2$s', 'wp-analytify' ), '<br /><br />', '<br />', '<a href=' . esc_url( admin_url( 'admin.php?page=analytify-settings&tab=authentication' ) ) . ' title="Reset">', '</a>', '<i>', '</i>', esc_textarea( $e->getMessage() ) );
				}
			} catch ( Analytify_Google_IO_Exception $e ) {
				// Show error message only for logged in users.
				if ( current_user_can( 'manage_options' ) ) {
					// translators: Error message
					echo sprintf( esc_html__( '%1$s Oops! %2$s %5$s %2$s %3$s Don\'t worry, This error message is only visible to Administrators. %4$s %2$s', 'wp-analytify' ), '<br /><br />', '<br />', '<i>', '</i>', esc_html( $e->getMessage() ) );
				}
			}
		}

		/**
		 * Echo value to be returned in ajax response.
		 *
		 * @param boolean $return
		 * 
		 * @return void
		 */
		public function end_ajax( $return = false ) {

			$return = apply_filters( 'wpanalytify_before_response', $return );
			
			echo ( false === $return ) ? '' : $return;
			exit;
		}

		/**
		 * Check ajax referer facade.
		 *
		 * @param string $action
		 * 
		 * @return void
		 */
		public function check_ajax_referer( $action ) {

			$result = check_ajax_referer( $action, 'nonce', false );

			if ( false === $result ) {
				// translators: Error message
				$return = array( 'wpanalytify_error' => 1, 'body' => sprintf( __( 'Invalid nonce for: %s', 'wp-analytify' ), $action ) );
				$this->end_ajax( json_encode( $return ) );
			}

			$cap = ( is_multisite() ) ? 'manage_network_options' : 'export';
			$cap = apply_filters( 'wpanalytify_ajax_cap', $cap );

			if ( ! current_user_can( $cap ) ) {
				// translators: Error message
				$return = array( 'wpanalytify_error' => 1, 'body' => sprintf( __( 'Access denied for: %s', 'wp-analytify' ), $action ) );
				$this->end_ajax( json_encode( $return ) );
			}
		}

		/**
		* Returns the function name that called the function using this function.
		*
		* @return string
		*/
		public function get_caller_function() {
			list( , , $caller ) = debug_backtrace( false );

			if ( ! empty( $caller['function'] ) ) {
				$caller = $caller['function'];
			} else {
				$caller = '';
			}

			return $caller;
		}

		/**
		 * Set $this->state_data from $_POST, potentially un-slashed and sanitized.
		 *
		 * @param array  $key_rules An optional associative array of expected keys and their sanitization rule(s).
		 * @param string $context   The method that is specifying the sanitization rules. Defaults to calling method.
		 *
		 * @since 2.0
		 * @return array
		 */
		public function set_post_data( $key_rules = array(), $context = '' ) {

			if ( defined( 'DOING_WPANALYTIFY_TESTS' ) ) {
				$this->state_data = $_POST;
			} elseif ( is_null( $this->state_data ) ) {
				$this->state_data = WPANALYTIFY_Utils::safe_wp_unslash( $_POST );
			} else {
				return $this->state_data;
			}

			// From this point on we're handling data originating from $_POST, so original $key_rules apply.
			global $wpanalytify_key_rules;

			if ( empty( $key_rules ) && ! empty( $wpanalytify_key_rules ) ) {
				$key_rules = $wpanalytify_key_rules;
			}

			// Sanitize the new state data.
			if ( ! empty( $key_rules ) ) {
				$wpanalytify_key_rules = $key_rules;

				$context          = empty( $context ) ? $this->get_caller_function() : trim( $context );
				$this->state_data = WPANALYTIFY_Sanitize::sanitize_data( $this->state_data, $key_rules, $context );

				if ( false === $this->state_data ) {
					exit;
				}
			}

			return $this->state_data;
		}

		/**
		 * Create no records markup.
		 *
		 * @return void
		 */
		public function no_records() {
			?>

			<div class="analytify-stats-error-msg">
				<div class="wpb-error-box">
					<span class="blk">
						<span class="line"></span>
						<span class="dot"></span>
					</span>
					<span class="information-txt"><?php esc_html_e( 'No Activity During This Period.', 'wp-analytify' ); ?></span>
				</div>
			</div>

			<?php
		}

		/**
		 * Get Exception value.
		 *
		 * @since 2.1.22
		 */
		public function get_exception() {
			return $this->exception;
		}

		/**
		 * Set Exception value.
		 *
		 * @since 2.1.22
		 */
		public function set_exception( $exception ) {
			$this->exception = $exception;
		}

		/**
		 * Get ga4 Exception value.
		 * 
		 * @since 5.0.0
		 */
		public function get_ga4_exception(){
			return $this->ga4_exception;
		}

		/**
		 * Set Exception value.
		 *
		 * @since 5.0.0
		 */
		public function set_ga4_exception( $exception ) {
			$this->ga4_exception = $exception;
		}

		/**
		 * Generate the Error box.
		 *
		 * @since 2.1.23
		 */
		protected function show_error_box( $message ) {
			$error = '<div class="analytify-stats-error-msg">
				<div class="wpb-error-box">
					<span class="blk">
						<span class="line"></span>
						<span class="dot"></span>
					</span>
					<span class="information-txt">'
					. $message .
					'</span>
				</div>
			</div>';

			return $error;
		}

		/**
		 * If error, return cache result else return error.
		 *
		 * @since 2.1.23
		 */
		public function tackle_exception ( $exception, $cache_result ) {

			if ( $cache_result ) {
				return $cache_result;
			}

			echo $this->show_error_box( $exception );
		}

		/**
		 * Set Cache time for Stats.
		 *
		 * @version 5.0.4
		 * @since 2.2.1
		 */
		public function set_cache_time() {
			$this->cache_timeout = $this->settings->get_option( 'delete_dashboard_cache','wp-analytify-dashboard','off' ) === 'on' ? apply_filters( 'analytify_stats_cache_time', 60 * 60 * 10 ) : apply_filters( 'analytify_stats_cache_time', 60 * 60 * 24 );
		}

		/**
		 * Get Cache time for Stats.
		 *
		 * @version 5.0.4
		 *
		 * @since 2.2.1
		 */
		public function get_cache_time() {
			return $this->cache_timeout;
		}

		/**
		 * Check the active/deactive state of addon/moudle.
		 * 
		 * @param string $slug Slug of addon/moudle 
		 * @return string $addon_state: active or deactive
		 */
		public function analytify_module_state( $slug ) {

			$WP_ANALYTIFY = $GLOBALS['WP_ANALYTIFY'];
			$addon_state = '';

			$pro_inner = [
				'detail-realtime',
				'detail-demographic',
				'search-terms',
        		'page-speed',
				'search-console-report',
				'video-tracking'
			];
			$pro_addon = [
				'wp-analytify-woocommerce',
				'wp-analytify-goals',
				'wp-analytify-authors',
				'wp-analytify-edd',
				'wp-analytify-forms',
				'wp-analytify-campaigns'
			];
			$pro_features = [
				'custom-dimensions',
				'events-tracking'
			];

			if ( in_array( $slug, $pro_features ) ) {
				$analytify_modules = get_option( 'wp_analytify_modules' );

				if ( 'active' === $analytify_modules[$slug]['status'] ) {
					$addon_state = 'active';
				}

				$addon_state = 'deactive';
			} elseif ( in_array( $slug, $pro_addon ) || in_array( $slug, $pro_inner ) ) {
				if ( in_array( $slug, $pro_inner ) ) {
					$slug = 'wp-analytify-pro';
				}

				if ( $WP_ANALYTIFY->addon_is_active( $slug ) ) {
					$addon_state = 'active';
				}

				$addon_state = 'deactive';
			}

			return $addon_state;
		}

		/**
		 * Check if external addon is active.
		 * 
		 * @param string $slug Slug of addon 
		 * 
		 * @return bool $addon_active
		 */
		public function addon_is_active( $slug ) {

			$addon_active = false;

			switch ( $slug ) {
				case 'wp-analytify':
					if ( class_exists( 'Analytify_General' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-goals':
					if ( class_exists( 'WP_Analytify_Goals' ) ) {
						$addon_active = true;
					}
					break;
				
				case 'wp-analytify-woocommerce':
					if ( class_exists( 'WP_Analytify_WooCommerce') || class_exists( 'WP_Analytify_WooCommerce_Addon' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-campaigns':
					if ( class_exists( 'ANALYTIFY_PRO_CAMPAIGNS' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-authors':
					if ( class_exists( 'Analytify_Authors' ) || class_exists( 'Analytify_Addon_Authors' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-edd':
					if ( class_exists( 'WP_Analytify_Edd' ) || class_exists( 'WP_Analytify_Edd_Addon' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-forms':
					if ( class_exists( 'Analytify_Forms' ) || class_exists( 'Analytify_Addon_Forms' ) ) {
						$addon_active = true;
					}
					break;

				case 'wp-analytify-pro':
					if ( class_exists( 'WP_Analytify_Pro_Base' ) ) {
						$addon_active = true;
					}
					break;

				default:
					$addon_active = false;
					break;
			}

			return $addon_active;
		}

		/**
		 * Create dashboard navigation anchors.
		 * 
		 * @param array $nav_item Single navigation item data array.
		 * 
		 * @return mixed $anchor
		 */
		private function navigation_anchors( array $nav_item ) {
			
			$current_screen = get_current_screen()->base;
			$current_addon_name = '';

			// Check if child dashboard page for addon/module.
			if ( isset( $_GET['addon'] ) ) {
				$current_addon_name = $_GET['addon'];
			} elseif ( isset( $_GET['show'] ) ) {
				$current_addon_name = $_GET['show'];
			}

			if ( 'pro_feature' === $nav_item['module_type'] ) {
				// Module availbe in pro version as switchable feature.

				$nav_link = $this->addon_is_active( 'wp-analytify-pro' ) && 'active' === $this->modules[ $nav_item['addon_slug'] ]['status'] ? admin_url( 'admin.php?page=' . $nav_item['page_slug'] ) : admin_url( 'admin.php?page=analytify-promo&addon=' . $nav_item['addon_slug'] );
				$active_tab = ( 'analytify_page_' . $nav_item['page_slug'] === $current_screen || $nav_item['addon_slug'] === $current_addon_name ) ? 'nav-tab-active' : '';
			} elseif ( 'pro_inner' === $nav_item['module_type'] ) {
				// Module build in pro version.

				$nav_link = $this->addon_is_active( 'wp-analytify-pro' ) ? admin_url( 'admin.php?page=' . $nav_item['page_slug'] .'&show=' . $nav_item['addon_slug'] ) : admin_url( 'admin.php?page=analytify-promo&addon=' . $nav_item['addon_slug'] );
				$active_tab = ( 'analytify_page_' . $nav_item['page_slug'] === $current_screen || $nav_item['addon_slug'] === $current_addon_name ) ? 'nav-tab-active' : '';
			} elseif ( 'pro_addon' === $nav_item['module_type'] ) {
				// Not inner module, rather a seperate plugin.

				$nav_link = $this->addon_is_active( $nav_item['addon_slug'] ) ? admin_url( 'admin.php?page=' . $nav_item['page_slug'] ) : admin_url( 'admin.php?page=analytify-promo&addon=' . $nav_item['addon_slug'] );
				$active_tab = ( 'analytify_page_' . $nav_item['page_slug'] === $current_screen || $nav_item['addon_slug'] === $current_addon_name ) ? 'nav-tab-active' : '';
			} elseif ( 'free' === $nav_item['module_type'] ) {
				// Free version main dashboard page.
				
				$nav_link = admin_url( 'admin.php?page='. $nav_item['page_slug'] );
				$active_tab = ( 'toplevel_page_' . $nav_item['page_slug'] === $current_screen && empty( $current_addon_name ) ) ? 'nav-tab-active' : '';
			}

			$anchor = '<a href="' . esc_url( $nav_link ) . '" class="analytify_nav_tab ' . $active_tab. '">' . $nav_item['name'];
			$anchor .= (isset($nav_item['sub_name']) AND !empty($nav_item['sub_name'])) ? '<span>'.$nav_item['sub_name'].'</span>' : '';
			$anchor .= '</a>';

			return $anchor;
		}

		/**
		 * Generate dashboard navigation markup.
		 * 
		 * @param array $nav_items Navigation items data array.
		 */
		private function navigation_markup( array $nav_items ) {
			if ( is_array( $nav_items ) && 0 < count( $nav_items ) ) {
				echo '<div class="analytify_nav_tab_wrapper nav-tab-wrapper">';
				echo $this->generate_submenu_markup( $nav_items, 'analytify_nav_tab_wrapper', 'analytify_nav_tab_parent' );
				echo '</div>';
			}
		}

		/**
		 * Create HTML markup for navigation on dashboard.
		 * 
		 * @param array $nav_items Navigation items data array.
		 * @param string $wrapper_classes Class attribute for navigation wrapper.
		 * @param string $list_item_classes Class attribute for list item.
		 * 
		 * @return mixed $markup
		 */
		private function generate_submenu_markup( array $nav_items, $wrapper_classes = false, $list_item_classes = false ) {

			// Hide tabs filter.
			$hide_tabs = apply_filters( 'analytify_hide_dashboard_tabs', array() );
			
			// Wrapper
			$markup = '<ul';
			$markup .= $wrapper_classes ? ' class="'.$wrapper_classes.'"' : '';
			$markup .= '>';

			// Loop over all the menu items
			foreach ( $nav_items as $items ) {

				// Exclude hidden tabs from dashboard as in filter.
				if ( $hide_tabs && in_array( $items['name'], $hide_tabs ) ) {
					continue;
				}

				$markup .= '<li';
				$markup .= $list_item_classes ? ' class="'.$list_item_classes.'"' : '';
				$markup .= '>';

				// generate anchor
				$markup .= $this->navigation_anchors( $items );
				
				// check if the menu has children, then call itself to generate the child menu
				if ( isset( $items['children'] ) && is_array( $items['children'] ) ) {
					$markup .= $this->generate_submenu_markup( $items['children'] );
				}

				$markup .= '</li>';
			}

			// End wrapper
			$markup .= '</ul>';

			return $markup;
		}

		/**
		 * Register dashboard navigation menu.
		 * 
		 */
		public function dashboard_navigation() {

			$nav_items = apply_filters('analytify_filter_navigation_items', array(

				array(
					'name'			=> 'Audience',
					'sub_name'		=> 'Overview',
					'page_slug'		=> 'analytify-dashboard',
					'addon_slug'	=> 'wp-analytify',
					'module_type'	=> 'free',
				),

				array(
					'name'			=> 'Conversions',
					'sub_name'		=> 'All Events',
					'page_slug'		=> 'analytify-forms',
					'addon_slug'	=> 'wp-analytify-forms',
					'module_type'	=> 'pro_addon',
					'children' 		=> array(
						array(
							'name'			=> 'Forms Tracking',
							'sub_name'		=> 'View Forms Analytics',
							'page_slug'		=> 'analytify-forms',
							'addon_slug'	=> 'wp-analytify-forms',
							'module_type'	=> 'pro_addon',
						),
						array(
							'name'			=> 'Events Tracking',
							'sub_name'		=> 'Affiliates, clicks & links tracking',
							'page_slug'		=> 'analytify-events',
							'addon_slug'	=> 'events-tracking',
							'module_type'	=> 'pro_feature',
						),
						array(
							'name'			=> 'Video Tracking',
							'sub_name'		=> 'Track actions, duration & events',
							'page_slug'		=> 'analytify-dashboard',
							'addon_slug'	=> 'video-tracking',
							'module_type'	=> 'pro_inner',
						)
					)
				),

				array(
					'name'			=> 'Acquisition',
					'sub_name'		=> 'Goals, Campaigns',
					'page_slug'		=> 'analytify-campaigns',
					'addon_slug'	=> 'wp-analytify-campaigns',
					'module_type'	=> 'pro_addon',
					'children'		=> array(
						array(
							'name'			=> 'Search Console',
							'sub_name'		=> 'Google Search Console',
							'page_slug'		=> 'analytify-dashboard',
							'addon_slug'	=> 'search-console-report',
							'module_type'	=> 'pro_inner',
						),
						array(
							'name'			=> 'Campaigns',
							'sub_name'		=> 'UTM Overview',
							'page_slug'		=> 'analytify-campaigns',
							'addon_slug'	=> 'wp-analytify-campaigns',
							'module_type'	=> 'pro_addon',
						),
						array(
							'name'			=> 'Goals',
							'sub_name'		=> 'Key Events',
							'page_slug'		=> 'analytify-goals',
							'addon_slug'	=> 'wp-analytify-goals',
							'module_type'	=> 'pro_addon',
						),
						array(
							'name'			=> 'PageSpeed Insights',
							'sub_name'		=> 'Google Web Performance',
							'page_slug'		=> 'analytify-dashboard',
							'addon_slug'	=> 'page-speed',
							'module_type'	=> 'pro_inner',
						),
					)
				),

				array(
					'name'			=> 'Monetization',
					'sub_name'		=> 'Overview',
					'page_slug'		=> 'analytify-woocommerce',
					'addon_slug'	=> 'wp-analytify-woocommerce',
					'module_type'	=> 'pro_addon',
					'clickable'		=> true,
					'children' 		=> array(
						array(
							'name'			=> 'WooCommerce',
							'sub_name'		=> 'eCommerce Stats',
							'page_slug'		=> 'analytify-woocommerce',
							'addon_slug'	=> 'wp-analytify-woocommerce',
							'module_type'	=> 'pro_addon',
						),
						array(
							'name'			=> 'EDD',
							'sub_name'		=> 'Checkout behavior',
							'page_slug'		=> 'edd-dashboard',
							'addon_slug'	=> 'wp-analytify-edd',
							'module_type'	=> 'pro_addon',
						)
					)
				),

				array(
					'name'			=> 'Engagement',
					'sub_name'		=> 'Authors, Dimensions',
					'page_slug'		=> 'analytify-authors',
					'addon_slug'	=> 'wp-analytify-authors',
					'module_type'	=> 'pro_addon',
					'children'		=> array(
						array(
							'name'			=> 'Authors',
							'sub_name'		=> 'Authors Content Overview',
							'page_slug'		=> 'analytify-authors',
							'addon_slug'	=> 'wp-analytify-authors',
							'module_type'	=> 'pro_addon',
						),
						array(
							'name'			=> 'Demographics',
							'sub_name'		=> 'Age, Gender & Interests',
							'page_slug'		=> 'analytify-dashboard',
							'addon_slug'	=> 'detail-demographic',
							'module_type'	=> 'pro_inner',
						),
						array(
							'name'			=> 'Search Terms',
							'sub_name'		=> 'On Site Searches',
							'page_slug'		=> 'analytify-dashboard',
							'addon_slug'	=> 'search-terms',
							'module_type'	=> 'pro_inner',
						),
						array(
							'name'			=> 'Dimensions',
							'sub_name'		=> 'Custom Dimensions',
							'page_slug'		=> 'analytify-dimensions',
							'addon_slug'	=> 'custom-dimensions',
							'module_type'	=> 'pro_feature',
						),
					)
				),

				array(
					'name'			=> 'Real-Time',
					'sub_name'		=> 'Live Stats',
					'page_slug'		=> 'analytify-dashboard',
					'addon_slug'	=> 'detail-realtime',
					'module_type'	=> 'pro_inner',
				)
			));

			$this->navigation_markup( $nav_items );
		}

		/**
		 * Handle caching of GA4 reports data and store it as transient
		 * 
		 * @since 7.0.0
		 * @param string $cache_key The cache key to use
		 * @param mixed $data The data to cache
		 * @param string $name The report name
		 * @param bool $should_cache Whether to cache the data
		 * @return void
		 */
		private function analytify_handle_report_cache($cache_key, $data, $name, $should_cache = true) {
			// Don't cache if caching is disabled or for specific reports
			if (!$should_cache || $name === 'show-worldmap-front') {
				return false;
			}

			// Set the cache with the configured timeout
			set_transient($cache_key, $data, $this->get_cache_time());
		}

	}
}
