<?php 
/**
 * GMW FL page - Content of the "Location" tab for the looged in user
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<?php //get_header( 'buddypress' ); ?>
<?php 
global $bp, $wpdb;
	//if( is_multisite() ) $wppl_options = get_site_option('wppl_site_options'); else $wppl_options = get_option('wppl_fields');
	$wppl_options = get_option('wppl_fields');

	//get the information of the user from database
	$member_loc_tab = $wpdb->get_results(
		$wpdb->prepare(
				"SELECT * FROM wppl_friends_locator
				WHERE member_id = %s",
				array($bp->displayed_user->id)
		), ARRAY_A
	);
	$mem_loc = '';
	if ( isset( $member_loc_tab ) && !empty( $member_loc_tab ) ) :
		$mem_loc = array(
				'savedLat' 	=> $member_loc_tab[0]['lat'],
				'savedLong' => $member_loc_tab[0]['long']
		);
	endif;

	wp_enqueue_script( 'jquery-ui-autocomplete');
	wp_enqueue_script( 'gmw-fl', GMW_FL_URL . 'includes/js/fl.js',array(),false,true );
	wp_localize_script('gmw-fl', 'adminUrl', GMW_AJAX);
	wp_localize_script('gmw-fl','memLoc', $mem_loc);
?>

<div id="wppl-bp-wrapper">
	<div id="gmw-location-tab-form-wrapper">
		<form name="addLocation" method="post" action="" id="wppl-location-form">
			
			<div id="saved-data">
				<div class="single-input-fields">
					<label for="address"><?php _e('Your location','GMW'); ?>:</label>
					<input name="wppl_address" id="wppl_address" value="<?php if ( isset($member_loc_tab[0]['address']) && !empty($member_loc_tab[0]['address']) ) echo $member_loc_tab[0]['address']; ?>"  type="text" size="40" disabled/>
					<input type="hidden" name="wppl_address" id="wppl_formatted_address" value="<?php echo $member_loc_tab[0]['formatted_address']; ?>"  type="text" size="40" disabled />
 				</div>
 				
 				<div class="single-input-fields" style="display:none;">
					<label for="address"><?php _e('Latitude','GMW'); ?>:</label>
					<input name="wppl_lat" id="wppl_lat" value="<?php if ( isset($member_loc_tab[0]['lat']) && !empty($member_loc_tab[0]['lat']) ) echo $member_loc_tab[0]['lat']; ?>" type="text" disabled />
 				</div>
 				<div class="single-input-fields" style="display:none;">
					<label for="address"><?php _e('Longitude','GMW'); ?>:</label>
					<input name="wppl_long" id="wppl_long" value="<?php if ( isset($member_loc_tab[0]['long']) && !empty($member_loc_tab[0]['long']) ) echo $member_loc_tab[0]['long']; ?>" type="text" disabled />
				</div>
				
				<div class="single-input-fields">
					<input type="button" id="gmw-location-tab-edit-user-location-btn" class="bp-btn" value="<?php _e('Edit location','GMW'); ?>" />
					<input type="button" id="remove-address-btn" class="bp-btn" value="<?php _e('Delete location','GMW'); ?>" />	
					<div id="wppl-ajax-loader-bp" style="display:none;margin-top:5px;">
						<img src="<?php echo GMW_FL_URL .'/includes/images/ajax-loader.gif'; ?>" id="wppl-loader-image" alt="" style="width:20px" /> 
						<span><?php _e('Loading...','GMW'); ?></span>
					</div>
					<div id="wppl-bp-feedback"></div>						
 				</div>
 				
 			</div><!-- saved data -->
 			<div id="gmw-location-tab-edit-user-location">
 				<div class="single-input-fields">
					<label for="street"><?php _e('Get you current location','GMW'); ?></label>
 					<input type="button" class="bp-btn" id="locate-me-bp" value="<?php _e('Locate me','GMW'); ?>" />
 					<div id="wppl-ajax-loader-locate" style=" display:none">
 						<img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__); ?>" id="wppl-loader-image" alt="" /><?php _e('Loading...','GMW'); ?>
 					</div>
 				</div>
 				<div class="clear"></div>
 				
 				<div class="single-map-field">
					<label><?php _e('Find your location on the map','GMW'); ?></label>
					<div id="gmw-location-tab-map-holder">	
    					<div id="bp-map" class="gmw-map" style="height:210px;width:100%;"></div>
					</div><!-- map holder -->	
				</div>
				
				<div id="gmw-location-tab-autocomplete-wrapper">
					<label><?php _e('Type an address for autocomplete','GMW'); ?></label>
					<input type="text" id="wppl-addresspicker" value="<?php if ( isset($member_loc_tab[0]['address']) && !empty($member_loc_tab[0]['address']) ) echo $member_loc_tab[0]['address']; ?>" style="width:80% !important" />
				</div>
				<div class="metabox-tabs-div">
					<label for="street"><?php _e('Enter your location manually','GMW'); ?></label>
					<ul class="metabox-tabs" id="metabox-tabs">
						<li class="active address-tab tab-btn-wrapper" style="width:70px"><input type="button" id="wppl-address-tab" value="<?php _e('Address','GMW'); ?>" /></li>
						<li class="lat-long-tab tab-btn-wrapper" style="width:120px"><input type="button" id="wppl-latlong-tab" value="<?php _e('Latitude / Longitude','GMW'); ?>" /></li>
					</ul>
					<div class="clear"></div>
					<div class="address-tab tab-wrapper" id="address-tab-wrapper">
					
					<?php 
						
						$gmw_fields ='';
						
		 				$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .=	'<label for="street">'. __('Street','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[street]" id="wppl_street" type="text" value="';if ( isset($member_loc_tab[0]['street']) && !empty($member_loc_tab[0]['street']) ) $gmw_fields .= $member_loc_tab[0]['street']; $gmw_fields .= '" />';
						$gmw_fields .=  '</div>';
								
						$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .= 	'<label for="apt">'. __('Apt/Suit','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[apt]" id="wppl_apt" type="text" value="'; if ( isset($member_loc_tab[0]['apt']) && !empty($member_loc_tab[0]['apt']) ) $gmw_fields .= $member_loc_tab[0]['apt']; $gmw_fields .= '"/>';
		 				$gmw_fields .= '</div>';
		 				
		 				$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .= 	'<label for="city">'. __('City','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[city]" id="wppl_city" type="text" value="'; if ( isset($member_loc_tab[0]['city']) && !empty($member_loc_tab[0]['city']) ) $gmw_fields .= $member_loc_tab[0]['city']; $gmw_fields .= '" />';
		 				$gmw_fields .= '</div>';
		 						
		 				$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .= 	'<label for="state">'. __('State','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[state]" id="wppl_state" type="text" value="'; if ( isset($member_loc_tab[0]['state']) && !empty($member_loc_tab[0]['state']) ) $gmw_fields .= $member_loc_tab[0]['state']; $gmw_fields .= '" />';
		 				$gmw_fields .= '</div>';
		 				
		 				$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .= 	'<label for="zipcode">'. __('Zipcode','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[zipcode]" id="wppl_zipcode" type="text" value="'; if ( isset($member_loc_tab[0]['zipcode']) && !empty($member_loc_tab[0]['zipcode']) ) $gmw_fields .= $member_loc_tab[0]['zipcode']; $gmw_fields .= '" />';
						$gmw_fields .= '</div>';
						
						$gmw_fields .= '<div class="single-input-fields">';
						$gmw_fields .= 	'<label for="country">'. __('Country','GMW'). '</label>';
						$gmw_fields .= 	'<input name="address[country]" id="wppl_country" type="text" value="'; if ( isset($member_loc_tab[0]['country']) && !empty($member_loc_tab[0]['country']) ) $gmw_fields .= $member_loc_tab[0]['country']; $gmw_fields .= '" />';
	
		 				$gmw_fields .= '</div>';
		 			
		 				echo apply_filters('gmw_fl_location_tab_address_fields', $gmw_fields, $wppl_options, $member_loc_tab );
		 				
					?>
 					</div>
 					<div class="lat-long-tab tab-wrapper" id="latlong-tab-wrapper">	
 						<div class="single-input-fields">
							<label for="address"><?php _e('Latitude','GMW'); ?></label>
							<input name="wppl_enter_lat" id="wppl_enter_lat" value="<?php if ( isset($member_loc_tab[0]['lat']) && !empty($member_loc_tab[0]['lat']) ) echo $member_loc_tab[0]['lat']; ?>" type="text" />
 						</div>
 						<div class="single-input-fields gmw-location-tab-save-button">
							<label for="address"><?php _e('Longitude','GMW'); ?></label>
							<input name="wppl_enter_long" id="wppl_enter_long" value="<?php if ( isset($member_loc_tab[0]['long']) && !empty($member_loc_tab[0]['long']) ) echo $member_loc_tab[0]['long']; ?>" type="text" />
 						</div>	
 						<div><input type="button" class="member-get-address" class="bp-btn" value="<?php _e('Get address','GMW'); ?>"></div>		
 					</div><!-- lat/long tab -->
 					<input type="hidden" name="action" value="addLocation"/>
				
				</div><!-- meta tabs wrapper -->
				<?php do_action('gmw_fl_location_tab_before_save_button', $wppl_options, $member_loc_tab); ?>
				<div class="single-input-fields">	
					<input type="button" id="member-save-location" class="bp-btn" value="<?php _e('Save Location','GMW'); ?>">
				</div>
				
			</div><!-- edit users location -->
		</form>
	</div><!-- location form wrapper -->

</div><!-- wppl bp wrapper -->
