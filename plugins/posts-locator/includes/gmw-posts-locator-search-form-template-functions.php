<?php
/**
 * GEO my WP Posts Locator search form helper.
 *
 * @package gwo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get search form post types field element.
 *
 * @param array $gmw gmw form.
 *
 * @return mixed HTML field.
 */
function gmw_get_search_form_post_types( $gmw ) {

	if ( isset( $gmw['search_form']['post_types_settings'] ) ) {

		$settings = $gmw['search_form']['post_types_settings'];

		// for different cases like Global Maps.
	} elseif ( isset( $gmw['search_form']['post_types_usage'] ) ) {

		$settings = array(
			'usage' => $gmw['search_form']['post_types_usage'],
		);

	} else {
		$settings = array();
	}

	$post_types = ! empty( $gmw['search_form']['post_types'] ) ? $gmw['search_form']['post_types'] : array( 'post' );
	$options    = array();
	$is_hidden  = false;
	$value      = array();

	// if a single post type, set it as a hidden field.
	if ( 1 === count( $post_types ) || ( isset( $settings['usage'] ) && 'pre_defined' === $settings['usage'] ) ) {

		$is_hidden = true;
		$type      = 'hidden';
		$value     = $post_types;

	} elseif ( ! empty( $settings['usage'] ) ) {

		$type = $settings['usage'];

	} else {
		$type = 'select';
	}

	if ( ! $is_hidden ) {

		// Generate select options.
		foreach ( $post_types as $post_type ) {

			$post_object = get_post_type_object( $post_type );

			if ( ! empty( $post_object ) ) {
				$options[ $post_type ] = $post_object->labels->name;
			} else {
				$options[ $post_type ] = $post_type;
			}
		}
	}

	$args = array(
		'id'               => $gmw['ID'],
		'slug'             => 'post-types',
		'name'             => 'post',
		'is_array'         => true,
		'type'             => $type,
		'label'            => isset( $settings['label'] ) ? $settings['label'] : '',
		'show_options_all' => isset( $settings['show_options_all'] ) ? $settings['show_options_all'] : '',
		'required'         => ! empty( $settings['required'] ) ? 1 : 0,
		'options'          => $options,
		'value'            => $value,
		'wrapper_class'    => 'gmw-post-types-wrapper gmw-post-types-' . $type . '-wrapper', // deprecated classes.
		'smartbox'         => ! empty( $settings['smartbox'] ) ? 1 : 0,
	);

	return gmw_get_form_field( $args, $gmw );
}

/**
 * Output post types filter in search form.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_post_types( $gmw = array() ) {

	do_action( 'gmw_before_search_form_post_types', $gmw );

	echo gmw_get_search_form_post_types( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_post_types', $gmw );
}

/**
 * Display taxonomies in fron-end search form.
 *
 * @author Eyal Fitoussi.
 *
 * @param array $gmw the form being displayed.
 */
function gmw_get_search_form_taxonomies( $gmw ) {

	// Abort if no post types was selected.
	if ( empty( $gmw['search_form']['post_types'] ) || empty( $gmw['search_form']['taxonomies'] ) ) {
		return;
	}

	$pt_count = count( $gmw['search_form']['post_types'] );

	// abort if multiple post types were selected.
	if ( 1 !== $pt_count && ( ! empty( $gmw['search_form']['post_types_settings']['usage'] && 'pre_defined' === $gmw['search_form']['post_types_settings']['usage'] ) ) ) {
		return;
	}

	$tax_elements = '';

	// Loop through and generate taxonomies.
	foreach ( $gmw['search_form']['taxonomies'] as $taxonomy => $tax_args ) {

		if ( empty( $tax_args['post_types'] ) || ! is_array( $tax_args['post_types'] ) ) {
			continue;
		}

		// skip If post type of the taxonomy was not selected in the form builder.
		if ( 0 === count( array_intersect( $tax_args['post_types'], $gmw['search_form']['post_types'] ) ) ) {
			continue;
		}

		// abort if set as pre_defined or disabled.
		if ( empty( $tax_args['style'] ) || in_array( $tax_args['style'], array( 'pre_defined', 'disable', 'disabled' ), true ) ) {
			continue;
		}

		$post_types = ! empty( $tax_args['post_types'] ) ? $tax_args['post_types'] : array();
		$conditions = array();

		if ( ! empty( $post_types ) ) {
			$conditions[] = array(
				'post_types' => implode( ',', $post_types ),
			);
		}

		$args = array(
			'id'              => $gmw['ID'],
			'slug'            => $taxonomy . '-taxonomy',
			'type'            => 'taxonomy',
			'name'            => $taxonomy,
			'is_array'        => true,
			'label'           => ! empty( $tax_args['label'] ) ? $tax_args['label'] : '',
			'required'        => ! empty( $settings['required'] ) ? 1 : 0,
			'conditions'      => $conditions,
			'wrapper_class'   => 'gmw-field-type-' . $tax_args['style'] . '-wrapper gmw-single-taxonomy-wrapper',
			'wrapper_atts'    => array(
				//'data-post_types' => implode( ',', $post_types ),
				'style'           => $pt_count > 1 ? 'display:none' : '',
			),
			'additional_args' => array(
				'id'                  => $gmw['ID'],
				'taxonomy'            => $taxonomy,
				'post_types'          => $post_types,
				'usage'               => $tax_args['style'],
				'show_options_all'    => isset( $tax_args['show_options_all'] ) ? $tax_args['show_options_all'] : true,
				'orderby'             => ! empty( $tax_args['orderby'] ) ? $tax_args['orderby'] : 'id',
				'order'               => ! empty( $tax_args['order'] ) ? $tax_args['order'] : 'ASC',
				'include'             => ! empty( $tax_args['include'] ) ? $tax_args['include'] : '',
				'exclude'             => ! empty( $tax_args['exclude'] ) ? $tax_args['exclude'] : '',
				'show_count'          => isset( $tax_args['show_count'] ) ? 1 : 0,
				'hide_empty'          => isset( $tax_args['hide_empty'] ) ? 1 : 0,
				'category_icons'      => isset( $tax_args['cat_icons'] ) ? 1 : 0,
				'multiple_selections' => isset( $tax_args['multiple_selections'] ) ? 1 : 0,
				'smartbox'            => isset( $tax_args['smartbox'] ) ? 1 : 0,
				'required'            => ! empty( $tax_args['required'] ) ? 1 : 0,
			),
		);

		$tax_element = gmw_get_form_field( $args, $gmw );

		if ( empty( $tax_element ) ) {
			continue;
		}

		$tax_elements .= $tax_element;
	}

	// abort if taxonomies disabled.
	if ( empty( $tax_elements ) ) {
		return;
	}

	if ( apply_filters( 'gmw_taxonomies_multiple_fields_wrapper', false, $gmw ) ) {

		$output  = '<div class="gmw-search-form-taxonomies gmw-search-form-multiple-fields-wrapper">';
		$output .= $tax_elements;
		$output .= '</div>';

	} else {
		$output = $tax_elements;
	}

	return $output;
}

/**
 * Output Taxonomies fields.
 *
 * @param  array $gmw gmw form.
 */
function gmw_search_form_taxonomies( $gmw = array() ) {

	do_action( 'gmw_before_search_form_taxonomies', $gmw );

	echo gmw_get_search_form_taxonomies( $gmw ); // WPCS: XSS ok.

	do_action( 'gmw_after_search_form_taxonomies', $gmw );
}
