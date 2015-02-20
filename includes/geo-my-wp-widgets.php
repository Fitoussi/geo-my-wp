<?php

/**
 * GMW Widget - User's current location
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
				'gmw_current_location_widget', // Base ID
				__('GMW Current Location', 'GMW'), // Name
				array('description' => __('Get/display the user\'s current location', 'GMW'),) // Args
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

        $widget_title   	  = $instance['widget_title']; // the widget title
        $title_location 	  = $instance['title_location'];
        $display_by     	  = ( !empty( $display_by ) ) ? implode(',', $display_by) : 'city';
        $name_guest     	  = $instance['name_guest'];
        $title_location 	  = $instance['title_location'];
        $text_only			  = $instance['text_only'];		
        $map				  = $instance['map'];
        $map_height			  = $instance['map_height'];
        $map_width  		  = $instance['map_width'];
        $map_type			  = $instance['map_type'];
        $zoom_level 		  = $instance['zoom_level'];
        $scrollwheel		  = $instance['scrollwheel'];
        $map_marker			  = ( !empty( $instance['map_marker'] ) ) ? $instance['map_marker'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png';
        $user_message		  = $instance['user_message'];
        $guest_message		  = $instance['guest_message'];
        $get_location_message = $instance['get_location_message'];
        
        echo $before_widget;

        if ( isset( $widget_title ) && !empty( $widget_title ) )
            echo $before_title . $widget_title . $after_title;

        echo do_shortcode('[gmw_current_location 
        		display_by="'.$display_by.'" 
        		show_name="'.$name_guest.'" 
        		title_location="'.$title_location.'"
				text_only="'.$text_only.'" 
        		map="'.$map.'"
        		map_height="'.$map_height.'" 
        		map_width="'.$map_width.'"
        		map_type="'.$map_type.'"
        		zoom_level='.$zoom_level.'"
        		scrollwheel="'.$scrollwheel.'" 
        		map_marker="'.$map_marker.'"	
        		]');

        echo '<div class="clear"></div>';

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
        	
    	$instance['widget_title']         = strip_tags($new_instance['widget_title']);
    	$instance['title_location']       = strip_tags($new_instance['title_location']);
    	$instance['short_code_location']  = $new_instance['short_code_location'];
    	$instance['display_by']           = $new_instance['display_by'];
    	$instance['name_guest']           = $new_instance['name_guest'];
    	$instance['text_only']            = $new_instance['text_only'];
    	$instance['map']          		  = $new_instance['map'];
    	$instance['map_width']         	  = $new_instance['map_width'];
    	$instance['map_height']           = $new_instance['map_height'];
    	$instance['map_type']         	  = $new_instance['map_type'];
    	$instance['zoom_level']           = $new_instance['zoom_level'];
    	$instance['scrollwheel']          = $new_instance['scrollwheel'];
    	$instance['map_marker']           = $new_instance['map_marker'];
    	$instance['user_message']         = $new_instance['user_message'];
    	$instance['guest_message']        = $new_instance['guest_message'];
    	$instance['get_location_message'] = $new_instance['get_location_message'];
    	
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
        		'widget_title'   		=> __('Current Location', 'GMW'),
        		'title_location' 		=> __('Your Location', 'GMW'),
        		'display_by'     		=> 'city,country',
        		'name_guest'     		=> 1,
        		'user_message' 			=> 'Hello',
        		'guest_message' 		=> 'Hello, guest!',
        		'text_only'      		=> 0,
        		'map'     		 		=> 0,
        		'map_height'     		=> '200px',
        		'map_width'      		=> '200px',
        		'map_type'       		=> 'ROADMAP',
        		'zoom_level'     		=> 12,
        		'scrollwheel'    		=> 1,
        		'map_marker'			=> 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
    			'get_location_message' 	=> 'Get your current location'
        );
        
        $instance = wp_parse_args( (array ) $instance, $defaults );
        
        if ( !empty( $instance['display_by'] ) && !is_array( $instance['display_by'] ) ) {
        	$instance['display_by'] = explode( ',', $instance['display_by'] );
        }
        ?>

        <p>
            <label><?php echo esc_attr( __( "Widget's Title", 'GMW' ) ); ?>:</label>     
            <input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if ( isset( $instance['widget_title'] ) ) echo $instance['widget_title']; ?>" class="widefat" />
        </p>
        <p>
            <label><?php echo esc_attr( __( 'Location Title', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('title_location'); ?>" value="<?php if (isset($instance['title_location'])) echo $instance['title_location']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "The title that will be displayed before the location. For example Your location...", 'GMW' ); ?>
            </em>
        </p>
         <p>
            <label><?php echo esc_attr(__( 'Display Location:' ) ); ?></label><br />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "The address fields to be displayed.", 'GMW' ); ?>
            </em>
            <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('street', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Street', 'GMW'); ?></label><br />
            <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('city', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('City', 'GMW'); ?></label><br />
            <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('state', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('State', 'GMW'); ?></label><br />
            <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('zipcode', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Zipcode', 'GMW'); ?></label><br />
            <input type="checkbox" value="country" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('country', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Country', 'GMW'); ?></label><br />
        </p>
        <p>
        	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('name_guest'); ?>" <?php if ( isset( $instance["name_guest"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
            <label><?php echo esc_attr( __( 'Display guest/User Name', 'GMW' ) ); ?></label>
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Display greeting with \"guest\" or user name before the location.", 'GMW' ); ?>
            </em>
        </p>      
         <p>
            <label><?php echo esc_attr( __( 'Greeting message ( logged in users )', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('user_message'); ?>" value="<?php if ( isset($instance['user_message'] ) ) echo $instance['user_message']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Text that will be displayed before the user name. For example \"Hello username\" ( requires the Display guest/User Name chekbox to be checked ).", 'GMW' ); ?>
            </em>           
        </p>    
        <p>
            <label><?php echo esc_attr( __( 'Greeting message ( guests )', 'GMW' ) ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('guest_message'); ?>" value="<?php if ( isset( $instance['guest_message'])) echo $instance['guest_message']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Text that will be displayed when user is not looged in. for example \"Hello Guest\" ( requires the Display guest/User Name chekbox to be checked ).", 'GMW' ); ?>
            </em>
        </p>          
        <p>
        	<input type="checkbox" value="1" name="<?php echo $this->get_field_name('map'); ?>" <?php if ( isset( $instance["map"] ) ) echo 'checked="checked"'; ?> class="checkbox" />
            <label><?php echo esc_attr( __( 'Display Google Map', 'GMW' ) ); ?></label>
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Display Google map showing the user's location.", 'GMW' ); ?>
            </em>
        </p>       
        <p>
            <label><?php echo esc_attr( __( 'Map Height', 'GMW') ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name( 'map_height' ); ?>" value="<?php if ( isset( $instance['map_height'] ) ) echo $instance['map_height']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Set the map height in pixels or percentage ( ex. 250px ).", 'GMW' ); ?>
            </em>
        </p>
        <p>
            <label><?php echo esc_attr( __( 'Map Width', 'GMW') ); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name( 'map_width' ); ?>" value="<?php if ( isset( $instance['map_width'] ) ) echo $instance['map_width']; ?>" class="widefat" />
            <em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "Set the map width in pixels or percentage ( ex. 100% ).", 'GMW' ); ?>
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
        		<option value="SATELLITE" <?php if ( isset( $instance['map_type'] ) && $instance['map_type'] == "SATELLITE" ) echo 'selected="selected"'; ?> >SATELLITE</options>
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
            <label><?php echo esc_attr( __( 'ScrollWheel Enabled', 'GMW' ) ); ?></label>       
        	<em style="font-size:12px;color:#777;display:block;margin:5px 0px;">
            	<?php _e( "When enabled the map will zoom in/out using the mouse scrollwheel.", 'GMW' ); ?>
            </em> 
        </p>
        <?php
    }
}

class GMW_Search_Form_Widget extends WP_Widget {

    /**
     * __constructor
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
                'gmw_search_form_widget', // Base ID
                __('GMW Search Form', 'GMW'), // Name
                array('description' => __('Displays Search forms in your sidebar.', 'GMW'),) // Args
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

        extract( $args );

        $title      = $instance['title']; // the widget title
        $short_code = $instance['short_code'];

        echo $before_widget;

        if ($title) {
            echo $before_title . $title . $after_title;
        }

        echo do_shortcode('[gmw form="' . $short_code . '" widget="1"]');

        echo '<div class="clear"></div>';

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
    	
        $defaults   = array('title' => __('Search Site', 'GMW'));
        $instance   = wp_parse_args((array) $instance, $defaults);
        $shortcodes = get_option('gmw_forms');
        ?>
        <p>
            <label><?php echo esc_attr(__('Title:', 'GMW')); ?></label>
            <input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
        </p>
        <p>
            <label><?php echo esc_attr(__('Choose form to use:', 'GMW')); ?></label>
            <br />
            <select name="<?php echo $this->get_field_name('short_code'); ?>">
	        <?php
	        foreach ($shortcodes as $shortcode) :
	        	$form_name = ( isset( $shortcode['name'] ) && !empty( $shortcode['name'] ) ) ? $shortcode['name'] : 'form_id_'.$shortcode['ID'];
	        
	            echo '<option value="' . $shortcode['ID'] . '"';
	            if (isset($instance['short_code']) && $instance['short_code'] == $shortcode['ID'])
	                echo 'selected="selected"'; echo '>'.$form_name .' - Form ID '. $shortcode['ID'] . '</options>';
	        endforeach;
	        ?>
            </select>
        </p>
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
        $instance['title']      = strip_tags($new_instance['title']);
        $instance['short_code'] = $new_instance['short_code'];

        return $instance;
    }
}
?>