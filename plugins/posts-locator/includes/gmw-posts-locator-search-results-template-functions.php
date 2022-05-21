<?php
/**
 * GEO my WP - Posts Locator search results tempalte functions.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate tax_query args for taxonomy terms query.
 *
 * To be used with posts locator WP_Query.
 *
 * @since 3.1
 *
 * @param  array  $tax_args [description].
 *
 * @param  [type] $gmw      [description].
 *
 * @return [type]           [description]
 */
function gmw_pt_get_tax_query_args( $tax_args = array(), $gmw = array() ) {

	// Abort if multiple post types selected.
	if ( empty( $gmw['search_form']['post_types'] ) || 1 !== count( $gmw['search_form']['post_types'] ) ) {
		return array();
	}

	$output = array( 'relation' => 'AND' );

	// Loop through taxonomies in the search form settings.
	foreach ( $gmw['search_form']['taxonomies'] as $taxonomy => $taxonomy_args ) {

		// Skip if taxonomy is disabled.
		if ( empty( $taxonomy_args['style'] ) || 'disable' === $taxonomy_args['style'] || 'disabled' === $taxonomy_args['style'] ) {
			continue;
		}

		// Skip taxonomy if not below to the selected post type.
		if ( ! in_array( $gmw['search_form']['post_types'][0], $taxonomy_args['post_types'], true ) ) {
			continue;
		}

		$tax_exists = false;
		$tax_values = array();

		// Check if taxonomy term/s were selected in the search form.
		if ( ! empty( $tax_args[ $taxonomy ] ) && array_filter( $tax_args[ $taxonomy ] ) ) {
			$tax_exists = true;
			$tax_values = $tax_args[ $taxonomy ];
		}

		// Query taxonomy from the search form.
		if ( $tax_exists ) {

			$output[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $tax_values,
				'operator' => 'IN',
			);
		}

		// Query include/exclude terms.
		// Only if taxonomy was not selected in the search form or is set to pre-defined.
		if ( ! $tax_exists || 'pre_defined' === $taxonomy_args['style'] ) {

			// include terms.
			if ( ! empty( $taxonomy_args['include'] ) ) {

				$output[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => is_array( $taxonomy_args['include'] ) ? $taxonomy_args['include'] : explode( ',', $taxonomy_args['include'] ),
					'operator' => 'IN',
				);
			}

			// exclude terms.
			if ( ! empty( $taxonomy_args['exclude'] ) ) {

				$output[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'id',
					'terms'    => is_array( $taxonomy_args['exclude'] ) ? $taxonomy_args['exclude'] : explode( ',', $taxonomy_args['exclude'] ),
					'operator' => 'NOT IN',
				);
			}
		}

		// extend the taxonomy query.
		$output = apply_filters( 'gmw_' . $gmw['prefix'] . '_query_taxonomy', $output, $taxonomy, $tax_values, $gmw );
	}

	return ! empty( $output[0] ) ? $output : array();
}

/**
 * Query pre-defined taxonomies.
 *
 * To be used with premium extensions.
 *
 * This functions can be used when multiple post types are selected.
 *
 * @param  array $gmw  gmw form.
 *
 * @since 4.0
 *
 * @return array
 */
function gmw_get_pre_defined_tax_query( $gmw ) {

	// for page load results.
	if ( $gmw['page_load_action'] && ! empty( $gmw['page_load_results']['include_exclude_terms'] ) ) {

		$post_types = $gmw['page_load_results']['post_types'];
		$tax_args   = $gmw['page_load_results']['include_exclude_terms'];

		// on form submission.
	} elseif ( $gmw['submitted'] && isset( $gmw['search_form']['post_types'] ) && 1 < count( $gmw['search_form']['post_types'] ) && ! empty( $gmw['search_form']['include_exclude_terms'] ) ) {

		$post_types = ! empty( $gmw['form_values']['post'] ) ? $gmw['form_values']['post'] : $gmw['search_form']['post_types'];
		$tax_args   = $gmw['search_form']['include_exclude_terms'];

	} else {

		return array();
	}

	$tax_query = array();

	foreach ( $tax_args as $taxonomy => $args ) {

		if ( empty( $args['post_types'] ) ) {
			continue;
		}

		$post_type = array_intersect( $args['post_types'], $post_types );

		if ( empty( $post_type ) ) {
			continue;
		}

		$post_type = $post_type[0];

		if ( ! isset( $tax_query[ $post_type ] ) ) {
			$tax_query[ $post_type ] = array();
		}

		// include terms.
		if ( isset( $args['include'] ) ) {

			$tax_query[ $post_type ][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => is_array( $args['include'] ) ? $args['include'] : explode( ',', $args['include'] ),
				'operator' => 'IN',
			);
		}

		// exclude terms.
		if ( isset( $args['exclude'] ) ) {

			$tax_query['tax_query'][ $post_type ][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => is_array( $args['exclude'] ) ? $args['exclude'] : explode( ',', $args['exclude'] ),
				'operator' => 'NOT IN',
			);
		}

		if ( ! empty( $tax_query[ $post_type ] ) ) {
			$tax_query[ $post_type ]['relation'] = 'AND';
		} else {
			unset( $tax_query[ $post_type ] );
		}
	}

	if ( ! empty( $tax_query ) ) {
		$tax_query['relation'] = 'OR';
	}

	return $tax_query;
}

/**
 * Apply tax query dynamically  to all of the Posts Locator forms.
 *
 * @since 4.0.
 *
 * @param  array $gmw gmw form.
 *
 * @return [type]      [description]
 */
function gmw_generate_tax_query( $gmw ) {

	$tax_query = gmw_get_pre_defined_tax_query( $gmw );

	if ( ! empty( $tax_query ) ) {
		return $tax_query;
	}

	if ( ! isset( $gmw['form_values']['tax'] ) ) {
		$gmw['form_values']['tax'] = array();
	}

	$tax_query = gmw_pt_get_tax_query_args( $gmw['form_values']['tax'], $gmw );

	if ( ! empty( $tax_query ) ) {
		return $tax_query;
	}

	return array();
}
//add_filter( 'gmw_posts_locator_form_before_posts_query', 'gmw_pt_execute_tax_query' );

/**
 * Get posts featured image.
 *
 * @param  array  $args image arguments.
 *
 * @param  object $post post object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return HTML element.
 */
function gmw_get_post_featured_image( $args = array(), $post = array(), $gmw = array(), $deprecated = array() ) {

	// Temporary here to support previous version of passing arguments.
	// Where first argument used to be the $post and second argument $gmw.
	if ( ! is_array( $args ) ) {

		$gmw  = $post;
		$post = $args;
		$args = array(
			'object_type' => 'post',
			'object_id'   => is_object( $post ) ? $post->ID : $post,
			'width'       => ! empty( $gmw['search_results']['image']['width'] ) ? $gmw['search_results']['image']['width'] : '150px',
			'height'      => ! empty( $gmw['search_results']['image']['height'] ) ? $gmw['search_results']['image']['height'] : '150px',
			'where'       => 'search_results',
		);
	}

	$args = apply_filters( 'gmw_get_post_featured_image_args', $args, $post, $gmw );
	$args = wp_parse_args(
		$args,
		array(
			'object_type'  => 'post',
			'object_id'    => 0,
			'permalink'    => true,
			'width'        => '150px',
			'height'       => '150px',
			'where'        => 'search_results',
			'class'        => '',
			'no_image_url' => '',
		)
	);

	if ( has_post_thumbnail( $args['object_id'] ) ) {
		$args['image_url'] = wp_get_attachment_image_src( get_post_thumbnail_id( $args['object_id'] ), 'full' )[0];

	} else {

		$args['image_url'] = $args['no_image_url'];
		$args['class']    .= ' gmw-no-image';
	}

	$args['permalink'] = ! empty( $args['permalink'] ) ? get_the_permalink( $args['object_id'] ) : false;

	return gmw_get_image_element( $args, $post, $gmw );
}

/**
 * Display featured image in search results.
 *
 * @param  object $post post object.
 *
 * @param  array  $gmw  GMW form.
 *
 * @return [type]       [description]
 */
function gmw_search_results_featured_image( $post, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['image']['enabled'] ) ) {
		return;
	}

	$settings = $gmw[ $where ]['image'];
	$args     = array(
		'object_type'  => 'post',
		'object_id'    => $post->ID,
		'width'        => ! empty( $settings['width'] ) ? $settings['width'] : '150px',
		'height'       => ! empty( $settings['height'] ) ? $settings['height'] : '150px',
		'no_image_url' => ! empty( $settings['no_image_url'] ) ? $settings['no_image_url'] : '',
		'where'        => $where,
	);

	echo gmw_get_post_featured_image( $args, $post, $gmw ); // WPCS: XSS ok.
}

/**
 * Get taxonomies in search results
 *
 * @param  object $post Post object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return [type]       [description]
 */
function gmw_search_results_taxonomies( $post, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['taxonomies'] ) ) {
		return;
	}

	$args = array(
		'id' => $gmw['ID'],
	);

	echo '<div class="gmw-item taxonomies-list-wrapper">' . gmw_get_post_taxonomies_terms_list( $post, $args ) . '</div>'; // WPCS: XSS ok.
}

/**
 * Display excerpt in search results
 *
 * @param  object $post post object.
 *
 * @param  array  $gmw  gmw form.
 *
 * @return [type]       [description]
 */
function gmw_search_results_post_excerpt( $post, $gmw = array(), $where = 'search_results' ) {

	if ( empty( $gmw[ $where ]['excerpt']['usage'] ) ) {
		return;
	}

	$settings = $gmw[ $where ]['excerpt'];

	// verify usage value.
	$usage = $settings['usage'];

	if ( empty( $post->$usage ) ) {
		return;
	}

	$args = array(
		'id'                 => $gmw['ID'],
		'content'            => $post->$usage,
		'words_count'        => isset( $settings['count'] ) ? $settings['count'] : '',
		'link'               => get_the_permalink( $post->ID ),
		'link_text'          => isset( $settings['link'] ) ? $settings['link'] : '',
		'enable_shortcodes'  => 1,
		'the_content_filter' => 1,
	);

	$excerpt = GMW_Template_Functions_Helper::get_excerpt( $args );

	echo apply_filters( 'gmw_search_results_post_excerpt_output', '<div class="gmw-item gmw-excerpt excerpt">' . $excerpt . '</div>', $excerpt, $args, $post, $gmw, $where ); // WPCS: XSS ok.
}
