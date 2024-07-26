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
function gmw_shortcode( $attr = array() ) {

	// verify that form element passed via shortcode attribute.
	if ( empty( $attr ) || ! is_array( $attr ) ) {

		ob_start();

		gmw_trigger_error( 'You must provide shortcode attributes to [gmw] shortcode.' ); // WPCS : XSS ok.

		$error_message = ob_get_contents();

		ob_end_clean();

		return $error_message;
	}

	$element       = key( $attr );
	$element_value = $attr[ $element ];

	// abort if no shortcode attribute provided!
	if ( ! in_array( $element, array( 'form', 'search_form', 'search_results', 'map' ), true ) ) {

		ob_start();

		gmw_trigger_error( '[gmw] shortcode is missing one of the required shortcode attributes.' );

		$error_message = ob_get_contents();

		ob_end_clean();

		return $error_message;
	}

	$_GET = apply_filters( 'gmw_modify_get_args', $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

	// verify that form element passed via shortcode attribute.
	if ( empty( $element_value ) ) {

		ob_start();

		gmw_trigger_error( 'Invalid or missing GEO my WP form type.' ); // phpcs:ignore: XSS ok.

		$error_message = ob_get_contents();

		ob_end_clean();

		return $error_message;
	}

	$url_px = gmw_get_url_prefix();

	// if this is results page we get the form ID from URL.
	if ( 'results' === $element_value ) {

		// abort if form was not submitted.
		if ( ! isset( $_GET[ $url_px . 'form' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			return;
			// phpcs:disable.
			/*ob_start();

			gmw_trigger_error( 'GEO my WP results page was not submitted.' ); // WPCS : XSS ok.

			$error_message = ob_get_contents();

			ob_end_clean();

			return $error_message;*/
			// phpcs:enable.
		}

		// get the form ID from URL.
		$form_id = absint( $_GET[ $url_px . 'form' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

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
	if ( empty( $form['ID'] ) ) {
		return;
	}

	// Abort if the add-on this form belongs to is deactivated.
	if ( ! gmw_is_addon_active( $form['addon'] ) || ! gmw_is_addon_active( $form['component'] ) ) {

		$addon = ! gmw_is_addon_active( $form['addon'] ) ? $form['addon'] : $form['component'];

		ob_start();

		gmw_trigger_error(
			sprintf(
				/* translators: %s: addon's name. */
				__( 'The add-on %s which this GEO my WP form belongs to is deactivated.', 'geo-my-wp' ),
				str_replace( '_', ' ', $form['addon'] )
			)
		); // WPCS : XSS ok.

		$error_message = ob_get_contents();

		ob_end_clean();

		return $error_message;
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

		// This resets the posts query and it is here temporary.
		// This should really be added after the loop in to the various posts locator search results template files across the different add-ons.
		if ( function_exists( 'wp_reset_query' ) && 'post' === $form_object->form['object_type'] ) {
			wp_reset_postdata();
		}
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
// phpcs:ignore.
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
