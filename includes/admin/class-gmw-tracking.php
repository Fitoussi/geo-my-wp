<?php
/**
 * GEO my WP tracking.
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Tracking class
 *
 * Tracking plugin usage. Class sends non-sensitive information to geomywp.com for users that have opted in.
 *
 * @since 3.0
 *
 * @Author The class was originally developed by Pippin Williamson for Easy Digital Downloads plugin
 *
 * and was modified to work with GEO my WP. Thank you!
 */
class GMW_Tracking {

	/**
	 * The data to send to geomywp.com
	 *
	 * @var array
	 *
	 * @access private
	 */
	private $data;

	/**
	 * __Construct function
	 */
	public function __construct() {

		// schedue data send.
		//$this->schedule_send();

		// optin user when click on "Allow tracking" buttton.
		add_action( 'gmw_opt_into_tracking', array( $this, 'optin_tracking' ) );

		// optout user when click on "Don't Allow tracking" buttton.
		add_action( 'gmw_opt_out_of_tracking', array( $this, 'optout_tracking' ) );

		// display tracking admin notice.
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

		// send data.
		add_action( 'admin_init', array( $this, 'send_data' ) );
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @access public
	 * @return void
	 */
	public function admin_notice() {

		// don't show message if was already dismissed.
		if ( get_option( 'gmw_tracking_notice' ) ) {
			return;
		}

		// hide message if tracking alreay allowed.
		if ( gmw_get_option( 'general_settings', 'allow_tracking', false ) ) {
			return;
		}

		// verify user access.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$optin_url  = add_query_arg( 'gmw_action', 'opt_into_tracking' );
		$optout_url = add_query_arg( 'gmw_action', 'opt_out_of_tracking' );

		$output = '<div class="gmw-tracking-notice gmw-admin-notice-box gmw-admin-notice-warning notice is-dismissible">';

		$output .= '<h3>' . __( 'Enable Usage Tracking', 'geo-my-wp' ) . '</h3>';
		$output .= __( 'Allow GEO my WP to track the plugin usage on your site. Tracking non-sensitive data can help us improve GEO my WP plugin.', 'geo-my-wp' );
		$output .= '<em style="margin-top:10px">' . __( '*You can change this setting at any time from GEO my WP Settings page.', 'geo-my-wp' ) . '</em>';

		$output .= '<p>';
		$output .= '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="gmw-settings-action-button button-primary">' . __( 'Allow tracking', 'geo-my-wp' ) . '</a>';
		$output .= '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="gmw-settings-action-button button-secondary">' . __( 'Do not allow tracking', 'geo-my-wp' ) . '</a>';
		$output .= '</p>';
		$output .= '</div>';

		echo $output; // phpcs:ignore: XSS ok.
	}

	/**
	 * Opt-in tracking via the admin notice button.
	 *
	 * @param  [type] $data [description].
	 */
	public function optin_tracking( $data ) {

		$gmw_options = gmw_get_options_group();

		// set true in admin settings global.
		$gmw_options['general_settings']['allow_tracking'] = '1';

		// update admin settings.
		update_option( 'gmw_options', $gmw_options );

		// send data.
		$this->send_data( true );

		// update tracking notice to true. We wont show it again.
		update_option( 'gmw_tracking_notice', '1' );

		// get back to GEO my WP add-ons page.
		$page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'gmw-extensions'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		// reload the page to prevent resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=' . $page . '&gmw_notice=tracking_allowed&gmw_notice_status=updated' ) );

		exit;
	}

	/**
	 * Opt-out tracking via the admin notice button.
	 *
	 * @param  [type] $data [description].
	 */
	public function optout_tracking( $data ) {

		$gmw_options = gmw_get_options_group();

		// set admin settings to false.
		$gmw_options['general_settings']['allow_tracking'] = '0';

		// update gmw options.
		update_option( 'gmw_options', $gmw_options );

		// set tracking notice to true. We wont show it again.
		update_option( 'gmw_tracking_notice', '1' );

		// go to gmw add-ons page.
		$page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'gmw-extensions'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		// reload the page to prevent resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=' . $page ) );

		exit;
	}

	/**
	 * Check if the user allowed tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function is_tracking_allowed() {
		return gmw_get_option( 'general_settings', 'allow_tracking', false );
	}

	/**
	 * Get the last time data was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_send() {
		return get_option( 'gmw_tracking_last_send' );
	}

	/**
	 * Schedule a weekly data send
	 *
	 * @access private
	 * @return void
	 */
	private function schedule_send() {
		add_action( 'gmw_weekly_scheduled_events', array( $this, 'send_data' ) );
	}

	/**
	 * Get plugin's name.
	 *
	 * @param  [type] $basename [description].
	 *
	 * @return [type]           [description]
	 */
	private function get_plugin_name( $basename ) {

		$basename = strtolower( $basename );

		if ( false === strpos( $basename, '/' ) ) {
			return basename( $basename, '.php' );
		}

		return dirname( $basename );
	}

	/**
	 * Setup the data that is going to be tracked and sent
	 *
	 * @access private
	 */
	private function setup_data() {

		$data = array();

		$data['url']       = home_url();
		$data['email']     = get_bloginfo( 'admin_email' );
		$data['multisite'] = is_multisite() ? 1 : 0;
		$data['locale']    = get_locale();

		// versions.
		$data['php_version'] = phpversion();
		$data['wp_version']  = get_bloginfo( 'version' );
		$data['gmw_version'] = GMW_VERSION;
		$data['server']      = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		// theme data.
		$theme_data    = wp_get_theme();
		$theme         = $theme_data->Name . ' v' . $theme_data->Version; // phpcs:ignore.
		$data['theme'] = $theme;

		// plugins data.
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );

		$data['active_plugins']   = array();
		$data['inactive_plugins'] = array();

		foreach ( get_plugins() as $plugin_basename => $plugin ) {

			$plugin_slug = preg_replace( '/[^a-z0-9]/', '_', $this->get_plugin_name( $plugin_basename ) );

			if ( in_array( $plugin_basename, $active_plugins, true ) ) {

				$data['active_plugins'][ $plugin_slug ] = $plugin['Version'];

			} else {

				$data['inactive_plugins'][ $plugin_slug ] = $plugin['Version'];
			}
		}

		// gmw data.
		$data['gmw_options'] = gmw_get_options_group();

		global $wpdb;

		// phpcs:ignore.
		$data['forms'] = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}gmw_forms", ARRAY_A );

		foreach ( $data['forms'] as $key => $form ) {

			$form['data'] = maybe_unserialize( $form['data'] );

			$data['forms'][ $key ] = $form;
		}

		return $data;
	}

	/**
	 * Send the data to geomywp.com
	 *
	 * @param  boolean $override [description].
	 *
	 * @return [type]            [description]
	 */
	public function send_data( $override = false ) {

		if ( ! $this->is_tracking_allowed() && ! $override ) {
			return;
		}

		// get the last time data was sent.
		$last_send = $this->get_last_send();

		// Send data every 30 days.
		if ( $last_send && $last_send > strtotime( '-30 days' ) ) {
			return;
		}

		$query_string = ( $override ) ? 'added' : 'updated';

		// send data using post request.
		$request = wp_remote_post(
			'https://geomywp.com/?gmw_action=tracking_data&status=' . $query_string,
			array(
				'method'      => 'POST',
				'timeout'     => 20,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => $this->setup_data(),
				'user-agent'  => 'GMW/' . GMW_VERSION . '; ' . get_bloginfo( 'url' ),
			)
		);

		// update the sent time.
		update_option( 'gmw_tracking_last_send', time() );
	}
}

new GMW_Tracking();
