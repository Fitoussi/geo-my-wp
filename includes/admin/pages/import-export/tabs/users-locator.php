<?php
/**
 * GEO my WP Users Locator Import/Export tab.
 *
 * @since 4.0
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_User_Meta_Fields_Importer class.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_User_Meta_Fields_Importer_Form extends GMW_Meta_Fields_Importer_Form {

	/**
	 * Name singular.
	 *
	 * @var string
	 */
	public $singular_name = 'user meta';

	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug = 'user_meta';

	/**
	 * Get user meta function.
	 *
	 * @var string
	 */
	public $meta_field_function = 'gmw_get_user_meta';

	/**
	 * Importer class.
	 *
	 * @var string
	 */
	public $importer_class = 'GMW_User_Meta_Fields_Importer';

	/**
	 * Title.
	 *
	 * @return [type] [description]
	 */
	public function get_title() {
		return __( 'User Meta Fields Importer', 'geo-my-wp' );
	}

	/**
	 * Get section description.
	 *
	 * @return [type] [description]
	 */
	public function get_description() {
		return __( 'Use this importer to import users\' location from specific user meta fields into GEO my WP database.', 'geo-my-wp' );
	}
}

/**
 * Users Locations importer class
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
class GMW_User_Meta_Fields_Importer extends GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 *
	 * @var string
	 */
	protected $object_type = 'user';

	/**
	 * Records to import per batch.
	 *
	 * @var integer
	 */
	protected $records_per_batch = 15;

	/**
	 * Form Message.
	 *
	 * @var string
	 */
	public $form_message = '';

	/**
	 * Location meta fields.
	 *
	 * @var array
	 */
	protected $location_meta_fields = array(
		'phone'   => 'phone',
		'fax'     => 'fax',
		'email'   => 'email',
		'website' => 'website',
	);

	/**
	 * Get location to import
	 *
	 * @return [type] [description]
	 */
	public function query_locations() {

		global $wpdb;

		// get meta fields.
		$location_fields = get_option( 'gmw_importer_user_meta_fields' );

		// Count rows only when init the importer.
		$count_rows = absint( $this->total_locations ) === 0 ? 'SQL_CALC_FOUND_ROWS' : '';

		// get users.
		// phpcs:disable
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT {$count_rows}
				wpusers.ID as object_id,
				wpusers.ID as user_id,
				wpusers.display_name as title,
				wpumeta.meta_value as latitude,
				wpumeta1.meta_value as longitude
				FROM {$wpdb->prefix}users wpusers
				INNER JOIN {$wpdb->prefix}usermeta wpumeta
				ON ( wpusers.ID = wpumeta.user_id )
				INNER JOIN {$wpdb->prefix}usermeta AS wpumeta1
				ON ( wpusers.ID = wpumeta1.user_id )
				AND (
	  			( wpumeta.meta_key = %s AND wpumeta.meta_value NOT IN ('') )
	  			AND
	  			( wpumeta1.meta_key = %s AND wpumeta1.meta_value NOT IN ('') )
				)
				GROUP BY wpusers.ID
				ORDER BY wpusers.ID
				LIMIT %d, %d",
				array(
					$location_fields['latitude'],
					$location_fields['longitude'],
					$this->records_completed,
					$this->records_per_batch,
				)
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.
		// phpcs:enable

		// count all rows only when init the importer.
		$this->total_locations = absint( $this->total_locations ) === 0 ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $this->total_locations; // WPCS: db call ok, cache ok, unprepared SQL ok.

		// abort if nothing was found.
		if ( empty( $results ) ) {
			return array();
		}

		// get rest of location data from custom fields.
		foreach ( $results as $meta_key => $user ) {

			foreach ( $location_fields as $key => $field ) {

				if ( 'latitude' === $key || 'longitude' === $key || empty( $field ) ) {
					continue;
				}

				$meta = get_user_meta( $user->object_id, $field, true );

				$results[ $meta_key ]->$key = ! empty( $meta ) ? $meta : '';
			}
		}

		return $results;
	}
}

/**
 * Users Location Export/Import tab output
 *
 * @access public
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_output_users_locator_import_export_tab() {

	$user_meta_importer = new GMW_User_Meta_Fields_Importer_Form();

	$user_meta_importer->output();
}
add_action( 'gmw_import_export_users_locator_tab', 'gmw_output_users_locator_import_export_tab' );
