<?php
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
				'geo_my_wp_current_location_widget', // Base ID
				__( 'GMW Current Location', 'GMW' ), // Name
				array( 'description' => __( "Get and display the user's current position.", 'GMW' ), ) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget($args, $instance) {

		extract($args);

		$widget_title   	  		= $instance['widget_title']; // the widget title
		$element_id 	  			= $instance['element_id'];
		$elements			  		= ( !empty( $instance['elements'] ) ) ? $instance['elements'] : 'username,address,map';
		$location_form_trigger 		= $instance['location_form_trigger'];
		$address_field_placeholder 	= $instance['address_field_placeholder'];
		$address_fields       		= ( !empty( $instance['address_fields'] ) ) ? implode(',', $instance['address_fields']) : '';
		$address_label				= $instance['address_label'];
		$user_greeting		  		= $instance['user_greeting'];
		$guest_greeting		  		= $instance['guest_greeting'];
		$map_height			  		= $instance['map_height'];
		$map_width  		  		= $instance['map_width'];
		$map_marker			  		= ( !empty( $instance['map_marker'] ) ) ? $instance['map_marker'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';		
		$map_type			  		= $instance['map_type'];
		$zoom_level 		  		= $instance['zoom_level'];
		$scrollwheel		 		= $instance['scrollwheel'];
	
		echo $before_widget;

		if ( !empty( $widget_title ) ) {
			echo $before_title . $widget_title . $after_title;
		}
		
		echo do_shortcode("[gmw_current_location
			element_id=\"{$element_id}\"
			elements=\"{$elements}\"
			location_form_trigger=\"{$location_form_trigger}\"
			address_field_placeholder=\"{$address_field_placeholder}\"
			address_fields=\"{$address_fields}\"
			address_label=\"{$address_label}\"
			user_greeting=\"{$user_greeting}\"
			guest_greeting=\"{$guest_greeting}\"
			map_height=\"{$map_height}\"
			map_width=\"{$map_width}\"
			map_marker=\"{$map_marker}\"
			map_type=\"{$map_type}\"
			zoom_level=\"{$zoom_level}\"
			scrollwheel_map_zoom=\"{$scrollwheel}\"			
			]");

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
	 
		$instance['widget_title']         		= strip_tags($new_instance['widget_title']);
		$instance['element_id']         		= strip_tags($new_instance['element_id']);
		$instance['elements']         	  		= strip_tags($new_instance['elements']);
		$instance['location_form_trigger'] 		= $new_instance['location_form_trigger'];
		$instance['address_field_placeholder'] 	= $new_instance['address_field_placeholder'];
		$instance['address_fields']       		= $new_instance['address_fields'];
		$instance['address_label']    			= strip_tags($new_instance['address_label']);
		$instance['user_greeting']         		= $new_instance['user_greeting'];
		$instance['guest_greeting']        		= $new_instance['guest_greeting'];
		$instance['map_width']         	  		= $new_instance['map_width'];
		$instance['map_height']           		= $new_instance['map_height'];
		$instance['map_marker']           		= $new_instance['map_marker'];
		$instance['map_type']         	  		= $new_instance['map_type'];
		$instance['zoom_level']           		= $new_instance['zoom_level'];
		$instance['scrollwheel']          		= $new_instance['scrollwheel'];

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

		$defaults = array(
				'widget_title'   			=> 'Current Location',
				'element_id'				=> rand( 550, 1000 ),
				'elements'					=> 'username,address,map,location_form',
				'location_form_trigger' 	=> 'Get your current location',
				'address_field_placeholder'	=> 'Enter address',
				'address_label' 			=> 'Your Location',
				'address_fields'     		=> 'city,country',
				'address_as_text'      		=> 0,
				'user_greeting' 			=> 'Hello',
				'guest_greeting' 			=> 'Hello, guest!',				
				'map_height'     			=> '200px',
				'map_width'      			=> '200px',
				'map_marker'				=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
				'map_type'       			=> 'ROADMAP',
				'zoom_level'     			=> 8,
				'scrollwheel'    			=> 1,				
	);

	$instance = wp_parse_args( (array ) $instance, $defaults );

	if ( !empty( $instance['address_fields'] ) && !is_array( $instance['address_fields'] ) ) {
		$instance['address_fields'] = explode( ',', $instance['address_fields'] );
	}
	?>
	<div class="gmw-widget-wrapper">
	
		<p class="gmw-message-box">
	    	<span class="dashicons dashicons-editor-help"></span>
	    	<a href="http://docs.geomywp.com/current-location-widget/" target="_blank" title="Current Location widget docs">Click here</a> for the full, detailed user guide for this widget.
	    </p>

	    <p>
	        <label><?php echo esc_attr( __( "Widget title", 'GMW' ) ); ?>:</label>     
	        <input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if ( isset( $instance['widget_title'] ) ) echo $instance['widget_title']; ?>" class="widefat" />
	    </p>

	    <p>
	        <label for="<?php echo $this->get_field_name('element_id'); ?>">
	        	<?php echo esc_attr( __( 'Element id', 'GMW' ) ); ?>:
	        </label>
	        <input id="<?php echo $this->get_field_name('element_id'); ?>" type="text" name="<?php echo $this->get_field_name('element_id'); ?>" value="<?php if (isset($instance['element_id'])) echo $instance['element_id']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Use the element ID to assign a unique ID to this widget. The unique ID can be useful for styling purposes as well when using the hooks provided by the widget when custom modifications are required.", 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	        <label><?php echo esc_attr( __( 'Elements to display', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('elements'); ?>" value="<?php if (isset($instance['elements'])) echo $instance['elements']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Enter the elements that you would like to display, in the order that you would like to display them, comma separated. The available elements are username,address,location_form and map.", 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	        <label><?php echo esc_attr( __( 'Location form trigger', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('location_form_trigger'); ?>" value="<?php if ( isset($instance['location_form_trigger'] ) ) echo $instance['location_form_trigger']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Enter in the input box the text that you would like to use as the form trigger. Otherwise, leave it blank if you wish to hide it.", 'GMW' ); ?>
	        </em>           
	    </p>  

	    <p>
	        <label><?php echo esc_attr( __( 'Address field placeholder', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('address_field_placeholder'); ?>" value="<?php if (isset($instance['address_field_placeholder'])) echo $instance['address_field_placeholder']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Enter in the input box the text that you would like to use as the address placeholder.", 'GMW' ); ?>
	        </em>
	    </p>  
	    
	    <p>
	        <label><?php echo esc_attr(__( 'Address Fields' ) ); ?>:</label><br />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "The address fields to be displayed.", 'GMW' ); ?>
	        </em>
	        <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('street', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Street', 'GMW'); ?></label><br />
	        <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('city', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('City', 'GMW'); ?></label><br />
	        <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('state', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('State', 'GMW'); ?></label><br />
	        <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('zipcode', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Zipcode', 'GMW'); ?></label><br />
	        <input type="checkbox" value="country" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('country', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Country', 'GMW'); ?></label><br />
	        <input type="checkbox" value="address" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('address', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Full address', 'GMW'); ?></label><br />
	    </p>

	    <p>
	        <label><?php echo esc_attr( __( 'Address label', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('address_label'); ?>" value="<?php if (isset($instance['address_label'])) echo $instance['address_label']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Enter in the input box the text that you would like to show before the address. Otherwise, leave the input box blank to disable the label.", 'GMW' ); ?>
	        </em>
	    </p>

	    <p>
	        <label><?php echo esc_attr( __( 'User greeting message ( logged in users )', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('user_greeting'); ?>" value="<?php if ( isset($instance['user_greeting'] ) ) echo $instance['user_greeting']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Type in the input box any text that you would like to use as a greeting that will show before the username. For example, type \"Hello \" in order to show \"Hello {username}\".", 'GMW' ); ?>
	        </em>           
	    </p> 

	    <p>
	        <label><?php echo esc_attr( __( 'Guest greeting message ( logged out users )', 'GMW' ) ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name('guest_greeting'); ?>" value="<?php if ( isset( $instance['guest_greeting'])) echo $instance['guest_greeting']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Enter in the input box any text that you would like to use as a greeting when a logged-out user is visiting your site. For example, \"Hello Guest!\".", 'GMW' ); ?>
	        </em>
	    </p>  

	    <p>
	        <label><?php echo esc_attr( __( 'Map Height', 'GMW') ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_height' ); ?>" value="<?php if ( isset( $instance['map_height'] ) ) echo $instance['map_height']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Set the map height in pixels or percentage ( ex. 250px or 100% ).", 'GMW' ); ?>
	        </em>
	    </p>
	    <p>
	        <label><?php echo esc_attr( __( 'Map Width', 'GMW') ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_width' ); ?>" value="<?php if ( isset( $instance['map_width'] ) ) echo $instance['map_width']; ?>" class="widefat" />
	        <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Set the map width in pixels or percentage ( ex. 250px or 100% ).", 'GMW' ); ?>
	        </em>
	    </p> 
	    <p>
	        <label><?php echo esc_attr( __( 'Map Marker', 'GMW') ); ?>:</label>
	        <input type="text" name="<?php echo $this->get_field_name( 'map_marker' ); ?>" value="<?php echo ( !empty( $instance['map_marker'] ) ) ? $instance['map_marker'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'; ?>" class="widefat" />
	    	<em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Link to the image that will be used as the map marker.", 'GMW' ); ?>
	        </em>  
	    </p>       
	    <p>
	        <label><?php echo _e( 'Map Type', 'GMW'); ?>:</label>
	        <select name="<?php echo $this->get_field_name( 'map_type' ); ?>">
	    		<option value="ROADMAP"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "ROADMAP" ) echo 'selected="selected"'; ?>>ROADMAP</options>
	    		<option value="SATELLITE" <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "SATELLITE" ) echo 'selected="selected"'; ?>>SATELLITE</options>
	    		<option value="HYBRID"    <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "HYBRID" ) echo 'selected="selected"'; ?>>HYBRID</options>
	    		<option value="TERRAIN"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "TERRAIN" ) echo 'selected="selected"'; ?>>TERRAIN</options>
	        </select>
	    </p>       
	     <p>
	        <label><?php echo _e( 'Zoom Level', 'GMW' ); ?>:</label>
	        <select name="<?php echo $this->get_field_name('zoom_level'); ?>">
	    	<?php for ($i = 1; $i < 18; $i++): ?>
	        	<option value="<?php echo $i; ?> " <?php if (isset($instance['zoom_level']) && $instance['zoom_level'] == $i) echo "selected"; ?>><?php echo $i; ?></option>
	    	<?php endfor; ?> 
	        </select>
	    </p>
	    <p>
	    	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('scrollwheel'); ?>" <?php if ( isset( $instance["scrollwheel"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
	        <label><?php echo esc_attr( __( 'Scroll-Wheel map zoom enabled', 'GMW' ) ); ?></label>       
	    	<em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
	        	<?php _e( "Check this checkbox to enable the map zoon in/out using the mouse-wheel.", 'GMW' ); ?>
	        </em> 
	    </p>
	</div>
   	<?php
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Current_Location_Widget" );' ) );