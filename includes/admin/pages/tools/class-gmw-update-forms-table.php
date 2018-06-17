<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * Update forms table and its data
 *
 * This class should be used after the update to GEO my WP v3.0
 *
 * @since 3.0
 */
class GMW_Update_Forms_Table {

	/**
	 * Run complete table update
	 * 
	 * @return [type] [description]
	 */
	public function init() {

		$this->add_table_columns();
		$this->import_forms();
		$this->fix_forms_table();
	}

	/**
	 * 
	 * Add missing table columns
	 * 
	 */
	public function add_table_columns() {

		global $wpdb;

		$forms_table = $wpdb->prefix . 'gmw_forms';

		$component = $wpdb->get_results( "SHOW COLUMNS FROM {$forms_table} LIKE 'component'" );

		if ( empty( $component ) ) {
			$wpdb->get_results( "ALTER TABLE {$forms_table} ADD COLUMN component VARCHAR(50) NOT NULL AFTER addon" );
		}
		
		$object_type = $wpdb->get_results( "SHOW COLUMNS FROM {$forms_table} LIKE 'object_type'" );

		if ( empty( $object_type ) ) {
			$wpdb->get_results( "ALTER TABLE {$forms_table} ADD COLUMN object_type VARCHAR(50) NOT NULL AFTER component" );
		}
	}

	/**
	 * Import existing forms to new database table created in version 3.0
	 * 
	 * @return [type] [description]
	 *
	 * @since 3.0
	 */
	public function import_forms() {
	    
	    global $wpdb;

	    // get existing forms
	    $gmw_forms = get_option( 'gmw_forms' );
		
		// abort if not forms to import
		if ( empty( $gmw_forms ) ) {
			return;
		}

	    // look for forms table
		$forms_table  = $wpdb->prefix . 'gmw_forms';
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );

		// abort if forms table not exists
		if ( count( $table_exists ) == 0 ) {

			trigger_error( 'GEO my WP Forms table not exists.', E_USER_NOTICE );

			return;
		}
		
		// loop through forms and insert into new table
		foreach ( $gmw_forms as $key => $form ) {

			if ( empty( $form['ID'] ) || empty( $form['addon'] ) ) {
				continue;
			}

			$form_id = ( int ) $form['ID'];
			unset( $form['ID'] );
			
			$addon = $form['addon'];
			unset( $form['addon'] );

			if ( ! empty( $form['form_type'] ) ) {

				$slug = $form['form_type'];
				unset( $form['form_type'] );

			} else {

				$slug = $addon;
			}

			if ( ! empty( $form['form_title'] ) ) {

				$form_name = $form['form_title'];
				unset( $form['form_title'] );

			} else {

				$form_name = '';
			}
			
			if ( ! empty( $form['name'] ) ) {
				
				$form_title = $form['name'];
				unset( $form['name'] );
			
			} else {
				$form_title = 'form_id_'.$form_id;
			}
			
			if ( ! empty( $form['prefix'] ) ) {

				$prefix = $form['prefix'];
				unset( $form['prefix'] );

			} else {

				$prefix = '';
			}

			if ( ! empty( $form['search_form']['address_field']['title'] ) ) {

				$form['search_form']['address_field']['label'] = $form['search_form']['address_field']['title'];

				if ( ! empty( $form['search_form']['address_field']['within'] ) ) {

					$form['search_form']['address_field']['placeholder'] = $form['search_form']['address_field']['title'];
				} else {
					
					$form['search_form']['address_field']['label'] = $form['search_form']['address_field']['title'];
				}	
			}

			// update page load tab settings
			$form['page_load_results']['enabled'] = ! empty( $form['page_load_results']['all_locations'] ) ? 1 : '';

			$form['page_load_results']['display_results'] = ! empty( $form['page_load_results']['display_posts'] ) ? 1 : '';

			// update the new form_submission tab
			$form['form_submission'] = array(
				'results_page'    => ! empty( $form['search_results']['results_page'] ) ? $form['search_results']['results_page'] : '',
				'display_results' => '',
				'display_map'     => ! empty( $form['search_results']['display_map'] ) ? $form['search_results']['display_map'] : 'results'
			);

			$form['search_results']['image'] = array(
				'enabled' => '',
				'width'   => '200px',
				'height'  => '200px'
			);

			$form['search_results']['directions_link'] = ! empty( $form['search_results']['get_directions'] ) ? $form['search_results']['get_directions'] : '';

			$component = '';

			// update posts locator form data
			if ( $slug == 'posts' ) {

				/*
				$addon 		 = 'posts_locator';
				$slug		 = 'posts_locator';
				$object_type = 'post';
				$component   = 'posts_locator'; */

				$form['form_submission']['display_results'] = ! empty( $form['search_results']['display_posts'] ) ? 1 : '';

				if ( ! empty( $form['search_results']['featured_image']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['featured_image']['height'];
					$form['search_results']['image']['width']   = $form['search_results']['featured_image']['width']; 
				}

				if ( ! empty( $form['search_results']['additional_info'] ) ) {
					$form['search_results']['location_meta'] = array_keys( $form['search_results']['additional_info'] );
				}

				if ( ! empty( $form['search_results']['excerpt']['use'] ) ) {
					$form['search_results']['excerpt']['enabled'] = 1;
					$form['search_results']['excerpt']['link'] = ! empty( $form['search_results']['more'] ) ? $form['search_results']['more'] : '';
				}

				if ( ! empty( $form['search_results']['custom_taxes'] ) ) {
					$form['search_results']['taxonomies'] = 1;
				}
			}

			// update friends locator form data
			if ( $slug == 'friends' ) {

				/*
				$addon 		 = 'members_locator';
				$slug 		 = 'members_locator';
				$object_type = 'user';
				$component   = 'members_locator'; */

				$form['form_submission']['display_results'] = ! empty( $form['search_results']['display_members'] ) ? 1 : '';

				if ( ! empty( $form['search_form']['profile_fields'] ) ) {
					$form['search_form']['xprofile_fields']['fields'] = $form['search_form']['profile_fields'];
				}

				if ( ! empty( $form['search_form']['profile_fields_date'] ) ) {
					$form['search_form']['xprofile_fields']['date_field'] = $form['search_form']['profile_fields_date'];
				}

				if ( ! empty( $form['search_results']['avatar']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['avatar']['height'];
					$form['search_results']['image']['width']   = $form['search_results']['avatar']['width']; 
				}
			}

			if ( $slug == 'groups' ) {

				/*
				$addon 		 = 'bp_groups_locator';
				$slug 		 = 'bp_groups_locator';
				$object_type = 'bp_group';
				$component   = 'bp_groups_locator';
				*/
			
				$form['form_submission']['display_results'] = ! empty( $form['search_results']['display_groups'] ) ? 1 : '';

				if ( ! empty( $form['search_results']['avatar']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['avatar']['height'];
					$form['search_results']['image']['width']   = $form['search_results']['avatar']['width']; 
				}
			}

			if ( $slug == 'wp_users' ) {
				
				/*
				$addon 		 = 'users_locator';
				$slug 		 = 'users_locator';
				$object_type = 'user';
				$component   = 'users_locator';
				*/
			
				$form['form_submission']['display_results'] = ! empty( $form['search_results']['display_users'] ) ? 1 : '';

				if ( ! empty( $form['search_results']['avatar']['use'] ) ) {
					$form['search_results']['image']['enabled'] = 1;
					$form['search_results']['image']['height']  = $form['search_results']['avatar']['width'];
					$form['search_results']['image']['width']   = $form['search_results']['avatar']['width']; 
				}
			}

			// global maps
			if ( $addon == 'global_maps' || in_array( $slug, array( 'gmaps_groups', 'gmaps_posts', 'gmaps_friends', 'gmaps_users' ) ) ) {
				
				//$addon = 'global_maps';
		
				if ( ! empty( $form['general_settings']['output_limit'] ) ) {
					$form['page_load_results']['pre_page'] = $form['general_settings']['output_limit'];
					$form['form_submission']['pre_page']   = $form['general_settings']['output_limit'];
				}

				$form['results_messages']['locations_found'] = ! empty( $form['general_settings']['results_message'] ) ? $form['general_settings']['results_message'] : '{results_count} locations found.';

				$form['results_messages']['no_locations_found'] = ! empty( $form['general_settings']['no_results_message'] ) ? $form['general_settings']['no_results_message'] : 'No locations found.';

				$form['search_form']['auto_submission'] = ! empty( $form['search_form']['auto_submit_form']   ) ? 1 : '';
				$form['results_map']['expand_on_load']  = ! empty( $form['general_settings']['pl_expand_map'] ) ? 1 : '';

				if ( ! empty( $form['results_map']['markers_display'] ) ) {
					$form['map_markers']['grouping'] = $form['results_map']['markers_display'];
				}

				if ( ! empty( $form['info_window']['address'] ) ) {
					$form['info_window']['address_fields'] = 'address';
				}

				if ( ! empty( $form['info_window']['get_directions'] ) ) {
					$form['info_window']['directions_link'] = $form['info_window']['get_directions'];
				}

				if ( ! empty( $form['info_window']['live_directions'] ) ) {
					$form['info_window']['directions_system'] = $form['info_window']['live_directions'];
				}

				if ( ! empty( $form['results_map']['map_controls'] ) ) {

					$new_controls = array();

					foreach ( $form['results_map']['map_controls'] as $control => $c_value ) {

						if ( ! empty( $c_value ) ) {
							$new_controls[] = $control;
						}
					}

					$form['results_map']['map_controls'] = $new_controls;
				
				} else {
				
					$form['results_map']['map_controls'] = array();

				}

				// psots global maps
				if ( $slug == 'gmaps_posts' ) {
					
					/*
					$slug 		 = 'posts_locator_global_map';
					$object_type = 'post';
					$component   = 'posts_locator';
					*/
				
					$form['search_form']['include_exclude_terms'] = $form['page_load_results']['include_exclude_terms'] = array(
						'usage'    => '',
						'terms_id' => ''
					);

					if ( ! empty( $form['page_load_results']['taxonomies']['tt_id'] ) ) {
						$form['page_load_results']['include_exclude_terms']['usage']    = $form['page_load_results']['taxonomies']['usage'];
						$form['page_load_results']['include_exclude_terms']['terms_id'] = $form['page_load_results']['taxonomies']['tt_id'];
					}
					
					if ( ! empty( $form['search_form']['taxonomies']['tt_id'] ) ) {
						$form['search_form']['include_exclude_terms']['usage']    = $form['search_form']['taxonomies']['usage'];
						$form['search_form']['include_exclude_terms']['terms_id'] = $form['search_form']['taxonomies']['tt_id'];
					}

				}

				// members locator global maps
				if ( $slug == 'gmaps_friends' ) {
					
					/*
					$slug 		 = 'members_locator_global_map';
					$object_type = 'user';
					$component   = 'members_locator';
					*/
				
					$form['search_form']['xprofile_fields']['fields']     = ! empty( $form['search_form']['profile_fields'] ) ? $form['search_form']['profile_fields'] : array();
					$form['search_form']['xprofile_fields']['date_field'] = ! empty( $form['search_form']['profile_fields_date'] ) ? $form['search_form']['profile_fields_date'] : '';

					if ( empty( $form['search_form']['bp_groups'] ) ) {
						$form['search_form']['bp_groups'] = array();
					}
					
					$form['search_form']['bp_groups']['usage'] = ! empty( $form['search_form']['bp_groups']['usage'] ) ? $form['search_form']['bp_groups']['usage'] : 'dropdown';
					$form['search_form']['bp_groups']['groups'] = ! empty( $form['search_form']['bp_groups']['ids'] ) ? $form['search_form']['bp_groups']['ids'] : array();
    				$form['search_form']['bp_groups']['label'] = 'Groups';
    				$form['search_form']['bp_groups']['show_options_all'] = 'All groups';

    				if ( ! empty( $form['info_window']['avatar'] ) ) {
						$form['info_window']['image']['enabled'] = 1;
						$form['info_window']['image']['width'] = $form['info_window']['image']['height'] = 200;
					}
				}

				// users global maps
				if ( $slug == 'gmaps_users' ) {
					
					/*
					$slug 		 = 'users_locator_global_map';
					$object_type = 'user';
					$component   = 'users_locator';
					*/
				
					if ( ! empty( $form['info_window']['avatar']['use'] ) ) {
						$form['info_window']['image']['enabled'] = 1;
						$form['info_window']['image']['width'] = $form['info_window']['image']['height'] = $form['info_window']['avatar']['width'];
					}
				}

				// users global maps
				if ( $slug == 'gmaps_groups' ) {
					
					/*
					$slug 		 = 'bp_groups_locator_global_map';
					$object_type = 'bp_group';
					$component   = 'groups_locator';
					*/
				
					if ( ! empty( $form['info_window']['avatar']['use'] ) ) {
						$form['info_window']['image']['enabled'] = 1;
						$form['info_window']['image']['width'] = $form['info_window']['image']['height'] = $form['info_window']['avatar']['width'];
					}
				}
			}

			$data = array(
				'ID'	 	  => $form_id,
				'slug'	 	  => $slug,
				'addon'  	  => $addon, 
				'component'   => '',
				'object_type' => '',
				'name'   	  => $form_name,
				'title'  	  => $form_title,
				'prefix' 	  => $prefix,
				'data'   	  => maybe_serialize( $form ),
			);

			// Insert form to database
			$wpdb->replace( 
				$forms_table, 
				$data, 
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) 
			);
		}

		// backup old forms in a new option to prevent the plugin
		// from trying to import forms again after it was already done.
		update_option( 'gmw_forms_old', $gmw_forms );
		delete_option( 'gmw_forms' );
	}

	/**
	 * Update data in forms table
	 *
	 * @since 3.0
	 * 
	 * @return [type] [description]
	 */
	public function fix_forms_table() {

		global $wpdb;

		// forms table name
		$forms_table = $wpdb->prefix . 'gmw_forms';
		
		// check if table exists already 
		$table_exists = $wpdb->get_results( "SHOW TABLES LIKE '{$forms_table}'", ARRAY_A );
		
		// if form table not exists create it
		if ( count( $table_exists ) == 0 ) {

			trigger_error( 'GEO my WP Forms table not exists.', E_USER_NOTICE );

			return;
		}

		/*** Posts locator ***/

		$wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'posts_locator',
                'addon'  	  => 'posts_locator',
                'component'   => 'posts_locator',
                'object_type' => 'post',
                'name'   	  => 'Posts Locator',
                'prefix' 	  => 'pt'
            ), 
            array( 
            	'slug' => 'posts_locator'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

		$wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'posts_locator',
                'addon'  	  => 'posts_locator',
                'component'   => 'posts_locator',
                'object_type' => 'post',
                'name'   	  => 'Posts Locator',
                'prefix' 	  => 'pt'
            ), 
            array( 
            	'slug' => 'posts'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

		/***** Members locator *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'members_locator',
                'addon'  	  => 'members_locator',
                'component'   => 'members_locator',
                'object_type' => 'user',
                'name'   	  => 'BP Members Locator',
                'prefix' 	  => 'fl'
            ), 
            array( 
            	'slug' => 'members_locator'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'members_locator',
                'addon'  	  => 'members_locator',
                'component'   => 'members_locator',
                'object_type' => 'user',
                'name'   	  => 'BP Members Locator',
                'prefix' 	  => 'fl'
            ), 
            array( 
            	'slug' => 'friends'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        /***** Groups Locator *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'bp_groups_locator',
                'addon'  	  => 'bp_groups_locator',
                'component'	  => 'bp_groups_locator',
                'object_type' => 'bp_group',
                'name'   	  => 'BP Groups Locator',
                'prefix' 	  => 'gl'
            ), 
            array( 
            	'slug' => 'bp_groups_locator'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'bp_groups_locator',
                'addon'  	  => 'bp_groups_locator',
                'component'	  => 'bp_groups_locator',
                'object_type' => 'bp_group',
                'name'   	  => 'BP Groups Locator',
                'prefix' 	  => 'gl'
            ), 
            array( 
            	'slug' => 'groups'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        /***** Users Locator *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'users_locator',
                'addon'  	  => 'users_locator',
                'component'   => 'users_locator',
                'object_type' => 'user',
                'name'   	  => 'WP Users Locator',
                'prefix' 	  => 'ul'
            ), 
            array( 
            	'slug' => 'users_locator'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'users_locator',
                'addon'  	  => 'users_locator',
                'component'   => 'users_locator',
                'object_type' => 'user',
                'name'   	  => 'WP Users Locator',
                'prefix' 	  => 'ul'
            ), 
            array( 
            	'slug' => 'wp_users'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        
        /*** Posts Map *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'posts_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'posts_locator',
                'object_type' => 'post',
                'name'   	  => 'Posts Global Map',
                'prefix' 	  => 'gmapspt'
            ), 
            array( 
            	'slug' => 'posts_locator_global_map'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'posts_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'posts_locator',
                'object_type' => 'post',
                'name'   	  => 'Posts Global Map',
                'prefix' 	  => 'gmapspt'
            ), 
            array( 
            	'slug' => 'gmaps_posts'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        /***** Members Map *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'members_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'members_locator',
                'object_type' => 'user',
                'name'   	  => 'BP Members Global Map',
                'prefix' 	  => 'gmapsfl'
            ), 
            array( 
            	'slug' => 'members_locator_global_map'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'members_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'members_locator',
                'object_type' => 'user',
                'name'   	  => 'BP Members Global Map',
                'prefix' 	  => 'gmapsfl'
            ), 
            array( 
            	'slug' => 'gmaps_friends'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        /***** Groups Map *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'bp_groups_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'bp_groups_locator',
                'object_type' => 'bp_group',
                'name'   	  => 'BP Groups Global Map',
                'prefix' 	  => 'gmapsgl'
            ), 
            array( 
            	'slug' => 'bp_groups_locator_global_map'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'bp_groups_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'bp_groups_locator',
                'object_type' => 'bp_group',
                'name'   	  => 'BP Groups Global Map',
                'prefix' 	  => 'gmapsgl'
            ), 
            array( 
            	'slug' => 'gmaps_groups'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        /***** Users Map *****/

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'users_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'users_locator',
                'object_type' => 'user',
                'name'   	  => 'Users Global Map',
                'prefix' 	  => 'gmapsul'
            ), 
            array( 
            	'slug' => 'users_locator_global_map'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        $wpdb->update( 

            $wpdb->prefix . 'gmw_forms', 
            array( 
            	'slug'   	  => 'users_locator_global_map',
                'addon'  	  => 'global_maps',
                'component'   => 'users_locator',
                'object_type' => 'user',
                'name'   	  => 'Users Global Map',
                'prefix' 	  => 'gmapsul'
            ), 
            array( 
            	'slug' => 'gmaps_users'
            ), 
            array( 
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
	}
}
