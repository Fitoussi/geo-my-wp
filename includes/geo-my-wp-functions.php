<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

/**
 * GMW class.
 */
abstract class GMW {

    /**
     * abstract function results();
     * 
     * This is a must be function when extending the class
     * this function is where the results query is happening
     * 
     */
    abstract protected function results();

    /**
     * abstract function seaerch_form();
     *
     * This is a must be function when extending the class
     * this function loads the search form from the plugin/addon folder
     *
     */
    abstract protected function search_form();

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct( $form, $results ) {

        $this->settings 			= get_option( 'gmw_options' );
        
        $form['get_per_page'] 	= false;
        $form['units_array']  	= false;
        $form['your_lat']     	= false;
        $form['your_lng']     	= false;
        $form['radius']			= false;
        $form['org_address']	= false;
        $this->form     	    = apply_filters( 'gmw_main_shortcode_form_args', $form, $this->settings );
        
        self::gmw( $results );

    }

    /**
     * Main Shortcode
     * 
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function gmw( $results ) {
	
        do_action( 'gmw_' . $this->form['form_type'] . '_main_shortcode_start', $this->form, $this->settings );
        
        //load search form template
        if ( $results == false && isset( $this->form['search_form'] ) ) {
            
        	do_action( 'gmw_'.$this->form['prefix'].'_before_search_form', $this->form, $this->settings );
        	
        	$this->search_form();
        	
        	do_action( 'gmw_'.$this->form['prefix'].'_after_search_form', $this->form, $this->settings );
        	
        }
        
        // when form submitted
        if ( apply_filters( 'gmw_form_submitted_trigger', false, $this ) == true || ( isset( $_GET['action'] ) && $_GET['action'] == "gmw_post" && ( ( $results == true ) || ( $results == false && $this->form['ID'] == $_GET['gmw_form'] && $this->form['search_results']['results_page'] == false ) ) ) ) {

            self::form_submitted();

        // if auto results 
        } elseif ( empty( $this->form['search_results']['results_page'] ) && isset( $_COOKIE['gmw_lat'] ) && isset( $_COOKIE['gmw_lng'] ) && isset( $this->form['search_results']['auto_search']['on'] ) ) {

            self::auto_results();
		
        //do some custom functions if nothing above worked
        } else { 
        	do_action( 'gmw_main_shortcode_custom_function', $this );
        }

        do_action( 'gmw_' . $this->form['form_type'] . '_main_shortcode_end', $this->form );

    }

    /**
     * When form submitted
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function form_submitted() {
    	
        $this->form['radius'] = ( isset( $_GET['gmw_distance'] ) && !empty( $_GET['gmw_distance'] ) ) ? $_GET['gmw_distance'] : 500;
        
        // get the address
        $this->form['org_address'] = ( isset( $_GET['gmw_address'] ) && array_filter( $_GET['gmw_address'] ) ) ? str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ) : '';
        
        //per page
        $per_page = ( isset( $this->form['search_results']['per_page'] ) ) ? current( explode( ",", $this->form['search_results']['per_page'] ) ) : -1;
        $this->form['get_per_page'] = ( isset( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : $per_page;

        	
        // distance units 
        if ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == "imperial" ) {
            $this->form['units_array'] = array( 'radius' => 3959, 'name' => "Mi", 'map_units' => "ptm", 'units' => 'imperial' );
        } else {
            $this->form['units_array'] = array( 'radius' => 6371, 'name' => "Km", 'map_units' => 'ptk', 'units' => "metric" );
        }
        
        $_GET = apply_filters( 'gmw_modify_get_args', $_GET, $this->form );
        
        $this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_form_submitted_before_results' , $this->form );
        
        //if lat/lng exist then use them
        if ( $this->form['your_lat'] != false && $this->form['your_lng'] != false ) {
        	
        	return $this->results();
        	
        //otherwise if lat/lng exist in URL then use that
        } elseif ( isset( $_GET['gmw_lat'] ) && !empty( $_GET['gmw_lat'] ) && isset( $_GET['gmw_lng'] ) && !empty( $_GET['gmw_lng'] ) ) {

        	$this->form['your_lat'] = $_GET['gmw_lat'];
        	$this->form['your_lng'] = $_GET['gmw_lng'];
        	
        	return $this->results();
        	
        }
        
        //if not lat/lng we will check for address and geocode it if exist
        if ( isset( $this->form['org_address'] ) && !empty( $this->form['org_address'] ) ) :
   
        	$this->form['location'] = GEO_my_WP::geocoder( $this->form['org_address'] );
	        	
        	//if geocode was unsuccessful return error message
	        if ( isset( $this->form['location']['error'] ) ) {
	
	        	return $this->no_results( $this->form['location']['error'] );
	        	
	        } else {
	        	
	            $this->form['your_lat'] = $this->form['location']['lat'];
	            $this->form['your_lng'] = $this->form['location']['lng'];
	        } 	
        else :
            $this->form['org_address'] = '';
        endif;
        
        return $this->results();

    }

    /**
     * When displaying auto results
     */
    public function auto_results() {
        
        // get address from cookies
        $this->form['org_address']  = urldecode( $_COOKIE['gmw_address'] );
        
        //per page from settings
        $this->form['get_per_page'] = ( isset( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : current( explode( ",", $this->form['search_results']['per_page'] ) );
       

        if ( $this->form['search_results']['auto_search']['units'] == 'imperial' )
            $this->form['units_array'] = array( 'radius' => 3959, 'name' => "mi", 'map_units' => "ptm", 'units' => 'imperial' );
        else
            $this->form['units_array'] = array( 'radius' => 6371, 'name' => "km", 'map_units' => 'ptk', 'units' => "metric" );
		
        //distance
        $this->form['radius'] = $this->form['search_results']['auto_search']['radius'];

        $this->form['your_lat'] = urldecode( $_COOKIE['gmw_lat'] );
        $this->form['your_lng'] = urldecode( $_COOKIE['gmw_lng'] );

        $this->form = apply_filters( 'gmw_'.$this->form['prefix'].'_auto_results_before_results' , $this->form );
        
        $this->results();

    }
    
    /**
     * No results function
     * @param $gmw
     * @param $gmw_options
     */
    function no_results( $message ) {
  
    	$class = ( isset( $this->form['location']['error'] ) ) ? 'gmw-geocode-error' : '';
    	
    	do_action( 'gmw_'.$this->form['form_type'].'_before_no_results', $this->form  );
    
    	$no_results = '<div class="gmw-no-results-wrapper gmw-'.$this->form['prefix'].'-no-results-wrapper '.$class.'"><p>' . $message . '</p></div>';
    	echo apply_filters( 'gmw_'.$this->form['form_type'].'_no_results_message', $no_results, $this->form );
    
    	do_action( 'gmw_'.$this->form['form_type'].'_after_no_results', $this->form );
    
    }
    
}

/**
 * GMW FL Search results function - display map
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw ) {

    $frame = ( isset( $gmw['results_map']['map_frame'] ) ) ? 'gmw-map-frame' : '';

    $output['open']    = '<div id="gmw-map-wrapper-' . $gmw['ID'] . '" class="gmw-map-wrapper gmw-map-wrapper-' . $gmw['ID'] . ' gmw-' . $gmw['prefix'] . '-map-wrapper ' . $frame . '"  style="display:none;width:' . $gmw['results_map']['map_width'] . ';height:' . $gmw['results_map']['map_height'] . ';">';
    $output['loader']  = 	'<div class="gmw-map-loader-wrapper gmw-' . $gmw['prefix'] . '-loader-wrapper">';
    $output['loader'] .= 		'<img class="gmw-map-loader gmw-' . $gmw['prefix'] . '-map-loader" src="' . GMW_IMAGES . '/map-loader.gif"/>';
    $output['loader'] .= 	'</div>';
    $output['map']     = 	'<div id="gmw-map-'.$gmw['ID'].'" class="gmw-map gmw-' . $gmw['prefix'] . '-map" style="width:100%; height:100%"></div>';
    $output['close']   = '</div>';

    $output = apply_filters( 'gmw_'.$gmw['prefix'].'_map_output', $output, $gmw );
    
    return implode( ' ', $output );
}

function gmw_results_map( $gmw ) {

	//check that we have only one map for each form
	if ( apply_filters('map_exists', false ) == true && $gmw['addon'] != 'global_maps' ) 
		return;
	
	if ( $gmw['addon'] != 'global_maps' ) 
		add_filter( 'map_exists', create_function( '' , 'return true;' ) );

    do_action( 'gmw_' . $gmw['prefix'] . '_before_map', $gmw );

    echo gmw_get_results_map( $gmw );

    do_action( 'gmw_' . $gmw['prefix'] . '_after_map', $gmw );

}

/**
 * GMW search form function - form submit hidden fields
 *
 */
function gmw_form_submit_fields( $gmw, $subValue ) {
    ?>
    <div id="gmw-submit-wrapper-<?php echo $gmw['ID']; ?>" class="gmw-submit-wrapper gmw-submit-wrapper-<?php echo $gmw['ID']; ?>">
        <input type="hidden" id="gmw-form-id-<?php echo $gmw['ID']; ?>" class="gmw-form-id gmw-form-id-<?php echo $gmw['ID']; ?>" name="gmw_form" value="<?php echo $gmw['ID']; ?>" />
        
        <?php if ( $gmw['prefix'] == 'pt' ) { ?>
        	<input type="hidden" id="gmw-paged-<?php echo $gmw['ID']; ?>" class="gmw-paged gmw-paged-<?php echo $gmw['ID']; ?>" name="paged" value="<?php echo ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; ?>" />
        <?php } ?>
        
        <?php if ( $gmw['prefix'] == 'fl' ) { ?>
        	<input type="hidden" id="gmw-upage-<?php echo $gmw['ID']; ?>" class="gmw-upage gmw-upage-<?php echo $gmw['ID']; ?>" name="upage" value="<?php echo ( get_query_var( 'upage' ) ) ? get_query_var( 'upage' ) : 1; ?>" />
        <?php } ?>
        
        <input type="hidden" id="gmw-per-page-<?php echo $gmw['ID']; ?>" class="gmw-per-page gmw-per-page-<?php echo $gmw['ID']; ?>" name="gmw_per_page" value="<?php echo current( explode( ",", $gmw['search_results']['per_page'] ) ); ?>" />
        <input type="hidden" id="prev-address-<?php echo $gmw['ID']; ?>" class="prev-address prev-address-<?php echo $gmw['ID']; ?>" value="<?php if ( isset( $_GET['gmw_address'] ) ) echo implode( ' ', $_GET['gmw_address'] ); ?>">
    
        <input type="hidden" id="gmw-lat-<?php echo $gmw['ID']; ?>" class="gmw-lat gmw-lat-<?php echo $gmw['ID']; ?>" name="gmw_lat" value="<?php if ( isset( $_GET['gmw_lat'] ) ) echo $_GET['gmw_lat']; ?>">
        <input type="hidden" id="gmw-long-<?php echo $gmw['ID']; ?>" class="gmw-lng gmw-long-<?php echo $gmw['ID']; ?>" name="gmw_lng" value="<?php if ( isset( $_GET['gmw_lng'] ) ) echo $_GET['gmw_lng']; ?>">

        <input type="hidden" id="gmw-prefix-<?php echo $gmw['ID']; ?>" class="gmw-prefix gmw-prefix-<?php echo $gmw['ID']; ?>" name="gmw_px" value="<?php echo $gmw['prefix']; ?>" />
        <input type="hidden" id="gmw-action-<?php echo $gmw['ID']; ?>" class="gmw-action gmw-action-<?php echo $gmw['ID']; ?>" name="action" value="gmw_post" />

        <?php do_action( 'gmw_from_submit_fields', $gmw ); ?>
        
        <?php $submit_button = '<input type="submit" id="gmw-submit-' . $gmw['ID'] . '" class="gmw-submit" value="' . $subValue . '" />'; ?>
        <?php echo apply_filters( 'gmw_form_submit_button', $submit_button, $gmw, $subValue ); ?>
    </div>
    <?php

}

/**
 * GMW Search form function - Address Field
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_search_form_address_field( $gmw, $id, $class ) {

    $address_field 	= '';
    $am 		   	= ( isset( $gmw['search_form']['address_field']['mandatory'] ) ) ? 'mandatory' : '';
    $title 		   	= ( isset( $gmw['search_form']['address_field']['title'] ) ) ? $gmw['search_form']['address_field']['title'] : '';
	$class		   	= ( isset( $class ) && !empty( $class ) ) ? $class : '';
	$id		   	   	= ( isset( $id ) && !empty( $id ) ) ? $id : '';
	$value			= ( isset( $_GET['gmw_address'] ) ) ? str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ) : ''; 
	$place_holder	= ( isset( $gmw['search_form']['address_field']['within'] ) ) ? 'placeholder="' . $title . '"' : '';
	
    $address_field .= '<div id="'.$id.'" class="gmw-address-field-wrapper gmw-address-field-wrapper-'.$gmw['ID'].' '.$class.'">';
    
    if ( !isset( $gmw['search_form']['address_field']['within'] ) && !empty( $title ) )
        $address_field .= '<label for="gmw-address-' . $gmw['ID'] . '">' . $gmw['search_form']['address_field']['title'] . '</label>';

    $address_field .= '<input type="text" name="gmw_address[]" id="gmw-address-' . $gmw['ID'] . '" class="' . $am . ' gmw-address gmw-full-address gmw-address-' . $gmw['ID'] . ' ' . $class . '" value="' .$value .'" '.$place_holder.'/>';
    
    $address_field .= '</div>';

    return apply_filters( 'gmw_search_form_address_field', $address_field, $gmw, $id, $class );

}

function gmw_search_form_address_field( $gmw, $id, $class ) {
    echo gmw_get_search_form_address_field( $gmw, $id, $class );

}

/**
 * GMW Search form function - Locator Icon
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_search_form_locator_icon( $gmw, $class ) {

    $lSubmit = ( isset( $gmw['search_form']['locator_submit'] ) && $gmw['search_form']['locator_submit'] == 1 ) ? 'gmw-locator-submit' : '';

    $icon = $gmw['search_form']['locator_icon'];

    if ( $icon == 'gmw_na' )
        return;

    $button = '<div class="gmw-locator-btn-wrapper gmw-locator-btn-wrapper-' . $gmw['ID'] . '">';

    $button .= apply_filters( 'gmw_search_form_locator_button_img', '<img id="gmw-locate-button-' . $gmw['ID'] . '" class="gmw-locate-btn ' . $lSubmit . ' ' . $class . '" src="' . GMW_IMAGES . '/locator-images/' . $icon . '" />', $gmw, $class );
    $button .= '<img class="gmw-locator-btn-loader" src="' . GMW_IMAGES . '/gmw-loader.gif" style="display:none;" />';

    $button .= '</div>';

    return apply_filters( 'gmw_search_form_locator_button', $button, $gmw );

}

function gmw_search_form_locator_icon( $gmw, $class ) {
    echo gmw_get_search_form_locator_icon( $gmw, $class );

}

/**
 * GMW Search form function - Radius Values
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_radius_values( $gmw, $class ) {

    $miles  = explode( ",", $gmw['search_form']['radius'] );
	$output ='';
    
	if ( count( $miles ) > 1 ) :
	    
    	if ( $gmw['search_form']['units'] == 'both' ) {
			$title =  __( ' -- Within -- ', 'GMW' );
		} else {
        	$title = ( $gmw['search_form']['units'] == 'imperial' ) ? __( '- Miles -', 'GMW' ) : __( '- Kilometers -', 'GMW' );
		}
   		
		$title = apply_filters( 'gmw_radius_dropdown_title', $title, $gmw );
		
        $output .= '<select class="gmw-distance-select gmw-distance-select-' . $gmw['ID'] . ' '.$class.'" name="gmw_distance">';
        $output .= 	'<option value="' . end( $miles ) . '">'.$title.'</option>';

        foreach ( $miles as $mile ) :

            if ( isset( $_GET['gmw_distance'] ) && $_GET['gmw_distance'] == $mile )
                $mile_s = 'selected="selected"';
            else
                $mile_s = "";
            $output .= '<option value="' . $mile . '" ' . $mile_s . '>' . $mile . '</option>';

        endforeach;

        $output .= '</select>';
        
    else :

        $output = '<input type="hidden" name="gmw_distance" value="' . end( $miles ) . '" />';
    endif;

    echo apply_filters( 'gmw_radius_dropdown_output', $output, $gmw, $class );

}

/**
 * GMW Search form function - Distance units
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_units( $gmw, $class ) {
	
	if ( $gmw['search_form']['units'] == 'both' ) :
	
        $unit_m = $unit_i = false;
        if ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == 'metric' )
            $unit_m = 'selected="selected"';
        else
            $unit_i = 'selected="selected"';

        echo 	'<select name="gmw_units" id="gmw-units-' . $gmw['ID'] . '" class="gmw-units gmw-units-' . $gmw['ID'] . ' ' . $class . '">';
        echo 		'<option value="imperial" ' . $unit_i . '>' . __( 'Miles', 'GMW' ) . '</option>';
        echo 		'<option value="metric" ' . $unit_m . '>' . __( 'Kilometers', 'GMW' ) . '</option>';
        echo 	'</select>';

    else :
        echo '<input type="hidden" name="gmw_units" value="' . $gmw['search_form']['units'] . '" />';
    endif;
    
}

function gmw_multiexplode( $delimiters, $string ) {

    $ready  = str_replace( $delimiters, $delimiters[0], $string );
    $launch = explode( $delimiters[0], $ready );
    return $launch;

}
