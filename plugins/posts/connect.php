<?php

define('GMW_PT_DB_VERSION', '1.1');
define('GMW_PT_URL', GMW_URL . '/plugins/posts/');
define('GMW_PT_PATH', GMW_PATH . '/plugins/posts/');

if ( is_admin() ) include_once GMW_PT_PATH . 'admin/admin-addons.php';

if (!isset($wppl_on['post_types']) ) return;

if ( is_admin() ) {
	global $wpdb;
	
	// Create or update database table
	if ( get_option( "gmw_pt_db_version" ) == '' || get_option( "gmw_pt_db_version" ) != GMW_PT_DB_VERSION ) {
		include_once GMW_PT_PATH . 'admin/db.php';
		$ptTable = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A);
		if ( count($ptTable) == 0 ) {
			gmw_pt_db_installation();
			update_option( "gmw_pt_db_version", GMW_PT_DB_VERSION );
		} elseif ( count($ptTable) == 1 ) {
			gmw_pt_db_update();
		}
	}
	
	include_once GMW_PT_PATH . 'admin/admin-settings.php';
	function gmw_register_pt_taxonomies_script() {
		wp_register_script( 'gmw-pt-shortcodes-categories', GMW_PT_URL . 'admin/js/admin-settings.js', array(),false,true );
	}
	add_action( 'admin_enqueue_scripts', 'gmw_register_pt_taxonomies_script' );
	
	//Include shortcode page
	function gmw_pt_shortcode_page($shortcode_page, $wppl_on, $options_r, $wppl_options, $posts, $pages_s) {
		if ( isset($_GET['form_type']) && $_GET['form_type'] == 'posts' ) :
			$shortcode_page = include_once GMW_PT_PATH . 'admin/edit-shortcode.php';
			return $shortcode_page;
		endif;
	}
	add_filter('gmw_edit_shortcode_page', 'gmw_pt_shortcode_page', 10, 6 );
	
	//Include javascripts, jquery and styles only in choosen post types admin area 
	function gmw_register_admin_location_scripts() {
		global $post_type;
		$gmw_options = get_option('wppl_fields');		
		
		if( isset( $gmw_options['address_fields'] ) && !empty( $gmw_options['address_fields'] ) && ( in_array( $post_type, $gmw_options['address_fields'] ) ) ) {
			wp_register_style( 'admin-style', GMW_PT_URL . 'admin/css/style-admin.css', array(),false,false);
			wp_enqueue_style('admin-style');
			wp_register_script( 'google-api-key', 'http://maps.googleapis.com/maps/api/js?key='.$gmw_options['google_api'].'&sensor=false&region='.$gmw_options['country_code'],array(),false); 
			wp_enqueue_script('google-api-key');
			wp_enqueue_script( 'jquery-ui-autocomplete');
			wp_register_script( 'wppl-address-picker', GMW_PT_URL .'admin/js/jquery.ui.addresspicker.js',array(),false,true);
			wp_enqueue_script( 'wppl-address-picker');
		}
	}
	add_action( 'admin_print_scripts-post-new.php', 'gmw_register_admin_location_scripts', 11 );
	add_action( 'admin_print_scripts-post.php', 'gmw_register_admin_location_scripts', 11 );
	
	// UPLOAD METABOXES ONLY ON NECESSARY PAGES 
	if (in_array( basename($_SERVER['PHP_SELF']), array( 'post-new.php', 'post.php','page.php','page-new' ) ) ) {
		include_once GMW_PT_PATH . 'admin/metaboxes.php'; 
	}
} else {	 
		
	function gmw_pt_results_shortcode_start($gmw, $gmw_options) {
		include_once GMW_PT_PATH. 'includes/search-functions.php';
	}
	add_action('gmw_posts_main_shortcode_start', 'gmw_pt_results_shortcode_start', 10 , 2);
	add_action('gmw_posts_results_shortcode_start', 'gmw_pt_results_shortcode_start', 10 , 2);
		
	include_once GMW_PT_PATH . 'includes/functions.php';
	
	function wppl_register_pt_scripts() {
		wp_register_script( 'gmw-pt-map', GMW_PT_URL . 'js/map.js', array(),false,true );
		wp_register_script( 'wppl-sl-map', GMW_PT_URL . 'js/single-location-map.js' ,array(),false,true );
	}
	add_action( 'gmw_register_cssjs_front_end', 'wppl_register_pt_scripts', 10 );
	
}