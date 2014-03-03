<div class="wppl-shortcodes-list" style="margin:0;">	
<?php
	if ( isset($_GET['gmw_action']) && $_GET['gmw_action'] == 'new' ) :
		$options_r[$_GET['gmw_form_id']] = array(
				'form_id' 				=> $_GET['gmw_form_id'],
				'form_type' 			=> $_GET['gmw_form_type'],
				'results_type' 			=> 'both',
				'map_icon_usage' 		=> 'same',
				'auto_zoom' 			=> 1,
				'map_icon'				=> '_default.png',
				'your_location_icon' 	=> 'blue-dot.png',
				'locator_icon' 	     	=> array('icon' => 'blue-dot.png'),
				'address_fields' 	    => array('how' => 'single'),
				'keywords_field'		=> 'dont'
			);
	
		foreach ($options_r as $key => $option) : 
			if ( !isset($option['form_id'] ) || empty($option['form_id'] ) ||  !is_numeric($option['form_id']) || !isset($option['form_type'] ) || empty($option['form_type'] ) ) : 
				unset( $options_r[$key] );
			endif;
		endforeach;
		
		update_option( 'wppl_shortcode', $options_r ); 
	endif;
	
	if ( isset($_GET['gmw_action']) && $_GET['gmw_action'] == 'duplicate' ) :
		$newShortcode = $options_r[$_GET['shortcodeID']];
		$newShortcode['form_id'] = $_GET['gmw_form_id'];
		array_push ( $options_r , $newShortcode );
		
		foreach ($options_r as $key => $option) :
			if ( !isset($option['form_id'] ) || empty($option['form_id'] ) ||  !is_numeric($option['form_id']) || !isset($option['form_type'] ) || empty($option['form_type'] ) ) :
				unset( $options_r[$key] );
			endif;
		endforeach;
		
		update_option( 'wppl_shortcode', $options_r );
	endif;
	
	if ( isset($_GET['gmw_action']) && $_GET['gmw_action'] == 'delete' ) :
		unset( $options_r[$_GET['shortcodeID']] );
		update_option( 'wppl_shortcode', $options_r );
	endif;
	
	$next_id = 1;
	
	if ( !empty($options_r) ) $sct = end($options_r); else $sct = 0;
	$next_id = $sct['form_id'] + 1;
?>
		<table class="widefat fixed">
            <thead>
            	<tr>
            		<th>
            			<h3 style="display: inline;color: #555;"><?php _e('Create new Search form','GMW'); ?></h3>
            			<span style="padding:5px;">
            				<?php if ( isset( $wppl_on['post_types'] ) && $wppl_on['post_types'] == 1) { ?>
        				<span style="padding: 4px 5px;border-radius: 3px;border: 1px solid #CCC;background:#fafafa;"><a title="Create new post types shortcode" href="admin.php?page=wppl-shortcodes&gmw_action=new&gmw_form_type=posts&gmw_form_id=<?php echo $next_id; ?>"><?php _e('Post Types','GMW'); ?></a></span>                                          
			    			<?php } ?>
			    			<?php if ( isset( $wppl_on['friends'] ) && $wppl_on['friends'] == 1) { ?>
			    		<span style="padding: 4px 5px;border-radius: 3px;border: 1px solid #CCC;background:#fafafa;"><a title="Create new post types shortcode" href="admin.php?page=wppl-shortcodes&gmw_action=new&gmw_form_type=friends&gmw_form_id=<?php echo $next_id; ?>"><?php _e('Buddypress Members','GMW'); ?></a></span>  
			    			<?php } ?>
			    		<?php do_action('gmw_shortcode_page_add_new_shortcode_button', $next_id, $wppl_on); ?>
						</span>
					</th>
                </tr>
           	</thead>
        </table>
        <br />
		<table class="widefat fixed">
            <thead>
            	<tr>
            		<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:8px"></th>
                 	<th scope="col" id="cb" class="manage-column column-cb check-column" style="width:5px"><?php _e('ID','GMW'); ?></th>
                    <th scope="col" id="active" class="manage-column column-cb check-column"><?php _e('Shortcode','GMW'); ?></th>
                    <th scope="col" id="active" class="manage-column column-cb check-column"><?php _e('Action','GMW'); ?></th>
                    <th scope="col" id="id" class="manage-column" style="width:50px;"><?php _e('Type','GMW'); ?></th>
                </tr>
           	</thead>
           	<tbody class="list:user user-list">
	           	<?php if (!empty($options_r)) : ?>
	           		<?php foreach ($options_r as $key => $option) : ?>
	           			<?php if ( isset($option['form_type'] ) && !empty($option['form_type'] ) ) : ?>
						<tr>
							<td style="padding: 7px 15px;">
								<?php if ($option['form_type'] == 'posts') { ?>
									<img src="<?php echo plugins_url('/geo-my-wp/admin/images/wp-icon.png'); ?>" width="40px" height="40px" style="float:left;" />
								<?php } elseif ($option['form_type'] == 'friends') { ?>
									<img src="<?php echo plugins_url('/geo-my-wp/admin/images/bp-members-icon.png'); ?>" width="40px" height="40px" style="float:left;" />
								<?php } elseif ($option['form_type'] == 'groups') { ?>
									<img src="<?php echo plugins_url('/geo-my-wp/admin/images/bp-groups-icon.png'); ?>" width="40px" height="40px" style="float:left;" />
								<?php }?>
								
								<?php do_action('gmw_add_image_to_shortcode', $option); ?>
							</td>
	                		<td>
	                			<span><?php echo $option['form_id']; ?></span>
	                		</td>
	                		
	                		<td class="column-title" style="padding: 5px 0px;">
	                        	<code>[gmw form="<?php echo $option['form_id']; ?>"]</code>
		                    </td>
		                    <td>		
	                            <span class="edit">
	                            	<a title="Edit this form" href="admin.php?page=wppl-shortcodes&gmw_action=edit&form_type=<?php echo $option['form_type']; ?>&shortcodeID=<?php echo $option['form_id']; ?>"><?php _e('Edit','GMW'); ?></a> | 
	                            </span>
	                            <span class="edit">
	                            	<a title="Edit this form" href="admin.php?page=wppl-shortcodes&gmw_action=duplicate&form_type=<?php echo $option['form_type']; ?>&shortcodeID=<?php echo $option['form_id']; ?>&gmw_form_id=<?php echo $next_id; ?>"><?php _e('Duplicate','GMW'); ?></a> | 
	                            </span>
	                            <span class="edit">
	                            	<a title="Edit notifications sent by this form" href="admin.php?page=wppl-shortcodes&gmw_action=delete&shortcodeID=<?php echo $option['form_id']; ?>"><?php _e('Delete','GMW'); ?></a>                                                    
	                            </span>	
		                    </td>
		                    <td>
	                       		<?php if ($option['form_type'] == 'posts') { ?>
									<span><?php _e('Post types search form','GMW'); ?></span>
								<?php } elseif ($option['form_type'] == 'friends') { ?>
									<span><?php _e('BP Friends search form','GMW'); ?></span>
								<?php } elseif ($option['form_type'] == 'groups') { ?>
									<span><?php _e('BP Groups search form','GMW'); ?></span>
								<?php }?>
								<?php do_action('gmw_add_title_to_shortcode', $option); ?>
							</td>
	                	</tr>
	                <?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
        <tfoot>
        	<tr>
                <th scope="col" id="cb" class="manage-column column-cb check-column" style="width:8px"></th>
              	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><?php _e('ID','GMW'); ?></th>
                <th scope="col" id="active" class="manage-column column-cb check-column"><?php _e('Shortcode','GMW'); ?></th>
                <th scope="col" id="id" class="manage-column"><?php _e('Action','GMW'); ?></th>
                <th scope="col" id="id" class="manage-column"><?php _e('Type','GMW'); ?></th>
        	</tr>
    	</tfoot>        
	</table>	
</div>
<?php wp_enqueue_script('wppl-admin'); ?>
    