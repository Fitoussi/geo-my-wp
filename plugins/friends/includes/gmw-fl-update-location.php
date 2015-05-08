<?php

/**
 * GMW Function - Save member's location
 */
function gmw_fl_update_location() {

    global $wpdb, $bp;

    parse_str($_POST['formValues'], $location);

    $location['gmw_map_icon'] = ( isset($location['gmw_map_icon']) ) ? $location['gmw_map_icon'] : '_default.png';

    $location = apply_filters('gmw_fl_location_before_updated', $location, $bp->displayed_user->id);

    if ( $wpdb->replace('wppl_friends_locator', array(
                'member_id'         => $bp->displayed_user->id,
                'street_number'     => $location['gmw_street_number'],
                'street_name'       => $location['gmw_street_name'],
                'street'            => $location['gmw_street'],
                'apt'               => $location['gmw_apt'],
                'city'              => $location['gmw_city'],
                'state'             => $location['gmw_state'],
                'state_long'        => $location['gmw_state_long'],
                'zipcode'           => $location['gmw_zipcode'],
                'country'           => $location['gmw_country'],
                'country_long'      => $location['gmw_country_long'],
                'address'           => $location['gmw_address'],
                'formatted_address' => $location['gmw_formatted_address'],
                'lat'               => $location['gmw_lat'],
                'long'              => $location['gmw_long'],
                'map_icon'          => $location['gmw_map_icon']
            ) ) === FALSE ) :

        echo __( 'There was a problem saving your location.', 'GMW' );

    else :

        echo __( 'Location successfully saved!', 'GMW' );

    	$args = array( 
    			'location' 	=> apply_filters('gmw_fl_activity_address_fields', $location['gmw_formatted_address'], $location ),
    			'user_id'	=> $bp->displayed_user->id
    	);
    
        $activity_id = gmw_location_record_activity( $args );

        do_action( 'gmw_fl_after_location_saved', $bp->displayed_user->id, $location );

    endif;

    die();

}

add_action('wp_ajax_gmw_fl_update_location', 'gmw_fl_update_location');

/**
 * GMW Function - Delete member's location
 */
function gmw_fl_delete_location() {

    global $wpdb, $bp;

    $wpdb->query($wpdb->prepare("DELETE FROM wppl_friends_locator WHERE member_id = %d", $bp->displayed_user->id));

    do_action('gmw_fl_after_location_deleted', $bp->displayed_user->id);

    die( __( 'Location successfully deleted!', 'GMW' ) );

}

add_action('wp_ajax_gmw_fl_delete_location', 'gmw_fl_delete_location');
?>