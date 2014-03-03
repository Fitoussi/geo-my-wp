<?php 
function gmw_bp_check() {
	function wppl_fl_addon_page($wppl_on) { 
	?>
	<tr <?php if ( !class_exists( 'BuddyPress' ) ) echo ' class="addon-not-exist"'; ?>>
		<td>
			<div class="wppl-settings">
				<span class="add-on-image"><img src="" /></span><span><?php echo _e('Friends Locator ( Require: Buddypress plugin )','GMW'); ?>:</span>
				<span class="help-btn-tooltip-wrapper">
					<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
					<span class="wppl-help-message">
						<?php _e('This feature will allow your Buddypress members add their location to their profile 
						and for you to create a members search form. Using that , members will be able to search for other members based on 
						a given address and distance. ', 'GMW'); ?>
						<span class="help-arrow"></span>
					</span>
				</span>
			</div>
		</td>		
		<td>
			<p style="color:brown; font-size:12px;"><input name="wppl_plugins[friends] " type="checkbox" value="1" <?php if ( isset( $wppl_on['friends'] ) ) echo ' checked="checked"'; ?>/></p>
			<span class="addon-error-message"></span>
		</td>
	</tr>
	<?php } add_action('wppl_addons_page_plugins', 'wppl_fl_addon_page', 20,1); ?>
<?php } 
add_action( 'admin_init', 'gmw_bp_check' ); ?>
