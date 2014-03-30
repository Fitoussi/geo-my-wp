<?php 
/**
 * GMW Results Theme - Gray
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
		
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<ul class="gmw-posts-wrapper">

		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
		
			<li id="post-<?php the_ID(); ?>" class="gmw-single-post">
				
				<?php do_action( 'gmw_posts_loop_post_start' , $gmw, $post ); ?>
					
				<h2 class="post-title">
					<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
						<?php the_title(); ?> <span class="radius-distance"><?php echo gmw_pt_by_radius( $gmw, $post ); ?></span>
					</a>
					<span class="post-address">
						<?php echo $post->formatted_address; ?>
					</span>
				</h2>
				
				<div class="post-content">
					<div class="left-col">
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-thumbnail">
								<?php the_post_thumbnail(); ?>
							</div>
						<?php endif; ?>
				
						<?php gmw_pt_taxonomies( $gmw, $post ); ?>
					</div>
					
					<div class="right-col">
						<h4><?php _e( 'Additional Information', 'GMW' ); ?></h4>
		    			<?php gmw_pt_additional_info( $gmw, $post, $tag='ul' ); ?>
		   			</div>
	   			</div>
	   			
				<!-- Get directions -->	 	
    			<?php gmw_pt_directions( $gmw, $post, $title=__('Get Directions','GMW') ) ?>
    			
				<!--  Driving Distance -->
    			<?php gmw_pt_driving_distance( $gmw, $post, $class='wppl-driving-distance', $title=__( 'Driving: ', 'GMW' ) ); ?>
    			
    			<?php do_action( 'gmw_posts_loop_post_end' , $gmw, $post ); ?>
				
			</li><!-- #post -->
		
		<?php endwhile;	 ?>
	</ul>
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
	
</div> <!-- output wrapper -->

