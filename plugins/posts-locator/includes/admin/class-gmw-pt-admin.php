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
class GMW_Posts_Locator_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        add_filter( 'gmw_admin_settings_setup_defaults', array( $this, 'setup_defaults' ) );

        // post types main settings page
        //create post types settings tab/group 
        add_filter( 'gmw_admin_settings_groups', array( $this, 'admin_settings_group' ), 5 );
        
        // load low priority to prevent other add-ons to load before
        add_filter( 'gmw_admin_settings', array( $this, 'admin_settings' ), 5 );

        // main settings custom function page
        add_action( 'gmw_main_settings_post_types', array( $this, 'main_settings_post_types' ), 5, 2 );

        // post type form settings - load early to prevent add-on to load earlier
        add_filter( 'gmw_posts_locator_form_settings', array( $this, 'form_settings_init' ), 5 );

        // form custom functions
        add_action( 'gmw_posts_locator_form_settings_post_types', array( $this, 'form_settings_post_types' ), 10, 3 );
        add_action( 'gmw_posts_locator_form_settings_excerpt', array( $this, 'form_settings_excerpt' ), 10, 2 );
        add_action( 'gmw_posts_locator_form_settings_form_taxonomies', array( $this, 'form_settings_form_taxonomies' ), 10, 3 );
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
            'edit_post_map_type'    => 'ROADMAP',
            'edit_post_zoom_level'  => 7,
            'edit_post_latitude'    => '40.711544',
            'edit_post_longitude'   => '-74.013486',          
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
            
            /*
            'edit_post_exclude_tabs' => array(
                'name'     => 'edit_post_exclude_tabs',
                'type'     => 'multicheckbox',
                'default'  => array(),
                'label'    => __( '"Edit Post" page - Exclude location tabs', 'GMW' ),
                'desc'     => __( 'Exclude tabs from GEO my WP Location section of the "Edit post" page.' , 'GMW' ),
                'options'  => array(
                    'dynamic'       => __( 'Dynamic ( map and autocomplete tab )', 'GMW' ),
                    'address'       => __( 'Address', 'GMW' ),
                    'coords'        => __( 'Coordinates', 'GMW' ),
                    'contact'       => __( 'Contact Info', 'GMW' ),
                    'days-hours'    => __( 'Days & Hours', 'GMW' ),
                ),
                'attributes'    => array(),
                'priority'      => 5
            ),
            */
           
            'edit_post_map_type' => array(
                'name'     => 'edit_post_map_type',
                'type'     => 'select',
                'default'  => 'ROADMAP',
                'label'    => __( '"Edit Post" page - map type', 'GMW' ),
                'desc'     => __( 'Select the map type.' , 'GMW' ),
                'options'  => array(
                    'ROADMAP'   => 'ROADMAP',
                    'SATELLITE' => 'SATELLITE',
                    'HYBRID'    => 'HYBRID',
                    'TERRAIN'   => 'TERRAIN'
                ),
                'attributes'    => array(),
                'priority'      => 10
            ),
			'edit_post_zoom_level' => array(
				'name'     => 'edit_post_zoom_level',
                'type'     => 'select',
				'default'  => '7',
				'label'    => __( "\"Edit Post\" page - map's zoom level", "GMW" ),
				'desc'     => __( "Set the default zoom level of the map being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
				'options'  => array(
					'1'    => '1',
					'2'    => '2',
					'3'    => '3',
					'4'    => '4',
					'5'    => '5',
					'6'    => '6',
					'7'    => '7',
					'8'    => '8',
					'9'    => '9',
					'10'   => '10',
					'11'   => '11',
					'12'   => '12',
					'13'   => '13',
					'14'   => '14',
					'15'   => '15',
					'16'   => '16',
					'17'   => '17',
					'18'   => '18',
				),
                'attributes'    => array(),
                'priority'      => 15
			),
			'edit_post_latitude' => array(
				'name'  	    => 'edit_post_latitude',
                'type'          => 'text',
				'default'	    => '40.711544',
                'placeholder'   => __( 'latitude', 'GMW' ),
				'label' 	    => __( "\"Edit Post\" page - default latitude", "GMW" ),
				'desc'  	    => __( "Set the latitude of the default location being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
				
				'attributes'    => array(),
                'priority'      => 20
			),
			'edit_post_longitude' => array(
				'name'  	    => 'edit_post_longitude',
                'type'          => 'text',
				'default'	    => '-74.013486',
                'placeholder'   => __( 'longitude', 'GMW' ),
				'label' 	    => __( "\"Edit Post\" page - default longitude", "GMW" ),
				'desc'  	    => __( "Set the longitude of the default location being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
				'attributes'    => array(),
                'priority'      => 25
			),
			'post_types' => array(
				'name'          => 'post_types',
                'type'          => 'function',
				'default'       => '',
				'label'         => __( 'Post Types', 'GMW' ),
				'desc'          => __( "Check the checkboxes of the post types which you'd like to add locations to. GEO my WP's location section will be displayed in the new/edit post screen of the post types you choose here. ", 'GMW' ),
                'attributes'    => array(),
                'priority'      => 30
			),
			'mandatory_address' => array(
				'name'          => 'mandatory_address',
                'type'          => 'checkbox',
				'default'       => 0,
				'label'         => __( 'Mandatory Address fields', 'GMW' ),
				'cb_label'      => __( 'Yes', 'GMW' ),
				'desc'          => __( 'Check this box if you want to make sure that users will add location toa post they create or update; It will prevent them from saving a post that do not have a location. Otherwise, users will be able to save a post even without a location. This way the post will be published and would show up in Wordpress search results but not in GEO my WP search results.', 'GMW' ),
				'attributes'    => array(),
                'priority'      => 35
			)
    	);
		
      	return $settings;
    }

    /**
     * Post types in main settings
     */
    public function main_settings_post_types( $value, $name_attr ) {
        ?>	
        <div>
        	<?php foreach ( get_post_types() as $post ) { ?>
				
                <?php $checked = ( ! empty( $value ) && in_array( $post, $value ) ) ? ' checked="checked"' : ''; ?>
            	
                <p><label>
                    <input type="checkbox" name="<?php echo $name_attr.'[]'; ?>" value="<?php echo esc_attr( $post ); ?>" id="<?php echo esc_attr( $post ); ?>" class="post-types-tax" <?php echo $checked; ?>>     
                    <?php echo esc_html( get_post_type_object( $post )->labels->name . ' ( '. $post .' ) ' ); ?>
                </label></p>

         	<?php } ?>
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

        //page laod features
        $settings['page_load_results']['post_types'] = array( 
            'name'          => 'post_types',
            'type'          => 'multicheckbox',
            'default'       => array( 'post' ),
            'label'         => __( 'Post Types', 'GMW' ),
            'desc'          => __( 'Choose the post types you would like to display.', 'GMW' ),
            'options'       => get_post_types(), 
            'attributes'    => '',
            'priority'      => 7     
        );
         
        //search form features
        $settings['search_form'] = array(
            'post_types' => array(
                'name'          => 'post_types',
                'type'          => 'function',
                'default'       => array( 'post' ),
                'label'         => __( 'Post Types', 'GMW' ),
                'desc'          => __( "Check the checkboxes of the post types you'd like to display in the search form. When selecting multiple post types they will be displayed as a dropdown menu.", 'GMW' ),
                'attributes'    => '',
                'priority'      => 17    
            ),
            'taxonomies' => array(
                'name'          => 'taxonomies',
                'type'          => 'function',
                'function'      => 'form_taxonomies',
                'default'       => '',
                'label'         => __( 'Taxonomies', 'GMW' ),
                'desc'          => __( "Choose the taxonomies that you'd like to display in the search form. The taxonomies will be displayed as a dropdown menues.", 'GMW' ),
                'attributes'    => '',
                'priority'      => 18   
            )                
        );
            
        $settings['search_results'] = array(
            'location_meta'  => array(
                'name'          => 'location_meta',
                'type'          => 'multicheckbox',
                'default'       => '',
                'label'         => __( 'Location Meta', 'GMW' ),
                'desc'          => __( "Check the checkboxes of the contact information which you'd like to display per location in the search results.", 'GMW' ),
                'options'       => array(
                    'phone'     => __( 'Phone', 'GMW' ),
                    'fax'       => __( 'Fax', 'GMW' ),
                    'email'     => __( 'Email', 'GMW' ),
                    'website'   => __( 'Website', 'GMW' ),
                ),
                'attributes'    => '',
                'priority'      => 50   
            ),
            'opening_hours'  => array(
                'name'          => 'opening_hours',
                'type'          => 'checkbox',
                'default'       => '',
                'label'         => __( 'Show opening hours', 'GMW' ),
                'cb_label'      => __( 'Yes', 'GMW' ),
                'desc'          => __( 'Display opening days & hours.', 'GMW' ),
                'attributes'    => '',
                'priority'      => 55   
            ),
            'excerpt'   => array(
                'name'          => 'excerpt',
                'type'          => 'function',
                'default'       => '',
                'label'         => __( 'Excerpt', 'GMW' ),
                'cb_label'      => '',
                'desc'          => __( 'Display the number of words that you choose from the post content and display it per location in the list of results.', 'GMW' ),
                'attributes'    => '',
                'priority'      => 60   
            ),
            'custom_taxes'   => array(
                'name'          => 'custom_taxes',
                'type'          => 'checkbox',
                'default'       => '',
                'label'         => __( 'Taxonomies', 'GMW' ),
                'cb_label'      => __( 'Yes', 'GMW' ),
                'desc'          => __( 'Display a list of taxonomies attached to each post in the list of results.', 'GMW' ),
                'attributes'    => '',
                'priority'      => 65   
            )
        );
         
        return $settings;
    }

    /**
     * Post types in form settings
     */
    public function form_settings_post_types( $value, $name_attr, $form ) {
        ?>
        <div class="posts-checkboxes-wrapper" id="<?php echo $form['ID']; ?>">
        	<?php foreach ( get_post_types() as $post ) { ?>
            	<?php $checked = ( ! empty( $value ) && in_array( $post, $value ) ) ? ' checked="checked"' : ''; ?>
                <p>
                	<input type="checkbox" name="<?php echo $name_attr.'[]'; ?>" value="<?php echo esc_attr( $post ); ?>" id="<?php echo esc_attr( $post ); ?>" class="post-types-tax" <?php echo $checked; ?> />
                	<label><?php echo esc_html( get_post_type_object( $post )->labels->name . ' ( '. $post .' ) ' ); ?></label>
                </p>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Taxonomies in form settongs
     */
    public function form_settings_form_taxonomies( $value, $name_attr, $form ) {
        ?>
        <div id="taxonomies-wrapper" style=" padding: 8px;">
            <?php
            foreach ( get_post_types() as $post ) {

                $taxes = get_object_taxonomies( $post );
                
                if ( ! empty( $taxes ) ) { 
                    
                    $style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) == 1 ) && is_array( $form['search_form']['post_types'] ) && ( in_array( $post, $form['search_form']['post_types'] ) ) ) ? '' : 'style="display:none"';

                    echo '<div id="post-type-'.$post.'-taxonomies-wrapper" class="post-type-taxonomies-wrapper" '.$style.'>';
                    
                        foreach ( $taxes as $tax ) {

                            echo '<div id="' . esc_attr( $post ) . '_cat' . '" class="taxonomy-wrapper">';

                                $nameAttr = esc_attr( $name_attr."[{$post}][{$tax}][style]" );
                                $selected  = ( ! empty( $value[$post][$tax]['style'] ) && ( $value[$post][$tax]['style'] == 'dropdown' || $value[$post][$tax]['style'] == 'drop' ) ) ? 'selected="seletced"' : '';

                                echo '<label>' . esc_html( get_taxonomy( $tax )->labels->singular_name ) . '</label>';
                                echo '<select name="'.$nameAttr.'">';
                                echo '<option value="na" checked="checked">' . __( 'Exclude', 'GMW' ).'</option>';
                                echo '<option value="dropdown" '.$selected.'>' . __( 'Dropdown', 'GMW' ). '</option>';
                                echo '</select>';
  
                            echo '</div>';
                        }

                    echo '</div>';
                }
            }

            $style = ( isset( $form['search_form']['post_types'] ) && ( count( $form['search_form']['post_types'] ) == 1 ) ) ? 'style="display: none;"' : ''; 

            echo '<div id="post-types-no-taxonomies-message" '.$style.'>';
            echo '<p>'.__( 'Taxonomies are not availabe for multiple post types.', 'GMW' ) .'</p>';
            echo '</div>';
            ?>
        </div>
        <?php
    }

    /**
     * excerpt in form settings
     */
    public static function form_settings_excerpt( $value, $name_attr ) {
        ?>
        <div class="gmw-ssb">
            <p>
                <input type="checkbox" value="1" name="<?php echo $name_attr.'[use]'; ?>" <?php echo ! empty( $value['use'] ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Words count ( leave blank to show the entire content )', 'GMW' ); ?>:
                <input type="text" name="<?php echo $name_attr.'[count]'; ?>" value="<?php echo ( ! empty( $value['count'] ) ) ? sanitize_text_field( esc_attr( $value['count'] ) ) : ''; ?>" size="5" />
            </p>
            <p>
                <?php _e( 'Read more link ( leave blank for no link )', 'GMW' ); ?>:
                <input type="text" name="<?php echo $name_attr.'[more]'; ?>" value="<?php echo ( ! empty( $value['more'] ) ) ? sanitize_text_field( esc_attr( $value['more'] ) ) : ''; ?>" size="15" />
            </p>
        </div>
        <?php
    }
}
new GMW_Posts_Locator_Admin();
?>