<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Locations_Importer' ) ) :

/**
 * GMW_Locations_Importer class
 *
 * This class can be used to batch import locations into GMW Locations database table ( gmw_locations ).
 *
 * This importer does not geocode locations but import geocoded locations into the table.
 *
 * You can probably batch geocode locations using the modify_location() method.
 *
 * It can be extended and used using a child class.
 */
class GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * Form message - the message to display in the importer form
	 * @var string
	 */
	public $form_message = '';

	/**
	 * Array of locations to import
	 * @var array
	 */
	protected $locations = array();

	/**
	 * Records to import per batch
	 * @var integer
	 */
	protected $records_per_batch = 30;

	/**
	 * Import locationmeta based on array of field => meta_key.
	 * 
	 * This function meant to work only when the meta values are included in the $locations array.
	 * 
	 * You will need to set the $location_meta_fields as array of field => meta_key pairs. 
	 * 
	 * The field will be the key in the $location object and meta_key will be the meta_key you'd like to save it to.
	 *
	 * Ex. to import the phone and fax numbers into location meta you can do something like:
	 *
	 * $location_meta_fields = array(
	 * 		'phone' => 'phone',
	 * 		'fax'	=> 'fax_number'
	 * )
	 *
	 * The above means that the $location object will have $location->phone and $location->fax which will hold the values
	 *
	 * That you want to save in phone and fax_number meta_keys.
	 **/
	protected $location_meta_fields = array();

	/**
	 * Query the locations need to import
	 * 
	 * Build a custom query in a child class that will pull all locations from database into an array of objects.
	 * 
	 * @return [type] [description]
	 */
	protected function query_locations() {}

	/**
	 * Update exsiting locations
	 * 
	 * @var boolean
	 */
	protected $update_locations = false;

	/**
	 * [done_message description]
	 * @return [type] [description]
	 */
	public function done_message() {
		return false;
	}

	/**
	 * Show locations log in window console. 
	 *
	 * Will show location information of all imported, exising and failed cases of an importing process
	 * 
	 * @var array
	 */
	private $locations_log = array(
		'imported' => array(),
		'exists'   => array(),
		'failed'   => array()
	);

	/**
	 * display import log in window console
	 * @var array
	 */
	private $log = array();

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Importer form
	 * 
	 * @return [type] [description]
	 */
	public function form() {
		?>
		<form method="post" class="gmw-locations-importer" action="">
			
			<input type="submit" class="gmw-locations-importer-submit button-primary" value="<?php _e( 'Import', 'geo-my-wp' ); ?>" />
			
			<input type="button" class="gmw-locations-importer-abort button-secondary" value="<?php _e( 'Abort', 'geo-my-wp' ); ?>" style="display:none;" />
			
			<input type="hidden" class="gmw_locations_importer_action" value="<?php echo esc_attr( get_class( $this ) ); ?>" />	
	    	
	    	<?php $nonce = wp_create_nonce( 'gmw_importer_nonce_'.get_class( $this ) ); ?>

	    	<input type="hidden" name="nonce" class="gmw_locations_importer_nonce" value="<?php echo $nonce; ?>">

	    </form>
	    <?php
	}

	/**
	 * Import details - displays the importer process and results
	 * 
	 * @return [type] [description]
	 */
	public function get_import_details() {

		$updated = '';
		$exist   = 'style="display:none"';
		
		if ( ! $this->update_locations ) {
			$updated = 'style="display:none"';
			$exist   = '';
		}

		$output = array();

		// create new form details element
        $output['div'] 	     = '<div class="gmw-importer-details" style="display:none;">';
        
        $output['importing'] = '<em class="importer-action-message"><i class="gmw-importer-spinner gmw-icon-spin-3 animate-spin"></i><span class="action-ph">' . __( 'Searching for locations...', 'geo-my-wp' ). '</span></em>';
        
        $output['bar'] 		 = '<div class="gmw-importer-progress-bar"><div class="importing"></div></div>';
        
        $output['scanned']   = '<p class="locations-completed-message">'. sprintf( __( '%s out of %s locations were scanned.', 'geo-my-wp' ), '<span class="completed-ph">0</span>', '<span class="total-ph">0</span>' ) .'</p>';
        
        $output['updated']   = '<p class="updated-locations-message" '.$updated.'>'. sprintf( __( '%s locations successfully updated ( were already exsist ).', 'geo-my-wp' ), '<span class="updated-ph">0</span>' ) . '</p>';
        
        $output['imported']  =  '<p class="imported-locations-message">' . sprintf( __( '%s locations successfully imported.', 'geo-my-wp' ), '<span class="imported-ph">0</span>' ) . '</p>';
        
        $output['existing']  =  '<p class="existing-locations-message" '.$exist .'>' . sprintf( __( '%s locations already exist ( were not updated ).', 'geo-my-wp' ), '<span class="existing-ph">0</span>' ) . '</p>';
        $output['failed'] 	 =  '<p class="failed-locations-message">' . sprintf( __( '%s failed to import.', 'geo-my-wp' ), '<span class="failed-ph">0</span>' ) . '</p>';

        $done_message = $this->done_message();

        if ( ! empty( $done_message ) ) {
        	$output['done']  =  '<p class="done-message" style="display:none">'.esc_html( $done_message ).'</p>';
        }

        $output['/div']   	 = '</div>';

        $output = apply_filters( 'gmw_locations_imported_details_output', $output, $this );

        return implode( ' ', $output );
	}

	/**
	 * Display the importer form and details
	 * 
	 * @return [type] [description]
	 */
	public function output() {	
		
		if ( ! wp_style_is( 'gmw-locations-importer', 'enqueued' ) ) {
			wp_enqueue_style( 'gmw-locations-importer' );
		}
		?>
		<div class="gmw-locations-importer-wrapper object-<?php echo esc_attr( $this->object_type ); ?> <?php echo esc_attr( get_class( $this ) ); ?>">

			<?php if ( ! empty( $this->form_message ) ) { ?>
				<p><?php echo esc_attr( $this->form_message ); ?></p>
			<?php } ?>
			
			<?php echo $this->get_import_details(); ?>
			<?php $this->form(); ?>
		</div>

		<?php
		if ( ! wp_script_is( 'gmw-locations-importer', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw-locations-importer' );
		}
	}

	/**
	 * Get importer data
	 * 
	 * @return [type] [description]
	 */
	public function get_data() {

		$this->total_locations 	  = absint( $_POST['totalLocations'] );
		$this->records_completed  = absint( $_POST['recordsCompleted'] );
		$this->locations_updated  = absint( $_POST['locationsUpdated'] );
		$this->locations_imported = absint( $_POST['locationsImported'] );
		$this->locations_exist 	  = absint( $_POST['locationsExist'] );
		$this->locations_failed   = absint( $_POST['locationsFailed'] );
		$this->batch_number   	  = absint( $_POST['batchNumber'] );
	}

	/**
	 * Modify the location args of each location in the loop before import
	 * 
	 * @param  array $location_args location args before imported to database
	 * 
	 * @return array modified location args before imported to database.
	 */
	protected function modify_location( $location_args ) {
		return $location_args;
	}

	/**
	 * Location exist. 
	 * @param  object $existing_location the location already exist in database
	 * @param  object $location          the new location we are trying to import
	 * @return void                    
	 */
	protected function location_exist( $existing_location, $location ) {
		
		//count location as exist
		$this->locations_exist++;

		//add status to log
		$existing_location->log = 'exist';
		$this->log[] = $existing_location; 
	}

	/**
	 * Location failed.
	 * @param  object $location      location we tried to import.
	 * @param  array  $location_args location_args 
	 * @return void               
	 */
	protected function location_failed( $location, $location_args ) {

		//count location as failed
		$this->locations_failed++;

		//add status to log
		$location_args['log'] = 'failed';
		$this->log[] = ( object ) $location_args; 
	}

	/**
	 * Location updated
	 * 
	 * @param  int $location_id     the ID of the updated location.
	 * @param  object $location     the location we just updated
	 * @param  array $location_args the location_args before import
	 * @return void                
	 */
	protected function location_updated( $location_id, $location, $location_args ) {
		
		//mark location as imported
		$this->locations_updated++;

		//add status and location ID to log
		$location_args['ID']  = $location_id;
		$location_args['log'] = 'updated';
		$this->log[] 		  = ( object ) $location_args; 
	}

	/**
	 * Location imported
	 * @param  int $location_id     the ID of the new imported location.
	 * @param  object $location     the location we just imported
	 * @param  array $location_args the location_args before import
	 * @return void                
	 */
	protected function location_imported( $location_id, $location, $location_args ) {
		
		//mark location as imported
		$this->locations_imported++;

		//add status and location ID to log
		$location_args['ID']  = $location_id;
		$location_args['log'] = 'imported';
		$this->log[] 		  = ( object ) $location_args; 
	}

	/**
	 * Import locationmeta based on array of field => meta_key.
	 * 
	 * This function ment to work only when the meta values are included in the $locations array.
	 * 
	 * You will need to set the $location_meta_fields as array of field => meta_key pairs. 
	 * 
	 * The field will be the key in the $location object and meta_key will be the meta_key you'd like to save it to.
	 *
	 * Ex. to import the phone and fax numbers into location meta you can do something like:
	 *
	 * $location_meta_fields = array(
	 * 		'phone' => 'phone',
	 * 		'fax'	=> 'fax_number'
	 * )
	 *
	 * The above means that the $location object will have $location->phone and $location->fax which will hold the values
	 *
	 * That you want to save in phone and fax_number meta_keys.
	 *
	 * If you cannot include the location meta values in the $locations array when building the query you will need to
	 *
	 * have a custom import_locationmeta() method that will save the location meta.
	 * 
	 * @param  int   $location_id the ID of the location imported
	 * @param  array $location    imported location data 
	 * @return void             
	 */
	protected function import_locationmeta( $location_id, $location ) {

		foreach ( $this->location_meta_fields as $field => $meta_key ) {

			if ( ! empty( $location->$field ) ) {
				gmw_update_location_meta( $location_id, $meta_key, $location->$field );
			}
		}
	}

	/**
	 * Loop and import locations
	 * @return void 
	 */
	public function import_locations() {

		global $wpdb;

		//loop locations
		foreach ( $this->locations as $location ) {

			if ( ! is_object( $location ) ) {
				$location = ( object ) $location;
			}

			//check if location already exist GEO my WP locations table.
			//if it does then no need to import again.
			$existing_location = GMW_Location::get_locations( $this->object_type, $location->object_id );

			// if location exists already and don't need to update it, skip it.
			if ( ! empty( $existing_location ) ) {

				$this->location_exist( $existing_location[0], $location );

				// abort this location if no need to updated it
				if ( ! $this->update_locations ) {
					continue;
				}
			}

			$default_location_fields = GMW_Location::default_values();
	
			$location_args = array(
				'object_type' => $this->object_type
			);

			foreach ( $default_location_fields as $field_name => $default_value ) {

				if ( $field_name == 'object_type' ) {
					
					$location_args['object_type'] = $this->object_type;

					continue;
				}

				if ( $field_name == 'created' ) {

					$location_args['created'] = ! empty( $location->created )  ? $location->created : current_time( 'mysql' );

					continue;
				}

				if ( $field_name == 'updated' ) {

					$location_args['updated'] = ! empty( $location->updated )  ? $location->updated : current_time( 'mysql' );
					
					continue;
				}

				$location_args[$field_name] = ! empty( $location->$field_name ) ? $location->$field_name : $default_value;
			}

			/*
			//new location row values
			$location_args = array( 
				'object_type'		=> $this->object_type,
				'object_id'			=> $location->object_id,
				'user_id'			=> ! empty( $location->user_id  ) ? $location->user_id  : 1,
				'status'        	=> ! empty( $location->status   ) ? $location->status   : 1,
				'featured'			=> ! empty( $location->featured ) ? $location->featured : 0,
				'title'				=> ! empty( $location->title ) ? $location->title : '',
				'latitude'          => ! empty( $location->latitude ) ? ,
				'longitude'         => ! empty( $location->longitude ) ? ,
				'street_number' 	=> ! empty( $location->street_number ) ? ,
				'street_name' 		=> ! empty( $location->street_name ) ? ,
				'street'			=> ! empty( $location->street ) ? ,
				'premise'       	=> ! empty( $location->permise ) ? ,
				'neighborhood'  	=> '',
				'city'          	=> ! empty( $location->city,
				'county'			=> '',
				'region_name'   	=> ! empty( $location->region_name,
				'region_code'   	=> ! empty( $location->region_code,
				'postcode'      	=> ! empty( $location->postcode,
				'country_name'  	=> ! empty( $location->country_name,
				'country_code'  	=> ! empty( $location->country_code,
				'address'			=> ! empty( $location->address,
				'formatted_address' => ! empty( $location->formatted_address ) ? $location->formatted_address : $location->address,
				'place_id'			=> ! empty( $location->place_id ) ? $location->place_id : '',
				'map_icon'			=> ! empty( $location->map_icon ) ? $location->map_icon : '_default.png',
				'created'       	=> ! empty( $location->date_created )  ? $location->date_created  : current_time( 'mysql' ),
				'updated'       	=> ! empty( $location->date_modified ) ? $location->date_modified : current_time( 'mysql' ),
			);
			
			*/
		
			// allow to modify the $location_args before tempting to import
			$location_args = $this->modify_location( $location_args );

			$location_args = apply_filters( 'gmw_locations_importer_location_args', $location_args, $this );

			// try to import location	
			$location_id = gmw_update_location_data( $location_args );

			// if location failed importing
			if ( empty( $location_id ) ) {
				
				$this->location_failed( $location, $location_args );
				continue;
			}

			// if we updated existing location
			if ( ! empty( $existing_location ) ) {

				// location imported
				$this->location_updated( $location_id, $location, $location_args );

			// otherwise mark it as imported
			} else {
				// location imported
				$this->location_imported( $location_id, $location, $location_args );
			}

			// import location meta
			if ( ! empty( $this->location_meta_fields ) ) {
				$this->import_locationmeta( $location_id, $location );
			}
		}
	}
	
	/**
	 * Send data to json
	 * @return [type] [description]
	 */
	public function send_json() {

		//check for records completed
		$this->records_completed = $this->locations_imported + $this->locations_exist + $this->locations_failed;

		//calculate percentage done
		if ( $this->total_locations != 0 ) {
			$percentage = ( $this->records_completed / $this->total_locations ) * 100 ;
		} else {
			$percentage = '100';
		} 
		
		//pass data to json
		wp_send_json( array( 
			'total_locations' 	 => $this->total_locations, 
			'locations_updated'  => $this->locations_updated,
			'locations_imported' => $this->locations_imported, 
			'locations_exist' 	 => $this->locations_exist,
			'locations_failed' 	 => $this->locations_failed,  
			'records_completed'  => $this->records_completed,
			'batchNumber'		 => $this->batch_number++,
			'percentage' 		 => $percentage,
			'log'				 => $this->log,
			'done'	 			 => $this->records_completed < $this->total_locations ? false : true
		) );

		//Done, good job!
		exit;
	}

	/**
	 * Run the importer
	 * @return void 
	 */
	public function process_import() {

		$this->get_data();

		//MYSQL query to get locations
		$this->locations = $this->query_locations();

		//import locations.
		$this->import_locations();

		//send data to json.
		$this->send_json();
	}

	/**
	 * Do something when importer is done
	 * 
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public function import_done( $data ) {

		add_action( 'gmw_locations_importer_done', $data );
	}
}

/**
 * Batch locations class init. 
 *
 * Trigger the child class that will execute the importer
 * @return [type] [description]
 */
function gmw_locations_importer_init() {

	//verify ajax nonce
	if ( ! check_ajax_referer( 'gmw_importer_nonce_'.$_POST['importAction'], 'security', false ) ) {

		//abort if bad nonce
		wp_die( __( 'Trying to cheat or something?', 'geo-my-wp' ), __( 'Error', 'geo-my-wp' ), array( 'response' => 403 ) );
	}

	// verify that a child class name passes.
	if ( empty( $_POST['importAction'] ) ) {
		
		wp_die( __( 'Action class name is missing', 'geo-my-wp' ), E_USER_ERROR );
		
		exit;
	}

	//get the child class name
	$class_name = $_POST['importAction'];
	
	//verify that the class exists.
	if ( ! class_exists( $class_name ) ) {

		wp_die( sprintf( __( 'Call to undefined function %s', 'geo-my-wp' ), $class_name ), E_USER_ERROR );
		
		exit;
	}

	$output = new $class_name;

	//run the importer
	if ( $_POST['action'] == 'gmw_locations_importer' ) {
		$output->process_import();
	}

	// importer done
	if ( $_POST['action'] == 'gmw_locations_importer_done' ) {
		$output->import_done( $_POST );
	}
}
add_action( 'wp_ajax_gmw_locations_importer', 'gmw_locations_importer_init' );
add_action( 'wp_ajax_gmw_locations_importer_done', 'gmw_locations_importer_init' );

endif;
