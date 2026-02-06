<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing -- File doc comment exists
/**
 * Analytify Settings Helpers Trait
 *
 * This trait contains utility and helper functions for the Analytify settings functionality.
 * It was created to provide common helper methods that support the main settings operations,
 * keeping the code DRY and well-organized.
 *
 * PURPOSE:
 * - Provides utility functions for settings operations
 * - Handles common settings-related tasks
 * - Offers helper methods for data processing
 *
 * @package WP_Analytify
 * @subpackage Settings
 * @since 8.0.0
 */

trait Analytify_Settings_Helpers {

	/**
	 * Get current post types.
	 *
	 * @return array
	 */
	public function get_current_post_types() {
		$post_types_list = array();
		$args            = array( 'public' => true );
		$post_types      = get_post_types( $args );
		foreach ( $post_types as $post_type ) {
			$post_types_list[ $post_type ] = $post_type;
		}
		return $post_types_list;
	}

	/**
	 * Get current user roles.
	 *
	 * @return array
	 */
	public function get_current_roles() {
		$roles = array();
		if ( get_editable_roles() > 0 ) {
			foreach ( get_editable_roles() as $role => $name ) {
				$roles[ $role ] = $name['name'];
			}
		} else {
			$roles['empty'] = 'no roles found';
		}
		return $roles;
	}

	/**
	 * Get field description.
	 *
	 * @param mixed $args The field arguments.
	 * @return string
	 */
	public function get_field_description( $args ) {
		if ( isset( $args['desc'] ) && ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
		} else {
			$desc = '';
		}
		return $desc;
	}

	/**
	 * Sanitize options.
	 *
	 * @param mixed $options The options to sanitize.
	 * @return mixed
	 */
	public function sanitize_options( $options ) {
		if ( ! $options ) {
			return; }
		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
				continue;
			}
		}
		return $options;
	}

	/**
	 * Get sanitize callback.
	 *
	 * @param mixed $slug The option slug.
	 * @return mixed
	 */
	public function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false; }
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				// Skip invalid options that don't have required fields.
				if ( ! isset( $option['name'] ) ) {
					continue;
				}

				if ( $option['name'] !== $slug ) {
					continue; }
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}
		return false;
	}

	/**
	 * Get option value.
	 *
	 * @param mixed $option  The option name.
	 * @param mixed $section The section name.
	 * @param mixed $default The default value.
	 * @return mixed
	 */
	public function get_option( $option, $section, $default = '' ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound -- Default parameter name is acceptable
		$options = get_option( $section );
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}
		return $default;
	}
}
