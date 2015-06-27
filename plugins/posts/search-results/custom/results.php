<?php
/**
 * Custom - Results Page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="gmw-results-count">
		<span><?php gmw_results_message( $gmw, false ); ?></span>
	</div>
	
	<?php do_action( 'gmw_before_top_pagination' , $gmw, $post ); ?>
	
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
				<h2>
					<a href="<?php echo the_permalink(); ?>"><?php echo $post->post_count; ?>) <?php the_title(); ?></a>
					<?php if ( isset( $gmw['your_lat'] ) && !empty( $gmw['your_lat'] ) ) { ?><span class="radius-dis">(<?php gmw_distance_to_location( $post, $gmw ); ?>)</span><?php } ?>
				</h2>
				
				<?php do_action( 'gmw_posts_loop_after_title' , $gmw, $post ); ?>
				
				<?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && has_post_thumbnail() ) { ?>
					<div class="post-thumbnail">
						<?php the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) ); ?>
					</div>
				<?php } ?>
			
				<!--  Excerpt -->
				<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
					<div class="excerpt">
						<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
					</div>
				<?php } ?>
				
				<?php do_action( 'gmw_posts_loop_after_content' , $gmw, $post ); ?>
				
				<!--  taxonomies -->
				<div>
					<?php gmw_pt_taxonomies( $gmw, $post ); ?>
				</div>
				
		    	<div class="clear"></div>
		    	
		    	<div class="wppl-info">
	    			
	    			<!--  Addiotional info -->
					<div>	
	    				<?php gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'div' ); ?> 
	    			</div>
	    		
	    			<?php if ( !empty( $gmw['search_results']['opening_hours'] ) ) { ?>
    
				    	<?php do_action( 'gmw_search_results_before_opening_hours', $post, $gmw ); ?>
					   	
				    	<div class="opening-hours">
				    		<?php gmw_pt_days_hours( $post, $gmw ); ?>
				    	</div>
				    <?php } ?>
					    
	    			<!--  Address -->
	    			<div class="wppl-address">
	    				<?php echo $post->address; ?>
	    			</div>
	    		
	    			<!--  Driving Distance -->
					<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
	    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
	    			<?php } ?>
	    			
	    			<!-- Get directions -->	 	
					<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
						<div class="get-directions-link">
	    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
	    				</div>
	    			<?php } ?>
		    	</div> <!-- info -->
		    	
		    	<?php do_action( 'gmw_posts_loop_post_end' , $gmw, $post ); ?>
		    	
		    </div> <!--  single- wrapper ends -->
		    
		    <div class="clear"></div>     
	
		<?php endwhile; ?>
		<!--  end of the loop -->
	
	</div> <!--  results wrapper -->    
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
	
	<!--  Pagination -->
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
</div> <!-- output wrapper -->
