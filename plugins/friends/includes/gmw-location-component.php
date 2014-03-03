<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class GMW_Location_Component extends BP_Component {

	/**
	 * Constructor method
	 *
	 * @package GMW 
	 */
	function __construct() {
		global $bp, $blog_id;
			
		parent::start(
			GMW_LOCATION_SLUG,
			__( 'Location', 'GMW' ),
			GMW_LOCATION_PLUGIN_DIR
		);

	 	$this->includes();
		
		$bp->active_components[$this->id] = '1';
		
		add_action( 'init', array( &$this, 'register_post_types' ) );

	}

	/**
	 * GMW location files
	 *
	 * @package GMW Location
	 * @since 1.0
	 */
	function includes() {

		// Files to include
		$includes = array(
			'includes/gmw-location-functions.php',
			'includes/gmw-location-actions.php',
			'includes/gmw-location-activity.php',
			'includes/gmw-location-screens.php',
			'includes/gmw-location-ajax.php',
			'includes/gmw-location-widgets.php',
			'includes/gmw-location-filters.php'
		);
		
		parent::includes( $includes );

	}

	/**
	 * GMW Location globals
	 * @package GMW Location
	 */
	function setup_globals() {
		global $bp;

		// Defining the slug in this way makes it possible for site admins to override it
		if ( !defined( 'GMW_LOCATION_SLUG' ) )
			define( 'GMW_LOCATION_SLUG', $this->id );

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'                  => GMW_LOCATION_SLUG,
			'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : GMW_LOCATION_SLUG,
			'has_directory'         => true, // Set to false if not required
			'notification_callback' => false,
			'search_string'         => __( 'Search Location...', 'GMW' )
		);

		// Let BP_Component::setup_globals() do its work.
		parent::setup_globals( $globals );

		// we'll use this to avoid conflict with activity's $bp->ajax_querystring
		//$bp->{$this->id}->ajax_query = '';
		
	}

	/**
	 * GMW Location menu tab
	 */
	function setup_nav() {
		global $bp;
		
		// Add 'location' to the main navigation
		$main_nav = array(
			'name' 		      	  => __( 'Location', 'GMW' ),
			'slug' 		       	  => GMW_LOCATION_SLUG,
			'position' 	      	  => 60,
			'screen_function'     => 'gmw_location_screen_function_page',
			'default_subnav_slug' => GMW_LOCATION_SLUG
		);
		
		$user_domain = ( !empty( $bp->displayed_user->id ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

		$gmw_location_link = trailingslashit( $user_domain . GMW_LOCATION_SLUG );

		parent::setup_nav( $main_nav );
	}
	
	/**
	 * GMW location admin bar
	 * @see BP_Component::setup_admin_bar()
	 */
	function setup_admin_bar() {
		global $bp;

		// Prevent debug notices
		$wp_admin_nav = array();

		// Menus for logged in user
		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$gmw_location_link = trailingslashit( bp_loggedin_user_domain() . GMW_LOCATION_SLUG );

			// Add location tab
			$wp_admin_nav[] = array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-' . GMW_LOCATION_SLUG,
				'title'  => __( 'Location', 'GMW' ),
				'href'   => trailingslashit( $gmw_location_link )
			);
			
		}

		parent::setup_admin_bar( $wp_admin_nav );
		
	}
	
	/**
	 * GMW Location register post types
	 * @see BP_Component::register_post_types()
	 */
	function register_post_types() {
		// Set up some labels for the post type
		$labels = array(
				'name'	   => __( 'Members Location', 'GMW' ),
				'singular' => __( "Member's Location", 'GMW' )
		);
	
		// Set up the argument array for register_post_type()
		$args = array(
				'label'	   => __( 'Members Location', 'GMW' ),
				'labels'   => $labels,
				'public'   => false,
				'show_ui'  => true,
				'supports' => array( 'title' )
		);
	
		// Register the post type.
		// Here we are using $this->id ('example') as the name of the post type. You may
		// choose to use a different name for the post type; if you register more than one,
		// you will have to declare more names.
		//register_post_type( $this->id, $args );
	
		//parent::register_post_types();
		
	}
	
	function register_taxonomies() {
	
	}

}

/**
 * Finally Loads the component into the $bp global
 *
 */
function gmw_location_load_core_component() {
	global $bp;

	$bp->gmw_location = new GMW_Location_Component;
	
}
add_action( 'bp_loaded', 'gmw_location_load_core_component' );
?>