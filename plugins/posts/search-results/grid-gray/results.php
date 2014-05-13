<?php 
/**
 * GMW Results Theme - Gray
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-grid-gray-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="results-count-wrapper">
		<p><?php gmw_pt_within( $gmw, $sm=__( 'Showing', 'GMW' ), $om=__( 'out of', 'GMW' ), $rm=__( 'results', 'GMW' ) ,$wm=__( 'within', 'GMW' ), $fm=__( 'from','GMW' ), $nm=__( 'your location', 'GMW' ) ); ?></p>
	</div>
	
	<?php do_action( 'gmw_before_top_pagination' , $gmw, $post ); ?>
	
	<div class="pagination-per-page-wrapper top">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>

	</div> 
	
	<!-- Map -->
	<?php gmw_results_map( $gmw ); ?>
		
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<ul class="posts-list-wrapper">

		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
		
			<li id="post-<?php the_ID(); ?>" class="single-post">
				
				<div class="wrapper-inner">
				
					<?php do_action( 'gmw_posts_loop_post_start' , $gmw, $post ); ?>
				
					<!-- Title -->
					<div class="top-wrapper">	
						<h2 class="post-title">
							<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
								<?php the_title(); ?> 
							</a>
						</h2>
						<span class="radius"><?php echo gmw_pt_by_radius( $gmw, $post ); ?></span>
					
					</div>
					
					<?php do_action( 'gmw_posts_loop_after_title' , $gmw, $post ); ?>
					
					<div class="post-content">
						<?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && $gmw['search_results']['featured_image']['use'] == 1 ) { ?>
							
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="post-thumbnail">
									<?php the_post_thumbnail( array( preg_replace("/[^0-9]/","", $gmw['search_results']['featured_image']['width'] ),preg_replace("/[^0-9]/","", $gmw['search_results']['featured_image']['height'] ) ) ); ?>
								</div>
							<?php else : ?>
								<div class="dashicons-before dashicons-admin-generic no-post-thumbnail"></div>
							<?php endif; ?>
							
						<?php } ?>
						
			    		<?php gmw_pt_additional_info( $gmw, $post, $tag='ul' ); ?>
			    			
			    		<?php gmw_pt_taxonomies( $gmw, $post ); ?>

		   			</div>
		   			
		   			<?php do_action( 'gmw_posts_loop_after_content' , $gmw, $post ); ?>
		   			
		   			<div class="bottom-wrapper">
						<!-- Get directions -->	
		   				<?php if ( isset( $gmw['search_results']['get_directions'] ) && $gmw['search_results']['get_directions'] == 1 ) { ?>
			    			<div class="get-directions-wrapper dashicons-before dashicons-admin-generic">
			    				<?php gmw_pt_directions( $gmw, $post, $post->formatted_address ) ?>
			    			</div>
			    		<?php } else { ?>
			    			<div class="address-wrapper">
			    				<?php echo $post->formatted_address; ?>
			    			</div>
		    		  	<?php  } ?>
		    			
						<!--  Driving Distance -->
		    			<?php gmw_pt_driving_distance( $gmw, $post, $class='wppl-driving-distance', $title=__( 'Driving: ', 'GMW' ) ); ?>
		    		</div>
	    			
	    			<?php do_action( 'gmw_posts_loop_post_end' , $gmw, $post ); ?>
	    		</div>
				
			</li><!-- #post -->
		
		<?php endwhile;	 ?>
	</ul>
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
	
	<div class="pagination-per-page-wrapper bottom">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
	
</div> <!-- output wrapper -->

