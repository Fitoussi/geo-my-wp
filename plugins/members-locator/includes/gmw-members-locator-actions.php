<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
    
function gmw_members_locator_activity_actions() {
	
	global $bp;

	// abort if activity is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	bp_activity_set_action( $bp->gmw_location->id, 'gmw_location', __( 'Member\'s location updated', 'GMW' ) );
	
	do_action( 'gmw_fl_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'gmw_members_locator_activity_actions' );

/**
 * Record member activity after location updated
 *
 * @since 3.0
 * 
 * @param  object $location   updated location
 * @param  array $form_values location form values
 * 
 * @return mixed              
 */
function gmw_after_member_location_updated( $location, $form_values ) {

	// proceed only if BP member Location updated
	if ( empty( $form_values['gmw_lf_slug'] ) || $form_values['gmw_lf_slug'] != 'members_locator' ) {
		return;
	}

    // verify User ID
    if ( empty( $form_values['gmw_location_form']['object_id'] ) ) {
        
        trigger_error( 'Invalid user ID.', E_USER_NOTICE );

        return;

    } else {

        $user_id = $form_values['gmw_location_form']['object_id'];
    }

    // hook from previous versions of GEO my WP
    // do something after location updated
    do_action( 'gmw_fl_after_location_saved', $user_id , $location, $form_values );

    // disable activity update
    if ( ! apply_filters( 'gmw_fl_disable_location_activity_update', false ) && function_exists( 'gmw_record_member_location_activity' ) ) {
        return gmw_record_member_location_activity( $user_id, $location );
    }
}
add_action( 'gmw_lf_after_user_location_updated', 'gmw_after_member_location_updated', 10 , 2 );