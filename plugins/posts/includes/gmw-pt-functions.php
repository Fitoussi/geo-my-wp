<?php
/**
 * GMW PT function - get post location from database or cache
 * @param $post_id
 */
function gmw_get_post_location_from_db( $post_id ) {

    global $wpdb;

    $location = wp_cache_get( 'gmw_post_location', $group = $post_id );

    if ( false === $location ) {

    	$location = $wpdb->get_row(
    			$wpdb->prepare("
    					SELECT * FROM {$wpdb->prefix}places_locator
    					WHERE `post_id` = %d", array( $post_id )
    			) );

        wp_cache_set( 'gmw_post_location', $location, $post_id );
    }

    return ( isset( $location ) ) ? $location : false;
}

/**
 * GMW Function - get single location information
 */
function gmw_get_post_info( $args ) {

	//default shortcode attributes
	extract(
			shortcode_atts(
					array(
							'info'    => 'formatted_address',
							'post_id' => 0,
							'divider' => ' '
					), $args )
	);

    /*
     * check if user entered post id
     */
    if ( $post_id == 0 ) :

        global $post;
        $post_id = $post->ID;

    endif;

    $post_info = gmw_get_post_location_from_db( $post_id );

    $info = explode( ',', str_replace( ' ', '', $info ) );

    $output = '';
    $count  = 1;

    foreach ( $info as $rr ) {
        if ( isset( $post_info->$rr ) ) {
            $output .= $post_info->$rr;

            if ( $count < count( $info ) )
                $output .= $divider;
            $count++;
        }
    }
    return $output;

}
add_shortcode( 'gmw_post_info', 'gmw_get_post_info' );

	function gmw_post_info( $args ) {
		echo gmw_get_post_info( $args );
	}

/**
 * when post status changes - change it in our table as well 
 */
function gmw_pt_filter_transition_post_status( $new_status, $old_status, $post ) {

    global $wpdb;
    $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}places_locator SET post_status = %s WHERE `post_id` = %d", array( $new_status, $post->ID ) ) );

}
add_action( 'transition_post_status', 'gmw_pt_filter_transition_post_status', 10, 3 );

/**
 *  delete info from our database after post was deleted 
 */
function gmw_pt_delete_location() {
    global $wpdb, $post;
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}places_locator WHERE `post_id` = %d", array( $post->ID ) ) );
}
add_action( 'before_delete_post', 'gmw_pt_delete_location' );

/**
 *  delete info from our database after post was deleted
 */
function gmw_pt_trash_location( $post_id ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}places_locator SET `post_status` = 'trash' WHERE `post_id` = %d", array( $post_id ) ) );
}
add_action( 'wp_trash_post', 'gmw_pt_trash_location' );
?>