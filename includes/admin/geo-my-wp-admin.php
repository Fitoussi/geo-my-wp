<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

/**
 * GMW_Admin class.
 */
class GMW_Admin {

	/**
	 * Add-ons required versions for this version of GEO my WP.
	 * @var array
	 */
	public $required_versions = array(
		'premium_settings' => '1.6'
	);

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
			
		//get options
		$this->addons   = get_option('gmw_addons');
		$this->settings = get_option('gmw_options');
			
		//do some actions	
		add_action( 'admin_init', 			 array( $this, 'init_addons' ) );
		add_action( 'admin_menu', 			 array( $this, 'admin_menu' ), 12 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		//"GMW Form" button
		if ( self::add_form_button_pages() ) {
			add_action( 'media_buttons', array( $this, 'add_form_button' ), 25 );
			add_action( 'admin_footer',  array( $this, 'form_insert_popup' ) );
		}

		//include admin pages
		include_once( 'geo-my-wp-admin-functions.php' );
		include_once( 'tools/geo-my-wp-tools.php' );
		include_once( 'geo-my-wp-addons.php' );
		include_once( 'geo-my-wp-settings.php' );
		include_once( 'geo-my-wp-forms.php' );
		include_once( 'geo-my-wp-edit-form.php' );
		include_once( 'geo-my-wp-shortcodes.php' );
		include_once( 'geo-my-wp-updater.php' );
		include_once( 'geo-my-wp-license-handler.php' );  
		
		//set pages
		$this->addons_page     	= new GMW_Addons();
		$this->settings_page   	= new GMW_Settings();
		$this->forms_page      	= new GMW_Forms();
		$this->edit_form_page 	= new GMW_Edit_Form;
		$this->shortcodes_page 	= new GMW_Shortcodes_page();
	
		add_filter( 'plugin_action_links_geo-my-wp/geo-my-wp.php', array( $this, 'gmw_action_links' ), 10, 2 );
				
		//display footer credits only on GEO my WP pages
		$gmw_pages = array( 'gmw-add-ons', 'gmw-settings', 'gmw-forms', 'gmw-shortcodes', 'gmw-tools' );
		
		//credit in footer
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $gmw_pages ) ) {
			add_filter( 'admin_footer_text', array( $this, 'gmw_credit_footer'), 10 );
		}
		
		//Rickey's credit
		add_action( 'form_editor_tab_start', array( $this, 'rickey_messick_credit' ), 10, 4 );
		add_action( 'form_editor_tab_end',   array( $this, 'rickey_messick_credit' ), 10, 4 );
	}

    /**
     * deactivate add-on.
     *
     * @access public
     * @return void
     */
    public function verify_addons_version() {
    	
    	foreach ( self::all_gmw_addons() as $addon => $values ) {
	    	
	    	if ( is_plugin_active( $values['basename'] ) ) {

	    		$plugin_data = get_plugin_data( ABSPATH . 'wp-content/plugins/'.$values['basename'], false, false );

	    		if ( version_compare( $plugin_data['Version'], $values['min_version'], '<' ) ) {


		        }
		    }
       }
    }

	/**
	 * admin_enqueue_scripts function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		
		$googleApi = ( !empty( $this->settings['general_settings']['google_api'] ) ) 	? '&key=' . $this->settings['general_settings']['google_api'] : '';
		$region	   = ( !empty( $this->settings['general_settings']['country_code'] ) )  ? '&region=' .$this->settings['general_settings']['country_code'] : '';
		$language  = ( !empty( $this->settings['general_settings']['language_code'] ) ) ? '&language=' .$this->settings['general_settings']['language_code'] : '';
	
		//wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_register_style( 'gmw-style-admin', GMW_URL . '/includes/admin/assets/css/style-admin.css' );
		wp_enqueue_style( 'gmw-style-admin' );
		
		wp_register_script( 'gmw-admin', GMW_URL.'/includes/admin/assets/js/gmw-admin.js', array( 'jquery' ), GMW_VERSION, true );
        wp_enqueue_script( 'gmw-admin' ); 

        //include font-awesome
		wp_enqueue_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );

        //wp_register_script( 'chosen', GMW_URL . '/assets/js/chosen.jquery.min.js', array( 'jquery' ), GMW_VERSION, true );
        //wp_register_style( 'chosen',  GMW_URL . '/assets/css/chosen.min.css' );
	}
	
	/**
	 * admin_menu function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menu() {
	
		add_menu_page( 'GEO my WP', 'GEO my WP', 'manage_options', 'gmw-add-ons', array( $this->addons_page, 'output' ), '', 66 );
		add_submenu_page( 'gmw-add-ons', __( 'Add-ons', 'GMW' ), __( 'Add-ons', 'GMW' ), 'manage_options', 'gmw-add-ons', array( $this->addons_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'GEO my WP Settings', 'GMW' ), __( 'Settings', 'GMW'), 'manage_options', 'gmw-settings', array( $this->settings_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'Forms', 'GMW' ), __( 'Forms', 'GMW' ), 'manage_options', 'gmw-forms', ( !empty( $_GET['gmw_action']) && $_GET['gmw_action'] == 'edit_form' ) ? array( $this->edit_form_page, 'output' ) : array( $this->forms_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'Tools', 'GMW' ), __( 'Tools', 'GMW' ), 'manage_options', 'gmw-tools', 'gmw_tools_page_output' );
		add_submenu_page( 'gmw-add-ons', __( 'Shortcodes', 'GMW' ), __( 'Shortcodes', 'GMW' ), 'manage_options', 'gmw-shortcodes', array( $this->shortcodes_page, 'output' ) );
	
		$menu_items = array();
	
		//hook your add-on's menu item
		$menu_items = apply_filters('gmw_admin_menu_items', $menu_items );

		foreach ( $menu_items as $item ) {
		
			add_submenu_page('gmw-add-ons', $item['page_title'], $item['menu_title'], $item['capability'], $item['menu_slug'], $item['callback_function']);
		}	
	}
	
	/**
	 * GEO my WP core add-ons
	 *
	 * @access public
	 * @return $addons
	 */
	private function core_addons() {
	
		$core_addons = array(
				'posts' => array(
						'name'    	=> 'posts',
						'title'   	=> __( 'Post Types Locator', 'GMW' ),
						'version' 	=> GMW_VERSION,
						'item'	  	=> 'Post Types Locator',
						'file' 	  	=> GMW_PATH . '/plugins/posts/loader.php',
						'folder'	=> 'posts',
						'author'  	=> 'Eyal Fitoussi',
						'desc'    	=> __( 'Add geo-location to Posts and pages. Create an advance proximity search forms to search for locations based on post types, categories, distance and more.', 'GMW' ),
						'license' 	=> false,
						'image'   	=> false,
						'require' 	=> array(),
						'min_version'=> false,
						'stand_alone'=> false,
						'core'		=> true,
						'installed' => true,
						'basename'	=> false
				),
				'friends' => array(
						'name'    	=> 'friends',
						'title'   	=> __( 'Members Locator', 'GMW' ),
						'version' 	=> GMW_VERSION,
						'item'	  	=> 'Members Locator',
						'file' 	  	=> GMW_PATH . '/plugins/friends/loader.php',
						'folder'	=> 'friends',
						'author'  	=> 'Eyal Fitoussi',
						'desc'    	=> __( 'Let the BuddyPress members of your site to add location to thier profile. Create an advance proximity search forms to search for members based on location, Xprofile Fields and more.', 'GMW' ),
						'image'   	=> false,
						'license' 	=> false,
						'require' 	=> array(
								'Buddypress Plugin' => array( 'plugin_file' => 'buddypress/bp-loader.php', 'link' => 'http://buddypress.org' )
						),
						'min_version'=> false,
						'stand_alone'=> false,
						'core'		=> true,
						'installed' => true,
						'basename'	=> false
				),
				'current_location' => array(
						'name'    	=> 'current_location',
						'title'   	=> __( 'Current Location', 'GMW' ),
						'version' 	=> '1.0',
						'item'	  	=> 'Single Location',
						'file' 	  	=> GMW_PATH.'/plugins/current-location/loader.php',
						'folder'	=> 'current-location',
						'author'  	=> 'Eyal Fitoussi',
						'desc'    	=> __( "Get and Display the visitor's current position." , 'GMW' ),
						'image'   	=> false,
						'license' 	=> false,
						'require' 	=> array(),
						'min_version'=> false,
						'stand_alone'=> false,
						'core'		=> true,
						'installed' => true,
						'basename'	=> false
				),
				'single_location' => array(
						'name'    	=> 'single_location',
						'title'   	=> __( 'Single Location', 'GMW' ),
						'version' 	=> '1.0',
						'item'	  	=> 'Single Location',
						'file' 	  	=> GMW_PATH . '/plugins/single-location/loader.php',
						'folder'	=> 'single-location',
						'author'  	=> 'Eyal Fitoussi',
						'desc'    	=> __( 'Display location of certain component ( post, member... ) via shortcode and widget.', 'GMW' ),
						'image'   	=> false,
						'license' 	=> false,
						'require' 	=> array(), 'min_version'=> false,
						'stand_alone'=> false,
						'core'		=> true,
						'installed' => true,
						'basename'	=> false
				),
				'sweetdate_geolocation' => array(
						'name'    	=> 'sweetdate_geolocation',
						'title'   	=> __( 'Sweet Date Geolocation', 'GMW' ),
						'version' 	=> '1.0',
						'item'	  	=> 'Sweet Date Geolocation',
						'file' 	  	=> GMW_PATH . '/plugins/sweetdate-geolocation/loader.php',
						'folder'	=> 'sweetdate-geolocation',
						'author'  	=> 'Eyal Fitoussi',
						'desc'    	=> __( 'Enhance Sweet-date theme with geolocation features. ', 'GMW' ),
						'image'   	=> false,
						'license' 	=> false,
						'require' 	=> array(), 'min_version'=> false,
						'stand_alone'=> false,
						'core'		=> true,
						'installed' => true,
						'basename'	=> false
				)
		);
		return $core_addons;
	}
	
	public static function update_addon_link( $basename ) {
        return '<a href="'.admin_url( 'plugins.php' ).'#'.esc_attr( strtolower( preg_replace('/[-]+/i', '-', str_replace( ' ', '-', $basename ) ) ) ).'" title="Plugins Page" style="color:white;text-decoration: underline"><i class="fa fa-refresh"></i>  Update now</a>';
    }

	/**
	 * Initiate all GMW's add-ons
	 *
	 */
	public function init_addons() {

		$addons_data   	   	= self::core_addons();
		$new_addons_status 	= array();

		/*
		$clean_addon = array(
				'name'    		=> false,
				'item'	  		=> false,
				'item_id'		=> false,
				'title'   		=> false,
				'version' 		=> '1.0',
				'file' 	  		=> false,
				'folder'		=> false,
				'author'  		=> 'Eyal Fitoussi',
				'desc'    		=> false,
				'image'   		=> false,
				'license' 		=> false,
				'require' 		=> array(),
				'min_version'	=> false,
				'stand_alone'	=> false,
				'core'			=> false,
				'installed' 	=> true
			
		);
		*/
	
		//hook your add-on here
		$addons_data = apply_filters( 'gmw_admin_addons_page', $addons_data );
		
		foreach ( $addons_data as $key => $addon ) {	
			
			//update core add-on status
			if ( !empty( $addon['core'] ) && isset( $this->addons[$addon['name']] ) && $this->addons[$addon['name']] == 'active' ) {

				//new status
				$new_addons_status[$addon['name']] = 'active';

				//mark addon as activated
				$addons_data[$key]['activated']    = true;
			
			//check for premium add-ons
			} elseif ( empty( $addon['core'] )  ) { 

				//check for min requirements of add-ons with current version of GEO my WP
				if ( !empty( $addon['gmw_version'] ) && version_compare( GMW_VERSION, $addon['gmw_version'], '<' ) ) {

					$addons_data[$key]['gmw_required'] 		   = $addon['gmw_version'];
					$addons_data[$key]['gmw_required_message'] = $addon['title'].' add-on version '.$addon['version'].' requires GEO my WP plugin version '.$addon['gmw_version'].' or higher.';

					//disabled add-on if doesnt meet requiremenst and display a message to update it
					add_action(
			            'admin_notices',
			            create_function(
			                '', 
			                'echo \'<div class="error"><p>'.$addons_data[$key]['gmw_required_message'].'</p></div>\';'
			            )
			        );
					
				//otehrwise mark add-on as activated
				} elseif ( !empty( $this->required_versions[$addon['name']] ) && version_compare( $addon['version'], $this->required_versions[$addon['name']], '<' ) ) {

					$addons_data[$key]['required_version'] = $this->required_versions[$addon['name']];
					$addons_data[$key]['required_message'] = $addon['title'].' version '.$addon['version'].' is not compatible with GEO my WP '.GMW_VERSION.'. Please update your add-on to version '.$this->required_versions[$addon['name']].' or higher.';
					
					//disabled add-on if doesnt meet requiremenst and display a message to update it
					add_action(
			            'admin_notices',
			            create_function(
			                '', 
			                'echo \'<div class="error"><p>'.$addons_data[$key]['required_message'].'</p></div>\';'
			            )
			        );
					
				//otehrwise mark add-on as activated
				} else {
					$new_addons_status[$addon['name']] = 'active';
				}				

				//trigger license updater
				if ( class_exists( 'GMW_License' ) && !empty( $addon['auto_trigger'] ) && !empty( $addon['file'] ) ) {
					$author  = ( !empty( $addon['author'] ) ) ? $addon['author'] : 'Eyal Fitoussi';
					$api_url = ( !empty( $addon['api_url'] ) ) ? $addon['api_url'] : null;
					$item_id = ( !empty( $addon['item_id'] ) ) ? $addon['item_id'] : null;
					new GMW_License( $addon['file'], $addon['item'], $addon['name'], $addon['version'], $author, $api_url, $item_id );
				}
			}	

			
		}

		//update addons data into database
		update_option( 'gmw_addons', 	  $new_addons_status );
		update_option( 'gmw_addons_data', $addons_data 		 );		
		
		//pass add-ons data to add-ons page
		$this->addons_page->addons 	    = $new_addons_status;
		$this->addons_page->addons_data = $addons_data;
	}
	
	/**
	 * add gmw action links in plugins page
	 * @param $links
	 * @param $file
	 */
	public function gmw_action_links( $links, $file ) {
		
		$links[] = '<a href="' . admin_url('admin.php?page=gmw-settings').'">' . __( 'Settings' , 'GMW') . '</a>';
		
		return $links;
	}

	/**
	 * pages allow to add the "GMW Form" button
	 */
	public static function add_form_button_pages() {
		
		$page = in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ) );

		$page = apply_filters( 'gmw_add_form_button_pages', $page );

		return $page;
	}
	
	/**
	 * Action target that adds the "Insert Form" button to the post/page edit screen
	 *
	 * This script insired by the the work of the developers of Gravity Forms plugin
	 */
    public static function add_form_button(){	

    	// do a version check for the new 3.5 UI
        $version = get_bloginfo('version');

        if ( $version < 3.5 ) {
            // show button for v 3.4 and below
            $image_btn = GFCommon::get_base_url() . "/images/form-button.png";
            echo '<a href="#TB_inline?width=480&inlineId=select_gmw_form" class="thickbox" id="add_gmw_form" title="' . __("Add GEO my WP Form", 'GMW') . '"><img src="'.$image_btn.'" alt="' . __("GMW Form", 'GMW') . '" /></a>';
        } else {
            // display button matching new UI
            echo '<style>
            		.gmw_media_icon:before {
            			content: "\f230" !important;
						color: rgb(103, 199, 134) !important;
					}
            		.gmw_media_icon {
                    	vertical-align: text-top;
                    	width: 18px;
                    }
                    .wp-core-ui a.gmw_media_link{
                     	padding-left: 0.4em;
                    }
                 </style>
                 <a href="#TB_inline?width=480&inlineId=select_gmw_form" class="thickbox button gmw_media_link" id="add_gmw_form" title="' . __("Add GEO my WP Form", 'GMW') . '"><span class="gmw_media_icon dashicons"></span> ' . __("GMW Form", 'GMW') . '</a>';
        }
    }
    
    /**
    * popup to inset GEO my WP form into content area
    *
    */
    public static function form_insert_popup(){

    	?>
            <script>
                function gmwInsertForm(){
                                    	
                    if ( jQuery('.gmw_form_type:checked').val() != 'results' ) { 
                        
                    	var form_id = jQuery("#gmw_form_id").val();
                        if(form_id == ""){
                            alert("<?php _e("Please select a form", "GMW") ?>");
                            return;
                        }
                        
                    	var form_name = jQuery("#gmw_form_id option[value='" + form_id + "']").text().replace(/[\[\]]/g, '');
                    	window.send_to_editor("[gmw "+ jQuery('.gmw_form_type:checked').val() + "=\"" + form_id + "\" name=\"" + form_name + "\"]");
                    	
                    } else {
                        
                    	window.send_to_editor('[gmw form="results"]');
                    }
                }
            </script>
    
            <div id="select_gmw_form" style="display:none;">
            <div class="wrap">
                <div>
                    <div style="padding:15px 15px 0 15px;">
                        <h3 style="color:#5A5A5A!important; font-family:Georgia,Times New Roman,Times,serif!important; font-size:1.8em!important; font-weight:normal!important;"><?php _e("Insert A Form Shortcode", "GMW"); ?></h3>
                        <span>
                            <?php _e("Select the type of shortcode you wish to add", "GMW"); ?>
                        </span>
                    </div>
                    <div style="padding:15px 15px 0 15px;">
                        <input type="radio" class="gmw_form_type" checked="checked" name="gmw_form_type" value="form" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':hidden') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> 
                        <label for="gmw-form"><?php _e("Form shortcode", "GMW"); ?></label> &nbsp;&nbsp;&nbsp;
                        <input type="radio" class="gmw_form_type" name="gmw_form_type"  value="map" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':hidden') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> 
                        <label for="gmw-map"><?php _e("Map Shortcode", "GMW"); ?></label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="gmw_form_type" name="gmw_form_type" value="results" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':visible') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> 
                        <label for="gmw-form"><?php _e("Results shortcode", "GMW"); ?></label> &nbsp;&nbsp;&nbsp;
                    </div>
                    <div id="gmw-forms-dropdown-wrapper" style="padding:15px 15px 0 15px;">
                        <select id="gmw_form_id">
                            <option value="">  <?php _e("Select a Form", "GMW"); ?>  </option>
                            <?php
                                $forms = get_option('gmw_forms');

                                if ( empty( $forms ) || ! is_array( $forms ) ) {
									$forms = array();
								}
				
                                foreach( $forms as $form ) {
                                	$form['name'] = ( !empty( $form['name'] ) ) ? $form['name'] : 'form_id_'.$form['ID'];
                                    ?>
                                    <option value="<?php echo absint( $form['ID'] ); ?>"><?php echo esc_html( $form['name'] ); ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                   
                    <div style="padding:15px;">
                        <input type="button" class="button-primary" value="<?php _e("Insert Shortcode", "GMW"); ?>" onclick="gmwInsertForm();"/>&nbsp;&nbsp;&nbsp;
                    	<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;"><?php _e("Cancel", "GMW"); ?></a>
                    </div>
                </div>
            </div>
        </div>
    
    <?php
    }
		
	/**
	 * GMW credit footer
	 * @param unknown_type $content
	 * @return string
	 */
	static public function gmw_credit_footer( $content ) {
		return preg_replace('/[.,]/', '', $content) . ' ' . sprintf( __( 'and Geolocating with <a %s>GEO my WP</a>. Please take a moment to show your support. ', 'GMW' ), "href=\"http://geomywp.com\" target=\"_blank\" title=\"GEO my WP\"" ).'<a href="https://wordpress.org/support/view/plugin-reviews/geo-my-wp?filter=5" target="_blank" title="Rate GEO my WP"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></a>';	
	}
	
	public function rickey_messick_credit( $key, $section, $formID, $gmw ) {
	
		if ( $key != 'page_load_results' || $gmw['prefix'] != 'pt' ) 
			return;	
		?>
		<tr class="gmw-sponsored-credit">
			<td></td>
			<td>
				<span>This tab and features were sponsored by <a href="http://www.rickeymessick.com" target="_blank" title="Rickey Messick Credit">Rickey Messick</a>. Thank you!</span>
			</td>
		</tr>
		<?php 		
	}
}
new GMW_Admin();
?>
