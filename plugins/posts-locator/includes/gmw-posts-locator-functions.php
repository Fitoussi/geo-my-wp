<?php
/**
 * GEO my WP posts locator template functions.
 *
 * @package geo-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if post exists.
 *
 * @param  integer $post_id The ID of the post to check for.
 *
 * @return boolean
 */
function gmw_is_post_exists( $post_id = 0 ) {

	if ( empty( $post_id ) ) {
		return false;
	}

	global $wpdb;

	// check if post exists.
	$post_id = $wpdb->get_var(
		$wpdb->prepare(
			"
            SELECT ID
            FROM $wpdb->posts
            WHERE ID = %d",
			$post_id
		)
	); // WPCS: db call ok, cache ok.

	// abort if post not exists.
	return ! empty( $post_id ) ? true : false;
}

/**
 * Get location form args.
 *
 * @param  object $post the post object.
 *
 * @since 4.1.1
 *
 * @return [type]       [description]
 */
function gmw_get_post_location_form_args( $post ) {

	/**
	 * This is a temporary solution for an issue with the $post object.
	 *
	 * In some occations the $post object might belong to a different post
	 *
	 * of a different post type than the post that is currently being edited.
	 *
	 * I am not certain if this issue cause by the theme that generates the different post types
	 *
	 * or by GEO my WP.
	 */
	if ( ! empty( $_GET['post'] ) && absint( $_GET['post'] ) && $_GET['post'] !== $post->ID ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		$post = get_post( absint( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
	}

	// form args.
	return apply_filters(
		'gmw_edit_post_location_form_args',
		array(
			'object_id'          => $post->ID,
			'form_template'      => 'location-form-tabs-left',
			//'submit_enabled'     => 1,
			//'auto_confirm'       => 1,
			'stand_alone'        => 0,
			//'ajax_enabled'       => ,
			'confirm_required'   => 0,
			'form_element'       => '.wrap form',
			'map_zoom_level'     => gmw_get_option( 'post_types_settings', 'edit_post_page_map_zoom_level', 7 ),
			'map_type'           => gmw_get_option( 'post_types_settings', 'edit_post_page_map_type', 'ROADMAP' ),
			'map_lat'            => gmw_get_option( 'post_types_settings', 'edit_post_page_map_latitude', '40.711544' ),
			'map_lng'            => gmw_get_option( 'post_types_settings', 'edit_post_page_map_longitude', '-74.013486' ),
			'location_mandatory' => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
			'location_required'  => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
		),
		$post
	);
}

/**
 * Get terms function using GEO my WP internal cache.
 *
 * @since 3.0
 *
 * @param  string $taxonomy the taxnomoy.
 *
 * @param  array  $args     array of arguments.
 *
 * @return [type]           [description]
 */
function gmw_get_terms( $taxonomy = 'category', $args = array() ) {

	$terms            = false;
	$args['taxonomy'] = $taxonomy;

	// look for cache helper class.
	if ( class_exists( 'GMW_Cache_Helper' ) && GMW()->internal_cache ) {

		// check for terms in transient.
		$hash  = md5( wp_json_encode( $args ) );
		$hash  = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_' . $taxonomy . '_terms' );
		$terms = get_transient( $hash );

		// if no terms found in transient get it from database.
		if ( empty( $terms ) ) {

			// get terms.
			$terms = get_terms( $args );

			// save terms in transient.
			set_transient( $hash, $terms, DAY_IN_SECONDS * 7 );
		}
	} else {

		$terms = get_terms( $args );
	}

	return $terms;
}

/**
 * Get an array of taxonomy terms
 *
 * Array of term_id => term_name ordered alphabetically as well as parent - children.
 *
 * @since 4.0
 */
/*function gmw_get_taxonomy_terms_list( $taxonomy ) {

	// vars
	$output          = array();
	$is_hierarchical = is_taxonomy_hierarchical( $taxonomy );
	$terms           = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	// bail early i no terms
	if ( empty( $terms ) ) {
		return array();
	}

	// sort into hierachial order!
	if ( $is_hierarchical ) {
		$terms = _get_term_children( 0, $terms, $taxonomy );
	}

	foreach ( $terms as $term ) {

		$label = $term->name;

		// Allow for empty name.
		if ( '' === $label ) {
			$label = __( '(no title)', 'geo-my-wp' );
		}

		// Prepend ancestors indentation.
		if ( $is_hierarchical ) {
			$ancestors = get_ancestors( $term->term_id, $taxonomy );
			$label     = str_repeat( '&nbsp;&nbsp; ', count( $ancestors ) ) . $label;
		}

		$output[ $term->term_id ] = $label;
	}

	// return
	return $output;
}*/

/**
 * GMW get_the_terms function using internal cache.
 *
 * Get terms attached to a post.
 *
 * @since 3.0
 *
 * @param  integer $post_id  The post ID.
 * @param  string  $taxonomy The taxonomy to retrieve.
 *
 * @return array             array of terms
 */
function gmw_get_the_terms( $post_id = 0, $taxonomy = '' ) {

	$terms = false;

	// phpcs:disable.
	// Cache is disabled for this function for now. It fills up the database pretty quickly.
	// look for cache helper class.
	/* if ( class_exists( 'GMW_Cache_Helper' ) && GMW()->internal_cache ) {
		// check for terms in transient.
		$hash  = md5( wp_json_encode( array( $post_id, $taxonomy ) ) );
		$hash  = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_the_' . $taxonomy . '_terms' );
		$terms = get_transient( $hash );

		// if no terms found in transient get it from database.
		if ( empty( $terms ) ) {

			// get terms.
			$terms = get_the_terms( $post_id, $taxonomy );

			// save terms in transient.
			set_transient( $hash, $terms, DAY_IN_SECONDS * 7 );
		}
	} else {

		$terms = get_the_terms( $post_id, $taxonomy );
	}*/
	// phpcs:enable.

	$terms = get_the_terms( $post_id, $taxonomy );

	return $terms;
}

/**
 * Get post taxonomies terms list
 *
 * @param  [type] $post the post object.
 *
 * @param  array  $args arguments.
 *
 * @return [type]       [description]
 */
function gmw_get_post_taxonomies_terms_list( $post, $args = array() ) {

	$defaults = array(
		'id'         => 0,
		'class'      => '',
		'exclude'    => '',
		'terms_link' => 1,
		'separator'  => ', ',
	);

	$args = wp_parse_args( $args, $defaults );
	$args = apply_filters( 'gmw_post_taxonomies_list_args', $args, $post );

	$excluded_taxes = '' !== $args['exclude'] ? explode( ',', $args['exclude'] ) : array();

	// get taxonomies attached to the post.
	$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

	$output      = '';
	$parent_args = $args;

	// loop through taxonomies.
	foreach ( $taxonomies as $taxonomy ) {

		// skip if taxonomy excluded.
		if ( in_array( $taxonomy->name, $excluded_taxes, true ) ) {
			continue;
		}

		// get terms attached to the post.
		$terms = gmw_get_the_terms( $post->ID, $taxonomy->name );

		if ( $terms && ! is_wp_error( $terms ) ) {

			// phpcs:disable.
			/*$defaults = array(
				'id'         => $args['id'],
				'class'      => $args['class'],
				'terms_link' => $args['terms_link'],
				'separator'  => $args['terms_lin'],
			);*/
			// phpcs:enable.

			// phpcs:ignore.
			// $args = wp_parse_args( $args, $defaults );
			$args = apply_filters( 'gmw_' . $taxonomy->name . '_taxonomy_list_args', $parent_args, $post, $taxonomy );

			$tax_output = array();
			$terms_list = array();

			// generate comma separated list of terms with or without a link.
			foreach ( $terms as $term ) {
				$terms_list[] = $args['terms_link'] ? '<a href="' . esc_url( get_term_link( $term->term_id, $taxonomy->name ) ) . '">' . esc_html( $term->name ) . '</a>' : esc_html( $term->name );
			}

			$tax_slug = ! empty( $taxonomy->rewrite['slug'] ) ? esc_attr( $taxonomy->rewrite['slug'] ) : '';

			$output .= '<div class="gmw-taxonomy-terms gmw-taxes ' . $tax_slug . ' ' . esc_attr( $args['class'] ) . '">';
			$output .= '<span class="gmw-tax-label">' . esc_attr( $taxonomy->label ) . ': </span>';
			$output .= '<span class="gmw-terms-wrapper">';
			$output .= join( $args['separator'], $terms_list );
			$output .= '</span>';
			$output .= '</div>';
		}
	}

	return $output;
}

/**
 * Generate the post location form.
 *
 * @see GMW_Location_Form class for list of argument.
 *
 * @param  array $args arguments.
 *
 * @return [type]       [description]
 */
function gmw_post_location_form( $args = array() ) {

	if ( ! empty( $args['post_id'] ) ) {
		$args['object_id'] = $args['post_id'];
	}

	// default args.
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

		if ( IS_ADMIN && isset( $_GET['post'] ) && absint( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			$args['object_id'] = wp_unslash( absint( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		} else {

			global $post;

			if ( isset( $post->ID ) ) {
				$args['object_id'] = $post->ID;
			} else {
				return;
			}
		}
	}

	require_once 'class-gmw-post-location-form.php';

	if ( ! class_exists( 'GMW_Post_Location_Form' ) ) {
		return;
	}

	// generate new location form.
	$location_form = new GMW_Post_Location_Form( $args );

	// display the location form.
	$location_form->display_form();
}

/**
 * Get the location of a post.
 *
 * @since 3.0
 *
 * @param  integer  $id  post ID to retrieve the default location of a specific post. Or location ID.
 *
 * to retrieve a specific location.
 *
 * @param  boolean  $by_location_id  when set to true the first argument has to be a location ID.
 *
 * @param  constant $output type of output object | array.
 *
 * @param  boolean  $cache get from cache? true | false.
 *
 * @return mixed  complete location object
 */
function gmw_get_post_location( $id = 0, $by_location_id = false, $output = OBJECT, $cache = true ) {

	if ( $by_location_id ) {
		return gmw_get_location( $id, $output, $cache );
	}

	// if no specific post ID pass, look for displayed post object.
	if ( empty( $id ) ) {

		global $post;

		if ( ! empty( $post ) ) {

			$id = $post->ID;

		} else {
			return;
		}
	}

	// get post location from database.
	return gmw_get_location_by_object( 'post', $id, $output, $cache );
}

/**
 * Get all locations of a post.
 *
 * @since 3.2
 *
 * @param  integer $post_id the post ID.
 *
 * @return mixed of locations.
 */
function gmw_get_post_locations( $post_id = 0 ) {

	// if no specific post ID pass, look for displayed post object.
	if ( empty( $post_id ) ) {

		global $post;

		if ( ! empty( $post ) ) {

			$post_id = $post->ID;

		} else {
			return;
		}
	}

	// get post location from database.
	return GMW_Location::get_locations_by_object( 'post', $post_id );
}

/**
 * Get the post location meta from database.
 *
 * This function is to be used with location metas.
 *
 * @since 3.0
 *
 * @param  integer $post_id the post ID.
 *
 * @param  mixed   $meta_keys string of a single or array of multiple meta keys to retrieve their values.
 *
 * @return [type]           [description]
 */
function gmw_get_post_location_meta( $post_id = false, $meta_keys = array() ) {

	// if no specific post ID pass, look for displayed post object.
	if ( empty( $post_id ) ) {

		global $post;

		if ( ! empty( $post ) ) {

			$post_id = $post->ID;

		} else {
			return;
		}
	}

	// Pass the object type. object ID and array of location meta keys.
	return gmw_get_location_meta_by_object( 'post', $post_id, $meta_keys );
}

/**
 * Get post locations data from database.
 *
 * The function returns locations data combined with posts data, such as post title, content, author...
 *
 * The function also verify that the post exists in database. That's in case
 *
 * That the post was deleted but the location still exists in database.
 *
 * @since 3.0
 *
 * @param integer $id - post ID by default. Can be location ID when second argument set to true.
 *
 * @param boolean $by_location_id - sert to true when first argument is location ID.
 *
 * @return mixed post data + location data
 */
function gmw_get_post_location_data( $id = 0, $by_location_id = false ) {

	$fields = implode(
		',',
		apply_filters(
			'gmw_get_post_location_data_fields',
			array(
				'gmw.ID',
				'gmw.object_id',
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
			),
			$id,
			$by_location_id
		)
	);

	if ( empty( $id ) ) {

		global $post;

		// try to get global post ID.
		if ( empty( $post->ID ) ) {
			return;
		}

		$id             = $post->ID;
		$by_location_id = false;
	}

	$id = (int) $id;

	global $wpdb;

	$fields      = esc_sql( $fields );
	$gmw_table   = esc_sql( $wpdb->base_prefix . 'gmw_locations' );
	$posts_table = esc_sql( $wpdb->prefix . 'posts' );

	// phpcs:disable
	if ( ! $by_location_id ) {

		$sql = $wpdb->prepare(
			"
            SELECT     $fields
            FROM       $gmw_table  gmw
            INNER JOIN $posts_table posts
            ON         gmw.object_id   = posts.ID
            WHERE      gmw.object_type = 'post'
            AND        gmw.object_id   = %d
        ",
			$id
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, unprepared SQL ok.

	} else {

		$sql = $wpdb->prepare(
			"
            SELECT     $fields
            FROM       $gmw_table  gmw
            INNER JOIN $posts_table posts
            ON         gmw.object_id = posts.ID
            WHERE      gmw.object_type = 'post'
            AND        gmw.ID = %d
        ",
			$id
		); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, unprepared SQL ok.
	}
	// phpcs:enable

	// phpcs:ignore.
	$location_data = $wpdb->get_row( $sql, OBJECT ); // db call ok, cache ok, unprepared SQL ok.

	return ! empty( $location_data ) ? $location_data : false;
}

/**
 * Get post locations data from database.
 *
 * The function returns all the locations of a specific post and the post object.
 *
 * @since 3.2
 *
 * @param integer $post_id the post ID.
 *
 * @return mixed array(
 *     'locations' => array of all the post's locations,
 *     'post'      => the post object
 * );
 */
function gmw_get_post_locations_data( $post_id = 0 ) {

	if ( empty( $post_id ) ) {

		global $post;

		// try to get global post ID.
		if ( empty( $post->ID ) ) {
			return;
		}

		$post_id = $post->ID;
	}

	$post = get_post( $post_id ); // phpcs:ignore: override ok.

	if ( empty( $post ) ) {
		return false;
	}

	$locations = GMW_Location::get_locations_by_object( 'post', $post_id );

	return empty( $locations ) ? false : array(
		'locations' => $locations,
		'post'      => $post,

	);
}

/**
 * Get specific or all post address fields
 *
 * @since 3.0
 *
 * @param  array $args array(
 *     'location_id' => 0,                   // when getting a specifc location by its ID.
 *     'post_id'     => 0,                   // When getting the default location of a post using the post ID.
 *     'fields'      => 'formatted_address', // address fields comma separated
 *     'separator'   => ', ',                // Separator between fields.
 *     'output'      => 'string'             // output type ( object, array or string ).
 * );.
 *
 * @return mixed object || array || string
 */
function gmw_get_post_address( $args = array() ) {

	// phpcs:disable.
	// to support older versions. should be removed in the future.
	/* If ( empty( $args['fields'] ) && ! empty( $args['info'] ) ) {

		Trigger_error( 'The "info" shortcode attribute of the shortcode [gmw_post_address] is deprecated since GEO my WP version 3.0. Please use the shortcode attribute "fields" instead.', E_USER_NOTICE );

		$args['fields'] = $args['info'];
	}*/
	// phpcs:enable.

	// if no specific post ID provided, look for current post ID.
	if ( empty( $args['location_id'] ) ) {

		$args['object_type'] = 'post';

		if ( empty( $args['post_id'] ) ) {

			global $post;

			if ( empty( $post ) ) {
				return;
			}

			$args['object_id'] = $post->ID;

		} else {

			$args['object_id'] = $args['post_id'];
		}
	}

	// get post address fields.
	return gmw_get_address_fields( $args );
}

/**
 * Display post address.
 *
 * @param  array $args arguments.
 */
function gmw_post_address( $args = array() ) {
	echo gmw_get_post_address( $args ); // phpcs:ignore: XSS ok.
}

/**
 * Get location or location meta fields of a post.
 *
 * @since 3.0.2
 *
 * @param  array $args array(
 *     'location_id'   => 0,                   // when getting a specifc location by its ID.
 *     'post_id'       => 0,                   // When getting the default location of a post using the post ID.
 *     'fields'        => 'formatted_address', // location or meta fields, comma separated.
 *     'separator'     => ', ',                // Separator between fields.
 *     'location_meta' => 0                    // Set to 1 when the fields argument is location meta.
 * );.
 *
 * @return mixed object || array || string
 */
function gmw_get_post_location_fields( $args = array() ) {

	if ( empty( $args['location_id'] ) ) {

		$args['object_type'] = 'post';

		if ( ! empty( $args['post_id'] ) ) {

			$args['object_id'] = $args['post_id'];

		} else {

			global $post;

			if ( empty( $post ) ) {
				return;
			}

			$args['object_id'] = $post->ID;
		}
	}

	return gmw_get_location_fields( $args );
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
 * @param  integer         $post_id  int ( post ID, user ID, comment ID... ).
 * @param  string || array $location to pass an address it can be either a string or an array of address field for example:
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
 * );.
 *
 * @param  integer         $user_id the user whom the location belongs to. By default it belongs to the user who creates/update the post location ( logged in user ).
 *
 * @param  string          $location_name name of the location ( optional ).
 *
 * @param  boolean         $force_refresh false to use geocoded address in cache || true to force address geocoding.
 *
 * @return mixed location ID
 */
function gmw_update_post_location( $post_id = 0, $location = array(), $user_id = 0, $location_name = '', $force_refresh = false ) {

	if ( ! gmw_is_post_exists( $post_id ) ) {
		return;
	}

	if ( ! is_string( $location_name ) ) {
		$force_refresh = $location_name;
		$location_name = '';
	}

	// Get post title if location name was not provided.
	if ( empty( $location_name ) && ! empty( $post_id ) ) {
		$location_name = get_the_title( $post_id );
	}

	$args = array(
		'object_type'   => 'post',
		'object_id'     => $post_id,
		'location_name' => is_string( $location_name ) ? $location_name : '',
		'user_id'       => $user_id,
	);

	// update post location.
	return gmw_update_location( $args, $location, $force_refresh );
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
 * @param  integer $post_id    post ID.
 * @param  array   $metadata   meta keys.
 * @param  boolean $meta_value meta values.
 */
function gmw_update_post_location_meta( $post_id = 0, $metadata = array(), $meta_value = false ) {

	// look for location ID.
	$location_id = gmw_get_location_id( 'post', $post_id );

	// abort if location not exists.
	if ( empty( $location_id ) ) {
		return false;
	}

	gmw_update_location_metas( $location_id, $metadata, $meta_value );
}

/**
 * Delete post location
 *
 * @since 3.0
 *
 * @param  integer $post_id the post ID.
 *
 * @param  boolean $delete_meta true || false if to also delete location meta.
 */
function gmw_delete_post_location( $post_id = false, $delete_meta = true ) {

	if ( empty( $post_id ) ) {
		return;
	}

	gmw_delete_location_by_object( 'post', $post_id, $delete_meta );
}

/**
 * Change post location status
 *
 * @since 3.0
 *
 * @param  integer $post_id post ID.
 * @param  integer $status  status.
 */
function gmw_post_location_status( $post_id = 0, $status = 1 ) {

	$status = 1 === absint( $status ) ? 1 : 0;

	global $wpdb;

	// phpcs:disable
	$wpdb->query(
		$wpdb->prepare(
			"
            UPDATE {$wpdb->base_prefix}gmw_locations
            SET   `status`      = %d
            WHERE `object_type` = 'post'
            AND   `blog_id`     = %d
            AND   `object_id`   = %d",
			array( $status, gmw_get_blog_id(), $post_id )
		)
	); // WPCS: unprepared SQL ok, db call ok, cache ok.
	// phpcs:enable
}

/**
 * Change location status in database when post status changes
 *
 * @param  string $new_status new status.
 * @param  string $old_status old status.
 * @param  object $post       post object.
 */
function gmw_transition_post_status( $new_status, $old_status, $post ) {

	$status = ( 'publish' === $new_status ) ? 1 : 0;

	gmw_post_location_status( $post->ID, $status );
}
add_action( 'transition_post_status', 'gmw_transition_post_status', 10, 3 );

/**
 * Change post status when post sent to trash
 *
 * @param  integer $post_id post ID.
 */
function gmw_trash_post_location( $post_id ) {

	gmw_post_location_status( $post_id, 0 );
}
add_action( 'wp_trash_post', 'gmw_trash_post_location' );

/**
 * Delete info from our database after post was deleted.
 *
 * @param integer $post_id post ID.
 */
function gmw_after_delete_post( $post_id ) {

	gmw_delete_post_location( $post_id, true );
}
add_action( 'after_delete_post', 'gmw_after_delete_post' );

/**
 * Load post info-window contant via AJAX.
 *
 * @param object $location location object.
 *
 * @param array  $gmw gmw form.
 *
 * @since 4.4.0.3
 *
 * @return void
 */
function gmw_posts_locator_ajax_info_window_loader( $location, $gmw ) {

	if ( empty( $location ) || ! isset( $location->object_id ) || ! isset( $location->location_id ) ) {
		return;
	}

	global $post;

	// get the post object.
	$post = get_post( $location->object_id ); // phpcs:ignore override ok.

	// get additional post location data.
	$location_data = gmw_get_post_location( $location->location_id, true );
	$fields        = array(
		'object_type',
		'object_id',
		'lat',
		'lng',
		'latitude',
		'longitude',
		'street',
		'premise',
		'city',
		'region_code',
		'region_name',
		'postcode',
		'country_code',
		'country_name',
		'address',
		'formatted_address',
		'location_name',
		'featured_location',
	);

	// append location to the post object.
	foreach ( $fields as $field ) {

		if ( isset( $location_data->$field ) ) {
			$post->$field = $location_data->$field;
		}
	}

	// get location meta if needed and append it to the post.
	if ( ! empty( $gmw['info_window']['location_meta'] ) ) {
		$post->location_meta = gmw_get_location_meta( $location->location_id, $gmw['info_window']['location_meta'] );
	}

	// append distance + units to the post.
	$post->distance = isset( $location->distance ) ? $location->distance : '';
	$post->units    = isset( $location->units ) ? $location->units : '';

	// filter post object.
	$post = apply_filters( 'gmw_' . $gmw['prefix'] . '_post_before_info_window', $post, $gmw ); // phpcs:ignore override ok.

	$file_path   = realpath( $gmw['info_window_template']['content_path'] );
	$base_path   = realpath( GMW_PT_PATH . '/templates' ) . DIRECTORY_SEPARATOR;
	$base_custom = realpath( get_stylesheet_directory() . '/geo-my-wp' ) . DIRECTORY_SEPARATOR;

	if ( false === $file_path || strpos( $file_path, $base_path ) !== 0 && strpos( $file_path, $base_custom ) !== 0 ) {

		gmw_trigger_error( 'Info-window template file is missing.' );

		return;
	}

	require $gmw['info_window_template']['content_path'];

	do_action( 'gmw_' . $gmw['prefix'] . '_after_post_info_window', $gmw, $post );
}
