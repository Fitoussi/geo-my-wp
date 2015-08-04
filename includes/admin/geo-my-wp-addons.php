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
    		    			
       	add_action( 'gmw_activate_addon',   	  array( $this, 'activate_addon'   ) );
        add_action( 'gmw_deactivate_addon', 	  array( $this, 'deactivate_addon' ) );       
        add_action( 'gmw_updater_action', 		  array( $this, 'addons_updater'   ) );            
        add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );           
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
        if ( array_key_exists( $plugin_basename, $plugins ) && is_plugin_inactive( $plugin_basename ) ) {
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
        if ( array_key_exists( $plugin_basename, $plugins ) && is_plugin_active( $plugin_basename ) ) {
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
    		    	
    		update_option( 'gmw_updater_disabled', false );
    		
    		//reload the page to prevent resubmission
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=updater_enabled&gmw_notice_status=updated' ) );
    		exit;
    			
    	} elseif ( $_POST['gmw_updater_action'] == 'disabled' ) {
    		    		
    		update_option( 'gmw_updater_disabled', true );
    		
    		//reload the page to prevent resubmission
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-add-ons&gmw_notice=updater_disabled&gmw_notice_status=updated' ) );
    		exit;
    		
    	} 
    }
    
  	/**
     * output function.
     *
     * @access public
     * @return void
     */
    public function output() { 
        
        $updater = get_option( 'gmw_updater_disabled' );
        
        //get installed plugins
        $plugins = get_plugins();

        //get remote addons data from geomywp.com
        $remote_addons = self::get_feed_addons();
        $addons_data   = $this->addons_data;

         //verify feed. if feed ok merge it with the addons array
        if ( !empty( $remote_addons ) ) {

            //merge remote add-ons with local addons data
            $addons_data = array_replace_recursive( $addons_data, $remote_addons );
        } 
             
        //sort add-ons by add-on name
        $inst = $title = array();
        foreach ( $addons_data as $key => $row ) {
            $title[$key] = $row['title'];
        }
        array_multisort( $title, SORT_ASC, $addons_data );
        
        //move the core add-ons to the beggining of the list
        $addons_data = array(
                'posts'                 => $addons_data['posts'],
                'friends'               => $addons_data['friends'],
                'single_location'       => $addons_data['single_location'],
                'current_location'      => $addons_data['current_location'],
                'sweetdate_geolocation' => $addons_data['sweetdate_geolocation']
        ) + $addons_data;
        
        ?>
        <!-- Addons page wrapper -->
        <div class="wrap">
            
            <!-- Title -->
            <h2 class="gmw-wrap-top-h2">
                <i class="fa fa-puzzle-piece"></i>
                <?php _e( 'GEO my WP Add-ons', 'GMW' ); ?>
                <?php gmw_admin_support_button(); ?>
            </h2>

            <div class="clear"></div>

            <!-- add-ons page information -->
            <div class="gmw-addons-page-top-area">
            	<div></span><h3 style="display:inline-block;margin:2px 0px"><?php _e( "Add-ons usage", "GMW" ); ?></h3> - <a href="#" id="addons-info-toggle" onclick="jQuery('#addons-info-wrapper').slideToggle();"><?php _e( "Show info", "GMW" ); ?></a></div>
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
            
            <!-- updater form -->
            <form method="post" action="" style="text-align:center">
            	
            	<div class="gmw-addons-page-top-area <?php echo ( !empty( $updater ) ) ? 'updater-disabled' : 'updater-enabled'; ?>">
            	
	            	<p class="description" style="margin-bottom: 10px;border-bottom: 1px solid #e5e5e5;padding-bottom: 10px;">
	            		<?php _e( 'Disable/enable the premium add-ons auto-updating system. The system can cause a slow load of the plugins.php/update.php pages of your site when checking for new version of the premium add-ons.', 'GMW' ); ?>
	            		<?php _e( 'You can temporary disable the system when working in the admin area and enable it again when you are ready to check for add-ons update. This can be useful when working on a development site and there is no need to check for updates.', 'GMW' ); ?>
	            	</p>
            			
	            	<?php if ( !empty( $updater ) ) { ?>
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

                    <?php foreach ( $addons_data as $addon ) : ?>
                
                        <?php
                        //Reset some variables
                        $addon['update_avaliable'] = '0';
                    
                        //check for some data in permium add-ons
                        if ( empty( $addon['core'] ) ) {
                            
                            //Reset some variables
                            $addon['installed'] = false;
                            $addon['activated'] = false;
                            $addon['license']   = true;
                            $addon['version']   = ( !empty( $addon['current_version'] ) ) ? $addon['current_version'] : '1.0';

                            //create basename if not exist
                            if ( empty( $addon['basename'] ) ) {
                                $addon['basename'] = plugin_basename( $addon['file'] );
                            }

                            //create file if doesnt exist
                            if ( empty( $addon['file'] ) ) {
                                $addon['file'] = ABSPATH . 'wp-content/plugins/'.$addon['basename'];
                            } 
                       
                            //if add-on installed
                            if ( isset( $plugins[$addon['basename']] ) ) {

                                $addon['installed'] = true;                              
                                $addon['version']   = $plugins[$addon['basename']]['Version'];

                                //if add-on activated
                                if ( is_plugin_active( $addon['basename'] ) ) {

                                    $addon['activated'] = true;

                                    //check if update avaliable and required
                                    if ( !empty( $addon['required_version'] ) && version_compare( $addon['version'], $addon['required_version'], '<' ) ){
                                        $addon['update_avaliable'] = '1';

                                    //check if update available but not required
                                    } elseif ( !empty( $addon['current_version'] ) && version_compare( $addon['version'], $addon['current_version'], '<' ) ) { 
                                        $addon['update_avaliable'] = '2';
                                    }

                                }
                            }
                        }
                        ?>

                        <!-- addon wrapper -->
                        <li class="gmw-single-addon-wrapper <?php echo $addon['name']; ?> first">	
                            
                            <!-- ribbons -->

                            <!-- free add-on -->
                            <?php if ( !empty( $addon['core'] ) && empty( $addon['activated'] ) ) { ?>    
                                <div class="gmw-ribbon-wrapper"><div class="gmw-ribbon free"><?php _e( 'Free Add-on', 'GMW' ); ?></div></div>
                            <?php } ?>

                            <!-- New add-on -->
                            <?php if ( empty( $addon['installed'] ) && !empty( $addon['new_addon'] ) ) { ?>                        
                                <div class="gmw-ribbon-wrapper"><div class="gmw-ribbon blue"><?php _e( 'New Add-on', 'GMW' ); ?></div></div>   
                            <?php } ?>

                            <!-- GMW version is too low -->
                            <?php if ( !empty( $addon['gmw_required'] ) ) { ?>
                                <div class="gmw-ribbon-wrapper"><div class="gmw-ribbon red"><?php _e( 'Incompatible', 'GMW' ); ?></div></div>   
                            <!-- Add-on new version is available and requires update  -->
                            <?php } elseif ( $addon['update_avaliable'] == 1 ) { ?>                        
                                <div class="gmw-ribbon-wrapper"><div class="gmw-ribbon red"><?php _e( 'Update Required', 'GMW' ); ?></div></div>   
                            <!-- Add-on new version is available but not required  -->
                            <?php } elseif ( $addon['update_avaliable'] == 2 ) { ?>
                                <div class="gmw-ribbon-wrapper"><div class="gmw-ribbon green"><?php _e( 'Update Available', 'GMW' ); ?></div></div>  
                            <?php } ?>

                            <!-- addon title -->
                            <div class="gmw-addon-top-wrapper">
                                <h2 class="gmw-addon-title">
                                    <?php echo esc_attr( $addon['title'] ); ?>                               
                                    <span style="float:right;">
                                        <?php if ( isset( $addon['version'] ) ) echo esc_attr( $addon['version'] ); ?>
                                    </span>
                                </h2>
                            </div>

                            <div class="gmw-addon-content-wrapper">

                                <!-- Image -->
                                <div class="gmw-addon-image-wrapper">
                                    <?php
                                    //get image from add-on
                                    //if ( isset( $addon['image'] ) && $addon['image'] != false ) {
                                        //echo '<img src="' . $addon['image'] . '" />';
                                    //get no-image image
                                    //} else
                                    //if ( empty( $addon['image'] ) ) {
                                    //    echo '<img src="https://geomywp.com/wp-content/uploads/2014/01/no-featured-image.png" />';
                                    //get image from GEO my WP server
                                   // } else {
                                    	echo '<img src="https://geomywp.com/wp-content/uploads/addons-images/'.esc_attr( $addon['name'] ).'.png" />';
                                    //}
                                    ?>
                                </div>

                                <!-- description -->
                                <div class="gmw-addon-desc-wrapper">
                                    
                                    <div class="gmw-addon-desc-inner">
                                        <em><?php esc_attr_e( $addon['desc'] ); ?></em> 
                                        
                                        <!-- Notices -->
                                        <?php if ( !empty( $addon['gmw_required'] ) ) { ?>
                                            <div class="gmw-addon-update-required-wrapper">
                                                <em><?php echo $addon['gmw_required_message']; ?> <?php echo GMW_Admin::update_addon_link( $plugins[$addon['basename']]['Name'] ); ?></em>
                                            </div>
                                        <?php } elseif ( $addon['update_avaliable'] == 1 ) { ?>     
                                            <div class="gmw-addon-update-required-wrapper">
                                                <em><?php echo $addon['required_message']; ?> <?php echo GMW_Admin::update_addon_link( $plugins[$addon['basename']]['Name'] ); ?></em>
                                            </div>
                                        <?php } elseif ( $addon['update_avaliable'] == 2 ) { ?>     
                                            <div class="gmw-addon-update-available-wrapper">
                                                <em>New version of <?php esc_attr_e( $addon['title'] ); ?> <?php esc_attr_e( $addon['current_version'] ); ?> is available for update. <?php echo GMW_Admin::update_addon_link( $plugins[$addon['basename']]['Name'] ); ?></em>.
                                            </div>
                                        <?php } ?>
                                     </div>
                                 </div>
                            </div>

                            <!-- when add-on is deactivated -->
                            <?php if ( !isset( $this->addons ) || !isset( $this->addons[$addon['name']] ) || $this->addons[$addon['name']] == 'inactive') { ?>
								
								<?php if ( $addon['license'] ) { ?>
								
									<table>
										<tr class="gmw-license-key-wrapper">
			
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
										<tr class="active gmw-license-key-wrapper">
			
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
									
                                <div class="gmw-addon-license-wrapper gmw-license-invalid gmw-addon-deactivate <?php if ( empty( $addon['installed'] ) ) echo 'not-installed'; ?>">

                                    <?php if ( !empty( $addon['installed'] ) ) { ?>
							                                    
    									<form method="post" action="">
    	                                    <!-- activate add-on button -->
    	                                    <input type="submit" class="button-secondary button-primary gmw-addon-activation-btn" value="<?php _e( 'Activate Add-on', 'GMW' ); ?>" />
    	                                    <input type="hidden" name="gmw_addon_basename" value="<?php echo plugin_basename( esc_attr( $addon['file'] ) ); ?>" />
    										<input type="hidden" name="gmw_action" value="activate_addon" />
    										<input type="hidden" name="gmw_addon_activated" value="<?php echo esc_attr( $addon['name'] ); ?>" />										
    										<?php wp_nonce_field( $addon['name'].'_activate_addon_nonce', $addon['name'].'_activate_addon_nonce' ); ?> 	
    									</form>

                                    <?php } else { ?>
                                        <a href="<?php echo esc_url( $addon['premalink'] ); ?>" class="button-secondary button-primary gmw-addon-activation-btn" target="_blank" title="Get add-on"><?php _e( 'Get Add-on', 'GMW' ); ?></a>
                                    <?php } ?>
									
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
										<tr class="active gmw-license-key-wrapper">
			
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
								
								<?php if ( isset( $addon['name'] ) ) { ?>

									<?php $deactivation_message = __( 'Please deactivate the license key before deactivating the plugin', 'GMW' ); ?>

	                                 <div class="gmw-addon-license-wrapper gmw-addon-activate">   
	                                 
	                                 	<form method="post" action="">
		                                                 																
		                                    <input 
		                                    	type="submit"
		                                    	title="<?php esc_attr_e( $deactivation_message ); ?>"
		                                    	<?php if ( gmw_is_license_valid( $addon['name'] ) ) { ?>
		                                    		onclick="alert('<?php esc_attr_e( $deactivation_message ); ?>'); return false;"
		                                    	<?php } ?>	
		                                    	class="button-secondary gmw-addon-activation-btn btn-disabled" 
		                                    	value="<?php _e( 'Deactivate Add-on', 'GMW' ); ?>" />
		                                    	
		                                    <input type="hidden" name="gmw_addon_basename" value="<?php echo plugin_basename( esc_attr( $addon['file'] ) ); ?>" />
		                                    <input type="hidden" name="gmw_action" value="deactivate_addon" />
		                                    <input type="hidden" name="gmw_addon_deactivated" value="<?php echo esc_attr( $addon['name'] ); ?>" />										
											<?php wp_nonce_field( $addon['name'].'_deactivate_addon_nonce', $addon['name'].'_deactivate_addon_nonce' ); ?>
	                                    	
	                                    </form>  									
	                                </div>
	                            <?php } ?>
														
                            <?php  } ?>

                        </li>

                    <?php endforeach; ?>
                </ul>          
        </div>
        <?php
    }

    /**
     * Add-ons feed from GEO my WP
     *
     * @access private
     * @return void
     */
    private function get_feed_addons() {

        //delete_transient( 'gmw_addons_feed' );

    	if ( false === ( $output = get_transient( 'gmw_addons_feed' ) ) ) {
  
    		$feed = wp_remote_get( 'http://geomywp.com/add-ons/?feed=gmw_addons_feed', array( 'sslverify' => false ) );

    		if ( !is_wp_error( $feed ) && $feed['response']['code'] == '200' ) {

    			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0) {

    				$output = wp_remote_retrieve_body( $feed );

                    $output = simplexml_load_string( $output );
                    $output = json_encode( $output );
                    $output = json_decode( $output, TRUE );

    				set_transient( 'gmw_addons_feed', $output, 6 * HOUR_IN_SECONDS );
    			}
    		} else {	
                echo '<div class="error"><p>' . __( 'There was an error retrieving the add-ons list from the server. Please try again later. Error Code: ', 'GMW' ).$feed['response']['code'].'</p></div>';    		   
                $output = false;
            }
    	}  	

    	return $output; 	
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
    	$messages['updater_disabled']   = __( 'Add-on updater disabled.', 'GMW' );
    	$messages['addon_deactivated']  = __( 'Add-on deactivated.', 'GMW' );
    	$messages['addon_activated'] 	= __( 'Add-on activated.', 'GMW' );
    
    	return $messages;
    }
}
endif;