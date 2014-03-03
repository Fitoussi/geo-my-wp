<?php
$wppl_options = get_option('wppl_fields');
if ( !isset( $wppl_options['address_fields'] ) || empty( $wppl_options['address_fields'] ) ) $wppl_options['address_fields'] = false;

$prefix = '_wppl_';
$wppl_meta_box = array(
    'id' => 'wppl-meta-box',
    'title' => __('GMW Location','GMW'),
    'pages' => $wppl_options['address_fields'],
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => __('Street','GMW'),
            'desc' => '',
            'id' => $prefix . 'street',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => __('Apt/Suit','GMW'),
            'desc' => '',
            'id' => $prefix . 'apt',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => __('City','GMW'),
            'desc' => '',
            'id' => $prefix . 'city',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => __('State','GMW'),
            'desc' => '',
            'id' => $prefix . 'state',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => __('Zipcode','GMW'),
            'desc' => '',
            'id' => $prefix . 'zipcode',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => __('Country','GMW'),
            'desc' => '',
            'id' => $prefix . 'country',
            'type' => 'text',
            'std' => ''
        ),
        
        array(
            'name' => __('Phone Number','GMW'),
            'desc' => '',
            'id' => $prefix . 'phone',
            'type' => 'text',
            'std' => ''
        ),
        
        array(
            'name' => __('fax Number','GMW'),
            'desc' => '',
            'id' => $prefix . 'fax',
            'type' => 'text',
            'std' => ''
        ),
        
        array(
            'name' => __('Email Address','GMW'),
            'desc' => '',
            'id' => $prefix . 'email',
            'type' => 'text',
            'std' => ''
        ),
        
        array(
            'name' => __('Website','GMW'),
            'desc' => 'Ex: www.website.com',
            'id' => $prefix . 'website',
            'type' => 'text',
            'std' => ''
        ),
        
       
        array(
            'name' => __('Latitude','GMW'),
            'desc' => '',
            'id' => $prefix . 'enter_lat',
            'type' => 'text-right',
            'std' => ''
        ),
        
         array(
            'name' => __('Longitude','GMW'),
            'desc' => '',
            'id' => $prefix . 'enter_long',
            'type' => 'text-right',
            'std' => ''
        ),
         array(
            'name' => __('Latitude','GMW'),
            'desc' => '',
            'id' => $prefix . 'lat',
            'type' => 'text-disable',
            'std' => ''
        ),
         array(
            'name' => __('Longitude','GMW'),
            'desc' => '',
            'id' => $prefix . 'long',
            'type' => 'text-disable',
            'std' => ''
        ),
        array(
            'name' => __('Full Address','GMW'),
            'desc' => '',
            'id' => $prefix . 'address',
            'type' => 'text-disable',
            'std' => ''
        ),
         array(
            'name' => __('Days & Hours','GMW'),
            'desc' => '',
            'id' => $prefix . 'days_hours',
            'type' => 'text',
            'std' => ''
        ),
    	array(
            'name' => __('State Long','GMW'),
            'desc' => '',
            'id' => $prefix . 'state_long',
            'type' => 'text',
            'std' => ''
        ),
    	array(
            'name' => __('Country Long','GMW'),
            'desc' => '',
            'id' => $prefix . 'country_long',
            'type' => 'text',
            'std' => ''
        ),
    	array(
            'name' => __('Formatted address','GMW'),
            'desc' => '',
            'id' => $prefix . 'formatted_address',
            'type' => 'text',
            'std' => ''
        )
    )
);

// Add meta box //
function wppl_add_box() {
    global $wppl_meta_box;
    $wppl_options = get_option('wppl_fields');
    	if ( isset($wppl_options['address_fields']) && !empty( $wppl_options['address_fields'] ) ) {
 		foreach ($wppl_meta_box['pages'] as $page) {
        	add_meta_box($wppl_meta_box['id'], $wppl_meta_box['title'], 'wppl_show_box', $page, $wppl_meta_box['context'], $wppl_meta_box['priority']);
   		}
    }
}
add_action('admin_menu', 'wppl_add_box');

// Callback function to show fields in meta box //
function wppl_show_box() {
    global $wppl_meta_box, $post;
    $wppl_options = get_option('wppl_fields');
    
    if ( isset($wppl_options['mandatory_address']) ) wp_localize_script('wppl-address-picker','addressMandatory',$wppl_options['mandatory_address']);
    
    echo 	'<input type="hidden" name="wppl_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

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
	echo			'<input type="text" id="wppl-addresspicker" style="width: 97%;margin-left: 5px;" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][14]['id'],true) , '" />';
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
	echo 						'<th><label for="', $wppl_meta_box['fields'][0]['id'], '">', $wppl_meta_box['fields'][0]['name'], '</label></th>';
	echo						'<td><input type="text" name="',$wppl_meta_box['fields'][0]['id'], '" id="', $wppl_meta_box['fields'][0]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][0]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][1]['id'], '">', $wppl_meta_box['fields'][1]['name'], '</label></th>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][1]['id'], '" id="', $wppl_meta_box['fields'][1]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][1]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][2]['id'], '">', $wppl_meta_box['fields'][2]['name'], '</label></th>';
	echo						'<td><input type="text" name="',$wppl_meta_box['fields'][2]['id'], '" id="', $wppl_meta_box['fields'][2]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][2]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][3]['id'], '">', $wppl_meta_box['fields'][3]['name'], '</label></th>';
	echo 						'<td><input type="text" id="', $wppl_meta_box['fields'][3]['id'],'" class="', $wppl_meta_box['fields'][3]['id'], '" value="', get_post_meta($post->ID,'_wppl_state',true),'" style="width: 97%;"/>', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][4]['id'], '">', $wppl_meta_box['fields'][4]['name'], '</label></th>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][4]['id'], '" id="', $wppl_meta_box['fields'][4]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][4]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][5]['id'], '">', $wppl_meta_box['fields'][5]['name'], '</label></th>';
	echo 						'<td><input type="text" id="', $wppl_meta_box['fields'][5]['id'],'" class="', $wppl_meta_box['fields'][5]['id'], '" value="', get_post_meta($post->ID,'_wppl_country',true),'" style="width: 97%;"/>', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo						'<th></th>';
	echo 						'<td><input type="button"id="gmw-admin-getlatlong-btn" class="button-primary" value="Get Lat/Long" style="margin: 10px 0px;"></td>';
	echo 					'</tr>';
	echo 				'</table>';
	echo				'<table class="gmw-admin-location-table" style="display:none;">';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][3]['id'], '">', $wppl_meta_box['fields'][3]['name'], '</label></th>';
	echo					'</tr>';
	echo					'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][3]['id'], '" class="', $wppl_meta_box['fields'][3]['id'],'" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][3]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][16]['id'], '">', $wppl_meta_box['fields'][16]['name'], '</label></th>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][16]['id'], '" id="', $wppl_meta_box['fields'][16]['id'], '" class="', $wppl_meta_box['fields'][16]['id'], '" value="', get_post_meta($post->ID,'_wppl_state_long',true),'" style="width: 100%;"/>', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][5]['id'], '">', $wppl_meta_box['fields'][5]['name'], '</label></th>';
	echo					'</tr>';
	echo 					'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][5]['id'], '"  class="', $wppl_meta_box['fields'][5]['id'],'" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][5]['id'],true), '"  style="width:97%" />', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][17]['id'], '">', $wppl_meta_box['fields'][17]['name'], '</label></th>';
	echo					'</tr>';
	echo					'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][17]['id'], '" id="', $wppl_meta_box['fields'][17]['id'], '" class="', $wppl_meta_box['fields'][17]['id'], '" value="', get_post_meta($post->ID,'_wppl_country_long',true),'" style="width: 100%;" />', '<br /></td>';
	echo 					'</tr>';
	echo	 				'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][14]['id'], '">', $wppl_meta_box['fields'][14]['name'], '</label></th>';
	echo 					'</tr>';
	echo	 				'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][14]['id'], '" id="', $wppl_meta_box['fields'][14]['id'], '" class="', $wppl_meta_box['fields'][14]['id'], '" value="', get_post_meta($post->ID,'_wppl_address',true),'" style="width: 100%;"/>', '<br /></td>';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][18]['id'], '">', $wppl_meta_box['fields'][18]['name'], '</label></th>';
	echo					'</tr>';
	echo					'<tr>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][18]['id'], '" id="', $wppl_meta_box['fields'][18]['id'], '" class="', $wppl_meta_box['fields'][18]['id'], '" value="', get_post_meta($post->ID,'_wppl_formatted_address',true),'" style="width: 100%;" />', '<br /></td>';
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
	echo 						'<th><label for="', $wppl_meta_box['fields'][12]['id'], '">', $wppl_meta_box['fields'][12]['name'], '</label></th>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][12]['id'], '" id="', $wppl_meta_box['fields'][12]['id'], '" class="', $wppl_meta_box['fields'][12]['id'], '" value="', get_post_meta($post->ID,'_wppl_lat',true), '"  />', '<br /></td>';
	echo						'<input type="hidden" name="gmw_check_lat" id="gmw_check_lat" value"">';
	echo 					'</tr>';
	echo 					'<tr>';
	echo 						'<th><label for="', $wppl_meta_box['fields'][13]['id'], '">', $wppl_meta_box['fields'][13]['name'], '</label></th>';
	echo 						'<td><input type="text" name="',$wppl_meta_box['fields'][13]['id'], '" id="', $wppl_meta_box['fields'][13]['id'], '" class="', $wppl_meta_box['fields'][13]['id'], '" value="', get_post_meta($post->ID,'_wppl_long',true), '"  />', '<br /></td>';
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
	echo						'<div id="gmw-location-loader" style="display:none;background:none; border:0px;height: 23px;"><img style="width:15px;margin-right: 5px"src="'. GMW_URL .'/images/gmw-loader.gif" id="ajax-loader-image" alt="" ">' . __('Loading...','GMW') . '</div>';
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
 	echo 							'<th><label for="', $wppl_meta_box['fields'][6]['id'], '">', $wppl_meta_box['fields'][6]['name'], '</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][6]['id'], '" id="', $wppl_meta_box['fields'][6]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][6]['id'],true), '"  style="width:97%;" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th><label for="', $wppl_meta_box['fields'][7]['id'], '">', $wppl_meta_box['fields'][7]['name'], '</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][7]['id'], '" id="', $wppl_meta_box['fields'][7]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][7]['id'],true), '"  style="width:97%;" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th><label for="', $wppl_meta_box['fields'][8]['id'], '">', $wppl_meta_box['fields'][8]['name'], '</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][8]['id'], '" id="', $wppl_meta_box['fields'][8]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][8]['id'],true), '"  style="width:97%;" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th><label for="', $wppl_meta_box['fields'][9]['id'], '">', $wppl_meta_box['fields'][9]['name'], '</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][9]['id'], '" id="', $wppl_meta_box['fields'][9]['id'], '" value="', get_post_meta($post->ID,$wppl_meta_box['fields'][9]['id'],true), '"  style="width:97%;" />', '<br /></td>';
 	echo 						'</tr>';	
 	echo 					'</table>';
 	echo 				'</div>';
 		
 	$days_hours = get_post_meta($post->ID,$wppl_meta_box['fields'][15]['id'],true);
 	$days_hours = ( isset($days_hours) && is_array($days_hours) && array_filter($days_hours) )  ? get_post_meta($post->ID,$wppl_meta_box['fields'][15]['id'],true) : false;
 	
 	echo 				'<div class="days-hours-tab">';
 	echo 					'<h4 class="heading">'; _e('Days & Hours','GMW'); echo '</h4>';
    echo 					'<table class="form-table">';    
  	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[0][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[0]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[0][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[0]['hours'], '" style="width:150px" />', '<br /></td>';  
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[1][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[1]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[1][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[1]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[2][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[2]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[2][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[2]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[3][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[3]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[3][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[3]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[4][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[4]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[4][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[4]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[5][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[5]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[5][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[5]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 						'<tr>';
 	echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Days</label></th>';
    echo 							'<td style="width:150px"><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[6][days]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[6]['days'], '" style="width:150px" />', '<br /></td>';
    echo 							'<th style="width:30px"><label for="', $wppl_meta_box['fields'][15]['id'], '">Hours</label></th>';
    echo 							'<td><input type="text" name="',$wppl_meta_box['fields'][15]['id'], '[6][hours]" id="', $wppl_meta_box['fields'][15]['id'], '" value="', $days_hours[6]['hours'], '" style="width:150px" />', '<br /></td>';
 	echo 						'</tr>';
 	echo 					'</table>';
 	echo 				'</div>';
 	echo 			'</div>';
 	echo		'</div>';
 	echo	'</div>';
 	
 	echo 	'<div class="clear"></div>';
 	
}

/* EVERY NEW POST OR WHEN POST IS BEING UPDATED 
 * CREATE MAP, LATITUDE, LONGITUDE AND SAVE DATA INTO OUR LOCATIONS TABLE 
 * DATA SAVED - POST ID, POST TYPE, POST STATUS , POST TITLE , LATITUDE, LONGITUDE AND ADDRESS
 */
function wppl_save_data($post_id) {
    global $wppl_meta_box, $wpdb, $wppl_feature_meta_box, $post;
    $wppl_options = get_option('wppl_fields');
    $wppl_on = get_option('wppl_plugins');
    
    // Return if it's a post revision
    if ( false !== wp_is_post_revision( $post_id ) )
    	return;
    
    if ( !isset( $wppl_options['address_fields'] ) || empty( $wppl_options['address_fields'] ) || !isset($_POST['post_type']) || !in_array( $_POST['post_type'], $wppl_options['address_fields'] ) ) 
    	return;

    // verify nonce //
    if ( !isset(  $_POST['wppl_meta_box_nonce']) || !wp_verify_nonce( $_POST['wppl_meta_box_nonce'] , basename(__FILE__))) {
    	return;
    }
 
    // check autosave //
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if ( ! current_user_can( 'edit_post', $post_id ) )
    	return;
    
    // Check permissions //
    /*if ( isset( $_POST['post_type'] ) && in_array( $_POST['post_type'], $wppl_options['address_fields'] ) ) {
    	if ( !current_user_can('edit_page', $post->ID) ) {
    		return;
    	}
    } else {
    	if ( !current_user_can('edit_post', $post->ID) ) {
    		return;
    	}
    } */
 	
	if ( isset( $wppl_on['featured_posts'] ) ) {
    	update_post_meta($post->ID,'_wppl_featured_post' , $_POST['_wppl_featured_post']);
    }
    
    foreach ( $wppl_meta_box['fields'] as $field ) :
    	if ( isset( $_POST[$field['id']] ) ) :	
	        $old = get_post_meta($post->ID, $field['id'], true);
	        $new = $_POST[$field['id']];
	 
	        if ($new && $new != $old) {
	            update_post_meta($post->ID, $field['id'], $new);
	        } elseif ('' == $new && $old) {
	            delete_post_meta($post->ID, $field['id'], $old);
	        }
		endif;
		
    endforeach;
    do_action('gmw_pt_admin_update_location_post_meta',$post->ID, $_POST, $wppl_options );
    
    //delete locaiton if there are no address or lat/long
    if ( !isset($_POST['_wppl_formatted_address']) || empty($_POST['_wppl_formatted_address']) || !isset($_POST['_wppl_lat']) || empty($_POST['_wppl_lat']) ) {
    	$wpdb->query(
    		$wpdb->prepare(
    			"DELETE FROM " . $wpdb->prefix . "places_locator WHERE post_id=%d",$post->ID
    		)
    	);
    } else {
    	
	    //Save information to database
	    global $wpdb;
	    $wpdb->replace( $wpdb->prefix . 'places_locator',
	    		array(
	    				'post_id'			=> $post->ID,
	    				'feature'  			=> 0,
	    				'post_type' 		=> $_POST['post_type'],
	    				'post_title'		=> $_POST['post_title'],
		    			'post_status'		=> $_POST['post_status'],
	    				'street' 			=> $_POST['_wppl_street'],
	    				'apt' 				=> $_POST['_wppl_apt'],
	    				'city' 				=> $_POST['_wppl_city'],
	    				'state' 			=> $_POST['_wppl_state'],
	    				'state_long' 		=> $_POST['_wppl_state_long'],
	    				'zipcode' 			=> $_POST['_wppl_zipcode'],
	    				'country' 			=> $_POST['_wppl_country'],
	    				'country_long' 		=> $_POST['_wppl_country_long'],
	    				'address' 			=> $_POST['_wppl_address'],
	    				'formatted_address' => $_POST['_wppl_formatted_address'],
	    				'phone' 			=> $_POST['_wppl_phone'],
	    				'fax' 				=> $_POST['_wppl_fax'],
	    				'email' 			=> $_POST['_wppl_email'],
	    				'website' 			=> $_POST['_wppl_website'],
	    				'lat' 				=> $_POST['_wppl_lat'],
	    				'long' 				=> $_POST['_wppl_long'],
	    				'map_icon'  		=> '_default.png',
	    		)
	    );
    }
    //do_action('gmw_pt_after_location_updated', $gmwLocation );
}
add_action( 'save_post' , 'wppl_save_data');
?>