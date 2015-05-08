<?php 
/**
 * Members locator "grid-gray" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "grid-gray" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: grid-gray".
 */
?>
<!--  Main results wrapper -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-grid-gray-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
	
	<div class="results-count-wrapper">
		<p><?php bp_members_pagination_count(); ?><?php gmw_results_message( $gmw, false ); ?></p>
	</div>
		        		
	<div class="pagination-per-page-wrapper top">
		<!-- per page -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
	
		<!-- pagination -->
		<div class="pagination-wrapper">
			<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
		</div>
	</div>
	
	 <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>

    <?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>

    <ul class="members-list-wrapper">

    	<!-- members loop -->
    	<?php while ( bp_members() ) : bp_the_member(); ?>

    		<!-- do not remove this line -->
    		<?php $member = $members_template->member; ?>
       	            		
	            <li class="single-member">
	        		
	        		<!-- do not remove this line -->
                <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>
				
				<div class="wrapper-inner">
				
					<div class="top-wrapper">	
					
						<?php do_action( 'gmw_search_results_before_title', $gmw, $member); ?>
						
						<h2 class="user-name-wrapper">
	                		<span class="member-count"><?php echo $member->member_count; ?>)</span>
	                    	<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
	                    </h2>   
	                         	
	                    <span class="radius">
                    		<?php gmw_distance_to_location( $members_template->member, $gmw ); ?>
                    	</span>
	                </div>
	                    
	                <div class="user-info">
		                
		                <?php do_action( 'gmw_search_results_before_avatar', $gmw, $member); ?>
		                
		                <?php if ( isset( $gmw['search_results']['avatar']['use'] ) ) { ?>
		                    <div class="user-avatar">
		                        <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar( array( 'type' => 'full', 'width' => $gmw['search_results']['avatar']['width'], 'height' => $gmw['search_results']['avatar']['height'] ) ); ?></a>
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
					
						<?php do_action( 'gmw_search_results_before_get_directions', $gmw, $member); ?>
						
						<!-- Get directions -->	
		   				<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
			    			<i class="get-directions-icon fa fa-map-marker"></i>
		    				<?php gmw_directions_link( $members_template->member, $gmw, $members_template->member->address ); ?>
			    			
			    		<?php } else { ?>
			    			<div class="address-wrapper">
			                    <?php gmw_location_address( $members_template->member, $gmw ); ?>
			    			</div>
		    		  	<?php  } ?>
		    			
						 <!--  Driving Distance -->
						<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				<?php gmw_driving_distance( $member, $gmw, false ); ?>
		    			<?php } ?>
				                
		    		</div>
		    							
				</div>
						                            
	        	<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>
	        		
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