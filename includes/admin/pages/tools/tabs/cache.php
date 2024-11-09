<?php
/**
 * GEO my WP Geocoders Cache tools tab.
 *
 * @since  4.1
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GEO my WP Cache tab output.
 *
 * @access public
 *
 * @since 4.1
 *
 * @author Eyal Fitoussi
 */
function gmw_output_internal_cache_tab() {
	?>

	<?php do_action( 'gmw_internal_cache_tab_top' ); ?>

	<div class="gmw-settings-panel gmw-enable-internal-cache-panel">

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-tools&tab=internal_cache' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'GEO my WP Internal Cache Status', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<!--<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Check this checkbox to enable the internal cache of GEO my WP.', 'geo-my-wp' ); ?>
					</div> -->

					<div class="gmw-settings-panel-field">

						<div class="gmw-settings-panel-checkboxes">

							<label>

								<?php $cache_status = get_option( 'gmw_internal_cache_status' ); ?>

								<span style="margin:0;font-size:14px;font-weight:bold;">
									<?php esc_attr_e( 'Cache Status:', 'geo-my-wp' ); ?>
								</span>

								<?php if ( ! empty( $cache_status ) ) { ?>

									<span style="margin:0;font-size:14px;color:green" class="gmw-icon-ok">
										<?php esc_attr_e( 'Enabled', 'geo-my-wp' ); ?>
									</span>

									<br />
									<?php
									submit_button(
										esc_html__( 'Disable Cache', 'geo-my-wp' ),
										'gmw-settings-action-button button-secondary',
										'submit',
										false,
									);
									?>
									<input type="hidden" name="cache_action" value="disable" />

								<?php } else { ?>

									<span style="margin:0;font-size:14px;color:red" class="gmw-icon-cancel">
										<?php esc_attr_e( 'Disabled', 'geo-my-wp' ); ?>
									</span>

									<input type="hidden" name="cache_action" value="enable" />

									<br />
									<?php
									submit_button(
										esc_html__( 'Enable Cache', 'geo-my-wp' ),
										'gmw-settings-action-button button-primary',
										'submit',
										false,
									);
									?>
								<?php } ?>

							</label>

						</div>

						<input type="hidden" name="gmw_action" value="update_cache_status" />

						<?php wp_nonce_field( 'gmw_update_cache_status_nonce', 'gmw_update_cache_status_nonce' ); ?>

					</div>
				</div>
			</fieldset>
		</form>

	</div>

	<div class="gmw-settings-panel gmw-clear-internal-cache-panel">

		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-tools&tab=internal_cache' ) ); ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Clear Internal Cache', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'Check the cache items that you would like to clear.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">

						<?php $count = gmw_get_cache_count(); ?>

						<div class="gmw-settings-panel-checkboxes">

							<input type="hidden" name="gmw_action" value="clear_cache" />

							<label>
								<input type="checkbox" class="gmw-cache-item" name="cache_items[]" value="search_queries" />
								<?php printf( __( 'Search queries ( %s entries )', 'geo-my-wp' ), $count['search_queries'] ); ?>
							</label>

							<label>
								<input type="checkbox" class="gmw-cache-item" name="cache_items[]" value="geocoded_data" />
								<?php printf( __( 'Geocoded data ( %s entries )', 'geo-my-wp' ), $count['geocoded_data'] ); ?>
							</label>

						</div>

						<?php
						submit_button(
							esc_html__( 'Clear Cache', 'geo-my-wp' ),
							'gmw-settings-action-button button-primary',
							'submit',
							false,
							array(
								'onclick' => "if ( !jQuery('.gmw-cache-item').is(':checked') ) { alert('You must check at least one cache item that you would like to clear.'); return false; }",
							)
						);
						?>
						<?php wp_nonce_field( 'gmw_clear_cache_nonce', 'gmw_clear_cache_nonce' ); ?>

						<!-- <input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Clear cache', 'geo-my-wp' ); ?>" /> -->

						<?php if ( ! empty( $_GET['gmw_notice'] ) && ! empty( $_GET['count'] ) && 'transients_deleted' === $_GET['gmw_notice'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok. ?>
							<p style="color: green;"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['count'] ) ) ); ?> <?php echo esc_html__( 'transients deleted', 'geo-my-wp' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok. ?></p>
						<?php } ?>

					</div>
				</div>
			</fieldset>
		</form>
	</div>

	<?php do_action( 'gmw_internal_cache_tab_bottom' ); ?>
	<?php
}
add_action( 'gmw_tools_internal_cache_tab', 'gmw_output_internal_cache_tab' );

/**
 * Update cache status.
 *
 * @since 4.1
 */
function gmw_update_cache_status() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || empty( $_POST['cache_action'] ) || 'update_cache_status' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_update_cache_status_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_update_cache_status_nonce'] ) ), 'gmw_update_cache_status_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$action = sanitize_text_field( wp_unslash( $_POST['cache_action'] ) );

	// update status.
	if ( 'disable' === $action ) {
		update_option( 'gmw_internal_cache_status', 0 );
	} else {
		update_option( 'gmw_internal_cache_status', 1 );
	}

	$page = 'admin.php?page=gmw-tools&tab=internal_cache&gmw_notice=&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_update_cache_status', 'gmw_update_cache_status' );

/**
 * Clear GEO my WP cache ( transients )
 *
 * @since 4.1
 */
function gmw_clear_cache() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'clear_cache' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// Make sure at least one item is checked.
	if ( empty( $_POST['cache_items'] ) ) {
		wp_die( esc_html__( 'You must check at least one cache item that you would like to clear.', 'geo-my-wp' ) );
	}

	// look for nonce.
	if ( empty( $_POST['gmw_clear_cache_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_clear_cache_nonce'] ) ), 'gmw_clear_cache_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$items = array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_items'] ) );

	// delete transients.
	$count = gmw_delete_transients( $items );

	$page = 'admin.php?page=gmw-tools&tab=internal_cache&gmw_notice=transients_deleted&count=' . $count . '&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_clear_cache', 'gmw_clear_cache' );

/**
 * Get cache items count.
 *
 * @since 4.1
 *
 * @return array
 */
function gmw_get_cache_count() {

	global $wpdb;

	$queries = $wpdb->get_col(
		"SELECT COUNT(*)
		FROM $wpdb->options
		WHERE option_name LIKE '\_transient\_gmw%'
		AND option_name NOT LIKE '\_transient\_gmw_geocoded%'
		AND option_name NOT LIKE '\_transient\_gmw_verify_license_keys%'
		AND option_name NOT LIKE '\_transient\_gmw_extensions_feed%'",
		0
	); // WPCS: db call ok, cache ok.

	$geocoded = $wpdb->get_col(
		"SELECT COUNT(*)
		FROM $wpdb->options
		WHERE option_name LIKE '\_transient\_gmw_geocoded%'
		AND option_name NOT LIKE '\_transient\_gmw_verify_license_keys%'
		AND option_name NOT LIKE '\_transient\_gmw_extensions_feed%'",
		0
	); // WPCS: db call ok, cache ok.

	return array(
		'search_queries' => $queries[0],
		'geocoded_data'  => $geocoded[0],
	);
}

/**
 * Delete all of GEO my WP's transients.
 *
 * @return integer count of deleted transients.
 *
 * @since 4.1
 */
function gmw_delete_transients( $items ) {

	global $wpdb;

	$sql = '';

	if ( count( $items ) === 2 ) {

		$sql = "
		WHERE ( option_name LIKE '_transient_gmw%'
		OR option_name LIKE '_transient_timeout_gmw%' )";

	} elseif ( in_array( 'search_queries', $items, true ) ) {

		$sql = "
		WHERE ( option_name LIKE '_transient_gmw%'
		OR option_name LIKE '_transient_timeout_gmw%' )
		AND option_name NOT LIKE '_transient_gmw_geocoded%'
		AND option_name NOT LIKE '_transient_timeout_gmw_geocoded%'";

	} else {
		$sql = "
		WHERE ( option_name LIKE '_transient_gmw_geocoded%'
		OR option_name LIKE '_transient_timeout_gmw_geocoded%' )";
	}

	// phpcs:disable
	$count = $wpdb->query(
		"DELETE FROM $wpdb->options
		$sql
		/*AND option_name NOT LIKE '_transient_gmw_get%'*/
		AND option_name NOT LIKE '_transient_gmw_verify_license_keys%'
		AND option_name NOT LIKE '_transient_timeout_gmw_verify_license_keys%'
		AND option_name NOT LIKE '_transient_gmw_extensions_feed%'
		AND option_name NOT LIKE '_transient_timeout_gmw_extensions_feed%'"
	); // WPCS: db call ok, cache ok.
	// phpcs:enable

	return $count;

	/*if ( count( $items ) === 2 ) {

		$count = $wpdb->query(
			"DELETE FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE '\_transient\_gmw%'
			AND a.option_name NOT LIKE '\_transient\_gmw_verify_license_keys%'
			AND a.option_name NOT LIKE '\_transient\_gmw_extensions_feed%'
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )"
		); // WPCS: db call ok, cache ok.

		return $count;

	} elseif ( in_array( 'queries', $items, true ) ) {

		$count = $wpdb->query(
			"DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE '\_transient\_gmw%'
			AND a.option_name NOT LIKE '\_transient\_gmw_geocoded%'
			AND a.option_name NOT LIKE '\_transient\_gmw_verify_license_keys%'
			AND a.option_name NOT LIKE '\_transient\_gmw_extensions_feed%'
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )"
		); // WPCS: db call ok, cache ok.

		echo $count;
		df();
		return $count;

	} else {

		$count = $wpdb->query(
			"DELETE FROM $wpdb->options
			WHERE option_name LIKE '\_transient\_gmw_geocoded%'
			AND option_name NOT LIKE '\_transient\_gmw_verify_license_keys%'
			AND option_name NOT LIKE '\_transient\_gmw_extensions_feed%'"
		); // WPCS: db call ok, cache ok.

		return $count;
	}*/
}
