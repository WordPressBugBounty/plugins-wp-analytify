<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName -- File naming is acceptable for this plugin structure
/**
 * Analytify Host Analytics Abstract Class
 *
 * This abstract class provides the base functionality for hosting analytics files
 * locally, handling different tracking modes and file management.
 *
 * @package WP_Analytify
 * @since 1.0.0
 */

// Exit if accessed directly.
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
		 * @var string
		 */
		public $remote_file_url;

		/**
		 * Tracking ID.
		 *
		 * @var string
		 */
		public $tracking_id;

		/**
		 * File contents.
		 *
		 * @var string
		 */
		public $file_contents;

		/**
		 * Tracking mode.
		 *
		 * @var string
		 */
		public $tracking_mode;

		/**
		 * Constructor - sets up the analytics hosting.
		 *
		 * Set's the remote file url, creates Analytify cache directory
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
		 * Set the URL of remote gtag library.
		 *
		 * @return void
		 * @since 5.0.6
		 */
		public function set_all_values() {
			$this->remote_file_url = Analytify_Host_Analytics::GTAG_URL . '/gtag/js?id=' . $this->tracking_id;
		}

		/**
		 * Check if Analytify cache directory exists and create it if not.
		 *
		 * @return void
		 * @since 5.0.6
		 */
		public function create_dir_rec() {

			if ( ! file_exists( defined( 'WP_ANALYTIFY_LOCAL_DIR' ) ? WP_ANALYTIFY_LOCAL_DIR : '' ) ) {

				wp_mkdir_p( defined( 'WP_ANALYTIFY_LOCAL_DIR' ) ? WP_ANALYTIFY_LOCAL_DIR : '' );

			}
		}

		/**
		 * Download the gtag library and generate a random alias for the file.
		 *
		 * This function is responsible for downloading the gtag library
		 * and deleting the existing locally hosted file.
		 * It generates a new random alias for newly downloaded file and
		 * assigns the alias to that file.
		 *
		 * @return void
		 * @since 5.0.6
		 */
		public function download_file() {

			$file_contents = wp_remote_get( $this->remote_file_url );
			$logger        = analytify_get_logger();
			if ( class_exists( 'QM' ) ) {
				if ( class_exists( 'QM' ) ) {
					QM::info( 'Analytify: Downloading analytics file from remote URL.' );
				}
			}

			if ( is_wp_error( $file_contents ) ) {

				$logger->warning( sprintf( 'Error occured while downloading analytics file: %1$s - %2$s', $file_contents->get_error_code(), $file_contents->get_error_message() ), array( 'source' => 'analytify_analytics_file_errors' ) );
				if ( class_exists( 'QM' ) ) {
					if ( class_exists( 'QM' ) ) {
						QM::warning( 'Analytify: Error occured while downloading analytics file: ' . $file_contents->get_error_code() . ' - ' . $file_contents->get_error_message(), array( 'source' => 'analytify_analytics_file_errors' ) );
					}
				}

				return;

			}

			$file_alias = $this->get_file_alias() ?? $this->tracking_mode . '.js';

			if ( $file_alias && file_exists( ( defined( 'WP_ANALYTIFY_LOCAL_DIR' ) ? WP_ANALYTIFY_LOCAL_DIR : '' ) . $file_alias ) ) {

				$deleted = unlink( ( defined( 'WP_ANALYTIFY_LOCAL_DIR' ) ? WP_ANALYTIFY_LOCAL_DIR : '' ) . $file_alias ); // phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Direct file operations are acceptable for analytics file hosting

				if ( ! $deleted ) {
					$logger->warning( 'File could not be deleted due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
					if ( class_exists( 'QM' ) ) {
						if ( class_exists( 'QM' ) ) {
							QM::warning( 'Analytify: File could not be deleted due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
						}
					}
				}
			}

			$file_alias = bin2hex( random_bytes( 4 ) ) . '.js';

			$write = file_put_contents( ( defined( 'WP_ANALYTIFY_LOCAL_DIR' ) ? WP_ANALYTIFY_LOCAL_DIR : '' ) . $file_alias, $file_contents['body'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Direct file operations are acceptable for analytics file hosting

			if ( ! $write ) {
				$logger->warning( 'File could not be saved due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
				if ( class_exists( 'QM' ) ) {
					if ( class_exists( 'QM' ) ) {
						QM::warning( 'Analytify: File could not be saved due to some error', array( 'source' => 'analytify_analytics_file_errors' ) );
					}
				}
			}

			$this->set_file_alias( $this->tracking_mode, $file_alias );
		}

		/**
		 * Get file alias.
		 *
		 * @return string|null
		 */
		public function get_file_alias() {
			return $this->file_contents;
		}

		/**
		 * Set file alias for tracking mode.
		 *
		 * @param string $tracking_mode The tracking mode.
		 * @param string $file_alias The file alias.
		 * @return void
		 */
		public function set_file_alias( $tracking_mode, $file_alias ) {
			$this->tracking_mode = $tracking_mode;
			$this->file_contents = $file_alias;
		}
	}
}
