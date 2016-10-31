<?php

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
        
/**
 *  GMW Search Form widget
 */
class GMW_Search_Form_Widget extends WP_Widget {

	/**
	 * __constructor
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
				'gmw_search_form_widget', // Base ID
				__('GMW Form', 'GMW'), // Name
				array( 'description' => __( 'Displays GEO my WP forms.', 'GMW' ), ) // Args
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

		extract( $args );
		
		$title   = $instance['title']; // the widget title
		$form_id = $instance['short_code'];
		$addon   = ( !empty( $instance['addon'] ) ) ? $instance['addon'] : 'posts';

		echo $before_widget;

		if ( !empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		if ( $addon == 'global_maps' ) {
			echo do_shortcode("[gmw form=\"{$form_id}\" widget=\"1\"]");
		} else {
			echo do_shortcode("[gmw search_form=\"{$form_id}\" widget=\"1\"]");
		}
			
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

		$defaults = array( 'title' => __( 'Search Locations', 'GMW' ) );
		$instance = wp_parse_args( (array) $instance, $defaults );
		$forms 	  = get_option( 'gmw_forms' );
		?>
		<p>
			<label><?php echo esc_attr( __( 'Widget Title:', 'GMW') ); ?></label> 
			<input type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label><?php echo esc_attr( __( 'Choose form to use:', 'GMW' ) ); ?> </label>
			<br />
			<select name="<?php echo $this->get_field_name('short_code'); ?>">		
			<?php

				if ( empty( $forms ) || ! is_array( $forms ) ) {
					$forms = array();
				}

				foreach ( $forms as $form ) {
					
					$form_id = absint( $form['ID'] );

					if ( empty( $form_id ) )
						continue;

					$form_name = ( !empty( $form['name'] ) ) ? $form['name'] : 'form_id_'.$form_id;
					$selected  = ( !empty( $instance['short_code'] ) && $instance['short_code'] == $form_id ) ? 'selected="selected"' : '';
					
					echo '<option value="'.$form_id.'" '.$selected.'>'. esc_attr( $form_name ).' - Form ID '.$form_id.' ( '.esc_attr( $form['addon'] ).' )</option>';
				} 
			?>
			</select>
		</p>
		<?php
	}

	/**
	* Sanitize widget form values as they are saved.
	* @see WP_Widget::update()
	* @param array $new_instance Values just sent to be saved.
	* @param array $old_instance Previously saved values from database.
	* @return array Updated safe values to be saved.
	*/
	function update( $new_instance, $old_instance ) {

		//get forms
		$forms = get_option( 'gmw_forms' );

		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['short_code'] = $new_instance['short_code'];
		$instance['addon'] 		= $forms[$new_instance['short_code']]['addon'];

		return $instance;
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Search_Form_Widget" );' ) );
?>