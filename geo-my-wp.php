<?php
/*
  Plugin Name: GEO my WP
  Plugin URI: http://www.geomywp.com
  Description: Add location to any post types, pages or members (using Buddypress) and create an advance proximity search forms.
  Version: 2.5
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
     * GEO my WP settings from database
     *
     * @access private
     */
    private $settings;

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

        define( 'GMW_VERSION', 	'2.5' );
        define( 'GMW_PATH', 	untrailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'GMW_URL', 		untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
        define( 'GMW_IMAGES', 	GMW_URL . '/assets/images' );
        define( 'GMW_AJAX'	, 	get_bloginfo( 'wpurl' ) . '/wp-admin/admin-ajax.php' );
        define( 'GMW_FILE', 	__FILE__ );
        define( 'GMW_BASENAME', plugin_basename( GMW_FILE ) );		
    }

    /**
     * Include files
     * 
     * @since 2.4
     * 
     */
    public function includes() {
		   	
    	include_once( 'includes/geo-my-wp-deprecated-functions.php' );
    	
    	
        //include admin files
        if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
            include( GMW_PATH . '/includes/admin/geo-my-wp-admin.php' );
            include( GMW_PATH . '/includes/admin/geo-my-wp-updater.php' );
           	include( GMW_PATH . '/includes/admin/geo-my-wp-license-handler.php' );   	
        }

        //include files in front-end
        if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
        	include( 'includes/geo-my-wp-gmw-class.php' );
            include( 'includes/geo-my-wp-template-functions.php' );
            include( 'includes/geo-my-wp-shortcodes.php' );
        }

        ///load posts locator add-on
        if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
            include_once GMW_PATH . '/plugins/posts/connect.php';
        }       
        
        //include widgets
        include_once GMW_PATH . '/includes/geo-my-wp-widgets.php';
    }

    /**
     * Actions
     * 
     * @since 2.4
     */
    public function actions() {
    	
    	//initiate add-ons hook
    	add_filter( 'gmw_admin_addons_page', array( $this, 'addons_init' ), 10 );
    	
        //include scripts in the front end
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ), 10 );
        add_filter( 'clean_url', 		  array( $this, 'clean_google_url' ), 		99, 3 );
        
        //main gmw shortcode
        add_shortcode( 'gmw', array( $this, 'gmw' ) );
        
        //map styles
        add_action( 'wp_footer', array( $this, 'maps_options' ), 5 );
        
        //google places autocomplete
        add_action( 'wp_footer', array( $this, 'google_places_address_autocomplete' ), 10 );
        
        //init widgets
        add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget" );' ) );
        add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Search_Form_Widget" );' 		) );

        //load friends locator add-on
        if ( GEO_my_WP::gmw_check_addon( 'friends' ) && class_exists( 'BuddyPress' ) ) {
            add_action( 'bp_loaded', array( $this, 'members_locator_addon_init' ), 20 );
        }
        
        //include sweetdate theme functions when needed
        $active_theme = wp_get_theme();
        if ( $active_theme->get('Name') == 'Sweetdate' || $active_theme->get('Template') ==  'Sweetdate' || $active_theme->get('Template') == 'sweetdate' ) {
        	add_action( 'bp_init', array( $this, 'sweetdate_init' ), 20 );
        }

        //add_action('wp_ajax_list_update_order', array( $this, 'order_list' ) );
    }
    
    //not ready yet. just a test...
    /* function order_list(){
    	
    	die(json_encode($_POST));
    	global $wp_logo_slider_images;
    
    	$list 	   = $wp_logo_slider_images;
    	$new_order = $_POST['list_item'];
    	$new_list  = array();
    
    	foreach( $new_order as $v ){
    		if ( isset( $list[$v] ) ){
    			$new_list[$v] = $list[$v];
    		}
    	}
    		
    	die($new_list);
    	//update_option('wp_logo_slider_images',$new_list);
    } */

    /**
     * Include addon function.
     *
     * @access public
     * @return $addons
     */
    public function addons_init( $addons ) {

    	$addons['posts'] = array(
    			'name'    	=> 'posts',
    			'title'   	=> __( 'Post Types Locator', 'GMW' ),
    			'version' 	=> GMW_VERSION,
				'item'	  	=> 'Post Types Locator',
    			'file' 	  	=> GMW_PATH . '/plugins/posts/connect.php',
    			'folder'	=> 'posts',
    			'author'  	=> 'Eyal Fitoussi',
    			'desc'    	=> __( 'Add geo-location to Posts and pages. Create an advance proximity search forms to search for locations based on post types, categories, distance and more.', 'GMW' ),
    			'license' 	=> false,
    			'image'   	=> false,
    			'require' 	=> array(),
    	);

    	$addons['friends'] = array(
    			'name'    	=> 'friends',
    			'title'   	=> __( 'Members Locator', 'GMW' ),
    			'version' 	=> GMW_VERSION,
    			'item'	  	=> 'Members Locator',
    			'file' 	  	=> GMW_PATH . '/plugins/friends/includes/gmw-fl-component.php',
    			'folder'	=> 'friends',
    			'author'  	=> 'Eyal Fitoussi',
    			'desc'    	=> __( 'Let the BuddyPress members of your site to add location to thier profile. Create an advance proximity search forms to search for members based on location, Xprofile Fields and more.', 'GMW' ),
    			'image'   	=> false,
    			'license' 	=> false,
    			'require' 	=> array(
    					'Buddypress Plugin' => array( 'plugin_file' => 'buddypress/bp-loader.php', 'link' => 'http://buddypress.org' )
    			)
    	);

    	return $addons;
    }

    /**
     * GMW function
     * Check if addon is active
     * 
     * @param unknown_type $addon
     */
    public static function gmw_check_addon( $addon ) {

    	$addons = get_option( 'gmw_addons' );

    	if ( ( isset( $addons[$addon] ) && $addons[$addon] == 'active' ) && ( !isset( $_POST['gmw_premium_license'] ) ) ) {
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
		
    	$protocol  = is_ssl() ? 'https' : 'http';
    	$googleApi = ( isset( $this->settings['general_settings']['google_api'] ) )    ? '&key=' . $this->settings['general_settings']['google_api'] :        '';
        $region	   = ( isset( $this->settings['general_settings']['country_code'] ) )  ? '&region=' .$this->settings['general_settings']['country_code'] :    '';
        $language  = ( isset( $this->settings['general_settings']['language_code'] ) ) ? '&language=' .$this->settings['general_settings']['language_code'] : '';
    	
        //register google maps api
        if ( !wp_script_is( 'google-maps', 'registered' ) ) {
            wp_register_script( 'google-maps', $protocol.'://maps.googleapis.com/maps/api/js?libraries=places'.$googleApi.$region.$language.'&sensor=false', array( 'jquery' ), false );
    	}
    	
        //enqueue google maps api
        if ( !wp_script_is( 'google-maps', 'enqueued' ) ) {
        	wp_enqueue_script( 'google-maps' );
    	}
    	
    	wp_enqueue_style( 'dashicons' );   	
    	wp_register_style( 'gmw-style', GMW_URL.'/assets/css/style.css' );
    	wp_enqueue_style( 'gmw-style' );
    	 
        //enqueue gmw style and script
    	wp_register_script( 'gmw-js', GMW_URL.'/assets/js/gmw.min.js', array( 'jquery' ), GMW_VERSION, true );
        wp_enqueue_script( 'gmw-js' );      
        wp_localize_script( 'gmw-js', 'gmwSettings', $this->settings );
          
        wp_register_script( 'gmw-map', GMW_URL.'/assets/js/map.min.js', array( 'jquery' ), GMW_VERSION, true );
        wp_register_script( 'gmw-google-autocomplete', GMW_URL.'/assets/js/googleAddressAutocomplete.js', array( 'jquery' ), GMW_VERSION, true );
        
        //wp_register_script( 'chosen', GMW_URL . '/assets/js/chosen.jquery.min.js', array( 'jquery' ), GMW_VERSION, true );
        //wp_register_style( 'chosen',  GMW_URL . '/assets/css/chosen.min.css' );
        
        //only register some JavsScript libraries
        if ( !wp_script_is( 'gmw-infobox', 'registered' ) ) {
        	wp_register_script( 'gmw-infobox', GMW_URL . '/assets/js/infobox.min.js', array( 'jquery' ), GMW_VERSION, true );
        	
        	$infobox_close_btn = $protocol.'://www.google.com/intl/en_us/mapfiles/close.gif';
        	wp_localize_script( 'gmw-infobox', 'closeButton', $infobox_close_btn );
        }
		   
        if ( !wp_script_is( 'gmw-marker-clusterer', 'registered' ) ) {
        	wp_register_script( 'gmw-marker-clusterer', GMW_URL . '/assets/js/marker-clusterer.min.js', array( 'jquery' ), GMW_VERSION, true );
        }
        
        $cluster_image = $protocol.'://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/images/m';
        wp_localize_script( 'gmw-marker-clusterer', 'clusterImage', $cluster_image );
        
        if ( !wp_script_is( 'gmw-marker-spiderfier', 'registered' ) ) {
        	wp_register_script( 'gmw-marker-spiderfier', GMW_URL . '/assets/js/marker-spiderfier.min.js', array( 'jquery' ), GMW_VERSION, true );
        }      
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
    	       	
    	$_GET 	  = apply_filters( 'gmw_modify_get_args', $_GET );       	
    	$elements = array( 'search_form', 'map', 'search_results', 'form' );
    	
    	if ( empty( $params ) ) 
    		return;
    	
    	//get the element type
    	$element = key( $params );
    	
    	//make sure the element is lagit
    	if ( !in_array( $element, $elements ) || empty( $params[$element] ) )
    		return;

    	//get the form ID
    	$formId = $params[$element];
    	
    	if ( $formId != 'results' ) {

    		if ( !is_numeric( $formId ) || empty( $this->forms[$formId] ) )
    			return;

    		$this->form = $this->forms[$formId];
    		$this->form['element_triggered'] = $element;

    	} elseif ( $formId == 'results' && !empty( $_GET['action'] ) && $_GET['action'] == "gmw_post" ) {
    		 
    		$this->form = $this->forms[$_GET['gmw_form']];
    		$this->form['element_triggered'] = 'results_page';
    		 
    	} else{
    		return;
    	}
    	    
    	//if results page is set
    	if ( !empty( $this->form['search_results']['results_page'] ) ) {
    		$this->form['search_results']['results_page'] = get_permalink( $this->form['search_results']['results_page'] );
    	
    	//if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
    	} elseif ( isset( $params['widget'] ) ) {
    		$this->form['search_results']['results_page'] = get_permalink( $this->settings['general_settings']['results_page'] );
    	} else {
    		$this->form['search_results']['results_page'] = false;
    	}
    	
        $this->form['params']			  		 = $params;
        $this->form['submitted']		  		 = ( !empty( $_GET['action'] ) && $_GET['action'] == "gmw_post" ) ? true : false;
        $this->form['page_load_results_trigger'] = ( !$this->form['submitted'] && !empty( $this->form['page_load_results']['all_locations'] ) ) ? true : false;
        $this->form['auto_results_trigger'] 	 = ( !$this->form['submitted'] && ( !empty( $this->form['search_results']['auto_search']['on'] ) || !empty( $this->form['search_results']['auto_all_results'] ) ) ) ? true : false;      
        $this->form['in_widget'] 		  		 = ( !empty( $params['widget'] ) ) ? true : false;
        $this->form['ul_address']		  		 = ( !empty( $_COOKIE['gmw_address'] ) ) ? urldecode( $_COOKIE['gmw_address'] ) : false;
        $this->form['ul_lat'] 			  		 = ( !empty( $_COOKIE['gmw_lat'] ) ) 	 ? urldecode( $_COOKIE['gmw_lat'] ) 	: false;
        $this->form['ul_lng'] 			  		 = ( !empty( $_COOKIE['gmw_lng'] ) ) 	 ? urldecode( $_COOKIE['gmw_lng'] ) 	: false;
        $this->form['ul_icon'] 			  		 = ( !empty( $this->form['results_map']['your_location_icon'] ) ) ? $this->form['results_map']['your_location_icon'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
        $this->form['region'] 					 = ( !empty( $this->settings['general_settings']['country_code'] ) )  ? $this->settings['general_settings']['country_code']  : 'US';
        $this->form['language']					 = ( !empty( $this->settings['general_settings']['language_code'] ) ) ? $this->settings['general_settings']['language_code'] : 'EN';
        $this->form['get_per_page'] 			 = false;
        $this->form['units_array'] 		 		 = false;
        $this->form['your_lat']     			 = false;
        $this->form['your_lng']     			 = false;
        $this->form['radius']					 = false;
        $this->form['org_address']				 = false;
        $this->form['paged']		  			 = 0;
        $this->form['per_page']		  			 = -1;
        $this->form['is_mobile']				 = ( wp_is_mobile() ) ? true : false;
        $this->form['gif_loader'] 				 = GMW_URL.'/assets/images/gmw-loader.gif';
        $this->form['map_loader'] 				 = GMW_URL.'/assets/images/map-loader.gif';
        $this->form['results'] 					 = array();
        
        //Get current page number
        $paged 				  = ( is_front_page() ) ? 'page' : 'paged';     
        $this->form['paged']  = ( get_query_var( $paged ) ) ? get_query_var( $paged ) : 1;
        $this->form['labels'] = gmw_form_set_labels( $this->form );
        
        //modify form values
        $this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_default_form_values' , $this->form );
        
        ob_start();
        
        do_action( 'gmw_'.$this->form['prefix'].'_shortcode_start', $this->form, $this->settings );
        
        //display search form anywere on the page using the search_form shortcode
        if ( !apply_filters( 'gmw_'.$this->form['ID'].'_search_form_disabled', false ) && ( $this->form['element_triggered'] == 'search_form' || $this->form['element_triggered'] == 'form' ) && !empty( $this->form['search_form']['form_template'] ) && $this->form['search_form']['form_template'] != 'no_form' ) {
               
        	do_action( 'gmw_' . $this->form['prefix'] . '_before_search_form', $this->form );
        
        	//display search form
        	gmw_search_form( $this->form );
        
        	do_action( 'gmw_' . $this->form['prefix'] . '_after_search_form', $this->form ); 
        } 
        
        //display map using shortcode
        if ( $this->form['element_triggered'] == 'map' ) {
        	        	  	          
            if ( $this->form['submitted'] && $this->form['search_results']['display_map'] != "shortcode" ) 
            	return;
            
            if ( $this->form['page_load_results_trigger'] && $this->form['page_load_results']['display_map'] != "shortcode" )
            	return;
                   
            if ( $this->form['auto_results_trigger'] && $this->form['search_results']['display_map'] != "shortcode" )
            	return;
            
            do_action( 'gmw_' . $this->form['prefix'] . '_before_map', $this->form );

            //display map
            echo gmw_get_results_map( $this->form );
            
            do_action( 'gmw_' . $this->form['prefix'] . '_after_map', $this->form );
        }
        
        //display results anywere on the page using the map shortcode
        if ( !$this->form['in_widget'] && in_array( $this->form['element_triggered'], array( 'form', 'search_results', 'results_page' ) ) ) {
        	    
        	do_action( 'gmw_'.$this->form['prefix'].'_results_shortcode', $this->form );
        	do_action( 'gmw_results_shortcode', 						  $this->form );	
        }

        do_action( 'gmw_'.$this->form['prefix'].'_shortcode_end', $this->form );
        
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
        if ( bp_current_component() == 'members' ) {
            include_once GMW_PATH . '/third-party/sweetdate/geo-my-wp-sd-class.php';
        }
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
    
    				foreach ($address_componenets as $ac) {
    
	    				if ($ac->types[0] == 'street_number') {
	    					$street_number = esc_attr($ac->long_name);
	    				}
	    
	    				if ($ac->types[0] == 'route') {
	    					$street_f = esc_attr( $ac->long_name );
	    
	    					if ( isset( $street_number ) && !empty( $street_number ) ) {
	    						$location['street'] = $street_number . ' ' . $street_f;
	    					} else {
	    						$location['street'] = $street_f;
	    					}
	    				}
    
	    				if ( $ac->types[0] == 'subpremise' ) {
	    					$location['apt'] = esc_attr($ac->long_name);
	    				}
	    				
	    				if ( $ac->types[0] == 'locality' ) {
	    					$location['city'] = esc_attr( $ac->long_name );
	    				}
    				
	    				if ($ac->types[0] == 'administrative_area_level_1') {
	    					$location['state_short'] = esc_attr($ac->short_name);
	    					$location['state_long']  = esc_attr($ac->long_name); 
	    				}
    
	    				if ($ac->types[0] == 'postal_code') {
	    					$location['zipcode'] = esc_attr($ac->long_name);
	    				}
    					
	    				if ($ac->types[0] == 'country') {
	    					$location['country_short'] = esc_attr($ac->short_name);
	    					$location['country_long']  = esc_attr($ac->long_name);
	    				}
    
    				}
    				do_action( 'gmw_geocoded_location', $location );
    
    				// cache coordinates for 3 months
    				set_transient( 'gmw_geocoded_'.$address_hash, $location, 3600*24*30*3 );

    			} elseif ( $data->status === 'ZERO_RESULTS' ) {
    				return array( 'error' => __( 'The address entered could not be geocoded.', 'GMW' ) );
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
    
    public function maps_options( $id=false) {
    
    	//initiate map styles object
    	echo '<script>gmwMapOptions = {}; gmwMapObjects = {};</script>';
    	 
    	do_action( 'gmw_map_options' );
    }
    
    /**
     * Gmw Google Places Address Autocomplete
     *
     * Will trigger Google Address autocomplete on input field
     * use the filter to add the field ID of the field where you'd like to have autocomplete
     *
     * @since 2.5
     * @author Eyal Fitoussi
     *
     */
    public static function google_places_address_autocomplete( $ac_fields=false ) {
      	
    	if ( !$ac_fields ) {

    		//add field ID here
    		$ac_fields = apply_filters( 'gmw_google_places_address_autocomplete_fields', array() );
    	
    		if ( empty( $ac_fields ) )
    			return;
    	
    		wp_localize_script( 'gmw-google-autocomplete', 'gacFields', $ac_fields );  	
    	}
    	
    	if ( !wp_script_is( 'gmw-google-autocomplete', 'enqueued') ) {
    		wp_enqueue_script( 'gmw-google-autocomplete' );
    	}  	
    	?>
    	<script>
		jQuery(document).ready(function($) {
			gmwGoogleAddressAutocomplete( JSON.parse('<?php echo json_encode( $ac_fields ); ?>') );
		});
    	</script>
    	<?php
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