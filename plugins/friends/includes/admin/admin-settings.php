<?php function wppl_fl_basic_admin_settings($wppl_options, $wppl_on, $pages_s) { ?>
<div id="poststuff">
	<div class="postbox-container">
		<div class="postbox">
			<div class="gmw-atoggle">
				<div style="float:left" class="gmw-atoggle-btn" title="Click to toggle">+<br></div>
				<div class="handlediv" title="Click to toggle"><br></div>
				<h3 class="gmw-hndle"><span><?php echo _e('Friends Locator ( Buddypress) - Members Settings','GMW'); ?></span></h3>
			</div>
			<div class="inside">
				<table class="widefat fixed gmw-admin-settings-table">
					<tbody>
							
						<?php do_action('gmw_fl_admin_settings_page', $wppl_options, $wppl_on); ?>
						
						<tr>
							<td>
								<p><input name="Submit" class="button button-primary button-large" type="submit" value="<?php echo _e('Save Changes','GMW'); ?>" /></p>
							</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php } add_action('gmw_main_settings_page', 'wppl_fl_basic_admin_settings', 2,3); ?>