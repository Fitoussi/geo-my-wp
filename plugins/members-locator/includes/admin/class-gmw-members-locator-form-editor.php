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

		add_filter( 'gmw_form_default_settings', array( $this, 'default_settings' ), 10, 2 );
		add_filter( 'gmw_form_settings', array( $this, 'form_settings' ), 10, 2 );

		// Mashup map form tasks.
		add_filter( 'gmw_members_locator_mashup_map_form_settings', array( $this, 'form_settings' ), 5, 2 );
	}

	/**
	 * Default settings
	 *
	 * @param  array $settings form settings.
	 *
	 * @param  array $form     GEO my WP form.
	 *
	 * @return [type]           [description]
	 */
	public static function default_settings( $settings, $form ) {

		if ( 'members_locator' !== $form['component'] ) {
			return $settings;
		}

		$settings['search_form']['form_template']                 = 'responsive-1';
		$settings['search_form']['xprofile_fields']['fields']     = array();
		$settings['search_form']['xprofile_fields']['date_field'] = '';
		$settings['search_results']['image']['width']             = '100px';
		$settings['search_results']['image']['show_grav']         = 1;
		$settings['search_results']['image']['show_default']      = 1;
		$settings['search_results']['last_active']                = 1;
		$settings['search_results']['friendship_button']          = 1;
		$settings['info_window']['last_active']                   = 1;
		$settings['info_window']['friendship_button']             = 1;

		$settings['search_results']['member_types'] = array(
			'enabled'      => 0,
			'label'        => 'Member Types:',
			'field_output' => '%member_types%',
		);

		$settings['info_window']['member_types'] = $settings['search_results']['member_types'];

		return $settings;
	}

	/**
	 * Form settings.
	 *
	 * @param  [type] $settings array of form fields.
	 *
	 * @param  [type] $form   form object.
	 *
	 * @return [type]         [description]
	 */
	public static function form_settings( $settings, $form ) {

		if ( 'members_locator' !== $form['component'] ) {
			return $settings;
		}

		// No need the settings below for the mashup map form.
		if ( 'members_locator_mashup_map' === $form['slug'] ) {
			return $settings;
		}

		$disabled        = ( ! class_exists( 'Buddypress' ) || ! bp_is_active( 'xprofile' ) ) ? true : false;
		$selected_fields = array();

		// phpcs:disable.
		/*if ( ! empty( $form['search_form']['xprofile_fields']['fields'] ) ) {
			$selected_fields = array_combine( $form['search_form']['xprofile_fields']['fields'], $form['search_form']['xprofile_fields']['fields'] );
		}*/
		// phpcs:enable.

		foreach ( $form['search_form']['xprofile_fields']['fields'] as $xpfield_id ) {
			$selected_fields[ $xpfield_id ] = __( 'Click to load option', 'geo-my-wp' );
		}

		$date_fields = array();

		if ( ! empty( $form['search_form']['xprofile_fields']['date_field'] ) ) {

			global $wpdb;

			$xfield = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT `name` FROM {$wpdb->prefix}bp_xprofile_fields WHERE `id` = %d",
					$form['search_form']['xprofile_fields']['date_field']
				),
			);

			if ( ! empty( $xfield[0] ) ) {
				$date_fields[ $form['search_form']['xprofile_fields']['date_field'] ] = $xfield[0];
			}
		}

		$settings['search_form']['xprofile_fields'] = array(
			'name'             => 'xprofile_fields',
			'type'             => 'fields_group',
			'label'            => esc_html__( 'Xprofile Fields Filters', 'geo-my-wp' ),
			'fields'           => array(
				'fields'     => gmw_get_admin_setting_args(
					array(
						'name'        => 'fields',
						'type'        => 'multiselect',
						'label'       => __( 'Xprofile Fields', 'geo-my-wp' ),
						'placeholder' => __( 'Select Xprofile fields', 'geo-my-wp' ),
						'desc'        => __( 'Select the Xprofile fields that you would like to use as filters in the search form.', 'geo-my-wp' ),
						'options'     => $selected_fields,
						'attributes'  => array(
							'data-gmw_ajax_load_options'          => 'gmw_get_bp_xprofile_fields',
							'data-gmw_ajax_load_options_xprofile' => 'no_date_field',
						),
						'priority'    => 5,
					)
				),
				'date_field' => gmw_get_admin_setting_args(
					array(
						'name'        => 'date_field',
						'type'        => 'select',
						'default'     => '',
						'label'       => __( 'Age Range Field ( date field )', 'geo-my-wp' ),
						'placeholder' => __( 'Select Date Field', 'geo-my-wp' ),
						'desc'        => __( 'Select a date xprofile field that will be used as an "Age range" filter in the search form.', 'geo-my-wp' ),
						'options'     => $date_fields,
						'attributes'  => array(
							'data-gmw_ajax_load_options'          => 'gmw_get_bp_xprofile_fields',
							'data-gmw_ajax_load_options_xprofile' => 'date_field',
						),
						'priority'    => 10,
					)
				),
			),
			'feature_disabled' => $disabled,
			'disabled_message' => __( 'Buddypress xprofile fields component is deactivated. You need to activate in in order to use this feature.', 'geo-my-wp' ),
			'priority'         => 13,
		);

		unset( $settings['search_results']['image']['fields']['no_image_url'] );

		$settings['search_results']['image']['fields']['show_grav'] = gmw_get_admin_setting_args(
			array(
				'name'     => 'show_grav',
				'type'     => 'checkbox',
				'default'  => '',
				'label'    => __( 'Try Gravatar', 'geo-my-wp' ),
				'desc'     => __( 'Look for gravatar if avatar was not found.', 'geo-my-wp' ),
				'cb_label' => __( 'Enable', 'geo-my-wp' ),
				'priority' => 20,
			)
		);

		$settings['search_results']['member_types'] = array(
			'name'            => 'member_types',
			'type'            => 'fields_group',
			'label'           => esc_html__( 'Member Types', 'geo-my-wp' ),
			'fields'          => array(
				'enabled'      => gmw_get_admin_setting_args(
					array(
						'name'       => 'enabled',
						'type'       => 'checkbox',
						'default'    => '',
						'label'      => __( 'Display Member Types', 'geo-my-wp' ),
						'desc'       => __( 'Display the member types of each member.', 'geo-my-wp' ),
						'cb_label'   => __( 'Enable', 'geo-my-wp' ),
						'class'      => 'gmw-options-toggle',
						'attributes' => array(),
						'priority'   => 5,
					)
				),
				'label'        => gmw_get_admin_setting_args(
					array(
						'name'     => 'label',
						'type'     => 'text',
						'default'  => '',
						'label'    => __( 'Label', 'geo-my-wp' ),
						'desc'     => __( 'Enter the label that you wish to display before the member types or leave blank to omit the label.', 'geo-my-wp' ),
						'priority' => 10,
					)
				),
				'field_output' => gmw_get_admin_setting_args(
					array(
						'name'     => 'field_output',
						'type'     => 'text',
						'default'  => '',
						'label'    => __( 'Field Output', 'geo-my-wp' ),
						'desc'     => __( 'Enter the text that you wish to display with the member types. Use the placeholder %member_types% anywhere in the text where you wish to display the member types.', 'geo-my-wp' ),
						'priority' => 15,
					)
				),
			),
			'premium_message' => gmw_get_admin_setting_args(
				array(
					'option_type'     => 'premium_message',
					'option_disabled' => 1,
				),
			),
			'priority'        => 28,
		);

		$settings['info_window']['member_types']             = $settings['search_results']['member_types'];
		$settings['info_window']['member_types']['priority'] = 55;

		$settings['search_results']['image']['fields']['show_default'] = gmw_get_admin_setting_args(
			array(
				'name'     => 'show_default',
				'type'     => 'checkbox',
				'default'  => '',
				'label'    => __( 'Show Default Avatar', 'geo-my-wp' ),
				'desc'     => __( 'Check to display the default avatar ( useually the Mystery Man image ) when no avatar or gravatar were found. Otherwise, uncheck it to display no image.', 'geo-my-wp' ),
				'cb_label' => __( 'Enable', 'geo-my-wp' ),
				'priority' => 25,
			)
		);

		$settings['search_results']['last_active'] = gmw_get_admin_setting_args(
			array(
				'name'      => 'last_active',
				'type'      => 'checkbox',
				'default'   => '',
				'label'     => __( 'Show Last Active', 'geo-my-wp' ),
				'desc'      => __( 'Display the member last active.', 'geo-my-wp' ),
				'cb_label'  => __( 'Enable', 'geo-my-wp' ),
				'priority'  => 35,
				'iw_option' => 1,
			)
		);

		$settings['search_results']['friendship_button'] = gmw_get_admin_setting_args(
			array(
				'name'      => 'friendship_button',
				'type'      => 'checkbox',
				'default'   => '',
				'label'     => __( 'Add Friend Button', 'geo-my-wp' ),
				'desc'      => __( 'Display the Add Friend Button.', 'geo-my-wp' ),
				'cb_label'  => __( 'Enable', 'geo-my-wp' ),
				'priority'  => 40,
				'iw_option' => 1,
			)
		);

		return $settings;
	}
}
new GMW_Members_Locator_Form_Editor();
