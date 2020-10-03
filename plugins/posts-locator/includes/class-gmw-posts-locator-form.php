<?php
/**
 * GEO my WP Posts Locator Class.
 *
 * The class queries posts based on location.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_PT_Search_Query class
 */
class GMW_Posts_Locator_Form extends GMW_Form {

	/**
	 * Gmw_location database fields that will be
	 *
	 * Added to the posts query.
	 *
	 * @var array
	 */
	/**
	Public $db_fields = array(
		'ID as location_id',
		'object_type',
		'object_id',
		'featured as featured_location',
		'user_id',
		'latitude as lat',
		'longitude as lng',
		'street',
		'city',
		'region_name',
		'postcode',
		'country_code',
		'address',
		'formatted_address',
		'map_icon',
	); */

	/**
	 * Permalink hook
	 *
	 * @var string
	 */
	public $object_permalink_hook = 'the_permalink';

	/**
	 * Info window data
	 *
	 * @param  object $post the post object.
	 *
	 * @return array of arg
	 */
	public function get_info_window_args( $post ) {

		if ( empty( $this->form['info_window']['image']['enabled'] ) ) {

			$image = false;

		} else {
			$image = gmw_get_post_featured_image(
				$post,
				$this->form,
				array(),
				array( 'class' => 'skip-lazy' )
			);
		}

		return array(
			'prefix' => $this->prefix,
			'type'   => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
			'image'  => $image,
			'url'    => get_permalink( $post->ID ),
			'title'  => $post->post_title,
		);
	}

	/**
	 * Modify wp_query clauses to search by distance
	 *
	 * @param array $clauses query clauses.
	 *
	 * @return modified $clauses
	 */
	public function query_clauses( $clauses ) {

		$count     = 0;
		$db_fields = '';

		// generate the db fields.
		foreach ( $this->db_fields as $field ) {

			if ( $count > 0 ) {
				$db_fields .= ', ';
			}

			$count++;

			if ( strpos( $field, 'as' ) !== false ) {

				$field = explode( ' as ', $field );

				$db_fields .= "gmw_locations.{$field[0]} as {$field[1]}";

				// Here we are including latitude and longitude fields
				// using their original field name.
				// for backward compatibility, we also need to have "lat" and "lng"
				// in the location object and that is what we did in the line above.
				// The lat and lng field are too involve and need to carfully change it.
				// eventually we want to completely move to using latitude and longitude.
				if ( 'latitude' === $field[0] || 'longitude' === $field[0] ) {
					$db_fields .= ",gmw_locations.{$field[0]}";
				}
			} else {

				$db_fields .= "gmw_locations.{$field}";
			}
		}

		global $wpdb;

		$where_clause_filter = apply_filters( 'gmw_filter_object_type_in_where_clause', false, $this->form );

		// add the location db fields to the query.
		$clauses['fields'] .= ", {$db_fields}";
		$clauses['having']  = '';
		$tjoin              = "{$wpdb->base_prefix}gmw_locations gmw_locations ON $wpdb->posts.ID = gmw_locations.object_id ";

		if ( ! $where_clause_filter ) {
			$tjoin .= "AND gmw_locations.object_type = 'post' ";
		}

		// In multisite we need to check for the blog ID.
		if ( is_multisite() && ! empty( $wpdb->blogid ) ) {
			$blog_id           = absint( $wpdb->blogid );
			$clauses['where'] .= "AND gmw_locations.blog_id = {$blog_id} ";
		}

		// get address filters query.
		$address_filters = GMW_Location::query_address_fields( $this->get_address_filters(), $this->form );

		// when address provided, and not filtering based on address fields, we will do proximity search.
		if ( empty( $address_filters ) && ! empty( $this->form['lat'] ) && ! empty( $this->form['lng'] ) ) {

			// generate some radius/units data.
			if ( in_array( $this->form['units_array']['units'], array( 'imperial', 3959, 'miles', '3959' ), true ) ) {
				$earth_radius = 3959;
				$units        = 'mi';
				$degree       = 69.0;
			} else {
				$earth_radius = 6371;
				$units        = 'km';
				$degree       = 111.045;
			}

			// add units to locations data.
			$clauses['fields'] .= ", '{$units}' AS units";

			// since these values are repeatable, we escape them previous
			// the query instead of running multiple prepares.
			$lat      = esc_sql( $this->form['lat'] );
			$lng      = esc_sql( $this->form['lng'] );
			$distance = ! empty( $this->form['radius'] ) ? esc_sql( $this->form['radius'] ) : '';

			$clauses['fields'] .= ", ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			$clauses['join'] .= "INNER JOIN {$tjoin}";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, 'gmw_pt' ) ) {

					// calculate the between point.
					$bet_lat1 = $lat - ( $distance / $degree );
					$bet_lat2 = $lat + ( $distance / $degree );
					$bet_lng1 = $lng - ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );
					$bet_lng2 = $lng + ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );

					$clauses['where'] .= " AND gmw_locations.latitude BETWEEN {$bet_lat1} AND {$bet_lat2}";
					// $clauses['where'] .= " AND gmw_locations.longitude BETWEEN {$bet_lng1} AND {$bet_lng2} ";
				}

				if ( $where_clause_filter ) {
					$clauses['where'] .= " AND gmw_locations.object_type = 'post'";
				}

				// filter locations based on the distance.
				$clauses['having'] = "HAVING distance <= {$distance} OR distance IS NULL";

				// Remove extra spaces.
				$this->form['query_args']['orderby'] = trim( $this->form['query_args']['orderby'] );

				// If there is another order-by parameter before the distance append the distance to the orderby clause.
				if ( false !== strpos( $this->form['query_args']['orderby'], ' distance' ) ) {

					$clauses['orderby'] .= ', distance ASC';

					// If there is another order-by parameter after the distance, prepend the distance first in the orderby clause.
				} elseif ( false !== strpos( $this->form['query_args']['orderby'], 'distance ' ) ) {

					$clauses['orderby'] = 'distance ASC, ' . $clauses['orderby'];

					// Otherise, we order by the distance only.
				} elseif ( 'distance' === $this->form['query_args']['orderby'] ) {

					$clauses['orderby'] = ! empty( $this->form['query_args']['order'] ) ? 'distance ' . $this->form['query_args']['order'] : 'distance';
				}
			}
		} else {

			// if showing posts without location.
			if ( $this->form['query_args']['gmw_args']['showing_objects_without_location'] ) {

				// left join the location table into the query to display posts with no location as well.
				$clauses['join']  .= " LEFT JOIN {$tjoin}";
				$clauses['where'] .= " {$address_filters} ";

			} else {

				$clauses['join']  .= " INNER JOIN {$tjoin}";
				$clauses['where'] .= " {$address_filters} AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 ) ";

				if ( $where_clause_filter ) {
					$clauses['where'] .= " AND gmw_locations.object_type = 'post'";
				}
			}
		}

		// modify the clauses.
		$clauses = apply_filters( 'gmw_pt_location_query_clauses', $clauses, $this->form );

		// make sure we have groupby to only pull posts one time.
		if ( empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] = $wpdb->prefix . 'posts.ID';
		}

		// add having clause.
		$clauses['groupby'] .= ' ' . $clauses['having'];

		unset( $clauses['having'] );

		return $clauses;
	}

	/**
	 * Query results
	 *
	 * @return [type] [description]
	 */
	public function search_query() {

		// get the post types from page load settings.
		if ( $this->form['page_load_action'] ) {

			$post_types = ! empty( $this->form['page_load_results']['post_types'] ) ? $this->form['page_load_results']['post_types'] : 'post';

			// when set to 1 means that we need to show all post types.
		} elseif ( ! empty( $this->form['form_values']['post'] ) && array_filter( $this->form['form_values']['post'] ) ) {

			$post_types = $this->form['form_values']['post'];

		} else {

			$post_types = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : 'post';
		}

		$this->query_cache_args['show_non_located'] = $this->enable_objects_without_location;

		// tax query can be disable if a custom query is needed.
		if ( apply_filters( 'gmw_enable_taxonomy_search_query', true, $this->form, $this ) ) {
			$tax_args = ! empty( $this->form['form_values']['tax'] ) ? gmw_pt_get_tax_query_args( $this->form['form_values']['tax'], $this->form ) : array();
		} else {
			$tax_args = array();
		}

		$meta_args = false;

		if ( empty( $this->form['get_per_page'] ) || -1 === $this->form['get_per_page'] ) {
			$this->form['get_per_page'] = -1;
		}

		// query args.
		$this->form['query_args'] = apply_filters(
			'gmw_pt_search_query_args',
			array(
				'post_type'           => $post_types,
				'post_status'         => array( 'publish' ),
				'tax_query'           => apply_filters( 'gmw_pt_tax_query', $tax_args, $this->form ), // WPCS: slow query ok.
				'posts_per_page'      => $this->form['get_per_page'],
				'paged'               => $this->form['paged'],
				'meta_query'          => apply_filters( 'gmw_pt_meta_query', $meta_args, $this->form ), // WPCS: slow query ok.
				'ignore_sticky_posts' => 1,
				'orderby'             => 'distance',
				'gmw_args'            => $this->query_cache_args,
			),
			$this->form,
			$this
		);

		$this->form     = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this );
		$internal_cache = GMW()->internal_cache;
		$this->query    = false;

		if ( $internal_cache ) {

			// cache key.
			$hash            = md5( wp_json_encode( $this->form['query_args'] ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_post_query' );
			$this->query     = get_transient( $query_args_hash );
		}

		// look for query in cache.
		if ( ! $internal_cache || empty( $this->query ) ) {

			// add filters to wp_query to do radius calculation and get locations detail into results.
			add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

			// posts query.
			$this->query = new WP_Query( $this->form['query_args'] );

			remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

			// set new query in transient.
			if ( $internal_cache ) {

				/**
				 * This is a temporary solution for an issue with caching SQL requests
				 * For some reason when LIKE is being used in SQL WordPress replace the % of the LIKE
				 * with long random numbers. This SQL is still being saved in the transient. Hoever,
				 * it is not being pulled back properly when GEO my WP trying to use it.
				 * It shows an error "unserialize(): Error at offset " and the value returns blank.
				 * As a temporary work around, we remove the [request] value, which contains the long numbers, from the WP_Query and save it in the transien without it.
				 *
				 * @var [type]
				 */
				unset( $this->query->request );

				set_transient( $query_args_hash, $this->query, GMW()->internal_cache_expiration );
			}
		}

		// Modify the form after the search query.
		$this->form = apply_filters( 'gmw_pt_form_after_posts_query', $this->form, $this );

		// make sure posts exist.
		if ( empty( $this->query->posts ) ) {
			return false;
		}

		$this->form['results']       = $this->query->posts;
		$this->form['results_count'] = count( $this->query->posts );
		$this->form['total_results'] = $this->query->found_posts;
		$this->form['max_pages']     = $this->query->max_num_pages;

		// if showing the list of results we use the 'the_post'
		// hook to generate the_location data.
		if ( $this->form['display_list'] ) {

			add_action( 'the_post', array( $this, 'the_post' ), 5 );

			add_action( 'gmw_shortcode_end', array( $this, 'remove_the_post' ) );

			// otherwise, if only the map shows, we need to run a loop
			// to generate the map data of each location.
		} else {

			foreach ( $this->form['results'] as $post ) {
				$this->map_locations[] = $this->get_map_location( $post, false );
			}
		}

		return $this->form['results'];
	}

	/**
	 * Generate the location data.
	 *
	 * @param object $post post object.
	 */
	public function the_post( $post ) {

		$post = parent::the_location( $post->ID, $post );

		return $post;
	}

	/**
	 * Remove the_post action hook when form completed
	 *
	 * @param  [type] $form gmw form.
	 */
	public function remove_the_post( $form ) {

		if ( absint( $this->form['ID'] ) === absint( $form['ID'] ) ) {
			remove_action( 'the_post', array( $this, 'the_post' ), 5 );
		}

		wp_reset_postdata();
	}
}
