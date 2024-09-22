<?php


if ( ! defined( 'ABSPATH' ) ) {
	// exit if accessed directly.
	exit;
}


class WP_ANALYTIFY_ADMIN_BAR {

	/**
	 * Initialize Admin Bar.
	 *
	 */
	public function init() {
		add_action( 'admin_bar_menu' , array( $this, 'admin_bar_menu' ) , 90 );
	}


	public function admin_bar_menu( $wp_admin_bar ) {

		global $tag, $wp_the_query;
		$current_object = $wp_the_query->get_queried_object();
		$menus = array();

		$is_access_level = $GLOBALS['WP_ANALYTIFY']->settings->get_option( 'show_analytics_roles_dashboard','wp-analytify-dashboard', array( 'administrator' ) );

		if ( $GLOBALS['WP_ANALYTIFY']->pa_check_roles( $is_access_level ) ) {

			$wp_admin_bar->add_node(array(
				'id'    => 'analytify',
				'title' => '<span class="ab-icon"></span><span id="ab-analytify" class="ab-label">Analytify</span>',
				'href'  => get_admin_url( null, 'admin.php?page=analytify-dashboard' ),
				'meta'  => array( 'title' => __( 'Analytify QuickLinks', 'wp-analytify' ) ),
			));


			$menus['analytify-dashboard']     = esc_html__( 'Dashboard' , 'wp-analytify' );
			$menus['analytify-refresh-stats'] = esc_html__( 'Refresh Statistics' , 'wp-analytify' );

			$menus = apply_filters( 'analytify_admin_bar_menu', $menus );

			if ( current_user_can( 'manage_options' ) ) {
				$menus['analytify-settings'] = esc_html__( 'Settings' , 'wp-analytify' );
			}

			foreach ( $menus as $id => $title ) {

				// Add refresh stat button in admin bar.
				if ( $id === 'analytify-refresh-stats' ) {

					$url = admin_url( 'admin-post.php?action=analytify_delete_cache' );

					$wp_admin_bar->add_node(
						array(
							'parent' => 'analytify',
							'id'     => $id,
							'title'  => $title,
							'href'   => add_query_arg(
								array(
									'analytify_delete_cache_nonce' => wp_create_nonce( 'analytify_delete_cache' ),
								),
								$url
							),
						)
					);
					continue;
				}
				$wp_admin_bar->add_node( array(
					'parent' => 'analytify',
					'id'     => $id,
					'title'  => $title,
					'href'   => get_admin_url( null, 'admin.php?page=' . $id ),

				));
			}
		}

	}
}

$admin_bar = new WP_ANALYTIFY_ADMIN_BAR();
$admin_bar->init();
