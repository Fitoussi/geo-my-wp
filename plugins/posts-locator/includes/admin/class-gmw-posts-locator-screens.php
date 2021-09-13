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

		// load page only on new and edit post pages.
		if ( empty( $pagenow ) || ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'edit.php' ), true ) ) {
			return;
		}

		// apply features only for the chosen post types.
		foreach ( gmw_get_option( 'post_types_settings', 'post_types', array() ) as $post_type ) {

			// no need to show in resumes or job_listings post types.
			/*if ( ( 'job_listing' === $post_type || 'resume' === $post_type ) && apply_filters( 'gmw_disable_location_form_in_job_listing', true ) ) {
				continue;
			}*/

			if ( 'edit.php' === $pagenow ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_address_column' ) );
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
	 * Add "address" column to manager posts page
	 *
	 * @param array $columns columns.
	 *
	 * @return columns
	 */
	public function add_address_column( $columns ) {

		$new_columns = array();
		$no_col      = true;

		// append "address" column depends on the table arrangemnt.
		foreach ( $columns as $key => $column ) {

			if ( array_key_exists( 'comments', $columns ) && 'comments' === $key ) {

				$new_columns['gmw_address'] = '<i class="gmw-icon-location"></i>' . __( 'Location', 'geo-my-wp' );
				$no_col                     = false;

			} elseif ( ! array_key_exists( 'comments', $columns ) && array_key_exists( 'date', $columns ) && 'date' === $key ) {

				$new_columns['gmw_address'] = '<i class="gmw-icon-location"></i>' . __( 'Location', 'geo-my-wp' ) . '</i>';
				$no_col                     = false;
			}
			$new_columns[ $key ] = $column;
		}

		if ( $no_col ) {
			$new_columns['gmw_address'] = __( 'Location', 'geo-my-wp' );
		}

		return $new_columns;
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
		if ( 'gmw_address' !== $column ) {
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

		$address = esc_attr( $address );

		// create link to address.
		$address = ( true === $address_ok ) ? '<a href="http://maps.google.com/?q=' . $address . '" target="_blank" title="location">' . $address . '</a>' : '<span style="color:red">' . $address . '</span>';
		echo '<i class="gmw-icon-ok-circled" style="color: green;margin-right: 1px;font-size: 12px;" style="color:green"></i>' . $address; // WPSC: XSS ok.

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

		/**
		 * This is a temporary solution for an issue with the $post object.
		 *
		 * In some occations the $post object might belong to a different post
		 *
		 * of a different post type than the post that is currently being edited.
		 *
		 * I am not certain if this issue cause by the theme that generates the different post types
		 *
		 * or by GEO my WP.
		 */
		if ( ! empty( $_GET['post'] ) && absint( $_GET['post'] ) && $_GET['post'] !== $post->ID ) {
			$post = get_post( $_GET['post'] ); // WPCS: CSRF ok, sanitization ok.
		}

		// form args.
		return apply_filters(
			'gmw_edit_post_location_form_args',
			array(
				'object_id'          => $post->ID,
				'form_template'      => 'location-form-tabs-left',
				'submit_enabled'     => 0,
				'auto_confirm'       => 0,
				'stand_alone'        => 0,
				'ajax_enabled'       => 0,
				'confirm_required'   => 0,
				'form_element'       => '.wrap form',
				'map_zoom_level'     => gmw_get_option( 'post_types_settings', 'edit_post_page_map_zoom_level', 7 ),
				'map_type'           => gmw_get_option( 'post_types_settings', 'edit_post_page_map_type', 'ROADMAP' ),
				'map_lat'            => gmw_get_option( 'post_types_settings', 'edit_post_page_map_latitude', '40.711544' ),
				'map_lng'            => gmw_get_option( 'post_types_settings', 'edit_post_page_map_longitude', '-74.013486' ),
				'location_mandatory' => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
				'location_required'  => gmw_get_option( 'post_types_settings', 'location_mandatory', 0 ),
			),
			$post
		);
	}

	/**
	 * Generate the location form
	 *
	 * @param  object $post the post being displayed.
	 */
	public function display_meta_box( $post ) {

		// expand button.
		echo '<i type="button" id="gmw-location-section-resize" class="gmw-icon-resize-full" title="Expand full screen" style="display: block" onclick="jQuery( this ).closest( \'#gmw-location-meta-box\' ).find( \'.inside\' ).toggleClass( \'fullscreen\' );"></i>';

		// form args.
		$form_args = self::get_location_form_args( $post );

		do_action( 'gmw_edit_post_page_before_location_form', $post );

		// generate the form.
		gmw_post_location_form( $form_args );

		do_action( 'gmw_edit_post_page_after_location_form', $post );
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
		if ( empty( $location['ID'] ) && ! empty( $_POST['post_ID'] ) ) { // WPCS CSRF ok.

			$location_id = gmw_get_location_id( 'post', absint( $_POST['post_ID'] ) ); // WPCS: CSRF ok.

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
				GMW.add_filter( 'gmw_location_form_prevent_form_submission', function() {
					return false;
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
