<?php
/**
 * GEO my WP Posts Locator Class.
 *
 * The class queries posts based on location.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User to extend the Posts Locator Form classes.
 *
 * @since 4.0.
 */
trait GMW_Posts_Locator_Form_Trait {

	/**
	 * Pass an array of arguments to the search query.
	 *
	 * Arguments can be modified using the filter:
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_search_query_args', $this->form['query_args'], $this->form, $this );
	 *
	 * The filter can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->parse_query_args();
	 *
	 * @since 4.0
	 *
	 * @return [type] [description]
	 */
	public function get_query_args() {

		// get the post types from page load settings.
		if ( $this->form['page_load_action'] ) {

			$post_types = $this->form['page_load_results']['post_types'];

			// otherwise, on form submission.
		} else {

			// Check in submitted values.
			if ( ! empty( $this->form['form_values']['post'] ) && array_filter( $this->form['form_values']['post'] ) ) {

				$post_types = $this->form['form_values']['post'];

				// otherwise grab all the post types from the search form settings.
			} else {

				$post_types                        = ! empty( $this->form['search_form']['post_types'] ) ? $this->form['search_form']['post_types'] : array( 'post' );
				$this->form['form_values']['post'] = $post_types;
			}
		}

		$tax_query = array();

		// tax query can be disable if a custom query is needed.
		if ( apply_filters( 'gmw_enable_taxonomy_search_query', true, $this->form, $this ) ) {
			//$tax_args = ! empty( $this->form['form_values']['tax'] ) ? gmw_pt_get_tax_query_args( $this->form['form_values']['tax'], $this->form ) : array();
			$tax_query = gmw_generate_tax_query( $this->form );
		}

		$orderby = ! empty( $this->form['form_values']['sortby'] ) ? $this->form['form_values']['sortby'] : $this->form['orderby'];

		// query args.
		return array(
			'post_type'           => $post_types,
			'post_status'         => array( 'publish' ),
			'tax_query'           => $tax_query, // WPCS: slow query ok.
			'posts_per_page'      => ! empty( $this->form['per_page'] ) ? $this->form['per_page'] : -1,
			'paged'               => $this->form['paged'],
			'orderby'             => $orderby,
			'order'               => 'post_modified' === $orderby || 'post_date' === $orderby ? 'DESC' : 'ASC',
			'ignore_sticky_posts' => 1,
			// below we can save on performance when showing map only ( without the list of results ).
			'no_found_rows'       => $this->form['results_enabled'] ? false : true,
			'fields'              => '*',
		);
	}

	/**
	 * Execute the search query.
	 *
	 * There are various filters that can be used to filter the form object and the query before and after the search query takes place.
	 *
	 * The filters can be found in geo-my-wp/includes/class-gmw-base-form.php.
	 *
	 * in GMW_Form_Core->pre_search_query_hooks() and GMW_Form_Core->post_search_query_hooks();
	 *
	 * Pre search query filters:
	 *
	 * apply_filters( 'gmw_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['component'] . '_form_before_search_query', $this->form, $this );
	 *
	 * apply_filters( 'gmw_' . $this->form['prefix'] . '_form_before_search_query', $this->form, $this );
	 *
	 * Post search query filters:
	 *
	 * // Modify the form.
	 * $this->form  = apply_filters( 'gmw_' . $this->form['prefix'] . '_form_after_search_query', $this->form, $this );
	 *
	 * // Modify the search query.
	 * $this->query = apply_filters( 'gmw_' . $this->form['prefix'] . '_query_after_search_query', $this->query, $this->form, $this );
	 *
	 * You can also modify the posts clauses using the filter below which can be found in class-gmw-wp-query.php file.
	 *
	 * $clauses = apply_filters( 'gmw_posts_locator_query_clauses', $clauses, $gmw, $object );
	 *
	 * @author Eyal Fitoussi
	 *
	 * @since 4.0
	 */
	public function parse_search_query() {

		$this->query          = new GMW_WP_Query( $this->form['query_args'], $this->form );
		$this->query->request = null; // Remove for internal cache purposes.
	}

	/**
	 * Parse the search query results.
	 *
	 * @since 4.0
	 */
	public function parse_query_results() {

		if ( ! empty( $this->query->posts ) ) {

			$this->form['results']       = $this->query->posts;
			$this->form['results_count'] = count( $this->query->posts );
			$this->form['total_results'] = $this->query->found_posts;
			$this->form['max_pages']     = $this->query->max_num_pages;
		}
	}

	/**
	 * The posts loop.
	 *
	 * To be used when displaying map only without the results.
	 *
	 * In that case we need to run the loop in order to collect some data for the map.
	 *
	 * @since 4.0
	 *
	 * @param  boolean $include [description].
	 */
	public function object_loop( $include = false ) {

		// The variables are for AJAX forms deprecated template files. To be removed.
		// phpcs:disable.
		$gmw       = $this->form;
		$gmw_form  = $this;
		$gmw_query = $this->query;
		// phpcs:enable.

		while ( $this->query->have_posts() ) :

			$this->query->the_post();

			global $post;

			// This action is required. Do not remove.
			do_action( 'gmw_the_object_location', $post, $this->form );

			// For AJAX forms deprecated template files. To be removed.
			if ( $include ) {

				if ( empty( $this->form['search_results']['styles']['disable_single_item_template'] ) ) {
					include $this->form['results_template']['content_path'] . 'single-result.php';
				} else {
					do_action( 'gmw_search_results_single_item_template', $post, $this->form );
				}
			}

		endwhile;
	}

	/**
	 * Info window data
	 *
	 * @param  object $post the post object.
	 *
	 * @return array of arg
	 */
	public function get_info_window_args( $post ) {

		if ( isset( $this->form['info_window']['image'] ) && empty( $this->form['info_window']['image']['enabled'] ) ) {

			$image = false;

		} else {

			$args = array(
				'object_type'  => 'post',
				'object_id'    => $post->ID,
				'width'        => ! empty( $this->form['info_window']['image']['width'] ) ? $this->form['info_window']['image']['width'] : '150px',
				'height'       => ! empty( $this->form['info_window']['image']['height'] ) ? $this->form['info_window']['image']['height'] : 'auto',
				'no_image_url' => ! empty( $this->form['info_window']['image']['no_image_url'] ) ? $this->form['info_window']['image']['no_image_url'] : '',
				'permalink'    => false,
				'wrapper'      => false,
				'where'        => 'info_window',
			);

			$image = gmw_get_post_featured_image( $args, $post, $this->form );
		}

		return array(
			'prefix' => $this->prefix,
			'type'   => ! empty( $this->form['info_window']['iw_type'] ) ? $this->form['info_window']['iw_type'] : 'standard',
			'image'  => $image,
			'url'    => get_permalink( $post->ID ),
			'title'  => $post->post_title,
		);
	}
}

/**
 * Posts Locator form class.
 *
 * @package geo-my-wp
 */
class GMW_Posts_Locator_Form extends GMW_Form {

	/**
	 * Inherit search queries fromt Trait.
	 */
	use GMW_Posts_Locator_Form_Trait;
}
