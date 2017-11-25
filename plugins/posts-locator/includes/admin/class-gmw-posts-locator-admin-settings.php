<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_PT_Admin class
 * 
 * Post type locator admin functions
 */
class GMW_Posts_Locator_Admin_Settings {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // setup default values for settings
        add_filter( 'gmw_admin_settings_setup_defaults', array( $this, 'setup_defaults' ) );
        add_filter( 'gmw_admin_settings_groups', array( $this, 'admin_settings_group' ), 5 );
        add_filter( 'gmw_admin_settings', array( $this, 'admin_settings' ), 5 );
        add_action( 'gmw_main_settings_edit_post_map_settings', array( $this, 'edit_post_map_settings' ), 10, 5 );
    }

    /**
     * Defaukt options will setup when no options exist. Usually when plugin first installed.
     * 
     * @param  [type] $defaults [description]
     * @return [type]           [description]
     */
    public function setup_defaults( $defaults ) {

        $defaults['post_types_settings'] = array(
            
            'edit_post_exclude_tabs' => array(
                'dynamic'    => 1,
                'address'    => 1,
                'coords'     => 1,
                'contact'    => 1,
                'days-hours' => 1
            ),
            'edit_post_map_settings' => array(
                'latitude'    => '40.711544',
                'longitude'   => '-74.013486', 
                'map_type'    => 'ROADMAP',
                'zoom_level'  => 7   
            ),         
            'post_types' => array(
                'post'
            )
        );

        return $defaults;
    }

    /**
     * Create Post Types settings group
     * 
     * @param  [type] $groups [description]
     * @return [type]         [description]
     */
    public function admin_settings_group( $groups ) {

        $groups[] = array(
            'id'       => 'post_types_settings',
            'label'    => __( 'Post Types', 'GMW' ),
            'icon'     => 'pinboard', 
            'priority' => 10
        );  

        return $groups;
    }

    /**
     * post types settings in GEO my WP main settings page
     *
     * @access public
     * @return $settings
     */
    public function admin_settings( $settings ) {

    	$settings['post_types_settings'] = array(
			'post_types' => array(
				'name'          => 'post_types',
                'type'          => 'multiselect',
				'default'       => '',
				'label'         => __( 'Post Types', 'GMW' ),
				'desc'          => __( 'Select the post type where you would like to enable geotagging. GEO my WP Location section will be added to the "Edit Post" page of the selected post types.', 'GMW' ),
                'options'       => gmw_get_post_types_array(),
                'attributes'    => array(),
                'priority'      => 10
			),
            'edit_post_map_settings' => array(
                'name'       => 'edit_post_map_settings',
                'type'       => 'function',
                'default'    => array(
                    'latitude'    => '40.711544',
                    'longitude'   => '-74.013486', 
                    'map_type'    => 'ROADMAP',
                    'zoom_level'  => 7   
                ),
                'label'      => __( 'Map Settings ( admin\'s "Edit Post" Page )', 'GMW' ),
                'desc'       => __( 'Deafult settings for the map displayed in the Location section in the "Edit Post" page.' , 'GMW' ),
                'attributes' => array(),
                'priority'   => 20
            ),
			'mandatory_address' => array(
				'name'          => 'mandatory_address',
                'type'          => 'checkbox',
				'default'       => 0,
				'label'         => __( 'Mandatory Address fields', 'GMW' ),
				'cb_label'      => __( 'Yes', 'GMW' ),
				'desc'          => __( 'Check this checkbox to make the post types address field required.', 'GMW' ),
				'attributes'    => array(),
                'priority'      => 30
			)
    	);

      	return $settings;
    }

    /**
     * Edit post page settings
     * 
     * @param  [type] $value        [description]
     * @param  [type] $name_attr    [description]
     * @param  [type] $gmw_settings [description]
     * @param  [type] $key          [description]
     * @param  [type] $option       [description]
     * @return [type]               [description]
     */
    public function edit_post_map_settings( $value, $name_attr, $gmw_settings, $key, $option ) {
        
        $name_attr = esc_attr( $name_attr );

        if ( empty( $value ) ) {
            $value = $option['default'];
        }
        ?>
        <div class="gmw-options-box">
            
            <div class="single-option">
                
                <label><?php _e( 'Default Latitude', 'GMW' ); ?>:</label>
                
                <div class="option-content">
                    <input 
                        type="text" 
                        class="regular-text text" 
                        name="<?php echo $name_attr.'[latitude]'; ?>]" 
                        value="<?php echo esc_attr( $value['latitude'] ); ?>" 
                        placeholder="Latitude"
                    />
                </div>
                  
                <label><?php _e( 'Default Latitude', 'GMW' ); ?>:</label>
                
                <div class="option-content">
                    <input 
                        type="text" 
                        class="regular-text text" 
                        name="<?php echo $name_attr.'[longitude]'; ?>]" 
                        value="<?php echo esc_attr( $value['longitude'] ); ?>" 
                        placeholder="Longitude"
                    />
                </div>
            
                <label><?php _e( 'Map Type', 'GMW' ); ?>:</label>

                <div class="option-content">
                    <select name="<?php echo $name_attr.'[map_type]'; ?>">
                        <?php foreach ( array( 'ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN' ) as $option ) { ?>
                            <option value="<?php echo $option; ?>" <?php if ( $option == $value['map_type'] ) echo 'selected="selected"'; ?>><?php echo $option; ?></option> 
                        <?php } ?>                                                             
                    </select>
                </div>
         
                <label><?php _e( 'Zoom Level', 'GMW' ); ?>:</label>
                
                <div class="option-content">
                    <select name="<?php echo $name_attr.'[zoom_level]'; ?>">
                        <?php for ( $i = 1; $i <= 21; $i++ ) { ?>
                            <option value="<?php echo $i; ?>" <?php if ( $i == $value['zoom_level'] ) echo 'selected="selected"'; ?> >
                                <?php echo $i; ?>
                            </option>
                        <?php } ?>    
                    </select>
                </div>
            </div>
        </div>
        <?php
    }
}
new GMW_Posts_Locator_Admin_Settings();
?>