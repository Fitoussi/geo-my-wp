<?php
/**
 * GEO my WP Posts Locator Mashup Map Class.
 *
 * The class queries posts based on location.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts Locator mashup map form class.
 *
 * @since 4.0.
 *
 * @package geo-my-wp
 */
class GMW_Posts_Locator_Mashup_Map_Form extends GMW_Posts_Locator_Form {

	/**
	 * __construct function.
	 *
	 * @param array $form gmw form.
	 */
	public function __construct( $form ) {

		// If passing form shortcode attribute, convert it to map.
		if ( 'map' !== $form['current_element'] ) {

			$form['current_element'] = 'map';
			$form['params']['map']   = $form['ID'];
		}

		parent::__construct( $form );
	}
}
