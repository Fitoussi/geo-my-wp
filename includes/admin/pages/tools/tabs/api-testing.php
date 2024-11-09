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
	<?php do_action( 'gmw_geocoders_testing_tab_top' ); ?>

	<?php gmw_google_server_key_test(); ?>

	<div class="gmw-settings-panel gmw-tools-api-testing-panel">

		<form method="post" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=api_testing' ); // WPCS: XSS ok. ?>">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php esc_html_e( 'Google Maps Server API Key Test', 'geo-my-wp' ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">
						<?php esc_html_e( 'To test your Google Maps server API key, the plugin will try to geocode a default address and will return a success or failed message.', 'geo-my-wp' ); ?>
					</div>

					<div class="gmw-settings-panel-field">
						<input type="submit" class="gmw-settings-action-button button-primary" value="<?php esc_attr_e( 'Test Google Maps Server API Key', 'geo-my-wp' ); ?>" />
						<input type="hidden" name="gmw_action" value="google_server_key_test" />
					</div>

				</div>
			</fieldset>

			<?php wp_nonce_field( 'gmw_google_server_key_test_nonce', 'gmw_google_server_key_test_nonce' ); ?>
		</form>
	</div>

	<?php do_action( 'gmw_geocoders_testing_tab_bottom' ); ?>

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
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_google_server_key_test_nonce'] ) ), 'gmw_google_server_key_test_nonce' ) ) {
		return;
	}

	$geocoder = new GMW_Google_Maps_Geocoder();
	$result   = $geocoder->geocode( 'Manhattan New York US', array(), true );

	if ( ! empty( $result['error'] ) ) {

		if ( ! empty( $result['data']->error_message ) ) {
			$result['error'] .= ' - ' . $result['data']->error_message;
		}
		?>
		<div class="gmw-settings-panel gmw-admin-notice-box gmw-admin-notice-error">
			<h3 class="gmw-admin-notice-title"><?php esc_attr_e( 'Geocoder Failed!', 'geo-my-wp' ); ?></h3>
			<div class="gmw-admin-notice-content">
				<div class="gmw-admin-notice-description">
					<?php esc_html_e( 'There seems to be an issue with your Google Maps API Server Key. Geocoding failed with the error message:', 'geo-my-wp' ); ?>
					<em style="background:rgba(255, 0, 0, 0.23);"><?php echo esc_html( $result['error'] ); ?></em>
					<a style="font-size:12px;" href="#" onclick="event.preventDefault();jQuery( '#google-test-error-details' ).toggle()"><?php esc_html_e( '( Show error details )', 'geo-my-wp' ); ?></a>
					<br />
					<textarea id="google-test-error-details" readonly="readonly">
						<?php print_r( $result ); // WPCS: XSS ok. ?>
					</textarea>
				</div>
			</div>
		</div>

		<?php

	} else {
		?>
		<div class=" gmw-settings-panel gmw-admin-notice-box gmw-admin-notice-success">
			<h3 class="gmw-admin-notice-title"><?php esc_html_e( 'Geocoder success!', 'geo-my-wp' ); ?></h3>
			<div class="gmw-admin-notice-content">
				<div class="gmw-admin-notice-description">
					<?php esc_html_e( 'Your Google Maps server API key appears to be working properly.', 'geo-my-wp' ); ?>
				</div>
			</div>
		</div>
		<?php
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
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gmw_google_server_key_test_nonce'] ) ), 'gmw_google_server_key_test_nonce' ) ) {
		wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}
}
add_action( 'gmw_google_server_key_test', 'gmw_verify_google_server_key_test_nonce' );
