<?php
/**
 * GEO my WP Import/Export locations tables.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Locations table tab output.
 */
function gmw_import_export_location_tables_tab() {
	?>
	<?php do_action( 'gmw_import_export_location_tables_start' ); ?>

	<?php do_action( 'gmw_import_export_before_location_table_export' ); ?>

	<div class="gmw-settings-panel gmw-admin-notice-box gmw-admin-notice-warning">

		<h3 class="gmw-admin-notice-title">
			<?php esc_html_e( 'Please Note', 'geo-my-wp' ); ?>
		</h3>

		<div class="gmw-admin-notice-content">
			<div class="gmw-admin-notice-description">
				<span>
					<?php esc_html_e( 'Transferring the data of the locations and locationmeta tables between different sites using a CSV file can only be done when the locations and their object ID are matching on both the original and the target site.', 'geo-my-wp' ); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="gmw-settings-panel gmw-export-location-data-panel">

		<form method="post"
			action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=location_tables' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title">
					<?php esc_html_e( 'Export Locations database tables to CSV File', 'geo-my-wp' ); ?>
				</legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">

						<?php esc_html_e( 'Click the buttons to generate CSV files with the location data.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<input type="hidden" name="gmw_action" value="export_location_tables_to_csv" />

						<?php wp_nonce_field( 'gmw_export_location_tables_nonce', 'gmw_export_location_tables_nonce' ); ?>

						<?php submit_button( __( 'Export Locations Table', 'geo-my-wp' ), 'gmw-settings-action-button button-primary', 'export_locations_table', false ); ?>

						<?php submit_button( __( 'Export Location Meta Table', 'geo-my-wp' ), 'gmw-settings-action-button button-primary', 'export_locationmeta_table', false ); ?>
					</div>

				</div>
			</fieldset>

		</form>

	</div>

	<?php do_action( 'gmw_import_export_before_location_tables_import' ); ?>

	<div class="gmw-settings-panel gmw-import-location-data-panel">

		<form method="post" enctype="multipart/form-data"
			action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=location_tables' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title">
					<?php esc_html_e( 'Import Location Data From CSV File', 'geo-my-wp' ); ?>
				</legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Select the type of data that you would like to import using the radio buttons then select the CSV file that you would like to import.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<div class="gmw-settings-panel-radio-buttons">

							<label>
								<input type="radio" name="location_tables_import" value="gmw_locations" checked="checked">
								<?php esc_html_e( 'Locations table', 'geo-my-wp' ); ?>
							</label>

							<label>
								<input type="radio" name="location_tables_import" value="gmw_locationmeta">
								<?php esc_html_e( 'Location meta table', 'geo-my-wp' ); ?>
							</label>
						</div>
						<p>
							<input type="file" name="import_csv_file" id="gmw-import-location-data" />
						</p>
						<p>
							<input type="hidden" name="gmw_action" value="import_location_tables_from_csv" />

							<?php wp_nonce_field( 'gmw_import_location_tables_from_csv_nonce', 'gmw_import_location_tables_from_csv_nonce' ); ?>

							<?php
							submit_button(
								__( 'Import', 'geo-my-wp' ),
								'gmw-settings-action-button button-primary',
								'submit',
								false,
								array(
									'onclick' => "if ( jQuery( '#gmw-import-location-data' ).get(0).files.length === 0 ) { alert( 'Select a file to import.' ); return false; }",
								)
							);
							?>
						</p>
					</div>

				</div>
			</fieldset>
		</form>

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

	// make sure at lease one checkbox is checked.
	if ( empty( $_POST['gmw_action'] ) || 'export_location_tables_to_csv' !== $_POST['gmw_action'] ) {
		return;
	}

	// check for nonce.
	if ( empty( $_POST['gmw_export_location_tables_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_export_location_tables_nonce'] ) ), 'gmw_export_location_tables_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	require_once dirname( __FILE__ ) . '/../class-gmw-location-tables-export.php';

	if ( ! class_exists( 'GMW_Locations_Table_Export' ) ) {
		wp_die( esc_html__( 'GMW_Locations_Table_Export class cannot be found.', 'geo-my-wp' ) );
	}

	if ( ! empty( $_POST['export_locations_table'] ) ) {

		$locations_export = new GMW_Locations_Table_Export();

		$locations_export->export();
	}

	if ( ! class_exists( 'GMW_Locationmeta_Table_Export' ) ) {
		wp_die( esc_html__( 'GMW_Locationmeta_Table_Export class cannot be found.', 'geo-my-wp' ) );
	}

	if ( ! empty( $_POST['export_locationmeta_table'] ) ) {

		$locationmeta_export = new GMW_Locationmeta_Table_Export();

		$locationmeta_export->export();
	}
}
add_action( 'gmw_export_location_tables_to_csv', 'export_location_tables_to_csv' );

/**
 * Import CSV to location tables.
 */
function gmw_import_location_tables_from_csv() {

	if ( empty( $_POST['gmw_import_location_tables_from_csv_nonce'] ) || empty( $_FILES['import_csv_file']['tmp_name'] ) || empty( $_POST['location_tables_import'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_import_location_tables_from_csv_nonce'] ) ), 'gmw_import_location_tables_from_csv_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! function_exists( 'gmw_csv_import' ) ) {
		wp_die( esc_html__( 'gmw_csv_import function not exist.', 'geo-my-wp' ) );
	}

	$file = sanitize_text_field( wp_unslash( $_FILES['import_csv_file']['tmp_name'] ) );

	if ( empty( $file ) ) {
		wp_die( esc_html__( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	gmw_csv_import( $file, sanitize_text_field( wp_unslash( $_POST['location_tables_import'] ) ) );
}
add_action( 'gmw_import_location_tables_from_csv', 'gmw_import_location_tables_from_csv' );
