<?php 	
function gmw_fl_search_address_fields($wppl_options, $option, $e_id) {
	?>
		<!--  address field -->
	
	<li class="gmw-global-map-no">
		<div class="gmw-ssh wppl-settings">
		<h4><?php _e('Addess fields','GMW'); ?></h4>
			<span class="help-btn-tooltip-wrapper">
				<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
				<span class="wppl-help-message">
					<?php _e('Type the title for the address field of the search form. for example "Enter your address". this title wll be displayed either next to the address input field or within if you check the checkbox for it. You can also choose to have the address field mandatory which will prevent users from submitting the form if no address entered. Otherwise if you allow the field to be empty and user submit a form with no address the plugin will display all results.', 'GMW'); ?>
					<span class="help-arrow"></span>
				</span>
			</span>
		</div>
		
		<div class="gmw-ssb">
			<div id="gmw-af">
			
				<div id="gmw-af-single">
					<p>
						<?php _e('Field title','GMW'); ?>:
						<input type="text" name="<?php echo 'wppl_shortcode[' .$e_id .'][address_title]'; ?>" size="40" value="<?php if ( isset($option['address_title']) ) echo $option['address_title']; else echo  _e('zipcode, city & state or full address...','GMW'); ?>" />
						<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][address_title_within]'; ?>" <?php echo (isset($option['address_title_within'])) ? " checked=checked " : ""; ?>>	
						<?php _e('Within the input field','GMW'); ?>
						<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][address_mandatory]'; ?>" <?php echo (isset($option['address_mandatory'])) ? " checked=checked " : ""; ?>>	
						<?php _e('Mandatory Field','GMW'); ?>
					</p>
				</div>
			</div>
		</div>
	</li>
<?php 
}
add_action('gmw_fl_shortcode_fields_search_form_start', 'gmw_fl_search_address_fields', 10, 3);

do_action('gmw_include_shortcodes_functions');
	
$e_id = $_GET['shortcodeID'];
$option = $options_r[$e_id]; ?>
	
<?php foreach ( $options_r as $key => $value ) : ?>
	
	<?php if ( $key != $e_id ) : ?>
		
		<?php if ( is_array($value) ) : ?>
			
			<?php foreach ( $value as $key2 => $value2 ) : ?>
				
				<?php if ( is_array($value2) ) :?>
					
					<?php foreach ( $value2 as $key3 => $value3 ) : ?>
					
						<?php if ( is_array($value3) ) : ?>
						
							<?php foreach ( $value3 as $key4 => $value4 ) : ?>
								<input type="hidden" name="<?php echo 'wppl_shortcode['.$key.']['.$key2.']['.$key3.']['.$key4.']'; ?>" value="<?php echo $value4; ?>">
							<?php endforeach; ?>
							
						<?php else : ?>
							<input type="hidden" name="<?php echo 'wppl_shortcode['.$key.']['.$key2.']['.$key3.']'; ?>" value="<?php echo $value3; ?>">
						<?php endif; ?>
							
					<?php endforeach; ?>
				
				<?php else: ?>
					<input type="hidden" name="<?php echo 'wppl_shortcode['.$key.']['.$key2.']'; ?>" value="<?php echo $value2;?>">
				<?php endif; ?>
			
			<?php endforeach; ?>
		
		<?php else : ?>
			<input type="hidden" name="<?php echo 'wppl_shortcode['.$key.']'; ?>" value="<?php echo $value;?>">
		<?php endif; ?>
		
	<?php else: ?>
		<?php gmw_edit_shortcode($e_id, $option); ?>
	<?php endif; ?>	
	
<?php endforeach; ?>

<?php 
function gmw_edit_shortcode($e_id, $option) { 
	$wppl_options = get_option('wppl_fields'); ?>
	
	<div style="margin-bottom: 15px;">
		<?php screen_icon('wppl'); ?>	
		<h2 style="padding:0px;margin: 1px 5px 0px 3px;">
	    	<?php _e('Edit Shortcode','GMW'); ?>
		</h2> 
	</div>
		
	<table class="widefat fixed">
    	<thead>
            <tr>
            	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:8px"></th>
        		<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:5px"><?php _e('ID','GMW'); ?></th>
             	<th scope="col" id="id" class="manage-column" style="width:5px;"><?php _e('Type','GMW'); ?></th>
                <th scope="col" id="active" class="manage-column column-cb check-column" style="width:15px"><?php _e('Shortcode','GMW'); ?></th>
                <th scope="col" id="active" class="manage-column column-cb check-column"><?php _e('Action','GMW'); ?></th>     
            </tr>
       	</thead>
           		
       	<tbody class="list:user user-list">
			<tr>
				<td style="padding: 7px 15px;">
					<img src="<?php echo plugins_url('/geo-my-wp/admin/images/bp-members-icon.png'); ?>" width="40px" height="40px"  />
				</td>	
                <td>
                	<span><?php echo $option['form_id']; ?></span>
                </td>
                <td>   		
					<span>BP Friends search form</span>
				</td>
                <td class="column-title" style="padding: 5px 0px;">
                    <code>[gmw form="<?php echo $option['form_id']; ?>"]</code>
                </td>
                <td>                                            	    
					<span style="margin-left:5px;"><a class="preview button" title="Create new post types shortcode" href="admin.php?page=wppl-shortcodes"><?php _e('Create new shortcode','GMW'); ?></a></span>
					<span style="margin-left:5px;"><a class="preview button" title="Edit notifications sent by this form" href="admin.php?page=wppl-shortcodes&gmw_action=delete&shortcodeID=<?php echo $option['form_id']; ?>"><?php _e('Delete','GMW'); ?></a></span> 
				</td>
			</tr>
		</tbody>  
	</table>				
    <br />
	<div id="poststuff">
	
		<!-- shortcode starts here -->
		
		<input type="hidden" name="<?php echo 'wppl_shortcode['.$e_id.'][form_type]'; ?>" value="<?php echo $option['form_type'];?>">
		<input type="hidden" name="<?php echo 'wppl_shortcode['.$e_id.'][form_id]'; ?>" value="<?php echo $e_id;?>">
		<input type="hidden" name="<?php echo 'wppl_shortcode['.$e_id.'][prefix]'; ?>" value="fl">
		
		<div class="postbox-container">
			
			<div id="normal-sortables" class="meta-box-sortables ui-sortable">
			
				<?php do_action('gmw_fl_shortcode_group_search_start', $wppl_options, $option, $e_id); ?>
					
				<div class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					
					<!--  search form -->
					
					<h3 class="gmw-atoggle gmw-hndle"><span><?php _e('Search form' , 'GMW'); ?></span></h3>
					<div class="inside">
						<ul class="gmw-single-setting">
						
							<?php do_action('gmw_fl_shortcode_fields_search_form_start', $wppl_options, $option, $e_id); ?>
																						
							<!-- xprofile fields -->
							
							<li  class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Xprofile fields','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose the Xprofile fields that you want to display in the search form. You can choose one or more Xprofile fields created using checkboxs, select box and multiselect box that will all be displayed as checkboxes. You can also choose one Xprofile field created using date field for the age range field.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							
								<div class="gmw-ssb">
									<?php gmw_bp_admin_profile_fields($e_id, $option, 'profile_fields'); ?>
								</div>
							</li>
								
							<?php do_action('gmw_fl_shortcode_fields_radius', $wppl_options, $option, $e_id); ?>
							
							<!--  radius values -->
						
							<li>
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Radius values','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>													
										<span class="wppl-help-message">
											<?php _e('Enter distance values in the input box comma separated if you want to have a select dropdown menu of multiple radius values in the search form. If only one value entered it will be the default value of the search form which will be hidden.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
									
								<div class="gmw-ssb">
									<?php _e('Values (comma separated)','GMW'); ?>:&nbsp;
									<input type="text" name="<?php echo 'wppl_shortcode[' .$e_id .'][distance_values]'; ?>" size="20" <?php if ( isset($option['distance_values']) ) echo ' value="' . $option['distance_values'] . '"'; else echo ' value="5,10,25,50"'; ?>>			
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_units', $wppl_options, $option, $e_id); ?>
							
							<!--  units  -->
							
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Units','GMW'); ?></h4>
							 		<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose if to show both type of units as a dropdown or a single default type.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							
								<div class="gmw-ssb">
									<?php _e('Show Units','GMW'); ?>:
									<select name="<?php echo 'wppl_shortcode[' .$e_id .'][units_name]'; ?>">
										<option value="both" <?php if ( isset($option['units_name']) && ( $option['units_name'] == "both" || empty($option['units_name']) ) ) echo 'selected="selected"'; ?>><?php _e('Both','GMW'); ?></option>
										<option value="imperial" <?php if ( isset($option['units_name']) && $option['units_name'] == "imperial" ) echo 'selected="selected"'; ?>><?php _e('Miles','GMW'); ?></option>
										<option value="metric" <?php if ( isset($option['units_name']) && $option['units_name'] == "metric" ) echo 'selected="selected"'; ?>><?php _e('Kilometers','GMW'); ?></option>
									</select>			
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_locator_icon', $wppl_options, $option, $e_id); ?>
							
							<!--  locator icon -->
						
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Locator icon','GMW'); ?></h4>
							 		<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose if to display the locator button in the search form. The locator button will get the user&#39;s current location and submit the search form based of the location found. you can choose one of the default icons or you can add icon of your own. ', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
								
								<div class="gmw-ssb">
									<span><input name="<?php echo 'wppl_shortcode[' .$e_id .'][locator_icon][show]'; ?>" type="checkbox" value="1" <?php if ( isset($option['locator_icon']['show']) && $option['locator_icon']['show'] == 1 ) echo ' checked="checked"'; ?>/>
										<?php _e('Yes','GMW'); ?>
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
									</span>
									<span style="width:365px;margin-left:10px;">	
										<?php $locator_icons = glob(GMW_PATH . '/images/locator-images/*.png');
										$display_icon = GMW_URL. '/images/locator-images/';
										foreach ($locator_icons as $locator_icon) { ?>
										<span>
											<input type="radio" name="<?php echo 'wppl_shortcode[' .$e_id .'][locator_icon][icon]'; ?>" value="<?php echo basename($locator_icon); ?>" <?php if ( isset($option['locator_icon']['icon']) && $option['locator_icon']['icon'] == basename($locator_icon) ) echo  ' checked="checked"'; ?> />
											<img src="<?php echo $display_icon.basename($locator_icon); ?>" height="30px" width="30px"/>
											&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										</span>
										<?php } ?>
									</span>
								</div>	
							</li>					
						</ul>
						<p><input type="submit" name="Submit" class="button button-primary button-large" value="<?php _e('Save Changes','GMW'); ?>" /></p>
					</div><!-- inside -->
				</div>
					
				<?php do_action('gmw_fl_shortcode_group_results', $wppl_options, $option, $e_id); ?>
				
				<!-- results -->
				
				<div class="postbox gmw-global-map-no">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="gmw-atoggle gmw-hndle"><span><?php _e('Results' , 'GMW'); ?></span></h3>
					<div class="inside">
						<ul class="gmw-single-setting">
								
							<?php do_action('gmw_fl_shortcode_fields_results_start', $wppl_options, $option, $e_id); ?>
							
							<!--  Results page -->
						
							<li>
								<div class="gmw-ssh wppl-settings">
									<h4><?php _e('Results page' , 'GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('The results page will display the search results in the selected page when using the "GMW Search Form" widget or when you want to have the search form in one page and the results showing in a different page. 
													Choose the results page from the dropdown menu and paste the shortcode [gmw_results] into that page. To display the search result in the same page as the search form choose "Same Page" from the select box.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>		
								
								<div class="gmw-ssb">
									<select name="<?php echo 'wppl_shortcode[' .$e_id .'][results_page]'; ?>">
										<?php $pages_s 	  = get_pages(); ?>
										<option value=""  <?php if ( !isset($option['results_page']) || $option['results_page'] == '' ) echo 'selected="selected"'; ?>><?php _e(' -- Same Page -- ','GMW'); ?></option>
										<?php foreach ($pages_s as $page_s) {
											echo '<option value="'.$page_s->ID.'"'; if ( isset($option['results_page']) && $option['results_page'] == $page_s->ID ) echo 'selected="selected"'; echo '>'. $page_s->post_title . '</option>';
										} ?>
									</select>			
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_results_template', $wppl_options, $option, $e_id); ?>
							
							<!-- results template -->
							
							<li>
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Results template','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose The resuls template file (results.php). You can find the search results template files in the <code>plugins folder/geo-my-wp/plugin/friends/search-results</code>. You can modify any of the templates or create your own.
													If you do modify or create you own template files you should create/save them in your theme or child theme folder and the plugin will read them from there. This way your changes will not be removed once the plugin is updated. 
													You will need to create the folders and save your results template there <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/friends/search-results/your-results-theme-folder</strong></code>. 
													Your theme folder will contain the results.php file and another folder named "css" and the style.css within it.', 'GMW'); ?>
											<span class="help-arrow"></span>										
										</span>
									</span>
								</div>
							
								<div class="gmw-ssb">
									<select name="<?php echo 'wppl_shortcode[' .$e_id .'][results_template]'; ?>">
										<?php foreach ( glob(GMW_FL_PATH .'search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
											<option value="<?php echo basename($dir); ?>" <?php if ( isset($option['results_template']) && $option['results_template'] == basename($dir) ) echo 'selected="selected"'; ?>><?php echo basename($dir); ?></option>
										<?php } ?>
										
										<?php foreach ( glob(STYLESHEETPATH. '/geo-my-wp/buddypress/search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
											<?php $cThems = 'custom_'.basename($dir)?>
											<option value="<?php echo $cThems; ?>" <?php if ( isset($option['results_template']) && $option['results_template'] == $cThems ) echo 'selected="selected"'; ?>>Custom Template: <?php echo basename($dir); ?></option>
										<?php } ?>
										
									</select>
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_auto_results', $wppl_options, $option, $e_id); ?>
							
							<!--  auto results -->
										
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
									<h4><?php _e('Auto Results', 'GMW'); ?> </h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Will automatically run initial search and display results based on the user\'s current location (if exists via cookies) when he/she first goes to a search page. You need to define the radius and the units for this initial search .', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
		
								<div class="gmw-ssb">
									<p>
										<input id="wppl-auto-search" name="<?php echo 'wppl_shortcode[' .$e_id .'][auto_search][on]'; ?>" type="checkbox" value="1" <?php if ( isset( $option['auto_search']['on'] ) ) echo "checked='checked'"; ?>/>
										<?php _e('Yes','GMW'); ?>
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<?php _e('Radius','GMW'); ?>		
										<input type="text" id="wppl-auto-radius" name="<?php echo 'wppl_shortcode[' .$e_id .'][auto_search][radius]'; ?>" SIZE="1" value="<?php echo ( isset( $option['auto_search']['radius'] ) ) ? $option['auto_search']['radius'] : "50"; ?>" />	
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<select id="wppl-auto-units" name="<?php echo 'wppl_shortcode[' .$e_id .'][auto_search][units]'; ?>">
											<option value="imperial" <?php if ( isset($option['auto_search']['units']) && $option['auto_search']['units'] == "imperial") echo 'selected="selected"'; ?>><?php _e('Miles','GMW'); ?></option>
											<option value="metric"   <?php if ( isset($option['auto_search']['units']) && $option['auto_search']['units'] == "metric") echo 'selected="selected"'; ?>><?php _e('Kilometers','GMW'); ?></option>
										</select>
									</p>
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_resuslts_output', $wppl_options, $option, $e_id); ?>
							
							<!--  results output -->
						
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Results Output','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose how to display the search results. you can do so using map only , members only or both map and the members.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
		
								<div class="gmw-ssb">
									<p><input type="radio" name="<?php echo 'wppl_shortcode[' .$e_id .'][results_type]'; ?>" value="both" <?php if ( !isset($option['results_type']) || $option['results_type'] == "both" ) echo 'checked="checked"'; ?> />&nbsp;&nbsp;<?php _e('Both members and map','GMW'); ?>
									<p><input type="radio" name="<?php echo 'wppl_shortcode[' .$e_id .'][results_type]'; ?>"  value="posts"  <?php if ( isset($option['results_type']) && $option['results_type'] == "posts") echo 'checked="checked"'; ?>/>&nbsp;&nbsp;<?php _e('Members only','GMW'); ?>
									<p><input type="radio" name="<?php echo 'wppl_shortcode[' .$e_id .'][results_type]'; ?>" value="map"  <?php if ( isset($option['results_type']) && $option['results_type'] == "map") echo 'checked="checked"'; ?>/>&nbsp;&nbsp;<?php _e('Map only','GMW'); ?>
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_avatar', $wppl_options, $option, $e_id); ?>
							
							<!--  Avatar -->
							
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Avatar','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Display avatar and define its width and height in PX. ', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
		
								<div class="gmw-ssb">
									<p>
										<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][show_thumb]'; ?>" <?php echo (isset($option['show_thumb'])) ? "checked=checked" : ""; ?>>
										<?php _e('Yes','GMW'); ?>
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<?php _e('Height','GMW'); ?>:
										&nbsp;<input type="text" onkeyup="this.value=this.value.replace(/[^\d]/,'')"  size="2" name="<?php echo 'wppl_shortcode[' .$e_id .'][thumb_height]'; ?>" value="<?php echo ( isset($option['thumb_height']) && !empty($option['thumb_height']) ) ? $option['thumb_height'] : '200'; ?>">px
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<?php _e('Width','GMW'); ?>:
										&nbsp;<input type="text" onkeyup="this.value=this.value.replace(/[^\d]/,'')"  size="2" name="<?php echo 'wppl_shortcode[' .$e_id .'][thumb_width]'; ?>" value="<?php echo ( isset($option['thumb_width']) && !empty($option['thumb_width']) ) ? $option['thumb_width'] : '200'; ?>">px
									</p>
								</div>
							</li>
											
							<?php do_action('gmw_fl_shortcode_fields_per_page', $wppl_options, $option, $e_id); ?>
								
							<!-- results per page -->
		
							<li>
								<div class="gmw-ssh wppl-settings">
								 	<h4><?php _e('Results per page','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page. ', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
								<div class="gmw-ssb">
									<p><input type="text" name="<?php echo 'wppl_shortcode[' .$e_id .'][per_page]'; ?>" value="<?php echo ( isset($option['per_page'] ) ) ? $option['per_page'] : '5'; ?>" size="10"></p>
								</div>
							</li>
					
							<?php do_action('gmw_fl_shortcode_fields_driving_distance', $wppl_options, $option, $e_id); ?>
							
							<!--  driving distance -->
							
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Driving Distance','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('While the results showing the radius distance from the user to each of the locations, this feature let you display the exact driving distance. Please note that each driving distance request counts with google API when you can have 2500 requests per day.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
								<div class="gmw-ssb">
									<p>
										<input type="checkbox"  value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][by_driving]'; ?>" <?php echo (isset($option['by_driving'])) ? "checked=checked" : ""; ?>>
										<?php _e('Yes','GMW'); ?>
									</p>
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_get_directions', $wppl_options, $option, $e_id); ?>
					
							<!--  Get Directions Link -->
							
							<li class="gmw-global-map-no">
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('"Get Directions" link','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Display "get directions" link that will open a new window with google map that shows the exact driving direction from the user to the location.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
								<div class="gmw-ssb">
									<p>
										<input type="checkbox"  value="1"  name="<?php echo 'wppl_shortcode[' .$e_id .'][get_directions]'; ?>" <?php echo (isset($option['get_directions'])) ? "checked=checked" : ""; ?>>
										<?php _e('Yes','GMW'); ?>
									</p>
								</div>
							</li>
																		
						</ul>
						<p><input type="submit" name="Submit" class="button button-primary button-large" value="<?php _e('Save Changes','GMW'); ?>" /></p>
					</div>
				</div>
				
				<?php do_action('gmw_fl_shortcode_group_search_map', $wppl_options, $option, $e_id); ?>
				
				<!-- Map -->
				
				<div class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="gmw-atoggle gmw-hndle"><span><?php _e('Map' , 'GMW'); ?></span></h3>
					<div class="inside">
						<ul class="gmw-single-setting">
						
							<?php do_action('gmw_fl_shortcode_fields_map_start', $wppl_options, $option, $e_id); ?>
							
							<!--  google map -->
						
							<li>
								<div class="gmw-ssh wppl-settings">
							 		<h4><?php _e('Map Settings','GMW'); ?></h4>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Settings for the main map. Define its height and width in PX or %. Choose the map type from the dropdown menu and check the "auto zoom" checkbox if you want all the markers to automatically fit within the map.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
								<div class="gmw-ssb">
									<p style="line-height: 40px;">
										<?php _e('Width','GMW'); ?>:
										&nbsp;<input type="text" onkeyup="this.value=this.value.replace(/[^\d]/,'')" name="<?php echo 'wppl_shortcode[' .$e_id .'][map_width][value]'; ?>" value="<?php if ( isset( $option['map_width']['value']) ) echo $option['map_width']['value']; ?>" size="2">
										<select name="<?php echo 'wppl_shortcode[' .$e_id .'][map_width][units]'; ?>">
											<option value="px" <?php if ( isset($option['map_width']['units']) && $option['map_width']['units'] == 'px') echo 'selected="selected"'; ?>>Px</option>
											<option value="%" <?php if ( isset($option['map_width']['units']) && $option['map_width']['units'] == '%') echo 'selected="selected"'; ?>>%</option>
										</select>
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<?php _e('height','GMW'); ?>:
										&nbsp;<input type="text" onkeyup="this.value=this.value.replace(/[^\d]/,'')" name="<?php echo 'wppl_shortcode[' .$e_id .'][map_height][value]'; ?>" value="<?php if ( isset($option['map_height']['value']) ) echo $option['map_height']['value']; ?>" size="2">
										<select name="<?php echo 'wppl_shortcode[' .$e_id .'][map_height][units]'; ?>">
											<option value="px" <?php if ( isset($option['map_height']['units']) && $option['map_height']['units'] == 'px') echo 'selected="selected"'; ?>>Px</option>
											<option value="%" <?php if ( isset($option['map_height']['units']) && $option['map_height']['units'] == '%') echo 'selected="selected"'; ?>>%</option>
										</select>
									</p>
									<p>
										<?php _e('Map Type','GMW'); ?>:
										<?php echo 				
										'<select name="wppl_shortcode[' .$e_id .'][map_type]">
											<option value="ROADMAP" '; if ( isset($option['map_type']) && $option['map_type'] == "ROADMAP" ) echo 'selected="selected"'; echo '>ROADMAP</option>
											<option value="SATELLITE" '; if ( isset($option['map_type']) && $option['map_type'] == "SATELLITE" ) echo 'selected="selected"'; echo '>SATELLITE</option>
											<option value="HYBRID" '; if ( isset($option['map_type']) && $option['map_type'] == "HYBRID" ) echo 'selected="selected"'; echo '>HYBRID</option>
											<option value="TERRAIN" '; if ( isset($option['map_type']) && $option['map_type'] == "TERRAIN" ) echo 'selected="selected"'; echo '>TERRAIN</option>
										</select>'
										?>
									</p>
									<p>
										<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][auto_zoom]'; ?>" <?php echo (isset($option['auto_zoom'])) ? "checked=checked" : ""; ?>>
										<?php _e('Auto zoom','GMW'); ?>:&nbsp;
										&nbsp;&nbsp;&#124;&nbsp;&nbsp;
										<?php _e('Or zoom lever','GMW'); ?>:&nbsp; 
										<select name="<?php echo 'wppl_shortcode[' .$e_id .'][zoom_level]'; ?>">
										<?php for ($r=1; $r< 18 ; $r++) { 			
											echo '<option value="' .$r. '"'; if ( isset($option['zoom_level']) && $option['zoom_level'] == $r ) echo 'selected="selected"'; echo '>'.$r.'</option>';
											} ?>						
										</select><?php _e('(will not count if auto zoom is checked)','GMW'); ?>
									</p>
									<p>
										<input type="hidden" value="0" name="<?php echo 'wppl_shortcode[' .$e_id .'][pin_animation]'; ?>">
										<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][pin_animation]'; ?>" <?php echo ( isset($option['pin_animation']) && $option['pin_animation'] == 1 ) ? "checked=checked" : ""; ?>>
										<?php _e('Icons drop animation','GMW'); ?>&nbsp;
									</p>
									<p>
										<input type="hidden" value="0" name="<?php echo 'wppl_shortcode[' .$e_id .'][map_frame]'; ?>">
										<input type="checkbox" value="1" name="<?php echo 'wppl_shortcode[' .$e_id .'][map_frame]'; ?>" <?php echo ( isset($option['map_frame']) && $option['map_frame'] == 1 ) ? "checked=checked" : ""; ?>>
										<?php _e('Map frame','GMW'); ?>&nbsp;
									</p>
								</div>
							</li>
							
							<?php do_action('gmw_fl_shortcode_fields_map_end', $wppl_options, $option, $e_id); ?>
										
						</ul>
						<p><input type="submit" name="Submit" class="button button-primary button-large" value="<?php _e('Save Changes','GMW'); ?>" /></p>
					</div>
				</div>
				
				<?php do_action('gmw_fl_shortcode_group_end', $wppl_options, $option, $e_id); ?>
					
			</div>								
		</div>				
	</div>

	<?php 
	wp_enqueue_script('wppl-admin'); 
}
	