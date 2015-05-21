<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

class GMW_Members_Locator_Component extends BP_Component {

    /**
     * Constructor method
     *
     * @package GMW 
     */
    function __construct() {
        global $bp, $blog_id;

        /*
         * Define Globals
         */
        define('GMW_FL_DB_VERSION', '1.2.1'						 );
        define('GMW_FL_URL',  		GMW_URL.'/plugins/friends/'  );
        define('GMW_FL_PATH', 	 	GMW_PATH.'/plugins/friends/' );
        define('GMW_FL_SLUG', 		'location'					 );

        add_action( 'wp_enqueue_scripts', 				 array($this, 'frontend_register_scripts'     ) );
        add_action( 'bp_activity_filter_options', 		 array($this, 'location_filter_options'   ), 10 );
        add_action( 'bp_member_activity_filter_options', array($this, 'location_filter_options'   ), 10 );

        parent::start( GMW_FL_SLUG, __('Location', 'GMW'), GMW_FL_PATH );

        $this->includes();

        $bp->active_components[$this->id] = '1';
    }

    /**
     * GMW location files
     *
     * @package GMW Location
     * @since 1.0
     */
    function includes( $includes = array() ) {

    	// Files to include
    	$includes = array(   			
    			'includes/gmw-fl-shortcodes.php',
    			'includes/gmw-fl-actions.php',
    			'includes/gmw-fl-functions.php',
    			'includes/gmw-fl-activity.php',
    			'includes/gmw-fl-update-location.php',
    			'includes/gmw-fl-template-functions.php',
    			'includes/gmw-fl-search-query-class.php'
    	);
    	
    	if ( class_exists( 'GMW_Single_Location' ) ) {
    		$includes[] = 'includes/gmw-fl-single-member-location-class.php';
    	} else {
    		$includes[] = 'includes/gmw-fl-member-location-shortcode.php';
    	}
    	    	
    	if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
    		$includes[] = 'includes/admin/gmw-fl-admin.php';
    		$includes[] = 'includes/admin/gmw-fl-db.php';
    	}

    	parent::includes( $includes );

    }

    /**
     * GMW Location globals
     */
    function setup_globals( $args = array() ) {
        global $bp;

        // Defining the slug in this way makes it possible for site admins to override it
        if ( !defined( 'GMW_FL_SLUG' ) )
            define( 'GMW_FL_SLUG', $this->id );

        // Set up the $globals array to be passed along to parent::setup_globals()
        $globals = array(
        		'slug'                  => GMW_FL_SLUG,
        		'root_slug'             => isset($bp->pages->{$this->id}->slug) ? $bp->pages->{$this->id}->slug : GMW_FL_SLUG,
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
    function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        global $bp;

        // Add 'location' to the main navigation
        $main_nav = apply_filters( 'gmw_fl_setup_nav', array(
        		'name'                => __( 'Location', 'GMW' ),
        		'slug'                => GMW_FL_SLUG,
        		'position'            => 60,
        		'screen_function'     => array($this, 'screen_functions'),
        		'default_subnav_slug' => GMW_FL_SLUG
        ), $bp->displayed_user );

        $user_domain = ( !empty( $bp->displayed_user->id ) ) ? $bp->displayed_user->domain : $bp->loggedin_user->domain;

        $gmw_location_link = trailingslashit( $user_domain . GMW_FL_SLUG );

        parent::setup_nav( $main_nav );
    }

     /**
     * screen functions
     */
    function screen_functions() {

        global $bp;

        $who = ( bp_is_my_profile() && apply_filters( 'gmw_fl_location_tab_mine', true ) == true ) ? 'my' : 'user';

        add_action( 'bp_template_title',   array( $this, 'screen_page_title' ) );
        add_action( 'bp_template_content', array( $this, 'screen_page_'.$who.'_content' ) );

        bp_core_load_template( apply_filters( 'gmw_location_my_screen_functions', 'members/single/plugins' ) );
    }

    /**
     * GMW FL function - title for the "location" tab
     */
    function screen_page_title() {
        //_e('Location', 'GMW');
    }

    /**
     * GMW FL function - Content of the  "location" tab for logged in user
     */
    function screen_page_my_content() {
        include_once GMW_FL_PATH . '/includes/gmw-fl-location-tab.php';
    }

    /**
     * GMW FL function - content for the user's "location" tab
     */
    function screen_page_user_content() {
    	
    	if ( class_exists( 'GMW_Single_Member_Location' ) ) {
    		$content = '[gmw_single_location item_type="member" elements="address,map" address_fields="address" map_height="300px" map_width="100%" user_map_icon="0"]';
    	} else {
    		$content = '[gmw_member_location map_height="300px" map_width="100%" no_location="1" address_fields="address" directions="0" display_name="0"]';
    	}
    	
        echo do_shortcode( apply_filters( 'gmw_fl_user_location_tab_content', $content ) );
    }

    /**
     * GMW location admin bar
     * @see BP_Component::setup_admin_bar()
     */
    function setup_admin_bar( $wp_admin_nav = array() ) {
        global $bp;

        // Prevent debug notices
        $wp_admin_nav = array();

        // Menus for logged in user
        if ( is_user_logged_in() ) {

            // Setup the logged in user variables
            $gmw_location_link = trailingslashit( bp_loggedin_user_domain().GMW_FL_SLUG );

            // Add location tab
            $wp_admin_nav[] = apply_filters( 'gmw_fl_setup_admin_bar', array(
            		'parent' => 'my-account-buddypress',
            		'id'     => 'my-account-' . GMW_FL_SLUG,
            		'title'  => __( 'Location', 'GMW' ),
            		'href'   => trailingslashit($gmw_location_link)
            ));

            // Add main bp checkins my places submenu
            $wp_admin_nav[] = array(
            		'parent' => 'my-account-' . GMW_FL_SLUG,
            		'id'     => 'my-account-' . GMW_FL_SLUG . '-gmw-location',
            		'title'  => __( 'Update Location', 'GMW' ),
            		'href'   => trailingslashit($gmw_location_link)
            );
        }

        parent::setup_admin_bar($wp_admin_nav);
    }

    /**
     * GMW Location register post types
     * 
     */
    function register_post_types() {
    	
    	// Set up some labels for the post type
    	$labels = array(
    			'name'     => __( 'Members Location', 'GMW' ),
    			'singular' => __( "Member's Location", 'GMW' )
    	);

    	// Set up the argument array for register_post_type()
    	$args = array(
    			'label'    => __( 'Members Location', 'GMW' ),
    			'labels'   => $labels,
    			'public'   => false,
    			'show_ui'  => true,
    			'supports' => array('title')
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

    /**
     * frontend_scripts function.
     *
     * @access public
     * @return void
     */
    public function frontend_register_scripts() {
        wp_register_style( 'gmw-fl-style', GMW_FL_URL.'assets/css/style.css' );
        wp_enqueue_style( 'gmw-fl-style' );
        wp_register_script( 'gmw-fl', GMW_FL_URL.'assets/js/fl.min.js', array('jquery'), GMW_VERSION, true );
    }

    /**
     * add location menu to activity order by
     */
    function location_filter_options() {
    ?>
        <option value="gmw_location"><?php _e( 'Location', 'GMW' ); ?></option>
    <?php
    }
}
?>