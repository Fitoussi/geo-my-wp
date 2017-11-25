<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_FL_Admin class.
 */
class GMW_Members_Locator_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        
        //Members Locator form settings page
        add_filter( 'gmw_members_locator_form_settings', array( $this, 'form_settings_init' ), 15 );

        //form custom functions
        add_action( 'gmw_members_locator_form_settings_profile_fields', array( $this, 'form_settings_profile_fields' ), 10, 2 );
  
        // validate profile fields field
        add_filter( 'gmw_validate_form_settings_profile_fields', array( $this, 'validate_profile_fields' ) );  
    }

    /**
     * form settings function.
     *
     * @access public
     * @return $settings
     */
    function form_settings_init( $settings ) {
                
        //search form features
        $settings['search_form']['profile_fields'] = array(
            'name'       => 'profile_fields',
            'type'       => 'function',
            'default'    => '',
            'label'      => __( 'Xprofile Fields', 'GMW' ),
            'desc'       => __( 'Choose the Xprofile fields that you want to display in the search form. You can choose one or more Xprofile fields created using checkboxs, select box and multiselect box that will all be displayed as checkboxes. You can also choose one Xprofile field created using date field for the age range field.', 'GMW' ),
            'attributes' => '',
            'priority'   => 11
        );
                
        return $settings;
    }

    /**
     * Form settings xprofile fields function

     * @param  [type] $formID    [description]
     * @param  [type] $section   [description]
     * @param  [type] $option    [description]
     * @return [type]            [description]
     */
    public static function form_settings_profile_fields( $value, $name_attr ) {
        
        global $bp;

        //show message if Xprofile Fields component deactivated
        if ( ! bp_is_active( 'xprofile' ) ) {
        	_e( 'Buddypress xprofile fields component is deactivated. You need to activate in in order to use this feature.', 'GMW' );
        	return;
        }

        //check for profile fields
        if ( function_exists( 'bp_has_profile' ) ) {
            
            $args = array ( 
                'hide_empty_fields' => false, 
                'member_type'       => bp_get_member_types()
            );

            //display profile fields
            if ( bp_has_profile( $args ) ) {

                $dateboxes    = array();
                $dateboxes[0] = '';
                ?>
                <label>
                    <strong style="margin:5px 0px;float:left;width:100%">
                        <?php _e( 'Select Profile Fields', 'GMW' ); ?>
                    </strong>
                </label>
                
                <br />

                <select name="<?php echo esc_attr( $name_attr.'[fields][]' ); ?>" multiple style="max-width: 500px;">';

                <?php 

                while ( bp_profile_groups() ) {
                    
                	bp_the_profile_group();

                    while ( bp_profile_fields() ) {
                        
                        bp_the_profile_field();

                        if ( ( bp_get_the_profile_field_type() == 'datebox' || bp_get_the_profile_field_type() == 'birthdate'  ) ) {
                            $dateboxes[] = bp_get_the_profile_field_id();
                        
                        } else {

                            $field_id   = bp_get_the_profile_field_id();
                            $field_name = bp_get_the_profile_field_name();
                            $field_type = bp_get_the_profile_field_type();

                            $selected  = ( isset( $value['fields'] ) && in_array( $field_id, $value['fields'] ) ) ? 'selected="slected"' : ''; ?>
                            ?>
                            <option value="<?php echo esc_attr( $field_id ); ?>" <?php echo $selected; ?>>
                                <?php echo esc_attr( $field_name ); ?> <!--( <?php echo esc_attr( $field_type ); ?> ) -->
                            </option>
                            <?php
                        }
                    }
                    
                }
                echo '</select>';
                ?>

                <!-- Display the date-range field -->
                <label style="margin-top: 20px; display: block;">
                    <strong style="margin:5px 0px;float:left;width:100%">
                        <?php _e( 'Select the "Age Range" profile Field', 'GMW' ); ?>
                    </strong>
                </label>
                
                <br />
                
                <select name="<?php echo esc_attr( $name_attr.'[date_field]' ); ?>">
                    <option value="" selected="selected"><?php _e( 'N/A', 'GMW' ); ?></option>
                    <?php
                    foreach ( $dateboxes as $datebox ) {

                        $field    = new BP_XProfile_Field( $datebox );
                        $selected = ( ! empty( $value['date_field'] ) && $value['date_field'] == $datebox ) ? 'selected="selected"' : '';
                    ?>
                    <option value="<?php echo esc_attr( $datebox ); ?>" <?php echo $selected; ?> ><?php echo esc_attr( $field->name ); ?></option>
                    <?php } ?>
                </select> 
                <?php
            }
        }
    }

    /**
     * validate xprofile fields
     * 
     * @param  [type] $output [description]
     * @return [type]         [description]
     */
    public function validate_profile_fields( $output ) {

        $output['fields']     = ! empty( $output['fields'] ) ? array_map( 'intval', $output['fields'] ) : array();
        $output['date_field'] = ! empty( $output['date_field'] ) ? intval( $output['date_field'] ) : '';

        return $output;
    }
}
new GMW_Members_Locator_Admin();
?>