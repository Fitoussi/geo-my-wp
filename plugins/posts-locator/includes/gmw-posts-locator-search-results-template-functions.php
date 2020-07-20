<?php
/**
 * GEO my WP - Posts Locator search results tempalte functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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
function gmw_pt_get_tax_query_args( $tax_args = array(), $gmw ) {

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
 * @param  object|integer $post the post object or post ID.
 *
 * @param  array          $gmw  gmw form.
 *
 * @param  array|string   $size image size in pixels. Will override the size provided in $gmw if any.
 *
 * @param  array          $attr image attributes.
 *
 * @return HTML element.
 */
function gmw_get_post_featured_image( $post = 0, $gmw = array(), $size = '', $attr = array() ) {

	$output = '';

	// If image size was not provided we are going to use
	// the size from the form settings if exists.
	if ( empty( $size ) ) {

		$size = 'post-thumbnail';

		// If form provide image size.
		if ( ! empty( $gmw['search_results']['image']['width'] ) && ! empty( $gmw['search_results']['image']['height'] ) ) {

			$size = array(
				$gmw['search_results']['image']['width'],
				$gmw['search_results']['image']['height'],
			);
		}
	}

	// Make sure the class gmw-image is added to all images.
	if ( isset( $attr['class'] ) ) {
		$attr['class'] .= ' gmw-image';
	} else {
		$attr['class'] = 'gmw-image';
	}

	// filter the image args.
	$args = apply_filters(
		'gmw_pt_post_featured_image_args',
		array(
			'size' => $size,
			'attr' => $attr,
		),
		$post,
		$gmw
	);

	// Look for post thumbnail.
	if ( has_post_thumbnail() ) {

		$output .= '<div class="post-thumbnail">';
		$output .= get_the_post_thumbnail(
			$post,
			$args['size'],
			$args['attr']
		);
		$output .= '</div>';

		// Otherise, use the default "No image".
	} else {

		if ( ! is_array( $args['size'] ) ) {
			$args['size'] = array( 200, 200 );
		}

		$output .= '<div class="post-thumbnail no-image">';
		$output .= '<img class="gmw-image"';
		$output .= 'src="' . GMW_IMAGES . '/no-image.jpg" ';
		$output .= 'width=" ' . esc_attr( $args['size'][0] ) . '" ';
		$output .= 'height=" ' . esc_attr( $args['size'][1] ) . '" ';
		$output .= '/>';
		$output .= '</div>';
	}

	return apply_filters( 'gmw_pt_post_feature_image', $output, $post, $gmw, $size, $attr );
}

/**
 * Display featured image in search results
 *
 * @param  [type] $post [description].
 *
 * @param  array  $gmw  [description].
 *
 * @return [type]       [description]
 */
function gmw_search_results_featured_image( $post, $gmw = array() ) {

	if ( ! $gmw['search_results']['image']['enabled'] ) {
		return;
	}

	echo gmw_get_post_featured_image( $post, $gmw ); // WPCS: XSS ok.
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

	if ( empty( $gmw['search_results']['excerpt']['enabled'] ) ) {
		return;
	}

	// verify usage value.
	$usage = isset( $gmw['search_results']['excerpt']['usage'] ) ? $gmw['search_results']['excerpt']['usage'] : 'post_content';

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
