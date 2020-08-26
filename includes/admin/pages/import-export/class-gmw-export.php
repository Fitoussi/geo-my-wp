<?php
/**
 * Export Class
 *
 * This is the base class for all export methods.
 *
 * @package     GMW
 * @subpackage  Admin/Tools
 * @author      This is a class originaly written by Pippin Williamson and modified based on the needs of GEO my WP. Thank you!
 * @since       2.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Export' ) ) :

	/**
	 * GMW_Export Class
	 *
	 * @since 2.5
	 */
	class GMW_Export {

		/**
		 * Our export type. Used for export-type specific filters/actions
		 *
		 * @var string
		 * @since 2.5
		 */
		public $export_type = 'default';

		/**
		 * Can we export?
		 *
		 * @access public
		 * @since 2.5
		 * @return bool Whether we can export or not
		 */
		public function can_export() {
			return (bool) apply_filters( 'gmw_export_capability', current_user_can( 'manage_options' ) );
		}

		/**
		 * Set the export headers
		 *
		 * @access public
		 * @since 2.5
		 * @return void
		 */
		public function headers() {

			ignore_user_abort( true );

			set_time_limit( 30 );

			nocache_headers();

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=gmw-export-' . $this->export_type . '-' . date( 'm-d-Y' ) . '.csv' );
			header( 'Expires: 0' );
		}

		/**
		 * Set the CSV columns
		 *
		 * @access public
		 * @since 2.5
		 * @return array $cols All the columns
		 */
		public function csv_cols() {

			$cols = array();

			return $cols;
		}

		/**
		 * Retrieve the CSV columns
		 *
		 * @access public
		 * @since 2.5
		 * @return array $cols Array of the columns
		 */
		public function get_csv_cols() {

			$cols = $this->csv_cols();

			return apply_filters( 'gmw_export_csv_cols_' . $this->export_type, $cols );
		}

		/**
		 * Output the CSV columns
		 *
		 * @access public
		 * @since 2.5
		 * @uses GMW_Export::get_csv_cols()
		 * @return void
		 */
		public function csv_cols_out() {

			$cols = $this->get_csv_cols();
			$i    = 1;

			foreach ( $cols as $col_id => $column ) {
				echo '"' . addslashes( $column ) . '"';
				echo count( $cols ) === $i  ? '' : ',';
				$i++;
			}
			echo "\r\n";
		}

		/**
		 * Get the data being exported
		 *
		 * @access public
		 * @since 2.5
		 * @return array $data Data for Export
		 */
		public function get_data() {

			// Just a sample data array.
			$data = array(
				0 => array(
					'post_id' => '',
				),
				1 => array(
					'post_id' => '',
				),
			);

			$data = apply_filters( 'gmw_export_get_data', $data );
			$data = apply_filters( 'gmw_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		/**
		 * Output the CSV rows
		 *
		 * @access public
		 * @since 2.5
		 * @return void
		 */
		public function csv_rows_out() {

			$data = $this->get_data();
			$cols = $this->get_csv_cols();

			// Output each row.
			foreach ( $data as $row ) {

				$i = 1;

				foreach ( $row as $col_id => $column ) {

					// Make sure the column is valid.
					if ( array_key_exists( $col_id, $cols ) ) {
						echo '"' . addslashes( $column ) . '"';
						echo count( $cols ) === $i ? '' : ',';
						$i++;
					}
				}
				echo "\r\n";
			}
		}

		/**
		 * Perform the export
		 *
		 * @access public
		 * @since 2.5
		 * @uses GMW_Export::can_export()
		 * @uses GMW_Export::headers()
		 * @uses GMW_Export::csv_cols_out()
		 * @uses GMW_Export::csv_rows_out()
		 * @return void
		 */
		public function export() {

			if ( ! $this->can_export() ) {
				wp_die( __( 'You do not have permission to export data.', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ) );
			}

			// Set headers.
			$this->headers();

			// Output CSV columns (headers).
			$this->csv_cols_out();

			// Output CSV rows.
			$this->csv_rows_out();

			die();
		}
	}

endif;
