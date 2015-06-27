<?php
/**
 * Posts locator "clean" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $post - each post in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "clean" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the posts locator form.
 * It will show in the "Search results" dropdown menu as "Custom: clean".
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="gmw-results-count">
		<span><?php gmw_results_message( $gmw, false ); ?></span>
	</div>
	
	<?php do_action( 'gmw_search_results_before_top_pagination' , $gmw, $post ); ?>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
		
	 <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>
	
	<div class="clear"></div>
	
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<div class="gmw-posts-wrapper">
		
		<!--  this is where wp_query loop begins -->
		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
			
			<!--  single results wrapper  -->
			<?php $featured = ( !empty( $post->feature ) ) ? 'gmw-featured-post' : ''; ?>

			<div id="post-<?php the_ID(); ?>" <?php post_class( 'wppl-single-result '.$featured ); ?>>
		
				<?php do_action( 'gmw_search_results_loop_item_start' , $gmw, $post ); ?>
				
				<!-- Title -->
				<div class="wppl-title-holder">
					<h2 class="wppl-h2">
						<a href="<?php echo the_permalink(); ?>"><?php echo $post->post_count; ?>) <?php the_title(); ?></a>
						<span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span>
					</h2>
				</div>
								
				<!--  Thumbnail -->			
				<?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && has_post_thumbnail() ) { ?>
					
					<?php do_action( 'gmw_search_results_before_image' , $gmw, $post ); ?>
				
					<div id="wppl-thumb" class="wppl-thumb">
						<?php the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) ); ?>
					</div>
				<?php } ?>
				
				<!--  Excerpt -->
				<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
					
					<?php do_action( 'gmw_search_results_before_excerpt' , $gmw, $post ); ?>
					
					<div class="wppl-excerpt">
						<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
					</div>
				<?php } ?>
				
				<?php do_action( 'gmw_search_results_before_taxonomies' , $gmw, $post ); ?>
				
				<!--  taxonomies -->
				<div id="wppl-taxes-wrapper" class="wppl-taxes-wrapper">
					<?php gmw_pt_taxonomies( $gmw, $post ); ?>
				</div>
					
		    	<div class="clear"></div>
		    	
		    	<div class="wppl-info">
		    		
		    		<div class="wppl-info-left">
			    		<?php if ( !empty( $gmw['search_results']['additional_info'] ) ) { ?>
    
					    	<?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>
						   	
						   	<div class="contact-info">
								<h3><?php echo $gmw['labels']['search_results']['contact_info']['contact_info']; ?></h3>
					    		<?php gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'ul' ); ?> 
					    	</div>
					    <?php } ?>	    
		    		</div>
		    		
		    		<?php if ( !empty( $gmw['search_results']['opening_hours'] ) ) { ?>
    
				    	<?php do_action( 'gmw_search_results_before_opening_hours', $post, $gmw ); ?>
					   	
				    	<div class="opening-hours">
				    		<?php gmw_pt_days_hours( $post, $gmw ); ?>
				    	</div>
				    <?php } ?>
					    
		    		<div class="wppl-info-right">
		    			
		    			<!--  Address -->
		    			<div class="wppl-address">
		    				<div class="address-wrapper">
						    	<span class="fa fa-map-marker address-icon"></span>
						    	<span class="wppl-address"><?php gmw_location_address( $post, $gmw ); ?></span>
						    </div>
		    			</div>
		    		
		    			<!--  Driving Distance -->
						<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
		    			<?php } ?>
		    			
		    			<!-- Get directions -->	 	
						<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
							<div class="wppl-get-directions">
		    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
		    				</div>
		    			<?php } ?>
			    		
		    		</div><!-- info right end -->
		    	
		    	</div> <!-- info end -->
		    
		    	<?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>
		    	
		    </div> <!--  single- wrapper ends -->
		    
		    <div class="clear"></div>     
		  	
		<?php endwhile; ?>
			
		<!--  end of the loop -->
		
	</div> <!--  results wrapper -->    
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
		
	<?php do_action( 'gmw_search_results_before_bottom_pagination' , $gmw, $post ); ?>
	
	<!--  Pagination -->
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
	
</div> <!-- output wrapper -->