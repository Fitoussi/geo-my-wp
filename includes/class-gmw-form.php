<?php
/**
 * GEO my WP Form class.
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different object types.
 *
 * @package geo-my-wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * GMW_Form_Init class
 *
 * Create the form object and display its elements
 *
 * @since 2.6.1
 *
 * @author FitoussiEyal
 */
class GMW_Form extends GMW_Form_Core {

	/**
	 * To be used with pagination and per page features
	 *
	 * @var string
	 */
	public $paged_name = 'paged';

	/**
	 * [$show_results description]
	 *
	 * @var boolean
	 */
	public $show_results = false;

	/**
	 * Trigger map once the shortcode done
	 *
	 * @var boolean
	 */
	public $render_map = false;

	/**
	 * Set default form values.
	 *
	 * @since 4.0
	 */
	public function set_default_values() {

		// Get current page slug. Home page and singular slug is different than other pages.
		$page_name = ( is_front_page() || is_single() ) ? 'page' : $this->paged_name;

		$this->form['paged_name'] = $page_name;
		$this->form['paged']      = get_query_var( $page_name ) ? get_query_var( $page_name ) : 1;

		// check if form submitted.
		if ( isset( $_GET[ $this->url_px . 'form' ] ) && isset( $_GET[ $this->url_px . 'action' ] ) && 'fs' === $_GET[ $this->url_px . 'action' ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, CSRF ok.

			$this->form['submitted']       = true;
			$this->form['form_values']     = $this->get_form_values();
			$this->form['map_enabled']     = '' === $this->form['form_submission']['display_map'] ? false : true;
			$this->form['map_usage']       = $this->form['form_submission']['display_map'];
			$this->form['results_enabled'] = $this->form['form_submission']['display_results'];

			// otherwise check if page load results is set.
		} elseif ( $this->form['page_load_results']['enabled'] ) {

			$this->form['page_load_action'] = true;
			$this->form['form_values']      = $this->get_form_values();
			$this->form['map_enabled']      = '' === $this->form['page_load_results']['display_map'] ? false : true;
			$this->form['map_usage']        = $this->form['page_load_results']['display_map'];
			$this->form['results_enabled']  = $this->form['page_load_results']['display_results'];
		}

		// Deprecated. Use results enabled instead.
		$this->form['display_list'] = $this->form['results_enabled']; // Deprecated.

		// Temporary fix for custom search form folders that use the argument $gmw['search_results']['results_page'] directly.
		if ( ! empty( $this->form['search_results']['results_page'] ) && 'disabled' === $this->form['search_results']['results_page'] ) {
			$this->form['search_results']['results_page'] = '';
		}

		// for older version. to prevent PHP warnings.
		$this->form['search_results']['results_page'] = $this->form['form_submission']['results_page'];
		$this->form['search_results']['display_map']  = $this->form['map_usage'];

		// phpcs:disable.
		/* temporary to support previous version of template files ( will be removed ) */
		/*if ( function_exists( 'gmw_3_deprecated_form_settings' ) ) {
			$this->form = gmw_3_deprecated_form_settings( $this->form );
		}*/
		// phpcs:enable.

		$this->load_info_window_templates = apply_filters( 'gmw_load_info_window_templates', $this->load_info_window_templates, $this->form, $this );

		// Disable standard info-window content if AJAX info-window is enabled.
		if ( $this->load_info_window_templates ) {
			$this->get_info_window_content = false;
		}

		$this->get_info_window_content = apply_filters( 'gmw_form_get_info_window_content', $this->get_info_window_content, $this->form, $this );
	}

	/**
	 * Get submitted form values from URL
	 *
	 * @return [type] [description]
	 */
	public function get_form_values() {

		$qs = isset( $_SERVER['QUERY_STRING'] ) ? wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore: CSRF ok, sanitization ok.

		return gmw_get_form_values( $this->url_px, wp_unslash( $qs ) );
	}

	/**
	 * Get search results page.
	 *
	 * DEPRECATED ( since v4.0 ).
	 *
	 * Use the function gmw_form_results_page( $gmw ); in the template file directly instead.
	 *
	 * @return [type] [description]
	 */
	public function get_results_page() {
		return gmw_get_form_results_page( $this->form );
	}

	/**
	 * Get search results page.
	 *
	 * DEPRECATED ( since v4.0 ).
	 *
	 * Use the function gmw_form_class( $element, $gmw ); in the template file directly.
	 *
	 * @param string $type element type.
	 *
	 * @return [type] [description]
	 */
	public function get_class_attr( $type = 'form_wrap' ) {

		$type = 'results_wrap' === $type ? 'results_wrapper' : 'form_wrapper';

		return gmw_get_form_class( $type, $this->form );
	}

	/**
	 * Verify some data before running the search query.
	 *
	 * @return void
	 */
	public function pre_search_query() {

		$this->form = apply_filters( 'gmw_pre_search_query_args', $this->form, $this );

		// run search query on form submission or page load results.
		if ( $this->form['submitted'] || $this->form['page_load_action'] ) {

			// on page load results.
			if ( $this->form['page_load_action'] ) {

				$this->get_page_load_values();

				// Otherwise, on form submission, make sure that the form that was submitted is the one we query and display.
			} elseif ( absint( $this->form['ID'] ) === absint( $this->form['form_values']['form'] ) ) {

				$this->get_form_submission_values();

				// otherwise abort.
			} else {

				return;
			}

			do_action( 'gmw_form_before_search_query', $this->form, $this );

			// Execute some location tasks.
			add_action( 'gmw_the_object_location', array( $this, 'the_location' ), 10, 2 );

			// Temporary. For older template files that do not have the action above. To be removed.
			add_action( 'gmw_search_results_loop_item_start', array( $this, 'the_location' ), 10, 2 );

			// run the search query using child class.
			$results = $this->search_query();

			do_action( 'gmw_form_after_search_query', $this->form, $this );

			$this->form['has_locations'] = ! empty( $results ) ? true : false;

			// generate map if needed.
			if ( $this->form['map_enabled'] && ( $this->form['has_locations'] || $this->form['no_results_map_enabled'] ) ) {
				$this->render_map = true;
			}

			// load main JavaScript and Google APIs.
			if ( ! wp_script_is( 'gmw', 'enqueued' ) ) {
				wp_enqueue_script( 'gmw' );
			}

			$this->show_results = true;

			// Otherwise, do something custom.
		} else {

			do_action( 'gmw_main_shortcode_custom_function', $this->form, $this );
			do_action( 'gmw_' . $this->form['prefix'] . '_main_shortcode_custom_function', $this->form, $this );
		}
	}

	/**
	 * Get page load form values.
	 *
	 * Generate some data on page load
	 *
	 * before search query takes place
	 *
	 * @version 3.0
	 */
	public function get_page_load_values() {

		// get form values.
		$form_values                = $this->form['form_values'];
		$page_load_options          = $this->form['page_load_results'];
		$this->form['address']      = '';
		$this->form['org_address']  = '';
		$this->form['per_page']     = ! empty( $form_values['per_page'] ) ? $form_values['per_page'] : current( explode( ',', $page_load_options['per_page'] ) );
		$this->form['get_per_page'] = $this->form['per_page']; // Deprecated.
		$this->form['units_array']  = ! empty( $page_load_options['units'] ) ? gmw_get_units_array( $page_load_options['units'] ) : gmw_get_units_array( 'imperial' );

		if ( ! empty( $page_load_options['orderby'] ) ) {
			$this->form['orderby'] = $page_load_options['orderby'];
		}

		// phpcs:disable.
		// Look for page page in submitted values.
		/*if ( isset( $this->form['per_page'] ) ) {

			$this->form['per_page'] = $form_values['per_page'];

			// Otherwise, look for default value in form settings.
		} elseif ( isset( $this->form['search_results']['per_page'] ) ) {

			$this->form['per_page'] = current( explode( ',', $this->form['search_results']['per_page'] ) );
		}*/
		// phpcs:enable.

		$this->form['get_per_page'] = $this->form['per_page']; // get_per_page deprecated.

		// filter the form value before query.
		$this->form = apply_filters( 'gmw_page_load_results_before_results', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_page_load_results_before_results", $this->form, $this );
	}

	/**
	 * Form_submission
	 *
	 * Generate some data on form submitted
	 *
	 * before search query takes place
	 *
	 * @version 3.0
	 */
	public function get_form_submission_values() {

		if ( ! empty(  $this->form['form_submission']['orderby'] ) ) {
			$this->form['orderby'] = $this->form['form_submission']['orderby'];
		}

		// get form values.
		$form_values = $this->form['form_values'];

		$this->form['radius']      = isset( $form_values['distance'] ) ? $form_values['distance'] : 500;
		$this->form['address']     = ( isset( $form_values['address'] ) && array_filter( $form_values['address'] ) ) ? implode( ' ', $form_values['address'] ) : '';
		$this->form['org_address'] = $this->form['address'];
		$this->form['units']       = isset( $form_values['units'] ) ? $form_values['units'] : 'imperial';
		$this->form['units_array'] = gmw_get_units_array( $this->form['units'] );

		// Look for page page in submitted values.
		if ( isset( $form_values['per_page'] ) ) {

			$this->form['per_page'] = $form_values['per_page'];

			// Otherwise, look for default value in form settings.
		} elseif ( isset( $this->form['search_results']['per_page'] ) ) {

			$this->form['per_page'] = current( explode( ',', $this->form['search_results']['per_page'] ) );
		}

		$this->form['get_per_page'] = $this->form['per_page']; // get_per_page deprecated.

		// Get lat/lng if exist in URL.
		if ( ! empty( $form_values['lat'] ) && ! empty( $form_values['lng'] ) ) {

			$this->form['lat'] = $form_values['lat'];
			$this->form['lng'] = $form_values['lng'];

			// Otherwise look for an address to geocode.
		} elseif ( ! empty( $this->form['address'] ) ) {

			$this->form['location'] = $this->geocode( $this->form['address'] );

			// if geocode was unsuccessful return error message.
			if ( -1 === $this->form['location']['lat'] ) {

				$this->form['lat'] = false;
				$this->form['lat'] = false;

			} else {

				$this->form['lat'] = $this->form['location']['lat'];
				$this->form['lng'] = $this->form['location']['lng'];
			}
		}

		// filter the form values before running search query.
		$this->form = apply_filters( 'gmw_form_submitted_before_results', $this->form, $this );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_form_submitted_before_results", $this->form, $this );
	}

	/**
	 * Display search form.
	 *
	 * @return void
	 */
	public function search_form() {

		// Enable/disable form filter.
		if ( apply_filters( "gmw_{$this->form['ID']}_disable_search_form", false, $this ) ) {
			return;
		}

		// Verify search form tempalte.
		if ( empty( $this->form['search_form']['form_template'] ) || 'disabled' === $this->form['search_form']['form_template'] ) {
			return;
		}

		if ( ! gmw_verify_template_file_requirement( $this->form['search_form']['form_template'] ) ) {
			return;
		}

		do_action( 'gmw_before_search_form', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_before_search_form", $this->form, $this );

		$template = $this->get_template_file( 'search_form' );
		$gmw      = $this->form;
		$gmw_form = $this;

		// Temporary fix for custom search form folders that use the argument $gmw['search_results']['results_page'] directly.
		if ( ! empty( $gmw['search_results']['results_page'] ) && 'disabled' === $gmw['search_results']['results_page'] ) {
			$gmw['search_results']['results_page'] = '';
		}

		include $template['content_path'];

		do_action( 'gmw_after_search_form', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_after_search_form", $this->form, $this );

		// load main JavaScript file.
		if ( ! wp_script_is( 'gmw', 'enqueued' ) ) {
			wp_enqueue_script( 'gmw' );
		}
	}

	/**
	 * Display the form elements.
	 *
	 * @return void
	 */
	public function output() {

		// do something before the output.
		do_action( 'gmw_shortcode_start', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_shortcode_start", $this->form, $this );

		// if using the "elements" shortcode attribute to display the form.
		if ( 'form' === $this->form['current_element'] && ! empty( $this->form['elements'] ) ) {

			if ( in_array( 'map', $this->form['elements'], true ) ) {
				$this->form['map_usage'] = 'shortcode';
			}

			if ( in_array( 'search_results', $this->form['elements'], true ) ) {
				$this->form['results_enabled'] = true;
				$this->form['display_list']    = true;
			} else {
				$this->form['results_enabled'] = true;
				$this->form['display_list']    = false;
			}

			// loop through and generate the elements.
			foreach ( $this->form['elements'] as $element ) {

				if ( ! in_array( $element, array( 'search_form', 'map', 'search_results' ), true ) ) {
					continue;
				}

				if ( method_exists( $this, $element ) ) {

					if ( 'search_results' === $element || ( 'map' === $element && ! $this->form['results_enabled'] ) ) {
						$this->pre_search_query();
					}

					$this->$element();
				}
			}

			// otherwise, generate in normal order.
		} else {

			// display search form.
			if ( 'search_form' === $this->form['current_element'] || 'form' === $this->form['current_element'] ) {
				$this->search_form();
			}

			// display map using shortcode.
			if ( 'map' === $this->form['current_element'] && 'shortcode' === $this->form['map_usage'] ) {

				$this->map();

				// When list of results is disabled.
				if ( ! $this->form['results_enabled'] ) {

					$this->pre_search_query();

					// If locations found.
					if ( $this->form['has_locations'] ) {

						// Execute the object loop in order to generate the data for the map.
						$this->object_loop();
					}
				}
			}

			// display search results.
			if ( $this->form['results_enabled'] && in_array( $this->form['current_element'], array( 'form', 'search_results' ), true ) ) {

				$this->pre_search_query();

				if ( $this->show_results ) {
					$this->search_results();
				}
			}
		}

		if ( $this->render_map ) {
			$this->generate_map();
		}

		// Remove actions.
		remove_action( 'gmw_the_object_location', array( $this, 'the_location' ), 10, 2 );
		remove_action( 'gmw_search_results_loop_item_start', array( $this, 'the_location' ), 10, 2 );

		// do something after the output.
		do_action( 'gmw_shortcode_end', $this->form, $this );
		do_action( "gmw_{$this->form['prefix']}_shortcode_end", $this->form, $this );
	}
}
