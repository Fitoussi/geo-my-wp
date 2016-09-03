<?php

/**
 * GMW Location function - post location to activity
 * @param $args
 * @return boolean|Ambigous <number, boolean, unknown, mixed>
 */
function gmw_location_record_activity( $args ) {
   
    if ( !function_exists( 'bp_activity_add' ) )
    	return false;
    
    global $bp;
    
    $settings 		= get_option( 'gmw_options' );
    $user_id 		= ( isset( $args['user_id'] ) ) ?  $args['user_id'] : $bp->loggedin_user->id; 
    $from_user_link = bp_core_get_userlink( $user_id );
    $defaults 	    = array(
        'id'                => false,
        'location'          => false,
        'user_id'           => $user_id,
        'action'            => false,
        'content'           => '',
        'primary_link'      => bp_core_get_userlink( $user_id, false, true ),
        'component'         => $bp->gmw_location->id,
        'type'              => 'gmw_location',
        'item_id'           => false,
        'secondary_item_id' => false,
        'recorded_time'     => gmdate("Y-m-d H:i:s")
    );

    $r = wp_parse_args( $args, $defaults );
    
    extract($r);

    $cCity    = ( !empty( $_COOKIE['gmw_city'] ) )    ? urldecode( $_COOKIE['gmw_city'] )    : false;
    $cState   = ( !empty( $_COOKIE['gmw_state'] ) )   ? urldecode( $_COOKIE['gmw_state'] )   : false;
    $cCountry = ( !empty( $_COOKIE['gmw_country'] ) ) ? urldecode( $_COOKIE['gmw_country'] ) : false;
    $region	  = ( !empty( $settings['general_settings']['country_code'] ) )  ? '&region=' .$settings['general_settings']['country_code'] : '';
    $language = ( !empty( $settings['general_settings']['language_code'] ) ) ? '&hl=' .$settings['general_settings']['language_code'] : '';
    	
    $activity_text = apply_filters( 'gmw_fl_update_activity_text', 'Updated new location at' );

    $activity_id = bp_activity_add( apply_filters( 'gmw_fl_activity_args', array(
    		//'id' => $id,
    		'user_id'           => $user_id,
    		'action'            => sprintf( __( '%s %s %s', 'GMW' ), $from_user_link, $activity_text, '<span class="gmw-fl-activity-map-marker fa fa-map-marker"></span><a target="_blank" href="https://maps.google.com/maps?f=d'.$language.''.$region.'&geocode=&saddr='.$location.'&daddr='.$cCity.' '.$cState.' '.$cCountry.'&ie=UTF8&z=12" >' . $location . '</a>' ),
    		'content'           => $content,
    		'primary_link'      => $primary_link,
    		'component'         => $component,
    		'type'              => $type,
    		'item_id'           => $item_id,
    		'secondary_item_id' => $secondary_item_id,
    		'recorded_time'     => $recorded_time
    ) ) );

    //if ( $type == 'gmw_location' )
        //bp_update_user_meta( $user_id, 'bp_latest_update', array( 'id' => $activity_id, 'content' => wp_filter_kses( $content ) ) );

    return $activity_id;
}
?>