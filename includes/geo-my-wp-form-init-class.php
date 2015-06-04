<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;
    
/**
 * GMW_Form_Init class
 * 
 * Create the form object and display its elements
 * 
 * @since 2.6.1
 * @author FitoussiEyal
 */
class GMW_Form_Init {

    /**
     * The form being displayed
     *
     * @since 2.6.1
     * @access public
     */
    public $form;
    
    /**
     * Construct 
     * @param unknown_type $form
     */
	function __construct( $form ) {

		//get geo my wp options
		global $gmw_options;

		/*
		 * check if form is already in cache. 
		 * We save already loaded forms in cache to save a bit of memomry 
		 * in the next time we load the same form. This can happen if we load 
		 * The serch form and search results or map of the same form but in separate elements.
		 */
		//wp_cache_add_non_persistent_groups( array( 'gmw_forms' ) );
		//$this->form = wp_cache_get( $form['ID'], 'gmw_forms' );
		$this->form = false;

		//get default form values
		if ( false === $this->form ) {
		
			$this->form 			  = $form;
			$this->form['query_type'] = $this->form['submitted']  = $this->form['page_load_results_trigger'] = $this->form['auto_results_trigger'] = false;	
			$user_position  		  = false;

			if ( !empty( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lng'] ) && !empty( $_COOKIE['gmw_address'] ) ) {
				$user_position = array(
						'exists'	=> true,
						'address' 	=> urldecode( $_COOKIE['gmw_address'] ),
						'lat'		=> urldecode( $_COOKIE['gmw_lat'] ),
						'lng'		=> urldecode( $_COOKIE['gmw_lng'] ),
						'map_icon'	=> ( !empty( $this->form['results_map']['your_location_icon'] ) ) ? $this->form['results_map']['your_location_icon'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
				);
			}

			$form_ext = array(
					'ajaxurl'		=> GMW_AJAX,
					'user_position'	=> $user_position,
					//should be remove in the future -start - replaced by user_position array
					'ul_address'	=> ( !empty( $_COOKIE['gmw_address'] ) ) ? urldecode( $_COOKIE['gmw_address'] ) : false,
					'ul_lat' 		=> ( !empty( $_COOKIE['gmw_lat'] ) ) 	 ? urldecode( $_COOKIE['gmw_lat'] ) 	: false,
					'ul_lng' 		=> ( !empty( $_COOKIE['gmw_lng'] ) ) 	 ? urldecode( $_COOKIE['gmw_lng'] ) 	: false,
					'ul_icon' 		=> ( !empty( $this->form['results_map']['your_location_icon'] ) ) ? $this->form['results_map']['your_location_icon'] : 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
					//should be removed - end

					'your_lat'		=> false,
					'your_lng'		=> false,
					'org_address'	=> false,
					'region' 		=> $gmw_options['general_settings']['country_code'],
					'language'		=> $gmw_options['general_settings']['language_code'],
					'get_per_page' 	=> false,
					'units_array' 	=> false,
					'radius'		=> false,
					'paged'			=> 0,
					'per_page'		=> -1,
					'is_mobile'		=> ( wp_is_mobile() ) ? true : false,
					'gif_loader' 	=> GMW_URL.'/assets/images/gmw-loader.gif',
					'map_loader' 	=> GMW_URL.'/assets/images/map-loader.gif',
					'labels'		=> gmw_form_set_labels( $this->form ),
					'results'		=> array(),
			);
			
			//extand form
			$this->form = array_merge( $this->form, $form_ext );
			
			//Get current page number
			$paged 				  = ( is_front_page() ) ? 'page' : 'paged';
			$this->form['paged']  = ( get_query_var( $paged ) ) ? get_query_var( $paged ) : 1;

			//wp_cache_set( 'gmw_form_id_'.$this->form['ID'], $this->form, 'gmw_forms' );
		}

		//get the element_trigger tand in_widget from the original form pass into the class
		$this->form['element_triggered'] = $form['element_triggered'];
		$this->form['in_widget'] 		 = ( !empty( $form['params']['widget'] ) ) ? true : false;

		//if results page is set
		if ( !empty( $this->form['search_results']['results_page'] ) ) {

			$this->form['search_results']['results_page'] = get_permalink( $this->form['search_results']['results_page'] );

		//if this is a widget and results page is not set in the shorcode settings we will get the results page from the main settings
		} elseif ( !empty( $this->form['in_widget'] ) ) {
			$this->form['search_results']['results_page'] = get_permalink( $gmw_options['general_settings']['results_page'] );
		} else {
			$this->form['search_results']['results_page'] = false;
		}

		//check if form submitted
		if ( !empty( $_GET['action'] ) && $_GET['action'] == $this->form['url_px'].'post' && !empty( $_GET[$this->form['url_px'].'form'] ) ) {

			$this->form['query_type'] = 'form_submitted';
			$this->form['submitted']  = true;
			$this->form['form_submit_trigger'] = true;

		} elseif ( $this->form['element_triggered'] != 'results_page' ) {
			
			//check if auto results 
			if ( ( !empty( $this->form['search_results']['auto_search']['on'] ) || !empty( $this->form['search_results']['auto_all_results'] ) ) ) {
		
				$this->form['query_type'] 			= 'auto_results';
				$this->form['auto_results_trigger'] = true;

			//check for page load results
			} elseif ( !empty( $this->form['page_load_results']['all_locations'] ) ) {
					
				$this->form['query_type'] 				 = 'page_load_results';
				$this->form['page_load_results_trigger'] = true;
			}		
		} 

		//modify form values
		$this->form = apply_filters( "gmw_default_form_values", 						$this->form );
		$this->form = apply_filters( "gmw_default_form_values_{$this->form['ID']}", 	$this->form );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_default_form_values", $this->form );
	}

	/**
	 * Display the search form
	 * @access public
	 */
	public function search_form() {

		if ( apply_filters( "gmw_{$this->form['ID']}_search_form_disabled", false ) ) 
			return;
		
		if ( empty( $this->form['search_form']['form_template'] ) )
			return;
		
		if ( $this->form['search_form']['form_template'] == 'no_form' )
			return;
		
		//display search form
		gmw_search_form( $this->form );		
	}

	/**
	 * Display the map
	 * @access public
	 */
	public function map() {

		if ( $this->form['submitted'] && $this->form['search_results']['display_map'] != "shortcode" )
			return;
		
		if ( $this->form['page_load_results_trigger'] && $this->form['page_load_results']['display_map'] != "shortcode" )
			return;
		
		if ( $this->form['auto_results_trigger'] && $this->form['search_results']['display_map'] != "shortcode" )
			return;
		
		//let the map function know that we are coming from a shortcode.
		$this->form['map_shortcode'] = true;
		
		//display the map
		gmw_results_map( $this->form );
	}

	/**
	 * Display the search results
	 * @access public
	 *
	 */
	public function results() {
	
		//get the class name name of the add-on need to be queried
		$class_name = 'GMW_'.strtoupper($this->form['prefix']).'_Search_Query';
						
		//temporary solution until add-on will be updated
		if ( defined( 'GMW_UG_PATH' ) && $this->form['prefix'] == 'ug' ) {
			include_once( GMW_UG_PATH . '/includes/gmw-ug-search-query-class.php' );
		}
			
		//check if the class exists
		if ( class_exists( $class_name ) ) {
			
			$search_query = new $class_name( $this->form );
			$search_query->output();
			
		} else {
			do_action( "gmw_{$this->form['prefix']}_results_shortcode", $this->form );
		}
				
		do_action( "gmw_results_shortcode", 				    $this->form );
		do_action( "gmw_results_shortcode_{$this->form['ID']}", $this->form );	
	}

	/**
	 * Display the form elements
	 */
	public function display() {

		do_action( "gmw_shortcode_start", 						  $this->form );
		do_action( "gmw_shortcode_start_{$this->form['ID']}", 	  $this->form );
		do_action( "gmw_{$this->form['prefix']}_shortcode_start", $this->form );

		//display search form anywere on the page using the search_form shortcode
		if ( $this->form['element_triggered'] == 'search_form' || $this->form['element_triggered'] == 'form' ) {
			$this->search_form();
		}

		//display map using shortcode
		if ( $this->form['element_triggered'] == 'map' ) {			
			$this->map();
		}

		//display results anywere on the page using the shortcode
		if ( in_array( $this->form['element_triggered'], array( 'form', 'search_results', 'results_page' ) ) ) {
			$this->results();
		}

		do_action( "gmw_shortcode_end", 						$this->form );
		do_action( "gmw_shortcode_end_{$this->form['ID']}", 	$this->form );
		do_action( "gmw_{$this->form['prefix']}_shortcode_end", $this->form );
	}
}