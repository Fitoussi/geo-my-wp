<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Export/Import tab output
 *
 * @access public
 * @since 2.5
 * @author Eyal Fitoussi
 */
function gmw_import_export_data_tab() {
?>	
	
	<?php do_action( 'gmw_import_export_data_start' ); ?>

	<?php do_action( 'gmw_import_export_before_data_export' ); ?>

	<div id="poststuff" class="metabox-holder">
		
		<div id="post-body">
		
			<div id="post-body-content">

				<div class="postbox ">
		
					<h3 class="hndle">
						<span><?php _e( 'Export Data', 'geo-my-wp' ); ?> </span>
					</h3>
					
					<div class="inside">
				    
					    <p>
					    	<?php _e( 'Check the checkboxes of the items that you would like to export, then click the "Export" button to generate a .json file.', 'geo-my-wp' ); ?>
					    </p>
					    
					    <form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=data' ); ?>">
							
							<p class="checkboxes">
								
								<label>
									<input type="checkbox" class="cb-export-item" name="export_item[]" value="settings" checked="checked" />
									
									<?php _e( 'Settings', 'geo-my-wp' ); ?>
									
									<em class="description">
										<?php _e( '( GEO my WP and its extensions )', 'geo-my-wp' ); ?>
									</em>
								</label>

								<lable>
									<input type="checkbox" class="cb-export-item" name="export_item[]" value="licenses" checked="checked" />
									
									<?php _e( 'License Keys', 'geo-my-wp' ); ?>
									
									<em class="description">
										<?php _e( '( exported license keys should be imported back to this site only )', 'geo-my-wp' ); ?>
									</em>
								</label>
							</p>
							<p>
								<input type="hidden" name="gmw_action" value="export_data" />
								
								<?php wp_nonce_field( 'gmw_export_data_nonce', 'gmw_export_data_nonce' ); ?>
								
								<?php submit_button( __( 'Export', 'geo-my-wp' ), 'secondary', 'submit', false, array( 
										'onclick' => "if ( !jQuery('.cb-export-item').is(':checked') ) { alert('You must check at least one item that you would like to export.'); return false; }" ) ); 
								?>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'gmw_import_export_before_data_import' ); ?>

	<div id="poststuff" class="metabox-holder">
		
		<div id="post-body">
		
			<div id="post-body-content">

				<div class="postbox ">
		
					<h3 class="hndle">
						<span><?php _e( 'Import Data', 'geo-my-wp' ); ?> </span>
					</h3>
					
					<div class="inside">
				    
					    <p>
							<?php _e( 'Choose the .json file ( can be generated using the "Export" form above ) and check the checkboxes of the items that you would like to import.', 'geo-my-wp' ); ?>
						</p>
					    
					    <form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=data' ); ?>">
							
							<p>
								<input type="file" name="import_file" />
							</p>

							<strong><?php _e( 'Items to import', 'geo-my-wp' ); ?></strong>

							<p class="checkboxes">
								<label>
									<input type="checkbox" class="cb-import-item" name="import_item[]" value="settings" checked="checked" />
									<?php _e( 'Settings', 'geo-my-wp' ); ?>
								</label>
																
								<label>
									<input type="checkbox" class="cb-import-item" name="import_item[]" value="licenses" checked="checked" /> 
									<?php _e( 'License Keys', 'geo-my-wp' ); ?>
								</label>
							</p>
							<p>
								<input type="hidden" name="gmw_action" value="import_data" />
								
								<?php wp_nonce_field( 'gmw_import_data_nonce', 'gmw_import_data_nonce' ); ?>
								
								<?php submit_button( __( 'Import', 'geo-my-wp' ), 'secondary', 'submit', false, array( 
										'onclick' => "if ( !jQuery('.cb-import-item').is(':checked') ) { alert('You must check at least one item that you would like to import.'); return false; }" ) ); 
								?>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'gmw_import_export_data_end' ); ?>
				
<?php
}
add_action( 'gmw_import_export_data_tab', 'gmw_import_export_data_tab' );

/**
 * Export data to json file
 *
 * @since 2.5
 * @return void
 */
function gmw_export_data() {

	// make sure at lease one checkbox is checked
	if ( empty( $_POST['export_item'] ) ) {
		wp_die( __( 'You must check at least one checkbox of an item that you would like to export.', 'geo-my-wp' ) );
	}
	 
	// check for nonce
	if ( empty( $_POST['gmw_export_data_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce
	if ( ! wp_verify_nonce( $_POST['gmw_export_data_nonce'], 'gmw_export_data_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$export 		  = array();
	//$export['addons'] = get_option( 'gmw_addons' );
	 
	// export settings
	if ( in_array( 'settings', $_POST['export_item'] ) && function_exists( 'gmw_get_options_group' ) ) {
		$export['options'] = gmw_get_options_group();
	}
	 	 
	// export licenses
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
	if ( empty( $_POST['import_item'] ) ) {
		wp_die( __( 'You must check at least on checkbox of an item that you would like to import', 'geo-my-wp' ) );
	}
	
	//look for nonce
	if ( empty( $_POST['gmw_import_data_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	//varify nonce
	if ( ! wp_verify_nonce( $_POST['gmw_import_data_nonce'], 'gmw_import_data_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}
 
	//get file
	$import_file = $_FILES['import_file']['tmp_name'];

	//abort if not file uploaded
	if ( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	// Retrieve the data from the file and convert the json object to an array
	$import_data = gmw_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	// import add-ons
	//if ( isset( $import_data['addons'] ) ) {
	//	update_option( 'gmw_addons', $import_data['addons'] );
	//}
	 
	// import settings
	if ( in_array( 'settings', $_POST['import_item'] ) && isset( $import_data['options'] ) ) {
		update_option( 'gmw_options', $import_data['options'] );
	}
		
	// import licenses
	if ( in_array( 'licenses', $_POST['import_item'] ) ) {
		if ( isset( $import_data['license_keys'] ) ) {
			update_option( 'gmw_license_keys', $import_data['license_keys'] );
		}
		if ( isset( $import_data['statuses'] ) ) {
			update_option( 'gmw_premium_plugin_status', $import_data['statuses'] );
		}
	}

	wp_safe_redirect( admin_url( 'admin.php?page=gmw-import-export&tab=data&gmw_notice=data_imported&gmw_notice_status=updated' ) ); 
		exit;

}
add_action( 'gmw_import_data', 'gmw_import_data' );
