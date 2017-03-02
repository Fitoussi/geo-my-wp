<?php 
/**
 * Members locator "Kleo" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "Kleo" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: Kleo".
 */
?>
<!--  Main results wrapper -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-kleo-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
	
	<div id="pag-top" class="pagination">

		<!-- per page -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>

		<div class="pag-count" id="member-dir-count-top">
			
			<p><?php bp_members_pagination_count(); ?></p>

		</div>
	
	</div>
	
	<!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>

    <?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>

    <ul id="members-list" class="item-list row kleo-isotope masonry">

    	<?php do_action( 'gmw_search_results_before_loop' , $gmw ); ?>

    	<!-- members loop -->
    	<?php while ( bp_members() ) : bp_the_member(); ?>

    		<!-- do not remove this line -->
    		<?php $member = $members_template->member; ?>
       	         
   	        <li class="kleo-masonry-item">
	
				<div class="member-inner-list animated animate-when-almost-visible bottom-to-top single-member">	

        			<!-- do not remove this line -->
            		<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>
					
					<?php do_action( 'gmw_search_results_before_avatar', $gmw, $member); ?>
	                
	                <?php if ( isset( $gmw['search_results']['avatar']['use'] ) ) { ?>
	                    
	                    <div class="item-avatar rounded">
	                        
	                        <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar( array( 'type' => 'full', 'width' => $gmw['search_results']['avatar']['width'], 'height' => $gmw['search_results']['avatar']['height'] ) ); ?></a>

	                        <?php do_action('bp_member_online_status', bp_get_member_user_id()); ?>

	                    </div>

	        		<?php } ?>

	        		<div class="item">
			          
			          	<?php do_action( 'gmw_search_results_before_title', $gmw, $member); ?>

				        <div class="item-title">

				            <a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
				          
				        </div>
			          
			          	<div class="item-meta">
			          		
			          		<span class="activity">
			          			
			          			<?php bp_member_last_active(); ?>
			          		
			          		</span>

			          	</div>
			          
			          	<?php if ( bp_get_member_latest_update() ) { ?>
			            
			            	<span class="update"> <?php bp_member_latest_update(); ?></span>
			          	
			          	<?php } else { ?>

			          		<span class="update"></span>

			          	<?php } ?>
			  
			          	<?php do_action( 'bp_directory_members_item' ); ?>
			  			
			  			<div class="gmw-location-wrapper">
			  				    
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

			    		  	<?php if ( array_filter( $_GET['gmw_address'] ) ) { ?>
				    		  	
				    		  	<span class="radius">
		                			( <?php gmw_distance_to_location( $members_template->member, $gmw ); ?> )
		                		</span>

		                	<?php } ?>

				    		<!--  Driving Distance -->
							<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				
		    					<?php gmw_driving_distance( $member, $gmw, false ); ?>
		    			
		    				<?php } ?>

		    			</div>

				        <?php
				           /***
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

				</div><!--end member-inner-list-->

				<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>

			</li>

		<?php endwhile; ?>

    </ul>
		
    <?php do_action( 'bp_after_directory_members_list' ); ?>

    <?php bp_member_hidden_fields(); ?>

    <div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">

			<p><?php bp_members_pagination_count(); ?></p>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>

		</div>

	</div>

</div>