<?php
/**
 * GMW FL function - display Xprofile fields in the shortcodes page
 */
function gmw_bp_admin_profile_fields($e_id, $option, $variable) {
	global $bp;
	
	if (bp_is_active ('xprofile')) : 
	if (function_exists ('bp_has_profile')) : 
		if (bp_has_profile ('hide_empty_fields=0')) :
		
			$dateboxes = array ();
			$dateboxes[0] = '';

			while (bp_profile_groups ()) : 
				bp_the_profile_group (); 

				//echo '<strong>'. bp_get_the_profile_group_name (). ':</strong><br />';

				while (bp_profile_fields ()) : 
					bp_the_profile_field(); ?>
					<?php if ( (bp_get_the_profile_field_type () == 'datebox') ) {  ?>	
						<?php $dateboxes[] = bp_get_the_profile_field_id(); ?>
					<?php } 
					
					?>
					<?php if ( (bp_get_the_profile_field_type () == 'radio') || (bp_get_the_profile_field_type () == 'selectbox') || (bp_get_the_profile_field_type () == 'multiselectbox') || (bp_get_the_profile_field_type () == 'checkbox') ) {  ?>	
						<?php $field_id = bp_get_the_profile_field_id(); ?>
						<input type="checkbox" name="<?php echo 'wppl_shortcode[' .$e_id .'][' .$variable . '][]'; ?>" value="<?php echo $field_id; ?>" <?php if ( isset($option[$variable]) && in_array($field_id, $option[$variable])) echo ' checked=checked'; ?>/>
						<label><?php bp_the_profile_field_name(); ?></label>
						<br />
					<?php } 
			endwhile;
			endwhile; ?>
			
			<label><strong style="margin:5px 0px;float:left;width:100%"><?php _e('Choose the "Age Range" Field','GMW'); ?></strong></label><br />
			<select name="<?php echo 'wppl_shortcode[' .$e_id .']['.$variable.'_date]'; ?>"> 
				<?php foreach ($dateboxes as $datebox) {  ?>
					<?php $field = new BP_XProfile_Field( $datebox ); ?>
					<?php $selected = ($option[$variable.'_date'] == $datebox) ? 'selected="selected"' : ''; ?>
					<option value="<?php echo $datebox; ?>" <?php echo $selected; ?> ><?php echo $field->name; ?></option>
				<?php } ?>
			</select> 

	<?php endif;
	endif; 
	endif; 
	
	if (!bp_is_active ('xprofile')) {
		if (is_multisite()) $site_url = network_site_url('/wp-admin/network/admin.php?page=bp-components&updated=true');
		else $site_url = site_url('/wp-admin/admin.php?page=bp-components&updated=true');
		_e('Your buddypress profile fields are deactivated.  To activate and use them <a href="'.$site_url.'"> click here</a>.','wppl');
	}
}

?>
