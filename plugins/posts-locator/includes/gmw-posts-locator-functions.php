<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate the post location form
 *
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_post_location_form( $args = array() ) {

	if ( ! empty( $args['post_id'] ) ) {
		$args['object_id'] = $args['post_id'];
	}

	// default args
	$defaults = array(
		'object_id'      => 0,
		'form_template'  => 'location-form-tabs-left',
		'submit_enabled' => 1,
		'stand_alone'    => 1,
		'ajax_enabled'   => 1,
		'auto_confirm'   => 1,
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_post_location_form_args', $args );

	if ( ! absint( $args['object_id'] ) ) {

		if ( IS_ADMIN && isset( $_GET['post'] ) && absint( $_GET['post'] ) ) {

			$args['object_id'] = $_GET['post'];

		} else {

			global $post;

			if ( isset( $post->ID ) ) {
				$args['object_id'] = $post->ID;
			} else {
				return;
			}
		}
	}

	include( 'class-gmw-post-location-form.php' );

	if ( ! class_exists( 'GMW_Post_Location_Form' ) ) {
		return;
	}

	// generate new location form
	$location_form = new GMW_Post_Location_Form( $args );

	// display the location form
	$location_form->display_form();
}

/**
 * Generate the post location form using shortcode
 *
 * @param  array  $atts [description]
 * @return [type]       [description]
 */
function gmw_post_location_form_shortcode( $atts = array() ) {

	if ( empty( $atts ) ) {
		$atts = array();
	}

	ob_start();

	gmw_post_location_form( $atts );

	$content = ob_get_clean();

	return $content;
}
add_shortcode( 'gmw_post_location_form', 'gmw_post_location_form_shortcode' );

/**
 * Get terms function using GEO my WP internal cache
 *
 * @since 3.0
 *
 * @param  string $taxonomy [description]
 * @param  array  $args     [description]
 * @return [type]           [description]
 */
function gmw_get_terms( $taxonomy = 'category', $args = array() ) {

	$terms = false;

	$args['taxonomy'] = $taxonomy;

	// look for cache helper class
	if ( class_exists( 'GMW_Cache_Helper' ) && GMW()->internal_cache ) {

		// check for terms in transient
		$hash = md5( json_encode( $args ) );
		$hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_' . $taxonomy . '_terms' );

		// if no terms found in transient get it from database
		if ( false === ( $terms = get_transient( $hash ) ) ) {

			//print_r( 'get terms done.' );

			// get terms
			$terms = get_terms( $taxonomy, $args );

			// save terms in transient
			set_transient( $hash, $terms, MONTH_IN_SECONDS );
		}
	} else {

		$terms = get_terms( $taxonomy, $args );
	}

	return $terms;
}

/**
 * GMW get_the_terms function using internal cache
 *
 * get terms attached to a post
 *
 * @since 3.0
 *
 * @param  integer $post_id  [description]
 * @param  [type]  $taxonomy [description]
 * @return [type]            [description]
 */
function gmw_get_the_terms( $post_id = 0, $taxonomy ) {

	$terms = false;

	//look for cache helper class
	if ( class_exists( 'GMW_Cache_Helper' ) && GMW()->internal_cache ) {

		// check for terms in transient
		$hash = md5( json_encode( array( $post_id, $taxonomy ) ) );
		$hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_the_' . $taxonomy . '_terms' );

		// if no terms found in transient get it from database
		if ( false === ( $terms = get_transient( $hash ) ) ) {

			//print_r( 'det the terms done.' );

			//get terms
			$terms = get_the_terms( $post_id, $taxonomy );

			//save terms in transient
			set_transient( $hash, $terms, MONTH_IN_SECONDS );
		}
	} else {

		$terms = get_the_terms( $post_id, $taxonomy );
	}

	return $terms;
}

/**
 * Check if post exists
 *
 * @param  integer $post_id [description]
 *
 * @return [type]           [description]
 */
function gmw_is_post_exists( $post_id = 0 ) {

	if ( empty( $post_id ) ) {
		return false;
	}

	global $wpdb;

	// check if post exists
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"
            SELECT ID 
            FROM $wpdb->posts 
            WHERE ID = %d",
			$post_id
		)
	);

	// abort if post not exists
	if ( empty( $post_id ) ) {

		return false;

	} else {

		return true;
	}
}

/**
 * get the post location from database
 *
 * @since 3.0
 *
 * @param  boolean $post_id [description]
 * @return [type]           [description]
 */
function gmw_get_post_location( $post_id = 0 ) {

	// if no specific post ID pass, look for displayed post object
	if ( empty( $post_id ) ) {

		global $post;

		if ( ! empty( $post ) ) {
			$post_id = $post->ID;
		} else {
			return;
		}
	}

	// get post location from database
	return gmw_get_location( 'post', $post_id );
}

/**
 * Get specific or all post address fields
 *
 * @since 3.0
 *
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_post_address( $args = array() ) {

	// to support older versions. should be removed in the future
	if ( empty( $args['fields'] ) && ! empty( $args['info'] ) ) {

		trigger_error( 'The "info" shortcode attribute of the shortcode [gmw_post_address] is deprecated since GEO my WP version 3.0. Please use the shortcode attribute "fields" instead.', E_USER_NOTICE );

		$args['fields'] = $args['info'];
	}

	//default shortcode attributes
	$args = shortcode_atts(
		array(
			'post_id'   => 0,
			'fields'    => 'formatted_address',
			'separator' => ', ',
		), $args
	);

	// if no specific post ID pass, look for displayed post object
	if ( empty( $args['post_id'] ) ) {

		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$args['post_id'] = $post->ID;
	}

	if ( is_string( $args['fields'] ) ) {
		$args['fields'] = explode( ',', $args['fields'] );
	}

	// get post address fields
	return gmw_get_address_fields( 'post', $args['post_id'], $args['fields'], $args['separator'] );
}
add_shortcode( 'gmw_post_address', 'gmw_get_post_address' );

function gmw_post_address( $args = array() ) {
	echo gmw_get_post_address( $args );
}

/**
 * Output post location meta using shortcode
 *
 * @param  [type] $atts [description]
 * @return [type]       [description]
 *
 * @since 3.0.2
 *
 */
/*function gmw_get_post_location_meta_values( $atts ) {

	//default shortcode attributes
	extract(
		shortcode_atts( array(
			'post_id'   => 0,
			'meta_keys' => '',
			'separator' => ' '
		), $atts )
	);

	// verify post ID
	if ( empty( $post_id ) ) {

		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$post_id = $post->ID;
	}

	return gmw_get_location_meta_values( 'post', $post_id, $meta_keys, $separator );
}
add_shortcode( 'gmw_post_location_meta', 'gmw_get_post_location_meta_values' ); */

/**
 * Get post address or location meta fields.
 *
 * @since 3.0.2
 *
 * @param  [type] $atts [description]
 * @return [type]       [description]
 */
function gmw_get_post_location_fields( $atts ) {

	//default shortcode attributes
	$args = shortcode_atts(
		array(
			'post_id'       => 0,
			'location_meta' => 0,
			'fields'        => '',
			'separator'     => ' ',
		), $atts
	);

	// verify post ID
	if ( empty( $args['post_id'] ) ) {

		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$args['post_id'] = $post->ID;
	}

	if ( is_string( $args['fields'] ) ) {
		$args['fields'] = explode( ',', $args['fields'] );
	}

	return gmw_get_location_fields( 'post', $args['post_id'], $args['fields'], $args['separator'], $args['location_meta'] );
}
add_shortcode( 'gmw_post_location_fields', 'gmw_get_post_location_fields' );

/**
 * get post location data from database
 *
 * This function returns location data and post data such as post title, content, author...
 *
 * The function also verify that the post exists in database. That is in case
 *
 * That the post was deleted but the location still exists in database.
 *
 * @since 3.0
 *
 * @param  int $post_id
 *
 * @return object post data + location data
 */
function gmw_get_post_location_data( $post_id = 0, $output = OBJECT, $cache = true ) {

	if ( empty( $fields ) ) {

		$fields = array(
			'gmw.ID',
			'gmw.latitude',
			'gmw.longitude',
			'gmw.latitude as lat',
			'gmw.longitude as lng',
			'gmw.address',
			'gmw.formatted_address',
			'gmw.street_number',
			'gmw.street_name',
			'gmw.street',
			'gmw.city',
			'gmw.region_code',
			'gmw.region_name',
			'gmw.postcode',
			'gmw.country_code',
			'gmw.country_name',
			'featured',
			'posts.ID as post_id',
			'posts.post_title',
			'posts.post_type',
			'posts.post_author',
			'posts.post_content',
		);
	}

	$fields = implode( ',', apply_filters( 'gmw_get_post_location_data_fields', $fields, $post_id ) );

	// if no specific user ID pass, look for logged in user object
	if ( empty( $post_id ) ) {

		global $post;

		// try to get global post ID
		if ( empty( $post->ID ) ) {
			return;
		}

		$post_id = $post->ID;
	}

	$location = $cache ? wp_cache_get( $post_id, 'gmw_posts_location_data' ) : false;

	if ( false === $location ) {

		global $wpdb;

		$gmw_table   = $wpdb->base_prefix . 'gmw_locations';
		$posts_table = $wpdb->prefix . 'posts';

		$location = $wpdb->get_row(
			$wpdb->prepare(
				"
                SELECT     $fields
                FROM       $gmw_table  gmw
                INNER JOIN $posts_table posts
                ON         gmw.object_id = posts.ID
                WHERE      gmw.object_type = 'post'
                AND        gmw.object_id = %d
            ", $post_id
			),
			OBJECT
		);

		// save to cache if location found
		if ( ! empty( $location ) ) {
			wp_cache_set( $post_id, $location, 'gmw_posts_location_data' );
			wp_cache_set( $location->ID, $location, 'gmw_location_data' );
		}
	}

	// if no location found
	if ( empty( $location ) ) {
		return null;
	}

	// convert to array if needed
	if ( ARRAY_A == $output || ARRAY_N == $output ) {
		$location = gmw_to_array( $location, $output );
	}

	return $location;
}

/**
 * Get post taxonomies terms list
 *
 * @param  [type] $post [description]
 * @param  array  $args [description]
 * @return [type]       [description]
 */
function gmw_get_post_taxonomies_terms_list( $post, $args = array() ) {

	$defaults = array(
		'id'         => 0,
		'class'      => '',
		'exclude'    => '',
		'terms_link' => 1,
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_post_taxonomies_list_args', $args, $post );

	$excluded_taxes = '' != $args['exclude'] ? explode( ',', $args['exclude'] ) : array();

	// get taxonomies attached to the post
	$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

	$output = '';

	// loop through taxonomies
	foreach ( $taxonomies as $taxonomy ) {

		// skip if taxonomy excluded
		if ( in_array( $taxonomy->name, $excluded_taxes ) ) {
			continue;
		}

		// get terms attached to the post
		$terms = gmw_get_the_terms( $post->ID, $taxonomy->name );

		if ( $terms && ! is_wp_error( $terms ) ) {

			$tax_output = array();
			$terms_list = array();

			//generate comma separated list of terms with or without a link
			foreach ( $terms as $term ) {
				$terms_list[] = $args['terms_link'] ? '<a href="' . esc_url( get_term_link( $term->term_id, $taxonomy->name ) ) . '">' . esc_html( $term->name ) . '</a>' : esc_html( $term->name );
			}

			$output .= '<div class="gmw-taxonomy-terms gmw-taxes ' . esc_attr( $taxonomy->rewrite['slug'] ) . ' ' . esc_attr( $args['class'] ) . '">';
			$output .= '<span class="label">' . esc_attr( $taxonomy->label ) . ': </span>';
			$output .= '<span class="gmw-terms-wrapper">';
			$output .= join( ', ', $terms_list );
			$output .= '</span>';
			$output .= '</div>';
		}
	}

	return $output;
}

/**
 * Delete post location
 *
 * @since 3.0
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function gmw_delete_post_location( $post_id = false, $delete_meta = false ) {

	if ( empty( $post_id ) ) {
		return;
	}

	gmw_delete_location( 'post', $post_id, $delete_meta );
}

/**
 * Change post location status
 *
 * @since 3.0
 *
 * @param  integer $post_id [description]
 * @param  integer $status  [description]
 * @return [type]           [description]
 */
function gmw_post_location_status( $post_id = 0, $status = 1 ) {

	$status = ( 1 == $status ) ? 1 : 0;

	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"
            UPDATE {$wpdb->base_prefix}gmw_locations 
            SET   `status`      = $status 
            WHERE `object_type` = 'post' 
            AND   `blog_id`     = %d
            AND   `object_id`   = %d",
			array( gmw_get_blog_id(), $post_id )
		)
	);
}

/**
 * Update post location.
 *
 * The function will geocode the address, or reverse geocode coords, and save it in the locations table in DB
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 *
 * @param  integer          $post_id  int ( post ID, user ID, comment ID... )
 * @param  string || array  $location to pass an address it can be either a string or an array of address field for example:
 *
 * $location = array(
 *      'street'    => 285 Fulton St,
 *      'apt'       => '',
 *      'city'      => 'New York',
 *      'state'     => 'NY',
 *      'zipcode'   => '10007',
 *      'country'   => 'USA'
 * );
 *
 * or pass a set of coordinates via an array of lat,lng. Ex
 *
 * $location = array(
 *     'lat' => 26.1345,
 *     'lng' => -80.4362
 * );
 *
 * @param  integer $user_id      the user whom the location belongs to. By default it will belong to the user who creates/update the post location ( logged in user ).
 * @param  boolean $force_refresh false to use geocoded address in cache || true to force address geocoding
 *
 * @return int location ID
 */
function gmw_update_post_location( $post_id = 0, $location = array(), $user_id = 0, $force_refresh = false ) {

	if ( ! gmw_is_post_exists( $post_id ) ) {
		return;
	}

	// update post location
	return gmw_update_location( 'post', $post_id, $location, $user_id, $force_refresh );
}

/**
 * Update post location metas
 *
 * Can update/create single or multiple post location metas.
 *
 * For a single location meta pass the post ID, meta key and meta value
 *
 * For multiple metas pass the post ID and an array of meta_key => meta_value pairs
 *
 * @since 3.0
 *
 * @param  integer $post_id    post ID
 * @param  array   $metadata   [description]
 * @param  boolean $meta_value [description]
 * @return [type]              [description]
 */
function gmw_update_post_location_meta( $post_id = 0, $metadata = array(), $meta_value = false ) {

	// look for location ID
	$location_id = gmw_get_location_id( 'post', $post_id );

	// abort if location not exists
	if ( empty( $location_id ) ) {
		return false;
	}

	gmw_update_location_metas( $location_id, $metadata, $meta_value );
}

/**
 * Change location status in database when post status changes
 *
 * @param  [type] $new_status [description]
 * @param  [type] $old_status [description]
 * @param  [type] $post       [description]
 * @return [type]             [description]
 */
function gmw_transition_post_status( $new_status, $old_status, $post ) {

	$status = ( 'publish' == $new_status ) ? 1 : 0;

	gmw_post_location_status( $post->ID, $status );
}
add_action( 'transition_post_status', 'gmw_transition_post_status', 10, 3 );

/**
 * Change post status when post sent to trash
 *
 * @param  [type] $post_id [description]
 * @return [type]          [description]
 */
function gmw_trash_post_location( $post_id ) {

	gmw_post_location_status( $post_id, 0 );
}
add_action( 'wp_trash_post', 'gmw_trash_post_location' );

/**
 *  delete info from our database after post was deleted
 */
function gmw_after_delete_post( $post_id ) {

	gmw_delete_location( 'post', $post_id, true );
}
add_action( 'after_delete_post', 'gmw_after_delete_post' );

