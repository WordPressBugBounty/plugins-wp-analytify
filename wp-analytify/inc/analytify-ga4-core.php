<?php
/**
 * GA4 Core Functionality File for Analytify Plugin
 *
 * This file contains all Google Analytics 4 core functionality including
 * stream management, property management, and reporting methods.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GA4 Core Methods for Analytify_General Class
 */
trait Analytify_GA4_Core {

	/**
	 * Get cached data or fetch from API with caching
	 *
	 * @param string   $cache_key The cache key.
	 * @param callable $fetch_callback The callback to fetch data if not cached.
	 * @param int      $cache_timeout Cache timeout in seconds (default: 1 hour).
	 * @return mixed Cached data or fresh data
	 */
	private function get_cached_or_fetch( $cache_key, $fetch_callback, $cache_timeout = 3600 ) {
		// Try to get from cache first.
		$cached_data = get_transient( $cache_key );
		if ( false !== $cached_data ) {
			return $cached_data;
		}

		// Fetch fresh data.
		$fresh_data = $fetch_callback();

		// Cache the data only if it's a valid result (not false or null).
		// Don't cache failure states to allow retry after temporary failures.
		if ( false !== $fresh_data && null !== $fresh_data ) {
			set_transient( $cache_key, $fresh_data, $cache_timeout );
		}

		return $fresh_data;
	}

	/**
	 * Create web stream for Analytify tracking in Google Analytics.
	 * Stream types: Google\Analytics\Admin\V1alpha\DataStream\DataStreamType
	 *
	 * @param string $property_id The GA4 property ID.
	 *
	 * @return array Measurement data.
	 *
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_create_ga_stream( $property_id ) {
		// Use caching for expensive operations.
		$cache_key = 'analytify_ga4_streams_' . $property_id;

		return $this->get_cached_or_fetch(
			$cache_key,
			function () use ( $property_id ) {

				$analytify_ga4_streams = $this->analytify_get_ga4_streams();

				// Check if the stream already exists in the saved option.
				if ( isset( $analytify_ga4_streams ) && isset( $analytify_ga4_streams[ $property_id ] ) && isset( $analytify_ga4_streams[ $property_id ]['measurement_id'] ) ) {
					return $analytify_ga4_streams[ $property_id ];
				}

				// Return if there is no property id given.
				if ( empty( $property_id ) ) {
					return;
				}

				$token = $this->analytify_get_google_token();

				// Validate that token is an array and has the expected structure.
				if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

					return;
				}

				$access_token     = $token['access_token']; // Method to retrieve your OAuth access token.
				$url_list_streams = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/dataStreams';
				$stream_name      = 'Analytify - ' . get_site_url(); // Defined stream name for Analytify.

				$args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
				);

				$measurement_data = array();

				// Try to fetch existing data streams.
				$response = wp_remote_get( $url_list_streams, $args );

				// Log the response for debugging.
				$logger = analytify_get_logger();
				$logger->info( 'Fetching existing streams.', array( 'response' => $response ) );
				if ( class_exists( 'QM' ) ) {
					QM::info( 'Analytify: Fetching existing streams.', array( 'response' => $response ) );
				}

				if ( is_wp_error( $response ) ) {
					$logger->error(
						'Error fetching streams.',
						array(
							'error_message' => $response->get_error_message(),
							'source'        => 'analytify_create_stream_errors',
						)
					);
					return;
				}

				$body             = wp_remote_retrieve_body( $response );
				$decoded_response = json_decode( $body, true );

				// Log the decoded response for debugging.
				$logger->info( 'Decoded response from GA.', array( 'decoded_response' => $decoded_response ) );

				// Check if any existing streams match the Analytify stream.
				if ( isset( $decoded_response['dataStreams'] ) ) {
					foreach ( $decoded_response['dataStreams'] as $stream ) {
						if ( isset( $stream['displayName'] ) && $stream_name === $stream['displayName'] ) {
							$web_stream = $stream;

							// Check if all required nested array elements exist.
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

								// Save the stream data.
								$analytify_ga4_streams[ $property_id ] = $measurement_data;
								update_option( 'analytify-ga4-streams', $analytify_ga4_streams );

								$logger->info( 'Stream found and saved.', array( 'measurement_data' => $measurement_data ) );
								return $measurement_data;
							}
						}
					}
				}

				// If no existing stream found, create a new one.
				$logger->info( 'No existing stream found, creating new one.' );

				$stream_data = array(
					'displayName'   => $stream_name,
					'type'          => 'WEB_DATA_STREAM',
					'webStreamData' => array(
						'defaultUri' => get_site_url(),
					),
				);

				$create_args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $stream_data ),
				);

				$create_response = wp_remote_post( $url_list_streams, $create_args );

				if ( is_wp_error( $create_response ) ) {
					$logger->error( 'Error creating stream.', array( 'error_message' => $create_response->get_error_message() ) );
					return;
				}

				$create_body    = wp_remote_retrieve_body( $create_response );
				$created_stream = json_decode( $create_body, true );

				if ( isset( $created_stream['name'] ) && isset( $created_stream['webStreamData']['measurementId'] ) ) {
					$measurement_data = array(
						'full_name'      => $created_stream['name'],
						'property_id'    => $property_id,
						'stream_name'    => $created_stream['displayName'],
						'measurement_id' => $created_stream['webStreamData']['measurementId'],
						'url'            => $created_stream['webStreamData']['defaultUri'],
					);

					// Save the new stream data.
					$analytify_ga4_streams[ $property_id ] = $measurement_data;
					update_option( 'analytify-ga4-streams', $analytify_ga4_streams );

					$logger->info( 'New stream created and saved.', array( 'measurement_data' => $measurement_data ) );
					return $measurement_data;
				}

				$logger->error( 'Failed to create stream.', array( 'response' => $created_stream ) );
				return false;
			},
			1800
		); // Cache for 30 minutes.
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

			if ( class_exists( 'QM' ) ) {
				QM::warning( 'Analytify: No property ID specified in analytify_get_ga_streams function.' );
			}
			return false;
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
			$logger = analytify_get_logger();

			if ( class_exists( 'QM' ) ) {
				QM::error( 'Analytify: Error: Invalid or missing Google Analytics token in analytify_get_ga_streams.' );
			}
			return null;
		}

		$access_token = $token['access_token']; // Method to retrieve your OAuth access token.
		if ( empty( $access_token ) ) {
			$logger = analytify_get_logger();

			if ( class_exists( 'QM' ) ) {
				QM::error( 'Analytify: Failed to retrieve access token in analytify_get_ga_streams function.' );
			}
			return null;
		}

		// Prepare the request URL and headers.
		$url  = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/dataStreams';
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
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging needed for debugging API failures.
			error_log( 'Error in wp_remote_get (analytify_fetch_ga_streams): ' . $response->get_error_message() );
			return false;
		}

		// Parse the response body.
		$body             = wp_remote_retrieve_body( $response );
		$decoded_response = json_decode( $body, true );

		// Check for JSON parsing errors.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging needed for debugging JSON parsing failures.
			error_log( 'JSON decoding error (analytify_fetch_ga_streams): ' . json_last_error_msg() );
			return false;
		}

		// Check if streams are available.
		if ( isset( $decoded_response['dataStreams'] ) ) {
			$all_streams = array();

			foreach ( $decoded_response['dataStreams'] as $stream ) {
				// Only include web data streams with proper checks.
				if ( isset( $stream['type'] ) && 'WEB_DATA_STREAM' === $stream['type'] &&
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
			$ga4_streams                 = $this->analytify_get_ga4_streams();
			$ga4_streams[ $property_id ] = $all_streams;

			// Check if update_option is successful.
			$update_result = update_option( 'analytify-ga4-streams', $ga4_streams );

			return $all_streams; // Return the list of streams.
		}

		return null; // Return null if no streams found.
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
			printf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Error: Invalid or missing Google Analytics token. Please re-authenticate.', 'wp-analytify' ) );
			return array();
		}

		$access_token = $token['access_token'];

		if ( ! $access_token ) {
			printf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Error: Unable to authenticate with Google Analytics.', 'wp-analytify' ) );
			return array();
		}

		// Check if token is expired and try to refresh.
		$expires_in = isset( $token['expires_in'] ) ? absint( $token['expires_in'] ) : 0;
		$created_at = isset( $token['created_at'] ) ? absint( $token['created_at'] ) : 0;

		if ( $expires_in > 0 && $created_at > 0 && ( time() - $created_at ) >= $expires_in ) {
			// Token has expired, try to refresh it.
			$refreshed_token = $this->analytify_pa_connect_v2();
			if ( $refreshed_token ) {
				$access_token = $refreshed_token;
				// Update the token in the class property.
				$this->token = $access_token;
			} else {
				printf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Error: Token expired and could not be refreshed. Please re-authenticate.', 'wp-analytify' ) );
				return array();
			}
		}

		$accounts = array();
		try {
			if ( $this->get_ga4_exception() ) {
				WPANALYTIFY_Utils::handle_exceptions( $this->get_ga4_exception() );
			}

			if ( get_option( 'pa_google_token' ) !== '' ) {
				$accounts = $this->analytify_list_accounts( $access_token );
			} else {
				printf( '<br /><div class="notice notice-warning"><p>%s</p></div>', esc_html__( 'Notice: You must authenticate to access your web profiles.', 'wp-analytify' ) );
				return array();
			}
		} catch ( Exception $e ) {
			$error_message = $e->getMessage();
			$logger        = analytify_get_logger();
			$logger->warning( $error_message, array( 'source' => 'analytify_analytify_get_ga_properties_errors' ) );
			if ( class_exists( 'QM' ) ) {
				QM::warning( 'Analytify: ' . $error_message, array( 'source' => 'analytify_analytify_get_ga_properties_errors' ) );
			}
			return array();
		}

		$ga_properties = array();

		foreach ( $accounts as $account ) {
			$account_name  = $account['name'];  // e.g., 'accounts/123456'.
			$properties    = $this->analytify_list_properties( $access_token, $account_name );
			$property_data = array();

			foreach ( $properties as $property ) {
				// Extract property ID in a similar way to the previous code.
				$id = explode( '/', $property['name'] );
				$id = isset( $id[1] ) ? intval( $id[1] ) : intval( $property['name'] );

				$property_data[] = array(
					'id'           => $id,
					'name'         => $property['name'],
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
	 * Retrieve and list Google Analytics accounts using the provided access token.
	 *
	 * This function interacts with the Google Analytics API to fetch a list of accounts
	 * associated with the given access token. It is used to display or process the accounts
	 * linked to the authenticated user.
	 *
	 * @since 7.0.0
	 * @version 7.0.3
	 * @param string $access_token The access token for authenticating with the Google Analytics API.
	 * @return array List of accounts.
	 */
	private function analytify_list_accounts( $access_token ) {
		// Early bail if access_token is empty.
		if ( empty( $access_token ) ) {

			return array();
		}

		$url        = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/accounts';
		$accounts   = array();
		$page_token = '';

		do {
			// Build URL with pageToken if needed.
			$request_url = $url;
			if ( ! empty( $page_token ) ) {
				$request_url .= '?pageToken=' . rawurlencode( $page_token );
			}

			$response = wp_remote_get(
				$request_url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				)
			);

			if ( is_wp_error( $response ) ) {

				return $accounts;
			}

			if ( ! is_array( $response ) ) {

				return $accounts;
			}

			$raw_body = wp_remote_retrieve_body( $response );
			$body     = json_decode( $raw_body, true );

			if ( isset( $body['accounts'] ) && is_array( $body['accounts'] ) ) {
				$accounts = array_merge( $accounts, $body['accounts'] );
			}

			// If nextPageToken exists, loop again.
			$page_token = isset( $body['nextPageToken'] ) ? $body['nextPageToken'] : '';

		} while ( ! empty( $page_token ) );

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
		$url = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/properties?filter=parent:' . $account_name . '&pageSize=1000';

		$all_properties = array();
		$page_token     = '';
		$max_pages      = 10; // Prevent infinite loops.
		$current_page   = 0;

		do {
			++$current_page;
			if ( $current_page > $max_pages ) {

				break;
			}

			$final_url = $url;
			if ( $page_token ) {
				$final_url .= '&pageToken=' . $page_token;
			}

			$response = wp_remote_get(
				$final_url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
					'timeout' => 60, // Add timeout for performance.
				)
			);

			if ( is_wp_error( $response ) ) {

				break;
			}

			if ( ! is_array( $response ) ) {

				break;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $response_code ) {

				break;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( isset( $body['properties'] ) && is_array( $body['properties'] ) ) {
				// Use array_merge more efficiently by collecting all properties first.
				$all_properties = array_merge( $all_properties, $body['properties'] );
			}

			$page_token = isset( $body['nextPageToken'] ) ? $body['nextPageToken'] : '';

		} while ( $page_token && $current_page < $max_pages );

		return $all_properties;
	}

	/**
	 * Get Measurement Protocol Secret for GA4 tracking.
	 *
	 * @param string $formatted_name The formatted name of the stream.
	 * @return array|false Array containing the secret data or false on failure.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_get_mp_secret( $formatted_name ) {
		// Validate input parameter.
		if ( empty( $formatted_name ) ) {

			return false;
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

			return false;
		}

		$access_token = $token['access_token'];

		// Validate that access token is not empty.
		if ( empty( $access_token ) ) {

			return false;
		}

		// Prepare the request URL for the Measurement Protocol Secret.
		$url = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/' . $formatted_name . '/measurementProtocolSecrets';

		// Set up the request arguments.
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
		);

		// Make the API call to get the Measurement Protocol Secret.
		$response = wp_remote_get( $url, $args );

		// Check for errors in the response.
		if ( is_wp_error( $response ) ) {

			return false;
		}

		// Parse the response body.
		$body             = wp_remote_retrieve_body( $response );
		$decoded_response = json_decode( $body, true );

		// Check if the response contains the secret.
		if ( isset( $decoded_response['measurementProtocolSecrets'] ) &&
			is_array( $decoded_response['measurementProtocolSecrets'] ) &&
			! empty( $decoded_response['measurementProtocolSecrets'] ) ) {

			// Return the first secret found.
			return $decoded_response['measurementProtocolSecrets'][0];
		}

		// If no secret found, try to create one.
		try {
			$create_secret_url  = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/' . $formatted_name . '/measurementProtocolSecrets';
			$create_secret_body = array(
				'displayName' => 'Analytify MP Secret',
			);

			$create_args = array(
				'method'  => 'POST',
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $create_secret_body ),
			);

			$create_response = wp_remote_post( $create_secret_url, $create_args );

			if ( is_wp_error( $create_response ) ) {

				return false;
			}

			$create_body    = wp_remote_retrieve_body( $create_response );
			$created_secret = json_decode( $create_body, true );

			if ( isset( $created_secret['secretValue'] ) ) {
				return $created_secret;
			}
		} catch ( Exception $e ) {

			return false;
		}

		return false;
	}

	/**
	 * Create Measurement Protocol Secret for GA4 tracking.
	 *
	 * @param string $property_id The property ID.
	 * @param string $formatted_name The formatted name of the stream.
	 * @param string $measurement_id The measurement ID.
	 * @return array|false Array containing the secret data or false on failure.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_create_mp_secret( $property_id, $formatted_name, $measurement_id ) {
		// Validate input parameters.
		if ( empty( $formatted_name ) || empty( $measurement_id ) ) {

			return false;
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

			return false;
		}

		$access_token = $token['access_token'];

		// Validate that access token is not empty.
		if ( empty( $access_token ) ) {

			return false;
		}

		// Prepare the request URL for creating the Measurement Protocol Secret.
		$url = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/' . $formatted_name . '/measurementProtocolSecrets';

		// Set up the request body.
		$body = array(
			'displayName' => 'Analytify MP Secret - ' . $measurement_id,
		);

		// Set up the request arguments.
		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		);

		// Make the API call to create the Measurement Protocol Secret.
		$response = wp_remote_post( $url, $args );

		// Check for errors in the response.
		if ( is_wp_error( $response ) ) {

			return false;
		}

		// Parse the response body.
		$response_body  = wp_remote_retrieve_body( $response );
		$created_secret = json_decode( $response_body, true );

		// Check if the secret was created successfully.
		if ( isset( $created_secret['secretValue'] ) ) {
			// Save the secret to the database for future reference.
			$mp_secrets                    = get_option( 'analytify_mp_secrets', array() );
			$mp_secrets[ $measurement_id ] = array(
				'secret_value' => $created_secret['secretValue'],
				'name'         => $created_secret['name'],
				'display_name' => $created_secret['displayName'],
				'created_at'   => current_time( 'mysql' ),
			);
			update_option( 'analytify_mp_secrets', $mp_secrets );

			return $created_secret;
		}

		return false;
	}

	/**
	 * Get real-time reports from Google Analytics 4.
	 *
	 * @param array $metrics Array of metrics to fetch.
	 * @param array $dimensions Array of dimensions to fetch.
	 * @return array|false Array containing the real-time data or false on failure.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function get_real_time_reports( $metrics, $dimensions = array() ) {
		$property_id = WPANALYTIFY_Utils::get_reporting_property();

		if ( empty( $property_id ) ) {

			return array(
				'rows'   => array(),
				'totals' => array(),
			);
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

			return array(
				'rows'   => array(),
				'totals' => array(),
			);
		}

		$access_token = $token['access_token'];

		// Validate that access token is not empty.
		if ( empty( $access_token ) ) {

			return array(
				'rows'   => array(),
				'totals' => array(),
			);
		}

		// Prepare the request URL for real-time reports.
		$url = 'https://analyticsdata.googleapis.com/v1beta/properties/' . $property_id . ':runRealtimeReport';

		// Prepare the request body.
		$request_body = array(
			'metrics'    => array(),
			'dimensions' => array(),
		);

		// Add metrics.
		if ( ! empty( $metrics ) ) {
			foreach ( $metrics as $metric ) {
				$request_body['metrics'][] = array( 'name' => $metric );
			}
		}

		// Add dimensions.
		if ( ! empty( $dimensions ) ) {
			foreach ( $dimensions as $dimension ) {
				$request_body['dimensions'][] = array( 'name' => $dimension );
			}
		}

		// Set up the request arguments.
		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $request_body ),
		);

		// Make the API call to get real-time reports.
		$response = wp_remote_post( $url, $args );

		// Check for errors in the response.
		if ( is_wp_error( $response ) ) {

			return array(
				'rows'   => array(),
				'totals' => array(),
			);
		}

		// Parse the response body.
		$response_body = wp_remote_retrieve_body( $response );
		$realtime_data = json_decode( $response_body, true );

		// Check if the response contains real-time data.
		if ( isset( $realtime_data['rows'] ) || isset( $realtime_data['totals'] ) ) {
			return $realtime_data;
		}

		// Return empty array structure when no data is available.
		return array(
			'rows'   => array(),
			'totals' => array(),
		);
	}

	/**
	 * List custom dimensions for GA4 property.
	 *
	 * @return array|false Array of dimensions or false on failure.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_list_dimensions() {
		$property_id = WPANALYTIFY_Utils::get_reporting_property();

		if ( empty( $property_id ) ) {

			return false;
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

			return false;
		}

		$access_token = $token['access_token'];

		// Validate that access token is not empty.
		if ( empty( $access_token ) ) {

			return false;
		}

		// Prepare the request URL for listing dimensions.
		$url = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/customDimensions';

		// Set up the request arguments.
		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
		);

		// Make the API call to list dimensions.
		$response = wp_remote_get( $url, $args );

		// Check for errors in the response.
		if ( is_wp_error( $response ) ) {

			return false;
		}

		// Parse the response body.
		$response_body   = wp_remote_retrieve_body( $response );
		$dimensions_data = json_decode( $response_body, true );

		// Check if the response contains dimensions.
		if ( isset( $dimensions_data['customDimensions'] ) ) {
			return $dimensions_data['customDimensions'];
		}

		return array();
	}

	/**
	 * Check if dimensions need to be created.
	 *
	 * @return array Array of dimensions that need creation.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_list_dimensions_needs_creation() {
		$current_property_dimensions = $this->analytify_list_dimensions();

		// Reduced list of essential dimensions to avoid hitting GA4 resource limits.
		// GA4 has a limit of 50 custom dimensions per property.
		$required_dimensions = array(
			array(
				'parameter_name' => 'page_title',
				'display_name'   => 'Page Title',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'page_path',
				'display_name'   => 'Page Path',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'user_type',
				'display_name'   => 'User Type',
				'scope'          => 'USER',
			),
			array(
				'parameter_name' => 'device_category',
				'display_name'   => 'Device Category',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'country',
				'display_name'   => 'Country',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'source',
				'display_name'   => 'Source',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'medium',
				'display_name'   => 'Medium',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'campaign',
				'display_name'   => 'Campaign',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'landing_page',
				'display_name'   => 'Landing Page',
				'scope'          => 'EVENT',
			),
			array(
				'parameter_name' => 'exit_page',
				'display_name'   => 'Exit Page',
				'scope'          => 'EVENT',
			),
		);

		// If no existing dimensions, all required dimensions need to be created.
		if ( empty( $current_property_dimensions ) ) {
			return $required_dimensions;
		}

		$existing_dimension_names = array();
		foreach ( $current_property_dimensions as $dimension ) {
			if ( isset( $dimension['parameterName'] ) ) {
				$existing_dimension_names[] = $dimension['parameterName'];
			}
		}

		$dimensions_to_create = array();
		foreach ( $required_dimensions as $required_dimension ) {
			if ( ! in_array( $required_dimension['parameter_name'], $existing_dimension_names, true ) ) {
				$dimensions_to_create[] = $required_dimension;
			}
		}

		return $dimensions_to_create;
	}

	/**
	 * Create custom dimension for GA4 property.
	 *
	 * @param string  $parameter_name The parameter name of the dimension to create.
	 * @param string  $display_name The display name of the dimension.
	 * @param string  $scope The scope of the dimension (EVENT, USER, etc.).
	 * @param string  $description Description of the dimension, max length 150 characters.
	 * @param integer $property_id Reporting property ID to associate dimension, default is current reporting property.
	 * @return array Array containing the response status.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_create_dimension( $parameter_name, $display_name, $scope, $description = '', $property_id = '' ) {
		// Get the property ID, if not provided.
		$property_id = ! empty( $property_id ) ? $property_id : WPANALYTIFY_Utils::get_reporting_property();

		if ( empty( $property_id ) || empty( $parameter_name ) ) {

			return array(
				'response' => 'failed',
				'message'  => 'Property ID or parameter name is empty',
			);
		}

		// Get the access token for authentication.
		$token = $this->analytify_get_google_token();

		// Validate that token is an array and has the expected structure.
		if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {

			return array(
				'response' => 'failed',
				'message'  => 'Invalid or missing Google Analytics token',
			);
		}

		$access_token = $token['access_token'];

		// Validate that access token is not empty.
		if ( empty( $access_token ) ) {

			return array(
				'response' => 'failed',
				'message'  => 'Access token is missing',
			);
		}

		// Prepare the request URL for creating dimensions.
		$url = WP_ANALYTIFY_GA_ADMIN_API_BASE . '/properties/' . $property_id . '/customDimensions';

		// Set up the request body.
		$body = array(
			'parameterName' => $parameter_name,
			'displayName'   => $display_name,
			'description'   => ! empty( $description ) ? $description : 'Analytify custom dimension.',
			'scope'         => $scope,
		);

		// Set up the request arguments.
		$args = array(
			'method'  => 'POST',
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
		);

		$return_response = array(
			'response' => 'created',
		);

		// Send the API request.
		$response = wp_remote_post( $url, $args );

		// Handle the response.
		if ( is_wp_error( $response ) ) {
			$logger = analytify_get_logger();
			$logger->warning( $response->get_error_message(), array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
			if ( class_exists( 'QM' ) ) {
				QM::warning( 'Analytify: ' . $response->get_error_message(), array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
			}
			return array(
				'response' => 'failed',
				'message'  => $response->get_error_message(),
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$body         = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
			$message      = isset( $decoded_body['error']['message'] ) ? $decoded_body['error']['message'] : 'Unknown error';

			// Check if we've hit the GA4 resource limit.
			if ( strpos( $message, 'maximum resource limit' ) !== false || strpos( $message, 'resource limit' ) !== false ) {

				return array(
					'response' => 'skipped',
					'message'  => 'GA4 resource limit reached - dimension creation skipped',
				);
			}

			$logger = analytify_get_logger();
			$logger->warning( $message, array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
			if ( class_exists( 'QM' ) ) {
				QM::warning( 'Analytify: ' . $message, array( 'source' => 'analytify_analytify_create_dimension_errors' ) );
			}

			return array(
				'response' => 'failed',
				'message'  => $message,
			);
		}

		// Return the response.
		return $return_response;
	}

	/**
	 * Format reports data fetched from Google Analytics Data API.
	 *
	 * For references check folder for class definitions: lib\Google\vendor\google\analytics-data\src\V1beta
	 *
	 * @param array $reports The raw reports data from GA4 API.
	 * @return array Formatted reports data.
	 * @since 5.0.0
	 * @version 7.0.1
	 */
	public function analytify_format_ga_reports( $reports ) {
		$metric_header_data    = array();
		$dimension_header_data = array();
		$aggregations          = array();
		$rows                  = array();

		// Get metric headers.
		if ( isset( $reports['metricHeaders'] ) ) {
			foreach ( $reports['metricHeaders'] as $metric_header ) {
				$metric_header_data[] = $metric_header['name'];
			}
		}

		// Get dimension headers.
		if ( isset( $reports['dimensionHeaders'] ) ) {
			foreach ( $reports['dimensionHeaders'] as $dimension_header ) {
				$dimension_header_data[] = $dimension_header['name'];
			}
		}

		$headers = array_merge( $metric_header_data, $dimension_header_data );

		// Bind metrics and dimensions to rows.
		if ( isset( $reports['rows'] ) ) {
			foreach ( $reports['rows'] as $row ) {
				$metric_data    = array();
				$dimension_data = array();

				// Process metric values.
				if ( isset( $row['metricValues'] ) ) {
					$index_metric = 0;
					foreach ( $row['metricValues'] as $value ) {
						$metric_data[ $metric_header_data[ $index_metric ] ] = $value['value'];
						++$index_metric;
					}
				}

				// Process dimension values.
				if ( isset( $row['dimensionValues'] ) ) {
					$index_dimension = 0;
					foreach ( $row['dimensionValues'] as $value ) {
						$dimension_data[ $dimension_header_data[ $index_dimension ] ] = $value['value'];
						++$index_dimension;
					}
				}

				// Combine metric and dimension data.
				$rows[] = array_merge( $metric_data, $dimension_data );
			}
		}

		// Get metric aggregations (totals).
		if ( isset( $reports['totals'] ) ) {
			foreach ( $reports['totals'] as $total ) {
				$index_metric = 0;

				if ( isset( $total['metricValues'] ) ) {
					foreach ( $total['metricValues'] as $value ) {
						$aggregations[ $metric_header_data[ $index_metric ] ] = $value['value'];
						++$index_metric;
					}
				}
			}
		}

		// Format and return the data.
		$formatted_data = array(
			'headers'      => $headers,
			'rows'         => $rows,
			'aggregations' => $aggregations,
		);

		return $formatted_data;
	}
}
