<?php 
/**
 * Members locator "purple" search results template file. 
 * 
 * This file outputs the search results.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * since your changes will be overridden on the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "purple" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site 
 * and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to select 
 * it in the form editor. It will show in the "Search results" dropdown menu as "Custom: purple".
 *
 * @param $gmw ( array ) the form being used
 * @param $members_template ( object ) buddypress members object
 * @param $members_template->member ( object ) each member in the loop
 * 
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper purple gmw-pt-purple-results-wrapper <?php echo $gmw['ID']; ?> <?php echo $gmw['prefix']; ?>">
	
	<?php if ( $gmw_form->has_locations() ) : ?>

        <div class="gmw-results">
	
			<?php do_action( 'gmw_search_results_start', $gmw ); ?>
			
			<!-- results count -->
			<div class="results-count-wrapper">
				<p><?php gmw_results_message( $gmw, false ); ?></p>
			</div>
			
			<?php do_action( 'gmw_search_results_before_top_pagination', $gmw ); ?>
			
			<div class="pagination-per-page-wrapper top">
				<!--  paginations -->
				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
				<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>

			</div> 
			
			 <!-- GEO my WP Map -->
		    <?php gmw_results_map( $gmw ); ?>
				
			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>
			
			<!--  Results wrapper -->
			<ul class="posts-list-wrapper">

				<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
				
					<?php global $post; ?>

					<li id="post-<?php the_ID(); ?>" class="<?php echo $post->location_class; ?>">
						
						<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $post ); ?>
					
						<!-- Title -->
						<div class="top-wrapper">

							<h2 class="post-title">
								<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
									<?php the_title(); ?> 
								</a>
							</h2>

							<span class="radius"><?php gmw_distance_to_location( $post, $gmw ); ?></span>
							
							<div class="address-wrapper">
						    	<span class="fa fa-map-marker address-icon"></span>
						    	<span class="address"><?php gmw_location_address( $post, $gmw ); ?></span>
						    </div>
							
						</div>
						
						<?php do_action( 'gmw_posts_loop_before_content', $gmw, $post ); ?>
						
						<div class="post-content">
							
							<div class="left-col">
								
								<?php if ( isset( $gmw['search_results']['image']['enabled'] ) && has_post_thumbnail() ) { ?>
									
									<?php do_action( 'gmw_posts_loop_before_image', $gmw, $post ); ?>
									
									<div class="post-thumbnail">
										<?php 
											the_post_thumbnail( array( 
												$gmw['search_results']['image']['width'], 
												$gmw['search_results']['image']['height'] 
											) ); 
										?>
									</div>

								<?php } ?>
						
								<!--  Excerpt -->
								<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
								
									<?php do_action( 'gmw_posts_loop_before_excerpt', $gmw, $post ); ?>
									
									<div class="excerpt">
										<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
									</div>
								<?php } ?>
						
								<?php gmw_taxonomies_list( $post, $gmw ); ?>
							</div>
							
							<div class="right-col">

								<?php if ( ! empty( $post->location_meta ) ) { ?>
		    	
							    	<?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>
								   	
								   	<div class="contact-info">
							    		<?php gmw_location_meta_output( $post, $gmw ); ?>
							    	</div>

							    <?php } ?>

							    <?php if ( ! empty( $gmw['search_results']['opening_hours'] ) ) { ?>
		    
							    	<?php do_action( 'gmw_search_results_before_opening_hours', $post, $gmw ); ?>
								   	
							    	<div class="opening-hours">
							    		<?php gmw_opening_hours( $post, $gmw ); ?>
							    	</div>

							    <?php } ?>
						    
				   			</div>
				   			
			   			</div>
			   			
						<!-- Get directions -->	 	
						<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
							
							<?php do_action( 'gmw_posts_loop_before_get_directions', $gmw, $post ); ?>
							
							<div class="get-directions-link">
		    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
		    				</div>

		    			<?php } ?>
		    			
						<!--  Driving Distance -->
						<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
		    			<?php } ?>
		    			
		    			<?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>
						
					</li><!-- #post -->
				
				<?php endwhile;	 ?>
			</ul>
			
			<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
			
			<div class="pagination-per-page-wrapper bottom">
				<!--  paginations -->
				<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
				<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>

			</div> 
			
			<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
			
		</div>

	<!-- no results -->
	<?php else : ?>

		<div class="gmw-no-results">
			
			<?php do_action( 'gmw_no_results_start', $gmw ); ?>

			<p><?php echo esc_attr( $gmw['no_results_message'] ); ?></p>
			
			<?php do_action( 'gmw_no_results_end', $gmw ); ?> 
		</div>

	<?php endif; ?>
	
</div> <!-- output wrapper -->