<?php
/**
 * Posts Locator Screen functions.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 *
 * GMW_Post_Types_Screens class.
 *
 * - Generate the location form in the Edit Post page.
 * - Show location details in "All posts" page.
 *
 * @since 3.0
 */
class GMW_Posts_Locator_Screens {

	/**
	 * Constructor
	 */
	public function __construct() {

		global $pagenow;

		add_action( 'ithemes_sync_duplicate_post_addon_default_post_meta', array( $this, 'duplicate_post_locations' ), 80, 2 );

		// load page only on new and edit post pages.
		if ( empty( $pagenow ) || ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'edit.php' ), true ) ) {
			return;
		}

		// apply features only for the chosen post types.
		foreach ( gmw_get_option( 'post_types_settings', 'post_types', array() ) as $post_type ) {

			// Prevent location update on page load. Now location should be saved inside the form box.
			remove_action( 'gmw_update_lf_location', 'gmw_update_lf_location' );

			// no need to show in resumes or job_listings post types.
			/*if ( ( 'job_listing' === $post_type || 'resume' === $post_type ) && apply_filters( 'gmw_disable_location_form_in_job_listing', true ) ) {
				continue;
			}*/

			if ( 'edit.php' === $pagenow ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_address_column' ), 99 );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'address_column_content' ), 10, 2 );
			}

			if ( in_array( $pagenow, array( 'post-new.php', 'post.php' ), true ) ) {

				add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_location_form_compatibility' ), 5 );
				add_action( 'add_meta_boxes_' . $post_type, array( $this, 'add_meta_box' ), 10 );

				// filters fires when location updated.
				add_filter( 'gmw_lf_post_location_args_before_location_updated', array( $this, 'pre_location_update_tasks' ) );
				add_filter( 'gmw_lf_post_location_meta_before_location_updated', array( $this, 'verify_location_meta' ), 10, 3 );

				/**
				 * NOTE: this function is currently disabled. It was originally added to be part of a workaround to an issue
				 *
				 * That was generated when the Location form was preventing the "Edit Post" page from its original submission.
				 *
				 * The method disable_prevent_form_submission() is now preventing the Location form from preventing the form submission of the "Edit Post" page.
				*/
				// these action fire functions responsible for a fix where posts status wont change from pending to publish.
				// add_action( 'gmw_lf_before_post_location_updated', array( $this, 'post_status_publish_fix' ) );
				// $this->post_status_publish_fix();
			}
		}
	}

	/**
	 * Duplicate post locations when duplicating a post.
	 *
	 * @since 4.0
	 *
	 * @param  [type] $post               [description].
	 *
	 * @param  [type] $duplicated_post_id [description].
	 */
	public function duplicate_post_locations( $post, $duplicated_post_id ) {

		$locations = gmw_get_post_locations( $duplicated_post_id );

		foreach ( $locations as $location ) {

			$org_location_id     = $location->ID;
			$location->ID        = 0;
			$location->object_id = $post->ID;

			// Insert location for the new post.
			$new_location_id = gmw_insert_location( ( array ) $location );

			// Verify that location was created.
			if ( ! empty( $new_location_id ) ) {

				// Get meta fields from the original location.
				$mfields = array( 'phone', 'fax', 'email', 'website', 'days_hours' );
				$lmeta   = gmw_get_location_meta( $org_location_id, $mfields );

				// Inset meta field to duplicated location.
				gmw_update_location_metas( $new_location_id, $lmeta );
			}
		}
	}

	/**
	 * Add "address" column to manager posts page
	 *
	 * @param array $columns columns.
	 *
	 * @return array columns
	 */
	public function add_address_column( $columns ) {

		if ( array_key_exists( 'comments', $columns ) ) {

			$offset = array_search( 'comments', array_keys( $columns ), true );

		} elseif ( array_key_exists( 'date', $columns ) ) {

			$offset = array_search( 'date', array_keys( $columns ), true );

		} else {

			$offset = array_search( array_key_last( $columns ), array_keys( $columns ), true );
		}

		return array_merge(
			array_slice( $columns, 0, $offset ),
			array( 'gmw_location' => '<i class="gmw-icon-location"></i>' . esc_html__( 'Location', 'geo-my-wp' ) ),
			array_slice( $columns, $offset, null )
		);
	}

	/**
	 * Add content to custom column
	 *
	 * @param  array $column  existing columns.
	 * @param  int   $post_id psot ID.
	 *
	 * @return void
	 */
	public function address_column_content( $column, $post_id ) {

		// abort if not "Address column".
		if ( 'gmw_location' !== $column ) {
			return;
		}

		global $wpdb;

		/**
		$location = $wpdb->get_row( $wpdb->prepare("
			SELECT formatted_address, address FROM {$wpdb->prefix}gmw_locations
			WHERE `object_type` = 'post'
			AND   `object_id`   = %d
			", array( $post_id )
		) );.*/

		$address_ok = false;

		$location = gmw_get_post_location( $post_id );

		if ( empty( $location ) ) {

			echo '<i class="gmw-icon-cancel-circled" style="color:red;margin-right:1px;font-size: 12px"></i>' . esc_html__( 'No location found', 'geo-my-wp' );

			return;
		}

		// first look for formatted address.
		if ( ! empty( $location->formatted_address ) ) {

			$address_ok = true;
			$address    = $location->formatted_address;

			// otherwise for entered address.
		} elseif ( ! empty( $location->address ) ) {

			$address_ok = true;
			$address    = $location->address;

			// if no address was found show an error message.
		} else {
			$address = __( 'Location found but the address is missing', 'GMW' );
		}

		// create link to address.
		$address = $address_ok ? '<a href="https://maps.google.com/?q=' . esc_attr( $address ) . '" target="_blank" title="location">' . esc_attr( $address ) . '</a>' : '<span style="color:red">' . esc_attr( $address ) . '</span>';

		echo '<i class="gmw-icon-ok-circled" style="color: green;margin-right: 1px;font-size: 12px;" style="color:green"></i>' . $address; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, XSS ok.
	}

	/**
	 * Add location meta box
	 *
	 * @param object $post the post object.
	 */
	public function add_meta_box( $post ) {

		$args = apply_filters(
			'gmw_pt_location_meta_box_args',
			array(
				'id'       => 'gmw-location-meta-box',
				'label'    => apply_filters( 'gmw_pt_mb_title', __( 'Location', 'geo-my-wp' ) ),
				'function' => array( $this, 'display_meta_box' ),
				'page'     => $post->post_type,
				'context'  => 'advanced',
				'priority' => 'high',
			),
			$post,
			$this
		);

		add_meta_box(
			$args['id'],
			$args['label'],
			$args['function'],
			$args['page'],
			$args['context'],
			$args['priority'],
			array(
				'__block_editor_compatible_meta_box' => true,
			)
		);

		do_action( 'gmw_pt_admin_add_location_meta_box', $post, $this );

		/**
		 * NOTE: this function is currently disabled. It was originally added to be part of a workaround to an issue
		 *
		 * That was generated when the Location form was preventing the "Edit Post" page from its original submission.
		 *
		 * The method disable_prevent_form_submission() is now preventing the Location form from preventing the form submission of the "Edit Post" page.
		*/
		// add hidden field that responsible for a fix where posts status wont change from pending to publish.
		// add_action( 'admin_footer', array( $this, 'add_hidden_status_field' ) );

		// Prevent the Location form from preventing the "Edit Post" page submission when clicking its Publish/Update button.
		add_action( 'admin_footer', array( $this, 'disable_prevent_form_submission' ) );
	}

	/**
	 * Make location form compatible with block editor page.
	 *
	 * Use this function to execute the filter that modified the location form args.
	 */
	public function block_editor_location_form_compatibility() {
		add_filter( 'gmw_edit_post_location_form_args', array( $this, 'modify_location_form_args' ), 5 );
	}

	/**
	 * Modify the location form args.
	 *
	 * When block editor is enable, we need to modify the form_element argument of the location form.
	 *
	 * @param  array $args location form args.
	 *
	 * @since 3.2
	 *
	 * @return [type]       [description]
	 */
	public function modify_location_form_args( $args ) {

		$args['form_element'] = '.edit-post-layout__metaboxes form';

		return $args;
	}

	/**
	 * Get location form args.
	 *
	 * @param  object $post the post object.
	 *
	 * @return [type]       [description]
	 */
	public static function get_location_form_args( $post ) {
		return gmw_get_post_location_form_args( $post );
	}

	/**
	 * Generate the location form
	 *
	 * @param  object $post the post being displayed.
	 */
	public function display_meta_box( $post ) {

		if ( apply_filters( 'gmw_edit_post_location_form_modal_disabled', true ) ) {

			// form args.
			$form_args = self::get_location_form_args( $post );

			do_action( 'gmw_edit_post_page_before_location_form', $post );

			// generate the form.
			gmw_post_location_form( $form_args );

			do_action( 'gmw_edit_post_page_after_location_form', $post );

			return;
		}

		$address    = gmw_get_post_location( $post->ID );
		$no_address = __( 'No location found.', 'geo-my-wp' );
		$address    = ! empty( $address->address ) ? $address->address : $no_address;

		wp_enqueue_script( 'gmw-admin' );
		?>
		<style type="text/css">
			#gmw-location-form-modal-element .gmw-popup-element-inner {
				padding: 0;
				max-height: initial;
			}

			#gmw-location-form-modal-element #gmw-location-form-wrapper {
				border: 0;
			}

			#gmw-modal-location-form-toggle {
				color: white;
				background: #1e90ff;
				border: 1px solid #1e90ff;
			}

			#gmw-modal-location-form-toggle:hover {
				background: #1d80e0;
			}

			#gmw-location-section-wrapper {
				padding: 1em;
			}

			#gmw-location {
				padding: 0;
				border: 0;
			}

			#gmw-location:focus {
				outline:none !important;
				outline-width: 0 !important;
				box-shadow: none;
				-moz-box-shadow: none;
				-webkit-box-shadow: none;
			}
		</style>
		<script type="text/javascript">

			jQuery( document ).ready( function( $) {

				var noLocationText = '<?php echo esc_attr( $no_address ); ?>';

				// Fill selected address in the address field when location updated.
				GMW.add_action( 'gmw_lf_location_saved', function( response, form_values, form ) {

					$( '#gmw-location' ).val( jQuery( '#gmw_lf_address' ).val() );

					setTimeout( function() {
						jQuery( '#gmw-location-form-modal-element' ).fadeOut( 'fast' );
					}, 1500 );
				});

				// Remove address from field when location deleted.
				GMW.add_action( 'gmw_lf_location_deleted', function( response, formValues ) {
					$( '#gmw-location' ).val( noLocationText );
				});
			});
		</script>

		<div id="gmw-location-section-wrapper">
			<span>
				<input style="background: none;width:100%" type="text" readonly="readonly" id="gmw-location" value="<?php echo esc_attr( $address ); ?>" class="regular-text" />
			</span>
			<div style="margin-top:10px">
				<?php echo '<a id="gmw-modal-location-form-toggle" href="#" class="gmw-popup-element-toggle button" data-element="#gmw-location-form-modal-element">' . esc_html__( 'Edit Location', 'geo-my-wp' ) . '</a>'; ?>
			</div>
		</div>

		<div id="gmw-location-form-modal-element" class="gmw-popup-element-wrapper">

			<div class="gmw-popup-element-inner">

				<span class="gmw-popup-element-close-button gmw-icon-cancel-light"></span>

				<?php

				// form args.
				$form_args = self::get_location_form_args( $post );

				do_action( 'gmw_edit_post_page_before_location_form', $post );

				// generate the form.
				gmw_post_location_form( $form_args );

				do_action( 'gmw_edit_post_page_after_location_form', $post );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Run some tasks before location is updated.
	 *
	 * @param  object $location the location object.
	 *
	 * @return [type]           [description]
	 */
	public function pre_location_update_tasks( $location ) {

		$title = get_the_title( $location['object_id'] );

		// Get post title if missing.
		if ( ! empty( $title ) ) {
			$location['title'] = $title;
		}

		// Try and get the location ID if does not already exist.
		// This fix takes care of an issue with the Gutenberg editor which does not reload the page when the post is first created.
		// Because of that, the location ID does not exist in the hidden fields of the location form
		// and the plugin create a new location each time the post is updated after it was initialy creation and before the page was refreshed at least once.
		if ( empty( $location['ID'] ) && ! empty( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, CSRF ok.

			$location_id = gmw_get_location_id( 'post', absint( $_POST['post_ID'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, CSRF ok.

			if ( ! empty( $location_id ) ) {
				$location['ID'] = $location_id;
			}
		}

		return $location;
	}

	/**
	 * Verify opening hours location meta before saving
	 *
	 * @param  array  $location_meta location meta.
	 *
	 * @param  object $location      the location object.
	 *
	 * @param  array  $form_values   the form values.
	 *
	 * @return [type]                [description]
	 */
	public function verify_location_meta( $location_meta, $location, $form_values ) {

		if ( ! empty( $location_meta['days_hours'] ) ) {

			$check = 0;

			// loop through and check if values exist in days/hours.
			foreach ( $location_meta['days_hours'] as $value ) {

				foreach ( $value as $dh ) {

					$dh = trim( $dh );

					// stop the loop in the first value we find. No need to continue.
					if ( ! empty( $dh ) ) {
						return $location_meta;
					}
				}
			}

			// if loop completed mean that no value was found in the array.
			// we need to set days_hours value to nothing to make sure it is not
			// being saved in database.
			$location_meta['days_hours'] = '';
		}

		return $location_meta;
	}

	/**
	 * Prevent the Location form from preventing the original form submission of the Edit Post page.
	 *
	 * Preventing the form submission on that page causes conflicts on Chrome browser, and it seems that is not needed regardless.
	 *
	 * @since 3.6.5
	 */
	public function disable_prevent_form_submission() {
		?>
		<script type="text/javascript">

			jQuery( 'document' ).ready( function( $ ) {
				GMW.add_filter( 'gmw_location_form_force_proceed_form_submission', function() {
					return true;
				});
			});
		</script>
		<?php
	}

	/**
	 * Add hidden field to hold the value 1 when pressing the "Publish" button.
	 *
	 * This way in the next function we can chnage the post status manually to publish.
	 *
	 * NOTE: this function is currently disabled. It was originally added to be part of a workaround to an issue
	 *
	 * that was generated when the Location form was preventing the "Edit Post" page from its original submission.
	 *
	 * The method disable_prevent_form_submission() is now preventing the Location form from preventing the form submission of the "Edit Post" page.
	 */
	/*public function add_hidden_status_field() {

		$pods_enabled = class_exists( 'PodsAdmin' ) ? 'true' : 'false';
		?>
		<script type="text/javascript">

			jQuery( 'document' ).ready( function( $ ) {

				if ( '<?php echo $pods_enabled; ?>' == 'true' ) {

					GMW.add_filter( 'gmw_location_form_prevent_form_submission', function() {
						return false;
					});
				}

				if ( $( 'form[name="post"]' ).length ) {

					$( 'form[name="post"]' ).append( '<input type="hidden" value="" name="gmw_post_published" id="gmw_post_published" />' );

					$( 'form[name="post"]' ).find( 'input#publish[name="publish"][type="submit"][id="publish"], input#publish[name="save"][type="submit"][id="publish"]' ).on( 'click', function() {
						$( '#gmw_post_published' ).val( '1' );
					});
				}
			});

		</script>
		<?php
	}*/

	/**
	 * Change the post status manually to publish when click on the publish button.
	 *
	 * For some reason, on some browsers the post status wont change to publish from pending review.
	 *
	 * Looks like this happens becaue the script of the Location form disables
	 *
	 * the original form submission ( event.preventDefault ) to allows to verify the location. Then
	 *
	 * the script will submit the form again.
	 *
	 * NOTE: this function is currently disabled. It was originally added to be part of a workaround to an issue
	 *
	 * that was generated when the Location form was preventing the "Edit Post" page from its original submission.
	 *
	 * The method disable_prevent_form_submission() is now preventing the Location form from preventing the form submission of the "Edit Post" page.
	 */
	/*
	public function post_status_publish_fix() {

		if ( ! empty( $_POST['post_status'] ) && 'publish' !== $_POST['post_status'] && ! empty( $_POST['gmw_post_published'] ) ) { // WPCS: CSRF ok.
			$_POST['post_status'] = 'publish';
		}
	}*/
}
new GMW_Posts_Locator_Screens();
