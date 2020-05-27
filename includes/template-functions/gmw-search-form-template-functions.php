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
 * Output radius field.
 *
 * @param  [type] $gmw gmw form.
 */
function gmw_search_form_radius( $gmw ) {

	do_action( 'gmw_before_search_form_radius', $gmw );

	echo gmw_get_search_form_radius( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_radius', $gmw );
}

/**
 * GMW Search form units
 *
 * @param  array $gmw the form being used.
 *
 * @return HTML element
 */
function gmw_get_search_form_units( $gmw ) {

	$id     = absint( $gmw['ID'] );
	$url_px = esc_attr( gmw_get_url_prefix() );

	$args = array(
		'id'       => $gmw['ID'],
		'units'    => $gmw['search_form']['units'],
		'mi_label' => __( 'Miles', 'geo-my-wp' ),
		'km_label' => __( 'Kilometers', 'geo-my-wp' ),
	);

	$output = '';

	if ( 'both' === $args['units'] ) {
		$output .= '<div class="gmw-form-field-wrapper gmw-units-field-wrapper">';
	}

	$output .= apply_filters( 'gmw_search_form_units_output', GMW_Search_Form_Helper::units_field( $args ), $gmw );

	if ( 'both' === $args['units'] ) {
		$output .= '</div>';
	}

	return $output;
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
