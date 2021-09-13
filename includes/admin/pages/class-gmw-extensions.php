<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Extensions class
 */
class GMW_Extensions {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Abort if not add-ons page
		if ( empty( $_GET['page'] ) || 'gmw-extensions' != $_GET['page'] ) {
			return;
		}

		add_action( 'gmw_activate_extension', array( $this, 'activate_extension' ) );
		add_action( 'gmw_deactivate_extension', array( $this, 'deactivate_extension' ) );
		add_action( 'gmw_updater_action', array( $this, 'extensions_updater' ) );
		add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
		add_action( 'gmw_clear_extensions_cache', array( $this, 'clear_extensions_cache' ) );
	}

	/**
	 * GMW Function - add notice messages
	 *
	 * @access public
	 * @since 2.5
	 * @author Eyal Fitoussi
	 *
	 */
	public function notices_messages( $messages ) {

		$messages['updater_enabled']             = __( 'Extensions updater enabled.', 'geo-my-wp' );
		$messages['updater_disabled']            = __( 'Extensions updater disabled.', 'geo-my-wp' );
		$messages['extension_deactivated']       = __( 'Extension deactivated.', 'geo-my-wp' );
		$messages['extension_activated']         = __( 'Extension activated.', 'geo-my-wp' );
		$messages['extension_activation_failed'] = __( 'Extension activation failed.', 'geo-my-wp' );
		$messages['extensions_cache_cleared']    = __( 'Extensions cache cleared.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Add-ons feed from GEO my WP
	 *
	 * @access private
	 * @return void
	 */
	private static function get_extensions_feed() {

		// look for extensions feed in transient. Transient should clear every 24 hours
		if ( false === ( $output = get_transient( 'gmw_extensions_feed' ) ) ) {

			$feed = wp_remote_get( 'https://geomywp.com/extensions/?feed=extensions', array( 'sslverify' => false ) );

			if ( ! is_wp_error( $feed ) && $feed['response']['code'] == '200' ) {

				if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {

					$output = wp_remote_retrieve_body( $feed );
					$output = simplexml_load_string( $output );
					$output = json_decode( json_encode( (array) $output ), true );

					set_transient( 'gmw_extensions_feed', $output, DAY_IN_SECONDS );
				}
			} else {

				$feed = (array) $feed;

				if ( ! empty( $feed['response'] ) ) {

					$message = 'Error Code: ' . $feed['response']['code'];

				} elseif ( ! empty( $feed['errors']['http_request_failed'][0] ) ) {

					$message = 'Error message: ' . $feed['errors']['http_request_failed'][0];
				}

				echo '<div class="error"><p>' . __( 'There was an error retrieving the add-ons list from the server. Please try again later.', 'geo-my-wp' ) . $message . '</p></div>';

				$output = false;
			}
		};

		return $output;
	}

	/**
	 * Enable / Disable extension updater.
	 *
	 * @access public
	 * @return void
	 */
	public static function extensions_updater() {

		// if doing ajax call
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			//verify nonce
			if ( ! check_ajax_referer( 'gmw_extension_updater_nonce', 'security', false ) ) {
				wp_die(
					__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			$action = $_POST['updater_action'];

			// Otherwise, page load call
		} else {

			// verify nonce
			if ( ! check_admin_referer( 'gmw_extension_updater_nonce' ) ) {
				wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}

			if ( empty( $_GET['action'] ) ) {
				return;
			}

			$action = $_GET['action'];
		}

		// enable updater
		if ( 'enable' == $action ) {

			update_option( 'gmw_extensions_updater', true );

			$notice = 'updater_enabled';

			// disable updater
		} else {

			update_option( 'gmw_extensions_updater', false );

			$notice = 'updater_disabled';
		}

		// done
		if ( defined( 'DOING_AJAX' ) ) {

			wp_send_json( $notice );

		} else {

			wp_safe_redirect( admin_url( 'admin.php?page=gmw-extensions&gmw_notice=' . $notice . '&gmw_notice_status=updated' ) );
			exit;
		}
	}

	/**
	 * Clear Extensions cache
	 *
	 * @access public
	 * @return void
	 */
	public function clear_extensions_cache() {

		//make sure we activated an add-on
		if ( empty( $_POST['gmw_clear_extensions_cache'] ) ) {
			return;
		}

		//varify nonce
		if ( empty( $_POST['gmw_clear_extensions_cache_nonce'] ) || ! wp_verify_nonce( $_POST['gmw_clear_extensions_cache_nonce'], 'gmw_clear_extensions_cache_nonce' ) ) {
			wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// delete extensions and license key transient to retrive new data.
		delete_transient( 'gmw_extensions_feed' );
		//delete_transient( 'gmw_verify_license_keys' );

		//reload the page to prevent resubmission
		wp_safe_redirect(
			admin_url( 'admin.php?page=gmw-extensions&gmw_notice=extensions_cache_cleared&gmw_notice_status=updated' )
		);

		exit;
	}

	/**
	 * Generate Activate extension button
	 *
	 * @param  string $slug     extension slug
	 * @param  string $basename extension basename
	 *
	 * @return HTML link
	 */
	public static function activate_extension_button( $slug = '', $basename = '' ) {

		$nonce = wp_create_nonce( 'gmw_' . $slug . '_extension_nonce' );
		$url   = admin_url( 'admin.php?page=gmw-extensions&gmw_action=activate_extension&basename=' . $basename . '&slug=' . $slug . '&_wpnonce=' . $nonce );
		$label = __( 'Activate', 'geo-my-wp' );

		$output  = '<a href="' . esc_url( $url ) . '"';
		$output .= ' class="button button-primary gmw-extension-action-button activate"';
		$output .= ' data-slug="' . esc_attr( $slug ) . '"';
		$output .= ' data-basename="' . esc_attr( $basename ) . '"';
		$output .= ' data-action="activate_extension"';
		$output .= ' data-nonce="' . $nonce . '"';
		$output .= ' data-updating_message="' . __( 'Activating', 'geo-my-wp' ) . '"';
		$output .= ' data-updated_message="' . __( 'Activated', 'geo-my-wp' ) . '"';
		$output .= ' data-label="' . $label . '"';
		$output .= ' >';
		$output .= $label;
		$output .= ' </a>';

		return $output;
	}

	/**
	 * Generate Deactivate extension button
	 *
	 * @param  string $slug     extension slug
	 * @param  string $basename extension basename
	 *
	 * @return HTML link
	 */
	public static function deactivate_extension_button( $slug = '', $basename = '' ) {

		$nonce = wp_create_nonce( 'gmw_' . $slug . '_extension_nonce' );
		$url   = admin_url( 'admin.php?page=gmw-extensions&gmw_action=deactivate_extension&basename=' . $basename . '&slug=' . $slug . '&_wpnonce=' . $nonce );
		$label = __( 'Deactivate', 'geo-my-wp' );

		$output  = '<a href="' . esc_url( $url ) . '"';
		$output .= ' class="button button-secondary gmw-extension-action-button deactivate"';
		$output .= ' data-slug="' . esc_attr( $slug ) . '"';
		$output .= ' data-basename="' . esc_attr( $basename ) . '"';
		$output .= ' data-action="deactivate_extension"';
		$output .= ' data-nonce="' . $nonce . '"';
		$output .= ' data-updating_message="' . __( 'Deactivating', 'geo-my-wp' ) . '"';
		$output .= ' data-updated_message="' . __( 'Deactivated', 'geo-my-wp' ) . '"';
		$output .= ' data-label="' . $label . '"';
		$output .= ' >';
		$output .= $label;
		$output .= ' </a>';

		return $output;
	}

	/**
	 * Activate extension
	 *
	 * @return [type] [description]
	 */
	public static function activate_extension() {

		// make sure we activating extension
		if ( empty( $_GET['gmw_action'] ) || 'activate_extension' != $_GET['gmw_action'] ) {
			wp_die();
		}

		$slug     = $_GET['slug'];
		$basename = $_GET['basename'];

		// If doing AJAX call
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// verify nonce
			if ( ! check_ajax_referer( 'gmw_' . $slug . '_extension_nonce', 'security', false ) ) {

				wp_die(
					__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			// otherwise, page load submission
		} else {

			if ( ! check_admin_referer( 'gmw_' . $slug . '_extension_nonce' ) ) {
				wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}
		}

		$plugins = get_plugins();

		//activate the WordPress plugin reagrdless it later it is set as disabled or inactive in GEO my WP.
		//We do so to allow the license key to load so updates will be available.
		if ( array_key_exists( $basename, $plugins ) && is_plugin_inactive( $basename ) ) {
			activate_plugins( $basename );
		}

		// enable addons after activation
		GMW_Addon::init_addons();

		// update active status and get the addon data.
		$extension = gmw_update_addon_status( $slug, 'active' );

		// get all extensions
		$extensions_data = gmw_get_addons_data( true );

		// if AJAX enabled
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			$dependends = array();

			// look for other extensions dependends on this extension.
			// we will enable them.
			foreach ( $extensions_data as $ext_data ) {

				// if disabled because of a theme skip checking for addons.
				if ( 'disabled' == $ext_data['status'] && 'theme_missing' == $ext_data['status_details']['error'] ) {

					continue;

				} elseif ( ! empty( $ext_data['required']['addons'] ) ) {

					foreach ( $ext_data['required']['addons'] as $required_addon ) {

						if ( isset( $extension['slug'] ) && $required_addon['slug'] == $extension['slug'] ) {

							$dependends[ $ext_data['slug'] ] = '';

							// deactivate addon
							gmw_update_addon_status( $ext_data['slug'], 'inactive' );
						}
					}
				}
			}

			// Generate deactivation button to pass to JS.
			// It will replace the Activate button.
			$link = self::deactivate_extension_button( $slug, $basename );

			// proceed to JS
			wp_send_json(
				array(
					'newLink'     => $link,
					'lisenseData' => $extension,
					'dependends'  => $dependends,
				)
			);

		} else {

			//reload the page to prevent resubmission
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

		//make sure we activated an add-on
		if ( empty( $_GET['gmw_action'] ) || $_GET['gmw_action'] != 'deactivate_extension' ) {
			return;
		}

		$slug     = $_GET['slug'];
		$basename = $_GET['basename'];

		// If doing ajax deactivation
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

			// verify nonce
			if ( ! check_ajax_referer( 'gmw_' . $slug . '_extension_nonce', 'security', false ) ) {
				wp_die(
					__( 'Cheatin\' eh?!', 'geo-my-wp' ),
					__( 'Error', 'geo-my-wp' ),
					array( 'response' => 403 )
				);
			}

			// otherwise, page load action
		} else {

			if ( ! check_admin_referer( 'gmw_' . $slug . '_extension_nonce' ) ) {
				wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
			}
		}

		// get WordPress plugins
		$plugins = get_plugins();

		// If the extension is a WordPress plugin, deactivate it.
		if ( array_key_exists( $basename, $plugins ) && is_plugin_active( $basename ) ) {
			deactivate_plugins( $basename );
		}

		// set addon status inactive and get the addon data
		$extension = gmw_update_addon_status( $slug, 'inactive' );

		// get extensions
		$extensions_data = gmw_get_addons_data( true );

		// if doing AJAX
		if ( defined( 'DOING_AJAX' ) ) {

			$dependends = array();

			// look for other extensions dependends on this extension.
			// we will disable them.
			foreach ( $extensions_data as $ext_data ) {

				// abort if already disabled because of a theme.
				if ( $ext_data['status'] == 'disabled' && $ext_data['status_details']['error'] == 'theme_missing' ) {

					continue;

				} elseif ( ! empty( $ext_data['required']['addons'] ) ) {

					foreach ( $ext_data['required']['addons'] as $required_addon ) {

						if ( isset( $extension['slug'] ) && $required_addon['slug'] == $extension['slug'] ) {

							$dependends[ $ext_data['slug'] ] = $required_addon['notice'];

							// update extensions status in database
							gmw_update_addon_status( $ext_data['slug'], 'disabled', $required_addon );
						}
					}
				}
			}

			// generate new Activate link to replace with Deactivate
			$link = self::activate_extension_button( $extension['slug'], $basename );

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
	 *
	 * @return [type] [description]
	 */
	public static function license_key_actions() {

		$form_data = $_POST['data'];

		// check nonce
		if ( ! check_ajax_referer( 'gmw_' . $form_data['license_name'] . '_license_nonce', 'security', false ) ) {

			//abort if bad nonce
			wp_die(
				__( 'Cheatin\' eh?!', 'geo-my-wp' ),
				__( 'Error', 'geo-my-wp' ),
				array( 'response' => 403 )
			);
		}

		// execute license action
		$license_data = gmw_license_key_actions( $form_data );

		// abort if failed connecting to remote server
		if ( ! $license_data->remote_connection ) {
			wp_die( __( 'connection to remote server failed.', 'geo-my-wp' ) );
		}

		// generate new license element to replace with current one.
		$license_input = new GMW_License_Key(
			$form_data['basename'],
			$form_data['item_name'],
			$form_data['license_name'],
			$form_data['item_id']
		);

		$form = $license_input->get_license_key_element();

		// proceed with AJAX
		wp_send_json(
			array(
				'license_data' => $license_data,
				'form'         => $form,
			)
		);
	}

	/**
	 * Display extensions
	 *
	 * @access public
	 * @return void
	 */
	public function output() {

		// get installed WordPress plugins
		$plugins = get_plugins();

		// get GMW extensions data. We merge both licenses data and addons data.
		$extensions_data = array_merge_recursive( GMW()->addons, GMW()->licenses );

		// get remote extensions data via geomywp.com feed
		$remote_extensions = self::get_extensions_feed();

		// verify feed. if feed ok merge some data with local extensions
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

				// if remote extension do not exists in GEO my WP extension
				// get the data from remote
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

		// collect core addons into temp array.
		// We will place the 2 important core extensions first.
		$core_extensions = array(
			'posts_locator'   => $extensions_data['posts_locator'],
			'members_locator' => $extensions_data['members_locator'],
		);

		$names = array();

		$core_extensions_count = 2;

		// rearrange extensions
		foreach ( $extensions_data as $key => $value ) {

			$names[ $key ] = $value['name'];

			if ( ! empty( $value['is_core'] ) && $key != 'posts_locator' && $key != 'members_locator' ) {
				$core_extensions_count ++;
				$core_extensions[ $key ] = $value;
			}
		}

		// sort add-ons by name.
		array_multisort( $names, SORT_ASC, $extensions_data );

		// move the core add-ons to the beggining of the array.
		$extensions_data = $core_extensions + $extensions_data;

		// extensions to exclude.
		$excluded_extensions = array(
			'formidable_geolocation',
			'geo_job_manager',
			'resume_manager_geo-location',
			'job_manager_geolocation_bundle',
			'gravity_forms_geo_fields',
		);

		// Use this filter to exclude extensions from the Extensions page.
		$exclude_extensions = apply_filters( 'gmw_extensions_page_exclude_extensions', $excluded_extensions, $extensions_data );

		foreach ( $exclude_extensions as $exclude ) {
			unset( $extensions_data[ $exclude ] );
		}

		?>
		<!-- Extensions page wrapper -->
		<div id="gmw-extensions-page" class="wrap gmw-admin-page">
			<h2>
				<i class="gmw-icon-puzzle"></i>

				<?php _e( 'GEO my WP Extensions', 'geo-my-wp' ); ?>

				<?php gmw_admin_helpful_buttons(); ?>
			</h2>
			<div id="gmw-extensions-filter">
				<ul>
					<li>
						<a href="#" class="filter-tab current" data-filter=""><?php _e( 'All', 'geo-my-wp' ); ?>
							<span class="count">( <?php echo $extensions_count = count( $extensions_data ); ?> )</span>
						</a>
					</li>
					<li>
						<a href="#" class="filter-tab" data-filter=".core"><?php _e( 'Core', 'geo-my-wp' ); ?>
							<span class="count">( <?php echo $core_extensions_count; ?> )</span>
						</a>
					</li>
					<li>
						<a href="#" class="filter-tab" data-filter=".premium"><?php _e( 'Premium', 'geo-my-wp' ); ?>
							<span class="count">( <?php echo $extensions_count - $core_extensions_count; ?> )</span>
						</a>
					<li>
					|
					<li><a href="#" class="active-tab" data-filter="active"><?php _e( 'Active', 'geo-my-wp' ); ?></a><li>
				</ul>

				<?php $updater = get_option( 'gmw_extensions_updater' ); ?>

				<div class="extensions-updater <?php echo ( ! empty( $updater ) ) ? 'enabled' : 'disabled'; ?>">         
					<?php

					if ( empty( $updater ) ) {

						$action = 'enable';
						$button = 'button-primary';
						$label  = __( 'Enable Updater', 'geo-my-wp' );

					} else {

						$action = 'disable';
						$button = 'button-secondary';
						$label  = __( 'Disable Updater', 'geo-my-wp' );
					}

					$nonce = wp_create_nonce( 'gmw_extension_updater_nonce' );
					$url   = admin_url( 'admin.php?page=gmw-extensions&gmw_action=updater_action&action=' . $action . '&_wpnonce=' . $nonce );

					?>
					<a 
						href="<?php echo esc_url( $url ); ?>"
						class="extensions-updater-button button <?php echo $button; ?>"
						data-action="<?php echo $action; ?>"
						data-nonce="<?php echo $nonce; ?>"
					><?php echo $label; ?></a>

					<i class="info-toggle dashicons dashicons-editor-help"></i>

					<div class="updater-info info-wrapper">
						<p class="description disable">
							<?php _e( 'Temporary disable the extensions updater to prevent slow page load on the admin\'s plugins.php/update.php pages.', 'geo-my-wp' ); ?>
						</p>

						<p class="description enable">
							<?php _e( 'Enable the extensions updater to check for new versions of the premium extensions.', 'geo-my-wp' ); ?>
						</p> 
					</div> 
				</div>

				<!-- add-ons page information -->
				<div class="extensions-cache">

					<form method="post">    
						<input type="submit" name="gmw_clear_extensions_cache" class="button-primary" value="<?php _e( 'Clear extensions cache', 'geo-my-wp' ); ?>" />  
						<input type="hidden" name="gmw_action" value="clear_extensions_cache" />
						<?php wp_nonce_field( 'gmw_clear_extensions_cache_nonce', 'gmw_clear_extensions_cache_nonce' ); ?>  
					</form> 

					<i class="info-toggle dashicons dashicons-editor-help"></i>

					<div class="cache-info info-wrapper">
						<p class="description">
							<?php _e( 'Try clearing extensions cache if extensions fails to load properly on this page.', 'geo-my-wp' ); ?> 
						</p>
					</div>
				</div>

				<div class="disabler-block"></div>
			</div>

			<div class="extensions-wrapper">
				<div class="extensions-title core">
					<h3>
						<?php _e( 'Core Extensions', 'geo-my-wp' ); ?>     
					</h3>
				</div>
				<div></div>

				<?php $prem_extension_count = 0; ?>

				<?php foreach ( $extensions_data as $extension ) : ?>
					<?php

					//set extension status
					$extension['status'] = ! empty( $extension['status'] ) ? $extension['status'] : 'inactive';

					// Verify some data in permium extensions
					if ( empty( $extension['is_core'] ) ) {

						$prem_extension_count++;

						if ( 1 == $prem_extension_count ) { ?>
						<div class="extensions-title premium">
							<h3>
								<?php _e( 'Premium Extensions', 'geo-my-wp' ); ?>
							</h3>
						</div>
						<?php
						}

						//Reset some variables
						$extension['installed'] = false;

						//create file if doesnt exist
						if ( empty( $extension['full_path'] ) ) {
							$extension['full_path'] = ABSPATH . 'wp-content/plugins/' . $extension['basename'];
						}

						//create basename if doesnt exist
						if ( empty( $extension['basename'] ) ) {
							$extension['basename'] = plugin_basename( $extension['full_path'] );
						}

						//if add-on installed
						if ( isset( $plugins[ $extension['basename'] ] ) ) {

							$extension['installed'] = true;
							$extension['version']   = $plugins[ $extension['basename'] ]['Version'];

						} elseif ( empty( $extension['version'] ) ) {

							$extension['version'] = ! empty( $extension['current_version'] ) ? $extension['current_version'] : '1.0';
						}
					} else {

						$extension['installed'] = true;
					}

					$status_class = ( 'disabled' == $extension['status'] ) ? 'inactive disabled' : $extension['status'];
					?>
					<!-- extension wrapper -->
					<div 
						class="gmw-extension-wrapper 
						<?php echo ! empty( $extension['installed'] ) ? 'installed' : 'not-installed'; ?> 
						<?php echo ! empty( $extension['is_core'] ) ? 'core' : 'premium'; ?> 
						<?php echo ! empty( $extension['license_name'] ) ? 'has-license' : 'free'; ?>
						<?php echo $status_class; ?>" 
						data-slug="<?php echo esc_attr( $extension['slug'] ); ?>" 
						data-name="<?php echo esc_attr( $extension['name'] ); ?>"
					>	
						<!-- free add-on -->
						<?php
						/* if ( ! empty( $extension['is_core'] ) && $extension['status'] != 'active' ) { ?>
							<div class="gmw-extension-ribbon-wrapper"><div class="gmw-extension-ribbon free"><?php _e( 'Free Add-on', 'geo-my-wp' ); ?></div></div>
						<?php } */
						?>
						<!-- New add-on -->
						<?php if ( ! $extension['installed'] && ! empty( $extension['new_addon'] ) ) { ?>                        
							<div class="gmw-extension-ribbon-wrapper"><div class="gmw-extension-ribbon blue"><?php _e( 'New Add-on', 'geo-my-wp' ); ?></div></div>   
						<?php } ?>

						<div class="extension-top">

							<div class="name">

								<h3>
									<?php echo esc_attr( $extension['name'] ); ?>                               

									<img src="https://geomywp.com/wp-content/uploads/extensions-images/<?php echo esc_attr( $extension['slug'] ); ?>.png" />
								</h3>

								<?php if ( isset( $extension['version'] ) && 'na' != $extension['version'] ) { ?>
									<p class="version">
										<?php echo sprintf( __( 'Version %s', 'geo-my-wp' ), esc_attr( $extension['version'] ) ); ?>
									</p>
								<?php } ?>

							</div>

							<div class="action-links">

								<ul class="action-buttons">

									<li class="activation-button">

										<div class="<?php if ( ! $extension['installed'] ) { echo 'not-installed'; } ?>">

											<?php if ( $extension['installed'] ) { ?>

												<?php
												// show Activate button
												if ( 'active' != $extension['status'] ) {

													echo self::activate_extension_button( $extension['slug'], $extension['basename'] );

													// show deactivate button
												} else {

													echo self::deactivate_extension_button( $extension['slug'], $extension['basename'] );
												}
												?>

											<?php } else { ?>

												<a href="<?php echo esc_url( $extension['addon_page'] ); ?>" class="button-secondary button get-extension" target="_blank">
													<?php _e( 'Get Extension', 'geo-my-wp' ); ?>    
												</a>
											<?php } ?>
										</div>
									</li>

									<?php $details_link = ! empty( $extension['addon_page'] ) ? $extension['addon_page'] : 'https://geomywp.com'; ?>

									<li class="details">

										<i class="gmw-icon-info-circled"></i>

										<a href="<?php echo esc_url( $details_link ); ?>"" target="_blank"> 
											<?php _e( 'Details', 'geo-my-wp' ); ?>
										</a>
									</li>

									<?php $docs_link = ! empty( $extension['docs_page'] ) ? $extension['docs_page'] : 'https://docs.geomywp.com'; ?>

									<li class="docs">
										<i class="gmw-icon-doc-text"></i>
										<a href="<?php echo esc_url( $docs_link ); ?>"" target="_blank"> 
											<?php _e( 'Documentation', 'geo-my-wp' ); ?>
										</a>
									</li>

									<?php $support_link = ! empty( $extension['support_page'] ) ? $extension['support_page'] : 'https://geomywp.com/forums/forum/support/'; ?>

									<li class="support">
										<i class="gmw-icon-lifebuoy"></i>
										<a href="<?php echo esc_url( $support_link ); ?>" target="_blank"> 
											<?php _e( 'Support', 'geo-my-wp' ); ?>
										</a>
									</li>

								</ul>
							</div>

							<div class="desc">
								<p>
								<?php
								// if plugin installed and description not provided in addon registration
								// get the description from WP plugins();
								if ( $extension['installed'] && empty( $extension['description'] ) ) {

									echo esc_attr( $plugins[ $extension['basename'] ]['Description'] );

								} elseif ( ! empty( $extension['description'] ) ) {

									echo esc_attr( $extension['description'] );
								}
								?>
								</p>
							</div>

							<div class="author">
								<p>    
									<?php
									// if plugin installed and description not provided in addon registration
									// get the description from WP plugins();
									if ( empty( $extension['is_core'] ) && $extension['installed'] && empty( $extension['author'] ) ) {

										echo 'By ' . esc_attr( $plugins[ $extension['basename'] ]['Author'] );

									} elseif ( ! empty( $extension['author'] ) ) {

										echo 'By ' . esc_attr( $extension['author'] );
									}
									?>
								</p>
							</div>

						</div>

						<div class="activation-disabled-message">
							<p> 
								<i class="gmw-icon-cancel-circled"></i>
								<span>
								<?php
								if ( 'disabled' == $extension['status'] ) {
									// in rare cases the notice can be an array.
									// When 2 versions of the same extension are installed.
									echo is_array( $extension['status_details']['notice'] ) ? $extension['status_details']['notice'][0] : $extension['status_details']['notice'];
								}
								?>
								</span>
							</p>
						</div>

						<?php if ( empty( $extension['status_details'] ) && ! empty( $extension['current_version'] ) && version_compare( $extension['version'], $extension['current_version'], '<' ) ) { ?>

							<div class="update-available-notice">
								<p>
									<i class="gmw-icon-spin"></i>
									<span>
										<?php echo sprintf( __( 'Version %s is now availabe. Update your plugin.', 'geo-my-wp' ), $extension['current_version'] ); ?>
									</span>
								</p>
							</div>

						<?php } ?>

						<div class="extension-bottom">

							<form method="post" action="" class="extension-license-form" data-slug="<?php echo $extension['slug']; ?>">

								<?php
								// if core or free extensions
								if ( ! empty( $extension['is_core'] ) || empty( $extension['license_name'] ) ) {
								?>
									<div id="gmw-<?php echo $extension['slug']; ?>-license-wrapper" class="gmw-license-wrapper free-extension <?php echo ( $extension['status'] != 'active' ) ? 'inactive' : 'active'; ?>">

										<p class="description free-extension">
											<?php _e( 'This is a free extension and does not require a license key.' ); ?>
										</p>

										<p class="description thank-you" >
											<?php echo sprintf( __( 'Thank you for using GEO my WP. Your <a href="%s">feedback</a> is greatly appriciated.', 'geo-my-wp' ), 'https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5' ); ?>
										</p>

									</div> 
								<?php
								} else {

									// Display license key form element
									if ( class_exists( 'GMW_License_Key' ) ) {

										$gmw_license_key = new GMW_License_Key(
											$extension['full_path'],
											$extension['item_name'],
											$extension['license_name'],
											$extension['item_id']
										);

										echo $gmw_license_key->get_license_key_element();
									}
								}
								?>

								<div class="license-key-disabler"></div>

							</form>

						</div>

						<div class="disabler-block"></div>
					</div>

				<?php endforeach; ?>
			</div>          
		</div>

		<?php
		wp_enqueue_script( 'gmw-extensions', GMW_URL . '/assets/js/gmw.extensions.min.js', array( 'jquery' ), GMW_VERSION, true );
	}
}
add_action( 'wp_ajax_gmw_activate_extension', array( 'GMW_Extensions', 'activate_extension' ) );
add_action( 'wp_ajax_gmw_deactivate_extension', array( 'GMW_Extensions', 'deactivate_extension' ) );
add_action( 'wp_ajax_gmw_license_key_actions', array( 'GMW_Extensions', 'license_key_actions' ) );
add_action( 'wp_ajax_gmw_extensions_updater', array( 'GMW_Extensions', 'extensions_updater' ) );
