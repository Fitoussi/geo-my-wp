<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_FL_Admin class.
 */

class GMW_FL_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        $this->settings = get_option( 'gmw_options' );

        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button'    ), 10, 1 );
        add_filter( 'gmw_friends_form_settings', array( $this, 'form_settings_init' ), 10, 1 );
        add_filter( 'gmw_admin_shortcodes_page', array( $this, 'shortcodes_page'    ) ,10, 10 );

        //form settings
        add_action( 'gmw_friends_form_settings_xprofile_fields', array( $this, 'form_settings_xprofile_fields' ), 10, 4 );
        add_action( 'gmw_friends_form_settings_show_avatar', 	 array( $this, 'show_avatar' 				   ), 10, 4 );
    }

    /**
     * New form button function.
     *
     * @access public
     * @return $buttons
     */
    public function new_form_button( $buttons ) {

        $buttons[10] = array(
            'name'       => 'friends',
            'addon'      => 'friends',
            'title'      => __( 'Members Locator', 'GMW' ),
            'link_title' => __( 'Create new Members form', 'GMW' ),
            'prefix'     => 'fl',
            'color'      => 'FFC793'
        );
        return $buttons;

    }

    public static function form_settings_xprofile_fields( $gmw_forms, $formID, $section, $option ) {
        global $bp;

        if ( !bp_is_active( 'xprofile' ) ) {
        	_e( 'Buddypress xprofile fields component is deactivated.  You will need to activate in in order to use this feature.', 'GMW' );
        	return;
        }

            if ( function_exists( 'bp_has_profile' ) ) {
            
                if ( bp_has_profile( 'hide_empty_fields=0' ) ) {

                    $dateboxes    = array();
                    $dateboxes[0] = '';

                    while ( bp_profile_groups() ) {
                        
                    	bp_the_profile_group();

                        while ( bp_profile_fields() ) {
                            bp_the_profile_field();

                            if ( ( bp_get_the_profile_field_type() == 'datebox' || bp_get_the_profile_field_type() == 'birthdate'  ) ) {
                                $dateboxes[] = bp_get_the_profile_field_id();
                            } else {

                                $field_id = bp_get_the_profile_field_id();
                                ?>
                                <input type="checkbox" name="<?php echo 'gmw_forms[' . $formID . '][' . $section . '][profile_fields][]'; ?>" value="<?php echo $field_id; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['profile_fields'] ) && in_array( $field_id, $gmw_forms[$formID][$section]['profile_fields'] ) ) echo ' checked=checked'; ?>/>
                                <label><?php bp_the_profile_field_name(); ?></label>
                                <br />
                                <?php
                            }
                        }
                    }
                    ?>

                    <label><strong style="margin:5px 0px;float:left;width:100%"><?php _e( 'Choose the "Age Range" Field', 'GMW' ); ?></strong></label><br />
                    <select name="<?php echo 'gmw_forms[' . $formID . '][' . $section . '][profile_fields_date]'; ?>">
                        <?php
                        foreach ( $dateboxes as $datebox ) {

                            $field    = new BP_XProfile_Field( $datebox );
                            $selected = ( $gmw_forms[$formID][$section]['profile_fields_date'] == $datebox ) ? 'selected="selected"' : '';
                            ?>
                            <option value="<?php echo $datebox; ?>" <?php echo $selected; ?> ><?php echo $field->name; ?></option>
                        <?php } ?>
                    </select> 
                    <?php
                }
            }
    }

    /**
     * Avatar
     */
    public function show_avatar( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <p>
                <input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][use]'; ?>" value="1" <?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][width]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['width'] ) && !empty( $gmw_forms[$formID][$section]['avatar']['width'] ) ) ? $gmw_forms[$formID][$section]['avatar']['width'] : '200px'; ?>" />
            </p>
            <p>
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][avatar][height]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['avatar']['height'] ) && !empty( $gmw_forms[$formID][$section]['avatar']['height'] ) ) ? $gmw_forms[$formID][$section]['avatar']['height'] : '200px'; ?>" />
            </p>
        </div>
        <?php
    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {
    	
    	unset( $settings['search_results'][1]['auto_results'], $settings['search_results'][1]['auto_all_results'] );
    	
    	//search form features
    	$newValues = array(
    			'xprofile_fields' => array(
    					'name'  => 'xprofile_fields',
    					'std'   => '',
    					'label' => __( 'Xprofile Fields', 'GMW' ),
    					'desc'  => __( 'Choose the Xprofile fields that you want to display in the search form. You can choose one or more Xprofile fields created using checkboxs, select box and multiselect box that will all be displayed as checkboxes. You can also choose one Xprofile field created using date field for the age range field.', 'GMW' ),
    					'type'  => 'function',
    			),    	
    	);
    	 
    	$afterIndex = 0;
    	$settings['search_form'][1] = array_merge( array_slice( $settings['search_form'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_form'][1], $afterIndex + 1 ) );
    	  
    	//seaerch results feature
    	$newValues = array(
    			'display_members'  => array(
    					'name'     => 'display_members',
    					'std'      => '',
    					'label'    => __( 'Display Members?', 'GMW' ),
    					'desc'     => __( 'Display results as list of memebrs', 'GMW' ),
    					'type'     => 'checkbox',
    					'cb_label' => __( 'Yes', 'GMW' ),
    			),
    			'show_avatar'      => array(
    					'name'  => 'show_avatar',
    					'std'   => '',
    					'label' => __( 'Avatar', 'GMW' ),
    					'desc'  => __( 'Display avatar and define its width and height in pixels or percentage.', 'GMW' ),
    					'type'  => 'function',
    			)
    	);
    	
    	$afterIndex = 3;
    	$settings['search_results'][1] = array_merge( array_slice( $settings['search_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_results'][1], $afterIndex + 1 ) );
    	    	
    	return $settings;
    }
    
    public function shortcodes_page( $shortcodes ) {

    	$shortcodes['member_info'] = array(
    			'name'		  	=> __( 'Member Information', 'GMW' ),
    			'basic_usage' 	=> '[gmw_member_info]',
    			'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_member_info]\'); &#63;&#62;',
    			'desc'        	=> __( "Display member's the location information.", 'GMW' ),
    			'attributes'  	=> array(
    					array(
    							'attr'	 	=> 'user_id',
    							'values' 	=> array(
    									'User ID',
    							),
    							'desc'	 	=> __( "Use the user_id only if you want to display information of a specific member. When using the shortcode on a profile page or within a members loop you don't need to use the user_id attribute. The shortcode will use the user ID of the member that is being displayed or the user ID of each member within the members loop.", 'GMW')
    					),
    					array(
    							'attr'	 	=> 'info',
    							'values' 	=> array(
    									'street',
    									'apt',
    									'city',
    									'state'.__( ' - state short name (ex FL )', 'GMW' ),
    									'state_long'.__( ' - state long name (ex Florida )', 'GMW' ),
    									'zipcode',
    									'country' .__( ' - country short name (ex IL )','GMW' ),
    									'country_long'. __( ' - country long name (ex Israel )', 'GMW' ),
    									'address',
    									'formatted_address',
    							),
    							'default'	=> 'formatted_address',
    							'desc'	 	=> __( 'Use a single value or multiple values comma separated of the information you would like to display. For example you can use info="city,state,country_long" to display "Hollywood FL United States"', 'GMW')
    					),

    					array(
    							'attr'	 => 'divider',
    							'values' => array(
    									__( 'any character','GMW' ),
    							),
    							'desc'	 => __( 'Use any character that you would like to display between the fields you choose above"', 'GMW')
    					),
    			),
    			'examples'  => array(
    					array(
    							'example' => "[gmw_member_info user_id=\"3\" info=\"city,state_long,zipcode\" divider=\",\"]",
    							'desc'	  => __( "This shortcode will display the information of the memebr with ID 3 which is ( for example ) \"Hollywood,Florida,33021\".", 'GMW' )

    					),
    					array(
    							'example' => "[gmw_member_info info=\"city,state\" divider=\"-\"]",
    							'desc'	  => __( 'Use the shortcode without user_id when within a members loop to display the city and state of each member a memebrs loop.', 'GMW' )

    					),
    					array(
    							'example' => "City: [gmw_member_info info=\"city\"] <br />
    										  State: [gmw_member_info info=\"state\"]<br />
    										  Country: [gmw_member_info info=\"country_long\"]",
    							'desc'	  => __( 'Use this example in a profile page to display the information of a member:', 'GMW' ).'<br />City: Hollywood <br /> State: FL <br/ > Country: United States'
    					),
    			),

    	);

    	return $shortcodes;
    }
}
new GMW_FL_Admin();
?>