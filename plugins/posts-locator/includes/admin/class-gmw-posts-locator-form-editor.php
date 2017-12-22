<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_PT_Admin class
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
        add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 20, 2 );
        // init form settings
        add_filter( 'gmw_posts_locator_form_settings', array( $this, 'form_settings_init' ), 5 );
        // form settings functions
        add_action( 'gmw_posts_locator_form_settings_form_taxonomies', array( 'GMW_Form_Settings_Helper', 'taxonomies' ), 10, 3 );
        add_action( 'gmw_posts_locator_form_settings_excerpt', array( 'GMW_Form_Settings_Helper', 'excerpt' ), 10, 2 );
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

        if ( isset( $args['prefix'] ) && $args['prefix'] == 'pt' ) {
            $settings['page_load_results']['post_types'] = array( 'post' ); 
            $settings['search_form']['form_template']    = 'gray';
            $settings['search_form']['post_types']       = array( 'post' );
            $settings['search_results']['excerpt']       = array( 
                'use'   => '', 
                'count' => 10,
                'link'  => 'read more...' 
            ); 
            $settings['search_results']['taxonomies'] = '';
        }

        return $settings;
    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {

        $settings['page_load_results']['post_types'] = array( 
            'name'          => 'post_types',
            'type'          => 'multiselect',
            'default'       => array( 'post' ),
            'label'         => __( 'Post Types', 'GMW' ),
            'placeholder'   => __( 'Select post types', 'GMW' ),
            'desc'          => __( 'Select the post types to filter the search results.', 'GMW' ),
            'options'       => GMW_Form_Settings_Helper::get_post_types(), 
            'attributes'    => '',
            'priority'      => 13     
        );

        $settings['search_form']['post_types'] = array(
            'name'          => 'post_types',
            'type'          => 'multiselect',
            'default'       => array( 'post' ),
            'label'         => __( 'Post Types', 'GMW' ),
            'placeholder'   => __( 'Select post types', 'GMW' ),
            'desc'          => __( 'Select a single post type to set as the default, or multiple post types to display as a dropdown select box in the search form.', 'GMW' ),
            'options'       => GMW_Form_Settings_Helper::get_post_types(),
            'attributes'    => '',
            'priority'      => 12    
        );

        $settings['search_form']['taxonomies'] = array(
            'name'          => 'taxonomies',
            'type'          => 'function',
            'function'      => 'form_taxonomies',
            'default'       => '',
            'label'         => __( 'Taxonomies', 'GMW' ),
            'desc'          => __( 'Enable the taxonomies which you would like to use as filters in the search form. This feature availabe only when selecting a single post type above.', 'GMW' ),
            'attributes'    => '',
            'priority'      => 13                
        );
            
        $settings['search_results']['location_meta'] = array(
            'name'          => 'location_meta',
            'type'          => 'multiselect',
            'default'       => '',
            'label'         => __( 'Location Meta', 'GMW' ),
            'placeholder'   => __( 'Select location metas', 'GMW' ),
            'desc'          => __( "Select the the location meta fields which you would like to display for each location in the list of results.", 'GMW' ),
            'options'       => array(
                'phone'     => __( 'Phone', 'GMW' ),
                'fax'       => __( 'Fax', 'GMW' ),
                'email'     => __( 'Email', 'GMW' ),
                'website'   => __( 'Website', 'GMW' ),
            ),
            'attributes'    => '',
            'priority'      => 36   
        );

        $settings['search_results']['opening_hours'] = array(
            'name'          => 'opening_hours',
            'type'          => 'checkbox',
            'default'       => '',
            'label'         => __( 'Hours of Operation', 'GMW' ),
            'cb_label'      => __( 'Enable', 'GMW' ),
            'desc'          => __( 'Display opening days & hours for each location in the list of results.', 'GMW' ),
            'attributes'    => '',
            'priority'      => 38   
        );

        $settings['search_results']['excerpt'] = array(
            'name'          => 'excerpt',
            'type'          => 'function',
            'default'       => '',
            'label'         => __( 'Excerpt', 'GMW' ),
            'cb_label'      => '',
            'desc'          => __( 'Display excerpt in each location in the results.', 'GMW' ),
            'attributes'    => '',
            'priority'      => 40   
        );

        $settings['search_results']['taxonomies'] = array(
            'name'          => 'taxonomies',
            'type'          => 'checkbox',
            'default'       => '',
            'label'         => __( 'Taxonomies', 'GMW' ),
            'cb_label'      => __( 'Enable', 'GMW' ),
            'desc'          => __( 'Check this checkbox to display the taxonomies and terms associate with each post in the list of results.', 'GMW' ),
            'attributes'    => '',
            'priority'      => 65   
        );
         
        return $settings;
    }
}
new GMW_Posts_Locator_Form_Editor();
?>