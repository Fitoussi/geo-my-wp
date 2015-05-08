<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_ND_Admin class
 */

class GMW_SD_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->settings = get_option( 'gmw_options' );
        
        add_filter( 'gmw_admin_settings', array( $this, 'settings_init' ) );

        if ( empty( $this->settings['sweet_date'] ) ) {
            add_filter( 'admin_init', array( $this, 'default_options' ) );
        }   
    }
    
    /**
     * Set deafult values if not exists
     * 
     */
    public function default_options() {

    	$this->settings['sweet_date'] = array(
    			'radius'     => '10,25,50,100,200',
    			'units'      => '3959',
    			'map_use'    => 1,
    			'map_width'  => '100%',
    			'map_height' => '300px',
    			'map_typw'   => 'ROADMAP',
    			'distance'   => 1,
    			'address'    => 1,
    			'directions' => 1
    	);
    	update_option( 'gmw_options', $this->settings );

    }

    /**
     * addon settings page function.
     *
     * @access public
     * @return $settings
     */
    public function settings_init( $settings ) {

    	$settings['sweet_date'] = array(
    			__( 'Sweet Date', 'GMW' ),
    			array(
    					'address_autocomplete_use' => array(
    							'name'       => 'address_autocomplete_use',
    							'std'        => '',
    							'label'      => __( 'Google Places address autocomplete', 'GMW' ),
    							'desc'       => __( 'Display suggested results via Google address autcompelte while typing an address.', 'GMW' ),
    							'type'       => 'checkbox',
    							'cb_label'   => 'Yes',
    							'attributes' => array(),
    					),
    					'radius'     => array(
    							'name'        => 'radius',
    							'std'         => '10,25,50,100,200',
    							'placeholder' => __( 'Enter radius values', 'GMW' ),
    							'label'       => __( 'Radius', 'GMW' ),
    							'desc'        => __( 'Enter the radius values. Single value to be default value or multiple values comma separated for a drop-down select box.', 'GMW' ),
    							'type'        => 'text'
    					),
    					'units'      => array(
    							'name'       => 'units',
    							'std'        => '3959',
    							'label'      => __( 'Units', 'GMW' ),
    							'desc'       => __( 'Choose Between Miles and Kilometers.', 'GMW' ),
    							'type'       => 'radio',
    							'attributes' => array(),
    							'options'    => array(
    									'3959' => __( 'Miles', 'GMW' ),
    									'6371' => __( 'Kilometers', 'GMW' ),
    							)
    					),
    					'orderby_use'  => array(
    							'name'       => 'orderby_use',
    							'std'        => '',
    							'label'      => __( "Display \"Order by\" filter", "GMW" ),
    							'desc'       => __( "Display \"Order by\" filter.", "GMW" ),
    							'type'       => 'checkbox',
    							'cb_label'   => 'Yes',
    							'attributes' => array(),
    					),
    					'map_use'    => array(
    							'name'       => 'map_use',
    							'std'        => '',
    							'label'      => __( 'Show Map', 'GMW' ),
    							'desc'       => __( 'Show/hide map above list of members results', 'GMW' ),
    							'type'       => 'checkbox',
    							'cb_label'   => 'Yes',
    							'attributes' => array(),
    					),
    					'map_width'  => array(
    							'name'        => 'map_width',
    							'std'         => '100%',
    							'placeholder' => __( 'Map width in pixels or percentage', 'GMW' ),
    							'label'       => __( 'Map Width', 'GMW' ),
    							'desc'        => __( 'Maps width in pixels or percentage', 'GMW' ),
    							'type'        => 'text'
    					),
    					'map_height' => array(
    							'name'        => 'map_height',
    							'std'         => '300px',
    							'placeholder' => __( 'Map height in pixels or percentage', 'GMW' ),
    							'label'       => __( 'Map Height', 'GMW' ),
    							'desc'        => __( 'Maps height in pixels or percentage', 'GMW' ),
    							'type'        => 'text'
    					),
    					'map_type'   => array(
    							'name'       => 'map_type',
    							'std'        => 'ROADMAP',
    							'label'      => __( 'Map Type', 'GMW' ),
    							'desc'       => __( 'Map type', 'GMW' ),
    							'type'       => 'select',
    							'attributes' => array(),
    							'options'    => array(
    									'ROADMAP'   => __( 'ROADMAP', 'GMW' ),
    									'SATELLITE' => __( 'SATELLITE', 'GMW' ),
    									'HYBRID'    => __( 'HYBRID', 'GMW' ),
    									'TERRAIN'   => __( 'TERRAIN', 'GMW' ),
    							)
    					),
    					'distance'   => array(
    							'name'       => 'distance',
    							'std'        => '',
    							'label'      => __( 'Display Distance?', 'GMW' ),
    							'cb_label'   => __( 'Yes', 'GMW' ),
    							'desc'       => __( 'Show/hide distance in results.', 'GMW' ),
    							'type'       => 'checkbox',
    							'attributes' => array()
    					),
    					'address'    => array(
    							'name'       => 'address',
    							'std'        => '',
    							'label'      => __( 'Display Address?', 'GMW' ),
    							'cb_label'   => __( 'Yes', 'GMW' ),
    							'desc'       => __( 'Show/hide member\'s address in results.', 'GMW' ),
    							'type'       => 'checkbox',
    							'attributes' => array()
    					),
    					'directions' => array(
    							'name'       => 'directions',
    							'std'        => '',
    							'label'      => __( 'Display "Get directions" Link?', 'GMW' ),
    							'cb_label'   => __( 'Yes', 'GMW' ),
    							'desc'       => __( 'Show/hide get directions link in each results. The link will open a new page with Google map showing the directions from the address entered by the user to the member\'s location.', 'GMW' ),
    							'type'       => 'checkbox',
    							'attributes' => array()
    					),
    			),
    	);

    	return $settings;
    }
}