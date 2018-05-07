<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GMW_Location_Form' ) ) {
	return;
}

/**
 * GMW_Posts_Location_Form Class extends GMW_Location_Form class
 *
 * Location form for Post types in "Edit post" page.
 *
 * @since 3.0
 *
 */
class GMW_Post_Location_Form extends GMW_Location_Form {

	/**
	 * Addon
	 *
	 * @var string
	 */
	public $slug = 'posts_locator';

	/**
	 * Object type
	 *
	 * @var string
	 */
	public $object_type = 'post';

	/**
	 * Run the form class
	 * @param array $attr [description]
	 */
	public function __construct( $attr = array() ) {

		parent::__construct( $attr );

		// add custom tab panels
		add_action( 'gmw_lf_content_end', array( $this, 'create_tabs_panels' ) );
	}

	/**
	 * Additional custom form tabs
	 *
	 * @return array
	 */
	public function form_tabs() {

		$tabs = parent::form_tabs();

		$tabs['contact']    = array(
			'label'    => __( 'Contact', 'geo-my-wp' ),
			'icon'     => 'gmw-icon-phone',
			'priority' => 20,
		);
		$tabs['days_hours'] = array(
			'label'    => __( 'Days & Hours', 'geo-my-wp' ),
			'icon'     => 'gmw-icon-clock',
			'priority' => 25,
		);

		// filter tabs
		$tabs = apply_filters( 'gmw_post_location_form_tabs', $tabs, $this );

		return $tabs;
	}

	/**
	 * Additional form fields
	 *
	 * @return array
	 */
	function form_fields() {

		// retreive parent fields
		$fields = parent::form_fields();

		// contact meta fields
		$fields['contact_info'] = array(
			'label'  => __( 'Contact Information', 'geo-my-wp' ),
			'fields' => array(
				'phone'   => array(
					'name'        => 'gmw_pt_phone',
					'label'       => __( 'Phone Number', 'geo-my-wp' ),
					'desc'        => '',
					'id'          => 'gmw-phone',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '',
					'attributes'  => '',
					'priority'    => 1,
					'meta_key'    => 'phone',
				),
				'fax'     => array(
					'name'        => 'gmw_pt_fax',
					'label'       => __( 'Fax Number', 'geo-my-wp' ),
					'desc'        => '',
					'id'          => 'gmw-fax',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '',
					'attributes'  => '',
					'priority'    => 2,
					'meta_key'    => 'fax',
				),
				'email'   => array(
					'name'        => 'gmw_pt_email',
					'label'       => __( 'Email Address', 'geo-my-wp' ),
					'desc'        => '',
					'id'          => 'gmw-email',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '',
					'attributes'  => '',
					'priority'    => 3,
					'meta_key'    => 'email',
				),
				'website' => array(
					'name'        => 'gmw_pt_website',
					'label'       => __( 'Website', 'geo-my-wp' ),
					'desc'        => 'Ex: www.website.com',
					'id'          => 'gmw-website',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '',
					'attributes'  => '',
					'priority'    => 4,
					'meta_key'    => 'website',
				),
			),
		);

		// days and hours
		$fields['days_hours'] = array(
			'label'  => __( 'Days & Hours', 'geo-my-wp' ),
			'fields' => array(
				'days_hours' => array(
					'name'        => 'gmw_pt_days_hours',
					'label'       => __( 'Days & Hours', 'geo-my-wp' ),
					'desc'        => '',
					'id'          => 'gmw-days-hours',
					'type'        => 'text',
					'default'     => '',
					'placeholder' => '',
					'attributes'  => '',
					'priority'    => 1,
				),
			),
		);

		$fields = apply_filters( 'gmw_post_location_form_fields', $fields, $this );

		return $fields;
	}

	/**
	 * Generate custom tabs panels
	 *
	 * @return void
	 */
	public function create_tabs_panels() {

		do_action( 'gmw_post_location_form_before_panels', $this );
		?>
		<!-- contact info tab -->
		<div id="contact-tab-panel" class="section-wrapper contact">

			<?php do_action( 'gmw_lf_pt_contact_section_start', $this ); ?>

			<?php $this->display_form_fields_group( 'contact_info' ); ?>

			<?php do_action( 'gmw_lf_pt_contact_section_end', $this ); ?>

		</div>

		<!-- contact info tab -->
		<div id="days_hours-tab-panel" class="section-wrapper days-hours">

			<?php do_action( 'gmw_lf_post_days_hours_section_start', $this ); ?>

			<h3><?php _e( 'Days & Hours', 'geo-my-wp' ); ?></h3>

			<?php
				//get the location's days_hours from database
				$days_hours = gmw_get_location_meta( $this->location_id, 'days_hours' );

			if ( empty( $days_hours ) ) {
				$days_hours = array();
			}
			?>
			<table class="form-table">

				<?php for ( $i = 0; $i <= 6; $i++ ) { ?>

					<tr>
						<th style="width:30px">
							<label for=""><?php _e( 'Days', 'geo-my-wp' ); ?></label>
						</th>
						<td style="width:150px">
							<input type="text" class="gmw-lf-field group_days_hours" name="gmw_location_form[location_meta][days_hours][<?php echo $i; ?>][days]" id="gmw-pt-days-<?php echo $i; ?>" value="<?php if ( ! empty( $days_hours[$i]['days'] ) ) echo esc_attr( $days_hours[$i]['days'] ); ?>" />
						</td>

						<th style="width:30px">
							<label for=""><?php _e( 'Hours', 'geo-my-wp' ); ?></label>
						</th>

						<td>
							<input type="text" class="gmw-pt-field group_days_hours" name="gmw_location_form[location_meta][days_hours][<?php echo $i; ?>][hours]" id="gmw-pt-hours-<?php echo $i; ?>" value="<?php if ( ! empty( $days_hours[$i]['hours'] ) ) echo esc_attr( $days_hours[$i]['hours'] ); ?>" />
						</td>
					</tr>

				<?php } ?>

			</table>

			<?php do_action( 'gmw_lf_post_days_hours_section_end', $this ); ?>

		</div>
		<?php
		do_action( 'gmw_post_location_form_after_panels', $this );
	}
}
