<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gmw Function - Covert object to array
 * 
 * @since  2.5
 * @param  object
 * @return Array/multidimensional array
 */
function gmw_object_to_array( $data ) {
	
	if ( is_array( $data ) || is_object( $data ) ) {
		$result = array();
		foreach ( $data as $key => $value ) {
			$result[ $key ] = gmw_object_to_array( $value );
		}
		return $result;
	}
	return $data;
}

/**
 * Processes all GMW actions sent via POST and GET by looking for the 'gmw_action'
 * request and running do_action() to call the function
 *
 * @since 2.5
 * @return void
 */
function gmw_process_actions() {
	
	if ( isset( $_POST['gmw_action'] ) ) {
		do_action( 'gmw_' . $_POST['gmw_action'], $_POST );
	}

	if ( isset( $_GET['gmw_action'] ) ) {
		do_action( 'gmw_' . $_GET['gmw_action'], $_GET );
	}
}
add_action( 'admin_init', 'gmw_process_actions' );

/**
 * Processes all GMW admin notices
 * Notice type pass through $_GET['gmw_notice'] and notice through $_GET['gmw_notice_stastus']
 *
 * @since 2.5
 * @author Eyal Fitoussi
 */

function gmw_output_admin_notices() {
		
	//check if notice exist
	if ( empty( $_GET['gmw_notice'] ) && empty( $_POST['gmw_notice'] ) )
		return;
	
	$gmw_messages = array();
	$gmw_messages = apply_filters( 'gmw_admin_notices_messages', $gmw_messages );
	
	$notice_type   = ( isset( $_GET['gmw_notice'] ) ) ? $_GET['gmw_notice'] : $_POST['gmw_notice'];
	$notice_status = ( isset( $_GET['gmw_notice_status'] ) ) ? $_GET['gmw_notice_status'] : $_POST['gmw_notice_status'];
	
	?>
    <div class="<?php echo $notice_status;?>">
    	<p><?php echo $gmw_messages[$notice_type]; ?></p>
    </div>
	<?php
	    	 		
}
add_action( 'admin_notices', 'gmw_output_admin_notices' );

/**
 * Insert location into wp_places_locator table. Table will be updated with data only if location doesnt exist
 * 
 * @since 2.5
 * @author Eyal Fitoussi
 * @param unknown_type $location
 */
function gmw_insert_pt_location_to_db( $location ) {

	global $wpdb;

	//check if location already exist in database
	$check_location = $wpdb->get_col( "SELECT `post_id` FROM `{$wpdb->prefix}places_locator` WHERE `post_id` = {$location['post_id']}", 0 );
	
	//insert location only if not already exist
	if ( empty( $check_location ) ) {
		
		$data = $wpdb->query( $wpdb->prepare(
				"INSERT INTO `{$wpdb->prefix}places_locator`
				( `post_id`, `feature`, `post_status`, `post_type`, `post_title`, `lat`,
				`long`, `street`, `apt`, `city`, `state`, `state_long`, `zipcode`, `country`,
				`country_long`, `address`, `formatted_address`, `phone`, `fax`, `email`, `website`, `map_icon` )
				VALUES ( %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )",
				array(
						$location['post_id'],
						$location['feature'],
						$location['post_status'],
						$location['post_type'],
						$location['post_title'],
						$location['lat'],
						$location['long'],
						$location['street'],
						$location['apt'],
						$location['city'],
						$location['state'],
						$location['state_long'],
						$location['zipcode'],
						$location['country'],
						$location['country_long'],
						$location['address'],
						$location['formatted_address'],
						$location['phone'],
						$location['fax'],
						$location['email'],
						$location['website'],
						$location['map_icon']
				) ));
		
		return $data;
	} else {
		return false;
	}
}

/**
 * replace location in wp_places_locator table. If not exist, data will be insert into table. 
 * Otherwise if exists the old data will be replaced with the new one.
 *
 * @since 2.5
 * @author Eyal Fitoussi
 * @param unknown_type $location
 */
function gmw_replace_pt_location_in_db( $location ) {

	global $wpdb;

	$wpdb->replace( $wpdb->prefix . 'places_locator', array(
			'post_id'           => $location['post_id'],
			'feature'           => $location['feature'],
			'post_status'       => $location['post_status'],
			'post_type'         => $location['post_type'],
			'post_title'        => stripslashes( $location['post_title'] ),
			'lat'               => $location['lat'],
			'long'              => $location['long'],
			'street'            => stripslashes( $location['street'] ),
			'apt'               => $location['apt'],
			'city'              => stripslashes( $location['city'] ),
			'state'             => stripslashes( $location['state'] ),
			'state_long'        => stripslashes( $location['state_long'] ),
			'zipcode'           => stripslashes( $location['zipcode'] ),
			'country'           => stripslashes( $location['country'] ),
			'country_long'      => stripslashes( $location['country_long'] ),
			'address'           => stripslashes( $location['address'] ),
			'formatted_address' => stripslashes( $location['formatted_address'] ),
			'phone'             => $location['phone'],
			'fax'               => $location['fax'],
			'email'             => $location['email'],
			'website'           => $location['website'],			
			'map_icon'          => $location['map_icon'],
	));
}