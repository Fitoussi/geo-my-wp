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
 * @param  array  $args field args.
 *
 * @param  array  $gmw  gmw form object.
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
 * @param  array  $args field args.
 *
 * @param  array  $gmw  gmw form object.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_form_field( $args = array(), $gmw = array() ) {
	echo gmw_get_form_field( $args, $gmw );
}

/**
 * Form submission hidden fields
 *
 * @param  array  $gmw   the form being used.
 *
 * @param  string $label the default value of the submit button.
 *
 * @return mix HTML elements of the submission fields
 */
function gmw_get_search_form_submit_button( $gmw = array(), $label = '' ) {

	$id = absint( $gmw['ID'] );

	if ( ! isset( $gmw['search_results']['per_page'] ) ) {
		$gmw['search_results']['per_page'] = 10;
	}

	$per_page = absint( current( explode( ',', $gmw['search_results']['per_page'] ) ) );

	$args = array(
		'id'    => $id,
		'label' => ! empty( $label ) ? $label : __( 'Submit', 'geo-my-wp' ),
	);

	$output  = '';
	$output .= '<div class="gmw-form-field-wrapper gmw-submit-field-wrapper">';
	// false argument is deprected. Temporary there to support old versions of search forms templates.
	$output .= apply_filters( 'gmw_form_submit_button', GMW_Search_Form_Helper::submit_button( $args ), $gmw, false );
	$output .= '</div>';
	$output .= GMW_Search_Form_Helper::submission_fields( $id, $per_page );

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
		'id'                   => absint( $gmw['ID'] ),
		'mandatory'            => ! empty( $settings['mandatory'] ) ? 1 : 0,
		'placeholder'          => isset( $settings['placeholder'] ) ? $settings['placeholder'] : '',
		'address_autocomplete' => ! empty( $settings['address_autocomplete'] ) ? 1 : 0,
		'locator_button'       => ! empty( $settings['locator'] ) ? 1 : 0,
		'locator_submit'       => ! empty( $settings['locator_submit'] ) ? 1 : 0,
		'value'                => $value,
	);

	$label_enabled = false;
	$label_css     = '';

	if ( ! empty( $settings['label'] ) ) {
		$label_css     = 'gmw-field-label-enabled';
		$label_enabled = true;
	}

	$output = '<div class="gmw-form-field-wrapper gmw-address-field-wrapper ' . $label_css . '">';

	if ( $label_enabled ) {
		$output .= '<label class="gmw-field-label" for="gmw-address-field-' . $args['id'] . '">' . esc_html( $settings['label'] ) . '</label>';
	}

	$output .= GMW_Search_Form_Helper::address_field( $args );

	$output .= '</div>';

	return apply_filters( 'gmw_search_form_address_field', $output, $gmw, $args );
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

	echo gmw_get_search_form_address_field( $gmw ); // WPCS: XSS ok.

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
 * Get locator button
 *
 * @param  [type] $gmw form being processed.
 *
 * @return HTML element
 */
function gmw_get_search_form_locator_button( $gmw ) {

	// abort if disabled.
	if ( empty( $gmw['search_form']['locator'] ) || 'disabled' === $gmw['search_form']['locator'] ) {
		return;
	}

	$args = array(
		'id'          => $gmw['ID'],
		'usage'       => $gmw['search_form']['locator'],
		'image'       => isset( $gmw['search_form']['locator_image'] ) ? $gmw['search_form']['locator_image'] : 'locate-me-blue.png',
		'form_submit' => ! empty( $gmw['search_form']['locator_submit'] ) ? 1 : 0,
		'label'       => isset( $gmw['search_form']['locator_text'] ) ? $gmw['search_form']['locator_text'] : '',
	);

	$output  = '<div class="gmw-form-field-wrapper gmw-locator-button-wrapper ' . esc_attr( $gmw['search_form']['locator'] ) . '">';
	$output .= apply_filters( 'gmw_search_form_locator_button', GMW_Search_Form_Helper::locator_button( $args ), $gmw );
	$output .= '</div>';

	return $output;
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

	if ( 'both' === $gmw['search_form']['units'] ) {
		$label = __( 'Within', 'geo-my-wp' );
	} else {
		$label = ( 'imperial' === $gmw['search_form']['units'] ) ? __( 'Miles', 'geo-my-wp' ) : __( 'Kilometers', 'geo-my-wp' );
	}

	$args = array(
		'id'            => $gmw['ID'],
		'label'         => $label,
		'default_value' => '',
		'options'       => $gmw['search_form']['radius'],
	);

	$output  = '<div class="gmw-form-field-wrapper gmw-distance-field-wrapper">';
	$output .= apply_filters( 'gmw_search_form_radius_output', GMW_Search_Form_Helper::radius_field( $args ), $gmw );
	$output .= '</div>';

	return apply_filters( 'gmw_radius_dropdown_output', $output, $gmw );
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
		echo gmw_get_search_form_radius_slider( $gmw );
	} else {
		echo gmw_get_search_form_radius( $gmw );
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

	$settings            = $gmw['search_form']['units'];
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

	$args   = array(
		'id'           => $gmw['ID'],
		'slug'         => 'units',
		'name'         => 'units',
		'type'         => $type,
		'label'        => ! empty( $settings['label'] ) ? $settings['label'] : '',
		'options'      => $options,
		'value'        => $defaut_value,
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
 * @param  array  $gmw   gmw form.
 *
 * This function requires the Premium Settings extension.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 * 
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
 * @param  array  $gmw   gmw form.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 * 
 */
function gmw_search_form_toggle_button( $gmw = array() ) {

	do_action( 'gmw_before_search_form_toggle_button', $gmw );

	echo gmw_get_search_form_toggle_button( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_toggle_button', $gmw );
}
