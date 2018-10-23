<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *  GMW_Sweet_Date_Geolocation class
 */
class GMW_Sweet_Date_Geolocation {

	public $prefix = 'sdate_geo';

	/**
	 * gmw_location database fields that will be pulled in the search query
	 *
	 * The fields can be modified using the filter 'gmw_database_fields'
	 *
	 * @var array
	 */
	public $db_fields = array(
		'',
		'ID as location_id',
		'object_type',
		'object_id',
		'user_id',
		'featured',
		'lat',
		'lng',
		'street',
		'city',
		'region_name',
		'postcode',
		'country_code',
		'address',
		'formatted_address',
		'map_icon',
	);

	/**
	 * GEO my WP locations holder
	 *
	 * @var array
	 */
	public $locations_data = array();

	/**
	 * IDs of objects with location
	 *
	 * @var array
	 */
	public $objects_id = array();

	/**
	 * Map locations holder
	 *
	 * @var array
	 */
	public $map_locations = array();

	/**
	 * Constructor
	 */
	public function __construct() {

		// Sweet-date options
		$this->options = gmw_get_options_group( 'sweet_date' );

		// abort if the add-on is not yet setup
		if ( empty( $this->options ) ) {
			return;
		}

		// default values
		$this->form_data = array(
			'language'      => gmw_get_option( 'general_settings', 'langugae_code', 'EN' ),
			'region'        => gmw_get_option( 'general_settings', 'country_code', 'US' ),
			'address'       => false,
			'lat'           => false,
			'lng'           => false,
			'address_found' => false,
			'radius'        => false,
			'orderby'       => '',
		);

		// labels
		$this->labels      = $this->labels();
		$this->units_label = ( '6371' == $this->options['units'] ) ? $this->labels['km'] : $this->labels['mi'];

		$doing_ajax = defined( 'DOING_AJAX' ) ? true : false;

		// get the default addres value from URL if exists
		if ( ! $doing_ajax && ! empty( $_GET['address'] ) ) {

			$this->form_data['address'] = sanitize_text_field( stripslashes( $_GET['address'] ) );

			// otherwise, check in cookies
		} elseif ( ! empty( $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) && 'undefined' != $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) {

			$this->form_data['address'] = urldecode( stripslashes( $_COOKIE[ 'gmw_' . $this->prefix . '_address' ] ) );
		}

		// orderby value from URL if exists
		if ( ! $doing_ajax && ! empty( $_GET['orderby'] ) ) {

			$this->form_data['orderby'] = sanitize_text_field( $_GET['orderby'] );

			// otherwise, check in cookies
		} elseif ( ! empty( $_COOKIE[ 'gmw_' . $this->prefix . '_orderby' ] ) && $_COOKIE[ 'gmw_' . $this->prefix . '_orderby' ] != 'undefined' ) {

			$this->form_data['orderby'] = urldecode( $_COOKIE[ 'gmw_' . $this->prefix . '_orderby' ] );
		}

		// get the default latitude
		if ( ! $doing_ajax && isset( $_REQUEST['lat'] ) ) {

			$this->form_data['lat'] = sanitize_text_field( $_REQUEST['lat'] );

		} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] ) && 'undefined' != $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] ) {

			$this->form_data['lat'] = urldecode( $_COOKIE[ 'gmw_' . $this->prefix . '_lat' ] );
		}

		// get the default longitude
		if ( ! $doing_ajax && isset( $_REQUEST['lng'] ) ) {

			$this->form_data['lng'] = sanitize_text_field( $_REQUEST['lng'] );

		} elseif ( isset( $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] ) && 'undefined' != $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] ) {

			$this->form_data['lng'] = urldecode( $_COOKIE[ 'gmw_' . $this->prefix . '_lng' ] );
		}

		// radius values
		$this->radius_values = str_replace( ' ', '', explode( ',', $this->options['radius'] ) );

		// if single, default value get it from the options
		if ( 1 == count( $this->radius_values ) ) {

			$this->form_data['radius'] = end( $this->radius_values );

			// check in URL if exists
		} elseif ( ! $doing_ajax && ! empty( $_GET['field_radius'] ) ) {

			$this->form_data['radius'] = $_GET['field_radius'];

			// otherwise, maybe in cookies
		} elseif ( ! empty( $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] ) && 'undefined' != $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] ) {

			$this->form_data['radius'] = urldecode( $_COOKIE[ 'gmw_' . $this->prefix . '_radius' ] );
		}

		$this->form_data = apply_filters( 'gmw_' . $this->prefix . '_form_data', $this->form_data, $this );

		add_action( 'bp_before_members_loop', array( $this, 'add_bp_pre_user_filter' ), 99 );
		add_action( 'bp_after_members_loop', array( $this, 'remove_bp_pre_user_filter' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'kleo_bp_search_add_data', array( $this, 'members_directory_form' ) );
		add_action( 'bp_members_inside_avatar', array( $this, 'get_distance' ) );
		add_action( 'bp_directory_members_item', array( $this, 'add_elements_to_results' ) );
		// add_filter( 'bp_user_query_uid_clauses',   array( $this, 'order_results_by_distance' ), 50, 2 );
		// enable map
		if ( ! empty( $this->options['map'] ) ) {
			add_action( 'bp_members_directory_member_sub_types', array( $this, 'map_element' ) );
			add_action( 'bp_after_members_loop', array( $this, 'trigger_js_and_map' ) );
		}
	}

	/**
	 * Add query filters before memmbers loop.
	 *
	 * @since 3.0.2
	 */
	public function add_bp_pre_user_filter() {
		add_filter( 'bp_pre_user_query_construct', array( $this, 'query_vars' ), 50 );
		add_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 50, 2 );
	}

	/**
	 * Remove filters after the loop.
	 *
	 * @since 3.0.2
	 */
	public function remove_bp_pre_user_filter() {
		remove_filter( 'bp_pre_user_query_construct', array( $this, 'query_vars' ), 50 );
		remove_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 50, 2 );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return [type] [description]
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'gmw-sdate-geo' );
	}

	/**
	 * Labels
	 *
	 * @since 2.6.1
	 *
	 * @return unknown
	 */
	public function labels() {

		$output = apply_filters(
			'gmw_' . $this->prefix . '_labels', array(
				'address_placeholder'   => __( 'Enter Address...', 'geo-my-wp' ),
				'miles'                 => __( 'Miles', 'geo-my-wp' ),
				'kilometers'            => __( 'Kilometers', 'geo-my-wp' ),
				'km'                    => __( 'km', 'geo-my-wp' ),
				'mi'                    => __( 'mi', 'geo-my-wp' ),
				'orderby'               => __( 'Order by', 'geo-my-wp' ),
				'orderby_dropdown'      => array(
					'active'       => __( 'Active', 'geo-my-wp' ),
					'newest'       => __( 'Newest', 'geo-my-wp' ),
					'alphabetical' => __( 'Alphabetical', 'geo-my-wp' ),
					'distance'     => __( 'Distance', 'geo-my-wp' ),
				),
				'address'               => __( 'Address', 'geo-my-wp' ),
				'get_directions'        => __( 'Get directions', 'geo-my-wp' ),
				'address_error_message' => __( 'Sorry, we could not find the address you entered. Please try a different address.', 'geo-my-wp' ),
				'resize_map'            => __( 'resize map', 'geo-my-wp' ),
			)
		);

		return $output;
	}

	/**
	 * Order search results by distance.
	 *
	 * @param  [type] $clauses [description]
	 * @param  [type] $vars    [description]
	 * @return [type]          [description]
	 */
	public function order_results_by_distance( $clauses, $vars ) {

		// remove this filter
		// remove_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 50, 2 );
		if ( 'distance' == $vars->query_vars['type'] ) {

			// verify ID
			$this->objects_id = array_map( 'absint', $this->objects_id );

			$objects_id = implode( ',', $this->objects_id );

			$clauses['orderby'] = " ORDER BY FIELD( id, {$objects_id} )";
		}

		return $clauses;
	}

	/**
	 * Modify the "type" argument of the members loop based on what the user chooses.
	 *
	 * @param unknown_type $query
	 *
	 * @return unknown
	 */
	public function query_vars( $query ) {

		// if address entered
		if ( ! empty( $this->form_data['address'] ) && ( empty( $this->form_data['lat'] ) || empty( $this->form_data['lng'] ) ) ) {

			include_once( GMW_PATH . '/includes/gmw-geocoder.php' );

			// geocode the address entered
			$this->returned_address = gmw_geocoder( $this->form_data['address'] );

			// If form submitted and address was not found stop search and display no results
			if ( isset( $this->returned_address['error'] ) ) {

				$this->form_data['lat'] = false;
				$this->form_data['lng'] = false;
				?>
				<script> 
					// display geocoding error message      
					jQuery( window ).ready( function( $ ) {                 
						jQuery( '#members-dir-list #message' ).html( '<?php echo $this->labels['address_error_message']; ?>' );
					});
				</script>
				<?php

				// abort query
				$query->query_vars['include'] = 0;

				return $query;

			} else {

				$this->form_data['lat'] = $this->returned_address['lat'];
				$this->form_data['lng'] = $this->returned_address['lng'];
			}
		}

		$args = array(
			'object_type'       => 'user',
			'lat'               => $this->form_data['lat'],
			'lng'               => $this->form_data['lng'],
			'radius'            => ! empty( $this->form_data['radius'] ) ? $this->form_data['radius'] : false,
			'units'             => $this->options['units'],
			'output_objects_id' => true,
		);

		// get locations from GEO my WP db
		$gmw_locations = GMW_Location::get_locations_data( $args );

		// update locations data
		$this->locations_data = $gmw_locations['locations_data'];
		$this->objects_id     = $gmw_locations['objects_id'];

		// if no members found stop the query
		if ( empty( $this->objects_id ) ) {

			$query->query_vars['include'] = 0;

			return $query;
		}

		/*
		 * compute the members to include based on the include arguments and
		 *
		 * returned from the locations query args.
		 *
		 * We do this to allow other plugins use the include argument first.
		 */
		if ( apply_filters( 'gmw_sdate_geo_disable_members_without_locations', false ) || ! empty( $this->form_data['address'] ) ) {

			if ( ! empty( $query->query_vars['include'] ) ) {

				if ( ! is_array( $query->query_vars['include'] ) ) {
					$query->query_vars['include'] = explode( ',', $query->query_vars['include'] );
				}
				$query->query_vars['include'] = array_intersect( $query->query_vars['include'], $this->objects_id );

			} else {
				$query->query_vars['include'] = $this->objects_id;
			}

			// abort if no members to include
			if ( empty( $query->query_vars['include'] ) || in_array( 0, $query->query_vars['include'] ) ) {

				$query->query_vars['include'] = array( 0 );

			}
		}

		if ( ! empty( $this->form_data['orderby'] ) ) {

			$query->query_vars['type'] = $this->form_data['orderby'];

		} elseif ( ! empty( $this->form_data['address'] ) ) {

			$query->query_vars['type'] = 'distance';
		}

		return $query;
	}

	/**
	 * Append radius default value or dropdown to the form
	 *
	 * @return [type] [description]
	 */
	public function search_form_radius() {

		$output = '';

		if ( count( $this->radius_values ) > 1 ) {

			$radius_label = ( $this->options['units'] == '6371' ) ? $this->labels['kilometers'] : $this->labels['miles'];

			$output .= '<div class="two columns gmw-sdate-geo-radius-wrapper">';
			$output .= '<select id="gmw-sdate-geo-radius-dropdown" class="expand gmw-sdate-geo-dropdown" name="field_radius">';
			$output .= '<option value="" selected="selected">' . esc_attr( $radius_label ) . '</option>';

			foreach ( $this->radius_values as $value ) {
				$selected = ( $value == $this->form_data['radius'] ) ? 'selected="selected"' : '';
				$output  .= '<option value="' . esc_attr( $value ) . '" ' . $selected . '>' . esc_attr( $value ) . '</option>';
			}

			$output .= '</select>';
			$output .= '</div>';

			// display hidden default value
		} else {
			$radius  = end( $this->radius_values );
			$output .= '<input type="hidden" id="gmw-sdate-geo-radius-dropdown" name="field_radius" value="' . esc_attr( $this->radius_values ) . '" />';
		}

		return apply_filters( 'gmw_' . $this->prefix . '_form_radius', $output, $this );
	}

	/**
	 * Generate orderby dropdown or default value
	 *
	 * @return [type] [description]
	 */
	public function search_form_orderby() {

		// orderby dropdown
		$orderby = array(
			'distance'     => ! empty( $this->form_data['address'] ) ? $this->labels['orderby_dropdown']['distance'] : false,
			'active'       => $this->labels['orderby_dropdown']['active'],
			'newest'       => $this->labels['orderby_dropdown']['newest'],
			'alphabetical' => $this->labels['orderby_dropdown']['alphabetical'],
		);

		// modify the orderby dropdown using a filter if needed
		$orderby = apply_filters( 'gmw_' . $this->prefix . '_orderby_options', $orderby, $this );

		$output = '';

		if ( ! empty( $orderby ) ) {

			$output .= '<div class="two columns gmw-sdate-geo-orderby-wrapper">';
			$output .= '<select id="gmw-sdate-geo-orderby-dropdown" class="expand gmw-sdate-geo-dropdown" name="orderby">';
			$output .= '<option value="">' . esc_attr( $this->labels['orderby'] ) . '</option>';

			foreach ( $orderby as $key => $value ) {

				if ( ! isset( $value ) ) {
					continue;
				}

				$selected = ( $key == $this->form_data['orderby'] ) ? 'selected="selected"' : '';
				$output  .= '<option value="' . sanitize_title( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>';
			}

			$output .= '</select>';
			$output .= '</div>';
		}

		$output = apply_filters( 'gmw_' . $this->prefix . '_form_orderby', $output, $orderby, $this );

		return $output;
	}

	/**
	 * Modify the members search form - append GMW features to it
	 *
	 * @param unknown_type $search_form_html
	 */
	public function members_directory_form() {

		$search_form_html = array();

		$args = array(
			'id'                   => 'sdate-geo',
			'placeholder'          => $this->labels['address_placeholder'],
			'locator_button'       => ! empty( $this->options['locator_button'] ) ? 1 : 0,
			'icon'                 => 'gmw-icon-target-light',
			'address_autocomplete' => ! empty( $this->options['address_autocomplete'] ) ? 1 : 0,
			'name_attr'            => 'address',
			'value'                => $this->form_data['address'],
		);

		$search_form_html['address_field'] = '<label class="two columns gmw-sdate-geo-address-label">' . GMW_Search_Form_Helper::address_field( $args ) . '</label>';
		// append radius to the search form
		// $search_form_html['radius_field'] = self::search_form_radius();
		// append units to the search form
		// $search_form_html['units_field'] = self::form_units();
		$search_form_html['coords'] = '<input type="hidden" name="lat" id="gmw-lat-sdate-geo" value="' . esc_attr( $this->form_data['lat'] ) . '" class="gmw-lat" /><input type="hidden" name="lng" id="gmw-lng-sdate-geo" value="' . esc_attr( $this->form_data['lng'] ) . '" class="gmw-lng" />';

		// append address field to the form
		// $search_form_html['address_label'] = '<label class="two columns">';
		// $search_form_html['address_input'] = '<input type="text" name="field_address" id="gmw-sd-address-field" value="'.sanitize_text_field( stripslashes( $this->form_data['address'] ) ).'" '.$autocomplete.' placeholder="'.esc_attr( $this->labels['address_placeholder'] ).'" />';
		// $search_form_html['/address_label'] = '</label>';
		// append radius to the search form
		$search_form_html['radius_field'] = self::search_form_radius();

		// append orderby to the search form
		if ( ! empty( $this->options['orderby'] ) ) {
			$search_form_html['orderby_field'] = self::search_form_orderby();
		}

		$search_form_html = apply_filters( 'gmw_' . $this->prefix . '_search_form_html', $search_form_html, $this );

		echo implode( ' ', $search_form_html );
	}

	/**
	 * Generate the map element
	 *
	 * @return [type] [description]
	 */
	public function map_element() {

		// map args
		$args = array(
			'map_id'       => $this->prefix,
			'map_type'     => 'sweetdate_geolocation',
			'prefix'       => $this->prefix,
			'map_width'    => $this->options['map_width'],
			'map_height'   => $this->options['map_height'],
			'form_data'    => $this->form_data,
			'init_visible' => false,
		);

		// display the map element
		echo GMW_Maps_API::get_map_element( $args );
	}

	/**
	 * Generate the map
	 *
	 * @return [type] [description]
	 */
	public function trigger_js_and_map() {

		// create the map object
		$map_args = array(
			'map_id'               => $this->prefix,
			'map_type'             => 'sweetdate_geolocation',
			'prefix'               => $this->prefix,
			'info_window_type'     => 'standard',
			'info_window_ajax'     => false,
			'info_window_template' => 'default',
			'group_markers'        => 'markers_clusterer',
			'render_on_page_load'  => false,
		);

		$map_options = array(
			'zoom'      => 'auto',
			'mapTypeId' => ! empty( $this->options['map_type'] ) ? $this->options['map_type'] : 'ROADMAP',
		);

		$user_position = array(
			'lat'        => $this->form_data['lat'],
			'lng'        => $this->form_data['lng'],
			'address'    => $this->form_data['address'],
			'map_icon'   => GMW()->default_icons['user_location_icon_url'],
			'icon_size'  => GMW()->default_icons['user_location_icon_size'],
			'iw_content' => __( 'You are here', 'geo-my-wp' ),
			'iw_open'    => false,
		);

		// triggers map on page load
		$map_args = gmw_get_map_object( $map_args, $map_options, $this->map_locations, $user_position );
		$map_args = wp_json_encode( $map_args );
		?>
		<script>       
		jQuery( window ).ready( function() {

			var mapArgs = <?php echo $map_args; ?>;
			// create map if not exists
			if ( typeof GMW_Maps['sdate_geo'] == 'undefined' ) {
				// generate map when ajax is triggered
				GMW_Maps['sdate_geo'] = new GMW_Map( mapArgs.settings, mapArgs.map_options, {} );
				// initiate it
				GMW_Maps['sdate_geo'].render( mapArgs.locations, mapArgs.user_location );
			// update existing map
			} else {
				GMW_Maps['sdate_geo'].update( mapArgs.locations, mapArgs.user_location );
			}
		});
		</script>
		<?php
	}

	/**
	 * Generate distance to each item in the results
	 *
	 * @return [type] [description]
	 */
	public function get_distance() {

		if ( ! isset( $this->options['distance'] ) || ! empty( $this->returned_address['error'] ) ) {
			return;
		}

		global $members_template;

		$member = $members_template->member;

		// look for distance value
		if ( empty( $this->locations_data[ $member->ID ]->distance ) ) {
			return;
		}

		// display the distance in results
		echo apply_filters( 'gmw_' . $this->prefix . '_member_distance', '<span class="gmw-sdate-geo-distance">' . esc_attr( $this->locations_data[ $member->ID ]->distance ) . ' ' . esc_attr( $this->units_label ) . '</span>', $member, $this->locations_data[ $member->ID ], $this );
	}

	/**
	 * Append address to each member in teh results
	 */
	public function get_address() {

		if ( empty( $this->options['address'] ) ) {
			return;
		}

		global $members_template;

		$member          = $members_template->member;
		$member_location = $this->locations_data[ $member->ID ];

		// make sure member has an address
		if ( ! empty( $member_location->formatted_address ) ) {

			$address_field = $member_location->formatted_address;

		} elseif ( ! empty( $member_location->address ) ) {

			$address_field = $member_location->address;

		} else {
			return;
		}

		$output  = '<div class="gmw-sdate-geo-address-wrapper">';
		$output .= '<i class="gmw-icon-location"></i>';
		$output .= '<span class="gmw-sdate-geo-address-value">';
		$output .= esc_attr( $address_field );
		$output .= '</span>';
		$output .= '</div>';

		return apply_filters( 'gmw_' . $this->prefix . '_member_address', $output, $member, $member_location, $this );
	}

	/**
	 * Generate member location for map
	 *
	 * @param  object $member          member data
	 * @param  object $member_location member location data
	 *
	 * @return [type]                  [description]
	 */
	public function map_location( $member, $info_window ) {

		// add lat/lng locations array to pass to map
		return apply_filters(
			'gmw_' . $this->prefix . '_member_data', array(
				'ID'                  => $member->ID,
				'lat'                 => $member->lat,
				'lng'                 => $member->lng,
				'map_icon'            => 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $member->location_count . '|FF776B|000000',
				'icon_size'           => GMW()->default_icons['location_icon_size'],
				'info_window_content' => $info_window,
			), $member, $this
		);
	}

	/**
	 * GEM MD Funciton - add GEO my WP elements to members results
	 */
	public function add_elements_to_results() {

		global $members_template;

		$member = $members_template->member;

		// abort if user does not have a location
		if ( empty( $this->locations_data[ $member->ID ] ) ) {
			return;
		}

		$location = $this->locations_data[ $member->ID ];

		// append member object with location data
		foreach ( $location as $key => $value ) {

			// add location data into object
			$members_template->member->$key = $value;
		}

		// member count
		$this->options['page']     = $members_template->pag_page;
		$this->options['per_page'] = $members_template->pag_num;

		// memebr count
		if ( 1 == $members_template->pag_page ) {

			$members_template->member->location_count = $members_template->current_member + 1;

		} else {

			$members_template->pag_page = $members_template->pag_page - 1;

			$members_template->member->location_count = ( $members_template->pag_page * $members_template->pag_num ) + $members_template->current_member + 1;
		}

		// show address in results
		echo self::get_address();

		// show directions in results
		if ( isset( $this->options['directions_link'] ) && '' != $this->options['directions_link'] ) {
			echo gmw_get_directions_link( $member, $this->form_data, $this->labels['get_directions'] );
		}

		// if displaying map, collect some data to pass to the map script
		if ( ! empty( $this->options['map'] ) ) {

			$info_window_args = array(
				'prefix'          => $this->prefix,
				'url'             => bp_get_member_permalink(),
				'title'           => $member->display_name,
				'image_url'       => false,
				// 'image_url'       => bp_core_fetch_avatar( 'item_id='.$member->ID.'&type=thumb&html=FALSE' ),
				'iw_type'         => 'standard',
				'address'         => true,
				'directions_link' => false,
				'distance'        => true,
				'location_meta'   => '',
			);

			$info_window = gmw_get_info_window_content( $member, $info_window_args, $this->form_data );

			$this->map_locations[] = $this->map_location( $members_template->member, $info_window );
		}
	}
}
