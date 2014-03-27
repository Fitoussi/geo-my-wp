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
		<span><?php gmw_pt_within( $gmw, $sm=__( 'Showing', 'GMW' ), $om=__( 'out of', 'GMW' ), $rm=__( 'results', 'GMW' ) ,$wm=__( 'within', 'GMW' ), $fm=__( 'from','GMW' ), $nm=__( 'your location', 'GMW' ) ); ?></span>
	</div>
	
	<?php do_action( 'gmw_before_top_pagination' , $gmw, $post ); ?>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
		
	<!-- Map -->
	<?php gmw_results_map( $gmw ); ?>
	
	<div class="clear"></div>
	
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<div class="gmw-posts-wrapper">
		
		<!--  this is where wp_query loop begins -->
		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
			
			<!--  single results wrapper  -->
			<div id="post-<?php the_ID(); ?>" <?php post_class('wppl-single-result'); ?>>
				
				<?php do_action( 'gmw_posts_loop_post_start' , $gmw, $post ); ?>
				
				<!-- Title -->
				<h2>
					<a href="<?php echo the_permalink(); ?>"><?php echo $post->post_count; ?>) <?php the_title(); ?></a>
					<?php if ( isset( $gmw['your_lat'] ) && !empty( $gmw['your_lat'] ) ) { ?><span class="radius-dis">(<?php echo gmw_pt_by_radius( $gmw, $post ); ?>)</span><?php } ?>
				</h2>
				
				<div>
					<?php gmw_pt_thumbnail( $gmw, $post ); ?>
				</div>
			
				<!--  Excerpt -->
    			<div>
				 	<?php gmw_pt_excerpt( $gmw, $post ); ?> 
				 </div>
				
				<!--  taxonomies -->
				<div>
					<?php gmw_pt_taxonomies( $gmw, $post ); ?>
				</div>
				
		    	<div class="clear"></div>
		    	
		    	<div class="wppl-info">
	    			
	    			<!--  Addiotional info -->
					<div>	
	    				<?php gmw_pt_additional_info( $gmw, $post, $tag='div' ); ?>
	    			</div>
	    		
	    			<!--  Address -->
	    			<div class="wppl-address">
	    				<?php echo $post->address; ?>
	    			</div>
	    		
	    			<!--  Driving Distance -->
	    			<?php gmw_pt_driving_distance( $gmw, $post, $class='wppl-driving-distance', $title=__( 'Driving: ', 'GMW' ) ); ?>
	    			
	    			<!-- Get directions -->	 	
    				<div>
		    			<?php gmw_pt_directions( $gmw, $post, $title=__( 'Get Directions', 'GMW' ) ); ?>
	    			</div>
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
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
	
</div> <!-- output wrapper -->
