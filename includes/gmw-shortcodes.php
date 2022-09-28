<?php
/**
 * GEO my WP shortcodes.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
	return;
}

/**
 * GMW main shortcode displaying the form elements
 *
 * @param  array $attr form args/attributes. ( form, search_form, map, search_results... with form ID as the value. ex. form="1" ).
 *
 * @return [type]   [description]
 */
function gmw_shortcode( $attr ) {

	$element       = key( $attr );
	$element_value = $attr[ $element ];

	// abort if no shortcode attribute provided!
	if ( ! in_array( $element, array( 'form', 'search_form', 'search_results', 'map' ), true ) ) {

		gmw_trigger_error( 'GEO my WP form shortcode is missing one of the required shortcode attributes.' );

		return;
	}

	$_GET = apply_filters( 'gmw_modify_get_args', $_GET ); // WPCS: CSRF ok.

	// verify that the element is legit.
	if ( empty( $element_value ) ) {

		gmw_trigger_error( 'Invalid or missing GEO my WP form type.' ); // WPCS : XSS ok.

		return;
	}

	$url_px = gmw_get_url_prefix();

	// if this is results page we get the form ID from URL.
	if ( 'results' === $element_value ) {

		// abort if form was not submitted.
		if ( ! isset( $_GET[ $url_px . 'form' ] ) ) { // WPCS: CSRF ok.

			gmw_trigger_error( 'GEO my WP results page was not submitted.' ); // WPCS : XSS ok.

			return;
		}

		// get the form ID from URL.
		$form_id = absint( $_GET[ $url_px . 'form' ] ); // WPCS: CSRF ok.

		// set the element as results page.
		$element = 'search_results';

		// otherwise, get the form ID from shortcode attribute value.
	} else {

		// verify the form ID.
		$form_id = absint( $attr[ $element ] );
	}

	// get form data.
	$form = gmw_get_form( $form_id );

	// abort if form was not found.
	if ( empty( $form ) ) {

		gmw_trigger_error( 'GEO my WP Form does not exist.' ); // WPCS : XSS ok.

		return;
	}

	// Abort if the add-on this form belongs to is deactivated.
	if ( ! gmw_is_addon_active( $form['addon'] ) ) {

		gmw_trigger_error( 'The add-on which this GEO my WP form belongs to is deactivated.' ); // WPCS : XSS ok.

		return;
	}

	// get current form element ( form, map, results... ).
	$form['current_element'] = key( $attr );

	// set form="results" as search results element.
	if ( isset( $attr['form'] ) && 'results' === $attr['form'] ) {
		$form['current_element'] = 'search_results';
	}

	$form['element'] = $form['current_element']; // Deprecated. Used in AJAX Forms.
	$form['params']  = $attr;

	// do something before everything begines.
	do_action( 'gmw_shortcode_pre_init', $form );
	do_action( 'gmw_element_pre_loaded', 'form', $form );

	ob_start();

	$form_object = GMW_Form_Core::init( $form );

	// Abort if form object doesn't exist.
	if ( empty( $form_object ) || ! is_object( $form_object ) ) {
		return $form_object;
	}

	do_action( 'gmw_element_loaded', 'form', $form_object->form );

	// output only if element allowed.
	if ( $form_object->element_allowed ) {

		// display the form.
		$form_object->output();
	}

	$output_form = ob_get_contents();

	ob_end_clean();

	// For deprecated template files. To be removed.
	if ( 'ajax_forms' === $form_object->form['addon'] && ! empty( $form_object->form['general_settings']['legacy_style'] ) && ! wp_style_is( 'gmw-ajax-forms-legacy-frontend', 'enqueued' ) ) {
		wp_enqueue_style( 'gmw-ajax-forms-legacy-frontend' );
	}

	return $output_form;
}
add_shortcode( 'gmw', 'gmw_shortcode' );

/**
 * GMW Function - get single location information.
 *
 * @param array $args of args.
 */
function gmw_get_address_fields_shortcode( $args ) {

	// default shortcode attributes.
	$attr = shortcode_atts(
		array(
			'location_id' => 0,
			'object_type' => '',
			'object_id'   => 0,
			'fields'      => 'formatted_address',
			'separator'   => ', ',
			'output'      => 'string',
		),
		$args,
		'gmw_get_address_fields'
	);

	$location = gmw_get_address_fields( $attr );

	return $location;
}
add_shortcode( 'gmw_address_fields', 'gmw_get_address_fields_shortcode' );
// add_shortcode( 'gmw_location_address_fields', 'gmw_get_address_fields_shortcode' );

/**
 * GME get location fields shortcode.
 *
 * @uses gmw_get_location_fields();
 *
 * @function in includes/gmw-location-functions.php
 */
add_shortcode( 'gmw_location_fields', 'gmw_get_location_fields' );

/**
 * GMW Function - display hours of operation.
 *
 * @since 3.6.3
 *
 * @author Eyal Fitoussi
 *
 * @param array $atts of args.
 */
function gmw_get_hours_of_operation_shortcode( $atts = array() ) {

	// default shortcode attributes.
	$atts = shortcode_atts(
		array(
			'location_id' => 0,
			'object_type' => 'post',
			'object_id'   => 0,
			'title'       => __( 'Hours of operation', 'geo-my-wp' ),
		),
		$atts,
		'gmw_get_hours_of_operation'
	);

	if ( ! empty( $atts['location_id'] ) ) {

		$location_id = absint( $atts['location_id'] );

		$output['data'] = gmw_get_hours_of_operation( $location_id );

	} elseif ( is_string( $atts['object_type'] ) && ! empty( $atts['object_id'] ) ) {

		$output = gmw_get_hours_of_operation( $atts['object_type'], $atts['object_id'] );
	} else {
		$output = false;
	}

	if ( empty( $output ) ) {
		return;
	}

	$output = apply_filters(
		'gmw_hours_of_operation_shortcode_output',
		array(
			'title'   => ! empty( $atts['title'] ) ? '<span>' . esc_attr( $atts['title'] ) . '</span>' : '',
			'content' => $output,
		),
		$atts
	);

	return implode( '', $output );
}
add_shortcode( 'gmw_hours_of_operation', 'gmw_get_hours_of_operation_shortcode' );
