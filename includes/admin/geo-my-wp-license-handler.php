<?php
/**
 * License handler for GEO my WP
 *
 * This class should simplify the process of adding license information
 * to GEO my WP add-ons.
 * 
 * @author Pippin Williamson
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'GMW_License' ) ) :

/**
 * GMW_License Class
 */
class GMW_License {
	private $file;
	private $license;
	private $item_name;
	private $version;
	private $author;
	private $api_url = 'https://geomywp.com';

	/**
	 * Class constructor
	 *
	 * @param string  $_file
	 * @param string  $_item_name
	 * @param string  $_version
	 * @param string  $_author
	 * @param string  $_optname
	 * @param string  $_api_url
	 */
	function __construct( $_file, $_item_name, $_license, $_version, $_author, $_api_url = null ) {
		
		$settings = get_option('gmw_options');
		
		if ( isset( $settings['admin_settings']['updater_disabled'] ) )
			return;
		
		$this->licenses 	= get_option( 'gmw_license_keys' );
		$this->statuses 	= get_option('gmw_premium_plugin_status');
		$this->file         = $_file;
		$this->item_name    = $_item_name;
		$this->license_name = $_license;
		$this->license      = isset( $this->licenses[$_license] ) ? trim( $this->licenses[$_license] ) : '';
		$this->version      = $_version;
		$this->author       = $_author;
		$this->api_url      = is_null( $_api_url ) ? $this->api_url : $_api_url;

		// Setup hooks
		$this->includes();
		$this->auto_updater();
	}

	/**
	 * Include the updater class
	 *
	 * @access  private
	 * @return  void
	 */
	private function includes() {
		if ( ! class_exists( 'GMW_Premium_Plugin_Updater' ) ) require_once 'geo-my-wp-updater.php';
	}

	/**
	 * Auto updater
	 *
	 * @access  private
	 * @global  array $edd_options
	 * @return  void
	 */
	private function auto_updater() {

		if ( empty( $this->license ) ) 
			return;
		
		if ( !isset( $this->statuses[$this->license_name] ) || 'valid' !== $this->statuses[$this->license_name] )
			return;
		
		// Setup the updater
		$gmw_updater = new GMW_Premium_Plugin_Updater(
				$this->api_url,
				$this->file,
				array(
						'version'   => $this->version,
						'license'   => $this->license,
						'item_name' => $this->item_name,
						'author'    => $this->author
				)
		);
	}
}

endif; // end class_exists check