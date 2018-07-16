<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
        
/**
 * GEO my WP search form widget.
 *
 * @since 1.0
 * @modified 3.0
 * 
 */
class GMW_Search_Form_Widget extends GMW_Widget {

	/**
	 * Widget ID
	 * 
	 * @var string
	 */
	public $widget_id = 'gmw_search_form_widget';

	/**
	 * Widget class
	 * 
	 * @var string
	 */
	public $widget_class = 'geo-my-wp widget-search-form';
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		
		$this->widget_description = __( 'Display GEO my WP search form.', 'geo-my-wp' );
		$this->widget_name        = __( 'GEO my WP Search Form', 'geo-my-wp' );

		$forms = gmw_get_forms();

		if ( empty( $forms ) || ! is_array( $forms ) ) {
			$forms = array();
		}
		
		$options = array();
		// allowed forms in this widget
		$allowed_forms = apply_filters( 'gmw_search_form_widget_allowed_forms', array( 'posts_locator', 'members_locator','bp_groups_locator', 'users_locator' ) );
		
		foreach ( $forms as $form ) {

			if ( ( $form_id = absint( $form['ID'] ) ) != FALSE && in_array( $form['slug'], $allowed_forms ) ) {

				$form_name  = ! empty( $form['name'] ) ? $form['name'] : 'form_id_'.$form_id;
				$form_label = 'Form ID '.$form_id.' ( '.$form_name.' )';
				$options[$form_id] = $form_label;
			}
		} 

		$this->settings = array(
			'title' 	=> array(
				'type'    => 'text',
				'default' => __( 'Search Locations', 'geo-my-wp' ),
				'label'   => __( 'Title', 'geo-my-wp' ),
			),
			'form_id' => array( 
				'type'    => 'select',
				'default' => '',
				'label'   => __( 'Form', 'geo-my-wp' ),
				'options' => $options
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

		ob_start();

		extract( $args );

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );		
		
		echo $before_widget;

		if ( $title ) { echo $before_title . esc_attr( $title ) . $after_title; } 
		
		// verify shortcode ID
		if ( absint( $instance['form_id'] ) != False ) {
			echo do_shortcode( "[gmw search_form=\"{$instance['form_id']}\" widget=\"1\"]" );
		}		
			
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;
	}
}
register_widget( 'GMW_Search_Form_Widget' );
?>
