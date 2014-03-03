<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * GMW Post Types Addon class.
 */
class GEO_Post_Types_Addon {

	/**
	 * __construct function.
	 */
	public function __construct() {

		define( 'GMW_PT_DB_VERSION' , '1.1' );
		define( 'GMW_PT_PATH' , GMW_PATH . '/plugins/posts/' );
		define( 'GMW_PT_URL' , GMW_URL . '/plugins/posts/' );
		
		// init add-on
		add_filter( 'gmw_admin_addons_page', array( $this , 'addon_init' ) );
		
		$this->addons   = get_option( 'gmw_addons' );
		$this->settings = get_option( 'gmw_options' );
		
		// check if add-on is active
		if ( !isset( $this->addons['posts'] ) || $this->addons['posts'] != 'active' ) return;
			
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_register_scripts' ) );
		add_action( 'gmw_posts_shortcode', array( $this, 'search_functions' ), 10, 2 );
		
		/*
		 * include some admin functions/files
		 */
		if ( is_admin() && !defined('DOING_AJAX') ) {
			
			$this->admin_functions();
			
		}
		
		/*
		 * include some frontend functions/files
		*/
		if ( !is_admin() ) {
			
			$this->frontend_functions();
			
		}
	}
	
	/**
	 * Include addon function.
	 *
	 * @access public
	 * @return $addons
	 */
	public function addon_init( $addons ) {
	
		$addons[1] = array(
				'name' 	  => 'posts',
				'title'   => __( 'Post Types Locator', 'GMW' ),
				'desc'    => __( 'Add location to Posts and pages. Create an advance proximity search form to search for locations based on post types, categories, distance and more.', 'GMW'),
				'license' => false,
				'image'	  => false,
				'require' => array(),
		);
		return $addons;
	}
	
	/**
	 * Admin functions
	 */
	protected function admin_functions() {
	
		//create or update database
		if ( get_option( "gmw_pt_db_version" ) == '' || get_option( "gmw_pt_db_version" ) != GMW_PT_DB_VERSION ) {
			global $wpdb;
	
			$ptTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );
	
			if ( count($ptTable) == 0 ) {
				$this->create_db();
					
			} elseif ( count($ptTable) == 1 ) {
				$this->update_db();
			}
	
		}
			
		include( GMW_PT_PATH . 'includes/admin/gmw-pt-admin.php' );
	
	}
	
	/**
	 * frontend_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function frontend_register_scripts() {
	
		wp_register_script( 'gmw-pt-map', GMW_PT_URL . 'assets/js/map.js', array('jquery'), GMW_VERSION, true );
		wp_enqueue_style( 'gmw-style', GMW_URL . '/css/style.css' );
			
	}
	
	/**
	 *  Front end functions and files
	 */
	protected function frontend_functions() {
	
		include_once GMW_PT_PATH . 'includes/gmw-pt-functions.php';
		if ( isset( $this->settings['features']['single_location_shortcode'] ) ) include_once GMW_PT_PATH . 'includes/gmw-pt-shortcodes.php';
				
	}
	
	/**
	 * Create database
	 */
	protected function create_db() {
		
		include_once GMW_PT_PATH . 'includes/admin/gmw-pt-db.php';
		gmw_pt_db_installation();
		update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
		
	}
	
	/**
	 * update database
	 */
	protected function update_db() {
		
		include_once GMW_PT_PATH . 'includes/admin/gmw-pt-update-db.php';
		gmw_pt_db_update();
		
	}
		
	/**
	 * Search functions
	 * @param $form
	 * @param $results
	 */
	public function search_functions( $form, $results ) {
		
		include_once GMW_PT_PATH. 'includes/gmw-pt-search-functions.php';
		new GMW_PT_Search_Query( $form, $results );
		
	}
	
}
new GEO_Post_Types_Addon();
