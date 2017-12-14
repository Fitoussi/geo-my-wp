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

    // Plugin use template files
    public $template_files = true;

    // folder name
    //public $templates_folder = 'templates';

    // custom folder name
    //public $custom_templates_folder = 'posts-locator';

    // path
    public $full_path = __FILE__;
    
    // core add-on
    public $is_core = true;

    // New form button
    public $form_buttons =  array( 
        'slug'      => 'posts_locator',
        'name'      => 'Posts Locator',
        'prefix'    => 'pt',
        'priority'  => 5 
    );
    
    public function pre_init() {  

        parent::pre_init();

        include( 'includes/gmw-posts-locator-functions.php' );

        // include admin files
        if ( IS_ADMIN ) {
            include( 'includes/admin/class-gmw-posts-locator-admin-settings.php' );
            include( 'includes/admin/class-gmw-posts-locator-form-editor.php' );
            include( 'includes/admin/class-gmw-posts-locator-screens.php' );
            include( 'includes/admin/class-gmw-posts-locator-location-form.php' );
        } 
        //else {

            include( 'includes/class-gmw-posts-locator-form.php' );

            if ( gmw_is_addon_active( 'single_location' ) ) {
                include( 'includes/class-gmw-single-post-location.php' );
            } 
        //}

        include( 'includes/gmw-posts-locator-search-form-template-functions.php' );
        include( 'includes/gmw-posts-locator-search-results-template-functions.php' );

    }
}
new GMW_Posts_Locator_Addon();