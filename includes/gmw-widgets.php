<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
        
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
			'gmw_search_form_widget', 
			__( 'GMW Form', 'GMW' ),
			array( 
				'description' => __( 'Displays GEO my WP forms.', 'GMW' ), 
			) 
		);

		$this->default_args = array(
			'title' 	 => __( 'Search Locations', 'GMW' ),
			'short_code' => ''
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
		
		$title   = ! empty( $instance['title'] ) ? esc_html( $instance['title'] ) : '';
		
		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		
		// verify shortcode ID
		if ( ! empty( $instance['short_code'] ) && absint( $instance['short_code'] ) ) {

			$addon   = ! empty( $instance['addon'] ) ? $instance['addon'] : 'posts_locator';

			if ( $addon == 'global_maps' ) {
				echo do_shortcode( "[gmw form=\"{$instance['short_code']}\" widget=\"1\"]" );
			} else {
				echo do_shortcode( "[gmw search_form=\"{$instance['short_code']}\" widget=\"1\"]" );
			}
		}		
			
		echo $after_widget;
	}

	/**
	* Sanitize widget form values as they are saved.
	* @see WP_Widget::update()
	* @param array $new_instance Values just sent to be saved.
	* @param array $old_instance Previously saved values from database.
	* @return array Updated safe values to be saved.
	*/
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		//get forms
		$forms = gmw_get_forms();

		$instance['title']      = sanitize_text_field( $new_instance['title'] );
		$instance['short_code'] = ( int ) $new_instance['short_code'];
		$instance['addon'] 		= $forms[$new_instance['short_code']]['addon'];

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

		$instance = wp_parse_args( (array) $instance, $this->default_args );
		$forms 	  = gmw_get_forms();

		?>
		<p>
			<label><?php esc_attr( __( 'Widget Title:', 'GMW' ) ); ?></label> 
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo sanitize_text_field( $instance['title'] ); ?>" class="widefat" />
		</p>
		<p>
			<label><?php esc_attr( __( 'Form:', 'GMW' ) ); ?> </label>
			<br />
			<select name="<?php echo $this->get_field_name( 'short_code' ); ?>">		
			<?php

				if ( empty( $forms ) || ! is_array( $forms ) ) {
					$forms = array();
				}
				
				foreach ( $forms as $form ) {
					
					$form_id = absint( $form['ID'] );

					if ( empty( $form_id ) ) {
						continue;
					}

					$form_name  = ! empty( $form['name'] ) ? $form['name'] : 'form_id_'.$form_id;
					$selected   = ( ! empty( $instance['short_code'] ) && $instance['short_code'] == $form_id ) ? 'selected="selected"' : '';
					$form_label = $form_name.' - Form ID '.$form_id.' ( '.$form['addon'].' )';
					
					echo '<option value="'.$form_id.'" '.$selected.'>'. esc_html( $form_label ).'</option>';
				} 
			?>
			</select>
		</p>
		<?php
	}
}
add_action( 'widgets_init', create_function( '', 'return register_widget( "GMW_Search_Form_Widget" );' ) );
?>