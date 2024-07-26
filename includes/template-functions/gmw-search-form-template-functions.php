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
	echo gmw_get_form_field( $args, $gmw ); // phpcs:ignore: XSS ok.
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
	echo gmw_get_search_form_submissionn_fields( $gmw ); // phpcs:ignore: XSS ok.
}

/**
 * Form submission hidden fields
 *
 * @param  array   $gmw       the form being used.
 *
 * @param  boolean $submission true || false to output the hidden submission fields.
 *
 * @return mixed HTML elements of the submission fields
 */
function gmw_get_search_form_submit_button( $gmw = array(), $submission = true ) {

	// phpcs:disable.
	/*if ( ! isset( $gmw['search_results']['per_page'] ) ) {
		$gmw['search_results']['per_page'] = 10;
	}

	$per_page = current( explode( ',', $gmw['search_results']['per_page'] ) );*/
	// phpcs:enable.

	$output = '';

	// phpcs:ignore.
	//$label = ! empty( $gmw['search_form']['submit_button']['label'] ) ? $gmw['search_form']['submit_button']['label'] : __( 'Submit', 'geo-my-wp' );

	$label = $gmw['search_form']['submit_button']['label'];
	$args  = array(
		'id'    => $gmw['ID'],
		'slug'  => 'submit',
		'name'  => 'submit',
		'type'  => 'submit',
		'value' => $label,
	);

	if ( empty( $gmw['search_form']['submit_button']['label'] ) ) {
		$args['wrapper_class'] = ' gmw-is-hidden ';
	}

	$args = apply_filters( 'gmw_search_form_submit_button_args', $args ); // Deprecated. To be removed.

	// Support previous versions of the filter above.
	if ( ! empty( $args['label'] ) ) {

		$args['value'] = $args['label'];

		unset( $args['label'] );
	}

	// false argument is deprected. Temporary there to support old versions of search forms templates.
	$output     = apply_filters( 'gmw_form_submit_button', gmw_get_form_field( $args, $gmw ), $gmw, false );
	$submission = apply_filters( 'gmw_submit_button_submission_fields', $submission, $gmw );

	if ( is_string( $submission ) || true === $submission ) {
		$output .= gmw_get_search_form_submissionn_fields( $gmw );
	}

	return $output;
}

/**
 * Output submit button.
 *
 * @param  array   $gmw gmw form.
 *
 * @param  boolean $submission true || false to output the hidden submission fields.
 */
function gmw_search_form_submit_button( $gmw = array(), $submission = true ) {

	do_action( 'gmw_before_search_form_submit_button', $gmw );

	echo gmw_get_search_form_submit_button( $gmw, $submission ); // phpcs:ignore: XSS ok.

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

		if ( empty( $_GET['action'] ) || ( ! empty( $_GET['action'] ) && 'fs' === $_GET['action'] && ! empty( $_GET['form'] ) && absint( $gmw['ID'] ) !== absint( $_GET['form'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

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

		echo gmw_get_search_form_address_field( $gmw ); // phpcs:ignore: XSS ok.

	} elseif ( function_exists( 'gmw_get_search_form_address_fields' ) ) {

		echo gmw_get_search_form_address_fields( $gmw ); // phpcs:ignore: XSS ok.
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

	echo gmw_get_search_form_address_fields( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_locator_button( $gmw ); // phpcs:ignore: XSS ok.

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

	// When in page load, add the radius value to the address field by default.
	/*if ( ! empty( $gmw['page_load_action'] ) && ! empty( $pl_options['enabled'] ) && ! empty( $pl_options['address_filter'] ) ) {

		if ( empty( $_GET['action'] ) || ( ! empty( $_GET['action'] ) && 'fs' === $_GET['action'] && ! empty( $_GET['form'] ) && absint( $gmw['ID'] ) !== absint( $_GET['form'] ) ) ) { // phpcs:ignore: CSRF ok.

			// get the radius value.
			$defaut_value = sanitize_text_field( $pl_options['radius'] );
		}
	}*/

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
		echo gmw_get_search_form_radius_slider( $gmw ); // phpcs:ignore: XSS ok.
	} else {
		echo gmw_get_search_form_radius( $gmw ); // phpcs:ignore: XSS ok.
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

	echo gmw_get_search_form_radius( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_radius_slider( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_units( $gmw ); // phpcs:ignore: XSS ok.

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
	echo gmw_get_search_form_keywords_field( $gmw ); // phpcs:ignore: XSS ok.

	do_action( 'gmw_after_search_form_keywords_field', $gmw );
}

if ( ! function_exists( 'gmw_get_search_form_custom_field' ) ) {

	/**
	 * Get single search form custom field element.
	 *
	 * @param  array $custom_field field arguments.
	 *
	 * @param  array $gmw  gmw form.
	 *
	 * @since 4.4 ( moved from the Premium Settings extension ).
	 *
	 * @return [type]       [description]
	 */
	function gmw_get_search_form_custom_field( $custom_field = array(), $gmw = array() ) {

		if ( ! empty( $custom_field['disable_field'] ) || 'pre_defined' === $custom_field['usage'] ) {
			return;
		}

		$fields          = array();
		$options         = array();
		$multiple_fields = false;
		$is_array        = false;

		// Get field options.
		if ( in_array( $custom_field['usage'], array( 'select', 'multiselect', 'radio', 'checkbox', 'checkboxes' ), true ) && ! empty( $custom_field['options'] ) ) {

			// Explode options from textarea value only if not already an array.
			if ( is_array( $custom_field['options'] ) ) {

				$options = $custom_field['options'];

			} else {

				$custom_field['options'] = explode( PHP_EOL, $custom_field['options'] );

				foreach ( $custom_field['options'] as $option ) {

					if ( empty( trim( $option ) ) ) {
						continue;
					}

					$option          = explode( ' : ', $option );
					$val             = trim( $option[0] );
					$options[ $val ] = ! empty( $option[1] ) ? trim( $option[1] ) : $val;
				}
			}
		}

		$acf_tax_field = array();

		// Load field options from ACF settings when available.
		if ( isset( $options['{acf_field_options}'] ) ) {

			if ( function_exists( 'acf_get_field' ) ) {

				$acf_field = acf_get_field( $custom_field['name'] );

				if ( 'taxonomy' === $acf_field['type'] ) {

					$acf_tax_field = array(
						'taxonomy' => $acf_field['taxonomy'],
					);

				} elseif ( ! empty( $acf_field['choices'] ) ) {

					if ( 1 === count( $options ) ) {

						$options = $acf_field['choices'];

					} else {

						$new_options = array();

						foreach ( $options as $value => $label ) {

							if ( '{acf_field_options}' === $value ) {

								$new_options = array_merge( $new_options, $acf_field['choices'] );

							} else {
								$new_options[ $value ] = $label;
							}
						}

						$options = $new_options;
					}
				}
			}

			unset( $options['{acf_field_options}'] );
		}

		// Load field options from Gravity Forms field option when available.
		if ( isset( $options['{gforms_field_options}'] ) && class_exists( 'GFAPI' ) ) {

			$gform_id    = ! empty( $gmw['general_settings']['gforms_form_ids'] ) ? absint( $gmw['general_settings']['gforms_form_ids'] ) : 0;
			$gform_field = GFAPI::get_field( $gform_id, $custom_field['name'] );

			if ( ! empty( $gform_field->choices ) ) {

				$gf_choices = array();

				foreach ( $gform_field->choices as $gf_choice ) {
					$gf_choices[ $gf_choice['value'] ] = $gf_choice['text'];
				}

				if ( 1 === count( $options ) ) {

					$options = $gf_choices;

				} else {

					$new_options = array();

					foreach ( $options as $value => $label ) {

						if ( '{gforms_field_options}' === $value ) {

							$new_options = array_merge( $new_options, $gf_choices );

						} else {
							$new_options[ $value ] = $label;
						}
					}

					$options = $new_options;
				}
			} elseif ( 'post_category' === $gform_field['type'] || 'post_tags' === $gform_field['type'] ) {

				$tax_terms = 'post_category' === $gform_field['type'] ? get_terms( 'category' ) : get_terms( 'post_tag' );

				if ( ! empty( $tax_terms ) ) {

					$gf_choices = array();

					foreach ( $tax_terms as $tax_term ) {
						$gf_choices[ $tax_term->term_id ] = $tax_term->name;
					}

					if ( 1 === count( $options ) ) {

						$options = $gf_choices;

					} else {

						$new_options = array();

						foreach ( $options as $value => $label ) {

							if ( '{gforms_field_options}' === $value ) {

								$new_options = array_merge( $new_options, $gf_choices );

							} else {
								$new_options[ $value ] = $label;
							}
						}

						$options = $new_options;
					}
				}
			}

			unset( $options['{gforms_field_options}'] );
		}

		// Get value of multiple fields.
		if ( in_array( $custom_field['usage'], array( 'multiselect', 'checkbox', 'checkboxes' ), true ) ) {

			$is_array = true;

			if ( ! empty( $custom_field['value'] ) ) {
				$custom_field['value'] = explode( ',', $custom_field['value'] );
			}
		}

		$conditions = array();

		if ( ! empty( $custom_field['post_types_cond'] ) ) {
			$conditions[] = array(
				'post_types' => is_array( $custom_field['post_types_cond'] ) ? implode( ',', $custom_field['post_types_cond'] ) : $custom_field['post_types_cond'],
			);
		}

		// Field args.
		$args = array(
			'id'               => $gmw['ID'],
			'slug'             => 'cf-' . $custom_field['name'],
			'name'             => 'cf',
			'class'            => 'gmw-custom-field',
			'sub_name'         => $custom_field['name'],
			'type'             => $custom_field['usage'],
			'label'            => isset( $custom_field['label'] ) ? $custom_field['label'] : '',
			'show_options_all' => isset( $custom_field['show_options_all'] ) ? $custom_field['show_options_all'] : '',
			'placeholder'      => $custom_field['placeholder'],
			'required'         => ! empty( $custom_field['required'] ) ? 1 : 0,
			'value'            => $custom_field['value'],
			'options'          => $options,
			'smartbox'         => ! empty( $custom_field['smartbox'] ) ? true : false,
			'is_array'         => $is_array,
			'conditions'       => $conditions,
		);

		if ( ! empty( $acf_tax_field['taxonomy'] ) ) {

			$args['type']            = 'taxonomy';
			$args['additional_args'] = array(
				'id'                  => $gmw['ID'],
				'taxonomy'            => $acf_tax_field['taxonomy'],
				'name_attr'           => 'cf',
				'sub_name_attr'       => $custom_field['name'],
				// 'post_types'          => $post_types,
				'usage'               => $custom_field['usage'],
				'show_options_all'    => isset( $custom_field['show_options_all'] ) ? $custom_field['show_options_all'] : '',
				'orderby'             => 'name',
				'order'               => 'ASC',
				'include'             => '',
				'exclude'             => '',
				'show_count'          => 0,
				'hide_empty'          => 0,
				'multiple_selections' => 0,
				'smartbox'            => ! empty( $custom_field['smartbox'] ) ? 1 : 0,
				'required'            => ! empty( $custom_field['requried'] ) ? 1 : 0,
			);
		}

		if ( 'slider' === $custom_field['usage'] || 'range_slider' === $custom_field['usage'] ) {

			if ( 'range_slider' === $custom_field['usage'] ) {
				$args['value'] = array( $custom_field['min_value'], $custom_field['max_value'] );
			}

			$args['value_prefix'] = isset( $custom_field['value_prefix'] ) ? $custom_field['value_prefix'] : '';
			$args['value_suffix'] = isset( $custom_field['value_suffix'] ) ? $custom_field['value_suffix'] : '';
			$args['min_value']    = isset( $custom_field['min_value'] ) ? $custom_field['min_value'] : '0';
			$args['max_value']    = isset( $custom_field['max_value'] ) ? $custom_field['max_value'] : '100';
			$args['step']         = isset( $custom_field['step'] ) ? $custom_field['step'] : '1';
		}

		// For data/time fields.
		if ( in_array( $custom_field['usage'], array( 'date', 'time', 'datetime' ), true ) ) {

			$args['date_format']     = isset( $custom_field['date_format'] ) ? $custom_field['date_format'] : 'm/d/Y';
			$args['time_format']     = isset( $custom_field['time_format'] ) ? $custom_field['time_format'] : 'h:i';
			$args['datetime_format'] = isset( $custom_field['datetime_format'] ) ? $custom_field['datetime_format'] : 'm/d/Y h:i';
			$compare                 = $custom_field['date_compare'];

			// phpcs:disable.
			/*if ( isset( $custom_field['date_format'] ) ) {
						 $args['date_format'] = $custom_field['date_format'];
					 }

					 if ( isset( $custom_field['time_format'] ) ) {
						 $args['time_format'] = $custom_field['time_format'];
					 }*/
			// phpcs:enable.
		} else {

			$compare = $custom_field['compare'];
		}

		$fields[] = $args;

		// If compare is between/not between, and field is not multiple fields, we generate 2 input fields.
		if ( ( 'BETWEEN' === $compare || 'NOT_BETWEEN' === $compare ) && 'multiselect' !== $custom_field['usage'] && 'checkboxes' !== $custom_field['usage'] ) {

			// Generate second field options.
			if ( in_array( $custom_field['usage'], array( 'select', 'radio' ), true ) && ! empty( $custom_field['second_options'] ) ) {

				$custom_field['second_options'] = explode( PHP_EOL, $custom_field['second_options'] );

				foreach ( $custom_field['second_options'] as $option ) {
					$option          = explode( ' : ', $option );
					$val             = trim( $option[0] );
					$options[ $val ] = ! empty( $option[1] ) ? trim( $option[1] ) : $val;
				}
			}

			// Second field args.
			$multiple_fields          = true;
			$args['label']            = $custom_field['second_label'];
			$args['show_options_all'] = $custom_field['second_show_options_all'];
			$args['placeholder']      = $custom_field['second_placeholder'];
			$args['value']            = $custom_field['second_value'];
			$args['options']          = $custom_field['second_options'];

			$fields[] = $args;

			// Complex field args.
			$wrapper_args = array(
				'id'            => $gmw['ID'],
				'slug'          => 'cf-' . $custom_field['name'],
				'wrap_disabled' => 'hidden' === $args['type'] ? true : false,
				// phpcs:ignore.
				// 'class' => $field_args['class'],
			);
		}

		return ! $multiple_fields ? gmw_get_form_field( $fields[0], $gmw ) : GMW_Search_Form_Helper::get_complex_field( $wrapper_args, $fields, $gmw );
	}
}

if ( ! function_exists( 'gmw_get_search_form_custom_fields' ) ) {

	/**
	 * Get all custom fields to display in search form
	 *
	 * @param  array $gmw gmw form.
	 *
	 * @param  array $fields array of fields to output or empty to output all.
	 *
	 * @version 4.4 ( moved from the Premium Settings extension ).
	 *
	 * @author Eyal Fitoussi
	 */
	function gmw_get_search_form_custom_fields( $gmw = array(), $fields = array() ) {

		if ( empty( $gmw['search_form']['custom_fields'] ) ) {
			return;
		}

		// These are the only 2 extensions that currently supposed to be using this function.
		if ( ! gmw_is_addon_active( 'premium_settings' ) && ( ! gmw_is_addon_active( 'gforms_entries_locator' ) || 'gfel' !== $gmw['prefix'] ) ) {
			return;
		}

		$multiple_field_wrap = apply_filters( 'gmw_custom_fields_multiple_fields_wrapper', false, $gmw );
		$output              = array();

		if ( $multiple_field_wrap ) {
			$output['wrapper'] = '<div class="gmw-search-form-custom-fields gmw-search-form-multiple-fields-wrapper">';
		}

		$output['element'] = '';

		// phpcs:disable.
		//$output['acf_element'] = '';
		//$field_output          = array();
		// phpcs:enable.

		foreach ( $gmw['search_form']['custom_fields'] as $custom_field ) {

			if ( ! empty( $fields ) && ! in_array( $custom_field['name'], $fields, true ) ) {
				continue;
			}

			$output['element'] .= gmw_get_search_form_custom_field( $custom_field, $gmw );
		}

		// phpcs:disable.
		/*foreach ( $gmw['search_form']['advanced_custom_fields'] as $field_id => $custom_field ) {

				  if ( ! empty( $fields ) && ! in_array( $custom_field['name'], $fields, true ) ) {
					  continue;
				  }

				  $output['acf_element'] .= gmw_get_search_form_advanced_custom_field( $field_id, $custom_field, $gmw );
			  }*/
		// phpcs:enable.

		if ( $multiple_field_wrap ) {
			$output['/wrapper'] = '</div>';
		}

		$output = apply_filters( 'gmw_ps_get_search_form_custom_fields_output', $output, $gmw );

		return implode( '', $output );
	}
}

/**
 * Output custom fields filters in the search form.
 *
 * This function requires the Premium Settings extension.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_custom_fields( $gmw ) {

	// This function lives in the Premium Settings extension.
	if ( ! function_exists( 'gmw_get_search_form_custom_fields' ) ) {
		return;
	}

	// Remove filter that adds the custom fields filter dynamically into the form.
	remove_action( 'gmw_search_form_before_distance', 'gmw_append_custom_fields_to_search_form', 10 );

	do_action( 'gmw_before_search_form_custom_fields', $gmw );

	echo gmw_get_search_form_custom_fields( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_reset_button( $gmw ); // phpcs:ignore: XSS ok.

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
 * @param  array $gmw [description].
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

	return gmw_get_search_form_toggle_button( $gmw, $args ); // phpcs:ignore: XSS ok.
}


/**
 * Output the toggle that opens the additional fields wrapper.
 *
 * @since 4.0.
 *
 * @param  array $gmw [description].
 */
function gmw_search_form_modal_box_toggle( $gmw = array() ) {
	echo gmw_get_search_form_modal_box_toggle( $gmw ); // phpcs:ignore: XSS ok.
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
 */
function gmw_search_form_modal_box( $tag = 'open', $gmw = array() ) {
	echo gmw_get_search_form_modal_box( $tag, $gmw ); // phpcs:ignore: XSS ok.
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

	echo gmw_get_search_form_toggle_button( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_bp_group_types_field( $gmw ); // phpcs:ignore: XSS ok.

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

	echo gmw_get_search_form_user_role_field( $gmw ); // phpcs:ignore: XSS ok.

	do_action( 'gmw_after_search_form_user_role_field', $gmw );
}
