<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

/**
 * GMW_Admin class.
 */
class GMW_Admin {

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
		add_action( 'admin_menu', 			 array( $this, 'admin_menu' ), 12);
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'media_buttons',  		 array( $this, 'add_form_button'), 25 );
		add_action( 'admin_footer',  		 array( $this, 'form_insert_popup') );
		
		//include admin pages
		include_once( 'geo-my-wp-admin-functions.php' );
		include_once( 'tools/geo-my-wp-tools.php' );
		include_once( 'geo-my-wp-addons.php' );
		include_once( 'geo-my-wp-settings.php' );
		include_once( 'geo-my-wp-forms.php' );
		include_once( 'geo-my-wp-edit-form.php' );
		include_once( 'geo-my-wp-shortcodes.php' );
		
		//set pages
		$this->addons_page     	= new GMW_Addons();
		$this->settings_page   	= new GMW_Settings();
		$this->forms_page      	= new GMW_Forms();
		$this->edit_form_page 	= new GMW_Edit_Form;
		$this->shortcodes_page 	= new GMW_Shortcodes_page();
	
		add_filter( 'plugin_action_links_geo-my-wp/geo-my-wp.php', array( $this, 'gmw_action_links' ), 10, 2 );
		
		//for loer version of plugin
		add_filter( 'plugin_action_links', array( $this, 'addons_action_links' ), 10, 2 );
		
		//display footer credits only on GEO my WP pages
		$gmw_pages = array( 'gmw-add-ons', 'gmw-settings', 'gmw-forms', 'gmw-shortcodes', 'gmw-tools' );
		
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $gmw_pages ) ) {
			add_filter( 'admin_footer_text', array( $this, 'gmw_credit_footer'), 10 );
		}
		
		add_action( 'form_editor_tab_end', array( $this, 'rickey_messick_credit' ), 10, 4 );
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
		
		//register google maps api
		wp_register_script( 'google-maps', ( is_ssl() ? 'https' : 'http' ) . '://maps.googleapis.com/maps/api/js?libraries=places'.$googleApi.'&sensor=false'.$region.$language, array( 'jquery' ), false );
		
		wp_register_style( 'gmw-style-admin', GMW_URL . '/includes/admin/assets/css/style-admin.css' );
		wp_enqueue_style( 'gmw-style-admin' );
		
		wp_register_script( 'gmw-admin', GMW_URL.'/includes/admin/assets/js/gmw-admin.js', array( 'jquery' ), GMW_VERSION, true );
        wp_enqueue_script( 'gmw-admin' ); 

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
	
		add_menu_page( 'GEO my WP', 'GEO my WP', 'manage_options', 'gmw-add-ons', array($this->addons_page, 'output' ), '', 66 );
		add_submenu_page( 'gmw-add-ons', __( 'Add-ons', 'GMW' ), __( 'Add-ons', 'GMW' ), 'manage_options', 'gmw-add-ons', array( $this->addons_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'GEO my WP Settings', 'GMW' ), __( 'Settings', 'GMW'), 'manage_options', 'gmw-settings', array( $this->settings_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'Forms', 'GMW' ), __( 'Forms', 'GMW' ), 'manage_options', 'gmw-forms', ( !empty( $_GET['gmw_action']) && $_GET['gmw_action'] == 'edit_form' ) ? array( $this->edit_form_page, 'output' ) : array( $this->forms_page, 'output' ) );
		add_submenu_page( 'gmw-add-ons', __( 'Tools', 'GMW' ), __( 'Tools', 'GMW' ), 'manage_options', 'gmw-tools', 'gmw_tools_page_output' );
		add_submenu_page( 'gmw-add-ons', __( 'Shortcodes', 'GMW' ), __( 'Shortcodes', 'GMW' ), 'manage_options', 'gmw-shortcodes', array( $this->shortcodes_page, 'output' ) );
	
		$menu_items = array();
	
		//hook your add-on's menu item
		$menu_items = apply_filters('gmw_admin_menu_items', $menu_items);
	
		foreach ( $menu_items as $item ) {
			add_submenu_page('gmw-add-ons', $item['page_title'], $item['menu_title'], $item['capability'], $item['menu_slug'], $item['callback_function']);
		}	
	}
	
	/**
	 * Initiate all GMW's add-ons
	 *
	 */
	public function init_addons() {
				
		$addons_data   	   = array();
		$new_addons_status = array();
						
		//hook your add-on here
		$addons_data = apply_filters( 'gmw_admin_addons_page', $addons_data );

		foreach ( $addons_data as $addon ) {	

			if ( $addon['name'] == 'posts' && isset( $this->addons['posts'] ) && $this->addons['posts'] == 'active' ) {
				$new_addons_status['posts'] = 'active';
			} elseif ( $addon['name'] == 'friends' && isset( $this->addons['friends'] ) && $this->addons['friends'] == 'active' ) {
				$new_addons_status['friends'] = 'active';
			} elseif ( $addon['name'] != 'posts' && $addon['name'] != 'friends' ) { 
				$new_addons_status[$addon['name']] = 'active';
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

	public function addons_action_links( $links, $file ) {
		static $this_plugin;
	
		$licenses = get_option('gmw_license_keys');
		$statuses = get_option('gmw_premium_plugin_status');
	
		$deactivate_links = array();
		$deactivate_links = apply_filters( 'gmw_plugin_action_links', $deactivate_links );
	
		if ( empty( $deactivate_links ) ) 
			return $links;
		
		foreach ( $deactivate_links as $addon => $link ) {
	
			if ( $file == $link ) {
	
				if ( isset($this->addons[$addon]) && $this->addons[$addon] == 'active' && !empty( $licenses[$addon] ) && isset($statuses[$addon]) && $statuses[$addon] == 'valid' )
					$links['deactivate'] = '<a href="' . admin_url( 'admin.php?page=gmw-add-ons' ).'">'.__( 'Please deactivate the license key before deactivating the plugin', 'GMW' ).'</a>';
				else
					$links = array( 'activate' => '<a href="'.admin_url( 'admin.php?page=gmw-add-ons' ).'">' . __('Activate license key', 'GMW') . '</a>') + $links;
			}
		}
		return $links;
	}
	
	/**
	 * Action target that adds the "Insert Form" button to the post/page edit screen
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
                        <input type="radio" class="gmw_form_type" checked="checked" name="gmw_form_type" value="form" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':hidden') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> <label for="gmw-form"><?php _e("Form shortcode", "GMW"); ?></label> &nbsp;&nbsp;&nbsp;
                        <input type="radio" class="gmw_form_type" name="gmw_form_type"  value="map" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':hidden') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> <label for="gmw-map"><?php _e("Map Shortcode", "GMW"); ?></label>&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="gmw_form_type" name="gmw_form_type" value="results" onclick="if ( jQuery('#gmw-forms-dropdown-wrapper').is(':visible') ) jQuery('#gmw-forms-dropdown-wrapper').slideToggle();" /> <label for="gmw-form"><?php _e("Results shortcode", "GMW"); ?></label> &nbsp;&nbsp;&nbsp;
                    </div>
                    <div id="gmw-forms-dropdown-wrapper" style="padding:15px 15px 0 15px;">
                        <select id="gmw_form_id">
                            <option value="">  <?php _e("Select a Form", "GMW"); ?>  </option>
                            <?php
                                $forms = get_option('gmw_forms');
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
     * GEO my WP credits - header
     * @return string
     */
	static public function gmw_credits() {

		$output  =	'<div class="gmw-credits">';
		$output .=	'<img src="'.GMW_URL .'/assets/images/gmw-logo.png" />';
		$output .=          '<div style="display: inline-block;"><a href="http://www.geomywp.com" target="_blank">'.__( 'Developed by Eyal Fitoussi - please take a moment to support my work' ,'GJM').'</a></div>';
		$output .=          '<div>';
		$output .=          	'<a href="http://wordpress.org/plugins/geo-my-wp/" title="Rate GEO my WP" target="_blank"><img src="'.GMW_URL .'/assets/images/star-icon.png" style="max-height:23px;" /></a>';
		$output .= 				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=WTF4HGEYNFF8W" class="gmw-credits-addons-button" target="_blank" Title="Thank you">Donate</a>';
		$output .=              '<a class="gmw-credits-facebook-button" title="GEO my WP Facebook page" href="https://www.facebook.com/geomywp" target="_blank">Facebook</a>';
		$output .=              '<a class="gmw-credits-twitter-button" title="GEO my WP Twitter page" href="https://twitter.com/GEOmyWP" target="_blank">Twitter</a>';
		$output .=              '<a class="gmw-credits-email-button" title="Contact Us" href="mailto:info@geomywp.com" title="Email" target="_blank">Email</a>';
		$output .=              '<div style="float:left;margin-top: 2px;" class="fb-like" data-href="https://www.facebook.com/geomywp" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>';
		$output .=              '<span style="margin: 2px 4px 5px 4px;float:left;"><a href="https://twitter.com/GEOmyWP" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false" style="margin-top:2px">Follow @GEOmyWP</a></span>';
		$output .=              '<a class="gmw-credits-addons-button" title="GEO my WP Add-ons" href="http://geomywp.com/add-ons" target="_blank">Add-ons</a>';
		$output .=          '</div>';
		$output .=	'</div>';
		?>
		<div id="fb-root"></div>
		<script>
            !function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                if (!d.getElementById(id)) {
                    js = d.createElement(s);
                    js.id = id;
                    js.src = p + '://platform.twitter.com/widgets.js';
                    fjs.parentNode.insertBefore(js, fjs);
                }
            }(document, 'script', 'twitter-wjs');
            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id))
                    return;
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=150962325088686";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
		<?php
		return $output;

	}
	
	/**
	 * GMW credit footer
	 * @param unknown_type $content
	 * @return string
	 */
	static public function gmw_credit_footer( $content ) {
		return preg_replace('/[.,]/', '', $content) . ' ' . __( 'and Geo-locating with', 'GMW' ). ' <a href="http://geomywp.com" target="_blank" title="GEO my WP">'.__( 'GEO my WP', 'GMW' ) . '</a>.';	
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
			<td></td>
		</tr>
		<?php 		
	}
}
new GMW_Admin();
?>
