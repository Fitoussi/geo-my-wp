<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Register_Addon' ) ) {
    return;
}

/**
 * Current Location addon
 * 
 */
class GMW_Single_Location_Addon extends GMW_Register_Addon {
    
    // slug
    public $slug = "single_location";

    // add-on's name
    public $name = "Single Location";

    // prefix
    public $prefix = "sl";

    // version
    public $version = GMW_VERSION;
    
    // author
    public $author = "Eyal Fitoussi";

    // path
    public $full_path = __FILE__;
    
    // description
    public $description = "Display location of certain component ( post, member... ) via shortcode and widget.";

    // core add-on
    public $is_core = true;

    /**
     * Init widgets
     * 
     * @return [type] [description]
     */
    function init_widgets() {
        include( 'includes/widget-gmw-single-location.php' );
    }
    
    /**
     * Include files
     * 
     * @return [type] [description]
     */
    public function pre_init() {  
        
        parent::pre_init();
        
        //include classes files
        if ( ! IS_ADMIN ) {  
            include( 'includes/class-gmw-single-location.php' );
            include( 'includes/shortcode-gmw-single-location.php' );  
        }
    }
}
new GMW_Single_Location_Addon();