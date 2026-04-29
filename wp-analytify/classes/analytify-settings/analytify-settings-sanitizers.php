<?php
/**
 * Sanitizer functions for Analytify settings.
 *
 * @package WP_Analytify
 * @subpackage Settings
 * @since 9.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize the comma-separated "query params to exclude" option.
 *
 * Keeps only safe query key names [a-z0-9_-], trims and lowercases each, removes empties.
 *
 * @since 9.0.0
 * @param string $value Raw comma-separated list from the form.
 * @return string Comma-separated list of allowed param names, or empty string.
 */
function analytify_sanitize_query_params_to_exclude( $value ) {
	if ( ! is_string( $value ) ) {
		return '';
	}
	$parts = array_map( 'trim', explode( ',', $value ) );
	$keep  = array();
	foreach ( $parts as $part ) {
		$part = strtolower( $part );
		if ( '' === $part ) {
			continue;
		}
		if ( preg_match( '/^[a-z0-9_-]+$/', $part ) ) {
			$keep[] = $part;
		}
	}
	$keep = array_unique( $keep );
	return implode( ', ', $keep );
}
