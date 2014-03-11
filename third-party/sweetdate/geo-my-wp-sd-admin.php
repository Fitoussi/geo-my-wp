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

        if ( !isset( $this->settings['sweet_date'] ) || empty( $this->settings['sweet_date'] ) ) {
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
            __( 'Sweet Date', 'GMW-MD' ),
            array(
                array(
                    'name'        => 'radius',
                    'std'         => '10,25,50,100,200',
                    'placeholder' => __( 'Enter radius values', 'GMW-MD' ),
                    'label'       => __( 'Radius', 'GMW-MD' ),
                    'desc'        => __( 'Enter the radius values. Single value to be default value or multiple values comma separated for a drop-down select box.', 'GMW-MD' ),
                    'type'        => 'text'
                ),
                array(
                    'name'       => 'units',
                    'std'        => '3959',
                    'label'      => __( 'Units', 'GMW-MD' ),
                    'desc'       => __( 'Choose Between Miles and Kilometers.', 'GMW-MD' ),
                    'type'       => 'radio',
                    'attributes' => array(),
                    'options'    => array(
                        '3959' => __( 'Miles', 'GMW-MD' ),
                        '6371' => __( 'Kilometers', 'GMW-MD' ),
                    )
                ),
                array(
                    'name'       => 'map_use',
                    'std'        => '',
                    'label'      => __( 'Show Map', 'GMW-MD' ),
                    'desc'       => __( 'Show/hide map above list of members results', 'GMW-MD' ),
                    'type'       => 'checkbox',
                    'cb_label'   => 'Yes',
                    'attributes' => array(),
                ),
                array(
                    'name'        => 'map_width',
                    'std'         => '100%',
                    'placeholder' => __( 'Map width in pixels or percentage', 'GMW-MD' ),
                    'label'       => __( 'Map Width', 'GMW-MD' ),
                    'desc'        => __( 'Maps width in pixels or percentage', 'GMW-MD' ),
                    'type'        => 'text'
                ),
                array(
                    'name'        => 'map_height',
                    'std'         => '300px',
                    'placeholder' => __( 'Map height in pixels or percentage', 'GMW-MD' ),
                    'label'       => __( 'Map Height', 'GMW-MD' ),
                    'desc'        => __( 'Maps height in pixels or percentage', 'GMW-MD' ),
                    'type'        => 'text'
                ),
                array(
                    'name'       => 'map_type',
                    'std'        => 'ROADMAP',
                    'label'      => __( 'Map Type', 'GMW-MD' ),
                    'desc'       => __( 'Map type', 'GMW-MD' ),
                    'type'       => 'select',
                    'attributes' => array(),
                    'options'    => array(
                        'ROADMAP'   => __( 'ROADMAP', 'GMW-MD' ),
                        'SATELLITE' => __( 'SATELLITE', 'GMW-MD' ),
                        'HYBRID'    => __( 'HYBRID', 'GMW-MD' ),
                        'TERRAIN'   => __( 'TERRAIN', 'GMW-MD' ),
                    )
                ),
                array(
                    'name'       => 'distance',
                    'std'        => '',
                    'label'      => __( 'Display Distance?', 'GMW-MD' ),
                    'cb_label'   => __( 'Yes', 'GMW-MD' ),
                    'desc'       => __( 'Show/hide distance in results.', 'GMW-MD' ),
                    'type'       => 'checkbox',
                    'attributes' => array()
                ),
                array(
                    'name'       => 'address',
                    'std'        => '',
                    'label'      => __( 'Display Address?', 'GMW-MD' ),
                    'cb_label'   => __( 'Yes', 'GMW-MD' ),
                    'desc'       => __( 'Show/hide member\'s address in results.', 'GMW-MD' ),
                    'type'       => 'checkbox',
                    'attributes' => array()
                ),
                array(
                    'name'       => 'directions',
                    'std'        => '',
                    'label'      => __( 'Display "Get directions" Link?', 'GMW-MD' ),
                    'cb_label'   => __( 'Yes', 'GMW-MD' ),
                    'desc'       => __( 'Show/hide get directions link in each results. The link will open a new page with Google map showing the directions from the address entered by the user to the member\'s location.', 'GMW-MD' ),
                    'type'       => 'checkbox',
                    'attributes' => array()
                ),
            ),
        );

        return $settings;

    }

}
new GMW_SD_Admin;
