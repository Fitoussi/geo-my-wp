<?php
/*
Plugin Name: GEO my WP
Plugin URI: http://www.geomywp.com 
Description: Add location to any post types, pages or members (using Buddypress) and create an advance proximity search forms.
Version: 2.4 beta
Author: Eyal Fitoussi
Author URI: http://www.geomywp.com
License: GPLv2
Text Domain: GMW
Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * GEO my WP class.
 */
class GEO_my_WP {
	
	/**
	 * Addons exist in database
	 * 
	 * @access private
	 */
	private $addons;
	
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
	 * __construct function.
	 */
	public function __construct() {
		
		$this->addons 	= get_option( 'gmw_addons' );
		$this->settings = get_option( 'gmw_options' );
		$this->forms 	= get_option( 'gmw_forms' );
		
		// Define constants
		if ( !defined( 'GMW_REMOTE_SITE_URL' ) ) define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' );
		
		define(	'GMW_VERSION', '2.3.0');
		define( 'GMW_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define(	'GMW_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define(	'GMW_IMAGES', GMW_URL .'/assets/images' );
		define(	'GMW_AJAX' , get_bloginfo('wpurl') .'/wp-admin/admin-ajax.php' );
				
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ) );
		add_filter(	'clean_url', array( $this , 'clean_google_url' ), 99, 3 );
		
		//main gmw shortcode
		add_shortcode( 'gmw', array( $this, 'gmw' ) );
		add_shortcode( 'gmw_results_map', array( $this, 'results_map' ) );
		
		//admin functions and files
		if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
			$this->admin_functions();
		}
		
		//frontend functions and files
		if ( !is_admin() ) {
			$this->frontend_functions();
		}
		
		//load posts locator add-on
		include_once GMW_PATH .'/plugins/posts/connect.php';
		
		//init widgets
		include_once GMW_PATH . '/includes/geo-my-wp-widgets.php';
		if ( isset( $this->settings['features']['current_location_shortcode'] ) ) add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget" );' ) ); 
		if ( isset( $this->settings['features']['search_form_widget'] ) ) add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Search_Form_Widget" );' ) ); 
		
		add_filter( 'gmw_admin_addons_page', array( $this , 'members_locator_addon_page' ) );
		
		//load friends locator add-on
		if ( GEO_my_WP::gmw_check_addon( 'friends' ) ) add_action( 'bp_loaded', array( $this, 'members_locator_addon_init'), 20 );
                	
	}
	
	/**
	 * Include addon function.
	 *
	 * @access public
	 * @return $addons
	 */
	public function members_locator_addon_page($addons) {
	
		$addons[2] = array(
				'name' 	  => 'friends',
				'title'   => __( 'Members Locator', 'GMW' ),
				'desc'    => __( 'Let your BuddyPress members add location to thier profile. Create an advance proximity search form to search for members based on location.', 'GMW'),
				'image'	  => false,
				'require' => array(
						'Buddypress Plugin' => array( 'plugin_file' => 'buddypress/bp-loader.php', 'link' => 'http://buddypress.org' )
				),
				'license' => false
		);
		return $addons;
	}
	
	/**
	 * Admin functions and files
	 */
	protected function admin_functions() {
	
		include( GMW_PATH . '/includes/admin/geo-my-wp-admin.php' );
		include_once( GMW_PATH . '/includes/admin/geo-my-wp-updater.php' );
	
	}
	
	public static function gmw_check_addon( $addon ) {
	
		$addons = get_option( 'gmw_addons' );
	
		if ( ( isset( $addons[$addon] ) && $addons[$addon] == 'active' ) && ( !isset( $_POST['gmw_premium_license'] ) ) )
			return true;
		else
			return false;
	}
	
	/**
	 * Localization
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'GMW', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_register_scripts() {
		
		$region = ( isset( $this->settings['general_settings']['country_code'] ) && !empty( $this->settings['general_settings']['country_code'] ) ) ? '&region='.$this->settings['general_settings']['country_code'] : '';
		
		if ( !wp_script_is( 'google-maps', 'enqueue' ) ) wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?key='.$this->settings['general_settings']['google_api'].''. $region.'&sensor=false', array(), false );
		wp_enqueue_script( 'gmw-js',  GMW_URL . '/assets/js/gmw.js', array('jquery'), GMW_VERSION, true );
		wp_enqueue_style( 'gmw-style', GMW_URL . '/assets/css/style.css' );
		
		if ( isset( $this->settings['general_settings']['auto_locate'] ) )
			wp_localize_script( 'gmw-js', 'autoLocate', $this->settings['general_settings']['auto_locate'] );
		else
			wp_localize_script( 'gmw-js', 'autoLocate', 'false');
		 
	}
		
	/**
	 * frontend functions and files
	 */
	protected function frontend_functions() {
		
		include( GMW_PATH . '/includes/geo-my-wp-functions.php' );
		include( GMW_PATH . '/includes/geo-my-wp-shortcodes.php' );
		if ( isset( $this->settings['features']['current_location_shortcode'] ) ) new GMW_Current_Location;
		
	}
	
	/**
	 * Load friends add-on component
	 *
	 */
	function members_locator_addon_init() {
		global $bp;
		
		include_once GMW_PATH .'/plugins/friends/includes/gmw-fl-component.php';
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
				
				do_action( 'gmw_'.$this->form['prefix'].'_before_map', $this->form );
				
				echo gmw_get_results_map( $this->form );
								
				do_action( 'gmw_'.$this->form['prefix'].'_after_map', $this->form );
			
		//display results when in results page				
		} elseif ( $params['form'] == 'results' ) {
			
			if ( isset( $_GET['action'] ) &&  $_GET['action'] == "gmw_post" ) {
			
				$this->form  = $this->forms[$_GET['gmw_form']];
				
				do_action( 'gmw_'.$this->form['form_type'].'_shortcode', $this->form, $results = ( $params['form'] == 'results' ) ? true : false );
			
			} 
			
		//display results when form submitted
		} else {
	
			$this->form = $this->forms[$params['form']];
			
			$this->form['search_results']['results_page'] = ( isset( $this->form['search_results']['results_page'] ) && !empty( $this->form['search_results']['results_page'] ) ) ? get_permalink( $this->form['search_results']['results_page'] ) : false;
			
			//if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
			if ( isset( $params['widget'] ) && ( !isset( $this->form['search_results']['results_page'] ) || empty( $this->form['search_results']['results_page'] ) ) ) $this->form['search_results']['results_page'] = get_permalink( $this->settings['general_settings']['results_page'] );
		
			do_action( 'gmw_'.$this->form['form_type'].'_shortcode', $this->form, $results = ( $params['form'] == 'results' ) ? true : false );
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
	public function clean_google_url($url, $original_url, $_context) {
		
		if ( strstr( $url, "googleapis.com" ) !== false ) {
			$url = str_replace( "&", "&", $url ); // or $url = $original_url
		}
		return $url;
	}
	
}
new GEO_my_WP(); 
?>