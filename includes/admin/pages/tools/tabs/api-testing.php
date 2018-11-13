<?php
/**
 * GEO my WP Geocoders testing tools tab.
 *
 * @since  3.2
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * API Testing tab output.
 *
 * @access public
 *
 * @since 3.2
 *
 * @author Eyal Fitoussi
 */
function gmw_output_api_testing_tab() {
	?>
	<div id="gmw-geocoders-testing-tab-content" class="gmw-tools-tab-content">

		<?php do_action( 'gmw_geocoders_testing_tab_top' ); ?>

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox">

						<h3 class="hndle">
							<span><?php esc_attr_e( 'Google Maps Server API Key Test', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">

							<p>To test the server key the plugin will try to geocode a default address and will return a success or failed message.</p>

							<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=api_testing' ); // WPCS: XSS ok. ?>">

								<p>
									<input type="hidden" name="gmw_action" value="google_server_key_test" />

									<?php wp_nonce_field( 'gmw_google_server_key_test_nonce', 'gmw_google_server_key_test_nonce' ); ?>

									<input type="submit" class="button-secondary" value="<?php esc_attr_e( 'Test server key', 'geo-my-wp' ); ?>" />
								</p>

							</form>

							<div id="gmw-google-maps-api-server-key-message-wrapper" style="margin-top: 30px">
								<?php gmw_google_server_key_test(); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php do_action( 'gmw_geocoders_testing_tab_bottom' ); ?>

	</div>
	<?php
}
add_action( 'gmw_tools_api_testing_tab', 'gmw_output_api_testing_tab' );

/**
 * Test Google Server API key
 *
 * @since 3.2
 */
function gmw_google_server_key_test() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'google_server_key_test' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_google_server_key_test_nonce'] ) ) {
		return;
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_google_server_key_test_nonce'], 'gmw_google_server_key_test_nonce' ) ) { // WPCS: CSRF ok, XSS ok, sanitization ok.
		return;
	}

	$geocoder = new GMW_Google_Maps_Geocoder();
	$result   = $geocoder->geocode( 'Manhattan New York US', array(), true );

	if ( ! empty( $result['error'] ) ) {

		if ( ! empty( $result['data']->error_message ) ) {
			$result['error'] .= ' - ' . $result['data']->error_message;
		}

		echo '<h3 style="color:red">Geocoder Failed!</h3>';
		echo '<p>There seems to be an issue with your Google Maps API Server Key.<p>';
		echo '<p>Geocoding failed with the error message: <em style="background:rgba(255, 0, 0, 0.23);">' . esc_html( $result['error'] ) . '</em>';
		echo ' <a style="font-size:12px;" href="#" onclick="event.preventDefault();jQuery( \'#google-test-error-details\' ).slideToggle()">( Show error details )</a></p>';
		echo '<br />';
		echo '<textarea id="google-test-error-details"readonly="readonly" style="min-height: 300px;width: 600px;clear: both;display: none;">';
		print_r( $result ); // WPCS: XSS ok.
		echo '</textarea>';

	} else {
		echo '<h3 style="color:green">Geocoder success!</h3>';
		echo '<p>Your Google Maps server API key seems to be working properly.</p>';
	}
}

/**
 * Google Server API key text verify nonce.
 *
 * @since 3.2
 *
 * @return [type] [description]
 */
function gmw_verify_google_server_key_test_nonce() {

	// Verify action.
	if ( empty( $_POST['gmw_action'] ) || 'google_server_key_test' !== $_POST['gmw_action'] ) { // WPCS: CSRF ok.
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_google_server_key_test_nonce'] ) ) {
		wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_google_server_key_test_nonce'], 'gmw_google_server_key_test_nonce' ) ) { // WPCS: CSRF ok, XSS ok, sanitization ok.
		wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}
}
add_action( 'gmw_google_server_key_test', 'gmw_verify_google_server_key_test_nonce' );
