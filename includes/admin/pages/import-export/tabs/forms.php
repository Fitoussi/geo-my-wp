<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export/Import forms tab output
 *
 * @access public
 * @since 3.5
 * @author Eyal Fitoussi
 */
function gmw_import_export_forms_tab() {
?>	
	<?php do_action( 'gmw_import_export_data_before_export' ); ?>

	<div id="poststuff" class="metabox-holder">

		<div id="post-body">

			<div id="post-body-content">

				<div class="postbox ">

					<h3 class="hndle">
						<span><?php _e( 'Export Forms', 'geo-my-wp' ); ?> </span>
					</h3>

					<div class="inside">

						<p>
							<?php _e( 'Select the forms you would like to export then click the "Export" button to generate a .json file. You can use the file to import your forms back to this site or to another site running GEO my WP version 3.0 or higher.', 'geo-my-wp' ); ?>
						</p>

						<?php $forms = gmw_get_forms(); ?>

						<?php if ( ! empty( $forms ) ) { ?>

							<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=forms' ); ?>">
								<p class="checkboxes">

									<label style="font-weight: 600;margin-bottom: 10px">
										<input 
											type="checkbox" 
											class="cb-export-item" 
											checked="checked" 
											onchange="if ( jQuery( this ).is( ':checked' ) ) { jQuery( this ).closest( 'form' ).find( '.cb-export-item' ).prop( 'checked', true ); } else { jQuery( this ).closest( 'form' ).find( '.cb-export-item' ).prop( 'checked', false ); }"
										/>
										<?php _e( 'All forms', 'geo-my-wp' ); ?>
									</label>

									<?php foreach ( $forms as $form_id => $values ) { ?>

										<?php
										if ( empty( $values ) ) {
											continue;}
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

								</p>
								<p>
									<input type="hidden" name="gmw_action" value="export_forms" />

									<?php wp_nonce_field( 'gmw_export_forms_nonce', 'gmw_export_forms_nonce' ); ?>

									<?php
									submit_button(
										__( 'Export', 'geo-my-wp' ), 'secondary', 'submit', false, array(
											'onclick' => "if ( !jQuery('.cb-export-item').is(':checked') ) { alert( 'You must check at least one form that you would like to export.' ); return false; }",
										)
									);
									?>
								</p>
							</form>
						<?php } else { ?>

							<p><?php _e( 'There are no forms to export', 'geo-my-wp' ); ?></p>

						<?php } ?>

					</div>
				</div>
			</div>
		</div>
	</div>

	<?php do_action( 'gmw_import_export_before_forms_import' ); ?>

	<div id="poststuff" class="metabox-holder">

		<div id="post-body">

			<div id="post-body-content">

				<div class="postbox ">

					<h3 class="hndle">
						<span><?php _e( 'Import Forms', 'geo-my-wp' ); ?> </span>
					</h3>

					<div class="inside">

						<p>
							<?php _e( 'Choose the .json file that you would like to import then click Import.', 'geo-my-wp' ); ?>
						</p>

						<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=forms' ); ?>">					
							<p>
								<input type="file" name="import_file" />
							</p>
							<p>
								<input type="hidden" name="gmw_action" value="import_forms" name="form_import_file" />

								<?php wp_nonce_field( 'gmw_import_forms_nonce', 'gmw_import_forms_nonce' ); ?>

								<?php
								submit_button( __( 'Import', 'geo-my-wp' ), 'secondary', 'submit', false );
								?>
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
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

	// make sure at lease one checkbox is checked
	if ( empty( $_POST['gmw_forms'] ) ) {
		wp_die( __( 'You must check at least one checkbox of a form that you would like to export.', 'geo-my-wp' ) );
	}

	// check for nonce
	if ( empty( $_POST['gmw_export_forms_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce
	if ( ! wp_verify_nonce( $_POST['gmw_export_forms_nonce'], 'gmw_export_forms_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	if ( ! function_exists( 'gmw_get_forms' ) ) {
		wp_die( __( 'gmw_get_forms function not exists.', 'geo-my-wp' ) );
		return;
	}

	$forms = gmw_get_forms();

	if ( empty( $forms ) ) {

		wp_die( __( 'There are no forms to export.', 'geo-my-wp' ) );

		return;
	}

	global $wpdb;

	// get all data from forms table
	$export = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT * 
			FROM {$wpdb->prefix}gmw_forms
			WHERE ID IN ( " . str_repeat( '%d,', count( $_POST['gmw_forms'] ) - 1 ) . '%d )', $_POST['gmw_forms']
		)
	);

	ignore_user_abort( true );

	set_time_limit( 30 );

	nocache_headers();

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=gmw-forms-export-' . date( 'm-d-Y' ) . '.json' );
	header( 'Expires: 0' );

	echo json_encode( $export );

	exit;
}
add_action( 'gmw_export_forms', 'gmw_export_forms' );

/**
 * Import forms from a json file
 *
 * @since 3.0
 * @return void
 */
function gmw_import_forms() {

	// look for nonce
	if ( empty( $_POST['gmw_import_forms_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce
	if ( ! wp_verify_nonce( $_POST['gmw_import_forms_nonce'], 'gmw_import_forms_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	//make sure at least one checkbox is checked
	if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
		wp_die( __( 'Please upload a file to import', 'geo-my-wp' ) );
	}

	// Retrieve the data from the file and convert the json object to an array
	$forms = gmw_object_to_array( json_decode( file_get_contents( $_FILES['import_file']['tmp_name'] ) ) );

	if ( empty( $forms ) ) {

		wp_safe_redirect( admin_url( 'admin.php?page=gmw-import-export&tab=forms&gmw_notice=no_forms_to_imported&gmw_notice_status=error' ) );
	} else {

		global $wpdb;

		foreach ( $forms as $form ) {

			global $wpdb;

			// create new form in database
			$wpdb->insert(
				$wpdb->prefix . 'gmw_forms',
				array(
					'slug'   => $form['slug'],
					'addon'  => $form['addon'],
					'name'   => $form['name'],
					'title'  => $form['title'],
					'prefix' => $form['prefix'],
					'data'   => $form['data'],
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		}

		// update forms in cache
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
 * @since 3.0
 * @author Eyal Fitoussi
 *
 */
function gmw_import_export_forms_notices_messages( $messages ) {

	$messages['no_forms_to_imported'] = __( 'There were no forms to import.', 'geo-my-wp' );
	$messages['forms_imported']       = __( 'Forms successfully imported.', 'geo-my-wp' );

	return $messages;
}
add_filter( 'gmw_admin_notices_messages', 'gmw_import_export_forms_notices_messages' );
