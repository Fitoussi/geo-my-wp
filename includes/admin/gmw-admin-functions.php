<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update addon_data
 * 
 * @param  [type] $addon [description]
 * @return [type]        [description]
 */
/*function gmw_update_addon_data( $addon ) {

	if ( empty( $addon ) ) {
		return;
	}

	// get addons data from database
	$addons_data = get_option( 'gmw_addons' );

	if ( empty( $addons_data ) ) {
		$addons_data = array();
	}

	// update addon data
	$addons_data[$addon['slug']] = $addon;

	// save new data in database
	update_option( 'gmw_addons', $addons_data );

	global $gmw_addons;

	$gmw_addons = $addons_data;

} */

/**
 * Processes all GMW admin notices
 * Notice type pass via $_GET['gmw_notice'] and notice status via $_GET['gmw_notice_stastus']
 *
 * @since 2.5
 * @author Eyal Fitoussi
 */
function gmw_output_admin_notices() {
		
	//check if notice exist
	if ( empty( $_GET['gmw_notice'] ) && empty( $_POST['gmw_notice'] ) ) {
		return;
	}
	
	$gmw_messages = apply_filters( 'gmw_admin_notices_messages', array( 
		'posts_db_table_updated'   => __( 'GEO my WP posts locations db table successfully updated.', 'geo-my-wp' ),
		'members_db_table_updated' => __( 'GEO my WP members locations db table successfully updated.', 'geo-my-wp' ),
		'tracking_allowed'		   => __( 'Thank you for helping us improve GEO my WP.', 'geo-my-wp' )
	) );
	
	$notice_type   = isset( $_GET['gmw_notice'] ) ? $_GET['gmw_notice'] : $_POST['gmw_notice'];
	$notice_status = isset( $_GET['gmw_notice_status'] ) ? $_GET['gmw_notice_status'] : $_POST['gmw_notice_status'];
	
	$allowed = array(
		'a'  => array( 
			'title' => array(),
			'href'  => array()
		),
		'p'  => array(),
		'em' => array()
	);
	?>
    <div class="<?php echo $notice_status;?>">
    	<p><?php echo isset( $gmw_messages[$notice_type] ) ? wp_kses( $gmw_messages[$notice_type], $allowed ) : ''; ?></p>
    </div>
	<?php
	    	 		
}
add_action( 'admin_notices', 'gmw_output_admin_notices' );

/**
 * Generate link to update plugins page
 * 
 * @param  [type] $basename [description]
 * @return [type]           [description]
 */
function gmw_get_update_addon_link( $basename ) {
    return '<a href="'.admin_url( 'plugins.php' ).'#'.esc_attr( strtolower( preg_replace('/[-]+/i', '-', str_replace( ' ', '-', $basename ) ) ) ).'" title="Plugins Page" style="color:white;text-decoration: underline"><i class="fa fa-refresh"></i>  Update now</a>';
}

/**
 * Insert location into wp_places_locator table. Table will be updated with data only if location doesnt exist
 * 
 * @since 2.5
 * @author Eyal Fitoussi
 * @param unknown_type $location
 */
/*
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
*/

/**
 * replace location in wp_places_locator table. If not exist, data will be insert into table. 
 * Otherwise if exists the old data will be replaced with the new one.
 *
 * @since 2.5
 * @author Eyal Fitoussi
 * @param unknown_type $location
 */
/*
function gmw_replace_pt_location_in_db( $location ) {

	global $wpdb;

	$wpdb->replace( $wpdb->prefix . 'places_locator', 
		array(
			'post_id'           => $location['post_id'],
			'feature'           => $location['feature'],
			'post_status'       => $location['post_status'],
			'post_type'         => $location['post_type'],
			'post_title'        => $location['post_title'],
			'lat'               => $location['lat'],
			'long'              => $location['long'],
			'street_number'     => $location['street_number'],
			'street_name'       => $location['street_name'],
			'street'            => $location['street'],
			'apt'               => $location['apt'],
			'city'              => $location['city'],
			'state'             => $location['state'],
			'state_long'        => $location['state_long'],
			'zipcode'           => $location['zipcode'],
			'country'           => $location['country'],
			'country_long'      => $location['country_long'],
			'address'           => $location['address'],
			'formatted_address' => $location['formatted_address'],
			'phone'             => $location['phone'],
			'fax'               => $location['fax'],
			'email'             => $location['email'],
			'website'           => $location['website'],			
			'map_icon'          => $location['map_icon'],
		)
	);
}
*/

function gmw_get_post_types_array() {

    $output = array();
    
    foreach ( get_post_types() as $post ) {
        $output[$post] = get_post_type_object( $post )->labels->name . ' ( '.$post.' )';
    }

    return $output;
}

/**
 * GEO my WP top credits
 * @return [type] [description]
 */
function gmw_admin_helpful_buttons() {
	
	if ( ! empty( $_GET['page'] ) && $_GET['page'] != 'gmw-forms' ) { ?>
		<span style="font-size:14px;margin-right:5px;"> - <?php echo sprintf( __( 'GEO my WP developed by %s', 'geo-my-wp' ), 'Eyal Fitoussi' ); ?></span>
		<a class="button action gmw-donate" title="Donate to GEO my WP" href="https://www.paypal.me/fitoussi" target="_blank"><i style="color:red;margin-right:4px;" class="gmw-icon-heart"></i><?php _e( 'Donate', 'geo-my-wp' ); ?></a>
	<?php } ?>
	<span class="gmw-helpful-links-wrapper">
		<a class="button action" title="GEO my WP Official Website" href="http://geomywp.com" target="_blank"><i class="dashicons dashicons-welcome-view-site" style="font-size:18px;margin-top:4px"></i>GEOmyWP.com</a>
		<a class="button action" title="GEO my WP Extensions" href="http://geomywp.com/extensions" target="_blank" style="color:green"><i class="gmw-icon-puzzle"></i>Extensions</a>
		<a class="button action" title="GEO my WP Demo" href="http://demo.geomywp.com" target="_blank"><i class="gmw-icon-monitor"></i><?php _e( 'Demo', 'geo-my-wp' ); ?></a>
		<a class="button action" title="GEO my WP documentation" href="http://docs.geomywp.com" target="_blank"><i class="gmw-icon-doc-text"></i><?php _e( 'Docs', 'geo-my-wp' ); ?></a>
		<a class="button action" title="GEO my WP support" href="http://geomywp.com/support" target="_blank"><i class="gmw-icon-lifebuoy"></i><?php _e( 'Support', 'geo-my-wp' ); ?></a>
		<a class="button action" title="GEO my WP on GitHub" href="https://github.com/Fitoussi/GEO-my-WP" target="_blank"><i class="gmw-icon-github"></i>GitHub</a>
		<a class="button action" title="Show your support" href="https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5" target="_blank"><i style="color:orange" class="gmw-icon-star"></i><?php _e( 'Love', 'geo-my-wp' ); ?></a>
		<a class="button action" title="GEO my WP on Facebook" href="https://www.facebook.com/geomywp" target="_blank"><i style="color:blue;font-size: 16px" class="gmw-icon-facebook-squared"></i><?php _e( 'Like', 'geo-my-wp' ); ?></a>
		<a class="button action" title="GEO my WP on Twitter" href="https://twitter.com/GEOmyWP" target="_blank"><i style="color:lightblue;font-size: 16px;" class="gmw-icon-twitter"></i><?php _e( 'Follow', 'geo-my-wp' ); ?></a>
		<?php do_action( 'gmw_admin_helpful_buttons' ); ?>
	</span>
	<?php
}