<?php
/**
 * Allows plugins to use their own update API.
 *
 * @author Pippin Williamson
 * @version 1.0
 */
class GMW_Premium_Plugin_Updater {
	private $api_url  = '';
	private $api_data = array();
	private $name     = '';
	private $slug     = '';

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string $_api_url The URL pointing to the custom API endpoint.
	 * @param string $_plugin_file Path to the plugin file.
	 * @param array $_api_data Optional data to send with API calls.
	 * @return void
	 */
	function __construct( $_api_url, $_plugin_file, $_api_data = null ) {
		$this->api_url  = trailingslashit( $_api_url );
		$this->api_data = urlencode_deep( $_api_data );
		$this->name     = plugin_basename( $_plugin_file );
		$this->slug     = basename( $_plugin_file, '.php');
		$this->version  = $_api_data['version'];

		// Set up hooks.
		$this->hook();
	}

	/**
	 * Set up Wordpress filters to hook into WP's update process.
	 *
	 * @uses add_filter()
	 *
	 * @return void
	 */
	private function hook() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins_filter' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api_filter' ), 10, 3 );
	}

	/**
	 * Check for Updates at the defined API endpoint and modify the update array.
	 *
	 * This function dives into the update api just when Wordpress creates its update array,
	 * then adds a custom API call and injects the custom plugin data retrieved from the API.
	 * It is reassembled from parts of the native Wordpress plugin update code.
	 * See wp-includes/update.php line 121 for the original wp_update_plugins() function.
	 *
	 * @uses api_request()
	 *
	 * @param array $_transient_data Update array build by Wordpress.
	 * @return array Modified update array with custom plugin data.
	 */
	function pre_set_site_transient_update_plugins_filter( $_transient_data ) {


		if( empty( $_transient_data ) ) return $_transient_data;

		$to_send = array( 'slug' => $this->slug );

		$api_response = $this->api_request( 'plugin_latest_version', $to_send );

		if( false !== $api_response && is_object( $api_response ) && isset( $api_response->new_version ) ) {
			if( version_compare( $this->version, $api_response->new_version, '<' ) )
				$_transient_data->response[$this->name] = $api_response;
	}
		return $_transient_data;
	}


	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 * @return object $_data
	 */
	function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( ( $_action != 'plugin_information' ) || !isset( $_args->slug ) || ( $_args->slug != $this->slug ) ) return $_data;

		$to_send = array( 'slug' => $this->slug );

		$api_response = $this->api_request( 'plugin_information', $to_send );
		if ( false !== $api_response ) $_data = $api_response;

		return $_data;
	}

	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string $_action The requested action.
	 * @param array $_data Parameters for the API action.
	 * @return false||object
	 */
	private function api_request( $_action, $_data ) {

		global $wp_version;

		$data = array_merge( $this->api_data, $_data );

		if( $data['slug'] != $this->slug )
			return;

		if( empty( $data['license'] ) )
			return;

		$api_params = array(
			'edd_action' 	=> 'get_version',
			'license' 		=> $data['license'],
			'name' 			=> $data['item_name'],
			'slug' 			=> $this->slug,
			'author'		=> $data['author']
		);
		$request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( ! is_wp_error( $request ) ):
			$request = json_decode( wp_remote_retrieve_body( $request ) );
			if( $request && isset( $request->sections ) )
				$request->sections = maybe_unserialize( $request->sections );
			return $request;
		else:
			return false;
		endif;
	}
}

function gmw_premium_license_page() {
	$wppl_addons = array();
	$wppl_addons = get_option('wppl_plugins');
	
	$license 	= get_option( 'gmw_license_keys' );
	$status 	= get_option( 'gmw_premium_plugin_status' );
	
	//gmw_premium_check_license();
	
	?>
	<div class="wrap">
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2><?php _e('GEO my WP Licenses','GMW'); ?></h2>
		<form method="post" action="options.php">
		
			<?php settings_fields('gmw_premium_license'); ?>
			
			<table class="widefat fixed">
	            <thead>
	            	<tr>
	            		<th style="width:25%"><?php _e('Add-On','GMW'); ?></th>
	                 	<th style="width:35%;"><?php _e('License Key','GMW'); ?></th>
	                    <th style="width:20%;"><?php _e('Action','GMW'); ?></th>
	                    <th style="width:20%;"><?php _e('Status','GMW'); ?></th>
	                </tr>
	           	</thead>	
				<tbody>
					<?php foreach ( $wppl_addons as $add_on ) : ?>
			
						<?php if ($add_on != 1 ) :?>
						
							<tr valign="top">	
								<td style="font-size: 13px;padding: 10px 6px;">
									<?php _e(ucwords(str_replace('_', ' ', $add_on))); ?>
								</td>
								<td style="padding: 7px 0px;">
									<input class="gmw_license_keys" name="gmw_license_keys[<?php echo $add_on; ?>]" type="text" class="regular-text" size="40" value="<?php if ( isset($license[$add_on]) && !empty($license[$add_on]) ) esc_attr_e( $license[$add_on] ); ?>" />
									<br />
									<label style="padding: 3px 2px;float: left;" class="description" for="gmw_license_keys"><?php _e('Enter your license key','GMW'); ?></label>
								</td>
								
								<?php if ( isset($license[$add_on]) && false !== $license[$add_on] ) { ?>
								
									<?php if ( isset($status[$add_on]) && $status[$add_on] !== false && $status[$add_on] == 'valid' ) { ?>
										<?php wp_nonce_field( $add_on, $add_on ); ?>
										<td>
											<button type="submit" class="button-secondary" name="gmw_license_key_deactivate" value="<?php _e($add_on); ?>" ><?php _e('Deactivate','GMW'); ?></button>
										</td>
										<td>
											<span style="color:green;"><?php _e('active','GMW'); ?></span>
										</td>
									<?php } else {
										wp_nonce_field( $add_on, $add_on ); ?>
										<td>
											<button type="submit" class="button button-primary" name="gmw_license_key_submit" value="<?php _e($add_on); ?>" ><?php _e('Save Changes','GMW'); ?></button>
											<?php if ( isset($license[$add_on]) && !empty($license[$add_on]) ) : ?>
												<button type="submit" class="button-secondary" name="gmw_license_key_activate" value="<?php _e($add_on); ?>"><?php _e('Activate','GMW'); ?></button>
											<?php endif; ?>
										</td>
										<td>
											<span style="color:red;"><?php _e('inactive','GMW'); ?></span>
										</td>
									<?php } ?>
								<?php } else { ?>
									<td>
										<button type="submit" class="button button-primary" name="gmw_license_key_submit" value="<?php _e($add_on); ?>" ><?php _e('Save Changes','GMW'); ?></button>
									</td>
									<td>
										<span style="color:red;"><?php _e('inactive','GMW'); ?></span>
									</td>
								<?php } ?>	
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
					<!--  <tr>
						<td style="font-size:13px;padding:8px;">
							<?php _e('Reset all license keys', 'GMW'); ?>
						</td>
						<td style="padding:8px;">
							<button type="submit" class="button-secondary" name="gmw_reset_all_keys" value="reset_all"><?php _e('Reset','GMW'); ?></button>
						</td>
						<td>
						</td>
						<td>
						</td>
					</tr> -->
				</tbody>
			</table>	
		</form>
	</div>
	<?php
}

function gmw_register_license_menu() {
	
	//if ( isset($_POST['gmw_license_key_submit']) ) 
		register_setting('gmw_premium_license', 'gmw_license_keys', 'gmw_premium_sanitize_license' );

}
add_action('admin_init', 'gmw_register_license_menu');

function gmw_premium_sanitize_license( $licenses ) {
	
	//if ( !isset($_POST['gmw_license_key_submit']) ) return;
	
	if ( isset($_POST['gmw_license_key_submit']) ) :
		
	$add_on = $_POST['gmw_license_key_submit'];
		
		$statuses = get_option( 'gmw_premium_plugin_status' );
		
		if ( isset($licenses[$add_on]) && !empty($licenses[$add_on]) ) :
			$old = $licenses[$add_on];
			if ( $old && $old != $_POST['gmw_license_keys'][$add_on] ) {
				unset($statuses[$add_on]);
				update_option( 'gmw_premium_plugin_status', $statuses ); // new license has been entered, so must reactivate
			}
		endif;
	endif;
		
	return $licenses;
}

function gmw_reset_all_keys() {
	if ( isset( $_POST['gmw_reset_all_keys'] ) && $_POST['gmw_reset_all_keys'] == 'reset_all') :
		delete_option( 'gmw_premium_plugin_status' );
	endif;
}
add_action('admin_init', 'gmw_reset_all_keys');

/************************************
* this illustrates how to check if 
* a license key is still valid
* the updater does this for you,
* so this is only needed if you
* want to do something custom
*************************************/

function gmw_premium_check_license() {

	global $wp_version;

	$licenses = get_option( 'gmw_license_keys' );
	
	if ( !isset($licenses) || empty($licenses) ) return;
	
	foreach ($licenses as $name => $license ) :
		
		$this_license = trim( $license);
		$this_name = ucwords(str_replace('_', ' ', $name));
		
		$api_params = array( 
			'edd_action' => 'check_license', 
			'license' => $this_license, 
			'item_name' => urlencode( $this_name ) 
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, GMW_REMOTE_SITE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
	
		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		$statuses = get_option( 'gmw_premium_plugin_status' );
		
		if ( isset($statuses) && !empty($statuses) ) :
			if ( !isset($license_data) ) :
				$statuses[$name] = 'inactive';
			else :
				$statuses[$name] = $license_data->license;
			endif;
			// $license_data->license will be either "active" or "inactive"
			update_option( 'gmw_premium_plugin_status', $statuses );
		endif;
		/*
		//print_r($license_data);
		if( $license_data->license == 'valid' ) {
			echo 'valid'; 
		
		// this license is still valid
		} else {
			echo 'invalid'; 
		// this license is no longer valid
		}
		*/
	endforeach;
}
add_action('admin_init', 'gmw_premium_check_license');

/************************************
* this illustrates how to activate 
* a license key
*************************************/

function gmw_premium_activate_license() {
	
	// listen for our activate button to be clicked
	if( isset( $_POST['gmw_license_key_activate'] ) ) {

		$add_on = $_POST['gmw_license_key_activate'];
	
		// run a quick security check 
	 	if( !check_admin_referer( $add_on,  $add_on ) ) 	
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$licenses = get_option( 'gmw_license_keys' );
			
		if ( isset($licenses[$add_on]) ) :
			
			$this_license = trim( $licenses[$add_on]);
			$this_name = ucwords(str_replace('_', ' ', $add_on));
			
			// data to send in our API request
			$api_params = array( 
				'edd_action'=> 'activate_license', 
				'license' 	=> $this_license, 
				'item_name' => urlencode( $this_name ) // the name of our product in EDD
			);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, GMW_REMOTE_SITE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
			
			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;
	
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			$statuses = get_option( 'gmw_premium_plugin_status' );
			
			$statuses[$add_on] = $license_data->license;
			// $license_data->license will be either "active" or "inactive"
			update_option( 'gmw_premium_plugin_status', $statuses ); 
		endif;
	}
}
add_action('admin_init', 'gmw_premium_activate_license');


/***********************************************
* Illustrates how to deactivate a license key.
* This will descrease the site count
***********************************************/

function gmw_premium_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['gmw_license_key_deactivate'] ) ) {

		$add_on = $_POST['gmw_license_key_deactivate'];
		// run a quick security check 
	 	if( ! check_admin_referer( $add_on, $add_on ) ) 	
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$licenses = get_option( 'gmw_license_keys' );
		
		if ( isset($licenses[$add_on]) ) :
		
			$this_license = trim( $licenses[$add_on]);
			$this_name = ucwords(str_replace('_', ' ', $add_on));
			
			$api_params = array( 
				'edd_action'=> 'deactivate_license', 
				'license' 	=> $this_license, 
				'item_name' => urlencode( $this_name ) // the name of our product in EDD
			);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, GMW_REMOTE_SITE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
	
			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;
	
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			// $license_data->license will be either "deactivated" or "failed"
			$statuses = get_option( 'gmw_premium_plugin_status' );
			
			if( $license_data->license == 'deactivated' ) :
				unset($statuses[$add_on]);
				update_option( 'gmw_premium_plugin_status',$statuses );
			endif;
				//delete_option( 'gmw_premium_plugin_status' );
		endif;
	}
}
add_action('admin_init', 'gmw_premium_deactivate_license');

function gmw_check_license_key( $license_name, $version) {

	$gmw_license_keys = get_option( 'gmw_license_keys' );

	if ( isset( $gmw_license_keys[$license_name] )  ) :

	$license = trim($gmw_license_keys[$license_name]);

	$gmw_updater = new GMW_Premium_Plugin_Updater( GMW_REMOTE_SITE_URL, __FILE__, array(
			'version' 	=> $version,
			'license' 	=> $license,
			'item_name' => ucwords( str_replace('_', ' ', $license_name) ),
			'author' 	=> 'Eyal Fitoussi'
	));

	endif;

}

