<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * GMW class.
 */
class GMW {

	/**
	 * gmw options
	 * @var array
	 */
	public $settings;

	/**
	 * gmw forms
	 * @var array
	 */
	protected $forms;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $form ) {
		$this->settings = get_option( 'gmw_options' );
		$this->form     = apply_filters( 'gmw_main_shortcode_form_args', $form );
	}
	 
	/**
	 * When form submitted
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	public function form_submitted() {
		
		//$this->form['org_address'] = ( isset(  $_GET['gmw_address'] ) && array_filter( $_GET['gmw_address'] ) ) ? str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ) : '';
		
		$this->form['radius'] 		= ( isset( $_GET[$this->form['url_px'].'distance'] ) ) ? $_GET[$this->form['url_px'].'distance'] : 500;	
		$this->form['org_address']  = ( isset( $_GET[$this->form['url_px'].'address'] ) && array_filter( $_GET[$this->form['url_px'].'address'] ) ) ? implode( ' ', $_GET[$this->form['url_px'].'address'] ) : '';
		$per_page 					= ( isset( $this->form['search_results']['per_page'] ) ) ? current( explode( ",", $this->form['search_results']['per_page'] ) ) : -1;
		$this->form['get_per_page'] = ( isset( $_GET[ $this->form['url_px'].'per_page' ] ) ) ? $_GET[ $this->form['url_px'].'per_page' ] : $per_page;
		$this->form['units_array']  = gmw_get_units_array( isset( $_GET[ $this->form['url_px'].'units' ] ) ? $_GET[ $this->form['url_px'].'units' ] : 'imperial' );
						 
		//otherwise if lat/lng exist in URL then use that
		if ( !empty( $_GET[$this->form['url_px'].'lat'] ) && !empty( $_GET[$this->form['url_px'].'lng'] ) && !empty( $_GET[$this->form['url_px'].'lng'] )) {

			$this->form['your_lat'] = $_GET[$this->form['url_px'].'lat'];
			$this->form['your_lng'] = $_GET[$this->form['url_px'].'lng'];
			 
		//return $this->results();
		} elseif ( !empty( $this->form['org_address'] ) ) {
	
			$this->form['location'] = GEO_my_WP::geocoder( $this->form['org_address'] );

			//if geocode was unsuccessful return error message
			if ( isset( $this->form['location']['error'] ) ) {

				return;
			} else {
				
				$this->form['your_lat'] = $this->form['location']['lat'];
				$this->form['your_lng'] = $this->form['location']['lng'];
			}
		} else {
			
			$this->form['org_address'] = '';
		}
		
		//filter the form values before running search query
		$this->form = apply_filters( "gmw_form_submitted_before_results", 						  $this->form );
		$this->form = apply_filters( "gmw_form_submitted_before_results_{$this->form['ID']}", 	  $this->form );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_form_submitted_before_results", $this->form );
		
		return $this->results();
	}

	/**
	 * When displaying auto results
	 */
	public function auto_results() {

		//per page from settings
		$this->form['get_per_page'] = ( !empty( $_GET[$this->form['url_px'].'per_page'] ) ) ? $_GET[$this->form['url_px'].'per_page'] : current( explode( ",", $this->form['search_results']['per_page'] ) );

		//show auto-results based on user's location
		if ( !empty( $this->form['user_position'] ) && isset( $this->form['search_results']['auto_search']['on'] ) ) {
			
			// get user's current location exsit
			$this->form['org_address'] 	= $this->form['user_position']['address'];
			$this->form['your_lat'] 	= $this->form['user_position']['lat'];
			$this->form['your_lng'] 	= $this->form['user_position']['lng'];
			$this->form['radius'] 		= $this->form['search_results']['auto_search']['radius'];
			$this->form['units_array']  = gmw_get_units_array( $this->form['search_results']['auto_search']['units'] );
			
		//otherwise show all results
		} elseif ( isset( $this->form['search_results']['auto_all_results'] ) ) {

			//pass empty address to display all results
			$this->form['org_address'] = '';
		} else {
			return;
		}
			
		$this->form = apply_filters( "gmw_auto_results_before_results", 						$this->form );
		$this->form = apply_filters( "gmw_auto_results_before_results_{$this->form['ID']}", 	$this->form );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_auto_results_before_results", $this->form );

		$this->results();
	}

	/**
	 * When displaying results on page load
	 *
	 * @since 2.5
	 *
	 */
	public function page_load_results() {

		$page_load_options							 = $this->form['page_load_results'];
		$this->form['org_address']  				 = '';
		$this->form['get_per_page'] 				 = ( !empty( $_GET[$this->form['url_px'].'per_page'] ) ) ? $_GET[$this->form['url_px'].'per_page'] : current( explode( ",", $page_load_options['per_page'] ) );
		$this->form['radius'] 						 = ( !empty( $page_load_options['radius'] ) ) ? $page_load_options['radius'] : 200;
		$this->form['search_results']['display_map'] = $page_load_options['display_map'];
		$this->form['units_array']  				 = gmw_get_units_array( $this->form['page_load_results']['units'] );

		//temporary solution - this part needs improvment. 
		//need to change all kind of display_? options to the one global name such as results_list.
		//to easier control the outcome
		if ( isset( $page_load_options['display_posts'] ) ) {
			
			$this->form['search_results']['display_posts']    = true;
			$this->form['search_results']['display_members']  = true;
			$this->form['search_results']['display_groups']   = true;
			$this->form['search_results']['display_users']    = true;
		} else {
			unset( 
				$this->form['search_results']['display_posts'],
				$this->form['search_results']['display_groups'],
				$this->form['search_results']['display_members'],
				$this->form['search_results']['display_users']
			);
		}

		//display results based on user's current location
		if ( isset( $page_load_options['user_location'] ) && !empty( $this->form['user_position'] ) ) {

			// get user's current location
			$this->form['org_address'] 	= $this->form['user_position']['address'];
			$this->form['your_lat'] 	= $this->form['user_position']['lat'];
			$this->form['your_lng'] 	= $this->form['user_position']['lng'];
		
		//if no user's position check for address filter
		} elseif ( !empty( $page_load_options['address_filter'] ) ) {

			// get user's current location exsit
			$this->form['org_address'] 	= sanitize_text_field( $page_load_options['address_filter'] );
			$this->form['location'] 	= GEO_my_WP::geocoder( $this->form['org_address'] );

			//if geocode was unsuccessful return error message
			if ( isset( $this->form['location']['error'] ) ) {

				//return $this->no_results( $this->form['location']['error'] );
				return;
			} else {
				$this->form['your_lat'] = $this->form['location']['lat'];
				$this->form['your_lng'] = $this->form['location']['lng'];
			}
		}
		 
		$this->form = apply_filters( "gmw_page_load_results_before_results", 						 $this->form );
		$this->form = apply_filters( "gmw_page_load_results_before_results_{$this->form['ID']}", 	 $this->form );
		$this->form = apply_filters( "gmw_{$this->form['prefix']}_page_load_results_before_results", $this->form );

		$this->results();
	}
	
	/**
	 * 
	 * DEPRECATED needs to be removed in the future
	 * Display search results template file
	 *
	 */
	public function search_results() {
		return self::results_template();
	}
	
	/**
	 * Display search results template file
	 */
	public function results_template() {
		
		$sResults = $this->form['search_results']['results_template'];	
		$folders  = apply_filters( 'gmw_search_results_folder', array(
				'pt'  => array(
						'url'	 => GMW_URL.'/plugins/posts/search-results/',
						'path'	 => GMW_PATH.'/plugins/posts/search-results/',
						'custom' => 'posts/search-results/'
				),		
				'fl'  => array(
						'url'	 => GMW_URL.'/plugins/friends/search-results/',
						'path'	 => GMW_PATH.'/plugins/friends/search-results/',
						'custom' => 'friends/search-results/'
				),
		), $this->form );
		
		//Load custom search form and css from child/theme folder
		if ( strpos( $sResults, 'custom_' ) !== false ) {
		
			$sResults  		= str_replace( 'custom_', '', $sResults );
			$style_title 	= "gmw-{$this->form['ID']}-{$this->form['prefix']}-search-results-{$sResults}";
			$style_url 	 	= get_stylesheet_directory_uri()."/geo-my-wp/{$folders[$this->form['prefix']]['custom']}{$sResults}/css/style.css";
			$content_path 	= STYLESHEETPATH . "/geo-my-wp/{$folders[$this->form['prefix']]['custom']}{$sResults}/results.php";
		
		} else {
			$style_title = "gmw-{$this->form['ID']}-{$this->form['prefix']}-search-results-{$sResults}";
			$style_url 	  = $folders[$this->form['prefix']]['url'].$sResults.'/css/style.css';
			$content_path = $folders[$this->form['prefix']]['path'].$sResults.'/results.php';
		}
		
		if ( !wp_style_is( $style_title, 'enqueued' ) ) {
			wp_enqueue_style( $style_title, $style_url );
		}
		
		return $content_path;				
	}
	
	/**
	 * trigger the map scripts
	 */
	public function map_element() {

		if ( !apply_filters( 'gmw_trigger_map', true ) )
			return;
				
		$mapArgs = array(
				'mapId' 	 		=> $this->form['ID'],
				'mapType'			=> $this->form['addon'],
				'prefix'			=> $this->form['prefix'],
				'form' 		 		=> $this->form,
				'locations'			=> $this->form['results'],
				'infoWindowType'	=> ( !empty( $this->form['info_window']['iw_type'] ) ) ? $this->form['info_window']['iw_type'] : 'normal',
				'zoomLevel'			=> $this->form['results_map']['zoom_level'],
				'mapOptions'		=> array(
						'mapTypeId'		=> $this->form['results_map']['map_type']
				),
				'userPosition'		=> array(
						'lat'		=> $this->form['your_lat'],
						'lng'		=> $this->form['your_lng'],
						'address' 	=> $this->form['org_address'],
						'mapIcon'	=> $this->form['ul_icon'],
						'iwContent' => $this->form['labels']['info_window']['your_location'],
						'iwOpen'	=> ( !empty( $this->form['results_map']['yl_icon'] ) ) ? true : false
				),
				'markersDisplay'	=> ( !empty( $this->form['results_map']['markers_display'] ) ) ? $this->form['results_map']['markers_display'] : false,
				'draggableWindow'	=> ( isset( $this->form['info_window']['draggable_use'] ) ) ? true : false
		);
		
		$mapArgs = apply_filters( "gmw_form_map_element_args", 					   $mapArgs, $this->form );
		$mapArgs = apply_filters( "gmw_form_map_element_args_{$this->form['ID']}", $mapArgs, $this->form );
		$mapArgs = apply_filters( "gmw_{$this->form['prefix']}_form_map_element_args",  $mapArgs, $this->form );

		do_action( "gmw_form_map_element", $this->form, $mapArgs );

		gmw_new_map_element( $mapArgs );
	}
	
	/**
	 * No results message
	 * @param $gmw
	 * @param $gmw_options
	 */
	function no_results( $message ) {

		if ( !empty( $this->form['location']['error'] ) ) {
			$message = $this->form['location']['error'];
		
		} elseif ( empty( $message ) ) {
			$message = $this->form['labels']['search_results'][$this->form['prefix'].'_no_results'];
		}
		
		$class = ( isset( $this->form['location']['error'] ) ) ? 'gmw-geocode-error' : '';
		
		do_action( "gmw_before_no_results", 						$this->form  );
		do_action( "gmw_before_no_results_{$this->form['ID']}", 	$this->form  );
		do_action( "gmw_{$this->form['prefix']}_before_no_results", $this->form  );
		
		//use custom template file for no results
		if ( apply_filters( 'gmw_no_results_template_file_enabled', false ) ) {
			
			if ( $this->form['prefix'] == 'pt' ) {
				$folder = 'posts';
			} elseif ( $this->form['prefix'] == 'fl' ) {
				$folder = 'friends';
			} elseif ( $this->form['prefix'] == 'gl' ) {
				$folder = 'groups';
			} elseif ( $this->form['prefix'] == 'ug' ) {
				$folder = 'users';
			}
			
			//include custom template if exists
			if ( file_exists( STYLESHEETPATH."/geo-my-wp/{$folder}/no-results/default/no-results.php" ) ) {
				$template_file = STYLESHEETPATH."/geo-my-wp/{$folder}/no-results/default/no-results.php";
			} else {
				//otherwise pull template from plugin folder
				$template_file = GMW_PATH."/plugins/{$folder}/no-results/default/no-results.php";
			}

			$gmw 	 = $this->form;
			$message = apply_filters( "gmw_no_results_template_message", $message, $this->form );

			include( $template_file );

		} else {
			
			$message    = apply_filters( "gmw_no_results_message", $message, $this->form );
			$no_results = "<div class=\"gmw-no-results-wrapper gmw-{$this->form['prefix']}-no-results-wrapper {$class}\"><p>{$message}</p></div>";
		
			$no_results = apply_filters( "gmw_no_results_message", 						   $no_results, $this->form, $message );
			$no_results = apply_filters( "gmw_{$this->form['prefix']}_no_results_message", $no_results, $this->form, $message );
	
			echo $no_results;
		}
		
		do_action( "gmw_after_no_results", 						   $this->form  );
		do_action( "gmw_after_no_results_{$this->form['ID']}", 	   $this->form  );
		do_action( "gmw_{$this->form['prefix']}_after_no_results", $this->form  );
	}

	/**
	 * Output Main Shortcode
	 *
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	public function output() {
				
		do_action( "gmw_results_shortcode_start", 						  $this->form, $this->settings );
		do_action( "gmw_{$this->form['prefix']}_results_shortcode_start", $this->form, $this->settings );
		do_action( "gmw_results_shortcode_start_{$this->form['ID']}", 	  $this->form, $this->settings );

		//form submission function
		if ( $this->form['query_type'] == 'form_submitted' )  {

			if ( $this->form['ID'] == absint( $_GET[$this->form['url_px'].'form'] ) ) {
					
				self::form_submitted();
					
				//display no results message
				if ( empty( $this->form['results'] ) ) {

					$this->no_results( false );

				//trigger map
				} elseif ( $this->form['search_results']['display_map'] != 'na' ) {

					$this->map_element();
				}
			}

		} elseif ( $this->form['query_type'] == 'auto_results' || $this->form['query_type'] == 'page_load_results' ) {
			
			//do auto results function
			if ( $this->form['query_type'] == 'auto_results' ) {

				self::auto_results();

			//page load results function
			} else {

				self::page_load_results();
			}
			
			//trigger map
			if ( !empty( $this->form['results'] ) && $this->form['search_results']['display_map'] != 'na' ) {
				$this->map_element();
			}
		
		//do some custom functions if nothing above worked
		} else {
			
			do_action( 'gmw_main_shortcode_custom_function', $this );
			do_action( 'gmw_'.$this->form['prefix'].'_main_shortcode_custom_function', $this );
		}
	
		do_action( "gmw_results_shortcode_end", 						$this->form, $this->settings );
		do_action( "gmw_{$this->form['prefix']}_results_shortcode_end", $this->form, $this->settings );
		do_action( "gmw_results_shortcode_end_{$this->form['ID']}", 	$this->form, $this->settings );
	}
}