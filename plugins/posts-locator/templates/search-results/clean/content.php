<?php
/**
 * Posts locator "clean" search results template file. 
 * 
 * This file outputs the search results.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overridden on the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "clean" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site 
 * and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to select 
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: clean".
 *
 * @param $gmw  ( array ) the form being used
 * @param $gmw_form ( object ) the form object
 * @param $post ( object ) post object in the loop
 * 
 */
?>
<div class="gmw-results-wrapper clean <?php echo $gmw['ID']; ?> <?php echo $gmw['prefix']; ?>">
	
	 <?php if ( $gmw_form->has_locations() ) : ?>

        <div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>
			
			<div class="gmw-results-count">
				<span><?php gmw_results_message( $gmw, false ); ?></span>
			</div>
			
			<?php do_action( 'gmw_search_results_before_top_pagination', $gmw ); ?>
			
			<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
	
				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
				
				<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
			
			</div> 
				
		    <?php gmw_results_map( $gmw ); ?>
			
			<div class="clear"></div>
			
			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>
			
			<div class="gmw-posts-wrapper">
				
				<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
					
					<?php global $post; ?>

					<li id="post-<?php the_ID(); ?>" class="<?php echo $post->location_class; ?>">
				
						<?php do_action( 'gmw_search_results_loop_item_start' , $gmw, $post ); ?>
						
						<!-- Title -->
						<div class="wppl-title-holder">

							<h2 class="wppl-h2">
								
								<a href="<?php echo the_permalink(); ?>">
									<?php echo $post->location_count; ?>) <?php the_title(); ?>	
								</a>

								<span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span>
							</h2>

						</div>
												
						<?php if ( isset( $gmw['search_results']['image']['enabled'] ) && has_post_thumbnail() ) { ?>
							
							<?php do_action( 'gmw_search_results_before_image' , $gmw, $post ); ?>
						
							<div id="wppl-thumb" class="wppl-thumb">
								<?php 
									the_post_thumbnail( array( 
										$gmw['search_results']['image']['width'], 
										$gmw['search_results']['image']['height'] 
									) ); 
								?>
							</div>

						<?php } ?>
						
						<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
							
							<?php do_action( 'gmw_search_results_before_excerpt' , $gmw, $post ); ?>
							
							<div class="wppl-excerpt">
								<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
							</div>
						<?php } ?>
						
						<?php do_action( 'gmw_search_results_before_taxonomies' , $gmw, $post ); ?>
						
						<!--  taxonomies -->
						<div id="wppl-taxes-wrapper" class="wppl-taxes-wrapper">
							<?php gmw_taxonomies_list( $post, $gmw ); ?>
						</div>
							
				    	<div class="clear"></div>
				    	
				    	<div class="wppl-info">
				    		
				    		<div class="wppl-info-left">
					    		
					    		<?php if ( ! empty( $post->location_meta ) ) { ?>
    	
							    	<?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>
								   	
							    	<?php gmw_location_meta_output( $post, $gmw ); ?>

							    <?php } ?>

							    <?php if ( ! empty( $gmw['search_results']['opening_hours'] ) ) { ?>
		    
							    	<?php do_action( 'gmw_search_results_before_opening_hours', $post, $gmw ); ?>
								   	
							    	<div class="opening-hours">
							    		<?php gmw_opening_hours( $post, $gmw ); ?>
							    	</div>

							    <?php } ?>
				    		
				    		</div>
							    
				    		<div class="wppl-info-right">
				    			
				    			<div class="wppl-address">

				    				<div class="address-wrapper">

								    	<span class="gmw-icon-address address-icon"></span>
								    	<span class="wppl-address"><?php gmw_location_address( $post, $gmw ); ?></span>
								    
								    </div>

				    			</div>
				    		
								<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
				    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
				    			<?php } ?>
				    			 	
								<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
									
									<div class="wppl-get-directions">
				    					
				    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
				    				</div>

				    			<?php } ?>
					    		
				    		</div>
				    	
				    	</div>
				    
				    	<?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>
				    	
				    </div> 
				    
				    <div class="clear"></div>     
				  	
				<?php endwhile; ?><!--  end of the loop -->
				
			</div> <!--  results wrapper -->    
			
			<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>
				
			<?php do_action( 'gmw_search_results_before_bottom_pagination', $gmw ); ?>
			

			<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
				
				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
				<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>

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