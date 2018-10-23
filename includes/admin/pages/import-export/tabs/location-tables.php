<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

function gmw_import_export_location_tables_tab() {
?>	
	<?php do_action( 'gmw_import_export_location_tables_start' ); ?>

	<?php do_action( 'gmw_import_export_before_location_table_export' ); ?>

	<?php global $wpdb; ?>

	<div id="poststuff" class="metabox-holder">
		
		<div id="post-body">
		
			<div id="post-body-content">

				<div class="postbox ">
		
					<h3 class="hndle">
						<span><?php _e( 'Export/Import Location tables using CSV File', 'geo-my-wp' ); ?></span>
					</h3>

					<div class="inside">
						<p>
						   <?php _e( "Export/Import of the locations and locationmeta tables on this page should be used for backup purposes only.", "geo-my-wp" ); ?>
						</p>	
						<p>
						   <?php _e( "Transferring the data of the locations and locationmeta tables between different sites using a CSV file can only be done when the locations and their object ID are matching on both the original and the target site.", 'geo-my-wp' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="poststuff" class="metabox-holder">
		
		<div id="post-body">
		
			<div id="post-body-content">

				<div class="postbox ">
		
					<h3 class="hndle">
						<span><?php _e( 'Export Location Database Tables To CSV Files', 'geo-my-wp' ); ?></span>
					</h3>

					<div class="inside">
							
						<p>
							<?php _e( 'Click the buttons to generate CSV files with the location data.', 'geo-my-wp' ); ?>
						</p>
						
						<form method="post">
															
							<input type="hidden" name="gmw_action" value="export_location_tables_to_csv"/>

							<?php wp_nonce_field( 'gmw_export_location_tables_nonce', 'gmw_export_location_tables_nonce' ); ?>
							
							<?php submit_button( __( 'Export Locations Table', 'geo-my-wp' ), 'secondary', 'export_locations_table', false ) 
							?>

							<?php submit_button( __( 'Export Location Meta Table', 'geo-my-wp' ), 'secondary', 'export_locationmeta_table', false ) 
							?>	
						</form>

					</div><!-- .inside -->
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
						<span><?php _e( 'Import Locations Data From CSV File', 'geo-my-wp' ); ?></span>
					</h3>

					<div class="inside">
							
						<p>
							<?php _e( "Select the type of data that you would like to import using the radio button and choose the CSV file that you would like to import.", "geo-my-wp" ); ?>
						</p>
					
						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=location_tables' ); ?>">

							<p>
								<input type="radio" name="location_tables_import" value="gmw_locations" checked="checked">
								<?php _e( 'Locations table', 'geo-my-wp' ); ?>
								<input type="radio" name="location_tables_import" value="gmw_locationmeta"><?php _e( 'Location meta table', 'geo-my-wp' ); ?>
							</p>
							<p>
								<input type="file" name="import_csv_file" />
							</p>						
							<p>
								<input type="hidden" name="gmw_action" value="import_location_tables_from_csv" />

								<?php wp_nonce_field( 'gmw_import_location_tables_from_csv_nonce', 'gmw_import_location_tables_from_csv_nonce' ); ?>
								<?php submit_button( __( 'Import', 'geo-my-wp' ), 'secondary', 'submit', false ); ?>
							</p>
						</form>
						
					</div><!-- .inside -->
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'gmw_import_export_data_end' ); ?>
				
<?php
}
add_action( 'gmw_import_export_location_tables_tab', 'gmw_import_export_location_tables_tab' );

/**
 * Export location tables to CSV
 * 
 * @return [type] [description]
 */
function export_location_tables_to_csv() {
	
	// make sure at lease one checkbox is checked
	if ( empty( $_POST ) || $_POST['gmw_action'] != 'export_location_tables_to_csv' ) {
		return;
	}
	 
	// check for nonce
	if ( empty( $_POST['gmw_export_location_tables_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce
	if ( ! wp_verify_nonce( $_POST['gmw_export_location_tables_nonce'], 'gmw_export_location_tables_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	include( dirname(__FILE__).'/../class-gmw-location-tables-export.php' );

	if ( ! class_exists( 'GMW_Locations_Table_Export' ) ) {
		wp_die( __( 'GMW_Locations_Table_Export class cannot be found.', 'geo-my-wp' ) );
	}

	if ( ! empty( $_POST['export_locations_table'] ) ) {

		$locations_export = new GMW_Locations_Table_Export();

		$locations_export->export();
	}

	if ( ! class_exists( 'GMW_Locationmeta_Table_Export' ) ) {
		wp_die( __( 'GMW_Locationmeta_Table_Export class cannot be found.', 'geo-my-wp' ) );
	}

	if ( ! empty( $_POST['export_locationmeta_table'] ) ) {
		
		$locationmeta_export = new GMW_Locationmeta_Table_Export();

		$locationmeta_export->export();
	}
}
add_action( 'gmw_export_location_tables_to_csv', 'export_location_tables_to_csv' );

/**
 * Import CSV to location tables
 * 
 * @return [type] [description]
 */
function gmw_import_location_tables_from_csv() {

	if ( empty( $_POST['gmw_import_location_tables_from_csv_nonce'] ) ) {

		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! wp_verify_nonce( $_POST['gmw_import_location_tables_from_csv_nonce'], 'gmw_import_location_tables_from_csv_nonce' ) ) {

		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! function_exists( 'gmw_csv_import' ) )  {
		wp_die( __( 'gmw_csv_import function not exist.', 'geo-my-wp' ) );
	}

	$file = $_FILES['import_csv_file']['tmp_name'];

	if ( empty( $file ) ) {
		wp_die( __( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	gmw_csv_import( $file, $_POST['location_tables_import'] );
}
add_action( 'gmw_import_location_tables_from_csv', 'gmw_import_location_tables_from_csv' );
