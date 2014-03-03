<?php

/**
 * GMW function - Geocode address
 * @version 1.0
 * @author Eyal Fitoussi
 */
function GmwConvertToCoords($org_address) {
	
	$gmw_options = get_option('wppl_fields');
 	$returned_address = array();
	$ch = curl_init();	
    $rip_it = array( " " => "+", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "");
    
    // MAKE SURE ADDRES DOENST HAVE ANY CHARACTERS THAT GOOGLE CANNOT READ 
    $address = str_replace(array_keys($rip_it), array_values($rip_it), $org_address);
    
    // GET THE XML FILE WITH RESUALTS
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/2.0 (compatible; MSIE 3.02; Update a; AK; Windows 95)");
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_URL, "http://maps.googleapis.com/maps/api/geocode/xml?address=". $address."&sensor=false"  );
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	$got_xml = curl_exec($ch);
  
    // PARSE THE XML FILE 
    $xml = false;
	$xml = new SimpleXMLElement($got_xml);

	if ( $xml->status == 'OVER_QUERY_LIMIT' ) :
		/*?>
		<script>console.log('google geocode gets OVER_QUERY_LIMIT');</script>
		<?php */
		/*
		$rip_it = false;
		$address = false;
		$ch = false;
		$returned_address = array();
		$ch = curl_init();
		//$rip_it = array( " " => ",", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "");
		
		//// MAKE SURE ADDRES DOENST HAVE ANY CHARACTERS THAT GOOGLE CANNOT READ //
		$address = str_replace(' ', ',', $org_address);
		
		echo $address;
		//// GET THE XML FILE WITH RESUALTS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/2.0 (compatible; MSIE 3.02; Update a; AK; Windows 95)");
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_URL, "http://www.mapquestapi.com/geocoding/v1/address?key=Fmjtd%7Cluub2g0zll%2Ca2%3Do5-9ubxqy&&outFormat=xml&location=".$address."&callback=renderGeocode"  );
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		$got_xml = curl_exec($ch);
		
		//// PARSE THE XML FILE //////////////
		$xml = false;
		
		$xml = new SimpleXMLElement($got_xml);
		
		if ( $xml->response->info->statusCode == 0 ) :
		
			$returned_address['lat']  = esc_attr( $xml->results->result->locations->location->displayLatLng->latLng->lat );
			$returned_address['long'] = esc_attr( $xml->results->result->locations->location->displayLatLng->latLng->lng );
			
			return $returned_address;
		
		else :
			
			$returned_address = false;
			return $returned_address;
		
		endif;
		*/
		
		/*
		 * if we get OVER_QUERY_LIMIT with google geocode and user has Bing maps key we will try to use Bing's services to geocode the address
		*/
		if ( isset($gmw_options['bing_key']) && !empty($gmw_options['bing_key'] ) ) :
		
			$rip_it = false;
			$address = false;
			$ch = false;
			$returned_address = array();
			$ch = curl_init();
			//$rip_it = array( " " => ",", "," => "", "?" => "", "&" => "", "=" => "" , "#" => "");
			
			// MAKE SURE ADDRES DOENST HAVE ANY CHARACTERS THAT GOOGLE CANNOT READ 
			$address = str_replace(' ', '%20', $org_address);
			
			//GET THE XML FILE WITH RESUALTS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/2.0 (compatible; MSIE 3.02; Update a; AK; Windows 95)");
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_URL, "http://dev.virtualearth.net/REST/v1/Locations?q=". $address ."&o=xml&key=". $gmw_options['bing_key']);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
			$got_xml = curl_exec($ch);
			
			//PARSE THE XML FILE 
			$xml = false;
			$xml = new SimpleXMLElement($got_xml);
			if ( $xml->ResourceSets->ResourceSet->EstimatedTotal > 0 ) :
				$returned_address['lat']  = esc_attr( $xml->ResourceSets->ResourceSet->Resources->Location->Point->Latitude );
				$returned_address['long'] = esc_attr( $xml->ResourceSets->ResourceSet->Resources->Location->Point->Longitude );
				return $returned_address;
			else :
				$returned_address = false;
				return $returned_address;
			endif;
		else :
			$returned_address = false;
		endif;				
	elseif ( $xml->status == 'ZERO_RESULTS' ) :
		$returned_address = false;
	elseif ( $xml->status == 'OK' ) :
		//GET THE LATITUDE/LONGITUDE FROM THE XML FILE 
		$returned_address['lat']  = esc_attr( $xml->result->geometry->location->lat );
		$returned_address['long'] = esc_attr( $xml->result->geometry->location->lng );
		
		$returned_address['formatted_address'] = esc_attr($xml->result->formatted_address);
		$address_array = $xml->result->address_component;
		
		if ( isset($address_array) && !empty($address_array) ) :
			$returned_address['street'] = false;
			$returned_address['apt'] = false;
			$returned_address['city'] = false;
			$returned_address['state_short'] = false;
			$returned_address['state_long'] = false;
			$returned_address['zipcode'] = false;
			$returned_address['country_short'] = false;
			$returned_address['country_long'] = false;
			
			foreach ($address_array as $ac) :
				if ( $ac->type == 'street_number' ) :
					$street_number = esc_attr($ac->long_name); 
				endif;
				
				if ($ac->type == 'route') :
					$street_f = esc_attr($ac->long_name); 
					if ( isset( $street_number )  && !empty( $street_number ) )	
						$returned_address['street'] = $street_number . ' ' . $street_f;
					else
						$returned_address['street'] = $street_f;
				endif;
				
				if ($ac->type == 'subpremise') 
					$returned_address['apt'] = esc_attr($ac->long_name); 
				
				if ($ac->type == 'locality') 
					$returned_address['city'] = esc_attr($ac->long_name); 
	
				if ($ac->type == 'administrative_area_level_1') :
					$returned_address['state_short'] = esc_attr($ac->short_name); 
					$returned_address['state_long'] = esc_attr($ac->long_name);
				endif;
				
				if ($ac->type == 'postal_code') 
					$returned_address['zipcode'] = esc_attr($ac->long_name); 
				
				if ($ac->type == 'country') :
					$returned_address['country_short'] = esc_attr($ac->short_name); 
					$returned_address['country_long'] = esc_attr($ac->long_name);
				endif;	
			endforeach;
		endif;
		return $returned_address;
	endif;
}

/**
 * GMW pt function - add location to GEO my WP custom fields and database
 * @param $postID
 * @param $post_type
 * @param $post_title
 * @param $post_status
 * @param $street
 * @param $apt
 * @param $city
 * @param $state
 * @param $zipcode
 * @param $country
 * @param $fullAddress
 * @param $apt
 * @param $phone
 * @param $fax
 * @param $email
 * @param $website
 * @param $mapIcon
 */
function gmw_pt_update_location( $args ) {
	
	$defaults = array(
    				'postID' 	  		=> false,
    				'post_type'			=> 'post',
    				'post_title'		=> false,
    				'post_status'		=> 'published',
    				'street'			=> false,
    				'apt'				=> false,
    				'city'				=> false,
    				'state'				=> false,
    				'zipcode'			=> false,
    				'country'			=> false,
    				'full_address'		=> false,
    				'phone' 			=> false,
    				'fax' 				=> false,
    				'email' 			=> false,
    				'website' 			=> false,
    				'map_icon'  		=> false,
					'auto_components'	=> false,
	    			);
	
	$r = wp_parse_args( $args, $defaults );
	extract($r);
	
	if ( !isset($postID) || $postID == false ) return;
	
	if ( !isset($map_icon) || $map_icon == false ) $map_icon = '_default.png';
	
	if ( isset($full_address) && $full_address != false ) :
		$address = $full_address;
		$address_apt = $full_address;
	else :
		$addressArray = array();
		if ( isset($street) && !empty($street) ) $addressArray[] = $street;
		if ( isset($apt) && !empty($apt) ) $addressArray[] = $apt;
		if ( isset($city) && !empty($city) ) $addressArray[] = $city;
		if ( isset($state) && !empty($state) ) $addressArray[] = $state;
		if ( isset($zipcode) && !empty($zipcode) ) $addressArray[] = $zipcode;
		if ( isset($country) && !empty($country) ) $addressArray[] = $country;
		$address_apt = implode(' ', $addressArray);
		unset($addressArray['apt']);
		$address = implode(' ', $addressArray);
	endif;
	
	$returned_address = GmwConvertToCoords( $address );
	
	if ( isset($full_address) && $full_address != false ) {
		$street  = $returned_address['street'];
		$apt 	 = $returned_address['apt'];
		$city 	 = $returned_address['city'];
		$state 	 = $returned_address['state_short'];
		$zipcode = $returned_address['zipcode'];
		$country = $returned_address['country_short'];
	} else {
		if ( ( !isset($street) || $street == false ) && $auto_components == true ) $street = $returned_address['street'];
		if ( ( !isset($apt) || $apt == false ) && $auto_components == true ) $apt = $returned_address['apt'];
		if ( ( !isset($city) || $city == false ) && $auto_components == true ) $city = $returned_address['city'];
		$state = ( ( !isset($state) || $state == false ) && $auto_components == false )  ? false : $returned_address['state_short'];
		if ( ( !isset($zipcode) || $zipcode == false ) && $auto_components == true ) $zipcode = $returned_address['zipcode'];
		$country = ( ( !isset($country) || $country == false ) && $auto_components == false )  ? false :  $returned_address['country_short'];
	}
	
	$gmwLocation = array(
			'post_id' 	  		=> $postID,
			'post_type'			=> $post_type,
			'post_title'		=> $post_title,
			'post_status'		=> $post_status,
			'address' 	  		=> $address,
			'address_apt' 		=> $address_apt,
			'returned_address'  => $returned_address,
			'street'			=> $street,
			'apt'				=> $apt,
			'city'				=> $city,
			'state_short'		=> $state,
			'state_long'		=> $returned_address['state_long'],
			'zipcode'			=> $zipcode,
			'country_short'		=> $country,
			'country_long'		=> $returned_address['country_long'],
			'formatted_address' => $returned_address['formatted_address'],
			'phone' 			=> $phone,
			'fax' 				=> $fax,
			'email' 			=> $email,
			'website' 			=> $website,
			'lat' 				=> $returned_address['lat'],
			'long' 				=> $returned_address['long'],
			'map_icon'  		=> $map_icon
	); 
	$gmwLocation = apply_filters('gmw_pt_before_location_updated', $gmwLocation );
	 
	do_action('gmw_pt_before_location_updated', $gmwLocation ); 
	
	//Save the custom fields of the address
	update_post_meta($postID, '_wppl_street', $gmwLocation['street']);
	update_post_meta($postID, '_wppl_apt', $gmwLocation['apt']);
	update_post_meta($postID, '_wppl_city', $gmwLocation['city']);
	update_post_meta($postID, '_wppl_state', $gmwLocation['state_short']);
	update_post_meta($postID, '_wppl_state_long', $gmwLocation['state_long']);
	update_post_meta($postID, '_wppl_zipcode', $gmwLocation['zipcode'] );
	update_post_meta($postID, '_wppl_country', $gmwLocation['country_short']);
	update_post_meta($postID, '_wppl_country_long', $gmwLocation['country_long']);
	update_post_meta($postID, '_wppl_address', $gmwLocation['address_apt']);
	update_post_meta($postID, '_wppl_formatted_address', $gmwLocation['formatted_address']);
	update_post_meta($postID, '_wppl_lat', $returned_address['lat']);
	update_post_meta($postID, '_wppl_long', $returned_address['long']);
	update_post_meta($postID, '_wppl_map_icon' , $gmwLocation['map_icon']);
	
	//Save additional information in custom fields
	update_post_meta($postID, '_wppl_phone', $gmwLocation['phone']);
	update_post_meta($postID, '_wppl_fax', $gmwLocation['fax']);
	update_post_meta($postID, '_wppl_email', $gmwLocation['email']);
	update_post_meta($postID, '_wppl_website', $gmwLocation['website']);
	 
	//$featuredPost = ( isset( $_POST['_wppl_featured_post'] ) ) ? $_POST['_wppl_featured_post'] : 0;
	 
	//Save information to database
	global $wpdb;
	$wpdb->replace( $wpdb->prefix . 'places_locator',
		array(
			'post_id'			=> $gmwLocation['post_id'],
			'feature'  			=> 0,
			'post_type' 		=> $gmwLocation['post_type'],
			'post_title' 		=> $gmwLocation['post_title'],
			'post_status'		=> $gmwLocation['post_status'],
			'street' 			=> $gmwLocation['street'],
			'apt' 				=> $gmwLocation['apt'],
			'city' 				=> $gmwLocation['city'],
			'state' 			=> $gmwLocation['state_short'],
			'state_long' 		=> $gmwLocation['state_long'],
			'zipcode' 			=> $gmwLocation['zipcode'],
			'country' 			=> $gmwLocation['country_short'],
			'country_long' 		=> $gmwLocation['country_long'],
			'address' 			=> $gmwLocation['address_apt'],
			'formatted_address' => $gmwLocation['formatted_address'],
			'phone' 			=> $gmwLocation['phone'],
			'fax' 				=> $gmwLocation['fax'],
			'email' 			=> $gmwLocation['email'],
			'website' 			=> $gmwLocation['website'],
			'lat' 				=> $gmwLocation['lat'],
			'long' 				=> $gmwLocation['long'],
			'map_icon'  		=> $gmwLocation['map_icon'],
		)
	);
	do_action('gmw_pt_after_location_updated', $gmwLocation );
}

/**
 * GMW search form function - form submit hidden fields
 * 
 */
function gmw_form_submit_fields($gmw, $subValue) {
?>
	<div id="gmw-submit-wrapper-<?php echo $gmw['form_id']; ?>" class="gmw-submit-wrapper">
		<input type="hidden" id="gmw-form-id-<?php echo $gmw['form_id']; ?>" class="gmw-form-id" name="wppl_form" value="<?php echo $gmw['form_id']; ?>" />
		<input type="hidden" id="gmw-paged-<?php echo $gmw['form_id']; ?>" class="gmw-paged" name="paged" value="<?php echo ( get_query_var('paged') ) ? get_query_var('paged') : 1; ?>" />
		<input type="hidden" id="gmw-per-page-<?php echo $gmw['form_id']; ?>" class="gmw-per-page" name="wppl_per_page" value="<?php echo current(explode(",", $gmw['per_page']));?>" />
		<input type="hidden" id="prev-address-<?php echo $gmw['form_id']; ?>" class="prev-address" value="<?php if ( isset($_GET['wppl_address']) ) echo implode(' ' ,$_GET['wppl_address']); ?>">
		<input type="hidden" id="gmw-lat-<?php echo $gmw['form_id']; ?>" class="gmw-lat" name="wppl_lat" value="<?php if ( isset($_GET['wppl_lat']) ) echo $_GET['wppl_lat']; ?>">
		<input type="hidden" id="gmw-long-<?php echo $gmw['form_id']; ?>" class="gmw-long" name="wppl_long" value="<?php if ( isset($_GET['wppl_long']) ) echo $_GET['wppl_long']; ?>">
		<input type="hidden" id="gmw-prefix-<?php echo $gmw['form_id']; ?>" class="gmw-prefix" name="gmw_px" value="<?php echo $gmw['prefix']; ?>" />
		<input type="hidden" id="gmw-action-<?php echo $gmw['form_id']; ?>" class="gmw-action" name="action" value="wppl_post" />
		<input type="submit" id="gmw-submit-<?php echo $gmw['form_id']; ?>" class="gmw-submit" value="<?php _e($subValue,'GMW'); ?>" />
	</div>
<?php
}

/**
 * GMW Search form function - Address Field
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_address_field($gmw, $class) {
	$address_field = '';
	$am = ( isset($gmw['address_mandatory']) ) ? 'mandatory' : '';
	if ( !isset( $gmw['address_title_within'] ) )  $address_field .= '<label for="gmw-address-'.$gmw['form_id'].'">' .$gmw['address_title']. '</label>';
	$address_field .=  '<input type="text" name="wppl_address[]" id="gmw-address-'.$gmw['form_id'].'" class="'.$am.' gmw-address gmw-full-address gmw-address-'.$gmw['form_id'].' '.$class.'" value="'; if ( isset($_GET['wppl_address']) ) $address_field .=  str_replace('+', ' ', implode(' ',$_GET['wppl_address'])); $address_field .=  '" size="35" '; if ( isset( $gmw['address_title_within'] ) ) $address_field .=  'placeholder="'. $gmw['address_title'] . '"'; $address_field .=  ' />';
	
	echo apply_filters('gmw_search_form_address_field', $address_field, $gmw);
}

/**
 * GMW Search form function - Locator Icon
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_locator_icon($gmw, $class) {
	$icon = $gmw['locator_icon'];

	if( isset($icon['show']) ) :
		echo '<div class="gmw-locator-btn-wrapper">';
			echo '<img id="gmw-locate-button'.$gmw['form_id'].'" class="gmw-locate-btn '.$class.'" src="'.GMW_URL . '/images/locator-images/'.$icon['icon'].'" />';
			echo '<img src="'.GMW_URL .'/images/gmw-loader.gif" style="display:none;" />';
		echo '</div>';
	endif;
}

/**
 * GMW Search form function - Radius Values
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_radius_values($gmw, $class, $btitle, $stitle) {
	$miles = explode(",", $gmw['distance_values']);

	if ( empty( $stitle ) ) $stitle = ( $gmw['units_name'] == 'imperial' ) ? __('- Miles -','GMW') : __('- Kilometers -','GMW');
	$btitle =( empty( $btitle ) )  ? __(' -- Within -- ','GMW') : $btitle;

	if ( count( $miles) > 1 ) :
		echo '<select id="gmw-distance-select-'.$gmw['form_id'].'" class="gmw-distance-select gmw-distance-select-'.$gmw['form_id'].' '.$class.'" name="wppl_distance">';
			echo '<option value="'.end( $miles ).'">'; if ( $gmw['units_name']  == 'both' ) echo $btitle; else echo $stitle; echo '</option>';

			foreach ( $miles as $mile ) :
				if ( $_GET['wppl_distance'] == $mile ) $mile_s = 'selected="selected"'; else $mile_s = "";
				echo '<option value="'.$mile .'" '.$mile_s.'>'.$mile.'</option>';
			endforeach;
		echo '</select>';
	else :
		echo '<input type="hidden" name="wppl_distance" value="'.end($miles).'" />';
	endif;
}

/**
 * GMW Search form function - Distance units
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_units($gmw, $class ) {

	if ( $gmw['units_name'] == 'both' ) :
		$unit_m = $unit_i = false;
		if ( isset($_GET['wppl_units']) && $_GET['wppl_units'] == 'metric')  $unit_m = 'selected="selected"'; else $unit_i = 'selected="selected"';
			echo '<select name="wppl_units" id="gmw-units-'.$gmw['form_id'].'" class="gmw-units gmw-units-'.$gmw['form_id'].' '.$class.'">';
				echo '<option value="imperial" '.$unit_i.'>' .__('Miles','GMW'). '</option>';
				echo '<option value="metric" '.$unit_m.'>' . __('Kilometers','GMW'). '</option>';
			echo '</select>';
		else :
	echo '<input type="hidden" name="wppl_units" value="'.$gmw['units_name'].'" />';
	endif;
}

/**
 * GMW Shortcode - Displays the search form and results
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_shortcode($params) {
	$options_r = get_option('wppl_shortcode');
	$gmw_options = get_option('wppl_fields');
	$gmw = $options_r[$params['form']];
	
	ob_start();
	do_action('gmw_'.$gmw['form_type'].'_main_shortcode_start', $gmw, $gmw_options);
	
	$gmw['results_page'] = ( isset($gmw['results_page']) && !empty($gmw['results_page']) ) ? get_permalink($gmw['results_page']) : false;
	//if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
	if ( isset($params['widget']) && ( !isset($gmw['results_page']) || empty($gmw['results_page']) ) ) $gmw['results_page'] = get_permalink($gmw_options['results_page']);
	//make sure we have form template
	if ( !isset($gmw['form_template']) || empty($gmw['form_template']) ) $gmw['form_template'] = 'default';
	
	//Load custom search form and css from child/theme folder
	if( strpos($gmw['form_template'], 'custom_') !== false ) :
		
		do_action('gmw_'.$gmw['form_type'].'_before_custom_search_form', $gmw, $gmw_options);
	
		$sForm = str_replace('custom_','',$gmw['form_template']);	
		wp_enqueue_style( 'gmw-'.$gmw['form_id'].'-'.$sForm.'-form-style', get_stylesheet_directory_uri(). '/geo-my-wp/'.$gmw['form_type'].'/search-forms/'.$sForm.'/css/style.css' );
		include(STYLESHEETPATH. '/geo-my-wp/'.$gmw['form_type'].'/search-forms/'.$sForm.'/search-form.php');
		
		do_action('gmw_'.$gmw['form_type'].'_after_custom_search_form', $gmw, $gmw_options);	
	else :
		//Hook to load search form pages and css styles from plugin folders
		do_action('gmw_'.$gmw['form_type'].'_main_shortcode_search_form', $gmw, $gmw_options);
	endif;
			
	if ( isset( $_GET['action'] ) && $_GET['action'] == "wppl_post" && $gmw['form_id'] == $_GET['wppl_form'] && !isset( $params['widget']) && $gmw['results_page'] == false ) :
		// when form submitted
		gmw_form_submitted($gmw, $gmw_options);
	// if auto results 
	elseif ( empty( $gmw['results_page']) && isset( $_COOKIE['wppl_lat'] ) && isset( $_COOKIE['wppl_long'] ) && isset( $gmw['auto_search']['on'] ) && !isset($params['widget']) ) :
		
		// get address from cookies 
		$gmw['org_address'] =  urldecode($_COOKIE['wppl_city']) . ' ' .  urldecode($_COOKIE['wppl_state']) . ' ' .  urldecode($_COOKIE['wppl_zipcode']) . ' ' .  urldecode($_COOKIE['wppl_country']);
			
		if ( isset($gmw['auto_search']['units']) && $gmw['auto_search']['units'] == 'imperial' ) 
			$gmw['units_array'] = array('radius' => 3959, 'name' => "Mi", 'map_units' => "ptm", 'units'	=> 'imperial');
		else 
			$gmw['units_array'] = array('radius' => 6371, 'name' => "Km", 'map_units' => 'ptk', 'units' => "metric");
		
		$gmw['radius'] 	  = $gmw['auto_search']['radius'];
		$gmw['your_lat']  = urldecode($_COOKIE['wppl_lat']);
		$gmw['your_long'] = urldecode($_COOKIE['wppl_long']);
		
		do_action('gmw_'.$gmw['form_type'].'_results_auto_results', $gmw, $gmw_options);	
	endif;
		
	do_action('gmw_'.$gmw['form_type'].'_main_shortcode_end', $gmw);
	$output_string=ob_get_contents();
	ob_end_clean();

	return $output_string;
}
add_shortcode( 'gmw' , 'gmw_shortcode' );

/**
 * GMW function - when form submitted
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_form_submitted($gmw, $gmw_options) {
	
	$gmw['get_per_page'] = current(explode(",", $gmw['per_page']));
	$gmw['units_array']  = false;
	$gmw['your_lat'] 	 = false;
	$gmw['your_long'] 	 = false;
	
	/* distance units */
	if ( isset($_GET['wppl_units']) && $_GET['wppl_units'] == "imperial" )
		$gmw['units_array'] = array('radius' => 3959, 'name' => "Mi", 'map_units' => "ptm", 'units'	=> 'imperial');
	else
		$gmw['units_array'] = array('radius' => 6371, 'name' => "Km", 'map_units' => 'ptk', 'units' => "metric");
	// get radius 
	$gmw['radius'] = $_GET['wppl_distance'];
	
	do_action('gmw_'.$gmw['form_type'].'_form_submitted_start', $gmw, $gmw_options);
	// get the address 		
	if ( array_filter($_GET['wppl_address']) ) $gmw['org_address'] = str_replace('+', ' ', implode( ' ', $_GET['wppl_address'] ));
	
	// If lat and long exists in the url than use that to query results instead of geocoding the address again. 
	if ( isset($gmw['org_address']) && !empty($gmw['org_address'])) :
		$gmw['your_lat']  = $_GET['wppl_lat'];
		$gmw['your_long'] = $_GET['wppl_long'];
		
		do_action('gmw_'.$gmw['form_type'].'_form_submitted_latlong', $gmw, $gmw_options);
	else :
		$gmw['org_address'] = '';
		do_action('gmw_'.$gmw['form_type'].'_form_submitted_address_not', $gmw, $gmw_options);
	endif;
}

function gmw_results_shortcode() {
	
	if ( !empty( $_GET['action'] ) &&  $_GET['action'] == "wppl_post" ) :
	
		$options_r = get_option('wppl_shortcode');
		$gmw = $options_r[$_GET['wppl_form']];
		$gmw_options = get_option('wppl_fields');
	
		ob_start();
		do_action('gmw_'.$gmw['form_type'].'_results_shortcode_start', $gmw, $gmw_options);
	
		gmw_form_submitted($gmw, $gmw_options);
	
		do_action('gmw_'.$gmw['form_type'].'_results_shortcode_end', $gmw, $gmw_options);
		
		$output_results=ob_get_contents();
		ob_end_clean();
		return $output_results;
	
	endif;
}
add_shortcode( 'gmw_results' , 'gmw_results_shortcode' );

function gmw_users_current_location_returned() {
	
}
add_action( 'wp_ajax_gmw_users_current_location_returned', 'gmw_users_current_location_returned' );
add_action('wp_ajax_nopriv_gmw_users_current_location_returned', 'gmw_users_current_location_returned');

/**
 * GMW Shortcode - User's current location
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_current_location_shortcode($locate) {
	global $gmw_options;
	
	extract(shortcode_atts(array(
			'display_by' 	=> 'city',
			'title' 		=> __('Your Location','GMW'),
			'show_name' 	=> 0
	),$locate));

	$userAddress = false;
	$location = false;
	$location .= '';
	$location .=	'<div class="gmw-cl-wrapper">';

	if ( isset($show_name) ) {

		if( is_user_logged_in() ) {
				
			global $current_user;
			get_currentuserinfo();
			$hMessage = 'Hello, '.$current_user->user_login.'!';
		} else {
			$hMessage = 'Hello, Guest!';
		}
		$location .= '<div class="gmw-cl-welcome-message">'.$hMessage.'</div>';
	}

	if ( isset($_COOKIE['wppl_city']) || isset($_COOKIE['wppl_state']) || isset($_COOKIE['wppl_country']) || isset($_COOKIE['wppl_zipcode']) ) {

		if ( isset($display_by) ) :

		$address_in = explode(',',$display_by);
		$userAddress = array();

		foreach ( $address_in as $field ) :
		if( isset($_COOKIE['wppl_'.$field] ) ) $userAddress[] = urldecode( $_COOKIE['wppl_'.$field] );
		endforeach;

		endif;

		if ( isset($title) && !empty($title) ) $location .= '<div class="gmw-cl-title">' . $title . '</div>';
		$location .=		'<div class="gmw-cl-location"><a href="#TB_inline?#bb&width=250&height=130&inlineId=gmw-locator-hidden" class="thickbox" title="Your Current Location">' . implode(' ', $userAddress) .'</a></div>';
	} else {
		$location .= '<div class="gmw-cl-title"><a href="#TB_inline?#bb&width=250&height=130&inlineId=gmw-locator-hidden" class="thickbox" title="Your Current Location">'; $location .=__('Get your current location','GMW'); $location .= '</a></div>';
	}

	$location .=    	'<div class="gmw-cl-form-wrapper" id="gmw-locator-hidden" style="display:none">';
	$location .=			'<form class="gmw-cl-form" name="wppl_location_form" onsubmit="return false" action="" method="post">';
	$location .=				'<div>';
	$location .=					'<div id="gmw-cl-info">';
	$location .=						'<span>' .__('- Enter Your Location -','GMW') . '</span>';
	$location .=						'<p><input type="text" name="gmw-cl_address" class="gmw-cl-address" value="" placeholder="zipcode or full address..." /><input type="submit" value="go" /></p>';
	$location .=						'<span class="clear"> - or - </span>';
	$location .= 						'<span id="wppl-locator-message"><a href="#" class="gmw-cl-trigger" >'; $location .= __('Get your current location','GMW'); $location .= '</a></span>';
	$location .=					'</div>';
	$location .=					'<div id="gmw-cl-message-wrapper"></div>';
	$location .= 					'<span><div class="gmw-locator-spinner" style="display:none;"><img src="'. GMW_URL .'/images/gmw-loader.gif" /></div></span>';
	$location .=				'</div>';
	$location .=				'<input type="hidden" name="action" value="wppl_user_location" />';
	$location .=			'</form>';
	$location .= 		'</div>';
	$location .=	'</div>';

	return apply_filters('gmw_cl_widget_display', $location, $userAddress, $display_by, $title, $show_name);
}
add_shortcode( 'gmw_current_location' , 'gmw_current_location_shortcode' );

?>
