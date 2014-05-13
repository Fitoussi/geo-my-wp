<?php

/**
 * GMW FL search form function - Display xprofile fields
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_xprofile_fields($gmw, $class) {

    if ((!isset($gmw['search_form']['profile_fields']) && !isset($gmw['search_form']['profile_fields_date'])))
        return;

    $total_fields = ( isset($gmw['search_form']['profile_fields']) ) ? $gmw['search_form']['profile_fields'] : array();
    if (isset($gmw['search_form']['profile_fields_date']))
        array_unshift($total_fields, $gmw['search_form']['profile_fields_date']);

    echo '<div class="gmw-fl-form-xprofile-fields ' . $class . '">';

    foreach ($total_fields as $field_id) {

        $field_data   = new BP_XProfile_Field($field_id);
        $fieldName    = explode(' ', $field_data->name);
        $fieldName    = preg_replace("/[^A-Za-z0-9]/", "", $fieldName[0]);
        $get_field    = ( isset($_GET[$fieldName . '_' . $field_id])) ? $_GET[$fieldName . '_' . $field_id] : '';
        $get_field_to = ( isset($_GET[$fieldName . '_' . $field_id . '_to'])) ? $_GET[$fieldName . '_' . $field_id . '_to'] : '';

        $children = $field_data->get_children();

        switch ($field_data->type) {

            case 'datebox':

                echo '<div class="editfield field_' . $field_id . ' datebox">';
                echo '<span class="label">' . __('Age Range (min - max)', 'GMW') . '</span>';
                echo '<input size="3" type="text" name="' . $fieldName . '_' . $field_id . '" value="' . $get_field . '" placeholder="' . __('Min', 'GMW') . '" />';
                echo '&nbsp;-&nbsp;';
                echo '<input size="3" type="text" name="' . $fieldName . '_' . $field_id . '_to" value="' . $get_field_to . '" placeholder="' . __('Max', 'GMW') . '" />';
                echo '</div>';
                break;

            case 'multiselectbox':
            case 'selectbox':
            case 'radio':
            case 'checkbox':

                echo '<div class="editfield field_' . $field_id . ' checkbox">';
                echo '<span class="label">' . $field_data->name . '</span>';
                $tt = array();

                if ($get_field) {
                    $tt = $get_field;
                }

                foreach ($children as $child) {
                    $child->name = trim($child->name);
                    $checked     = ( in_array($child->name, $tt) ) ? "checked='checked'" : "";
                    echo '<label><input ' . $checked . ' type="checkbox" name="' . $fieldName . '_' . $field_id . '[]" value="' . $child->name . '" />' . $child->name . '</label>';
                }

                echo '</div>';

                break;
        } // switch
    }
    echo '</div>';

}

/**
 * GMW FL Search results function - Display user's full address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_member_address( $gmw ) {

    global $members_template;
    echo apply_filters( 'gmw_fl_members_loop_address', $members_template->member->formatted_address, $gmw, $members_template );

}

/**
 * GMW FL Search results function - Display Radius distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_get_by_radius($gmw) {
    global $members_template;

    if (isset($gmw['your_lat']) && !empty($gmw['your_lat']))
        return apply_filters('gmw_fl_by_radius', '<div class="gmw-fl-radius-wrapper">' . $members_template->member->distance . ' ' . $gmw['units_array']['name'] . '</div>', $gmw, $members_template);

}

function gmw_fl_by_radius($gmw) {
    echo gmw_fl_get_by_radius($gmw);

}

/**
 * GMW FL search results function - "Get directions" link
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_get_directions_link($gmw, $title) {
    global $members_template;
	
    $settings = get_option( 'gmw_options' );
    
    if (!isset($gmw['search_results']['get_directions']))
        return;

    $region	   = ( isset( $settings['general_settings']['country_code'] ) && !empty( $settings['general_settings']['country_code'] ) ) ? '&region=' .$settings['general_settings']['country_code'] : '';
    $language  = ( isset( $settings['general_settings']['language_code'] ) && !empty( $settings['general_settings']['language_code'] ) ) ? '&hl=' .$settings['general_settings']['language_code'] : '';
    
    return apply_filters('gmw_fl_get_directions_link', '<a href="http://maps.google.com/maps?f=d'.$language . '' .$region . '&doflg=' . $gmw['units_array']['map_units'] . '&geocode=&saddr=' . $gmw['org_address'] . '&daddr=' . str_replace(" ", "+", $members_template->member->formatted_address) . '&ie=UTF8&z=12" target="_blank">' . $title . '</a>', $gmw, $members_template, $title);

}

function gmw_fl_directions_link($gmw, $title) {
    echo gmw_fl_get_directions_link($gmw, $title);

}

/**
 * GMW FL search results function - display within distance message
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_wihtin_message($gmw) {

    if (!isset($gmw['org_address']) || empty($gmw['org_address']))
        return;
    echo ' <span>within ' . $gmw['radius'] . ' ' . $gmw['units_array']['name'] . ' from ' . $gmw['org_address'] . '</span>';

}

/**
 * GMW FL search results function - calculate driving distance
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_driving_distance($gmw, $class) {
    global $members_template;

    if (!isset($gmw['search_results']['by_driving']) || $gmw['units_array']['name'] == false)
        return;

    echo '<div id="gmw-fl-driving-distance-' . $members_template->member->ID . '" class="' . $class . '"></div>';
    ?>
    <script>
        var directionsDisplay;
        var directionsService = new google.maps.DirectionsService();
        var directionsDisplay = new google.maps.DirectionsRenderer();

        var start = new google.maps.LatLng('<?php echo $gmw['your_lat']; ?>', '<?php echo $gmw['your_lng']; ?>');
        var end = new google.maps.LatLng('<?php echo $members_template->member->lat; ?>', '<?php echo $members_template->member->long; ?>');
        var request = {
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING
        };

        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);
                if ('<?php echo $gmw['units_array']['name']; ?>' == 'Mi') {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.000621371192 * 10) / 10) + ' Mi';
                } else {
                    totalDistance = (Math.round(result.routes[0].legs[0].distance.value * 0.01) / 10) + ' Km';
                }

                jQuery('#<?php echo 'gmw-fl-driving-distance-' . $members_template->member->ID; ?>').text('Driving: ' + totalDistance)
            }
        });
    </script>
    <?php

}

/**
 * GMW FL Search results function - Per page dropdown
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_fl_per_page_dropdown($gmw, $class) {
    global $members_template;

    $perPage  = explode(",", $gmw['search_results']['per_page']);
    $lastpage = ceil($members_template->total_member_count / $gmw['get_per_page']);

    if (count($perPage) > 1) :

        echo '<select name="gmw_per_page" class="gmw-fl-per-page-dropdown ' . $class . '">';

        foreach ($perPage as $pp) :

            if (isset($_GET['gmw_per_page']) && $_GET['gmw_per_page'] == $pp)
                $pp_s = 'selected="selected"';
            else
                $pp_s = "";
            echo '<option value="' . $pp . '" ' . $pp_s . '>' . $pp . ' per page</option>';

        endforeach;

        echo '</select>';

    endif;
    ?>
    <script>
	
        jQuery(document).ready(function($) {

            $(".gmw-fl-per-page-dropdown").change(function() {

                var totalResults = <?php echo $members_template->total_member_count; ?>;
                var lastPage = Math.ceil(totalResults / $(this).val());
                var newPaged = (<?php echo $members_template->pag_num; ?> > lastPage) ? lastPage : <?php echo $members_template->pag_num; ?>;

                if ( window.location.search.length ) {
			   		window.location.href = window.location.href.replace(/(gmw_per_page=).*?(&)/,'$1' + $(this).val() + '$2').replace(/(upage=).*?(&)/,'$1' + newPaged + '$2');
			   	} else {
			   		window.location.href = window.location.href + '?gmw=auto&gmw_per_page='+$(this).val() + '&gmw_form=<?php echo $gmw['ID']; ?>&upage='+newPaged;
			   	}

            });
        });
    </script>
    <?php

}

/**
 * GMW FL results function - no members found
 */
function gmw_fl_no_members( $gmw ) {
	
	do_action( 'gmw_'.$gmw['form_type'].'_before_no_results', $gmw  );
	
	echo apply_filters( 'gmw_'.$gmw['form_type'].'_no_results_message', __( 'Sorry, No members found', 'GMW' ), $gmw );
	
	do_action( 'gmw_'.$gmw['form_type'].'_after_no_results', $gmw );
	
}

/**
 * GMW FL function - display members count
 * @para  $gmw
 * @param $gmw_options
 */
function gmw_fl_member_count($gmw) {
    global $members_template;

    echo $members_template->member->member_count;

}

/**
 * GMW_FL_Search_Query class
 *
 */
class GMW_FL_Search_Query extends GMW {

    /**
     * __construct function.
     */
    function __construct($form, $results) {

        do_action('gmw_fl_search_query_start', $form, $results);

        add_filter('gmw_fl_after_query_clauses', array($this, 'query_xprofile_fields'), 5, 2);
        add_action('member_loop_end', array($this, 'localize_members'), 10);
        add_action('gmw_fl_directory_member_start', array($this, 'modify_member'), 10);

        add_filter('member_loop_start', array($this, 'loop_start'), 10, 2);

        parent::__construct($form, $results);

    }

    /**
     * Include search form
     * 
     */
    public function search_form() {

        $gmw = $this->form;

        do_action('gmw_fl_before_search_form', $this->form, $this->settings);

        wp_enqueue_style('gmw-' . $this->form['ID'] . '-' . $this->form['search_form']['form_template'] . '-form-style', GMW_FL_URL . 'search-forms/' . $this->form['search_form']['form_template'] . '/css/style.css');
        include GMW_FL_PATH . 'search-forms/' . $this->form['search_form']['form_template'] . '/search-form.php';

        do_action('gmw_fl_after_search_form', $this->form, $this->settings);

    }

    /**
     * Query xprofile fields
     * @version 1.0
     * @author Eyal Fitoussi
     * @author Some of the code in this function was inspired by the code written by Andrea Taranti the creator of BP Profile Search - Thank you
     */
    public function query_xprofile_fields($clauses) {

        global $bp, $wpdb;

        $total_fields = false;
        $total_fields = ( isset($this->form['search_form']['profile_fields']) ) ? $this->form['search_form']['profile_fields'] : array();
        if (isset($this->form['search_form']['profile_fields_date']) && !empty($this->form['search_form']['profile_fields_date']))
            array_unshift($total_fields, $this->form['search_form']['profile_fields_date']);

        if (!isset($total_fields) || empty($total_fields))
            return $clauses;

        $empty_fields = array();
        $userids      = false;

        foreach ($total_fields as $field_id) {

            $field_data = new BP_XProfile_Field($field_id);
            $fieldName  = explode(' ', $field_data->name);
            $fieldName  = preg_replace("/[^A-Za-z0-9]/", "", $fieldName[0]);
            $value      = ( isset($_GET[$fieldName . '_' . $field_id]) ) ? $_GET[$fieldName . '_' . $field_id] : '';
            $to         = ( isset($_GET[$fieldName . '_' . $field_id . '_to']) ) ? $_GET[$fieldName . '_' . $field_id . '_to'] : '';

            if ($value)
                array_push($empty_fields, $value);

            if ($value || $to) {

                switch ($field_data->type) {

                    case 'selectbox':
                    case 'multiselectbox':
                    case 'checkbox':
                    case 'radio':

                        $sql  = "SELECT user_id from {$bp->profile->table_name_data}";
                        $sql .= " WHERE field_id = $field_id ";
                        $like = array();

                        foreach ($value as $curvalue)
                            $like[] = "value = '$curvalue' OR value LIKE '%\"$curvalue\"%' ";

                        $sql .= ' AND (' . implode(' OR ', $like) . ')';

                        break;
                    case 'datebox':

                        $value = (!$value ) ? '1' : $value;
                        $to    = (!$to ) ? '200' : $to;
                        if ($to < $value)
                            $to    = $value;

                        $time  = time();
                        $day   = date("j", $time);
                        $month = date("n", $time);
                        $year  = date("Y", $time);
                        $ymin  = $year - $to - 1;
                        $ymax  = $year - $value;

                        $sql = "SELECT user_id from {$bp->profile->table_name_data}";
                        $sql .= " WHERE field_id = $field_id AND value > '$ymin-$month-$day' AND value <= '$ymax-$month-$day'";

                        break;
                }

                $results = $wpdb->get_col($sql, 0);

                if (!is_array($userids))
                    $userids = $results;
                else
                    $userids = array_intersect($userids, $results);
            } // if value //
        } // for eaech //

        /* build SQL filter from profile fields results - member ids array */
        if (isset($userids) && !empty($userids)) {

            $clauses['bp_user_query']['where'] .= $wpdb->prepare(" AND gmwlocations.member_id IN (" . str_repeat("%d,", count($userids) - 1) . "%d)", $userids);
            return $clauses;
        }
        /* if no results and profile fields are not empty - buba is going to stop the function */ elseif (!empty($empty_fields)) {

            $clauses['bp_user_query']['where'] .= " AND 1 = 0 ";
            return $clauses;
        } else {

            return $clauses;
        }

    }

    /**
     * members query clauses
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function query_clauses() {

        global $wpdb;

        $clauses['bp_user_query'] = false;
        $clauses['wp_user_query'] = false;

        /*
         * prepare the filter of bp_query_user. the filter will modify the SQL function and will check the distance
         * of each user from the address entered and will results in user ID's of the users that within
         * the radius entered. The user IDs will then pass to the next wp_query_user below.
         */
        if (!empty($this->form['org_address'])) :
            /*
             * if address entered:
             * prepare the filter of the select clause of the SQL function. the function join Buddypress's members table with
             * wppl_friends_locator table, will calculate the distance and will get only the members that
             * within the radius was chosen
             */
            $clauses['bp_user_query']['select'] = $wpdb->prepare(" SELECT gmwlocations.member_id as id, 			
				ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance ", $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat']);

            $clauses['bp_user_query']['from']     = " FROM wppl_friends_locator gmwlocations";
            $clauses['bp_user_query']['where']    = " WHERE 1 = 1 ";
            $clauses['bp_user_query']['having']   = $wpdb->prepare('HAVING distance <= %d OR distance IS NULL ', $this->form['radius']);
            $clauses['bp_user_query']['order_by'] = 'ORDER BY distance';
        else:
            /*
             * if no address entered choose all members that in members table and wppl_friends_locator table
             * check agains the useids (returned from xprofile fields query) if exist and results in ids
             */
            $clauses['bp_user_query']['select']   = " SELECT gmwlocations.member_id as id ";
            $clauses['bp_user_query']['where']    = " WHERE 1 = 1 ";
            $clauses['bp_user_query']['having']   = "";
            $clauses['bp_user_query']['from']     = " FROM wppl_friends_locator gmwlocations";
            $clauses['bp_user_query']['order_by'] = "";
        endif;
        /*
         * prepare the filter of the wp_query_user which is within bp_query_user.
         * the filter will modify the SQL function and will calculate the distance of each user
         * in the array of user IDs that was returned from the function above.
         * the filter will also add the members information from wppl_friends_locator table into the results
         * as well as the distance.
         */
        if (!empty($this->form['org_address'])) :

            $clauses['wp_user_query']['query_fields'] = $wpdb->prepare(" , gmwlocations.* , 
					ROUND( %d * acos( cos( radians( %s ) ) * cos( radians( gmwlocations.lat ) ) * cos( radians( gmwlocations.long ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( gmwlocations.lat) ) ),1 ) AS distance", $this->form['units_array']['radius'], $this->form['your_lat'], $this->form['your_lng'], $this->form['your_lat']);
        else :

            $clauses['wp_user_query']['query_fields'] = " , gmwlocations.* ";
        endif;

        $clauses['wp_user_query']['query_from'] = " INNER JOIN wppl_friends_locator gmwlocations ON ID = gmwlocations.member_id ";

        return apply_filters('gmw_fl_after_query_clauses', $clauses, $this->form);

    }

    /**
     * Add filter to BP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwBpQuery($gmwBpQuery) {

        /* modify the function to to calculate the total rows(members). */
        $gmwBpQuery->query_vars['count_total'] = 'sql_calc_found_rows';
        $gmwBpQuery->uid_clauses['select']     = $this->clauses['bp_user_query']['select'];
        $gmwBpQuery->uid_clauses['select'] .= $this->clauses['bp_user_query']['from'];
        $gmwBpQuery->uid_clauses['where']      = $this->clauses['bp_user_query']['where'];
        $gmwBpQuery->uid_clauses['where'] .= $this->clauses['bp_user_query']['having'];
        if (isset($this->clauses['bp_user_query']['order_by']))
            $gmwBpQuery->uid_clauses['orderby']    = $this->clauses['bp_user_query']['order_by'];
        if (isset($this->clauses['bp_user_query']['order']))
            $gmwBpQuery->uid_clauses['order']      = $this->clauses['bp_user_query']['order'];

        return $gmwBpQuery;

    }

    /**
     * Add filter to WP_user_query
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmwWpQuery($gmwWpQuery) {

        $gmwWpQuery->query_fields .= $this->clauses['wp_user_query']['query_fields'];
        $gmwWpQuery->query_from .= $this->clauses['wp_user_query']['query_from'];

        return $gmwWpQuery;

    }

    public function loop_start() {
        global $members_template;

        //setup member count
        $this->form['paged']        = (!isset($_GET['upage']) || $_GET['upage'] == 1 ) ? 1 : $_GET['upage'];
        $this->form['member_count'] = ( $this->form['paged'] == 1 ) ? 1 : ( $this->form['get_per_page'] * ( $this->form['paged'] - 1 ) ) + 1;
        $this->form['results']      = $members_template->members;

    }

    /**
     * modify members_template 
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function modify_member() {
        global $members_template;

        $members_template->member->permalink    = bp_get_member_permalink();
        $members_template->member->avatar       = bp_get_member_avatar($args                                   = 'type=full');
        $members_template->member->member_count = $this->form['member_count'];
        $members_template->member->mapIcon      = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld=' . $members_template->member->member_count . '|FF776B|000000';

        $this->form['member_count'] ++;

        $members_template = apply_filters('gmw_fl_modify_member', $members_template, $this->form);

    }

    public function localize_members() {
        global $members_template;

        if ($this->form['search_results']['display_map'] != 'na')
            wp_localize_script('gmw-fl-map', 'flMembers', $members_template->members);

    }

    public function results() {

        echo '<div id="buddypress">';

        // query clauses
        $this->clauses             = $this->query_clauses();
        $this->form['region']      = ( WPLANG ) ? explode('_', WPLANG) : array('en', 'US');
        $this->form['post_loader'] = GMW_URL . '/assets/images/gmw-loader.gif';

        add_action('bp_pre_user_query', array($this, 'gmwBpQuery'));
        add_action('pre_user_query', array($this, 'gmwWpQuery'));

        $this->form['query_args'] = array(
            'type'     => 'distance',
            'per_page' => $this->form['get_per_page']
        );

        // Hooks
        $this->form = apply_filters('gmw_fl_form_before_members_query', $this->form, $this->settings);
        do_action('gmw_fl_before_memebrs_query', $this->form, $this->settings);

        //load results template file to display list of members
        if ( isset($this->form['search_results']['display_members'] ) ) :

            $gmw = $this->form;

            // include custom results and stylesheet pages from child/theme 
            if (strpos($this->form['search_results']['results_template'], 'custom_') !== false) :

                $sResults = str_replace('custom_', '', $this->form['search_results']['results_template']);
                wp_register_style('gmw-current-style', get_stylesheet_directory_uri() . '/geo-my-wp/friends/search-results/' . $sResults . '/css/style.css');
                wp_enqueue_style('gmw-current-style');

                include(STYLESHEETPATH . '/geo-my-wp/friends/search-results/' . $sResults . '/results.php');
            //include results and stylesheet pages from plugin's folder
            else :

                wp_register_style('gmw-current-style', GMW_FL_URL . 'search-results/' . $this->form['search_results']['results_template'] . '/css/style.css');
                wp_enqueue_style('gmw-current-style');
                include GMW_FL_PATH . 'search-results/' . $this->form['search_results']['results_template'] . '/results.php';

            endif;

        /*
         * if we do not display list of members we still need to have a loop
         * and add some information to each members in order to be able to 
         * display it on the map
         */
        else :

            if ( bp_has_members( $this->form['query_args'] ) ) :

                while (bp_members()) : bp_the_member();

                    self::modify_member();

                endwhile;

            endif;

        endif;

        global $members_template;

        // if we need to display map
        if ($this->form['search_results']['display_map'] != 'na') {

        	$this->form['iw_labels'] = array(
        			'distance'   	=> __('Distance: ', 'GMW'),
        			'address'    	=> __('Address: ', 'GMW'),
        			'directions' 	=> __('Get Directions: ', 'GMW'),
        			'your_location' => __('Your Location', 'GMW')
        	);

        	$this->form       = apply_filters('gmw_fl_form_before_map', $this->form, $members_template, $this->settings);
        	$members_template = apply_filters('gmw_fl_members_before_map', $members_template, $this->form, $this->settings);

        	do_action('gmw_fl_has_memebrs_before_map', $this->form, $this->settings, $members_template);

        	$form            = $this->form;
        	$form['results'] = $members_template->members;

        	wp_enqueue_script('gmw-fl-map', true);
        	wp_localize_script('gmw-fl-map', 'gmwForm', $form);

        }

        echo '</div>';

    }

}
