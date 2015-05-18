<?php
/*
Plugin Name: GEO my WP
Plugin URI: http://www.geomywp.com
Description: Assign geolocation to post types and BuddyPress members. Create an advance proximity search forms to search for locations based on address, radius, units and more.
Version: 2.6.1-beta1
Author: Eyal Fitoussi
Author URI: http://www.geomywp.com
Requires at least: 4.0
Tested up to: 4.2.2
Text Domain: GMW
Domain Path: /languages/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
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
			self::$instance->core_addons();
		}
		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent GEO my WP from being loaded more than once.
	 *
	 * @since 2.4
	 */
	private function __construct() {}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 2.4
	 * @return void
	 */
	private function constants() {

		// Define constants
		if ( !defined( 'GMW_REMOTE_SITE_URL' ) ) {
			define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' );
		}
		
		define( 'GMW_VERSION', '2.6.1' );
		define( 'GMW_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GMW_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'GMW_IMAGES', GMW_URL . '/assets/images' );
		define( 'GMW_AJAX', admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ) );
		define( 'GMW_FILE', __FILE__ );
		define( 'GMW_BASENAME', plugin_basename( GMW_FILE ) );		
	}

	/**
	 * Include files
	 * 
	 * @since 2.4
	 * 
	 */
	public function includes() {
		global $gmw_options;

		$gmw_options = get_option( 'gmw_options' );

		//include deprecated functions. Should be removed in the future
		include( 'includes/geo-my-wp-functions.php' );
		include( 'includes/geo-my-wp-user-update-location.php' );

		//some default options if not exists
		$gmw_options['general_settings']['js_geocode']    	  = gmw_get_option( 'general_settings', 'js_geocode', false );
		$gmw_options['general_settings']['auto_locate']   	  = gmw_get_option( 'general_settings', 'auto_locate', false );
		$gmw_options['general_settings']['country_code']  	  = gmw_get_option( 'general_settings', 'country_code', 'US' );
		$gmw_options['general_settings']['language_code'] 	  = gmw_get_option( 'general_settings', 'language_code', 'EN' );

		include( 'includes/geo-my-wp-deprecated-functions.php' );

		//include admin files
		if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
			include( GMW_PATH . '/includes/admin/geo-my-wp-admin.php' );
			include( GMW_PATH . '/includes/admin/geo-my-wp-updater.php' );
			include( GMW_PATH . '/includes/admin/geo-my-wp-license-handler.php' );   	
		}

		//include files in front-end
		if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
			include( 'includes/geo-my-wp-form-init-class.php' );
			include( 'includes/geo-my-wp-gmw-class.php' );
			include( 'includes/geo-my-wp-template-functions.php' );
			include( 'includes/geo-my-wp-shortcodes.php' );
		}

		//include widgets
		include( 'includes/geo-my-wp-widgets.php' );
	}

	/**
	 * Include core add-ons if needed
	 */
	private function core_addons() {
		
		//load current_location add-on
		if ( GEO_my_WP::gmw_check_addon( 'current_location' ) ) {
			include( 'plugins/current-location/loader.php' );
		}
		
		//load single_location add-on
		if ( GEO_my_WP::gmw_check_addon( 'single_location' ) ) {
			include( 'plugins/single-location/loader.php' );
		}
		
		//load Posts Types locator add-on
		if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
			include( 'plugins/posts/loader.php' );
		}

		//load friends locator add-on
		if ( GEO_my_WP::gmw_check_addon( 'friends' ) ) {
			add_action( 'bp_loaded', array( $this, 'members_locator_addon_init' ), 20 );
		}

		//load Sweetdate Theme locator add-on
		if ( GEO_my_WP::gmw_check_addon( 'sweetdate_geolocation' ) ) {
			include( 'plugins/sweetdate-geolocation/loader.php' );
		}
	}

	/**
	 * Load members locator add-on component
	 */
	function members_locator_addon_init() {
		global $bp;

		include_once( 'plugins/friends/includes/gmw-fl-component.php' );
		$bp->gmw_location = new GMW_Members_Locator_Component;
	}
	
	/**
	 * add actions
	 * 
	 * @since 2.4
	 */
	public function actions() {

		//include scripts in the front end
		add_action( 'wp_enqueue_scripts', 	 array( $this, 'register_scripts' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 10 );
		add_filter( 'clean_url', 		  	 array( $this, 'clean_google_url' ), 99, 3 );

		//map options
		add_action( 'wp_footer', array( $this, 'google_api_features_init' ), 5 );
		
		//Load google places autocomplete in admin dashboard
		add_action( 'admin_footer', array( $this, 'google_places_address_autocomplete' ), 10 );

		//add_action('wp_ajax_list_update_order', array( $this, 'order_list' ) );
	}
	
	//not ready yet. just playing around with an idea...
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
	 * Verify if add-on is active
	 * @param  [array] $addon
	 * @return [boolean]      
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
	public function register_scripts() {
		
		global $gmw_options;

		//$settings  = get_option( 'gmw_options' );
		$protocol  = is_ssl() ? 'https' : 'http';
			
		//register google maps api
		if ( !wp_script_is( 'google-maps', 'registered' ) && apply_filters( 'gmw_google_maps_api', true ) ) {

			//Build Google API url. elements can be modified via filters
			$google_url = apply_filters( 'gmw_google_maps_api_url', array( 
				'protocol'	=> $protocol,
				'url_base'	=> '://maps.googleapis.com/maps/api/js?',
				'url_data'	=> http_build_query( apply_filters( 'gmw_google_maps_api_args', array(
						'libraries' => 'places',
		            	'key'		=> gmw_get_option( 'general_settings', 'google_api', '' ),
		         		'region'	=> $gmw_options['general_settings']['country_code'],
		              	'language'	=> $gmw_options['general_settings']['language_code'],
		              	'sansor'	=> 'false'
	        	) ), '', '&amp;'),
			), $gmw_options );

			wp_register_script( 'google-maps', implode( '', $google_url ) , array( 'jquery' ), GMW_VERSION, false );
		}

		//include font-awesome
		if ( !wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), GMW_VERSION );
		}

		if ( !is_admin() ) {

			//enqueue google maps api
			if ( !wp_script_is( 'google-maps', 'enqueued' ) ) {
				wp_enqueue_script( 'google-maps' );
			}
			
			//include dashicons. Will be removed in the future replaced by fontawsome
			wp_enqueue_style( 'dashicons' );   	
				
			//include GMW main stylesheet
			wp_register_style( 'gmw-style', GMW_URL.'/assets/css/style.css', array(), GMW_VERSION );
			wp_enqueue_style( 'gmw-style' );
			
			//temporary until this feature will be removed
			if ( !GEO_my_WP::gmw_check_addon( 'current_location' ) ) {
				wp_register_style( 'gmw-cl-style-dep', GMW_URL.'/assets/css/gmw-cl-style-dep.css', array(), GMW_VERSION );
				wp_enqueue_style( 'gmw-cl-style-dep' );
			}
			
			//register gmw script
			wp_register_script( 'gmw-js', GMW_URL.'/assets/js/gmw.min.js', array( 'jquery' ), GMW_VERSION, true );
			
			wp_localize_script( 'gmw-js', 'gmwSettings', $gmw_options );
			  
			//register gmw map script
			wp_register_script( 'gmw-map', GMW_URL.'/assets/js/map.min.js', array( 'jquery' ), GMW_VERSION, true );
			
			//register gmw autocomplete script
			wp_register_script( 'gmw-google-autocomplete', GMW_URL.'/assets/js/googleAddressAutocomplete.js', array( 'jquery' ), GMW_VERSION, true );
			
			//will be removed in the future replaced by the new current location core add-on
			if ( !class_exists( 'GMW_Current_Location' ) ) {
				wp_register_script( 'gmw-cl-map', GMW_URL . '/assets/js/gmw-cl.min.js', array('jquery', 'google-maps' ), GMW_VERSION, true );
			}
			
			if ( !wp_script_is( 'chosen', 'registered' ) ) {
				wp_register_script( 'chosen', GMW_URL . '/assets/js/chosen.jquery.min.js', array( 'jquery' ), GMW_VERSION, true );
			}

			if ( !wp_style_is( 'chosen', 'registered' ) ) {
				wp_register_style( 'chosen',  GMW_URL . '/assets/css/chosen.min.css', array(), GMW_VERSION );
			}
			
			//Google Maps Infobox - only register, to be used with premium features
			if ( !wp_script_is( 'gmw-infobox', 'registered' ) ) {
				
				//infobox library
				wp_register_script( 'gmw-infobox', GMW_URL . '/assets/js/infobox.min.js', array( 'jquery' ), GMW_VERSION, true );
				
				$infobox_close_btn = $protocol.'://www.google.com/intl/en_us/mapfiles/close.gif';
				wp_localize_script( 'gmw-infobox', 'closeButton', $infobox_close_btn );
			}
			   
			//Marker clusterer library - only register, to be used with premium features
			if ( !wp_script_is( 'gmw-marker-clusterer', 'registered' ) ) {
				wp_register_script( 'gmw-marker-clusterer', GMW_URL . '/assets/js/marker-clusterer.min.js', array( 'jquery' ), GMW_VERSION, true );
			}
			
			$cluster_image = apply_filters( 'gmw_clusters_folder' , $protocol.'://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclustererplus/images/m' );
			wp_localize_script( 'gmw-marker-clusterer', 'clusterImage', $cluster_image );
			
			//Marker spiderfire library - only register, to be used with premium features
			if ( !wp_script_is( 'gmw-marker-spiderfier', 'registered' ) ) {
				wp_register_script( 'gmw-marker-spiderfier', GMW_URL . '/assets/js/marker-spiderfier.min.js', array( 'jquery' ), GMW_VERSION, true );
			}
		}  
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
	 * GMW function - Geocode address
	 * @since 1.0
	 * @author Eyal Fitoussi
	 * @author This function inspired by a script written by Pippin Williamson - Thank you
	 */
	public static function geocoder( $address, $force_refresh = false ) {
	
		include_once( 'includes/geo-my-wp-geocoder.php' );

		return gmw_geocoder( $address, $force_refresh );
	}
	
	/**
	 * Display all the maps found in the global $gmwMapElements
	 * Trigger all autocomplete found in the global $gmwAutocompleteElements
	 */
	public function google_api_features_init() {
			 
		do_action( 'gmw_map_options' );
		
		//enqueue gmw scripts
		wp_enqueue_script( 'gmw-js' );

		//register blank map options holder. 
		//Can be used to modify the map options using custom functions 
		wp_localize_script( 'gmw-js', 'gmwMapOptions', array() );
		
		//include gmw map script and pass gloabl map elements to it using localize
		global $gmwMapElements, $gmwAutocompleteElements;
		
		if ( !empty( $gmwMapElements ) ) {
		
			//modify the maps global before dipsplaying
			$gmwMapElements = apply_filters( 'gmw_map_elements', $gmwMapElements );
			
			//pass the mapObjects to JS
			wp_localize_script( 'gmw-map', 'gmwMapObjects', $gmwMapElements );
			
			//enqueue the map script
			wp_enqueue_script( 'gmw-map' );
		}

		//verify the autocomplete global
		if ( !empty( $gmwAutocompleteElements ) ) {
		
			//modify the autocomplete global
			$gmwAutocompleteElements = apply_filters( 'gmw_google_places_address_autocomplete_fields', $gmwAutocompleteElements );

			//trigger autocomplete
			wp_localize_script( 'gmw-google-autocomplete', 'gacFields', $gmwAutocompleteElements );
			wp_enqueue_script( 'gmw-google-autocomplete' );	
		}
	}
	
	/**
	 * Gmw Google Places Address Autocomplete
	 *
	 * Will trigger Google Address autocomplete on input field
	 * use the filter to add the field ID of the field where you'd like to have autocomplete
	 *
	 * @since 2.5
	 * @author Eyal Fitoussi
	 */
	public static function google_places_address_autocomplete( $ac_fields=array() ) {

		global $gmwAutocompleteElements;

		if ( empty( $gmwAutocompleteElements ) ) {
			$gmwAutocompleteElements = array();
		} 

		if ( !empty( $ac_fields ) ) {
			$gmwAutocompleteElements = array_merge( $gmwAutocompleteElements, $ac_fields );
		}
		return;
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