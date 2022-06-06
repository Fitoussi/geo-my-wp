<?php
/**
 * GEO my WP Posts Locator Class.
 *
 * The class queries posts based on location.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extend the Posts Locator Form classes.
 *
 * @since 4.0.
 */
trait GMW_Posts_Locator_Form_Trait {

	/**
	 * Modify wp_query clauses to search by distance
	 *
	 * @param array $clauses query clauses.
	 *
	 * @return modified $clauses
	 */
	public function query_clauses( $clauses ) {

		global $wpdb;

		// add the location db fields to the query.
		$fields       = ', gmw_locations.' . implode( ', gmw_locations.', $this->db_fields );
		$having       = '';
		$join         = "INNER JOIN {$wpdb->base_prefix}gmw_locations gmw_locations ON ( $wpdb->posts.ID = gmw_locations.object_id AND gmw_locations.object_type = 'post' ) ";
		$where        = '';
		$units        = '';
		$distance_sql = "'' AS distance";

		// In multisite we need to check for the blog ID.
		if ( is_multisite() && ! empty( $wpdb->blogid ) ) {
			$blog_id = absint( $wpdb->blogid );
			$where   = " AND gmw_locations.blog_id = {$blog_id} ";
		}

		// get address filters query.
		$address_filters = gmw_get_address_fields_filters_sql( $this->form['address_filters'], $this->form );

		// search within map bounderies.
		if ( ! empty( $this->form['form_values']['nelatlng'] ) && ! empty( $this->form['form_values']['swlatlng'] ) ) {

			$where .= gmw_get_locations_within_bounderies_sql( $this->form['form_values']['swlatlng'], $this->form['form_values']['nelatlng'] );

		// when address provided, and not filtering based on address fields, we will do proximity search.
		} elseif ( empty( $address_filters ) && ! empty( $this->form['lat'] ) && ! empty( $this->form['lng'] ) ) {

			// generate some radius/units data.
			if ( 'imperial' === $this->form['units'] ) {
				$earth_radius = 3959;
				$units        = 'mi';
				$degree       = 69.0;
			} else {
				$earth_radius = 6371;
				$units        = 'km';
				$degree       = 111.045;
			}

			// since these values are repeatable, we escape them previous
			// the query instead of running multiple prepares.
			$lat      = esc_sql( $this->form['lat'] );
			$lng      = esc_sql( $this->form['lng'] );
			$distance = ! empty( $this->form['radius'] ) ? esc_sql( $this->form['radius'] ) : '';

			$distance_sql = "ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, 'gmw_' . $this->form['prefix'], $this->form ) ) {

					// calculate the between point.
					$bet_lat1 = $lat - ( $distance / $degree );
					$bet_lat2 = $lat + ( $distance / $degree );
					$bet_lng1 = $lng - ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );
					$bet_lng2 = $lng + ( $distance / ( $degree * cos( deg2rad( $lat ) ) ) );

					$where .= " AND gmw_locations.latitude BETWEEN {$bet_lat1} AND {$bet_lat2}";
					// $clauses['where'] .= " AND gmw_locations.longitude BETWEEN {$bet_lng1} AND {$bet_lng2} ";
				}

				// filter locations based on the distance.
				$having = "HAVING distance <= {$distance} OR distance IS NULL";
			}

			// Remove extra spaces.
			$this->form['query_args']['orderby'] = ! empty( $this->form['query_args']['orderby'] ) ? trim( $this->form['query_args']['orderby'] ) : 'distance';

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
		} else {

			// if showing posts without location.
			if ( ! empty( $this->enable_objects_without_location ) ) {

				// left join the location table into the query to display posts with no location as well.
				$join = str_replace( 'INNER', 'LEFT', $join );

			} else {

				$where .= " AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 ) ";
			}

			$where .= ' ' . $address_filters;
		}

		$clauses['fields'] .= " {$fields}, {$distance_sql}, '{$units}' AS units";
		$clauses['join']   .= $join;
		$clauses['where']  .= $where;
		$clauses['having']  = $having;

		// modify the clauses.
		$clauses = apply_filters( 'gmw_posts_locator_locations_query_clauses', $clauses, $this->form, $this );
		$clauses = apply_filters( 'gmw_' . $this->form['prefix'] . '_location_query_clauses', $clauses, $this->form );

		// make sure we have groupby to only pull posts one time.
		if ( empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] = $wpdb->prefix . 'posts.ID';
		}

		// add having clause.
		$clauses['groupby'] .= ' ' . $clauses['having'];

		unset( $clauses['having'] );

		return $clauses;
	}
}

/**
 * Posts Locator form class.
 *
 * @package geo-my-wp
 */
class GMW_Posts_Locator_Form extends GMW_Form {

	/**
	 * Inherit search queries fromt Trait.
	 */
	use GMW_Posts_Locator_Form_Trait;

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

		if ( isset( $this->form['info_window']['image'] ) && empty( $this->form['info_window']['image']['enabled'] ) ) {

			$image = false;

		} else {

			$args = array(
				'object_type'  => 'post',
				'object_id'    => $post->ID,
				'width'        => ! empty( $this->form['info_window']['image']['width'] ) ? $this->form['info_window']['image']['width'] : '150px',
				'height'       => ! empty( $this->form['info_window']['image']['height'] ) ? $this->form['info_window']['image']['height'] : 'auto',
				'no_image_url' => ! empty( $this->form['info_window']['image']['no_image_url'] ) ? $this->form['info_window']['image']['no_image_url'] : '',
				'permalink'    => false,
				'wrapper'      => false,
				'where'        => 'info_window',
			);

			$image = gmw_get_post_featured_image( $args, $post, $this->form );
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

			$post_types                        = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : 'post';
			$this->form['form_values']['post'] = $post_types;
		}

		$tax_query = array();
		
		// tax query can be disable if a custom query is needed.
		if ( apply_filters( 'gmw_enable_taxonomy_search_query', true, $this->form, $this ) ) {
			//$tax_args = ! empty( $this->form['form_values']['tax'] ) ? gmw_pt_get_tax_query_args( $this->form['form_values']['tax'], $this->form ) : array();
			$tax_query = gmw_generate_tax_query( $this->form );
		}

		if ( empty( $this->form['get_per_page'] ) ) {
			$this->form['get_per_page'] = -1;
		}

		$this->query_cache_args['post_types']       = $post_types;
		$this->query_cache_args['show_non_located'] = $this->enable_objects_without_location;

		// query args.
		$this->form['query_args'] = apply_filters(
			'gmw_pt_search_query_args',
			array(
				'post_type'           => $post_types,
				'post_status'         => array( 'publish' ),
				'tax_query'           => $tax_query, // WPCS: slow query ok.
				'posts_per_page'      => $this->form['get_per_page'],
				'paged'               => $this->form['paged'],
				'ignore_sticky_posts' => 1,
				'orderby'             => 'distance',
				'gmw_args'            => $this->query_cache_args,
				// below we can save on performance when showing map only ( without the list of results ).
				'no_found_rows'       => $this->form['display_list'] ? false : true,
				//'fields'              => $this->form['display_list'] ? '*' : 'id=>parent',
			),
			$this->form,
			$this
		);

		$this->form = apply_filters( 'gmw_posts_locator_form_before_posts_query', $this->form, $this );
		$this->form = apply_filters( 'gmw_pt_form_before_posts_query', $this->form, $this );

		$internal_cache = GMW()->internal_cache;
		$this->query    = false;

		if ( $internal_cache ) {

			// cache key.
			$hash            = md5( wp_json_encode( $this->form['query_args'] ) );
			$query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_post_query' );
			$this->query     = get_option( $query_args_hash );
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
				unset( $this->query->request, $this->query->query_vars['search_orderby_title'] );

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
