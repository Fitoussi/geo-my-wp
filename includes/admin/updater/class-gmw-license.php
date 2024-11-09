<?php
/**
 * License handler for GEO my WP.
 *
 * This class should simplify the process of adding license information
 * to GEO my WP add-ons.
 *
 * @author Eyal Fitoussi. Inspired by a class written by Pippin Williamson.
 *
 * @version 1.1
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// abort if this page already loaded.
if ( ! class_exists( 'GMW_License' ) ) :

	/**
	 * GMW_License Class
	 *
	 * Responsiable for updates of the premium add-ons as well for the action links
	 * of the Plugins page.
	 */
	class GMW_License {

		/**
		 * File.
		 *
		 * @var string
		 */
		private $file;

		/**
		 * License name.
		 *
		 * @var string
		 */
		private $license_name;

		/**
		 * Item name ( usually post's name in remote website ).
		 *
		 * @var string
		 */
		private $item_name;

		/**
		 * Item ID ( usually post's ID in remote website ).
		 *
		 * @var integer
		 */
		private $item_id;

		/**
		 * License key.
		 *
		 * @var [type]
		 */
		private $license_key;

		/**
		 * License status.
		 *
		 * @var [type]
		 */
		private $license_status;

		/**
		 * Plugin's version.
		 *
		 * @var string
		 */
		private $version;

		/**
		 * Author.
		 *
		 * @var string
		 */
		private $author;

		/**
		 * Remote URL.
		 *
		 * @var string
		 */
		private $api_url = 'https://geomywp.com';

		/**
		 * Action links.
		 *
		 * @var array
		 */
		private $action_links;

		/**
		 * Enable/disable license key box in plugin's page.
		 *
		 * @var boolean.
		 */
		private $plugins_page_license_enabled;

		/**
		 * Class constructor
		 *
		 * @param string  $_file         file name.
		 *
		 * @param string  $_item_name    item name.
		 *
		 * @param string  $_license_name license name.
		 *
		 * @param string  $_version      version.
		 *
		 * @param string  $_author       author.
		 *
		 * @param string  $_api_url      API URL.
		 *
		 * @param integer $_item_id      item ID.
		 */
		public function __construct( $_file, $_item_name, $_license_name, $_version, $_author = 'Eyal Fitoussi', $_api_url = null, $_item_id = null, $_action_links = array() ) {

			$this->file           = $_file;
			$this->license_name   = $_license_name;
			$this->item_name      = $_item_name;
			$this->item_id        = $_item_id;
			$this->license_key    = gmw_get_license_data( $_license_name );
			$this->license_status = gmw_get_license_data( $_license_name, 'status' );
			$this->version        = $_version;
			$this->author         = $_author;
			$this->api_url        = is_null( $_api_url ) ? $this->api_url : $_api_url;
			$this->action_links   = $_action_links;

			// run action.
			add_action( 'admin_init', array( $this, 'plugins_page_actions' ) );

			// Setup hooks.
			$this->includes();
			$this->auto_updater();
		}

		/**
		 * Enable/disable license key in plugin's page.
		 */
		public function plugins_page_actions() {

			$basename                           = plugin_basename( $this->file );
			$this->plugins_page_license_enabled = apply_filters( 'gmw_plugins_page_license_key_enabled', false, $basename );

			// Enable it for the Formidable Geolocation plugin.
			if ( 'formidable-geolocation/formidable-geolocation.php' === $basename ) {
				$this->plugins_page_license_enabled = true;
			}

			// action links.
			add_filter( 'plugin_action_links_' . $basename, array( $this, 'extension_action_links' ), 10 );

			// license key input in plugins page is disabled by default.
			if ( $this->plugins_page_license_enabled ) {
				add_action( 'after_plugin_row_' . $basename, array( $this, 'license_key_element' ), 10 );
			}
		}

		/**
		 * Add gmw add-ons action links in plugins page.
		 *
		 * @param  array $links array of links.
		 *
		 * @return $links
		 */
		public function extension_action_links( $links ) {

			if ( empty( $this->action_links ) ) {
				return $links;
			}

			if ( is_array( $this->action_links ) ) {

				foreach ( $this->action_links as $key => $action_link ) {

					$target = ! empty( $action_link['new_page'] ) ? 'target="_blank"' : '';

					$links[ sanitize_key( $key ) ] = '<a href="' . $action_link['link'] . '" ' . $target . '>' . esc_attr( $action_link['label'] ) . '</a>';

					if ( ! $this->plugins_page_license_enabled ) {

						// if license is not activated display the "Activate License" message.
						if ( empty( $this->license_key ) || 'valid' !== $this->license_status ) {

							$action = 'active_license';
							$text   = __( 'Activate License', 'geo-my-wp' );
							$color  = 'red';

						} else {

							$action = 'deactive_license';
							$text   = __( 'Deactivate License', 'geo-my-wp' );
							$color  = 'green';
						}

						$links[ $action ] = '<a style="color:' . $color . '" href="' . admin_url( 'admin.php?page=gmw-extensions&tab=premium' ) . '">' . $text . '</a>';

					} else {

						// if license is not activated display the "Activate License" message.
						if ( ! empty( $this->license_key ) && 'valid' === $this->license_status ) {

							$links['deactivate_license'] = '<a href="#" style="color:green" onclick="event.preventDefault();jQuery( this ).closest( \'.gmw-license-key-addon-wrapper\' ).next().find( \'.gmw-license-wrapper\' ).show();">' . __( 'Dectivate license', 'geo-my-wp' ) . '</a>';
						}
					}
				}

				return $links;

			} elseif ( 'gmw_action_links' === $this->action_links ) {

				$links['settings'] = '<a href="' . admin_url( 'admin.php?page=gmw-settings' ) . '">' . __( 'Settings', 'geo-my-wp' ) . '</a>';

				$docs_link = '<a href="https://docs.geomywp.com/" target="_blank">' . __( 'Documentation', 'geo-my-wp' ) . '</a>';

				if ( ! $this->plugins_page_license_enabled ) {

					// if license is not activated display the "Activate License" message.
					if ( empty( $this->license_key ) || 'valid' !== $this->license_status ) {

						$action = 'active_license';
						$text   = __( 'Activate License', 'geo-my-wp' );
						$color  = 'red';

					} else {

						$action = 'deactive_license';
						$text   = __( 'Deactivate License', 'geo-my-wp' );
						$color  = 'green';
					}

					$links[ $action ] = '<a style="color:' . $color . '" href="' . admin_url( 'admin.php?page=gmw-extensions&tab=premium' ) . '">' . $text . '</a>';

					$links['docs'] = $docs_link;

				} else {

					$links['extensions'] = '<a href="' . admin_url( 'admin.php?page=gmw-extensions' ) . '">' . __( 'Extensions', 'geo-my-wp' ) . '</a>';

					$links['docs'] = $docs_link;

					// if license is not activated display the "Activate License" message.
					if ( ! empty( $this->license_key ) && 'valid' === $this->license_status ) {

						$links['deactivate_license'] = '<a href="#" style="color:green" onclick="event.preventDefault();jQuery( this ).closest( \'.gmw-license-key-addon-wrapper\' ).next().find( \'.gmw-license-wrapper\' ).show();">' . __( 'Dectivate license', 'geo-my-wp' ) . '</a>';
					}
				}

				return $links;
			}

			return $link;

		}

		/**
		 * Append license key input box in plugins page.
		 */
		public function license_key_element() {

			if ( class_exists( 'GMW_License_Key' ) ) {

				$license_key = new GMW_License_Key(
					$this->file,
					$this->item_name,
					$this->license_name,
					$this->item_id
				);
			}

			$license_key->license_key_output();
		}

		/**
		 * Include the updater class
		 *
		 * @access  private
		 * @return  void
		 */
		private function includes() {
			if ( ! class_exists( 'GMW_Premium_Plugin_Updater' ) && file_exists( 'class-gmw-plugins-updater.php' ) ) {
				require_once 'class-gmw-plugins-updater.php';
			}
		}

		/**
		 * Auto updater
		 *
		 * @access  private
		 * @return  void
		 */
		private function auto_updater() {

			if ( empty( $this->license_key ) ) {
				return;
			}

			if ( 'valid' !== $this->license_status ) {
				return;
			}

			if ( class_exists( 'GMW_Premium_Plugin_Updater' ) ) {

				// Setup the updater.
				$gmw_updater = new GMW_Premium_Plugin_Updater(
					$this->api_url,
					$this->file,
					array(
						'version'   => $this->version,
						'license'   => $this->license_key,
						'item_name' => $this->item_name,
						'item_id'   => $this->item_id,
						'author'    => $this->author,
					)
				);
			}
		}
	}

	/**
	 * GMW_License_Key input field Class
	 *
	 * Create input field for a license key.
	 */
	class GMW_License_Key {

		/**
		 * File name.
		 *
		 * @var [type]
		 */
		private $file;

		/**
		 * Item name.
		 *
		 * @var [type]
		 */
		private $item_name;

		/**
		 * License Name.
		 *
		 * @var [type]
		 */
		private $license_name;

		/**
		 * Item ID.
		 *
		 * @var [type]
		 */
		private $item_id;

		/**
		 * Basename.
		 *
		 * @var [type]
		 */
		private $basename;

		/**
		 * Messages.
		 *
		 * @var [type]
		 */
		private $messages;

		/**
		 * Temporary items id holder.
		 *
		 * This is for older verison where the item ID is not provided
		 * with the extension
		 *
		 * @var array
		 */
		public static $item_ids = array(
			'ajax_forms'                       => 147860,
			'bp_groups_locator'                => 4647,
			'groups_locator'                   => 4647, // old slug.
			'bp_members_directory_geolocation' => 2347,
			'geo_members_directory'            => 2347, // old slug.
			'bp_xprofile_geolocation'          => 670,
			'xprofile_fields'                  => 670, // old slug.
			'exclude_locations'                => 800,
			'exclude_members'                  => 800, // old slug.
			'global_maps'                      => 2602,
			'gmw_kleo_geolocation'             => 42902,
			'nearby_locations'                 => 7991,
			'nearby_posts'                     => 7991, // old slug.
			'premium_settings'                 => 668,
			'users_locator'                    => 11188,
			'wp_users_geo-location'            => 11188, // old slug.
			'formidable_geolocation'           => 54725,
			'gravity_forms_geo_fields'         => 2273,
			'geo_job_manager'                  => 5417,
			'resume_manager_geo-location'      => 8547,
		);

		/**
		 * [__construct description]
		 *
		 * @param string  $file         file.
		 * @param string  $item_name    item name.
		 * @param string  $license_name license name.
		 * @param integer $item_id      item ID.
		 */
		public function __construct( $file, $item_name, $license_name, $item_id = null ) {

			$this->file           = basename( dirname( $file ) );
			$this->basename       = plugin_basename( $file );
			$this->item_name      = $item_name;
			$this->item_id        = $item_id;
			$this->license_name   = $license_name;
			$this->license_key    = gmw_get_license_data( $license_name );
			$this->license_status = gmw_get_license_data( $license_name, 'status' );
			$this->messages       = gmw_license_update_notices();

			// if item ID missing get it from the array of items id.
			if ( empty( $this->item_id ) && ! empty( self::$item_ids[ $license_name ] ) ) {
				$this->item_id = $this->item_ids[ $license_name ];
			}
		}

		/**
		 * Generate license key element
		 *
		 * @return [type] [description]
		 */
		public function get_license_key_element() {

			// check if in plugins page.
			$plugins_page   = ( ! empty( get_current_screen()->base ) && 'plugins' === get_current_screen()->base ) ? true : false;
			$output         = '';
			$license_name   = esc_attr( $this->license_name );
			$item_name      = esc_attr( $this->item_name );
			$item_id        = esc_attr( $this->item_id );
			$basename       = esc_attr( $this->basename );
			$nonce          = wp_create_nonce( 'gmw_' . $license_name . '_license_nonce' );
			$license_value  = ! empty( $this->license_key ) ? esc_attr( sanitize_text_field( $this->license_key ) ) : '';
			$expires        = gmw_get_license_data( $license_name, 'expires' );
			$expires        = ! empty( $expires ) ? gmdate( 'm/d/Y', strtotime( $expires ) ) : '';
			$action         = '';
			$data_attr      = '';
			$class          = '';
			$activations    = '';
			$expired_output = '';

			// if license valid.
			if ( ! empty( $this->license_key ) && 'valid' === $this->license_status ) {

				// generate data.
				$action         = 'deactivate_license';
				$label          = __( 'Deactivate license', 'geo-my-wp' );
				$updating_label = __( 'Deactivating license', 'geo-my-wp' );
				$updated_label  = __( 'License deactivated', 'geo-my-wp' );
				$failed_label   = __( 'Deactivation failed', 'geo-my-wp' );
				$message        = esc_html( $this->messages['valid'] );
				$icon           = '<i class="dashicons dashicons-yes-alt"></i>';
				$status         = 'valid';
				$expired_output = ! empty( $expires ) ? '<span>' . __( 'Valid untill', 'geo-my-wp' ) . ' ' . $expires . '</span>' : '';
				$class         .= 'deactivate';

				// hidden input fields.
				$key_field  = '<input class="gmw-license-key-disabled" disabled="disabled" type="password" size="31" value="' . $license_value . '" />';
				$key_field .= '<input type="hidden" class="gmw-license-key" name="gmw_licenses[' . $license_name . '][license_key]" value="' . $license_value . '" />';

			} else {

				// generate data.
				$action         = 'activate_license';
				$message        = $this->messages['activate'];
				$label          = __( 'Activate license', 'geo-my-wp' );
				$updating_label = __( 'Activating license', 'geo-my-wp' );
				$updated_label  = __( 'License activated', 'geo-my-wp' );
				$failed_label   = __( 'Activation failed', 'geo-my-wp' );
				$allow          = array(
					'a' => array(
						'href'  => array(),
						'title' => array(),
					),
				);
				$message = wp_kses( $message, $allow );
				$icon    = '<i class="dashicons dashicons-info"></i>';
				$status  = 'inactive';
				$class  .= 'activate';

				// generate error message.
				if ( ! empty( $this->license_key ) && ! empty( $this->license_status ) && 'inactive' !== $this->license_status ) {

					$status .= ' gmw-license-error';

					if ( 'expired' === $this->license_status ) {

						if ( ! empty( $expires ) ) {

							$message = sprintf( __( 'License expired on %s.', 'geo-my-wp' ), $expires );

						} else {

							$message = __( 'License expired.', 'geo-my-wp' );
						}

						$message .= sprintf( __( ' <a href="%s" target="_blank">Renew your license key now</a> to receive support and updates.', 'geo-my-wp' ), 'https://geomywp.com/your-account/' );

					} else {
						$message = array_key_exists( $this->license_status, $this->messages ) ? $this->messages[ $this->license_status ] : $this->messages['missing'];
					}

					$icon = '<i class="dashicons dashicons-info"></i>';
				}

				// generate input fields.
				$key_field = '<input  class="gmw-license-key" name="gmw_licenses[' . $license_name . '][license_key]" type="text" class="regular-text" size="31" placeholder="' . __( 'Enter your license key then activated it', 'geo-my-wp' ) . '" value="' . $license_value . '" />';
			}

			// if not in plugins page.
			if ( ! $plugins_page ) {

				$data_attr  = ' data-action="' . $action . '"';
				$data_attr .= ' data-license_name="' . $license_name . '"';
				$data_attr .= ' data-item_id="' . $item_id . '"';
				$data_attr .= ' data-item_name="' . $item_name . '"';
				$data_attr .= ' data-nonce="' . $nonce . '"';
				$data_attr .= ' data-basename="' . $basename . '"';
				$data_attr .= ' data-updating_message="' . $updating_label . '"';
				$data_attr .= ' data-updated_message="' . $updated_label . '"';
				$data_attr .= ' data-failed_message="' . $failed_label . '"';
				$data_attr .= ' data-label="' . $label . '"';
			}

			// generate the license element.
			$output .= '<div class="gmw-license-wrapper ' . $status . '">';
			$output .= '<div class="field-wrapper">';
			$output .= $key_field;

			$output .= '<div class="actions-wrapper">';
			$output .= '<button type="submit" name="gmw_license_submit"';
			$output .= ' class="' . $class . ' ' . $action . ' gmw-license-action-button gmw-action-toggle-button gmw-atb-secondary"';
			$output .= ' value="' . $license_name . '"';
			$output .= ' ' . $data_attr . '>';
			$output .= '<span class="gmw-atb-toggle"></span>';
			$output .= '<span class="gmw-atb-label">' . $label . '</span></button>';
			$output .= '</div>';
			$output .= '</div>';
			$output .= '<p class="description"><span>' . $icon . $message . '</span>' . $expired_output . '</p>';

			$output .= '<input type="hidden" name="gmw_licenses[' . $license_name . '][action]" value="' . $action . '" />';
			$output .= '<input type="hidden" name="gmw_licenses[' . $license_name . '][nonce]" value="' . $nonce . '" />';
			$output .= '<input type="hidden" name="gmw_licenses[' . $license_name . '][license_name]" value ="' . $license_name . '" />';
			$output .= '<input type="hidden" name="gmw_licenses[' . $license_name . '][item_id]" value="' . $item_id . '" />';
			$output .= '<input type="hidden" name="gmw_licenses[' . $license_name . '][item_name]" value="' . $item_name . '" />';
			$output .= '</div>';

			return $output;
		}

		/**
		 * Display license key field in plugins page
		 */
		public function license_key_output() {

			$file = esc_attr( $this->file );
			?>
			<tr id="<?php echo $file; // WPCS: XSS ok. ?>-license-key-row" class="gmw-license-key-row">

				<td class="plugin-update" colspan="3">

					<?php echo $this->get_license_key_element(); // WPCS: XSS ok. ?>

					<script>
						jQuery( function($) {

							onkeydown="if (event.keyCode == 13) { jQuery(this).closest(\'form\').find( \'.activate-license-btn\' ).click(); return false; }";

							$( 'tr#<?php echo $file; // WPCS: XSS ok. ?>-license-key-row' ).prev().addClass( 'gmw-license-key-addon-wrapper' );

							if ( $( 'tr#<?php echo $file; // WPCS: XSS ok. ?>-license-key-row' ).prev().hasClass( 'update' ) ) {

								$( 'tr#<?php echo $file; // WPCS: XSS ok. ?>-license-key-row' ).addClass( 'update' );
							}

							$( 'tr#<?php echo $file; // WPCS: XSS ok. ?>-license-key-row' ).find( '.gmw-license-action-button' ).click( function() {

								jQuery( this ).closest( 'tr' ).prev( 'tr' ).find( 'th input[type=checkbox]' ).prop( 'checked', true );
							});

							$( 'tr#<?php echo $file; // WPCS: XSS ok. ?>-license-key-row' ).find( '.gmw-license-key' ).on( 'keydown', function( e ) {
								if ( e.keyCode == 13 ) {
									jQuery( this ).closest( 'tr' ).prev( 'tr' ).find( 'th input[type=checkbox]' ).prop( 'checked', true );
								}
							});

						});
					</script>

				</td>
			</tr>

			<?php
			if ( ! wp_style_is( 'gmw-updater', 'enqueued' ) ) {
				wp_enqueue_style( 'gmw-updater', untrailingslashit( plugins_url( '', __FILE__ ) ) . '/assets/css/gmw.updater.css', array() );
			}
		}
	}

	/**
	 * [gmw_get_license_data Get license key or status
	 *
	 * @param  string $license_name license slug/name.
	 * @param  string $data         key || status.
	 *
	 * @return [type]
	 */
	function gmw_get_license_data( $license_name = '', $data = 'key' ) {

		if ( empty( $license_name ) ) {
			return false;
		}

		$license_keys = get_option( 'gmw_license_data' );

		if ( 'status' === $data ) {

			$output = ! empty( $license_keys[ $license_name ]['status'] ) ? $license_keys[ $license_name ]['status'] : 'inactive';

		} elseif ( 'expires' === $data ) {

			$output = ! empty( $license_keys[ $license_name ]['expires'] ) ? $license_keys[ $license_name ]['expires'] : '';

		} elseif ( 'activations_left' === $data ) {

			$output = ! empty( $license_keys[ $license_name ]['activations_left'] ) ? $license_keys[ $license_name ]['activations_left'] : '';

		} else {

			$output = ! empty( $license_keys[ $license_name ]['key'] ) ? trim( $license_keys[ $license_name ]['key'] ) : '';
		}

		return $output;
	}

	/**
	 * Check license status
	 *
	 * @param string $addon addon slug.
	 *
	 * @return boolean
	 */
	function gmw_is_license_valid( $addon ) {

		$license_keys = get_option( 'gmw_license_data' );

		if ( ! empty( $license_keys[ $addon ]['key'] ) && ! empty( $license_keys[ $addon ]['status'] ) && 'valid' === $license_keys[ $addon ]['status'] ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * GMW Cheack Licenses
	 *
	 * Do check of licenses every 7 days to varify that thier status is up tp date.
	 *
	 * @since  2.5
	 *
	 * @author Eyal Fitoussi
	 */
	function gmw_check_license() {

		if ( apply_filters( 'gmw_disable_auto_license_key_verification', false ) ) {
			return;
		}

		$license_trans = get_transient( 'gmw_verify_license_keys' );

		// run licenses check every 7 days just to make sure that their status is up to date.
		if ( ! empty( $license_trans ) ) {
			return;
		}

		// set new transient.
		set_transient( 'gmw_verify_license_keys', true, DAY_IN_SECONDS * 7 );

		// get license keys.
		$license_keys = get_option( 'gmw_license_data' );

		if ( empty( $license_keys ) ) {
			return;
		}

		// loop through and check all license keys.
		foreach ( $license_keys as $license_name => $values ) {

			// If GEO my WP exists, use its function.
			if ( function_exists( 'gmw_get_addon_data' ) ) {

				$addon_data         = gmw_get_addon_data( $license_name );
				$addon_license_data = gmw_get_addon_license_data( $license_name );
				$item_id            = '';
				$item_name          = '';

				if ( ! empty( $addon_data['item_id'] ) ) {
					$item_id = absint( $addon_data['item_id'] );
				} elseif ( ! empty( $addon_license_data['item_id'] ) ) {
					$item_id = absint( $addon_license_data['item_id'] );
				}

				if ( ! empty( $addon_data['item_name'] ) ) {
					$item_name = urlencode( $addon_data['item_name'] );
				} elseif ( ! empty( $addon_license_data['item_name'] ) ) {
					$item_name = urlencode( $addon_license_data['item_name'] );
				}

				//$item_id    = ! empty( $addon_data['item_id'] ) ? absint( $addon_data['item_id'] ) : '';
				//$item_name  = ! empty( $addon_data['item_name'] ) ? urlencode( $addon_data['item_name'] ) : '';

				// Otherwise, for stand alone plugins.
			} else {
				$item_id   = ! empty( GMW_License_Key::$item_ids[ $license_name ] ) ? absint( GMW_License_Key::$item_ids[ $license_name ] ) : '';
				$item_name = $license_name;
			}

			$license_key    = trim( $values['key'] );
			$license_status = $values['status'];

			if ( ! empty( $license_key ) ) {

				$api_params = array(
					'edd_action' => 'check_license',
					'license'    => $license_key,
					'item_id'    => $item_id,
					'url'        => home_url(),
					'item_name'  => '',
				);

				if ( empty( $api_params['item_id'] ) && ! empty( $item_name ) ) {
					$api_params['item_name'] = $item_name;
				}

				// Call the custom API.
				$response = wp_remote_post(
					GMW_REMOTE_SITE_URL,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params,
					)
				);

				if ( is_wp_error( $response ) ) {
					return false;
				}

				// get license data.
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// update license status if changed.
				if ( $license_data->license !== $license_status ) {
					$license_keys[ $license_name ]['status'] = $license_data->license;
				}
			}
		}

		// update new data to database.
		update_option( 'gmw_license_data', $license_keys );

	}
	add_action( 'admin_init', 'gmw_check_license' );

	/**
	 * GMW Update license key API activate/deactivate
	 *
	 * @param array $form_args form args.
	 *
	 * @since  2.5
	 *
	 * @author Eyal Fitoussi
	 */
	function gmw_license_key_actions( $form_args = array(), $response = false ) {

		// default args.
		$defaults = array(
			'action'       => 'activate_license',
			'license_name' => false,
			'item_id'      => false,
			'license_key'  => '',
			'item_name'    => false,
		);

		$form_args = wp_parse_args( $form_args, $defaults );

		// verify that at least item name and license data exist.
		if ( empty( $form_args['item_id'] ) || empty( $form_args['license_name'] ) ) {
			return;
		}

		// get licenses data from database.
		$license_keys = get_option( 'gmw_license_data' );

		$action       = $form_args['action'];
		$license_name = $form_args['license_name'];
		$license_key  = sanitize_text_field( trim( $form_args['license_key'] ) );
		$item_name    = $form_args['item_name'];
		$item_id      = ! empty( $form_args['item_id'] ) ? $form_args['item_id'] : false;
		$license_data = (object) array();

		// if license key field is empty and trying to activate, clear key in database.
		if ( empty( $license_key ) && 'activate_license' === $action ) {

			unset( $license_keys[ $license_name ] );

			update_option( 'gmw_license_data', $license_keys );

			$license_data->license_name      = $form_args['license_name'];
			$license_data->notice_message    = 'no_key_entered';
			$license_data->notice_action     = 'error';
			$license_data->remote_connection = 'blank_key';

			return $license_data;
		}

		if ( empty( $license_key ) ) {
			return $license_data;
		}

		// data to send in our API request.
		$api_params = array(
			'edd_action' => $action,
			'license'    => $license_key,
			'item_name'  => urlencode( $item_name ),
			'item_id'    => $item_id,
		);

		if ( empty( $response ) ) {

			// Call the custom API.
			$response = wp_remote_post(
				GMW_REMOTE_SITE_URL,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);
		}

		// If connection failed.
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$license_data                    = $response;
			$license_data->remote_connection = false;
			$license_data->license_name      = $form_args['license_name'];
			$license_data->notice_message    = 'connection_failed';
			$license_data->notice_action     = 'error';

			// otherwise, if succeed.
		} else {

			// decode the license data.
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			$license_data->remote_connection = true;
			$license_data->license_name      = $form_args['license_name'];

			if ( 'valid' === $license_data->license ) {

				$license_data->notice_message = 'activated';
				$license_data->notice_action  = 'updated';

				$license_keys[ $license_name ]['key']              = $license_key;
				$license_keys[ $license_name ]['status']           = 'valid';
				$license_keys[ $license_name ]['expires']          = ! empty( $license_data->expires ) ? $license_data->expires : '';
				$license_keys[ $license_name ]['activations_left'] = ! empty( $license_data->activations_left ) ? $license_data->activations_left : '';

				update_option( 'gmw_license_data', $license_keys );

			} elseif ( 'invalid' === $license_data->license ) {

				$license_data->notice_message = $license_data->error;
				$license_data->notice_action  = 'error';

				$license_keys[ $license_name ]['key']              = $license_key;
				$license_keys[ $license_name ]['status']           = $license_data->error;
				$license_keys[ $license_name ]['expires']          = ! empty( $license_data->expires ) ? $license_data->expires : '';
				$license_keys[ $license_name ]['activations_left'] = ! empty( $license_data->activations_left ) ? $license_data->activations_left : '';


				update_option( 'gmw_license_data', $license_keys );

			} elseif ( 'deactivated' === $license_data->license || 'failed' === $license_data->license ) {

				$license_data->notice_message = 'deactivated';
				$license_data->notice_action  = 'updated';

				$license_keys[ $license_name ]['key']              = $license_key;
				$license_keys[ $license_name ]['status']           = 'inactive';
				$license_keys[ $license_name ]['expires']          = ! empty( $license_data->expires ) ? $license_data->expires : '';
				$license_keys[ $license_name ]['activations_left'] = ! empty( $license_data->activations_left ) ? $license_data->activations_left : '';

				update_option( 'gmw_license_data', $license_keys );
			}
		}

		return $license_data;
	}

	/**
	 * To be used with license key action on page load
	 *
	 * @return [type] [description]
	 */
	function gmw_pre_license_key_actions() {

		// check for license data.
		if ( empty( $_POST['gmw_license_submit'] ) || empty( $_POST['gmw_licenses'][ $_POST['gmw_license_submit'] ] ) ) {
			return;
		}

		// current page.
		$page = ( isset( $_GET['page'] ) && 'gmw-extensions' === $_GET['page'] ) ? 'admin.php?page=gmw-extensions&' : 'plugins.php?';

		// get license data.
		$license_data = map_deep( wp_unslash( $_POST['gmw_licenses'][ $_POST['gmw_license_submit'] ] ), 'sanitize_text_field' );

		// varify nonce.
		if ( empty( $license_data['nonce'] ) || ! wp_verify_nonce( $license_data['nonce'], 'gmw_' . $license_data['license_name'] . '_license_nonce' ) ) {
			wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) ); // phpcs:ignore CSRF ok.
		}

		// run license action.
		$license_data = gmw_license_key_actions( $license_data );

		$url = $page . 'gmw_license_status_notice=' . esc_attr( $license_data->notice_message ) . '&license_name=' . esc_attr( $license_data->license_name ) . '&gmw_notice_status=' . esc_attr( $license_data->notice_action );

		// reload the page to prevent resubmission.
		wp_safe_redirect(
			admin_url( $url )
		);

		exit;
	}
	add_action( 'admin_init', 'gmw_pre_license_key_actions' );

	/**
	 * Messages for license status and notices
	 *
	 * @since  2.5
	 *
	 * @author Eyal Fitoussi
	 */
	function gmw_license_update_notices() {

		return apply_filters(
			'gmw_license_update_notices',
			array(
				//'activate'            => __( 'Activate your license key to receive support and updates.', 'geo-my-wp' ),
				'activate'             => __( 'Activate your license key to start using the extension.', 'geo-my-wp' ),
				'license_key_inactive' => __( 'Activate your license key to start using the extension.', 'geo-my-wp' ),
				'inactive'             => __( 'Activate your license key to start using the extension.', 'geo-my-wp' ),
				'activated'            => __( 'License activated. Thank you for your support!', 'geo-my-wp' ),
				'deactivated'          => __( 'License deactivated.', 'geo-my-wp' ),
				'valid'                => __( 'License activated. Thank you for your support!', 'geo-my-wp' ),
				'no_key_entered'       => __( 'No license key entered.', 'geo-my-wp' ),
				'expired'              => sprintf( __( 'License key expired. You can still use the extension, but you should <a href="%s" target="_blank">renew your license</a> to receive updates and support.', 'geo-my-wp' ), 'https://geomywp.com/your-account/' ),
				'revoked'              => sprintf( __( 'license disabled. Contact <a href="%s" target="_blank">support</a> for more information.', 'geo-my-wp' ), 'https://geomywp.com/support/#extension-support' ),
				'missing'              => sprintf( __( 'Something is wrong with the license key you entered. <a href="%s" target="_blank">Verify your license key</a>.', 'geo-my-wp' ), 'https://geomywp.com/your-account/' ),
				'disabled'             => sprintf( __( 'License disabled by the provider. <a href="%s" target="_blank">Contact support</a> for assistance.', 'geo-my-wp' ), 'https://geomywp.com/support/' ),
				'invalid'              => __( 'Your license is not active for this domain.', 'geo-my-wp' ),
				'site_inactive'        => __( 'Your license is not active for this domain.', 'geo-my-wp' ),
				'invalid_item_id'      => __( 'The license key you entered does not belong to this extension.', 'geo-my-wp' ),
				'item_name_mismatch'   => __( 'An error occurred while trying to activate your license. ERROR item_name_mismatch', 'geo-my-wp' ),
				'no_activations_left'  => sprintf( __( 'License key has reached its activation limit. <a %s>Manage licenses</a>.', 'geo-my-wp' ), 'href="https://geomywp.com/your-account/" target="_blank"' ),

				'retrieve_key'         => sprintf( __( 'Lost or forgot your license key? <a %s >Retrieve it here.</a>', 'geo-my-wp' ), 'href="http://geomywp.com/purchase-history/" target="_blank"' ),
				'activation_error'     => __( 'Your license for %s plugin could not be activated. See error message below.', 'geo-my-wp' ),
				'default'              => sprintf( __( 'An error occurred. Try again or contact <a href="%s" target="_blank">support</a>.', 'geo-my-wp' ), 'https://geomywp.com/support/#extension-support' ),
				'connection_failed'    => sprintf( __( 'Connection to remote server failed. Try again or contact <a href="%s" target="_blank">support</a>.', 'geo-my-wp' ), 'https://geomywp.com/support/#general-questions' ),
			)
		);
	}

	/**
	 * Generate notices
	 *
	 * @return [type] [description]
	 */
	function gmw_display_license_update_notice() {

		// check if updating license key.
		if ( empty( $_GET['gmw_license_status_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
			return;
		}

		$messages      = gmw_license_update_notices();
		$message       = sanitize_text_field( wp_unslash( $messages[ $_GET['gmw_license_status_notice'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		$allowed_html  = array( 'a' => array( 'href' => array() ) );
		$notice_status = ! empty( $_GET['gmw_notice_status'] ) ? sanitize_text_field( wp_unslash( $_GET['gmw_notice_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.
		?>
		<div class="<?php echo esc_attr( $notice_status ); ?>">
			<p>
				<?php echo wp_kses( $message, $allowed_html ); ?>
			</p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'gmw_display_license_update_notice' );

endif;
