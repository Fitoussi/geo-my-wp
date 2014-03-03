<!-- Add ons -->

<div id="poststuff">
	<div class="postbox-container">
		<div class="postbox">
			<div class="gmw-atoggle wppl-settings">
				<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
				<div class="handlediv" title="Click to toggle"><br></div>				
				<h3 class="gmw-hndle" style="min-height:20px">
					<span><?php echo _e('Add-ons' , 'GMW'); ?></span>
					<span class="help-btn-tooltip-wrapper">
						<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px" /></a>
						<span class="wppl-help-message">
								<?php _e('The add-ons will add extra features to GEO my WP. You can choose to activate/deactivate any of them. 
								Activate only the add-ons that you are going to use to have better performance. You need to check the checkboxes for the add-ons you wish to use
								and click on "Save Changes". If exists, the settings for those add-ons will show up in the "Settings" page.', 'GMW'); ?>
								<span class="help-arrow"></span>
						</span>
					</span>
				</h3>
			</div>
			<div class="inside">
				<table class="widefat gmw-admin-settings-table">
					<tbody>
						<?php do_action('wppl_addons_page_plugins', $wppl_on); ?>
						<tr>
							<td>
								<p><input name="Submit" class="button button-primary button-large" type="submit" value="<?php echo esc_attr_e('Save Changes'); ?>" /></p>
							</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Post types themes -->

<?php if ( isset($wppl_on['post_types']) ) { ?>

	<div id="poststuff">
		<div class="postbox-container">
			<div class="postbox">
				<div class="gmw-atoggle wppl-settings">
					<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="gmw-hndle" style="min-height:20px">
						<span><?php echo _e('Post types themes' , 'GMW'); ?></span>
						<span class="help-btn-tooltip-wrapper">
							<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
							<span class="wppl-help-message">
								<?php _e('Here you can see all the standard and custom search from and search results templates file that you can use with the shortcodes you are creating. Standard termplate files are those that comes with the plugin and custom template files 
										are those that you can create and upload to your theme or child theme folder. This way you can be sure that any changes you are making to the files are not going to be remove when updating the plugin.
										The standard search forms template files located at <code><strong>geo-my-wp/plugins/posts/search-from/.</strong></code> and the search results template files at <code><strong>geo-my-wp/plugins/posts/search-results/</strong></code>. 
										In order To use your custom template files you will need to upload (you will need create the necessary folders first) the search form template fiels to 
										<code><strong>themes/your theme or child theme folder/geo-my-wp/posts/search-from/.</strong></code> and the search results template files to <code><strong>themes/your theme or child theme folder/geo-my-wp/posts/search-results/.</strong></code>', 'GMW'); ?>
								<span class="help-arrow"></span>
							</span>
						</span>
					</h3>		
				</div>
				<div class="inside">
					
					<!--  search forms standard -->
					
					<div class="wppl-settings" style="background: #f3f3f3;height:20px;border:1px solid #ddd;padding:10px;font-size:12px;color: #277699;font-size: 13px">
						<label><?php _e('Search forms template files', 'GMW'); ?></label>
					</div>
					<table class="widefat gmw-admin-settings-table">
						<tbody>				
							<?php foreach ( glob(GMW_PT_PATH .'search-forms/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
							<?php foreach (  glob(STYLESHEETPATH. '/geo-my-wp/posts/search-forms/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php _e('Custom','GMW'); ?>: <?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<!-- search results standard -->
					
					<div class="wppl-settings" style="background: #f3f3f3;height:20px;border:1px solid #ddd;padding:10px;font-size:12px;color: #277699;font-size: 13px">
						<label><?php _e('Search results template files', 'GMW'); ?></label>
					</div>
					<table class="widefat gmw-admin-settings-table"><tbody>
						<tbody>
							<?php if ( isset($wppl_on['post_types']) && $wppl_on['post_types'] == 1) { ?>
								<?php foreach ( glob(GMW_PT_PATH .'search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
									<tr>
										<td>
											<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php echo basename($dir) ?></p>
										</td>
										<td>
										</td>
									</tr>
								<?php } ?>
								<?php foreach (  glob(STYLESHEETPATH. '/geo-my-wp/posts/search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
									<tr>
										<td>
											<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php _e('Custom','GMW'); ?>: <?php echo basename($dir) ?></p>
										</td>
										<td>
										</td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
					
				</div>
			</div>
		</div>
	</div>
<?php } ?>

<!--  friends locator themes -->

<?php if ( isset($wppl_on['friends']) ) { ?>

	<div id="poststuff">
		<div class="postbox-container">
			<div class="postbox">
				<div class="gmw-atoggle wppl-settings">
					<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="gmw-hndle" style="min-height:20px">
						<span><?php echo _e('Friends locator (Buddypress) themes' , 'GMW'); ?></span>
						<span class="help-btn-tooltip-wrapper">
							<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
							<span class="wppl-help-message">
								<?php _e('Here you can see all the standard and custom search from and search results templates file that you can use with the shortcodes you are creating for buddypress members. Standard termplate files are those that comes with the plugin and custom template files 
										are those that you can create and upload to your theme or child theme folder. This way you can be sure that any changes you are making to the files are not going to be remove when updating the plugin.
										The standard search forms template files located at <code><strong>geo-my-wp/plugins/friends/search-from/.</strong></code> and the search results template files at <code><strong>geo-my-wp/plugins/friends/search-results/</strong></code>. 
										In order To use your custom template files you will need to upload (you will need create the necessary folders first) the search form template fiels to 
										<code><strong>themes/your theme or child theme folder/geo-my-wp/friends/search-from/.</strong></code> and the search results template files to <code><strong>themes/your theme or child theme folder/geo-my-wp/friends/search-results/.</strong></code>', 'GMW'); ?>
								<span class="help-arrow"></span>
							</span>
						</span>
					</h3>		
				</div>
				<div class="inside">
					
					<!--  search forms standard -->
					
					<div class="wppl-settings" style="background: #f3f3f3;height:20px;border:1px solid #ddd;padding:10px;font-size:12px;color: #277699;font-size: 13px">
						<label><?php _e('Search forms template files', 'GMW'); ?></label>
					</div>
					<table class="widefat gmw-admin-settings-table">
						<tbody>
							<?php foreach ( glob(GMW_FL_PATH .'search-forms/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
							
							<?php foreach (  glob(STYLESHEETPATH. '/geo-my-wp/friends/search-forms/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php _e('Custom','GMW'); ?>: <?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<!-- search results standard -->
					
					<div class="wppl-settings" style="background: #f3f3f3;height:20px;border:1px solid #ddd;padding:10px;font-size:12px;color: #277699;font-size: 13px">
						<label><?php _e('Search results template files', 'GMW'); ?></label>
					</div>
					<table class="widefat gmw-admin-settings-table"><tbody>
						<tbody>	
							<?php foreach ( glob(GMW_FL_PATH .'search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
							<?php foreach (  glob(STYLESHEETPATH. '/geo-my-wp/friends/search-results/*', GLOB_ONLYDIR) as $dir ) { ?>
								<tr>
									<td>
										<p style="text-transform: capitalize"><span class="add-on-image"><img src="" /></span><?php _e('Custom','GMW'); ?>: <?php echo basename($dir) ?></p>
									</td>
									<td>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					
				</div>
			</div>
		</div>
	</div>
<?php } ?>