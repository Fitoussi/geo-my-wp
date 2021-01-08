<?php
/**
 * Export Import tab
 *
 * @since 2.5
 *
 * @author The functions below inspired by functions written by Pippin Williamson.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export/Import tab output
 *
 * @access public
 * @since 2.5
 * @author Eyal Fitoussi
 */
function gmw_output_import_export_tab() {
	?>	
	<ul class="gmw-tabs-wrapper">

		<?php
		$tabs = array(
			// 'gmw_custom_fields'  => __( 'GEO my WP Custom Fields', 'geo-my-wp' ),
			'post_custom_fields' => __( 'Post Custom Fields', 'geo-my-wp' ),
		);

		if ( class_exists( 'Mappress' ) ) {
			$tabs['mappress_plugin'] = __( 'MapPress Plugin', 'geo-my-wp' );
		}

		// filter tabs.
		$tabs = apply_filters( 'gmw_import_export_posts_locator_tabs', $tabs );

		foreach ( $tabs as $key => $title ) {
			echo '<li><a href="#" id="' . esc_attr( sanitize_title( $key ) ) . '" title="' . esc_attr( $title ) . '"  class="gmw-nav-tab" data-name="' . esc_attr( sanitize_title( $key ) ) . '">' . esc_attr( $title ) . '</a></li>';
		}
		?>
	</ul>			

	<!-- import export GMW post meta -->

	<div class="gmw-tab-panel gmw_custom_fields">

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox ">

						<h3 class="hndle">
							<span><?php _e( 'Export/Import Posts Types Locations using GEO my WP post_meta', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">
							<p>
								<?php _e( 'The forms below will help you in the process of exporting the post types locations created on this site and importing them into a different site.', 'geo-my-wp' ); ?><br />
								<?php printf( __( 'The export/import forms below need to be used together with the native <a href="%1$s" target="blank"> WordPress export system*</a> and <a href="%2$s" target"_blank">WordPress importer*</a> for a complete process.', 'geo-my-wp' ), admin_url( 'export.php' ), admin_url( 'import.php' ) ); ?><br />
							</p>
							<p class="description">	
								<?php _e( '*You can use other plugins ( other than the WordPress native plugins mentioned above ) to export/import your WordPress posts. However, the plugins you chose to use must export and import the custom fields of these posts in order to import/export the locations.', 'geo-my-wp' ); ?>
							</p>
							<p>
								<?php _e( 'Please follow the steps of each form below for a complete process of exporting and importing your post types locations.', 'geo-my-wp' ); ?><br />				
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox ">

						<h3 class="hndle">
							<span><?php _e( 'Export Posts Types Locations To GEO my WP post_meta', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">
							<ol>	
								<?php global $wpdb; ?> 
								<li>
									<?php printf( __( "Click on the \"Export\" button below. By doing so the plugin will duplicate each post type location created on this site from GEO my WP's custom table ( %splaces_locator ) into a custom field of the post it belongs to.", 'geo-my-wp' ), $wpdb->prefix ); ?>
									<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
										<p>
											<input type="hidden" name="gmw_action" value="pt_locations_post_meta_export" />
											<?php wp_nonce_field( 'gmw_pt_locations_post_meta_export_nonce', 'gmw_pt_locations_post_meta_export_nonce' ); ?>
											<?php submit_button( __( 'Export', 'geo-my-wp' ), 'secondary', 'submit', false ); ?>
										</p>
									</form>
								</li>
								<li><?php printf( __( 'The next step will be to export your posts using the native <a href="%s" target="blank"> WordPress export system</a>.', 'geo-my-wp' ), esc_url( admin_url( 'export.php' ) ) ); ?></li>
							</ol>
					</div>
					</div>
				</div>
			</div>
		</div>

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox ">

						<h3 class="hndle">
							<span><?php esc_html_e( 'Import Posts Types Locations From GEO my WP post_meta', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">
							<ol>
								<li><?php esc_html_e( 'Before importing your locations into this site make sure you used the "Export" form above on the original site in order to export your locations.', 'geo-my-wp' ); ?></li>
								<li><?php printf( __( 'Import your posts using <a href="%s" target"_blank">WordPress importer</a>. After done so come back to this page to complete step 3.', 'geo-my-wp' ), admin_url( 'import.php' ) ); ?></li>
								<li><?php printf( __( "Click on the \"Import\" button. By doing so the plugin will duplicate each post type location from the custom field of the post it belongs to into GEO my WP's custom table in database ( %splaces_locator ).", 'geo-my-wp' ), $wpdb->prefix ); ?></li>
							</ol>

							<?php
							// get all custom fields with gmw location from database.
							$check_pm_locations = $wpdb->get_results(
								"
									SELECT *
									FROM `{$wpdb->prefix}postmeta`
									WHERE `meta_key` = 'gmw_pt_location'",
								ARRAY_A
							);

							// abort if no locations found.
							$check_pm_locations = ( ! empty( $check_pm_locations ) ) ? true : false;
							?>
							<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-tools&tab=import_export' ); ?>">
								<p>
									<input type="hidden" name="gmw_action" value="pt_locations_post_meta_import" />
									<?php wp_nonce_field( 'gmw_pt_locations_post_meta_import_nonce', 'gmw_pt_locations_post_meta_import_nonce' ); ?>
									<input type="submit" class="button-secondary" value="<?php _e( 'Import', 'geo-my-wp' ); ?>" <?php if ( ! $check_pm_locations ) { echo 'disabled="disabled"'; } ?>
									/>
									<?php echo ( $check_pm_locations ) ? '<em style="color:green">' . __( 'Locations are avalible for import.', 'geo-my-wp' ) . '</em>' : '<em style="color:red">' . __( 'No locations are avalible for import.', 'geo-my-wp' ) . '</em>'; ?>
								</p>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- import export from custom meta -->
	<div class="gmw-tab-panel post_custom_fields">

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox ">

						<h3 class="hndle">
							<span><?php esc_html_e( 'Post Custom Fields Importer', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">
							<p>
								<?php esc_html_e( 'Use this form to import locations from specific custom fields into GEO my WP. This is useful when importing locations created by another plugin and its location data is saved in custom fields.', 'geo-my-wp' ); ?><br />
							</p>
							<p>
								<?php esc_html_e( 'Before you can import locations you need to set the location custom fields. To do so, Click "Set custom field" to set each of GEO my WP\'s location fields.', 'geo-my-wp' ); ?>	
							</p>

							<p>			
								<em><?php esc_html_e( '* Note that only the latitude and longitude fields are mandatory. However, to take full advantage of GEO my WP features it is recomended to provide as many fields as possible.', 'geo-my-wp' ); ?>
								</em>
							</p>

							<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=gmw-import-export&tab=posts_locator' ); ?>">
								<?php

								$saved_fields = get_option( 'gmw_importer_post_custom_fields' );

								if ( empty( $saved_fields ) ) {
									$saved_fields = array();
								}

								global $wpdb;

								// get all existing custom fields.
								$cFields = $wpdb->get_col(
									"
						        	SELECT meta_key
						        	FROM   $wpdb->postmeta
						        	GROUP  BY meta_key
						        	ORDER  BY meta_id DESC"
								);

								if ( $cFields ) {
									natcasesort( $cFields );
								}

								$fields_data = array(
									'latitude'          => __( 'Latitude ( mandatory )', 'geo-my-wp' ),
									'longitude'         => __( 'Longitude ( mandatory )', 'geo-my-wp' ),
									'street_number'     => __( 'Street Number', 'geo-my-wp' ),
									'street_name'       => __( 'Street Name', 'geo-my-wp' ),
									'street'            => __( 'Street ( number + name )', 'geo-my-wp' ),
									'premise'           => __( 'Apt / Premise', 'geo-my-wp' ),
									'neighborhood'      => __( 'Neighborhood', 'geo-my-wp' ),
									'city'              => __( 'City', 'geo-my-wp' ),
									'county'            => __( 'County', 'geo-my-wp' ),
									'region_name'       => __( 'State Name ( ex. Florida )', 'geo-my-wp' ),
									'region_code'       => __( 'State Code ( ex. FL )', 'geo-my-wp' ),
									'postcode'          => __( 'zipcode', 'geo-my-wp' ),
									'country_name'      => __( 'Country Name ( ex. United States )', 'geo-my-wp' ),
									'country_code'      => __( 'Country Code ( ex. US )', 'geo-my-wp' ),
									'address'           => __( 'Address ( address field the way the user enteres )', 'geo-my-wp' ),
									'formatted_address' => __( 'Formatted Address ( formatted address returned from Google after geocoding )', 'geo-my-wp' ),
									'place_id'          => __( 'Google Place ID', 'geo-my-wp' ),
									'map_icon'          => __( 'Map Icon', 'geo-my-wp' ),
									'phone'             => __( 'Phone', 'geo-my-wp' ),
									'fax'               => __( 'Fax', 'geo-my-wp' ),
									'email'             => __( 'email', 'geo-my-wp' ),
									'website'           => __( 'website', 'geo-my-wp' ),
								);
								?>

								<a href="#" id="post-meta-fields-toggle" onclick="event.preventDefault(); jQuery('#post-meta-wrapper').slideToggle();">

									<?php esc_html_e( 'Set Custom Fields', 'geo-my-wp' ); ?>	
								</a>
   
								<div id="post-meta-wrapper" style="display:none">					

									<?php foreach ( $fields_data as $name => $title ) { ?>			

										<p>
											<label><?php echo esc_attr( $title ); ?>: </label>

											<select 
												id="gmw-import-custom-field-<?php echo $name; ?>"
												class="gmw-import-custom-field gmw-chosen" 
												name="gmw_post_meta[<?php echo $name; ?>]"
											>
												<option value="" selected="selected">
													<?php esc_html_e( 'N/A', 'geo-my-wp' ); ?>
												</option>

												<?php foreach ( $cFields as $cField ) { ?>

													<?php $selected = ( ! empty( $saved_fields[ $name ] ) && $saved_fields[ $name ] == $cField ) ? 'selected="selected"' : ''; ?>
													<option <?php echo $selected; ?> value="<?php echo esc_attr( $cField ); ?>"><?php echo esc_attr( $cField ); ?></option>

												<?php } ?>

											</select>
										</p>	

									<?php } ?>

									<p>	
										<input type="hidden" name="gmw_action" value="save_post_custom_fields" />

										<?php wp_nonce_field( 'gmw_save_post_custom_fields_nonce', 'gmw_save_post_custom_fields_nonce' ); ?>

										<input type="submit" id="import-custom-post-meta-submit" class="button-secondary" value="<?php esc_attr_e( 'Save Fields', 'geo-my-wp' ); ?>" />
									</p>
								</div>
							</form>
							<p>																				
								<?php
								if ( empty( $saved_fields['latitude'] ) || empty( $saved_fields['longitude'] ) ) {

									?>
											<p style="color:red"><?php _e( '*You must set the latitude and longitude fields before you can import locations.', 'geo-my-wp' ); ?>
											</p>
											<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Import', 'geo-my-wp' ); ?>" disabled />
										<?php

								} else {
									$cf_importer = new GMW_Post_Custom_Fields_Importer();
									$cf_importer->output();
								}
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- import export from custom meta -->
	<div class="gmw-tab-panel mappress_plugin">

		<div id="poststuff" class="metabox-holder">

			<div id="post-body">

				<div id="post-body-content">

					<div class="postbox ">

						<h3 class="hndle">
							<span><?php esc_html_e( 'MapPress Importer', 'geo-my-wp' ); ?></span>
						</h3>

						<div class="inside">
							<p>
								<?php esc_html_e( 'Use this form to import locations from MapPress plugin.' ); ?>
							</p>
							<p>
								<em><?php esc_html_e( '*Note, since the current version of GEO my WP supports only single location per post, this importer imports only the first location created by MapPress from each post.', 'geo-my-wp' ); ?></em>	
							</p>			
							<p>																				
								<?php

									$cf_importer = new GMW_Map_Press_Importer();
									$cf_importer->output();
								?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	// load chosen.
	if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );
	}
}
add_action( 'gmw_import_export_posts_locator_tab', 'gmw_output_import_export_tab' );

/**
 * Import locations from post_meta
 *
 * @since 2.5
 * @return void
 */
function gmw_save_post_custom_fields() {

	if ( empty( $_POST['gmw_post_meta'] ) ) {
		return;
	}

	// look for nonce.
	if ( empty( $_POST['gmw_save_post_custom_fields_nonce'] ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// varify nonce.
	if ( ! wp_verify_nonce( $_POST['gmw_save_post_custom_fields_nonce'], 'gmw_save_post_custom_fields_nonce' ) ) {
		wp_die( __( 'Cheatin\' eh?!', 'geo-my-wp' ) );
	}

	// save custom fields in options table.
	update_option( 'gmw_importer_post_custom_fields', $_POST['gmw_post_meta'] );

	wp_safe_redirect(
		admin_url(
			'admin.php?page=gmw-import-export&tab=posts_locator&gmw_notice=&gmw_notice_status=updated'
		)
	);

	exit;

}
add_action( 'gmw_save_post_custom_fields', 'gmw_save_post_custom_fields' );

/**
 * Locations importer class
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Post_Custom_Fields_Importer extends GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * records to import per batch.
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
		$location_fields = get_option( 'gmw_importer_post_custom_fields' );

		// count rows only when init the importer.
		$count_rows = $this->total_locations == 0 ? 'SQL_CALC_FOUND_ROWS' : '';

		// get posts.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT {$count_rows}
				wpposts.ID as object_id,
				wpposts.post_author as user_id,
				wpposts.post_title as title,
				wpposts.post_date as created, 
				wpposts.post_modified as updated,
				wppmeta.meta_value as latitude,
				wppmeta1.meta_value as longitude
				FROM {$wpdb->prefix}posts wpposts
				INNER JOIN {$wpdb->prefix}postmeta wppmeta
				ON ( wpposts.ID = wppmeta.post_id )  
				INNER JOIN {$wpdb->prefix}postmeta AS wppmeta1 
				ON ( wpposts.ID = wppmeta1.post_id )
				AND ( 
	  			( wppmeta.meta_key = '%s' AND wppmeta.meta_value NOT IN ('') ) 
	  			AND 
	  			( wppmeta1.meta_key = '%s' AND wppmeta1.meta_value NOT IN ('') )
				) 
				GROUP BY wpposts.ID 
				ORDER BY wpposts.ID 
				LIMIT %d, %d",
				array(
					$location_fields['latitude'],
					$location_fields['longitude'],
					$this->records_completed,
					$this->records_per_batch,
				)
			)
		);

		// count all rows only when init the importer.
		$this->total_locations = $this->total_locations == 0 ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $this->total_locations;

		// abort if nothing was found.
		if ( empty( $results ) ) {
			return array();
		}

		// get rest of location data from custom fields.
		foreach ( $results as $post_key => $post ) {

			foreach ( $location_fields as $key => $field ) {

				if ( 'latitude' === $key || 'longitude' === $key || empty( $field ) ) {
					continue;
				}

				$meta = get_post_meta( $post->object_id, $field, true );

				$results[ $post_key ]->$key = ! empty( $meta ) ? $meta : '';
			}
		}

		return $results;
	}
}

/**
 * Locations importer class
 *
 * @since 3.0
 *
 * @author Eyal Fitoussi
 */
class GMW_Map_Press_Importer extends GMW_Locations_Importer {

	/**
	 * The object type we importing.
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * Records to import per batch.
	 *
	 * @var integer
	 */
	protected $records_per_batch = 15;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	public $form_message = '';

	/**
	 * Get location to import
	 *
	 * @return [type] [description]
	 */
	public function query_locations() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'mappress_maps';

		// look for Mappress DB table.
		$table = $wpdb->get_results( "SHOW TABLES LIKE '{$table_name}'", ARRAY_A );

		// abort if no table exist.
		if ( count( $table ) == 0 ) {
			wp_die( sprintf( __( '%s database table cannot be found.', 'geo-my-wp' ), $table_name ) );
		}

		// count rows only when init the importer.
		$count_rows = $this->total_locations == 0 ? 'SQL_CALC_FOUND_ROWS' : '';

		// get posts.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DISTINCT {$count_rows}
				mpposts.*, 
				mpmaps.obj
				FROM {$wpdb->prefix}mappress_posts mpposts
				INNER JOIN {$wpdb->prefix}mappress_maps mpmaps
				ON mpposts.mapid = mpmaps.mapid
				GROUP BY mpposts.postid
				LIMIT %d, %d",
				array(
					$this->records_completed,
					$this->records_per_batch,
				)
			)
		);

		// count all rows only when init the importer.
		$this->total_locations = $this->total_locations == 0 ? $wpdb->get_var( 'SELECT FOUND_ROWS()' ) : $this->total_locations;

		// abort if nothing was found.
		if ( empty( $results ) ) {
			return array();
		}

		$output = array();

		foreach ( $results as $location ) {

			$post_id  = $location->postid;
			$location = maybe_unserialize( $location->obj );

			if ( ! empty( $location->pois ) && ! empty( $location->center['lat'] ) && ! empty( $location->center['lng'] ) ) {

				$location_args                      = array();
				$location_args['object_id']         = $post_id;
				$location_args['title']             = get_the_title( $post_id );
				$location_args['latitude']          = $location->center['lat'];
				$location_args['longitude']         = $location->center['lng'];
				$location_args['address']           = $location->pois[0]->address;
				$location_args['formatted_address'] = $location->pois[0]->correctedAddress;

				$output[] = $location_args;
			}
		}

		return (object) $output;
	}
}
add_action( 'gmw_mappress_import', 'gmw_mappress_import' );
