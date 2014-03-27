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

        $this->add_ons  = get_option( 'gmw_addons' );
        $this->settings = get_option( 'gmw_options' );

        //check if we are in new/edit post page
        if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'post.php', 'page.php', 'page-new' ) ) ) {
            include_once GMW_PT_PATH . 'includes/admin/gmw-pt-metaboxes.php';
        }

        add_filter( 'gmw_admin_settings', array( $this, 'settings_init' ), 1 );
        add_filter( 'gmw_admin_new_form_button', array( $this, 'new_form_button' ), 1, 1 );
        add_filter( 'gmw_posts_form_settings', array( $this, 'form_settings_init' ), 1, 1 );
        add_filter( 'gmw_admin_shortcodes_page', array( $this, 'shortcodes_page' ),1 , 10 );
        		
        //main settings
        add_action( 'gmw_main_settings_post_types', array( $this, 'main_settings_post_types' ), 1, 4 );

        //form settings
        //posts locator form settings
        add_action( 'gmw_posts_form_settings_search_form_template', array( $this, 'form_settings_search_form_template' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_post_types', array( $this, 'form_settings_post_types' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_address_field', array( $this, 'form_settings_address_field' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_results_template', array( $this, 'form_settings_results_template' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_auto_results', array( $this, 'form_settings_auto_results' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_featured_image', array( $this, 'featured_image' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_show_excerpt', array( $this, 'show_excerpt' ), 1, 4 );
        add_action( 'gmw_posts_form_settings_form_taxonomies', array( $this, 'form_taxonomies' ), 1, 4 );

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
                array(
                    'name'  => 'post_types',
                    'std'   => '',
                    'label' => __( 'Post Types', 'GMW' ),
                    'desc'  => __( "Choose the post types that you want to add locations to. GEO my WP's location section will be displayed in the new/edit post screen of the post types you choose here. ", 'GMW' ),
                    'type'  => 'function'
                ),
                array(
                    'name'       => 'mandatory_address',
                    'std'        => '',
                    'label'      => __( 'Mandatory Address fields', 'GMW' ),
                    'cb_label'   => __( 'Yes', 'GMW' ),
                    'desc'       => __( 'Check this box if you want to make sure that users will add location to a new post. it will prevent them from saving a post that do not have a location. Otherwise, users will be able to save a post even without a location. This way the post will be published and would show up in Wordpress search results but not in the Proximity search results.', 'GMW' ),
                    'type'       => 'checkbox',
                    'attributes' => array()
                ),
            ),
        );

        $settings['features'][1][2] = array(
            'name'     => 'single_location_shortcode',
            'label'    => __( 'Single Location Shortcode', 'GMW' ),
            'std'      => '',
            'cb_label' => __( 'Yes', 'GMW' ),
            'desc'     => __( 'Display location of a single post', 'GMW' ),
            'type'     => 'checkbox'
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
     *
     */
    public function main_settings_post_types( $gmw_options, $section, $option ) {

        $saved_data = ( isset( $gmw_options[$section]['post_types'] ) ) ? $gmw_options[$section]['post_types'] : array();
        ?>	
        <div>
            <div class="posts-checkboxes-wrapper" id="<?php echo $formID; ?>">
                <?php $posts      = get_post_types(); ?>

                <?php foreach ( $posts as $post ) { ?>

                    <?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $post, $saved_data ) ) ? ' checked="checked"' : ''; ?>

                    <label><input type="checkbox" name="<?php echo 'gmw_options[' . $section . '][post_types][]'; ?>" value="<?php echo $post; ?>" id="<?php echo $post; ?>" class="post-types-tax" <?php echo $checked; ?>><?php echo get_post_type_object( $post )->labels->name; ?></label><br />

                <?php } ?>
            </div>
        </div>
        <?php

    }

    /**
     * search form template
     * @param unknown_type $gmw_forms
     * @param unknown_type $formID
     * @param unknown_type $section
     * @param unknown_type $option
     */
    public function form_settings_search_form_template( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <select name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][form_template]'; ?>">
                <?php foreach ( glob( GMW_PT_PATH . 'search-forms/*', GLOB_ONLYDIR ) as $dir ) { ?>
                    <option value="<?php echo basename( $dir ); ?>" <?php if ( isset( $gmw_forms[$formID][$section]['form_template'] ) && $gmw_forms[$formID][$section]['form_template'] == basename( $dir ) ) echo 'selected="selected"'; ?>><?php echo basename( $dir ); ?></option>
                <?php } ?>

                <?php foreach ( glob( STYLESHEETPATH . '/geo-my-wp/posts/search-forms/*', GLOB_ONLYDIR ) as $dir ) { ?>
                    <?php $cThems = 'custom_' . basename( $dir ) ?>
                    <option value="<?php echo $cThems; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['form_template'] ) && $gmw_forms[$formID][$section]['form_template'] == $cThems ) echo 'selected="selected"'; ?>><?php _e( 'Custom Form: ', 'GMW' ); ?><?php echo basename( $dir ); ?></option>
                <?php } ?>

            </select>
        </div>
        <?php

    }

    /**
     * Post types form settings
     * 
     */
    public function form_settings_post_types( $gmw_forms, $formID, $section, $option ) {

        $saved_data = ( isset( $gmw_forms[$formID][$section]['post_types'] ) ) ? $gmw_forms[$formID][$section]['post_types'] : array();
        ?>

        <div>
            <div class="posts-checkboxes-wrapper" id="<?php echo $formID; ?>">
                <?php $posts      = get_post_types(); ?>

                <?php foreach ( $posts as $post ) { ?>

                    <?php $checked = ( isset( $saved_data ) && !empty( $saved_data ) && in_array( $post, $saved_data ) ) ? ' checked="checked"' : ''; ?>

                    <p>
                    	<input type="checkbox" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][post_types][]'; ?>" value="<?php echo $post; ?>" id="<?php echo $post; ?>" class="post-types-tax" <?php echo $checked; ?> />
                    	<label><?php echo get_post_type_object( $post )->labels->name; ?></label>
                    </p>

                <?php } ?>
            </div>
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

                        //if (is_taxonomy_hierarchical($tax)) :

                        echo '<div style="border-bottom:1px solid #eee;padding-bottom: 10px;margin-bottom: 10px;" class="gmw-single-taxonomie">';
                        echo '<strong>' . get_taxonomy( $tax )->labels->singular_name . ': </strong>';
                        echo '<span id="gmw-st-wrapper">';
                        echo '<input type="radio" class="gmw-st-btns radio-na" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies][' . $tax . '][style]" value="na" checked="checked" />' . __( 'Exclude', 'GMW' );
                        echo '<input type="radio" class="gmw-st-btns" name="gmw_forms[' . $formID . '][' . $section . '][taxonomies][' . $tax . '][style]" value="drop" ';
                        if ( isset( $gmw_forms[$formID][$section]['taxonomies'][$tax]['style'] ) && $gmw_forms[$formID][$section]['taxonomies'][$tax]['style'] == 'drop' )
                            echo "checked=checked"; echo ' style="margin-left: 10px; " />' . __( 'Dropdown', 'GMW' );
                        echo '</span>';

                        echo '</div>';

                        //endif;

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
     * address field form settings
     *
     */
    public function form_settings_address_field( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <p>
                <?php _e( 'Field title', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][title]'; ?>" size="30" placeholder="<?php _e( 'enter title for the address field', 'GMW' ); ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['address_field']['title'] ) ) ? $gmw_forms[$formID][$section]['address_field']['title'] : ''; ?>" />
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][within]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['within'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Within the input field', 'GMW' ); ?>
            </p>
            <p>
                <input type="checkbox" value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][address_field][mandatory]'; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['address_field']['mandatory'] ) ) echo 'checked="checked"'; ?>>	
                <?php _e( 'Mandatory Field', 'GMW' ); ?>
            </p>
        </div>

        <?php

    }

    /**
     * results template form settings
     *
     */
    public function form_settings_results_template( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <select name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][results_template]'; ?>">

                <?php foreach ( glob( GMW_PT_PATH . 'search-results/*', GLOB_ONLYDIR ) as $dir ) { ?>

                    <option value="<?php echo basename( $dir ); ?>" <?php if ( isset( $gmw_forms[$formID][$section]['results_template'] ) && $gmw_forms[$formID][$section]['results_template'] == basename( $dir ) ) echo 'selected="selected"'; ?>><?php echo basename( $dir ); ?></option>

                <?php } ?>

                <?php foreach ( glob( STYLESHEETPATH . '/geo-my-wp/posts/search-results/*', GLOB_ONLYDIR ) as $dir ) { ?>

                    <?php $cThems = 'custom_' . basename( $dir ) ?>
                    <option value="<?php echo $cThems; ?>" <?php if ( isset( $gmw_forms[$formID][$section]['results_template'] ) && $gmw_forms[$formID][$section]['results_template'] == $cThems ) echo 'selected="selected"'; ?>><?php _e( 'Custom Template:' ); ?> <?php echo basename( $dir ); ?></option>

                <?php } ?>

            </select>
        </div>
        <?php

    }

    /**
     * auto results settings
     *
     */
    public function form_settings_auto_results( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div>
            <p>
                <input name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][on]'; ?>" type="checkbox" value="1" <?php if ( isset( $gmw_forms[$formID][$section]['auto_search']['on'] ) ) echo "checked='checked'"; ?>/>
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>	
            <p>

                <?php _e( 'Radius', 'GMW' ); ?>		
                <input type="text" id="wppl-auto-radius" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][radius]'; ?>" size="5" value="<?php echo ( isset( $gmw_forms[$formID][$section]['auto_search']['radius'] ) ) ? $gmw_forms[$formID][$section]['auto_search']['radius'] : "50"; ?>" />	
            </p>
            <p>
                <select id="wppl-auto-units" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][auto_search][units]'; ?>">
                    <option value="imperial" <?php echo 'selected="selected"'; ?>><?php _e( 'Miles', 'GMW' ); ?></option>
                    <option value="metric"   <?php if ( isset( $gmw_forms[$formID][$section]['auto_search']['units'] ) && $gmw_forms[$formID][$section]['auto_search']['units'] == "metric" ) echo 'selected="selected"'; ?>><?php _e( 'Kilometers', 'GMW' ); ?></option>
                </select>
            </p>
        </div>
        <?php

    }

    /**
     * Get pages
     */
    public function get_pages() {
        $pages = array();

        $pages[''] = __( ' -- Same Page -- ', 'GMW' );
        foreach ( get_pages() as $page ) {
            $pages[$page->ID] = $page->post_title;
        }

        return $pages;

    }

    /**
     * locator icons
     */
    public function get_locator_icons() {
        $icons         = array();
        $locator_icons = glob( GMW_PATH . '/assets/images/locator-images/*.png' );
        $display_icon  = GMW_IMAGES . '/locator-images/';

        $icons['gmw_na'] = __( 'Do not use', 'GMW' );
        foreach ( $locator_icons as $locator_icon ) {
            $icons[basename( $locator_icon )] = '<img src="' . $display_icon . basename( $locator_icon ) . '" height="30px" width="30px"/>';
        }
        return $icons;

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
                <?php _e( 'Height', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][featured_image][width]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['width'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['width'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['width'] : '200px'; ?>" />
            </p>
            <p>
                <?php _e( 'Width', 'GMW' ); ?>:
                &nbsp;<input type="text" size="5" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][featured_image][height]'; ?>" value="<?php echo ( isset( $gmw_forms[$formID][$section]['featured_image']['height'] ) && !empty( $gmw_forms[$formID][$section]['featured_image']['height'] ) ) ? $gmw_forms[$formID][$section]['featured_image']['height'] : '200px'; ?>" />
            </p>
        </div>
        <?php

    }

    /**
     * excerpt 
     */
    public function show_excerpt( $gmw_forms, $formID, $section, $option ) {
        ?>
        <div class="gmw-ssb">
            <p>
                <input type="checkbox"  value="1" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][use]'; ?>" <?php echo ( isset( $gmw_forms[$formID][$section]['excerpt']['use'] ) ) ? "checked=checked" : ""; ?> />
                <label><?php _e( 'Yes', 'GMW' ); ?></label>
            </p>
            <p>
                <?php _e( 'Words count', 'GMW' ); ?>:
                <input type="text" name="<?php echo 'gmw_forms[' . $_GET['formID'] . '][' . $section . '][excerpt][count]'; ?>" value="<?php if ( isset( $gmw_forms[$formID][$section]['excerpt']['count'] ) ) echo $gmw_forms[$formID][$section]['excerpt']['count']; ?>" size="5" />
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

        $settings['search_form'][1] = array(
            'form_template'   => array(
                'name'     => 'form_template',
                'std'      => '',
                'label'    => __( 'Search Form Template', 'GMW' ),
                'desc'     => __( 'Choose the search form template that you want to use.', 'GMW' ),
                'type'     => 'function',
                'function' => 'search_form_template'
            ),
            'post_types'      => array(
                'name'     => 'post_types',
                'std'      => '',
                'label'    => __( 'Post Types', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'Choose the post types to use in the search form.', 'GMW' ),
                'type'     => 'function',
            ),
            'form_taxonomies' => array(
                'name'  => 'form_taxonomies',
                'std'   => '',
                'label' => __( 'Taxonomies / Categories', 'GMW' ),
                'desc'  => __( 'Choose the taxonomies/categories that you want to display as select box in the search form.', 'GMW' ),
                'type'  => 'function'
            ),
            'address_field'   => array(
                'name'     => 'address_field',
                'std'      => '',
                'label'    => __( 'Address Field', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'Type the title for the address field of the search form. for example "Enter your address". this title wll be displayed either next to the address input field or within if you check the checkbox for it. You can also choose to have the address field mandatory which will prevent users from submitting the form if no address entered. Otherwise if you allow the field to be empty and user submit a form with no address the plugin will display all results.', 'GMW' ),
                'type'     => 'function',
            ),
            'radius'          => array(
                'name'        => 'radius',
                'std'         => '5,10,15,25,50,100',
                'placeholder' => __( 'Radius values comma separated', 'GMW' ),
                'label'       => __( 'Radius / Distance', 'GMW' ),
                'desc'        => __( 'Enter distance values in the input box comma separated if you want to have a select dropdown menu of multiple radius values in the search form. If only one value entered it will be the default value of the search form which will be hidden.', 'GMW' ),
                'attributes'  => array( 'size' => '30' )
            ),
            'units'           => array(
                'name'    => 'units',
                'std'     => 'both',
                'label'   => __( 'Units', 'GMW' ),
                'desc'    => __( 'Choose if to show both type of units as a dropdown or a single default type.', 'GMW' ),
                'type'    => 'select',
                'options' => array(
                    'both'     => __( 'Both', 'GMW' ),
                    'imperial' => __( 'Miles', 'GMW' ),
                    'metric'   => __( 'Kilometers', 'GMW' )
                ),
            ),
            'locator_icon'    => array(
                'name'    => 'locator_icon',
                'std'     => 'gmw_na',
                'label'   => __( 'Locator Icon', 'GMW' ),
                'desc'    => __( 'Choose if to display the locator button in the search form. The locator will get the user&#39;s current location and submit the search form based of the location found. you can choose one of the default icons or you can add icon of your own. ', 'GMW' ),
                'type'    => 'radio',
                'options' => self::get_locator_icons()
            ),
            'locator_submit'  => array(
                'name'     => 'locator_submit',
                'std'      => '',
                'label'    => __( 'Locator auto-submit', 'GMW' ),
                'desc'     => __( 'When checked, automatically submit the form when location found using the Locator button.', 'GMW' ),
                'type'     => 'checkbox',
                'cb_label' => __( 'Yes', 'GMW' ),
            ),
        );

        $settings['search_results'][1] = array(
            'results_page'     => array(
                'name'    => 'results_page',
                'std'     => '',
                'label'   => __( 'Results Page', 'GMW' ),
                'desc'    => __( 'The results page will display the search results in the selected page when using the "GMW Search Form" widget or when you want to have the search form in one page and the results showing in a different page. 
											Choose the results page from the dropdown menu and paste the shortcode [gmw form="results"] into that page. To display the search result in the same page as the search form choose "Same Page" from the select box.', 'GMW' ),
                'type'    => 'select',
                'options' => self::get_pages()
            ),
            'results_template' => array(
                'name'  => 'results_template',
                'std'   => '',
                'label' => __( 'Results Template', 'GMW' ),
                'desc'  => __( 'Choose The resuls template file (results.php). You can find the search results template files in the <code>plugins folder/geo-my-wp/plugin/posts/search-results</code>. You can modify any of the templates or create your own.
											If you do modify or create you own template files you should create/save them in your theme or child theme folder and the plugin will read them from there. This way your changes will not be removed once the plugin is updated.
											You will need to create the folders and save your results template there <code><strong>themes/your-theme-or-child-theme-folder/geo-my-wp/posts/search-results/your-results-theme-folder</strong></code>.
											Your theme folder will contain the results.php file and another folder named "css" and the style.css within it.', 'GMW' ),
                'type'  => 'function',
            ),
            'auto_results'     => array(
                'name'  => 'auto_results',
                'std'   => '',
                'label' => __( 'Auto Results', 'GMW' ),
                'desc'  => __( 'Will automatically run initial search and display results based on the user\'s current location (if exists via cookies) when he/she first goes to a search page. You need to define the radius and the units for this initial search .', 'GMW' ),
                'type'  => 'function'
            ),
            'display_posts'    => array(
                'name'     => 'display_posts',
                'std'      => '',
                'label'    => __( 'Display Posts?', 'GMW' ),
                'desc'     => __( 'Display results as list of posts', 'GMW' ),
                'type'     => 'checkbox',
                'cb_label' => __( 'Yes', 'GMW' ),
            ),
            'display_map'      => array(
                'name'    => 'display_map',
                'std'     => 'na',
                'label'   => __( 'Display Map?', 'GMW' ),
                'desc'    => __( 'Display results on map. You can do so automatically above the list of results or manually using the shortcode [gmw map="form ID"].', 'GMW' ),
                'type'    => 'radio',
                'options' => array(
                    'na'        => __( 'No map', 'GMW' ),
                    'results'   => __( 'In results', 'GMW' ),
                    'shortcode' => __( 'Using shortcode', 'GMW' ),
                ),
            ),
            'featured_image'   => array(
                'name'     => 'featured_image',
                'std'      => '',
                'label'    => __( 'Featured Image', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'Display featured image and define its width and height in pixels or percentage.', 'GMW' ),
                'type'     => 'function',
            ),
            'additional_info'  => array(
                'name'    => 'additional_info',
                'std'     => '',
                'label'   => __( 'Additional Information', 'GMW' ),
                'desc'    => __( 'Which fields of the additional information do you want to display for each of the results.', 'GMW' ),
                'type'    => 'multicheckbox',
                'options' => array(
                    'phone'   => __( 'Phone', 'GMW' ),
                    'fax'     => __( 'Fax', 'GMW' ),
                    'email'   => __( 'Email', 'GMW' ),
                    'website' => __( 'Website', 'GMW' ),
                ),
            ),
            'show_excerpt'     => array(
                'name'     => 'show_excerpt',
                'std'      => '',
                'label'    => __( 'Excerpt', 'GMW' ),
                'cb_label' => '',
                'desc'     => __( 'This featured will grab the number of words that you choose from the post content and display it in each of the resuts. Set a high number (ex. 99999) if you wish to display the entire content.', 'GMW' ),
                'type'     => 'function'
            ),
            'custom_taxes'     => array(
                'name'     => 'custom_taxes',
                'std'      => '',
                'label'    => __( 'Taxonomies / Categories', 'GMW' ),
                'cb_label' => __( 'Yes', 'GMW' ),
                'desc'     => __( 'Display the taxonomies/categories for each of the results.', 'GMW' ),
                'type'     => 'checkbox'
            ),
            'per_page'         => array(
                'name'        => 'per_page',
                'std'         => '5,10,15,25',
                'placeholder' => __( 'Enter values', 'GMW' ),
                'label'       => __( 'Results Per Page', 'GMW' ),
                'desc'        => __( 'Choose the number of results per page. By setting a single value you set the default number of results per page. By giving multiple values, comma separated, a select box will be created and the users will be able to set the number of results per page.', 'GMW' ),
                'attributes'  => array( 'style' => 'width:170px' )
            ),
            'by_driving'       => array(
                'name'       => 'by_driving',
                'std'        => '',
                'label'      => __( 'Driving Distance', 'GMW' ),
                'cb_label'   => __( 'Yes', 'GMW' ),
                'desc'       => __( 'While the results showing the radius distance from the user to each of the locations, this feature let you display the exact driving distance. Please note that each driving distance request counts with google API when you can have 2500 requests per day.', 'GMW' ),
                'type'       => 'checkbox',
                'attributes' => array()
            ),
            'get_directions'   => array(
                'name'       => 'get_directions',
                'std'        => '',
                'label'      => __( 'Get Directions Link', 'GMW' ),
                'cb_label'   => __( 'Yes', 'GMW' ),
                'desc'       => __( 'Display "get directions" link that will open a new window with google map that shows the exact driving direction from the user to the location.', 'GMW' ),
                'type'       => 'checkbox',
                'attributes' => array()
            ),
        );
        $settings['results_map'][1]    = array(
            'map_width'  => array(
                'name'        => 'map_width',
                'std'         => '100%',
                'placeholder' => __( 'Map width in px or %', 'GMW' ),
                'label'       => __( 'Map Width', 'GMW' ),
                'desc'        => __( 'Enter the map\'s width in pixels or percentage. ex. 100% or 200px', 'GMW' ),
                'attributes'  => array( 'size' => '7' )
            ),
            'map_height' => array(
                'name'        => 'map_height',
                'std'         => '300px',
                'placeholder' => __( 'Map height in px or %', 'GMW' ),
                'label'       => __( 'Map Height', 'GMW' ),
                'desc'        => __( 'Enter the map\'s height in pixels or percentage. ex. 100% or 200px', 'GMW' ),
                'attributes'  => array( 'size' => '7' )
            ),
            'map_type'   => array(
                'name'    => 'map_type',
                'std'     => 'ROADMAP',
                'label'   => __( 'Map Type', 'GMW' ),
                'desc'    => __( 'Choose the map type', 'GMW' ),
                'type'    => 'select',
                'options' => array(
                    'ROADMAP'   => __( 'ROADMAP', 'GMW' ),
                    'SATELLITE' => __( 'SATELLITE', 'GMW' ),
                    'HYBRID'    => __( 'HYBRID', 'GMW' ),
                    'TERRAIN'   => __( 'TERRAIN', 'GMW' )
                ),
            ),
            'zoom_level' => array(
                'name'    => 'zoom_level',
                'std'     => 'auto',
                'label'   => __( 'Zoom Level', 'GMW' ),
                'desc'    => __( 'Map zoom level', 'GMW' ),
                'type'    => 'select',
                'options' => array(
                    'auto' => 'Auto Zoom',
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
            'map_frame'  => array(
                'name'       => 'map_frame',
                'std'        => '',
                'label'      => __( 'Map Frame', 'GMW' ),
                'cb_label'   => __( 'Yes', 'GMW' ),
                'desc'       => __( 'show frame around the map?', 'GMW' ),
                'type'       => 'checkbox',
                'attributes' => array()
            ),
        );
        return $settings;

    }

    public function shortcodes_page( $shortcodes ) {

    	$shortcodes['post_info'] = array(
    			'name'		  => __( 'Post Information', 'GMW' ),
    			'basic_usage' => '[gmw_post_info]',
    			'desc'        => __( 'Easy way to display any of the location/contact information of a post.', 'GMW' ),
    			'attributes'  => array(
    					array(
    							'attr'	 => __( 'post_id', 'GMW' ),
    							'values' => array(
    									__( 'Post ID','GMW' ),
    							),
    							'desc'	 => __( 'Use the post ID only if you want to display information of a specific post. When using the shortcode on a single post page or within
    									a posts loop you don\'t need to use the post_id attribute. The shortcode will use the post ID of the post being displayed or the post ID of
    									each post within the loop. ', 'GMW')
    					),
    					array(
    							'attr'	 => __( 'info', 'GMW' ),
    							'values' => array(
    									__( 'street','GMW' ),
    									__( 'apt','GMW' ),
    									__( 'city','GMW' ),
    									__( 'state - state\'s short name (ex FL )','GMW' ),
    									__( 'state_long - state\'s long name (ex Florida )','GMW' ),
    									__( 'zipcode','GMW' ),
    									__( 'country - country short name (ex IL )','GMW' ),
    									__( 'country_long - country long name (ex Israel )','GMW' ),
    									__( 'address','GMW' ),
    									__( 'formatted_address','GMW' ),
    									__( 'phone','GMW' ),
    									__( 'fax','GMW' ),
    									__( 'email','GMW' ),
    									__( 'website','GMW' ),
    							),
    							'desc'	 => __( 'Use a single value or multiple values comma separated of the information you would like to display. For example use
    									info="city,state,country_long" to display "Hollywood FL United States"', 'GMW')
    					),
    						
    					array(
    							'attr'	 => __( 'divider', 'GMW' ),
    							'values' => array(
    									__( 'any character','GMW' ),
    							),
    							'desc'	 => __( 'Use any character that you would like to display between the fields you choose above"', 'GMW')
    					),
    			),
    			'examples'  => array(
    					array(
    							'example' => __( '[gmw_post_info post_id="3" info="city,state_long,zipcode" divider=","]', 'GMW' ),
    							'desc'	  => __( 'This shortcode will display the information of the post with ID 3 which is ( for example ) "Hollywood,Florida,33021"', 'GMW' )

    					),
    					array(
    							'example' => __( '[gmw_post_info info="city,state" divider="-"]', 'GMW' ),
    							'desc'	  => __( 'Use the shortcode without post_id when within a posts loop to display "Hollywood-FL"', 'GMW' )
    					
    					),
    					array(
    							'example' => __( 'Address:', 'GMW' ) . ' [gmw_post_info info="formatted_address"] <br />'
    									. __( 'Phone:' , 'GMW' ) . '[gmw_post_info info="phone"]<br />'
    									. __( 'Email:' , 'GMW' ) . '[gmw_post_info info="email"]<br />'
    									. __( 'Website:' , 'GMW' ) . 'Website: [gmw_post_info info="website"]',
    							'desc'	  => __( 'Use this example in the content of a post to display:', 'GMW' ) . '<br />'
    									.__ ( 'Address: blah street, Hollywodo Fl 33021, USA', 'GMW' ) . '<br />'
    									. __( 'Phone: 123-456-7890', 'GMW' ) . '<br />'
    									. __( 'Email: blah@geomywp.com', 'GMW' ) . '<br />'
    									. __( 'Website: www.geomywp.com', 'GMW' ) .  '<br />'
    					
    					),
    			),

    	);

    	return $shortcodes;
    	 
    }

}
new GMW_PT_Admin();
?>