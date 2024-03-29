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
 * Get the hidden fields for the search form of GEO my WP.
 *
 * @since 4.0
 *
 * @param array $gmw gmw form.
 *
 * @return mixed HTML element.
 */
function gmw_get_search_form_submissionn_fields( $gmw = array() ) {
	return GMW_Search_Form_Helper::submission_fields( $gmw );
}

/**
 * Output the hidden fields for the search form of GEO my WP.
 *
 * @since 4.0
 *
 * @param array $gmw gmw form.
 */
function gmw_search_form_submissionn_fields( $gmw ) {
	echo gmw_get_search_form_submissionn_fields( $gmw );
}

/**
 * Form submission hidden fields
 *
 * @param  array   $gmw       the form being used.
 *
 * @param  boolean $submission true || false to output the submission fields.
 *
 * @return mixed HTML elements of the submission fields
 */
function gmw_get_search_form_submit_button( $gmw = array(), $submission = true ) {

	/*if ( ! isset( $gmw['search_results']['per_page'] ) ) {
		$gmw['search_results']['per_page'] = 10;
	}

	$per_page = current( explode( ',', $gmw['search_results']['per_page'] ) );*/

	$output = '';

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

	$submission = apply_filters( 'gmw_submit_button_submission_fields', $submission, $gmw );

	if ( is_string( $submission ) || true === $submission ) {
		$output .= gmw_get_search_form_submissionn_fields( $gmw );
	}

	return $output;
}

/**
 * Output submit button.
 *
 * @param  array  $gmw   gmw form.
 *
 * @param  string $label custom button label.
 */
function gmw_search_form_submit_button( $gmw = array(), $submission = true ) {

	do_action( 'gmw_before_search_form_submit_button', $gmw );

	echo gmw_get_search_form_submit_button( $gmw, $submission ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_submit_button', $gmw );
}

/**
 * GMW get address field
 *
 * @param  array $gmw the form being used.
 *
 * @return mixed HTML element
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
 * @return mixed HTML element
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
 * @return mixed HTML select dropdown
 *
 * Since 1.0
 */
function gmw_get_search_form_radius( $gmw ) {

	if ( empty( $gmw['search_form']['radius']['usage'] ) || ! in_array( $gmw['search_form']['radius']['usage'], array( 'default', 'dropdown', 'select', 'radio' ), true ) ) {
		return;
	}

	$settings     = $gmw['search_form']['radius'];
	$pl_options   = $gmw['page_load_results'];
	$type         = 'hidden';
	$options      = array();
	$defaut_value = '';

	if ( 'select' === $settings['usage'] || 'radio' === $settings['usage'] ) {

		$type = 'select';

		if ( 'radio' === $settings['usage'] ) {
			$type                         = 'radio';
			$settings['show_options_all'] = '';
		}

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

	// When in page load, add the address to the address field by default.
	if ( ! empty( $pl_options['enabled'] ) && ! empty( $pl_options['address_filter'] ) ) {

		if ( empty( $_GET['action'] ) || ( ! empty( $_GET['action'] ) && 'fs' === $_GET['action'] && ! empty( $_GET['form'] ) && absint( $gmw['ID'] ) !== absint( $_GET['form'] ) ) ) {

			// get the addres value.
			$defaut_value = sanitize_text_field( $pl_options['radius'] );
		}
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
 * @return mixed HTML element
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
 * Output custom fields filters in the search form.
 *
 * This function requires the Premium Settings extension.
 *
 * @since 4.0 ( function moved from the Premium Settings function );
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_custom_fields( $gmw ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_custom_fields' ) ) {
		return;
	}

	// Remove filter that adds the custom fields filter dynamically into the form.
	remove_action( 'gmw_search_form_before_distance', 'gmw_append_custom_fields_to_search_form', 10, 1 );

	do_action( 'gmw_before_search_form_custom_fields', $gmw );

	echo gmw_get_search_form_custom_fields( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_custom_fields', $gmw );
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
	if ( ! function_exists( 'gmw_get_search_form_reset_button' ) || empty( $gmw['search_form']['reset_button']['enabled'] ) ) {
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
 * @return mixed HTML element
 */
function gmw_get_search_form_toggle_button( $gmw = array(), $args = array() ) {

	$args = apply_filters( 'gmw_search_form_toggle_button_args', $args, $gmw );
	$args = wp_parse_args(
		$args,
		array(
			'id'          => $gmw['ID'],
			'slug'        => 'toggle-button',
			'type'        => 'link',
			'name'        => '',
			'class'       => 'gmw-form-button',
			'inner_label' => __( 'Filter', 'geo-my-wp' ),
			'attributes'  => array(
				'data-type'     => 'toggle',
				'data-element'  => '.gmw-modal-box-wrapper',
				'data-duration' => 'fast',
			),
		)
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Get the toggle that opens the modal box.
 *
 * @since 4.0.
 *
 * @param  array  $gmw [description].
 *
 * @return [type]      [description]
 */
function gmw_get_search_form_modal_box_toggle( $gmw = array() ) {

	if ( empty( $gmw['search_form']['filters_modal']['enabled'] ) ) {
		return;
	}

	$args = array(
		'inner_label' => $gmw['search_form']['filters_modal']['toggle_label'],
	);

	return gmw_get_search_form_toggle_button( $gmw, $args ); // WPCS: XSS ok.
}


/**
 * Output the toggle that opens the additional fields wrapper.
 *
 * @since 4.0.
 *
 * @param  array  $gmw [description].
 *
 * @return [type]      [description]
 */
function gmw_search_form_modal_box_toggle( $gmw = array() ) {
	echo gmw_get_search_form_modal_box_toggle( $gmw ); // WPCS: XSS ok.
}

/**
 * Get the modal box element.
 *
 * This function needs to be used twice in the form, once where the wrapper of the modal box begins
 *
 * and again where it ends.
 *
 * @author Eyal Fitoussi.
 *
 * @since 4.0
 *
 * @param  string $tag  open || close.
 *
 * @param  array  $gmw  GEO my WP form.
 *
 * @return [type]       [description]
 */
function gmw_get_search_form_modal_box( $tag = 'open', $gmw = array() ) {

	if ( empty( $gmw['search_form']['filters_modal']['enabled'] ) ) {
		return;
	}

	$type = ! empty( $gmw['search_form']['filters_modal']['modal_type'] ) ? $gmw['search_form']['filters_modal']['modal_type'] : 'popup';

	if ( 'close' === $tag ) {

		$output = '</div></div></div>';

	} else {

		$title = ! empty( $gmw['search_form']['filters_modal']['modal_title'] ) ? $gmw['search_form']['filters_modal']['modal_title'] : '';

		$output  = '<div class="gmw-modal-box-wrapper" data-type="' . esc_attr( $type ) . '">';
		$output .= '<div class="gmw-modal-box-inner">';

		$output .= '<div class="gmw-modal-box-header">';
		$output .= '<span></span>';
		$output .= '<span class="gmw-modal-box-title">' . esc_html( $title ) . '</span>';
		$output .= '<span class="gmw-close-filters-button gmw-icon-cancel-circled"></span>';
		$output .= '</div>';

		$output .= '<div class="gmw-modal-box-content gmw-flexed-wrapper">';
	}

	return $output;
}

/**
 * Output the modal box element.
 *
 * @author Eyal Fitoussi.
 *
 * @since 4.0
 *
 * @param  string $tag  open || close.
 *
 * @param  array  $gmw  GEO my WP form.
 *
 * @return [type]       [description]
 */
function gmw_search_form_modal_box( $tag = 'open', $gmw = array() ) {
	echo gmw_get_search_form_modal_box( $tag, $gmw );
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

/**
 * Search form user role filter.
 *
 * Requires the Premium Settings extension.
 *
 * since 4.0
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_user_role_field( $gmw = array() ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_user_role_field' ) ) {
		return;
	}

	do_action( 'gmw_before_search_form_user_role_field', $gmw );

	echo gmw_get_search_form_user_role_field( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_user_role_field', $gmw );
}
