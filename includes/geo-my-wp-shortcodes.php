<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

//look for the form init class
if ( !class_exists( 'GMW_Form_Init' ) )
	return;

/**
 * gmw_shortcode
 *
 * GEO my WP's main shortcode
 * @param unknown_type $atts
 */
function gmw_shortcode( $atts ) {

	//abort if no attributes found
	if ( empty( $atts ) )
		return;

	//get the first attribute of the shortcode.
	//the first attribute must be the element ( form, search_form, map or search_results ).
	$element = key( $atts );

	//get the form ID
	$formId  = $atts[$element];

	//make sure the element is lagit
	if ( empty( $formId ) || !in_array( $element, array( 'search_form', 'map', 'search_results', 'form' ) ) )
		return;

	$gmw_prefix = 'gmw_';
	$gmw_prefix = apply_filters( 'gmw_form_url_prefix', $gmw_prefix, $atts, $element );

	//if this is results page we get the formId from URL
	if ( $formId == 'results' ) {

		if ( empty( $_GET['action'] ) || $_GET['action'] != $gmw_prefix.'post' || empty( $_GET[$gmw_prefix.'form'] ) )
			return;

		$formId  = absint( $_GET[$gmw_prefix.'form'] );
		$element = 'results_page';

	} else {
	 	$formId = absint( $atts[$element] );
	}

	//get the forms from database
	$forms = get_option( 'gmw_forms' );

	//look for the form based on the form ID. Abort if no form was found
	if ( empty( $forms[$formId] ) )
		return;

	//get the current form
	$form = $forms[$formId];

	//make sure the add-on of the form is activated
	if ( !GEO_my_WP::gmw_check_addon( $form['addon'] ) )
		return;
		
	$form['element_triggered'] = $element;
	$form['params'] 		   = $atts;
	$form['url_px']			   = $gmw_prefix;
	$form_output  			   = new GMW_Form_Init( $form );
	
	ob_start();

	$form_output->display();

	$output_string = ob_get_contents();

	ob_end_clean();

	return $output_string;
}
add_shortcode( 'gmw', 'gmw_shortcode' );