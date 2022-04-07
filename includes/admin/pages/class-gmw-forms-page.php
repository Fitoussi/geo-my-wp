<?php
/**
 * GMW forms page.
 *
 * @package gmw-my-wp.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Forms class.
 *
 * GEO my WP forms page.
 */
class GMW_Forms_Page {

	/**
	 * __construct function.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function __construct() {

		if ( empty( $_GET['page'] ) || 'gmw-forms' !== $_GET['page'] ) { // WPCS: CSRF ok.
			return;
		}

		add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
		add_action( 'gmw_create_new_form', array( $this, 'create_new_form' ) );
		add_action( 'gmw_duplicate_form', array( $this, 'duplicate_form' ) );
		add_action( 'gmw_delete_form', array( $this, 'delete_form' ) );
	}

	/**
	 * GMW Function - admin notices.
	 *
	 * @param  [type] $messages [description].
	 *
	 * @return [type]           [description]
	 */
	public function notices_messages( $messages ) {

		$messages['form_created']        = __( 'Form successfully created.', 'geo-my-wp' );
		$messages['form_not_created']    = __( 'There was an error while trying to create the new form.', 'geo-my-wp' );
		$messages['form_duplicated']     = __( 'Form successfully duplicated.', 'geo-my-wp' );
		$messages['form_not_duplicated'] = __( 'There was an error while trying to duplicate the form.', 'geo-my-wp' );
		$messages['form_deleted']        = __( 'Form successfully deleted.', 'geo-my-wp' );
		$messages['form_not_deleted']    = __( 'There was an error while trying to delete the form.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Create new form.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function create_new_form() {

		// verfiy form data.
		if ( empty( $_GET['addon'] ) || empty( $_GET['slug'] ) ) { // WPCS: CSRF ok.

			wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_created&gmw_notice_status=error' ) );

			exit;
		}

		$new_data = array();

		// get form values.
		$new_form['slug']        = sanitize_text_field( wp_unslash( $_GET['slug'] ) ); // WPCS: CSRF ok.
		$new_form['addon']       = sanitize_text_field( wp_unslash( $_GET['addon'] ) ); // WPCS: CSRF ok.
		$new_form['component']   = ! empty( $_GET['component'] ) ? sanitize_text_field( wp_unslash( $_GET['component'] ) ) : ''; // WPCS: CSRF ok.
		$new_form['object_type'] = ! empty( $_GET['object_type'] ) ? sanitize_text_field( wp_unslash( $_GET['object_type'] ) ) : ''; // WPCS: CSRF ok.
		$new_form['name']        = ! empty( $_GET['name'] ) ? str_replace( '+', ' ', sanitize_text_field( wp_unslash( $_GET['name'] ) ) ) : ''; // WPCS: CSRF ok.
		$new_form['prefix']      = ! empty( $_GET['prefix'] ) ? sanitize_text_field( wp_unslash( $_GET['prefix'] ) ) : ''; // WPCS: CSRF ok.

		global $wpdb;

		// create new form in database.
		$wpdb->insert(
			$wpdb->prefix . 'gmw_forms',
			array(
				'slug'        => $new_form['slug'],
				'addon'       => $new_form['addon'],
				'component'   => $new_form['component'],
				'object_type' => $new_form['object_type'],
				'addon'       => $new_form['addon'],
				'name'        => $new_form['name'],
				'prefix'      => $new_form['prefix'],
				'title'       => '',
				'data'        => '',
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		); // WPCS: db call ok, cache ok.

		// get the ID of the new form.
		$new_form_id = $wpdb->insert_id;

		// make sure a form was created.
		if ( empty( $new_form_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_created&gmw_notice_status=error' ) );
			exit;
		}

		$new_form['ID'] = $new_form_id;

		// Update new form with the default values and title.
		$wpdb->update(
			$wpdb->prefix . 'gmw_forms',
			array(
				'title' => 'form_id_' . $new_form_id,
				'data'  => maybe_serialize( GMW_Forms_Helper::default_settings( $new_form ) ), // Generate default values.
			),
			array( 'ID' => $new_form_id ),
			array(
				'%s',
				'%s',
			),
			array( '%d' )
		); // WPCS: db call ok, cache ok.

		// update forms in cache.
		GMW_Forms_Helper::update_forms_cache();

		// reload the page to prevent resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_action=edit_form&form_id=' . absint( $new_form_id ) . '&slug=' . esc_attr( $new_form['slug'] ) . '&prefix=' . esc_attr( $new_form['prefix'] ) ) );

		exit;
	}

	/**
	 * Duplicate form.
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function duplicate_form() {

		// verify the form ID.
		if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) { // WPCS: CSRF ok.

			wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_duplicated&gmw_notice_status=error' ) );

			exit;
		}

		global $wpdb;

		// get form data.
		$form = $wpdb->get_row(
			$wpdb->prepare(
				"
		        SELECT * FROM {$wpdb->prefix}gmw_forms
		        WHERE ID = %d",
				absint( $_GET['form_id'] )
			)
		); // WPCS: db call ok, cache ok, CSRF ok.

		if ( empty( $form ) ) {
			wp_die( esc_html__( 'An error occurred while trying to retrieve the form.', 'geo-my-wp' ) );
		}

		// create new form in database.
		$new_form = $wpdb->insert(
			$wpdb->prefix . 'gmw_forms',
			array(
				'slug'        => $form->slug,
				'addon'       => $form->addon,
				'component'   => $form->component,
				'object_type' => $form->object_type,
				'name'        => $form->name,
				'title'       => $form->title . ' copy',
				'prefix'      => $form->prefix,
				'data'        => $form->data,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		); // WPCS: db call ok, cache ok.

		// Update forms in cache.
		GMW_Forms_Helper::update_forms_cache();

		// Reload the page to prevent resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_duplicated&gmw_notice_status=updated' ) );

		exit;
	}

	/**
	 * Delete form.
	 */
	public function delete_form() {

		// Abort if form ID doesn't exists.
		if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) { // WPCS: CSRF ok.
			wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_deleted&gmw_notice_status=error' ) );
			exit;
		}

		GMW_Forms_Helper::delete_form( absint( $_GET['form_id'] ) ); // WPCS: CSRF ok.

		// Reload the page to prevent resubmission.
		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_deleted&gmw_notice_status=updated' ) );

		exit;
	}

	/**
	 * You can add your own button using the filter below. To create a button you will need to pass an array with the following args.
	 *
	 * Name - the name/slug for the button ( ex. posts or post_types ).
	 * addon - the addon's slug the button belongs to
	 * title - the title/lable for the button ( ex. Posts locator )
	 * prefix - a prefix for your button ( ex. for post_type a good prefix would be "pt" )
	 * priority - the prority the button will show in the dropdown
	 *
	 * example :
	 * $buttons = array(
	 *      'slug'       => 'posts',
	 *      'addon'      => 'posts',
	 *      'name'       => __( 'Post Types ','geo-my-wp' ),
	 *      'prefix'     => pt,
	 *      'priority'   => 1
	 *  );
	 */
	public static function new_form_buttons() {

		$buttons = array();
		$buttons = apply_filters( 'gmw_admin_new_form_button', $buttons );

		// order buttons by priority.
		usort( $buttons, 'gmw_sort_by_priority' );

		$output = '<select class="gmw-admin-select-enhanced" onchange="window.location.href = jQuery(this).val();">';

		if ( empty( $buttons ) ) {

			$output .= '<option value="">' . __( 'Form buttons are not available', 'geo-my-wp' ) . '</option>';

		} else {

			$output .= '<option value="">' . __( 'Select form type', 'geo-my-wp' ) . '</option>';

			// Generate buttons.
			foreach ( $buttons as $button ) {

				if ( in_array( $button['slug'], array( 'posts_locator', 'members_locator', 'bp_groups_locator', 'users_locator' ), true ) ) {
					$output .= '<option disabled>----- ' . esc_html( $button['name'] ) . 's -----</option>';
				}

				// support older version of the extensions.
				if ( empty( $button['slug'] ) && ( ! empty( $button['title'] ) && ! empty( $button['name'] ) ) ) {

					$button['slug'] = $button['name'];
					$button['name'] = $button['title'];
				}

				$form_url = 'admin.php?page=gmw-forms&gmw_action=create_new_form&name=' . str_replace( ' ', '+', $button['name'] ) . '&addon=' . $button['addon'] . '&component=' . $button['component'] . '&object_type=' . $button['object_type'] . '&prefix=' . $button['prefix'] . '&slug=' . $button['slug'];

				$output .= '<option value="' . esc_url( $form_url ) . '">' . esc_html( $button['name'] ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Output list of forms.
	 */
	public function output() {

		gmw_admin_pages_header();
		?>		
		<div id="gmw-forms-page" class="wrap gmw-admin-page-content gmw-admin-page gmw-admin-page-wrapper gmw-admin-page-no-nav">

			<nav class="gmw-admin-page-navigation"></nav>

			<div class="gmw-admin-page-panels-wrapper">

				<h1 style="display:none"></h1>

				<div class="gmw-new-form-wrapper">
					<h3><?php esc_html_e( 'New Form: ', 'geo-my-wp' ); ?></h3> <?php echo self::new_form_buttons(); // WPCS: XSS ok. ?>
				</div>

				<form id="gmw_forms_admin" class="gmw-admin-page-conten" enctype="multipart/form-data" method="post">

					<input type="hidden" name="gmw_page" id="gmw_page" value="gmw-forms">

					<?php wp_nonce_field( 'gmw_forms_page', 'gmw_forms_page' ); ?>

					<div class="clear"></div>

					<?php
						$forms_table = new GMW_Forms_Table();
						$forms_table->prepare_items();
						$forms_table->display();
					?>

				</form>
			</div>

			<!-- Side bar -->
			<div class="gmw-admin-page-sidebar">
				<?php gmw_admin_sidebar_content(); ?>
			</div>    
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				jQuery( 'select' ).addClass( 'gmw-smartbox-not' );
			});
		</script>
		<?php
	}
}
