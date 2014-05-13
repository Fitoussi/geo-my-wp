<?php
function gmw_fl_activity_actions() {
	global $bp;

	// Bail if activity is not active
	if ( ! bp_is_active( 'activity' ) )
		return false;

	bp_activity_set_action( $bp->gmw_location->id, 'gmw_location', __( "Member's location updated", 'GMW' ) );
	
	do_action( 'gmw_fl_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'gmw_fl_activity_actions' );