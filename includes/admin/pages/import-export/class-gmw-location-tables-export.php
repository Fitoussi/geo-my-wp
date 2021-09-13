<?php
/**
 * GEO my WP Locations and Locations Meta table exporter.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class GMW_Location_Tables_Export
 *
 * Export locations database table to CSV file
 *
 * @since 3.0
 */
class GMW_Locations_Table_Export extends GMW_Export {

	/**
	 * Export type "post types locations"
	 *
	 * @var string
	 * @since 2.5
	 */
	public $export_type = 'gmw_locations';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  2.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array();

		foreach ( GMW_Location::$format as $key => $value ) {
			$cols[ $key ] = $key;
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  2.5
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		global $wpdb;

		$locations = $wpdb->get_results(
			"SELECT * FROM {$wpdb->base_prefix}gmw_locations",
			ARRAY_A
		); // WPCS: cache ok, db call ok.

		$locations = apply_filters( 'gmw_export_locations_table', $locations );

		return $locations;
	}
}

/**
 * Class GMW_Locationmeta_Table_Export
 *
 * Export location meta database table to CSV file
 *
 * @since 3.0
 */
class GMW_Locationmeta_Table_Export extends GMW_Export {

	/**
	 * Export type "post types locations"
	 *
	 * @var string
	 * @since 2.5
	 */
	public $export_type = 'gmw_locationmeta';

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  2.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'meta_id'     => 'meta_id',
			'location_id' => 'location_id',
			'meta_key'    => 'meta_key', // WPCS: slow query ok.
			'meta_value'  => 'meta_value', // WPCS: slow query ok.
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @access public
	 * @since  2.5
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		global $wpdb;

		$locationmeta = $wpdb->get_results(
			"SELECT * FROM {$wpdb->base_prefix}gmw_locationmeta",
			ARRAY_A
		); // WPCS: db call ok, cache ok.

		$locationmeta = apply_filters( 'gmw_export_locationmeta_table', $locationmeta );

		return $locationmeta;
	}
}
