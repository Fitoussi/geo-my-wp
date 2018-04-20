<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_FL_Admin class.
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
	 * @param  [type] $settings [description]
	 * @return [type]           [description]
	 */
	public function set_defaults( $settings ) {

		$settings['search_form']['form_template']                 = 'yellow';
		$settings['search_results']['results_template']           = 'yellow';
		$settings['search_form']['xprofile_fields']['fields']     = array();
		$settings['search_form']['xprofile_fields']['date_field'] = '';

		return $settings;
	}

	/**
	 * form settings function.
	 *
	 * @access public
	 * @return $settings
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

