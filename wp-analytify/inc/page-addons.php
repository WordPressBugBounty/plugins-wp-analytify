<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable for this plugin structure.
/**
 * Addons Page Handler
 *
 * @package WP_Analytify
 */

// phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable

if ( ! class_exists( 'WP_Analytify_Addons' ) ) {

	/**
	 * WP Analytify Addons Class
	 */
	class WP_Analytify_Addons {

		/**
		 * List of plugins.
		 *
		 * @var array<string, mixed>
		 */
		protected $plugins_list;

		/**
		 * List of modules.
		 *
		 * @var array<string, mixed>
		 */
		protected $modules_list;

		/**
		 * Constructor
		 *
		 * @version 7.0.5
		 * @return void
		 */
		public function __construct() {
			$this->plugins_list = get_plugins();
			$this->modules_list = $this->modules();

			// Use priority 20 to ensure it runs after scripts-styles.php (which uses default priority 10).
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 20 );
		}

		/**
		 * Returns a list of addons
		 *
		 * @return array<string, mixed>
		 * @since 1.3
		 */
		public function addons() {
			if ( ! class_exists( 'WP_Analytify_Pro' ) || ( defined( 'ANALYTIFY_PRO_VERSION' ) && version_compare( ANALYTIFY_PRO_VERSION, '6.0.0', '<' ) ) ) {

				// Get the transient where the addons are stored on-site.
				$data = get_transient( 'analytify_api_addons' );

				// If we already have data, return it.
				if ( ! empty( $data ) && is_array( $data ) && count( $data ) > 0 ) {
					// Validate the cached data structure.
					$valid_count = 0;
					foreach ( $data as $item ) {
						if ( is_object( $item ) && isset( $item->slug ) && isset( $item->title ) ) {
							++$valid_count;
						}
					}

					// Only return if we have valid addon data.
					if ( $valid_count > 0 ) {
						return $data;
					} else {
						// Clear bad cached data.
						delete_transient( 'analytify_api_addons' );
					}
				}

				// Make sure this matches the exact URL from your site.
				$url = 'https://analytify.io/wp-json/analytify/v1/plugins';

				// Allow SSL verification to be disabled for local development.
				$sslverify = apply_filters( 'analytify_api_sslverify', true );

				$response = wp_remote_get(
					$url,
					array(
						'timeout'   => 20,
						'sslverify' => $sslverify,
					)
				);

				if ( ! is_wp_error( $response ) ) {
					$response_code = wp_remote_retrieve_response_code( $response );

					// Only process if we got a successful response.
					if ( 200 === (int) $response_code ) {
						// Decode the data that we got.
						$body = wp_remote_retrieve_body( $response );

						if ( ! empty( $body ) ) {
							$data = json_decode( $body, false );

							// Validate decoded data - can be array or object.
							if ( json_last_error() === JSON_ERROR_NONE ) {
								// Convert object to array if needed.
								if ( is_object( $data ) ) {
									$data = json_decode( $body, true );
								}

								// Ensure we have a valid array with at least one valid addon.
								if ( ! empty( $data ) && is_array( $data ) ) {
									// Validate structure - ensure we have objects with required fields.
									$valid_data = array();
									foreach ( $data as $key => $item ) {
										// Convert array items to objects if needed.
										if ( is_array( $item ) ) {
											$item = (object) $item;
										}
										if ( is_object( $item ) && isset( $item->slug ) && isset( $item->title ) ) {
											$valid_data[ $key ] = $item;
										}
									}

									if ( ! empty( $valid_data ) ) {
										// Store the data for a week.
										set_transient( 'analytify_api_addons', $valid_data, 7 * DAY_IN_SECONDS );
										return $valid_data;
									}
								}
							}
						}
					}
				} else {
					// Log the error for debugging, but don't expose it to users.
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only logs when WP_DEBUG is enabled.
						error_log( 'Analytify: Failed to fetch addons - ' . $response->get_error_message() );
					}
					// Try again with SSL verification disabled if it was enabled.
					if ( $sslverify ) {
						$retry_response = wp_remote_get(
							$url,
							array(
								'timeout'   => 20,
								'sslverify' => false,
							)
						);

						if ( ! is_wp_error( $retry_response ) ) {
							$retry_code = wp_remote_retrieve_response_code( $retry_response );
							if ( 200 === (int) $retry_code ) {
								$retry_body = wp_remote_retrieve_body( $retry_response );
								if ( ! empty( $retry_body ) ) {
									$retry_data = json_decode( $retry_body, false );
									if ( json_last_error() === JSON_ERROR_NONE ) {
										// Convert object to array if needed.
										if ( is_object( $retry_data ) ) {
											$retry_data = json_decode( $retry_body, true );
										}

										// Validate structure.
										if ( ! empty( $retry_data ) && is_array( $retry_data ) ) {
											$valid_data = array();
											foreach ( $retry_data as $key => $item ) {
												if ( is_array( $item ) ) {
													$item = (object) $item;
												}
												if ( is_object( $item ) && isset( $item->slug ) && isset( $item->title ) ) {
													$valid_data[ $key ] = $item;
												}
											}

											if ( ! empty( $valid_data ) ) {
												set_transient( 'analytify_api_addons', $valid_data, 7 * DAY_IN_SECONDS );
												return $valid_data;
											}
										}
									}
								}
							}
						}
					}
				}

				return array();
			}

			// API URL to fetch addons.
			$url = 'https://analytify.io/wp-json/analytify/v1/plugins';

			// Allow SSL verification to be disabled for local development.
			$sslverify = apply_filters( 'analytify_api_sslverify', true );

			// Fetch data from the API.
			$response = wp_remote_get(
				$url,
				array(
					'timeout'   => 20,
					'sslverify' => $sslverify,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );

				// Only process if we got a successful response.
				if ( 200 === (int) $response_code ) {
					// Decode the JSON response.
					$body = wp_remote_retrieve_body( $response );
					if ( ! empty( $body ) ) {
						$data = json_decode( $body, false );

						// Validate decoded data - can be array or object.
						if ( json_last_error() === JSON_ERROR_NONE ) {
							// Convert object to array if needed.
							if ( is_object( $data ) ) {
								$data = json_decode( $body, true );
							}

							// Ensure we have a valid array with at least one valid addon.
							if ( ! empty( $data ) && is_array( $data ) ) {
								// Validate structure - ensure we have objects with required fields.
								$valid_data = array();
								foreach ( $data as $key => $item ) {
									if ( is_array( $item ) ) {
										$item = (object) $item;
									}
									if ( is_object( $item ) && isset( $item->slug ) && isset( $item->title ) ) {
										$valid_data[ $key ] = $item;
									}
								}

								if ( ! empty( $valid_data ) ) {
									// Cache the data for a week.
									set_transient( 'analytify_api_addons', $valid_data, 7 * DAY_IN_SECONDS );
								}
							}
						}
					}
				}
			} else {
				// Log the error for debugging, but don't expose it to users.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only logs when WP_DEBUG is enabled.
					error_log( 'Analytify: Failed to fetch addons - ' . $response->get_error_message() );
				}
				// Try again with SSL verification disabled if it was enabled.
				if ( $sslverify ) {
					$retry_response = wp_remote_get(
						$url,
						array(
							'timeout'   => 20,
							'sslverify' => false,
						)
					);

					if ( ! is_wp_error( $retry_response ) ) {
						$retry_code = wp_remote_retrieve_response_code( $retry_response );
						if ( 200 === (int) $retry_code ) {
							$retry_body = wp_remote_retrieve_body( $retry_response );
							if ( ! empty( $retry_body ) ) {
								$retry_data = json_decode( $retry_body, false );
								if ( json_last_error() === JSON_ERROR_NONE ) {
									// Convert object to array if needed.
									if ( is_object( $retry_data ) ) {
										$retry_data = json_decode( $retry_body, true );
									}

									if ( ! empty( $retry_data ) && is_array( $retry_data ) ) {
										$valid_data = array();
										foreach ( $retry_data as $key => $item ) {
											if ( is_array( $item ) ) {
												$item = (object) $item;
											}
											if ( is_object( $item ) && isset( $item->slug ) && isset( $item->title ) ) {
												$valid_data[ $key ] = $item;
											}
										}

										if ( ! empty( $valid_data ) ) {
											set_transient( 'analytify_api_addons', $valid_data, 7 * DAY_IN_SECONDS );
										}
									}
								}
							}
						}
					}
				}
			}

			// Fetch the cached API data or an empty array if not available.
			$api_data = get_transient( 'analytify_api_addons' );
			$api_data = $api_data ? $api_data : array();

			// Filter out WooCommerce and EDD add-ons.
			if ( version_compare( ANALYTIFY_PRO_VERSION, '6.1.0', '<' ) ) {
				$filtered_addons = array_filter(
					$api_data,
					function ( $addon ) {
						return ! (
						isset( $addon->slug ) && (
							strpos( $addon->slug, 'woocommerce' ) !== false ||
							strpos( $addon->slug, 'edd' ) !== false ||
							strpos( $addon->slug, 'campaigns' ) !== false ||
							strpos( $addon->slug, 'authors' ) !== false
						)
						);
					}
				);
			} else {
				$filtered_addons = array_filter(
					$api_data,
					function ( $addon ) {
						return ! (
						isset( $addon->slug ) && (
							strpos( $addon->slug, 'woocommerce' ) !== false ||
							strpos( $addon->slug, 'edd' ) !== false ||
							strpos( $addon->slug, 'campaigns' ) !== false ||
							strpos( $addon->slug, 'authors' ) !== false ||
							strpos( $addon->slug, 'forms' ) !== false ||
							strpos( $addon->slug, 'email' ) !== false ||
							strpos( $addon->slug, 'goals' ) !== false
						)
						);
					}
				);
			}
			// Return the filtered addons as associative array.
			return $filtered_addons;
		}
		/**
		 * Enqueue admin scripts and localize variables.
		 *
		 * @return void
		 */
		public function enqueue_admin_scripts() {
			// Get current screen to check if we're on addons page.
			$screen = get_current_screen();
			if ( ! $screen || strpos( $screen->base, 'analytify-addons' ) === false ) {
				return;
			}

			$addons = $this->addons();

			// Extract slugs from addons array for JavaScript validation.
			$slugs = array();

			// Add Pro addon slugs from the pro_addons option.
			$pro_addons_local = get_option( 'wp_analytify_pro_addons' );
			if ( ! empty( $pro_addons_local ) && is_array( $pro_addons_local ) ) {
				foreach ( $pro_addons_local as $slug => $data ) {
					$slugs[] = $slug;
					// Also add the full plugin path format for compatibility.
					$slugs[] = $slug . '/' . $slug . '.php';
				}
			}

			// Add API addon slugs.
			if ( ! empty( $addons ) && is_array( $addons ) ) {
				foreach ( $addons as $key => $addon ) {
					// Handle both object and array formats.
					if ( is_object( $addon ) && isset( $addon->slug ) ) {
						$slug = $addon->slug;
						if ( ! in_array( $slug, $slugs, true ) ) {
							$slugs[] = $slug;
						}
						// Also add the full plugin path format for compatibility.
						$plugin_path = $slug . '/' . ( 'analytify-analytics-dashboard-widget' === $slug ? 'wp-analytify-dashboard' : $slug ) . '.php';
						if ( ! in_array( $plugin_path, $slugs, true ) ) {
							$slugs[] = $plugin_path;
						}
					} elseif ( is_array( $addon ) && isset( $addon['slug'] ) ) {
						$slug = $addon['slug'];
						if ( ! in_array( $slug, $slugs, true ) ) {
							$slugs[] = $slug;
						}
						$plugin_path = $slug . '/' . ( 'analytify-analytics-dashboard-widget' === $slug ? 'wp-analytify-dashboard' : $slug ) . '.php';
						if ( ! in_array( $plugin_path, $slugs, true ) ) {
							$slugs[] = $plugin_path;
						}
					} elseif ( is_string( $key ) ) {
						// If key is the slug (for associative arrays).
						if ( ! in_array( $key, $slugs, true ) ) {
							$slugs[] = $key;
						}
						$plugin_path = $key . '/' . ( 'analytify-analytics-dashboard-widget' === $key ? 'wp-analytify-dashboard' : $key ) . '.php';
						if ( ! in_array( $plugin_path, $slugs, true ) ) {
							$slugs[] = $plugin_path;
						}
					}
				}
			}

			// Add module slugs.
			$modules_local = $this->modules();
			if ( ! empty( $modules_local ) && is_array( $modules_local ) ) {
				foreach ( $modules_local as $slug => $data ) {
					if ( is_array( $data ) && isset( $data['slug'] ) ) {
						$module_slug = $data['slug'];
						if ( ! in_array( $module_slug, $slugs, true ) ) {
							$slugs[] = $module_slug;
						}
					} elseif ( is_string( $slug ) ) {
						if ( ! in_array( $slug, $slugs, true ) ) {
							$slugs[] = $slug;
						}
					}
				}
			}

			// Ensure script is enqueued (should already be done by scripts-styles.php).
			if ( ! wp_script_is( 'analytify-addons-js', 'enqueued' ) ) {
				$plugin_file = defined( 'WP_ANALYTIFY_PLUGIN_DIR' ) ? WP_ANALYTIFY_PLUGIN_DIR . '/wp-analytify.php' : dirname( __DIR__ ) . '/wp-analytify.php';
				wp_enqueue_script( 'analytify-addons-js', plugins_url( 'assets/js/wp-analytify-addons.js', $plugin_file ), array( 'jquery' ), ANALYTIFY_VERSION, false );
			}

			// Localize to the correct script handle.
			// wp_localize_script replaces any previous localization.
			$localized = wp_localize_script(
				'analytify-addons-js',
				'analytify_addons',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'addons' ),
					'allowed_slugs' => $slugs,
				)
			);
		}

		/**
		 * Check plugin status
		 *
		 * @param string $slug Plugin slug.
		 * @param string $extension_or_status Extension or status.
		 * @return array<string, mixed>
		 * @since 1.3
		 */
		public function pro_addons_status( $slug, $extension_or_status ) {
			$nonce = wp_create_nonce( $slug );

			if ( 'active' === $extension_or_status ) {
				printf(   // translators: Deactivate add-on.
					esc_html__( '%1$s Deactivate add-on %2$s', 'wp-analytify' ),
					'<button type="button" class="button-primary analytify-addon-state analytify-deactivate-addon" data-slug="' . esc_attr( $slug ) . '" data-set-state="deactive" data-nonce="' . esc_attr( $nonce ) . '" >',
					'</button>'
				);
				return array(
					'status' => 'active',
					'slug'   => $slug,
				);
			} else {
				printf(   // translators: Activate add-on.
					esc_html__( '%1$s Activate add-on %2$s', 'wp-analytify' ),
					'<button type="button" class="button-primary analytify-addon-state analytify-activate-addon" data-slug="' . esc_attr( $slug ) . '" data-set-state="active" data-nonce="' . esc_attr( $nonce ) . '" >',
					'</button>'
				);
				return array(
					'status' => 'inactive',
					'slug'   => $slug,
				);
			}
		}

		/**
		 * Get addon status.
		 *
		 * @param string $slug Plugin slug.
		 * @param mixed  $extension Extension data.
		 * @return void
		 */
		public function addons_status( $slug, $extension ) {
			// Free addon has different filename.
			$addon_file_name = ( 'analytify-analytics-dashboard-widget' === $slug ) ? 'wp-analytify-dashboard' : $slug;
			$slug            = $slug . '/' . $addon_file_name . '.php';

			if ( is_plugin_active( $slug ) ) {
				// translators: Deactivate add-on.
				printf( esc_html__( '%1$s Deactivate add-on %2$s', 'wp-analytify' ), '<button type="button" class="button-primary analytify-module-state analytify-deactivate-module" data-slug="' . esc_attr( $slug ) . '" data-set-state="deactive" data-internal-module="false">', '</button>' );

			} elseif ( array_key_exists( $slug, $this->plugins_list ) ) {

				$link = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'activate',
							'plugin' => $slug,
						),
						admin_url( 'plugins.php' )
					),
					'activate-plugin_' . $slug
				);
				// translators: Activate add-on.
				printf( esc_html__( '%1$s Activate add-on %2$s', 'wp-analytify' ), '<a href="' . esc_url( $link ) . '" class="button-primary analytify-module-state analytify-activate-module" data-slug="' . esc_attr( $slug ) . '" data-set-state="active" data-internal-module="false" >', '</a>' );

			} elseif ( is_plugin_inactive( $slug ) ) {

				if ( isset( $extension->status ) && '' !== $extension->status ) {
					// translators: Simple shortcodes.
					printf( esc_html__( '%1$s Download %2$s', 'wp-analytify' ), '<a target="_blank" href="' . esc_url( isset( $extension->url ) ? $extension->url : '#' ) . '" class="button-primary">', '</a>' );
				} else {
					// translators: Get add-on.
					printf( esc_html__( '%1$s Get this add-on %2$s', 'wp-analytify' ), '<a target="_blank" href="' . esc_url( isset( $extension->url ) ? $extension->url : '#' ) . '" class="button-primary">', '</a>' );
				}
			}
		}


		/**
		 * Check if pro version is supporitng modules.
		 *
		 * @version 7.0.5
		 * @return bool
		 */
		public function check_pro_support() {

			if ( class_exists( 'WP_Analytify_Pro' ) ) {
				$plugins = get_plugins();

				if ( version_compare( $plugins['wp-analytify-pro/wp-analytify-pro.php']['Version'], '4.0', '>=' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Returns a list of modules.
		 *
		 * @return array<string, mixed>
		 */
		public function modules() {

			if ( get_option( 'wp_analytify_modules' ) ) {
				return get_option( 'wp_analytify_modules' );
			}

			return array();
		}

		/**
		 * Check module status.
		 *
		 * @param string $slug Plugin slug.
		 * @return void
		 */
		public function check_module_status( $slug ) {

			$nonce = wp_create_nonce( $slug );

			if ( 'active' === $this->modules_list[ $slug ]['status'] && $this->check_pro_support() ) {
				// translators: Deactivate add-on.
				printf( esc_html__( '%1$s Deactivate add-on %2$s', 'wp-analytify' ), '<button type="button" class="button-primary analytify-module-state analytify-deactivate-module" data-slug="' . esc_attr( $slug ) . '" data-set-state="deactive" data-internal-module="true" data-nonce="' . esc_attr( $nonce ) . '" >', '</button>' );

			} elseif ( ! $this->modules_list[ $slug ]['status'] && $this->check_pro_support() ) {
				// translators: Activate add-on.
				printf( esc_html__( '%1$s Activate add-on %2$s', 'wp-analytify' ), '<button type="button" class="button-primary analytify-module-state analytify-activate-module" data-slug="' . esc_attr( $slug ) . '" data-set-state="active" data-internal-module="true" data-nonce="' . esc_attr( $nonce ) . '" >', '</button>' );

			} elseif ( 'deactive' === $this->modules_list[ $slug ]['status'] && $this->check_pro_support() ) {
				// translators: Activate add-on.
				printf( esc_html__( '%1$s Activate add-on %2$s', 'wp-analytify' ), '<button type="button" class="button-primary analytify-module-state analytify-activate-module" data-slug="' . esc_attr( $slug ) . '" data-set-state="active" data-internal-module="true" data-nonce="' . esc_attr( $nonce ) . '" >', '</button>' );

			} else {
				// translators: Get add-on.
				printf( esc_html__( '%1$s Get this add-on %2$s', 'wp-analytify' ), '<a type="button" class="button-primary analytify-activate-module" href=" ' . esc_url( $this->modules_list[ $slug ]['url'] ) . '?utm_source=analytify-lite" target="_blank">', '</a>' );
			}
		}

		/**
		 * Get addon icon URL.
		 *
		 * @param string $slug Plugin slug.
		 * @return string
		 */
		public function get_addon_icon( $slug ) {

			return ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . '/assets/img/addons-svgs/' . $slug . '.svg';
		}

		/**
		 *  Loaders HTML.
		 *
		 * @version 7.0.5
		 * @param string $addon Addon name.
		 * @param string $logo Logo url.
		 * @return string $html HTML markup string.
		 */
		public function loaders( $addon, $logo ) {

			$html  = '<div class="analytify-addons-loader-container"><div class="wp-analytify-addon-enable analytify-loader" style="display:none !important;">
						<div class="analytify-logo-container">
						<img src="' . $logo . '" alt="' . $addon . '">
						<svg class="circular-loader" viewBox="25 25 50 50" >
						<circle class="loader-path" cx="50" cy="50" r="18" fill="none" stroke="#d8d8d8" stroke-width="1" />
						</svg>
						</div>
						<p>' . __( 'Activating...', 'wp-analytify' ) . '</p>
						</div>';
			$html .= '<div class="wp-analytify-addon-install analytify-loader activated" style="display:none !important;">
						<svg class="circular-loader2" viewBox="25 25 50 50" >
						<circle class="loader-path2" cx="50" cy="50" r="18" fill="none" stroke="#00c853" stroke-width="1" />
						</svg>
						<div class="checkmark draw"></div>
						<p>' . __( 'Activated', 'wp-analytify' ) . '</p>
						</div>';
			$html .= '<div class="wp-analytify-addon-uninstalling analytify-loader activated" style="display:none !important;">
						<div class="analytify-logo-container">
						<img src="' . $logo . '" alt="' . esc_attr( $addon ) . '">
						<svg class="circular-loader" viewBox="25 25 50 50">
							<circle class="loader-path" cx="50" cy="50" r="18" fill="none" stroke="#d8d8d8" stroke-width="1" />
						</svg>
						</div>
						<p>' . __( 'Deactivating...', 'wp-analytify' ) . '</p>
					  </div>';
			$html .= '<div class="wp-analytify-addon-uninstall analytify-loader activated" style="display:none !important;">
						<svg class="circular-loader2" viewBox="25 25 50 50" >
						<circle class="loader-path2" cx="50" cy="50" r="18" fill="none" stroke="#ff0000" stroke-width="1" />
						</svg>
						<div class="checkmark draw"></div>
						<p>' . __( 'Deactivated', 'wp-analytify' ) . '</p>
						</div>';
			$html .= '<div class="wp-analytify-addon-wrong activated analytify-loader" style="display:none !important;">
						<svg class="checkmark_login" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
						<circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"></circle>
						<path class="checkmark__check" stroke="#ff0000" fill="none" d="M16 16 36 36 M36 16 16 36"></path>
						</svg>
						<p>' . __( 'Something Went Wrong', 'wp-analytify' ) . '</p>
						</div></div>';

			return $html;
		}
	}
}

$obj_wp_analytify_addons = new WP_Analytify_Addons();

// Clear transient if requested for debugging (add ?clear_addons_cache=1 to URL).
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Cache clearing is a safe admin action, nonce verified via capability check.
if ( isset( $_GET['clear_addons_cache'] ) && '1' === $_GET['clear_addons_cache'] && current_user_can( 'manage_options' ) ) {
	delete_transient( 'analytify_api_addons' );
}

$addons     = $obj_wp_analytify_addons->addons();
$pro_addons = get_option( 'wp_analytify_pro_addons' );
$modules    = $obj_wp_analytify_addons->modules();

$analytify_screen = get_current_screen() ? get_current_screen()->base : '';
$version          = defined( 'ANALYTIFY_PRO_VERSION' ) ? ANALYTIFY_PRO_VERSION : ( defined( 'ANALYTIFY_VERSION' ) ? ANALYTIFY_VERSION : '1.0.0' ); ?>

<div class="wpanalytify analytify-dashboard-nav">
	<div class="wpb_plugin_wraper">
		<div class="wpb_plugin_header_wraper">
			<div class="graph"></div>
			<div class="wpb_plugin_header">
				<div class="wpb_plugin_header_title"></div>
				<div class="wpb_plugin_header_info">
					<a href="https://analytify.io/changelog/" target="_blank" class="btn">View Changelog</a>
				</div>
				<div class="wpb_plugin_header_logo">
					<img src="<?php echo esc_url( ( defined( 'ANALYTIFY_PLUGIN_URL' ) ? ANALYTIFY_PLUGIN_URL : '' ) . 'assets/img/logo.svg' ); ?>" alt="Analytify">
				</div>
			</div>
		</div>

		<div class="analytify-settings-body-container">
			<div class="wpb_plugin_body_wraper">
				<div class="wpb_plugin_body">
					<div class="wpa-tab-wrapper">
						<ul class="analytify_nav_tab_wrapper nav-tab-wrapper">
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=analytify-addons' ) ); ?>"
									class="analytify_nav_tab <?php echo ( 'analytify_page_analytify-addons' === $analytify_screen ) ? 'nav-tab-active' : ''; ?>">Addons</a>
							</li>
							<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=analytify-settings#wp-analytify-license' ) ); ?>"
									class="analytify_nav_tab">License</a></li>
						</ul>
					</div>

					<div class="wpb_plugin_tabs_content analytify-dashboard-content">
						<div class="wrap analytify-addons-wrapper">

							<h2 class='opt-title'><span id='icon-options-general' class='analytics-options'><img
										src="<?php echo esc_url( plugins_url( '../assets/img/wp-analytics-logo.png', __FILE__ ) ); ?>"
										alt="analytics"></span>
								<?php esc_html_e( 'Extend the functionality of Analytify with these awesome Add-ons', 'wp-analytify' ); ?>
							</h2>

							<div class="tabwrapper">

								<?php

								if ( class_exists( 'WP_Analytify_Pro' ) && defined( 'ANALYTIFY_PRO_VERSION' ) && version_compare( ANALYTIFY_PRO_VERSION, '6.0.0', '>=' ) && ! empty( $pro_addons ) ) {
									foreach ( $pro_addons as $slug => $meta ) :
										?>
										<div class="wp-extension <?php echo esc_attr( $meta['name'] ); ?>">
											<a target="_blank" href="<?php echo esc_url( $meta['url'] ); ?>">
												<h3
													style="background-image: url(<?php echo esc_url( $obj_wp_analytify_addons->get_addon_icon( $slug ) ); ?>);">
													<?php echo esc_html( $meta['name'] ); ?>
												</h3>
											</a>
											<p><?php echo wp_kses_post( wpautop( wp_strip_all_tags( $meta['description'] ) ) ); ?></p>
											<p><?php $obj_wp_analytify_addons->pro_addons_status( $slug, $meta['status'] ); ?>
											</p>

											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe internal HTML from loaders().
											echo ( $obj_wp_analytify_addons->loaders( $meta['name'], $obj_wp_analytify_addons->get_addon_icon( $slug ) ) );
											?>

										</div>

										<?php
									endforeach;
									foreach ( $addons as $name => $extension ) :
										// Skip if extension data is invalid or missing required properties.
										if ( ! is_object( $extension ) || ! isset( $extension->url ) || ! isset( $extension->title ) ) {
											continue;
										}

										// Get icon URL safely.
										$icon_url = '';
										if ( isset( $extension->media ) && is_object( $extension->media ) &&
											isset( $extension->media->icon ) && is_object( $extension->media->icon ) &&
											isset( $extension->media->icon->url ) ) {
											$icon_url = $extension->media->icon->url;
										}

										// Get excerpt safely.
										$excerpt = isset( $extension->excerpt ) ? $extension->excerpt : '';

										// Get slug safely - use slug property if available, otherwise use array key.
										$slug = isset( $extension->slug ) ? $extension->slug : ( is_string( $name ) ? $name : '' );
										// Fallback for class name if name is numeric.
										$extension_class = isset( $extension->slug ) ? esc_attr( $extension->slug ) : esc_attr( $name );
										?>
									
										<div class="wp-extension <?php echo esc_attr( $extension_class ); ?>">
											<a target="_blank" href="<?php echo esc_url( $extension->url ); ?>">
												<h3
													<?php if ( ! empty( $icon_url ) ) : ?>
													style="background-image: url(<?php echo esc_url( $icon_url ); ?>);"
													<?php endif; ?>
												>
													<?php echo esc_html( $extension->title ); ?>
												</h3>
											</a>
											<p>
												<?php echo wp_kses_post( wpautop( wp_strip_all_tags( $excerpt ) ) ); ?>
											</p>
											<p>
												<?php $obj_wp_analytify_addons->addons_status( $slug, $extension ); ?>
											</p>
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe internal HTML from loaders().
											echo ( $obj_wp_analytify_addons->loaders( $name, $icon_url ) );
											?>
										</div>
										<?php
									endforeach;

								} else {
									foreach ( $addons as $name => $extension ) :
										// Skip if extension data is invalid or missing required properties.
										if ( ! is_object( $extension ) || ! isset( $extension->url ) || ! isset( $extension->title ) ) {
											continue;
										}

										// Get icon URL safely.
										$icon_url = '';
										if ( isset( $extension->media ) && is_object( $extension->media ) &&
											isset( $extension->media->icon ) && is_object( $extension->media->icon ) &&
											isset( $extension->media->icon->url ) ) {
											$icon_url = $extension->media->icon->url;
										}

										// Get excerpt safely.
										$excerpt = isset( $extension->excerpt ) ? $extension->excerpt : '';

										// Get slug safely - use slug property if available, otherwise use array key.
										$slug = isset( $extension->slug ) ? $extension->slug : ( is_string( $name ) ? $name : '' );
										// Fallback for class name if name is numeric.
										$extension_class = isset( $extension->slug ) ? esc_attr( $extension->slug ) : esc_attr( $name );
										?>
										<div class="wp-extension <?php echo esc_attr( $extension_class ); ?>">
											<a target="_blank" href="<?php echo esc_url( $extension->url ); ?>">
												<h3
													<?php if ( ! empty( $icon_url ) ) : ?>
													style="background-image: url(<?php echo esc_url( $icon_url ); ?>);"
													<?php endif; ?>
												>
													<?php echo esc_html( $extension->title ); ?>
												</h3>
											</a>
											<p>
												<?php echo wp_kses_post( wpautop( wp_strip_all_tags( $excerpt ) ) ); ?>
											</p>
											<p>
												<?php $obj_wp_analytify_addons->addons_status( $slug, $extension ); ?>
											</p>
											<?php
											// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe internal HTML from loaders().
											echo ( $obj_wp_analytify_addons->loaders( $name, $icon_url ) );
											?>
										</div>
										<?php
									endforeach;

									// Show message if no valid addons found.
									$valid_addons_count = 0;
									foreach ( $addons as $extension ) {
										if ( is_object( $extension ) && isset( $extension->url ) && isset( $extension->title ) ) {
											++$valid_addons_count;
										}
									}

									if ( 0 === $valid_addons_count && empty( $pro_addons ) && empty( $modules ) ) {
										?>
										<div class="notice notice-error">
											<p><?php esc_html_e( 'Unable to load addons. Please check your internet connection and try refreshing the page.', 'wp-analytify' ); ?></p>
										</div>
										<?php
									}
								}
								?>

								<?php
								foreach ( $modules as $module ) :
									?>

									<div class="wp-extension <?php echo esc_attr( $module['slug'] ); ?>">
										<a target="_blank" href="<?php echo esc_url( $module['url'] ); ?>">
											<h3
												style="background-size: 90px 90px; background-image: url(<?php echo esc_url( $module['image'] ); ?>);">
												<?php echo esc_html( $module['title'] ); ?>
											</h3>
										</a>
										<p><?php echo esc_html( $module['description'] ); ?></p>
										<p><?php $obj_wp_analytify_addons->check_module_status( $module['slug'] ); ?></p>

										<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe internal HTML from loaders().
										echo ( $obj_wp_analytify_addons->loaders( $module['title'], $module['image'] ) );
										?>

									</div>
								<?php endforeach; ?>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
