<?php

/**
 * GMW Function - Save member's location
 */
function addLocation() {
	global $wpdb, $bp;
	$address = array();
	$address_apt = array();
	$map_icon = '_default.png';
	
	//if( is_multisite() ) $wppl_options = get_site_option('wppl_site_options'); else $wppl_options = get_option('wppl_fields');
	$gmw_options = get_option('wppl_fields');
	$mem_id = $bp->loggedin_user->id;
	
	$address = $_POST['address'];
	$address_apt = $_POST['address'];
	
	if ( isset($address['apt']) ) unset($address['apt']);
	$address = implode(' ', $address);
	$address_apt = implode(' ', $address_apt);
	
	if ( isset($_POST['map_icon']) ) $map_icon = $_POST['map_icon'];
	
	$emptyAddress = str_replace(' ','',$address);
	
	/* delete address */
	if ( ( !isset( $emptyAddress ) || empty( $emptyAddress) ) && ( !isset( $_POST['wppl_enter_lat'] ) || empty( $_POST['wppl_enter_lat'] ) ) ) :
	
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM wppl_friends_locator WHERE member_id=%d",$mem_id
			)
		);
	
		do_action('gmw_fl_after_delete_location', $mem_id);
		
		echo __('Data successfully deleted!','GMW');
		
		die();
	
	else :
		// get address details from google
		$returned_address = GmwConvertToCoords($address);
		
		$orgAddress = $_POST['address'];
		$street 	   = ( isset($orgAddress['street']) ) ? $orgAddress['street'] : false;
		$apt           = ( isset($orgAddress['apt']) ) ? $orgAddress['apt'] : false;
		$city          = ( isset($orgAddress['city']) ) ? $orgAddress['city'] : false;
		/*$state  	   = ( isset($orgAddress['state']) ) ? $returned_address['state_short'] : false;
		$state_long    = ( isset($orgAddress['state']) ) ? $returned_address['state_long'] : false;
		$zipcode  	   = ( isset($orgAddress['zipcode']) ) ? $orgAddress['zipcode'] : false;
		$country       = ( isset($orgAddress['country']) ) ? $returned_address['country_short'] : false;
		$country_long  = ( isset($orgAddress['country']) ) ? $returned_address['country_long'] : false; */

		if ( $wpdb->replace( 'wppl_friends_locator', array( 
			'member_id'			=> $mem_id,	
			'street' 			=> $street,
			'apt' 				=> $apt,
			'city' 				=> $city,
			'state' 			=> $returned_address['state_short'], 
			'state_long' 		=> $returned_address['state_long'], 
			'zipcode'			=> $orgAddress['zipcode'],
			'country' 			=> $returned_address['country_short'],
			'country_long' 		=> $returned_address['country_long'],
			'address'			=> $address_apt,
			'formatted_address' => $returned_address['formatted_address'],
			'lat'				=> $returned_address['lat'],
			'long'				=> $returned_address['long'],
			'map_icon'			=> $map_icon	
		))===FALSE) : 
			
			echo "Error";
	 	
		else :
			echo __('Data successfully saved!','GMW');
			
			$activity_address = $returned_address['formatted_address'];
			$activity_id = gmw_location_record_activity( $args = array('location' => apply_filters('gmw_fl_activity_address_fields', $activity_address, $returned_address, $gmw_options) ) );
		
			do_action('gmw_fl_after_save_location', $mem_id, $returned_address, $address_apt, $street, $apt, $city);
			
		endif;
		
		die();
	endif;
}
add_action('wp_ajax_addLocation', 'addLocation');

?>