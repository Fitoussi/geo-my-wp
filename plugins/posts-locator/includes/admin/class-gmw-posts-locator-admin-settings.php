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
class GMW_Posts_Locator_Admin_Settings {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // setup default values for settings
        add_filter( 'gmw_admin_settings_setup_defaults', array( $this, 'setup_defaults' ) );
        add_filter( 'gmw_admin_settings', array( $this, 'admin_settings' ), 5 );
    }

    /**
     * Defaukt options will setup when no options exist. Usually when plugin first installed.
     * 
     * @param  [type] $defaults [description]
     * @return [type]           [description]
     */
    public function setup_defaults( $defaults ) {

        $defaults['post_types_settings'] = array(
            
            'edit_post_exclude_tabs' => array(
                'dynamic'    => 1,
                'address'    => 1,
                'coords'     => 1,
                'contact'    => 1,
                'days-hours' => 1
            ),
            'edit_post_page_map_latitude'   => '40.711544',
            'edit_post_page_map_longitude'  => '-74.013486', 
            'edit_post_page_map_type'       => 'ROADMAP',
            'edit_post_page_map_zoom_level' => 7,       
            'post_types' => array( 'post' )
        );

        return $defaults;
    }

    /**
     * post types settings in GEO my WP main settings page
     *
     * @access public
     * @return $settings
     */
    public function admin_settings( $settings ) {

    	$settings['post_types_settings']['post_types'] = array(
			'name'          => 'post_types',
            'type'          => 'multiselect',
			'default'       => '',
			'label'         => __( 'Post Types', 'GMW' ),
			'desc'          => __( 'Select the post types where you would like to enable geotagging. GEO my WP Location section will be added to the "Edit Post" page of the selected post types.', 'GMW' ),
            'options'       => gmw_get_post_types_array(),
            'attributes'    => array(),
            'priority'      => 10
		);

        $zoom_levels = array();
        
        for ( $i = 1; $i <= 21; $i++ ) {
            $zoom_levels[$i] = $i;
        }

        $settings['post_types_settings']['edit_post_page_options'] = array(
            'name'          => 'edit_post_page_options',
            'type'          => 'fields_group',
            'label'         => __( 'Map Settings ( "Edit Post" Page )', 'GMW' ),
            'desc'          => __( 'Setup the map of the Location section in the "Edit Post" page.' , 'GMW' ),
            'fields'        => array(
                'edit_post_page_map_latitude'  => array(
                    'name'          => 'edit_post_page_map_latitude',
                    'type'          => 'text',
                    'default'       => '40.711544',
                    'placeholder'   => __( 'Latitude', 'GMW' ),
                    'label'         => __( 'Default latitude', 'GMW' ),
                    'desc'          => __( 'Enter the latitude of the default location that will show when the map first loads.', 'GMW' ),
                    'attributes'    => array(),
                    'priority'      => 5
                ),
                'edit_post_page_map_longitude'  => array(
                    'name'          => 'edit_post_page_map_longitude',
                    'type'          => 'text',
                    'default'       => '-74.013486',
                    'placeholder'   => __( 'Longitude', 'GMW' ),
                    'label'         => __( 'Default longitude', 'GMW' ),
                    'desc'          => __( 'Enter the longitude of the default location that will show when the map first loads.', 'GMW' ),
                    'attributes'    => array(),
                    'priority'      => 10
                ),
                'edit_post_page_map_type'   => array(
                    'name'          => 'edit_post_page_map_type',
                    'type'          => 'select',
                    'default'       => 'ROADMAP',
                    'label'         => __( 'Map type', 'GMW' ),
                    'desc'          => __( 'Select the map type.', 'GMW' ),
                    'options'       => array(
                        'ROADMAP'       => __( 'ROADMAP', 'GMW' ),
                        'SATELLITE'     => __( 'SATELLITE', 'GMW' ),
                        'HYBRID'        => __( 'HYBRID', 'GMW' ),
                        'TERRAIN'       => __( 'TERRAIN', 'GMW' )
                    ),
                    'attributes'    => array(),
                    'priority'      => 15
                ),
                'edit_post_page_map_zoom_level'   => array(
                    'name'          => 'edit_post_page_map_zoom_level',
                    'type'          => 'select',
                    'default'       => 7,
                    'label'         => __( 'Map type', 'GMW' ),
                    'desc'          => __( 'Select the zoom level of the map.', 'GMW' ),
                    'options'       => $zoom_levels,
                    'attributes'    => array(),
                    'priority'      => 20
                ),
            ),
            'attributes' => '',
            'optionsbox' => 1,  
            'priority'   => 20
        );

        $settings['post_types_settings']['location_mandatory'] = array(
            'name'          => 'location_mandatory',
            'type'          => 'checkbox',
            'default'       => 0,
            'label'         => __( 'Mandatory Location', 'GMW' ),
            'cb_label'      => __( 'Enable', 'GMW' ),
            'desc'          => __( 'Prevent post submission when no location entered.', 'GMW' ),
            'attributes'    => array(),
            'priority'      => 30
        );

      	return $settings;
    }
}
new GMW_Posts_Locator_Admin_Settings();
?>