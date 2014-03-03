<div id="poststuff">
	<div class="postbox-container">
		<div class="postbox">
			<div class="gmw-atoggle">
				<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="gmw-hndle"><span><?php echo _e('General Settings' , 'GMW'); ?></span></h3>
			</div>
			<div class="inside">
				<table class="widefat gmw-admin-settings-table">
					<tbody>
						
						<?php do_action('gmw_main_settings_page_general_start', $wppl_on, $wppl_options, $posts, $pages_s); ?>
						
						<!-- Google Api -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Google Maps API V3 Key', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('This is optional but will let you track your API requests. you can obtain your free Goole API key <a href="https://code.google.com/apis/console/" target="_blank">here</a>.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							</td>
							<td>
								<p><input id="api_key" name="wppl_fields[google_api]" size="100" type="text" value="<?php if ( isset( $wppl_options['google_api']) && !empty($wppl_options['google_api']) ) echo $wppl_options['google_api']; ?>" /></p>
							</td>
						</tr>
						
						<!-- Bing's Api -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Bing maps API', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Use this feature if you often get "OVER_QUERY_LIMIT" response from google when using the search form. 
														OVER_QUERY_LIMIT happens when there are too many calls to Google API from the same IP at a very short time or when the per day limit had been used. 
														This will prevent the address entered in the search form from being geocoded and so no results will return.
														You can read about Bing services and obtain your key <a href="http://www.bingmapsportal.com/application/" target="_blank">Here</a>', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							</td>
							<td>
								<p><input id="api_key" name="wppl_fields[bing_key]" size="100" type="text" value="<?php if ( isset( $wppl_options['bing_key']) && !empty($wppl_options['bing_key']) ) echo $wppl_options['bing_key']; ?>" /></p>
							</td>
						</tr>
						
						<?php do_action('gmw_main_settings_page_after_api', $wppl_on, $wppl_options, $posts, $pages_s); ?>
						
						<!--  Country code -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Country code', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Enter you country code. For example for United States enter US. you can find your country code <a href="http://geomywp.com/country-code/" target="blank">here</a>', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							</td>
							<td>
								<p><input id="country-code" name="wppl_fields[country_code]" size="5" type="text" value="<?php echo $wppl_options['country_code']; ?>" /></p>
							</td>
						</tr> 
					
						<!--  auto locate -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Auto Locator', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('This feature will automatically try to get the user\'s current location when first visiting the website. If location found it will be saved via cookies and later will be used to auto display results.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>
							</td>
							<td>
								<p>
									<input id="wppl-locate-message" name="wppl_fields[auto_locate]" type="checkbox" value="1" <?php if (  isset( $wppl_options['auto_locate'] ) ) echo 'checked="checked"'; ?>/>
								</p>
							</td>
						</tr>
										
						<?php do_action('gmw_main_settings_page_after_auto_locate', $wppl_on, $wppl_options, $posts, $pages_s); ?>
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Choose results page' , 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('This page will display the search results ( for any of your shortcode ) when using the "GMW Search Form". The plugin will first check if a results page was set in the shortcode setting and if so the results will be displayed in that page. Otherwise, if no results page
													was set in the shortcode setting the results will be displayed in the page you choose from the select box. Choose the page from the dropdown menu and paste the shortcode [gmw_results] into it.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>		
							</td>
							<td>
							<select name="wppl_fields[results_page]">
								<?php 
									$pages_s = get_pages();
									foreach ($pages_s as $page_s) {
										echo '<option value="'.$page_s->ID.'"'; echo ( isset($wppl_options['results_page']) && $wppl_options['results_page'] == $page_s->ID ) ? 'selected="selected"' : "" ; echo '>'. $page_s->post_title . '</option>';
									}
								?>
							</select>
								<span style="font-size:12px;margin-left:25px"><?php _e('Paste the shortcode', 'wppl'); ?> <code style="color:brown"> [gmw_results] </code><?php echo esc_attr_e('into the results page'); ?></span>
							</td>
						</tr>
						
						<?php do_action('gmw_main_settings_page_general_end', $wppl_on, $wppl_options, $posts, $pages_s); ?>
						
						<tr>
							<td>
								<p><input name="Submit" class="button button-primary button-large" type="submit" value="<?php echo _e('Save Changes', 'GMW'); ?>" /></p>
							</td>
							<td></td>
						</tr>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php do_action('gmw_main_settings_page', $wppl_options, $wppl_on, $pages_s, $wppl_site_options); ?> 

