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

        $this->addons   = get_option( 'gmw_addons' );
        $this->settings = get_option( 'gmw_options' );

        define( 'GMW_PT_DB_VERSION', '1.1' );
        define( 'GMW_PT_PATH', GMW_PATH . '/plugins/posts/' );
        define( 'GMW_PT_URL', GMW_URL . '/plugins/posts/' );

        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ) );
        add_action( 'gmw_posts_shortcode', array( $this, 'search_functions' ), 10, 2 );

        self::includes();

    }

    private function includes() {

        //include files in admin
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-admin.php' );
            include( GMW_PT_PATH . 'includes/admin/gmw-pt-db.php' );
        }

        //include files in front end
        if ( !is_admin() ) {

            include_once GMW_PT_PATH . 'includes/gmw-pt-functions.php';

            //include single location shortcocde
            if ( isset( $this->settings[ 'features' ][ 'single_location_shortcode' ] ) )
                include_once GMW_PT_PATH . 'includes/gmw-pt-shortcodes.php';
        }

    }

    /**
     * frontend_scripts function.
     *
     * @access public
     * @return void
     */
    public function frontend_register_scripts() {

        wp_register_script( 'gmw-pt-map', GMW_PT_URL . 'assets/js/map.js', array( 'jquery' ), GMW_VERSION, true );

    }

    /**
     * Search functions
     * 
     * @param $form
     * @param $results
     */
    public function search_functions( $form, $results ) {

        include_once GMW_PT_PATH . 'includes/gmw-pt-search-functions.php';
        new GMW_PT_Search_Query( $form, $results );

    }

}

new GEO_Post_Types_Addon();
