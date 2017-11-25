<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMW_Current_Location_Widget' ) ) :

/**
 * GMW Current Location Widget
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Current_Location_Widget extends WP_Widget {

	/**
	 * __constructor
	 * Register widget with WordPress.
	 */
	function __construct() {
		
		parent::__construct(
			
			'geo_my_wp_current_location_widget',
			__( 'GMW Current Location', 'GMW' ),
			array( 'description' => __( 'Retrieve and display the user\'s current position.', 'GMW' ), )
		);

		$this->default_args = apply_filters( 'gmw_current_location_widget_default_args', array(
			'widget_title'   			=> 'Current Location',
			'element_id'				=> rand( 550, 1000 ),
			'elements'					=> 'username,address,map,location_form',
			'location_form_trigger' 	=> 'Get your current location',
			'address_field_placeholder'	=> 'Enter address',
			'address_fields'     		=> 'city,country',
			'address_label' 			=> 'Your Location',
			'address_autocomplete'		=> 1,
			'user_greeting' 			=> 'Hello',
			'guest_greeting' 			=> 'Hello, guest!',				
			'map_height'     			=> '200px',
			'map_width'      			=> '200px',
			'map_marker'				=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
			'map_type'       			=> 'ROADMAP',
			'zoom_level'     			=> 8,
			'scrollwheel_zoom'    		=> 1,	
			'ajax_update'				=> 1,
			'loading_message'			=> 'Retrieving your current location...'			
		) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		
		extract( $args );

		$instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : 'city,country';

		echo $before_widget;
	
		$output_string = '';

		//build the shortcode 
		foreach ( $this->default_args as $key => $value ) {

			if ( $key == 'widget_title' ) {
				
				if ( ! empty( $widget_title ) ) {
					echo $before_title . $widget_title . $after_title;
				}

			} else {
				
				$value = ! isset( $instance[$key] ) ? $instance[$key] : $value;
				
				$output_string .= $key.'="'.$value.'" ';
			}
		}

		//display the shortcode
		echo do_shortcode( '[gmw_current_location '.$output_string.' ]' );

		echo $after_widget;
	}

	/**
	* Sanitize widget form values as they are saved.
	*
	* @see WP_Widget::update()
	*
	* @param array $new_instance Values just sent to be saved.
	* @param array $old_instance Previously saved values from database.
	*
	* @return array Updated safe values to be saved.
	*/
	function update( $new_instance, $old_instance ) {
	 
		$instance['widget_title']         		= strip_tags( $new_instance['widget_title'] );
		$instance['element_id']         		= $new_instance['element_id'];
		$instance['elements']         	  		= $new_instance['elements'];
		$instance['location_form_trigger'] 		= strip_tags( $new_instance['location_form_trigger'] );
		$instance['address_field_placeholder'] 	= strip_tags( $new_instance['address_field_placeholder'] );
		$instance['address_fields']       		= $new_instance['address_fields'];
		$instance['address_label']    			= strip_tags( $new_instance['address_label'] );
		$instance['address_autocomplete']    	= $new_instance['address_autocomplete'];
		$instance['user_greeting']         		= strip_tags( $new_instance['user_greeting'] );
		$instance['guest_greeting']        		= strip_tags( $new_instance['guest_greeting'] );
		$instance['map_width']         	  		= $new_instance['map_width'];
		$instance['map_height']           		= $new_instance['map_height'];
		$instance['map_marker']           		= $new_instance['map_marker'];
		$instance['map_type']         	  		= $new_instance['map_type'];
		$instance['zoom_level']           		= $new_instance['zoom_level'];
		$instance['scrollwheel_zoom']          	= $new_instance['scrollwheel_zoom'];
		$instance['ajax_update']          		= $new_instance['ajax_update'];
		$instance['loading_message']          	= strip_tags( $new_instance['loading_message'] );

		return $instance;
	}

	/**
	* Back-end widget form.
	*
	* @see WP_Widget::form()
	*
	* @param array $instance Previously saved values from database.
	*/
	function form( $instance ) {

	$instance = wp_parse_args( (array ) $instance, $this->default_args );

	// explode address array
	if ( ! empty( $instance['address_fields'] ) && ! is_array( $instance['address_fields'] ) ) {
		$instance['address_fields'] = explode( ',', $instance['address_fields'] );
	}
	?>
	<div class="gmw-widget-wrapper current-location-widget">
	
		<p class="gmw-message-box">
			<i class="gmw-icon-lifebuoy"></i>

			<?php echo sprintf( __( '<a href="%s">Click here</a> for the full, detailed user guide for this widget.', 'GMW' ), 'http://docs.geomywp.com/current-location-widget/" target="_blank" title="Current Location widget docs"' ); ?>
	    </p>

	    <p>
	        <label><?php _e( 'Widget title', 'GMW' ); ?>:</label>     
	        <input type="text" name="<?php echo $this->get_field_name( 'widget_title' ); ?>" value="<?php if ( isset( $instance['widget_title'] ) ) echo esc_html( stripslashes( $instance['widget_title'] ) ); ?>" class="widefat" />
	    </p>

	    <p>
	        <label for="<?php echo $this->get_field_name( 'element_id' ); ?>">
	        	<?php _e( 'Element id', 'GMW' ); ?>:
	        </label>

	        <input id="<?php echo $this->get_field_name( 'element_id' ); ?>" type="text" name="<?php echo $this->get_field_name( 'element_id' ); ?>" value="<?php echo ( ! empty( $instance['element_id'] ) ) ? esc_attr( $instance['element_id'] ) : ''; ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Use the element ID to assign a unique ID to this widget. The unique ID can be useful for styling purposes as well when using the hooks provided by the widget when custom modifications are required.', 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	        <label><?php _e( 'Elements', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'elements' ); ?>" value="<?php echo ( ! empty( $instance['elements'] ) ) ? esc_html( stripslashes( $instance['elements'] ) ) : 'username,address,map,location_form'; ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Enter the elements that you would like to display, in the order that you would like to display them, comma separated. The available elements are username,address,location_form and map.', 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	        <label><?php _e( 'Location form trigger', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'location_form_trigger' ); ?>" value="<?php echo ( ! empty( $instance['location_form_trigger'] ) ) ? esc_html( stripslashes( $instance['location_form_trigger'] ) ) : ''; ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Enter in the input box the text that you would like to use as the form trigger. Otherwise, leave it blank if you wish to hide it.', 'GMW' ); ?>
	        </em>           
	    </p>  

	    <p>
	        <label><?php _e( 'Address field placeholder', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'address_field_placeholder' ); ?>" value="<?php if ( ! empty( $instance['address_field_placeholder'] ) ) echo esc_html( stripslashes( $instance['address_field_placeholder'] ) ); ?>" class="widefat" />
	        <em>
	        	<?php _e( "Enter in the input box the text that you would like to use as the address placeholder.", 'GMW' ); ?>
	        </em>
	    </p>  
	    
	    <p>
	        <label><?php _e( 'Loading message', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'loading_message' ); ?>" value="<?php echo ( ! empty( $instance['loading_message'] ) ) ? esc_html( stripslashes( $instance['loading_message'] ) ) : ''; ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Enter a message to display while retrieving the user\'s location. Leave blank for no message.', 'GMW' ); ?>
	        </em>           
	    </p> 

	   	<p>
	        <label><?php _e( 'Address Fields', 'GMW' ); ?>:</label><br />
	        <em>
	        	<?php _e( 'Select the address fields that you would like to display as the user\'s location', 'GMW' ); ?>
	        </em>
	        <?php $instance['address_fields'] = ! empty( $instance['address_fields'] ) ? $instance['address_fields'] : array(); ?>

	        <input type="checkbox" value="address" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'address', $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'Full address', 'GMW' ); ?></label><br />
	        <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'street',  $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'Street', 'GMW' ); ?></label><br />
	        <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'city',    $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'City', 'GMW' ); ?></label><br />
	        <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'state',   $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'State', 'GMW' ); ?></label><br />
	        <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'zipcode', $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'Zipcode', 'GMW' ); ?></label><br />
	        <input type="checkbox" value="country" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if ( in_array( 'country', $instance['address_fields'] ) ) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e( 'Country', 'GMW' ); ?></label><br />
	    </p>

	    <p>
	        <label><?php _e( 'Address label', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'address_label' ); ?>" value="<?php if ( ! empty( $instance['address_label'] ) ) echo esc_html( stripslashes( $instance['address_label'] ) ); ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Enter in the input box the text that you would like to show before the address. Otherwise, leave the input box blank to hide the label.', 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	    	<input type="checkbox" value="1" name="<?php echo $this->get_field_name( 'address_autocomplete' ); ?>" <?php if ( isset( $instance['address_autocomplete'] ) ) echo 'checked="checked"'; ?> class="checkbox" />
	        <label><?php _e( 'Enable Google address autocomplete.', 'GMW' ); ?></label>       
	    	<em>
	        	<?php _e( 'Check this checkbox to enable live suggested results by Google address autocompelte while typing an address.', 'GMW' ); ?>
	        </em> 
	    </p>

	    <p>
	        <label><?php _e( 'User greeting message ( logged in users )', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'user_greeting' ); ?>" value="<?php if ( ! empty( $instance['user_greeting'] ) ) echo esc_html( stripslashes( $instance['user_greeting'] ) ); ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Type in the input box any text that you would like to use as a greeting that will show before the username. For example, type "Hello " if you\'d like to show "Hello {username}".', 'GMW' ); ?>
	        </em>           
	    </p> 

	    <p>
	        <label><?php _e( 'Guest greeting message ( logged out users )', 'GMW' ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'guest_greeting' ); ?>" value="<?php if ( ! empty( $instance['guest_greeting'] ) ) echo esc_html( stripslashes( $instance['guest_greeting'] ) ); ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Enter in the input box any text that you would like to use as a greeting when a logged-out user is visiting your site. For example, "Hello Guest!".', 'GMW' ); ?>
	        </em>
	    </p>  

	    <p>
	        <label><?php _e( 'Map Height', 'GMW'); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_height' ); ?>" value="<?php echo ( ! empty( $instance['map_height'] ) ) ? esc_attr( $instance['map_height'] ) : '250px'; ?>" class="widefat" />
	        <em>
	        	<?php _e( "Set the map height in pixels or percentage ( ex. 250px or 100% ).", 'GMW' ); ?>
	        </em>
	    </p>
	    <p>
	        <label><?php _e( 'Map Width', 'GMW'); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_width' ); ?>" value="<?php echo ( ! empty( $instance['map_width'] ) ) ? esc_attr( $instance['map_width'] ) : '250px'; ?>" class="widefat" />
	        <em>
	        	<?php _e( 'Set the map width in pixels or percentage ( ex. 250px or 100% ).', 'GMW' ); ?>
	        </em>
	    </p> 
	    <p>
	        <label><?php _e( 'Map Marker', 'GMW'); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_marker' ); ?>" value="<?php echo ! empty( $instance['map_marker'] ) ? esc_url( $instance['map_marker'] ) : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'; ?>" class="widefat" />
	    	<em>
	        	<?php _e( 'Link to the image that will be used as the map marker.', 'GMW' ); ?>
	        </em>  
	    </p>       
	    <p>
	        <label><?php _e( 'Map Type', 'GMW'); ?>:</label>
	        <select name="<?php echo $this->get_field_name( 'map_type' ); ?>">
	    		<option value="ROADMAP"   selected="selected">ROADMAP</options>
	    		<option value="SATELLITE" <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "SATELLITE" ) echo 'selected="selected"'; ?>>SATELLITE</options>
	    		<option value="HYBRID"    <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "HYBRID" )    echo 'selected="selected"'; ?>>HYBRID</options>
	    		<option value="TERRAIN"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "TERRAIN" )   echo 'selected="selected"'; ?>>TERRAIN</options>
	        </select>
	    </p>       
	     <p>
	        <label><?php _e( 'Zoom Level', 'GMW' ); ?>:</label>
	        
	        <select name="<?php echo $this->get_field_name('zoom_level'); ?>">
	    	
	    	<?php for ($i = 1; $i < 18; $i++): ?>
	        
	        	<option value="<?php echo $i; ?> " <?php if ( isset( $instance['zoom_level'] ) && $instance['zoom_level'] == $i ) echo 'selected="selected"'; ?>><?php echo $i; ?></option>
	    	<?php endfor; ?> 
	        
	        </select>
	    </p>

	    <p>
	    	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('scrollwheel_zoom'); ?>" <?php if ( isset( $instance["scrollwheel_zoom"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
	        <label><?php _e( 'Scroll-Wheel map zoom enabled', 'GMW' ); ?></label>       
	    	<em>
	        	<?php _e( "Check this checkbox to enable the map zoon in/out using the mouse-wheel.", 'GMW' ); ?>
	        </em> 
	    </p>

	    <p>
	    	<input type="checkbox" value="1" name="<?php echo $this->get_field_name( 'ajax_update' ); ?>" <?php if ( isset( $instance['ajax_update'] ) ) echo 'checked="checked"'; ?> class="checkbox" />
	        <label><?php _e( 'Enable Ajax', 'GMW' ); ?></label>       
	    	<em>
	        	<?php _e( 'Check this checkbox to update the location form via AJAX instead of page load', 'GMW' ); ?>
	        </em> 
	    </p>
	</div>
   	<?php
	}
}
endif;
add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget" );' ) );