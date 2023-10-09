<?php
/**
 * GEO my WP WP Query class.
 *
 * This class extends the WP_Query class to allow filtering posts based on location and distance.
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GEO my WP WP Query class.
 *
 * THis class extends the WP_Query class to allow filtering posts based on location and distance.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_WP_Query extends WP_Query {

	/**
	 * GEO my WP default query vars.
	 *
	 * @since 4.0
	 *
	 * @var array
	 */
	public static $gmw_vars = array(
		'gmw_enabled'                         => true,
		'gmw_address'                         => '',
		'gmw_lat'                             => '',
		'gmw_lng'                             => '',
		'gmw_radius'                          => '100',
		'gmw_units'                           => 'metric',
		'gmw_address_filters'                 => array(),
		'gmw_swlatlng'                        => '',
		'gmw_nelatlng'                        => '',
		'gmw_enable_objects_without_location' => true,
		'gmw_map_locations'                   => false,
		'gmw_info_window'                     => true,
		'gmw_object_type'                     => 'post',
	);

	/**
	 * GMW Form when Available.
	 *
	 * @since 4.0
	 *
	 * @var array
	 */
	public $gmw_form = array();

	/**
	 * Collect map locations.
	 *
	 * @since 4.0
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * [__construct description]
	 *
	 * @since 4.0
	 *
	 * @param string $query [description].
	 *
	 * @param array  $gmw   gmw form.
	 */
	public function __construct( $query = '', $gmw = array() ) {

		if ( empty( $query ) ) {
			$query = array();
		}

		// Pass GEO my WP form to WP_Query.
		$this->gmw_form = $gmw;

		// Append GEO my WP default args to WP_Query args.
		$query = wp_parse_args( $query, self::$gmw_vars );

		// Causes conflict with object cache.
		add_filter( 'split_the_query', '__return_false' );

		// Filter the search query.
		add_filter( 'posts_clauses', array( 'GMW_WP_Query', 'gmw_locations_query' ), 10, 2 );

		parent::__construct( $query );
	}

	/**
	 * Info window data.
	 *
	 * @return array of info window args.
	 */
	public function get_info_window_args( $post ) {

		return array(
			'prefix' => 'gmw_wpq',
			'type'   => 'standard',
			'url'    => get_permalink( $post->ID ),
			'title'  => $post->post_title,
		);
	}

	/**
	 * Get post's map location.
	 *
	 * @since 4.0
	 */
	public function the_post_map_location( $post ) {

		$iw_args  = ! empty( $this->query_vars['gmw_info_window'] ) ? $this->get_info_window_args( $post ) : array();
		$location = gmw_get_object_map_location( $post, $iw_args, $this->gmw_form );

		if ( $location ) {
			$this->map_locations[] = $location;
		}
	}

	/**
	 * Modify the_post() to allow collecting map locations if needed.
	 *
	 * @since 4.0
	 */
	public function the_post() {

		parent::the_post();

		// Collect location for map.
		if ( ! empty( $this->query_vars['gmw_map_locations'] ) ) {

			global $post;

			$this->the_post_map_location( $post );
		}
	}

	/**
	 * Modify the WP_Query clauses to perform proximity search.
	 *
	 * This function execute automatically when using the GMW_WP_Query class.
	 *
	 * However, it can also be used with the WP_Query when passing some or all of the query arguments below.
	 *
	 * First, to enable the function you need to add the below line of code to the functions.php file of your theme.
	 *
	 * add_filter( 'posts_clauses', array( 'GMW_WP_Query', 'gmw_locations_query' ), 20, 2 );
	 *
	 * Then you will need to pass some or all of the arguments below to the WP_Query(). Either directly or by filtering the WP_Query.
	 *
	 * A rqeuired argument is the 'gmw_enabled' that needs to be set to "true". This is what enables the function.
	 *
	 * Then you can pass an adress and or coordinates along with the distance and other arguments.
	 *
	 * The available arguments are listed below:
	 *
	 * array(
	 *  'gmw_address'                         => '',
	 *  'gmw_lat'                             => '',
	 *  'gmw_lng'                             => '',
	 *  'gmw_radius'                          => '100',
	 *  'gmw_units'                           => 'metric',
	 *  'gmw_address_filters'                 => array(),
	 *  'gmw_swlatlng'                        => '',
	 *  'gmw_nelatlng'                        => '',
	 *  'gmw_enable_objects_without_location' => true,
	 *  'gmw_object_type'                     => 'post',
	 * );
	 *
	 * @param  array  $clauses original WP_Query posts clauses.
	 *
	 * @param  object $object  WP_Query object.
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 4.0
	 *
	 * @return array modified $clauses
	 */
	public static function gmw_locations_query( $clauses, $object ) {

		// Abort if GEO my WP filtering is disabled.
		if ( empty( $object->query_vars['gmw_enabled'] ) ) {
			return $clauses;
		}

		// Check if we are running this script within the GMW_WP_Query.
		// If so then the below is not requried as it is already done during the __construct() function.
		if ( ! isset( $object->gmw_form ) ) {

			$defaults = array(
				'gmw_address'                         => '',
				'gmw_lat'                             => '',
				'gmw_lng'                             => '',
				'gmw_radius'                          => '100',
				'gmw_units'                           => 'metric',
				'gmw_address_filters'                 => array(),
				'gmw_swlatlng'                        => '',
				'gmw_nelatlng'                        => '',
				'gmw_enable_objects_without_location' => true,
				'gmw_object_type'                     => 'post',
			);

			$object->query_vars = wp_parse_args( $object->query_vars, self::$gmw_vars );
		}

		$args = $object->query_vars;

		// Look for GEo my WP's form in the GMW_WP_Query.
		if ( ! empty( $object->gmw_form ) ) {

			$gmw = $object->gmw_form;

			// Otherwise, look in the query args.
		} elseif ( ! empty( $object->query_vars['gmw_form'] ) ) {

			$gmw = $object->query_vars['gmw_form'];

			// Lastly, if we can't find the form, we will create a fake form.
		} else {

			$gmw                = $args;
			$gmw['ID']          = wp_rand( 10, 500 );
			$gmw['object_type'] = 'post';
			$gmw['prefix']      = 'gmw_wpq';
			$gmw['component']   = 'gmw_wp_query';
			$gmw['addon']       = 'gmw_wp_query';
		}

		// Let's make sure address is not an array.
		if ( is_array( $args['gmw_address'] ) ) {
			$args['gmw_address'] = trim( implode( ' ', $args['gmw_address'] ) );
		}

		global $wpdb;

		$location_status = '';

		if ( apply_filters( 'gmw_search_query_location_status_enabled', true, $gmw ) ) {
			$location_status = ' AND gmw_locations.status = 1';
		}

		$blog_id      = gmw_get_blog_id( 'post' );
		$fields       = ', ' . implode( ',', gmw_parse_form_db_fields( array(), $gmw ) );
		$having       = '';
		$join         = " INNER JOIN {$wpdb->base_prefix}gmw_locations gmw_locations ON ( $wpdb->posts.ID = gmw_locations.object_id AND gmw_locations.object_type = '{$args['gmw_object_type']}' AND gmw_locations.blog_id = {$blog_id}{$location_status} ) ";
		$where        = '';
		$units        = '';
		$distance_sql = "'' AS distance";

		// get address filters query.
		$address_filters = gmw_get_address_fields_filters_sql( $args['gmw_address_filters'], $gmw );

		// search within map boundaries.
		if ( ! empty( $args['gmw_swlatlng'] ) && ! empty( $args['gmw_nelatlng'] ) ) {

			$where .= gmw_get_locations_within_boundaries_sql( $args['gmw_swlatlng'], $args['gmw_nelatlng'] );

			// When address provided, and not filtering based on address fields, we will do proximity search.
		} elseif ( empty( $address_filters ) && ( ! empty( $args['gmw_address'] ) || ( ! empty( $args['gmw_lat'] ) && ! empty( $args['gmw_lng'] ) ) ) ) {

			if ( empty( $args['gmw_lat'] ) ) {

				$geocoded = gmw_geocoder( $args['gmw_address'] );

				if ( empty( $geocoded['lat'] ) ) {

					$clauses['where'] .= ' AND 1 = 0';

					return $clauses;
				}

				$args['gmw_lat'] = $geocoded['lat'];
				$args['gmw_lng'] = $geocoded['lng'];
			}

			// generate some radius/units data.
			if ( 'imperial' === $args['gmw_units'] ) {
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
			$lat          = esc_sql( $args['gmw_lat'] );
			$lng          = esc_sql( $args['gmw_lng'] );
			$distance     = ! empty( $args['gmw_radius'] ) ? esc_sql( $args['gmw_radius'] ) : '';
			$distance_sql = "ROUND( {$earth_radius} * acos( cos( radians( {$lat} ) ) * cos( radians( gmw_locations.latitude ) ) * cos( radians( gmw_locations.longitude ) - radians( {$lng} ) ) + sin( radians( {$lat} ) ) * sin( radians( gmw_locations.latitude ) ) ),1 ) AS distance";

			if ( ! empty( $distance ) ) {

				if ( ! apply_filters( 'gmw_disable_query_clause_between', false, 'gmw_' . $gmw['prefix'], $gmw ) ) {

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
			$orderby = ! empty( $args['orderby'] ) ? trim( $args['orderby'] ) : 'distance';

			// If there is another order-by parameter before the distance append the distance to the orderby clause.
			if ( false !== strpos( $orderby, ' distance' ) ) {

				$clauses['orderby'] .= ', distance ASC';

				// If there is another order-by parameter after the distance, prepend the distance first in the orderby clause.
			} elseif ( false !== strpos( $orderby, 'distance ' ) ) {

				$clauses['orderby'] = 'distance ASC, ' . $clauses['orderby'];

				// Otherise, we order by the distance only.
			} elseif ( 'distance' === $orderby ) {

				$clauses['orderby'] = ! empty( $args['order'] ) ? 'distance ' . $args['order'] : 'distance';
			}
		} else {

			// if showing posts without location.
			if ( ! empty( $args['gmw_enable_objects_without_location'] ) ) {

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

		// Deprecated filter. Use the below instead.
		$clauses = apply_filters( 'gmw_' . $gmw['prefix'] . '_location_query_clauses', $clauses, $gmw, $object );

		// modify the clauses.
		$clauses = apply_filters( 'gmw_posts_locator_query_clauses', $clauses, $gmw, $object );
		$clauses = apply_filters( 'gmw_' . $gmw['prefix'] . '_posts_query_clauses', $clauses, $gmw, $object );

		// add having clause.
		if ( ! empty( $clauses['groupby'] ) ) {
			$clauses['groupby'] .= ' ' . $clauses['having'];
		} else {
			$clauses['where'] .= ' ' . $clauses['having'];
		}

		unset( $clauses['having'] );

		return $clauses;
	}
}
