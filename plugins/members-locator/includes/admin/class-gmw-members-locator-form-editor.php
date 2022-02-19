<?php
/**
 * GEO my WP Memebrs Locator form editor.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Members_Locator_Form_Editor
 *
 * Members Locator form settings class.
 */
class GMW_Members_Locator_Form_Editor {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_filter( 'gmw_members_locator_form_default_settings', array( $this, 'set_defaults' ), 20 );
		add_filter( 'gmw_members_locator_form_settings', array( $this, 'form_settings' ), 15 );
		// settings fields
		add_action( 'gmw_members_locator_form_settings_xprofile_fields', array( 'GMW_Form_Settings_Helper', 'bp_xprofile_fields' ), 10, 2 );
		// validations
		add_filter( 'gmw_members_locator_validate_form_settings_xprofile_fields', array( 'GMW_Form_Settings_Helper', 'validate_bp_xprofile_fields' ) );
	}

	/**
	 * Default settings
	 *
	 * @param  [type] $settings [description].
	 *
	 * @return [type]           [description]
	 */
	public function set_defaults( $settings, $args ) {

		$settings['search_form']['form_template']                 = 'responsive-1';
		$settings['search_form']['xprofile_fields']['fields']     = array();
		$settings['search_form']['xprofile_fields']['date_field'] = '';
		$settings['search_results']['image']['width']             = '100px';
		$settings['search_results']['image']['show_grav']         = 1;
		$settings['search_results']['image']['show_default']      = 1;
		$settings['search_results']['friendship_button']          = 1;

		// For mashup map.
		if ( 'members_locator_mashup_map' === $args['slug'] ) {

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
	 * @return [type]           [description].
	 */
	public function modify_form_settings_groups( $settings, $form ) {

		if ( 'members_locator_mashup_map' === $form['slug'] ) {

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
	 */
	function form_settings( $fields ) {

		// search form features
		$fields['search_form']['xprofile_fields'] = array(
			'name'       => 'xprofile_fields',
			'type'       => 'function',
			'default'    => '',
			'label'      => __( 'Xprofile Fields', 'geo-my-wp' ),
			'desc'       => __( '<ul><li> - Profile fields - Select the profile fields that will be used as filters in the search form.</li><li> - Age range field - select a date field that will be used as a age range filter in the search form.</li></ul>', 'geo-my-wp' ),
			'attributes' => '',
			'priority'   => 13,
		);

		return $fields;
	}
}
new GMW_Members_Locator_Form_Editor();

