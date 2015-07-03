<?php
if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

/**
 * GMW_PT_Admin class
 */

class GMW_PT_Admin {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {

        //check if we are in new/edit post page
        if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'post.php', 'page.php', 'page-new' ) ) ) {
            include_once GMW_PT_PATH . 'includes/admin/gmw-pt-metaboxes.php';
        }

        //add "address" column to manage posts page
        foreach ( gmw_get_option( 'post_types_settings', 'post_types', array() ) as $post_type ) {

            if ( $post_type == 'job_listing' || $post_type == 'resume')
                continue;

            add_filter( "manage_{$post_type}_posts_columns" , array( $this, 'add_address_column' ) );
            add_action( "manage_{$post_type}_posts_custom_column" , array( $this, 'address_column_content' ), 10, 2 );
        }

        add_filter( 'gmw_admin_settings', 		 array( $this, 'settings_init' 		), 1 );
        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button' 	), 1, 1 );
        add_filter( 'gmw_posts_form_settings', 	 array( $this, 'form_settings_init' ), 1, 1 );
        add_filter( 'gmw_admin_shortcodes_page', array( $this, 'shortcodes_page' 	),1 , 10 );
        		
        //main settings page
        add_action( 'gmw_main_settings_post_types', array( $this, 'main_settings_post_types' ), 1, 4 );

        //form settings
        add_action( 'gmw_posts_form_settings_post_types', 	   	   array( $this, 'form_settings_post_types' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_featured_image',      array( $this, 'featured_image' 			), 1, 4 );
        add_action( 'gmw_posts_form_settings_show_excerpt',    	   array( $this, 'show_excerpt' 			), 1, 4 );
        add_action( 'gmw_posts_form_settings_form_taxonomies', 	   array( $this, 'form_taxonomies' 			), 1, 4 );
    }

    /**
     * Add "address" column to manager posts page
     * @param  array $columns columns
     * @return columns
     */
    public function add_address_column( $columns ) {

        $new_columns = array();
        $no_col      = true;

        foreach ( $columns as $key => $column ) {
            if ( array_key_exists( 'comments', $columns ) && $key == 'comments' ) {
                $new_columns['gmw_address'] = __( 'Location', 'GMW' );
                $no_col = false;
            } elseif ( !array_key_exists( 'comments', $columns ) && array_key_exists( 'date', $columns ) && $key == 'date' ) {
                $new_columns['gmw_address'] = __( 'Location', 'GMW' );
                $no_col = false;
            } 
            $new_columns[$key] = $column;
        }

        if ( $no_col ) {
            $new_columns['gmw_address'] = __( 'Location', 'GMW' );
        }

        return $new_columns;
    }

    /**
     * Add content to custom column
     * @param  [type] $column  [description]
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function address_column_content( $column, $post_id ) {

        if ( $column != 'gmw_address' ) 
            return;

        global $wpdb;

        $address_ok = false;

        $location = $wpdb->get_row(
                $wpdb->prepare("
                        SELECT formatted_address, address FROM {$wpdb->prefix}places_locator
                        WHERE `post_id` = %d", array( $post_id )
                ) );

        if ( empty( $location ) ) {
            echo '<i class="fa fa-times-circle" style="color:red;margin-right:5px;"></i>'.__( 'No location found', "GMW" );
            return;
        }
        
        if ( !empty( $location->formatted_address ) ) {
            $address_ok = true;
            $address = $location->formatted_address;
        } elseif ( !empty( $location->address ) ) {
            $address_ok = true;
            $address = $location->address;
        } else {
            $address =  __( 'Location found but the address is missing', "GMW" );
        }
        
        $address = ( $address_ok == true ) ? '<a href="http://maps.google.com/?q='.$address.'" target="_blank" title="location">'.$address.'</a>' : '<span style="color:red">'.$address.'</span>';
        echo '<i class="fa fa-check-circle" style="color:green;margin-right:5px;" style="color:green"></i>'. $address;

    }

    /**
     * addon settings page function.
     *
     * @access public
     * @return $settings
     */
    public function settings_init( $settings ) {

    	$settings['post_types_settings'] = array(
    			__( 'Post Types', 'GMW' ),
    			array(
    					'edit_post_zoom_level' => array(
    							'name'    => 'edit_post_zoom_level',
    							'std'     => '7',
    							'label' 	 => __( "\"Edit Post\" page - map's zoom level", "GMW" ),
    							'desc'  	 => __( "Set the default zoom level of the map being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
    							'type'    => 'select',
    							'options' => array(
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
    							)
    					),
    					'edit_post_latitude' => array(
    							'name'  	 => 'edit_post_latitude',
    							'std'   	 => '40.7115441',
    							'label' 	 => __( "\"Edit Post\" page - default latitude", "GMW" ),
    							'desc'  	 => __( "Set the latitude of the default location being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
    							'type'  	 => 'text',
    							'attributes' => array()
    					),
    					'edit_post_longitude' => array(
    							'name'  	 => 'edit_post_longitude',
    							'std'   	 => '-74.01348689999998',
    							'label' 	 => __( "\"Edit Post\" page - default longitude", "GMW" ),
    							'desc'  	 => __( "Set the longitude of the default location being displayed in \"GMW section\" of the \"Edit Post\" page." , "GMW" ),
    							'type'  	 => 'text',
    							'attributes' => array()
    					),
    					array(
    							'name'  => 'post_types',
    							'std'   => '',
    							'label' => __( 'Post Types', 'GMW' ),
    							'desc'  => __( "Check the checkboxes of the post types which you'd like to add locations to. GEO my WP's location section will be displayed in the new/edit post screen of the post types you choose here. ", 'GMW' ),
    							'type'  => 'function'
    					),
    					array(
    							'name'       => 'mandatory_address',
    							'std'        => '',
    							'label'      => __( 'Mandatory Address fields', 'GMW' ),
    							'cb_label'   => __( 'Yes', 'GMW' ),
    							'desc'       => __( 'Check this box if you want to make sure that users will add location toa post they create or update; It will prevent them from saving a post that do not have a location. Otherwise, users will be able to save a post even without a location. This way the post will be published and would show up in Wordpress search results but not in GEO my WP search results.', 'GMW' ),
    							'type'       => 'checkbox',
    							'attributes' => array()
    					),
    			),
    	);
		
      	return $settings;
    }

    /**
     * New form button function.
     *
     * @access public
     * @return $buttons
     */
    public function new_form_button( $buttons ) {

    	$buttons[1] = array(
    			'name'       => 'posts',
    			'addon'      => 'posts',
    			'title'      => __( 'Posts Locator', 'GMW' ),
    			'link_title' => __( 'Create new post types form', 'GMW' ),
    			'prefix'     => 'pt',
    			'color'      => 'C3D5E6'
    	);
    	return $buttons;

    }

    /**
     * Post types main settings
     */
    public function main_settings_post_types( $gmw_options, $section, $option ) {
        $saved_data = ( isset( $gmw_options[$section]['post_types'] ) ) ? $gmw_options[$section]['post_types'] : array();
        ?>	
        <div>
        	<?php foreach ( get_post_types() as $post ) { ?>
				<?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $post, $saved_data ) ) ? ' checked="checked"' : ''; ?>
            	<p><label>
                <input type="checkbox" name="<?php echo 'gmw_options[' . $section . '][post_types][]'; ?>" value="<?php echo $post; ?>" id="<?php echo $post; ?>" class="post-types-tax" <?php echo $checked; ?>>
                <?php echo get_post_type_object( $post )->labels->name . ' ( '. $post .' ) '; ?>
                </label></p>
         	<?php } ?>
        </div>
        <?php
    }

    /**
     * Post types form settings
     */
    public function form_settings_post_types( $gmw_forms, $formID, $section, $option ) {
        $saved_data = ( isset( $gmw_forms[$formID][$section]['post_types'] ) ) ? $gmw_forms[$formID][$section]['post_types'] : array();
        ?>
        <div class="posts-checkboxes-wrapper" id="<?php echo $formID; ?>">
        	<?php foreach ( get_post_types() as $post ) { ?>
            	<?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $post, $saved_data ) ) ? ' checked="checked"' : ''; ?>
                <p>
                	<input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][post_types][]'; ?>" value="<?php echo $post; ?>" id="<?php echo $post; ?>" class="post-types-tax" <?php echo $checked; ?> />
                	<label><?php echo get_post_type_object( $post )->labels->name . ' ( '. $post .' ) '; ?></label>
                </p>
            <?php } ?>
        </div>
        <?php
    }

    /**
     * Taxonomies
     */
    public function form_taxonomies( $gmw_forms, $formID, $section, $option ) {
        $posts = get_post_types();
        ?>
        <div>
            <div id="taxonomies-wrapper" style=" padding: 8px;">
                <?php
                foreach ( $posts as $post ) :

                    $taxes = get_object_taxonomies( $post );

                    echo '<div id="' . $post . '_cat' . '" class="taxes-wrapper" ';
                    echo ( isset( $gmw_forms[$formID][$section]['post_types'] ) && (count( $gmw_forms[$formID][$section]['post_types'] ) == 1) && ( in_array( $post, $gmw_forms[$formID][$section]['post_types'] ) ) ) ? 'style="display: block; " ' : 'style="display: none;"';
                    echo '>';

                    foreach ( $taxes as $tax ) :

                        echo '<div style="border-bottom:1px solid #eee;padding-bottom: 10px;margin-bottom: 10px;" class="gmw-single-taxonomie">';
                        echo '<strong>' . get_taxonomy( $tax )->labels->singular_name . ': </strong>';
                        echo '<span id="gmw-st-wrapper">';
                        echo '<input type="radio" class="gmw-st-btns radio-na" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies]['.$post.'][' . $tax . '][style]" value="na" checked="checked" />' . __( 'Exclude', 'GMW' );
                        echo '<input type="radio" class="gmw-st-btns" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies]['.$post.'][' . $tax . '][style]" value="drop" ';
                        if ( isset( $gmw_forms[$formID][$section]['taxonomies'][$post][$tax]['style'] ) && $gmw_forms[$formID][$section]['taxonomies'][$post][$tax]['style'] == 'drop' )
                            echo "checked=checked"; echo ' style="margin-left: 10px; " />' . __( 'Dropdown', 'GMW' );
                        echo '</span>';

                        echo '</div>';

                    endforeach;

                    echo '</div>';

                endforeach;
                ?>
            </div>
        </div>
        <script>

            jQuery(document).ready(function($) {

                $(".post-types-tax").click(function() {

                    var cCount = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").length;
                    var scId = $(this).closest(".posts-checkboxes-wrapper").attr('id');
                    var pChecked = $(this).attr('id');

                    if (cCount == 1) {
                        var n = $(this).closest(".posts-checkboxes-wrapper").find(":checkbox:checked").attr('id');
                        $("#taxonomies-wrapper #" + n + "_cat").css('display', 'block');
                        if ($(this).is(':checked')) {
                            $("#taxonomies-wrapper .taxes-wrapper").css('display', 'none').find(".radio-na").attr('checked', true);
                            $("#taxonomies-wrapper #" + pChecked + "_cat").css('display', 'block');
                        } else {
                            $("#taxes-" + scId + " #" + pChecked + "_cat").css('display', 'none').find(".radio-na").attr('checked', true);
                        }
                    } else {
                        $("#taxonomies-wrapper .taxes-wrapper").css('display', 'none').find(".radio-na").attr('checked', true);
                    }
                });

            });
        </script>
        <?php

    }

    /**
     * Featured Image
     */
    public function featured_image( $gmw_forms, $formID, $section, $option ) {
    ?>
        <div>
            <p>
                <input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][featured_image][use]'; ?>" value="1" <?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'].']['.$section.'][featured_image][width]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['width'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['width'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['width'] : '200px'; ?>" />px          
            </p>
            <p>
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms['.$_GET['formID'].']['.$section.'][featured_image][height]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['height'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['height'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['height'] : '200px'; ?>" />px          
           </p>      
        </div>
    <?php
    }

    /**
     * excerpt 
     */
    public static function show_excerpt( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div class="gmw-ssb">
            <p>
                <input type="checkbox"  value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][use]'; ?>" <?php echo ( isset( $gmw_forms[$formID][$section]['excerpt']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Words count ( leave blank to show the eintire content )', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][count]'; ?>" value="<?php if ( isset( $gmw_forms[$formID][$section]['excerpt']['count'] ) ) echo $gmw_forms[$formID][$section]['excerpt']['count']; ?>" size="5" />
            </p>
            <p>
                <?php _e( 'Read more link ( leave blank for no link )', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][more]'; ?>" value="<?php if ( isset( $gmw_forms[$formID][$section]['excerpt']['more'] ) ) echo $gmw_forms[$formID][$section]['excerpt']['more']; ?>" size="15" />
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
  		
    	//page laod features
    	$newValues = array(
    			 
    			'post_types'     => array(
    					'name'    => 'post_types',
    					'std'     => '',
    					'label'   => __( 'Post Types', 'GMW' ),
    					'desc'    => __( 'Choose the post types you would like to display.', 'GMW' ),
    					'type'    => 'multicheckboxvalues',
    					'options' => get_post_types()
    			),			 
    	);
    	 
    	$afterIndex = 0;
    	$settings['page_load_results'][1] = array_merge( array_slice( $settings['page_load_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['page_load_results'][1], $afterIndex + 1 ) );
    	 
    	//search form features
    	$newValues = array(
    			'post_types' => array(
    					'name'     		=> 'post_types',
    					'std'      		=> '',
    					'label'    		=> __( 'Post Types', 'GMW' ),
    					'cb_label' 		=> '',
    					'desc'     		=> __( "Check the checkboxes of the post types you'd like to display in the search form. When selecting multiple post types they will be displayed as a dropdown menu.", 'GMW' ),
    					'type'     		=> 'function',
    			),
    			 
    			'form_taxonomies' => array(
    					'name'  => 'form_taxonomies',
    					'std'   => '',
    					'label' => __( 'Taxonomies', 'GMW' ),
    					'desc'  => __( "Choose the taxonomies that you'd like to display in the search form. The taxonomies will be displayed as a dropdown menues.", 'GMW' ),
    					'type'  => 'function'
    			)   			 
    	);

    	$afterIndex = 0;
    	$settings['search_form'][1] = array_merge( array_slice( $settings['search_form'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_form'][1], $afterIndex + 1 ) );
    	   	
    	//search results features
    	unset( $settings['search_results'][1]['auto_results'], $settings['search_results'][1]['auto_all_results'] );
    	$newValues = array(
    			
    			'display_posts'    => array(
    					'name'     => 'display_posts',
    					'std'      => '',
    					'label'    => __( 'Display Posts?', 'GMW' ),
    					'desc'     => __( 'Display results as list of posts', 'GMW' ),
    					'type'     => 'checkbox',
    					'cb_label' => __( 'Yes', 'GMW' ),
    			),
    			'featured_image'   => array(
    					'name'     => 'featured_image',
    					'std'      => '',
    					'label'    => __( 'Featured Image', 'GMW' ),
    					'cb_label' => '',
    					'desc'     => __( 'Display featured image and define its width and height in pixels.', 'GMW' ),
    					'type'     => 'function',
    			),
    			'additional_info'  => array(
    					'name'    => 'additional_info',
    					'std'     => '',
    					'label'   => __( 'Contact Information', 'GMW' ),
    					'desc'    => __( "Check the checkboxes of the contact information which you'd like to display per location in the search results.", 'GMW' ),
    					'type'    => 'multicheckbox',
    					'options' => array(
    							'phone'   => __( 'Phone', 'GMW' ),
    							'fax'     => __( 'Fax', 'GMW' ),
    							'email'   => __( 'Email', 'GMW' ),
    							'website' => __( 'Website', 'GMW' ),
    					),
    			),
                'opening_hours'  => array(
                        'name'     => 'opening_hours',
                        'std'      => '',
                        'label'    => __( 'Show opening hours', 'GMW' ),
                        'cb_label' => __( 'Yes', 'GMW' ),
                        'desc'     => __( 'Display opening days & hours.', 'GMW' ),
                        'type'     => 'checkbox'
                ),
    			'show_excerpt'     => array(
    					'name'     => 'show_excerpt',
    					'std'      => '',
    					'label'    => __( 'Excerpt', 'GMW' ),
    					'cb_label' => '',
    					'desc'     => __( 'Display the number of words that you choose from the post content and display it per location in the list of results.', 'GMW' ),
    					'type'     => 'function'
    			),
    			'custom_taxes'     => array(
    					'name'     => 'custom_taxes',
    					'std'      => '',
    					'label'    => __( 'Taxonomies', 'GMW' ),
    					'cb_label' => __( 'Yes', 'GMW' ),
    					'desc'     => __( 'Display a list of taxonomies attached to each post in the list of results.', 'GMW' ),
    					'type'     => 'checkbox'
    			),
    
    	);
    	
    	$afterIndex = 3;
    	$settings['search_results'][1] = array_merge( array_slice( $settings['search_results'][1], 0, $afterIndex + 1 ), $newValues, array_slice( $settings['search_results'][1], $afterIndex + 1 ) );
    	 
    	return $settings;

    }

    public function shortcodes_page( $shortcodes ) {

    	$shortcodes['single_post_location'] = array(
    			'name'		  	=> __( 'Single Post Location', 'GMW' ),
    			'basic_usage' 	=> '[gmw_single_location]',
    			'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_single_location]\'); &#63;&#62;',
    			'desc'        	=> __( 'Display map and/or location information of specific post.', 'GMW' ),
    			'attributes'  	=> array(
    					array(
    							'attr'	 	=> 'post_id',
    							'values' 	=> array(
    									'Post ID',
    							),
    							'default'	=> __( 'By default the shortcode displays the posts being displayed in a sinle post page or within the posts loop', 'GMW' ),
    							'desc'	 	=> __( 'Use the post ID only if you want to display information of a specific post. When using the shortcode on a single post page or within a posts loop you don\'t need to use the post_id attribute. The shortcode will use the post ID of the post being displayed or the post ID of each post within the loop. ', 'GMW')
    					),
    					array(
    							'attr'	 	=> 'post_title',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '0',
    							'desc'	 	=> __( 'Use the value 1 to display the post title above the map.', 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'distance',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '1',
    							'desc'	 	=> __( 'Use the value 1 to display distance of the post\'s location from the user\'s current location when exists.', 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'distance_units',
    							'values' 	=> array(
    									'm',
    									'k',
    							),
    							'default'	=> 'm',
    							'desc'	 	=> __( "Distance units - \"m\" for Miles \"k\" for Kilometers", 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'map',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '1',
    							'desc'	 	=> __( 'Use the value 1 if you want to display map of the locaiton.', 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'map_height',
    							'values' 	=> array(
    									__( 'Value in pixels or percentage', 'GMW' ),
    							),
								'default'	=> '250px',
    							'desc'	 	=> __( 'Map height in px or % ( ex. 250px or 100% ).', 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'map_width',
    							'values' 	=> array(
    									__( 'Value in pixels or percentage', 'GMW' ),
    							),
								'default'	=> '250px',
    							'desc'	 	=> __( 'Map width in px or % ( ex. 250px or 100% ).', 'GMW' )
    					),  					
    					array(
    							'attr'	 	=> 'map_type',
    							'values' 	=> array(
    									'ROADMAP',
    									'SATELLITE',
    									'HYBRID',
    									'TERRAIN'
    							),
    							'default'	=> 'ROADMAP', 
    							'desc'	 	=> __( 'Choose the map type.', 'GMW' )
    					),
    					array(
    							'attr'	 	=> 'zoom_level',
    							'values' 	=> array(
    									__( 'Numeric value between 1 to 18.', 'GMW' ),
    							),
    							'default'	=> '13',
    							'desc'	 	=> __( 'Choose the map zoom level.', 'GMW')
    					),
    					array(
    							'attr'	 	=> 'additional_info',
    							'values' 	=> array(
    									'address',
    									'phone',
    									'fax',
    									'email',
    									'website',
    							),
    							'default' 	=> 'address,phone,fax,email,website',
    							'desc'	 	=> __( 'Use a single or multiple values comma separated of the contact information which you would like to display. For example use additional_info="address,phone,fax" to display the full address of the location and its phone and fax numbers.', 'GMW')
    					),
    					array(
    							'attr'		=> 'directions',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '1',
    							'desc'	 	=> __( 'Use the value 1 if you want to display "Get Directions" link.', 'GMW' )
    					),
    					array(
    							'attr'		=> 'info_window',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '1',
    							'desc'	 	=> __( 'Use the 0 to disable or the value 1 to enable the info-window of the marker represents the post being dispplayed.', 'GMW' )
    					),
    					array(
    							'attr'		=> 'hide_info',
    							'values' 	=> array(
    									'1',
    									'0',
    							),
    							'default'	=> '0',
    							'desc'	 	=> __( "Use the value 1 to Hide all other information except for the map", "GMW" )
    					),
    					array(
    							'attr'		=> 'location_marker',
    							'values' 	=> array(
    									'link to an image',
    							),
    							'default'	=> 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
    							'desc'	 	=> __( "Provide a link to an image that will be used as a marker which represents the location of the post being displayed.", 'GMW' )
    					),
    					array(
    							'attr'		=> 'ul_marker',
    							'values' 	=> array(
    									'link to an image',
    									'0',
    							),
    							'default'	=> 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
    							'desc'	 	=> __( "Provide a link to an image that will be used as a marker which represents the user's location on the map. Use 0 if you do not want to show the user's location.", 'GMW' )
    					),
    					array(
    							'attr'		=> 'ul_message',
    							'values' 	=> array(
    									'Any text',
    									'0',
    							),
    							'default'	=> 'Your location',
    							'desc'	 	=> __( "Any text that will be display within the info-window of the marker represents the user's current location. Use 0 if you want the info-window to be disabled.", 'GMW' )
    					),
    						
    			),
    			'examples'  => array(
    					array(
    							'example' => "[gmw_single_location]",
    							'desc'	  => __( 'Place this shortcode in the content of a page or post to display the map, contact information and "get directions" link of the post.', 'GMW' )
    							 
    					),
    					array(
    							'example' => "[gmw_single_location map=\"1\" map_width=\"100%\" map_height=\"450px\" additional_info=\"0\" directions=\"0\"]",
    							'desc'	  => __( 'Display map of the location. Map width set to 100% and map height 450px. No additional information and no "Get directions" link will be displayed.', 'GMW' )

    					),
    			),
    			 
    	);
    	 
    	$shortcodes['post_info'] = array(
    			'name'		 	=> __( 'Post Information', 'GMW' ),
    			'basic_usage' 	=> '[gmw_post_info]',
    			'template_usage'=> '&#60;&#63;php echo do_shortcode(\'[gmw_post_info]\'); &#63;&#62;',
    			'desc'        	=> __( 'Easy way to display any of the location/contact information of a post.', 'GMW' ),
    			'attributes'  	=> array(
    					array(
    							'attr'	 => 'post_id',
    							'values' => array(
    									'Post ID',
    							),
    							'desc'	 => __( "Use the post ID only if you want to display information of a specific post. When using the shortcode on a single post page or within a posts loop you don't need to use the post_id attribute.", 'GMW' ).
    										__( " The shortcode will use the post ID of the post being displayed or the post ID of each post within the loop. ", 'GMW')
    					),
    					array(
    							'attr'	 => 'info',
    							'values' => array(
    									'street',
    									'apt',
    									'city',
    									'state -' . __( "state short name (ex FL )", 'GMW' ),
    									'state_long' . __( "state long name (ex Florida )",'GMW' ),
    									'zipcode',
    									'country - ' . __( "country short name (ex IL )",'GMW' ),
    									'country_long - ' . __( "country long name (ex Israel )",'GMW' ),
    									'address',
    									'formatted_address',
    									'lat - ' . 'Latitude',
    									'long - ' . 'Longitude',
    									'phone',
    									'fax',
    									'email',
    									'website',
    							),
    							'default'	=> 'formatted_address',
    							'desc'	 => __( 'Use a single value or multiple values comma separated of the information you would like to display. For example use info="city,state,country_long" to display "Hollywood FL United States"', 'GMW')
    					),

    					array(
    							'attr'	 	=> 'divider',
    							'values' 	=> array(
    									__( 'any character','GMW' ),
    							),
    							'default'	=> 'space',
    							'desc'	 	=> __( 'Use any character that you would like to display between the fields you choose above"', 'GMW')
    					),
    			),
    			'examples'  => array(
    					array(
    							'example' => "[gmw_post_info post_id=\"3\" info=\"city,state_long,zipcode\" divider=\",\"]",
    							'desc'	  => __( 'This shortcode will display the information of the post with ID 3 which is ( for example ) "Hollywood,Florida,33021"', 'GMW' )

    					),
    					array(
    							'example' => "[gmw_post_info info=\"city,state\" divider=\"-\"]",
    							'desc'	  => __( 'Use the shortcode without post_id when within a posts loop to display "Hollywood-FL"', 'GMW' )
    								
    					),
    					array(
    							'example' => "Address [gmw_post_info info=\"formatted_address\"]<br />
    								 Phone: [gmw_post_info info=\"phone\"]<br />
    								 Email: [gmw_post_info info=\"email\"]<br />
    								 Website: [gmw_post_info info=\"website\"]",
    							'desc'	  => __( 'Use this example in the content of a post to display:', 'GMW' ) . "<br />
    								 Address: blah street, Hollywodo Fl 33021, USA <br />
    								 Phone: 123-456-7890 <br />
    								 Email: blah@geomywp.com <br />
    								 Website: www.geomywp.com <br />"								
    					),
    			),
    	);

    	return $shortcodes;
    }
}
new GMW_PT_Admin();
?>