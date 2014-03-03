<?php
/**
 * GMW Widget - User's current location
 * @version 1.0
 * @author Eyal Fitoussi
 */

class wppl_widget_location extends WP_Widget {
	// Constructor //
		function wppl_widget_location() {
			$widget_location_ops = array( 'classname' => 'wppl_widget_location', 'description' => 'Displays the current location of the logged in user' ); // Widget Settings
			$control_location_ops = array( 'id_base' => 'wppl_widget_location' ); // Widget Control Settings
			$this->WP_Widget( 'wppl_widget_location', 'GMW Current Location', $widget_location_ops, $control_location_ops ); // Create the widget
		}
	// Extract Args //
		function widget($args, $instance) {
			extract( $args );
			$widget_title			= $instance['widget_title']; // the widget title
			if ( isset( $instance['title_location'] ) ) $title_location = $instance['title_location']; 
			$short_code_location	= $instance['short_code_location'];
			$display_by 			= $instance['display_by'];
			$name_guest 			= $instance['name_guest'];
			
			echo $before_widget;

			if (isset($widget_title) && !empty($widget_title) ) echo $before_title . $widget_title . $after_title; 
			
        	echo do_shortcode('[gmw_current_location show_name="'.$name_guest.'" display_by="'.implode(',',$display_by).'" title="'.$title_location.'"]');
			echo '<div class="clear"></div>';
			echo $after_widget;
		}

	// Update Settings //
		function update($new_instance, $old_instance) {
			$instance['widget_title'] 		 = strip_tags($new_instance['widget_title']);
			$instance['title_location'] 	 = strip_tags($new_instance['title_location']);
			$instance['short_code_location'] = $new_instance['short_code_location'];
			$instance['display_by'] 		 = $new_instance['display_by'];
			$instance['name_guest'] 		 = $new_instance['name_guest'];
			
			return $instance;
		}

	// Widget Control Panel //
		function form($instance) {
			$defaults = array( 'title' => 'WPPL User Location');
			$instance = wp_parse_args( (array) $instance, $defaults ); 
			?>
			<p style="margin-bottom:10px; float:left;">
		    	<label><?php echo  esc_attr( __( "Widget's Title: " ) ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if ( isset($instance['widget_title']) ) echo $instance['widget_title']; ?>" width="25" style="float: left;width: 100%;"/>
			</p>
  			<p style="margin-bottom:10px; float:left;">
		    	<label><?php echo  esc_attr( __( 'Title: (ex:"Your Location")' ) ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('title_location'); ?>" value="<?php if ( isset($instance['title_location']) ) echo $instance['title_location']; ?>" width="25" style="float: left;width: 100%;"/>
			</p>
			<p style="margin-bottom:10px; float:left;width:100%">
		    	<?php echo '<input type="checkbox" value="1" name="'. $this->get_field_name('name_guest').'"'; if ( isset($instance["name_guest"]) ) echo 'checked="checked"'; echo 'width="25" style="float: left;margin-right:10px;"/>'; ?>
		    	<label><?php echo  esc_attr( __( 'Display User Name.' ) ); ?></label>
			</p>
			<p>
			<label><?php echo esc_attr( __( 'Display location:' ) ); ?></label><br />
				<input type="checkbox" value="street"  name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if ( isset( $instance['display_by'] ) && in_array( 'street', $instance['display_by'] ) ) echo 'checked="checked"'; ?> width="25" style="float: left;margin-right:10px" />Street  <br />
				<input type="checkbox" value="city"    name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if ( isset( $instance['display_by'] ) && in_array( 'city', $instance['display_by'] ) ) echo 'checked="checked"'; ?> width="25" style="float: left;margin-right:10px" />City    <br />
				<input type="checkbox" value="state"   name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if ( isset( $instance['display_by'] ) && in_array( 'state', $instance['display_by'] ) ) echo 'checked="checked"'; ?> width="25" style="float: left;margin-right:10px" />State   <br />
				<input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if ( isset( $instance['display_by'] ) && in_array( 'zipcode', $instance['display_by'] ) ) echo 'checked="checked"'; ?> width="25" style="float: left;margin-right:10px" />Zipcode <br />
				<input type="checkbox" value="country" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if ( isset( $instance['display_by'] ) && in_array( 'country', $instance['display_by'] ) ) echo 'checked="checked"'; ?> width="25" style="float: left;margin-right:10px" />County  <br />
			</p>
	<?php } 
	 }
add_action('widgets_init', create_function('', 'return register_widget("wppl_widget_location");'));

//// Register Search Form widget ////
class wppl_widget extends WP_Widget {
	// Constructor //
		function wppl_widget() {
			$widget_ops = array( 'classname' => 'wppl_widget', 'description' => 'Displays Search forms in your sidebar.' ); // Widget Settings
			$control_ops = array( 'id_base' => 'wppl_widget' ); // Widget Control Settings
			$this->WP_Widget( 'wppl_widget', 'GMW Search Form', $widget_ops, $control_ops ); // Create the widget
		}
	// Extract Args //
		function widget($args, $instance) {
			$gmw_options = get_option('wppl_fields');
			extract( $args );
			
			$title 			= apply_filters('widget_title', $instance['title']); // the widget title
			$short_code		= $instance['short_code'];
			
			echo $before_widget;

			if ( $title ) { echo $before_title . $title . $after_title; }

        	echo do_shortcode('[gmw form="'.$short_code.'" widget="1"]');
			echo '<div class="clear"></div>';
			echo $after_widget;
		}

	// Update Settings //
		function update($new_instance, $old_instance) {
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['short_code'] = $new_instance['short_code'];
			//$instance['pages'] = $new_instance['pages'];
			return $instance;
		}

	// Widget Control Panel //
		function form($instance) {
			$w_posts = get_post_types();
			$defaults = array( 'title' => 'Search Places');
			$instance = wp_parse_args( (array) $instance, $defaults ); 
			$shortcodes = get_option('wppl_shortcode');
			?>
  			<p style="margin-bottom:10px; float:left;">
		    	<label><?php echo  esc_attr( __( 'Title:' ) ); ?></label>
				<input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" width="25" style="float: left;width: 100%;"/>
			</p>
			<p>
			<label><?php echo esc_attr( __( 'Choose shortcode to use in the sidebar:' ) ); ?></label>
				<select name="<?php echo $this->get_field_name('short_code'); ?>" style="float: left;width: 100%;">
				<?php 
				foreach ($shortcodes as $shortcode) :
					//if ( isset( $shortcode['results_page'] ) && !empty($shortcode['results_page']) ) :
						echo '<option value="' . $shortcode['form_id'] . '"'; if ( isset($instance['short_code']) && $instance['short_code'] == $shortcode['form_id'] ) echo 'selected="selected"'; echo '>GMW form ID '. $shortcode['form_id'] . '</options>';
					//endif;
				endforeach;
				 ?>
				</select>
			</p>
	<?php } 
	 }
add_action('widgets_init', create_function('', 'return register_widget("wppl_widget");'));
?>
