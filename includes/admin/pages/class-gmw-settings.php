<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

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
    	  
        $this->settings_group = 'gmw_options';

        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function setup_defaults() {

        $defaults = apply_filters( 'gmw_admin_settings_setup_defaults', array( 
            
            'general_settings' => array(
                'google_api'    => '',
                'js_geocode'    => 1,
                'country_code'  => 'US',
                'language_code' => 'EN',
                'results_page'  => '',
                'auto_locate'   => 1
            )
        ) );

        $gmw_options = get_option( 'gmw_options' );
        
        if ( empty( $gmw_options ) ) {
            $gmw_options = array();
        }

        $count = 0;

        foreach( $defaults as $group_name => $values ) {

            if ( empty( $gmw_options[$group_name] ) ) {
                $gmw_options[$group_name] = $values;
                $count++;
            }
        }

        if ( $count > 0 ) {
            update_option( 'gmw_options', $gmw_options );  
        }      
    }

    /**
     * init_settings function.
     *
     * @access protected
     * @return void
     */
    protected function init_settings() {

        $this->setup_defaults();

        $this->settings_tabs = apply_filters( 'gmw_admin_settings_groups', array(
            array( 
                'id'        => 'general_settings',
                'label'     => __( 'General Settings', 'GMW' ),
                'icon'      => 'cog',
                'priority'  => 5
            )
        ) );

    	$this->settings = apply_filters( 'gmw_admin_settings', array(

			'general_settings' => array(
                'allow_tracking' => array(
                    'name'        => 'allow_tracking',
                    'type'        => 'checkbox',
                    'default'     => '',
                    'label'       => __( 'Allow Tracking', 'GMW' ),
                    'cb_label'    => __( 'Enable', 'GMW' ),
                    'desc'        => __( "Check this checkbox if you want to use client-side to geocode the address entered in GEO my WP's search form. clients-de geocoding might prevent Google API's OVER_QUERY_LIMIT issue.", 'GMW' ),                  
                    'attributes'  => array(),
                    'priority'    => 5
                ),
				'google_api'    => array(
					'name'        => 'google_api',
                    'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'Enter your google api key', 'GMW' ),
					'label'       => __( 'Google Maps API V3 Key', 'GMW' ),
					'desc'        => __( 'This is optional but will let you track your API requests. you can obtain your free Goole API key <a href="https://code.google.com/apis/console/" target="_blank">here</a>.', 'GMW' ),
					'attributes'  => array( 'size' => '50' ),
                    'priority'    => 10
				),
				'js_geocode' => array(
					'name'        => 'js_geocode',
                    'type'        => 'checkbox',
					'default'     => '',
					'label'       => __( 'Client-side Geocoder', 'GMW' ),
					'cb_label'    => __( 'Enable', 'GMW' ),
					'desc'        => __( "Check this checkbox if you want to use client-side to geocode the address entered in GEO my WP's search form. clients-de geocoding might prevent Google API's OVER_QUERY_LIMIT issue.", 'GMW' ),					
					'attributes'  => array(),
                    'priority'    => 15
				),
                'country_code' => array(
                    'name'        => 'country_code',
                    'type'        => 'text',
                    'default'     => 'US',
                    'placeholder' => 'ex. US',
                    'label'       => __( 'Country Code', 'GMW' ),
                    'desc'        => __( 'Enter the default country code to be used with GEO my WP. For example for United States enter US. List of country code can be found <a href="http://geomywp.com/country-code/" target="blank">here</a>', 'GMW' ),
                    'attributes'  => array( 'size' => '5' ),
                    'priority'    => 20
                ),
				'language_code' => array(
					'name'        => 'language_code',
                    'type'        => 'text',
					'default'     => 'EN',
					'placeholder' => 'ex. EN',
					'label'       => __( 'Google API Language', 'GMW' ),
					'desc'        => __( 'Set the language to be used with Google Places address auto-complete and with Google Maps API. The language codes can be found','GMW'). '<a href="https://spreadsheets.google.com/spreadsheet/pub?key=0Ah0xU81penP1cDlwZHdzYWkyaERNc0xrWHNvTTA1S1E&gid=1" target="_blank"> '.__('here', 'GMW' ) .'</a>',
					'attributes'  => array( 'size' => '5' ),
                    'priority'    => 25
				),	
				'auto_locate' => array(
					'name'        => 'auto_locate',
                    'type'        => 'checkbox',
					'default'     => '',
					'label'       => __( 'Auto Locator', 'GMW' ),
					'cb_label'    => __( 'Enable', 'GMW' ),
					'desc'        => __( "GEO my WP will try to retrive the user's current location when first visits the website. If a location found it will be saved via cookies and later will be used to automatically display results based on that.", 'GMW' ),
					'attributes'  => array(),
                    'priority'    => 30
				),
				'results_page' => array(
					'name'        => 'results_page',
                    'type'        => 'select',
					'default'     => '0',
					'label'       => __( 'Results Page', 'GMW' ),
					'desc'        => __( "This page displays the search results ( of any of your forms ) when using the \"GMW Search Form\" widget. The plugin will first check if a results page was set in the form settings and if so the results will be displayed in that page. Otherwise, if no results page was set in the form settings the results will be displayed in the page you choose from the select box. Choose the page from the dropdown menu and paste the shortcode <code>[gmw form=\"results\"]</code> in there.", "GMW" ),
					'options'     => $this->get_pages(),
                    'attributes'  => array(),
                    'priority'    => 35
				),
			),
    	) );
        
        // backward capability for settings before settings groups were created
        foreach ( $this->settings as $key => $section ) { 

            if ( ! empty( $section[0] ) && ! empty( $section[1] ) && is_string( $section[0] ) ) {
                
                trigger_error( 'Using deprecated method for registering GMW settings and settings groups.', E_USER_NOTICE );

                $this->settings_tabs[] = array(
                    'id'        => $key,
                    'label'     => $section[0],
                    'icon'      => '',
                    'priority'  => 99
                );

                $this->settings[$key] = $section[1];
            }            
        }

        // backward capability for replacing std with default
        foreach ( $this->settings as $key => $section ) { 

            foreach ( $section as $sec_key => $sec_value ) {
                
                if ( ! isset( $sec_value['default'] ) ) {

                    trigger_error( '"std" attribute is no longer supported in GMW settings and was replaced with "default" in version 3.0.', E_USER_NOTICE );

                    $this->settings[$key][$sec_key]['default'] = ! empty( $sec_value['std'] ) ? $sec_value['std'] : '';

                    unset( $this->settings[$key][$sec_key]['std'] );
                }
            }        
        }
    }

    /**
     * Get list of pages
     * 
     * @return array of pages
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
    
   		if ( empty( $_POST['option_page'] ) || $_POST['option_page'] != $this->settings_group ) {
   			return;
        }

   		if ( empty( $_POST['action'] ) || $_POST['action'] != 'update' ) {
   			return;
        }

   		register_setting( $this->settings_group, 'gmw_options', array( $this, 'validate') );
    }
    
    /**
     * Validate inputs
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    function validate( $values ) { 	
        
        $this->init_settings();

        //get the submitted values into the valid_input array
        //then below we run validation through the valid_input 
        $valid_input = $values;

        foreach ( $this->settings as $section_name => $section ) {
            
            foreach ( $section as $option ) {
                
                switch ( $option['type'] ) {

                    case "tab_section" :

                    Break;

                    case "function" :

                        if ( ! empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = $values[$section_name][$option['name']];
                        }

                    break;

                    case "checkbox" :

                        if ( !empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = 1;
                        }
                        
                    break;
                    
                    case "multicheckbox" :

                        if ( empty( $values[$section_name][$option['name']] ) || ! is_array( $values[$section_name][$option['name']] ) ) {

                            $valid_input[$section_name][$option['name']] = is_array( $option['default'] ) ? $option['default'] : array();

                        } else {

                            foreach ( $option['options'] as $keyVal => $name ) {

                                if ( !empty( $values[$section_name][$option['name']][$keyVal] ) ) {
                                    $valid_input[$section_name][$option['name']][$keyVal] = 1; 
                                }
                            }
                        }

                    break;
                                             
                    case "multicheckboxvalues" :

                        if ( empty( $values[$section_name][$option['name']] ) || ! is_array( $values[$section_name][$option['name']] ) ) {

                            $valid_input[$section_name][$option['name']] = is_array( $option['default'] ) ? $option['default'] : array();

                        } else {

                            foreach ( $option['options'] as $keyVal => $name ) {

                                if ( in_array( $keyVal, $values[$section_name][$option['name']] ) ) {
                                    $valid_input[$section_name][$option['name']][] = $keyVal; 
                                }
                            }
                        }
                    break;
                    
                    case "select" :
                    case "radio" :
                        if ( ! empty( $values[$section_name][$option['name']] ) && in_array( $values[$section_name][$option['name']], array_keys( $option['options'] ) ) ) {
                            $valid_input[$section_name][$option['name']] = $values[$section_name][$option['name']];
                        } else {
                            $valid_input[$section_name][$option['name']] = ( !empty( $option['default'] ) ) ? $option['default'] : '';
                        }
                    break;

                    case "textarea" :
                        if ( ! empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = esc_textarea( $values[$section_name][$option['name']] );
                        } else {
                            $valid_input[$section_name][$option['name']] = ( !empty( $option['default'] ) ) ? esc_textarea( $option['default'] ) : '';
                        }
                    break;

                    case "text" :
                    case "password" :
                        if ( ! empty( $values[$section_name][$option['name']] ) ) {
                            $valid_input[$section_name][$option['name']] = sanitize_text_field( $values[$section_name][$option['name']] );
                        } else {
                            $valid_input[$section_name][$option['name']] = ( !empty( $option['default'] ) ) ? sanitize_text_field( $option['default'] ) : '';
                        }
                    break;
                }
            }
        }

    	return $valid_input;
    }

    /**
     * display settings 
     *
     * @access public
     * @return void
     */
    public function output() {
		
        $this->init_settings();
        $gmw_options = get_option( 'gmw_options' );
        
        ?>
        <div id="gmw-settings-page" class="wrap gmw-admin-page">

            <h2>
                <i class="gmw-icon-cog-alt"></i>
                
                <?php echo _e( 'GEO my WP Settings', 'GMW' ); ?>
                
                <?php gmw_admin_helpful_buttons(); ?>
            </h2>

            <div class="clear"></div>
            
            <form method="post" action="options.php" class="gmw-settings-form">

                <?php settings_fields( $this->settings_group ); ?>

                <?php
                if ( ! empty( $_GET[ 'settings-updated' ] ) ) {
                    
                    flush_rewrite_rules();

                    echo '<div class="updated fade gmw-settings-updated"><p>' . __( 'Settings successfully saved!', 'GMW' ) . '</p></div>';
                }
                ?>

                <div class="update-button-wrapper top">
                    <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'GMW' ); ?>" />
                </div>

                <div class="gmw-settings-wrapper gmw-left-tabs-menu-wrapper">

                    <ul class="gmw-tabs-wrapper">
                    	
                        <?php uasort( $this->settings_tabs, 'gmw_sort_by_priority' ); ?>

                        <?php foreach ( $this->settings_tabs as $tab ) { ?>

                            <li>
                        	   <a href="#" 
                               id="<?php echo sanitize_title( $tab['id'] ); ?>" 
                               title="<?php echo esc_attr( $tab['label'] ); ?>" 
                               class="gmw-nav-tab" 
                               data-name="<?php echo sanitize_title( $tab['id'] ); ?>"
                            >
                                <i class="<?php if ( ! empty( $tab['icon'] ) ) echo 'gmw-icon-'.esc_attr( $tab['icon'] );?>"></i>
                                <span><?php echo esc_attr( $tab['label'] ); ?></span>
                            </a>
                        
                        <?php } ?>
                    
                    </ul>

                    <div class="gmw-panels-wrapper">
                             
                        <?php foreach ( $this->settings as $key => $section ) { ?>

                            <?php uasort( $section, 'gmw_sort_by_priority' ); ?>

                            <div class="gmw-tab-panel <?php echo sanitize_title( $key ); ?>">
                                
        	                    <table class="widefat">

                                    <tbody>
            	                        <?php

            	                        foreach ( $section as $option ) {
            	                           
                                            // section tab
                                            if ( $option['type'] == 'tab_section' ) {
                                                ?>
                                                <tr valign="top" class="gmw-tab-section">
                                                    <td><?php echo esc_html( $option['title'] ); ?></td>
                                                    <td></td>    
                                                </tr>
                                                <?php
                                                continue;
                                            }

                                            $option['default']      = ! empty( $option['default'] ) ? $option['default'] : '';
                                            $option['type']     = ! empty( $option['type'] ) ? $option['type'] : '';
            	                            $placeholder        = ! empty( $option['placeholder'] ) ? 'placeholder="'.esc_attr( $option[ 'placeholder' ] ).'"' : '';
            	                            $class              = ! empty( $option[ 'class' ] ) ? $option[ 'class' ]. ' ' . $option['name'] . ' ' . $option['type'] : $option['name'] . ' ' . $option['type'] . ' ' . $key;
            	                            $value              = ! empty( $gmw_options[$key][$option['name']] ) ? $gmw_options[$key][$option['name']] : $option['default']; 
            	                            $attributes         = array();
                                            $option['cb_label'] = ! empty( $option['cb_label'] ) ? $option['cb_label'] : '';
                                            $attr_id            = 'setting-'.esc_attr( $key ) . '-' . esc_attr( $option['name'] );
                                            $attr_name          = 'gmw_options['.esc_attr( $key ).']['.esc_attr( $option['name'] ).']';

                                            //build attributes
            	                            if ( ! empty( $option['attributes'] ) && is_array( $option[ 'attributes' ] ) ) {
            	                                
                                                foreach ( $option['attributes'] as $attribute_name => $attribute_value ) {
            	                                    $attributes[] = esc_attr( $attribute_name ) . '="' . esc_attr( $attribute_value ) . '"';
            	                                }
            	                            }
                                            ?>

            	                            <tr valign="top" id="<?php echo $attr_id; ?>-row" class="feature-<?php echo esc_attr( $class ); ?>">
                                                
                                                <!-- feature description -->
                                                <td class="gmw-form-feature-desc">
                                                    
                                                    <label for="setting-<?php echo $option['name']; ?>"><?php echo $option['label']; ?></label>
                                                    <?php
                                                    if ( $option[ 'desc' ] ) {
                                                        echo '<em class="description">'. esc_attr( $option['desc'] ).'</em>';
                                                    }
                                                    ?>
                                                </td>
                                                
                                                <!-- feature settings -->
                                                <td class="gmw-form-feature-settings">
            	                                   
                                                <?php 
                	                            switch ( $option[ 'type' ] ) {
                	                                
                                                    //create custom function
                	                                case "function" :
                	                                    
                                                        $function  = ( !empty( $option['function'] ) ) ? $option['function'] : $option['name'];
                                                        $name_attr = 'gmw_options['.$key.']['.$option['name'].']';
                                                        $thisVAlue = ! empty( $gmw_options[$key][$option['name']] ) ? $gmw_options[$key][$option['name']] : array();

                	                                    do_action( 'gmw_main_settings_' . $function, $thisVAlue, $name_attr, $gmw_options, $key, $option );

                	                                break;
                	
                	                                case "checkbox" :
                	                                    ?><label><input type="checkbox" id="<?php echo $attr_id ?>" class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox" name="<?php echo $attr_name; ?>" value="1" <?php echo implode( ' ', $attributes ); ?> <?php checked( '1', $value ); ?> /> <?php echo $option['cb_label']; ?></label><?php          
                                                    break;
                	
                	                                case "multicheckbox" :
                	                                    
                                                        foreach ( $option[ 'options' ] as $keyVal => $name ) {

                                                            $value = ! empty( $gmw_options[$key][$option['name']][$keyVal] ) ? $gmw_options[$key][$option['name'] ][$keyVal] : $option['default']; ?>

                                                            <label>
                                                                <input 
                                                                    type="checkbox" 
                                                                    id="<?php echo $attr_id .'-'. esc_attr( $keyVal ); ?>" class="setting-<?php echo $option['name']; ?> checkbox multicheckbox"
                                                                    name="<?php echo $attr_name.'['.$keyVal.']'; ?>" 
                                                                    value="1" <?php checked( '1', $value ); ?> 
                                                                /> 
                                                                <?php echo esc_attr( $name ); ?>
                                                            </label>
                                                            <?php
                	                                    }
                	                                break;
                	                                                            
                                                    case "multicheckboxvalues" :

                                                        $option['default'] = is_array( $option['default'] ) ? $option['default'] : array();
                                                        $gmw_options[$key][$option['name']] = ( !empty( $gmw_options[$key][$option['name']] ) && is_array( $gmw_options[$key][$option['name']] ) ) ? $gmw_options[$key][$option['name']] : $option['default'];

                                                        foreach ( $option['options'] as $keyVal => $name ) {

                                                            $checked = in_array( $keyVal, $gmw_options[$key][$option['name']] ) ? 'checked="checked"' : '';
                                                            ?><label><input type="checkbox" id="<?php echo $attr_id .'-'. esc_attr( $keyVal ); ?>" class="setting-<?php echo esc_attr( $option['name'] ); ?> checkbox multicheckboxvalues" name="<?php echo $attr_name.'[]'; ?>" value="<?php echo sanitize_title( $keyVal ); ?>" <?php echo $checked; ?> /> <?php echo $name; ?></label><?php
                                                        }
                                                    break;
                                                    
                	                                case "textarea" :
                	                                	?><textarea id="<?php echo $attr_id ?>" class="<?php echo 'setting-'.esc_attr( $option['name'] );?> textarea large-text" cols="50" rows="3" name="<?php echo $attr_name; ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?>><?php echo esc_textarea( $value ); ?></textarea><?php
                	                                break;
                	
                	                                case "radio" :
                	                                	$rc = 1;
                	                                	foreach ( $option['options'] as $keyVal => $name ) {
                	                                    	$checked = ( $rc == 1 ) ? 'checked="checked"' : checked( $value, $keyVal, false );
                	                                     	echo '<label><input type="radio" id="'.$attr_id.'" class="setting-'.esc_attr( $option['name'] ).'" name="<?php echo $attr_name; ?>" value="'.esc_attr( $keyVal ).'" '.$checked.' />'.$name.'</label>&nbsp;&nbsp;';
                	                                    	$rc++;
                	                                    }
                	                                break;
                	
                	                                case "select" :
                	                                	?>
                	                                	<select id="<?php echo $attr_id ?>" class="<?php echo 'setting-'.esc_attr( $option['name'] );?> select" name="<?php echo $attr_name; ?>" <?php echo implode( ' ', $attributes ); ?>>
                	                             			<?php foreach ( $option[ 'options' ] as $keyVal => $name ) { ?>
                	                                			<?php echo '<option value="' . sanitize_title( $keyVal ) . '" ' . selected( $value, $keyVal, false ) . '>' . esc_html( $name ) . '</option>'; ?>
                											<?php } ?>
                	                                	</select>
                	                                	<?php 
                	                                break;
                	
                	                                case "password" :
                	                                    ?>
                	                                    <input type="password" id="<?php echo $attr_id ?>" class="<?php echo 'setting-'.esc_attr( $option['name'] );?> regular-text password" name="<?php echo $attr_name; ?>" value="<?php echo sanitize_text_field( esc_attr( $value ) ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
                	                                    <?php
                	                                break;
                                                    
                                                    case "" :
                                                    case "input" :
                                                    case "text" :   
                	                                default :
                	                                	?>
                	                                	<input type="text" id="<?php echo $attr_id ?>" class="<?php echo 'setting-'.esc_attr( $option['name'] );?> regular-text text" name="<?php echo $attr_name; ?>" value="<?php echo sanitize_text_field( esc_attr( $value ) ); ?>" <?php echo implode( ' ', $attributes ); ?> <?php echo $placeholder; ?> />
                	                                	<?php
                	                                break;
                	                            }
            	
            	                               echo '</td>';
            	                            echo '</tr>';
            	                        }
            	                        ?>
                                    </tbody>
                                    <!--
        	                        <tfoot>
        	                            <tr style="height:40px;">
        	                                <th scope="col" id="id" class="manage-column"></th>
                                             <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px;text-align: right">
                                                <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
                                            </th>
        	                            </tr>
        	                        </tfoot>
                                    -->
        	                    </table>
                            </div>
                    	<?php } ?>
                    </div>
                </div> <!-- menu wrapper -->

                <div class="update-button-wrapper bottom">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes', 'GMW'); ?>" />
                </div>

        	</form>
        </div>
        <?php   
            // load chosen  
            if ( ! wp_script_is( 'chosen', 'enqueued' ) ) {
                wp_enqueue_script( 'chosen' );
                wp_enqueue_style( 'chosen' );
            }
        ?>
        <!-- enable chosen on all select menu -->
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            
            $( '.gmw-form-feature-settings select' ).addClass( 'gmw-chosen' );

            $(".gmw-chosen").chosen({
                width:"100%"
            });
        });
        </script>
        <?php
    }
}