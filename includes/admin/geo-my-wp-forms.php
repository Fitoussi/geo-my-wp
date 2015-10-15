<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * GMW_Forms class.
 */

class GMW_Forms {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
    		    	
        $this->forms = get_option('gmw_forms');
               
        add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
        add_action( 'gmw_create_new_form', 		  array( $this, 'new_form' 		   ) );
        add_action( 'gmw_duplicate_form',  		  array( $this, 'duplicate_form'   ) );
        add_action( 'gmw_delete_form', 			  array( $this, 'delete_form' 	   ) );
    }

    /**
     * GMW Function - add notice messages
     *
     * @access public
     * @since 2.5
     * @author Eyal Fitoussi
     *
     */
    public function notices_messages( $messages ) {
    
    	$messages['form_created'] 					 		= __( 'Form successfully created.', 'GMW' );
    	$messages['form_not_created'] 						= __( 'There was an error while trying to create the new form.', 'GMW' );
    	$messages['form_duplicated'] 					 	= __( 'Form successfully duplicated.', 'GMW' );
    	$messages['form_not_duplicated'] 					= __( 'There was an error while trying to duplicate the form.', 'GMW' );
    	$messages['form_deleted'] 					 		= __( 'Form successfully deleted.', 'GMW' );
    	$messages['form_not_deleted'] 						= __( 'There was an error while trying to delete the form.', 'GMW' );
    	$messages['no_pt_locations_to_export'] 		 		= __( 'No Locations found. Nothing was exported', 'GMW' );
    	$messages['no_pt_locations_to_import'] 		 		= __( 'No Locations found. Nothing was imported.', 'GMW' );
    	$messages['pt_locations_imported'] 			 		= __( 'locations successfully imported.', 'GMW' );
    	$messages['pt_locations_csv_imported'] 	 	 		= __( 'locations successfully imported.', 'GMW' );
    	$messages['pt_locations_csv_import_failed']  		= __( 'Import failed. No locations found.', 'GMW' );
    	$messages['store_locator_import_failed_no_table']   = __( "Import failed! The database table wp_store_locator does not exist in database.", 'GMW' );
    	$messages['store_locator_imported'] 			 	= __( 'locations successfully imported.', 'GMW' );
    
    	return $messages;
    }

    /**
     * Create new form
     * 
     * @access public
     * @return void
     * 
     */
    public function new_form() {

    	if ( empty( $_GET['gmw_form_id'] ) || empty( $_GET['gmw_form_type'] ) ) {
    		//reload the page to prevent resubmission
       		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_created&gmw_notice_status=error' ) );
        	exit;
    	}
    	
        $formID = $_GET['gmw_form_id'];

    	//default form settings
    	$this->forms[$formID] = array(
    			'ID'          => $_GET['gmw_form_id'],
    			'addon'       => $_GET['gmw_form_addon'],
    			'form_title'  => str_replace( '+', ' ', $_GET['gmw_form_title'] ),
    			'name'  	  => $_GET['gmw_form_name'],
    			'form_type'   => $_GET['gmw_form_type'],
    			'prefix'      => $_GET['gmw_form_prefix'],
    			'ajaxurl'     => GMW_AJAX,
                'page_load_results' => array (
                        'post_types'     => array( 'post' ),
                        'display_posts'  => 1,
                        'display_map'    => 'results'
                ),
    			'search_form' => array (
    					'post_types' 	 => array( 'post' ),
    					'form_template'  => 'default',
    					'address_field'  => array(
    							'title'	               => __( 'Enter Address...', 'GMW' ),
    							'within'               => 1,
                                'address_autocomplete' => 1
    					),
    					'locator_icon'	 => 'within_address_field',
    					'locator_submit' => 1,
                        'units'          => 'both',
                        'radius'         => '5,10,25,50,100,150,200'
    			),
    			'search_results' => array (
                        'results_template' => 'default',
    					'results_list'     => 1,
                        'display_posts'    => 1,
    					'display_members'  => 1,
    					'display_members'  => 1,
    					'display_groups'   => 1,
    					'display_users'	   => 1,
    					'display_map'	   => 'results',
    					'get_directions'   => 1,
    					'excerpt'		   => array(
    							'use'	=> 1,
    							'more'  => 'read more...'
    					),
                        'per_page'      => '5,10,15,25',
    			),
    			'results_map' => array(
    					'map_width'	       => '100%',
    					'map_height'       => '300px',
    					'map_type'	       => 'ROADMAP',
    					'zoom_level'       => 'auto',
                        'map_icon_usage'   => 'same',
                        'markers_display'  => 'normal'   					
    			),
    			'info_window' => array(
    					'iw_type'	 	  => 'popup',
    					'popup_template'  => 'left-white',
    					'address'	 	  => 1,
    					'draggable_use'   => 1,
    					'get_directions'  => 1,
    					'live_directions' => 1,
    					'address'		  => 1
    			)
    	);
        
        foreach ( $this->forms as $key => $option ) {
            if ( empty( $option['ID'] ) || !is_numeric( $option['ID'] ) || empty( $option['form_type'] ) ) {
                unset( $this->forms[$key] );
            }
        }

        //ksort($this->forms);

        update_option( 'gmw_forms', $this->forms );
        
        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_action=edit_form&gmw_form_title='.str_replace( ' ', '+', $_GET['gmw_form_title'] ).'&gmw_form_prefix='.$this->forms[$formID]['prefix'].'&form_type='.$this->forms[$formID]['form_type'].'&formID='.$formID ) );
        exit;
    }

    /**
     * Duplicate form
     * 
     * @access public
     * @return void
     * 
     */
    public function duplicate_form() {

    	if ( empty( $_GET['formID'] ) ) {
    		//reload the page to prevent resubmission
       		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_duplicated&gmw_notice_status=error' ) );
        	exit;
    	}
    	
        $newForm       = $this->forms[$_GET['formID']];
        $newForm['ID'] = $_GET['gmw_form_id'];
    
        $newForm['name'] = $newForm['name']. ' copy';

        if ( array_key_exists( $newForm['ID'], $this->forms ) )
            return;

        array_push( $this->forms, $newForm );

        foreach ( $this->forms as $key => $option ) {
            if ( empty( $option['ID'] ) || !is_numeric( $option['ID'] ) || empty( $option['form_type'] ) ) {
                unset($this->forms[$key]);
            }
        }

        update_option('gmw_forms', $this->forms);

        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_duplicated&gmw_notice_status=updated' ) );
        exit;
    }

    /**
     * delete form
     * 
     */
    public function delete_form() {

    	if ( empty( $_GET['formID'] ) ) {
    		//reload the page to prevent resubmission
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_deleted&gmw_notice_status=error' ) );
    		exit;
    	}
    	
        unset( $this->forms[$_GET['formID']] );
        update_option( 'gmw_forms', $this->forms );

        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_deleted&gmw_notice_status=updated' ) );
        exit;       
    }
    
     /*
     *  you can add your own button with the filter below. 
     *  example of array to pass:
     *  $buttons = array(
     *              'name'       => 'posts',
     *              'title'      => __('Post Types','GMW'),
     *              'link_title' => 'Create new post types form",
     *          );
     */
    public static function new_form_list() {
                      
        $nextFormID = 1;
        $gmw_forms  = get_option('gmw_forms');

        if ( !empty( $gmw_forms ) ) {
            $nextFormID = max( array_keys( $gmw_forms ) ) + 1;
        }

        $buttons = array();
        $buttons = apply_filters( 'gmw_admin_new_form_button', $buttons );
        
        ksort( $buttons );

        $output = '<select onchange="if ( jQuery(this).val() != \'\' )  { window.location.href = jQuery(this).val(); }">';
        $output .= '<option value="">'.__( 'Create new form', 'GMW' ).'</option>';

        //display buttons
        foreach ($buttons as $button) {
            $form_url = esc_url( 'admin.php?page=gmw-forms&gmw_action=create_new_form&gmw_form_title='.str_replace( ' ', '+', $button['title'] ).'&gmw_form_addon='.$button['addon'].'&gmw_form_prefix='.$button['prefix'].'&gmw_form_type='.$button['name'].'&gmw_form_id='.$nextFormID.'&gmw_form_name=form_id_'.$nextFormID );
            $output  .= '<option value="'.$form_url.'">'.esc_attr( $button['title'] ).'</option>';
        }
        $output .= '</select>';

        return $output;
    }

    /**
     * output list of forms
     *
     * @access public
     * @return void
     */
    public function output() {

        $this->addons = get_option('gmw_addons');
        $nextFormID   = 1;

        if ( !empty( $this->forms ) ) {
            $nextFormID = max( array_keys( $this->forms ) ) + 1;
        }

        //$gmw_forms = get_option( 'gmw_forms' );
        ?>
        <div class="wrap">

            <h2 class="gmw-wrap-top-h2">
                <i class="fa fa-map-marker"></i>
                <?php echo _e('Forms', 'GMW'); ?>
                <?php echo self::new_form_list(); ?>
                <?php gmw_admin_support_button(); ?>
            </h2>

            <div class="clear"></div>

            <table class="widefat" style="margin-top: 10px">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:50px;padding:11px 10px"><?php _e('ID', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Type', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Name', 'GMW'); ?></th>
                        <th scope="col" id="id" class="manage-column"><?php _e('Action', 'GMW'); ?></th>
                        <th scope="col" id="active" class="manage-column"><?php _e('Usage', 'GMW'); ?></th> 

                    </tr>
                </thead>
                <tbody class="list:user user-list">

                    <?php $alternate = ''; ?>

                    <?php if ( !empty( $this->forms ) ) : ?>

                        <?php $rowNumber = 0; ?>

			            <?php foreach ( $this->forms as $key => $option ) : ?>

			                <?php if ( !empty( $option['addon'] ) && !empty( $this->addons[$option['addon']] ) && $this->addons[$option['addon']] == 'active' ) : ?>
			
			                    <?php $alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
								
								<?php $formName = ( !empty( $option['name'] ) ) ? $option['name'] : 'form_id_'.$option['ID']; ?>
								
                                <tr class="<?php echo $alternate; ?>" style="height:50px;">
                                    <td>
                                        <span><?php echo $option['ID']; ?></span>
                                    </td>
                                    <td>
                                        <span><a title="<?php __('Edit this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_action=edit_form&gmw_form_title=<?php echo $option['form_title']; ?>&gmw_form_prefix=<?php echo $option['prefix']; ?>&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>"><?php echo $option['form_title']; ?></a></span>
                                    </td>
									<td>
                                        <span>
                                        	<a title="<?php echo $formName; ?>" href="admin.php?page=gmw-forms&gmw_action=edit_form&gmw_form_title=<?php echo $option['form_title']; ?>&gmw_form_prefix=<?php echo $option['prefix']; ?>&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>&gmw_form_name=<?php echo $formName; ?>"><?php echo $formName; ?></a></span>
                                    </td>
                                    <td>		
                                        <span class="edit">
                                            <a title="<?php __('Edit this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_action=edit_form&gmw_form_title=<?php echo $option['form_title']; ?>&gmw_form_prefix=<?php echo $option['prefix']; ?>&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>"><?php _e('Edit', 'GMW'); ?></a> | 
                                        </span>
                                        <span class="edit">
                                            <a title="<?php __('Duplicate this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_action=duplicate_form&form_type=<?php echo $option['form_type']; ?>&formID=<?php echo $option['ID']; ?>&gmw_form_id=<?php echo $nextFormID; ?>"><?php _e('Duplicate', 'GMW'); ?></a> | 
                                        </span>
                                        <span class="edit">
                                            <a title="<?php __('Delete this form', 'GMW'); ?>" href="admin.php?page=gmw-forms&gmw_action=delete_form&formID=<?php echo $option['ID']; ?>" onclick="return confirm( 'sure?' ); "><?php _e('Delete', 'GMW'); ?></a>                                                    
                                        </span>	
                                    </td>
                                    <td class="column-title" style="padding: 5px 0px;">
                                        <code>[gmw form="<?php echo $option['ID']; ?>"]</code>
                                    </td>
                                </tr>
	
                            <?php endif; ?>
			
			                <?php $rowNumber++; ?>
			
			            <?php endforeach; ?>
				
			        <?php else : ?>

                        <tr class="<?php echo $alternate; ?>" style="height:50px;">
                            <td>
                                <span></span>
                            </td>
                            <td>
                                <span><?php _e( "You don't have any forms yet.", "GMW" ); ?></span>
                            </td>
                            <td class="column-title" style="padding: 5px 0px;">

                            </td>
                            <td>		

                            </td>			          
                        </tr>

			        <?php endif; ?>
		                
	                </tbody>
	                <tfoot>
	                    <tr>
	                        <th scope="col" id="cb" class="manage-column  column-cb check-column" style="width:50px;padding:11px 10px"><?php _e( 'ID', 'GMW' ); ?></th>
	                        <th scope="col" id="id" class="manage-column"><?php _e( 'Type', 'GMW' ); ?></th>
	                        <th scope="col" id="id" class="manage-column"><?php _e( 'Name', 'GMW' ); ?></th>
	                        <th scope="col" id="active" class="manage-column"><?php _e( 'Usage', 'GMW' ); ?></th> 
	                        <th scope="col" id="id" class="manage-column"><?php _e( 'Action', 'GMW' ); ?></th>
	                    </tr>
	                </tfoot>        
	            </table>	
	        </div>
	        
        <?php
    }
}