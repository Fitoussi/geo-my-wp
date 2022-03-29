<?php
/**
 * GEO my WP - search form template functions.
 *
 * @package geo-my-wp.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get search form field.
 *
 * @param  array $args field args.
 *
 * @param  array $gmw  gmw form object.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 *
 * @return [type]       [description]
 */
function gmw_get_form_field( $args = array(), $gmw = array() ) {
	return GMW_Search_Form_Helper::get_field( $args, $gmw );
}

/**
 * Output search form field.
 *
 * @param  array $args field args.
 *
 * @param  array $gmw  gmw form object.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_form_field( $args = array(), $gmw = array() ) {
	echo gmw_get_form_field( $args, $gmw ); // WPCS: XSS ok.
}

/**
 * Form submission hidden fields
 *
 * @param  array  $gmw       the form being used.
 *
 * @param  string $dep_label the default value of the submit button ( deprecated ).
 *
 * @return mix HTML elements of the submission fields
 */
function gmw_get_search_form_submit_button( $gmw = array(), $dep_label = '' ) {

	if ( ! isset( $gmw['search_results']['per_page'] ) ) {
		$gmw['search_results']['per_page'] = 10;
	}

	$per_page = absint( current( explode( ',', $gmw['search_results']['per_page'] ) ) );

	if ( ! empty( $gmw['search_form']['submit_button']['label'] ) ) {
		
		//$label = ! empty( $gmw['search_form']['submit_button']['label'] ) ? $gmw['search_form']['submit_button']['label'] : __( 'Submit', 'geo-my-wp' );
		$label = $gmw['search_form']['submit_button']['label'];
		$args  = array(
			'id'    => $gmw['ID'],
			'slug'  => 'submit',
			'name'  => 'submit',
			'type'  => 'submit',
			'value' => $label,
		);

		$args = apply_filters( 'gmw_search_form_submit_button_args', $args ); // Deprecated. To be removed.

		// Support previous versions of the filter above.
		if ( ! empty( $args['label'] ) ) {

			$args['value'] = $args['label'];

			unset( $args['label'] );
		}

		// false argument is deprected. Temporary there to support old versions of search forms templates.
		$output  = apply_filters( 'gmw_form_submit_button', gmw_get_form_field( $args, $gmw ), $gmw, false );
	}

	$output .= GMW_Search_Form_Helper::submission_fields( $gmw['ID'], $per_page );

	return $output;
}

/**
 * Output submit button.
 *
 * @param  array  $gmw   gmw form.
 *
 * @param  string $label custom button label.
 */
function gmw_search_form_submit_button( $gmw = array(), $label = '' ) {

	do_action( 'gmw_before_search_form_submit_button', $gmw );

	echo gmw_get_search_form_submit_button( $gmw, $label ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_submit_button', $gmw );
}

/**
 * GMW get address field
 *
 * @param  array $gmw the form being used.
 *
 * @return mix        HTML element
 *
 * @since 1.0
 */
function gmw_get_search_form_address_field( $gmw ) {

	$settings   = $gmw['search_form']['address_field'];
	$pl_options = $gmw['page_load_results'];
	$value      = '';

	// When in page load, add the address to the address field by default.
	if ( ! empty( $pl_options['enabled'] ) ) {

		if ( empty( $_GET['action'] ) || ( ! empty( $_GET['action'] ) && 'fs' === $_GET['action'] && ! empty( $_GET['form'] ) && absint( $gmw['ID'] ) !== absint( $_GET['form'] ) ) ) {

			// When using the user's current location.
			if ( ! empty( $pl_options['user_location'] ) ) {

				$user_location = gmw_get_user_current_location();

				if ( ! empty( $user_location ) ) {
					$value = ! empty( $user_location->address ) ? $user_location->address : $user_location->formatted_address;
				}

				// When address filter is set.
			} elseif ( ! empty( $pl_options['address_filter'] ) ) {

				// get the addres value.
				$value = sanitize_text_field( $pl_options['address_filter'] );
			}
		}
	}

	$args = array(
		'id'              => absint( $gmw['ID'] ),
		'slug'            => 'address',
		'name'            => 'address',
		'is_array'        => true,
		'type'            => 'address',
		'label'           => ! empty( $settings['label'] ) ? $settings['label'] : '',
		'placeholder'     => isset( $settings['placeholder'] ) ? $settings['placeholder'] : '',
		'required'        => ! empty( $settings['required'] ) ? 1 : 0,
		'value'           => $value,
		'class'           => ' gmw-full-address',
		'wrapper_class'   => ! empty( $settings['locator'] ) ? 'gmw-locator-enabled' : '',
		'additional_args' => array(
			'locator_button' => ! empty( $settings['locator'] ) ? 1 : 0,
			'locator_submit' => ! empty( $settings['locator_submit'] ) ? 1 : 0,
			'icon'           => 'gmw-icon-target-light',
		),
		'inner_element'   => true,
	);

	if ( ! empty( $settings['address_autocomplete'] ) ) {
		$args['class'] .= ' gmw-address-autocomplete';
	}

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output address field.
 *
 * @param  array   $gmw   gmw form.
 *
 * @param  integer $id    ID attribute // deprecated.
 *
 * @param  boolean $class class attribute // deprecated.
 */
function gmw_search_form_address_field( $gmw = array(), $id = 0, $class = false ) {

	do_action( 'gmw_before_search_form_address_field', $gmw );

	if ( empty( $gmw['search_form']['address_field']['usage'] ) || 'single' === $gmw['search_form']['address_field']['usage'] ) {

		echo gmw_get_search_form_address_field( $gmw ); // WPCS: XSS ok.

	} elseif ( function_exists( 'gmw_get_search_form_address_fields' ) ) {

		echo gmw_get_search_form_address_fields( $gmw ); // WPCS: XSS ok.
	}

	do_action( 'gmw_after_search_form_address_field', $gmw );
}

/**
 * Output address fields.
 *
 * Requires the Premium Settings extension.
 *
 * @since 4.0 ( function moved from the Premium Settings extension ).
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_address_fields( $gmw ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_keywords_field' ) ) {
		return;
	}

	do_action( 'gmw_before_search_form_address_fields', $gmw );

	echo gmw_get_search_form_address_fields( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_address_fields', $gmw );
}

/**
 * Get locator button
 *
 * @param  [type] $gmw form being processed.
 *
 * @return HTML element
 */
function gmw_get_search_form_locator_button( $gmw ) {

	// abort if disabled.
	if ( empty( $gmw['search_form']['locator_button']['usage'] ) || 'disabled' === $gmw['search_form']['locator_button']['usage'] ) {
		return;
	}

	$settings     = $gmw['search_form']['locator_button'];
	$button_class = 'text' === $settings['usage'] ? 'gmw-form-button' : '';
	$args         = array(
		'id'              => $gmw['ID'],
		'slug'            => 'locator-button',
		'name'            => 'locator_button',
		'type'            => 'locator_button',
		'label'           => '',
		'value'           => '',
		'inner_class'     => ' gmw-locator-inner locator-' . $settings['usage'] . ' ' . $button_class,
		'wrapper_class'   => 'locator-' . $settings['usage'] . ' gmw-locator-button-wrapper ' . $settings['usage'], // gmw-locator-button-wrapper and $settings['usage'] are depreacated.
		'additional_args' => array(
			'usage'       => $settings['usage'],
			'image'       => isset( $settings['image'] ) ? $settings['image'] : 'blue-dot.png',
			'form_submit' => ! empty( $settings['locator_submit'] ) ? 1 : 0,
			'label'       => isset( $settings['text'] ) ? $settings['text'] : '',
			'image_url'   => ! empty( $settings['url'] ) ? $settings['url'] : GMW_IMAGES . '/locator-images/locate-me-blue.png',
		),
		'inner_element'   => true,
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output locator button.
 *
 * @param  array   $gmw   gmw form.
 *
 * @param  boolean $class class attr - deprecated.
 */
function gmw_search_form_locator_button( $gmw = array(), $class = false ) {

	do_action( 'gmw_before_search_form_locator_button', $gmw );

	echo gmw_get_search_form_locator_button( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_locator_button', $gmw );
}

/**
 * Search form radius field
 *
 * @param array $gmw the form being processed.
 *
 * @return HTML select dropdown
 *
 * Since 1.0
 */
function gmw_get_search_form_radius( $gmw ) {

	if ( empty( $gmw['search_form']['radius']['usage'] ) || ! in_array( $gmw['search_form']['radius']['usage'], array( 'default', 'dropdown', 'select', 'radio' ), true ) ) {
		return;
	}

	$settings     = $gmw['search_form']['radius'];
	$type         = 'hidden';
	$options      = array();
	$defaut_value = '';

	if ( 'select' === $settings['usage'] ) {

		$type = 'select';

		if ( ! empty( $settings['options'] ) ) {

			$options = gmw_get_form_field_options( $settings['options'] );

		} else {

			$options = array(
				'200' => 'Radius',
				'5'   => '5',
				'10'  => '10',
				'25'  => '25',
				'50'  => '50',
				'100' => '100',
			);
		}
	} else {

		$defaut_value = ! empty( $settings['default_value'] ) ? $settings['default_value'] : '';
	}

	$args = array(
		'id'               => $gmw['ID'],
		'slug'             => 'distance',
		'name'             => 'distance',
		'type'             => $type,
		'label'            => ! empty( $settings['label'] ) ? $settings['label'] : '',
		'required'         => ! empty( $settings['required'] ) ? $settings['required'] : '',
		'value'            => $defaut_value,
		'options'          => $options,
		'show_options_all' => isset( $settings['show_options_all'] ) ? $settings['show_options_all'] : '',
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output radius field in search form.
 *
 * This field will output either the slider from the premium settings or the normal radius field.
 *
 * @param  [type] $gmw gmw form.
 */
function gmw_search_form_radius( $gmw = array() ) {

	do_action( 'gmw_before_search_form_radius', $gmw );

	if ( ! empty( $gmw['search_form']['radius']['usage'] ) && 'slider' === $gmw['search_form']['radius']['usage'] && function_exists( 'gmw_get_search_form_radius_slider' ) ) {
		echo gmw_get_search_form_radius_slider( $gmw ); // WPCS: XSS ok.
	} else {
		echo gmw_get_search_form_radius( $gmw ); // WPCS: XSS ok.
	}

	do_action( 'gmw_after_search_form_radius', $gmw );
}

/**
 * Output radius field in search form.
 *
 * This function will output the normal radius field only.
 *
 * @since 4.0.
 *
 * @param  [type] $gmw gmw form.
 */
function gmw_search_form_radius_field( $gmw = array() ) {

	do_action( 'gmw_before_search_form_radius_field', $gmw );

	echo gmw_get_search_form_radius( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_radius_field', $gmw );
}

/**
 * Output radius slider in a search form.
 *
 * Requires the Premium Settings extension.
 *
 * @since 4.0 ( function moved from the Premium Settings extension ).
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_radius_slider( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_radius_slider' ) ) {
		return;
	}

	do_action( 'gmw_before_search_form_radius_slider', $gmw );

	echo gmw_get_search_form_radius_slider( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_radius_slider', $gmw );
}

/**
 * GMW Search form units
 *
 * @param  array $gmw the form being used.
 *
 * @return HTML element
 */
function gmw_get_search_form_units( $gmw ) {

	$settings = $gmw['search_form']['units'];

	// Make sure settings are properly set ( to support old version of form in case were not properly updated with the upgrade to v4.0. ).
	if ( empty( $settings ) || ! is_array( $settings ) ) {
		$settings = array(
			'options' => 'imperial',
		);
	}

	$settings['options'] = ! empty( $settings['options'] ) ? $settings['options'] : 'imperial';
	$type                = 'hidden';
	$options             = array();
	$defaut_value        = '';

	if ( 'both' === $settings['options'] ) {

		$type    = 'select';
		$options = array(
			'imperial' => 'Miles',
			'metric'   => 'Kilometers',
		);

	} else {

		$defaut_value = $settings['options'];
	}

	$args = array(
		'id'      => $gmw['ID'],
		'slug'    => 'units',
		'name'    => 'units',
		'type'    => $type,
		'label'   => ! empty( $settings['label'] ) ? $settings['label'] : '',
		'options' => $options,
		'value'   => $defaut_value,
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output units field.
 *
 * @param  array   $gmw   gmw form.
 *
 * @param  boolean $class class attr - deprecated.
 */
function gmw_search_form_units( $gmw = array(), $class = false ) {

	do_action( 'gmw_before_search_form_units', $gmw );

	echo gmw_get_search_form_units( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_units', $gmw );
}

/**
 * Output keywords field.
 *
 * Requires the Premium Settings extension.
 *
 * @since 4.0 ( function moved from the Premium Settings extension ).
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_keywords_field( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_keywords_field' ) ) {
		return;
	}

	// Prevent adding the keywords field using a hook.
	// This method was used in  previous version of the plugin and template files.
	remove_action( 'gmw_before_search_form_address_field', 'gmw_append_keywords_field_to_search_form' );

	do_action( 'gmw_before_search_form_keywords_field', $gmw );

	// Function exists in the Premium Settings extension.
	echo gmw_get_search_form_keywords_field( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_keywords_field', $gmw );
}

/**
 * Output reset button.
 *
 * @param  array $gmw   gmw form.
 *
 * This function requires the Premium Settings extension.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_search_form_reset_button( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_reset_button' ) ) {
		return;
	}

	do_action( 'gmw_before_search_form_reset_button', $gmw );

	echo gmw_get_search_form_reset_button( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_reset_button', $gmw );
}

/**
 * GMW Additional Filters button.
 *
 * @param  array $gmw the form being used.
 *
 * @param  array $args field arguments.
 *
 * @since 4.0
 *
 * @return HTML element
 */
function gmw_get_search_form_toggle_button( $gmw = array(), $args = array() ) {

	$args = array(
		'id'          => $gmw['ID'],
		'slug'        => 'toggle-button',
		'type'        => 'link',
		'name'        => '',
		'class'       => 'gmw-form-button',
		'inner_label' => __( 'Filter', 'geo-my-wp' ),
		'attributes'  => array(
			'data-type'     => 'toggle',
			'data-element'  => '.gmw-additional-filters-wrapper',
			'data-duration' => 'fast',

		),
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output Additional Filters button.
 *
 * @param  array $gmw   gmw form.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_search_form_toggle_button( $gmw = array() ) {

	do_action( 'gmw_before_search_form_toggle_button', $gmw );

	echo gmw_get_search_form_toggle_button( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_toggle_button', $gmw );
}

/**
 * Search form group types filter.
 *
 * @since 4.0.
 *
 * This function was originall in the Groups Locator extension.
 *
 * Requires the Groups Locator extension.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_bp_group_types_field( $gmw = array() ) {

	// This function lives in the Groups Locator extension.
	if ( ! function_exists( 'gmw_get_search_form_bp_group_types_field' ) ) {
		return;
	}

	do_action( 'gmw_before_search_form_bp_group_types_field', $gmw );

	echo gmw_get_search_form_bp_group_types_field( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_bp_group_types_field', $gmw );
}
