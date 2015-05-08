<?php
/**
 * GMW FL function - get members location from database or cache
 * @param unknown_type $user_id
 */
function gmw_get_member_info_from_db( $user_id ) {

    $info = wp_cache_get('gmw_member_info', $group = $user_id);

    if ( false === $info ) {

        global $wpdb;

        $info = $wpdb->get_row(
        		$wpdb->prepare(
        				"SELECT * FROM `wppl_friends_locator`
        				WHERE member_id = %d", $user_id
        		));
        wp_cache_set( 'gmw_member_info', $info, $user_id );
    }

    return ( isset($info) ) ? $info : false;
}

/**
 * GMW FL function - display members's info
 */
function gmw_get_member_info( $args ) {
    global $bp, $members_template;

    $args = apply_filters('gmw_fl_get_member_info_args', $args);

    //default shortcode attributes
    extract(
    		shortcode_atts(
    				array(
    						'user_id'  => false,
    						'info' 	   => 'formatted_address',
    						'message'  => '',
    						'divider'  => ' '
    				), $args)
    );

    if ( $user_id != false ) {
        $user_id = $user_id;

    } elseif ( isset( $bp->displayed_user->id ) ) {
        $user_id = $bp->displayed_user->id;
    
    } elseif ( isset( $members_template->member->id ) ) {
    	$user_id = $members_template->member->id;
    
    } else return;

    $member_info = gmw_get_member_info_from_db( $user_id );

    if (!isset($member_info) || $member_info === false)
        return $message;

    $mem_loc = array();

    $info = explode(',', str_replace(' ', '', $info));
    $count    = 1;

    foreach ($info as $lc) :

        $loc       = $member_info->$lc;
        if ($count < count($info))
            $loc .= $divider;
        $mem_loc[] = $loc;
        $count++;

    endforeach;

    return apply_filters('gmw_fl_get_member_info', implode(' ', $mem_loc), $member_info);

}
add_shortcode('gmw_member_info', 'gmw_get_member_info');

function gmw_fl_member_info($args = false) {
    echo gmw_get_member_info($args);
}

function gmw_fl_delete_bp_user($user_id) {
	global $wpdb;

	$wpdb->query(
			$wpdb->prepare(
					"DELETE FROM wppl_friends_locator WHERE member_id=%d", $user_id
			)
	);
	do_action('gmw_fl_after_delete_location', $user_id);
}
add_action('delete_user', 'gmw_fl_delete_bp_user');
?>