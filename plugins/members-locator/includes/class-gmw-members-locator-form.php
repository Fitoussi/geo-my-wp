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
    public $object_permalink_hook = "bp_get_member_permalink";

    /**
     * [get_object_data description]
     * @param  [type] $location [description]
     * @return [type]           [description]
     */
    public function get_object_data( $member ) {

        return array(
            'url'       => bp_core_get_user_domain( $member->ID ),
            'title'     => $member->display_name,
            'image_url' => bp_core_fetch_avatar( 'item_id='.$member->ID.'&type=thumb&html=FALSE' )
        );
    }

    public function before_search_results() {

        echo '<div class="buddypress">';
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
    public static function query_xprofile_fields( $form_values = array(), $gmw = array() ) {

        global $bp, $wpdb, $wp_version;

        $users_id = array();

        foreach ( $form_values as $field_name => $value ) {
     
            // skip if not xprofile value
            if ( ! absint( $field_id = trim( $field_name, 'field_' ) ) ) {
                continue;
            }

            // get the field data
            $field_data = new BP_XProfile_Field( $field_id );
            $field_name = esc_attr( $field_name );
            $value      = '';

            // if form submitted
            if ( ! empty( $_GET['action'] ) && ! empty( $form_values[$field_name] ) ) {

                if ( is_array( $form_values[$field_name] ) ) {

                    $value  = array_map( 'esc_attr', $form_values[$field_name] );

                } else {

                    $value = esc_attr( stripslashes( $form_values[$field_name] ) );
                }

            } else {
                    
                $value = apply_filters( 'gmw_fl_xprofile_query_default_value', '', $field_id, $field_data, $gmw );

                if ( ! empty( $value ) ) {
                    $value = ( is_array( $value ) ) ? array_map( 'esc_attr', $value ) : esc_attr( stripslashes( $value ) );
                }
            }

            $max = ( isset( $form_values[$field_name.'_max'] ) ) ? absint( $form_values[$field_name.'_max'] ) : '';
            $sql = $wpdb->prepare ( "SELECT `user_id` FROM {$bp->profile->table_name_data} WHERE `field_id` = %d ", $field_id );
             
            if ( ! $value && ! $max ) {
                continue;
            }

            $fields_empty = false;
            
            if ( $value || $max ) {

                switch ( $field_data->type ) {
                
                    case 'textbox':
                    case 'textarea':

                        $value = str_replace ( '&', '&amp;', $value );

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
                        
                        $value = str_replace ( '&', '&amp;', $value );
                        $sql  .= $wpdb->prepare ( "AND value = %s", $value );
                    
                    break;
                            
                    case 'multiselectbox':
                    case 'checkbox':

                        $values = $value;
                        $like   = array ();
                         
                        foreach ( $values as $value ) {
                            
                            $value = str_replace ( '&', '&amp;', $value );

                            if ( $wp_version < 4.0 ) {

                                $escaped = '%'. esc_sql( like_escape( $value ) ). '%';
                            
                            } else {
                            
                                $escaped = '%' . $wpdb->esc_like( $value ) . '%';
                            }

                            $like[] = $wpdb->prepare( "value = %s OR value LIKE %s", $value, $escaped );
                        }
                         
                        $sql .= 'AND ('. implode (' OR ', $like). ')';
                         
                    break;

                    case 'datebox':
                    case 'birthdate':

                        $value = ! $value ? '1'   : $value;
                        $max   = ! $max   ? '200' : $max;
                                            
                        if ( $max < $value ) {
                            $max = $value;
                        }

                        $time  = time();
                        $day   = date( 'j', $time );
                        $month = date( 'n', $time );
                        $year  = date( 'Y', $time );
                        $ymin  = $year - $max - 1;
                        $ymax  = $year - $value;

                        if ( $max   !== '') $sql .= $wpdb->prepare( " AND DATE(value) > %s", "$ymin-$month-$day" );
                        if ( $value !== '') $sql .= $wpdb->prepare( " AND DATE(value) <= %s", "$ymax-$month-$day" );

                    break;                   
                }
                        
                $results  = $wpdb->get_col( $sql, 0 );
                $users_id = empty( $users_id ) ? $results : array_intersect( $users_id, $results ); 

                //abort if no users found for this fields
                if ( empty( $users_id ) ) {
                    return -1;
                }
                         
            } // if value //
        } // for eaech //
            
        return $users_id;
    }

    function oreder_results_by_distance( $clauses, $vars ) {

        if ( $vars->query_vars['type'] == 'distance' ) {
    
            $objects_id = implode( ',', $this->objects_id );
     
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
        
        $include_users = array();

        $locations_objects_id = $this->pre_get_locations_data();

        // do locations query. If nothing was found abort and show no result
        if ( empty( $locations_objects_id ) ) {
       
            return false;
        }

        // query xprofile fields
        if ( apply_filters( 'gmw_fl_xprofile_query_enabled', true, $this->form ) && array_filter( $this->form['search_form']['profile_fields'] ) ) {
            $xf_users = self::query_xprofile_fields( $this->form['form_values'], $this->form );

            // if no users returned from xprofile fields we can skip the rest and return
            // no results
            if ( $xf_users == -1 ) {

                return false;
            } 

            // only if users returned from xprofile fields query we will compare it 
            // with users returned from locations. Otherwise we will keep the original users
            // fron location query
            if ( ! empty( $xf_users ) ) {

                $include_users = array_intersect( $locations_objects_id, $xf_users );
            }
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
            'include'   => $include_users,
            'gmw_args'  => $gmw_query_args,
        ), $this->form, $this );

        //modify the form values before the query takes place
        $this->form = apply_filters( 'gmw_fl_form_before_members_query', $this->form, $this );

        // order results by distance if needed
        add_filter( 'bp_user_query_uid_clauses', array( $this, 'oreder_results_by_distance' ), 50, 2 );

        $internal_cache = GMW()->internal_cache;

        if ( $internal_cache ) {

            // prepare for cache
            $hash            = md5( json_encode( $this->form['query_args'] ) );
            $query_args_hash = 'gmw' . $hash . GMW_Cache_Helper::get_transient_version( 'gmw_get_object_user_query' );
        }

        global $members_template;

        if ( ! $internal_cache || false === ( $members_template = get_transient( $query_args_hash ) ) ) {
        
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

        $this->form['results']       = $members_template->members;
        $this->form['results_count'] = count( $members_template->members );
        $this->form['total_results'] = $members_template->total_member_count;
        $this->form['max_pages']     = $this->form['total_results']/$this->form['get_per_page'];

        $temp_array = array();

        foreach ( $this->form['results'] as $member ) {

            $temp_array[] = parent::the_location( $member->ID, $member );
        }

        $this->form['results'] = $temp_array;

        return $this->form['results'];
    }
}