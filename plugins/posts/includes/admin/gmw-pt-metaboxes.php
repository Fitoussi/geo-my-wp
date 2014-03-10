<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * MW_PT_Meta_Boxes class.
 */

class GMW_PT_Meta_Boxes {

    /**
     * Constructor
     */
    public function __construct() {

        $this->settings   = get_option( 'gmw_options' );
        $this->meta_boxes = $this->create_meta_boxes();

        add_action( 'admin_init', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_data' ) );

        add_action( 'admin_print_scripts-post-new.php', array( $this, 'register_admin_location_scripts' ), 11 );
        add_action( 'admin_print_scripts-post.php', array( $this, 'register_admin_location_scripts' ), 11 );

    }

    /**
     * admin_enqueue_scripts function.
     *
     * @access public
     * @return void
     */
    public function register_admin_location_scripts() {
        global $post_type;

        if ( isset( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) && !empty( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) && ( in_array( $post_type, $this->settings[ 'post_types_settings' ][ 'post_types' ] ) ) ) {

            wp_register_style( 'gmw-pt-admin-style', GMW_PT_URL . 'assets/css/style-admin.css' );
            wp_register_script( 'gmw-admin-address-picker', GMW_PT_URL . 'assets/js/jquery.ui.addresspicker.js', array( 'jquery' ), GMW_VERSION, true );
        }

    }

    /**
     * Create meta boxes
     */
    public function create_meta_boxes() {

        $prefix     = '_wppl_';
        $meta_boxes = array(
            'id'       => 'wppl-meta-box',
            'title'    => __( 'GMW Location', 'GMW' ),
            'pages'    => $this->settings[ 'post_types_settings' ][ 'post_types' ],
            'context'  => 'normal',
            'priority' => 'high',
            'fields'   => array(
                array(
                    'name' => __( 'Street', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'street',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Apt/Suit', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'apt',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'City', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'city',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'State', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'state',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Zipcode', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'zipcode',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Country', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'country',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Phone Number', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'phone',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'fax Number', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'fax',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Email Address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'email',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Website', 'GMW' ),
                    'desc' => 'Ex: www.website.com',
                    'id'   => $prefix . 'website',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Latitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'enter_lat',
                    'type' => 'text-right',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Longitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'enter_long',
                    'type' => 'text-right',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Latitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'lat',
                    'type' => 'text-disable',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Longitude', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'long',
                    'type' => 'text-disable',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Full Address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'address',
                    'type' => 'text-disable',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Days & Hours', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'days_hours',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'State Long', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'state_long',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Country Long', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'country_long',
                    'type' => 'text',
                    'std'  => ''
                ),
                array(
                    'name' => __( 'Formatted address', 'GMW' ),
                    'desc' => '',
                    'id'   => $prefix . 'formatted_address',
                    'type' => 'text',
                    'std'  => ''
                )
            )
        );

        return $meta_boxes;

    }

    /**
     * add meta boxes
     */
    public function add_meta_box() {

        if ( isset( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) && !empty( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) ) {
            foreach ( $this->settings[ 'post_types_settings' ][ 'post_types' ] as $page ) {
                add_meta_box( $this->meta_boxes[ 'id' ], $this->meta_boxes[ 'title' ], array( $this, 'display_meta_box' ), $page, $this->meta_boxes[ 'context' ], $this->meta_boxes[ 'priority' ] );
            }
        }

    }

    /**
     * Display meta boxes
     */
    function display_meta_box() {
        global $post, $wpdb;

        $post_info = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "places_locator WHERE post_id = %d", array( $post->ID ) ) );

        if ( !isset( $post_info ) || empty( $post_info ) )
            $post_info = ( object ) array(
                        'post_id'           => '',
                        'feature'           => '',
                        'post_status'       => '',
                        'post_type'         => '',
                        'post_title'        => '',
                        'lat'               => '',
                        'long'              => '',
                        'street'            => '',
                        'apt'               => '',
                        'city'              => '',
                        'state'             => '',
                        'state_long'        => '',
                        'zipcode'           => '',
                        'country'           => '',
                        'country_long'      => '',
                        'address'           => '',
                        'formatted_address' => '',
                        'phone'             => '',
                        'fax'               => '',
                        'email'             => '',
                        'website'           => '',
                        'map_icon'          => ''
            );

        if ( isset( $this->settings[ 'general_settings' ][ 'mandatory_address' ] ) )
            wp_localize_script( 'wppl-address-picker', 'addressMandatory', $this->settings[ 'general_settings' ][ 'mandatory_address' ] );

        echo 	'<input type="hidden" name="this->meta_boxes_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
            echo	'<div class="wppl-admin-map-holder" style="position:relative">';
            echo		'<div class="gmw-admin-location-head" style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo			'<h3 style="font-size:13px;">'. __('Use the map to drag and drop the marker to the desired location.','GMW').'</h3>';
            echo		'</div>';
            echo		'<div id="map"></div>';
            echo	'</div>';

            echo	'<div class="gmw-admin-location-head" style="float:left;width:30%;text-align:center;margin-top: 10px;padding: 0px;border-bottom: 1px solid #ddd;">';
            echo		'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo			'<h3 style="font-size:13px;">'. __('Get your current location','GMW').'</h3>';
            echo		'</div>';
            echo		'<div style="padding:5px 0px;">';
            echo 			'<input type="button" id="gmw-admin-locator-btn" class="button-primary" value="'; _e('Locate Me','GMW'); echo'" />';
            echo		'</div>';
            echo	'</div>';

            echo	'<div class="gmw-admin-location-head" style="float:right;width:69%;margin-top: 10px;border-bottom: 1px solid #ddd;padding:0px;">';
            echo		'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo			'<h3 style="font-size:13px;">'. __('Type an address to autocomplete','GMW').'</h3>';
            echo		'</div>';
            echo		'<div style="padding: 4px 0px 5px 0px;">';
            echo			'<input type="text" id="wppl-addresspicker" style="width: 97%;margin-left: 5px;" value="', $post_info->address , '" />';
            echo		'</div>';
            echo	'</div>';

            echo 	'<div class="clear"></div>';

            echo	'<div class="gmw-admin-location-head" style="padding: 0px;margin-top: 10px;float:left;width:100%;border-bottom:1px solid #ddd;">';
            echo		'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo			'<h3 style="font-size:13px;">'. __('Enter Location Manually','GMW').'</h3>';
            echo		'</div>';
            echo		'<div id="gmw-location-wrapper" style="float:left;padding:5px;">';

            echo 			'<div style="float:left;width:49%">';
            echo				'<div class="gmw-admin-location-head" style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo					'<h3>'. __('Address','GMW') . '</h3>';
            echo					'</div>';
            echo				'<div class="gmw-admin-location-head">';
            echo					'<p>'. __('Fill out the address fields and click "Get Lat/Long" to retrive the latitude and longitude of the location.','GMW') . '</p>';
            echo				'</div>';
            echo 				'<table class="gmw-admin-location-table">';
            echo					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][0]['id'], '">', $this->meta_boxes['fields'][0]['name'], '</label></th>';
            echo						'<td><input type="text" name="',$this->meta_boxes['fields'][0]['id'], '" id="', $this->meta_boxes['fields'][0]['id'], '" value="', $post_info->street, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][1]['id'], '">', $this->meta_boxes['fields'][1]['name'], '</label></th>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][1]['id'], '" id="', $this->meta_boxes['fields'][1]['id'], '" value="', $post_info->apt, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][2]['id'], '">', $this->meta_boxes['fields'][2]['name'], '</label></th>';
            echo						'<td><input type="text" name="',$this->meta_boxes['fields'][2]['id'], '" id="', $this->meta_boxes['fields'][2]['id'], '" value="', $post_info->city, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][3]['id'], '">', $this->meta_boxes['fields'][3]['name'], '</label></th>';
            echo 						'<td><input type="text" id="', $this->meta_boxes['fields'][3]['id'],'" class="', $this->meta_boxes['fields'][3]['id'], '" value="', $post_info->state,'" style="width: 97%;"/>', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][4]['id'], '">', $this->meta_boxes['fields'][4]['name'], '</label></th>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][4]['id'], '" id="', $this->meta_boxes['fields'][4]['id'], '" value="', $post_info->zipcode, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][5]['id'], '">', $this->meta_boxes['fields'][5]['name'], '</label></th>';
            echo 						'<td><input type="text" id="', $this->meta_boxes['fields'][5]['id'],'" class="', $this->meta_boxes['fields'][5]['id'], '" value="', $post_info->country,'" style="width: 97%;"/>', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo						'<th></th>';
            echo 						'<td><input type="button"id="gmw-admin-getlatlong-btn" class="button-primary" value="Get Lat/Long" style="margin: 10px 0px;"></td>';
            echo 					'</tr>';
            echo 				'</table>';
            echo				'<table class="gmw-admin-location-table" style="display:none;">';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][3]['id'], '">', $this->meta_boxes['fields'][3]['name'], '</label></th>';
            echo					'</tr>';
            echo					'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][3]['id'], '" class="', $this->meta_boxes['fields'][3]['id'],'" value="', $post_info->city, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][16]['id'], '">', $this->meta_boxes['fields'][16]['name'], '</label></th>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][16]['id'], '" id="', $this->meta_boxes['fields'][16]['id'], '" class="', $this->meta_boxes['fields'][16]['id'], '" value="', $post_info->state_long,'" style="width: 100%;"/>', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][5]['id'], '">', $this->meta_boxes['fields'][5]['name'], '</label></th>';
            echo					'</tr>';
            echo 					'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][5]['id'], '"  class="', $this->meta_boxes['fields'][5]['id'],'" value="', $post_info->zipcode, '"  style="width:97%" />', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][17]['id'], '">', $this->meta_boxes['fields'][17]['name'], '</label></th>';
            echo					'</tr>';
            echo					'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][17]['id'], '" id="', $this->meta_boxes['fields'][17]['id'], '" class="', $this->meta_boxes['fields'][17]['id'], '" value="', $post_info->country_long,'" style="width: 100%;" />', '<br /></td>';
            echo 					'</tr>';
            echo	 				'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][14]['id'], '">', $this->meta_boxes['fields'][14]['name'], '</label></th>';
            echo 					'</tr>';
            echo	 				'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][14]['id'], '" id="', $this->meta_boxes['fields'][14]['id'], '" class="', $this->meta_boxes['fields'][14]['id'], '" value="', $post_info->address,'" style="width: 100%;"/>', '<br /></td>';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][18]['id'], '">', $this->meta_boxes['fields'][18]['name'], '</label></th>';
            echo					'</tr>';
            echo					'<tr>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][18]['id'], '" id="', $this->meta_boxes['fields'][18]['id'], '" class="', $this->meta_boxes['fields'][18]['id'], '" value="', $post_info->formatted_address,'" style="width: 100%;" />', '<br /></td>';
            echo 					'</tr>';
            echo 				'</table>';
            echo			'</div>';

            echo 			'<div style="float:right;width:49%">';
            echo				'<div class="gmw-admin-location-head" style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo					'<h3>'. __('Latitude / Longitude','GMW') . '</h3>';
            echo				'</div>';
            echo				'<div class="gmw-admin-location-head">';
            echo					'<p>'. __('Fill out the Latitude and Longitude fields and click on "Get Address" to retrive the address of the location.','GMW') . '</p>';
            echo				'</div>';
            echo 				'<table class="gmw-admin-location-table">';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][12]['id'], '">', $this->meta_boxes['fields'][12]['name'], '</label></th>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][12]['id'], '" id="', $this->meta_boxes['fields'][12]['id'], '" class="', $this->meta_boxes['fields'][12]['id'], '" value="', $post_info->lat, '"  />', '<br /></td>';
            echo						'<input type="hidden" name="gmw_check_lat" id="gmw_check_lat" value"">';
            echo 					'</tr>';
            echo 					'<tr>';
            echo 						'<th><label for="', $this->meta_boxes['fields'][13]['id'], '">', $this->meta_boxes['fields'][13]['name'], '</label></th>';
            echo 						'<td><input type="text" name="',$this->meta_boxes['fields'][13]['id'], '" id="', $this->meta_boxes['fields'][13]['id'], '" class="', $this->meta_boxes['fields'][13]['id'], '" value="', $post_info->long, '"  />', '<br /></td>';
            echo						'<input type="hidden" name="gmw_check_long" id="gmw_check_long" value"">';
            echo 					'</tr>';
            echo 					'<tr>';
            echo						'<th></th>';
            echo						'<td><input style="margin: 10px 0px;" type="button" id="gmw-admin-getaddress-btn" class="button-primary" value="Get Address" /></td>';
            echo 					'</tr>';
            echo 				'</table>';

            echo				'<div class="gmw-admin-location-head" style="float:left;width:100%;text-align:center;margin-top: 10px;padding: 0px;border-bottom: 1px solid #ddd;">';
            echo					'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo						'<h3 style="font-size:13px;">'. __('Delete Location','GMW').'</h3>';
            echo					'</div>';
            echo					'<div style="padding:5px 0px;">';
            echo						'<input type="button" style="float:none;" id="gmw-admin-delete-btn" class="button-primary" value="'; _e('Delete address','GMW'); echo '" />';
            echo					'</div>';
            echo				'</div>';

            echo				'<div id="gmw-getting-info" class="gmw-admin-location-head" style="float:left;width:100%;text-align:center;margin-top: 10px;padding: 0px;border-bottom: 1px solid #ddd;">';
            echo					'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo						'<h3 style="font-size:13px;">'. __('Location status','GMW').'</h3>';
            echo					'</div>';
            echo					'<div style="height: 20px;padding:8px 0px;position:relative;">';
            echo						'<div id="gmw-location-loader" style="display:none;background:none; border:0px;height: 23px;"><img style="width:15px;margin-right: 5px"src="'. GMW_IMAGES .'/gmw-loader.gif" id="ajax-loader-image" alt="" ">' . __('Loading...','GMW') . '</div>';
            echo						'<div id="gmw-good-location-message" class="" style="display:none;height: 23px;"><p>Location is ready</p></div>';
            echo						'<div id="gmw-bad-location-message" class="gmw-location-message" style="height: 23px;"><p style="color:red">A valid address, latitude and longitude are required to save the Location</p></div>';
            echo					'</div>';
            echo				'</div>';

            echo 				'<div class="clear"></div>';

            echo			'</div>';
            echo		'</div>';
            echo 	'</div>';

            echo	'<div class="gmw-admin-location-head" style="padding: 0px;margin-top: 10px;float:left;width:100%;border-bottom:1px solid #ddd;">';
            echo		'<div style="background:#f9f9f9;padding: 4px 0px 1px 0px;">';
            echo			'<h3 style="font-size:13px;">'. __('Additional Information','GMW').'</h3>';
            echo		'</div>';
            echo		'<div style="padding:5px;">';
		
	    echo			'<div class="metabox-tabs-div">';
	    echo				'<ul class="metabox-tabs" id="metabox-tabs">';
	    echo					'<li class="active extra-info-tab"><a class="active" href="javascript:void(null);">'; _e('Contact Information','GMW'); echo '</a></li>';
	    echo					'<li class="days-hours-tab"><a href="javascript:void(null);">'; _e('Days & Hours','GMW'); echo '</a></li>';
	    echo				'</ul>';
	    
            echo 				'<div class="extra-info-tab">';
            echo 					'<h4 class="heading">'; _e('Additional Information','GMW'); echo '</h4>';
	    echo 					'<table class="form-table">';    
	    echo 						'<tr>';
            echo 							'<th><label for="', $this->meta_boxes['fields'][6]['id'], '">', $this->meta_boxes['fields'][6]['name'], '</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][6]['id'], '" id="', $this->meta_boxes['fields'][6]['id'], '" value="', $post_info->phone, '"  style="width:97%;" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th><label for="', $this->meta_boxes['fields'][7]['id'], '">', $this->meta_boxes['fields'][7]['name'], '</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][7]['id'], '" id="', $this->meta_boxes['fields'][7]['id'], '" value="', $post_info->fax, '"  style="width:97%;" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th><label for="', $this->meta_boxes['fields'][8]['id'], '">', $this->meta_boxes['fields'][8]['name'], '</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][8]['id'], '" id="', $this->meta_boxes['fields'][8]['id'], '" value="', $post_info->email, '"  style="width:97%;" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th><label for="', $this->meta_boxes['fields'][9]['id'], '">', $this->meta_boxes['fields'][9]['name'], '</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][9]['id'], '" id="', $this->meta_boxes['fields'][9]['id'], '" value="', $post_info->website, '"  style="width:97%;" />', '<br /></td>';
            echo 						'</tr>';	
            echo 					'</table>';
            echo 				'</div>';
	 		
	 	$days_hours = get_post_meta( $post->ID, $this->meta_boxes['fields'][15]['id'], true );
	 	$days_hours = ( isset( $days_hours ) && is_array( $days_hours ) && array_filter( $days_hours ) )  ? get_post_meta( $post->ID, $this->meta_boxes['fields'][15]['id'], true ) : false;
	 	
            echo 				'<div class="days-hours-tab">';
            echo 					'<h4 class="heading">'; _e( 'Days & Hours', 'GMW' ); echo '</h4>';
	    echo 					'<table class="form-table">';    
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[0][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[0]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[0][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[0]['hours'], '" style="width:150px" />', '<br /></td>';  
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[1][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[1]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[1][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[1]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[2][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[2]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[2][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[2]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[3][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[3]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[3][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[3]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[4][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[4]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[4][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[4]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[5][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[5]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[5][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[5]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 						'<tr>';
            echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Days</label></th>';
	    echo 							'<td style="width:150px"><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[6][days]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[6]['days'], '" style="width:150px" />', '<br /></td>';
	    echo 							'<th style="width:30px"><label for="', $this->meta_boxes['fields'][15]['id'], '">Hours</label></th>';
	    echo 							'<td><input type="text" name="',$this->meta_boxes['fields'][15]['id'], '[6][hours]" id="', $this->meta_boxes['fields'][15]['id'], '" value="', $days_hours[6]['hours'], '" style="width:150px" />', '<br /></td>';
            echo 						'</tr>';
            echo 					'</table>';
            echo 				'</div>';
            echo 			'</div>';
            echo		'</div>';
            echo	'</div>';
	 	
            echo 	'<div class="clear"></div>';

            wp_enqueue_style( 'gmw-pt-admin-style' );
            wp_enqueue_script( 'google-maps' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            wp_enqueue_script( 'gmw-admin-address-picker' );
	 	
	}
	
	/* EVERY NEW POST OR WHEN POST IS BEING UPDATED 
     * CREATE MAP, LATITUDE, LONGITUDE AND SAVE DATA INTO OUR LOCATIONS TABLE 
     * DATA SAVED - POST ID, POST TYPE, POST STATUS , POST TITLE , LATITUDE, LONGITUDE AND ADDRESS
     */

    function save_data( $post_id ) {
        global $wpdb, $post;

        // Return if it's a post revision
        if ( false !== wp_is_post_revision( $post_id ) )
            return;

        if ( !isset( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) || empty( $this->settings[ 'post_types_settings' ][ 'post_types' ] ) || !isset( $_POST[ 'post_type' ] ) || !in_array( $_POST[ 'post_type' ], $this->settings[ 'post_types_settings' ][ 'post_types' ] ) )
            return;

        // verify nonce //
        if ( !isset( $_POST[ 'this->meta_boxes_nonce' ] ) || !wp_verify_nonce( $_POST[ 'this->meta_boxes_nonce' ], basename( __FILE__ ) ) ) {
            return;
        }

        // check autosave //
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( !current_user_can( 'edit_post', $post_id ) )
            return;

        // Check permissions //
        /* if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $wppl_options['address_fields'] ) ) {
          if ( !current_user_can('edit_page', $post->ID) ) {
          return;
          }
          } else {
          if ( !current_user_can('edit_post', $post->ID) ) {
          return;
          }
          } */

        foreach ( $this->meta_boxes[ 'fields' ] as $field ) :

            if ( $field[ 'id' ] == '_wppl_days_hours' ) {

                if ( isset( $_POST[ $field[ 'id' ] ] ) ) :

                    $old = get_post_meta( $post->ID, $field[ 'id' ], true );
                    $new = $_POST[ $field[ 'id' ] ];

                    if ( $new && $new != $old ) {
                        update_post_meta( $post->ID, $field[ 'id' ], $new );
                    } elseif ( '' == $new && $old ) {
                        delete_post_meta( $post->ID, $field[ 'id' ], $old );
                    }

                endif;
            }

        endforeach;

        //do_action( 'gmw_pt_admin_update_location_post_meta', $post->ID, $_POST, $wppl_options );
        //delete locaiton if there are no address or lat/long
        if ( !isset( $_POST[ '_wppl_formatted_address' ] ) || empty( $_POST[ '_wppl_formatted_address' ] ) || !isset( $_POST[ '_wppl_lat' ] ) || empty( $_POST[ '_wppl_lat' ] ) ) {

            $wpdb->query(
                    $wpdb->prepare(
                            "DELETE FROM " . $wpdb->prefix . "places_locator WHERE post_id=%d", $post->ID
                    )
            );
        } else {

            //Save information to database
            global $wpdb;

            $_POST[ 'gmw_map_icon' ] = ( isset( $_POST[ 'gmw_map_icon' ] ) && !empty( $_POST[ 'gmw_map_icon' ] ) ) ? $_POST[ 'gmw_map_icon' ] : '_default.png';

            $_POST = apply_filters( 'gmw_pt_before_location_updated', $_POST, $post->ID );

            $wpdb->replace( $wpdb->prefix . 'places_locator', array(
                'post_id'           => $post->ID,
                'feature'           => 0,
                'post_type'         => $_POST[ 'post_type' ],
                'post_title'        => $_POST[ 'post_title' ],
                'post_status'       => $_POST[ 'post_status' ],
                'street'            => $_POST[ '_wppl_street' ],
                'apt'               => $_POST[ '_wppl_apt' ],
                'city'              => $_POST[ '_wppl_city' ],
                'state'             => $_POST[ '_wppl_state' ],
                'state_long'        => $_POST[ '_wppl_state_long' ],
                'zipcode'           => $_POST[ '_wppl_zipcode' ],
                'country'           => $_POST[ '_wppl_country' ],
                'country_long'      => $_POST[ '_wppl_country_long' ],
                'address'           => $_POST[ '_wppl_address' ],
                'formatted_address' => $_POST[ '_wppl_formatted_address' ],
                'phone'             => $_POST[ '_wppl_phone' ],
                'fax'               => $_POST[ '_wppl_fax' ],
                'email'             => $_POST[ '_wppl_email' ],
                'website'           => $_POST[ '_wppl_website' ],
                'lat'               => $_POST[ '_wppl_lat' ],
                'long'              => $_POST[ '_wppl_long' ],
                'map_icon'          => $_POST[ 'gmw_map_icon' ],
                    )
            );
        }
        do_action( 'gmw_pt_after_location_updated', $post->ID, $_POST );

    }

}

new GMW_PT_Meta_Boxes;
?>