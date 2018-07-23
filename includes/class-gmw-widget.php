<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GEO my WP Widget base.
 *
 * @author this base widget was originally written for the WP Job Managaer plugin ( thank you ) and
 * was modified to work with GEO my WP. 
 * 
 * @since 3.0.0
 */
class GMW_Widget extends WP_Widget {
	
	/**
	 * Widget id.
	 *
	 * @var string
	 */
	public $widget_id;

	/**
	 * Widget class tag.
	 *
	 * @var string
	 */
	public $widget_class;

	/**
	 * Widget description.
	 *
	 * @var string
	 */
	public $widget_description;

	/**
	 * Widget name.
	 *
	 * @var string
	 */
	public $widget_name;

	/**
	 * Widget settings.
	 *
	 * @var array
	 */
	public $settings;

	/**
	 * Link to widget docs page
	 * 
	 * @var boolean
	 */
	public $help_link = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Registers widget.
	 */
	public function register() {
		$widget_ops = array(
			'classname'   => $this->widget_class,
			'description' => $this->widget_description,
		);

		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops );
	}

	/**
	 * Updates a widget instance settings.
	 *
	 * @see WP_Widget->update
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( ! $this->settings ) {
			return $instance;
		}

		foreach ( $this->settings as $key => $setting ) {

			if ( ! isset( $new_instance[ $key ] ) ) {
				$new_instance[ $key ] = ( 'checkbox' != $setting['type'] ) ? $setting['default'] : '';
			}

			if ( ! is_array( $new_instance[ $key ] ) ) {
				$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
			} else {
				$instance[ $key ] = array_map( 'sanitize_text_field', $new_instance[ $key ] );
			}
		}

		return $instance;
	}

	/**
	 * Displays widget setup form.
	 *
	 * @see WP_Widget->form
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {

		if ( ! $this->settings ) {
			return;
		}

		?>
		<div class="gmw-widget-wrapper">
		<?php

		if ( $this->help_link ) { ?>
			<p class="gmw-message-box">
                <i class="gmw-icon-lifebuoy"></i> 
                <a href="<?php echo esc_url( $this->help_link ); ?>" target="_blank" title="Single Location widget docs">Click here</a> for the full, detailed user guide for this widget.
            </p>
		<?php }
		
		$allowed_html = array(
			'a'  => array( 
				'title' => array(),
				'href'  => array()
			),
			'p'  => array(),
			'em' => array(),
			'ul' => array( 'li' => array() ),
			'ol' => array( 'li' => array() ),
			'li' => array()
		);

		foreach ( $this->settings as $key => $setting ) {

			// get saved or default value
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['default'];

			$description = '';

			if ( ! empty( $setting['description'] ) ) {
				$description = '<em class="desc">'. wp_kses( $setting['description'], $allowed_html ).'</em>';
			}

			switch ( $setting['type'] ) {
				case 'text' :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
						<?php echo $description; ?>
					</p>
					<?php
				break;
				case 'number' :
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<?php echo $description; ?>
					</p>
					<?php
				break;
				case 'select' :
					if ( empty( $settings['options'] ) || ! is_array( $settings['options'] ) ) {
						$settings['options'] = array();
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo $this->get_field_name( $key ); ?>">
							<?php foreach ( $setting['options'] as $opt_value => $opt_label ) { ?>
								<?php $selected = $value == $opt_value ? 'selected="selected"' : ''; ?>
								<option value="<?php echo esc_attr( $opt_value ); ?>" <?php echo $selected; ?>>
									<?php echo esc_attr( $opt_label ); ?>		
								</option>
							<?php } ?>
						</select>
						<?php echo $description; ?>
					</p>
					<?php
				break;
				case 'checkbox' :
					?>
					<p>
						<label>
							<input id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" value="1" type="checkbox" name="<?php echo $this->get_field_name( $key ); ?>" <?php if ( ! empty( $value ) ) echo 'checked="checked"'; ?> class="checkbox" />
							<?php echo $setting['label']; ?>
						</label>
						<br />
						<?php echo $description; ?>
					</p>
					<?php
				break;
				case 'multicheckbox' :

					// get saved or default value
					$value = ! empty( $instance[ $key ] ) ? $instance[ $key ] : $setting['default'];

					if ( ! is_array( $value ) ) {
						$value = array();
					}

					if ( empty( $settings['options'] ) || ! is_array( $settings['options'] ) ) {
						$settings['options'] = array();
					}
					?>
					<p>
						<label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo $setting['label']; ?></label>
						<br />
						<?php foreach ( $setting['options'] as $opt_value => $opt_label ) { ?>
							<?php $checked = in_array( $opt_value, $value ) ? 'checked="checked"' : ''; ?>
							<label>
								<input id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" value="<?php echo esc_attr( $opt_value ); ?>" type="checkbox" name="<?php echo $this->get_field_name( $key ); ?>[]" <?php echo $checked ?> class="checkbox" />
								<?php echo esc_attr( $opt_label ); ?>
							</label>
							<br />
						<?php } ?>
						<?php echo $description; ?>
					</p>
					<?php
				break;
			}
		}
		echo '</div>';
	}

	/**
	 * Echoes the widget content.
	 *
	 * @see    WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {}
}
