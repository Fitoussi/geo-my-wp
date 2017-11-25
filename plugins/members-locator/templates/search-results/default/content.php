<?php
/**
 * Members locator "default" search results template file. 
 * 
 * This file outputs the search results.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overridden on the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "default" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site 
 * and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to select 
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: default".
 *
 * @param $gmw ( array ) the form being used
 * @param $members_template ( object ) buddypress members object
 * @param $members_template->member ( object ) each member in the loop
 * 
 */
?>
<?php global $members_template; ?>

<div class="gmw-results-wrapper default gmw-fl-default-results-wrapper <?php echo $gmw['ID']; ?> <?php echo $gmw['prefix']; ?>">

    <?php if ( $gmw_form->has_locations() ) : ?>

        <div class="gmw-results">

        	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
        	
            <div id="pag-top" class="pagination">

                <div class="pag-count" id="member-dir-count-top">
                    <p>
                        <?php bp_members_pagination_count(); ?>
                        <?php gmw_results_message( $gmw, false ); ?>        
                    </p>
                </div>

                <div class="clear"></div>
        		
        		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
        		
                <div class="pagination-links" id="member-dir-pag-top">
            		<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
                </div>

            </div>

            <div class="clear"></div>

            <?php gmw_results_map( $gmw ); ?>
            
            <?php do_action( 'bp_before_directory_members_list' ); ?>
        	    
            <ul id="members-list" class="item-list" role="main">

            	<?php while ( bp_members() ) : bp_the_member(); ?>

                    <li>         
                        <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $members_template->member ); ?>

                        <div class="item">
                        
                            <div class="item-title">
                            	
                                <div class="gmw-fl-member-count">
                                    <?php echo $members_template->member->location_count; ?>)
                                </div>
                                
                                <a href="<?php bp_member_permalink(); ?>">
                                    <?php bp_member_name(); ?>    
                                </a>
                            		
                            	<?php do_action( 'gmw_search_results_before_distance', $gmw, $members_template->member ); ?>
                            	
                            	<?php gmw_distance_to_location( $members_template->member, $gmw ); ?>

               					<?php if ( bp_get_member_latest_update() ) { ?>
                                    <span class="update"> <?php bp_member_latest_update(); ?></span>
                				<?php }; ?>

                            </div>

                             <?php if ( isset( $gmw['search_results']['image']['enabled'] ) ) { ?>
                            
                                <div class="item-avatar">
                                    
                                    <a href="<?php bp_member_permalink(); ?>">
                                        
                                        <?php 
                                            bp_member_avatar( array( 
                                                'type'   => 'full', 
                                                'width'  => $gmw['search_results']['image']['width'], 
                                                'height' => $gmw['search_results']['image']['height'] 
                                            ) ); 
                                        ?>
                                    
                                    </a>
                                
                                </div>
                            
                            <?php } ?>

                            <div class="item-meta">
                            	<span class="activity">
                            		<?php bp_member_last_active(); ?>
                            	</span>
                            </div>

                			<?php do_action( 'bp_directory_members_item' ); ?>
                			<?php do_action( 'gmw_fl_search_results_member_items', $gmw, $members_template->member ); ?>

                            <?php
                            /*                     * *
                             * If you want to show specific profile fields here you can,
                             * but it'll add an extra query for each member in the loop
                             * (only one regardless of the number of fields you show):
                             *
                             * bp_member_profile_data( 'field=the field name' );
                             */
                            ?>
                        </div>

                        <div class="action">
                			<?php do_action( 'bp_directory_members_actions' ); ?>
                        </div>

                        <div class="clear"></div>

                        <?php do_action( 'gmw_search_results_before_address', $gmw, $members_template->member ); ?>

                        <div>
        	                <span>
                                <?php echo $gmw['labels']['search_results']['address'] ?>        
                            </span>
                        	<?php gmw_location_address( $members_template->member, $gmw ); ?>
                        </div>
                        	
        				<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
        					
                            <?php global $members_template; ?>
        					
                            <div class="get-directions-link">
            					
                                <?php gmw_directions_link( $members_template->member, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
            				</div>

            			<?php } ?>
                        
        				<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
            				
                            <?php gmw_driving_distance( $members_template->member, $gmw, false ); ?>
            			
                        <?php } ?>

                		<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $members_template->member ); ?>

                    </li>

            	<?php endwhile; ?>
        	
            </ul>

            <?php do_action( 'bp_after_directory_members_list' ); ?>

            <?php bp_member_hidden_fields(); ?>
        	    
            <div id="pag-bottom" class="pagination">

                <div class="pag-count" id="member-dir-count-top">
                    <p><?php bp_members_pagination_count(); ?><?php gmw_results_message( $gmw, false ); ?></p>
                </div>
            		
                <div class="clear"></div>
        		
        		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
        		
                <div class="pagination-links" id="member-dir-pag-top">
            		<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
                </div>

            </div>
        	
        	<?php do_action( 'gmw_search_results_end', $gmw ); ?>	

        </div>

    <?php else : ?>

        <div class="gmw-no-results">
            
            <?php do_action( 'gmw_no_results_start', $gmw ); ?>

            <p><?php echo esc_attr( $gmw['no_results_message'] ); ?></p>
            
            <?php do_action( 'gmw_no_results_end', $gmw ); ?> 

        </div>

    <?php endif; ?>

</div>