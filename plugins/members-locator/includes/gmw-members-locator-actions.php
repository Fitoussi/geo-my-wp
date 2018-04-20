<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter activity by location
 *
 * @param  [type] $where [description]
 * @param  [type] $args  [description]
 * @return [type]        [description]
 */
function gmw_fl_filter_location_activity( $where, $args ) {

	if ( isset( $_COOKIE['bp-activity-filter'] ) && $_COOKIE['bp-activity-filter'] == 'gmw_member_location_updated' ) {

		if ( ! isset( $where['filter_sql'] ) ) {

			$where['filter_sql'] = " a.type IN ( 'gmw_member_location_updated' )";

		} else {
			$where['filter_sql'] .= " AND a.type IN ( 'gmw_member_location_updated' )";
		}
	}

	return $where;
}
add_filter( 'bp_activity_get_where_conditions', 'gmw_fl_filter_location_activity', 50, 2 );

/**
 * Get the member name to save as location title before location is saved
 *
 * @param  [type] $location [description]
 * @return [type]           [description]
 */
function gmw_fl_get_member_name( $location ) {

	$name = bp_core_get_username( $location['object_id'] );

	if ( ! empty( $name ) ) {
		$location['title'] = sanitize_text_field( stripslashes( $name ) );
	}

	return $location;
}
add_filter( 'gmw_lf_user_location_args_before_location_updated', 'gmw_fl_get_member_name' );

/**
 * Add location item to dropdown menu filter
 *
 * @return [type] [description]
 */
function gmw_fl_location_filter_options() {
?>
	<option value="gmw_member_location_updated"><?php _e( 'Member Location', 'geo-my-wp' ); ?></option>
<?php
}
add_action( 'bp_activity_filter_options', 'gmw_fl_location_filter_options', 10 );
add_action( 'bp_member_activity_filter_options', 'gmw_fl_location_filter_options', 10 );

/**
 * Register location activity for members
 *
 * @return [type] [description]
 */
function gmw_members_locator_activity_actions() {

	// abort if activity is not active
	if ( ! bp_is_active( 'activity' ) ) {
		return false;
	}

	bp_activity_set_action( buddypress()->members->id, 'gmw_member_location_updated', __( 'Member location updated', 'geo-my-wp' ) );

	do_action( 'gmw_fl_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'gmw_members_locator_activity_actions' );

/**
 * Record member activity after location updated
 *
 * @since 3.0
 *
 * @param  object $location   updated location
 * @param  array  $form_values location form values
 *
 * @return mixed
 */
function gmw_after_member_location_updated( $user_location, $form_values ) {

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
	do_action( 'gmw_fl_after_location_saved', $user_id, $user_location, $form_values );

	// filter allows to disable activity update
	if ( ! apply_filters( 'gmw_fl_disable_location_activity_update', false ) ) {

		include_once( 'gmw-members-locator-activity.php' );

		// update activity
		if ( function_exists( 'gmw_record_member_location_activity' ) ) {
			gmw_record_member_location_activity( $user_id, $user_location );
		}
	}
}
add_action( 'gmw_lf_after_user_location_updated', 'gmw_after_member_location_updated', 10, 2 );
