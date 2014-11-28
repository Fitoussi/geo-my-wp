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

        // Define constants
        define( 'GMW_SD_PATH', GMW_PATH. '/third-party/sweetdate' );
        define( 'GMW_SD_URL', GMW_URL. '/third-party/sweetdate' );

        $this->settings = get_option( 'gmw_options' );

        //return if disabled
        if ( !$this->settings['sweet_date']['status'] )
            return;

        $this->gmwSD                = ( isset( $this->settings['sweet_date'] ) ) ? $this->settings['sweet_date'] : false;
        $this->gmwSD['your_lat']    = false;
        $this->gmwSD['your_lng']    = false;
        $this->gmwSD['org_address'] = false;
        $this->formData['query']    = false;
        $this->formData['address']  = ( !empty( $_GET['field_address'] ) ) ? $_GET['field_address'] : false;
        $this->formData['orderby']  = ( !empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : '';
        $radius                     = str_replace( ' ', '', explode( ',', $this->gmwSD['radius'] ) );
        $this->formData['radius']   = ( !empty( $_GET['field_radius'] ) ) ? $_GET['field_radius'] : false;

        $this->clauses = array(
            'bp_user_query' => array( 'where' => false ),
            'wp_user_query' => array( 'query_fields' => false ),
        );

        //action hooks/ filters
        add_action( 'wp_enqueue_scripts', 				  array( $this, 'frontend_register_styles' ) );
        add_filter( 'kleo_bp_search_add_data', 			  array( $this, 'members_directory_form'   ) );
        add_action( 'bp_members_directory_order_options', array( $this, 'orderby_distance' 		), 5 );
        add_action( 'bp_before_members_loop', 			  array( $this, 'members_query' 		   ) );
        add_action( 'bp_pre_user_query', 				  array( $this, 'gmwBpDirectoryQuery'  ), 99 );
        add_action( 'pre_user_query', 					  array( $this, 'gmwWpDirectoryQuery'  ), 99 );
        add_action( 'bp_after_directory_members_list', 	  array( $this, 'trigger_js_and_maps'      ) );
        add_action( 'bp_members_inside_avatar', 		  array( $this, 'get_distance' 			   ) );
        add_action( 'bp_directory_members_item', 		  array( $this, 'add_elements_to_results'  ) );

        if ( $this->gmwSD['address_autocomplete_use'] ) {
        	add_filter( 'gmw_google_places_address_autocomplete_fields', array( $this, 'address_autocomplete' ) );
        }
        
        if ( isset( $this->gmwSD['map_use'] ) ) {
            add_action( 'bp_before_directory_members_list', array( $this, 'main_map' ) );
        }
    }

    public function frontend_register_styles() {

        $screenLoader = ( isset( $this->gmwSD['screen_loader'] ) ) ? $this->gmwSD['screen_loader'] : false;

        wp_enqueue_style( 'gmw-sd-style', GMW_SD_URL . '/assets/css/style.css' );

        if ( isset( $this->gmwSD['map_use'] ) ) {
            wp_register_script( 'gmw-sd-map', GMW_SD_URL . '/assets/js/map.js', array( 'jquery' ), GMW_VERSION, true );
        }

    }

    public function members_directory_form( $search_form_html ) {

        $radius = str_replace( ' ', '', explode( ',', $this->gmwSD['radius'] ) );

        $search_form_html .= '<label class="two columns">';
        $search_form_html .= '<input type="text" name="field_address" id="gmw-sd-address-field" value="' . $this->formData['address'] . '" placeholder="' . __( 'Enter Address...', 'GMW' ) . '" />';
        $search_form_html .= '</label>';

        //Display radius dropdown
        if ( count( $radius ) > 1 ) {

            $radiusText = ( $this->gmwSD['units'] == '6371' ) ? __( ' -- Kilometers -- ', 'GMW' ) : __( ' -- Miles -- ', 'GMW' );

            $search_form_html .= '<div class="two columns">';
            $search_form_html .= 	'<select class="expand" name="field_radius">';
            $search_form_html .= 		'<option value="9999999999999" selected="selected">' . $radiusText . '</option>';
            
            foreach ( $radius as $value ) {
            	$selected = ( $value == $this->formData['radius'] ) ? 'selected="selected"': '';
            	$search_form_html .= 	'<option value="' . $value . '" '.$selected.'>' . $value . '</option>';
            }
            
            $search_form_html .= 	'</select>';
            $search_form_html .= '</div>';

        //display hidden default value
        } else {
            $search_form_html .= '<input type="hidden" id="gmw-sd-radius-dropdown" name="field_radius" value="' . end( $radius ) . '" />';
        }

        //orderby dropdown
        $orderby = array( 
        		'active' 	   => __( 'Active', 'GMW' ), 
        		'newest' 	   => __( 'Newest', 'GMW' ),
        		'alphabetical' => __( 'Alphabetical', 'GMW' )
        );

        if ( $this->formData['address'] == true ) {
            $orderby = array_merge( array( 'distance' => __( 'Distance', 'GMW' ) ), $orderby );
        }

        $orderby = apply_filters( 'gmw_sd_orderby_values', $orderby );

        if ( !empty( $orderby ) && !empty( $this->gmwSD['orderby_use'] ) ) {
        
	        $search_form_html .= '<div class="two columns">';
	        $search_form_html .= 	'<select class="expand" name="orderby">';
	        $search_form_html .= 		'<option value="">' . __( 'Order By', 'GMW' ) . '</option>';
	
	        foreach ( $orderby as $key => $value ) {
			
	        	$selected = ( $value == $this->formData['orderby'] ) ? 'selected="selected"' : '';
	            $search_form_html .= 	'<option value="' . $key . '" '.$selected.'>' . $value . '</option>';
	
	        }
	
	        $search_form_html .= 	'</select>';
	        $search_form_html .= '</div>';
        }
        
        echo $search_form_html;

    }

    /**
     *  Members Query
     */
    function members_query() {
        global $wpdb, $bp;

        //set join type based on the query. if no address entered will join all members even if they have no location
        $tJoin  = "RIGHT";
        $tJoin2 = "LEFT";

        //when doing query by address entered
        if ( $this->formData['address'] ) {

            // $this->formData['orderby']  = 'distance';
            //do INNER JOIN. we will show only members with location
            $tJoin  = $tJoin2 = "INNER";

            //geocode the address entered
            $this->returned_address = GEO_my_WP::geocoder( $this->formData['address'] );

            //If form submitted and address was not found stop search and display no results
            if ( !isset( $this->returned_address ) || empty( $this->returned_address ) ) {

                $this->formData['query']    = false;
                $this->gmwSD['your_lat']    = false;
                $this->gmwSD['your_lng']    = false;
                $this->gmwSD['org_address'] = 'bad';

                //modify the query to no results
                $this->clauses['bp_user_query']['where']   = ' AND 0 = 1 ';
                $this->clauses['bp_user_query']['orderby'] = ' ORDER BY u.display_name';

                $message = apply_filters( 'gmw_sd_no_addrss_found_message', __( 'Sorry, the address was not found. Please try a different address.', 'GMW' ) );
                ?>
                <script>
                    jQuery('#message').html('<p>' + '<?php echo $message; ?>' + '</p>');
                </script>
                <?php
            } else {

                $this->formData['query']    = 'address';
                $this->gmwSD['your_lat']    = $this->returned_address['lat'];
                $this->gmwSD['your_lng']    = $this->returned_address['lng'];
                $this->gmwSD['org_address'] = (!empty( $_POST ) ) ? $_POST['search_terms'] : $this->formData['address'];
            }
        } elseif ( $this->formData['orderby'] == 'distance' ) {
            $this->formData['orderby'] = 'active';
        }
		
     
        $users_table   = ( $bp->version < '2.0' ) ? $wpdb->usermeta : $bp->members->table_name_last_activity;
        
        if ( $this->formData['orderby'] == 'alphabetical' ) {

           // if ( !bp_disable_profile_sync() || !bp_is_active( 'xprofile' ) ) {

                $this->clauses['bp_user_query']['select']  = "SELECT DISTINCT u.ID as id , gmwlocations.member_id";
                $this->clauses['bp_user_query']['from']    = " FROM wppl_friends_locator gmwlocations {$tJoin} JOIN {$wpdb->users} u ON gmwlocations.member_id = u.ID";
                $this->clauses['bp_user_query']['orderby'] = "ORDER BY u.display_name";
                $this->clauses['bp_user_query']['order']   = "ASC";

                // When profile sync is disabled, alphabetical sorts must happen against
                // the xprofile table
           /* } else {

                $fullname_field_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$bp->profile->table_name_fields} WHERE name = %s", bp_xprofile_fullname_field_name() ) );

                $this->clauses['bp_user_query']['select']  = "SELECT DISTINCT u.user_id as id , gmwlocations.member_id";
                $this->clauses['bp_user_query']['from']    = " FROM wppl_friends_locator gmwlocations {$tJoin} JOIN {$bp->profile->table_name_data} u ON gmwlocations.member_id = id";
                //$this->clauses['bp_user_query']['where']   = "WHERE u.field_id = {$fullname_field_id} ";
                $this->clauses['bp_user_query']['orderby'] = "ORDER BY u.value";
                $this->clauses['bp_user_query']['order']   = "ASC";
            } */
        } elseif ( $this->formData['orderby'] == 'newest' || $this->formData['orderby'] == 'active' || $this->formData['orderby'] == '' ) {
        	
        	$orderby = ( $bp->version < '2.0' ) ? 'u.meta_value' : 'u.date_recorded';
        	
            $this->clauses['bp_user_query']['select'] = "SELECT DISTINCT u.user_id , gmwlocations.member_id";
            $this->clauses['bp_user_query']['from']   = " FROM wppl_friends_locator gmwlocations {$tJoin} JOIN {$users_table} u ON gmwlocations.member_id = u.user_id";
            //$this->clauses['bp_user_query']['where']  = "u.component = 'members' AND u.type = 'last_activity''";

            if ( $this->formData['orderby'] == 'newest' ) {
                $this->clauses['bp_user_query']['orderby'] = "ORDER BY u.user_id";
            } else {
                $this->clauses['bp_user_query']['orderby'] = "ORDER BY {$orderby}";
            }

            $this->clauses['bp_user_query']['order'] = "DESC";

            //when order by distance
        } elseif ( $this->formData['orderby'] == 'distance' ) {

            $this->clauses['bp_user_query']['select'] = "SELECT gmwlocations.member_id, u.user_id as id";
            $this->clauses['bp_user_query']['from']   = " FROM wppl_friends_locator gmwlocations INNER JOIN {$users_table} u ON gmwlocations.member_id = u.user_id";
            //$this->clauses['bp_user_query']['where']  = "WHERE u.meta_key = 'last_activity'";

            if ( $this->gmwSD['your_lat'] != false ) {

                $this->clauses['bp_user_query']['orderby'] = "ORDER BY distance";
            } else {

                $this->formData['query'] = false;
                $this->gmwSD['your_lat'] = false;
                $this->gmwSD['your_lng'] = false;

                $this->clauses['bp_user_query']['where']   = 'AND 0 = 1';
                $this->clauses['bp_user_query']['orderby'] = 'ORDER BY u.user_id';
                ?>
                <script>
                    jQuery(window).ready(function() {
                        jQuery('#message').html("<p>We couldn't find the address you enter. Please try a different address.</p>");
                    });
                </script>
                <?php
            }

            $this->clauses['bp_user_query']['order'] = "ASC";
        }

        /*
         * if address entered
         * prepare the filter of the select clause of the SQL function. the function join the members table with
         * wppl_friends_locator table, will calculate the distance and will get only the members that
         * within the radius was chosen
         */
        if ( $this->gmwSD['your_lat'] !== false ) {

        	$this->clauses['bp_user_query']['select'] 	   .= $wpdb->prepare( " , ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ",
        			$this->gmwSD['units'], $this->gmwSD['your_lat'], $this->gmwSD['your_lng'], $this->gmwSD['your_lat'] );
        	$this->clauses['bp_user_query']['having']       = $wpdb->prepare( " HAVING distance <= %d OR distance IS NULL", $this->formData['radius'] );
        	$this->clauses['wp_user_query']['query_fields'] = $wpdb->prepare( " ,ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance",
        			$this->gmwSD['units'], $this->gmwSD['your_lat'], $this->gmwSD['your_lng'], $this->gmwSD['your_lat'] );
        }

        //select all fields from location table
        $this->clauses['wp_user_query']['query_fields'] .= " , gmwlocations.lat, gmwlocations.long, gmwlocations.address, gmwlocations.formatted_address";
        $this->clauses['wp_user_query']['query_from'] = " {$tJoin2} JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id ";

    }

    public function gmwBpDirectoryQuery( $gmwBpQuery ) {
 
        $gmwBpQuery->query_vars['count_total'] = 'sql_calc_found_rows';

        $gmwBpQuery->uid_clauses['select']  = $this->clauses['bp_user_query']['select'];
        $gmwBpQuery->uid_clauses['select'] .= $this->clauses['bp_user_query']['from'];
        
        if ( $this->formData['orderby'] != 'alphabetical' ) {
       		$gmwBpQuery->uid_clauses['where']   .= $this->clauses['bp_user_query']['where'];
        } else {
        	
        	$gmwBpQuery->uid_clauses['where'] = ( isset( $gmwBpQuery->query_vars['include'] ) && !empty( $gmwBpQuery->query_vars['include'] ) ) ? "WHERE u.ID IN ( ".implode(',',$gmwBpQuery->query_vars['include']).")" : '';
        }
        
        
        if ( isset( $this->clauses['bp_user_query']['having'] ) ) {
            $gmwBpQuery->uid_clauses['where'] .= $this->clauses['bp_user_query']['having'];
        } 
        
        $gmwBpQuery->uid_clauses['orderby'] = $this->clauses['bp_user_query']['orderby'];
        $gmwBpQuery->uid_clauses['order']   = $this->clauses['bp_user_query']['order'];
        
        return $gmwBpQuery;

    }

    public function gmwWpDirectoryQuery( $gmwWpQuery ) {

        $gmwWpQuery->query_fields .= $this->clauses['wp_user_query']['query_fields'];
        $gmwWpQuery->query_from .= $this->clauses['wp_user_query']['query_from'];

        return $gmwWpQuery;

    }

    /**
     * Trigger javascript to display maps and markers
     */
    public function trigger_js_and_maps() {
        global $members_template;

        $this->gmwSD['map_icon_usage'] = '';
        $this->gmwSD['page']           = $members_template->pag_page;
        $this->gmwSD['per_page']       = $members_template->pag_num;

        wp_enqueue_script( 'gmw-sd-map' );
        wp_enqueue_script( 'gmw-marker-clusterer' );
        
        ?>
        <script>
        	//pass some values to javascript
        	var sdMapArgs = JSON.parse('<?php echo json_encode( $this->gmwSD ); ?>');
        	jQuery(window).ready(function($) {
            	setTimeout(function() {
                	jQuery('#gmw-sd-main-map-wrapper').slideToggle(function() {
                		sdMapInit(sdMapArgs);
                		jQuery('.gmw-sd-map-loader').fadeOut(1500);
                	});
            	}, 500);
        	});
       	</script>
       	<?php  
        
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
     * Create Main map
     */
    public function main_map() {

        $mainMap = '';
        $mainMap .= '<div class="gmw-map-wrapper gmw-sd-main-map-wrapper" id="gmw-sd-main-map-wrapper" style="display:none;width:' . $this->gmwSD['map_width'] . ';height:' . $this->gmwSD['map_height'] . '">';
        $mainMap .= 	'<div class="gmw-map-loader-wrapper gmw-sd-loader-wrapper">';
        $mainMap .= 		'<img class="gmw-map-loader gmw-sd-map-loader" src="' . GMW_URL . '/assets/images/map-loader.gif"/>';
        $mainMap .= 	'</div>';
        $mainMap .= 	'<div class="gmw-map gmw-sd-main-map" id="gmw-sd-main-map" style="width:100%; height:100%"></div>';
        $mainMap .= '</div>';

        echo $mainMap;

    }

    /**
     * Get directions" link
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function get_directions() {
        global $members_template;

        if ( !isset( $this->gmwSD['directions'] ) || !$members_template->member->lat || !$members_template->member->long )
        	return;
		
        $settings = get_option( 'gmw_options' );
        
        $region	   = ( !empty( $settings['general_settings']['country_code'] ) )  ? '&region=' .$settings['general_settings']['country_code'] : '';
        $language  = ( !empty( $settings['general_settings']['language_code'] ) ) ? '&hl=' .$settings['general_settings']['language_code'] : ''; 
        $units 	   = ( $this->gmwSD['units'] == '6371' ) ? 'ptk' : 'ptm';
        $ulLatLng  = ( $this->gmwSD['your_lat'] && $this->gmwSD['your_lng'] ) ? "{$this->gmwSD['your_lat']},{$this->gmwSD['your_lng']}" : "";
        $title 	   = __( 'Get Directions', 'GMW' );
         
        return "<a title=\"{$title}\" href=\"https://maps.google.com/maps?f=d{$language}{$region}&doflg={$units}&saddr={$ulLatLng}&daddr={$members_template->member->lat},{$members_template->member->long}&ie=UTF8&z=12\" target=\"_blank\">{$title}</a>";
    }

    public function get_distance() {

        //distance
        if ( !isset( $this->gmwSD['distance'] ) || $this->formData['query'] != 'address' )
            return;

        global $members_template;

        if ( !isset( $members_template->member->distance ) )
            return;

        $units = ( $this->gmwSD['units'] == '6371' ) ? __( 'km', 'GMW' ) : __( 'mi', 'GMW' );
        echo '<span class="gmw-sd-distance">' . $members_template->member->distance . $units . '</span>';

    }

    public function get_address() {
        global $members_template;

        return apply_filters( 'gmw_sd_member_address', $members_template->member->address, $members_template->member );

    }

    /**
     * GEM MD Funciton - add GEO my WP elements to members results
     */
    public function add_elements_to_results() {
        global $members_template;
		$distance = false;
		
        //if member does not have location get out!!
        if ( !isset( $members_template->member->lat ) )
            return;

        //address
        if ( isset( $this->gmwSD['address'] ) )
            echo '<div class="gmw-sd-address-wrapper"><span class="gmw-sd-address">' . __( 'Address:', 'GMW' ) . '</span> <span class="gmw-sd-address-value">' . $this->get_address() . '</span></div>';
        //directions
     
        echo self::get_directions();
        
        if ( isset( $members_template->member->distance ) && !empty( $members_template->member->distance ) ) {
        	$units 	  = ( $this->gmwSD['units'] == '6371' ) ? __( 'km', 'GMW' ) : __( 'mi', 'GMW' );
      		$distance = $members_template->member->distance . ' ' . $units;
        }

        //add lat/long locations array to pass to map
        $this->gmwSD['members'][] = array(
        		'ID' 		=> $members_template->member->ID,
        		'user_link' => bp_core_get_user_domain( $members_template->member->ID ),
        		'avatar'	=> ( bp_get_user_has_avatar( $members_template->member->ID ) ) ? bp_core_fetch_avatar( array( 'item_id' => $members_template->member->ID, 'type' => 'thumb', 'width' => 10, 'height' => 10, 'html' => false, 'no_grav' => true ) ) : GMW_SD_URL . '/assets/images/_no_avatar.png',
        		'lat' 		=> $members_template->member->lat,
        		'long' 		=> $members_template->member->long,
        		'user_name' => $members_template->member->user_nicename,
        		'distance' 	=> $distance
        );

    }

}
new GMW_SD_Class_Query();
