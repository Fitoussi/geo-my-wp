<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_Settings class.
 */

class GMW_Settings {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->settings_group = 'gmw_options_group';
        add_action( 'admin_init', array( $this, 'register_settings' ) );

    }

    /**
     * init_settings function.
     *
     * @access protected
     * @return void
     */
    protected function init_settings() {

    	$this->settings = apply_filters( 'gmw_admin_settings', array(
    			'general_settings' => array(
    					__( 'General Settings', 'GMW' ),
    					array(
    							array(
    									'name'        => 'google_api',
    									'std'         => '',
    									'placeholder' => __( 'Enter your google api key here', 'GMW' ),
    									'label'       => __( 'Google Maps API V3 Key', 'GMW' ),
    									'desc'        => __( 'This is optional but will let you track your API requests. you can obtain your free Goole API key <a href="https://code.google.com/apis/console/" target="_blank">here</a>.', 'GMW' ),
    									'attributes'  => array( 'size' => '40' )
    							),
    							array(
    									'name'       => 'js_geocode',
    									'std'        => '',
    									'label'      => __( 'JavaScript Geocoding', 'GMW' ),
    									'cb_label'   => __( 'Use Javascript call for geocoding', 'GMW' ),
    									'desc'       => __( 'Check this checkbox to geocode the address on form submission via JavaScript call instaed of HTTP. You can try that if you are keep getting the message "Something went wrong trying to retrieve your location" when sumitting a search form which can be cause by OVER_QUERY_LIMIT', 'GMW' ),
    									'type'       => 'checkbox',
    									'attributes' => array()
    							),
    							/*
    							 array(
    							 		'name'        => 'bing_api',
    							 		'std'         => '',
    							 		'placeholder' => __('Enter your Bing api key here', 'GMW'),
    							 		'label'       => __( 'Bing Maps API Key', 'GMW' ),
    							 		'desc'        => __( 'Use this feature if you often get "OVER_QUERY_LIMIT" response from google when using the search form.
    							 				OVER_QUERY_LIMIT happens when there are too many calls to Google API from the same IP at a very short time or when the per day limit had been used.
    							 				This will prevent the address entered in the search form from being geocoded and so no results will return.
    							 				You can read about Bing services and obtain your key <a href="http://www.bingmapsportal.com/application/" target="_blank">Here</a>', 'GMW' ),
    							 		'attributes'  => array( 'size' => '40' )
    							 ), */
    							array(
    									'name'        => 'language_code',
    									'std'         => '',
    									'placeholder' => '',
    									'label'       => __( 'Google API Language', 'GMW' ),
    									'desc'        => __( 'Set the language to be used with Google Places address auto-complete and with Google Maps API. The language codes can be found','GMW'). '<a href="https://spreadsheets.google.com/spreadsheet/pub?key=0Ah0xU81penP1cDlwZHdzYWkyaERNc0xrWHNvTTA1S1E&gid=1" target="_blank"> '.__('here', 'GJM' ) .'</a>',
    									'attributes'  => array( 'size' => '5' )
    							),
    							array(
    									'name'        => 'country_code',
    									'std'         => '',
    									'placeholder' => '',
    									'label'       => __( 'Country Code', 'GMW' ),
    									'desc'        => __( 'Enter you country code. For example for United States enter US. you can find your country code <a href="http://geomywp.com/country-code/" target="blank">here</a>', 'GMW' ),
    									'attributes'  => array( 'size' => '5' )
    							),
    							array(
    									'name'       => 'auto_locate',
    									'std'        => '',
    									'label'      => __( 'Auto Locator', 'GMW' ),
    									'cb_label'   => __( 'Use auto locator', 'GMW' ),
    									'desc'       => __( 'This feature will automatically try to get the user\'s current location when first visiting the website. If location found it will be saved via cookies and later will be used to auto display results.', 'GMW' ),
    									'type'       => 'checkbox',
    									'attributes' => array()
    							),
    							array(
    									'name'     => 'results_page',
    									'std'      => '0',
    									'label'    => __( 'Results Page', 'GMW' ),
    									'cb_label' => '',
    									'desc'     => __( 'This page will display the search results ( for any of your forms ) when using the "GMW Search Form" widget.
    											The plugin will first check if a results page was set in the form settings and if so the results will be displayed in that page. Otherwise, if no results page
    											was set in the form settings the results will be displayed in the page you choose from the select box.
    											Choose the page from the dropdown menu and paste the shortcode [gmw form="results"] into it.', 'GMW' ),
    									'type'     => 'select',
    									'options'  => $this->get_pages()
    							),
    					),
    			),
    			'features'         => array(
    					__( 'Features', 'GMW' ),
    					array(
    							array(
    									'name'       => 'current_location_shortcode',
    									'std'        => '',
    									'label'      => __( 'Current Location Shortcode/widget', 'GMW' ),
    									'cb_label'   => __( 'Yes', 'GMW' ),
    									'desc'       => __( 'Turn on/off the current location shortcode and widget.', 'GMW' ),
    									'type'       => 'checkbox',
    									'attributes' => array()
    							),
    							array(
    									'name'       => 'search_form_widget',
    									'std'        => '',
    									'label'      => __( 'Search Form Widget', 'GMW' ),
    									'cb_label'   => __( 'Yes', 'GMW' ),
    									'desc'       => __( 'Turn on/off the search form widget.', 'GMW' ),
    									'type'       => 'checkbox',
    									'attributes' => array()
    							),
    					),
    			),
    	)
    	);

    }

    /**
     * Get existing pages
     */
    public function get_pages() {
        $pages = array();
        foreach ( get_pages() as $page ) {
            $pages[ $page->ID ] = $page->post_title;
        }

        return $pages;

    }

    /**
     * register_settings function.
     *
     * @access public
     * @return void
     */
    public function register_settings() {
        self::init_settings();

        $gmw_options = array();
        foreach ( $this->settings as $key => $section ) {

            foreach ( $section[ 1 ] as $option ) {

                if ( isset( $option[ 'std' ] ) )
                    $gmw_options[ $key ][ $option[ 'name' ] ] = $option[ 'std' ];
            }
        }

        add_option( 'gmw_options', $gmw_options );
        register_setting( $this->settings_group, 'gmw_options' );

    }

    /**
     * display settings 
     *
     * @access public
     * @return void
     */
    public function output() {

        //self::init_settings();
        $gmw_options = get_option( 'gmw_options' );
        ?>
        <div class="wrap">

            <?php echo GMW_Admin::gmw_credits(); ?>
            <h2 class="gmw-wrap-top-h2"><?php echo _e( 'GEO my WP Settings', 'GMW' ); ?></h2>

            <div class="clear"></div>

            <form method="post" action="options.php">

                <?php settings_fields( $this->settings_group ); ?>

                <?php
                if ( !empty( $_GET[ 'settings-updated' ] ) ) {
                    flush_rewrite_rules();
                    echo '<div class="updated fade" style="clear:both"><p>' . __( 'Settings successfully saved', 'GMW' ) . '</p></div>';
                }
                ?>

                <table class="widefat fixed gmw-tabs-table">
                    <thead>
                        <tr>
                            <th class="widgets-holder-wrap closed gmw-nav-tab-wrapper" style="padding:0px;border-left: 4px solid #7ad03a;padding-left:0px;">

                                <?php
                                foreach ( $this->settings as $key => $section ) {
                                    echo '<span><a href="#settings-' . sanitize_title( $key ) . '" title="' . esc_html( $section[ 0 ] ) . '"  class="gmw-nav-tab">' . esc_html( $section[ 0 ] ) . '</a></span>';
                                }
                                ?>

                            </th>
                        </tr>
                    </thead>
                </table>
                <br />

                <?php
                foreach ( $this->settings as $key => $section ) {

                    echo '<div id="settings-' . sanitize_title( $key ) . '" class="settings_panel">';
                    ?>
                    <table class="widefat fixed">
                        <thead>
                            <tr>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:20%;padding:11px 10px"><?php _e( 'Feature', 'GMW' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:30%;padding:11px 10px"><?php _e( 'Settings', 'GMW' ); ?></th>
                                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50%;padding:11px 10px"><?php _e( 'Explanation', 'GMW' ); ?></th>
                            </tr>
                        </thead>
                        <?php
                        $rowNumber = 0;

                        foreach ( $section[ 1 ] as $option ) {

                            $placeholder    = (!empty( $option[ 'placeholder' ] ) ) ? 'placeholder="' . $option[ 'placeholder' ] . '"' : '';
                            $class          = !empty( $option[ 'class' ] ) ? $option[ 'class' ] : '';
                            $value          = ( isset( $gmw_options[ $key ][ $option[ 'name' ] ] ) && !empty( $gmw_options[ $key ][ $option[ 'name' ] ] ) ) ? $gmw_options[ $key ][ $option[ 'name' ] ] : $option[ 'std' ];
                            $option[ 'type' ] = !empty( $option[ 'type' ] ) ? $option[ 'type' ] : '';
                            $attributes     = array();

                            if ( !empty( $option[ 'attributes' ] ) && is_array( $option[ 'attributes' ] ) )
                                foreach ( $option[ 'attributes' ] as $attribute_name => $attribute_value )
                                    $attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';

                            $alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : '';

                            echo '<tr valign="top" class="' . $class . ' ' . $alternate . '"><th scope="row" style="color: #555;border-bottom:1px solid #eee;"><label for="setting-' . $option[ 'name' ] . '">' . $option[ 'label' ] . '</label></th><td style="color: #555;border-bottom:1px solid #eee;min-width:400px;vertical-align: top">';

                            switch ( $option[ 'type' ] ) {

                                case "function" :

                                    $function = ( isset( $option[ 'function' ] ) && !empty( $option[ 'function' ] ) ) ? $option[ 'function' ] : $option[ 'name' ];

                                    do_action( 'gmw_main_settings_' . $function, $gmw_options, $key, $option );

                                    break;

                                case "checkbox" :
                                    ?><label><input class="setting-<?php echo $option[ 'name' ]; ?>" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option[ 'cb_label' ]; ?></label><?php
                                    break;

                                case "multicheckbox" :
                                    foreach ( $option[ 'options' ] as $keyVal => $name ) {
                                        ?><label><input class="setting-<?php echo $option[ 'name' ]; ?>" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . '][' . $keyVal . ']'; ?>" type="checkbox" value="1" <?php if ( isset( $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] ) && $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] == 1 ) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label><br /> <?php
                                        }
                                        break;

                                    case "textarea" :
                                        ?><textarea id="setting-<?php echo $option[ 'name' ]; ?>" class="large-text" cols="50" rows="3" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php
                                        break;

                                    case "radio" :
                                        $rc = 1;
                                        foreach ( $option[ 'options' ] as $keyVal => $name ) {
                                            $checked = ( $rc == 1 ) ? 'checked="checked"' : checked( $value, $keyVal, false );
                                            echo '<input type="radio" class="setting-' . $option[ 'name' ] . '" name="gmw_options[' . $key . '][' . $option[ 'name' ] . ']" value="' . esc_attr( $keyVal ) . '" ' . $checked . ' />' . $name . ' ';
                                            $rc++;
                                        }
                                        break;

                                    case "select" :
                                        ?><select id="setting-<?php echo $option[ 'name' ]; ?>" class="regular-text" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" <?php echo implode( ' ', $attributes ); ?>><?php
                                    foreach ( $option[ 'options' ] as $keyVal => $name )
                                        echo '<option value="' . esc_attr( $keyVal ) . '" ' . selected( $value, $keyVal, false ) . '>' . esc_html( $name ) . '</option>';
                                    ?></select><?php
                                    break;

                                case "password" :
                                    ?><input id="setting-<?php echo $option[ 'name' ]; ?>" class="regular-text" type="password" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php
                                        break;
                                    default :
                                        ?><input id="setting-<?php echo $option[ 'name' ]; ?>" class="regular-text" type="text" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> /><?php
                                    break;
                            }

                            echo '</td>';
                            echo '<td style="color: #555;border-bottom:1px solid #eee;vertical-align: top">';

                            if ( $option[ 'desc' ] )
                                echo ' <p class="description">' . $option[ 'desc' ] . '</p>';

                            echo '</td>';
                            echo '</tr>';

                            $rowNumber++;
                        }
                        ?>
                        <tfoot>
                            <tr style="height:40px;">
                                <th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px">
                                    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
                                </th>
                                <th scope="col" id="id" class="manage-column"></th>
                                <th scope="col" id="active" class="manage-column"></th> 	
                            </tr>
                        </tfoot>
                    </table>
            </div>
            <?php
        }
        ?>
        </form>
        </div>
        <script type="text/javascript">
            jQuery('.gmw-nav-tab-wrapper a').click(function() {
                jQuery('.settings_panel').hide();
                jQuery('.gmw-nav-tab-active').css('background', '#f7f7f7');
                jQuery('.gmw-nav-tab-active').removeClass('gmw-nav-tab-active');

                jQuery(jQuery(this).attr('href')).show();
                jQuery(this).addClass('gmw-nav-tab-active');
                jQuery(this).css('background', '#C3D5E6');

                return false;
            });

            jQuery('.gmw-nav-tab-wrapper a:first').click();
        </script>
        <?php

    }

}
