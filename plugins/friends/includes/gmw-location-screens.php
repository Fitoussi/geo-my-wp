<?php
function gmw_location_is_bp_default() {
	// if active theme is BP Default or a child theme, then we return true
	// If the Buddypress version  is < 1.7, then return true too
	if(current_theme_supports('buddypress') || in_array( 'bp-default', array( get_stylesheet(), get_template() ) )  || ( defined( 'BP_VERSION' ) && version_compare( BP_VERSION, '1.7', '<' ) ))
		return true;
	else
		return false;
}

function gmw_location_add_template_stack( $templates ) {
	// if we're on a page of our plugin and the theme is not BP Default, then we
	// add our path to the template path array
	if ( bp_is_current_component( 'gmw_location' ) && !gmw_location_is_bp_default() ) {
		$templates[] = GMW_LOCATION_PLUGIN_DIR . '/templates/';
	}
	return $templates;
}
add_filter( 'bp_get_template_stack', 'gmw_location_add_template_stack', 10, 1 );

function gmw_location_load_template_filter( $found_template, $templates ) {
	// if theme is not BP Default or a child, we interrupt the process
	// BuddyPress will not find the path to our template and will
	// finally fire the do_action( 'bp_setup_theme_compat' ) hook
	if( !gmw_location_is_bp_default() )
		return $found_template;
	
	//Only filter the template location when we're on the bp-plugin component pages.
	if ( !bp_is_current_component( 'gmw_location' ) )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		elseif( file_exists( TEMPLATEPATH . '/' . $template ) )
		$filtered_templates[] = TEMPLATEPATH . '/' . $template;
		else
			$filtered_templates[] = GMW_LOCATION_PLUGIN_DIR . $template;
	}

	$found_template = $filtered_templates[0];

	return apply_filters( 'gmw_location_load_template_filter', $found_template );
}

add_filter( 'bp_located_template', 'gmw_location_load_template_filter', 10, 2 );

function gmw_location_screen_index() {

	// i first check i'm on my plugin directory area...
	if (  bp_is_current_component( 'gmw_location' ) && !bp_current_action() ) {
		bp_update_is_directory( true, 'location' );

		//... before using bp_core_load_template to ask BuddyPress
		// to load the template bp-plugin (which is located in
		// BP_PLUGIN_DIR . '/templates/bp-plugin.php)
		bp_core_load_template( apply_filters( 'gmw_location_screen_index', 'location' ) );
	}
}
add_action( 'bp_screens', 'gmw_location_screen_index' );

class GMW_Location_Theme_Compat {

	/**
	 * Setup the bp plugin component theme compatibility
	 */
	public function __construct() {
		/* this is where we hook bp_setup_theme_compat !! */
		add_action( 'bp_setup_theme_compat', array( $this, 'is_location' ) );
	}

	/**
	 * Are we looking at something that needs theme compatability?
	 */
	public function is_location() {

		if ( ! bp_current_action() && !bp_displayed_user_id() && bp_is_current_component( 'gmw_location' ) ) {
			// first we reset the post
			add_action( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			// then we filter 'the_content' thanks to bp_replace_the_content
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content'    ) );
		}
	}

	/**
	 * Update the global $post with directory data
	 */
	public function directory_dummy_post() {

		bp_theme_compat_reset_post( array(
				'ID'             => 0,
				'post_title'     => 'BP Plugin Directory',
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => '',
				'post_type'      => 'location',
				'post_status'    => 'publish',
				'is_archive'     => true,
				'comment_status' => 'closed'
		) );
	}
	/**
	 * Filter the_content with bp-plugin index template part
	 */
	public function directory_content() {
		bp_buffer_template_part( 'location' );
	}
}
new GMW_Location_Theme_Compat ();

function gmw_location_locate_template( $template = false ) {
	if( empty( $template ) )
		return false;

	if( gmw_location_is_bp_default() ) {
		locate_template( array(  $template . '.php' ), true );
	} else {
		bp_get_template_part( $template );
	}
}

function gmw_location_screen_function_page() {
	global $bp;
	//if( is_multisite() ) $wppl_options = get_site_option('wppl_site_options'); else $wppl_options = get_option('wppl_fields');
	$wppl_options = get_option('wppl_fields');
	
	$who = ( bp_is_my_profile() && !isset( $wppl_options['friends']['my_location_tab'] ) ) ? 'my' : 'user';
	add_action( 'bp_template_title', 'gmw_location_title' );
	add_action( 'bp_template_content', 'gmw_location_'.$who.'_content' );
	
	bp_core_load_template( apply_filters( 'gmw_location_my_screen', 'members/single/plugins' ) );
}

/*
function gmw_location_my_location_template_part( $templates, $slug, $name ) {
	
	if( $slug != 'members/single/plugins' )
		return $templates;

	return array( 'my-location-tab.php' );
}
*/
/**
 * GMW FL function - title for the "location" tab
 */
function gmw_location_title() {
	_e( 'Location', 'GMW' );
}

/**
 * GMW FL function - Content of the  "location" tab for logged in user
 */
function gmw_location_my_content() {
	
	include_once GMW_LOCATION_PLUGIN_DIR . '/templates/my-location-tab.php';

}

/**
 * GMW FL function - content for the user's "location" tab
 */
function gmw_location_user_content() {
	
	$content = '[gmw_member_location map_height="400px" map_width="400px" no_location="1"]';
	echo do_shortcode(apply_filters('gmw_fl_user_location_tab_content', $content ));
}
?>
