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

        $this->settings = get_option( 'gmw_options' );
        $this->form     = $form;

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
        if ( $results == false && isset( $this->form['search_form'] ) )
            $this->custom_search_form();

        // when form submitted
        if ( isset( $_GET['action'] ) && $_GET['action'] == "gmw_post" && ( ( $results == true ) || ( $results == false && $this->form['ID'] == $_GET['gmw_form'] && !isset( $params['widget'] ) && $this->form['search_results']['results_page'] == false ) ) ) :

            self::form_submitted();

        // if auto results 
        elseif ( empty( $this->form['search_results']['results_page'] ) && isset( $_COOKIE['gmw_lat'] ) && isset( $_COOKIE['gmw_lng'] ) && isset( $this->form['search_results']['auto_search']['on'] ) && !isset( $params['widget'] ) ) :

            self::auto_results();

        endif;

        do_action( 'gmw_' . $this->form['form_type'] . '_main_shortcode_end', $this->form );

    }

    public function custom_search_form() {

        $gmw = $this->form;

        //Load custom search form and css from child/theme folder
        if ( strpos( $this->form['search_form']['form_template'], 'custom_' ) !== false ) :

            do_action( 'gmw_' . $this->form['form_type'] . '_before_custom_search_form', $this->form, $this->settings );

            $sForm = str_replace( 'custom_', '', $this->form['search_form']['form_template'] );

            wp_enqueue_style( 'gmw-' . $this->form['ID'] . '-' . $sForm . '-form-style', get_stylesheet_directory_uri() . '/geo-my-wp/' . $this->form['form_type'] . '/search-forms/' . $sForm . '/css/style.css' );

            include( STYLESHEETPATH . '/geo-my-wp/' . $this->form['form_type'] . '/search-forms/' . $sForm . '/search-form.php' );

            do_action( 'gmw_' . $this->form['form_type'] . '_after_custom_search_form', $this->form, $this->settings );

            return;

        else :

            $this->search_form();

        endif;

    }

    /**
     * When form submitted
     * @version 1.0
     * @author Eyal Fitoussi
     */
    public function form_submitted() {

        $this->form['get_per_page'] = $this->form['get_per_page'] = ( isset( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : current( explode( ",", $this->form['search_results']['per_page'] ) );
        $this->form['units_array']  = false;
        $this->form['your_lat']     = false;
        $this->form['your_lng']     = false;
        $this->form['radius']       = $_GET['gmw_distance'];

        // get the address
        if ( array_filter( $_GET['gmw_address'] ) )
            $this->form['org_address'] = str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) );

        // distance units 
        if ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == "imperial" )
            $this->form['units_array'] = array( 'radius' => 3959, 'name' => "Mi", 'map_units' => "ptm", 'units' => 'imperial' );
        else
            $this->form['units_array'] = array( 'radius' => 6371, 'name' => "Km", 'map_units' => 'ptk', 'units' => "metric" );

        if ( isset( $this->form['org_address'] ) && !empty( $this->form['org_address'] ) ) :

            $this->form['your_lat'] = $_GET['gmw_lat'];
            $this->form['your_lng'] = $_GET['gmw_lng'];

        else :

            $this->form['org_address'] = '';
        endif;

        $this->results();

    }

    /**
     * When displaying auto results
     */
    public function auto_results() {

        $this->form['get_per_page'] = $this->form['get_per_page'] = ( isset( $_GET['gmw_per_page'] ) ) ? $_GET['gmw_per_page'] : current( explode( ",", $this->form['search_results']['per_page'] ) );
        // get address from cookies
        $this->form['org_address']  = urldecode( $_COOKIE['gmw_address'] );

        if ( $this->form['search_results']['auto_search']['units'] == 'imperial' )
            $this->form['units_array'] = array( 'radius' => 3959, 'name' => "mi", 'map_units' => "ptm", 'units' => 'imperial' );
        else
            $this->form['units_array'] = array( 'radius' => 6371, 'name' => "km", 'map_units' => 'ptk', 'units' => "metric" );

        $this->form['radius']   = $this->form['search_results']['auto_search']['radius'];
        $this->form['your_lat'] = urldecode( $_COOKIE['gmw_lat'] );
        $this->form['your_lng'] = urldecode( $_COOKIE['gmw_lng'] );

        $this->results();

    }

}

/**
 * GMW FL Search results function - display map
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_get_results_map( $gmw ) {

    $frame = ( isset( $gmw['results_map']['map_frame'] ) ) ? 'gmw-map-frame' : '';

    $output = '<div id="gmw-map-wrapper-' . $gmw['ID'] . '" class="gmw-map-wrapper gmw-map-wrapper-' . $gmw['ID'] . ' gmw-' . $gmw['prefix'] . '-map-wrapper ' . $frame . '"  style="display:none;width:' . $gmw['results_map']['map_width'] . ';height:' . $gmw['results_map']['map_height'] . ';">';
    $output .= '<div class="gmw-map-loader-wrapper gmw-' . $gmw['prefix'] . '-loader-wrapper">';
    $output .= '<img class="gmw-map-loader gmw-' . $gmw['prefix'] . '-map-loader" src="' . GMW_IMAGES . '/map-loader.gif"/>';
    $output .= '</div>';
    $output .= '<div id="gmw-map-' . $gmw['ID'] . '" gmw-map-' . $gmw['ID'] . ' class="gmw-map gmw-' . $gmw['prefix'] . '-map" style="width:100%; height:100%"></div>';
    $output .= '</div>';

    return $output;

}

function gmw_results_map( $gmw ) {

    if ( $gmw['search_results']['display_map'] != "results" )
        return;

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
        <input type="hidden" id="gmw-paged-<?php echo $gmw['ID']; ?>" class="gmw-paged gmw-paged-<?php echo $gmw['ID']; ?>" name="paged" value="<?php echo ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; ?>" />
        <input type="hidden" id="gmw-per-page-<?php echo $gmw['ID']; ?>" class="gmw-per-page gmw-per-page-<?php echo $gmw['ID']; ?>" name="gmw_per_page" value="<?php echo current( explode( ",", $gmw['search_results']['per_page'] ) ); ?>" />
        <input type="hidden" id="prev-address-<?php echo $gmw['ID']; ?>" class="prev-address prev-address-<?php echo $gmw['ID']; ?>" value="<?php if ( isset( $_GET['gmw_address'] ) ) echo implode( ' ', $_GET['gmw_address'] ); ?>">
        <input type="hidden" id="gmw-lat-<?php echo $gmw['ID']; ?>" class="gmw-lat gmw-lat-<?php echo $gmw['ID']; ?>" name="gmw_lat" value="<?php if ( isset( $_GET['gmw_lat'] ) ) echo $_GET['gmw_lat']; ?>">
        <input type="hidden" id="gmw-long-<?php echo $gmw['ID']; ?>" class="gmw-lng gmw-long-<?php echo $gmw['ID']; ?>" name="gmw_lng" value="<?php if ( isset( $_GET['gmw_lng'] ) ) echo $_GET['gmw_lng']; ?>">
        <input type="hidden" id="gmw-prefix-<?php echo $gmw['ID']; ?>" class="gmw-prefix gmw-prefix-<?php echo $gmw['ID']; ?>" name="gmw_px" value="<?php echo $gmw['prefix']; ?>" />
        <input type="hidden" id="gmw-action-<?php echo $gmw['ID']; ?>" class="gmw-action gmw-action-<?php echo $gmw['ID']; ?>" name="action" value="gmw_post" />

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

    $address_field = '';
    $am 		   = ( isset( $gmw['search_form']['address_field']['mandatory'] ) ) ? 'mandatory' : '';
    $title 		   = ( isset( $gmw['search_form']['address_field']['title'] ) ) ? $gmw['search_form']['address_field']['title'] : '';
	$class		   = ( isset( $class ) && !empty( $class ) ) ? $class : '';
	$id		   	   = ( isset( $id ) && !empty( $id ) ) ? $id : '';
	
    $address_field .= '<div id="'.$id.'" class="gmw-address-field-wrapper gmw-address-field-wrapper-'.$gmw['ID'].' '.$class.'">';
    
    if ( !isset( $gmw['search_form']['address_field']['within'] ) && !empty( $title ) )
        $address_field .= '<label for="gmw-address-' . $gmw['ID'] . '">' . $gmw['search_form']['address_field']['title'] . '</label>';

    $address_field .= '<input type="text" name="gmw_address[]" id="gmw-address-' . $gmw['ID'] . '" class="' . $am . ' gmw-address gmw-full-address gmw-address-' . $gmw['ID'] . ' ' . $class . '" value="';
    if ( isset( $_GET['gmw_address'] ) )
        $address_field .= str_replace( '+', ' ', implode( ' ', $_GET['gmw_address'] ) ); $address_field .= '" size="35" ';
    if ( isset( $gmw['search_form']['address_field']['within'] ) )
        $address_field .= 'placeholder="' . $title . '"'; $address_field .= ' />';
    
    $address_field .= '</div>';

    return apply_filters( 'gmw_search_form_address_field', $address_field, $gmw, $id, $class);

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
    $button .= '<img src="' . GMW_IMAGES . '/gmw-loader.gif" style="display:none;" />';

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
function gmw_search_form_radius_values( $gmw, $class, $btitle, $stitle ) {

    $miles  = explode( ",", $gmw['search_form']['radius'] );
	$output ='';
    
	$output .= '<div class="gmw-radius-dropdown-wrapper ' . $class . '">';
    
    if ( empty( $stitle ) )
        $stitle = ( $gmw['search_form']['units'] == 'imperial' ) ? __( '- Miles -', 'GMW' ) : __( '- Kilometers -', 'GMW' );
    
    $btitle = ( empty( $btitle ) ) ? __( ' -- Within -- ', 'GMW' ) : $btitle;

    if ( count( $miles ) > 1 ) :

        $output .= '<select class="gmw-distance-select gmw-distance-select-' . $gmw['ID'] . '" name="gmw_distance">';
        $output .= '<option value="' . end( $miles ) . '">';
        if ( $gmw['search_form']['units'] == 'both' )
            $output .= $btitle;
        else
            $output .= $stitle; $output .= '</option>';

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

    $output .= '</div>';
    
    echo apply_filters( 'gmw_search_form_radius_field', $output, $gmw, $class, $btitle, $stitle );

}

/**
 * GMW Search form function - Distance units
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmw_search_form_units( $gmw, $class ) {
	
	echo '<div class="gmw-units-dropdown-wrapper ' . $class . '">';
	
    if ( $gmw['search_form']['units'] == 'both' ) :

        $unit_m = $unit_i = false;
        if ( isset( $_GET['gmw_units'] ) && $_GET['gmw_units'] == 'metric' )
            $unit_m = 'selected="selected"';
        else
            $unit_i = 'selected="selected"';

        echo '<select name="gmw_units" id="gmw-units-' . $gmw['ID'] . '" class="gmw-units gmw-units-' . $gmw['ID'] . ' ' . $class . '">';

        echo '<option value="imperial" ' . $unit_i . '>' . __( 'Miles', 'GMW' ) . '</option>';
        echo '<option value="metric" ' . $unit_m . '>' . __( 'Kilometers', 'GMW' ) . '</option>';

        echo '</select>';

    else :
        echo '<input type="hidden" name="gmw_units" value="' . $gmw['search_form']['units'] . '" />';
    endif;
    
    echo '</div>';

}

function gmw_multiexplode( $delimiters, $string ) {

    $ready  = str_replace( $delimiters, $delimiters[0], $string );
    $launch = explode( $delimiters[0], $ready );
    return $launch;

}
