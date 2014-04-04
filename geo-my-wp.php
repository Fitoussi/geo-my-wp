<?php
/*
  Plugin Name: GEO my WP
  Plugin URI: http://www.geomywp.com
  Description: Add location to any post types, pages or members (using Buddypress) and create an advance proximity search forms.
  Version: 2.4.1
  Author: Eyal Fitoussi
  Author URI: http://www.geomywp.com
  License: GPLv2
  Text Domain: GMW
  Domain Path: /languages/
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GEO my WP class.
 */
class GEO_my_WP {

    /**
     * @var GEO my WP
     * 
     * @since 2.4
     */
    private static $instance;

    /**
     * Addons exist in database
     * 
     * @access private
     */
    public $addons;

    /**
     * GEO my WP settings from database
     *
     * @access private
     */
    public $settings;

    /**
     * GEO my WP forms from database
     *
     * @access private
     */
    private $forms;

    /**
     * Main Instance
     *
     * Insures that only one instance of GEO_my_WP exists in memory at any one
     * time.
     *
     * @since 2.4
     * @static
     * @staticvar array $instance
     * @return GEO_my_WP
     */
    public static function instance() {

        if ( !isset( self::$instance ) && !( self::$instance instanceof GEO_my_WP ) ) {

            self::$instance = new GEO_my_WP;
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->actions();
            self::$instance->load_textdomain();
        }

        return self::$instance;

    }

    /**
     * A dummy constructor to prevent GEO my WP from being loaded more than once.
     *
     * @since 2.4
     */
    private function __construct() {

        $this->addons   = get_option( 'gmw_addons' );
        $this->settings = get_option( 'gmw_options' );
        $this->forms    = get_option( 'gmw_forms' );

    }

    /**
     * Setup plugin constants
     *
     * @access private
     * @since 2.4
     * @return void
     */
    private function constants() {

        // Define constants
        if ( !defined( 'GMW_REMOTE_SITE_URL' ) )
            define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' );

        define( 'GMW_VERSION', '2.3.0' );
        define( 'GMW_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'GMW_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
        define( 'GMW_IMAGES', GMW_URL . '/assets/images' );
        define( 'GMW_AJAX', get_bloginfo( 'wpurl' ) . '/wp-admin/admin-ajax.php' );

    }

    /**
     * Include files
     * 
     * @since 2.4
     * 
     */
    private function includes() {
		   	
        //admin functions and files
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include_once( GMW_PATH . '/includes/admin/geo-my-wp-admin.php' );
            include_once( GMW_PATH . '/includes/admin/geo-my-wp-updater.php' );
            
        }

        if ( !is_admin() ) {
            include( GMW_PATH . '/includes/geo-my-wp-functions.php' );
            if ( isset( $this->settings['features']['current_location_shortcode'] ) )
                include( GMW_PATH . '/includes/geo-my-wp-shortcodes.php' );
        }

        ///load posts locator add-on
        if ( GEO_my_WP::gmw_check_addon( 'posts' ) )
            include_once GMW_PATH . '/plugins/posts/connect.php';

        //include widgets
        include_once GMW_PATH . '/includes/geo-my-wp-widgets.php';

    }

    /**
     * Actions
     * 
     * @since 2.4
     */
    public function actions() {

        //include scripts in the front end
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ) );
        add_filter( 'clean_url', array( $this, 'clean_google_url' ), 99, 3 );

        //main gmw shortcode
        add_shortcode( 'gmw', array( $this, 'gmw' ) );
        add_shortcode( 'gmw_results_map', array( $this, 'results_map' ) );

        //init current location widget
        if ( isset( $this->settings['features']['current_location_shortcode'] ) ) {
            add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget" );' ) );
        }

        //init search form widet
        if ( isset( $this->settings['features']['search_form_widget'] ) ) {
            add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Search_Form_Widget" );' ) );
        }

        //initiate posts and friends add-ons
        add_filter( 'gmw_admin_addons_page', array( $this, 'addons_init' ) );

        //load friends locator add-on
        if ( GEO_my_WP::gmw_check_addon( 'friends' ) )
            add_action( 'bp_loaded', array( $this, 'members_locator_addon_init' ), 20 );

        //include sweetdate theme functions
        if ( wp_get_theme() == 'Sweetdate' )
            add_action( 'bp_init', array( $this, 'sweetdate_init' ), 20 );

    }

    /**
     * Include addon function.
     *
     * @access public
     * @return $addons
     */
    public function addons_init( $addons ) {

        $addons[1] = array(
            'name'    => 'posts',
            'title'   => __( 'Post Types Locator', 'GMW' ),
            'desc'    => __( 'Add location to Posts and pages. Create an advance proximity search form to search for locations based on post types, categories, distance and more.', 'GMW' ),
            'license' => false,
            'image'   => false,
            'require' => array(),
        );

        $addons[2] = array(
            'name'    => 'friends',
            'title'   => __( 'Members Locator', 'GMW' ),
            'desc'    => __( 'Let your BuddyPress members add location to thier profile. Create an advance proximity search form to search for members based on location.', 'GMW' ),
            'image'   => false,
            'require' => array(
                'Buddypress Plugin' => array( 'plugin_file' => 'buddypress/bp-loader.php', 'link' => 'http://buddypress.org' )
            ),
            'license' => false
        );

        return $addons;

    }

    public static function gmw_check_addon( $addon ) {

        $addons = get_option( 'gmw_addons' );

        if ( ( isset( $addons[$addon] ) && $addons[$addon] == 'active' ) && (!isset( $_POST['gmw_premium_license'] ) ) ) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Localization
     *
     * @access public
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'GMW', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }

    /**
     * frontend_scripts function.
     *
     * @access public
     * @return void
     */
    public function frontend_register_scripts() {

        //register google maps api
        if ( !wp_script_is( 'google-maps', 'enqueue' ) )
            wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?key=' . $this->settings['general_settings']['google_api'] . '&' . $this->settings['general_settings']['country_code'] . '&sensor=false', array(), false );

        //enqueue gmw style and script
        wp_enqueue_script( 'gmw-js', GMW_URL . '/assets/js/gmw.js', array( 'jquery' ), GMW_VERSION, true );
        wp_enqueue_style( 'gmw-style', GMW_URL . '/assets/css/style.css' );

        if ( isset( $this->settings['general_settings']['auto_locate'] ) )
            wp_localize_script( 'gmw-js', 'autoLocate', $this->settings['general_settings']['auto_locate'] );
        else
            wp_localize_script( 'gmw-js', 'autoLocate', 'false' );

    }

    /**
     * Load members locator add-on component
     *
     */
    function members_locator_addon_init() {
        global $bp;

        include_once GMW_PATH . '/plugins/friends/includes/gmw-fl-component.php';
        $bp->gmw_location = new GMW_Location_Component;

    }

    /**
     * GEO my WP main shortcode
     * @param $params
     */
    public function gmw( $params ) {

        include_once GMW_PATH . '/includes/geo-my-wp-functions.php';

        ob_start();

        //display map
        if ( isset( $params['map'] ) ) {

            $this->form = $this->forms[$params['map']];

            if ( isset( $this->form['search_results']['display_map'] ) && $this->form['search_results']['display_map'] == "shortcode" )
                do_action( 'gmw_' . $this->form['prefix'] . '_before_map', $this->form );

            echo gmw_get_results_map( $this->form );

            do_action( 'gmw_' . $this->form['prefix'] . '_after_map', $this->form );

            //display results when in results page				
        } elseif ( $params['form'] == 'results' ) {

            if ( isset( $_GET['action'] ) && $_GET['action'] == "gmw_post" ) {

                $this->form = $this->forms[$_GET['gmw_form']];

                do_action( 'gmw_' . $this->form['form_type'] . '_shortcode', $this->form, $results = ( $params['form'] == 'results' ) ? true : false );
            }

            //display results when form submitted
        } else {

            $this->form = $this->forms[$params['form']];

            $this->form['search_results']['results_page'] = ( isset( $this->form['search_results']['results_page'] ) && !empty( $this->form['search_results']['results_page'] ) ) ? get_permalink( $this->form['search_results']['results_page'] ) : false;

            //if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
            if ( isset( $params['widget'] ) && (!isset( $this->form['search_results']['results_page'] ) || empty( $this->form['search_results']['results_page'] ) ) )
                $this->form['search_results']['results_page'] = get_permalink( $this->settings['general_settings']['results_page'] );

            do_action( 'gmw_' . $this->form['form_type'] . '_shortcode', $this->form, $results = ( $params['form'] == 'results' ) ? true : false );
        }

        $output_string = ob_get_contents();

        ob_end_clean();

        return $output_string;

    }

    /**
     * make sure google maps API loads properly
     * fix provided by user dfa327 http://wordpress.org/support/topic/google-maps-server-rejected-your-request-proposed-fix
     * Thank you
     */
    public function clean_google_url( $url, $original_url, $_context ) {

        if ( strstr( $url, "googleapis.com" ) !== false ) {
            $url = str_replace( "&", "&", $url ); // or $url = $original_url
        }
        return $url;

    }

    /**
     * Sweetdate theme files
     * 
     * @since 2.4
     * 
     */
    public function sweetdate_init() {

        //admin settings
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include_once GMW_PATH . '/third-party/sweetdate/geo-my-wp-sd-admin.php';
        }

        //include members query only on members page
        if ( bp_current_component() == 'members' )
            include_once GMW_PATH . '/third-party/sweetdate/geo-my-wp-sd-class.php';

    }

}

/**
 *  GMW Instance
 *
 * @since 1.1.1
 * @return GEO my WP Instance
 */
function GMW() {
    return GEO_my_WP::instance();

}
// Init GMW
GMW();
