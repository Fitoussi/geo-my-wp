<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMW_Register_Addon' ) ) {
    return;
}

/**
 * Members Locator add-on class
 */
class GMW_Members_locator_Addon extends GMW_Register_Addon {
    
    // slug
    public $slug = "members_locator";

    // title
    public $name = "Members Locator";
    
    // prefix
    public $prefix = "fl";

    // version
    public $version = GMW_VERSION;

     // description
    public $description = "GeoTag Buddypress members and create proximity form to search and find BuddyPress members location based.";

    // database object type
    public $object_type = 'user';

    // db table prefix. We use the base prefix table to save users across all subsites 
    // in multisite installation
    public $global_db = true;

    public $templates = true;

    // folder name
    //public $templates_folder = 'templates';

    // custom folder name
    //public $custom_templates_folder = 'members-locator';

    // path
    public $full_path = __FILE__;
    
    // is core add-on
    public $is_core = true;

    /**
     * New form button
     * @var array
     */
    public $form_buttons =  array( 
        'slug'      => 'members_locator',
        'name'      => 'Members Locator',
        'prefix'    => 'fl',
        'priority'  => 10 
    );
    
    public function __construct() {
        
        // When multisite enabled, and buddypress is multisite activated,
        // the blog_id we will be using will be buddypress's root blog.
        // Otherwise, members will be saved per blog 
        if ( is_multisite() && function_exists( 'bp_is_network_activated' ) && bp_is_network_activated() ) {
            $this->locations_blog_id = BP_ROOT_BLOG;
        }

        parent::__construct();
    }
    /**
     * [required description]
     * @return [type] [description]
     */
    public function required() {

        return array(

            'plugins' => array(
                array(
                    'function' => 'BuddyPress',
                    'notice'   => __( 'Members Locator add-on requires BuddyPress plugin version 2.8 or higher.', 'GMW' )
                )
            )
        );
    }

    /**
     * Disable activation in add-ons page if BP is not installed.
     * 
     * @return [type] [description]
     */
    /*
    public function disable_activation() {

        $this->verify_bp = ( ! class_exists( 'BuddyPress' ) || version_compare( BP_VERSION, 2.8, '<' )  ) ? false : true;

        $this->deactivation_message = __( 'Members Locator add-on requires BuddyPress plugin version 2.8 or higher.', 'GMW' );

        if ( ! $this->verify_bp ) {
            
            return $this->deactivation_message;
        
        } else {
            
            return false;
        }
    }
    */
    /**
     * Missing plugin notice
     * 
     * @return [type] [description]
     */
    /*
    public function buddypress_missing_notice() {
        ?>
        <div class="error">
            <p><?php echo $this->deactivation_message; ?></p>
        </div> 
        <?php 
    }
    */
    /**
     * Verify BuddyPress plugin
     * 
     * @return [type] [description]
     */
    /*
    public function verify_activation() {

        if ( ! $this->verify_bp ) {
            
            add_action( 'admin_notices', array( $this, 'buddypress_missing_notice' ) );

            // deactivate addon
            if ( IS_ADMIN ) {
                $this->deactivate_addon();
            }

            return false;

        } else {

            return true;
        }
    }
    */

    /**
     * Initiate the plugin
     * @return void
     */
    public function pre_init() {
       
        parent::pre_init();

        // load add-on with bp initiate
        add_action( 'bp_loaded', array( $this, 'load_members_locator' ), 20 );
    }

    /**
     * Initicallback function
     * @return [type] [description]
     */
    public function load_members_locator() {
    	global $bp;

	    //include component files
	    include_once( 'includes/class-gmw-members-locator-component.php' );

	    //load Members Locator component
	    if ( class_exists( 'GMW_Members_Locator_Component' ) ) {
	        $bp->gmw_location = new GMW_Members_Locator_Component;
	    }
    }
}
new GMW_Members_locator_Addon();