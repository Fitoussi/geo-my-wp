<?php
/**
 * GEO my WP Extensions page.
 *
 * @author Eyal Fitoussi
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Extensions class
 */
class GMW_Extensions {

	/**
	 * All Access - 359643
	 *
	 * Ultimate - 304605
	 *
	 * Extended Pass - 359672
	 *
	 * Social Pass - 304606
	 *
	 * Professional - 304604
	 *
	 * Personal Pass - 303650
	 *
	 * @since 4.4
	 *
	 * @var array
	 */
	public $passes_extensions = array(
		'ajax_forms'                       => array( 304604 ),
		'bp_groups_locator'                => array( 359672, 304606 ),
		'bp_members_directory_geolocation' => array( 359672, 304606 ),
		'bp_xprofile_geolocation'          => array( 359672, 304606 ),
		'exclude_locations'                => array( 303650, 359672, 304606, 304604 ),
		'global_maps'                      => array( 304604 ),
		'ip_address_locator'               => array( 303650, 359672, 304606, 304604 ),
		'gmw_meta_fields_geo'              => array( 359672, 304606, 304604 ),
		'gmw_multiple_locations'           => array( 304604 ),
		'nearby_locations'                 => array( 303650, 359672, 304606, 304604 ),
		'gmw_peepso_geolocation'           => array( 359672, 304606 ),
		'premium_settings'                 => array( 303650, 359672, 304606, 304604 ),
		'radius_per_location'              => array( 303650, 359672, 304606, 304604 ),
		'users_locator'                    => array( 304606, 304604 ),
	);

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Abort if not add-ons page.
		if ( empty( $_GET['page'] ) || 'gmw-extensions' !== $_GET['page'] ) { // phpcs:ignore
			return;
		}

		add_action( 'gmw_activate_extension', array( $this, 'activate_extension' ) );
		add_action( 'gmw_deactivate_extension', array( $this, 'deactivate_extension' ) );
		// phpcs:ignore.
		// add_action( 'gmw_updater_action', array( $this, 'extensions_updater' ) );
		add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
		add_action( 'gmw_activate_access_pass_license', array( $this, 'activate_access_pass_license' ) );
		add_action( 'gmw_clear_extensions_cache', array( $this, 'clear_extensions_cache' ) );
	}

	/**
	 * Notices.
	 *
	 * @param  array $messages messages.
	 *
	 * @return [type]           [description]
	 */
	public function notices_messages( $messages ) {

		$messages['updater_enabled']             = __( 'Extensions updater enabled.', 'geo-my-wp' );
		$messages['updater_disabled']            = __( 'Extensions updater disabled.', 'geo-my-wp' );
		$messages['extension_deactivated']       = __( 'Extension deactivated.', 'geo-my-wp' );
		$messages['extension_activated']         = __( 'Extension activated.', 'geo-my-wp' );
		$messages['extension_activation_failed'] = __( 'Extension activation failed.', 'geo-my-wp' );
		$messages['extensions_cache_cleared']    = __( 'Extensions cache cleared.', 'geo-my-wp' );
		$messages['access_pass_activated']       = __( 'Access pass extensions activated.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Add-ons feed from GEO my WP
	 *
	 * @access private
	 *
	 * @return array
	 */
	private static function get_extensions_feed() {

		// look for extensions feed in transient. Transient should clear every 24 hours.
		$output = get_transient( 'gmw_extensions_feed' );

		if ( false === $output ) {

			$feed = wp_remote_get( 'https://geomywp.com/extensions/?feed=extensions', array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) && ( 200 === $feed['response']['code'] || '200' === $feed['response']['code'] ) ) {

				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {

					$output = wp_remote_retrieve_body( $feed );
					$output = simplexml_load_string( $output );
					$output = json_decode( wp_json_encode( (array) $output ), true );

					set_transient( 'gmw_extensions_feed', $output, DAY_IN_SECONDS );
				}
			} else {

				$feed = (array) $feed;

				if ( ! empty( $feed['response'] ) ) {

					$message = 'Error Code: ' . $feed['response']['code'];

				} elseif ( ! empty( $feed['errors']['http_request_failed'][0] ) ) {

					$message = 'Error message: ' . $feed['errors']['http_request_failed'][0];
				}

				echo '<div class="gmw-admin-notice-top error"><p>' . sprintf(
					/* translators: %s : error message. */
					esc_attr__( 'There was an error retrieving GEO my WP\'s list of add-ons from the server. Please try again later. ( %s )', 'geo-my-wp' ),
					esc_html( $message )
				) . '</p></div>';

				$output = false;
			}
		}

		return $output;
	}

	/**
	 * Get extensions data.
	 *
	 * This function merge extension data exists on the site with data retrieved remotly
	 *
	 * from GEO my WP's website.
	 *
	 * @since 4.4
	 *
	 * @return array
	 */
	public function get_extensions_data() {

		// Get GMW extensions data. We merge both licenses data and addons data.
		$extensions_data = array_merge_recursive( GMW()->addons, GMW()->licenses );

		// Get remote extensions data via geomywp.com feed.
		$remote_extensions = self::get_extensions_feed();

		// Verify feed. if feed ok merge some data with local extensions.
		if ( ! empty( $remote_extensions ) ) {

			$replace_data = array(
				'release_date',
				'current_version',
				'addon_page',
				'docs_page',
				'support_page',
				'description',
				'item_id',
				'item_name',
			);

			foreach ( $remote_extensions as $slug => $values ) {

				// if remote extension do not exists in GEO my WP extension.
				// get the data from remote.
				if ( empty( $extensions_data[ $slug ] ) ) {
					$extensions_data[ $slug ] = $values;
				} else {

					foreach ( $replace_data as $rd ) {

						if ( empty( $extensions_data[ $slug ][ $rd ] ) ) {

							if ( ! empty( $values[ $rd ] ) ) {
								$extensions_data[ $slug ][ $rd ] = ! empty( $values[ $rd ] ) ? $values[ $rd ] : '';
							}
						}
					}
				}
			}
		}

		return $extensions_data;
	}

	/**
	 * Enable / Disable extension updater.
	 *
	 * @access public
	 *
	 * @return void
	 */
	// phpcs:disable
	/*public static function extensions_updater() {

		// if doing ajax call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// verify nonce.
			if ( ! check_ajax_referer( 'gmw_extension_updater_nonce', 'security', false ) ) {
				wp_die(
					esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					esc_attr__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			$action = ! empty( $_POST['updater_action'] ) ? $_POST['updater_action'] : 'disable';

			// Otherwise, page load call.
		} else {

			// verify nonce.
			if ( ! check_admin_referer( 'gmw_extension_updater_nonce' ) ) {
				wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}

			if ( empty( $_GET['action'] ) ) {
				return;
			}

			$action = $_GET['action'];
		}

		// enable updater.
		if ( 'enable' === $action ) {

			update_option( 'gmw_extensions_updater', true );

			$notice = 'updater_enabled';

			// disable updater.
		} else {

			update_option( 'gmw_extensions_updater', false );

			$notice = 'updater_disabled';
		}

		// done.
		if ( defined( 'DOING_AJAX' ) ) {

			wp_send_json( $notice );

		} else {

			wp_safe_redirect( admin_url( 'admin.php?page=gmw-extensions&gmw_notice=' . $notice . '&gmw_notice_status=updated' ) );
			exit;
		}
	}*/

	/**
	 * Bulk activate access pass extensions.
	 *
	 * @since 4.4
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function activate_access_pass_license() {

		// Abort if form was not submitted.
		if ( empty( $_POST['gmw_action'] ) || 'activate_access_pass_license' !== $_POST['gmw_action'] ) {
			return;
		}

		// Varify nonce.
		if ( empty( $_POST['gmw_activate_access_pass_license_nonce'] ) || ! wp_verify_nonce( $_POST['gmw_activate_access_pass_license_nonce'], 'gmw_activate_access_pass_license_nonce' ) ) { // phpcs:ignore. CSRF ok, sanitization ok.
			wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// Abort if license key was not entered.
		if ( empty ( $_POST['license_key'] ) ) {
			wp_die( esc_attr__( 'You did not provide a license key.', 'geo-my-wp' ) );
		}

		$access_pass_license = sanitize_text_field( wp_unslash( $_POST['license_key'] ) );
		$api_params          = array(
			'edd_action' => 'activate_license',
			'license'    => $access_pass_license,
			'url'        => home_url(),
			'item_id'    => 668,
		);

		// Get license data via remote API call.
		// Here we call and activate a random extension
		$response = wp_remote_post(
			GMW_REMOTE_SITE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// Abort if remote connection failed.
		if ( is_wp_error( $response ) ) {
			wp_die( sprintf( esc_attr__( 'Connection to remote server failed. Error: %s.', 'geo-my-wp' ), $response->get_error_message() ) );
		}

		// Get license data.
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// Fail if license key doesn't exists.
		if ( empty( $license_data->license ) || empty( $license_data->access_pass_id ) || ( 'invalid' === $license_data->license && empty( $license_data->error ) || 'missing' === $license_data->error ) ) {

			wp_safe_redirect(
				admin_url( 'admin.php?page=gmw-extensions&access_pass_activation_notice=missing&license_key=' . $access_pass_license )
			);

			exit;
		}

		// Fail if license key invalid except if it is expired.
		if ( 'invalid' === $license_data->license && 'expired' !== $license_data->error ) {

			wp_safe_redirect(
				admin_url( 'admin.php?page=gmw-extensions&access_pass_activation_notice=' . $license_data->error . '&license_key=' . $access_pass_license )
			);

			exit;
		}

		$access_pass_id = absint( $license_data->access_pass_id );

		// Fail if license key is not an access pass.
		if ( ! in_array( $access_pass_id, array( 359643, 304605, 359672, 304606, 304604, 303650 ) ) ) {

			wp_safe_redirect(
				admin_url( 'admin.php?page=gmw-extensions&access_pass_activation_notice=not_access_pass&license_key=' . $access_pass_license )
			);

			exit;
		}

		// Get GMW extensions data. We merge both licenses data and addons data.
		$extensions_data = $this->get_extensions_data();
		$plugins         = get_plugins();

		foreach ( $this->passes_extensions as $license_name => $passes ) {

			if ( in_array( $access_pass_id, array( 359643, 304605 ), true ) || in_array( $access_pass_id, $passes, true ) ) {

				$this_license = $extensions_data[ $license_name ];
				$slug         = sanitize_text_field( wp_unslash( $this_license['slug'] ) );
				$basename     = sanitize_text_field( wp_unslash( $this_license['basename'] ) );

				// Proceed only if the extension already installed.
				if ( ! array_key_exists( $basename, $plugins ) ) {
					continue;
				}

				$args         = array(
					'action'       => 'activate_license',
					'license_name' => $this_license['license_name'],
					'item_id'      => $this_license['item_id'],
					'license_key'  => $access_pass_license,
					'item_name'    => $this_license['item_name'],
				);

				// Activate license.
				gmw_license_key_actions( $args, $response );

				// Activate the WordPress plugin reagrdless it later it is set as disabled or inactive in GEO my WP.
				// We do so to allow the license key to load so updates will be available.
				if ( array_key_exists( $basename, $plugins ) && is_plugin_inactive( $basename ) ) {
					 activate_plugins( $basename );
				}

				// Activate extension.
				    gmw_update_addon_status( $slug, 'active' );
			}
		}

		wp_safe_redirect(
			admin_url( 'admin.php?page=gmw-extensions&gmw_notice=access_pass_activated&gmw_notice_status=updated&license_key=' . $access_pass_license )
		);

		exit;
	}

	// phpcs:enable
	/**
	 * Clear Extensions cache.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function clear_extensions_cache() {

		// Abort if form was not submitted.
		if ( empty( $_POST['gmw_action'] ) || 'clear_extensions_cache' !== $_POST['gmw_action'] ) {
			return;
		}

		// Varify nonce.
		if ( empty( $_POST['gmw_clear_extensions_cache_nonce'] ) || ! wp_verify_nonce( $_POST['gmw_clear_extensions_cache_nonce'], 'gmw_clear_extensions_cache_nonce' ) ) { // phpcs:ignore. CSRF ok, sanitization ok.
			wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// delete extensions and license key transient to retrive new data.
		delete_transient( 'gmw_extensions_feed' );
		// delete_transient( 'gmw_verify_license_keys' );.

		// Reload the page.
		wp_safe_redirect(
			admin_url( 'admin.php?page=gmw-extensions&gmw_notice=extensions_cache_cleared&gmw_notice_status=updated' )
		);

		exit;
	}

	/**
	 * Generate Activate extension button
	 *
	 * @param  string $slug     extension slug.
	 *
	 * @param  string $basename extension basename.
	 *
	 * @return string ( link )
	 */
	public static function get_activate_extension_button( $slug = '', $basename = '' ) {

		$nonce = wp_create_nonce( 'gmw_' . $slug . '_extension_nonce' );
		$url   = admin_url( 'admin.php?page=gmw-extensions&gmw_action=activate_extension&basename=' . $basename . '&slug=' . $slug . '&_wpnonce=' . $nonce );
		$label = esc_attr__( 'Activate', 'geo-my-wp' );

		$output  = '<a href="' . esc_url( $url ) . '"';
		$output .= ' class="gmw-extension-action-button activate gmw-action-toggle-button"';
		$output .= ' data-slug="' . esc_attr( $slug ) . '"';
		$output .= ' data-basename="' . esc_attr( $basename ) . '"';
		$output .= ' data-action="activate_extension"';
		$output .= ' data-nonce="' . $nonce . '"';
		$output .= ' data-updating_message="' . __( 'Activating', 'geo-my-wp' ) . '"';
		$output .= ' data-updated_message="' . __( 'Activated', 'geo-my-wp' ) . '"';
		$output .= ' data-failed_message="' . __( 'Activation failed', 'geo-my-wp' ) . '"';
		$output .= ' data-label="' . $label . '"';
		$output .= ' >';

		$output .= '<span class="gmw-atb-toggle"></span>';
		$output .= '<span class="gmw-atb-label">' . $label . '</span>';
		$output .= '<span class="gmw-atb-bg"></span>';
		$output .= ' </a>';

		return $output;
	}

	/**
	 * Generate Deactivate extension button
	 *
	 * @param  string $slug     extension slug.
	 *
	 * @param  string $basename extension basename.
	 *
	 * @return string ( link )
	 */
	public static function get_deactivate_extension_button( $slug = '', $basename = '' ) {

		$nonce = wp_create_nonce( 'gmw_' . $slug . '_extension_nonce' );
		$url   = admin_url( 'admin.php?page=gmw-extensions&gmw_action=deactivate_extension&basename=' . $basename . '&slug=' . $slug . '&_wpnonce=' . $nonce );
		$label = esc_attr__( 'Deactivate', 'geo-my-wp' );

		$output  = '<a href="' . esc_url( $url ) . '"';
		$output .= ' class="gmw-extension-action-button deactivate gmw-action-toggle-button"';
		$output .= ' data-slug="' . esc_attr( $slug ) . '"';
		$output .= ' data-basename="' . esc_attr( $basename ) . '"';
		$output .= ' data-action="deactivate_extension"';
		$output .= ' data-nonce="' . $nonce . '"';
		$output .= ' data-updating_message="' . __( 'Deactivating', 'geo-my-wp' ) . '"';
		$output .= ' data-updated_message="' . __( 'Deactivated', 'geo-my-wp' ) . '"';
		$output .= ' data-failed_message="' . __( 'Deactivation failed', 'geo-my-wp' ) . '"';
		$output .= ' data-label="' . $label . '"';
		$output .= ' >';
		$output .= '<span class="gmw-atb-toggle"></span>';
		$output .= '<span class="gmw-atb-label">' . $label . '</span>';
		$output .= '<span class="gmw-atb-bg"></span>';
		$output .= ' </a>';

		return $output;
	}

	/**
	 * Activate extension.
	 */
	public static function activate_extension() {

		// make sure we activating extension.
		if ( empty( $_GET['gmw_action'] ) || 'activate_extension' !== $_GET['gmw_action'] || empty( $_GET['slug'] ) || empty( $_GET['basename'] ) ) {
			wp_die();
		}

		$slug     = sanitize_text_field( wp_unslash( $_GET['slug'] ) );
		$basename = sanitize_text_field( wp_unslash( $_GET['basename'] ) );

		// If doing AJAX call.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// verify nonce.
			if ( ! check_ajax_referer( 'gmw_' . $slug . '_extension_nonce', 'security', false ) ) {

				wp_die(
					esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					esc_attr__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			// otherwise, page load submission.
		} else {

			if ( ! check_admin_referer( 'gmw_' . $slug . '_extension_nonce' ) ) {
				wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}
		}

		$plugins = get_plugins();

		// Activate the WordPress plugin reagrdless it later it is set as disabled or inactive in GEO my WP.
		// We do so to allow the license key to load so updates will be available.
		if ( array_key_exists( $basename, $plugins ) && is_plugin_inactive( $basename ) ) {
			activate_plugins( $basename );
		}

		// Enable addons after activation.
		GMW_Addon::init_addons();

		// Update active status and get the addon data.
		$extension = gmw_update_addon_status( $slug, 'active' );

		// Get all extensions.
		$extensions_data = gmw_get_addons_data( true );

		// If AJAX enabled.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			$dependends = array();

			// look for other extensions dependends on this extension.
			// we will enable them.
			foreach ( $extensions_data as $ext_data ) {

				// phpcs:disable.
				//Activate child extensions.
				/*if ( ! empty( $ext_data['parent'] ) && $slug === $ext_data['parent'] ) {
					//gmw_update_addon_status( $ext_data['slug'], 'active' );
				}*/
				// phpcs:enable

				// if disabled because of a theme skip checking for addons.
				if ( 'disabled' === $ext_data['status'] && 'theme_missing' === $ext_data['status_details']['error'] ) {

					continue;

				} elseif ( ! empty( $ext_data['required']['addons'] ) ) {

					foreach ( $ext_data['required']['addons'] as $required_addon ) {

						if ( isset( $extension['slug'] ) && $required_addon['slug'] === $extension['slug'] ) {

							$dependends[ $ext_data['slug'] ] = '';

							// deactivate addon.
							gmw_update_addon_status( $ext_data['slug'], 'inactive' );
						}
					}
				}
			}

			// Generate deactivation button to pass to JS. It will replace the Activate button.
			$link = self::get_deactivate_extension_button( $slug, $basename );

			// proceed to JS.
			wp_send_json(
				array(
					'newLink'     => $link,
					'lisenseData' => $extension,
					'dependends'  => $dependends,
				)
			);

		} else {

			// Reload the page to prevent resubmission.
			wp_safe_redirect(
				admin_url(
					'admin.php?page=gmw-extensions&gmw_notice=extension_activated&gmw_notice_status=updated'
				)
			);

			exit;
		}
	}

	/**
	 * Deactivate Extension
	 *
	 * @return [type] [description]
	 */
	public static function deactivate_extension() {

		// Make sure we activated an add-on.
		if ( empty( $_GET['gmw_action'] ) || 'deactivate_extension' !== $_GET['gmw_action'] || empty( $_GET['slug'] ) || empty( $_GET['basename'] ) ) { // phpcs:ignore: CSRF ok.
			return;
		}

		$slug           = sanitize_text_field( wp_unslash( $_GET['slug'] ) );
		$basename       = sanitize_text_field( wp_unslash( $_GET['basename'] ) );
		$extension_data = gmw_get_addon_data( $slug );

		// If doing ajax deactivation.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// Verify nonce.
			if ( ! check_ajax_referer( 'gmw_' . $slug . '_extension_nonce', 'security', false ) ) {
				wp_die(
					esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					esc_attr__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			// Otherwise, page load action.
		} else {

			if ( ! check_admin_referer( 'gmw_' . $slug . '_extension_nonce' ) ) {
				wp_die( esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}
		}

		// Don't deactivate the plugin when activating a sub-extension.
		if ( empty( $extension_data['parent'] ) ) {

			// get WordPress plugins.
			$plugins = get_plugins();

			// If the extension is a WordPress plugin, deactivate it.
			if ( array_key_exists( $basename, $plugins ) && is_plugin_active( $basename ) ) {
				deactivate_plugins( $basename );
			}
		}

		// set addon status inactive and get the addon data.
		$extension = gmw_update_addon_status( $slug, 'inactive' );

		// get extensions.
		$extensions_data = gmw_get_addons_data( true );

		// if doing AJAX.
		if ( defined( 'DOING_AJAX' ) ) {

			$dependends = array();

			// look for other extensions dependends on this extension.
			// we will disable them.
			foreach ( $extensions_data as $ext_data ) {

				// phpcs:disable.
				// deactivate child extensions.
				/*if ( ! empty( $ext_data['parent'] ) && $slug === $ext_data['parent'] ) {
								//gmw_update_addon_status( $ext_data['slug'], 'inactive' );
							}*/
				// phpcs:enable.

				// Abort if already disabled because of a theme.
				if ( 'disabled' === $ext_data['status'] && 'theme_missing' === $ext_data['status_details']['error'] ) {

					continue;

				} elseif ( ! empty( $ext_data['required']['addons'] ) ) {

					foreach ( $ext_data['required']['addons'] as $required_addon ) {

						if ( isset( $extension['slug'] ) && $required_addon['slug'] === $extension['slug'] ) {

							$dependends[ $ext_data['slug'] ] = $required_addon['notice'];

							// Update extensions status in database.
							gmw_update_addon_status( $ext_data['slug'], 'disabled', $required_addon );
						}
					}
				}
			}

			// Generate new Activate link to replace with Deactivate.
			$link = self::get_activate_extension_button( $extension['slug'], $basename );

			wp_send_json(
				array(
					'newLink'     => $link,
					'lisenseData' => $extension,
					'dependends'  => $dependends,
				)
			);

		} else {

			wp_safe_redirect( admin_url( 'admin.php?page=gmw-extensions&gmw_notice=extension_deactivated&gmw_notice_status=updated' ) );
			exit;
		}
	}

	/**
	 * Activate / deactivate license via ajax
	 *
	 * This function made specificly to work with AJAX.
	 *
	 * To update license key via HTTP, the function can be found
	 *
	 * In the license key file.
	 */
	public static function license_key_actions() {

		if ( empty( $_POST['data'] ) || ! is_array( $_POST['data'] ) ) { // phpcs:ignore: CSRF ok.
			return wp_send_json( array() );
		}

		$form_data = array_map( 'sanitize_text_field', wp_unslash( $_POST['data'] ) ); // phpcs:ignore: CSRF ok.

		// Check nonce.
		if ( ! check_ajax_referer( 'gmw_' . $form_data['license_name'] . '_license_nonce', 'security', false ) ) {

			// Abort if bad nonce.
			wp_die(
				esc_attr__( 'Cheatin\' eh?!', 'geo-my-wp' ),
				esc_attr__( 'Error', 'geo-my-wp' ),
				array( 'response' => 403 )
			);
		}

		// Execute license action.
		$license_data = gmw_license_key_actions( $form_data );

		// Abort if failed connecting to remote server.
		if ( ! $license_data->remote_connection ) {
			wp_die( esc_attr__( 'connection to remote server failed.', 'geo-my-wp' ) );
		}

		if ( class_exists( 'GMW_License_Key' ) ) {

			// Generate new license element to replace with current one.
			$license_input = new GMW_License_Key(
				$form_data['basename'],
				$form_data['item_name'],
				$form_data['license_name'],
				$form_data['item_id']
			);
		}

		$form = $license_input->get_license_key_element();

		// proceed with AJAX.
		wp_send_json(
			array(
				'license_data' => $license_data,
				'form'         => $form,
			)
		);
	}

	/**
	 * Generate single extension card.
	 *
	 * @param  array $extension extension data.
	 *
	 * @param  array $plugins   plugins data.
	 */
	public function extension_card( $extension, $plugins ) {

		// Set extension status.
		$extension['status'] = ! empty( $extension['status'] ) ? $extension['status'] : 'inactive';

		// Verify some data in a premium extension.
		if ( empty( $extension['is_core'] ) ) {

			// Reset some variables.
			$extension['installed'] = false;

			// Create file if doesnt exist.
			if ( empty( $extension['full_path'] ) ) {
				$extension['full_path'] = ABSPATH . 'wp-content/plugins/' . $extension['basename'];
			}

			// Create basename if doesnt exist.
			if ( empty( $extension['basename'] ) ) {
				$extension['basename'] = plugin_basename( $extension['full_path'] );
			}

			// If add-on installed.
			if ( isset( $plugins[ $extension['basename'] ] ) ) {

				$extension['installed'] = true;
				$extension['version']   = $plugins[ $extension['basename'] ]['Version'];

			} elseif ( empty( $extension['version'] ) ) {

				$extension['version'] = ! empty( $extension['current_version'] ) ? $extension['current_version'] : '1.0';
			}
		} else {

			$extension['installed'] = true;
		}

		$status_class    = 'disabled' === $extension['status'] ? 'inactive disabled' : $extension['status'];
		$is_installed    = false;
		$installed_class = 'not-installed';

		if ( $extension['installed'] ) {

			$is_installed    = true;
			$installed_class = 'installed';
		}

		$allowed_html = array(
			'a' => array(
				'title'  => array(),
				'href'   => array(),
				'target' => array(),
			),
		);

		$details_classes   = array();
		$details_classes[] = $status_class;
		$details_classes[] = ! empty( $extension['installed'] ) ? 'installed' : 'not-installed';
		$details_classes[] = ! empty( $extension['is_core'] ) ? 'core' : 'premium';
		$details_classes[] = ! empty( $extension['license_name'] ) ? 'has-license' : 'free';
		$details_classes[] = ! empty( $extension['always_active'] ) ? 'always-active' : '';
		?>

		<!-- extension wrapper -->
		<div class="gmw-extension-wrapper <?php echo implode( ' ', $details_classes ); // phpcs:ignore: XSS ok. ?>"
			data-slug="<?php echo esc_attr( $extension['slug'] ); ?>" data-name="<?php echo esc_attr( $extension['name'] ); ?>">

			<!-- New add-on -->
			<?php if ( ! $extension['installed'] && ! empty( $extension['new_addon'] ) ) { ?>
				<div class="gmw-extension-ribbon-wrapper">
					<div class="gmw-extension-ribbon blue">
						<?php esc_attr_e( 'New Add-on', 'geo-my-wp' ); ?>
					</div>
				</div>
			<?php } ?>

			<?php
			$show_error = false;

			// No need to display this error message in the extensions page if it is related to the license key activation.
			// This message is already displayed below the license key inbox.
			// This message will eb displayed only when related to other activation issues such as missing theme or incorrect plugin version.
			if ( 'disabled' === $extension['status'] ) {

				$status_error = '';

				if ( ! empty( $extension['status_details']['error'] ) ) {
					$status_error = is_array( $extension['status_details']['error'] ) ? $extension['status_details']['error'][0] : $extension['status_details']['error'];
				}

				$license_statuses = array_keys( gmw_license_update_notices() );

				if ( ! in_array( $status_error, $license_statuses, true ) ) {
					$show_error = true;
				}
			}

			if ( $show_error ) {
				?>

				<div class="activation-disabled-message">
					<?php
					// in rare cases the notice can be an array.
					// When 2 versions of the same extension are installed.
					$notice = is_array( $extension['status_details']['notice'] ) ? $extension['status_details']['notice'][0] : $extension['status_details']['notice'];

					echo '<span class="gmw-icon-cancel-circled">' . wp_kses( $notice, $allowed_html ) . '</span>';
					?>
				</div>

			<?php } elseif ( empty( $extension['status_details'] ) && ! empty( $extension['current_version'] ) && version_compare( $extension['version'], $extension['current_version'], '<' ) ) { ?>

				<div class="update-available-notice">

				<?php
					$notice = sprintf(
						/* translators: %1$s: plugin's version available for update, %2$s: link to plugins page. */
						__( 'Version %1$s is now availabe. <a href="%2$s">Update your plugin</a>.', 'geo-my-wp' ),
						esc_attr( $extension['current_version'] ),
						admin_url( 'plugins.php' )
					);

					echo '<span class="gmw-icon-spin">' . wp_kses( $notice, $allowed_html ) . '</span>';
				?>

				</div>

			<?php } else { ?>

				<div class="gmw-extension-action-links">

					<?php $details_link = ! empty( $extension['addon_page'] ) ? $extension['addon_page'] : 'https://geomywp.com'; ?>

					<div class="gmw-extension-single-action details">
						<a href="<?php echo esc_url( $details_link ); ?>" target="_blank">
							<i class="gmw-icon-info-circled"></i>
							<?php esc_attr_e( 'Details', 'geo-my-wp' ); ?>
						</a>
					</div>

					<?php $docs_link = ! empty( $extension['docs_page'] ) ? $extension['docs_page'] : 'https://docs.geomywp.com'; ?>

					<div class="gmw-extension-single-action docs">
						<a href="<?php echo esc_url( $docs_link ); ?>" target="_blank">
							<i class="gmw-icon-doc-text"></i>
							<?php esc_attr_e( 'Documentation', 'geo-my-wp' ); ?>
						</a>
					</div>

					<?php $support_link = ! empty( $extension['support_page'] ) ? $extension['support_page'] : 'https://geomywp.com/forums/forum/support/'; ?>

					<div class="gmw-extension-single-action support">
						<a href="<?php echo esc_url( $support_link ); ?>" target="_blank">
							<i class="gmw-icon-lifebuoy"></i>
							<?php esc_attr_e( 'Support', 'geo-my-wp' ); ?>
						</a>
					</div>
				</div>

			<?php } ?>

			<div class="gmw-extension-top">

				<div class="gmw-extension-image">
					<img src="https://geomywp.com/wp-content/uploads/extensions-images/icons/<?php echo esc_attr( $extension['slug'] ); ?>_icon.svg?v=<?php echo gmdate( 'm' ); // phpcs:ignore: XSS ok. ?>"
						onerror="jQuery( this ).hide();">
				</div>

				<div class="gmw-extension-content">

					<h3 class="gmw-extension-title">
						<?php echo esc_attr( $extension['name'] ); ?>
						<span class="dev-details">

							<?php

							if ( ! empty( $extension['version'] ) && 'na' !== $extension['version'] ) {
								/* translators: %s extension's version */
								printf( esc_attr__( 'Version %s | ', 'geo-my-wp' ), esc_attr( $extension['version'] ) );
							}

							$author = 'Eyal Fitoussi';

							if ( empty( $extension['is_core'] ) && $extension['installed'] && empty( $extension['author'] ) ) {

								$authur = esc_attr( $plugins[ $extension['basename'] ]['Author'] );

							} elseif ( ! empty( $extension['author'] ) ) {

								$authur = esc_attr( $extension['author'] );
							}

							/* translators: %s author's name */
							printf( esc_attr__( 'By %s', 'geo-my-wp' ), esc_attr( $author ) );
							?>
						</span>
					</h3>

					<div class="gmw-extension-activation-buttons-wrapper">

						<?php
						if ( $is_installed ) {

							// show Activate button.
							if ( 'active' !== $extension['status'] ) {
								echo self::get_activate_extension_button( $extension['slug'], $extension['basename'] ); // phpcs:ignore: XSS ok.
							} else {
								echo self::get_deactivate_extension_button( $extension['slug'], $extension['basename'] ); // phpcs:ignore: XSS ok.
							}
						}
						?>
					</div>

					<div class="gmw-extension-description">
						<span>
							<?php
							// If plugin installed and description not provided in addon registration
							// get the description from WP plugins();.
							if ( $extension['installed'] && empty( $extension['description'] ) ) {

								echo esc_attr( $plugins[ $extension['basename'] ]['Description'] );

							} elseif ( ! empty( $extension['description'] ) ) {

								echo esc_attr( $extension['description'] );
							}
							?>
						</span>
					</div>
				</div>

			</div>

			<div class="gmw-extension-bottom">

				<?php if ( $is_installed ) { ?>

					<div class="gmw-core-extension-activation-message">

						<span class="description inactive-message">
							<?php esc_attr_e( 'This extension is free, activate it and start using it now.', 'geo-my-wp' ); ?>
						</span>

						<span class="description active-message">
							<?php
							printf(
								/* translators: %s feedback link */
								esc_html__( 'Thank you for using GEO my WP. Your %s is greatly appreciated.', 'geo-my-wp' ),
								/* translators: %1$s feedback link, %2$s feedback text */
								sprintf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									'https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5',
									esc_html__( 'feedback', 'geo-my-wp' )
								)
							);
							?>
						</span>
					</div>

				<?php } ?>

				<?php if ( empty( $extension['is_core'] ) && ! empty( $extension['license_name'] ) ) { ?>

					<?php if ( ! $is_installed ) { ?>

						<a href="<?php echo esc_url( $extension['addon_page'] ); ?>" class="get-extension gmw-extension-action-button"
							target="_blank">
							<i class="gmw-icon-download"></i>
							<?php esc_attr_e( 'Get Extension', 'geo-my-wp' ); ?>
						</a>

					<?php } else { ?>

						<?php if ( empty( $extension['parent'] ) ) { ?>

							<form method="post" action="" class="extension-license-form"
								data-slug="<?php echo esc_attr( $extension['slug'] ); ?>">

								<?php
								// Display license key form element.
								if ( class_exists( 'GMW_License_Key' ) ) {

									$gmw_license_key = new GMW_License_Key(
										$extension['full_path'],
										$extension['item_name'],
										$extension['license_name'],
										$extension['item_id']
									);

									echo $gmw_license_key->get_license_key_element(); // phpcs:ignore: XSS ok.
								}
								?>
							</form>

						<?php } else { ?>

							<?php $parent = gmw_get_addon_data( $extension['parent'] ); ?>

							<div class="gmw-license-wrapper valid">
								<p class="description">License key is already activated via the
									<?php echo esc_attr( $parent['name'] ); ?> extension.
								</p>
							</div>

						<?php } ?>

					<?php } ?>

				<?php } ?>

			</div>

			<div class="disabler-block"></div>

		</div>
		<?php
	}

	/**
	 * Display extensions
	 *
	 * @access public
	 * @return void
	 */
	public function output() {

		$extensions_data = $this->get_extensions_data();
		$plugins         = get_plugins();
		$names           = array();
		$statuses        = array();
		$extensions      = array(
			'core'    => array(
				'posts_locator'   => $extensions_data['posts_locator'],
				'members_locator' => $extensions_data['members_locator'],
			),
			'premium' => array(),
		);

		// rearrange extensions.
		foreach ( $extensions_data as $key => $value ) {

			if ( ! empty( $value['hide_in_extensions'] ) ) {

				unset( $extensions_data[ $key ] );

				continue;
			}

			if ( ! empty( $value['is_core'] ) ) {

				if ( 'posts_locator' !== $key && 'members_locator' !== $key ) {
					$extensions['core'][ $key ] = $value;
				}
			} else {

				$names[ $key ]                 = $value['name'];
				$statuses[ $key ]              = ! empty( $value['status'] ) ? $value['status'] : 'inactive';
				$extensions['premium'][ $key ] = $value;

				// phpcs:disable.
				/*if ( ! empty( $value['status'] ) ) {

					if ( 'active')
				}*/
			// phpcs:enable.

			}
		}

		// sort add-ons by status then by name.
		array_multisort( $statuses, SORT_ASC, $names, SORT_ASC, $extensions['premium'] );

		// extensions to exclude.
		$excluded_extensions = array(
			'formidable_geolocation',
			'geo_job_manager',
			'resume_manager_geo-location',
			'job_manager_geolocation_bundle',
			'gravity_forms_geo_fields',
		);

		// Use this filter to exclude extensions from the Extensions page.
		$exclude_extensions = apply_filters( 'gmw_extensions_page_exclude_extensions', $excluded_extensions, $extensions );

		// Exclude extensions.
		foreach ( $exclude_extensions as $exclude ) {
			unset( $extensions_data['core'][ $exclude ], $extensions_data['premium'][ $exclude ] );
		}
		?>
		<?php gmw_admin_pages_header(); ?>

		<!-- Extensions page wrapper -->
		<div id="gmw-extensions-page" class="wrap gmw-admin-page-content gmw-admin-page gmw-admin-page-wrapper">

			<?php gmw_admin_page_loader(); ?>

			<nav class="gmw-admin-page-navigation"></nav>

			<div class="gmw-admin-page-panels-wrapper">

				<h3 class="gmw-admin-page-title" style="margin-left: .25rem;margin-bottom:1.5rem">
					<?php echo esc_html__( 'Extensions & Licenses', 'geo-my-wp' ); ?>
					<span style="display: block;font-size: 14px;">
						<?php echo esc_html__( 'Manage extensions and activate your license keys', 'geo-my-wp' ); ?>
					</span>
				</h3>

				<h1 style="display:none;"></h1>

				<div id="gmw-admin-notices-holder"></div>

				<div class="gmw-admin-page-content-inner">

					<div id="gmw-extensions-tabs-wrapper">

						<?php
						$all_active     = '';
						$core_active    = '';
						$premium_active = '';

						if ( ! empty( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

							if ( 'core' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

								$core_active = 'active';

							} elseif ( 'premium' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

								$premium_active = 'active';

							} else {

								$all_active = 'active';
							}
						} else {
							$all_active = 'active';
						}

						?>
						<span class="<?php echo $all_active; // phpcs:ignore: XSS ok. ?>" data-type="all">
							<?php
							/* translators: %s total extensions count */
							printf( esc_html__( 'All ( %s )', 'geo-my-wp' ), count( $extensions['core'] + $extensions['premium'] ) );
							?>
						</span>
						<span class="<?php echo $core_active; // phpcs:ignore: XSS ok. ?>" data-type="core">
							<?php
							/* translators: %s total extensions count */
							printf( esc_html__( 'Core ( %s )', 'geo-my-wp' ), count( $extensions['core'] ) );
							?>
						</span>
						<span class="<?php echo $premium_active; // phpcs:ignore: XSS ok. ?>" data-type="premium">
							<?php
							/* translators: %s core extensions count */
							printf( esc_html__( 'Premium ( %s )', 'geo-my-wp' ), count( $extensions['premium'] ) );
							?>
						</span>

						<select id="gmw-extensions-status-filter" class="gmw-smartbox-not">
							<option value="">
								<?php esc_html_e( 'Status', 'geo-my-wp' ); ?>
							</option>
							<option value="active">
								<?php esc_html_e( 'Active', 'geo-my-wp' ); ?>
							</option>
							<option value="inactive">
								<?php esc_html_e( 'Inactive', 'geo-my-wp' ); ?>
							</option>
						</select>

						<form method="post" id="extensions-cache-form">

							<button type="submit" id="clear-extensions-cache-button"
								class="gmw-action-button button-primary"
								aria-label="<?php esc_attr_e( 'Clear the extensions cache if extensions fails to load properly on this page.', 'geo-my-wp' ); ?>" /><span
								class="gmw-icon gmw-icon-spin"></span>
							<?php esc_attr_e( 'Refresh extensions', 'geo-my-wp' ); ?></button>

							<input type="hidden" name="gmw_action" value="clear_extensions_cache" />

							<?php wp_nonce_field( 'gmw_clear_extensions_cache_nonce', 'gmw_clear_extensions_cache_nonce' ); ?>
						</form>
					</div>

					<div
						class="gmw-extensions-wrapper core <?php echo $core_active . ' ' . $all_active; // phpcs:ignore: XSS ok. ?>">

						<h3>
							<?php echo esc_html__( 'Core Extensions', 'geo-my-wp' ); ?>
						</h3>

						<div class="gmw-admin-notice-box gmw-admin-notice-warning">
							<?php esc_attr_e( 'The core extensions of GEO my WP are free to use. Activate any of the extensions and start using them now.', 'geo-my-wp' ); ?>
						</div>

						<div class="gmw-extensions-inner">
							<?php foreach ( $extensions['core'] as $extension ) { ?>
								<?php $this->extension_card( $extension, $plugins ); ?>
							<?php } ?>
						</div>
					</div>

					<div
						class="gmw-extensions-wrapper premium <?php echo $premium_active . ' ' . $all_active; // phpcs:ignore: XSS ok. ?>">

						<h3>
							<?php echo esc_html__( 'Premium Extensions', 'geo-my-wp' ); ?>
						</h3>

						<div class="gmw-admin-notice-box gmw-admin-notice-warning">

							<?php
							$allowed = array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							);

							/* translators: %s link to GEO my WP Extensions page. */
							$notice = sprintf( __( 'The premium extensions require a license key. Visit <a href="%s" target="_blank">GEO my WP\'s Extensions page</a> to learn more about each extension and to purchase a license key.', 'geo-my-wp' ), 'https://geomywp.com/extensions/' );

							echo '<span>' . wp_kses( $notice, $allowed ) . '</span>';
							?>
						</div>

						<?php /*
						<div id="gmw-access-pass-license-key-wrapper">

							<div id="gmw-access-pass-license-key-inner">

								<div id="gmw-access-pass-license-key-header">
									<h3><?php esc_attr_e( 'Access Pass License', 'geo-my-wp' ); ?></h3>
									<span><?php esc_attr_e( 'Use this form to bulk activate the license key and extensions that belong to your access pass.', 'geo-my-wp' ); ?></span>
								</div>

								<form method="post">

									<?php $license_key = ! empty( $_GET['license_key'] ) ? sanitize_text_field( wp_unslash( $_GET['license_key'] ) ) : ''; // phpcs:ignore:CSRF ok. ?>

									<input
										type="text"
										placeholder="<?php esc_attr_e( 'Access pass license key', 'geo-my-wp' ); ?>"
										id="access-pass-license-key" required="required" name="license_key" value="<?php echo esc_attr( $license_key ); ?>">

									<?php
									// phpcs:disable.
									/*<label class="gmw-checkbox-toggle-field">
										<input type="checkbox" id="setting-general_settings-auto_locate" class="gmw-form-field checkbox" name="activate_extensions" value="1">
										<span class="gmw-checkbox-toggle"></span>
									</label>
									<span class="gmw-checkbox-label">Activate Extensions</span> */
									// phpcs:enable.



									/*
									?>

									<button type="submit"
										id="gmw-access-pass-license-activation-button"
										class="gmw-action-button button-primary" />
									<?php esc_attr_e( 'Activate', 'geo-my-wp' ); ?></button>

									<input type="hidden" name="gmw_action" value="activate_access_pass_license" />

									<?php wp_nonce_field( 'gmw_activate_access_pass_license_nonce', 'gmw_activate_access_pass_license_nonce' ); ?>

									<?php
									if ( ! empty( $_GET['access_pass_activation_notice'] ) ) { // phpcs:ignore:CSRF ok.

										$messages = gmw_license_update_notices();

										if ( 'not_access_pass' === $_GET['access_pass_activation_notice'] ) { // phpcs:ignore:CSRF ok.
											$message = esc_attr__( 'The license key you entered is not an access pass.', 'geo-my-wp' );
										} else {
											$message = $messages[ sanitize_text_field( wp_unslash( $_GET['access_pass_activation_notice'] ) ) ]; // phpcs:ignore:CSRF ok.
										}
										?>
										<p class="error-message">
											<span><i class="dashicons dashicons-info"></i>
												<?php echo $message; // phpcs:ignore:XSS ok. ?>
											</span>
										</p>
										<?php
									}
									?>
								</form>
							</div>
						</div>
						*/
						?>

						<div class="gmw-extensions-inner">
							<?php foreach ( $extensions['premium'] as $extension ) { ?>
								<?php $this->extension_card( $extension, $plugins ); ?>
							<?php } ?>
						</div>
					</div>
				</div>

			</div>

			<div class="gmw-admin-page-sidebar">
				<?php gmw_admin_sidebar_content(); // WPCS: XSS ok. ?>
			</div>
		</div>
		<?php
		wp_enqueue_script( 'gmw-extensions', GMW_URL . '/assets/js/gmw.extensions.min.js', array( 'jquery' ), GMW_VERSION, true );
	}
}
add_action( 'wp_ajax_gmw_activate_extension', array( 'GMW_Extensions', 'activate_extension' ) );
add_action( 'wp_ajax_gmw_deactivate_extension', array( 'GMW_Extensions', 'deactivate_extension' ) );
add_action( 'wp_ajax_gmw_license_key_actions', array( 'GMW_Extensions', 'license_key_actions' ) );
// phpcs:ignore.
// add_action( 'wp_ajax_gmw_extensions_updater', array( 'GMW_Extensions', 'extensions_updater' ) );
