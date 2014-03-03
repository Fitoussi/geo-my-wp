<?php
/**
 * Custom - Results Page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div id="wppl-output-wrapper" class="wppl-output-wrapper wppl-output-wrapper">
	
	<!-- results count -->
	<div class="gmw-results-count">
		<h2><?php gmw_pt_within( $gmw,$sm=__('Showing','GMW'), $om=__('out of','GMW'), $rm=__('results','GMW') ,$wm=__('within','GMW'), $fm=__('from','GMW'), $nm=__('your location','GMW') ); ?></h2>
	</div>
		
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown($gmw, ''); ?><?php gmw_pt_paginations($gmw); ?>
	</div> 
	
	<!-- Map -->
	<div class="wppl-map-wrapper" style="position:relative">
		<?php gmw_pt_results_map($gmw); ?>
		<img class="gmw-map-loader" src="<?php echo GMW_URL; ?>/images/map-loader.gif" style="position:absolute;top:45%;left:33%;"/>
	</div>
	
	<div class="clear"></div>
	
	<!--  Results wrapper -->
	<div id="wppl-results-wrapper-<?php echo $gmw['form_id']; ?>" class="wppl-single-result-wrapper">
		
		<!--  this is where wp_query loop begins -->
		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
			
			<!--  single results wrapper  -->
			<div class="wppl-single-result">
		
				<!-- Title -->
				<h2>
					<a href="<?php echo the_permalink(); ?>"><?php echo $pc; ?>) <?php the_title(); ?></a>
					<?php if ( isset( $gmw['your_lat'] ) && !empty( $gmw['your_lat'] ) ) { ?><span class="radius-dis">(<?php echo gmw_pt_by_radius($gmw, $post); ?>)</span><?php } ?>
				</h2>
				
				<div>
					<?php gmw_pt_thumbnail($gmw); ?>
				</div>
			
				<!--  Excerpt -->
    			<div>
				 	<?php gmw_pt_excerpt($gmw, $post); ?> 
				 </div>
				
				<!--  taxonomies -->
				<div>
					<?php gmw_pt_taxonomies($gmw, $post); ?>
				</div>
				
		    	<div class="clear"></div>
		    	
		    	<div class="wppl-info">
	    			
	    			<!--  Addiotional info -->
					<div>	
	    				<?php gmw_pt_additional_info($gmw, $post); ?>
	    			</div>
	    		
	    			<!--  Address -->
	    			<div class="wppl-address">
	    				<?php echo $post->address; ?>
	    			</div>
	    		
	    			<!--  Driving Distance -->
	    			<?php gmw_pt_driving_distance($gmw, $post, $class='wppl-driving-distance', $title=__('Driving','GMW') ); ?>
	    			
	    			<!-- Get directions -->	 	
    				<div>
		    			<?php gmw_pt_directions($gmw, $post, $title=__('Get Directions','GMW') ); ?>
	    			</div>
		    	</div> <!-- info -->
		    
		    </div> <!--  single- wrapper ends -->
		    
		    <div class="clear"></div>     
		 <?php $pc++; ?>
		<?php endwhile; ?>
		<!--  end of the loop -->
	
	</div> <!--  results wrapper -->    
	
	<!--  Pagination -->
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown($gmw, ''); ?><?php gmw_pt_paginations($gmw); ?>
	</div> 
	
</div> <!-- output wrapper -->
