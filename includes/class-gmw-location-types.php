<?php
/**
 * GMW Location Types.
 *
 * This class is disabled by default. It can be enabled when needed.
 *
 * @author Eyal Fitoussi
 *
 * @since 3.6.4
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Location_Types class.
 *
 * @since 3.6.4
 *
 * @author Eyal Fitoussi
 */
class GMW_Location_Types {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		add_action( 'gmw_admin_menu_items', array( $this, 'menu_item' ) );
		add_action( 'admin_footer-post.php', array( $this, 'load_scripts' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'load_scripts' ) );
		add_action( 'init', array( $this, 'register_post_type' ), 45 );
		add_filter( 'manage_edit-gmw_location_type_columns', array( $this, 'edit_columns' ) );
		add_action( 'manage_gmw_location_type_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
		add_filter( 'enter_title_here', array( $this, 'modify_title_placeholder' ), 50, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'wp_insert_post_data', array( $this, 'update_meta' ), 10, 3 );
		add_filter( 'post_updated_messages', array( $this, 'update_notices' ) );
		add_action( 'wp_ajax_gmw_set_missing_location_type', array( $this, 'set_missing_location_type' ) );
	}

	/**
	 * Add submenu item.
	 *
	 * @param  array $menu_items menu items.
	 *
	 * @since 1.1
	 *
	 * @author Eyal Fitoussi
	 *
	 * @return array $menu_items menu items.
	 */
	public function menu_item( $menu_items ) {

		$menu_items[] = array(
			'parent_slug'       => 'gmw-extensions',
			'page_title'        => __( 'GEO my WP Location Types', 'geo-my-wp' ),
			'menu_title'        => __( 'Location Types', 'gmw-bp-xprofile-geolocation' ),
			'capability'        => 'manage_options',
			'menu_slug'         => 'edit.php?post_type=gmw_location_type',
			'callback_function' => '',
			'priority'          => 8,
		);

		return $menu_items;
	}

	/**
	 * Load some scripts and styles.
	 *
	 * @since 3.6.4
	 */
	public function load_scripts() {

		// If the post we're editing isn't a project_summary type, exit this function.
		if ( 'gmw_location_type' !== get_post_type() ) {
			return;
		}
		?>
		<style type="text/css">
			#minor-publishing {
				display: none;
			}
			#gmw_lt_xprofile_fields_meta_box .inside {
				padding:0;
			}

			.gmw-xprofile-fields-setting-wrapper.single-address-field-setting,
			.gmw-xprofile-fields-setting-wrapper.multiple-address-field-setting {
				margin:0;padding: 20px;
				display:none;background-color: #f7f7f7;
				border-top: 1px solid #ececec;
				border-bottom: 1px solid #ececec;
			}

			.gmw-xprofile-fields-setting-wrapper.usage-setting,
			.gmw-xprofile-fields-setting-wrapper.address-autocomplete-setting {
				margin: 0;
				padding: 20px;
			}
		</style>
		<script type="text/javascript">

			jQuery( document ).ready( function ($) {

				//Require post title when adding/editing Project Summaries
				$( 'body' ).on( 'submit.edit-post', '#post', function () {

					// If the title isn't set
					if ( $( "#title" ).val().replace( / /g, '' ).length === 0 ) {

					// Show the alert
					window.alert( 'A location type name is required.' );

					// Hide the spinner
					$( '#major-publishing-actions .spinner' ).hide();

					// The buttons get "disabled" added to them on submit. Remove that class.
					$( '#major-publishing-actions' ).find( ':button, :submit, a.submitdelete, #post-preview' ).removeClass( 'disabled' );

					// Focus on the title field.
					$( "#title" ).focus();

						return false;
					}
				});

				if ( $( '#gmw-xprofile-fields-usage-setting' ).val() == 'disabled' ) {
					$( '.gmw-xprofile-fields-setting-wrapper:not( .usage-setting )' ).slideUp();
				}

				if ( $( '#gmw-xprofile-fields-usage-setting' ).val() == 'single' ) {
					$( '.gmw-xprofile-fields-setting-wrapper:not( .usage-setting )' ).slideUp();
					$( '.gmw-xprofile-fields-setting-wrapper.address-autocomplete-setting' ).slideDown();
					$( '.gmw-xprofile-fields-setting-wrapper.single-address-field-setting' ).slideDown(); 
				}

				if ( $( '#gmw-xprofile-fields-usage-setting' ).val() == 'multiple' ) {
					$( '.gmw-xprofile-fields-setting-wrapper:not( .usage-setting )' ).slideUp();
					$( '.gmw-xprofile-fields-setting-wrapper.multiple-address-field-setting' ).slideDown(); 
				}

				$( '#gmw-xprofile-fields-usage-setting' ).change(function() {

					$( '.gmw-xprofile-fields-setting-wrapper:not( .usage-setting )' ).slideUp();

					if ( $( this ).val() == 'single' ) {

						$( '.gmw-xprofile-fields-setting-wrapper.single-address-field-setting' ).slideDown(); 
						$( '.gmw-xprofile-fields-setting-wrapper.address-autocomplete-setting' ).slideDown();

					} else if ( $( this ).val() == 'multiple' ) {

						$( '.gmw-xprofile-fields-setting-wrapper.multiple-address-field-setting' ).slideDown(); 
					}
				});

				if ( jQuery().select2 ) {
					jQuery( '.gmw-xprofile-fields-setting-wrapper select:not( .gmw-smartbox-not )' ).select2({
						theme: 'classic',
						width: '100%',
					});
				}

				// Update location without location types.
				jQuery( '#gmw-update-location-types-button' ).on( 'click', function(e) {

					e.preventDefault();

					var gmwAjaxUrl  = '<?php echo GMW()->ajax_url; ?>';
					var thisData    = jQuery( this ).data();
					var objectTypes = $( '.gmw-update-location-types-object-types:checked' ).map(function(){
						return $(this).val();
					});

					// Show error message if not object types were checked.
					if ( objectTypes.length == 0 ) {

						alert( 'You must check at least one checkbox.' );

						return false;
					}

					// Show spinner.
					jQuery( '#gmw-update-location-types-spinner' ).fadeIn();

					// Update location via ajax.
					jQuery.ajax( {
						type     : 'POST',
						url      : gmwAjaxUrl,
						dataType : 'json',
						data     : {
							action        : 'gmw_set_missing_location_type',
							object_types  : objectTypes.get().join(','),
							location_type : thisData.location_type,
							security      : thisData.nonce,
						},
						success : function( response ) {

							if ( response == 0 ) {
								jQuery( '#gmw-update-location-types-message' ).html( 'There were no locations to update.' ).css( 'color', '#444' ).fadeIn();
							} else {
								jQuery( '#gmw-update-location-types-message' ).html( response + ' locations updated.' ).css( 'color', 'green' ).fadeIn();
							}

							setTimeout( function() {
								jQuery( '#gmw-update-location-types-message, #gmw-update-location-types-spinner' ).fadeOut();
							}, 5000 );
						}

					//if inporter failed or aborted by user
					}).fail( function ( jqXHR, textStatus, error ) {

						if ( window.console && window.console.log ) {

							console.log( textStatus + ': ' + error );

							if ( jqXHR.responseText ) {
								console.log(jqXHR.responseText);
							}
						}

						jQuery( '#gmw-update-location-types-message' ).html( 'Unexpected error occurred while tying to update the locations.' ).css( 'color', 'red' ).fadeIn();

						setTimeout( function() {
							jQuery( '#gmw-update-location-types-message, #gmw-update-location-types-spinner' ).fadeOut();
						}, 5000 );
					});
				});
			});
		</script>
		<?php

	}

	/**
	 * Register Location Types post type.
	 *
	 * @access public
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 3.6.4
	 */
	public function register_post_type() {

		$labels = array(
			'name'                  => _x( 'Location Types', 'Post Type General Name', 'geo-my-wp' ),
			'singular_name'         => _x( 'Location Type', 'Post Type Singular Name', 'geo-my-wp' ),
			'menu_name'             => __( 'Location Types', 'geo-my-wp' ),
			'name_admin_bar'        => __( 'Location Types', 'geo-my-wp' ),
			'archives'              => __( 'Location Type Archives', 'geo-my-wp' ),
			'attributes'            => __( 'Location Type Attributes', 'geo-my-wp' ),
			'parent_item_colon'     => __( 'Parent Location Type:', 'geo-my-wp' ),
			'all_items'             => __( 'All Location Types', 'geo-my-wp' ),
			'add_new_item'          => __( 'Add New Location Types', 'geo-my-wp' ),
			'add_new'               => __( 'Add New', 'geo-my-wp' ),
			'new_item'              => __( 'New Location Type', 'geo-my-wp' ),
			'edit_item'             => __( 'Edit Location Type', 'geo-my-wp' ),
			'update_item'           => __( 'Update Location Type', 'geo-my-wp' ),
			'view_item'             => __( 'View Location Type', 'geo-my-wp' ),
			'view_items'            => __( 'View Location Types', 'geo-my-wp' ),
			'search_items'          => __( 'Search Location Type', 'geo-my-wp' ),
			'not_found'             => __( 'Location types not found', 'geo-my-wp' ),
			'not_found_in_trash'    => __( 'Location types not found in Trash', 'geo-my-wp' ),
			'featured_image'        => __( 'Featured Image', 'geo-my-wp' ),
			'set_featured_image'    => __( 'Set featured image', 'geo-my-wp' ),
			'remove_featured_image' => __( 'Remove featured image', 'geo-my-wp' ),
			'use_featured_image'    => __( 'Use as featured image', 'geo-my-wp' ),
			'insert_into_item'      => __( 'Insert into location type', 'geo-my-wp' ),
			'uploaded_to_this_item' => __( 'Uploaded to this location type', 'geo-my-wp' ),
			'items_list'            => __( 'Location types list', 'geo-my-wp' ),
			'items_list_navigation' => __( 'Location types list navigation', 'geo-my-wp' ),
			'filter_items_list'     => __( 'Filter location types list', 'geo-my-wp' ),
		);

		$args = array(
			'label'             => __( 'Locations Type', 'geo-my-wp' ),
			'description'       => __( 'Locations Type for GEO my WP', 'geo-my-wp' ),
			'labels'            => $labels,
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'show_in_menu'      => false,
			'show_ui'           => true,
			'supports'          => array( 'title', 'ex' ),
			'rewrite'           => false,
			'map_meta_cap'      => true,
			'capability_type'   => 'gmw_location_type',
			'query_var'         => false,
		);

		register_post_type( 'gmw_location_type', $args );

		// Default Capabilities.
		$group_caps = array(
			'administrator' => array(
				'delete_gmw_location_types',
				'delete_others_gmw_location_types',
				'delete_published_gmw_location_types',
				'edit_gmw_location_types',
				'edit_others_gmw_location_types',
				'edit_published_gmw_location_types',
				'publish_gmw_location_types',
			),
		);

		// Modify Capabilities.
		$group_caps = apply_filters( 'gmw_location_types_caps', $group_caps );

		// Apply capabilities only once to each role.
		foreach ( $group_caps as $key => $caps ) {

			$role = get_role( $key );

			foreach ( $caps as $cap ) {
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Edit columns in post type Edit page.
	 *
	 * @param  array $columns original columns.
	 *
	 * @return array          modified columns.
	 *
	 * @since 3.6.4
	 *
	 * @author Eyal Fitoussi.
	 */
	public function edit_columns( $columns ) {

		$columns = array(
			'cb'              => '&lt;input type="checkbox" />',
			'title'           => __( 'Name' ),
			'description'     => __( 'Description' ),
			//'user_roles'  => __( 'User Roles' ),
			'xprofile_fields' => __( 'Xprofile Fields' ),
			'date'            => __( 'Date' ),
		);

		return $columns;
	}

	/**
	 * Display content in columns.
	 *
	 * @param  [type] $column  [description].
	 *
	 * @param  [type] $post_id [description].
	 *
	 * @since 3.6.4
	 *
	 * @author Eyal Fitoussi
	 */
	public function manage_columns( $column, $post_id ) {

		global $post;

		switch ( $column ) {

			case 'description':
				echo ! empty( $post->post_excerpt ) ? esc_attr( $post->post_excerpt ) : esc_attr__( 'N/A', 'geo-my-wp' );

				break;

			/*case 'user_roles':
				$roles = maybe_unserialize( $post->post_content );

				echo ! empty( $roles['user_roles'] ) ? implode( ', ', $roles['user_roles'] ) : esc_attr__( 'All user roles', 'geo-my-wp' ); // WPCS: XSS ok.

				break;*/

			case 'xprofile_fields':

				$settings = maybe_unserialize( $post->post_content );

				echo ! empty( $settings['xprofile_fields']['usage'] ) ? esc_attr( $settings['xprofile_fields']['usage'] ) : esc_attr__( 'Disabled', 'geo-my-wp' ); // WPCS: XSS ok.

				break;

			default:
				break;
		}
	}

	/**
	 * Update Location Type post type notices.
	 *
	 * @param  array $messages original notices.
	 *
	 * @since 3.6.4
	 *
	 * @return array           modified notices.
	 */
	public function update_notices( $messages ) {

		$messages['gmw_location_type'][1]  = __( 'Location type updated.', 'geo-my-wp' );
		$messages['gmw_location_type'][4]  = __( 'Location type updated.', 'geo-my-wp' );
		$messages['gmw_location_type'][6]  = __( 'Location type published.', 'geo-my-wp' );
		$messages['gmw_location_type'][7]  = __( 'Location type saved.', 'geo-my-wp' );
		$messages['gmw_location_type'][8]  = __( 'Location type submitted.', 'geo-my-wp' );
		$messages['gmw_location_type'][10] = __( 'Location type draft updated.', 'geo-my-wp' );

		return $messages;
	}

	/**
	 * Modify the placeholder of the post title.
	 *
	 * @param  string $placeholder original placeholder.
	 *
	 * @param  object $post        post object.
	 *
	 * @return string             modified placeholder.
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 3.6.4
	 */
	public function modify_title_placeholder( $placeholder, $post ) {
		return 'gmw_location_type' === $post->post_type ? __( 'Add location type name', 'geo-my-wp' ) : $placeholder;
	}

	/**
	 * Generate meta boxes.
	 *
	 * @since 3.6.4
	 */
	public function add_meta_boxes() {

		add_meta_box( 'gmw_lt_description', __( 'Description', 'geo-my-wp' ), array( $this, 'description_meta_box' ), 'gmw_location_type', 'normal', 'core', array( '__back_compat_meta_box' => true ) );

		// get the xprofile fields.
		if ( gmw_is_addon_active( 'bp_xprofile_geolocation' ) && function_exists( 'bp_is_active' ) && bp_is_active( 'xprofile' ) ) {
			add_meta_box( 'gmw_lt_xprofile_fields_meta_box', __( 'Xprofile Fields', 'geo-my-wp' ), array( $this, 'xprofile_fields_meta_box' ), 'gmw_location_type', 'side' );
		}

		// get the xprofile fields.
		add_meta_box( 'gmw_lt_update_locations_location_type', __( 'Update Location Types', 'geo-my-wp' ), array( $this, 'update_locations_location_type_meta_box' ), 'gmw_location_type', 'side' );

		// Disable for now. Will be available in the future.
		//add_meta_box( 'gmw_lt_user_role_meta_box', __( 'User Roles', 'geo-my-wp' ), array( $this, 'user_role_meta_box' ), 'gmw_location_type', 'side' );
	}

	/**
	 * Add the description meta box.
	 *
	 * @param  object $post post object.
	 *
	 * @since 3.6.4
	 */
	public function description_meta_box( $post ) {
		?>
			<label class="screen-reader-text" for="gmw-location-type-description">
			<?php esc_attr__( 'Description', 'geo-my-wp' ); ?>
			</label>
			<textarea 
				rows="1"
				cols="40"
				name="excerpt" 
				id="gmw-location-type-description"
				style="display: block;margin: 12px 0 0;height: 4em;width: 100%;"><?php echo esc_html( $post->post_excerpt ); // WPCS: XSS ok. textarea_escaped. ?></textarea>
			<?php
	}

	/**
	 * Add the user roles meta box.
	 *
	 * @param  object $post post object.
	 *
	 * @since 3.6.4
	 */
	public function user_role_meta_box( $post ) {

		$saved_data     = maybe_unserialize( $post->post_content );
		$selected       = ! empty( $saved_data['user_roles'] ) ? $saved_data['user_roles'] : array();
		$editable_roles = array_reverse( get_editable_roles() );

		$output = '<select id="gmw-location-types-user-role-settings" multiple name="content[user_roles][]" style="width: 100%;min-height: 150px;">';

		foreach ( $editable_roles as $role => $details ) {

			$name = translate_user_role( $details['name'] );

			if ( in_array( $role, $selected, true ) ) {
				$output .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
			} else {
				$output .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
			}
		}

		$output .= '</select>';

		echo $output; // WPCS: XSS ok.
	}

	/**
	 * Add the user roles meta box.
	 *
	 * @param  object $post post object.
	 *
	 * @since 3.6.4
	 */
	public function xprofile_fields_meta_box( $post ) {

		if ( ! function_exists( 'bp_has_profile' ) || ! bp_has_profile() ) {

			echo '<div class="gmw-xprofile-fields-setting-wrapper usage-setting">';
			echo esc_attr( 'Please verify that the Xprofiel Fields component is activated', 'geo-my-wp' );
			echo '</pre>';

			return;
		}

		$saved_data = maybe_unserialize( $post->post_content );

		if ( ! empty( $saved_data['xprofile_fields'] ) ) {
			$saved_data = $saved_data['xprofile_fields'];
		} else {
			$saved_data = array(
				'usage'                => 'disabled',
				'address_autocomplete' => '',
				'address_fields'       => array(),
			);
		}
		?>
		<div class="gmw-xprofile-fields-setting-wrapper usage-setting">

			<label style="display:block;margin-bottom: 5px">
				<?php esc_attr_e( 'Fields Usage:', 'geo-my-wp' ); ?>
			</label>

			<select id="gmw-xprofile-fields-usage-setting" name="content[xprofile_fields][usage]">
				<option value="disabled"><?php esc_attr_e( 'Disabled', 'geo-my-wp' ); ?></option>											
				<option value="single" <?php selected( $saved_data['usage'], 'single', true ); ?>><?php esc_attr_e( 'Single Address Field', 'geo-my-wp' ); ?></option>
				<option value="multiple" <?php selected( $saved_data['usage'], 'multiple', true ); ?>><?php esc_attr_e( 'Multiple Address Fields', 'geo-my-wp' ); ?></option>									
			</select>
			<em style="margin-top: 5px;display: block;"><?php esc_attr_e( 'Select to either use a single address field as the full address or multiple address fields.', 'geo-my-wp' ); ?></em>
		</div>

		<?php

		$xprofile_fields = array();

		while ( bp_profile_groups() ) {

			bp_the_profile_group();

			while ( bp_profile_fields() ) {

				bp_the_profile_field();

				if ( 'datebox' !== bp_get_the_profile_field_type() ) {
					$xprofile_fields[ bp_get_the_profile_field_id() ] = bp_get_the_profile_field_name();
				}
			}
		}

		$saved_address_fields = $saved_data['address_fields'];
		?>
		<div class="gmw-xprofile-fields-setting-wrapper single-address-field-setting">

			<em style="margin-bottom: 10px;display: block;"><?php esc_attr_e( 'Select the xprofile field that will be used as the address field.', 'geo-my-wp' ); ?></em>

			<label style="display:block;margin-bottom: 5px"><?php echo esc_attr_e( 'Address ', 'geo-my-wp' ); ?></label>

			<select name="content[xprofile_fields][address_fields][address]">

				<option value=""><?php esc_attr_e( 'Disabled', 'geo-my-wp' ); ?></option>

				<?php foreach ( $xprofile_fields as $field_id => $field_name ) { ?>

					<?php $selected = ( isset( $saved_address_fields['address'] ) && $saved_address_fields['address'] == $field_id ) ? 'selected="selected"' : ''; ?>

					<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $field_id ); ?>">
						<?php echo esc_attr( $field_name ); ?>		
					</option>

				<?php } ?>

			</select>
		</div>

		<div class="gmw-xprofile-fields-setting-wrapper multiple-address-field-setting">

			<em style="margin-bottom: 10px;display: block;"><?php esc_attr_e( 'Select the xprofile fields for each address field below.', 'geo-my-wp' ); ?></em>

			<?php $address_fields = array( 'street', 'apt', 'city', 'state', 'zipcode', 'country' ); ?>

			<?php foreach ( $address_fields as $address_field ) : ?>

				<div class="gmw-xprofile-address-fields" style="margin-bottom: 15px">

					<label style="display:block;margin-bottom: 5px"><?php echo ucwords( $address_field ); // WPCS: XSS ok. ?></label>

					<select name="content[xprofile_fields][address_fields][<?php echo $address_field; // WPCS: XSS ok. ?>]">

						<option value=""><?php esc_attr_e( 'Disabled', 'geo-my-wp' ); ?></option>

						<?php foreach ( $xprofile_fields as $field_id => $field_name ) { ?>

							<?php $selected = ( isset( $saved_address_fields[ $address_field ] ) && $saved_address_fields[ $address_field ] == $field_id ) ? 'selected="selected"' : ''; ?>

							<option <?php echo $selected; // WPCS: XSS ok. ?> value="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_attr( $field_name ); ?></option>
						<?php } ?>

					</select>
				</div>
			<?php endforeach; ?>

		</div>

		<div class="gmw-xprofile-fields-setting-wrapper address-autocomplete-setting" style="display:none">
			<label style="display:block;margin-bottom: 5px">
				<input 
					type="checkbox"
					id="setting-bp_xprofile_geolocation-address_autocomplete"
					class="setting-address_autocomplete checkbox"
					name="content[xprofile_fields][address_autocomplete]"
					<?php checked( $saved_data['address_autocomplete'], 'on', true ); ?>
				><?php echo esc_attr_e( 'Google Address Autocomplete', 'geo-my-wp' ); ?>
			</label>
			<em style="margin-top: 5px;display: inline-block;"><?php esc_attr_e( 'Check this checkbox to enable the Google Address Autocompelte feature.', 'geo-my-wp' ); ?></em>
		</div>
		<?php
	}

	/**
	 * Add the user roles meta box.
	 *
	 * @param  object $post post object.
	 *
	 * @since 3.6.4
	 */
	public function update_locations_location_type_meta_box( $post ) {

		echo '<em style="font-size:12px;line-height:10px;">' . esc_html__( 'Use this tool to update locations which do not have a location type set and assign them this location type.', 'geo-my-wp' ) . '</em>';
		echo '<hr />';
		echo '<p>' . esc_html_e( 'Select the Object Types that you would like to update', 'geo-my-wp' ) . '</p>';

		if ( gmw_is_addon_active( 'posts_locator' ) ) {
			echo '<label style="margin-top:5px;display:block;"><input type="checkbox" class="gmw-update-location-types-object-types" value="post" checked="checked" />Posts </label>';
		}

		if ( gmw_is_addon_active( 'members_locator' ) || gmw_is_addon_active( 'users_locator' ) ) {
			echo '<label style="margin-top:5px;display:block;"><input type="checkbox" class="gmw-update-location-types-object-types" value="user" checked="checked" />Users/ BP Memebrs</label>';
		}

		if ( gmw_is_addon_active( 'bp_groups_locator' ) ) {
			echo '<label style="margin-top:5px;display:block;"><input type="checkbox" class="gmw-update-location-types-object-types" value="bp_group" checked="checked" />BP Groups</label>';
		}
		?>
		<div style="margin-top:15px;">
			<button 
				id="gmw-update-location-types-button"
				class="button button-secondary button-large"
				data-location_type="<?php echo absint( $post->ID ); ?>"
				data-nonce="<?php echo wp_create_nonce( 'gmw_update_locations_location_type_nonce' ); // WPCS: XSS ok. ?>"><?php echo esc_html( 'Update Locations', 'ge-my-wp' ); ?></button>

			<span id="gmw-update-location-types-spinner" class="spinner" style="display:none;visibility: visible;float:none;"></span>
		</div>
		<p style="disaply:none;" id="gmw-update-location-types-message"></p>
		<?php

		wp_nonce_field( 'gmw_update_locations_location_type_nonce', 'gmw_update_locations_location_type_nonce' );
	}

	/**
	 * Update locations location types.
	 *
	 * Update locations that do not have a location type set with a specific location type.
	 *
	 * @sicne 1.1
	 *
	 * @author Eyal Fitoussi.
	 */
	public function set_missing_location_type() {

		// verify nonce.
		check_ajax_referer( 'gmw_update_locations_location_type_nonce', 'security', true );

		// Abort if missing data.
		if ( empty( $_POST ) || empty( $_POST['location_type'] ) || empty( $_POST['object_types'] ) ) {
			die( 'Missing location types data.' );
		}

		// Get checked object types.
		$object_type_in = " AND `object_type` IN ( '" . str_replace( ',', "','", sanitize_text_field( wp_unslash( $_POST['object_types'] ) ) ) . "' ) ";

		global $wpdb;

		// Get objects that already use this location. We can't set multiple locations of the same object with the same location type.
		$object_ids = $wpdb->get_col(
			$wpdb->prepare(
				"
	            SELECT `object_id`
	            FROM {$wpdb->base_prefix}gmw_locations 
	            WHERE `location_type` = %s",
				array(
					absint( $_POST['location_type'] ),
				),
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// Prepare the excluded object ID.
		$object_id_not_in = ! empty( $object_ids ) ? 'AND `object_id` NOT IN ( ' . implode( ',', $object_ids ) . ' )' : '';

		// Get the locations ID that we need to update.
		$locations_id = $wpdb->get_col(
			"
	            SELECT `ID`
	            FROM {$wpdb->base_prefix}gmw_locations
	            WHERE `location_type` = 0
				{$object_id_not_in}
				{$object_type_in}
				GROUP BY `object_id`"
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		// Abort if nothing was found.
		if ( empty( $locations_id ) ) {
			wp_send_json( 0 );
		}

		// Update locations.
		$updated = $wpdb->query(
			$wpdb->prepare(
				"
	            UPDATE {$wpdb->base_prefix}gmw_locations 
	            SET   `location_type` = %s 
	            WHERE `ID` IN ( " . implode( ',', $locations_id ) . ' )',
				array( absint( $_POST['location_type'] ) )
			)
		); // WPCS: db call ok, cache ok, unprepared SQL ok.

		$updated = ! empty( $updated ) ? absint( $updated ) : 0;

		wp_send_json( $updated );
	}

	/**
	 * Update meta values before saving the post.
	 *
	 * @param  array $data data that will be saved.
	 *
	 * @param  array $post raw data before modified.
	 *
	 * @since 3.6.4
	 *
	 * @return array      modified saved data.
	 */
	public function update_meta( $data, $post ) {

		if ( 'gmw_location_type' !== $post['post_type'] ) {
			return $data;
		}

		if ( empty( $post['content']['xprofile_fields']['address_autocomplete'] ) ) {
			$post['content']['xprofile_fields']['address_autocomplete'] = '';
		}

		if ( empty( $post['content']['user_roles'] ) ) {
			$post['content']['user_roles'] = array();
		}

		$data['post_content'] = maybe_serialize( $post['content'] );

		return $data;
	}
}
new GMW_Location_Types();
