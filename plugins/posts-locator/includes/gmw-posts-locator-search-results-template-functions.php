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

	$tax_value = false;
	$output    = array( 'relation' => 'AND' );

	foreach ( $tax_args as $taxonomy => $values ) {

		if ( array_filter( $values ) ) {
			$output[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'id',
				'terms'    => $values,
				'operator' => 'IN',
			);
		}

		// extend the taxonomy query.
		$output = apply_filters( 'gmw_' . $gmw['prefix'] . '_query_taxonomy', $output, $taxonomy, $values, $gmw );
	}

	// verify that there is at least one query to performe.
	if ( empty( $output[0] ) ) {
		$output = array();
	}

	return $output;
}

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
function gmw_search_results_featured_image( $post, $gmw = array() ) {

	if ( ! $gmw['search_results']['image']['enabled'] ) {
		return;
	}

	$args = array(
		'object_type'  => 'post',
		'object_id'    => $post->ID,
		'width'        => ! empty( $gmw['search_results']['image']['width'] ) ? $gmw['search_results']['image']['width'] : '150px',
		'height'       => ! empty( $gmw['search_results']['image']['height'] ) ? $gmw['search_results']['image']['height'] : '150px',
		'no_image_url' => ! empty( $gmw['search_results']['image']['no_image_url'] ) ? $gmw['search_results']['image']['no_image_url'] : '',
		'where'        => 'search_results',
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
function gmw_search_results_taxonomies( $post, $gmw = array() ) {

	if ( ! isset( $gmw['search_results']['taxonomies'] ) || '' === $gmw['search_results']['taxonomies'] ) {
		return;
	}

	$args = array(
		'id' => $gmw['ID'],
	);

	echo '<div class="taxonomies-list-wrapper">' . gmw_get_post_taxonomies_terms_list( $post, $args ) . '</div>'; // WPCS: XSS ok.
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
function gmw_search_results_post_excerpt( $post, $gmw = array() ) {

	if ( empty( $gmw['search_results']['excerpt']['usage'] ) ) {
		return;
	}

	// verify usage value.
	$usage = $gmw['search_results']['excerpt']['usage'];

	if ( empty( $post->$usage ) ) {
		return;
	}

	$args = array(
		'id'                 => $gmw['ID'],
		'content'            => $post->$usage,
		'words_count'        => isset( $gmw['search_results']['excerpt']['count'] ) ? $gmw['search_results']['excerpt']['count'] : '',
		'link'               => get_the_permalink( $post->ID ),
		'link_text'          => isset( $gmw['search_results']['excerpt']['link'] ) ? $gmw['search_results']['excerpt']['link'] : '',
		'enable_shortcodes'  => 1,
		'the_content_filter' => 1,
	);

	$excerpt = GMW_Template_Functions_Helper::get_excerpt( $args );

	echo apply_filters( 'gmw_search_results_post_excerpt_output', '<div class="gmw-excerpt excerpt">' . $excerpt . '</div>', $excerpt, $args, $post, $gmw ); // WPCS: XSS ok.
}
