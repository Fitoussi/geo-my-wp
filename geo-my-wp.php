<?php
/*
  Plugin Name: GEO my WP
  Plugin URI: http://www.geomywp.com
  Description: Add location to any post types, pages or members (using Buddypress) and create an advance proximity search forms.
  Version: 2.4.4
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

        define( 'GMW_VERSION', '2.4.3' );
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
            if ( isset( $this->settings['features']['current_location_shortcode'] ) ) {
                include( GMW_PATH . '/includes/geo-my-wp-shortcodes.php' );
            }
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
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ), 10 );
        add_filter( 'clean_url', array( $this, 'clean_google_url' ), 99, 3 );
		
        add_action( 'wp_enqueue_scripts', array( $this, 'load_dashicons' ) );
        
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
        
        //include sweetdate theme functions when needed
        $active_theme = wp_get_theme();
         if ( $active_theme->get('Name') == 'Sweetdate' || $active_theme->get('Template') ==  'Sweetdate' || $active_theme->get('Template') == 'sweetdate' )
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

    	$googleApi = ( isset( $this->settings['general_settings']['google_api'] ) && !empty( $this->settings['general_settings']['google_api'] ) ) ? '&key=' . $this->settings['general_settings']['google_api'] : '';
        $region	   = ( isset( $this->settings['general_settings']['country_code'] ) && !empty( $this->settings['general_settings']['country_code'] ) ) ? '&region=' .$this->settings['general_settings']['country_code'] : '';
        $language  = ( isset( $this->settings['general_settings']['language_code'] ) && !empty( $this->settings['general_settings']['language_code'] ) ) ? '&language=' .$this->settings['general_settings']['language_code'] : '';
    	
        //register google maps api
        if ( !wp_script_is( 'google-maps', 'enqueue' ) )
            wp_enqueue_script( 'google-maps', ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js?libraries=places'.$googleApi.'&sensor=false&ver=3.13'.$region.$language, array( 'jquery' ), false );

        //enqueue gmw style and script
        wp_enqueue_script( 'gmw-js', GMW_URL . '/assets/js/gmw.js', array( 'jquery' ), GMW_VERSION, true );
        wp_enqueue_style( 'gmw-style', GMW_URL . '/assets/css/style.css' );
        wp_localize_script( 'gmw-js', 'gmwSettings', $this->settings );


    }
	
    function load_dashicons() {
    	wp_enqueue_style( 'dashicons' );
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
            
            if ( isset( $params['widget'] ) ) $this->form['in_widget'] = true;
            		
            if ( isset( $this->form['search_results']['display_map'] ) && $this->form['search_results']['display_map'] == "shortcode" )
                do_action( 'gmw_' . $this->form['prefix'] . '_before_map', $this->form );

            echo gmw_get_results_map( $this->form );

            do_action( 'gmw_' . $this->form['prefix'] . '_after_map', $this->form );

            //display results when in results page				
        } elseif ( $params['form'] == 'results' ) {

            if ( isset( $_GET['action'] ) && $_GET['action'] == "gmw_post" ) {

                $this->form = $this->forms[$_GET['gmw_form']];
                if ( isset( $params['widget'] ) ) $this->form['in_widget'] = true;
                
                do_action( 'gmw_' . $this->form['form_type'] . '_shortcode', $this->form, $results = ( $params['form'] == 'results' ) ? true : false );
            }

            //display results when form submitted
        } else {

            $this->form = $this->forms[$params['form']];
            
            if ( isset( $params['widget'] ) ) $this->form['in_widget'] = true;
            $this->form['search_results']['results_page'] = ( isset( $this->form['search_results']['results_page'] ) && !empty( $this->form['search_results']['results_page'] ) ) ? get_permalink( $this->form['search_results']['results_page'] ) : false;

            //if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
            if ( isset( $params['widget'] ) && (!isset( $this->form['search_results']['results_page'] ) || empty( $this->form['search_results']['results_page'] ) ) ) {
                $this->form['search_results']['results_page'] = get_permalink( $this->settings['general_settings']['results_page'] );
                
            }

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
    
    /**
     * GMW function - Geocode address
     * @since 1.0
     * @author Eyal Fitoussi
     * @author This function inspired by a script written by Pippin Williamson - Thank you
     */
    public static function geocoder( $address, $force_refresh = false ) {
    
    	$address_hash = md5( $address );
    
    	$coordinates = get_transient( 'gmw_geocoded_'.$address_hash );
    
    	if ( $force_refresh || $coordinates === false ) {
    
    		$args       = array( 'address' => urlencode( $address ), 'sensor' => 'false' );
    		$url        = add_query_arg( $args, 'http://maps.googleapis.com/maps/api/geocode/json' );
    		$response 	= wp_remote_get( $url );
    
    		if( is_wp_error( $response ) )
    			return;
    
    		$data = wp_remote_retrieve_body( $response );
    
    		if( is_wp_error( $data ) )
    			return;
    
    		if ( $response['response']['code'] == 200 ) {
    		
    			$data = json_decode( $data );
    
    			if ( $data->status === 'OK' ) {
    
    				$location['street']        = false;
    				$location['apt']           = false;
    				$location['city']          = false;
    				$location['state_short']   = false;
    				$location['state_long']    = false;
    				$location['zipcode']       = false;
    				$location['country_short'] = false;
    				$location['country_long']  = false;
    
    				$coordinates = $data->results[0]->geometry->location;
    
    				$location['lat']               = $coordinates->lat;
    				$location['lng']               = $coordinates->lng;
    				$location['formatted_address'] = (string) $data->results[0]->formatted_address;
    
    				$address_componenets = $data->results[0]->address_components;
    
    				foreach ($address_componenets as $ac) :
    
    				if ($ac->types[0] == 'street_number') :
    				$street_number = esc_attr($ac->long_name);
    				endif;
    
    				if ($ac->types[0] == 'route') :
    				$street_f = esc_attr($ac->long_name);
    
    				if (isset($street_number) && !empty($street_number))
    					$location['street'] = $street_number . ' ' . $street_f;
    				else
    					$location['street'] = $street_f;
    				endif;
    
    				if ($ac->types[0] == 'subpremise')
    					$location['apt'] = esc_attr($ac->long_name);
    
    				if ($ac->types[0] == 'locality')
    					$location['city'] = esc_attr($ac->long_name);
    
    				if ($ac->types[0] == 'administrative_area_level_1') :
    
    				$location['state_short'] = esc_attr($ac->short_name);
    				$location['state_long']  = esc_attr($ac->long_name);
    
    				endif;
    
    				if ($ac->types[0] == 'postal_code') {
    					$location['zipcode'] = esc_attr($ac->long_name);
    				}
    					
    				if ($ac->types[0] == 'country') :
    
    				$location['country_short'] = esc_attr($ac->short_name);
    				$location['country_long']  = esc_attr($ac->long_name);
    
    				endif;
    
    				endforeach;
    
    				do_action( 'gmw_geocoded_location', $location );
    
    				// cache coordinates for 3 months
    				set_transient( 'gmw_geocoded_'.$address_hash, $location, 3600*24*30*3 );
    
    			} elseif ( $data->status === 'ZERO_RESULTS' ) {
    				return array( 'error' => __( 'No location found for the entered address.', 'GMW' ) );
    			} elseif ( $data->status === 'INVALID_REQUEST' ) {
    				return array( 'error' => __( 'Invalid request. Did you enter an address?', 'GMW' ) );
    			} elseif ( $data->status === 'OVER_QUERY_LIMIT' ) { 
    				return array( 'error' => __( 'Something went wrong while retrieving your location.', 'GMW' ) . '<span style="display:none">OVER_QUERY_LIMIT</span>' );
    			} else {
    				return array( 'error' => __( 'Something went wrong while retrieving your location.', 'GMW' ) );
    			}
    
    		} else {
    			return array( 'error' => __( 'Unable to contact Google API service.', 'GMW' ) );
    		}
    
    	} else {
    		// return cached results
    		$location = $coordinates;
    	}
    
    	return $location;
    
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