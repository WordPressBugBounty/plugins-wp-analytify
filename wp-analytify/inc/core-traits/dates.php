<?php
/**
 * Core Date Functions for WP Analytify
 *
 * This file contains all date and time related functions that were previously
 * in wpa-core-functions.php. Functions are kept as standalone functions for
 * simplicity and backward compatibility.
 *
 * @package WP_Analytify
 * @since 8.0.0
 */

/**
 * This function returns dates for the date picker.
 *
 * @version 5.0.4
 * @since 5.0.4
 * @return array<string, mixed> Array with start_date and end_date
 */
function analytify_datepicker_dates() {

	$wp_analytify = $GLOBALS['WP_ANALYTIFY'];

	$start_date_val = strtotime( '-1 month' );
	$end_date_val   = strtotime( 'now' );
	$start_date     = wp_date( 'Y-m-d', $start_date_val );
	$end_date       = wp_date( 'Y-m-d', $end_date_val );

	/**
	 * Always remember the previously selected date.
	 */
	$_differ = get_option( 'analytify_date_differ' );

	if ( $_differ ) {
		if ( 'current_day' === $_differ ) {
			$start_date = wp_date( 'Y-m-d' );
		} elseif ( 'yesterday' === $_differ ) {
			$start_date = wp_date( 'Y-m-d', strtotime( '-1 day' ) );
			$end_date   = wp_date( 'Y-m-d', strtotime( '-1 day' ) );
		} elseif ( 'last_7_days' === $_differ ) {
			$start_date = wp_date( 'Y-m-d', strtotime( '-7 days' ) );
		} elseif ( 'last_14_days' === $_differ ) {
			$start_date = wp_date( 'Y-m-d', strtotime( '-14 days' ) );
		} elseif ( 'last_30_days' === $_differ ) {
			$start_date = wp_date( 'Y-m-d', strtotime( '-1 month' ) );
		} elseif ( 'this_month' === $_differ ) {
			$start_date = wp_date( 'Y-m-01' );
		} elseif ( 'last_month' === $_differ ) {
			$start_date = wp_date( 'Y-m-01', strtotime( '-1 month' ) );
			$end_date   = wp_date( 'Y-m-t', strtotime( '-1 month' ) );
		} elseif ( 'last_3_months' === $_differ ) {
			$start_date = wp_date( 'Y-m-01', strtotime( '-3 month' ) );
			$end_date   = wp_date( 'Y-m-t', strtotime( '-1 month' ) );
		} elseif ( 'last_6_months' === $_differ ) {
			$start_date = wp_date( 'Y-m-01', strtotime( '-6 month' ) );
			$end_date   = wp_date( 'Y-m-t', strtotime( '-1 month' ) );
		} elseif ( 'last_year' === $_differ ) {
			$start_date = wp_date( 'Y-m-01', strtotime( '-1 year' ) );
			$end_date   = wp_date( 'Y-m-t', strtotime( '-1 month' ) );
		}
	}
	/**
	 * Default dates.
	 * $_POST dates are checked incase the Per version is older than 5.0.0.
	 */
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by caller.
	if ( isset( $_POST['analytify_date_start'] ) && ! empty( $_POST['analytify_date_start'] ) && isset( $_POST['analytify_date_end'] ) && ! empty( $_POST['analytify_date_end'] ) ) {
		$start_date = sanitize_text_field( wp_unslash( $_POST['analytify_date_start'] ) );
		$end_date   = sanitize_text_field( wp_unslash( $_POST['analytify_date_end'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	return array(
		'start_date' => $start_date,
		'end_date'   => $end_date,
	);
}
