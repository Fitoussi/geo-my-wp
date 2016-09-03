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
                                        'placeholder' => __( 'Enter your google api key', 'GMW' ),
                                        'label'       => __( 'Google Maps API V3 Key', 'GMW' ),
                                        'desc'        => __( 'Google Maps API is required in order to use GEO my WP plugin ( and Google Maps features in general ). Please follow <a href="http://docs.gravitygeolocation.com/article/101-create-google-map-api-key" target="_blank">this tutorial</a> on how to create the required API key and how to enable the required API services. Note that the tutorial refers to Gravity Geolocation add-on. However, the API key creation process is the same for GEO my WP plugin.', 'GMW' ),
                                        'attributes'  => array( 'size' => '50' )
                                ),
                                array(
                                        'name'       => 'js_geocode',
                                        'std'        => '',
                                        'label'      => __( 'Client-side Geocoder', 'GMW' ),
                                        'cb_label'   => __( 'Enable client-side ( Javascript ) geocoding', 'GMW' ),
                                        'desc'       => __( "Check this checkbox if you want to use client-side to geocode the address entered in GEO my WP's search form. clients-de geocoding might prevent Google API's OVER_QUERY_LIMIT issue.", 'GMW' ),
                                        'type'       => 'checkbox',
                                        'attributes' => array()
                                ),
                                array(
                                        'name'        => 'language_code',
                                        'std'         => '',
                                        'placeholder' => '',
                                        'label'       => __( 'Google API Language', 'GMW' ),
                                        'desc'        => __( 'Set the language to be used with Google Places address auto-complete and with Google Maps API. The language codes can be found','GMW'). '<a href="https://spreadsheets.google.com/spreadsheet/pub?key=0Ah0xU81penP1cDlwZHdzYWkyaERNc0xrWHNvTTA1S1E&gid=1" target="_blank"> '.__('here', 'GMW' ) .'</a>',
                                        'attributes'  => array( 'size' => '5' )
                                ),
                                array(
                                        'name'        => 'country_code',
                                        'std'         => '',
                                        'placeholder' => '',
                                        'label'       => __( 'Country Code', 'GMW' ),
                                        'desc'        => __( 'Enter the default country code to be used with GEO my WP. For example for United States enter US. List of country code can be found <a href="http://geomywp.com/country-code/" target="blank">here</a>', 'GMW' ),
                                        'attributes'  => array( 'size' => '5' )
                                ),
                                array(
                                        'name'       => 'auto_locate',
                                        'std'        => '',
                                        'label'      => __( 'Auto Locator', 'GMW' ),
                                        'cb_label'   => __( 'Enable auto-locator', 'GMW' ),
                                        'desc'       => __( "GEO my WP will try to retrive the user's current location when first visits the website. If a location found it will be saved via cookies and later will be used to automatically display results based on that.", 'GMW' ),
                                        'type'       => 'checkbox',
                                        'attributes' => array()
                                ),
                                array(
                                        'name'     => 'results_page',
                                        'std'      => '0',
                                        'label'    => __( 'Results Page', 'GMW' ),
                                        'cb_label' => '',
                                        'desc'     => __( 'This page displays the search results ( of any of your forms ) when using the "GMW Search Form" widget.', 'GMW' ).
                                                      __( 'The plugin will first check if a results page was set in the form settings and if so the results will be displayed in that page. Otherwise, if no results page', 'GMW' ).
                                                      __( 'was set in the form settings the results will be displayed in the page you choose from the select box.', 'GMW' ).
                                                      __( "Choose the page from the dropdown menu and paste the shortcode [gmw form=\"results\"] in there.", 'GMW' ),
                                        'type'     => 'select',
                                        'options'  => $this->get_pages()
                                ),
                        ),
                ),
        ));
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
         
        if ( empty( $_POST['option_page'] ) || $_POST['option_page'] != 'gmw_options_group' )
            return;

        if ( empty( $_POST['action'] ) || $_POST['action'] != 'update' )
            return;

        register_setting( $this->settings_group, 'gmw_options', array( $this, 'validate') );
    }
    
    function validate( $gmw_options ) {     
        return $gmw_options;
    }

    /**
     * display settings 
     *
     * @access public
     * @return void
     */
    public function output() {
        
        self::init_settings();
        $gmw_options = get_option( 'gmw_options' );
        
        ?>
        <div class="wrap">

            <h2 class="gmw-wrap-top-h2">
                <i class="fa fa-cog"></i>
                <?php echo _e( 'Settings', 'GMW' ); ?>
                <?php gmw_admin_support_button(); ?>
            </h2>

            <div class="clear"></div>
            
            <form method="post" action="options.php">

                <?php settings_fields( $this->settings_group ); ?>

                <?php
                if ( !empty( $_GET[ 'settings-updated' ] ) ) {
                    echo '<div class="updated fade" style="clear:both"><p>' . __( 'Settings successfully saved', 'GMW' ) . '</p></div>';
                }
                ?>

                <div class="gmw-tabs-table gmw-settings-page-nav-tabs gmw-nav-tab-wrapper">
                    <?php
                    foreach ( $this->settings as $key => $section ) {
                        echo '<span><a href="#settings-'.sanitize_title( $key ).'" id="'.sanitize_title( $key ).'" title="' . esc_html( $section[ 0 ] ) . '"  class="gmw-nav-tab">' . esc_html( $section[ 0 ] ) . '</a></span>';
                    }
                    ?>
                </div>

                <?php foreach ( $this->settings as $key => $section ) { ?>

                    <div id="settings-<?php echo sanitize_title( $key ); ?>" class="gmw-settings-panel">
                    
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:20%;padding:11px 10px"><?php _e( 'Feature', 'GMW' ); ?></th>
                                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:35%;padding:11px 10px"><?php _e( 'Settings', 'GMW' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rowNumber = 0;
        
                                foreach ( $section[ 1 ] as $option ) {
        
                                    $placeholder    = ( !empty( $option['placeholder'] ) ) ? 'placeholder="'.$option[ 'placeholder' ].'"' : '';
                                    $class          =   !empty( $option[ 'class' ] ) ? $option[ 'class' ] : '';
                                    $value          = ( !empty( $gmw_options[$key][$option['name']] ) ) ? $gmw_options[$key][$option['name']] : $option['std'];
                                    $option['type'] =   !empty( $option['type'] ) ? $option[ 'type' ] : '';
                                    $attributes     = array();
        
                                    if ( !empty( $option[ 'attributes' ] ) && is_array( $option[ 'attributes' ] ) ) {
                                        foreach ( $option[ 'attributes' ] as $attribute_name => $attribute_value ) {
                                            $attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
                                        }
                                    }

                                    echo '<tr valign="top" class="' . $class .'">';
                                        echo '<td class="gmw-form-feature-desc">';
                                            echo '<label for="setting-' . $option['name'] . '">' . $option[ 'label' ] . '</label>';
                                            if ( $option[ 'desc' ] ) {
                                                echo ' <p class="description">' . $option[ 'desc' ] . '</p>';
                                            }
                                        echo '</td>';
                                        echo '<td class="gmw-form-feature-settings">';
        
                                        switch ( $option[ 'type' ] ) {
            
                                            case "function" :
            
                                                $function = ( !empty( $option['function'] ) ) ? $option['function'] : $option['name'];
                                                   
                                                do_action( 'gmw_main_settings_' . $function, $gmw_options, $key, $option );
            
                                            break;
            
                                            case "checkbox" :
                                                ?><label><input class="setting-<?php echo $option[ 'name' ]; ?>" name="<?php echo 'gmw_options[' . $key . '][' . $option[ 'name' ] . ']'; ?>" type="checkbox" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option[ 'cb_label' ]; ?></label><?php
                                            break;
            
                                            case "multicheckbox" :
                                                foreach ( $option[ 'options' ] as $keyVal => $name ) {
                                                    ?><label><input class="setting-<?php echo $option[ 'name' ]; ?>" name="<?php echo 'gmw_options['.$key.']['.$option['name'].']['.$keyVal.']'; ?>" type="checkbox" value="1" <?php if ( isset( $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] ) && $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] == 1 ) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label><br /> <?php
                                                }
                                            break;
                                            
                                            //needs improvment
                                            /*                                 
                                            case "multicheckboxvalues" :
                                                foreach ($option['options'] as $keyVal => $name) {
                                                    ?><p><label><input id="setting-<?php echo $option[ 'name' ]; ?>" class="setting-<?php echo $option['name']; ?>"  name="<?php echo 'gmw_options['.$key.']['.$option['name'].']['.$keyVal.']'; ?>" type="checkbox" value="<?php echo $keyVal; ?>" <?php if ( isset( $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] ) && $gmw_options[ $key ][ $option[ 'name' ] ][ $keyVal ] == 1 ) echo 'checked="checked"'; ?> /> <?php echo $name; ?></label><br /> <?php
                                                }
                                            break;
                                            */
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
                                                ?>
                                                <select id="setting-<?php echo $option['name']; ?>" class="regular-text" name="<?php echo 'gmw_options['.$key.']['.$option['name'].']'; ?>" <?php echo implode( ' ', $attributes ); ?>>
                                                    <?php foreach ( $option[ 'options' ] as $keyVal => $name ) { ?>
                                                        <?php echo '<option value="' . esc_attr( $keyVal ) . '" ' . selected( $value, $keyVal, false ) . '>' . esc_html( $name ) . '</option>'; ?>
                                                    <?php } ?>
                                                </select>
                                                <?php 
                                            break;
            
                                            case "password" :
                                                ?>
                                                <input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="password" name="<?php echo 'gmw_options['.$key.']['.$option['name'].']'; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
                                                <?php
                                            break;
                                                
                                            default :
                                                ?>
                                                <input id="setting-<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo 'gmw_options['.$key.']['.$option['name'].']'; ?>" value="<?php esc_attr_e( $value ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
                                                <?php
                                            break;
                                        }
        
                                       echo '</td>';
                                    echo '</tr>';
        
                                    $rowNumber++;
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr style="height:40px;">
                                    <th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px">
                                        <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
                                    </th>
                                    <th scope="col" id="id" class="manage-column">--</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php } ?>
            </form>
        </div>
        <?php $current_tab = ( isset( $_COOKIE['gmw_admin_tab'] ) ) ? $_COOKIE['gmw_admin_tab'] : false; ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            if ( '<?php echo $current_tab; ?>' != false ) { 
                jQuery('#<?php echo $current_tab; ?>').click();
            }
        });
        </script>
        <?php
    }
}
