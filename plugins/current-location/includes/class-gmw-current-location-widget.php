<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
        
/**
 * Current Locations Widget
 *
 * @since 1.0.0
 */
class GMW_Current_Location_Widget extends GMW_Widget {

    /**
     * Widget ID
     * 
     * @var string
     */
    public $widget_id = 'geo_my_wp_current_location_widget';

    /**
     * Widget class
     * 
     * @var string
     */
    public $widget_class = 'geo-my-wp widget-current-location';

    /**
     * Help page link
     * 
     * @var string
     */
    public $help_link = 'http://docs.geomywp.com/current-location-widget/';

    /**
     * Constructor.
     */
    public function __construct() {
       
        $this->widget_description = __( 'Retrieve and display the user\'s current position', 'GMW' );
        $this->widget_name        = __( 'GEO my WP Current Location', 'GMW' );

        $zoom_options = [];

        for ( $i = 1; $i < 21; $i++ ) {
            $zoom_options[$i] = $i;
        }
       
        $this->settings = array(
            'widget_title' => array(
                'type'        => 'text',
                'default'     => __( 'Current Location', 'GMW' ),
                'label'       => __( 'Widget title', 'GMW' ),
                'description' => __( 'Enter a title for the widget or leave blank to omit.', 'GMW' )
            ),
            'element_id' => array(
                'type'        => 'number',
                'step'        => 1,
                'min'         => 1,
                'max'         => '',
                'default'     => rand( 550, 1000 ),
                'label'       => __( 'Element ID', 'GMW' ),
                'description' => __( 'Use the element ID to assign a unique ID to this shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcode when custom modifications required.', 'GMW' )
            ),  
            'elements' => array(
                'type'        => 'text',
                'default'     => 'username,address,map,location_form',
                'label'       => __( 'Elements', 'GMW' ),
                'description' => __( 'Enter the elements that you would like to display, and in the order that you want to display them, comma separated. The available elements are username,address,location_form and map.', 'GMW' )
            ),
            'location_form_trigger' => array(
                'type'        => 'text',
                'default'     => 'Get your current location',
                'label'       => __( 'Location form trigger', 'GMW' ),
                'description' => __( 'Enter in the input box the text that you would like to use as the form trigger. Leave it blank to omit.', 'GMW' )
            ),
            'address_field_placeholder' => array(
                'type'        => 'text',
                'default'     => 'Enter address',
                'label'       => __( 'Address field placeholder', 'GMW' ),
                'description' => __( 'Enter in the input box the text that you would like to use as the address field placeholder.', 'GMW' )
            ),
            'address_fields' => array(
                'type'        => 'multicheckbox',
                'default'     => array( 'address' ),
                'label'       => __( 'Address fields', 'GMW' ),
                'options'     => array( 
                    'address'      => __( 'Address', 'GMW' ),
                    'street'       => __( 'Street', 'GMW' ),
                    'city'         => __( 'City', 'GMW' ),
                    'region_code'  => __( 'State', 'GMW' ),
                    'postcode'     => __( 'Postcode', 'GMW' ),
                    'country_code' => __( 'Country', 'GMW' ),
                ),
                'description' => __( 'Choose the address fields that you would like to display.', 'GMW' ),
            ), 
            'address_label' => array(
                'type'        => 'text',
                'default'     => 'Your location',
                'label'       => __( 'Location meta', 'GMW' ),
                'description' => __( 'Enter a label for the address field. Leave it blank to omit.', 'GMW' )
            ),
             'address_autocomplete' => array(
                'type'        => 'checkbox',
                'default'     => 1,
                'label'       => __( 'Address autocomplete', 'GMW' ),
                'description' => __( 'Enable live suggested results by Google address autocompelte while typing an address.', 'GMW' ),
            ),
            'user_greeting' => array(
                'type'        => 'text',
                'default'     => 'Hello',
                'label'       => __( 'User greeting message ( logged in users )', 'GMW' ),
                'description' => __( 'Type in the input box any text that you would like to use as a greeting that will show before the username. For example, type "Hello " to show "Hello {username}.', 'GMW' )
            ),
            'guest_greeting' => array(
                'type'        => 'text',
                'default'     => 'Hello, guest!',
                'label'       => __( 'Guest greeting message', 'GMW' ),
                'description' => __( 'Enter in the input box any text that you would like to use as a greeting when a logged-out user is visiting your site. For example, "Hello Guest!', 'GMW' )
            ),
            'map_height' => array(
                'type'        => 'text',
                'default'     => '250px',
                'label'       => __( 'Map height', 'GMW' ),
                'description' => __( 'Set the map height in pixels or percentage ( ex. 250px or 100% ).', 'GMW' )
            ),
            'map_width' => array(
                'type'        => 'text',
                'default'     => '100%',
                'label'       => __( 'Map width', 'GMW' ),
                'description' => __( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'GMW' )
            ),
            'map_marker' => array(
                'type'        => 'text',
                'default'     => 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                'label'       => __( 'Map marker icon', 'GMW' ),
                'description' => __( 'Link to the image that you want to use as the map marker.', 'GMW' )
            ),
            'map_type'   => array( 
                'type'        => 'select',
                'default'     => '',
                'label'       => __( 'Map type', 'GMW' ),
                'options'     => array( 
                    'ROADMAP'   => __( 'ROADMAP', 'GMW' ),
                    'SATELLITE' => __( 'SATELLITE', 'GMW' ),
                    'HYBRID'    => __( 'HYBRID', 'GMW' ),
                    'TERRAIN'   => __( 'TERRAIN', 'GMW' )
                ),
            ),
            'zoom_level'   => array( 
                'type'        => 'select',
                'default'     => 13,
                'label'       => __( 'Zoom level', 'GMW' ),
                'options'     => $zoom_options
            ),
            'scrollwheel_zoom' => array(
                'type'        => 'checkbox',
                'default'     => 0,
                'label'       => __( 'Mouse wheel zoom', 'GMW' ),
                'description' => __( 'When enabled, the map will zoom in/out using the mouse scroll wheel.', 'GMW' ),
            ),
            'ajax_update' => array(
                'type'        => 'checkbox',
                'default'     => 1,
                'label'       => __( 'Update via ajax', 'GMW' ),
                'description' => __( 'Check this checkbox to update the location form via AJAX instead of page load.', 'GMW' ),
            ), 
            'loading_message' => array(
                'type'        => 'text',
                'default'     => 'Retrieving your current location...',
                'label'       => __( 'Loading message', 'GMW' ),
                'description' => __( 'Enter a message to display while retrieving the user\'s location. Leave blank to omit.', 'GMW' )
            )
        );

        $this->register();
    }

    /**
     * Echoes the widget content.
     *
     * @see WP_Widget
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {

    	extract( $args );

		echo $before_widget;

        $instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : 'city,country';
		$instance['widget_title']   = ! empty( $instance['widget_title'] )   ? htmlentities( $args['before_title'].$instance['widget_title'].$args['after_title'], ENT_QUOTES ) : 0;

		$current_location = new GMW_Current_Location( $instance );

		echo $current_location->output();
	
		echo $after_widget; 
    }
}
register_widget( 'GMW_Current_Location_Widget' );