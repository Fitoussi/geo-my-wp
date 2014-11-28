<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW Post Types Addon class.
 */
class GEO_Post_Types_Addon {

    /**
     * 
     * __construct function.
     */
    public function __construct() {

        define( 'GMW_PT_DB_VERSION', '1.1' 						  );
        define( 'GMW_PT_PATH', 		 GMW_PATH . '/plugins/posts/' );
        define( 'GMW_PT_URL', 		 GMW_URL . '/plugins/posts/'  );

        add_action( 'gmw_pt_results_shortcode', array( $this, 'search_functions' ), 20, 2 );

     	if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-admin.php' );
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-db.php' );
        }

        //include files in front end
        if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
        	include( 'includes/gmw-pt-template-functions.php' );
            include( 'includes/gmw-pt-functions.php' );
            include( 'includes/gmw-pt-shortcodes.php' );
        }
    }

    /**
     * Search functions
     * 
     * @param $form
     * 
     */
    public function search_functions( $form ) {
        include_once GMW_PT_PATH . 'includes/gmw-pt-search-query-class.php';
        new GMW_PT_Search_Query( $form );
    }
}
new GEO_Post_Types_Addon();