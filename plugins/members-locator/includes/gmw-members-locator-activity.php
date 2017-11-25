<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Record location activity
 * 
 * @param  [type] $args [description]
 * 
 * @return [type]       [description]
 */
function gmw_record_member_location_activity( $user_id, $location ) {
   
    if ( ! function_exists( 'bp_activity_add' ) ) {
    	return false;
    }
    
    global $bp;

    // get user ID
    $new_address = apply_filters( 'gmw_fl_activity_address_fields', $location['formatted_address'], $location, $user_id );
        
    // get user link
    $from_user_link = bp_core_get_userlink( $user_id );
    
    // get user's current address if exists
    $current_address = ! empty( $_COOKIE['gmw_ul_formatted_address'] ) ? '&daddr='. str_replace( ' ', '+', urldecode( $_COOKIE['gmw_ul_formatted_address'] ) ) : '';
    
    // region and language
    $region   = '&region='.gmw_get_option( 'general_settings', 'country_code', 'us' );
    $language = '&hl='.gmw_get_option( 'general_settings', 'langauge_code', 'en' );
    
    // activity updated text
    $activity_text = apply_filters( 'gmw_fl_update_activity_text', __( '%s updated new location at %s', 'GMW' ), $user_id, $new_address, $location );
    
    // generate activity arguments
    $args = apply_filters( 'gmw_fl_activity_update_args', array(
		'user_id'           => $user_id,
		'action'            => sprintf( esc_attr( $activity_text ), $from_user_link, '<span class="gmw-fl-activity-map-marker gmw-icon-location"></span><a target="_blank" href="https://maps.google.com/maps?f=d'.esc_attr( $language ).esc_attr( $region ).'&geocode=&saddr='.esc_attr( $new_address ). esc_attr( $current_address ).'&ie=UTF8&z=12" >' . esc_attr( $new_address ) . '</a>' ),
		'content'           => '',
		'primary_link'      => bp_core_get_userlink( $user_id, false, true ),
		'component'         => $bp->gmw_location->id,
		'type'              => 'gmw_location',
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => gmdate( 'Y-m-d H:i:s' )
    ), $user_id, $location, $current_address );
    
    // generate activity
    $activity_id = bp_activity_add( $args );

    // do something after activity updated
    do_action( 'gmw_fl_after_activity_updated', $activity_id, $args, $user_id, $location );

    return $activity_id;
}
?>