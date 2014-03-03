<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * GMW Location function - register scripts and styles
 */
function gmw_location_register_scripts(){
	//include main map
	function gmw_fl_register_main_map($main_map) {
		wp_register_script( 'gmw-fl-map', GMW_FL_URL . '/includes/js/main-map.js',array(),false,true );
	}
	add_action( 'wp_enqueue_scripts', 'gmw_fl_register_main_map', 10 );
	
	if( bp_current_action() == 'location' ){
		wp_enqueue_style( 'gmw-fl-style', GMW_FL_URL . 'includes/css/style.css', array(),false,false);
	}
}
add_action('bp_actions', 'gmw_location_register_scripts', 10);

/**
 * Gmw Location function - add location menu to activity order by
 */
function gmw_fl_location_add_filter_options(){
	?>
	<option value="gmw_location"><?php _e( 'Location', 'gmw-location' ); ?></option>
	<?php
}

add_action('bp_activity_filter_options', 'gmw_fl_location_add_filter_options', 15);
add_action('bp_member_activity_filter_options', 'gmw_fl_location_add_filter_options', 15 );