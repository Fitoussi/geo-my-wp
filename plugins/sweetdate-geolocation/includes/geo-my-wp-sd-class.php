<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW_SD_Query class.
 */
class GMW_SD_Class_Query {

    /**
     * Constructor
     */
    public function __construct() {

        $settings = get_option( 'gmw_options' );

        //Sweet-date options
        $this->settings                  = ( isset( $settings['sweet_date'] ) ) ? $settings['sweet_date'] : false;
        $this->formData['address']       = false;
        $this->formData['your_lat']      = false;
        $this->formData['your_lng']      = false;
        $this->formData['address_found'] = false;       
        $this->formData['radius']        = false;
        $this->formData['orderby']       = '';
        $this->labels                    = $this->labels();
        $this->units_label               = ( $this->settings['units'] == '6371' ) ? $this->labels['km'] : $this->labels['mi'];
        
        //get the addres value
        if ( !empty( $_GET['field_address'] ) ) {
            $this->formData['address'] =  sanitize_text_field( $_GET['field_address'] );
        } else if ( !empty( $_COOKIE['gmw_sd_address'] ) && $_COOKIE['gmw_sd_address'] != 'undefined' ) {
            $this->formData['address'] = urldecode( $_COOKIE['gmw_sd_address'] );
        }
        
        //orderby value
        if ( !empty( $_GET['orderby'] ) ) {
            $this->formData['orderby'] =  sanitize_text_field( $_GET['orderby'] );
        } elseif ( !empty( $_COOKIE['gmw_sd_orderby'] ) && $_COOKIE['gmw_sd_orderby'] != 'undefined' ) {
            $this->formData['orderby'] = urldecode( $_COOKIE['gmw_sd_orderby'] );
        }
        
        //radius value
        $radius = str_replace( ' ', '', explode( ',', $this->settings['radius'] ) );
        //if single value get it from the settings
        if ( count( $radius ) == 1 ) {
            $this->formData['radius'] = end($radius);
        } elseif ( !empty( $_GET['field_radius'] ) ) {
            $this->formData['radius'] =  absint( $_GET['field_radius'] );
        } elseif ( !empty( $_COOKIE['gmw_sd_radius'] )  && $_COOKIE['gmw_sd_radius'] != 'undefined' ) {
            $this->formData['radius'] = urldecode( $_COOKIE['gmw_sd_radius'] );
        }
                
        //action hooks/ filters
        add_action( 'wp_enqueue_scripts',           array( $this, 'register_scripts'        ) );
        add_filter( 'kleo_bp_search_add_data',      array( $this, 'members_directory_form'  ) );
        add_filter( 'bp_pre_user_query_construct',  array( $this, 'query_vars'              ) );
        add_action( 'bp_pre_user_query',            array( $this, 'bp_pre_user_query'   ), 99 );    
        add_action( 'pre_user_query',               array( $this, 'pre_user_query'      ), 99 );
        add_action( 'bp_members_inside_avatar',     array( $this, 'get_distance'            ) );
        add_action( 'bp_directory_members_item',    array( $this, 'add_elements_to_results' ) );

        if ( !empty( $this->settings['address_autocomplete_use'] ) ) {
            add_filter( 'gmw_google_places_address_autocomplete_fields', array( $this, 'address_autocomplete' ) );
        }
        
        if ( !empty( $this->settings['map_use'] ) ) {
            add_action( 'bp_before_directory_members_list', array( $this, 'map_element' ) );
            add_action( 'member_loop_end', array( $this, 'trigger_js_and_map'       ) );
        }
    }
    
    /**
     * Register scripts
     * 
     */
    public function register_scripts() {
        
        wp_register_style( 'gmw-sd-style', GMW_SD_URL . '/assets/css/style.css' );
        wp_enqueue_style( 'gmw-sd-style' );
        
        wp_register_script( 'gmw-sd-js', GMW_SD_URL . '/assets/js/gmw-sd.js', array( 'jquery' ), GMW_VERSION, true ); 
        wp_enqueue_script( 'gmw-sd-js' );
        
        if ( isset( $this->settings['map_use'] ) ) {
            
            if ( !wp_script_is( 'gmw-map', 'enqueued') ) {
                //wp_enqueue_script( 'gmw-map' );
            }
            if ( !wp_script_is( 'gmw-marker-clusterer', 'enqueued') ) {
                wp_enqueue_script( 'gmw-marker-clusterer' );
            }
        }
    }
    
    /**
     * Create labels
     * 
     * @since 2.6.1
     * @return unknown
     */
    public function labels() {

        $output = apply_filters( 'gmw_sd_labels', array(
                'address_placeholder'   => __( 'Enter Address...', 'GMW' ),
                'miles'                 => __( 'Miles', 'GMW' ),
                'kilometers'            => __( 'Kilometers', 'GMW' ),
                'km'                    => __( 'km', 'GMW' ),
                'mi'                    => __( 'mi', 'GMW' ),
                'orderby'               => __( 'Order by', 'GMW' ),
                'orderby_dropdown'      => array(
                        'active'        =>__( 'Active', 'GMW' ),
                        'newest'        => __( 'Newest', 'GMW' ),
                        'alphabetical'  => __( 'Alphabetical', 'GMW' ),
                        'distance'      => __( 'Distance', 'GMW' ),
                ),
                'address'               => __( 'Address', 'GMW' ),
                'get_directions'        => __( 'Get directions', 'GMW' ),
                'address_error_message' => __( "Sorry, we could not find the address you entered. Please try a different address.", "GMW" ),
                'resize_map'            => __( 'resize map', 'GMW' )
            )
        );
        
        return $output;
    }
    
    /**
     * Modify the "type" argument of the members loop before anything happens.

     * @param unknown_type $query
     * @return unknown
     */
    public function query_vars( $query ) {
        
        if ( !empty( $this->formData['orderby'] ) ) {
            $query->query_vars['type'] = $this->formData['orderby'];
        }
        
        return $query;
    }
    
    /**
     * Append radius to the search form
     * 
     */
    public function radius() {
        
        $radius = str_replace( ' ', '', explode( ',', $this->settings['radius'] ) );
        $output = '';
        
        if ( count( $radius ) > 1 ) {
        
            $radiusText = ( $this->settings['units'] == '6371' ) ? $this->labels['kilometers'] : $this->labels['miles'];
        
            $output .= "<div class=\"two columns\">";
            $output .=  "<select id=\"gmw-sd-radius-dropdown\" class=\"expand gmw-sd-dropdown\" name=\"field_radius\">";
            $output .=      '<option value="" selected="selected">'.esc_attr( $radiusText ).'</option>';
        
            foreach ( $radius as $value ) {
                $selected = ( $value == esc_attr( sanitize_text_field( $this->formData['radius'] ) ) ) ? 'selected="selected"': '';
                $output .=  "<option value=\"{$value}\" {$selected}>{$value}</option>";
            }
        
            $output .=  "</select>";
            $output .= "</div>";
        
            //display hidden default value
        } else {
            $radius = end( $radius );
            $output .= '<input type="hidden" id="gmw-sd-radius-dropdown" name="field_radius" value="'. esc_attr( $radius ).'" />';
        }
        
        return apply_filters( 'gmw_sd_form_radius', $output, $this->settings );
    }
    
    /**
     * Append orderby dropdown to the search form
     *
     */
    public function orderby() {
                    
        $output = '';
        
        //orderby dropdown
        $orderby = array(
                'active'       => $this->labels['orderby_dropdown']['active'],
                'newest'       => $this->labels['orderby_dropdown']['newest'],
                'alphabetical' => $this->labels['orderby_dropdown']['alphabetical'],
        );
        
        //if address entered append the distance option to the orderby
        if ( !empty( $this->formData['address'] ) ) {
            $orderby = array_merge( array( 'distance' => $this->labels['orderby_dropdown']['distance'] ), $orderby );
        }
        
        //modify the orderby dropdown
        $orderby = apply_filters( 'gmw_sd_orderby_values', $orderby );
        
        if ( !empty( $orderby ) ) {
        
            $output .= "<div class=\"two columns\">";
            $output .=  "<select id=\"gmw-sd-orderby-dropdown\" class=\"expand gmw-sd-dropdown\" name=\"orderby\">";
            $output .=      '<option value="">'.esc_attr( $this->labels['orderby'] ).'</option>';
        
            foreach ( $orderby as $key => $value ) {
        
                $selected = ( $key == esc_attr( sanitize_text_field( $this->formData['orderby'] ) ) ) ? 'selected="selected"' : '';
                $output .=  "<option value=\"{$key}\" {$selected}>{$value}</option>";
            }
        
            $output .=  "</select>";
            $output .= "</div>";
        }
        
        return apply_filters( 'gmw_sd_form_orderby', $output, $this->settings, $orderby );
    }
    
    /**
     * Modify the members search form - append GMW features to it
     * 
     * @param unknown_type $search_form_html
     */
    public function members_directory_form( $search_form_html ) {

        $search_form_html .= "<label class=\"two columns\">";
        $search_form_html .= '<input type="text" name="field_address" id="gmw-sd-address-field" value="'.esc_attr( $this->formData['address'] ).'" placeholder="'.esc_attr( $this->labels['address_placeholder'] ).'" />';
        $search_form_html .= "</label>";

        //append radius to the search form
        $search_form_html .= self::radius();
        
        //append orderby to the search form
        if ( !empty( $this->settings['orderby_use'] ) ) {
            $search_form_html .= self::orderby();
        }
      
        echo $search_form_html;
    }
    
    /**
     * Address Autocomplete
     *
     * @since 2.5
     *
     */
    public function address_autocomplete( $fields ) {
    
        $fields[] = 'gmw-sd-address-field';
        return $fields;
    }
    
    /**
     * Create the map element
     */
    public function map_element() {
        
        //map arguments
        $gmw = array(
                'ID'            => 'sd',
                'prefix'        => 'sd',
                'addon'         => 'sweetdate_geolocation',
                'results_map'   => array(
                        'map_width'  => $this->settings['map_width'],
                        'map_height' => $this->settings['map_height'],
                ),
                'formData'      => $this->formData
        );
        
        //get the map element
        $output = gmw_get_results_map( $gmw );
            
        echo $output;
    }

    /**
     * Modify the BP query caluses
     * 
     * @param unknown_type $gmwBpQuery
     * @return unknown
     */
    public function bp_pre_user_query( $gmwBpQuery ) {
        
        global $wpdb;
                
        //break the select clause into 2: SELECT and FROM so we can modify it based on out needs
        $select_clause = explode( 'FROM', $gmwBpQuery->uid_clauses['select']);
         
        //find the user_id column based on the query type
        $uid_col = ( in_array( $gmwBpQuery->query_vars['type'], array( "alphabetical", 'distance' ) ) ) ? 'u.ID' : 'u.user_id';
         
        //default values
        $fields  = '';
        $from    = '';
        $having  = '';
        $where   = '';
        $orderby = '';
        
        //if address entered
        if ( !empty( $this->formData['address'] ) ) {

            //geocode the address entered
            $this->returned_address = GEO_my_WP::geocoder( sanitize_text_field( $this->formData['address'] ) );
        
            //If form submitted and address was not found stop search and display no results
            if ( isset( $this->returned_address['error'] ) ) {
            
                $this->formData['address_found'] = false;
                $this->formData['your_lat']      = false;
                $this->formData['your_lng']      = false;
                $this->formData['address']       = 'error';
   
                //modify the query to no results
                $gmwBpQuery->uid_clauses['where']   = ' WHERE 0 = 1 ';
                $gmwBpQuery->uid_clauses['orderby'] = ' ';
                $gmwBpQuery->uid_clauses['limit']   = ' ';
                $gmwBpQuery->uid_clauses['order']   = ' ';
                
                ?>
                <script> 
                    //pass some values to javascript        
                    jQuery(window).ready(function($) {                  
                        jQuery('#members-dir-list #message').html('<?php echo $this->labels['address_error_message']; ?>');         
                    });
                </script>
                <?php       
        
            } else {

                $this->formData['address_found'] = true;
                $this->formData['your_lat']      = $this->returned_address['lat'];
                $this->formData['your_lng']      = $this->returned_address['lng'];
   
                //do radius calculation
                $fields = $wpdb->prepare(" , ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ",
                    array( $this->settings['units'], $this->formData['your_lat'], $this->formData['your_lng'], $this->formData['your_lat'] ) );
             
                //from clause joining locations table
                $from = "INNER JOIN wppl_friends_locator gmwlocations ON {$uid_col} = gmwlocations.member_id";
             
                //HAVING clause to display members within the distance entered
                
                if ( !empty( $this->formData['radius'] ) ) {
                    $having = $wpdb->prepare( 'HAVING distance <= %d OR distance IS NULL ', $this->formData['radius'] );      
                }   
                
                //if order by distance
                if ( $gmwBpQuery->query_vars['type'] == 'distance' ) {
                    $gmwBpQuery->uid_clauses['orderby'] = 'ORDER BY distance';
                }           
            }
             
            //if no address entered
        } else {
                         
            //join the locations table to the query
            $from = "LEFT JOIN wppl_friends_locator gmwlocations ON {$uid_col} = gmwlocations.member_id";
            
            if ( $this->formData['orderby'] == 'distance' ) {
                $this->formData['orderby'] = 'active';
            }
        }
             
        //apply our filters to BP_user_qeury clauses
        $gmwBpQuery->query_vars['count_total'] = 'sql_calc_found_rows';
        $gmwBpQuery->uid_clauses['select']     = "{$select_clause[0]} {$fields} FROM {$select_clause[1]} {$from} ";
        $gmwBpQuery->uid_clauses['where']     .= $where;
        $gmwBpQuery->uid_clauses['where']     .= $having;
         
        //modify the clauses
        $gmwBpQuery = apply_filters( 'gmw_sd_after_bp_pre_user_query', $gmwBpQuery, $this->formData, $this->settings );
        
        return $gmwBpQuery;
    }

    /**
     * Modify the WP users query clauses
     * 
     * @param unknown_type $gmwBpQuery
     * @return unknown
     */
    public function pre_user_query( $gmwWpQuery ) {

        global $wpdb;
         
        $fields = '';
        $from   = '';
         
        if ( ! empty( $this->formData['address'] ) ) {
            
            $fields = $wpdb->prepare(", gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address, gmwlocations.map_icon, ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance",
                    $this->settings['units'], $this->formData['your_lat'], $this->formData['your_lng'], $this->formData['your_lat'] );
             
            $from = " INNER JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id";
             
        } else {
            $fields = ", gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address, gmwlocations.map_icon";
        
            $from = " LEFT JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id";
        }
        
        $gmwWpQuery->query_fields  .= $fields;
        $gmwWpQuery->query_from    .= $from;

        //modify the clauses
        $gmwWpQuery = apply_filters( 'gmw_sd_after_pre_user_query', $gmwWpQuery, $this->formData, $this->settings );
        
        return $gmwWpQuery;
    }

    /**
     * Trigger javascript to display maps and markers
     */
    public function trigger_js_and_map() {
        
        //create the map object
        $mapArgs = array(
                'mapId'          => 'sd',
                'mapType'        => 'sweetdate_geolocation',
                'prefix'         => 'sd',
                'triggerMap'     => false,
                'locations'      => $this->formData['members'],
                'zoomLevel'      => 'auto',
                'mapTypeId'      => ( !empty( $this->settings['map_type'] ) ) ? $this->settings['map_type'] : 'ROADMAP',            
                'userPosition'   => array(
                        'lat'       => $this->formData['your_lat'],
                        'lng'       => $this->formData['your_lng'],
                        'address'   => $this->formData['address'],
                        'mapIcon'   => 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                        'iwContent' => 'You are here',
                        'iwOpen'    => true
                ),
                'markersDisplay' => 'markers_clusterer'
        );
        
        $map_element = gmw_new_map_element( $mapArgs, true );

        ?>
        <script> 
            //pass some values to javascript        
            jQuery(window).ready(function($) {

                //pass the map object to JS
                values = <?php print json_encode( $map_element ); ?>;
                
                jQuery('#gmw-map-wrapper-sd').slideToggle(function() {
                    //display the map
                    gmwMapInit( values );
                });   
            });
        </script>
        <?php       
    }

    /**
     * Info window content
     * 
     * @param unknown_type $member
     * 
     */
    public function info_window_content( $member )  {
            
        //user link
        $user_link = bp_core_get_user_domain( $member->ID );
        
        //get avatar
        $avatar =  bp_core_fetch_avatar(
                array(
                        'item_id'   => $member->ID,
                        'type'      => 'full',
                        'html'      => true,                    
                ) );
        
        //avatar link       
        $avatar = "<a href=\"{$user_link}\" title=\"{$member->user_nicename}\">{$avatar}</a>";
                        
        $output                  = array();
        $output['wrap_start']    = "<div class=\"gmw-sd-info-window-wrapper\">";
        $output['avatar']        = "<div class=\"avatar\">{$avatar}</div>";
        $output['content_start'] = "<div class=\"content-wrapper\">";
        $output['name']          = "<div class=\"user-name\"><a href=\"{$user_link}\" title=\"{$member->user_nicename}\">{$member->user_nicename}</a></div>";
        $output['address']       = "<div class=\"address\"><i class=\"fa fa-map-marker\"></i>{$member->formatted_address}</div>";
        if ( !empty( $member->distance ) ) {
            $output['distance'] = "<div class=\"distance\"></i>{$member->distance} {$this->units_label}</div>";
        }
        $output['content_end']   = "</div>";
        $output['wrap_end'] = "</div>";
        
        //modify the content
        $output = apply_filters( 'gmw_sd_info_window_content', $output, $member, $this->formData, $this->settings );
            
        return implode( ' ', $output);
    }
    
    /**
     * Get directions" link
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function get_directions() {
        
        global $members_template;

        if ( !isset( $this->settings['directions'] ) || !$members_template->member->lat || !$members_template->member->long )
            return;
        
        $gmw_settings = get_option( 'gmw_options' );
        
        $region    = ( !empty( $settings['general_settings']['country_code'] ) )  ? '&region=' .$gmw_settings['general_settings']['country_code'] : '';
        $language  = ( !empty( $settings['general_settings']['language_code'] ) ) ? '&hl=' .$gmw_settings['general_settings']['language_code'] : ''; 
        $units     = ( $this->settings['units'] == '6371' ) ? 'ptk' : 'ptm';
        $ulLatLng  = ( $this->formData['your_lat'] && $this->formData['your_lng'] ) ? "{$this->formData['your_lat']},{$this->formData['your_lng']}" : "";
         
        $output = "<div class=\"gmw-sd-directions-wrapper\"><i class=\"fa fa-location-arrow\"></i><a title=\"{$this->labels['get_directions']}\" href=\"https://maps.google.com/maps?f=d{$language}{$region}&doflg={$units}&saddr={$ulLatLng}&daddr={$members_template->member->lat},{$members_template->member->long}&ie=UTF8&z=12\" target=\"_blank\">{$this->labels['get_directions']}</a></div>";

        return apply_filters( 'gmw_sd_get_directions', $output, $members_template->member, $this->formData, $this->settings );
    }

    /**
     * Append distance to each member in the results
     * 
     */
    public function get_distance() {
        
        if ( !isset( $this->settings['distance'] ) || empty( $this->formData['address_found'] ) )
            return;

        global $members_template;

        if ( empty( $members_template->member->distance ) )
            return;

        echo '<span class="gmw-sd-distance">'.esc_attr( $members_template->member->distance ).' '.esc_attr( $this->units_label ) .'</span>';
    }

    /**
     * Append address to each member in teh results
     * 
     */
    public function get_address() {
        
        if ( empty( $this->settings['address'] ) )
            return;
        
        global $members_template;
        
        //make sure member has an address
        if ( !empty( $members_template->member->formatted_address ) ) {
            
            $address_field = $members_template->member->formatted_address;
            
        } elseif ( !empty( $members_template->member->address ) ) {
            
            $address_field = $members_template->member->address;
        } else {
            return;
        }
            
        $address_field = apply_filters( "gmw_sd_member_address_field", $address_field, $members_template->member );
        
        $output = '<div class="gmw-sd-address-wrapper"><i class="fa fa-map-marker"></i><span class="gmw-sd-address-value">'. esc_attr( $address_field ) .'</span></div>';
             
        return apply_filters( 'gmw_sd_member_address', $output, $members_template->member );
    }

    /**
     * GEM MD Funciton - add GEO my WP elements to members results
     */
    public function add_elements_to_results() {
        
        global $members_template;
        
        $distance = false;
        
        //if member does not have location, abort!!
        if ( empty( $members_template->member->lat ) || empty( $members_template->member->long ) )
            return;

        //address
        echo self::get_address();
     
        echo self::get_directions();
        
        $this->settings['page']           = $members_template->pag_page;
        $this->settings['per_page']       = $members_template->pag_num;

        if ( $members_template->pag_page == 1 ) {
            $member_count = $members_template->current_member + 1;
        } else {
            $members_template->pag_page = $members_template->pag_page - 1;
            $member_count = ( $members_template->pag_page * $members_template->pag_num  ) + $members_template->current_member + 1;
        }
        
        //add lat/long locations array to pass to map
        $this->formData['members'][] = apply_filters( 'gmw_sd_member_data', array(
                'ID'                  => $members_template->member->ID,
                'lat'                 => $members_template->member->lat,
                'long'                => $members_template->member->long,
                'info_window_content' => $this->info_window_content( $members_template->member ),
                'mapIcon'             => 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='. $member_count .'|FF776B|000000'
        ), $members_template->member, $this->formData, $this->settings );
    }
}