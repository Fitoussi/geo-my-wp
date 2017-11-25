<?php 
/**
 * Members locator "grid-gray" search results template file. 
 * 
 * This file outputs the search results.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overridden on the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "grid-gray" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site 
 * and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to select 
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: grid-gray".
 *
 * @param $gmw ( array ) the form being used
 * @param $members_template ( object ) buddypress members object
 * @param $members_template->member ( object ) each member in the loop
 * 
 */
?>
<?php global $members_template; ?>

<!--  Main results wrapper -->
<div class="gmw-results-wrapper grid-gray gmw-fl-grid-gray-results-wrapper <?php echo $gmw['ID']; ?> <?php echo $gmw['prefix']; ?>">
	
	<?php if ( $gmw_form->has_locations() ) : ?>
		
		<div class="gmw-results">

			<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
			
			<div class="results-count-wrapper">
				<p>
					<?php bp_members_pagination_count(); ?>
					<?php gmw_results_message( $gmw, false ); ?>	
				</p>
			</div>
				        		
			<div class="pagination-per-page-wrapper top">
				
				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
			
				<div class="pagination-wrapper">
					<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
				</div>

			</div>
			
			<?php gmw_results_map( $gmw ); ?>

		    <?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>

		    <ul class="members-list-wrapper">

		    	<?php while ( bp_members() ) : bp_the_member(); ?>
		       	            		
			        <li class="single-member">
			        		
			        		<!-- do not remove this line -->
		                <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $members_template->member ); ?>
						
						<div class="wrapper-inner">
						
							<div class="top-wrapper">	
							
								<?php do_action( 'gmw_search_results_before_title', $gmw, $members_template->member ); ?>
								
								<h2 class="user-name-wrapper">
			                		<span class="member-count"><?php echo $members_template->member->location_count; ?>)</span>
			                    	<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
			                    </h2>   
			                         	
			                    <span class="radius">
		                    		<?php gmw_distance_to_location( $members_template->member, $gmw ); ?>
		                    	</span>
			                </div>
			                    
			                <div class="user-info">
				                
				                <?php do_action( 'gmw_search_results_before_avatar', $gmw, $members_template->member ); ?>
				                
				                <?php if ( isset( $gmw['search_results']['image']['enabled'] ) ) { ?>
				                    
				                    <div class="user-avatar">
				                        
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
			        				                    
			                    <span class="activity">
			                    	<?php bp_member_last_active(); ?>
			                    </span>
				        		
				        		<?php do_action( 'bp_directory_members_actions' ); ?>
				
				                <?php if ( bp_get_member_latest_update() ) : ?>

				                	<div class="update"><?php bp_member_latest_update(); ?></div>
				        		
				        		<?php endif; ?>
			                    		                    			       
			               		<?php do_action( 'bp_directory_members_item' ); ?>
					                             		
							</div>
							
							<div class="bottom-wrapper">
							
								<?php do_action( 'gmw_search_results_before_get_directions', $gmw, $members_template->member ); ?>
								
				   				<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
					    			
				    				<?php if ( ! empty( $members_template->member->address ) ) { ?>

				    					<?php gmw_directions_link( $members_template->member, $gmw, $members_template->member->address ); ?>				
				    				<?php } ?>
					    			
					    		<?php } else { ?>

					    			<i class="get-directions-icon gmw-icon-location"></i>
					    			
					    			<div class="address-wrapper">
					                    <?php gmw_location_address( $members_template->member, $gmw ); ?>
					    			</div>

				    		  	<?php  } ?>
				    			
								 <!--  Driving Distance -->
								<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>

				    				<?php gmw_driving_distance( $members_template->member, $gmw, false ); ?>
				    			
				    			<?php } ?>
						                
				    		</div>
				    							
						</div>
								                            
			        	<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $members_template->member ); ?>
			        		
					</li>

		    	<?php endwhile; ?>

		    </ul>
			
			<?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>
			
		    <?php do_action( 'bp_after_directory_members_list' ); ?>

		    <?php bp_member_hidden_fields(); ?>

			<div class="pagination-per-page-wrapper bottom">	

				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>	

				<div class="pagination-wrapper">
					<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
				</div>

			</div>

		</div>

	<?php else : ?>

		<div class="gmw-no-results">
			
			<?php do_action( 'gmw_no_results_start', $gmw ); ?>

			<p><?php echo esc_attr( $gmw['no_results_message'] ); ?></p>
			
			<?php do_action( 'gmw_no_results_end', $gmw ); ?> 

		</div>

	<?php endif; ?>

</div>