<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'GMW_Form' ) ) {
	return;
}

/**
 * Members Locator search query class
 *
 * @author Eyal Fitoussi
 */
class GMW_Members_Locator_Form extends GMW_Form {

    /**
     * Permalink hook
     * 
     * @var string
     */
    public $object_permalink_hook = 'bp_get_member_permalink';

    /**
     * Results message
     * 
     * @var array
     */
    public $results_message = array(
        'count_message'  => 'Viewing {from_count} - {to_count} of {total_results} members',
        'radius_message' => ' within {radius} {units} from {address}'
    );

    /**
     * [get_info_window_args description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    public function get_info_window_args( $member ) {

        if ( isset( $this->form['info_window']['image'] ) ) {
            if ( $this->form['info_window']['image']['enabled'] == '' ) {
                $avatar = false;
            } else {
                $avatar = bp_core_fetch_avatar( array( 
                    'item_id' => $member->ID,
                    'width'   => $this->form['info_window']['image']['width'],
                    'height'  => $this->form['info_window']['image']['height']
                ) );
            }
        } else {
            $avatar = bp_core_fetch_avatar( array( 
                'item_id' => $member->ID,
                'width'   => 180,
                'height'  => 180
            ) );
        }

        return array(
            'prefix'    => $this->prefix,
            'type'      => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
            'image'     => $avatar,
            'url'       => bp_core_get_user_domain( $member->ID ),
            'title'     => $member->display_name
        );
    }

    public function before_search_results() {
        echo '<div id="buddypress">';
    }

    public function after_search_results() {
        echo '</div>';
    }

    /**
     * Query xprofile fields
     *
     * Note $formValues might come from URL. IT needs to be sanitized before being used
     * 
     * @version 1.0
     * @author Eyal Fitoussi
     * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
     * 
    */
    public static function query_xprofile_fields( $fields_values = array(), $gmw = array() ) {

        global $bp, $wpdb, $wp_version;

        $users_id = array();

        foreach ( $fields_values as $field_id => $value ) {
        
            if ( empty( $value ) || ( is_array( $value ) && ! array_filter( $value ) ) ) {
                continue;
            }

            // get the field data
            $field_data = new BP_XProfile_Field( $field_id );

            $sql = $wpdb->prepare ( "SELECT `user_id` FROM {$bp->profile->table_name_data} WHERE `field_id` = %d ", $field_id );

            switch ( $field_data->type ) {
            
                case 'textbox':
                case 'textarea':

                    $value = str_replace( '&', '&amp;', $value );

                    if ( $wp_version < 4.0 ) {
                        $escaped = '%'. esc_sql( like_escape( trim( $value ) ) ). '%';
                    } else {
                        $escaped = '%' . $wpdb->esc_like( trim( $value ) ) . '%';
                    }

                    $sql .= $wpdb->prepare ( "AND value LIKE %s", $escaped );

                break;

                case 'number':
                    
                    $sql .= $wpdb->prepare ( "AND value = %d", $value );
                
                break;

                case 'selectbox':
                case 'radio':
                    
                    $value = str_replace( '&', '&amp;', $value );
                    $sql  .= $wpdb->prepare( 'AND value = %s', $value );
                
                break;
                        
                case 'multiselectbox':
                case 'checkbox':

                    $values = $value;
                    $like   = array ();
                     
                    foreach ( $values as $value ) {
                        $value = str_replace( '&', '&amp;', $value );
                        if ( $wp_version < 4.0 ) {
                            $escaped = '%'.esc_sql( like_escape( $value ) ).'%';
                        } else {
                            $escaped = '%'.$wpdb->esc_like( $value ).'%';
                        }

                        $like[] = $wpdb->prepare( 'value = %s OR value LIKE %s', $value, $escaped );
                    }
                     
                    $sql .= 'AND ('. implode (' OR ', $like). ')';
                     
                break;

                case 'datebox':
                case 'birthdate':

                    if ( ! is_array( $value ) || ! array_filter( $value ) ) {
                        continue;
                    }
                    
                    $min = ! empty( $value['min'] ) ? $value['min'] : '1';
                    $max = ! empty( $value['max'] ) ? $value['max'] : '200';

                    if ( $min > $max ) $max = $min;

                    $time  = time();
                    $day   = date( 'j', $time );
                    $month = date( 'n', $time );
                    $year  = date( 'Y', $time );
                    $ymin  = $year - $max - 1;
                    $ymax  = $year - $min;

                    if ( $max !== '' ) $sql .= $wpdb->prepare( " AND DATE(value) > %s", "$ymin-$month-$day" );
                    if ( $min !== '' ) $sql .= $wpdb->prepare( " AND DATE(value) <= %s", "$ymax-$month-$day" );

                break;                   
            }
                    
            $results  = $wpdb->get_col( $sql, 0 );
            $users_id = empty( $users_id ) ? $results : array_intersect( $users_id, $results ); 

            //abort if no users found for this fields
            if ( empty( $users_id ) ) {
                return -1;
            }         
        }

        return $users_id;
    }

    /**
     * Orderby distance
     * 
     * @param  [type] $clauses [description]
     * @param  [type] $vars    [description]
     * @return [type]          [description]
     */
    function order_results_by_distance( $clauses, $vars ) {

        if ( $vars->query_vars['type'] == 'distance' ) {

            $objects_id         = implode( ',', $this->objects_id );
            $clauses['orderby'] = " ORDER BY FIELD( id, {$objects_id} )";
        }

        return $clauses;
    }

    /**
     * Query results
     * 
     * @return [type] [description]
     */
    public function search_query() {
        	 
    	//prevent BuddyPress from using its own "paged" value for the current page
    	if ( ! empty( $_GET['upage'] ) ) {
    		unset( $_GET['upage'] );
        }
        
        $include_users = $loc_args = array();

        $locations_objects_id = $this->pre_get_locations_data();

        // do locations query. If nothing was found abort and show no result
        if ( empty( $locations_objects_id ) ) {
            return false;
        }

        $include_users = $locations_objects_id;

        // look for xprofile values in URL
        if ( isset( $this->form['form_values']['xf'] ) && array_filter( $this->form['form_values']['xf'] ) ) {
            $fields_values = $this->form['form_values']['xf'];
        // otherwise, can do something custom with xprofile fields
        // by passing array of array( fields => value ).
        } else {
            $fields_values = apply_filters( 'gmw_fl_xprofile_fields_query_default_values', array(), $this->form );
        }

        // query xprofile fields
        if ( apply_filters( 'gmw_fl_xprofile_query_enabled', true, $this->form ) && array_filter( $fields_values ) ) {
            
            $xf_users = self::query_xprofile_fields( $fields_values, $this->form );

            // if no users returned from xprofile fields we can skip the rest and return
            // no results
            if ( $xf_users == -1 ) {
                return false;
            } 

            // only if users returned from xprofile fields query we will compare it 
            // with users returned from locations. Otherwise we will keep the original users
            // fron location query
            if ( ! empty( $xf_users ) ) {
                $include_users = array_intersect( $include_users, $xf_users );
            }
        }   

        // abort if no users found
        if ( empty( $include_users ) ) {
            return false;
        }

        // get query args for cache
        if ( $this->form['page_load_action'] ) {

            $gmw_query_args = $this->form['page_load_results'];

        } elseif ( $this->form['submitted'] ) {  

            $gmw_query_args = $this->form['form_values'];
        }

        //query args
        $this->form['query_args'] = apply_filters( 'gmw_fl_search_query_args', array(
            'type'      => 'distance',
            'per_page'  => $this->form['get_per_page'],
            'page'      => $this->form['paged'],
            'gmw_args'  => $gmw_query_args,
        ), $this->form, $this );

        /*
         * compute the members to include based on the include arguments and 
         * 
         * returned from the locations query args.
         *
         * We do this to allow other plugins use the include argument first.
         */ 
        if ( ! empty( $this->form['query_args']['include'] ) ) {

            if ( ! is_array( $this->form['query_args']['include'] ) ) {
                $this->form['query_args']['include'] = explode( ',', $this->form['query_args']['include'] );
            }

            $this->form['query_args']['include'] = array_intersect( $this->form['query_args']['include'], $include_users );
        
        } else {
        
            $this->form['query_args']['include'] = $include_users;
        }

        //modify the form values before the query takes place
        $this->form = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this );

        if ( empty( $this->form['query_args']['include'] ) || in_array( 0, $this->form['query_args']['include'] ) ) {
            return false;
        }

        // order results by distance if needed
        add_filter( 'bp_user_query_uid_clauses', array( $this, 'order_results_by_distance' ), 30, 2 );

        $internal_cache = GMW()->internal_cache;

        if ( $internal_cache ) {

            // prepare for cache
            $hash = md5( json_encode( $this->form['query_args'] ) );
            $query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_user_query' );
        }

        global $members_template;

        if ( ! $internal_cache || false === ( $members_template = get_transient( $query_args_hash ) ) ) {
        //if ( 1 == 1 ){ 
            //print_r( 'Members query done' );

            // query members
            $results = bp_has_members( $this->form['query_args'] ) ? true : false;

            // set new query in transient    
            if ( $internal_cache ) {
                set_transient( $query_args_hash, $members_template, GMW()->internal_cache_expiration );
            }
        }
        
        //Modify the form after the search query
        $this->form = apply_filters( 'gmw_fl_form_after_members_query', $this->form, $this );

        $members_template = $this->query = apply_filters( 'gmw_fl_members_before_members_loop', $members_template, $this->form, $this );

        $this->form['results_count'] = count( $members_template->members );
        $this->form['total_results'] = $members_template->total_member_count;
        $this->form['max_pages']     = $this->form['total_results']/$this->form['get_per_page'];

        $temp_array = [];

        foreach ( $members_template->members as $member ) {
            $temp_array[] = parent::the_location( $member->id, $member );
        }

         $this->form['results'] = $members_template->members = $temp_array;

        return $this->form['results'];
    }

    /**
     * Merge member object with location object
     * 
     * @param  [type] $member [description]
     * @return [type]         [description]
     */
    public function the_member( $member ) {
        return $this->the_location( $member->id, $member );
    }

}