<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * class GMW_Location_Tables_Export
 *
 * Export locations database table to CSV file 
 *
 * @since 3.0
 */
class GMW_Locations_Table_Export extends GMW_Export {
	
	/**
	 * Export type "post types locations"
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

		$cols = array(
			'ID'				=> 'ID',
			'object_type'		=> 'object_type',
			'object_id'			=> 'object_id',
			'blog_id'			=> 'blog_id',
			'user_id'			=> 'user_id',
			'parent'			=> 'parent',
			'status'        	=> 'status',
			'featured'			=> 'featured',
			'title'				=> 'title',
			'latitude'          => 'latitude',
			'longitude'         => 'longitude',
			'street_number' 	=> 'street_number',
			'street_name' 		=> 'street_name',
			'street'			=> 'street',
			'premise'       	=> 'premise',
			'neighborhood'  	=> 'neighborhood',
			'city'          	=> 'city',
			'county'			=> 'county',
			'region_name'   	=> 'region_name',
			'region_code'   	=> 'region_code',
			'postcode'      	=> 'postcode',
			'country_name'  	=> 'country_name',
			'country_code'  	=> 'country_code',
			'address'			=> 'address',
			'formatted_address' => 'formatted_address',
			'place_id'			=> 'place_id',
			'map_icon'			=> 'map_icon',
			'created'       	=> 'created',
			'updated'       	=> 'updated'
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
		
		$locations = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}gmw_locations", ARRAY_A
		);
		
		$locations = apply_filters( 'gmw_export_locations_table', $locations );

		return $locations;
	}
}

/**
 * class GMW_Locationmeta_Table_Export
 *
 * Export location meta database table to CSV file 
 *
 * @since 3.0
 */
class GMW_Locationmeta_Table_Export extends GMW_Export {
	
	/**
	 * Export type "post types locations"
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
			'meta_id'			=> 'meta_id',
			'location_id'		=> 'location_id',
			'meta_key'			=> 'meta_key',
			'meta_value'		=> 'meta_value'
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
			"SELECT * FROM {$wpdb->prefix}gmw_locationmeta", ARRAY_A
		);
		
		$locationmeta = apply_filters( 'gmw_export_locationmeta_table', $locationmeta );

		return $locationmeta;
	}
}