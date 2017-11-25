<?php
// Block direct requests
if ( !defined('ABSPATH') )
	die( '-1' );

/**
 * GMW_Single_Location_Widget clasc
 * 
 * @author Eyal Fitoussi
 * 
 * @since 2.6.1
 *
 */
class GMW_Single_Location_Widget extends WP_Widget {

	/**
	 * __constructor
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'GMW_Single_Location_Widget', // Base ID
			__( 'GMW Single Location', 'GMW' ), // Name
			array( 'description' => __( 'Display the location of a single item ( Post, BP member...)', 'GMW' ),) // Args
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
	function widget( $args, $instance ) {
			
		extract($args);

		$instance['address_fields'] = ! empty( $instance['address_fields'] ) ? implode( ',', $instance['address_fields'] ) : '';

		echo $before_widget;

		$widget_title = ! empty( $instance['widget_title'] ) ? htmlentities( $args['before_title'].$instance['widget_title'].$args['after_title'], ENT_QUOTES ) : 0;

		echo do_shortcode('[gmw_single_location
			element_id="'.$instance['element_id'].'"
			item_type="'.$instance['item_type'].'"
			elements="'.$instance['elements'].'"
			address_fields="'.$instance['address_fields'].'"
			item_id="'.$instance['item_id'].'"
			units="'.$instance['units'].'"
			map_height="'.$instance['map_height'].'"
			map_width="'.$instance['map_width'].'"
			map_type="'.$instance['map_type'].'"
			zoom_level="'.$instance['zoom_level'].'"
            scrollwheel_map_zoom="'.$instance['scrollwheel'].'"
			additional_info="'.$instance['additional_info'].'"
			item_info_window="'.$instance['item_info_window'].'"
			user_map_icon="'.$instance['user_map_icon'].'"
			user_info_window="'.$instance['user_info_window'].'"
			item_map_icon="'.$instance['item_map_icon'].'"
			no_location_message="'.$instance['no_location_message'].'"
			show_in_single_post="'.$instance['show_in_single_post'].'"
			is_widget="1"
			widget_title="'.$widget_title.'"
			]');

		echo $after_widget;
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
			'widget_title'			=> 'Single location',
			'element_id'	  		=> rand( 100, 549 ),
			'item_type'				=> 'post',
			'item_id'         		=> 0,
			'elements'				=> 'title,distance,map,address,live_directions,directions_panel,additional_info',
			'address_fields'		=> 'address',
			'additional_info' 		=> 'phone,fax,email,website',
			'units'		            => 'm',
			'map_height'      		=> '250px',
			'map_width'       		=> '250px',
			'map_type'        		=> 'ROADMAP',
			'zoom_level'      		=> 'auto',
            'scrollwheel'           => 1,
			'item_map_icon'			=> 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
			'item_info_window'	  	=> 'distance,title,address,additional_info',
			'user_map_icon'   	  	=> 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
			'user_info_window'	  	=> 'Your Location',
			'no_location_message'	=> 'No location found',
			'show_in_single_post'	=> 0			
		);

		$instance = wp_parse_args( ( array ) $instance, $defaults );

		if ( ! empty( $instance['address_fields'] ) && ! is_array( $instance['address_fields'] ) ) {
			$instance['address_fields'] = explode( ',', $instance['address_fields'] );
		}
		?>
        <div class="gmw-widget-wrapper">

            <p class="gmw-message-box">
                <i class="gmw-icon-lifebuoy"></i> 
                <a href="http://docs.geomywp.com/single-location-widget/" target="_blank" title="Single Location widget docs">Click here</a> for the full, detailed user guide for this widget.
            </p>

    		<p>
                <label for="<?php echo $this->get_field_name('widget_title'); ?>">
                	<?php _e( "Widget title", 'GMW' ); ?>:
                </label>     
                <input type="text" id="<?php echo $this->get_field_name('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if ( isset( $instance['widget_title'] ) ) echo esc_attr( $instance['widget_title'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Enter the title for this widget otherwise leave empty for no title.", 'GMW' ); ?>
                </em>
            </p>

            <p>
    	        <label for="<?php echo $this->get_field_name('item_type'); ?>">
    	        	<?php echo _e('Item type', 'GMW'); ?>:
    	        </label>
                
                <select id="<?php echo $this->get_field_name('item_type'); ?>" name="<?php echo $this->get_field_name('item_type'); ?>" onchange="if ( jQuery(this).val() == 'member' ) { jQuery('p.single-post-wrapper').show() } else { jQuery('p.single-post-wrapper').hide() }; if ( jQuery(this).val() == 'post' ) { jQuery('p.additional-info-wrapper').show() } else { jQuery('p.additional-info-wrapper').hide() };">
                	
                    <?php if ( gmw_is_addon_active( 'posts_locator' ) ) { ?>
                		<option value="post" <?php if ( isset( $instance['item_type'] ) && $instance['item_type'] == "post" ) echo 'selected="selected"' ; ?>><?php _e( 'Post', 'GMW' ); ?></option>
                	<?php } ?>
                	
                    <?php if ( gmw_is_addon_active( 'members_locator' ) ) { ?>
                		<option value="member" <?php if ( isset( $instance['item_type'] ) && $instance['item_type'] == "member" ) echo 'selected="selected"' ; ?>><?php _e( 'BuddyPress Member', 'GMW' ); ?></option>
                	<?php } ?>

                </select>
                <em>
                	<?php _e( "Chose the item that you would like to display.", 'GMW' ); ?>
                </em>
            </p>

            <p>
                <label for="<?php echo $this->get_field_name('element_id'); ?>">
                	<?php _e( 'Element id', 'GMW' ); ?>:
                </label>
                <input id="<?php echo $this->get_field_name('element_id'); ?>" type="text" name="<?php echo $this->get_field_name('element_id'); ?>" value="<?php if ( isset( $instance['element_id'] ) ) echo esc_attr( $instance['element_id'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Use the element ID to assign a unique ID to this shortcode. The unique ID can be useful for styling purposes as well when using the hooks provided by the shortcode when custom modifications required.", 'GMW' ); ?>
                </em>
            </p>

            <p>
                <label for="<?php echo $this->get_field_name('item_id'); ?>">
                	<?php _e( 'Item id', 'GMW' ); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name('item_id'); ?>" name="<?php echo $this->get_field_name('item_id'); ?>" value="<?php if ( isset( $instance['item_id'] ) ) echo esc_attr( $instance['item_id'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Item ID is the ID of the item that you want to display. For example, if you want to show the location of a particular post the item ID will be the post ID of the post that you want to display. Same goes for member ID. Leave it 0 if you want the item to be displayed based on the single item page or based on the item being displayed within a loop.", 'GMW' ); ?>
                </em>
            </p>

            <p class="single-post-wrapper" <?php if ( isset( $instance['item_type'] ) && $instance['item_type'] != "member" ) echo 'style="display:none"'; ?>>
                
                <label for="<?php echo $this->get_field_name('show_in_single_post'); ?>">
                	<input id="<?php echo $this->get_field_name('show_in_single_post'); ?>" type="checkbox" value="1"  name="<?php echo $this->get_field_name('show_in_single_post'); ?>" <?php if ( ! empty( $instance['show_in_single_post'] ) ) echo 'checked="checked"'; ?> class="checkbox" />
                	<?php _e( 'Show on single post page', 'GMW' ); ?>
                </label>
                <em >
                	<?php _e( "Show the post author location when viewing a single post page. The author must have his location added via Members Locator add-on and the item ID above needs to be set to 0.", 'GMW' ); ?>
                </em>   
            </p>

            <p>
                <label for="<?php echo $this->get_field_name('elements'); ?>">
                	<?php _e( 'Elements to display', 'GMW' ); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name('elements'); ?>" name="<?php echo $this->get_field_name('elements'); ?>" value="<?php if ( isset( $instance['elements'] ) ) echo esc_attr( $instance['elements'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Enter the elements that you would like to display, in the order that you want to display them, comma separated. The available elements are title, distance, map, address, directions_link, live_directions, directions_panel and additional_info ( additional_info is only available for post ).", 'GMW' ); ?>
                </em>
            </p>
            <p>
                <label><?php _e( 'Address fields', 'GMW' ); ?>:</label><br />
                <em>
                	<?php _e( "Choose the address fields that you would like to display.", 'GMW' ); ?>
                </em>
                <input type="checkbox" value="address" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('address', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Full address', 'GMW'); ?></label><br />
                <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('street',  $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Street', 'GMW'); ?></label><br />
                <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('city',    $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('City', 'GMW'); ?></label><br />
                <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('state',   $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('State', 'GMW'); ?></label><br />
                <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('zipcode', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Zipcode', 'GMW'); ?></label><br />
                <input type="checkbox" value="country" name="<?php echo $this->get_field_name('address_fields'); ?>[]" <?php if (isset($instance['address_fields']) && in_array('country', $instance['address_fields'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Country', 'GMW'); ?></label><br />
            </p>
            
            <p>
                <label for="<?php echo $this->get_field_name('additional_info'); ?>">
                	<?php _e( "Additional info fields", 'GMW' ); ?>:
                </label>     
                <input type="text" id="<?php echo $this->get_field_name('additional_info'); ?>" name="<?php echo $this->get_field_name( 'additional_info' ); ?>" value="<?php if ( isset( $instance['additional_info'] ) ) echo esc_attr( $instance['additional_info'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Enter the additional information that you would like to display, comma separated. Ex. Phone, fax, email, website.", 'GMW' ); ?>
                </em>
            </p>
    		
    		 <p>
                <label for="<?php echo $this->get_field_name('units'); ?>"><?php _e( 'Distance units', 'GMW' ); ?>:</label>
                <select id="<?php echo $this->get_field_name('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>">
                	<option value="m" selected="selected"><?php echo _e('Miles', 'GMW'); ?></option>
                	<option value="k" <?php if ( isset( $instance['units'] ) && $instance['units'] == "k" ) echo 'selected="selected"' ; ?>><?php echo _e('Kilometers', 'GMW'); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'map_height' ); ?>" >
                	<?php _e( 'Map Height', 'GMW'); ?>:
                </label>
                <input id="<?php echo $this->get_field_name( 'map_height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'map_height' ); ?>" value="<?php if ( isset( $instance['map_height'] ) ) echo esc_attr( $instance['map_height'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Set the map height in pixels or percentage ( ex. 250px or 100% ).", 'GMW' ); ?>
                </em>
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'map_width' ); ?>">
                	<?php _e( 'Map Width', 'GMW' ); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name( 'map_width' ); ?>" name="<?php echo $this->get_field_name( 'map_width' ); ?>" value="<?php if ( isset( $instance['map_width'] ) ) echo esc_attr( $instance['map_width'] ); ?>" class="widefat" />
                <em>
                	<?php _e( "Set the map width in pixels or percentage ( ex. 250px or 100% ).", 'GMW' ); ?>
                </em>
            </p>        
            <p>
                <label for="<?php echo $this->get_field_name( 'map_type' ); ?>">
                	<?php _e( 'Map Type', 'GMW'); ?>:
                </label>
                <select id="<?php echo $this->get_field_name( 'map_type' ); ?>" name="<?php echo $this->get_field_name( 'map_type' ); ?>">
            		<option value="ROADMAP"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "ROADMAP" ) echo 'selected="selected"'; ?>>ROADMAP</options>
            		<option value="SATELLITE" <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "SATELLITE" ) echo 'selected="selected"'; ?> >SATELLITE</options>
            		<option value="HYBRID"    <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "HYBRID" ) echo 'selected="selected"'; ?>>HYBRID</options>
            		<option value="TERRAIN"   <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "TERRAIN" ) echo 'selected="selected"'; ?>>TERRAIN</options>
                </select>
            </p>      
            <p>
                <label for="<?php echo $this->get_field_name('zoom_level'); ?>">
                	<?php _e( 'Zoom Level', 'GMW' ); ?>:
                </label>
                <select id="<?php echo $this->get_field_name('zoom_level'); ?>" name="<?php echo $this->get_field_name('zoom_level'); ?>">
                    <option value="auto" selected="selected"><?php _e( 'Auto', 'GMW' ); ?></option>
            	    <?php for ( $i = 1; $i < 18; $i++ ) : ?>
                	   <option value="<?php echo $i; ?> " <?php if ( isset( $instance['zoom_level'] ) && $instance['zoom_level'] == $i ) echo "selected"; ?>><?php echo $i; ?></option>
            	    <?php endfor; ?> 
                </select>
            </p> 
            <p>
                <input type="checkbox" value="1" name="<?php echo $this->get_field_name('scrollwheel'); ?>" <?php if ( isset( $instance["scrollwheel"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
                <label><?php _e( 'Scroll-Wheel map zoom enabled', 'GMW' ); ?></label>       
                <em>
                    <?php _e( "When enabled the map will zoom in/out using the mouse scrollwheel.", 'GMW' ); ?>
                </em> 
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'item_map_icon' ); ?>">
                	<?php _e( "Item's map icon", 'GMW'); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name( 'item_map_icon' ); ?>" name="<?php echo $this->get_field_name( 'item_map_icon' ); ?>" value="<?php echo ! empty( $instance['item_map_icon'] ) ? esc_attr( $instance['item_map_icon'] ) : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'; ?>" class="widefat" />
            	<em>
                	<?php _e( "Link to the image that you want to use as the map icon that represents the item's location on the map.", 'GMW' ); ?>
                </em>  
            </p>
            <p>
                <label for="<?php echo $this->get_field_name('item_info_window'); ?>">
                	<?php _e( "Item's Info-window elements", 'GMW' ); ?>:
                </label>     
                <input type="text" id="<?php echo $this->get_field_name('item_info_window'); ?>" name="<?php echo $this->get_field_name('item_info_window'); ?>" value="<?php if ( isset( $instance['item_info_window'] ) ) echo esc_attr( $instance['item_info_window'] ); ?>" class="widefat" />
            	<em>
                	<?php _e( "Enter the elements that you would like to display in the item's info-window in the order that you want to display them, comma saperated. Otherwise, leave the input box blank to disable the info-window. The elements available are distance, title, address, additional_info ( additional info is only available for post ).", 'GMW' ); ?>
                </em>
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'user_map_icon' ); ?>">
                	<?php _e( "User's location map icon", 'GMW'); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name( 'user_map_icon' ); ?>" name="<?php echo $this->get_field_name( 'user_map_icon' ); ?>" value="<?php echo ! empty( $instance['user_map_icon'] ) ? esc_attr( $instance['user_map_icon'] ) : ''; ?>" class="widefat" />
            	<em>
                	<?php _e( "Link to the image that you would like to use as the map marker that represents the user's location on the map. Leave the input box blank if you do not wish to display the marker of the user's location.", 'GMW' ); ?>
                </em>  
            </p>
            <p>
                <label for="<?php echo $this->get_field_name( 'user_info_window' ); ?>">
                	<?php _e( "User's info-window content", 'GMW'); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name( 'user_info_window' ); ?>" name="<?php echo $this->get_field_name( 'user_info_window' ); ?>" value="<?php echo ! empty( $instance['user_info_window'] ) ? esc_attr( $instance['user_info_window'] ) : ''; ?>" class="widefat" />
            	<em>
                	<?php _e( "Enter the content that you would like to display in the info-window of the user's map icon. Otherwise, leave the input box blank to disable the info-window.", 'GMW' ); ?>
                </em>  
            </p>  
            <p>
                <label for="<?php echo $this->get_field_name( 'no_location_message' ); ?>">
                	<?php _e( "No location message", 'GMW'); ?>:
                </label>
                <input type="text" id="<?php echo $this->get_field_name( 'no_location_message' ); ?>" name="<?php echo $this->get_field_name( 'no_location_message' ); ?>" value="<?php echo ! empty( $instance['no_location_message'] ) ? esc_attr( $instance['no_location_message'] ) : ''; ?>" class="widefat" />
            	<em>
                	<?php _e( "The message that you would like to display if no location exists for the item being displayed. Leave blank for no message.", 'GMW' ); ?>
                </em>  
            </p>
        </div>
    <?php
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

    	$instance['widget_title']    	 = $new_instance['widget_title'];
    	$instance['element_id']    		 = $new_instance['element_id'];
    	$instance['elements']   		 = $new_instance['elements'];
    	$instance['item_type']    		 = $new_instance['item_type'];
        $instance['address_fields']      = $new_instance['address_fields'];
        $instance['item_id']      		 = $new_instance['item_id'];
        $instance['units']               = $new_instance['units'];
        $instance['map_height']       	 = $new_instance['map_height'];
        $instance['map_width']        	 = $new_instance['map_width'];
        $instance['map_type']    		 = $new_instance['map_type'];
        $instance['zoom_level']     	 = $new_instance['zoom_level'];
        $instance['scrollwheel']         = $new_instance['scrollwheel'];
        $instance['additional_info'] 	 = $new_instance['additional_info'];
        $instance['item_info_window'] 	 = $new_instance['item_info_window'];
        $instance['item_map_icon'] 	 	 = $new_instance['item_map_icon'];
        $instance['user_map_icon'] 		 = $new_instance['user_map_icon'];
        $instance['user_info_window'] 	 = $new_instance['user_info_window'];
        $instance['no_location_message'] = $new_instance['no_location_message'];
        $instance['show_in_single_post'] = $new_instance['show_in_single_post'];
        
        return $instance;
    }
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Single_Location_Widget" );' ) );