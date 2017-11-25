<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Members Locator component
 *
 * @author Eyal Fitoussi
 */
class GMW_Members_Locator_Component extends BP_Component {

    /**
     * Constructor method
     *
     * @package GMW 
     */
    public function __construct() {
        
        global $bp;

        define( 'GMW_FL_SLUG' , 'location' );

        // add location item to dropdown menu filter
        add_action( 'bp_activity_filter_options', 		 array( $this, 'location_filter_options' ), 10 );
        add_action( 'bp_member_activity_filter_options', array( $this, 'location_filter_options' ), 10 );
        add_filter( 'gmw_lf_user_location_args_before_location_updated', array( $this, 'get_member_name' ) );

        parent::start( GMW_FL_SLUG, __( 'Location', 'GMW' ), GMW_FL_PATH );

        $this->includes();

        $bp->active_components[$this->id] = '1';
    }

    /**
     * Add location item to dropdown menu filter
     * 
     * @return [type] [description]
     */
    public function location_filter_options() {
    ?>
        <option value="gmw_location"><?php _e( 'Location', 'GMW' ); ?></option>
    <?php
    }

    /**
     * GMW location files
     *
     * @package GMW Location
     * @since 1.0
     */
    public function includes( $includes = array() ) {

    	// include files
    	$includes = array(   			
			'includes/gmw-members-locator-actions.php',
			'includes/gmw-member-locator-activity.php',
			'includes/gmw-members-locator-template-functions.php',
			'includes/class-gmw-members-locator-form.php'
    	);
    	
        // include single member location file if needed
    	if ( gmw_is_addon_active( 'single_location' ) ) {
    		$includes[] = 'includes/class-gmw-single-member-location.php';
    	}
    	
        // admin files
    	if ( is_admin() ) {
    		$includes[] = 'includes/admin/class-gmw-members-locator-form-editor.php';
    	}

    	parent::includes( $includes );
    }

    /**
     * GMW Location globals
     */
    public function setup_globals( $args = array() ) {
        
        global $bp;

        // Defining the slug in this way makes it possible for site admins to override it
        if ( ! defined( 'GMW_FL_SLUG' ) ) {
            define( 'GMW_FL_SLUG', $this->id );
        }

        // Set up the $globals array to be passed along to parent::setup_globals()
        $globals = array(
    		'slug'                  => GMW_FL_SLUG,
    		'root_slug'             => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug : GMW_FL_SLUG,
    		'has_directory'         => false, // Set to false if not required
    		'notification_callback' => false,
    		'search_string'         => __( 'Search Location...', 'GMW' )
        );

        // Let BP_Component::setup_globals() do its work.
        parent::setup_globals( $globals );
    }

    /**
     * Setup Location tab
     * @param  array  $main_nav [description]
     * @param  array  $sub_nav  [description]
     * @return [type]           [description]
     */
    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        
        global $bp;

        // Add 'location' to the main navigation
        $main_nav = apply_filters( 'gmw_fl_setup_nav', array(
    		'name'                => __( 'Location', 'GMW' ),
    		'slug'                => GMW_FL_SLUG,
    		'position'            => 60,
    		'screen_function'     => array( $this, 'screen_functions' ),
    		'default_subnav_slug' => GMW_FL_SLUG
        ), $bp->displayed_user );

        parent::setup_nav( $main_nav );
    }

    /**
     * Location tab screen functions
     * 
     * @return [type] [description]
     */
    public function screen_functions() {

        global $bp;

        $who = ( bp_is_my_profile() && ! apply_filters( 'gmw_fl_disable_logged_in_location_tab', false ) ) ? 'logged_in_user' : 'displayed_user';

        $this->location_tab_title = apply_filters( 'gmw_fl_location_tab_title', false );
        
        // location tab title. can be enabled by passing text to the filter
        if ( ! empty( $this->location_tab_title ) ) {
            add_action( 'bp_template_title',   array( $this, 'location_tab_title' ) );
        }
        
        add_action( 'bp_template_content', array( $this, $who.'_content' ) );

        bp_core_load_template( apply_filters( 'gmw_location_my_screen_functions', 'members/single/plugins' ) );
    }

    /**
     * Location tab title.
     * @return [type] [description]
     */
    public function location_tab_title() {
        echo $this->location_tab_title;
    }

    /**
     * Location tab content
     * @return [type] [description]
     */
    public function logged_in_user_content() {
        include_once GMW_FL_PATH . '/includes/class-gmw-member-location-form.php';
    }

    /**
     * GMW FL function - content for the user's "location" tab
     */
    public function displayed_user_content() {
    	
        echo '<div class="location gmw">';
        // Single Location add-on must be activated to display full location details
    	if ( gmw_is_addon_active( 'single_location' ) ) {
    		
            $content = '[gmw_single_location object_type="member" elements="address,map" address_fields="address" map_height="300px" map_width="100%" user_map_icon="0" no_location_message="'.__( ' The user has not added his location yet.', 'GMW' ).'"]';
    	
        // otherwise, display only address field
        } else {
    		
            $content = '<div id="gmw-ml-member-address"><i class="gmw-icon-location"></i>'. esc_attr( gmw_get_user_address() ) .'</div>';
    	}
    	
        echo do_shortcode( apply_filters( 'gmw_fl_user_location_tab_content', $content ) );

        echo '</div>';
    }

    /**
     * Location menu item in admin bar
     * 
     * @see BP_Component::setup_admin_bar()
     */
    public function setup_admin_bar( $wp_admin_nav = array() ) {
        
        global $bp;

        // Prevent debug notices
        $wp_admin_nav = array();

        // Menus for logged in user
        if ( is_user_logged_in() ) {

            // Setup the logged in user variables
            $location_link = trailingslashit( bp_loggedin_user_domain().GMW_FL_SLUG );

            // Add location tab
            $wp_admin_nav[] = apply_filters( 'gmw_fl_setup_admin_bar', array(
        		'parent' => 'my-account-buddypress',
        		'id'     => 'my-account-' . GMW_FL_SLUG,
        		'title'  => __( 'Location', 'GMW' ),
        		'href'   => $location_link
            ));

            // add submenu tab
            $wp_admin_nav[] = array(
        		'parent' => 'my-account-' . GMW_FL_SLUG,
        		'id'     => 'my-account-' . GMW_FL_SLUG . '-gmw-location',
        		'title'  => __( 'Update Location', 'GMW' ),
        		'href'   => $location_link
            );
        }

        parent::setup_admin_bar( $wp_admin_nav );
    }

    /**
     * Get the member name to save as location title before location is saved
     * 
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    public function get_member_name( $location ) {

        $name = bp_core_get_username( $location['object_id'] );

        if ( ! empty( $name ) ) {
            $location['title'] = $name;
        }
        
        return $location;
    }
}
?>