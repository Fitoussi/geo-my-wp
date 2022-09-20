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
 * User to extend the Posts Locator Form classes.
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

		$fields       = ', ' . $this->db_fields;
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

			// When address provided, and not filtering based on address fields, we will do proximity search.
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
			$lat          = esc_sql( $this->form['lat'] );
			$lng          = esc_sql( $this->form['lng'] );
			$distance     = ! empty( $this->form['radius'] ) ? esc_sql( $this->form['radius'] ) : '';
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

				$where .= ' AND ( gmw_locations.latitude != 0.000000 && gmw_locations.longitude != 0.000000 ) ';
			}

			$where .= ' ' . $address_filters;
		}

		$clauses['fields'] .= " {$fields}, {$distance_sql}, '{$units}' AS units";
		$clauses['join']   .= $join;
		$clauses['where']  .= $where;
		$clauses['having']  = $having;

		if ( empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] = $wpdb->prefix . 'posts.ID';
		}

		// modify the clauses.
		$clauses = apply_filters( 'gmw_posts_locator_locations_query_clauses', $clauses, $this->form, $this );
		$clauses = apply_filters( 'gmw_' . $this->form['prefix'] . '_posts_query_clauses', $clauses, $this->form );
		$clauses = apply_filters( 'gmw_' . $this->form['prefix'] . '_location_query_clauses', $clauses, $this->form );

		// add having clause.
		if ( ! empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] .= ' ' . $clauses['having'];
		} else {
			$clauses['where'] .= ' ' . $clauses['having'];
		}

		unset( $clauses['having'] );

		return $clauses;
	}

	/**
	 * Pass an array of arguments to the search query.
	 *
	 * Arguments can be modified using the filter:
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	 *
	 * The filter can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->parse_query_args();
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_query_args() {

		// get the post types from page load settings.
		if ( $this->form['page_load_action'] ) {

			$post_types = $this->form['page_load_results']['post_types'];

			// otherwise, on form submission.
		} else {

			// Check in submitted values.
			if ( ! empty( $this->form['form_values']['post'] ) && array_filter( $this->form['form_values']['post'] ) ) {

				$post_types = $this->form['form_values']['post'];

				// otherwise grab all the post types from the search form settings.
			} else {

				$post_types                        = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : array( 'post' );
				$this->form['form_values']['post'] = $post_types;
			}
		}

		$tax_query = array();
		// tax query can be disable if a custom query is needed.
		if ( apply_filters( 'gmw_enable_taxonomy_search_query', true, $this->form, $this ) ) {
			//$tax_args = ! empty( $this->form['form_values']['tax'] ) ? gmw_pt_get_tax_query_args( $this->form['form_values']['tax'], $this->form ) : array();
			$tax_query = gmw_generate_tax_query( $this->form );
		}

		// query args.
		return array(
			'post_type'           => $post_types,
			'post_status'         => array( 'publish' ),
			'tax_query'           => $tax_query, // WPCS: slow query ok.
			'posts_per_page'      => ! empty( $this->form['per_page'] ) ? $this->form['per_page'] : -1,
			'paged'               => $this->form['paged'],
			'orderby'             => $this->form['orderby'],
			'order'               => 'post_modified' === $this->form['orderby'] || 'post_date' === $this->form['orderby'] ? 'DESC' : 'ASC',
			'ignore_sticky_posts' => 1,
			// below we can save on performance when showing map only ( without the list of results ).
			'no_found_rows'       => $this->form['results_enabled'] ? false : true,
			'fields'              => '*',
		);
	}

	/**
	 * Execute the search query.
	 *
	 * There are various filters that can be used to filter the form object and the query before and after the search query takes place.
	 *
	 * The filters can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->pre_search_query_hooks() and GMW_Form_Core->post_search_query_hooks();
	 *
	 * Pre search query filters:
	 *
	 * apply_filters( 'gmw_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['component'] . '_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_search_query', $this->form, $this );
	 *
	 * Post search query filters:
	 *
	 * // Modify the form object.
	 * $this->form  = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_after_search_query', $this->form, $this );
	 *
	 * // Modify the search query.
	 * $this->query = apply_filters( 'gmw_' . $this->form['prefix'] . '_query_after_search_query', $this->query, $this->form, $this );
	 *
	 * @since 4.0
	 */
	public function parse_search_query() {

		// add filters to wp_query to do radius calculation and get locations detail into results.
		add_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

		$this->query = new WP_Query( $this->form['query_args'] );

		remove_filter( 'posts_clauses', array( $this, 'query_clauses' ) );

		$this->query->request = null;
	}

	/**
	 * Parse the search query results.
	 *
	 * @since 4.0
	 */
	public function parse_query_results() {

		if ( ! empty( $this->query->posts ) ) {

			$this->form['results']       = $this->query->posts;
			$this->form['results_count'] = count( $this->query->posts );
			$this->form['total_results'] = $this->query->found_posts;
			$this->form['max_pages']     = $this->query->max_num_pages;
		}
	}

	/**
	 * The posts loop.
	 *
	 * To be used when displaying map only without the results.
	 *
	 * In that case we need to run the loop in order to collect some data for the map.
	 *
	 * @since 4.0
	 *
	 * @param  boolean $include [description].
	 */
	public function object_loop( $include = false ) {

		// The variables are for AJAX forms deprecated template files. To be removed.
		$gmw       = $this->form;
		$gmw_form  = $this;
		$gmw_query = $this->query;

		while ( $this->query->have_posts() ) :

			$this->query->the_post();

			global $post;

			// This action is required. Do not remove.
			do_action( 'gmw_the_object_location', $post, $this->form );

			// For AJAX forms deprecated template files. To be removed.
			if ( $include ) {

				if ( empty( $this->form['search_results']['styles']['disable_single_item_template'] ) ) {
					include $this->form['results_template']['content_path'] . 'single-result.php';
				} else {
					do_action( 'gmw_search_results_single_item_template', $post, $this->form );
				}
			}

		endwhile;
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
}
