<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
        
/**
 * Display GEO my WP search form
 *
 * @since 1.0.0
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
		
		$this->widget_description = __( 'Display GEO my WP search form.', 'GMW' );
		$this->widget_name        = __( 'GEO my WP Search Form', 'GMW' );

		$forms = gmw_get_forms();

		if ( empty( $forms ) || ! is_array( $forms ) ) {
			$forms = [];
		}
		
		$options = [];

		foreach ( $forms as $form ) {
			if ( ( $form_id = absint( $form['ID'] ) ) != FALSE ) {
				$form_name  = ! empty( $form['name'] ) ? $form['name'] : 'form_id_'.$form_id;
				$form_label = 'Form ID '.$form_id.' ( '.$form_name.' )';
				$options[$form_id] = $form_label;
			}
		} 

		$this->settings = array(
			'title' 	=> array(
				'type'    => 'text',
				'default' => __( 'Search Locations', 'GMW' ),
				'label'   => __( 'Title', 'GMW' ),
			),
			'form_id' => array( 
				'type'    => 'select',
				'default' => '',
				'label'   => __( 'Form', 'GMW' ),
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
			if ( gmw_get_form($instance['form_id'])['addon'] == 'global_maps' ) {
				echo do_shortcode( "[gmw form=\"{$instance['short_code']}\" widget=\"1\"]" );
			} else {
				echo do_shortcode( "[gmw search_form=\"{$instance['form_id']}\" widget=\"1\"]" );
			}
		}		
			
		echo $after_widget;

		$content = ob_get_clean();

		echo $content;
	}
}
register_widget( 'GMW_Search_Form_Widget' );
?>