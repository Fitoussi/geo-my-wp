<?php 
/**
 * Default Wordpress loop results page
 * @version 1.0
 * @author Eyal Fitoussi
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div id="gmw-pt-output-wrapper-<?php echo $gmw['form_id']; ?>" class="gmw-pt-output-wrapper-<?php echo $gmw['form_id']; ?> gmw-pt-output-wrapper">
	
	<!-- results count -->
	<div class="gmw-results-count">
		<h2><?php gmw_pt_within( $gmw,$sm=__('Showing','GMW'), $om=__('out of','GMW'), $rm=__('results','GMW') ,$wm=__('within','GMW'), $fm=__('from','GMW'), $nm=__('your location','GMW') ); ?></h2>
	</div>
		
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown($gmw, ''); ?><?php gmw_pt_paginations($gmw); ?>
	</div> 
	
	<!-- Map -->
	<?php gmw_pt_results_map($gmw); ?>
	
	<div class="clear"></div>
	
	<!--  Results wrapper -->
	<div id="gmw-results-wrapper-<?php echo $gmw['form_id']; ?>" class="gmw-results-wrapper">

		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
		
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
					<div class="entry-thumbnail">
						<?php the_post_thumbnail(); ?>
					</div>
					<?php endif; ?>
			
					<h1 class="entry-title">
						<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?><span><?php echo '('; gmw_pt_by_radius($gmw, $post); echo ')'; ?></span></a>
					</h1>
			
					<div class="entry-meta">
			
						<?php twentythirteen_entry_meta(); ?>
						<?php edit_post_link( __( 'Edit', 'twentythirteen' ), '<span class="edit-link">', '</span>' ); ?>
						<div>
							<?php echo $post->formatted_address; ?>
						</div>
					</div><!-- .entry-meta -->
				</header><!-- .entry-header -->
			
				
				<div class="entry-summary">
					<?php gmw_pt_excerpt($gmw, $post); ?>
					
					<div class="clear"></div>
					
					<?php gmw_pt_taxonomies($gmw, $post); ?>
					<div id="gmw-additional-info" class="gmw-additional-info">	
    					<?php gmw_pt_additional_info($gmw, $post); ?>
    				</div>

				</div><!-- .entry-summary -->
				
				<footer class="entry-meta">
					
					<!-- Get directions -->	 	
    				<?php gmw_pt_directions($gmw, $post, $title=__('Get Directions','GMW') ) ?>
    			
					<!--  Driving Distance -->
    				<?php gmw_pt_driving_distance($gmw, $post, $class='wppl-driving-distance', $title=__('Driving','GMW') ); ?>
				</footer><!-- .entry-meta -->
			
			</article><!-- #post -->
		
		<?php endwhile;	 ?>
	</div>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown($gmw, ''); ?><?php gmw_pt_paginations($gmw); ?>
	</div> 
	
</div> <!-- output wrapper -->

