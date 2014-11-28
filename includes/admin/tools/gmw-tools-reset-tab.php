<?php
/**
 * admin tools "Reset" tab
 * 
 * @since  2.5
 * @author Eyal Fitoussi
 */

if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Export/Import tab output
 *
 * @access public
 * @since 2.5
 * @author Eyal Fitoussi
 */
function gmw_output_reset_tab() {
?>
<div id="gmw-reset_gmw-tab-content" class="gmw-tools-tab-content">

	<?php do_action( 'gmw_reset_gmw_tab_top' ); ?>

	<?php do_action( 'gmw_reset_gmw_tab_before_uninstall' ); ?>
	
	<!-- Export Settings box -->
	<div class="postbox">
		<h3>
			<span><?php _e( 'Remove GEO my WP Data', 'GMW' ); ?></span>
		</h3>
		<div class="inside">
			<p>
				<?php _e( "Use this form to remove any data created by GEO my WP; Settings, forms, license keys and locations.", "GMW" ); ?><br />
				<em style="color:red;font-weight:bold;font-size:14px;">
					<?php _e( "Very Important! This action cannot be undone so please be very careful when using it. Once you remove the data you wont be able to restore it unless you have a backup.", "GMW" ); ?><br />
					<?php _e( "By removing the post types locations database table you will lose all the post types locations created on this site." ,"GMW" ); ?><br/>
					<?php _e( "This includes locations create using GEO my WP, GEO Job Manager and Resume Manager Geolocation plugins.", "GMW" ); ?><br />
					<?php printf( __( "You might want to <a href=\"%s\">backup your data before removing it.</a>", "GMW" ), admin_url( 'admin.php?page=gmw-tools&tab=import_export' ) ); ?>
				</em>
			</p>
			<p>
				<?php _e( "Check the checkboxes of the items that you would like to remove then click on the \"Clear Data\" button.", "GMW" ); ?>
			</p>

			<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=reset_gmw' ); ?>">
				<p>
					<span><input type="checkbox" class="cb-clear-item" name="clear_item[]" value="settings"  /><?php _e( 'Settings', 'GMW' ); ?></span>
					<span><input type="checkbox" class="cb-clear-item" name="clear_item[]" value="forms"     /><?php _e( 'Forms', 'GMW' ); ?></span>
					<span><input type="checkbox" class="cb-clear-item" name="clear_item[]" value="licenses"  /><?php _e( 'License Keys', 'GMW' ); ?></span>
					
					<?php 
					global $wpdb;
					
					//look for places_locator table
					$ptTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

					//if table exists make it avaliable for uninstallation
					if ( count( $ptTable ) != 0 ) {
					?>
						<span>
							<input type="checkbox" class="cb-clear-item" name="clear_item[]" value="pt_table" onchange="jQuery('#pt-remove-cb').toggle();" />
							<?php printf( __( 'Posts Locator Database Table ( %splaces_locator )', 'GMW' ), $wpdb->prefix); ?>
							<br /><em id="pt-remove-cb" style="display:block;display:none;color:red;margin-left:20px">this will permanently remove all the post types locations created on this site</em>
						</span>
					<?php
					}

					//look for places_locator table
					$flTable = $wpdb->get_results( "SHOW TABLES LIKE 'wppl_friends_locator'", ARRAY_A );
					
					//if table exists make it avaliable for uninstallation
					if ( count( $flTable ) != 0 ) {
					?>
						<span>
							<input type="checkbox" class="cb-clear-item" name="clear_item[]" value="fl_table" onchange="jQuery('#fl-remove-cb').toggle();"/>
							<?php _e( 'Members Locator Database Table ( wppl_friends_locator ) - members location', 'GMW' ); ?>
							<br /><em id="fl-remove-cb" style="display:block;display:none;color:red;margin-left:20px">this will permanently remove all members locations created on this site</em>				
						</span>
					<?php } ?>	
					<span><input type="checkbox" class="cb-clear-item" name="clear_item[]" value="gmw_uninstall" /><?php _e( 'Completely Uninstall and deactivate GEO my WP', 'GMW' ); ?></span>							
				</p>
				<p>
					<input type="hidden" name="gmw_action" value="clear_data" />
					<?php wp_nonce_field( 'gmw_clear_data_nonce', 'gmw_clear_data_nonce' ); ?>
					<input type="submit" class="button-secondary" value="<?php _e( 'Clear Data','GMW' ); ?>" id="gmw-clear-data-button" />
				</p>
				<script>
					jQuery(document).ready(function($) {
						$('#gmw-clear-data-button').click(function() {
							if ( !jQuery('.cb-clear-item').is(':checked') ) { 					
								alert("<?php echo _e( 'You must check at least one item which you\'d like to clear its data.', 'GMW'); ?>"); 
								return false; 
							} else { 
								return confirm("<?php echo _e( 'This action cannot be undone! are you sure that you want to do that?', 'GMW'); ?>"); 
							}
						});				
					});
				</script>
			</form>
			
		</div>
		<!-- .inside -->
	</div>
	<!-- .postbox -->

	<?php do_action( 'gmw_reset_gmw_tab_after_uninstall' ); ?>
			
	<?php do_action( 'gmw_reset_gmw_tab_bottom' ); ?>

</div>
<?php
}
add_action( 'gmw_tools_tab_reset_gmw', 'gmw_output_reset_tab' );

/**
 * Clear GMW data
 *
 * @since 2.5
 * @return void
 */
function gmw_clear_data() {

	//make sure at least one item is checked
	if ( empty( $_POST['clear_item'] ) )
		wp_die( __( "You must check at least one checkbox of an item that you'd like to clear.", "GMW" ) );

	//look for nonce
	if ( empty( $_POST['gmw_clear_data_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_clear_data_nonce'], 'gmw_clear_data_nonce' ) )
		return;

	//clear settings
	if ( in_array( 'settings', $_POST['clear_item'] ) || in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {
		delete_option( 'gmw_options' );
	}

	//clear forms
	if ( in_array( 'forms', $_POST['clear_item'] ) || in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {
		delete_option( 'gmw_forms' );
	}
	
	//clear licensess
	if ( in_array( 'licenses', $_POST['clear_item'] ) || in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {	
		delete_option( 'gmw_license_keys' );
		delete_option( 'gmw_premium_plugin_status' );
	}
	
	//clear posts table
	if ( in_array( 'pt_table', $_POST['clear_item'] ) || in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {
		global $wpdb;
     	$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}places_locator`" );
     	delete_option( "gmw_pt_db_version" );
	}
	
	//clear members table
	if ( in_array( 'fl_table', $_POST['clear_item'] ) || in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS `wppl_friends_locator`" );
		delete_option( "gmw_fl_db_version" );
	}

	//deactivate GEO my WP
	if ( in_array( 'gmw_uninstall', $_POST['clear_item'] ) ) {
		deactivate_plugins( GMW_BASENAME );
	}
	
	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=reset_gmw&gmw_notice=data_reset&gmw_notice_status=updated' ) );
	exit;
	
}
add_action( 'gmw_clear_data', 'gmw_clear_data' );