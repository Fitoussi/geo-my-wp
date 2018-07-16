<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_Posts_Locator_Form_Editor
 * 
 * Post type locator admin functions
 */
class GMW_Posts_Locator_Form_Editor {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        
        add_filter( 'gmw_posts_locator_form_default_settings', array( $this, 'default_settings' ), 5, 2 );
        
        // init form settings
        add_filter( 'gmw_posts_locator_form_settings', array( $this, 'form_settings_init' ), 5 );
        
        // form settings functions
        add_action( 'gmw_posts_locator_form_settings_form_taxonomies', array( 'GMW_Form_Settings_Helper', 'taxonomies' ), 5, 3 );
        add_action( 'gmw_posts_locator_form_settings_excerpt', array( 'GMW_Form_Settings_Helper', 'excerpt' ), 5, 2 );
        
        // form settings validations
        add_filter( 'gmw_posts_locator_validate_form_settings_excerpt', array( 'GMW_Form_Settings_Helper', 'validate_excerpt' ) );
    }

    /**
     * Default settings 
     * 
     * @param  [type] $settings [description]
     * @return [type]           [description]
     */
    public function default_settings( $settings, $args ) {

        $settings['page_load_results']['post_types'] = array( 'post' ); 
        $settings['search_form']['form_template']    = 'gray';
        $settings['search_form']['post_types']       = array( 'post' );
        $settings['search_results']['excerpt']       = array( 
            'use'   => '', 
            'count' => 10,
            'link'  => 'read more...' 
        ); 
        $settings['search_results']['taxonomies'] = '';

        return $settings;
    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    public function form_settings_init( $settings ) {

        $settings['page_load_results']['post_types'] = array( 
            'name'          => 'post_types',
            'type'          => 'multiselect',
            'default'       => array( 'post' ),
            'label'         => __( 'Post Types', 'geo-my-wp' ),
            'placeholder'   => __( 'Select post types', 'geo-my-wp' ),
            'desc'          => __( 'Select the post types to filter the search results.', 'geo-my-wp' ),
            'options'       => GMW_Form_Settings_Helper::get_post_types(), 
            'attributes'    => '',
            'priority'      => 13     
        );

        $settings['search_form']['post_types'] = array(
            'name'          => 'post_types',
            'type'          => 'multiselect',
            'default'       => array( 'post' ),
            'label'         => __( 'Post Types', 'geo-my-wp' ),
            'placeholder'   => __( 'Select post types', 'geo-my-wp' ),
            'desc'          => __( 'Select a single post type to set as the default, or multiple post types to display as a dropdown select box in the search form.', 'geo-my-wp' ),
            'options'       => GMW_Form_Settings_Helper::get_post_types(),
            'attributes'    => '',
            'priority'      => 12    
        );

        $settings['search_form']['taxonomies'] = array(
            'name'          => 'taxonomies',
            'type'          => 'function',
            'function'      => 'form_taxonomies',
            'default'       => '',
            'label'         => __( 'Taxonomies', 'geo-my-wp' ),
            'desc'          => __( 'Enable the taxonomies which you would like to use as filters in the search form. This feature availabe only when selecting a single post type above.', 'geo-my-wp' ),
            'attributes'    => '',
            'priority'      => 13                
        );
            
        $settings['search_results']['location_meta'] = array(
            'name'          => 'location_meta',
            'type'          => 'multiselect',
            'default'       => '',
            'label'         => __( 'Location Meta', 'geo-my-wp' ),
            'placeholder'   => __( 'Select location metas', 'geo-my-wp' ),
            'desc'          => __( "Select the the location meta fields which you would like to display for each location in the list of results.", 'geo-my-wp' ),
            'options'       => array(
                'phone'     => __( 'Phone', 'geo-my-wp' ),
                'fax'       => __( 'Fax', 'geo-my-wp' ),
                'email'     => __( 'Email', 'geo-my-wp' ),
                'website'   => __( 'Website', 'geo-my-wp' ),
            ),
            'attributes'    => '',
            'priority'      => 36   
        );

        $settings['search_results']['opening_hours'] = array(
            'name'          => 'opening_hours',
            'type'          => 'checkbox',
            'default'       => '',
            'label'         => __( 'Hours of Operation', 'geo-my-wp' ),
            'cb_label'      => __( 'Enable', 'geo-my-wp' ),
            'desc'          => __( 'Display opening days & hours for each location in the list of results.', 'geo-my-wp' ),
            'attributes'    => '',
            'priority'      => 38   
        );

        $settings['search_results']['excerpt'] = array(
            'name'          => 'excerpt',
            'type'          => 'function',
            'default'       => '',
            'label'         => __( 'Excerpt', 'geo-my-wp' ),
            'cb_label'      => '',
            'desc'          => __( 'Display excerpt in each location in the results.', 'geo-my-wp' ),
            'attributes'    => '',
            'priority'      => 40   
        );

        $settings['search_results']['taxonomies'] = array(
            'name'          => 'taxonomies',
            'type'          => 'checkbox',
            'default'       => '',
            'label'         => __( 'Taxonomies', 'geo-my-wp' ),
            'cb_label'      => __( 'Enable', 'geo-my-wp' ),
            'desc'          => __( 'Check this checkbox to display the taxonomies and terms associate with each post in the list of results.', 'geo-my-wp' ),
            'attributes'    => '',
            'priority'      => 65   
        );
         
        return $settings;
    }
}
new GMW_Posts_Locator_Form_Editor();
?>
