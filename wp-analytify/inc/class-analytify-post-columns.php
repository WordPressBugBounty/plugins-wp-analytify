<?php
/**
 * Analytify Post Columns Class
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Analytify_Post_Columns' ) ) {

	/**
	 * Class Analytify_Post_Columns
	 *
	 * Handles the display of the Sessions column in the posts list table.
	 *
	 * @since 8.0.0
	 */
	class Analytify_Post_Columns {

		/**
		 * Constructor
		 *
		 * @since 8.0.0
		 */
		public function __construct() {
			// Add columns for default post type.
			add_filter( 'manage_edit-post_columns', array( $this, 'wpa_add_column' ), 99 );
			add_action( 'manage_posts_custom_column', array( $this, 'wpa_render_column' ), 10, 2 );

			// Add columns for pages.
			add_filter( 'manage_edit-page_columns', array( $this, 'wpa_add_column' ), 99 );
			add_action( 'manage_pages_custom_column', array( $this, 'wpa_render_column' ), 10, 2 );

			// Add columns for all registered custom post types.
			add_action( 'init', array( $this, 'wpa_register_cpt_columns' ), 20 );
			add_action( 'restrict_manage_posts', array( $this, 'wpa_add_date_filter' ) );
			add_action( 'admin_footer', array( $this, 'wpa_footer_scripts' ) );
			add_action( 'wp_ajax_analytify_get_post_sessions', array( $this, 'wpa_ajax_get_sessions' ) );
		}

		/**
		 * Register column hooks for all custom post types.
		 *
		 * @since 8.0.0
		 * @return void
		 */
		public function wpa_register_cpt_columns() {
			$post_types = get_post_types(
				array(
					'public'   => true,
					'_builtin' => false,
				),
				'names'
			);

			foreach ( $post_types as $post_type ) {
				// Add column filter for custom post types (using the standard filter).
				add_filter( "manage_edit-{$post_type}_columns", array( $this, 'wpa_add_column' ), 99 );
				// Add column rendering action for custom post types.
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'wpa_render_column' ), 10, 2 );
			}
		}

		/**
		 * Add the column to the posts table.
		 *
		 * @since 8.0.0
		 * @param array $columns Existing columns.
		 * @return array Modified columns.
		 */
		public function wpa_add_column( $columns ) {
			$screen = get_current_screen();
			if ( ! $screen || 'edit' !== $screen->base ) {
				$columns['analytify_sessions'] = __( 'Sessions', 'wp-analytify' );
				return $columns;
			}

			// Get selected date range from localStorage via JavaScript or default to 30 days.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameter for display purposes
			$date_range                    = isset( $_GET['analytify_sessions_range'] ) ? sanitize_text_field( wp_unslash( $_GET['analytify_sessions_range'] ) ) : '30days';
			$label                         = $this->wpa_get_date_range_label( $date_range );
			$columns['analytify_sessions'] = __( 'Sessions', 'wp-analytify' ) . ' (' . $label . ')';
			return $columns;
		}

		/**
		 * Get label for date range.
		 *
		 * @since 8.0.0
		 * @param string $range Date range key.
		 * @return string Label for the date range.
		 */
		private function wpa_get_date_range_label( $range ) {
			$labels = array(
				'7days'   => __( '7 Days', 'wp-analytify' ),
				'30days'  => __( '30 Days', 'wp-analytify' ),
				'90days'  => __( '90 Days', 'wp-analytify' ),
				'1year'   => __( 'Last Year', 'wp-analytify' ),
				'alltime' => __( 'All Time', 'wp-analytify' ),
			);
			return isset( $labels[ $range ] ) ? $labels[ $range ] : $labels['30days'];
		}

		/**
		 * Add date range filter dropdown above posts table.
		 *
		 * @since 8.0.0
		 * @param string $post_type Post type.
		 * @return void
		 */
		public function wpa_add_date_filter( $post_type ) {
			$screen = get_current_screen();
			if ( ! $screen || 'edit' !== $screen->base ) {
				return;
			}

			// Only show on public post types.
			$post_type_obj = get_post_type_object( $post_type );
			if ( ! $post_type_obj || ! $post_type_obj->public ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading URL parameter for display purposes
			$selected_range = isset( $_GET['analytify_sessions_range'] ) ? sanitize_text_field( wp_unslash( $_GET['analytify_sessions_range'] ) ) : '30days';
			?>
			<style>
				/* Hide filter button for sessions date range filter */
				#posts-filter .tablenav .alignleft.actions input[type="submit"][name="filter_action"] {
					display: none !important;
				}
				.column-analytify_sessions {
					width: 140px;
					min-width: 140px;
					text-align: center !important;
				}
				th.column-analytify_sessions {
					white-space: nowrap;
				}
			</style>
			<span style="margin-left: 10px; display: inline-flex; align-items: center; vertical-align: middle;">
				<span style="margin-right: 5px; white-space: nowrap;"><?php esc_html_e( 'Session Date Range:', 'wp-analytify' ); ?></span>
				<select name="analytify_sessions_range" id="analytify-sessions-range-filter" style="vertical-align: middle;">
					<option value="7days" <?php selected( $selected_range, '7days' ); ?>><?php esc_html_e( 'Last 7 Days', 'wp-analytify' ); ?></option>
					<option value="30days" <?php selected( $selected_range, '30days' ); ?>><?php esc_html_e( 'Last 30 Days', 'wp-analytify' ); ?></option>
					<option value="90days" <?php selected( $selected_range, '90days' ); ?>><?php esc_html_e( 'Last 90 Days', 'wp-analytify' ); ?></option>
					<option value="1year" <?php selected( $selected_range, '1year' ); ?>><?php esc_html_e( 'Last Year', 'wp-analytify' ); ?></option>
					<option value="alltime" <?php selected( $selected_range, 'alltime' ); ?>><?php esc_html_e( 'All Time', 'wp-analytify' ); ?></option>
				</select>
			</span>
			<?php
		}

		/**
		 * Render the column content.
		 *
		 * @since 8.0.0
		 * @param string $column Column name.
		 * @param int    $post_id Post ID.
		 * @return void
		 */
		public function wpa_render_column( $column, $post_id ) {
			if ( 'analytify_sessions' === $column ) {
				// Use static array to prevent duplicate rendering for the same post.
				static $rendered_posts = array();
				$key                   = $post_id . '_' . $column;

				if ( isset( $rendered_posts[ $key ] ) ) {
					return; // Already rendered for this post.
				}
				$rendered_posts[ $key ] = true;

				echo '<span class="analytify-sessions-wrapper" data-post-id="' . esc_attr( $post_id ) . '"><span class="spinner is-active" style="float:none;margin:0;"></span></span>';
			}
		}

		/**
		 * Output JavaScript in the admin footer to handle AJAX.
		 *
		 * @since 8.0.0
		 * @return void
		 */
		public function wpa_footer_scripts() {
			$screen = get_current_screen();

			// We want this on the 'edit' screen for any post type.
			if ( ! $screen || 'edit' !== $screen->base || ! isset( $screen->post_type ) ) {
				return;
			}

			// Only show on public post types.
			$post_type = get_post_type_object( $screen->post_type );
			if ( ! $post_type || ! $post_type->public ) {
				return;
			}

			// Prevent duplicate script output.
			static $script_output = false;
			if ( $script_output ) {
				return;
			}
			$script_output = true;
			?>
		<script>
		(function($) {
			// Function to get date range from filter or localStorage
			function getDateRange() {
				var $filter = $('#analytify-sessions-range-filter');
				if ( $filter.length ) {
					var range = $filter.val();
					// Store in localStorage for persistence
					localStorage.setItem('analytify_sessions_range', range);
					return range;
				}
				// Try to get from localStorage
				return localStorage.getItem('analytify_sessions_range') || '30days';
			}

			// Function to load sessions data
			function loadSessionsData() {
				// Reset all wrappers
				$('.analytify-sessions-wrapper').each(function() {
					$(this).html('<span class="spinner is-active" style="float:none;margin:0;"></span>').data('updated', false);
				});

				var postIds = [];
				var seenIds = {};
				$('.analytify-sessions-wrapper').each(function() {
					var postId = $(this).data('post-id');
					// Prevent duplicate post IDs.
					if ( postId && ! seenIds[postId] ) {
						postIds.push(postId);
						seenIds[postId] = true;
					}
				});

				if ( postIds.length > 0 ) {
					var dateRange = getDateRange();
					// Process in chunks of 5 to avoid timeouts.
					var chunkSize = 5;
					for (var i = 0; i < postIds.length; i += chunkSize) {
						var chunk = postIds.slice(i, i + chunkSize);
						
						$.post(ajaxurl, {
							action: 'analytify_get_post_sessions',
							post_ids: chunk,
							date_range: dateRange,
							nonce: '<?php echo esc_js( wp_create_nonce( 'analytify_sessions_nonce' ) ); ?>'
						}, function(response) {
							if ( response.success ) {
								$.each(response.data, function(id, sessions) {
									// Only update the first matching element to prevent duplicates.
									var $wrapper = $('.analytify-sessions-wrapper[data-post-id="' + id + '"]').first();
									if ( $wrapper.length && ! $wrapper.data('updated') ) {
										$wrapper.html(sessions).data('updated', true);
									}
								});
							}
						});
					}
				}
			}

			$(document).ready(function() {
				// Use a unique identifier to prevent duplicate processing.
				var processedKey = 'analytify_sessions_processed';
				if ( window[processedKey] ) {
					return;
				}
				window[processedKey] = true;

				// Hide filter button - we handle filtering via AJAX on dropdown change
				$('#posts-filter .tablenav .alignleft.actions input[type="submit"][name="filter_action"]').hide();

				// Set filter value from localStorage if available
				var savedRange = localStorage.getItem('analytify_sessions_range');
				if ( savedRange && $('#analytify-sessions-range-filter').length ) {
					$('#analytify-sessions-range-filter').val(savedRange);
				}

				// Load sessions on page load
				loadSessionsData();

				// Handle filter change - prevent form submission since we use AJAX
				$(document).on('change', '#analytify-sessions-range-filter', function(e) {
					// Prevent any default form submission behavior
					e.preventDefault();
					e.stopPropagation();
					
					var selectedRange = $(this).val();
					var rangeLabels = {
						'7days': '<?php echo esc_js( __( '7 Days', 'wp-analytify' ) ); ?>',
						'30days': '<?php echo esc_js( __( '30 Days', 'wp-analytify' ) ); ?>',
						'90days': '<?php echo esc_js( __( '90 Days', 'wp-analytify' ) ); ?>',
						'1year': '<?php echo esc_js( __( 'Last Year', 'wp-analytify' ) ); ?>',
						'alltime': '<?php echo esc_js( __( 'All Time', 'wp-analytify' ) ); ?>'
					};
					
					// Update column header dynamically
					var $header = $('th.column-analytify_sessions, th[data-column="analytify_sessions"]');
					if ( !$header.length ) {
						// Try to find by text content
						$('th').each(function() {
							if ( $(this).text().indexOf('Sessions') !== -1 ) {
								$header = $(this);
								return false;
							}
						});
					}
					if ( $header.length ) {
						var label = rangeLabels[selectedRange] || rangeLabels['30days'];
						$header.text('<?php echo esc_js( __( 'Sessions', 'wp-analytify' ) ); ?> (' + label + ')');
					}
					
					// Update URL parameter without reload
					var url = new URL(window.location.href);
					url.searchParams.set('analytify_sessions_range', selectedRange);
					window.history.pushState({}, '', url.toString());
					
					// Reload sessions data
					loadSessionsData();
					
					return false;
				});
			});
		})(jQuery);
		</script>
			<?php
		}

		/**
		 * AJAX handler to fetch sessions.
		 *
		 * @since 8.0.0
		 * @return void
		 */
		/**
		 * Convert date range to GA4 date format.
		 *
		 * @since 8.0.0
		 * @param string $range Date range key.
		 * @param int    $post_id Post ID for alltime range.
		 * @return array Array with 'start' and 'end' dates.
		 */
		private function wpa_get_date_range( $range, $post_id = 0 ) {
			$end_date = 'today';

			switch ( $range ) {
				case '7days':
					$start_date = '7daysAgo';
					break;
				case '30days':
					$start_date = '30daysAgo';
					break;
				case '90days':
					$start_date = '90daysAgo';
					break;
				case '1year':
					$start_date = '365daysAgo';
					break;
				case 'alltime':
					// For all time, use post publish date or a very old date.
					if ( $post_id > 0 ) {
						$post_obj = get_post( $post_id );
						if ( $post_obj ) {
							$post_date = get_the_time( 'Y-m-d', $post_obj );
							// GA4 data typically starts from 2005, so use that as minimum.
							if ( get_the_time( 'Y', $post_obj ) < 2005 ) {
								$start_date = '2005-01-01';
							} else {
								$start_date = $post_date;
							}
						} else {
							$start_date = '2005-01-01';
						}
					} else {
						$start_date = '2005-01-01';
					}
					break;
				default:
					$start_date = '30daysAgo';
			}

			return array(
				'start' => $start_date,
				'end'   => $end_date,
			);
		}

		/**
		 * AJAX handler to fetch sessions.
		 *
		 * @since 8.0.0
		 * @return void
		 */
		public function wpa_ajax_get_sessions() {
			check_ajax_referer( 'analytify_sessions_nonce', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'Unauthorized' );
			}

			$post_ids   = isset( $_POST['post_ids'] ) ? array_map( 'intval', $_POST['post_ids'] ) : array();
			$date_range = isset( $_POST['date_range'] ) ? sanitize_text_field( wp_unslash( $_POST['date_range'] ) ) : '30days';
			$data       = array();

			if ( ! class_exists( 'WP_Analytify' ) || ! class_exists( 'WPANALYTIFY_Utils' ) ) {
				wp_send_json_error( 'Required classes missing' );
			}

			// Use the main instance which has the authenticated client.
			$analytify = WP_Analytify::get_instance();

			$property_id = WPANALYTIFY_Utils::get_reporting_property();

			if ( empty( $property_id ) ) {
				wp_send_json_error( 'No Property ID found' );
			}

			foreach ( $post_ids as $id ) {
				// Use slug only, avoiding date-based permalinks.
				$slug = get_post_field( 'post_name', $id );
				$path = '/' . $slug . '/';

				// Get date range for this post.
				$dates = $this->wpa_get_date_range( $date_range, $id );

				// Fetch from GA4.
				$filters = array(
					'logic'   => 'AND',
					'filters' => array(
						array(
							'type'       => 'dimension',
							'name'       => 'pagePath',
							'match_type' => 'EXACT',
							'value'      => $path,
						),
						// Exclude (not set) as per core logic.
						array(
							'type'           => 'dimension',
							'name'           => 'pagePath',
							'match_type'     => 'PARTIAL_REGEXP',
							'value'          => '(not set)',
							'not_expression' => true,
						),
					),
				);

				try {
					// Include date range in cache key to prevent cache conflicts.
					$cache_key = 'post_sessions_' . $id . '_' . $date_range;

					// get_reports handles caching internally using 'analytify_transient_' prefix.
					$report = $analytify->get_reports(
						$cache_key,
						array( 'sessions' ),
						$dates,
						array( 'pagePath' ), // dimensions.
						array(
							'type'  => 'metric',
							'name'  => 'sessions',
							'order' => 'desc',
						), // order.
						$filters
					);

					$sessions = 0;
					if ( isset( $report['rows'][0]['sessions'] ) ) {
						$sessions = $report['rows'][0]['sessions'];
					}

					// Check for error in report response structure.
					if ( isset( $report['error'] ) && ! empty( $report['error'] ) ) {
						$sessions = 'Error';
					}
				} catch ( Exception $e ) {
					$sessions = 'Error';
				}

				$data[ $id ] = $sessions;
			}

			wp_send_json_success( $data );
		}
	}

	new Analytify_Post_Columns();
}
