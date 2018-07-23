<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

/**
 * Importer page.
 */
class GMW_V_3_Import_Page {

	public function __construct() {

		global $wpdb;

		// look for post types table
		$this->posts_table = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}places_locator'", ARRAY_A );

		// look for users table
		$this->users_table = $wpdb->get_results( "SHOW TABLES LIKE 'wppl_friends_locator'", ARRAY_A );

		// abort if tables do not exsit
		if ( count( $this->posts_table ) == 0 && count( $this->users_table ) == 0 ) {
		    return;
		}

		// add importer tab
		add_filter( 'gmw_import_export_tabs', array( $this, 'create_tab' ), 50 );

		add_action( 'gmw_import_export_gmw_v_3_tab', array( $this, 'tab_content' ) );
	}

	public function create_tab( $tabs ) {

		$tabs['gmw_v_3'] = __( 'GEO my WP v3.0 Importer', 'geo-my-wp' );

		return $tabs;
	}

	public function tab_content() {
		
		do_action( 'gmw_v3_import_page_start' ); 

		if ( count( $this->posts_table ) != 0 ) : ?>

			<div id="poststuff" class="metabox-holder">
					
				<div id="post-body">
				
					<div id="post-body-content">

						<div class="postbox ">
				
							<h3 class="hndle">
								<span><?php _e( 'Import Post Types Locations', 'geo-my-wp' ); ?></span>
							</h3>

							<div class="inside">
									
								<?php 
					    		$pt_importer = new GMW_Posts_Locations_Importer_V3(); 
					    		$pt_importer->output(); 
					    		?>		  
									
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php endif; ?>

		<?php if ( count( $this->users_table ) != 0 ) : ?>

			<div id="poststuff" class="metabox-holder">
					
				<div id="post-body">
				
					<div id="post-body-content">

						<div class="postbox ">
				
							<h3 class="hndle">
								<span><?php _e( 'Import Users/Members Locations', 'geo-my-wp' ); ?></span>
							</h3>

							<div class="inside">
									
								<?php 
						    		$users_importer = new GMW_Users_Locations_Importer_V3(); 
						    		$users_importer->output(); 
						    	?>		  
								
							</div><!-- .inside -->
						</div>
					</div>
				</div>
			</div>

		<?php endif;
	
		do_action( 'gmw_v3_import_page_end' ); 
	}
}
new GMW_V_3_Import_Page();

/**
 * Post types locations importer class
 *
 * @since 3.0
 */
class GMW_Posts_Locations_Importer_V3 extends GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * records to import per batch
	 * @var integer
	 */
	protected $records_per_batch = 20;

	/**
	 * Message
	 * @var string
	 */
	public $form_message = 'Import existing posts locations into GEO my WP v3.0 database table.';

	/**
	 * location meta fields
	 * @var array
	 */
	protected $location_meta_fields = array(
		'phone' 	=> 'phone',
		'fax'		=> 'fax',
		'email'		=> 'email',
		'website' 	=> 'website'
	);

	protected function import_locationmeta( $location_id, $location ) {
		
		parent::import_locationmeta( $location_id, $location );

		$days_hours = get_post_meta( $location->object_id, '_wppl_days_hours', true );

		if ( ! empty( $days_hours ) ) {
			gmw_update_location_meta( $location_id, 'days_hours', $days_hours );
		}
	}

	public function query_locations() {

		global $wpdb;

		//check for post types table
		$gmw_pt_table = $wpdb->prefix . 'places_locator';

		//count rows only when init the importer
		$count_rows = $this->total_locations == 0 ? 'SQL_CALC_FOUND_ROWS' : '';

		//get records from database
		$data = $wpdb->get_results( "
			SELECT {$count_rows} 
			gmwLocations.post_id as object_id,
			wpposts.post_author as user_id,
			'1' as status,
			gmwLocations.feature as featured, 
			wpposts.post_title as title, 
			gmwLocations.lat as latitude,
			gmwLocations.long as longitude,
			gmwLocations.street_number,
			gmwLocations.street_name,
			gmwLocations.street,
			gmwLocations.apt as permise,
			gmwLocations.city,
			gmwLocations.state_long as region_name,
			gmwLocations.state as region_code,
			gmwLocations.zipcode as	postcode,
			gmwLocations.country_long as country_name,
			gmwLocations.country as country_code,
			gmwLocations.address,
			gmwLocations.formatted_address,
			gmwLocations.phone,
			gmwLocations.fax,
			gmwLocations.email,
			gmwLocations.website,
			gmwLocations.map_icon,
			wpposts.post_date as created, 
			wpposts.post_modified as updated
			FROM {$gmw_pt_table} gmwLocations 
			INNER JOIN {$wpdb->prefix}posts wpposts 
			ON gmwLocations.post_id = wpposts.ID
			LIMIT {$this->records_completed}, {$this->records_per_batch}
		");

		//count rows only when init the importer
		$this->total_locations = $this->total_locations == 0 ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $this->total_locations;

		return $data;
	}

	/**
	 * Delete import notice option when done importing
	 * 
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function import_done( $data ) {
		delete_option( 'gmw_old_locations_tables_exist' );
		update_option( 'gmw_old_locations_tables_updated', true );
	}
}

/**
 * Users Locations importer class
 *
 * @since 3.0
 */
class GMW_Users_Locations_Importer_V3 extends GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 * @var string
	 */
	protected $object_type = 'user';

	/**
	 * records to import per batch
	 * @var integer
	 */
	protected $records_per_batch = 20;

	/**
	 * Message
	 * @var string
	 */
	public $form_message = 'Import existing users/members locations into GEO my WP v3.0 database table.';

	/**
	 * Get location from database
	 * @return [type] [description]
	 */
	public function query_locations() {

		global $wpdb;

		//check for post types table
		$gmw_users_table = 'wppl_friends_locator';

		//count rows only when init the importer
		$count_rows = $this->total_locations == 0 ? 'SQL_CALC_FOUND_ROWS' : '';

		/** 
		 * check if street_name and street number columns exists. If so, add them to the query below.
		 *
		 * These columns added to GEO my WP in a later version, so in some sites they 
		 *
		 * might not exists. 
		 *
		 * We do this to prevent error with the importer.
		 *
		 */
		$street_colums = '';
		if ( $wpdb->get_results( "SHOW COLUMNS FROM {$gmw_users_table} LIKE 'street_name'" ) != false ) {
			$street_colums = 'gmwLocations.street_number, gmwLocations.street_name,';
		}

		//get records from database
		$data = $wpdb->get_results( "
			SELECT {$count_rows} 
			gmwLocations.member_id as object_id, 
			gmwLocations.member_id as user_id, 
			wpusers.user_status as status,		
			'0' as featured,
			wpusers.display_name as title,
			gmwLocations.lat as latitude,
			gmwLocations.long as longitude,
			{$street_colums}
			gmwLocations.street,
			gmwLocations.apt as permise,
			gmwLocations.city,
			gmwLocations.state_long as region_name,
			gmwLocations.state as region_code,
			gmwLocations.zipcode as	postcode,
			gmwLocations.country_long as country_name,
			gmwLocations.country as country_code,
			gmwLocations.address,
			gmwLocations.formatted_address,
			gmwLocations.map_icon,
			wpusers.user_registered as created
			FROM {$gmw_users_table} gmwLocations 
			INNER JOIN {$wpdb->users} wpusers 
			ON gmwLocations.member_id = wpusers.ID
			LIMIT {$this->records_completed}, {$this->records_per_batch}
		");
			
		//count rows only when init the importer
		$this->total_locations = $this->total_locations == 0 ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $this->total_locations;

		return $data;
	}

	/**
	 * Delete import notice option when done importing
	 * 
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function import_done( $data ) {
		delete_option( 'gmw_old_locations_tables_exist' );
		update_option( 'gmw_old_locations_tables_updated', true );
	}
}
