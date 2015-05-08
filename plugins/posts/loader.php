<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW Post Types Addon class.
 */
class GMW_Post_Types_Addon {

    /**
     * 
     * __construct function.
     */
    public function __construct() {
	
        define( 'GMW_PT_DB_VERSION', '1.2' );
        define( 'GMW_PT_PATH', GMW_PATH . '/plugins/posts/' );
        define( 'GMW_PT_URL', GMW_URL . '/plugins/posts/' );
		
        //include files in admin
     	if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-admin.php' );
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-db.php' );
        }

        //include files in front-end or when doing ajax
        if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
        	include( 'includes/gmw-pt-search-query-class.php' );
        	include( 'includes/gmw-pt-template-functions.php' );
            include( 'includes/gmw-pt-functions.php' );
            
            if ( class_exists( 'GMW_Single_Location' ) ) {
            	include( 'includes/gmw-pt-single-post-location-class.php' );
            } else {
            	include( 'includes/gmw-pt-shortcodes.php' );
            }
        }
    }
}
new GMW_Post_Types_Addon();