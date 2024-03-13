<?php
/**
 * GEO my WP Forms Helper Class.
 *
 * Helper class for form editor.
 *
 * @author Eyal Fitoussi.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Helper class.
 */
class GMW_Forms_Helper {

	/**
	 * [__construct description]
	 */
	public function __construct() {}

	/**
	 * Default form values.
	 *
	 * @param  array $form form settings.
	 *
	 * @return [type]       [description]
	 */
	public static function default_settings( $form = array() ) {

		$css = '/* Below is an example of basic CSS that you could use to modify some of the basic colors and text of the search results template. */
/*div.gmw-results-wrapper[data-id="' . absint( $form['ID'] ) . '"] {

	--gmw-form-color-primary: #1C90FF;
	--gmw-form-color-hover-primary: #256fb8;
	--gmw-form-font-color-primary: white;

	--gmw-form-color-secondary: #63CC61;
	--gmw-form-color-hover-secondary: #70d56e;
	--gmw-form-font-color-secondary: white;

	--gmw-form-color-accent: #317ABE;
	--gmw-form-color-hover-accent: #de7a22;
	--gmw-form-font-color-accent: white;

	--gmw-form-title-font-color: #1e90ff;
	--gmw-form-title-font-hover-color: #1c80e0;

	--gmw-form-link-color: #2f8be4;
	--gmw-form-link-color-hover: #236db5;

	--gmw-form-font-color: #333;
	--gmw-form-background-color-primary: #fbfcfe;
	--gmw-form-font-size: 14px;
}*/
';

		$form_data = array(
			'general_settings'  => array(
				'form_name'        => 'form_id_' . $form['ID'],
				'minimize_options' => 1,
			),
			'page_load_results' => array(
				'enabled'         => 1,
				'user_location'   => '',
				'address_filter'  => '',
				'radius'          => '200',
				'units'           => 'imperial',
				'city_filter'     => '',
				'state_filter'    => '',
				'zipcode_filter'  => '',
				'country_filter'  => '',
				'display_results' => 1,
				'display_map'     => 'results',
				'per_page'        => '10,20,50,100',
				'orderby'         => 'distance',
			),
			'search_form'       => array(
				'form_template'  => 'responsive-1',
				'filters_modal'  => array(
					'enabled'      => 0,
					'toggle_label' => __( 'Filter', 'geo-my-wp' ),
					'modal_title'  => __( 'More Filters', 'geo-my-wp' ),
				),
				'address_field'  => array(
					'usage'                => 'single',
					'label'                => 'Address',
					'placeholder'          => 'Type an address',
					'address_autocomplete' => 1,
					'locator'              => 1,
					'locator_submit'       => '',
					'required'             => '',
				),
				'locator_button' => array(
					'usage'          => 'disabled',
					'text'           => __( 'Get my current location', 'geo-my-wp' ),
					'url'            => GMW_IMAGES . '/locator-images/locate-me-blue.png',
					'image'          => GMW_IMAGES . '/locator-images/locate-me-blue.png',
					'locator_submit' => '',
				),
				'radius'         => array(
					'usage'            => 'select',
					'label'            => 'Radius',
					'show_options_all' => 'Miles',
					'options'          => "5\n10\n25\n50\n100",
					'default_value'    => '50',
				),
				'units'          => array(
					'options' => 'imperial',
					'label'   => 'Units',
				),
				'submit_button'  => array(
					'label' => __( 'Submit', 'geo-my-wp' ),
				),
				'styles'         => array(
					'enhanced_fields'    => 1,
					'custom_css'         => '',
					'disable_stylesheet' => '',
				),
			),
			'form_submission'   => array(
				'results_page'    => '',
				'display_results' => 1,
				'display_map'     => 'results',
				'orderby'         => 'distance',
			),
			'search_results'    => array(
				'results_template' => 'responsive-2',
				'results_view'     => array(
					'default' => 'grid',
					'toggle'  => 1,
				),
				'per_page'         => '10,20,50,100',
				'address'          => array(
					'enabled' => 1,
					'fields'  => array(),
					'linked'  => 1,
				),
				'image'            => array(
					'enabled'      => 1,
					'width'        => '120px',
					'height'       => 'auto',
					'no_image_url' => GMW_IMAGES . '/no-image.jpg',
				),
				'excerpt'          => array(
					'usage' => 'disabled',
					'count' => '10',
					'link'  => 'read more...',
				),
				'location_meta'    => array(),
				'opening_hour'     => '',
				'directions_link'  => 1,
				'distance'         => 1,
				'styles'           => array(
					'enhanced_fields'    => 1,
					'custom_css'         => $css,
					'disable_stylesheet' => '',
				),
			),
			'results_map'       => array(
				'map_width'          => '100%',
				'map_height'         => '300px',
				'map_type'           => 'ROADMAP',
				'zoom_level'         => 'auto',
				'min_zoom_level'     => '',
				'max_zoon_level'     => '',
				'map_controls'       => array( 'zoomControl', 'mapTypeControl' ),
				'styles'             => '',
				'snazzy_maps_styles' => '',
			),
			'info_window'       => array(
				'iw_type'         => 'standard',
				'ajax_enabled'    => 1,
				'template'        => array(
					'popup' => 'slide-left',
				),
				'address'         => array(
					'enabled' => 1,
					'fields'  => array(),
					'linked'  => 1,
				),
				'image'           => array(
					'enabled'      => 1,
					'width'        => '120px',
					'height'       => 'auto',
					'no_image_url' => GMW_IMAGES . '/no-image.jpg',
				),
				'excerpt'         => array(
					'usage' => 'disabled',
					'count' => '10',
					'link'  => 'read more...',
				),
				'location_meta'   => array(),
				'opening_hour'    => '',
				'directions_link' => 1,
				'distance'        => 1,
				'styles'          => array(
					'enhanced_fields'    => 1,
					'custom_css'         => '',
					'disable_stylesheet' => '',
				),
			),
		);

		$form_data = apply_filters( 'gmw_form_default_settings', $form_data, $form );

		if ( ! empty( $form['component'] ) ) {
			$form_data = apply_filters( 'gmw_' . $form['component'] . '_component_form_default_settings', $form_data, $form );
		}

		if ( ! empty( $form['addon'] ) ) {
			$form_data = apply_filters( 'gmw_' . $form['addon'] . '_addon_form_default_settings', $form_data, $form );
		}

		if ( ! empty( $form['slug'] ) ) {

			$form_data = apply_filters( 'gmw_' . $form['slug'] . '_form_default_settings', $form_data, $form );

			if ( strpos( $form['slug'], '_mashup_map' ) !== false ) {
				$form_data = apply_filters( 'gmw_mashup_map_form_default_settings', $form_data, $form );
			}
		}

		return $form_data;
	}

	/**
	 * Get all forms from database
	 *
	 * @return array of forms
	 */
	public static function get_forms() {

		// disable cache for now. Causes some issues.
		//$forms = wp_cache_get( 'all_forms', 'gmw_forms' );
		$forms = array();

		if ( empty( $forms ) ) {

			global $wpdb;

			$forms  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}gmw_forms", ARRAY_A ); // WPCS: db call ok, cache ok.
			$output = array();

			foreach ( $forms as $form ) {

				// if happened that form has no data we need to apply the defaults.
				// phpcs:disable.
				/*
				if ( empty( $form['data'] ) ) {
					$form['data'] = maybe_serialize( self::default_settings( $form ) );
				}*/
				// phpcs:enable.

				$data = maybe_unserialize( $form['data'] );

				// most likely bad data.
				if ( ! is_array( $data ) ) {
					$data = array();
				}

				$output[ $form['ID'] ] = array_merge( $form, $data );

				unset( $output[ $form['ID'] ]['data'] );
			}

			$forms = $output;

			//wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
		}

		return ! empty( $forms ) ? $forms : array();
	}

	/**
	 * Transfer forms data from GMW v3.x to v4.0.
	 *
	 * @since 4.0.
	 *
	 * @param  array $form gmw form.
	 *
	 * @return array
	 */
	public static function gmw_v4_form_data_importer( $form ) {

		/* ---- Search Form ---- */

		if ( ! isset( $form['search_form']['filters_modal'] ) ) {

			$form['search_form']['filters_modal'] = array(
				'enabled'      => '',
				'toggle_label' => 'Filters',
				'modal_title'  => 'More Filters',
			);
		}

		$form['search_form']['address_field']['usage']    = 'single';
		$form['search_form']['address_field']['required'] = ! empty( $form['search_form']['address_field']['mandatory'] ) ? 1 : 0;

		unset( $form['search_form']['address_field']['mandatory'] );

		/* ---- Locator Button ---- */

		$form['search_form']['locator_button'] = array(
			'usage'          => ! empty( $form['search_form']['locator'] ) ? $form['search_form']['locator'] : 'disabled',
			'text'           => ! empty( $form['search_form']['locator_text'] ) ? $form['search_form']['locator_text'] : 'Get my current location',
			'image'          => ! empty( $form['search_form']['locator_image'] ) ? $form['search_form']['locator_image'] : 'blue-dot.png',
			'url'            => GMW_IMAGES . '/locator-images/locate-me-blue.png',
			'locator_submit' => ! empty( $form['search_form']['locator_submit'] ) ? $form['search_form']['locator_submit'] : 0,
		);

		unset(
			$form['search_form']['locator'],
			$form['search_form']['locator_text'],
			$form['search_form']['locator_image'],
			$form['search_form']['locator_submit']
		);

		/* ---- Radius ---- */

		if ( ! empty( $form['search_form']['radius_slider']['enabled'] ) ) {

			$form['search_form']['radius'] = array(
				'usage'            => 'slider',
				'label'            => 'Radius',
				'show_options_all' => 'Miles',
				'options'          => "5\n10\n25\n50\n100",
				'default_value'    => ! empty( $form['search_form']['radius_slider']['default_value'] ) ? $form['search_form']['radius_slider']['default_value'] : '50',
				'min_value'        => ! empty( $form['search_form']['radius_slider']['min_value'] ) ? $form['search_form']['radius_slider']['min_value'] : '0',
				'max_value'        => ! empty( $form['search_form']['radius_slider']['max_value'] ) ? $form['search_form']['radius_slider']['max_value'] : '200',
				'prefix'           => '',
				'suffix'           => '',
				'required'         => 0,
			);

			unset( $form['search_form']['radius_slider'] );

		} else {

			if ( ! is_array( $form['search_form']['radius'] ) ) {

				if ( strpos( $form['search_form']['radius'], ',' ) !== false ) {

					$usage   = 'select';
					$default = explode( ',', $form['search_form']['radius'] )[0];
					$options = str_replace( ',', "\n", $form['search_form']['radius'] );

				} else {

					$usage   = 'default';
					$default = $form['search_form']['radius'];
					$options = "5\n10\n25\n50\n100";
				}

				$form['search_form']['radius'] = array(
					'usage'            => $usage,
					'label'            => 'Radius',
					'show_options_all' => 'Miles',
					'options'          => $options,
					'default_value'    => $default,
					'required'         => 0,
					'min_value'        => '0',
					'max_value'        => '200',
					'prefix'           => '',
					'suffix'           => '',
				);
			}
		}

		$form['search_form']['units'] = array(
			'options' => ! empty( $form['search_form']['units'] ) ? $form['search_form']['units'] : 'both',
			'label'   => 'Units',
		);

		$form['search_form']['submit_button'] = array(
			'label' => 'Search',
		);

		$form['search_form']['styles'] = array(
			'disable_core_styles' => '',
			'custom_css'          => '',
		);

		if ( isset( $form['search_form']['keywords'] ) && is_array( $form['search_form']['keywords'] ) ) {

			if ( empty( $form['search_form']['keywords']['usage'] ) ) {
				$form['search_form']['keywords']['usage'] = 'disabled';
			}

			$form['search_form']['keywords']['required'] = '';
		}

		$form['search_form']['reset_button'] = array(
			'enabled' => '',
			'label'   => 'Reset',
		);

		/* ---- post types ---- */

		if ( ! empty( $form['search_form']['post_types_settings'] ) ) {

			$form['search_form']['post_types_settings']['required'] = 0;
			$form['search_form']['post_types_settings']['smartbox'] = 0;

			if ( 'dropdown' === $form['search_form']['post_types_settings']['usage'] ) {

				$form['search_form']['post_types_settings']['usage'] = 'select';

			} elseif ( 'smartbox' === $form['search_form']['post_types_settings']['usage'] ) {

				$form['search_form']['post_types_settings']['usage']    = 'select';
				$form['search_form']['post_types_settings']['smartbox'] = 1;

			} elseif ( 'smartbox_multiple' === $form['search_form']['post_types_settings']['usage'] ) {

				$form['search_form']['post_types_settings']['usage']    = 'multiselect';
				$form['search_form']['post_types_settings']['smartbox'] = 1;

			} elseif ( 'checkbox' === $form['search_form']['post_types_settings']['usage'] ) {

				$form['search_form']['post_types_settings']['usage'] = 'checkboxes';
			}
		}

		if ( ! empty( $form['search_form']['custom_fields'] ) ) {

			foreach ( $form['search_form']['custom_fields'] as $cf_name => $cf_value ) {

				$usage        = 'text';
				$compare      = '=';
				$date_compare = '=';
				$date_format  = 'm/d/Y';

				if ( 'NUMERIC' === $cf_value['type'] ) {

					$usage   = 'number';
					$compare = $cf_value['compare'];

				} elseif ( 'DATE' === $cf_value['type'] ) {

					$usage        = 'date';
					$date_compare = $cf_value['compare'];
					$date_format  = $cf_value['date_type'];

				} elseif ( 'TIME' === $cf_value['type'] ) {

					$usage        = 'time';
					$compare      = $cf_value['compare'];
					$date_compare = $cf_value['compare'];
				}

				$form['search_form']['custom_fields'][ $cf_name ] = array(
					'name'                    => $cf_name,
					'usage'                   => $usage,
					'options'                 => array(),
					'second_options'          => array(),
					'date_format'             => $date_format,
					'time_format'             => 'h:iK',
					'compare'                 => $compare,
					'date_compare'            => $date_compare,
					'slider_compare'          => '=',
					'label'                   => ! empty( $cf_value['label'][0] ) ? $cf_value['label'][0] : '',
					'second_label'            => ! empty( $cf_value['label'][1] ) ? $cf_value['label'][1] : '',
					'placeholder'             => ! empty( $cf_value['placeholder'][0] ) ? $cf_value['placeholder'][0] : '',
					'second_placeholder'      => ! empty( $cf_value['placeholder'][1] ) ? $cf_value['placeholder'][1] : '',
					'show_options_all'        => '',
					'second_show_options_all' => '',
					'min_value'               => 0,
					'max_value'               => 100,
					'value'                   => '',
					'second_value'            => '',
					'value_prefix'            => '',
					'value_suffix'            => '',
					'step'                    => 1,
				);
			}
		}

		// For Global Maps only.
		if ( 'posts_locator_global_map' === $form['slug'] ) {

			// Page Load taxonomies.
			if ( isset( $form['page_load_results']['include_exclude_terms']['usage'] ) && ! empty( $form['page_load_results']['include_exclude_terms']['terms_id'] ) ) {

				$terms_usage = $form['page_load_results']['include_exclude_terms']['usage'];

				foreach ( $form['page_load_results']['include_exclude_terms']['terms_id'] as $term_tax_id ) {

					$term = get_term_by( 'term_taxonomy_id', $term_tax_id );

					if ( empty( $term ) || is_wp_error( $term ) ) {
						continue;
					}

					if ( ! isset( $form['page_load_results']['include_exclude_terms'][ $term->taxonomy ] ) ) {

						$form['page_load_results']['include_exclude_terms'][ $term->taxonomy ] = array(
							'post_types' => array(),
							'include'    => array(),
							'exclude'    => array(),
						);
					}

					$form['page_load_results']['include_exclude_terms'][ $term->taxonomy ][ $terms_usage ][] = $term->term_id;
				}

				unset(
					$form['page_load_results']['include_exclude_terms']['usage'],
					$form['page_load_results']['include_exclude_terms']['terms_id']
				);
			}

			// Search Form taxonomies.
			if ( isset( $form['search_form']['taxonomies']['include_exclude_terms']['usage'] ) && ! empty( $form['search_form']['taxonomies']['include_exclude_terms']['terms_id'] ) ) {

				$terms_usage = $form['search_form']['taxonomies']['include_exclude_terms']['usage'];

				foreach ( $form['search_form']['taxonomies']['include_exclude_terms']['terms_id'] as $term_tax_id ) {

					$term = get_term_by( 'term_taxonomy_id', $term_tax_id );

					if ( empty( $term ) || is_wp_error( $term ) ) {
						continue;
					}

					if ( ! isset( $form['search_form']['taxonomies']['include_exclude_terms'][ $term->taxonomy ] ) ) {

						$form['search_form']['taxonomies']['include_exclude_terms'][ $term->taxonomy ] = array(
							'post_types' => array(),
							'include'    => array(),
							'exclude'    => array(),
						);
					}

					$form['search_form']['taxonomies']['include_exclude_terms'][ $term->taxonomy ][ $terms_usage ][] = $term->term_id;
				}

				unset(
					$form['search_form']['taxonomies']['include_exclude_terms']['usage'],
					$form['search_form']['taxonomies']['include_exclude_terms']['terms_id']
				);
			}
		}

		if ( ! empty( $form['search_form']['taxonomies'] ) ) {

			$new_taxonomies = array();
			$all_post_types = get_post_types();
			$inc_exc_terms  = isset( $form['search_form']['taxonomies']['include_exclude_terms'] ) ? $form['search_form']['taxonomies']['include_exclude_terms'] : array();

			foreach ( $form['search_form']['taxonomies'] as $pt_slug => $pt_options ) {

				if ( 'include_exclude_terms' === $pt_slug ) {
					continue;
				}

				foreach ( $pt_options as $tax_name => $tax_options ) {

					if ( empty( $tax_options['style'] ) ) {
						continue;
					}

					$taxonomy                    = get_taxonomy( $tax_name );
					$new_taxonomies[ $tax_name ] = $tax_options;
					$pt_added                    = false;

					if ( ! empty( $taxonomy ) && is_object( $taxonomy ) ) {

						$post_types = $taxonomy->object_type;

						if ( 0 !== count( array_intersect( $post_types, $all_post_types ) ) ) {

							$new_taxonomies[ $tax_name ]['post_types'] = $post_types;

							if ( ! empty( $inc_exc_terms[ $tax_name ] ) ) {
								$inc_exc_terms[ $tax_name ]['post_types'] = $post_types;
							}

							/*if ( ! empty( $form['page_load_results']['include_exclude_terms'][ $tax_name ] ) ) {
								$form['page_load_results']['include_exclude_terms'][ $tax_name ]['post_types'] = $post_types;
							}*/

							$pt_added = true;
						}
					}

					if ( ! $pt_added ) {

						$new_taxonomies[ $tax_name ]['post_types'] = array( $pt_slug );

						if ( ! empty( $inc_exc_terms[ $tax_name ] ) ) {
							$inc_exc_terms[ $tax_name ]['post_types'] = array( $pt_slug );
						}

						/*if ( ! empty( $form['page_load_results']['include_exclude_terms'][ $tax_name ] ) ) {
							$form['page_load_results']['include_exclude_terms'][ $tax_name ]['post_types'] = array( $pt_slug );
						}*/
					}

					// skip If post type of the taxonomy does not exists.
					// phpcs:disable.
					/*if ( 0 === count( array_intersect( $post_types, $all_post_types ) ) ) {
						continue;
					}
					*/
					// phpcs:enable.

					if ( 'dropdown' === $tax_options['style'] ) {

						$new_taxonomies[ $tax_name ]['style'] = 'select';

					} elseif ( 'smartbox' === $tax_options['style'] ) {

						$new_taxonomies[ $tax_name ]['style']    = 'select';
						$new_taxonomies[ $tax_name ]['smartbox'] = 1;

					} elseif ( 'smartbox_multiple' === $tax_options['style'] ) {

						$new_taxonomies[ $tax_name ]['style']    = 'multiselect';
						$new_taxonomies[ $tax_name ]['smartbox'] = 1;

					} elseif ( 'checkbox' === $tax_options['style'] ) {

						$new_taxonomies[ $tax_name ]['style'] = 'checkboxes';
					}
				}
			}

			$form['search_form']['taxonomies']            = $new_taxonomies;
			$form['search_form']['include_exclude_terms'] = $inc_exc_terms;

				// phpcs:disable.
				/*if ( 'posts_locator_global_map' === $form['slug'] && isset( $form['search_form']['include_exclude_terms']['usage'] ) && ! empty( $form['search_form']['include_exclude_terms']['terms_id'] ) ) {

					foreach( $form['search_form']['include_exclude_terms']['terms_id'] as $term_id ) {

						echo '<Pre>';
						print_r(get_term( 1 ) );
							df();
					}
				} */
				// phpcs:enable.
		}

		/* ----- Search Results ----- */

		$form['search_results']['results_view'] = array(
			'default'      => 'grid',
			'toggle'       => 1,
			'grid_columns' => '',
		);

		if ( isset( $form['search_results']['pagination'] ) && empty( $form['search_results']['pagination']['label'] ) ) {
			$form['search_results']['pagination']['label'] = 'Load more';
		}

		if ( isset( $form['search_results']['image'] ) && is_array( $form['search_results']['image'] ) ) {

			$form['search_results']['image']['no_image_url'] = GMW_IMAGES . '/no-image.jpg';

			if ( isset( $form['search_results']['image']['width'] ) && is_numeric( $form['search_results']['image']['width'] ) ) {
				$form['search_results']['image']['width'] .= 'px';
			}

			if ( isset( $form['search_results']['image']['height'] ) && is_numeric( $form['search_results']['image']['height'] ) ) {
				$form['search_results']['image']['height'] .= 'px';
			}

		} else {

			$form['search_results']['image'] = array(
				'enabled'      => 0,
				'width'        => '120px',
				'height'       => 'auto',
				'no_image_url' => GMW_IMAGES . '/no-image.jpg',
			);
		}

		$form['search_results']['address'] = array(
			'enabled' => 1,
			'linked'  => 1,
			'fields'  => array(),
		);

		$form['search_results']['styles'] = array(
			'disable_core_styles'          => '',
			'disable_single_item_template' => '',
			'custom_css'                   => '',
		);

		if ( isset( $form['search_results']['address_fields'] ) ) {

			$form['search_results']['address']['fields'] = $form['search_results']['address_fields'];

			unset( $form['search_results']['address_fields'] );
		}

		if ( ! is_array( $form['search_results']['orderby'] ) ) {

			$form['search_results']['orderby'] = array(
				'enabled' => ! empty( $form['search_results']['orderby'] ) ? 1 : 0,
				'options' => ! empty( $form['search_results']['orderby'] ) ? str_replace( array( ':', ',' ), array( ' : ', "\n" ), $form['search_results']['orderby'] ) : '',
			);
		}

		$form['search_results']['distance'] = 1;

		/* ----- No results ---- */

		if ( ! empty( $form['no_results']['message'] ) && ! is_array(  $form['no_results']['message'] ) ) {

			$form['no_results']['message'] = array(
				'wider_search_radius'    => ! empty( $form['no_results']['wider_search']['radius'] ) ? $form['no_results']['wider_search']['radius'] : 200,
				'wider_search_link_text' => ! empty( $form['no_results']['wider_search']['link_text'] ) ? $form['no_results']['wider_search']['link_text'] : 'click here',
				'all_results_link_text'  => ! empty( $form['no_results']['all_results_link'] ) ? $form['no_results']['all_results_link'] : 'click here',
				'message'                => $form['no_results']['message'],
			);

			unset( $form['no_results']['wider_search'], $form['no_results']['all_results_link'] );
		}

		/***** Post types */
		if ( isset( $form['search_results']['excerpt']['enabled'] ) ) {

			$form['search_results']['excerpt'] = array(
				'usage' => ! empty( $form['search_results']['excerpt']['enabled'] ) ? $form['search_results']['excerpt']['usage'] : 'disabled',
				'count' => ! empty( $form['search_results']['excerpt']['count'] ) ? $form['search_results']['excerpt']['count'] : 20,
				'link'  => ! empty( $form['search_results']['excerpt']['link'] ) ? $form['search_results']['excerpt']['link'] : '',
			);
		}

		/* ---- Info - window ---- */

		if ( isset( $form['info_window'] ) ) {

			if ( isset( $form['info_window']['iw_type'] ) && ( 'infobubble' === $form['info_window']['iw_type'] || 'infobox' === $form['info_window']['iw_type'] ) ) {
				$form['info_window']['iw_type']              = 'standard';
				$form['info_window']['template']['standard'] = 'default';
			}

			$form['info_window']['address'] = array(
				'enabled' => 1,
				'linked'  => 1,
				'fields'  => array(),
			);

			if ( isset( $form['info_window']['address_fields'] ) ) {

				$form['info_window']['address']['fields'] = $form['info_window']['address_fields'];

				unset( $form['info_window']['address_fields'] );
			}

			if ( isset( $form['info_window']['image'] ) ) {
				$form['info_window']['image']['no_image_url'] = GMW_IMAGES . '/no-image.jpg';

				if ( isset( $form['info_window']['image']['width'] ) && is_numeric( $form['info_window']['image']['width'] ) ) {
					$form['info_window']['image']['width'] .= 'px';
				}

				if ( isset( $form['info_window']['image']['height'] ) && is_numeric( $form['info_window']['image']['height'] ) ) {
					$form['info_window']['image']['height'] .= 'px';
				}
			}

			$form['info_window']['styles'] = array(
				'disable_core_styles' => '',
				'custom_css'          => '',
			);

			/* ---- Post types ---- */

			if ( isset( $form['info_window']['excerpt']['enabled'] ) ) {

				$form['info_window']['excerpt'] = array(
					'usage' => ! empty( $form['info_window']['excerpt']['enabled'] ) ? $form['info_window']['excerpt']['usage'] : 'disabled',
					'count' => ! empty( $form['info_window']['excerpt']['count'] ) ? $form['info_window']['excerpt']['count'] : 20,
					'link'  => ! empty( $form['info_window']['excerpt']['link'] ) ? $form['info_window']['excerpt']['link'] : '',
				);
			}
		}

		/* ---- Memebrs Locator forms ---- */

		if ( 'members_locator' === $form['component'] ) {

			if ( ! empty( $form['page_load_results']['include_exclude_member_types'] ) && is_array( $form['page_load_results']['include_exclude_member_types'] ) ) {

				$mtypes = $form['page_load_results']['include_exclude_member_types'];

				if ( isset( $mtypes['member_types'] ) && is_array( $mtypes['member_types'] ) ) {

					$form['page_load_results']['include_exclude_member_types']['options'] = $mtypes['member_types'];

					unset( $form['page_load_results']['include_exclude_member_types']['member_types'] );
				}

				$form['page_load_results']['member_types_filter'] = $form['page_load_results']['include_exclude_member_types'];

				unset( $form['page_load_results']['include_exclude_member_types'] );
			}

			if ( ! empty( $form['search_form']['member_types_filter'] ) && is_array( $form['search_form']['member_types_filter'] ) ) {

				if ( isset( $form['search_form']['member_types_filter']['member_types'] ) ) {

					$form['search_form']['member_types_filter']['options'] = $form['search_form']['member_types_filter']['member_types'];

					unset( $form['search_form']['member_types_filter']['member_types'] );
				}

				$form['search_form']['member_types_filter']['required'] = 0;
				$form['search_form']['member_types_filter']['smartbox'] = 0;

				if ( isset( $form['search_form']['member_types_filter']['usage'] ) ) {

					if ( 'dropdown' === $form['search_form']['member_types_filter']['usage'] ) {

						$form['search_form']['member_types_filter']['usage'] = 'select';

					} elseif ( 'smartbox' === $form['search_form']['member_types_filter']['usage'] ) {

						$form['search_form']['member_types_filter']['usage']    = 'select';
						$form['search_form']['member_types_filter']['smartbox'] = 1;

					} elseif ( 'smartbox_multiple' === $form['search_form']['member_types_filter']['usage'] ) {

						$form['search_form']['member_types_filter']['usage']    = 'multiselect';
						$form['search_form']['member_types_filter']['smartbox'] = 1;

					} elseif ( 'checkbox' === $form['search_form']['member_types_filter']['usage'] ) {

						$form['search_form']['member_types_filter']['usage'] = 'checkboxes';
					}
				} else {
					$form['search_form']['member_types_filter']['usage'] = 'disabled';

				}
			}

			$new_xfields = array();

			if ( ! empty( $form['search_results']['xprofile_fields']['fields'] ) && is_array( $form['search_results']['xprofile_fields']['fields'] ) ) {

				foreach ( $form['search_results']['xprofile_fields']['fields'] as $xfield ) {

					$field_id   = absint( $xfield );
					$field_data = xprofile_get_field( $field_id );

					$new_xfields[ $field_id ] = array(
						'name'         => $field_data->name,
						'label'        => $field_data->name,
						'field_output' => '%field%',
					);
				}
			}

			if ( ! empty( $form['search_results']['xprofile_fields']['date_field'] ) ) {

				$field_id   = absint( $form['search_results']['xprofile_fields']['date_field'] );
				$field_data = xprofile_get_field( $field_id );

				$new_xfields[ $field_id ] = array(
					'name'         => $field_data->name,
					'label'        => $field_data->name,
					'field_output' => '%field%',
				);
			}

			$form['search_results']['xprofile_fields'] = $new_xfields;

			$new_xfields = array();

			if ( ! empty( $form['info_window']['xprofile_fields']['fields'] ) ) {

				foreach ( $form['info_window']['xprofile_fields']['fields'] as $xfield ) {

					$field_id   = absint( $xfield );
					$field_data = xprofile_get_field( $field_id );

					$new_xfields[ $field_id ] = array(
						'name'         => $field_data->name,
						'label'        => $field_data->name,
						'field_output' => '%field%',
					);
				}
			}

			if ( ! empty( $form['info_window']['xprofile_fields']['date_field'] ) ) {

				$field_id   = absint( $form['info_window']['xprofile_fields']['date_field'] );
				$field_data = xprofile_get_field( $field_id );

				$new_xfields[ $field_id ] = array(
					'name'         => $field_data->name,
					'label'        => $field_data->name,
					'field_output' => '%field%',
				);
			}

			$form['info_window']['xprofile_fields'] = $new_xfields;

			if ( ! empty( $form['search_form']['bp_groups'] ) ) {

				if ( is_array( $form['search_form']['bp_groups'] ) ) {

					if ( isset( $form['search_form']['bp_groups']['groups'] ) ) {

						$form['search_form']['bp_groups']['options'] = $form['search_form']['bp_groups']['groups'];

						unset( $form['search_form']['bp_groups']['groups'] );

					} else {
						$form['search_form']['bp_groups']['options'] = array();
					}

					$form['search_form']['bp_groups']['required'] = 0;
					$form['search_form']['bp_groups']['smartbox'] = 0;

					if ( 'dropdown' === $form['search_form']['bp_groups']['usage'] ) {

						$form['search_form']['bp_groups']['usage'] = 'select';

					} elseif ( 'smartbox' === $form['search_form']['bp_groups']['usage'] ) {

						$form['search_form']['bp_groups']['usage']    = 'select';
						$form['search_form']['bp_groups']['smartbox'] = 1;

					} elseif ( 'smartbox_multiple' === $form['search_form']['bp_groups']['usage'] ) {

						$form['search_form']['bp_groups']['usage']    = 'multiselect';
						$form['search_form']['bp_groups']['smartbox'] = 1;

					} elseif ( 'checkbox' === $form['search_form']['bp_groups']['usage'] ) {

						$form['search_form']['bp_groups']['usage'] = 'checkboxes';
					}
				} else {

					$form['search_form']['bp_groups'] = array(
						'usage'            => 'disabled',
						'options'          => array(),
						'smartbox'         => 0,
						'label'            => 'Groups',
						'show_options_all' => '',
						'required'         => 0,
					);
				}
			}
		}

		/* ---- Groups Locator forms ---- */

		if ( 'bp_groups_locator' === $form['component'] ) {

			if ( ! empty( $form['page_load_results']['include_exclude_group_types'] ) ) {

				$mtypes = $form['page_load_results']['include_exclude_group_types'];

				if ( isset( $mtypes['group_types'] ) && is_array( $mtypes['group_types'] ) ) {

					$form['page_load_results']['include_exclude_group_types']['options'] = array_values( $mtypes['group_types'] );

					unset( $form['page_load_results']['include_exclude_group_types']['group_types'] );
				}

				$form['page_load_results']['group_types_filter'] = $form['page_load_results']['include_exclude_group_types'];

				unset( $form['page_load_results']['include_exclude_group_types'] );
			}

			if ( ! empty( $form['search_form']['group_types_filter'] ) ) {

				if ( isset( $form['search_form']['group_types_filter']['group_types'] ) ) {

					$form['search_form']['group_types_filter']['options'] = $form['search_form']['group_types_filter']['group_types'];

					unset( $form['search_form']['group_types_filter']['group_types'] );
				}

				$form['search_form']['group_types_filter']['required'] = 0;
				$form['search_form']['group_types_filter']['smartbox'] = 0;

				if ( 'dropdown' === $form['search_form']['group_types_filter']['usage'] ) {

					$form['search_form']['group_types_filter']['usage'] = 'select';

				} elseif ( 'smartbox' === $form['search_form']['group_types_filter']['usage'] ) {

					$form['search_form']['group_types_filter']['usage']    = 'select';
					$form['search_form']['group_types_filter']['smartbox'] = 1;

				} elseif ( 'smartbox_multiple' === $form['search_form']['group_types_filter']['usage'] ) {

					$form['search_form']['group_types_filter']['usage']    = 'multiselect';
					$form['search_form']['group_types_filter']['smartbox'] = 1;

				} elseif ( 'checkbox' === $form['search_form']['group_types_filter']['usage'] ) {

					$form['search_form']['group_types_filter']['usage'] = 'checkboxes';
				}
			}
		}

		$form['general_settings'] = array(
			'form_name'        => '',
			'minimize_options' => 1,
			'form_usage'       => '',
		);

		/* ---- AJAX Forms ---- */

		if ( 'ajax_forms' === $form['addon'] ) {
			$form['general_settings']['legacy_style'] = 1;
		}

		/* ---- Update new form data in database ---- */

		$form_data = $form;

		unset(
			$form_data['ID'],
			$form_data['slug'],
			$form_data['addon'],
			$form_data['component'],
			$form_data['object_type'],
			$form_data['name'],
			$form_data['title'],
			$form_data['prefix'],
		);

		global $wpdb;

		$wpdb->update(
			$wpdb->prefix . 'gmw_forms',
			array(
				'data' => serialize( $form_data ),
			),
			array( 'ID' => $form['ID'] ),
			array(
				'%s',
			),
			array( '%d' )
		); // DB call ok, cache ok.

		return $form;
	}

	/**
	 * Get specific form by form ID.
	 *
	 * @param integer $form_id form ID.
	 *
	 * @return mixed specific form if form ID pass otherwise all forms
	 */
	public static function get_form( $form_id = 0 ) {

		absint( $form_id );

		// abort if no ID passes.
		if ( empty( $form_id ) ) {
			return;
		}

		$form = wp_cache_get( $form_id, 'gmw_forms' );

		// Disable the cache for now as it causes some issues on sites with object cache enabled.
		$form = false;

		if ( empty( $form ) ) {

			global $wpdb;

			$form = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}gmw_forms WHERE ID = %d",
					$form_id
				),
				ARRAY_A
			); // WPCS: db call ok, cache ok.

			if ( ! empty( $form ) ) {

				// if happens that form has no data, we need to use the form's default values.
				if ( empty( $form['data'] ) || ! is_array( maybe_unserialize( $form['data'] ) ) ) {
					$form['data'] = self::default_settings( $form );
				}

				$form_data = maybe_unserialize( $form['data'] );

				if ( empty( $form_data ) || ! is_array( $form_data ) ) {

					wp_cache_delete( $form_id, 'gmw_forms' );

					return;
				}

				$form = array_merge( $form, $form_data );

				unset( $form['data'] );

				wp_cache_set( $form_id, $form, 'gmw_forms' );

			} else {

				$form = false;

				wp_cache_delete( $form_id, 'gmw_forms' );
			}
		}

		// Abort if form does not exist.
		if ( empty( $form ) ) {

			gmw_trigger_error(
				sprintf(
					/* translators: %s: GMW form ID. */
					__( 'GEO my WP Form with ID %s does not exist.', 'geo-my-wp' ),
					$form_id
				)
			);

			return;
		}

		// Import form data from GEO my WP v3.0 to v4.0.
		// We know that we need to import when the "general_settings" tab value is missing.
		if ( ! isset( $form['general_settings'] ) ) {
			$form = self::gmw_v4_form_data_importer( $form );
		}

		if ( ! empty( $form ) ) {

			if ( empty( $form['component'] ) ) {

				if ( strpos( $form['slug'], 'posts' ) !== false ) {

					$form['component'] = 'posts_locator';

				} elseif ( strpos( $form['slug'], 'members' ) !== false ){

					$form['component'] = 'members_locator';

				} elseif ( strpos( $form['slug'], 'groups' ) !== false ){

					$form['component'] = 'bp_groups_locator';

				} elseif( strpos( $form['slug'], 'users' ) !== false ){

					$form['component'] = 'users_locator';

				} else {
					$form['component'] = $form['addon'];
				}
			}

			if ( empty( $form['object_type'] ) ) {

				if ( strpos( $form['slug'], 'posts' ) !== false ) {

					$form['object_type'] = 'post';

				} elseif ( strpos( $form['slug'], 'members' ) !== false ){

					$form['object_type'] = 'user';

				} elseif ( strpos( $form['slug'], 'groups' ) !== false ){

					$form['object_type'] = 'bp_group';

				} elseif( strpos( $form['slug'], 'users' ) !== false ){

					$form['object_type'] = 'user';

				} else {
					$form['object_type'] = 'post';
				}
			}

			return $form;

		} else {
			return false;
		}

		//return ! empty( $form ) ? $form : false;
	}

	/**
	 * Delte form
	 *
	 * @param  integer $form_id form ID.
	 *
	 * @return [type]           [description]
	 */
	public static function delete_form( $form_id = 0 ) {

		if ( ! absint( $form_id ) ) {
			return false;
		}

		global $wpdb;

		// delete form from database.
		$delete = $wpdb->delete(
			$wpdb->prefix . 'gmw_forms',
			array(
				'ID' => $form_id,
			),
			array(
				'%d',
			)
		); // WPCS: db call ok, cache ok.

		// update forms in cache.
		self::update_forms_cache();

		return $delete;
	}

	/**
	 * Update forms cache
	 */
	public static function update_forms_cache() {

		global $wpdb;

		$forms = $wpdb->get_results( "SELECT ID, data FROM {$wpdb->prefix}gmw_forms", ARRAY_A ); // WPCS: db call ok, cache ok.

		$output = array();

		foreach ( $forms as $form ) {
			$output[ $form['ID'] ] = maybe_unserialize( $form['data'] );
		}

		$forms = $output;

		wp_cache_set( 'all_forms', $forms, 'gmw_forms' );
	}
}
