<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Analytify_Host_Analytics_Abstract' ) ) {
	/**
	 * This class will be used as base class that
	 * will do all the heavy lifting for hosting analytics files.
	 * based on the type of tracking mode passed.
	 */
	abstract class Analytify_Host_Analytics_Abstract {

		/**
		 * Remote File URL.
		 *
		 * @var $remote_file_url the custom url containing measurement id for gtag library.
		 */
		public $remote_file_url;

		/**
		 * Set's the remote file url
		 * Creates Analytify cache directory
		 * and call the function to download gtag library from google servers.
		 *
		 * @since 5.0.6
		 */
		public function __construct() {

			$this->set_all_values();

			$this->create_dir_rec();

			$this->download_file();
		}

		/**
		 * This function set the url of remote gtag library.
		 *
		 * @since 5.0.6
		 */
		public function set_all_values() {
			$this->remote_file_url = Analytify_Host_Analytics::GTAG_URL . '/gtag/js?id=' . $this->tracking_id;
		}

		/**
		 * This function checks if Analytify cache directory exists.
		 * and if not it creates it.
		 *
		 * @since 5.0.6
		 */
		public function create_dir_rec() {

			if ( ! file_exists( ANALYTIFY_LOCAL_DIR ) ) {

				return wp_mkdir_p( ANALYTIFY_LOCAL_DIR );

			}
		}

		/**
		 * This function is responsible for downloading the gtag library
		 * and delete the existing locally hosted file.
		 * It generate a new random alias for newly download file and
		 * assign the alias to that file.
		 *
		 * @since 5.0.6
		 */
		public function download_file() {

			$file_contents = wp_remote_get( $this->remote_file_url );
			$logger        = analytify_get_logger();

			if ( is_wp_error( $file_contents ) ) {

				$logger->warning( sprintf( 'Error occured while downloading analytics file: %1$s - %2$s', $file_contents->get_error_code(), $file_contents->get_error_message() ), array( 'source' => 'analytify_analytics_file_errors' ) );

				return $file_contents->get_error_code() . ': ' . $this->file_contents->get_error_message();

			}

			$file_alias = $this->get_file_alias() ?? $this->tracking_mode . '.js';

			if ( $file_alias && file_exists( ANALYTIFY_LOCAL_DIR . $file_alias ) ) {

				$deleted = unlink( ANALYTIFY_LOCAL_DIR . $file_alias );

				if ( ! $deleted ) {
					$logger->warning( 'File could not be deleted due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
				}
			}

			$file_alias = bin2hex( random_bytes( 4 ) ) . '.js';

			$write = file_put_contents( ANALYTIFY_LOCAL_DIR . $file_alias, $file_contents['body'] );

			if ( ! $write ) {
				$logger->warning( 'File could not be saved due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
			}

			$this->set_file_alias( $this->tracking_mode, $file_alias );

			return ANALYTIFY_LOCAL_DIR . $file_alias;

		}
	}
}
