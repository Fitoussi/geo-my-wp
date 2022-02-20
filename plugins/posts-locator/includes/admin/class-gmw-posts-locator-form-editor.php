<?php
/**
 * GEO my WP posts Locator form editor.
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
		add_filter( 'gmw_posts_locator_form_editor_disable_additional_fields', '__return_false' );

		// Posts Locator form tasks.
		add_filter( 'gmw_posts_locator_form_default_settings', array( $this, 'default_settings' ), 5, 2 );
		add_filter( 'gmw_posts_locator_form_settings', array( $this, 'form_settings_init' ), 5, 2 );
		add_action( 'gmw_posts_locator_form_settings_form_taxonomies', array( 'GMW_Form_Settings_Helper', 'taxonomies' ), 5, 3 );

		// Mashup map form tasks.
		add_filter( 'gmw_posts_locator_mashup_map_form_default_settings', array( $this, 'default_settings' ), 5, 2 );
		add_filter( 'gmw_posts_locator_mashup_map_form_settings', array( $this, 'form_settings_init' ), 5, 2 );
		add_filter( 'gmw_posts_locator_mashup_map_form_settings_groups', array( $this, 'modify_form_settings_groups' ), 5, 2 );
	}

	/**
	 * Default settings.
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $args     [description].
	 *
	 * @return [type]           [description]
	 */
	public function default_settings( $settings, $args ) {

		$settings['page_load_results']['post_types']    = array( 'post' );
		$settings['search_form']['post_types']          = array( 'post' );
		$settings['search_form']['post_types_settings'] = array(
			'usage'            => 'select',
			'label'            => '',
			'show_options_all' => 'Search site',
			'required'         => '',
		);
		$settings['search_form']['taxonomies']          = '';
		$settings['search_results']['excerpt']          = array(
			'usage' => 'post_content',
			'count' => 10,
			'link'  => 'read more...',
		);
		$settings['search_results']['opening_hours'] = '';
		$settings['search_results']['taxonomies']    = 1;

		// For mashup map.
		if ( 'posts_locator_mashup_map' === $args['slug'] ) {

			$settings['page_load_results']['enabled']         = 1;
			$settings['page_load_results']['display_results'] = 0;
			$settings['page_load_results']['display_map']     = 'shortcode';
			$settings['page_load_results']['per_page']        = 200;
			$settings['search_form']['form_template']         = '';
		}

		return $settings;
	}

	/**
	 * Modify some settings and tabs for the mashup map form.
	 *
	 * @param  [type] $settings [description].
	 *
	 * @param  [type] $form     [description].
	 *
	 * @return [type]           [description]
	 */
	public function modify_form_settings_groups( $settings, $form ) {

		if ( 'posts_locator_mashup_map' === $form['slug'] ) {

			unset( $settings['no_results'] );

			$settings['page_load_results']['label'] = __( 'Map Filters', 'geo-my-wp' );

			$settings['search_form']['tab_class']   = 'gmw-hidden-form-editor-object';
			$settings['search_form']['panel_class'] = 'gmw-hidden-form-editor-object';

			$settings['search_results']['tab_class']   = 'gmw-hidden-form-editor-object';
			$settings['search_results']['panel_class'] = 'gmw-hidden-form-editor-object';

			$settings['form_submission']['tab_class']   = 'gmw-hidden-form-editor-object';
			$settings['form_submission']['panel_class'] = 'gmw-hidden-form-editor-object';
		}

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
	public function form_settings_init( $settings, $form ) {

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
				'sub_option'  => false,
			),
		);

		$settings['page_load_results']['post_types']            = $post_types_settings;
		$settings['page_load_results']['post_types']['options'] = ! empty( $form['page_load_results']['post_types'] ) ? array_combine( $form['page_load_results']['post_types'], $form['page_load_results']['post_types'] ) : array();

		// Modify some settings for mashup map.
		if ( 'posts_locator_mashup_map' === $form['slug'] ) {

			$settings['page_load_results']['enabled']['wrap_class']    = 'gmw-hidden-form-editor-object';
			$settings['page_load_results']['enabled']['default']       = 1;
			$settings['page_load_results']['enabled']['force_default'] = 1;

			$settings['page_load_results']['display_results']['wrap_class']    = 'gmw-hidden-form-editor-object';
			$settings['page_load_results']['display_results']['type']          = 'hidden';
			$settings['page_load_results']['display_results']['default']       = 0;
			$settings['page_load_results']['display_results']['force_default'] = 1;

			$settings['page_load_results']['display_map']['wrap_class']    = 'gmw-hidden-form-editor-object';
			$settings['page_load_results']['display_map']['type']          = 'hidden';
			$settings['page_load_results']['display_map']['default']       = 'shortcode';
			$settings['page_load_results']['display_map']['force_default'] = 1;

			$settings['page_load_results']['per_page']['label']   = __( 'Results Count', 'geo-my-wp' );
			$settings['page_load_results']['per_page']['desc']    = __( 'Enter the maximum number of locations to show on the map.', 'geo-my-wp' );
			$settings['page_load_results']['per_page']['type']    = 'number';
			$settings['page_load_results']['per_page']['default'] = 200;

			$settings['search_form']['form_template']['type']          = 'hidden';
			$settings['search_form']['form_template']['default']       = '';
			$settings['search_form']['form_template']['force_default'] = 1;

			return $settings;
		}

		/* translators: %s link to the prmium settings page. */
		$premium_message = sprintf( __( 'Checkout the <a href="%s" target="_blank">Premium Settings extension</a> for additional post types options.', 'geo-my-wp' ), 'https://geomywp.com/extensions/premium-settings' );

		$settings['search_form']['post_types_settings'] = array(
			'name'       => 'post_types_settings',
			'type'       => 'fields_group',
			'label'      => __( 'Post Types', 'geo-my-wp' ),
			'fields'     => array(
				'post_types'     => $post_types_settings,
				'usage'          => gmw_get_admin_setting_args(
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
			'premium_message' => array(
				'class'   => 'GMW_Premium_Settings_Addon',
				'message' => $premium_message,
			),
			'attributes'      => '',
			'priority'        => 12,
		);

		$settings['search_form']['taxonomies'] = gmw_get_admin_setting_args(
			array(
				'name'            => 'taxonomies',
				'type'            => 'function',
				'function'        => 'form_taxonomies',
				'label'           => __( 'Taxonomy Filters', 'geo-my-wp' ),
				'desc'            => __( 'Enable the taxonomies that you would like to use as filters in the search form.', 'geo-my-wp' ),
				'priority'        => 13,
				'premium_message' => array(
					'class'   => 'GMW_Premium_Settings_Addon',
					'message' => $premium_message,
				),
			),
		);

		$settings['search_results']['excerpt'] = array(
			'name'       => 'excerpt',
			'type'       => 'fields_group',
			'label'      => __( 'Post Excerpt', 'geo-my-wp' ),
			'desc'       => __( 'Display the post excerpt in the search results.', 'geo-my-wp' ),
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
						'default'     => '20',
						'placeholder' => __( 'Enter numeric value', 'geo-my-wp' ),
						'label'       => __( 'Word Count', 'geo-my-wp' ),
						'desc'        => __( 'Enter the max number of words to display or leave blank to display the entire excerpt.', 'geo-my-wp' ),
						'priority'    => 15,
					),
				),
				'link' => gmw_get_admin_setting_args(
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
			'priority'   => 30,
		);

		$settings['search_results']['taxonomies'] = gmw_get_admin_setting_args(
			array(
				'name'     => 'taxonomies',
				'type'     => 'checkbox',
				'label'    => __( 'Taxonomies', 'geo-my-wp' ),
				'cb_label' => __( 'Enable', 'geo-my-wp' ),
				'desc'     => __( 'Check this checkbox to display the taxonomies and terms associate with each post in the list of results.', 'geo-my-wp' ),
				'priority' => 65,
			),
		);

		return $settings;
	}
}
new GMW_Posts_Locator_Form_Editor();
