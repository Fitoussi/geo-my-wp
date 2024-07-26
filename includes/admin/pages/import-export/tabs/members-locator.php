<?php
/**
 * GEO my WP BP Members Locator Import/Export tab.
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
 * GMW_BP_Xprofile_Fields_Importer_Form class.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_BP_Xprofile_Fields_Importer_Form extends GMW_Meta_Fields_Importer_Form {

	/**
	 * Name singular.
	 *
	 * @var string
	 */
	public $singular_name = 'Buddypress Xprofile';

	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug = 'bp_xprofile';

	/**
	 * Get user meta function.
	 *
	 * @var string
	 */
	public $meta_field_function = 'gmw_get_bp_xprofile_fields';

	/**
	 * Importer class.
	 *
	 * @var string
	 */
	public $importer_class = 'GMW_BP_Xprofile_Fields_Importer';

	/**
	 * Get the xprofile field name from field ID.
	 *
	 * @param  [type] $field_id [description].
	 *
	 * @return [type]           [description]
	 */
	public function get_saved_field_label( $field_id ) {

		$field = new BP_XProfile_Field( $field_id );

		return $field->name;
	}

	/**
	 * Title.
	 *
	 * @return [type] [description]
	 */
	public function get_title() {
		return __( 'BuddyPress Xprofile Fields Importer', 'geo-my-wp' );
	}

	/**
	 * Get section description.
	 *
	 * @return [type] [description]
	 */
	public function get_description() {
		return __( 'Use this importer to import members\' location from specific BuddyPress Xprofle fields into GEO my WP database.', 'geo-my-wp' );
	}
}

/**
 * GMW_BP_Xprofile_Fields_Importer class
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
class GMW_BP_Xprofile_Fields_Importer extends GMW_Locations_Importer {

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
	 * @return array
	 */
	public function query_locations() {

		global $wpdb;

		// get meta fields.
		$location_fields = get_option( 'gmw_importer_bp_xprofile_fields' );

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
				xprofile.value as latitude,
				xprofile1.value as longitude
				FROM {$wpdb->prefix}users wpusers
				INNER JOIN {$wpdb->prefix}bp_xprofile_data xprofile
				ON ( wpusers.ID = xprofile.user_id )
				INNER JOIN {$wpdb->prefix}bp_xprofile_data AS xprofile1
				ON ( wpusers.ID = xprofile1.user_id )
				AND (
	  			( xprofile.field_id = %s AND xprofile.value NOT IN ('') )
	  			AND
	  			( xprofile1.field_id = %s AND xprofile1.value NOT IN ('') )
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

				$meta = xprofile_get_field_data( $field, $user->object_id );

				$results[ $meta_key ]->$key = ! empty( $meta ) ? $meta : '';
			}
		}

		return $results;
	}
}

/**
 * Members Locator Export/Import tab output
 *
 * @access public
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi
 */
function gmw_output_members_locator_import_export_tab() {

	$xprofile_importer = new GMW_BP_Xprofile_Fields_Importer_Form();

	$xprofile_importer->output();

	// Show user meta importer only if the Users Locator extension is deactivated.
	// Otherwise, it will be in its own Users Locator import tab.
	if ( ! gmw_is_addon_active( 'users_locator' ) ) {

		$user_meta_importer = new GMW_User_Meta_Fields_Importer_Form();

		$user_meta_importer->output();
	}
}
add_action( 'gmw_import_export_members_locator_tab', 'gmw_output_members_locator_import_export_tab' );
