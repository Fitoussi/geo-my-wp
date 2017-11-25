<?php
/**
 * Try to get the user ID
 *
 * We first checking if BuddyPress is activated and if so we will try to get
 *
 * the user ID using $bp global or within the loop.
 *
 * Otherwise, we will check for the logged in user ID
 * 
 * @return [type] [description]
 */
function gmw_try_get_user_id() {

    $user_id = 0;

    // if BuddyPress activated we look for user ID
    // using $bp
    if ( class_exists( 'BuddyPress' ) ) {

        global $bp, $members_template;

        // look for member ID in the loop
        if ( ! empty( $members_template->member->id ) ) {
            
            $user_id = $members_template->member->id;

        // look for displayed user ID
        } elseif ( ! empty( $bp->displayed_user->id ) ) {
        
            $user_id = $bp->displayed_user->id;

         } elseif ( ! empty( $bp->loggedin_user->id ) ) {
        
            $user_id = $bp->loggedin_user->id;
        } 
    }

    // if not found via BuddyPress look for loggedin user ID
    if ( empty( $user_id ) ) {

        $user_id = get_current_user_id();
    }
    
    return $user_id;
}

/**
 * Check if user exists
 *
 * This is in case that the user was deleted but the location
 *
 * still exists in database. 
 * 
 * @param  integer $user_id [description]
 * 
 * @return [type]           [description]
 */
function gmw_is_user_exists( $user_id = 0 ) {

    if ( empty( $user_id ) ) {
        return false;
    }

    global $wpdb;
    
    // look for user in database
    $user_id = $wpdb->get_var( 
        $wpdb->prepare( "
            SELECT ID 
            FROM $wpdb->users 
            WHERE ID = %d", 
            $user_id 
        ) 
    );

    // abort if user not exists
    if ( empty( $user_id ) ) {
        
        return false;
    
    } else {

        return true;
    }
}

/**
 * get the user location from database
 *
 * @since 3.0
 * 
 * @param  boolean $post_id [description]
 * @return [type]           [description]
 */
function gmw_get_user_location( $user_id = 0 ) {
	
    // if no specific user ID pass, look for logged in user object
    if ( empty( $user_id ) ) {
            
        // try to get user ID
        $user_id = gmw_try_get_user_id();

        // abort if no user ID
        if ( empty( $user_id ) ) {
            return;
        }
    }

    // get post location from database
    return GMW_Location::get_location( 'user', $user_id );
}

/**
 * get the user location meta from database
 *
 * @since 3.0
 * 
 * @param  boolean $post_id [description]
 * @return [type]           [description]
 */
function gmw_get_user_location_meta( $user_id = 0, $meta_keys = array() ) {

    // if no specific user ID pass, look for logged in user object
    if ( empty( $user_id ) ) {
        
        // try to get user ID
        $user_id = gmw_try_get_user_id();

        // abort if no user ID
        if ( empty( $user_id ) ) {
            return;
        }
    }

    // get user location from database
    return GMW_Location::get_location_meta_by_object( 'user', $user_id, $meta_keys );
}

/**
 * get the user location data from database
 *
 * This function returns location data and user data such as user name, displya name , email...
 *
 * The function also verify that the user exists in database. That is in case
 *
 * That the user was deleted but the location still exists in database.
 *
 * @since 3.0
 * 
 * @param  boolean $post_id user ID
 * 
 * @return object  user data + location 
 */
function gmw_get_user_location_data( $user_id = 0, $output = OBJECT, $cache = true) {
    
    if ( empty( $fields ) ) {

        $fields = array(
            'gmw.ID', 
            'gmw.latitude', 
            'gmw.longitude', 
            'gmw.latitude as lat', 
            'gmw.longitude as lng',
            'gmw.address', 
            'gmw.formatted_address', 
            'gmw.street_number', 
            'gmw.street_name', 
            'gmw.street', 
            'gmw.city', 
            'gmw.region_code',
            'gmw.region_name', 
            'gmw.postcode', 
            'gmw.country_code', 
            'gmw.country_name',
            'featured',
            'users.ID as user_id', 
            'users.user_login', 
            'users.user_nicename', 
            'users.display_name', 
            'users.user_email'
        );
    }

    $fields = implode( ',', apply_filters( 'gmw_get_user_location_data_fields', $fields, $user_id ) );

    // if no specific user ID pass, look for logged in user object
    if ( empty( $user_id ) ) {
            
        // try to get user ID
        $user_id = gmw_try_get_user_id();

        // abort if no user ID
        if ( empty( $user_id ) ) {
            return;
        }
    }

    $location = $cache ? wp_cache_get( $user_id, 'gmw_users_location_data' ) : false;

    if ( false === $location ) {

        global $wpdb;

        $gmw_table  = $wpdb->prefix . 'gmw_locations';
        $user_table = $wpdb->prefix . 'users';

        $location  = $wpdb->get_row( 
            $wpdb->prepare( "
                SELECT     $fields
                FROM       $gmw_table  gmw
                INNER JOIN $user_table users
                ON         gmw.object_id = users.ID
                WHERE      gmw.object_type = 'user'
                AND        gmw.object_id = %d
            ", $user_id ), 
        OBJECT );

        // save to cache if location found
        if ( ! empty( $location ) ) {
            wp_cache_set( $user_id, $location, 'gmw_users_location_data' );
            wp_cache_set( $location->ID, $location, 'gmw_location_data' );
        }
    }
      
    // if no location found
    if ( empty( $location ) ) {
        return null;
    }

    // convert to array if needed
    if ( $output == ARRAY_A || $output == ARRAY_N ) {
        $location = gmw_to_array( $location, $output );
    }
    
    return $location;
}

/**
 * Delete user location
 *
 * @since 3.0
 * 
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function gmw_delete_user_location( $user_id = 0, $delete_meta = false ) {

    if ( empty( $user_id ) ) {
        return;
    }

    do_action( 'gmw_before_user_location_deleted', $user_id );

    GMW_Location::delete_location( 'user', $user_id, $delete_meta );

    do_action( 'gmw_after_user_location_deleted', $user_id );
}

/**
 * Delete user from GEO my WP database when user deleted from WordPress
 *
 * @since 3.0
 * 
 * @param  int $user_id user ID
 * @return [type]          [description]
 */
function gmw_delete_user_location_action( $user_id ) {
    
    gmw_delete_user_location( $user_id, true );
}
add_action( 'delete_user', 'gmw_delete_user_location_action' );

/**
 * Change user location status
 *
 * @since 3.0
 * 
 * @param  integer $post_id [description]
 * @param  integer $status  [description]
 * @return [type]           [description]
 */
function gmw_user_location_status( $user_id = 0, $status = 1 ) {

    $status = $status == 1 ? 1 : 0;

    global $wpdb;
    
    $wpdb->query( 
        $wpdb->prepare( "
            UPDATE {$wpdb->prefix}gmw_locations 
            SET   `status`      = $status 
            WHERE `object_type` = 'user' 
            AND   `object_id`   = %d", 
            array( $user_id ) 
        ) 
    );
}

/**
 * Get a specific or all user address fields
 *
 * @since 3.0
 * 
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_user_address( $args = array() ) {
    
    // to support older versions. should be removed in the future
    if ( empty( $args['fields'] ) && ! empty( $args['info'] ) ) {

        trigger_error( 'The "info" shortcode attribute of the shortcode [gmw_member_address] is deprecated since GEO my WP version 3.0. Please use the shortcode attribute "fields" instead.', E_USER_NOTICE );

        $args['fields'] = $args['info'];
    }

    //default shortcode attributes
    $attr = shortcode_atts( array(
        'user_id'   => 0,
        'fields'    => 'formatted_address',
        'separator' => ', '
    ), $args );

    // if no specific user ID pass, look for logged in user ID
    if ( empty( $attr['user_id'] ) ) {

        // try to get user ID
        $attr['user_id'] = gmw_try_get_user_id();
    }

    $fields = explode( ',', $attr['fields'] );

    // get post address fields
    return gmw_get_address_fields( 'user', $attr['user_id'], $fields, $attr['separator'] );
}
add_shortcode( 'gmw_user_address', 'gmw_get_user_address' );

	function gmw_user_address( $args = array() ) {
		echo gmw_get_user_address( $args );
	}

/**
 * Add/update user location
 * 
 * use this function if you want to add or update user location.
 * 
 * function accepts an accociative array as below:
 * 
 * $args = array (
 *     'user_id' => 1,      //must pass post id in order to work
 *     'address' => false,  //can be eiter single line of full address field or an array of the adress components - 
 *      array( 
 *          'street'  => 'Lincoln st', 
 *          'apt'     => '',
 *          'city'    => 'Hollywood', 
 *          'state'   => 'Florida',
 *          'country' => 'USA'
 *      );
 *  );
 */
function gmw_update_user_location( $user_id = 0, $address = false, $force_refresh = false ) {

    if ( ! gmw_is_user_exists( $user_id ) ) {
        return;
    }

    return gmw_update_location( 'user', $user_id, $address, $user_id, $force_refresh );
}

/**
 * Update user location metas
 *
 * Can update/create single or multiple user location metas.
 *
 * For a single location meta pass the user ID, meta key and meta value
 *
 * For multiple metas pass the user ID and an array of meta_key => meta_value pairs
 *
 * @since 3.0
 * 
 * @param  integer $user_id    [description]
 * @param  array   $metadata   [description]
 * @param  boolean $meta_value [description]
 * @return [type]              [description]
 */
function gmw_update_user_location_meta( $user_id = 0, $metadata = array(), $meta_value = false ) {

    // look for location ID
    $location_id = gmw_get_location_id( 'user', $user_id );

    // abort if location not exists
    if ( empty( $location_id ) ) {
        return false;
    }

    GMW_Location::update_location_metas( $location_id, $metadata, $meta_value );
}