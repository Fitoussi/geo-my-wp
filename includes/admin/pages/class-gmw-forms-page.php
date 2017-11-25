<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * GMW_Forms class.
 *
 * GEO my WP forms page
 */
class GMW_Forms_Page {

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct() {
    		    	
        if ( empty( $_GET['page'] ) || $_GET['page'] != 'gmw-forms' ) {
            return;
        }

        add_filter( 'gmw_admin_notices_messages', array( $this, 'notices_messages' ) );
        add_action( 'gmw_create_new_form', 		  array( $this, 'create_new_form'  ) );
        add_action( 'gmw_duplicate_form',  		  array( $this, 'duplicate_form'   ) );
        add_action( 'gmw_delete_form', 			  array( $this, 'delete_form' 	   ) );
        add_action( 'admin_init',                 array( $this, 'bulk_delete'      ) );
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
    
    	$messages['form_created'] 		 = __( 'Form successfully created.', 'GMW' );
    	$messages['form_not_created'] 	 = __( 'There was an error while trying to create the new form.', 'GMW' );
    	$messages['form_duplicated']     = __( 'Form successfully duplicated.', 'GMW' );
    	$messages['form_not_duplicated'] = __( 'There was an error while trying to duplicate the form.', 'GMW' );
    	$messages['form_deleted'] 		 = __( 'Form successfully deleted.', 'GMW' );
    	$messages['form_not_deleted'] 	 = __( 'There was an error while trying to delete the form.', 'GMW' );

    	return $messages;
    }

    /**
     * Create new form
     * 
     * @access public
     * 
     * @return void
     */
    public function create_new_form() {
        
        //verfiy form data
        if ( empty( $_GET['addon'] ) || empty( $_GET['slug'] ) ) {
            
            wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_created&gmw_notice_status=error' ) );
            
            exit;
        }

        $new_data = array();

        //get form values
        $new_form['slug']   = $_GET['slug'];
        $new_form['addon']  = $_GET['addon'];
        $new_form['name']   = str_replace( '+', ' ', $_GET['name'] );    
        $new_form['prefix'] = $_GET['prefix'];
        $new_form['data']   = serialize( GMW_Forms_Helper::default_settings( $new_form ) );

        global $wpdb;

        //create new form in database
        $wpdb->insert( 
            $wpdb->prefix . 'gmw_forms', 
            array( 
                'slug'   => $new_form['slug'],
                'addon'  => $new_form['addon'],    
                'name'   => $new_form['name'],
                'prefix' => $new_form['prefix'],
                'title'  => '',
                'data'   => $new_form['data']
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        //get the ID of the new form
        $new_form_id = $wpdb->insert_id;

        //make sure a form was created
    	if ( empty( $new_form_id ) ) {
       		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_created&gmw_notice_status=error' ) );
        	exit;
    	}
        
        //update new form with the default values
        $wpdb->update( 
            $wpdb->prefix . 'gmw_forms', 
            array( 
                'title' => 'form_id_'.$new_form_id,
            ), 
            array( 'ID' => $new_form_id ), 
            array( 
                '%s'
            ), 
            array( '%d' ) 
        );
        
        // update forms in cache
        GMW_Forms_Helper::update_forms_cache();

        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_action=edit_form&form_id='.$new_form_id.'&prefix='.$new_form['prefix'] ) );
        
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

        //verify the form ID
    	if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) {
       		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_duplicated&gmw_notice_status=error' ) );
        	exit;
    	}
    	
        global $wpdb;

        // get form data
        $form = $wpdb->get_row( 
            $wpdb->prepare( "
                SELECT * FROM {$wpdb->prefix}gmw_forms
                WHERE ID = %d"
            , $_GET['form_id'] )
        );

        if ( empty( $form ) ) {
            wp_die( __( 'An error occurred while trying to retrieve the form.', 'GMW' ) );
        }

        //create new form in database
        $new_form = $wpdb->insert( 
            $wpdb->prefix . 'gmw_forms', 
            array( 
                'slug'   => $form->slug,
                'addon'  => $form->addon,
                'name'   => $form->name,
                'title'  => $form->title.' copy',
                'prefix' => $form->prefix,
                'data'   => $form->data
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        // update forms in cache
        GMW_Forms_Helper::update_forms_cache();

        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_duplicated&gmw_notice_status=updated' ) );
        
        exit;
    }

    /**
     * Delete form
     * 
     * @return [type] [description]
     */
    public function delete_form() {

        //abort if form ID doesn't exists
    	if ( empty( $_GET['form_id'] ) || ! absint( $_GET['form_id'] ) ) {
    		wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_not_deleted&gmw_notice_status=error' ) );
    		exit;
    	}
    	
        GMW_Forms_Helper::delete_form( $_GET['form_id'] );

        //reload the page to prevent resubmission
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_deleted&gmw_notice_status=updated' ) );
        
        exit;       
    }

    /**
     * Bulk delete forms
     * 
     * @return [type] [description]
     */
    public function bulk_delete() {

        if ( empty( $_POST['gmw_page'] ) || $_POST['gmw_page'] != 'gmw-forms' || empty( $_POST['form_ids'] ) || $_POST['bulk_action'] != 'delete' )
            return;

        // run a quick security check
        if ( ! check_admin_referer( 'gmw_forms_page', 'gmw_forms_page' ) ) {
            wp_die( __( 'Cheatin\' eh?!', 'GMW' ) );
        }

        global $wpdb;

        //delete forms from database
        $wpdb->query( 
            $wpdb->prepare( "
                DELETE FROM {$wpdb->prefix}gmw_forms
                WHERE ID IN (".str_repeat( "%d,", count( $_POST['form_ids'] ) - 1 ) . "%d )", $_POST['form_ids'] 
            )
        );

        // update forms in cache
        GMW_Forms_Helper::update_forms_cache();
        
        wp_safe_redirect( admin_url( 'admin.php?page=gmw-forms&gmw_notice=form_deleted&gmw_notice_status=updated' ) );
        exit;  
    }
    
     /*
     *  you can add your own button using the filter below. To create a button you will need to pass an array with the following arg:
     *
     *  name - the name/slug for the button ( ex. posts or post_types )
     *  addon - the addon's slug the button belongs to
     *  title - the title/lable for the button ( ex. Posts locator )
     *  prefix - a prefix for your button ( ex. for post_type a good prefix would be "pt" )
     *  priority - the prority the button will show in the dropdown
     *  
     *  example :
     *  $buttons = array(
     *      'slug'       => 'posts',
     *      'addon'      => 'posts',
     *      'name'       => __( 'Post Types ','GMW' ),
     *      'prefix'     => pt,
     *      'priority'   => 1
     *   );
     */
    public static function new_form_buttons() {
                      
        $buttons = array();
        $buttons = apply_filters( 'gmw_admin_new_form_button', $buttons );
        
        // order buttons by priority
        usort( $buttons, 'gmw_sort_by_priority' );

        $output  = '<select onchange="window.location.href = jQuery(this).val();">';

        if ( empty( $buttons ) ) {

            $output .= '<option value="">'.__( 'Form buttons are not available', 'GMW' ).'</option>';

        } else { 
            
            $output .= '<option value="">'.__( 'Create new form', 'GMW' ).'</option>';

            // Generate buttons
            foreach ( $buttons as $button ) {

                // support older version of the extensions.
                if ( empty( $button['slug'] ) && ( ! empty( $button['title'] ) && ! empty( $button['name'] ) ) ) {

                    $button['slug'] = $button['name'];
                    $button['name'] = $button['title'];
                }

                $form_url = 'admin.php?page=gmw-forms&gmw_action=create_new_form&name='.str_replace( ' ', '+', $button['name'] ).'&addon='.$button['addon'].'&prefix='.$button['prefix'].'&slug='.$button['slug'];

                $output  .= '<option value="'. esc_url( $form_url ).'">'.esc_html( $button['name'] ).'</option>';
            }
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

        //get forms
        $forms = GMW_Forms_Helper::get_forms();
        ?>
        <div class="wrap">
           <h2 class="gmw-wrap-top-h2">
                <i class="gmw-icon-doc-text-inv"></i>
                <?php echo _e( 'GEO my WP Forms', 'GMW' ); ?> 
                <?php echo self::new_form_buttons(); ?>
                <?php gmw_admin_helpful_buttons(); ?>
            </h2>
                
            <form id="gmw_forms_admin" enctype="multipart/form-data" method="post">
                <input type="hidden" name="gmw_page" id="gmw_page" value="gmw-forms">
                
                <?php wp_nonce_field( 'gmw_forms_page', 'gmw_forms_page' ); ?>
                
                <div class="clear"></div>
                
                <table class="widefat" style="margin-top: 10px">
                    
                    <!-- bulk actions -->
                    <div id="" class="tablenav top">

                        <div class="alignleft actions">

                            <?php if ( ! empty( $forms ) ) { ?>
                                
                                <!-- bulk actions -->
                                <select id="" class="" name="bulk_action">
                                    <option value=""><?php _e( 'Bulk Actions', 'GMW' ); ?></option>
                                    <option value="delete"><?php _e( 'Delete', 'GMW' ); ?></option>
                                </select>

                                <?php $delete_messages = __( 'This action cannot be undone. Would you like to proceed?', 'GMW' ) ; ?>

                                <input type="submit" name="submit" onclick="return confirm( '<?php echo $delete_messages; ?>' );" value="<?php _e( 'Apply', 'GMW' ); ?>" class="button-secondary">
                            
                            <?php } ?>
                        </div>
                    </div>

                    <thead>
                        <tr>
                            <th class="check-column"  style="width:2%;padding: 15px 3px 15px;">
                                <input type="checkbox" id="" class="gmw-forms-select-all" title="gmw-forms-bulk-action">
                            </th>
                            <th scope="col" id="id" class="manage-column"   style="width:3%;"><?php _e( 'ID', 'GMW' ); ?></th>
                            <th scope="col" id="title" class="manage-column"  style="width:25%;"><?php _e( 'Form Title', 'GMW' ); ?></th>
                            <th scope="col" id="type" class="manage-column"  style="width:25%;"><?php _e( 'Form Type', 'GMW' ); ?></th>
                            <th scope="col" id="extension" class="manage-column"  style="width:25%;"><?php _e( 'Extension', 'GMW' ); ?></th> 
                            <th scope="col" id="shortcode" class="manage-column" style="width:20%;"><?php _e( 'Shortcode', 'GMW' ); ?></th> 
                        </tr>
                    </thead>

                    <!-- body -->
                    <tbody class="list:user user-list">

                        <?php $alternate = ''; ?>

                        <?php if ( ! empty( $forms ) ) : ?>

                            <?php $rowNumber = 0; ?>

    			            <?php foreach ( $forms as $option ) : ?>

    			                <?php if ( ! empty( $option['addon'] ) ) : ?>
    			
    			                    <?php $alternate = ( $rowNumber % 2 == 0 ) ? 'alternate' : ''; ?>
    								
    								<?php $formName = ( ! empty( $option['title'] ) ) ? $option['title'] : 'form_id_'.$option['ID']; ?>
    								
                                    <tr class="<?php echo $alternate; ?>" style="height:50px;">
                                        <th scope="row" class="check-column">
                                            <input type="checkbox" id="" name="form_ids[]" value="<?php echo $option['ID']; ?>" class="gmw-forms-bulk-action">
                                        </th>
                                        <td>
                                            <span><?php echo esc_attr( $option['ID'] ); ?></span>
                                        </td>
                                        <td>
                                            <span>
                                                <?php if ( gmw_is_addon_active( $option['addon'] ) ) { ?>
                                                    
                                                    <strong><a class="row-title" title="<?php _e( 'Edit this form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=edit_form&form_id='.$option['ID'].'&prefix='.$option['prefix'] ); ?>"><?php echo esc_html( $formName ); ?></a></strong>
                                                    
                                                    <div class="row-actions">
                                                                                                        
                                                        <span class="edit">
                                                            <a title="<?php _e( 'Edit form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=edit_form&form_id='.$option['ID'].'&prefix='.$option['prefix'] ); ?>"><?php _e( 'Edit', 'GMW' ); ?></a> | 
                                                        </span>
                                                        <span class="duplicate">
                                                            <a title="<?php _e( 'Duplicate form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=duplicate_form&slug='.$option['slug'].'&form_id='.$option['ID'] ); ?>"><?php _e( 'Duplicate', 'GMW' ); ?></a> | 
                                                        </span>
                                                        <span class="delete">

                                                            <?php $delete_message = __( 'This action cannot be undone. Would you like to proceed?', 'GMW' ) ; ?>

                                                            <a title="<?php _e( 'Delete form', 'GMW' ); ?>" href="<?php echo esc_url( 'admin.php?page=gmw-forms&gmw_action=delete_form&form_id='.$option['ID'] ); ?>" onclick="return confirm( '<?php echo $delete_message; ?>' ); "><?php _e( 'Delete', 'GMW' ); ?></a>                                                    
                                                        </span> 
                                                    </div>

                                                <?php } else { ?>
                                                    
                                                    <strong class="row-title"><?php echo esc_attr( $formName ); ?></strong>
                                                    <div class="row-actions">
                                                        <span style="color:#444">
                                                            <?php _e( 'Extension deactivated.', 'GMW' ); ?></em> <a href="<?php echo esc_url( 'admin.php?page=gmw-extensions' ); ?>"><?php _e( 'Manage extensions', 'GMW' ); ?></a>
                                                        </span>
                                                    </div>

                                                <?php } ?>
                                            </span>
                                        </td>
    									<td><span><?php echo esc_attr( $option['name'] ); ?></span></td>
                                        <td><span><?php echo esc_attr( $option['name'] ); ?></span></td>
                                        <td class="column-title" style="padding: 5px 0px;">
                                            <code>[gmw form="<?php echo esc_attr( $option['ID'] ); ?>"]</code>
                                        </td>
                                    </tr>   

                                    <?php $rowNumber++; ?>
    	
                                <?php endif; ?>
    						
    			            <?php endforeach; ?>
    				
    			        <?php else : ?>

                            <tr class="" style="height: 30px;background: #f7f7f7">
                                <td>
                                </td>
                                <td>
                                </td>
                                <td>
                                    <span><?php _e( 'No forms found.', 'GMW' ); ?></span>
                                </td>
                                <td class="column-title" style="padding: 5px 0px;"></td>
                                <td></td>
                                <td></td>			          
                            </tr>

    			        <?php endif; ?>
    		                
    	            </tbody>

                    <!-- table footer -->
	                <tfoot>
	                    <tr>   
                            <th class="check-column" style="width: 2%;padding: 15px 3px 15px;"><input type="checkbox" id="" class="gmw-forms-select-all" title="gmw-forms-bulk-action"></th>
	                        <th scope="col" id="id" class="manage-column" style="width: 3%;"><?php _e( 'ID', 'GMW' ); ?></th>
                            <th scope="col" id="title" class="manage-column"><?php _e( 'Form Title', 'GMW' ); ?></th>
	                        <th scope="col" id="type" class="manage-column"><?php _e( 'Form Type', 'GMW' ); ?></th>
                            <th scope="col" id="extension" class="manage-column"><?php _e( 'Extension', 'GMW' ); ?></th>  
                            <th scope="col" id="shortcode" class="manage-column"><?php _e( 'Shortcode', 'GMW' ); ?></th> 
	                    </tr>
	                </tfoot> 

    	        </table>	 
            </form>
         </div> 
        <?php
    }
}