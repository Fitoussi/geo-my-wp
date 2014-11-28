<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * GMW class.
 */
class GMW {

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $form ) {

		$this->settings 	  = get_option( 'gmw_options' );
		$this->form     	  = apply_filters( 'gmw_main_shortcode_form_args', $form, $this );

		self::gmw_output();
	}
	 
	/**
	 * When form submitted
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	public function form_submitted() {

		$this->form['radius'] 		= ( !empty( $_GET['gmw_distance'] ) ) ? $_GET['gmw_distance'] : 500;
		$this->form['org_address']  = ( isset(  $_GET['gmw_address'] ) && array_filter( $_GET['gmw_address'] ) ) ? str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ) : '';
		$per_page 					= ( isset(  $this->form['search_results']['per_page'] ) ) ? current( explode( ",", $this->form['search_results']['per_page'] ) ) : -1;
		$this->form['get_per_page'] = ( !empty( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : $per_page;
		 
		// distance units
		if ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == "imperial" ) {
			$this->form['units_array'] = array( 'radius' => 3959, 'name' => "mi", 'map_units' => "ptm", 'units' => 'imperial' );
		} else {
			$this->form['units_array'] = array( 'radius' => 6371, 'name' => "km", 'map_units' => 'ptk', 'units' => "metric" );
		}

		$this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_form_submitted_before_results' , $this->form );

		//if lat/lng exist then use them
		if ( $this->form['your_lat'] != false && $this->form['your_lng'] != false ) {
			 
			return $this->results();
			 
			//otherwise if lat/lng exist in URL then use that
		} elseif ( !empty( $_GET['gmw_lat'] ) && !empty( $_GET['gmw_lng'] ) ) {

			$this->form['your_lat'] = $_GET['gmw_lat'];
			$this->form['your_lng'] = $_GET['gmw_lng'];
			 
			return $this->results();
		}

		//if not lat/lng we will check for address and geocode it if exist
		if ( !empty( $this->form['org_address'] ) ) {
			 
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
		return $this->results();
	}

	/**
	 * When displaying auto results
	 *
	 */
	public function auto_results() {

		$this->form['auto_results_trigger'] = true;
		
		//per page from settings
		$this->form['get_per_page'] = ( !empty( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : current( explode( ",", $this->form['search_results']['per_page'] ) );

		if ( $this->form['ul_lat'] != false && $this->form['ul_lng'] != false && isset( $this->form['search_results']['auto_search']['on'] ) ) {
			// get user's current location exsit
			$this->form['org_address'] 	= $this->form['ul_address'];
			$this->form['your_lat'] 	= $this->form['ul_lat'];
			$this->form['your_lng'] 	= $this->form['ul_lng'];
			$this->form['radius'] 		= $this->form['search_results']['auto_search']['radius'];
			 
			if ( $this->form['search_results']['auto_search']['units'] == 'imperial' ) {
				$this->form['units_array'] = array( 'radius' => 3959, 'name' => "mi", 'map_units' => "ptm", 'units' => 'imperial' );
			} else {
				$this->form['units_array'] = array( 'radius' => 6371, 'name' => "km", 'map_units' => 'ptk', 'units' => "metric" );
			}
			 
		} elseif ( isset( $this->form['search_results']['auto_all_results'] ) ) {

			//otherwise leave it empty to show all results
			$this->form['org_address'] = '';
		} else {
			return;
		}
			
		$this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_auto_results_before_results' , $this->form );

		$this->results();
	}

	/**
	 * When displaying results on page load
	 *
	 * @since 2.5
	 *
	 */
	public function page_load_results() {

		$this->form['page_load_results_trigger']  	 	= true;
		$this->form['org_address']  				 	= '';
		$this->form['get_per_page'] 				 	= ( !empty( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : current( explode( ",", $this->form['page_load_results']['per_page'] ) );
		$this->form['radius'] 						 	= ( !empty( $this->form['page_load_results']['radius'] ) ) ? $this->form['page_load_results']['radius'] : 200;
		$this->form['search_results']['display_map'] 	= $this->form['page_load_results']['display_map'];
		 
		if ( isset( $this->form['page_load_results']['display_posts'] ) ) {
			$this->form['search_results']['display_posts'] = true;
		} else {
			unset( $this->form['search_results']['display_posts'] );
		}
		 
		if ( $this->form['page_load_results']['units'] == 'imperial' ) {
			$this->form['units_array'] = array( 'radius' => 3959, 'name' => "mi", 'map_units' => "ptm", 'units' => 'imperial' );
		} else {
			$this->form['units_array'] = array( 'radius' => 6371, 'name' => "km", 'map_units' => 'ptk', 'units' => "metric" );
		}
		 
		if ( isset( $this->form['page_load_results']['user_location'] ) && $this->form['ul_lat'] != false && $this->form['ul_lng'] != false ) {

			// get user's current location
			$this->form['org_address'] 	= $this->form['ul_address'];
			$this->form['your_lat'] 	= $this->form['ul_lat'];
			$this->form['your_lng'] 	= $this->form['ul_lng'];
				
		} elseif ( !empty( $this->form['page_load_results']['address_filter'] ) ) {

			// get user's current location exsit
			$this->form['org_address'] 	= $this->form['page_load_results']['address_filter'];
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
		 
		$this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_page_load_results_before_results' , $this->form );

		$this->results();
	}

	/**
	 * Display search results template file
	 */
	public function search_results() {
		
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
	public function trigger_map() {

		if ( !apply_filters( 'gmw_trigger_map', true ) )
			return;
		
		$this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_form_before_map_triggered', $this->form, $this->settings );
		$this->form = apply_filters( 'gmw_form_before_map_triggered', $this->form, $this->settings );
		
		do_action( 'gmw_'.$this->form['prefix'].'_before_map_triggered', $this->form, $this->settings );
		do_action( 'gmw_before_map_triggered', $this->form, $this->settings );
		
		wp_enqueue_script( 'gmw-map' );
		wp_localize_script( 'gmw-map', 'gmwForm', $this->form );	
		
		add_filter( 'gmw_trigger_map', '__return_false' );
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
			
		do_action( 'gmw_'.$this->form['prefix'].'_before_no_results', $this->form  );

		$no_results = '<div class="gmw-no-results-wrapper gmw-'.$this->form['prefix'].'-no-results-wrapper '.$class.'"><p>'.$message.'</p></div>';
		echo apply_filters( 'gmw_'.$this->form['prefix'].'_no_results_message', $no_results, $this->form, $message );

		do_action( 'gmw_'.$this->form['prefix'].'_after_no_results', $this->form );
	}

	/**
	 * Output Main Shortcode
	 *
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	public function gmw_output() {
				
		do_action( 'gmw_'.$this->form['prefix'].'_results_shortcode_start', $this->form, $this->settings );
				
		//form submission function
		if ( $this->form['submitted'] )  {

			if ( $this->form['ID'] == $_GET['gmw_form'] ) {
					
				self::form_submitted();
					
				//display no results message
				if ( empty( $this->form['results'] ) ) {

					$this->no_results( false );
				} elseif ( $this->form['search_results']['display_map'] != 'na' ) {

					$this->trigger_map();
				}
			}

		} elseif ( $this->form['element_triggered'] != 'results_page' ) {
			//auto results function
			if ( ( !empty( $this->form['search_results']['auto_search']['on'] ) || !empty( $this->form['search_results']['auto_all_results'] ) ) ) {
					
				self::auto_results();
				//page load function
			} elseif ( $this->form['page_load_results_trigger'] ) {
					
				self::page_load_results();
			}

			if ( !empty( $this->form['results'] ) && $this->form['search_results']['display_map'] != 'na' ) {
				$this->trigger_map();
			}
			//do some custom functions if nothing above worked
		} else {
			do_action( 'gmw_main_shortcode_custom_function', $this );
			do_action( 'gmw_'.$this->form['prefix'].'_main_shortcode_custom_function', $this );
		}
	
		do_action( 'gmw_'.$this->form['prefix'].'_results_shortcode_end', $this->form );
	}
}