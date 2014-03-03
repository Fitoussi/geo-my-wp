<?php function wppl_pt_addon_page($wppl_on) { ?>
<tr>
	<td>
		<div class="wppl-settings">
			<span class="add-on-image"><img src="" /></span>
			<span><?php echo _e('Post Types','GMW'); ?>:</span>
			<span class="help-btn-tooltip-wrapper">
				<a href="#" class="wppl-help-btn"><img src="<?php echo plugins_url('/geo-my-wp/images/help-btn.png'); ?>" width="25px" height="25px"  /></a>
				<span class="wppl-help-message">
					<?php _e('This feature will let you add a location to any post type or pages you choose. Also you will be able to create an advance search form to let the users of your site
					search for locations based on post types, categories, distance and units and get detailed results near any address they enter.', 'GMW'); ?>
					<span class="help-arrow"></span>
				</span>
			</span>
		</div>
	</td>		
	<td>
		<p style="color:brown; font-size:12px;"><input name="wppl_plugins[post_types] " type="checkbox" value="1" <?php if ( isset( $wppl_on['post_types'] ) && $wppl_on['post_types'] == 1 ) echo ' checked="checked"'; ?>/></p>
		<span class="addon-error-message"></span>
	</td>
</tr>
<?php } add_action('wppl_addons_page_plugins', 'wppl_pt_addon_page', 10,1); ?>