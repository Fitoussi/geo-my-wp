<?php

/**
 * Export Import tab 
 * 
 * @since 2.5
 * @author The functions below inspired by functions written by Pippin Williamson
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

//include export class
include_once ( 'gmw-tools-class-export.php' );

/**
 * Export/Import tab output
 *
 * @access public
 * @since 2.5
 * @author Eyal Fitoussi
 */
function gmw_output_import_export_tab() {
?>	
	<div class="gmw-tabs-table">
		<div class="gmw-tabs-table gmw-edit-form-page-nav-tabs gmw-nav-tab-wrapper">
			<?php 			
			$ei_tabs = array(
					'ei_data' => __( 'GEO my WP Data', 'GMW' )
			);
				
			if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) {
				$ei_tabs['ei_pt_locations']  = __( 'Post Types Locations', 'GMW' );
				$ei_tabs['plugins_importer'] = __( 'Other Plugins Importer', 'GMW' );
			}
				
			$ei_tabs =  apply_filters( 'gmw_tools_page_import_export_tabs', $ei_tabs );
				
			foreach ( $ei_tabs as $key => $title ) {
				echo '<span><a href="#settings-' . sanitize_title( $key ) . '" id="'.sanitize_title( $key ).'" title="' . esc_html( $title ) . '"  class="gmw-nav-tab gmw-nav-trigger">' . esc_html( $title ) . '</a></span>';
			}
			?>
		</div>
	</div>
		
	<div id="gmw-import-export-tab-content" class="gmw-tools-tab-content">
	
		<div id="settings-ei_data" class="gmw-settings-panel gmw-import-export-page-tab-wrapper">
	
			<?php do_action( 'gmw_export_import_top' ); ?>
	
			<?php do_action( 'gmw_export_import_before_ei_data' ); ?>
						
			<!-- Export Settings box -->
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<span><?php _e( 'Export/Import Data', 'GMW' ); ?> </span>
					</h3>
					<div class="inside">
						<p>
							<?php _e( "Use the Export and Import data forms below to create a back-up file ( .json ) of GEO my WP's data; the settings of GEO my WP and its add-ons, the forms you created and license keys you might have activated.", "GMW" ); ?><br />
							<?php _e( "You can use the back-up file to restore the data on this site in case that something goes wrong or you could import it into a different site.", "GMW" ); ?><br />
						</p>
						<p>
							<?php _e( "Please follow the steps of each form below for a complete process of exporting and importing your data.", "GMW"); ?><br />	
							<br />
							<span class="description">
								<?php _e( "*Note, the license keys should only be imported back to the same site it was exported from. If you need to activate your license keys on a different site you should first deactivate them on this site then activate them back on the other site.", "GMW" ); ?>
							</span>
						</p>
					</div>
				</div>
				
				<div class="inside-middle">
					<h3>
						<span><?php _e( 'Export Data', 'GMW' ); ?> </span>
					</h3>
					<div class="inside">
						<p>
							<?php _e( 'To export your data please check the checkboxes of the items that you would like to export then click the "Export" button to create a .json file.', 'GMW' ); ?>
						</p>
						</strong>
		
						<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<p>
								<span>
									<input type="checkbox" class="cb-export-item" name="export_item[]" value="settings" checked="checked" /><?php _e( 'Settings', 'GMW' ); ?>
									<em class="description"><?php _e( '( GEO my WP and its add-ons )', 'GMW' ); ?></em>
								</span>
								<span><input type="checkbox" class="cb-export-item" name="export_item[]" value="forms" checked="checked" /><?php _e( 'Forms', 'GMW' ); ?></span>
								<span>
									<input type="checkbox" class="cb-export-item" name="export_item[]" value="licenses" checked="checked" /><?php _e( 'License Keys', 'GMW' ); ?>
									<em class="description"><?php _e( '( exported license keys should only be imported back to this site. )', 'GMW' ); ?></em>
								</span>
							</p>
							<p>
								<input type="hidden" name="gmw_action" value="export_data" />
								<?php wp_nonce_field( 'gmw_export_data_nonce', 'gmw_export_data_nonce' ); ?>
								<?php submit_button( __( 'Export', 'GMW' ), 'secondary', 'submit', false, array( 
										'onclick' => "if ( !jQuery('.cb-export-item').is(':checked') ) { alert('You must check at least one item that you\'d like to export.'); return false; }" ) ); 
								?>
							</p>
						</form>
		
					</div>
				</div>
				<!-- .inside -->
	
				<div class="inside-bottom">
					<h3>
						<span><?php _e( 'Import Data', 'GMW' ); ?> </span>
					</h3>
					<div class="inside">
		
						<p>
							<?php _e( 'Import GEO my WP data from a .json file. The file can be created using the export form above.', 'GMW' ); ?>
						</p>
		
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<p>
								<?php _e( "To import data first choose the .json file you would like to use. Then check the checkboxes of the items that you would like to import and click the \"Import\" button.", "GMW" ); ?>
							</p>
							<p>
								<input type="file" name="import_file" />
							</p>
							<p>
								<span><input type="checkbox" class="cb-import-item" name="import_item[]" value="settings" checked="checked" /><?php _e( 'Settings', 'GMW' ); ?></span>
								<span><input type="checkbox" class="cb-import-item" name="import_item[]" value="forms"    checked="checked" /><?php _e( 'Forms', 'GMW' ); ?></span>
								<span><input type="checkbox" class="cb-import-item" name="import_item[]" value="licenses" checked="checked" /> <?php _e( 'License Keys', 'GMW' ); ?></span>
							</p>
							<p>
								<input type="hidden" name="gmw_action" value="import_data" />
								<?php wp_nonce_field( 'gmw_import_nonce', 'gmw_import_nonce' ); ?>
								<?php submit_button( __( 'Import', 'GMW' ), 'secondary', 'submit', false, array( 
										'onclick' => "if ( !jQuery('.cb-import-item').is(':checked') ) { alert('You must check at least one item that you\'d like to import.'); return false; }" ) ); 
								?>
							</p>
						</form>
		
					</div>
				</div>
	
			</div>
			<!-- .postbox -->
	
			<?php do_action( 'gmw_export_import_after_ei_data' ); ?>
		</div>
		
		<div id="settings-ei_pt_locations" class="gmw-settings-panel gmw-import-export-page-tab-wrapper">
			
			<?php do_action( 'gmw_export_import_before_pt_locations_to_post_meta' ); ?>
			
			<!--  make sure Post types locator add-on is activated -->
			<?php  if ( GEO_my_WP::gmw_check_addon( 'posts' ) ) { ?>
				
			<!-- Import settings box -->
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<span><?php _e( "Export/Import Posts Types Locations using GEO my WP's post_meta", "GMW" ); ?> </span>
					</h3>
		
					<div class="inside">
						<p>
							<?php _e( "The forms below will help you in the process of exporting the post types locations created on this site and importing them into a different site.", "GMW" ); ?><br />
							<?php printf( __( "The export/import forms below need to be used together with the native <a href=\"%s\" target=\"blank\"> WordPress export system*</a> and <a href=\"%s\" target\"_blank\">WordPress importer*</a> for a complete process.", "GMW" ), admin_url( 'export.php' ), admin_url( 'import.php' ) ); ?><br />
						</p>
						<p class="description">	
							<?php _e( "*You can use other plugins ( other than the WordPress native plugins mentioned above ) to export/import your WordPress posts. However, the plugins you chose to use must export and import the custom fields of these posts in order to import/export the locations.", "GMW" ); ?>
						</p>
						<p>
							<?php _e( "Please follow the steps of each form below for a complete process of exporting and importing your post types locations.", "GMW"); ?><br />				
						</p>
		
					</div>
				</div>
				
				<div class="inside-middle">
					<h3>
						<span><?php _e( "Export Posts Types Locations To GEO my WP's post_meta", "GMW" ); ?> </span>
					</h3>
		
					<div class="inside">
						<ol>	
							<?php global $wpdb; ?> 
							<li>
								<?php printf( __( "Click on the \"Export\" button below. By doing so the plugin will duplicate each post type location created on this site from GEO my WP's custom table ( %splaces_locator ) into a custom field of the post it belongs to.", "GMW" ), $wpdb->prefix ); ?>
								<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
									<p>
										<input type="hidden" name="gmw_action" value="pt_locations_post_meta_export" />
										<?php wp_nonce_field( 'gmw_pt_locations_post_meta_export_nonce', 'gmw_pt_locations_post_meta_export_nonce' ); ?>
										<?php submit_button( __( 'Export', 'GMW' ), 'secondary', 'submit', false ); ?>
									</p>
								</form>
							</li>
							<li><?php printf( __( "The next step will be to export your posts using the native <a href=\"%s\" target=\"blank\"> WordPress export system</a>.", "GMW"), admin_url( 'export.php' ) ); ?></li>
						</ol>
					</div>
				</div>
				<!-- .inside -->
	
				<div class="inside-bottom">
					<h3>
						<span><?php _e( "Import Posts Types Locations From GEO my WP's post_meta", "GMW" ); ?> </span>
					</h3>
		
					<div class="inside">
						<ol>
							<li><?php _e( "Before importing your locations into this site make sure you used the \"Export\" form above on the original site in order to export your locations.", "GMW" ); ?></li>
							<li><?php printf( __( "Import your posts using <a href=\"%s\" target\"_blank\">WordPress importer</a>. After done so come back to this page to complete step 3.", "GMW" ), admin_url( 'import.php' ) ); ?></li>
							<li><?php printf( __( "Click on the \"Import\" button. By doing so the plugin will duplicate each post type location from the custom field of the post it belongs to into GEO my WP's custom table in database ( %splaces_locator ).", "GMW" ), $wpdb->prefix ); ?></li>
						</ol>
		
						<?php 
						//get all custom fields with gmw location from database
						$check_pm_locations = $wpdb->get_results("
								SELECT *
								FROM `{$wpdb->prefix}postmeta`
								WHERE `meta_key` = 'gmw_pt_location'", ARRAY_A );

						//abort if no locations found
						$check_pm_locations = ( !empty( $check_pm_locations ) ) ? true : false;					
						?>
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<p>
								<input type="hidden" name="gmw_action" value="pt_locations_post_meta_import" />
								<?php wp_nonce_field( 'gmw_pt_locations_post_meta_import_nonce', 'gmw_pt_locations_post_meta_import_nonce' ); ?>
								<input type="submit" class="button-secondary" value="<?php _e( "Import", "GMW" ); ?>" <?php if ( !$check_pm_locations ) echo 'disabled="disabled"'; ?>/>
								<?php echo ( $check_pm_locations ) ? '<em style="color:green">'.__( 'Locations are avalible for import.', "GMW" ).'</em>' :  '<em style="color:red">'.__( 'No locations are avalible for import.', "GMW" ) .'</em>'; ?>
							</p>
						</form>
		
					</div>
				</div>
			</div>
			
			<!-- .postbox -->
	
			<?php do_action( 'gmw_export_import_after_pt_locations_to_post_meta' ); ?>
			
			<?php do_action( 'gmw_export_import_before_pt_locations_to_custom_post_meta' ); ?>
							
			<!-- Import settings box -->
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<span><?php _e( 'Import Posts Types Locations Using Custom post_meta', 'GMW' ); ?> </span>
					</h3>
					
					<div class="inside">
						<p>
							<?php _e( "Using this form you can import locations to GEO my WP using the custom fields of your choice. This can be helpful when you want to import locations created by other plugin and its location data is being saved in custom fields.", "GMW" ); ?><br />
						</p>
						<p>
							<?php _e( "To import the locations click on the \"Set custom field\" link to see the fields. You can choose a custom field from the dropdown menus for each location component that exists in GEO my WP database table.", "GMW"); ?><br />				
							<?php _e( "Note that the latitude and longitude fields are mandatory as without both of the fields GEO my WP cannot perform the search query.", "GMW"); ?><br />
							<?php _e( "Other than the lat/long fields the rest of the fields are optional and search can be performed without them. However, certain fields might be needed for certain features. ", "GMW"); ?><br />
						</p>
		
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<?php 
							$gmw_settings = get_option( 'gmw_options' );
							$saved_field =  ( !empty( $gmw_settings['tools_page_options']['gmw_pt_import_custom_post_meta'] ) ) ? $gmw_settings['tools_page_options']['gmw_pt_import_custom_post_meta'] : array();
							
					        global $wpdb;
					        
					        //get custom fields
					        $cFields = $wpdb->get_col( "
					        	SELECT meta_key
					        	FROM $wpdb->postmeta
					        	GROUP BY meta_key
					        	ORDER BY meta_id DESC"
					        );
					        if ( $cFields ) natcasesort( $cFields );
							
					        $fieldsArray = array(
					        		'lat'				=> __( 'Latitude ( mandatory )', 'GMW' ),
					        		'long'				=> __( 'Longitude ( mandatory )', 'GMW' ),
					        		'street' 			=> __( 'Street', 'GMW' ),
					        		'apt' 	 			=> __( 'Apt', 'GMW' ),
					        		'city' 				=> __( 'City', 'GMW' ),
					        		'state' 			=> __( 'State Short Name ( ex. FL )', 'GMW' ),
					        		'state_long' 		=> __( 'State Long name ( ex. Florida )', 'GMW' ),
					        		'zipcode' 			=> __( 'zipcode', 'GMW' ),
					        		'country' 			=> __( 'Country Short Name ( ex. US )', 'GMW' ),
					        		'country_long' 		=> __( 'Country Long Name ( ex. United States )', 'GMW' ),
					        		'address' 			=> __( 'Address ( address field the way the user enteres )', 'GMW' ),
					        		'formatted_address' => __( 'Formatted Address ( formatted address returned from Google after geocoding )', 'GMW' ),
					        		'phone' 			=> __( 'Phone', 'GMW' ),
					        		'fax' 				=> __( 'Fax', 'GMW' ),
					        		'email' 			=> __( 'Email', 'GMW' ),
					        		'website' 			=> __( 'Website', 'GMW' ),
					        		'map_icon' 			=> __( 'Map Icon', 'GMW' ),
					        );
					        ?>
					        <a href="#" id="post-meta-fields-toggle" onclick="event.preventDefault(); jQuery('#post-meta-wrapper').slideToggle();"><?php _e( 'Set Custom Fields', 'GMW'); ?></a>
					        <div id="post-meta-wrapper" style="display:none">					
								<?php foreach ( $fieldsArray as $name => $title ) { ?>			
									<p>
										<label><?php echo $title; ?>: </label><br />
										<select id="gmw-import-custom-field-<?php echo $name; ?>" class="gmw-import-custom-field" name="gmw_post_meta[<?php echo $name; ?>]">
											<option value="" selected="selected"><?php _e( 'N/A', 'GMW' ); ?> 
											<?php foreach ( $cFields as $cField ) { ?>			
										   		<option <?php if ( !empty( $saved_field[$name] ) ) selected( $saved_field[$name], $cField ); ?> value="<?php echo $cField; ?>"><?php echo $cField; ?></option>										
											<?php } ?>
										</select>
									</p>	
								<?php } ?>
							</div>
							<p>	
								<input type="hidden" name="gmw_action" value="pt_locations_custom_post_meta_import" />
								<?php wp_nonce_field( 'gmw_pt_locations_custom_post_meta_import_nonce', 'gmw_pt_locations_custom_post_meta_import_nonce' ); ?>
								<input type="submit" id="import-custom-post-meta-submit" class="button-secondary" value="<?php _e( "Import", "GMW" ); ?>" />
								<script>
									jQuery(document).ready(function($) {
										$('#import-custom-post-meta-submit').click(function() {
											if ( $('#gmw-import-custom-field-lat').val() == '' || $('#gmw-import-custom-field-long').val() == '' ) {
												alert( 'You must have both Latitude and longitude field to be able to import locations' );
												return false;
											};
										});
									});
								</script>
							</p>
						</form>
		
					</div>
				</div>
			</div>
			<!-- .postbox -->
	
			<?php do_action( 'gmw_export_import_after_pt_locations_to_custom_post_meta' ); ?>
		
			<?php do_action( 'gmw_export_import_before_pt_locations_to_csv' ); ?>
			
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<?php _e('Export/Import Post Type Locations using CSV File', 'GMW'); ?>
					</h3>
					
					<div class="inside">
						
						<p>
						   <?php _e( "Export/Import locations using CSV file should be used for backup purposes only. The best method for export/import post types locations between different sites is by using the post_meta export/import forms above. ", "GMW" ); ?><br />
						   <?php _e( "Export/import locations between different sites using CSV file can only be done when the posts and thier post ID are equal on both the original site and the target site.", "GMW" ); ?><br />
						   <?php printf( __( "By exporting the locations to CSV file the plugin simply backup GEO my WP's custom database table ( %splaces_locator ) when each location has the post ID it belongs to.", "GMW" ), $wpdb->prefix ); ?><br />
						   <?php _e( "And so, when importing the location back from the CSV file the posts from the original site must exists with thier original post ID in the target site. ", "GMW" ); ?><br />
						<p>
							
					</div>
				</div>
				
				<div class="inside-middle">
				
					<h3>
						<?php _e('Export Post Type Locations to CSV File', 'GMW'); ?>
					</h3>
					
					<div class="inside">
						
						<p><?php _e( "Click the \"Generate CSV\" button to created a CSV back file of the post types locations created on this site.", "GMW" ); ?></p>
						<p>
							<form method="post" id="gmw_csv_export_pt_locations">
							
								<input type="hidden" name="gmw_action" value="pt_locations_csv_export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'GMW' ); ?>" class="button-secondary"/>
							</form>
						</p>
						
					</div><!-- .inside -->
				</div>
				
				<div class="inside-bottom">
						
					<h3>
						<span><?php _e( 'Import Post Type Locations From CSV File', 'GMW' ); ?> </span>
					</h3>
					<div class="inside">
		
						<p>
							<?php _e( "Choose the CSV file you would like to import and click the \"Import\" button to import the locations into GEO my WP's database table.", "GMW" ); ?>
						</p>
		
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<p>
								<input type="file" name="import_csv_file" />
							</p>						
							<p>
								<input type="hidden" name="gmw_action" value="pt_locations_csv_import" />
								<?php wp_nonce_field( 'gmw_pt_locations_csv_import_nonce', 'gmw_pt_locations_csv_import_nonce' ); ?>
								<?php submit_button( __( 'Import', 'GMW' ), 'secondary', 'submit', false ); ?>
							</p>
						</form>
		
					</div>
				</div>
	
			</div>
				
			<?php do_action( 'gmw_export_import_after_pt_locations_to_csv' ); ?>
			
		</div>
		<?php /* ?>
		<div id="settings-ei_users_locations" class="gmw-settings-panel gmw-import-export-page-tab-wrapper">
			
			<?php do_action( 'gmw_export_import_before_fl_locations_to_csv' ); ?>
			
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<?php _e('Export/Import Users Locations using CSV File', 'GMW'); ?>
					</h3>
					
					<div class="inside">
						
						<p>
						   <?php _e( "Export/Import locations using CSV file should be used for backup purposes only. The best method for export/import post types locations between different sites is by using the post_meta export/import forms above. ", "GMW" ); ?><br />
						   <?php _e( "Export/import locations between different sites using CSV file can only be done when the posts and thier post ID are equal on both the original site and the target site.", "GMW" ); ?><br />
						   <?php printf( __( "By exporting the locations to CSV file the plugin simply backup GEO my WP's custom database table ( %splaces_locator ) when each location has the post ID it belongs to.", "GMW" ), $wpdb->prefix ); ?><br />
						   <?php _e( "And so, when importing the location back from the CSV file the posts from the original site must exists with thier original post ID in the target site. ", "GMW" ); ?><br />
						<p>
							
					</div>
				</div>
				
				<div class="inside-middle">
				
					<h3>
						<?php _e('Export User Locations to CSV File', 'GMW'); ?>
					</h3>
					
					<div class="inside">
						
						<p><?php _e( "Click the \"Generate CSV\" button to created a CSV back file of the post types locations created on this site.", "GMW" ); ?></p>
						<p>
							<form method="post" id="gmw_csv_export_pt_locations">
							
								<input type="hidden" name="gmw_action" value="pt_locations_csv_export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'GMW' ); ?>" class="button-secondary"/>
							</form>
						</p>
						
					</div><!-- .inside -->
				</div>
				
				<div class="inside-bottom">
						
					<h3>
						<span><?php _e( 'Import User Locations From CSV File', 'GMW' ); ?> </span>
					</h3>
					<div class="inside">
		
						<p>
							<?php _e( "Choose the CSV file you would like to import and click the \"Import\" button to import the locations into GEO my WP's database table.", "GMW" ); ?>
						</p>
		
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
							<p>
								<input type="file" name="import_csv_file" />
							</p>						
							<p>
								<input type="hidden" name="gmw_action" value="pt_locations_csv_import" />
								<?php wp_nonce_field( 'gmw_pt_locations_csv_import_nonce', 'gmw_pt_locations_csv_import_nonce' ); ?>
								<?php submit_button( __( 'Import', 'GMW' ), 'secondary', 'submit', false ); ?>
							</p>
						</form>
		
					</div>
				</div>
	
			</div>
			
			<?php do_action( 'gmw_export_import_after_pt_locations_to_csv' ); ?>
		</div>
			*/ ?>
		<div id="settings-plugins_importer" class="gmw-settings-panel gmw-import-export-page-tab-wrapper">
			
			<?php do_action( 'gmw_export_import_before_other_plugins_import' ); ?>
			
			<div class="postbox">
				
				<div class="inside-top">
					<h3>
						<?php _e( 'Import Locations From Other Plugins', 'GMW' ); ?>
					</h3>
					
					<div class="inside">
						
						<p>
						   <?php _e( "Use the forms below to import locations created by different plugins into GEO my WP.", "GMW" ); ?><br />
						<p>
							
					</div>
				</div>
					
				<div class="inside-middle">
				
					<?php $store_locator_status = ( is_plugin_active( 'store-locator/store-locator.php' ) ) ? 'active' : 'inactive' ; ?>
					<h3>
						<?php printf( __( 'Store Locator Plugin %s', 'GMW' ), ( $store_locator_status == 'inactive' ) ? ' - <em style="color:red;font-size:12px;">Plugin Inactive</em>' : '' );?>
					</h3>
					
					<div class="inside">
						
						<p>
							<?php _e( "Use this form to import locations created by <a href=\"https://wordpress.org/plugins/store-locator/\" target=\"_blank\">Store Locator plugin</a>.", "GMW" ); ?><br />
							<?php _e( "Because Store Locator plugin doesn't use post type with its locations new post will need to be created for each location being imported.", "GMW" ); ?><br />
							<?php _e( "To import the locations from Store Locator plugin first choose from the drop-down menu the post type you would like be used when importing the locations then click the \"Import\" button.", "GMW" ); ?><br />
						
						</p>
						<p>
							<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">	
							
								<p>
									<?php _e( "Choose a post type:", "GMW" ); ?>
								</p>
								<select id="post-type-selector" name="post_type" <?php if ( $store_locator_status == 'inactive' ) echo 'disabled="disabled"'; ?>>
									<?php foreach ( get_post_types() as $post_type ) { ?>
										<option value="<?php echo $post_type; ?>"><?php echo $post_type; ?></option>
									<?php } ?>
								</select>				
								<p>
									<input type="hidden" name="gmw_action" value="store_locator_import" />
									<?php wp_nonce_field( 'gmw_store_locator_import_nonce', 'gmw_store_locator_import_nonce' ); ?>
									<input type="submit" class="button-secondary" <?php if ( $store_locator_status == 'inactive' ) echo 'disabled="disabled"'; ?> value="<?php _e( 'Import', 'GMW' ); ?>" />
								</p>
							</form>
						</p>
						
					</div><!-- .inside -->
				</div>
				
				<div class="inside-middle">
				
					<?php $mappress_status = ( is_plugin_active( 'mappress-google-maps-for-wordpress/mappress.php' ) ) ? 'active' : 'inactive' ; ?>
					<h3>
						<?php printf( __( 'Map-Press Plugin %s', 'GMW' ), ( $mappress_status == 'inactive' ) ? ' - <em style="color:red;font-size:12px;">Plugin Inactive</em>' : '' );?>
					</h3>
					
					<div class="inside">
						
						<p>
							<?php _e( "Use this form to import locations created by <a href=\"https://wordpress.org/plugins/mappress-google-maps-for-wordpress/\" target=\"_blank\">MapPress Easy Google Maps</a> plugin.", "GMW" ); ?><br />
							<br />
							<span class="description">
								<?php _e( "*MapPress allows to creates multiple locations per post where GEO my WP does not have this capability yet. For this reason at the moment GEO my WP will only import the first location of each post. ", "GMW" ); ?>
								<br />
								<?php _e( "And so, if your posts have multiple locations they will be ignored except for the first one that will be imported. ", "GMW" ); ?>
								<br />
								<?php _e( "This issue might be improved in the future.", "GMW" ); ?>
							</span>
						<p>
							<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">					
								<p>
									<input type="hidden" name="gmw_action" value="mappress_import" />
									<?php wp_nonce_field( 'gmw_mappress_import_nonce', 'gmw_mappress_import_nonce' ); ?>
									<input type="submit" class="button-secondary" <?php if ( $mappress_status == 'inactive' ) echo 'disabled="disabled"'; ?> value="<?php _e( 'Import', 'GMW' ); ?>" />
								</p>
							</form>
						</p>
						
					</div><!-- .inside -->
				</div>
			</div>
		
		</div>	
			
			<!-- end of addon check -->
		<?php } ?>	
			
		<?php do_action( 'gmw_export_import_bottom' ); ?>
	
		<?php $current_tab = ( isset( $_COOKIE['gmw_admin_tab'] ) ) ? $_COOKIE['gmw_admin_tab'] : false; ?>
	</div>
 	<script type="text/javascript">
		jQuery(document).ready(function($) {
         	if ( '<?php echo $current_tab; ?>' != false ) { 
         		jQuery('#<?php echo $current_tab; ?>').click();
         	}
		});
	</script>
<?php
}
add_action( 'gmw_tools_tab_import_export', 'gmw_output_import_export_tab' );
 
/**
 * Export data to json file
 *
 * @since 2.5
 * @return void
 */
function gmw_export_data() {

	//make sure at lease one checkbox is checked
	if ( empty( $_POST['export_item'] ) )
		wp_die( __( "You must check at least one checkbox of an item that you'd like to export.", "GMW" ) );
	 
	//check for nonce
	if ( empty( $_POST['gmw_export_data_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_export_data_nonce'], 'gmw_export_data_nonce' ) )
		return;

	$export 			= array();
	$export['addons'] 	= get_option( 'gmw_addons' );
	 
	//export settings
	if ( in_array( 'settings', $_POST['export_item'] ) ) {
		$export['options'] = get_option( 'gmw_options' );
	}
	 
	//export forms
	if ( in_array( 'forms', $_POST['export_item'] ) ) {
		$export['forms'] = get_option( 'gmw_forms' );
	}
	 
	//export licenses
	if ( in_array( 'licenses', $_POST['export_item'] ) ) {
		$export['license_keys'] = get_option( 'gmw_license_keys' );
		$export['statuses'] 	= get_option( 'gmw_premium_plugin_status' );
	}
	 
	ignore_user_abort( true );

	set_time_limit( 30 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=gmw-data-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $export );
	
	exit;

}
add_action( 'gmw_export_data', 'gmw_export_data' );

/**
 * Import data from a json file
 *
 * @since 2.5
 * @return void
 */
function gmw_import_data() {

	//make sure at least one checkbox is checked
	if ( empty( $_POST['import_item'] ) )
		wp_die( __( "You must check at least on checkbox of an item that you'd like to import", 'GMW' ) );
	
	//look for nonce
	if ( empty( $_POST['gmw_import_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_import_nonce'], 'gmw_import_nonce' ) )
		return;
 
	//get file
	$import_file = $_FILES['import_file']['tmp_name'];

	//abort if not file uploaded
	if ( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'GMW' ) );
	}

	// Retrieve the data from the file and convert the json object to an array
	$import_data = gmw_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	//import add-ons
	if ( isset( $import_data['addons'] ) ) {
		update_option( 'gmw_addons', $import_data['addons'] );
	}
	 
	//import settings
	if ( in_array( 'settings', $_POST['import_item'] ) && isset( $import_data['options'] ) ) {
		update_option( 'gmw_options', $import_data['options'] );
	}
	
	//import forms
	if ( in_array( 'forms', $_POST['import_item'] ) && isset( $import_data['forms'] ) ) {
		update_option( 'gmw_forms', $import_data['forms'] );
	}
	
	//import licenses
	if ( in_array( 'licenses', $_POST['import_item'] ) ) {
		if ( isset( $import_data['license_keys'] ) ) {
			update_option( 'gmw_license_keys', $import_data['license_keys'] );
		}
		if ( isset( $import_data['statuses'] ) ) {
			update_option( 'gmw_premium_plugin_status', $import_data['statuses'] );
		}
	}

	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=data_imported&gmw_notice_status=updated' ) ); 
		exit;

}
add_action( 'gmw_import_data', 'gmw_import_data' );

/**
 * Export location into post_meta
 *
 * @since 2.5
 * @return void
 */
function gmw_pt_locations_post_meta_export() {
	 
	//look for nonce
	if ( empty( $_POST['gmw_pt_locations_post_meta_export_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_pt_locations_post_meta_export_nonce'], 'gmw_pt_locations_post_meta_export_nonce' ) )
		return;

	global $wpdb;
	 
	//select location from GEO my WP database table
	$locations = $wpdb->get_results("
			SELECT gmwLocations.post_id, gmwLocations.feature, gmwLocations.street, gmwLocations.apt, gmwLocations.city, gmwLocations.state,
			gmwLocations.state_long, gmwLocations.zipcode, gmwLocations.country, gmwLocations.country_long, gmwLocations.address,
			gmwLocations.formatted_address, gmwLocations.lat, gmwLocations.long, gmwLocations.phone, gmwLocations.fax, gmwLocations.email, gmwLocations.website, gmwLocations.map_icon
			FROM `{$wpdb->prefix}places_locator` gmwLocations INNER JOIN {$wpdb->posts} wpposts
			ON gmwLocations.post_id = wpposts.ID", ARRAY_A );
	 
	//abort if no locations found
	if ( empty( $locations ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=no_pt_locations_to_export&gmw_notice_status=error' ) );
		exit;
	}
	 
	//update locations into custom fields
	foreach ( $locations as $location ) {
		update_post_meta( $location['post_id'], 'gmw_pt_location', $location );
	}
	 
	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=pt_locations_post_meta_exported&gmw_notice_status=updated' ) );
	exit;

}
add_action( 'gmw_pt_locations_post_meta_export', 'gmw_pt_locations_post_meta_export' );

/**
 * Import locations from post_meta
 *
 * @since 2.5
 * @return void
 */
function gmw_pt_locations_post_meta_import() {

	//look for nonce
	if ( empty( $_POST['gmw_pt_locations_post_meta_import_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_pt_locations_post_meta_import_nonce'], 'gmw_pt_locations_post_meta_import_nonce' ) )
		return;

	global $wpdb;
	 
	//get all custom fields with gmw location from database
	$posts_data = $wpdb->get_results("
			SELECT *
			FROM `{$wpdb->prefix}postmeta`
			WHERE `meta_key` = 'gmw_pt_location'", ARRAY_A );
	 
	//abort if no locations found
	if ( empty( $posts_data ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=no_pt_locations_to_import&gmw_notice_status=error' ) );
		exit;
	}
	 
	//loop through locations
	foreach ( $posts_data as $post_data ) {
		
		$postID 		= $post_data['post_id'];	
		
		//get location from custom meta
		$location_data 	= unserialize( $post_data['meta_value'] );

		$location_data['post_id']  	  = $postID;
		$location_data['post_title']  = get_the_title( $postID );
		$location_data['post_status'] = get_post_status( $postID );
		$location_data['post_type']   = get_post_type( $postID );
			
		//insert location to database
		gmw_insert_pt_location_to_db( $location_data );
				
		//delete the post meta of the location
		delete_post_meta( $postID, 'gmw_pt_location' );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=pt_locations_imported&gmw_notice_status=updated' ) );
	exit;

}
add_action( 'gmw_pt_locations_post_meta_import', 'gmw_pt_locations_post_meta_import' );

/**
 * Import locations from post_meta
 *
 * @since 2.5
 * @return void
 */
function gmw_pt_locations_custom_post_meta_import() {

	//look for nonce
	if ( empty( $_POST['gmw_pt_locations_custom_post_meta_import_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_pt_locations_custom_post_meta_import_nonce'], 'gmw_pt_locations_custom_post_meta_import_nonce' ) )
		return;

	global $wpdb;

	//save fields into database
	$gmw_settings = get_option( 'gmw_options' );
	$gmw_settings['tools_page_options']['gmw_pt_import_custom_post_meta'] = $_POST['gmw_post_meta'];
	
	update_option( 'gmw_options', $gmw_settings );
		
	$locationFields = array_filter( $_POST['gmw_post_meta'] );
	
	$where = $wpdb->prepare(" WHERE `meta_key` IN (" . str_repeat( "%s,", count( $locationFields ) - 1 ) . "%s )", $locationFields );
	header( 'Content-Type: text/csv; charset=utf-8' );
	
	//get all custom fields with gmw location from database
	$results = $wpdb->get_results("
			SELECT `post_id`, `meta_key`, `meta_value`
			FROM `{$wpdb->prefix}postmeta`
			{$where}
			ORDER BY `post_id`
			", ARRAY_A );
			
	$posts_data = array();
	
	//order array of locations
	foreach ( $results as $field_data ) {
		$posts_data[$field_data['post_id']]['post_id'] = $field_data['post_id'];
		$posts_data[$field_data['post_id']][array_search( $field_data['meta_key'], $locationFields )] = maybe_unserialize($field_data['meta_value']);	
	}

	//abort if no locations found
	if ( empty( $posts_data ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=no_pt_locations_to_import&gmw_notice_status=error' ) );
		exit;
	}

	//number of locations imported
	$imported = 0;
	
	//loop through locations
	foreach ( $posts_data as $key => $post_data ) {
		
		if ( empty( $post_data['lat'] ) || empty( $post_data['long'] ) )
			continue;
		
		$postID  = $post_data['post_id'];
		$results = $wpdb->get_row("
				SELECT `post_status`, `post_title`, `post_type`
				FROM `{$wpdb->posts}`
				WHERE `ID` = {$postID}
		");
	
		$posts_data[$key]['post_title']  = $results->post_title;
		$posts_data[$key]['post_status'] = $results->post_status;
		$posts_data[$key]['post_type']   = $results->post_type;
			
		if ( empty( $posts_data[$key]['address'] ) && !empty( $posts_data[$key]['formatted_address'] ) ) {
			$posts_data[$key]['address'] = $posts_data[$key]['formatted_address'];
		} elseif ( !empty( $posts_data[$key]['address'] ) && empty( $posts_data[$key]['formatted_address'] ) ) {
			$posts_data[$key]['formatted_address'] = $posts_data[$key]['address'];
		}
		
		$posts_data[$key] = array_merge( array(
				'post_id' 			=> '',
				'feature' 			=> 0,
				'post_status' 		=> '',
				'post_type' 		=> '',
				'post_title' 		=> '',
				'lat' 				=> '',
				'long' 				=> '',
				'street' 			=> '',
				'apt' 				=> '',
				'city' 				=> '',
				'state' 			=> '',
				'state_long' 		=> '',
				'zipcode' 			=> '',
				'country' 			=> '',
				'country_long' 		=> '',
				'address' 			=> '',
				'formatted_address' => '',
				'phone' 			=> '',
				'fax' 				=> '',
				'email' 			=> '',
				'website' 			=> '',
				'map_icon' 			=> 'deafult.png',
		), $posts_data[$key] );
				
		//insert location to database
		$data = gmw_insert_pt_location_to_db( $posts_data[$key] );
		
		if ( $data )
			$imported++;

	}

	if ( $imported == 0 ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=no_pt_locations_to_import&gmw_notice_status=error' ) );
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=pt_locations_imported&gmw_notice_status=updated' ) );
	}
	exit;

}
add_action( 'gmw_pt_locations_custom_post_meta_import', 'gmw_pt_locations_custom_post_meta_import' );

/**
 * Export all post types locations to a CSV file.
 *
 * @since 2.5
 * @return void
 */
function gmw_pt_locations_csv_export() {
	
	include_once GMW_PATH .'/includes/admin/tools/gmw-tools-class-pt-locations-csv-export.php';

	$locations_export = new GMW_PT_Locations_Export();

	$locations_export->export();
}
add_action( 'gmw_pt_locations_csv_export', 'gmw_pt_locations_csv_export' );

/**
 * Import data from a json file
 *
 * @since 2.5
 * @return void
 */
function gmw_pt_locations_csv_import() {

	if ( empty( $_POST['gmw_pt_locations_csv_import_nonce'] ) )
		return;

	if ( !wp_verify_nonce( $_POST['gmw_pt_locations_csv_import_nonce'], 'gmw_pt_locations_csv_import_nonce' ) )
		return;

	$import_file = $_FILES['import_csv_file']['tmp_name'];

	if ( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'GMW' ) );
	}

	$row 	 = 0;
	$col 	 = 0;
	$results = array();
	$handle  = @fopen($import_file, "r");

	if ( $handle ) {

		while ( ( $row = fgetcsv( $handle, 4096) ) !== false ) {
						
			if ( empty( $fields ) ) {
		
				$fields = $row;				
				$count  = count($fields);
				continue;
			}

			foreach ( $row as $k => $value ) {
				if ( $k < $count ) {
					$results[$col][$fields[$k]] = $value;
				}
			}
				
			$col++;
			unset( $row );
		}

		if ( !feof( $handle ) ) {
			echo "Error: unexpected fgets() failn";
		}
		fclose($handle);
	}
	
	if ( empty( $results ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=pt_locations_csv_import_failed&gmw_notice_status=error' ) );
		exit;
	}
	
	//Save information to database
	global $wpdb;
	
	foreach ( $results as $location ) {	
		$wpdb->replace( $wpdb->prefix . 'places_locator', $location );	
	}
	
	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=pt_locations_csv_imported&gmw_notice_status=updated' ) );
	exit;

}
add_action( 'gmw_pt_locations_csv_import', 'gmw_pt_locations_csv_import' );

/**
 * Import data from WordPress Store Locator plugin
 *
 * @since 2.5
 * @return void
 */
function gmw_store_locator_import() {

	//look for nonce
	if ( empty( $_POST['gmw_store_locator_import_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_store_locator_import_nonce'], 'gmw_store_locator_import_nonce' ) )
		return;
	
	global $wpdb;
	
	//look for places_locator table
	$slTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}store_locator'", ARRAY_A );
	
	//abort if no table exist
	if ( count( $slTable ) == 0 ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=store_locator_import_failed_no_table&gmw_notice_status=error' ) );
		exit;
	}

	//array of states
	$state_list = ARRAY(
			'AL'=>"Alabama",
			'AK'=>"Alaska",
			'AZ'=>"Arizona",
			'AR'=>"Arkansas",
			'CA'=>"California",
			'CO'=>"Colorado",
			'CT'=>"Connecticut",
			'DE'=>"Delaware",
			'DC'=>"District Of Columbia",
			'FL'=>"Florida",
			'GA'=>"Georgia",
			'HI'=>"Hawaii",
			'ID'=>"Idaho",
			'IL'=>"Illinois",
			'IN'=>"Indiana",
			'IA'=>"Iowa",
			'KS'=>"Kansas",
			'KY'=>"Kentucky",
			'LA'=>"Louisiana",
			'ME'=>"Maine",
			'MD'=>"Maryland",
			'MA'=>"Massachusetts",
			'MI'=>"Michigan",
			'MN'=>"Minnesota",
			'MS'=>"Mississippi",
			'MO'=>"Missouri",
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",
			'OK'=>"Oklahoma",
			'OR'=>"Oregon",
			'PA'=>"Pennsylvania",
			'RI'=>"Rhode Island",
			'SC'=>"South Carolina",
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",
			'TX'=>"Texas",
			'UT'=>"Utah",
			'VT'=>"Vermont",
			'VA'=>"Virginia",
			'WA'=>"Washington",
			'WV'=>"West Virginia",
			'WI'=>"Wisconsin",
			'WY'=>"Wyoming"
	);
		
	$post_type = ( isset( $_POST['post_type'] ) ) ? $_POST['post_type'] : 'post';
	
	//get location from store locator table in database
	$locations = $wpdb->get_results("
			SELECT
			`sl_store` as `post_title`,
			`sl_address` as `street`,
			`sl_address2` as `street2`,
			`sl_city` as `city`,
			`sl_state` as `state`,
			`sl_zip` as `zipcode`,
			`sl_country` as `country`,
			`sl_latitude` as `lat`,
			`sl_longitude` as `long`,
			`sl_phone` as `phone`,
			`sl_fax` as `fax`,
			`sl_email` as `email`,
			`sl_url` as `website`,
			`sl_tags` as `tags`,
			`sl_description` as `content`
			FROM {$wpdb->prefix}store_locator", ARRAY_A
	);

	foreach ( $locations as $location ) {
		
		// Create post object
		$new_post = apply_filters( 'gmw_store_locator_import_new_post_args', array(
				'post_title'    => $location['post_title'],
				'post_content'  => $location['content'],
				'post_type'		=> $post_type,
				'post_status'   => 'publish',
				'post_author'   => 1,
		));
		
		//create new post
		$postID = wp_insert_post( $new_post );
		
		$address = array();
		
		//build complete adress field from address components
		foreach ( $location as $key => $value ) {
			if ( in_array( $key, array( 'street', 'street2', 'city', 'state', 'zipcode', 'country' ) ) && isset( $value ) && $value != '' && $value != ' ' ) {
				$address[] = $value;
			}
		}
				
		$state = strtoupper( $location['state'] );
		
		$location['post_id'] 		   	= $postID;
		$location['post_status'] 	   	= 'publish';
		$location['post_type'] 		   	= $post_type;
		$location['state_long'] 	   	= ( isset( $state_list[$state] ) && array_key_exists( $state, $state_list ) ) ? $state_list[$state] : '';
		$location['country_long'] 	   	= '';
		$location['address'] 			= implode( ' ', $address );
		$location['formatted_address'] 	= $location['address'];
		$location['feature']			= 0;
		$location['apt']				= '';
		$location['map_icon']			= 'deafult.png';
		
		//update GEO my WP table in database
		gmw_replace_pt_location_in_db( $location );	
	
	}
	
	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=store_locator_imported&gmw_notice_status=updated' ) );
	exit;

}
add_action( 'gmw_store_locator_import', 'gmw_store_locator_import' );

/**
 * Import data from WordPress Store Locator plugin
 *
 * @since 2.5
 * @return void
 */
function gmw_mappress_import() {

	//look for nonce
	if ( empty( $_POST['gmw_mappress_import_nonce'] ) )
		return;

	//varify nonce
	if ( !wp_verify_nonce( $_POST['gmw_mappress_import_nonce'], 'gmw_mappress_import_nonce' ) )
		return;

	global $wpdb;
	
	//look for places_locator table
	$slTable = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}mappress_maps'", ARRAY_A );

	//abort if no table exist
	if ( count( $slTable ) == 0 ) {
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=mappress_import_failed_no_table&gmw_notice_status=error' ) );
		exit;
	}

	//get locations from Map-press table in database
	$locations = $wpdb->get_results("
			SELECT DISTINCT mposts.*, mmaps.obj
			FROM {$wpdb->prefix}mappress_posts mposts
			INNER JOIN {$wpdb->prefix}mappress_maps mmaps
			ON mposts.mapid = mmaps.mapid
			GROUP BY mposts.postid", ARRAY_A
			);

	foreach ( $locations as $location ) {
		
		$loc_details = unserialize( $location['obj'] );
	
		if ( !empty( $loc_details->pois ) && $loc_details->center['lat'] != '0' && $loc_details->center['lng'] != '0' ) {
						
			$postID = $location['postid'];

			$location_data['post_id'] 			= $postID;
			$location_data['feature']			= 0;
			$location_data['post_status']		= get_post_status( $postID );
			$location_data['post_type']			= get_post_type( $postID );
			$location_data['post_title']			= get_the_title( $postID );
			$location_data['lat']				= $loc_details->center['lat'];
			$location_data['long']				= $loc_details->center['lng'];
			$location_data['street']				= '';
			$location_data['apt']				= '';
			$location_data['city']				= '';
			$location_data['state']				= '';
			$location_data['state_long']			= '';
			$location_data['zipcode']			= '';
			$location_data['country']			= '';
			$location_data['country_long']		= '';
			$location_data['address']			= $loc_details->pois[0]->address;
			$location_data['formatted_address'] 	= $loc_details->pois[0]->correctedAddress;
			$location_data['phone']				= '';
			$location_data['fax']				= '';
			$location_data['email']				= '';
			$location_data['website']			= '';
			$location_data['map_icon']			= 'default.png';
			
			//insert location to database
			gmw_insert_pt_location_to_db( $location_data );

		}
	}

	wp_safe_redirect( admin_url( 'admin.php?page=gmw-tools&tab=import_export&gmw_notice=mappress_imported&gmw_notice_status=updated' ) );
	exit;

}
add_action( 'gmw_mappress_import', 'gmw_mappress_import' );

/**
 * GMW Function - add notice messages
 *
 * @access public
 * @since 2.5
 * @author Eyal Fitoussi
 *
 */
function gmw_import_export_notices_messages( $messages ) {

	$messages['data_imported'] 					 		= __( 'Data successfully imported.', 'GMW' );
	$messages['pt_locations_post_meta_exported'] 		= __( 'Locations successfully exported to post_meta.', 'GMW' );
	$messages['no_pt_locations_to_export'] 		 		= __( 'No Locations found. Nothing was exported', 'GMW' );
	$messages['no_pt_locations_to_import'] 		 		= __( 'No Locations found. Nothing was imported.', 'GMW' );
	$messages['pt_locations_imported'] 			 		= __( 'locations successfully imported.', 'GMW' );
	$messages['pt_locations_csv_imported'] 	 	 		= __( 'locations successfully imported.', 'GMW' );
	$messages['pt_locations_csv_import_failed']  		= __( 'Import failed. No locations found.', 'GMW' );
	$messages['store_locator_import_failed_no_table']   = __( "Import failed! The database table wp_store_locator does not exist in database.", 'GMW' );
	$messages['store_locator_imported'] 			 	= __( 'locations successfully imported.', 'GMW' );
	$messages['mappress_import_failed_no_table']   		= __( "Import failed! The database table mappress_maps does not exist in database.", 'GMW' );
	$messages['mappress_imported'] 			 			= __( 'locations successfully imported.', 'GMW' );
	$messages['data_reset'] 			 				= __( 'Data successfully removed.', 'GMW' );
	
	return $messages;
}
add_filter( 'gmw_admin_notices_messages', 'gmw_import_export_notices_messages' );
