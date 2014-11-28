<?php
/**
 * Export Post types locations to CSV
 *
 * @package GMW
 * @since   2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) 
	exit;

/**
 * GMW_PT_Locations_Export class
 *
 * @since 2.5
 */
class GMW_PT_Locations_Export extends GMW_Export {
	
	/**
	 * Export type "post types locations"
	 * @var string
	 * @since 2.5
	 */
	public $export_type = 'pt_locations';

	/**
	 * Set the export headers
	 *
	 * @access public
	 * @since  2.5
	 * @return void
	 */
	public function headers() {
		
		ignore_user_abort( true );

		set_time_limit( 0 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'gmw_pt_locations_export_filename', 'gmw-export-' . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since  2.5
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
				'post_id'  			=> __( 'post_id',   		'GMW' ),
				'feature'  			=> __( 'feature', 			'GMW' ),
				'post_status'    	=> __( 'post_status', 		'GMW' ),
				'post_type'     	=> __( 'post_type', 		'GMW' ),
				'post_title' 		=> __( 'post_title', 		'GMW' ),
				'lat' 				=> __( 'lat', 				'GMW' ),
				'long'     			=> __( 'long', 				'GMW' ),
				'street'    		=> __( 'street', 			'GMW' ),
				'apt'  				=> __( 'apt', 				'GMW' ),
				'city'      		=> __( 'city', 				'GMW' ),
				'state' 			=> __( 'state', 			'GMW' ),
				'state_long'     	=> __( 'state_long', 		'GMW' ),
				'zipcode'   		=> __( 'zipcode', 			'GMW' ) ,
				'country'      		=> __( 'country', 			'GMW' ) ,
				'country_long' 		=> __( 'country_long', 		'GMW' ),
				'address'  			=> __( 'address', 			'GMW' ),
				'formatted_address'	=> __( 'formatted_address', 'GMW' ),
				'phone'     		=> __( 'phone', 			'GMW' ),
				'fax'     			=> __( 'fax', 				'GMW' ),
				'email'   			=> __( 'email', 			'GMW' ),
				'website'   		=> __( 'website', 			'GMW' ),
				'map_icon'   		=> __( 'map_icon', 			'GMW' )
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
		
		$locations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}places_locator`");
	
		foreach ( $locations as $location ) {
			
			$postID = $location->post_id;
			
			$data[] = array(
					'post_id'  			=> $postID,
					'feature'  			=> $location->feature,
					'post_status'    	=> ( get_post_status( $postID ) ) ? get_post_status( $postID ) : $location->post_status,
					'post_type'     	=> ( get_post_type( $postID ) ) ? get_post_type( $postID ) : $location->post_type,
					'post_title' 		=> ( get_the_title( $postID ) ) ? get_the_title( $postID ) : $location->post_title,
					'lat' 				=> $location->lat,
					'long'     			=> $location->long,
					'street'    		=> $location->street,
					'apt'  				=> $location->apt,
					'city'      		=> $location->city,
					'state' 			=> $location->state,
					'state_long'     	=> $location->state_long,
					'zipcode'   		=> $location->zipcode,
					'country'      		=> $location->country,
					'country_long' 		=> $location->country_long,
					'address'  			=> $location->address,
					'formatted_address'	=> $location->formatted_address,
					'phone'     		=> $location->phone,
					'fax'     			=> $location->fax,
					'email'   			=> $location->email,
					'website'   		=> $location->website,
					'map_icon'   		=> $location->map_icon
			);
		}

		$data = apply_filters( 'gmw_export_get_data', $data );
		$data = apply_filters( 'gmw_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
