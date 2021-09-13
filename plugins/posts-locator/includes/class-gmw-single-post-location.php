<?php
/**
 * GEO my WP Single Post Location.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


if ( ! class_exists( 'GMW_Single_Location' ) ) {
	return;
}

/**
 * GMW_Single_Post_Location Class extends GMW_Single_Location class
 *
 * Display the location of a single post
 *
 * @version 1.0
 *
 * @author Eyal Fitoussi
 *
 * @since 2.6.1
 */
class GMW_Single_Post_Location extends GMW_Single_Location {

	/**
	 * Extends the default shortcode atts
	 *
	 * @since 2.6.1
	 *
	 * @var array
	 */
	protected $args = array(
		'elements'         => 'title,address,map,distance,location_meta,directions_link',
		'object'           => 'post',
		'prefix'           => 'pt',
		'location_meta'    => 'address,phone,fax,email,website',
		'item_info_window' => 'title,address,distance,location_meta',
	);

	/**
	 * Trt and get post ID if missing.
	 *
	 * @return [type] [description]
	 */
	public function get_object_id() {

		$object_id = get_queried_object_id();

		if ( empty( $object_id ) ) {

			global $post;

			if ( ! empty( $post->ID ) ) {
				$object_id = $post->ID;
			}
		}

		return ! empty( $object_id ) ? $object_id : false;
	}

	/**
	 * Get the post title.
	 *
	 * @param object $location location object.
	 *
	 * @return [type] [description]
	 */
	public function title( $location ) {

		$title     = get_the_title( $this->args['object_id'] );
		$permalink = get_the_permalink( $this->args['object_id'] );

		return apply_filters( 'gmw_sl_title', "<h3 class=\"gmw-sl-title post-title gmw-sl-element\"><a href=\"{$permalink}\" title=\"{$title}\">{$title}</a></h3>", $location, $this->args, $this->user_position, $this );
	}
}

/**
 * GMW Single post location shortcode.
 *
 * @version 2.0
 *
 * @param array $atts arguments.
 *
 * @author Eyal Fitoussi
 */
function gmw_single_post_location_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	$atts['object'] = 'post';

	if ( isset( $atts['post_id'] ) ) {
		$atts['object_id'] = $atts['post_id'];
	}

	$single_post_location = new GMW_Single_Post_Location( $atts );

	return $single_post_location->output();
}
add_shortcode( 'gmw_post_location', 'gmw_single_post_location_shortcode' );
