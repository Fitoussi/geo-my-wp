<?php
/**
 * GEO my WP Import/Export data tab.
 *
 * @since 2.5
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Export/Import tab output
 *
 * @access public
 *
 * @since 2.5
 *
 * @author Eyal Fitoussi
 */
function gmw_import_export_data_tab() {
	?>

	<?php do_action( 'gmw_import_export_data_start' ); ?>

	<?php do_action( 'gmw_import_export_before_data_export' ); ?>

	<div class="gmw-settings-panel gmw-export-data-panel">

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=data' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Export Data', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Check the checkboxes of the items that you would like to export, then click the "Export" button to generate a .json file.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<div class="gmw-settings-panel-checkboxes">

							<label>
								<input type="checkbox" class="cb-export-item" name="export_item[]" value="settings" checked="checked" />

								<?php esc_html_e( 'Settings', 'geo-my-wp' ); ?>

								<em class="description">
									<?php esc_html_e( '( GEO my WP and its extensions )', 'geo-my-wp' ); ?>
								</em>
							</label>

							<label>
								<input type="checkbox" class="cb-export-item" name="export_item[]" value="licenses" checked="checked" />

								<?php esc_html_e( 'License Keys', 'geo-my-wp' ); ?>

								<em class="description">
									<?php esc_html_e( '( exported license keys should be imported back to this site only )', 'geo-my-wp' ); ?>
								</em>
							</label>
						</div>
						<p>
							<input type="hidden" name="gmw_action" value="export_data" />

							<?php wp_nonce_field( 'gmw_export_data_nonce', 'gmw_export_data_nonce' ); ?>

							<?php
							submit_button(
								esc_html__( 'Export', 'geo-my-wp' ),
								'gmw-settings-action-button button-primary',
								'submit',
								false,
								array(
									'onclick' => "if ( !jQuery('.cb-export-item').is(':checked') ) { alert('You must check at least one item that you would like to export.'); return false; }",
								)
							);
							?>
						</p>
					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<?php do_action( 'gmw_import_export_before_data_import' ); ?>

	<div class="gmw-settings-panel gmw-import-data-panel">

		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=data' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Import Data', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Select a .json file and check the items that you would like to import.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<p><input type="file" name="import_file" /></p>

						<strong><?php esc_html_e( 'Items to import', 'geo-my-wp' ); ?></strong>

						<div class="gmw-settings-panel-checkboxes">
							<label>
								<input type="checkbox" class="cb-import-item" name="import_item[]" value="settings" checked="checked" />
								<?php esc_html_e( 'Settings', 'geo-my-wp' ); ?>
							</label>

							<label>
								<input type="checkbox" class="cb-import-item" name="import_item[]" value="licenses" checked="checked" />
								<?php esc_html_e( 'License Keys', 'geo-my-wp' ); ?>
							</label>
						</div>
						<p>
							<input type="hidden" name="gmw_action" value="import_data" />

							<?php wp_nonce_field( 'gmw_import_data_nonce', 'gmw_import_data_nonce' ); ?>

							<?php
							submit_button(
								esc_html__( 'Import', 'geo-my-wp' ),
								'gmw-settings-action-button button-primary',
								'submit',
								false,
								array(
									'onclick' => "if ( !jQuery('.cb-import-item').is(':checked') ) { alert('You must check at least one item that you would like to import.'); return false; }",
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
add_action( 'gmw_import_export_data_tab', 'gmw_import_export_data_tab' );

/**
 * Export data to json file
 *
 * @since 2.5
 *
 * @return void
 */
function gmw_export_data() {

	// make sure at lease one checkbox is checked.
	if ( empty( $_POST['export_item'] ) ) {
		wp_die( esc_html__( 'You must check at least one checkbox of an item that you would like to export.', 'geo-my-wp' ) );
	}

	// check for nonce.
	if ( empty( $_POST['gmw_export_data_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_export_data_nonce'] ) ), 'gmw_export_data_nonce' ) ) { // WPCS: CSRF ok, sanitization ok.
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$export = array();

	// export settings.
	if ( in_array( 'settings', $_POST['export_item'] ) && function_exists( 'gmw_get_options_group' ) ) { // WPCS: CSRF ok, sanitization ok.
		$export['options'] = gmw_get_options_group();
	}

	// export licenses.
	if ( in_array( 'licenses', $_POST['export_item'] ) ) { // WPCS: CSRF ok, sanitization ok.
		$export['license_keys'] = get_option( 'gmw_license_keys' );
		$export['statuses']     = get_option( 'gmw_premium_plugin_status' );
	}

	ignore_user_abort( true );

	set_time_limit( 30 );

	nocache_headers();

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=gmw-data-export-' . gmdate( 'm-d-Y' ) . '.json' );
	header( 'Expires: 0' );

	echo wp_json_encode( $export );

	exit;
}
add_action( 'gmw_export_data', 'gmw_export_data' );

/**
 * Import data from a json file
 *
 * @since 2.5
 *
 * @return void
 */
function gmw_import_data() {

	// make sure at least one checkbox is checked and that file name exists.
	if ( empty( $_POST['import_item'] ) || empty( $_FILES['import_file']['tmp_name'] ) ) {
		wp_die( esc_html__( 'You must check at least on checkbox of an item that you would like to import', 'geo-my-wp' ) );
	}

	// look for nonce.
	if ( empty( $_POST['gmw_import_data_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_import_data_nonce'] ) ), 'gmw_import_data_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// get file.
	$import_file = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );

	// abort if not file uploaded.
	if ( empty( $import_file ) ) {
		wp_die( esc_html__( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	// Retrieve the data from the file and convert the json object to an array.
	// phpcs:disable
	$import_data = gmw_object_to_array( json_decode( file_get_contents( $import_file ) ) );
	// phpcs:enable

	// import settings.
	if ( in_array( 'settings', $_POST['import_item'], true ) && isset( $import_data['options'] ) ) { // WPCS: CSRF ok, sanitization ok.
		update_option( 'gmw_options', $import_data['options'] );
	}

	// import licenses.
	if ( in_array( 'licenses', $_POST['import_item'], true ) ) { // WPCS: CSRF ok, sanitization ok.

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
