<?php
/*
Plugin Name: GEO my WP
Plugin URI: http://www.geomywp.com 
Description: Add location to any post types, pages or members (using Buddypress) and create an advance proximity search.
Version: 2.3
Author: Eyal Fitoussi
Author URI: http://www.geomywp.com
License: GPLv2
Text Domain: GMW
Domain Path: /languages/
*/

$wppl_on = get_option('wppl_plugins');
$wppl_options = get_option('wppl_fields');

define(	'GMW_VERSION', '2.3');
define(	'GMW_URL', plugins_url() . '/geo-my-wp');
define(	'GMW_PATH', plugin_dir_path(dirname(__FILE__)) . 'geo-my-wp');
define(	'GMW_AJAX' ,get_bloginfo('wpurl') .'/wp-admin/admin-ajax.php');
define( 'GMW_REMOTE_SITE_URL', 'https://geomywp.com' ); 
define( 'GMW_PREMIUM_PLUGIN_NAME', 'GEO my WP Premium' ); 

do_action('gmw_define_name_constant');

function gmw_loaded() {};

//Load language
function gmw_load_text_domain() {
	load_plugin_textdomain('GMW', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}
add_action('init', 'gmw_load_text_domain');

/**
 * make sure google maps API loads properly
 * fix provided by user dfa327 http://wordpress.org/support/topic/google-maps-server-rejected-your-request-proposed-fix
 * Thank you
 */
function gmw_clean_google_url($url, $original_url, $_context) {
	if (strstr($url, "googleapis.com") !== false) {
		$url = str_replace("&", "&", $url); // or $url = $original_url
	}
	return $url;
}
add_filter('clean_url', 'gmw_clean_google_url', 99, 3);

//REGISTER STYLESHEET AND JAVASCRIPTS IN THE FRONT END
function gmw_register_cssjs_front_end() {
	$gmw_options = get_option('wppl_fields');
	$gmw_on = get_option('wppl_plugins');
	
	wp_register_style( 'wppl-style', plugins_url('css/style.css', __FILE__) );
	wp_enqueue_style( 'wppl-style' );
	
	wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css');
	
	wp_register_script( 'google-maps', '//maps.googleapis.com/maps/api/js?key='.$gmw_options['google_api'].'&sensor=false&libraries=places&region='.$gmw_options['country_code'],array(),false);
	wp_enqueue_script( 'google-maps');
    wp_register_script( 'gmw-js',  plugins_url('js/gmw.js', __FILE__),array(),false,true );
    wp_enqueue_script( 'gmw-js' );
    
    if ( isset($wppl_options['auto_locate']) ) 
    	wp_localize_script( 'gmw-js', 'autoLocate', $gmw_options['auto_locate']);
	else 
    	wp_localize_script( 'gmw-js', 'autoLocate', 'false');
   
    wp_enqueue_script('thickbox', null,  array('jquery')); 
    wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    wp_enqueue_script('jquery-ui-datepicker');
    
    do_action('gmw_register_cssjs_front_end', $gmw_options, $gmw_on);
}
add_action( 'wp_enqueue_scripts', 'gmw_register_cssjs_front_end' );

include_once GMW_PATH . '/includes/functions.php';
include_once GMW_PATH . '/includes/widgets.php';

if (is_admin() ) {
	include_once GMW_PATH . '/admin/admin-settings.php';
	include_once GMW_PATH  . '/admin/gmw-premium-updater.php';
}
// include add-ons
include_once GMW_PATH .'/plugins/posts/connect.php';
include_once GMW_PATH .'/plugins/friends/connect.php';
 
?>