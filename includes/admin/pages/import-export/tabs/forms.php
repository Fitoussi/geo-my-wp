<?php
/**
 * GEO my WP Import/Export forms.
 *
 * @since 3.5
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Export/Import forms tab output
 *
 * @access public
 *
 * @since 3.5
 *
 * @author Eyal Fitoussi
 */
function gmw_import_export_forms_tab() {
?>
	<?php do_action( 'gmw_import_export_forms_before_export' ); ?>

	<div class="gmw-settings-panel gmw-export-form-panel">

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=forms' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Export Forms', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Select the forms you would like to export then click the "Export" button to generate a .json file.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<?php $forms = gmw_get_forms(); ?>

						<?php if ( ! empty( $forms ) ) { ?>

							<div class="gmw-settings-panel-checkboxes" style="max-height: 300px;overflow-y: scroll;">
								<label>
									<input
										type="checkbox"
										class="cb-export-item"
										checked="checked"
										onchange="if ( jQuery( this ).is( ':checked' ) ) { jQuery( this ).closest( 'form' ).find( '.cb-export-item' ).prop( 'checked', true ); } else { jQuery( this ).closest( 'form' ).find( '.cb-export-item' ).prop( 'checked', false ); }"
									/>
									<?php esc_html_e( 'All forms', 'geo-my-wp' ); ?>
								</label>

								<?php foreach ( $forms as $form_id => $values ) { ?>

									<?php
									if ( empty( $values ) ) {
										continue;
									}
									?>

									<?php $title = $values['title'] . ' - ID ' . $values['ID'] . ' ( ' . $values['addon'] . ' ) '; ?>

									<label>
										<input
											type="checkbox"
											class="cb-export-item"
											name="gmw_forms[]"
											value="<?php echo esc_attr( $values['ID'] ); ?>"
											checked="checked"
										/>

										<?php echo esc_attr( $title ); ?>
									</label>

								<?php } ?>

							</div>
							<p>
								<input type="hidden" name="gmw_action" value="export_forms" />

								<?php wp_nonce_field( 'gmw_export_forms_nonce', 'gmw_export_forms_nonce' ); ?>

								<?php
								submit_button(
									esc_html__( 'Export', 'geo-my-wp' ),
									'gmw-settings-action-button button-primary',
									'submit',
									false,
									array(
										'onclick' => "if ( ! jQuery('.cb-export-item').is(':checked') ) { alert( 'You must check at least one form that you would like to export.' ); return false; }",
									)
								);
								?>
							</p>

						<?php } else { ?>

							<h4><?php esc_html_e( 'There are no forms to export', 'geo-my-wp' ); ?></h4>

						<?php } ?>

					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<?php do_action( 'gmw_import_export_before_forms_import' ); ?>

	<div class="gmw-settings-panel gmw-import-forms-panel">

		<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=forms' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Import Forms', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Select the .json file that you would like to import then click Import.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<p>
							<input type="file" name="import_file" id="gmw-import-forms-file" />
						</p>
						<p>
							<input type="hidden" name="gmw_action" value="import_forms" name="form_import_file" />

							<?php wp_nonce_field( 'gmw_import_forms_nonce', 'gmw_import_forms_nonce' ); ?>

							<?php
								submit_button(
									esc_html__( 'Import', 'geo-my-wp' ),
									'gmw-settings-action-button button-primary',
									'submit',
									false,
									array(
										'onclick' => "if ( jQuery( '#gmw-import-forms-file' ).get(0).files.length === 0 ) { alert( 'Select a file to import.' ); return false; }",
									)
								);
							?>
						</p>
					</div>
				</div>
			</fieldset>
		</form>

	</div>

	<?php do_action( 'gmw_import_export_forms_end' ); ?>

<?php
}
add_action( 'gmw_import_export_forms_tab', 'gmw_import_export_forms_tab' );

/**
 * Export forms to json file
 *
 * @since 3.0
 *
 * @return void
 */
function gmw_export_forms() {

	// make sure at lease one checkbox is checked.
	if ( empty( $_POST['gmw_forms'] ) ) {
		wp_die( esc_html__( 'You must check at least one checkbox of a form that you would like to export.', 'geo-my-wp' ) );
	}

	// check for nonce.
	if ( empty( $_POST['gmw_export_forms_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_export_forms_nonce'], 'gmw_export_forms_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! function_exists( 'gmw_get_forms' ) ) {

		wp_die( esc_html__( 'gmw_get_forms function not exists.', 'geo-my-wp' ) );

		return;
	}

	$forms = gmw_get_forms();

	if ( empty( $forms ) ) {

		wp_die( esc_html__( 'There are no forms to export.', 'geo-my-wp' ) );

		return;
	}

	global $wpdb;

	$forms = wp_unslash( array_map( 'absint', $_POST['gmw_forms'] ) );
	$forms = esc_sql( implode( ',', $forms ) );
	$db    = esc_sql( $wpdb->prefix . 'gmw_forms' );
	//$ph    = str_repeat( '%d,', count( $forms ) - 1 ) . '%d';

	// phpcs:disable
	$export = $wpdb->get_results( "SELECT * FROM $db WHERE ID IN ( $forms )" ); // phpcs: ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, db call ok, cache ok.
	// phpcs:enable

	ignore_user_abort( true );

	set_time_limit( 30 );

	nocache_headers();

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=gmw-forms-export-' . gmdate( 'm-d-Y' ) . '.json' );
	header( 'Expires: 0' );

	echo wp_json_encode( $export );

	exit;
}
add_action( 'gmw_export_forms', 'gmw_export_forms' );

/**
 * Import forms from a json file
 *
 * @since 3.0
 *
 * @return void
 */
function gmw_import_forms() {

	// look for nonce.
	if ( empty( $_POST['gmw_import_forms_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_import_forms_nonce'], 'gmw_import_forms_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// Make sure at least one checkbox is checked.
	if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
		wp_die( esc_html__( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	$file_name = sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) );

	// Retrieve the data from the file and convert the json object to an array.
	// phpcs:disable
	$forms = gmw_object_to_array( json_decode( file_get_contents( $file_name ) ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents.
	// phpcs:enable

	if ( empty( $forms ) ) {

		wp_safe_redirect( admin_url( 'admin.php?page=gmw-import-export&tab=forms&gmw_notice=no_forms_to_imported&gmw_notice_status=error' ) );

	} else {

		global $wpdb;

		foreach ( $forms as $form ) {

			global $wpdb;

			// create new form in database.
			$wpdb->insert(
				$wpdb->prefix . 'gmw_forms',
				array(
					'slug'        => $form['slug'],
					'addon'       => $form['addon'],
					'component'   => $form['component'],
					'object_type' => $form['object_type'],
					'name'        => $form['name'],
					'title'       => $form['title'],
					'prefix'      => $form['prefix'],
					'data'        => $form['data'],
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		}

		// update forms in cache.
		GMW_Forms_Helper::update_forms_cache();

		wp_safe_redirect( admin_url( 'admin.php?page=gmw-import-export&tab=forms&gmw_notice=forms_imported&gmw_notice_status=updated' ) );

	}

	exit;

}
add_action( 'gmw_import_forms', 'gmw_import_forms' );

/**
 * Forms notice messages
 *
 * @access public
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 *
 */
function gmw_import_export_forms_notices_messages( $messages ) {

	$messages['no_forms_to_imported'] = __( 'There were no forms to import.', 'geo-my-wp' );
	$messages['forms_imported']       = __( 'Forms successfully imported.', 'geo-my-wp' );

	return $messages;
}
add_filter( 'gmw_admin_notices_messages', 'gmw_import_export_forms_notices_messages' );
