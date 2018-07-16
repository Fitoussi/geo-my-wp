<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Post_Types_Screens class
 *
 * Class responsible for displaying GEO my WP Post Types related featured.
 *
 * @since 3.0
 *
 */
class GMW_Posts_Locator_Screens {

	/**
	 * Constructor
	 */
	public function __construct() {

		global $pagenow;

		// load page only on new and edit post pages.
		if ( empty( $pagenow ) || ! in_array( $pagenow, array( 'post-new.php', 'post.php', 'edit.php' ) ) ) {
			return;
		}

		// apply features only for the chosen post types
		foreach ( gmw_get_option( 'post_types_settings', 'post_types', array() ) as $post_type ) {

			// no need to show in resumes or job_listings post types
			if ( 'job_listing' == $post_type || 'resume' == $post_type ) {
				continue;
			}

			if ( 'edit.php' == $pagenow ) {
				add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_address_column' ) );
				add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'address_column_content' ), 10, 2 );
			}

			if ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) {

				add_action( "add_meta_boxes_{$post_type}", array( $this, 'add_meta_box' ), 10 );

				// filters fires when location updated.
				add_filter( 'gmw_lf_post_location_args_before_location_updated', array( $this, 'get_post_title' ) );
				add_filter( 'gmw_lf_post_location_meta_before_location_updated', array( $this, 'verify_location_meta' ), 10, 3 );

				// these action fire functions responsible for a fix where posts status wont change from pending to publish.
				//add_action( 'gmw_lf_before_post_location_updated', array( $this, 'post_status_publish_fix' ) );
				$this->post_status_publish_fix();
			}
		}
	}

	/**
	 * Add "address" column to manager posts page
	 *
	 * @param  array $columns columns
	 *
	 * @return columns
	 */
	public function add_address_column( $columns ) {

		$new_columns = array();
		$no_col      = true;

		// append "address" column depends on the table arrangemnt
		foreach ( $columns as $key => $column ) {

			if ( array_key_exists( 'comments', $columns ) && 'comments' == $key ) {

				$new_columns['gmw_address'] = '<i class="gmw-icon-location"></i>' . __( 'Location', 'geo-my-wp' );
				$no_col                     = false;

			} elseif ( ! array_key_exists( 'comments', $columns ) && array_key_exists( 'date', $columns ) && 'date' == $key ) {

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
	 * @param  array $column  existing columns
	 * @param  int   $post_id psot ID
	 *
	 * @return void
	 *
	 */
	public function address_column_content( $column, $post_id ) {

		// abort if not "Address column"
		if ( 'gmw_address' != $column ) {
			return;
		}

		global $wpdb;

		$address_ok = false;

		/*$location = $wpdb->get_row( $wpdb->prepare("
			SELECT formatted_address, address FROM {$wpdb->prefix}gmw_locations
			WHERE `object_type` = 'post'
			AND   `object_id`   = %d
			", array( $post_id )
		) ); */

		$location = gmw_get_location( 'post', $post_id );

		if ( empty( $location ) ) {
			echo '<i class="gmw-icon-cancel-circled" style="color:red;margin-right:1px;font-size: 12px"></i>' . __( 'No location found', 'GMW' );
			return;
		}

		// first look for formatted address
		if ( ! empty( $location->formatted_address ) ) {

			$address_ok = true;
			$address    = $location->formatted_address;

			// otherwise for entered address
		} elseif ( ! empty( $location->address ) ) {

			$address_ok = true;
			$address    = $location->address;

			// if no address was found show an error message
		} else {
			$address = __( 'Location found but the address is missing', 'GMW' );
		}

		$address = esc_attr( $address );

		// create link to address
		$address = ( true == $address_ok ) ? '<a href="http://maps.google.com/?q=' . $address . '" target="_blank" title="location">' . $address . '</a>' : '<span style="color:red">' . $address . '</span>';
		echo '<i class="gmw-icon-ok-circled" style="color: green;margin-right: 1px;font-size: 12px;" style="color:green"></i>' . $address;

	}

	/**
	 * add meta boxes
	 */
	public function add_meta_box( $post ) {

		add_meta_box(
			'gmw-location-meta-box', apply_filters( 'gmw_pt_mb_title', __( 'Location', 'geo-my-wp' ) ), array( $this, 'display_meta_box' ), $post->post_type, 'advanced', 'high'
		);

		// add hidden field that responsible for a fix where posts status wont change from pending to publish.
		add_action( 'admin_footer', array( $this, 'add_hidden_status_field' ) );
	}

	/**
	 * Generate the location form
	 *
	 * @param  object $post the post being displayed
	 *
	 * @return [type]       [description]
	 */
	function display_meta_box( $post ) {

		// expand button
		echo '<i type="button" id="gmw-location-section-resize" class="gmw-icon-resize-full" title="Expand full screen" style="display: block" onclick="jQuery( this ).closest( \'#gmw-location-meta-box\' ).find( \'.inside\' ).toggleClass( \'fullscreen\' );"></i>';

		// form args
		$form_args = apply_filters(
			'gmw_edit_post_location_form_args', array(
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
			)
		);

		do_action( 'gmw_edit_post_page_before_location_form', $post );

		// generate the form
		gmw_post_location_form( $form_args );

		do_action( 'gmw_edit_post_page_after_location_form', $post );
	}

	/**
	 * Get the post title to save as location title before location saved
	 *
	 * @param  [type] $location [description]
	 * @return [type]           [description]
	 */
	public function get_post_title( $location ) {

		$title = get_the_title( $location['object_id'] );

		if ( ! empty( $title ) ) {
			$location['title'] = $title;
		}

		return $location;
	}

	/**
	 * Verify opening hours location meta before saving
	 *
	 * @param  [type] $location_meta [description]
	 * @param  [type] $location      [description]
	 * @param  [type] $form_values   [description]
	 * @return [type]                [description]
	 *
	 */
	public function verify_location_meta( $location_meta, $location, $form_values ) {

		if ( ! empty( $location_meta['days_hours'] ) ) {

			$check = 0;

			// loop through and check if values exist in days/hours
			foreach ( $location_meta['days_hours'] as $value ) {

				foreach ( $value as $dh ) {
					$dh = trim( $dh );

					// stop the loop in the first value we find. No need to continue.
					if ( ! empty( $dh ) ) {

						return $location_meta;

						break;
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
	 * Add hidden field to hold the value 1 when pressing the "Publish" button.
	 *
	 * This way in the next function we can chnage the post status manually to publish.
	 */
	public function add_hidden_status_field() {
		?>
		<script type="text/javascript">

			jQuery( 'document' ).ready( function( $ ) {

				if ( $( 'form[name="post"]' ).length ) {

					$( 'form[name="post"]' ).append( '<input type="hidden" value="" name="gmw_post_published" id="gmw_post_published" />' );

					$( 'form[name="post"]' ).find( 'input#publish[type="submit"][value="Publish"]' ).on( 'click', function() {
						$( '#gmw_post_published' ).val( '1' );
					});
				}
			});

		</script>
		<?php
	}

	/**
	 * Change the post status manually to publish when click on the publish button.
	 *
	 * For some reason, on some browsers the post status wont change to publish from pending review.
	 *
	 * Looks like this happens becaue the script of the Location form disables
	 *
	 * the original form submission ( event.preventDefault ) to allows to verify the location. Then
	 *
	 * the script will submit the form agaain.
	 *
	 * @return [type] [description]
	 */
	public function post_status_publish_fix() {

		if ( ! empty( $_POST['post_status'] ) && 'publish' != $_POST['post_status'] && ! empty( $_POST['gmw_post_published'] ) ) {
			$_POST['post_status'] = 'publish';
		}
	}
}
new GMW_Posts_Locator_Screens;
