<?php
/**
 * GEO my WP Map Icons manager page.
 *
 * Requires the Premium Settings extension.
 *
 * @since  4.0
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Generate map icons section ( once per component ).
 *
 * @access public
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */

function gmw_tools_generate_map_icon_section( $addon ) {

	$addon_data = gmw_get_addon_data( $addon );

	if ( empty( $addon_data ) ) {
		return;
	}
	?>
	<div class="gmw-settings-panel gmw-tools-map-icons-panel <?php echo esc_attr( $addon_data['slug'] ); ?>-map-icons">

		<fieldset>

			<legend class="gmw-settings-panel-title">
				<?php
				$title = sprintf(
					/* translators: %s: addon name. */
					__( '%s Map Icons', 'geo-my-wp' ),
					$addon_data['name']
				);
				echo esc_html( $title );
				?>
			</legend>

			<div class="gmw-settings-panel-content">

				<div class="gmw-settings-panel-field">

					<?php
					$upload_dir    = wp_upload_dir();
					$addon_folder  = ! empty( $addon_data['templates_folder'] ) ? $addon_data['templates_folder'] : $addon;
					$icons_dirname = $upload_dir['basedir'] . "/geo-my-wp/{$addon_folder}/map-icons/";
					$icons_path    = $upload_dir['baseurl'] . "/geo-my-wp/{$addon_folder}/map-icons/";
					$map_icons     = glob( $icons_dirname . '*.*' );

					if ( ! empty( $map_icons ) ) { ?>

						<form style="padding-bottom: 15px;border-bottom: 1px solid #ededed;margin-bottom: 30px;" method="post"
							enctype="multipart/form-data"
							action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=map_icons' ); // WPCS: XSS ok. ?>">

							<h3>
								<?php esc_html_e( 'Map Icons', 'geo-my-wp' ); ?>
							</h3>

							<div class="gmw-map-icons-list-wrapper"
								style="border: 1px solid #ededed;padding:5px 0px;border-radius: 5px;height: 300px;overflow-x:hidden;overflow-y: scroll;box-sizing: border-box;">
								<?php

								foreach ( $map_icons as $map_icon ) {

									$file_name = basename( $map_icon );

									echo '<p style="border-bottom: 1px solid #ededed;padding:10px 20px;margin:0;">';
									echo '<label style="display: flex;align-items: center;gap: 0.5rem;">';
									echo '<input type="checkbox" name="map_icons[]" value="' . esc_attr( $file_name ) . '" />'; // WPCS: XSS ok.
									echo '<img style="height: 25px;width: auto;" src="' . esc_url( $icons_path . $file_name ) . '" />';
									echo '<span>' . esc_attr( $file_name ) . '</span>';
									echo '</label>';
									echo '</p>';
								}

								?>
							</div>

							<div style="margin-top:2rem;">

								<h3>
									<?php esc_html_e( 'Delete Map Icons', 'geo-my-wp' ); ?>
								</h3>

								<p class="gmw-settings-panel-description">
									<?php esc_html_e( 'Select the icons that you would like to delete then click the "Delete Icons" button.', 'geo-my-wp' ); ?>
								</p>
							</div>

							<p><input type="submit" class="gmw-delete-icons-button gmw-settings-action-button button-primary"
									value="<?php esc_attr_e( 'Delete Icons', 'geo-my-wp' ); ?>"></p>
							<input type="hidden" name="gmw_action" value="map_icons_delete" />
							<input type="hidden" name="map_icons_addon" value="<?php echo esc_attr( $addon_data['slug'] ); ?>">

							<?php wp_nonce_field( 'gmw_map_icons_delete_nonce', 'gmw_map_icons_delete_nonce' ); ?>
						</form>

					<?php } ?>

					<form method="post" enctype="multipart/form-data"
						action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=map_icons' ); // WPCS: XSS ok. ?>">

						<h3>
							<?php esc_html_e( 'Upload Map Icons', 'geo-my-wp' ); ?>
						</h3>

						<div class="gmw-settings-panel-description">
							<?php esc_html_e( 'Select the map icons that you wish to upload then click the "Upload Icons" button.', 'geo-my-wp' ); ?>
						</div>

						<p><input multiple type="file" name="map_icons[]" required></p>
						<p><input type="submit" class="gmw-settings-action-button button-primary"
								value="<?php esc_attr_e( 'Upload Icons', 'geo-my-wp' ); ?>"></p>

						<input type="hidden" name="gmw_action" value="map_icons_upload" />
						<input type="hidden" name="map_icons_addon" value="<?php echo esc_attr( $addon_data['slug'] ); ?>">

						<?php wp_nonce_field( 'gmw_map_icons_upload_nonce', 'gmw_map_icons_upload_nonce' ); ?>
					</form>
				</div>
			</div>
		</fieldset>
	</div>
	<?php
}

/**
 * Output the map icons tab.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_output_map_icons_tab() {

	do_action( 'gmw_map_icons_tab_top' );

	$addons = array(
		'posts_locator'     => __( 'Posts Locator', 'geo-my-wp' ),
		'members_locator'   => __( 'BP Members Locator', 'geo-my-wp' ),
		'bp_groups_locator' => __( 'BP Groups Locator', 'geo-my-wp' ),
		'users_locator'     => __( 'Users Locator', 'geo-my-wp' ),
	);

	?>
	<div class="gmw-admin-notice-box" style="grid-column: span 2;">
		<?php esc_html_e( 'Use this page to upload custom map icons that will be used instead of the core map icons provided with the plugin.', 'geo-my-wp' ); ?>
	</div>

	<?php

	foreach ( $addons as $addon => $label ) {

		if ( ! gmw_is_addon_active( $addon ) ) {
			continue;
		}

		gmw_tools_generate_map_icon_section( $addon );
	}

	do_action( 'gmw_map_icons_tab_bottom' );
	?>
	<script>
		jQuery(document).ready(function ($) {

			$('.gmw-delete-icons-button').click(function () {

				if (!jQuery(this).closest('form').find('.gmw-map-icons-list-wrapper input').is(':checked')) {

					alert("<?php esc_html_e( 'You need to check at least one map icon that you would like to delete.', 'geo-my-wp' ); ?>");
					return false;
				} else {
					return confirm("<?php esc_html_e( 'This action cannot be undone. Would you like to proceed?', 'geo-my-wp' ); ?>");
				}
			});
		});
	</script>
	<?php
}
add_action( 'gmw_tools_map_icons_tab', 'gmw_output_map_icons_tab' );

/**
 * Modify the upload directory for map icons.
 *
 * @since 4.4.0.3
 *
 * @param mixed $dir
 *
 * @return array
 */
function gmw_modify_map_icons_upload_dir( $dir ) {

	global $gmw_custom_dirname;

	return array(
		'path'   => $dir['basedir'] . $gmw_custom_dirname,
		'url'    => $dir['baseurl'] . $gmw_custom_dirname,
		'subdir' => $gmw_custom_dirname,
	) + $dir;
}

/**
 * Upload map icons action.
 *
 * @since 4.0
 */
function gmw_map_icons_upload() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'map_icons_upload' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_map_icons_upload_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_map_icons_upload_nonce'] ) ), 'gmw_map_icons_upload_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// Abort if no files were found.
	if ( empty( $_FILES['map_icons']['name'][0] ) ) {
		wp_die( esc_html__( 'You didn\'t select any files to upload.', 'geo-my-wp' ) );
	}

	// verify addon.
	if ( empty( $_POST['map_icons_addon'] ) ) {
		wp_die( esc_html__( 'Something went wrong. Try again or contact support.', 'geo-my-wp' ) );
	}

	global $gmw_custom_dirname;

	$addon              = sanitize_text_field( wp_unslash( $_POST['map_icons_addon'] ) );
	$addon_data         = gmw_get_addon_data( $addon );
	$upload_dir         = wp_upload_dir();
	$addon_folder       = ! empty( $addon_data['templates_folder'] ) ? $addon_data['templates_folder'] : $addon;
	$gmw_custom_dirname = "/geo-my-wp/{$addon_folder}/map-icons";
	$custom_dirname     = $upload_dir['basedir'] . $gmw_custom_dirname;
	$allowed_types      = array( 'image/jpeg', 'image/gif', 'image/png', 'image/svg+xml' );

	// Create custom map icons folder if not already exists.
	if ( ! file_exists( $custom_dirname ) ) {
		wp_mkdir_p( $custom_dirname );
	}

	$files          = array();
	$uploaded_icons = map_deep( wp_unslash( $_FILES['map_icons'] ), 'sanitize_text_field' );

	// Arrange files.
	foreach ( $uploaded_icons as $key1 => $value1 ) { // phpcs:ignore sanitization ok.

		foreach ( $value1 as $key2 => $value2 ) {
			$files[ $key2 ][ $key1 ] = $value2;
		}
	}

	add_filter( 'upload_dir', 'gmw_modify_map_icons_upload_dir' );

	foreach ( $files as $file ) {

		// Verify the extension of the file.
		if ( ! in_array( $file['type'], $allowed_types, true ) ) {
			continue;
		}

		// Verify the actual file type.
		$type = mime_content_type( $file['tmp_name'] );

		if ( ! in_array( $type, $allowed_types, true ) ) {
			continue;
		}

		// Rename file if name already exists.
		$file['name'] = wp_unique_filename( $custom_dirname, $file['name'] );

		require_once ABSPATH . 'wp-admin/includes/file.php';

		wp_handle_upload( $file, array( 'test_form' => false ) );
	}

	remove_filter( 'upload_dir', 'gmw_modify_map_icons_upload_dir' );

	// Upload files.
	/*foreach ( $files['name'] as $key => $file_name ) {

		// Verify the extension of the file.
		if ( ! in_array( $files['type'][ $key ], $allowed_types ) ) {
			continue;
		}

		// Verify the actual file type.
		$type = mime_content_type( $files['tmp_name'][ $key ] );

		if ( ! in_array( $type, $allowed_types ) ) {
			continue;
		}

		// Rename file if name already exists.
		$unique_filename = wp_unique_filename( $custom_dirname, $file_name );

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$uploadedfile = $file;

		$movefile = wp_handle_upload($uploadedfile, array('test_form' => false));

		// Uplaod file.
		move_uploaded_file( $files['tmp_name'][ $key ], $custom_dirname . '/' . $unique_filename );
	}*/

	// Refresh icons in internal cache.
	if ( function_exists( 'gmw_ps_collect_icons' ) ) {
		gmw_ps_collect_icons();
	}

	$page = 'admin.php?page=gmw-tools&tab=map_icons&gmw_notice=&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_map_icons_upload', 'gmw_map_icons_upload' );

/**
 * Delete map icons action.
 *
 * @since 4.0
 */
function gmw_map_icons_delete() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'map_icons_delete' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_map_icons_delete_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_map_icons_delete_nonce'] ) ), 'gmw_map_icons_delete_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// Abort if no files were found.
	if ( empty( $_POST['map_icons'] ) || empty( $_POST['map_icons_addon'] ) ) {
		wp_die( esc_html__( 'You must check at least one map icon that you would like to delete.', 'geo-my-wp' ) );
	}

	$addon          = sanitize_text_field( wp_unslash( $_POST['map_icons_addon'] ) );
	$addon_data     = gmw_get_addon_data( $addon );
	$upload_dir     = wp_upload_dir();
	$addon_folder   = ! empty( $addon_data['templates_folder'] ) ? $addon_data['templates_folder'] : $addon;
	$custom_dirname = $upload_dir['basedir'] . "/geo-my-wp/{$addon_folder}/map-icons";
	$map_icons      = array_map( 'sanitize_text_field', wp_unslash( $_POST['map_icons'] ) );

	// Delete files.
	foreach ( $map_icons as $map_icon ) {
		wp_delete_file( $custom_dirname . '/' . $map_icon );
	}

	// Refresh icons in cache.
	if ( function_exists( 'gmw_ps_collect_icons' ) ) {
		gmw_ps_collect_icons();
	}

	$page = 'admin.php?page=gmw-tools&tab=map_icons&gmw_notice=&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_map_icons_delete', 'gmw_map_icons_delete' );
