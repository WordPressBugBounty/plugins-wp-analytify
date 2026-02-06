<?php
/**
 * Authentication File for Analytify Plugin
 *
 * This file contains all authentication-related functionality including
 * OAuth connection, token management, refresh tokens, and Google API
 * authentication methods.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Authentication Methods for Analytify_General Class
 * since 8.0
 */
trait Analytify_Authentication {

	/**
	 * Update authentication date with current timestamp.
	 *
	 * @since 7.0.0
	 * @return void
	 */
	private function analytify_update_authentication_date() {
		$this->auth_date_format = gmdate( 'l jS F Y h:i:s A' ) . ' ' . date_default_timezone_get();
		update_option( 'analytify_authentication_date', $this->auth_date_format );
	}

	/**
	 * Get Google token data from options.
	 *
	 * @since 7.0.0
	 * @return array|false Token data or false if not found
	 */
	public function analytify_get_google_token() {
		if ( empty( $this->google_token ) ) {
			$this->google_token = get_option( 'pa_google_token' );
		}
		return $this->google_token;
	}

	/**
	 * Update Google token data in options and class variable.
	 *
	 * @since 7.0.0
	 * @param array $token_data Token data to save.
	 * @return void
	 */
	private function analytify_update_google_token( $token_data ) {
		$this->google_token = $token_data;
		update_option( 'pa_google_token', $token_data );
	}

	/**
	 * Get GA4 streams data from options.
	 *
	 * @since 7.0.0
	 * @return array GA4 streams data
	 */
	public function analytify_get_ga4_streams() {
		if ( empty( $this->ga4_streams ) ) {
			$this->ga4_streams = get_option( 'analytify-ga4-streams', array() );
		}
		return $this->ga4_streams;
	}

	/**
	 * Check the tracking method.
	 *
	 * @return void
	 */
	public function analytify_set_tracking_mode() {
		if ( ! defined( 'WP_ANALYTIFY_TRACKING_MODE' ) ) {
			define( 'WP_ANALYTIFY_TRACKING_MODE', $this->settings->get_option( 'gtag_tracking_mode', 'wp-analytify-advanced', 'gtag' ) );
		}
	}

	/**
	 * Connect with Google Analytics API and get authentication token and save it.
	 *
	 * @since 6.0.0
	 * @version 7.1.2
	 *
	 * @return string|false|null Access token, false on error, or null if no auth code.
	 */
	public function analytify_pa_connect_v2() {

		// Retrieve stored token data.
		$token_data    = $this->analytify_get_google_token();
		$auth_code     = get_option( 'post_analytics_token' );
		$refresh_token = ! empty( $token_data['refresh_token'] ) ? $token_data['refresh_token'] : null;
		$expires_in    = isset( $token_data['expires_in'] ) ? (int) $token_data['expires_in'] : 0;
		$token_time    = isset( $token_data['created_at'] ) ? (int) $token_data['created_at'] : 0;

		// Return valid access token if available.
		if ( ! empty( $token_data['access_token'] ) && ( 0 === $expires_in || ( time() - $token_time ) < $expires_in ) ) {
			return $token_data['access_token'];
		}

		// Try refreshing using refresh token.
		if ( ! empty( $refresh_token ) ) {
			$access_token_data = $this->analytify_refresh_access_token( $refresh_token );
			if ( $access_token_data && ! empty( $access_token_data['access_token'] ) ) {
				$this->token = $access_token_data['access_token'];
				return $access_token_data['access_token'];
			}
			return null;
		}

		// Fallback: use authorization code.
		if ( empty( $auth_code ) ) {
			return null;
		}

		try {
			$token_uri          = WP_ANALYTIFY_TOKEN_URL;
			$token_request_data = array(
				'client_id'     => WP_ANALYTIFY_CLIENTID,
				'client_secret' => WP_ANALYTIFY_CLIENTSECRET,
				'code'          => $auth_code,
				'redirect_uri'  => WP_ANALYTIFY_REDIRECT,
				'grant_type'    => 'authorization_code',
				'access_type'   => 'offline',
			);

			$response = wp_remote_post(
				$token_uri,
				array(
					'body'    => $token_request_data,
					'headers' => array( 'Referer' => ANALYTIFY_VERSION ),
				)
			);

			if ( is_wp_error( $response ) ) {
				if ( ! get_transient( 'analytify_token_request_error_logged' ) ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Transient-based rate limiting for error logging.
					error_log( 'Error: Failed to send token request.' );
					set_transient( 'analytify_token_request_error_logged', true, 24 * HOUR_IN_SECONDS );
				}
				return false;
			}

			$body              = wp_remote_retrieve_body( $response );
			$access_token_data = json_decode( $body, true );

			if ( ! empty( $access_token_data['access_token'] ) ) {
				$access_token_data['created_at'] = time();
				$this->analytify_update_google_token( $access_token_data );
				$this->analytify_update_authentication_date();
				// Reset email notification flag on successful re-authentication.
				delete_option( 'analytify_token_refresh_failed_email_sent' );
				$this->token = $access_token_data['access_token'];
				return $access_token_data['access_token'];
			} else {
				if ( ! get_transient( 'analytify_token_response_error_logged' ) ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Transient-based rate limiting for error logging.
					error_log( 'Error: Access token not found in response.' );
					set_transient( 'analytify_token_response_error_logged', true, 24 * HOUR_IN_SECONDS );
				}
				return false;
			}
		} catch ( Exception $e ) {
			if ( ! get_transient( 'analytify_token_exception_error_logged' ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Transient-based rate limiting for error logging.
				error_log( 'Analytify (Error): ' . $e->getMessage() );
				set_transient( 'analytify_token_exception_error_logged', true, 24 * HOUR_IN_SECONDS );
			}
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
	 * @version 8.0.0
	 *
	 * @param string $refresh_token The refresh token used to obtain a new access token.
	 * @return mixed The new access token or an error response if the refresh fails.
	 */
	public function analytify_refresh_access_token( $refresh_token ) {
		if ( empty( $refresh_token ) ) {
			return false;
		}

		$token_uri    = WP_ANALYTIFY_TOKEN_URL;
		$request_data = array(
			'client_id'     => WP_ANALYTIFY_CLIENTID,
			'client_secret' => WP_ANALYTIFY_CLIENTSECRET,
			'refresh_token' => $refresh_token,
			'grant_type'    => 'refresh_token',
		);

		$response = wp_remote_post(
			$token_uri,
			array(
				'body'    => $request_data,
				'headers' => array( 'Referer' => ANALYTIFY_VERSION ),
			)
		);

		if ( is_wp_error( $response ) ) {
			if ( ! get_transient( 'analytify_token_error_logged' ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Error: Failed to refresh access token. WP_Error: ' . $response->get_error_message() );
				set_transient( 'analytify_token_error_logged', true, HOUR_IN_SECONDS );
			}
			return false;
		}

		$response_code     = wp_remote_retrieve_response_code( $response );
		$body              = wp_remote_retrieve_body( $response );
		$access_token_data = json_decode( $body, true );

		if ( 200 !== $response_code || empty( $access_token_data['access_token'] ) ) {
			$error_message = "HTTP {$response_code}: Failed to refresh access token. Response: {$body}";

			// Check if email notification is enabled in Advanced settings.
			$advanced_settings = get_option( 'wp-analytify-advanced', array() );
			$email_enabled     = isset( $advanced_settings['enable_token_refresh_failure_email'] ) && 'on' === $advanced_settings['enable_token_refresh_failure_email'];

			// Send one-time email notification if enabled and not already sent.
			if ( $email_enabled ) {
				$email_already_sent = get_option( 'analytify_token_refresh_failed_email_sent', false );

				if ( ! $email_already_sent ) {
					$site_name = get_bloginfo( 'name' );
					$site_url  = home_url();

					// Default email arguments.
					$default_mail_args = array(
						'to'      => get_option( 'admin_email' ),
						'subject' => sprintf(
							/* translators: %s: Site name */
							__( '[%s] Analytify: Google Analytics Token Refresh Failed', 'wp-analytify' ),
							$site_name
						),
						'message' => sprintf(
							/* translators: 1: Site name, 2: Site URL, 3: Error message, 4: Settings URL */
							__(
								'Hello,

Your Google Analytics token refresh has failed on %1$s (%2$s).

Error details: %3$s

Please re-authenticate your Google Analytics connection in the Analytify settings to restore functionality.

You can access the settings here: %4$s

This is an automated notification from Analytify.',
								'wp-analytify'
							),
							$site_name,
							$site_url,
							wp_kses( $error_message, array() ),
							admin_url( 'admin.php?page=analytify-settings' )
						),
						'headers' => array( 'Content-Type: text/plain; charset=UTF-8' ),
					);

					/**
					 * Filter email arguments for token refresh failure notification.
					 *
					 * @since 8.0.0
					 * @param array  $mail_args     Email arguments. Keys: to, subject, message, headers.
					 * @param string $error_message Error message describing the token refresh failure.
					 * @return array Filtered email arguments with keys: to, subject, message, headers.
					 */
					$mail_args = apply_filters( 'analytify_token_refresh_failed_email_args', $default_mail_args, $error_message );

					// Ensure filter result is an array.
					if ( ! is_array( $mail_args ) ) {
						$mail_args = $default_mail_args;
					}

					// Parse with defaults to ensure all required keys exist.
					$mail_args = wp_parse_args( $mail_args, $default_mail_args );

					// Validate and sanitize recipient(s).
					$recipients = $mail_args['to'];
					if ( is_string( $recipients ) ) {
						// Handle comma-separated emails.
						$recipients = array_map( 'trim', explode( ',', $recipients ) );
					} elseif ( ! is_array( $recipients ) ) {
						$recipients = array();
					}

					// Sanitize each email address and filter out empty/invalid ones.
					$sanitized_recipients = array();
					foreach ( $recipients as $recipient ) {
						$sanitized = sanitize_email( $recipient );
						if ( ! empty( $sanitized ) && is_email( $sanitized ) ) {
							$sanitized_recipients[] = $sanitized;
						}
					}

					// Defensive: Skip wp_mail if no valid recipients.
					if ( empty( $sanitized_recipients ) ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logging skipped email due to invalid recipients.
						error_log( 'Analytify: Token refresh failure email skipped - no valid recipients' );
					} else {
						// Sanitize subject.
						$subject = wp_strip_all_tags( $mail_args['subject'] );

						// Validate and sanitize headers.
						$headers = $mail_args['headers'];
						if ( is_string( $headers ) ) {
							$headers = array( $headers );
						} elseif ( ! is_array( $headers ) ) {
							$headers = array();
						}

						// Sanitize header strings.
						$sanitized_headers = array();
						foreach ( $headers as $header ) {
							if ( is_string( $header ) && ! empty( trim( $header ) ) ) {
								$sanitized_headers[] = sanitize_text_field( $header );
							}
						}

						// Convert recipients array to comma-separated string for wp_mail.
						$to = implode( ',', $sanitized_recipients );

						// Send email.
						$email_sent = wp_mail( $to, $subject, $mail_args['message'], $sanitized_headers );

						if ( $email_sent ) {
							// Store flag in separate option only if email succeeds.
							update_option( 'analytify_token_refresh_failed_email_sent', true );
						} else {
							// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logging wp_mail failure.
							error_log( 'Analytify: Token refresh failure email failed to send via wp_mail' );
						}
					}
				}
			}

			if ( ! apply_filters( 'analytify_suppress_default_token_error_log', false, $error_message ) ) {
				if ( ! get_transient( 'analytify_token_error_logged' ) ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Transient-based rate limiting for error logging.
					error_log( 'Analytify: Token refresh failed - ' . $error_message );
					set_transient( 'analytify_token_error_logged', true, DAY_IN_SECONDS );
				}
			}

			return false;
		}

		// Merge with existing token data and save.
		$existing_token_data = $this->analytify_get_google_token();
		if ( ! is_array( $existing_token_data ) ) {
			$existing_token_data = array();
		}
		$updated_token_data = array_merge(
			$existing_token_data,
			array(
				'access_token' => $access_token_data['access_token'],
				'expires_in'   => $access_token_data['expires_in'],
				'created_at'   => time(),
			)
		);

		$this->analytify_update_google_token( $updated_token_data );
		$this->analytify_update_authentication_date();
		// Reset email notification flag on successful token refresh.
		delete_option( 'analytify_token_refresh_failed_email_sent' );

		return $updated_token_data;
	}

	/**
	 * Connect with Google Analytics admin API.
	 *
	 * @return array|null API connection details or null on failure
	 * @version 7.0.1
	 */
	private function analytify_connect_admin_api() {

		try {
			// Get a fresh access token using the refresh token.
			$token  = $this->analytify_get_google_token();
			$logger = analytify_get_logger();
			if ( class_exists( 'QM' ) ) {
				QM::info( 'Analytify: Getting Google Analytics token for GA4 web properties.' );
			}

			// Validate that token is an array and has the expected structure.
			if ( ! is_array( $token ) || ! isset( $token['access_token'] ) ) {
				$logger->warning( 'Error: Invalid or missing Google Analytics token in analytify_list_ga4_web_properties.' );
				if ( class_exists( 'QM' ) ) {
					QM::warning( 'Analytify: Error: Invalid or missing Google Analytics token in analytify_list_ga4_web_properties.' );
				}
				return array();
			}

			$access_token = $token['access_token'];

			// Set the headers for the API request.
			$headers = array(
				"Authorization: Bearer $access_token",
				'Content-Type: application/json',
			);

			// Define the base API URL for Google Analytics Admin API.
			$api_base_url = WP_ANALYTIFY_GA_ADMIN_API_BASE;

			// Log the API base URL for debugging purposes.

			return array(
				'api_base_url' => $api_base_url,
				'headers'      => $headers,
			);
		} catch ( Exception $e ) {
			// Log the error message for debugging purposes.
			// error_log( 'Error connecting to Google Analytics Admin API: ' . $e->getMessage() );.
			return null;
		}
	}

	/**
	 * Get a fresh access token.
	 *
	 * @since 7.0.0
	 */
	public function analytify_get_fresh_access_token() {
		// Load the token from your storage.
		$auth_token = $this->client->getAccessToken();

		// Extract the created time and expires_in value.
		$created_time = $auth_token['created'];
		$expires_in   = $auth_token['expires_in'];

		// Get the current time.
		$current_time = time();

		// Check if the token has expired.
		if ( ( $created_time + $expires_in ) < $current_time ) {
			// Token has expired, refresh it.
			if ( $this->client->isAccessTokenExpired() ) {
				$this->client->fetchAccessTokenWithRefreshToken( $this->client->getRefreshToken() );

				// Save the new token to your storage.
				$new_token = $this->client->getAccessToken();
			}
		}

		// Return the access token (fresh or existing).
		$auth_token = $this->client->getAccessToken();
		return $auth_token['access_token'];
	}
}
