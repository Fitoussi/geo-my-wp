<?php

if ( !defined( 'ABSPATH' ) )
    exit; // Exit if accessed directly

//check that not included already
if ( !class_exists( 'GMW_Addons') ) :

/**
 * GMW_Addon class
 */

class GMW_Addons {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
		
    	//if not add-ons page no need to load
    	if ( !isset( $_GET['page'] ) || $_GET['page'] != 'gmw-add-ons' )
    		return;
    		    	
    	$this->settings = get_option( 'gmw_options' );
		
       	add_action( 'gmw_activate_addon',   	  array( $this, 'activate_addon' ) );
        add_action( 'gmw_deactivate_addon', 	  array( $this, 'deactivate_addon' ) );       
        add_action( 'gmw_updater_action', 		  array( $this, 'addons_updater' ) );            
        add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
              
    }
    
    public function all_gmw_addons() {
    	
    	return array(
    			'exclude_members' 				=> 'gmw-exclude-members/exclude-members.php',
    			'global_maps' 					=> 'gmw-global-maps/gmw-global-maps.php',
    			'groups_locator' 				=> 'gmw-groups-locator/groups-locator.php' ,
    			'geo_members_directory' 		=> 'gmw-members-directory/gmw-members-directory.php',
    			'nearby_posts' 					=> 'gmw-nearby-posts/gmw-nearby-posts.php',
    			'premium_settings' 				=> 'gmw-premium-settings/premium-settings.php' ,
    			'wp_users_geo-location' 		=> 'gmw-users-geolocation/gmw-users-geolocation.php' ,
    			'xprofile_fields' 				=> 'gmw-xprofile-fields/xprofile-fields.php',
    			'gmw_ajax' 						=> 'gmw-ajax/gmw-ajax.php',
    			'gravity_forms_geo_fields' 		=> 'gravity-forms-geo-fields/geo-fields.php',
    			'resume_manager_geo-location' 	=> 'resume-manager-geolocation/resume-manager-geolocation.php' ,
    			'geo_job_manager' 				=> 'geo-job-manager/geo-job-manager.php' 	,
    			'current_location_forms' 		=> 'geo-my-wp-current-location-forms/gmw-current-location-forms.php'
    	);
    }
    
    /**
     * Activate add-on.
     *
     * @access public
     * @return void
     */
    public function activate_addon() {
    	
    	//make sure we activated an add-on
    	if ( empty( $_POST['gmw_action'] ) || $_POST['gmw_action'] != 'activate_addon' )
    		return;
    	
        $active_addon 	 = $_POST['gmw_addon_activated'];
        $plugin_basename = $_POST['gmw_addon_basename'];
        
        //look for nonce
        if ( empty( $_POST[$active_addon.'_activate_addon_nonce'] ) )
        	return;

        //varify nonce
        if ( !wp_verify_nonce( $_POST[$active_addon.'_activate_addon_nonce'], $active_addon.'_activate_addon_nonce' ) )
        	return;

        unset( $this->addons[$active_addon] );
        $this->addons[$active_addon] = 'active';

        update_option( 'gmw_addons', $this->addons );
       
        $plugins = get_plugins();

        //activate the addon
        if (  array_key_exists( $plugin_basename, $plugins ) && is_plugin_inactive( $plugin_basename ) ) {
        	activate_plugins( $plugin_basename );
        }
        
        //reload the page to prevent resubmission
    	wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=addon_activated&gmw_notice_status=updated' ) );
    	exit;  	
    }

    /**
     * deactivate add-on.
     *
     * @access public
     * @return void
     */
    public function deactivate_addon() {
  
    	//make sure we activated an add-on
    	if ( empty( $_POST['gmw_action'] ) || $_POST['gmw_action'] != 'deactivate_addon' )
    		return;
    	
        $inactive_addon  = $_POST['gmw_addon_deactivated'];
        $plugin_basename = $_POST['gmw_addon_basename'];
        
        //look for nonce
        if ( empty( $_POST[$inactive_addon.'_deactivate_addon_nonce'] ) )
        	return;

        //varify nonce
        if ( !wp_verify_nonce( $_POST[$inactive_addon.'_deactivate_addon_nonce'], $inactive_addon.'_deactivate_addon_nonce' ) )
        	return;
        
        unset( $this->addons[$inactive_addon] );
        update_option( 'gmw_addons', $this->addons );
                
    	$plugins = get_plugins();

        //activate the addon
        if (  array_key_exists( $plugin_basename, $plugins ) && is_plugin_active( $plugin_basename ) ) {
        	deactivate_plugins( $plugin_basename );
        }
        
        //reload the page to prevent resubmission
    	wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=addon_deactivated&gmw_notice_status=updated' ) );
    	exit;    
    }
    
    /**
     * Toggle add-on updater.
     *
     * @access public
     * @return void
     */
    public function addons_updater() {
    	     	
    	//make sure we activated an add-on
    	if ( empty( $_POST['gmw_updater_action'] ) )
			return;

    	//look for nonce
    	if ( empty( $_POST['gmw_addon_updater_nonce'] ) )
			return;
    	    	
    	//varify nonce
    	if ( !wp_verify_nonce( $_POST['gmw_addon_updater_nonce'], 'gmw_addon_updater_nonce' ) )
    		return;
    	
    	if ( $_POST['gmw_updater_action'] == 'enabled' ) {
    		
    		unset( $this->settings['admin_settings']['updater_disabled'] );	
    		
    		update_option( 'gmw_options', $this->settings );
    		
    		//reload the page to prevent resubmission
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=updater_enabled&gmw_notice_status=updated' ) );
    		exit;
    			
    	} elseif ( $_POST['gmw_updater_action'] == 'disabled' ) {
    		
    		if ( !isset( $this->settings['admin_settings'] ) ) {
    			$this->settings['admin_settings'] = array();
    		}
    		$this->settings['admin_settings']['updater_disabled'] = true;
    		
    		update_option( 'gmw_options', $this->settings );
    		
    		//reload the page to prevent resubmission
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=updater_disabled&gmw_notice_status=updated' ) );
    		exit;
    		
    	} 
    }
    
    /**
     * Get add-ons data to be displayed in the add-ons page
     * 
     */
    public function addons_data() {
    
    	$activated_addons = array();
    	$addons_status 	  = array();
    		    
    	//loop through add-ons
    	foreach ( $this->addons_data as $key => $addon ) {
    
    		//this is a temporary solution for lower versions of add-ons which are missing data
    		if ( !isset( $addon['file'] ) ) {
    	   
    			if ( $addon['name'] == 'exclude_members' ) {
    				$this->addons_data[$key]['file'] 		= GMW_EXM_PATH . '/exclude-members.php';
    				$this->addons_data[$key]['item'] 		= 'Exclude Members';
    			}
    			if ( $addon['name'] == 'global_maps' ) {
    				$this->addons_data[$key]['file'] 		= GMAPS_PATH . '/gmw-global-maps.php';
    				$this->addons_data[$key]['item'] 		= 'Global Maps';
    			}
    			if ( $addon['name'] == 'groups_locator' ) {
    				$this->addons_data[$key]['file'] 		= GMW_GL_PATH . '/groups-locator.php';
    				$this->addons_data[$key]['item'] 		= 'Groups Locator';
    			}
    			if ( $addon['name'] == 'geo_members_directory' ) {
    				$this->addons_data[$key]['file'] 		= GMW_MD_PATH . '/gmw-members-directory.php';
    				$this->addons_data[$key]['item'] 		= 'GEO Members Directory';
    			}
    			if ( $addon['name'] == 'nearby_posts' ) {
    				$this->addons_data[$key]['file'] 		= GMW_NBP_PATH . '/gmw-nearby-posts.php';
    				$this->addons_data[$key]['item'] 		= 'Nearby Posts';
    			}
    			if ( $addon['name'] == 'premium_settings' ) {
    				$this->addons_data[$key]['file'] 		= GMW_PS_PATH . '/premium-settings.php';
    				$this->addons_data[$key]['item'] 		= 'Premium Settings';
    			}
    			if ( $addon['name'] == 'wp_users_geo-location' ) {
    				$this->addons_data[$key]['file'] 		= GMW_UG_PATH . '/gmw-users-geolocation.php';
    				$this->addons_data[$key]['item'] 		= 'WP Users Geo-location';
    			}
    			if ( $addon['name'] == 'xprofile_fields' ) {
    				$this->addons_data[$key]['file'] 		= GMW_XF_PATH . '/xprofile-fields.php';
    				$this->addons_data[$key]['item'] 		= 'Xprofile Fields';
    			}
    			if ( $addon['name'] == 'gravity_forms_geo_fields' ) {
    				$this->addons_data[$key]['file'] 		= GGF_PATH . '/geo-fields.php';
    				$this->addons_data[$key]['item'] 		= 'Gravity Forms Geo Fields';
    			}
    			if ( $addon['name'] == 'resume_manager_geo-location' ) {
    				$this->addons_data[$key]['file'] 		= GRM_PATH . '/resume-manager-geolocation.php';
    				$this->addons_data[$key]['item'] 		= 'Resume Manager Geo-Location';
    			}
    			if ( $addon['name'] == 'geo_job_manager' ) {
    				$this->addons_data[$key]['file'] 		= GJM_PATH . '/geo-job-manager.php';
    				$this->addons_data[$key]['item'] 		= 'GEO Job Manager';
    			}
    			if ( $addon['name'] == 'current_location_forms' ) {
    				$this->addons_data[$key]['file'] 		= GMW_CLF_PATH . '/gmw-current-location-forms.php';
    				$this->addons_data[$key]['item'] 		= 'Current Location Forms';
    			}  
    		}
    
    		//trim name for better sorting
    		$this->addons_data[$key]['title'] = trim( str_replace( array('GMW','Add-on','-') , '', $this->addons_data[$key]['title'] ) );
    		 
    		//add all activated addons to an array
    		$activated_addons[] = $this->addons_data[$key]['name'];
    		 
    		//replace the key of array items instead of numeric to the name of the plugin
    		//if not already a name
    		if ( is_numeric( $key ) ) {
    			$this->addons_data[$this->addons_data[$key]['name']] = $this->addons_data[$key];
    			unset( $this->addons_data[$key] );
    		} 		 
    	}
    
    	//get installed plugins
    	$plugins = get_plugins();
    
    	//check all add-ons agains all installed plugins and if axists and not activated already
    	//add them to the addons_data array. This is how we can display deactivated plugins
    	//in the add-ons page as well as make it possible to activated the plugins from the add-ons page
    	foreach ( $this->all_gmw_addons() as $addon => $basename ) {
    		 
    		if ( isset( $plugins[$basename] ) && !isset( $this->addons[$addon] ) ) {
    			 
    			$this->addons_data[$addon]['name'] 	 	= $addon;
    			$this->addons_data[$addon]['title'] 	= trim( str_replace( array('GMW ','Add-on','-') , '', $plugins[$basename]['Name'] ) );
    			$this->addons_data[$addon]['desc'] 	 	= $plugins[$basename]['Description'];
    			$this->addons_data[$addon]['file'] 	    = ABSPATH . 'wp-content/plugins/'.$basename;
    			$this->addons_data[$addon]['version']  	= $plugins[$basename]['Version'];
    			$this->addons_data[$addon]['license']  	= true;  			 
    		}
    	}
    	 
    	//sort add-ons by add-on name
    	foreach ( $this->addons_data as $key => $row ) {
    		$mid[$key]  = $row['title'];
    	}
    	array_multisort( $mid, SORT_ASC, $this->addons_data );
    	 
    	//move the two free add-ons Posts Locator and Members Locator
    	//to the beggining of the array
    	$this->addons_data = array('posts' => $this->addons_data['posts'], 'friends' => $this->addons_data['friends']) + $this->addons_data;
      		
    	return $this->addons_data;
    }
    
  	 /**
     * output function.
     *
     * @access public
     * @return void
     */
    public function output() { ?>
    
        <div class="wrap">
        
            <a href="http://www.geomywp.com" target="_blank"></a>

            <?php echo GMW_Admin::gmw_credits(); ?>

            <h2 class="gmw-wrap-top-h2"><?php _e( 'GEO my WP Add-ons', 'GMW' ); ?></h2>

            <div class="clear"></div>
            
            <div class="gmw-addons-page-top-area">
            	<div></span><h3 style="display:inline-block;margin:2px 0px"><?php _e( "Add-ons usage", "GMW" ); ?></h3> - <a href="#" id="addons-info-toggle"><?php _e( "Show info", "GMW" ); ?></a></div>
            	<ol id="addons-info-wrapper" style="display:none">
            		<li><?php _e( "<strong>Add-ons installation</strong> - GEO my WP add-ons ( Except the free add-ons \"Posts Locator\" and \"Members Locator\" ) are WordPress plug-ins and need to be installed just as any other WordPress plug-ins via the plug-ins page of your site's dashboard. See <a href=\"http://codex.wordpress.org/Managing_Plugins\" target=\"_blank\">this post</a> for information about plug-ins installation.", "GMW"); ?></li>
            		<li><?php _e( "<strong>Add-ons activation</strong> - after installation please activate your add-ons. Activation can be done via the WordPress's Plug-ins page or from this Add-ons page of GEO my WP.", "GMW" ); ?></li>
            		<li><?php _e( "<strong>License activation</strong> - If any of your add-ons require a license key please enter it in the license key input box and click \"Activate\".", "GMW" ); ?>
            		    <?php _e( " The license key provided to you after a purchase of an add-on via the purchase receipt which should be sent to your email address. You can also retrieve and manage any of your license keys via your <a href=\"http://geomywp.com/purchase-history\" target=\"_blank\">Purchase History page</a>.", "GMW"); ?>
            		    <?php _e( " Note that the license key must be valid and activated in order to receive automatic updates and support for its add-on.", "GMW" ); ?></li>
            		<li><?php _e( "<strong>License deactivation</strong> - To deactivate a license simply click on \"Deactivate License\".", "GMW" ); ?></li>
            		<li><?php _e( "<strong>Add-ons deactivation</strong> - deactivation can be done from this Add-ons page of GEO my WP or via the plugins.", "GMW" ); ?>
            			<?php _e( " Please note, the license of an add-on must be deactivated before you could deactivate its add-on. This is done to prevent conflicts with any license keys you might have.", "GMW" ); ?></li>         	
            	</ol>     
            </div>
            
            <form method="post" action="" style="text-align:center">
            	
            	<div class="gmw-addons-page-top-area <?php echo ( isset( $this->settings['admin_settings']['updater_disabled'] ) ) ? 'updater-disabled' : 'updater-enabled'; ?>">
            	
	            	<p class="description" style="margin-bottom: 10px;border-bottom: 1px solid #e5e5e5;padding-bottom: 10px;">
	            		<?php _e( 'Disable/enable the premium add-ons auto-updating system. The system can cause a slow load of the plugins.php/update.php pages of your site when checking for new version of the premium add-ons.', 'GMW' ); ?>
	            		<?php _e( 'You can temporary disable the system when working in the admin area and enable it again when you are ready to check for add-ons update. This can be useful when working on a development site and there is no need to check for updates.', 'GMW' ); ?>
	            	</p>
            			
	            	<?php if ( isset( $this->settings['admin_settings']['updater_disabled'] ) ) { ?>
            			<div>
            				<p class="description" style="color:red"><?php _e( 'The add-ons updater is disabled and will not check for new updates.', 'GMW'); ?></p>    
            				<input type="submit" name="gmw_enable_updater" class="button-primary" value="<?php _e( 'Enable Updater', 'GMW' ); ?>" />
            				<input type="hidden" name="gmw_updater_action" value="enabled" />          				       				
            			</div>
	            	<?php } else { ?>
            			<div>
            				<p class="description" style="color:green"><?php _e( 'The add-ons updater is enabled and will check for updates when needed. You will be notified when new updates are avalible.', 'GMW'); ?></p>              				
            				<input type="submit" name="gmw_disable_updater" class="button-secondary" value="<?php _e( 'Disable Updater', 'GMW' ); ?>" />
            				<input type="hidden" name="gmw_updater_action" value="disabled" />         				       				
            			</div>
	            	<?php } ?>
	            	
				</div>
				<input type="hidden" name="gmw_action" value="updater_action" />
            	<?php wp_nonce_field( 'gmw_addon_updater_nonce', 'gmw_addon_updater_nonce' ); ?>
            	
            </form>
                    
                <ul class="widefat fixed">
          	
                    <?php $addOnsArray = array(); ?>

                    <?php foreach ( self::addons_data() as $addon ) : ?>

                        <?php $addOnsArray[] = $addon['name']; ?>         

                        <li class="gmw-single-addon-wrapper <?php echo $addon['name']; ?> first">	
                            <div class="gmw-addon-top-wrapper">
                                <h2 class="gmw-addon-title">
                                    <?php echo $addon['title']; ?>                               
                                    <span style="float:right;">
                                        <?php if ( isset( $addon['version'] ) ) echo $addon['version']; ?>
                                    </span>
                                </h2>
                            </div>
                            <div class="gmw-addon-content-wrapper">

                                <div class="gmw-addon-image-wrapper">
                                    <?php
                                    //get image from add-on
                                    //if ( isset( $addon['image'] ) && $addon['image'] != false ) {
                                        //echo '<img src="' . $addon['image'] . '" />';
                                    //get no-image image
                                    //} else
                                    if ( !empty( $addon['image'] ) ) {
                                        echo '<img src="https://geomywp.com/wp-content/uploads/2014/01/no-featured-image.png" />';
                                    //get image from GEO my WP server
                                    } else {
                                    	echo '<img src="https://geomywp.com/wp-content/uploads/addons-images/'.$addon['name'].'.png" />';
                                    }
                                    ?>
                                </div>
								
                                <div class="gmw-addon-desc-wrapper">
                                    <?php echo $addon['desc']; ?>
                                </div>
                            </div>

                            <!-- when add-on is deactivated -->
                            <?php if ( !isset( $this->addons ) || !isset( $this->addons[$addon['name']] ) || $this->addons[$addon['name']] == 'inactive') { ?>
								
								<?php if ( $addon['license'] ) { ?>
								
									<table>
										<tr class="gmw-licence-key-wrapper">
			
											<td class="plugin-update" colspan="3">
												
												<div class="gmw-license-key-fields-wrapper">
													
													<form method="post" action="">
																						
														<div class="gmw-license-wrapper gmw-license-invalid-wrapper addon-deactivated">
															
															<span><?php _e( 'License Key:', 'GMW' ); ?></span>
															<input 
																class="gmw-license-key-input-field" 
																disabled="disabled" 
																type="text" 
																size="30"
																value=""
																placeholder="license Key" />
																
															<button 
																disabled="disabled"
																type="submit"
																class="gmw-license-key-button button-secondary activate-license-btn button-primary"
																name="gmw_license_key_activate"
																title="<?php _e('Activate License Key', 'GMW'); ?>"
																style="padding: 0 8px !important;"
																value="<?php echo $this->license_name; ?>"><?php _e( 'Activate License', 'GMW' ); ?></button>
																										
															<p class="description"><?php _e( 'Please activate the add-on to be able to enter and activate the license key.', 'GMW' ); ?></p>
														</div> <!-- if status invalid -->												
													
													</form>	
												</div>
											</td>
										</tr>
									</table>
									
								<?php } else { ?>
									
									<table>
										<tr class="active gmw-licence-key-wrapper">
			
											<td class="plugin-update" colspan="3">
												
												<div class="gmw-license-key-fields-wrapper">
													
													<form method="post" action="">
																						
														<div class="gmw-license-wrapper gmw-license-invalid-wrapper addon-deactivated">
															
															<span>License Key: </span>
															<input 
																class="gmw-license-key-input-field" 
																disabled="disabled" 
																type="text" 
																size="30"
																value=""
																placeholder="license Key" />
												
															<p class="description"><?php _e( 'This is a free add-on and does not require a license key. Please activate the add-on to start using it.', 'GMW' ); ?></p>
														</div> <!-- if status invalid --> 	
													</form>	
												</div>
											</td>
										</tr>
									</table>
								<?php } ?>
									
                                <div class="gmw-addon-license-wrapper gmw-license-invalid gmw-addon-deactivate">
							                                    
									<form method="post" action="">
	                                    <!-- activate add-on button -->
	                                    <input type="submit" class="button-secondary button-primary gmw-addon-activation-btn" value="<?php _e( 'Activate Add-on', 'GMW' ); ?>" />
	                                    <input type="hidden" name="gmw_addon_basename" value="<?php echo plugin_basename( $addon['file'] ); ?>" />
										<input type="hidden" name="gmw_action" value="activate_addon" />
										<input type="hidden" name="gmw_addon_activated" value="<?php echo $addon['name']; ?>" />										
										<?php wp_nonce_field( $addon['name'].'_activate_addon_nonce', $addon['name'].'_activate_addon_nonce' ); ?> 	
									</form>
									
                                </div>

                            <?php } else { ?>
																		
								<?php if ( $addon['license'] ) { ?>
									<table>
										<?php 
										if ( class_exists( 'GMW_License_Key' ) ) {

											//display license key box
											$gmw_license_key = new GMW_License_Key( $addon['file'], $addon['item'], $addon['name'] );
											$gmw_license_key->license_key_output();
										}
										?>
									</table>								
								<?php } else { ?>
									
									<table>
										<tr class="active gmw-licence-key-wrapper">
			
											<td class="plugin-update" colspan="3">
												
												<div class="gmw-license-key-fields-wrapper">
													
													<form method="post">
																						
														<div class="gmw-license-wrapper gmw-license-valid-wrapper">
															
															<span>License Key: </span>
															<input 
																class="gmw-license-key-input-field" 
																disabled="disabled" 
																type="text" 
																size="30"
																value=""
																placeholder="license Key" />
												
															<p class="description"><?php _e( 'This is a free add-on and does not require a license key. Thank you for using GEO my WP.', 'GMW' ); ?></p>
														</div> <!-- if status invalid --> 	
													</form>	
												</div>
											</td>
										</tr>
									</table>
								<?php } ?>
								
								<?php if ( isset( $addon['name'] )  )  { ?>
									<?php $deactivation_message = __( 'Please deactivate the license key before deactivating the plugin', 'GMW' ); ?>
	                                 <div class="gmw-addon-license-wrapper gmw-addon-activate">   
	                                 
	                                 	<form method="post" action="">
		                                                 																
		                                    <input 
		                                    	type="submit"
		                                    	title="<?php echo $deactivation_message; ?>"
		                                    	<?php if ( gmw_is_license_valid( $addon['name'] ) ) { ?>
		                                    		onclick="alert('<?php echo $deactivation_message; ?>'); return false;"
		                                    	<?php } ?>	
		                                    	class="button-secondary gmw-addon-activation-btn btn-disabled" 
		                                    	value="<?php _e( 'Deactivate Add-on', 'GMW' ); ?>" />
		                                    	
		                                    <input type="hidden" name="gmw_addon_basename" value="<?php echo plugin_basename( $addon['file'] ); ?>" />
		                                    <input type="hidden" name="gmw_action" value="deactivate_addon" />
		                                    <input type="hidden" name="gmw_addon_deactivated" value="<?php echo $addon['name']; ?>" />										
											<?php wp_nonce_field( $addon['name'].'_deactivate_addon_nonce', $addon['name'].'_deactivate_addon_nonce' ); ?>
	                                    	
	                                    </form>  									
	                                </div>
	                            <?php } ?>
														
                            <?php  } ?>

                        </li>

                    <?php endforeach; ?>

                    <?php self::output_feed_addons(); ?>
                </ul>          
        </div>
        <script>
            jQuery(document).ready(function($) {

                var addonsArray = JSON.parse('<?php echo json_encode($addOnsArray); ?>');

                $.each(addonsArray, function(i, ob) {
                    $('.' + ob).each(function() {
                        if (!$(this).hasClass('first'))
                            $(this).hide();
                    });
                });

                $('#addons-info-toggle').click(function() {
					$('#addons-info-wrapper').slideToggle();
                });
            });
        </script>
        <?php
    }

    /**
     * Add-ons feed from GEO my WP
     *
     * @access private
     * @return void
     */
    private function output_feed_addons() {
  	    
    	if ( false === ( $cache = get_transient( 'gmw_add_ons_feed' ) ) ) {
    		    		
    		$feed = wp_remote_get( 'http://geomywp.com/add-ons/?feed=gmw_addons', array( 'sslverify' => false ) );

    		if ( !is_wp_error( $feed ) && $feed['response']['code'] == '200' ) {
    			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0) {
    				$cache = wp_remote_retrieve_body( $feed );
    				set_transient( 'gmw_add_ons_feed', $cache, 3600 );
    			}
    		} else {
    			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the add-ons list from the server. Please try again later. Error Message: ', 'GMW' ). '<b style="color:red">'.$feed->get_error_message(). '. </b>' . __( 'Error code: ', 'GMW' ). '<b style="color:red">'.$feed->get_error_code(). '</b></p></div>';
    		}
    	}  	
    	
    	echo $cache; 	
    }
    
    /**
     * GMW Function - add notice messages
     *
     * @access public
     * @since 2.5
     * @author Eyal Fitoussi
     *
     */
    function notices_messages( $messages ) {
    
    	$messages['updater_enabled']  	= __( 'Add-on updater enabled.', 'GMW' );
    	$messages['updater_disabled'] 	= __( 'Add-on updater disabled.', 'GMW' );
    	$messages['addon_deactivated']  = __( 'Add-on deactivated.', 'GMW' );
    	$messages['addon_activated'] 	= __( 'Add-on activated.', 'GMW' );
    
    	return $messages;
    }
   
}
endif;