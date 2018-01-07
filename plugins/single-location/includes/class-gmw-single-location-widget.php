<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
        
/**
 * Display GEO my WP search form
 *
 * @since 1.0.0
 */
class GMW_Single_Location_Widget extends GMW_Widget {

    /**
     * Widget ID
     * 
     * @var string
     */
    public $widget_id = 'gmw_single_location_widget';

    /**
     * Widget class
     * 
     * @var string
     */
    public $widget_class = 'geo-my-wp widget-single-location';

    /**
     * Help page link
     * 
     * @var string
     */
    public $help_link = 'http://docs.geomywp.com/single-location-widget/';

    /**
     * Constructor.
     */
    public function __construct() {
   
        $this->widget_description = __( 'Display the location of a single object ( Post, BP member... ).', 'GMW' );
        $this->widget_name        = __( 'GEO my WP Single Location', 'GMW' );

        $objects = [];

        $zoom_options = [];

        for ( $i = 1; $i < 21; $i++ ) {
            $zoom_options[$i] = $i;
        }
              
        $this->settings = array(
            'widget_title' => array(
                'type'        => 'text',
                'default'     => __( 'Single location', 'GMW' ),
                'label'       => __( 'Widget title', 'GMW' ),
                'description' => __( 'Enter a title for the widget or leave blank to omit.', 'GMW' )
            ),
            'element_id' => array(
                'type'        => 'number',
                'step'        => 1,
                'min'         => 1,
                'max'         => '',
                'default'     => rand( 100, 549 ),
                'label'       => __( 'Element ID', 'GMW' ),
                'description' => __( 'Use the element ID to assign a unique ID to this shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcode when custom modifications required.', 'GMW' )
            ),  
            'object'    => array( 
                'type'        => 'select',
                'default'     => '',
                'label'       => __( 'Object', 'GMW' ),
                'options'     => apply_filters( 'gmw_single_location_widget_objects', array() ),
                'description' => __( 'Select the object that you would like to display.', 'GMW' )
            ),
            'object_id' => array(
                'type'        => 'number',
                'step'        => 1,
                'min'         => '',
                'max'         => '',
                'default'     => 0,
                'label'       => __( 'Object ID', 'GMW' ),
                'description' => __( 'Item ID is the ID of the item that you want to display. For example, if you want to show the location of a specific post, the item ID will be the post ID of that post. Same goes for member ID and so on. Enter 0 if you want the item to be displayed based on the single item page or based on the item being displayed inside a loop.', 'GMW' )
            ),
            /*'show_in_single_post' => array(
                'type'        => 'checkbox',
                'default'     => 0,
                'label'       => __( 'Show in single post page', 'GMW' ),
                'description' => __( 'Show the post author location when viewing a single post page. The author must have his location added via GEO my WP ( using Members Locator add-on and the item ID above needs to be set to 0.', 'GMW' ),
            ),*/
            'elements' => array(
                'type'        => 'text',
                'default'     => 'title,distance,map,address,directions_form,directions_panel',
                'label'       => __( 'Elements', 'GMW' ),
                'description' => __( 'Enter the elements that you would like to display, and in the order that you want to display them, comma separated. The available elements are title, distance, map, address, directions_link, directions_form, directions_panel and location_meta ( when availabe for the object ).', 'GMW' )
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
            'location_meta' => array(
                'type'        => 'text',
                'default'     => '',
                'label'       => __( 'Location meta', 'GMW' ),
                'description' => __( 'Enter the location meta that you would like to display, comma separated. Ex. Phone,fax,email,website.', 'GMW' )
            ),
            'units'         => array( 
                'type'        => 'select',
                'default'     => '',
                'label'       => __( 'Distance units', 'GMW' ),
                'options'     => array( 
                    'metric'   => __( 'Miles', 'GMW' ),
                    'imperial' => __( 'Kilometers', 'GMW' )
                ),
            ),
            'map_width' => array(
                'type'        => 'text',
                'default'     => '100%',
                'label'       => __( 'Map width', 'GMW' ),
                'description' => __( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'GMW' )
            ),
            'map_height' => array(
                'type'        => 'text',
                'default'     => '250px',
                'label'       => __( 'Map height', 'GMW' ),
                'description' => __( 'Set the map height in pixels or percentage ( ex. 250px or 100% ).', 'GMW' )
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
                'default'     => '',
                'label'       => __( 'Zoom level', 'GMW' ),
                'options'     => $zoom_options
            ),
            'scrollwheel' => array(
                'type'        => 'checkbox',
                'default'     => 0,
                'label'       => __( 'Mouse wheel zoom', 'GMW' ),
                'description' => __( 'When enabled, the map will zoom in/out using the mouse scroll wheel.', 'GMW' ),
            ),
            'object_map_icon' => array(
                'type'        => 'text',
                'default'     => 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                'label'       => __( 'Object map icon', 'GMW' ),
                'description' => __( 'Link to the image that you want to use as the map icon that marks the object location on the map.', 'GMW' )
            ), 
            'object_info_window' => array(
                'type'        => 'text',
                'default'     => 'distance,title,address',
                'label'       => __( 'Object info window elements', 'GMW' ),
                'description' => __( 'Enter the elements that you would like to display in the map info window of the object, in the order that you want to display them, comma saperated. Leave blank to disable the info-window. The elements available are distance, title, address.', 'GMW' )
            ),
            'user_map_icon' => array(
                'type'        => 'text',
                'default'     => 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                'label'       => __( 'User location map icon', 'GMW' ),
                'description' => __( 'Link to the image that you would like to use as the map marker that marks the user\'s location on the map. Leave blank to disable.', 'GMW' )
            ),
            'user_info_window' => array(
                'type'        => 'text',
                'default'     => __( 'Your Location', 'GMW' ),
                'label'       => __( 'User location info window', 'GMW' ),
                'description' => __( 'Enter the content that you would like to display in the user location info-window. Leave the blank to disable.', 'GMW' )
            ),
            'no_location_message' => array(
                'type'        => 'text',
                'default'     => __( 'No location found', 'GMW' ),
                'label'       => __( 'No location message', 'GMW' ),
                'description' => __( 'The message that you would like to display if no location exists for the item being displayed. Leave blank for no message.', 'GMW' )
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

        echo gmw_single_location_shortcode( $instance );

        echo $after_widget; 
    }
}
register_widget( 'GMW_Single_Location_Widget' );