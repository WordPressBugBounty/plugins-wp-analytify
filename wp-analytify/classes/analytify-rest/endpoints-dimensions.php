<?php
/**
 * Analytify REST Endpoints Dimensions Trait
 *
 * This trait provides dimensional analytics endpoints for the Analytify REST API.
 * It was created to separate dimensional analytics functionality from the main REST class,
 * offering endpoints for traffic sources, keywords, social media, and referrer analytics.
 *
 * PURPOSE:
 * - Provides dimensional analytics endpoints
 * - Handles traffic source analysis
 * - Manages keyword and social media analytics
 * - Offers referrer and campaign tracking
 *
 * @package WP_Analytify
 * @subpackage REST_API
 * @since 8.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

trait Analytify_Rest_Endpoints_Dimensions {
	/**
	 * Get top pages and posts.
	 *
	 * @return array<string, mixed>
	 */
	private function geographic_stats() {
		$this->set_compare_dates();
		$country_limit          = apply_filters( 'analytify_api_limit_country_stats', 5, 'dashboard' );
		$cities_limit           = apply_filters( 'analytify_api_limit_city_stats', 5, 'dashboard' );
		$geo_map_data           = array();
		$country_stats          = array();
		$city_stats             = array();
		$after_top_country_text = '';
		$after_top_city_text    = '';
		ob_start();
		do_action( 'analytify_after_top_country_text' );
		$after_top_country_text .= ob_get_clean();
		ob_start();
		do_action( 'analytify_after_top_city_text' );
		$after_top_city_text    .= ob_get_clean();
		$dashboard_profile_id    = WPANALYTIFY_Utils::get_reporting_property();
		$report_url              = WP_ANALYTIFY_FUNCTIONS::get_ga_report_url( $dashboard_profile_id );
		$after_top_country_text .= ' <a href="javascript: return false;" data-ga-dashboard-link="' . WPANALYTIFY_Utils::get_all_stats_link( $report_url, 'top_countries' ) . '" target="_blank" class="analytify_tooltip"><span class="analytify_tooltiptext">' . __( 'View All Top Countries', 'wp-analytify' ) . '</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>';
		$after_top_city_text    .= ' <a href="javascript: return false;" data-ga-dashboard-link="' . WPANALYTIFY_Utils::get_all_stats_link( $report_url, 'top_cities' ) . '" target="_blank" class="analytify_tooltip"><span class="analytify_tooltiptext">' . __( 'View All Top Cities', 'wp-analytify' ) . '</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>';
		$country_stats_raw       = $this->wp_analytify->get_reports(
			'show-geographic-countries-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'country' ),
			array(
				'type'  => 'dimension',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'country',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
				),
			)
		);
		$city_stats_raw          = $this->wp_analytify->get_reports(
			'show-geographic-cities-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'city', 'country' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'city',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
					array(
						'type'           => 'dimension',
						'name'           => 'country',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
				),
			),
			$cities_limit
		);
		if ( isset( $country_stats_raw['rows'] ) && $country_stats_raw['rows'] ) {
			$country_count = 0; foreach ( $country_stats_raw['rows'] as $row ) {
				if ( $country_count < $country_limit ) {
					$country_stats[] = array(
						'country'  => '<span role="img" aria-label="' . $row['country'] . '" class="analytify_' . str_replace( ' ', '_', strtolower( $row['country'] ) ) . ' analytify_flages"></span> ' . $row['country'],
						'sessions' => $row['sessions'],
					);
				}
				if ( 'United States' === $row['country'] ) {
					$row['country'] = 'United States of America'; }
				$geo_map_data[] = $row;
				++$country_count;
			}
		}
		if ( isset( $city_stats_raw['rows'] ) && $city_stats_raw['rows'] ) {
			foreach ( $city_stats_raw['rows'] as $row ) {
				$city_stats[] = array(
					'city'     => '<span  role="img" aria-label="' . $row['country'] . '" class="analytify_' . str_replace( ' ', '_', strtolower( $row['country'] ) ) . ' analytify_flages"></span> ' . $row['city'],
					'sessions' => $row['sessions'],
				);
			}
		}
		$country = array(
			'headers' => array(
				'country'  => array(
					'label'    => esc_html__( 'Top Countries', 'wp-analytify' ) . $after_top_country_text,
					'th_class' => 'analytify_txt_left analytify_vt_middle analytify_top_geographic_detials_wraper',
					'td_class' => '',
				),
				'sessions' => array(
					'label'    => esc_html__( 'Visitors', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center',
				),
			),
			'stats'   => $country_stats,
		);
		$city    = array(
			'headers' => array(
				'city'     => array(
					'label'    => esc_html__( 'Top Cities', 'wp-analytify' ) . $after_top_city_text,
					'th_class' => 'analytify_txt_left analytify_vt_middle analytify_top_geographic_detials_wraper analytify_brd_lft',
					'td_class' => 'analytify_boder_left',
				),
				'sessions' => array(
					'label'    => esc_html__( 'Visitors', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center',
				),
			),
			'stats'   => $city_stats,
		);
		return array(
			'success' => true,
			'map'     => array(
				'title'   => esc_html__( 'Geographic Stats', 'wp-analytify' ),
				'label'   => array(
					'high' => esc_html__( 'High', 'wp-analytify' ),
					'low'  => esc_html__( 'Low', 'wp-analytify' ),
				),
				'stats'   => $geo_map_data,
				'highest' => ! empty( $geo_map_data ) ? max( array_column( $geo_map_data, 'sessions' ) ) + 1 : 1,
				'colors'  => apply_filters( 'analytify_world_map_colors', array( '#ff5252', '#ffbc00', '#448aff' ) ),
			),
			'country' => $country,
			'city'    => $city,
			'footer'  => apply_filters( 'analytify_top_country_city_footer', __( 'Top countries and cities.', 'wp-analytify' ), array( $this->start_date, $this->end_date ) ),
		);
	}

	/**
	 * Get system stats.
	 *
	 * @return array<string, mixed>
	 */
	private function system_stats() {
		$browser_stats_limit = apply_filters( 'analytify_api_limit_browser_stats', 5, 'dashboard' );
		$os_stats_limit      = apply_filters( 'analytify_api_limit_os_stats', 5, 'dashboard' );
		$mobile_stats_limit  = apply_filters( 'analytify_api_limit_mobile_stats', 5, 'dashboard' );
		$browser_stats       = array();
		$os_stats            = array();
		$mobile_stats        = array();
		$browser_stats_raw   = $this->wp_analytify->get_reports(
			'show-default-browser-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'browser', 'operatingSystem' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'operatingSystem',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
				),
			),
			$browser_stats_limit
		);
		$os_stats_raw        = $this->wp_analytify->get_reports(
			'show-default-os-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'operatingSystem', 'operatingSystemVersion' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'operatingSystemVersion',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
				),
			),
			$os_stats_limit
		);
		$mobile_stats_raw    = $this->wp_analytify->get_reports(
			'show-default-mobile-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'mobileDeviceBranding', 'mobileDeviceModel' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'deviceCategory',
						'match_type'     => 4,
						'value'          => 'desktop',
						'not_expression' => true,
					),
					array(
						'type'           => 'dimension',
						'name'           => 'mobileDeviceModel',
						'match_type'     => 4,
						'value'          => '(not set)',
						'not_expression' => true,
					),
				),
			),
			$mobile_stats_limit
		);
		if ( isset( $browser_stats_raw['rows'] ) && $browser_stats_raw['rows'] ) {
			foreach ( $browser_stats_raw['rows'] as $row ) {
				$browser_stats[] = array(
					'browser'  => '<span role="img" aria-label="' . $row['browser'] . '" class="' . pretty_class( $row['browser'] ) . ' analytify_social_icons"></span><span class="' . pretty_class( $row['operatingSystem'] ) . ' analytify_social_icons"></span>' . $row['browser'] . ' ' . $row['operatingSystem'],
					'sessions' => $row['sessions'],
				); }
		}
		if ( isset( $os_stats_raw['rows'] ) && $os_stats_raw['rows'] ) {
			foreach ( $os_stats_raw['rows'] as $row ) {
				$os_stats[] = array(
					'os'       => '<span  role="img" aria-label="' . $row['operatingSystem'] . '" class="' . pretty_class( $row['operatingSystem'] ) . ' analytify_social_icons"></span> ' . $row['operatingSystem'] . ' ' . $row['operatingSystemVersion'],
					'sessions' => $row['sessions'],
				); }
		}
		if ( isset( $mobile_stats_raw['rows'] ) && $mobile_stats_raw['rows'] ) {
			foreach ( $mobile_stats_raw['rows'] as $row ) {
				$mobile_stats[] = array(
					'mobile'   => '<span role="img" aria-label="' . $row['mobileDeviceBranding'] . '" class="' . pretty_class( $row['mobileDeviceBranding'] ) . ' analytify_social_icons"></span> ' . $row['mobileDeviceBranding'] . ' ' . $row['mobileDeviceModel'],
					'sessions' => $row['sessions'],
				); }
		}
		$after_top_browser_text          = '';
		$after_top_operating_system_text = '';
		$after_top_mobile_device_text    = '';
		ob_start();
		do_action( 'analytify_after_top_browser_text' );
		$after_top_browser_text .= ob_get_clean();
		ob_start();
		do_action( 'analytify_after_top_operating_system_text' );
		$after_top_operating_system_text .= ob_get_clean();
		ob_start();
		do_action( 'analytify_after_top_mobile_device_text' );
		$after_top_mobile_device_text .= ob_get_clean();
		$browser                       = array(
			'headers' => array(
				'browser'  => array(
					'label'    => esc_html__( 'Browsers statistics', 'wp-analytify' ) . $after_top_browser_text,
					'th_class' => 'analytify_txt_left analytify_top_geographic_detials_wraper',
					'td_class' => '',
				),
				'sessions' => array(
					'label'    => esc_html__( 'Visits', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center',
				),
			),
			'stats'   => $browser_stats,
		);
		$os                            = array(
			'headers' => array(
				'os'       => array(
					'label'    => esc_html__( 'Operating system statistics', 'wp-analytify' ) . $after_top_operating_system_text,
					'th_class' => 'analytify_txt_left analytify_top_geographic_detials_wraper analytify_brd_lft',
					'td_class' => 'analytify_boder_left',
				),
				'sessions' => array(
					'label'    => esc_html__( 'Visits', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center',
				),
			),
			'stats'   => $os_stats,
		);
		$mobile                        = array(
			'headers' => array(
				'mobile'   => array(
					'label'    => esc_html__( 'Mobile device statistics', 'wp-analytify' ) . $after_top_mobile_device_text,
					'th_class' => 'analytify_txt_left analytify_top_geographic_detials_wraper analytify_brd_lft',
					'td_class' => 'analytify_boder_left',
				),
				'sessions' => array(
					'label'    => esc_html__( 'Visits', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center',
				),
			),
			'stats'   => $mobile_stats,
		);
		return array(
			'success' => true,
			'browser' => $browser,
			'os'      => $os,
			'mobile'  => $mobile,
			'footer'  => apply_filters( 'analytify_system_stats_footer', __( 'Top browsers and operating systems.', 'wp-analytify' ), array( $this->start_date, $this->end_date ) ),
		);
	}

	/**
	 * Get keyword analytics data
	 *
	 * Retrieves search keyword data including organic search terms,
	 * search volume, and associated conversion metrics.
	 *
	 * @return array<string, mixed> Keyword statistics with search performance data
	 */
	private function keyword_stats() {
		$api_stats_limit   = apply_filters( 'analytify_api_limit_keywords_stats', 10, 'dashboard' );
		$headers           = true;
		$keywords_stats    = array();
		$total_clicks      = 0;
		$success           = true;
		$error_message     = false;
		$keyword_stats_raw = $this->wp_analytify->get_search_console_stats( 'show-default-keyword-dashboard', $this->get_dates(), $api_stats_limit );
		if ( isset( $keyword_stats_raw['error']['status'] ) && isset( $keyword_stats_raw['error']['message'] ) ) {
			return array(
				'success'   => false,
				'error_box' => array(
					'title'   => __( 'Unable To Fetch Reports', 'wp-analytify' ),
					'content' => '<p class="analytify-promo-popup-paragraph analytify-error-popup-paragraph"><strong>' . __( 'Status:', 'wp-analytify' ) . ' </strong> ' . $keyword_stats_raw['error']['status'] . '</p><p class="analytify-promo-popup-paragraph analytify-error-popup-paragraph"><strong>' . __( 'Message:', 'wp-analytify' ) . ' </strong> ' . $keyword_stats_raw['error']['message'] . '</p>',
				),
			);
		}
		if ( isset( $keyword_stats_raw['response']['rows'] ) && $keyword_stats_raw['response']['rows'] > 0 ) {
			foreach ( $keyword_stats_raw['response']['rows'] as $row ) {
				$keywords_stats[] = array(
					'keyword_url' => $row['keys'][0],
					'impressions' => $row['impressions'],
					'clicks'      => $row['clicks'],
				);
				$total_clicks    += $row['clicks'];
			}
			$success = true;
			$headers = array(
				'keyword_url' => array(
					'label'    => esc_html__( 'Keywords', 'wp-analytify' ),
					'th_class' => 'analytify_txt_left analytify_link_title',
					'td_class' => '',
				),
				'impressions' => array(
					'label'    => esc_html__( 'Impressions', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center analytify_value_row',
				),
				'clicks'      => array(
					'label'    => esc_html__( 'Clicks', 'wp-analytify' ),
					'th_class' => 'analytify_value_row',
					'td_class' => 'analytify_txt_center analytify_value_row',
				),
			);
		}
		return array(
			'success'       => $success,
			'error_message' => $error_message,
			'headers'       => $headers,
			'stats'         => $keywords_stats,
			'title_stats'   => $total_clicks > 0 ? '<span class="analytify_medium_f">' . __( 'Total Clicks', 'wp-analytify' ) . '</span> ' . $total_clicks : false,
			'footer'        => apply_filters( 'analytify_keywords_footer', __( 'Ranked keywords.', 'wp-analytify' ), array( $this->start_date, $this->end_date ) ),
		);
	}

	/**
	 * Get social media analytics
	 *
	 * Retrieves social media traffic data including platform-specific
	 * metrics, engagement rates, and conversion tracking.
	 *
	 * @return array<string, mixed> Social media statistics with platform performance
	 */
	private function social_stats() {
		$api_stats_limit  = apply_filters( 'analytify_api_limit_social_media_stats', 5, 'dashboard' );
		$social_stats     = array();
		$total_sessions   = false;
		$social_stats_raw = $this->wp_analytify->get_reports(
			'show-default-social-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'sessionSource' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(
				'logic'   => 'AND',
				'filters' => array(
					array(
						'type'           => 'dimension',
						'name'           => 'sessionSource',
						'match_type'     => 5,
						'value'          => '^([a-z-]*\.|)(facebook|reddit|youtube|tumblr|quora|instagram|linkedin|yelp|wordpress|pinterest|twitter|t)(\.(com|org|co|)|)$',
						'not_expression' => false,
					),
				),
			),
			$api_stats_limit * 3
		);
		if ( isset( $social_stats_raw['rows'] ) && $social_stats_raw['rows'] && is_array( $social_stats_raw['rows'] ) ) {
			$social_stats_ga4_raw = WPANALYTIFY_Utils::ga4_social_stats( $social_stats_raw['rows'] );
			$total_sessions       = 0;
			foreach ( $social_stats_ga4_raw as $row ) {
				$social_stats[]  = array(
					'network'  => '<span  role="img" aria-label="' . $row['sessionSource'] . '" class="' . pretty_class( $row['sessionSource'] ) . ' analytify_social_icons"></span> ' . $row['sessionSource'],
					'sessions' => WPANALYTIFY_Utils::pretty_numbers( $row['sessions'] ),
				);
				$total_sessions += $row['sessions']; }
		}
		return array(
			'success'       => true,
			'error_message' => false,
			'headers'       => array(
				'network'  => array(
					'label'    => false,
					'th_class' => '',
					'td_class' => '',
				),
				'sessions' => array(
					'label'    => false,
					'th_class' => '',
					'td_class' => 'analytify_txt_center analytify_value_row',
				),
			),
			'stats'         => $social_stats,
			'title_stats'   => $total_sessions ? '<span class="analytify_medium_f">' . __( 'Total Visits', 'wp-analytify' ) . '</span> ' . $total_sessions : false,
			'footer'        => apply_filters( 'analytify_social_footer', __( 'Number of visitors coming from Social Channels.', 'wp-analytify' ), array( $this->start_date, $this->end_date ) ),
		);
	}

	/**
	 * Get referrer analytics data
	 *
	 * Retrieves traffic source data including referring websites,
	 * campaign tracking, and attribution analysis.
	 *
	 * @return array<string, mixed> Referrer statistics with traffic source data
	 */
	private function get_referer_stats() {
		$api_stats_limit   = apply_filters( 'analytify_api_limit_referer_stats', 30, 'dashboard' );
		$referer_stats     = array();
		$total_sessions    = false;
		$referer_stats_raw = $this->wp_analytify->get_reports(
			'show-default-refers-dashboard',
			array( 'sessions' ),
			$this->get_dates(),
			array( 'sessionSource', 'sessionMedium' ),
			array(
				'type'  => 'metric',
				'name'  => 'sessions',
				'order' => 'desc',
			),
			array(),
			$api_stats_limit
		);
		if ( isset( $referer_stats_raw['aggregations']['sessions'] ) ) {
			$total_sessions = $referer_stats_raw['aggregations']['sessions']; }
		if ( isset( $referer_stats_raw['rows'] ) && $referer_stats_raw['rows'] ) {
			foreach ( $referer_stats_raw['rows'] as $row ) {
				$bar = '';
				if ( $total_sessions && $total_sessions > 0 ) {
					$bar = ' <span class="analytify_bar_graph"><span style="width:' . ( $row['sessions'] / $total_sessions ) * 100 . '%"></span></span>'; }
				$referer_stats[] = array(
					'referer'  => $row['sessionSource'] . '/' . $row['sessionMedium'] . $bar,
					'sessions' => $row['sessions'],
				);
			}
		}
		return array(
			'success'     => true,
			'headers'     => array(
				'referer'  => array(
					'label'    => false,
					'th_class' => '',
					'td_class' => '',
				),
				'sessions' => array(
					'label'    => false,
					'th_class' => '',
					'td_class' => 'analytify_txt_center analytify_value_row',
				),
			),
			'stats'       => $referer_stats,
			'pagination'  => true,
			'title_stats' => $total_sessions ? '<span class="analytify_medium_f">' . esc_html__( 'Total Visits', 'wp-analytify' ) . '</span> ' . $total_sessions : false,
			'footer'      => apply_filters( 'analytify_referer_footer', __( 'Top referrers to your website.', 'wp-analytify' ), array( $this->start_date, $this->end_date ) ),
		);
	}

	/**
	 * Get what is happening stats.
	 *
	 * @version 8.0.0
	 * @return array<string, mixed>
	 */
	private function get_what_is_happening_stats() {
		$api_stats_limit   = apply_filters( 'analytify_api_limit_what_happen_stats', 5, 'dashboard' );
		$what_happen_stats = array();
		$headers           = false;
		$footer            = false;
		$page_stats_raw    = $this->wp_analytify->get_reports(
			'show-default-what-happen',
			array( 'engagedSessions', 'engagementRate', 'userEngagementDuration' ),
			$this->get_dates(),
			array( 'landingPage', 'pageTitle' ),
			array(
				'type'  => 'metric',
				'name'  => 'engagedSessions',
				'order' => 'desc',
			),
			array(),
			$api_stats_limit
		);
		if ( isset( $page_stats_raw['rows'] ) && $page_stats_raw['rows'] ) {
			$num = 1; foreach ( $page_stats_raw['rows'] as $row ) {
				$what_happen_stats[] = array(
					'title_link'             => '<span class="analytify_page_name analytify_bullet_' . $num . '">' . $row['pageTitle'] . '</span><a target="_blank" href="' . $row['landingPage'] . '">' . $row['landingPage'] . '</a>',
					'userEngagementDuration' => WPANALYTIFY_Utils::pretty_time( $row['userEngagementDuration'] ),
					'engagedSessions'        => WPANALYTIFY_Utils::pretty_numbers( $row['engagedSessions'] ),
					'engagementRate'         => '<div class="analytify_enter_exit_bars">' . round( WPANALYTIFY_Utils::fraction_to_percentage( $row['engagementRate'] ), 2 ) . '<span class="analytify_persantage_sign">%</span><span class="analytify_bar_graph"><span class="analytify_engagement_bar" data-rate="' . round( WPANALYTIFY_Utils::fraction_to_percentage( $row['engagementRate'] ), 2 ) . '" style="width:' . round( WPANALYTIFY_Utils::fraction_to_percentage( $row['engagementRate'] ), 2 ) . '%"></span></span></div>',
				);
				++$num;
			}
			$headers = array(
				'title_link'             => array(
					'label'    => esc_html__( 'Page / Title / Link', 'wp-analytify' ),
					'th_class' => 'analytify_txt_left analytify_link_title',
					'td_class' => 'analytify_page_url_detials',
				),
				'userEngagementDuration' => array(
					'label'    => esc_html__( 'User Engagement Duration', 'wp-analytify' ),
					'th_class' => 'analytify_compair_value_row',
					'td_class' => 'analytify_txt_center analytify_w_300 analytify_l_f',
				),
				'engagedSessions'        => array(
					'label'    => esc_html__( 'Engaged Sessions', 'wp-analytify' ),
					'th_class' => 'analytify_compair_value_row',
					'td_class' => 'analytify_txt_center analytify_w_300 analytify_l_f',
				),
				'engagementRate'         => array(
					'label'    => esc_html__( 'Engagement Rate', 'wp-analytify' ),
					'th_class' => 'analytify_compair_row',
					'td_class' => 'analytify_txt_center analytify_w_300 analytify_l_f',
				),
			);
		}
		return array(
			'success' => true,
			'headers' => $headers,
			'stats'   => $what_happen_stats,
			'footer'  => $footer,
		);
	}
}
