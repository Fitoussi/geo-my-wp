<?php
/**
 * GEO my WP posts Locator form editor.
 *
 * @author Fitoussi <fitoussi_eyal2hotmail.com>
 *
 * @package geo-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Posts_Locator_Form_Editor
 *
 * Post type locator admin functions
 */
class GMW_Posts_Locator_Form_Editor {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Enable Location meta and hours of operation settings.
		add_filter( 'gmw_form_editor_disable_additional_fields', array( $this, 'enable_location_meta' ), 15, 4 );

		// Default settings.
		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 10, 2 );
		add_filter( 'gmw_form_settings', array( $this, 'form_settings' ), 10, 2 );

		// Mashup map form tasks.
		add_filter( 'gmw_posts_locator_mashup_map_form_settings', array( $this, 'form_settings' ), 5, 2 );

		// Taxonomies.
		add_action( 'gmw_posts_locator_component_form_settings_form_taxonomies', array( $this, 'taxonomies' ), 5, 3 );
	}

	/**
	 * Generate the taxonomy settings.
	 *
	 * @param  mixed  $value     field value.
	 *
	 * @param  string $name_attr [description].
	 *
	 * @param  array  $form      GMW form.
	 */
	public function taxonomies( $value, $name_attr, $form ) {
		GMW_Form_Settings_Helper::form_editor_taxonomies( array(), $value, $name_attr, $form );
	}

	/**
	 * Enable location meta.
	 *
	 * @param  [type] $output      [description].
	 * @param  [type] $groups      [description].
	 * @param  [type] $slug        [description].
	 * @param  [type] $form_object [description].
	 * @return [type]              [description]
	 */
	public function enable_location_meta( $output, $groups, $slug, $form_object ) {
		return 'posts_locator' === $form_object->form['component'] ? false : $output;
	}

	/**
	 * Default settings.
	 *
	 * @param  array $settings [description].
	 *
	 * @param  array $form     [description].
	 *
	 * @return array           [description]
	 */
	public static function default_settings( $settings, $form ) {

		if ( empty( $form['component'] ) || 'posts_locator' !== $form['component'] ) {
			return $settings;
		}

		$settings['page_load_results']['post_types'] = array( 'post' );

        // phpcs:disable.
		// No need the settings below for the mashup map form.
		/*if ( 'posts_locator_mashup_map' === $form['slug'] ) {
            return $settings;
        }*/
        // phpcs:enable.

		$settings['search_form']['post_types'] = array( 'post' );

		$settings['search_form']['post_types_settings'] = array(
			'usage'            => 'select',
			'label'            => '',
			'show_options_all' => 'Search site',
			'required'         => '',
		);
		$settings['search_form']['taxonomies']          = '';
		$settings['search_results']['excerpt']          = array(
			'usage' => 'disabled',
			'count' => 20,
			'link'  => 'read more...',
		);
		$settings['search_results']['opening_hours']    = '';
		$settings['search_results']['taxonomies']       = 1;

		$settings['info_window']['excerpt']    = $settings['search_results']['excerpt'];
		$settings['info_window']['taxonomies'] = 1;

		return $settings;
	}

	/**
	 * Form settings.
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $form     [description].
	 *
	 * @return [type]           [description]
	 */
	public static function form_settings( $settings, $form ) {

		if ( 'posts_locator' !== $form['component'] ) {
			return $settings;
		}

		// Post types settings.
		$post_types_settings = gmw_get_admin_setting_args(
			array(
				'name'        => 'post_types',
				'type'        => 'multiselect',
				'default'     => array( 'post' ),
				'label'       => __( 'Post Types', 'geo-my-wp' ),
				'placeholder' => __( 'Select post types', 'geo-my-wp' ),
				'desc'        => __( 'Select the post types that you would like to use in the form.', 'geo-my-wp' ),
				'options'     => ! empty( $form['search_form']['post_types'] ) ? array_combine( $form['search_form']['post_types'], $form['search_form']['post_types'] ) : array(),
				'attributes'  => array(
					'data-gmw_ajax_load_options' => 'gmw_get_post_types',
				),
				'priority'    => 4,
				'wrap_class'  => 'gmw-option-toggle-not',
				'sub_option'  => false,
			),
		);

		$settings['page_load_results']['post_types']             = $post_types_settings;
		$settings['page_load_results']['post_types']['options']  = ! empty( $form['page_load_results']['post_types'] ) ? array_combine( $form['page_load_results']['post_types'], $form['page_load_results']['post_types'] ) : array();
		$settings['page_load_results']['post_types']['priority'] = 10;

        // phpcs:disable.
		// No need the settings below for the mashup map form.
		/*if ( 'posts_locator_mashup_map' === $form['slug'] ) {
            return $settings;
        }*/
        // phpcs:enable.

		$settings['search_form']['post_types_settings'] = array(
			'name'            => 'post_types_settings',
			'type'            => 'fields_group',
			'label'           => __( 'Post Types', 'geo-my-wp' ),
			'fields'          => array(
				'post_types'       => $post_types_settings,
				'usage'            => gmw_get_admin_setting_args(
					array(
						'option_type' => 'usage_select',
						'default'     => 'pre_defined',
						'options'     => array(
							'pre_defined' => __( 'Pre-defined ( default value )', 'geo-my-wp' ),
							'select'      => __( 'Select Dropdown', 'geo-my-wp' ),
						),
					),
				),
				'label'            => gmw_get_admin_setting_args( 'label' ),
				'show_options_all' => gmw_get_admin_setting_args( 'show_options_all' ),
				'required'         => gmw_get_admin_setting_args( 'required' ),
			),
			'premium_message' => gmw_get_admin_setting_args(
				array(
					'option_type' => 'premium_message',
					'field_name'  => 'post types',
				),
			),
			'wrap_class'      => 'gmw-field-usage-options-toggle',
			'attributes'      => '',
			'priority'        => 12,
		);

		$settings['search_form']['taxonomies'] = gmw_get_admin_setting_args(
			array(
				'name'            => 'taxonomies',
				'type'            => 'function',
				'function'        => 'form_taxonomies',
				'label'           => __( 'Taxonomies', 'geo-my-wp' ),
				'desc'            => __( 'Enable the taxonomies that you would like to use as filters in the search form.', 'geo-my-wp' ),
				'priority'        => 13,
				'premium_message' => gmw_get_admin_setting_args(
					array(
						'option_type' => 'premium_message',
						'field_name'  => 'taxonomies',
					),
				),
			),
		);

		$settings['search_results']['excerpt'] = array(
			'name'       => 'excerpt',
			'type'       => 'fields_group',
			'label'      => __( 'Post Excerpt', 'geo-my-wp' ),
			'desc'       => __( 'Display the post excerpt.', 'geo-my-wp' ),
			'fields'     => array(
				'usage' => gmw_get_admin_setting_args(
					array(
						'option_type' => 'usage_select',
						'default'     => 'disabled',
						'options'     => array(
							'disabled'     => __( 'Disable', 'geo-my-wp' ),
							'post_content' => __( 'Post Content', 'geo-my-wp' ),
							'post_excerpt' => __( 'Post Excerpt', 'geo-my-wp' ),
						),
						'class'       => 'gmw-options-toggle gmw-smartbox-not',
					),
				),
				'count' => gmw_get_admin_setting_args(
					array(
						'name'        => 'count',
						'type'        => 'number',
						'default'     => '',
						'placeholder' => __( 'Enter numeric value', 'geo-my-wp' ),
						'label'       => __( 'Word Count', 'geo-my-wp' ),
						'desc'        => __( 'Enter the max number of words to display or leave blank to display the entire excerpt.', 'geo-my-wp' ),
						'priority'    => 15,
					),
				),
				'link'  => gmw_get_admin_setting_args(
					array(
						'name'        => 'link',
						'type'        => 'text',
						'default'     => '',
						'label'       => __( 'Read More Link', 'geo-my-wp' ),
						'placeholder' => 'Read more link',
						'desc'        => __( 'Enter the text that will be used as the "Read more" link at the end of the excerpt and will link to the single post\'s page.', 'geo-my-wp' ),
						'priority'    => 20,
					),
				),
			),
			'attributes' => '',
			'priority'   => 60,
			'iw_option'  => 1,
		);

		$settings['search_results']['taxonomies'] = gmw_get_admin_setting_args(
			array(
				'name'      => 'taxonomies',
				'type'      => 'checkbox',
				'label'     => __( 'Taxonomies', 'geo-my-wp' ),
				'cb_label'  => __( 'Enable', 'geo-my-wp' ),
				'desc'      => __( 'Check this checkbox to display the taxonomies and terms associate with each post.', 'geo-my-wp' ),
				'priority'  => 65,
				'iw_option' => 1,
			),
		);

		return $settings;
	}
}
new GMW_Posts_Locator_Form_Editor();
