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

        $widget_title   = $instance['widget_title']; // the widget title
        $title_location = $instance['title_location'];
        $display_by     = $instance['display_by'];
        $name_guest     = $instance['name_guest'];

        $title_location = ( isset($title_location) ) ? 'title="' . $title_location . '"' : 'title=""';
        $display_by     = ( isset($display_by) && !empty($display_by) ) ? 'display_by="' . implode(',', $display_by) . '"' : 'display_by="city"';
        $name_guest     = ( isset($name_guest) ) ? 'show_name="1"' : 'show_name="0"';

        echo $before_widget;

        if (isset($widget_title) && !empty($widget_title))
            echo $before_title . $widget_title . $after_title;

        echo do_shortcode('[gmw_current_location ' . $name_guest . ' ' . $display_by . ' ' . $title_location . ' ]');

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
    function form($instance) {

        $defaults = array(
            'widget_title'   => __('Current Location', 'GMW'),
            'title_location' => __('Your Location', 'GMW')
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label><?php echo esc_attr(__("Widget's Title: ", 'GMW')); ?></label>
            <input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php if (isset($instance['widget_title'])) echo $instance['widget_title']; ?>" class="widefat" />
        </p>
        <p>
            <label><?php echo esc_attr(__('Title: (ex:"Your Location")', 'GMW')); ?></label>
            <input type="text" name="<?php echo $this->get_field_name('title_location'); ?>" value="<?php if (isset($instance['title_location'])) echo $instance['title_location']; ?>" class="widefat" />
        </p>
        <p>
        <?php echo '<input type="checkbox" value="1" name="' . $this->get_field_name('name_guest') . '"';
        if (isset($instance["name_guest"])) echo 'checked="checked"'; echo 'class="checkbox" />'; ?>
            <label><?php echo esc_attr(__('Display User Name.', 'GMW')); ?></label>
        </p>
        <p>
            <label><?php echo esc_attr(__('Display location:')); ?></label><br />
            <input type="checkbox" value="street"  name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('street', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Street', 'GMW'); ?></label><br />
            <input type="checkbox" value="city"    name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('city', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('City', 'GMW'); ?></label><br />
            <input type="checkbox" value="state"   name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('state', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('State', 'GMW'); ?></label><br />
            <input type="checkbox" value="zipcode" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('zipcode', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Zipcode', 'GMW'); ?></label><br />
            <input type="checkbox" value="country" name="<?php echo $this->get_field_name('display_by'); ?>[]" <?php if (isset($instance['display_by']) && in_array('country', $instance['display_by'])) echo 'checked="checked"'; ?> class="checkbox" /><label><?php _e('Country', 'GMW'); ?></label><br />
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
    function update($new_instance, $old_instance) {

        $instance['widget_title']        = strip_tags($new_instance['widget_title']);
        $instance['title_location']      = strip_tags($new_instance['title_location']);
        $instance['short_code_location'] = $new_instance['short_code_location'];
        $instance['display_by']          = $new_instance['display_by'];
        $instance['name_guest']          = $new_instance['name_guest'];

        return $instance;

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

        extract($args);

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
    function form($instance) {
    	
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