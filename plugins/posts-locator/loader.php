<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//abort if register add-on class is not found
if ( ! class_exists( 'GMW_Register_Addon' ) ) {
    return;
}

/**
 * Register Posts locator add-on
 * 
 */
class GMW_Posts_Locator_Addon extends GMW_Register_Addon {
    
    // slug
    public $slug = "posts_locator";

    // add-on's name
    public $name = "Posts Locator";

    // prefix
    public $prefix = "pt";
    
    // version
    public $version = GMW_VERSION;

    // author
    public $author = "Eyal Fitoussi";

    // description
    public $description = "Provides geolocation for WordPress post types.";

    // database object type
    public $object_type = 'post';

    // db table prefix
    public $global_db = false;

    // folder name
    public $templates_folder = 'posts';

    // path
    public $full_path = __FILE__;
    
    // core add-on
    public $is_core = true;

    // New form button
    public $form_buttons =  array( 
        'slug'      => 'posts_locator',
        'name'      => 'Posts Locator',
        'prefix'    => 'pt',
        'priority'  => 1 
    );
    
    public function pre_init() {  

        parent::pre_init();

        include( 'includes/gmw-pt-functions.php' );

        // include admin files
        if ( IS_ADMIN ) {
            
            include( 'includes/admin/class-gmw-pt-admin.php' );
            include( 'includes/admin/class-gmw-pt-screens.php' );
            include( 'includes/admin/class-gmw-pt-location-form.php' );
        
        } 
        //else {

            include( 'includes/class-gmw-posts-locator-form.php' );

            if ( gmw_is_addon_active( 'single_location' ) ) {
                include( 'includes/class-gmw-single-post-location.php' );
            } 
        //}

        include( 'includes/gmw-pt-template-functions.php' );
    }
}
new GMW_Posts_Locator_Addon();