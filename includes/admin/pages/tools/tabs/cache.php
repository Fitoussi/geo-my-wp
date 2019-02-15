<?php
/**
 * GEO my WP Geocoders Cache tools tab.
 *
 * @since  3.2.1
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
 * @since 3.2.1
 *
 * @author Eyal Fitoussi
 */
function gmw_output_internal_cache_tab() {
	?>
	<div id="gmw-geocoders-cache-tab-content" class="gmw-tools-tab-content">

		<?php do_action( 'gmw_geocoders_cache_tab_top' ); ?>

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox">

						<h3 class="hndle">
							<span><?php esc_attr_e( 'GEO my WP internal cache status', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">

							<p>Disable/enable the internal cache of GEO my WP.</p>

							<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=internal_cache' ); // WPCS: XSS ok. ?>">

								<p>	
									<input type="hidden" name="gmw_action" value="update_cache_status" />

									<?php wp_nonce_field( 'gmw_update_cache_status_nonce', 'gmw_update_cache_status_nonce' ); ?>

									<?php $cache_status = get_option( 'gmw_internal_cache_status' ); ?>

									<?php if ( empty( $cache_status ) || 'enabled' === $cache_status ) { ?>

										<p><?php esc_attr_e( 'Cache enabled', 'geo-my-wp' ); ?>

										<input type="hidden" name="cache_action" value="disable" />
										<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Disable cache', 'geo-my-wp' ); ?>" />

									<?php } else { ?>

										<p><?php esc_attr_e( 'Cache disabled', 'geo-my-wp' ); ?>

										<input type="hidden" name="cache_action" value="enable" />
										<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Enable cache', 'geo-my-wp' ); ?>" />
									<?php } ?>
								</p>

							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox">

						<h3 class="hndle">
							<span><?php esc_attr_e( 'Clear cache', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">

							<p>Clear the internal cache of GEO my WP.</p>

							<?php $count = gmw_get_cache_count(); ?>

							<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=internal_cache' ); // WPCS: XSS ok. ?>">

								<p>	
									<input type="hidden" name="gmw_action" value="clear_cache" />

									<span>
										<input type="checkbox" class="gmw-cache-item" name="cache_items[]" value="queries" />
										<?php printf( __( 'Search queries ( %s entries )', 'geo-my-wp' ), $count['queries'] ); ?>
									</span>

									<span>
										<input type="checkbox" class="gmw-cache-item" name="cache_items[]" value="geocoded" />
										<?php printf( __( 'Geocoded data ( %s entries )', 'geo-my-wp' ), $count['geocoded'] ); ?>
									</span>
								</p>
								<p>
									<?php wp_nonce_field( 'gmw_clear_cache_nonce', 'gmw_clear_cache_nonce' ); ?>

									<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Clear cache', 'geo-my-wp' ); ?>" />

									<?php if ( ! empty( $_GET['gmw_notice'] ) && 'transients_deleted' === $_GET['gmw_notice'] ) { ?>
										<p style="color: green;"><?php echo esc_html( $_GET['count'] ); ?> <?php echo esc_html( 'transients deleted', 'geo-my-wp' ); ?></p>
									<?php } ?>
								</p>

							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php do_action( 'gmw_geocoders_cache_tab_bottom' ); ?>

	</div>
	<?php
}
add_action( 'gmw_tools_internal_cache_tab', 'gmw_output_internal_cache_tab' );

/**
 * Update cache status.
 *
 * @since 3.2.1
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
	if ( ! wp_verify_nonce( $_POST['gmw_update_cache_status_nonce'], 'gmw_update_cache_status_nonce' ) ) { // WPCS: CSRF ok, XSS ok, sanitization ok.
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$action = sanitize_text_field( wp_unslash( $_POST['cache_action'] ) );

	// update status.
	if ( 'disable' === $action ) {
		update_option( 'gmw_internal_cache_status', 'disabled' );
	} else {
		update_option( 'gmw_internal_cache_status', 'enabled' );
	}

	$page = 'admin.php?page=gmw-tools&tab=internal_cache&gmw_notice=&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_update_cache_status', 'gmw_update_cache_status' );

/**
 * Clear GEO my WP cache ( transients )
 *
 * @since 3.2.1
 */
function gmw_clear_cache() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'clear_cache' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// Make sure at least one item is checked.
	if ( empty( $_POST['cache_items'] ) ) {
		wp_die( esc_html__( "You must check at least one checkbox of a cache item that you'd like to clear.", 'geo-my-wp' ) );
	}

	// look for nonce.
	if ( empty( $_POST['gmw_clear_cache_nonce'] ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_clear_cache_nonce'], 'gmw_clear_cache_nonce' ) ) { // WPCS: CSRF ok, XSS ok, sanitization ok.
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	$items = $_POST['cache_items'];

	// delete transients.
	$count = gmw_delete_transients( $items );

	$page = 'admin.php?page=gmw-tools&tab=internal_cache&gmw_notice=transients_deleted&count=' . $count . '&gmw_notice_status=updated';

	wp_safe_redirect( admin_url( $page ) );

	exit;
}
add_action( 'gmw_clear_cache', 'gmw_clear_cache' );

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
		'queries'  => $queries[0],
		'geocoded' => $geocoded[0],
	);
}

/**
 * Delete all of GEO my WP's transients.
 *
 * @return integer count of deleted transients.
 *
 * @since 3.2.1
 */
function gmw_delete_transients( $items ) {

	global $wpdb;

	$sql = '';

	if ( count( $items ) === 2 ) {

		$sql = "
		WHERE ( option_name LIKE '_transient_gmw%'
		OR option_name LIKE '_transient_timeout_gmw%' )";

	} elseif ( in_array( 'queries', $items, true ) ) {

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
	
	$count = $wpdb->query(
		"DELETE FROM $wpdb->options
		$sql
		AND option_name NOT LIKE '_transient_gmw_get%'
		AND option_name NOT LIKE '_transient_gmw_verify_license_keys%'
		AND option_name NOT LIKE '_transient_timeout_gmw_verify_license_keys%'
		AND option_name NOT LIKE '_transient_gmw_extensions_feed%'
		AND option_name NOT LIKE '_transient_timeout_gmw_extensions_feed%'"
	); // WPCS: db call ok, cache ok.

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
