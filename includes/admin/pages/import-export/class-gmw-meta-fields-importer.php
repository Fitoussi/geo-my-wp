<?php
/**
 * GEO my WP Meta Fields Importer form.
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
 * GMW_Meta_Fields_Importer_Form class.
 *
 * Generates the meta fields importer form with the meta fields to select and the importer form.
 *
 * @since 4.0
 *
 * @author Eyal Fitoussi.
 */
class GMW_Meta_Fields_Importer_Form {

	/**
	 * Name singular.
	 *
	 * @var string
	 */
	public $singular_name = '';

	/**
	 * Name plural.
	 *
	 * @var string
	 */
	public $plural_name = '';

	/**
	 * Slug.
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * Name of the importer class.
	 *
	 * @var string
	 */
	public $importer_class = '';

	/**
	 * Meta fields function name.
	 *
	 * @var string
	 */
	public $meta_field_function = '';

	/**
	 * If the saved field value need to be modified when generating the field select option.
	 *
	 * This is the selected value saved in the database.
	 *
	 * @param  [type] $field [description].
	 *
	 * @return [type]        [description]
	 */
	public function get_saved_field_label( $field ) {
		return $field;
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {
		$this->save_meta_fields();
	}

	/**
	 * Get the section title.
	 *
	 * @return [type] [description]
	 */
	public function get_title() {
		return '';
	}

	/**
	 * Button label.
	 *
	 * @return [type] [description]
	 */
	public function inner_element_title() {
		/* translators: %s meta field type. */
		return sprintf( __( 'Select %s fields', 'geo-my-wp' ), $this->singular_name );
	}

	/**
	 * Get section description.
	 *
	 * @return [type] [description]
	 */
	public function get_description() {
		return __( 'Use this importer to import locations from specific meta fields into GEO my WP.', 'geo-my-wp' );
	}

	/**
	 * Location fields that can be imported.
	 *
	 * @return [type] [description]
	 */
	public function get_location_fields() {

		return array(
			'latitude'          => __( 'Latitude ( required )', 'geo-my-wp' ),
			'longitude'         => __( 'Longitude ( required )', 'geo-my-wp' ),
			'street_number'     => __( 'Street Number', 'geo-my-wp' ),
			'street_name'       => __( 'Street Name', 'geo-my-wp' ),
			'street'            => __( 'Street ( number + name )', 'geo-my-wp' ),
			'premise'           => __( 'Apt / Premise', 'geo-my-wp' ),
			'neighborhood'      => __( 'Neighborhood', 'geo-my-wp' ),
			'city'              => __( 'City', 'geo-my-wp' ),
			'county'            => __( 'County', 'geo-my-wp' ),
			'region_name'       => __( 'State Name ( ex. Florida )', 'geo-my-wp' ),
			'region_code'       => __( 'State Code ( ex. FL )', 'geo-my-wp' ),
			'postcode'          => __( 'Zipcode', 'geo-my-wp' ),
			'country_name'      => __( 'Country Name ( ex. United States )', 'geo-my-wp' ),
			'country_code'      => __( 'Country Code ( ex. US )', 'geo-my-wp' ),
			'address'           => __( 'Address ( address field the way the user enteres )', 'geo-my-wp' ),
			'formatted_address' => __( 'Formatted Address ( formatted address returned from Google after geocoding )', 'geo-my-wp' ),
			'place_id'          => __( 'Google Place ID', 'geo-my-wp' ),
			'map_icon'          => __( 'Map Icon', 'geo-my-wp' ),
			'phone'             => __( 'Phone', 'geo-my-wp' ),
			'fax'               => __( 'Fax', 'geo-my-wp' ),
			'email'             => __( 'Email', 'geo-my-wp' ),
			'website'           => __( 'Website', 'geo-my-wp' ),
		);
	}

	/**
	 * Output.
	 */
	public function output() {

		$slug        = esc_attr( $this->slug );
		$current_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

		do_action( 'gmw_before_' . $slug . '_import' );
		?>
		<div class="gmw-settings-panel gmw-<?php echo esc_attr( $slug ); ?>-import-panel">

			<fieldset>

				<legend class="gmw-settings-panel-title"><?php echo esc_html( $this->get_title() ); ?></legend>

				<div class="gmw-settings-panel-content">

					<div class="gmw-settings-panel-description">

						<?php
						$allowed = array(
							'p'     => array(),
							'class' => array(),
							'id'    => array(),
							'b'     => array(),
							'style' => array(),
							'span'  => array(
								'class' => array(),
							),
							'a'     => array(
								'href'   => array(),
								'target' => array(),
								'class'  => array(),
							),

						);
						echo wp_kses( $this->get_description(), $allowed );

						/* translators: %s meta field type. */
						echo '<p>' . sprintf( esc_html__( 'To import locations, click the Select Fields button and select a %s field for each of GEO my WP\'s location fields.', 'geo-my-wp' ), esc_attr( $this->singular_name ) ) . '</p>';

						echo '<div class="gmw-admin-notice-box gmw-admin-notice-warning">';
						esc_html_e( 'Note that only the address, latitude, and longitude fields are required in order for GEO my WP to search and find locations and display them on the map. However, to take full advantage of GEO my WP features it is recomended to provide as many fields as possible.', 'geo-my-wp' ) . '</p>';
						echo '</div>';
						?>
					</div>

					<div class="gmw-settings-panel-field">

						<div id="gmw-mata-fields-importer-element" class="gmw-popup-element-wrapper">

							<div class="gmw-popup-element-inner">

								<span class="gmw-popup-element-close-button gmw-icon-cancel-light"></span>

								<h3><?php echo esc_html( $this->inner_element_title() ); ?></h3>

								<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin.php?page=gmw-import-export&tab=' . $current_tab ) ); ?>">
									<?php
									$saved_fields = get_option( 'gmw_importer_' . $slug . '_fields' );

									if ( empty( $saved_fields ) ) {
										$saved_fields = array();
									}

									$fields_data = $this->get_location_fields();
									?>
									<div id="<?php echo $slug; // WPCS: XSS ok. ?>-wrapper" class="gmw-popup-element-fields-wrapper">

										<?php foreach ( $fields_data as $name => $title ) { ?>

											<?php $name = esc_attr( $name ); ?>
											<p class="gmw-popup-element-single-field">
												<label><?php echo esc_attr( $title ); ?>: </label>
												<select
													id="gmw-import-<?php echo esc_attr( $this->slug ); ?>-<?php echo $name; // WPCS: XSS ok. ?>"
													class="gmw-import-meta-field gmw-import-<?php echo esc_attr( $this->slug ); ?>"
													name="gmw_<?php echo $slug; // WPCS: XSS ok. ?>[<?php echo $name; // WPCS: XSS ok. ?>]"
													data-gmw_ajax_load_options="<?php echo esc_attr( $this->meta_field_function ); ?>"
													style="width:100%;"
												>
													<option value="" selected="selected"><?php esc_html_e( 'Disable', 'geo-my-wp' ); ?></option>

													<?php if ( ! empty( $saved_fields[ $name ] ) ) { ?>
														<option value="<?php echo esc_attr( $saved_fields[ $name ] ); ?>" selected="selected"><?php echo esc_attr( $this->get_saved_field_label( $saved_fields[ $name ] ) ); ?></option>
													<?php } ?>

												</select>
											</p>

										<?php } ?>

										<p>
											<input type="hidden" name="gmw_action" value="save_<?php echo $slug; // WPCS: XSS ok. ?>_fields" />

											<?php wp_nonce_field( 'gmw_save_' . $slug . '_fields_nonce', 'gmw_save_' . $slug . '_fields_nonce' ); ?>

											<input type="submit" id="gmw-popup-element-<?php echo $slug; // WPCS: XSS ok. ?>-submit" class="gmw-popup-element-meta-fields-submit button-primary gmw-settings-action-button" value="<?php esc_attr_e( 'Save Fields', 'geo-my-wp' ); ?>" />
										</p>
									</div>
								</form>
							</div>

						</div>

						<a href="#" class="gmw-popup-element-toggle gmw-settings-action-button button-secondary" data-element="#gmw-mata-fields-importer-element">
							<?php esc_html_e( 'Select Fields', 'geo-my-wp' ); ?>
						</a>
						<div class="gmw-meta-fields-importer-wrapper">
							<?php if ( empty( $saved_fields['latitude'] ) || empty( $saved_fields['longitude'] ) ) { ?>

								<input type="submit" class="gmw-meta-fields-importer-disabled-button gmw-settings-action-button button-primary" value="<?php esc_attr_e( 'Import', 'geo-my-wp' ); ?>" disabled="disabled" />

								<div class="gmw-admin-notice-box gmw-admin-notice-error"><?php esc_html_e( 'You must set the latitude and longitude fields before you can import locations.', 'geo-my-wp' ); ?></div>

							<?php } else { ?>

								<?php
								if ( class_exists( $this->importer_class ) ) {
									$cf_importer = new $this->importer_class();
									$cf_importer->output();
								}
								?>

							<?php } ?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<?php

		do_action( 'gmw_after_' . $slug . '_import' );

		if ( ! wp_script_is( 'gmw-select2', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw-select2' );
			wp_enqueue_style( 'gmw-select2' );
		}
	}

	/**
	 * Svae meta fields.
	 *
	 * @return [type] [description]
	 */
	public function save_meta_fields() {

		$slug        = esc_attr( $this->slug );
		$current_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // WPCS: CSRF ok.

		if ( empty( $_POST[ 'gmw_' . $slug ] ) ) {
			return;
		}

		// look for nonce.
		if ( empty( $_POST[ 'gmw_save_' . $slug . '_fields_nonce' ] ) ) {
			wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		// varify nonce.
		if ( ! wp_verify_nonce( $_POST[ 'gmw_save_' . $slug . '_fields_nonce' ], 'gmw_save_' . $slug . '_fields_nonce' ) ) { // WPCS: CSRF ok, sanitization ok.
			wp_die( esc_html__( 'Cheatin\' eh?!', 'geo-my-wp' ) );
		}

		$output = array();

		foreach ( $_POST[ 'gmw_' . $slug ] as $key => $value ) { // WPCS: CSRF ok, sanitization ok.
			$output[ sanitize_text_field( wp_unslash( $key ) ) ] = sanitize_text_field( wp_unslash( $value ) );
		}

		// save custom fields in options table.
		update_option( 'gmw_importer_' . $slug . '_fields', $output );

		wp_safe_redirect(
			admin_url(
				'admin.php?page=gmw-import-export&tab=' . $current_tab . '&gmw_notice=&gmw_notice_status=updated'
			)
		);

		exit;
	}
}
