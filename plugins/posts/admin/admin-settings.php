<?php function gmw_pt_admin_settings($wppl_options,$wppl_on, $pages_s) {
	$posts 	 = get_post_types();
	//wp_enqueue_style( 'farbtastic' );
    //wp_enqueue_script( 'farbtastic' ); 
 ?>
<?php /*?><script type="text/javascript"> jQuery(document).ready(function($){ $('#wppl-theme-color-picker').farbtastic('#wppl-theme-color'); }); </script>	*/ ?>
<div id="poststuff">
	<div class="postbox-container">
		<div class="postbox">
			<div class="gmw-atoggle">
				<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="gmw-hndle"><span><?php echo _e('Post Types Settings' , 'GMW'); ?></span></h3>
			</div>
			<div class="inside">
				<table class="widefat fixed gmw-admin-settings-table">
					<tbody>
						
						<?php do_action('gmw_main_settings_pt_fields_start', $wppl_options,$wppl_on ); ?>
						
						<!-- Post type -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Post types', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Choose the post types where you want the address fields to appear. choose only the post types which you want to add a location too.', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>	
							</td>
							<td>			
							<?php foreach ($posts as $post) { 
								echo '<input id="address_fields" type="checkbox" name="wppl_fields[address_fields][]" value="' . $post . '" '; if ( isset($wppl_options['address_fields']) && in_array($post, $wppl_options['address_fields']) ) echo  ' checked="checked"'; echo  '>' .get_post_type_object($post)->labels->name .'&nbsp;&nbsp;&nbsp;&nbsp;' ;
									}	
							?>
							</td>
						</tr>
						
						<?php do_action('gmw_main_settings_pt_fields_mandatory_fields', $wppl_options,$wppl_on ); ?>
						
						<!-- Mandatory address -->
						
						<tr>
							<td>
								<div class="wppl-settings">
									<label><?php _e('Mandatory address fields', 'GMW'); ?>:</label>
									<span class="help-btn-tooltip-wrapper">
										<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
										<span class="wppl-help-message">
											<?php _e('Check this box if you want to make sure that users will add location to a new post. it will prevent them from saving a post that do not have a location. Otherwise, users will be able to save a post even without a location. This way the post will be published and would show up in Wordpress search results but not in the Proximity search results. ', 'GMW'); ?>
											<span class="help-arrow"></span>
										</span>
									</span>
								</div>		
							</td>
							<td>
								<p>
								<input name="wppl_fields[mandatory_address] " type="checkbox" value="1" <?php if ( isset($wppl_options['mandatory_address']) ) echo "checked"; ?>/>
								<?php echo _e('Yes','wppl'); ?>
								</p>
							</td>
						</tr>
						
						<?php do_action('gmw_main_settings_pt_theme_color', $wppl_options, $wppl_on ); ?>
						
						<?php /* ?>
						<tr>
							<td>
								<div class="wppl-settings">
									<span>
										<span><?php _e('Theme color:', 'wppl'); ?></span>
										<span><a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a></span>
									</span>
									<div class="clear"></div>
									<span class="wppl-help-message">
										<?php _e('Check the checkbox if you want to use the theme color and choose the color you want. This feature controls the color of the links, address, and the title in the search results. If the checkbox left empty the colors that will be used will be from the stylesheet.', 'wppl'); ?>
									</span>
								</div>		
							</td>
							<td>
								<input type="checkbox" name="wppl_fields[use_theme_color]" value="1" <?php echo ( isset($wppl_options['use_theme_color']) && $wppl_options['use_theme_color']) ? 'checked="checked"' : ''; ?> />
								<?php echo _e('Yes','wppl'); ?>
								&nbsp;&nbsp;&#124;&nbsp;&nbsp;
								<input type="text" id="wppl-theme-color" name="wppl_fields[theme_color]" value="<?php echo ($wppl_options['theme_color']) ? $wppl_options['theme_color'] : "#2E738F"; ?>" />
								<div id="wppl-theme-color-picker"></div>
							</td>
						</tr>
						<? */ ?>
						<?php do_action('gmw_main_settings_pt_fields_end', $wppl_options,$wppl_on ); ?>
						
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
<?php } add_action('gmw_main_settings_page', 'gmw_pt_admin_settings', 1,3); ?>