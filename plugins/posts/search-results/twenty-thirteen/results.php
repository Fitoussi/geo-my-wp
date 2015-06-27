<?php 
/**
 * Default Wordpress loop results page
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

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'wppl-single-result '.$featured ); ?>>
				
				<?php do_action( 'gmw_posts_loop_post_start' , $gmw, $post ); ?>
				
				<header class="entry-header">
				
					<?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && has_post_thumbnail() && ! post_password_required() ) : ?>
					<div class="entry-thumbnail">
						<?php the_post_thumbnail(); ?>
					</div>
					<?php endif; ?>
			
					<h1 class="entry-title">
						<a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?><span><?php gmw_distance_to_location( $post, $gmw ); ?></span></a>
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
				
					<!--  Excerpt -->
					<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
						<div class="wppl-excerpt">
							<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
						</div>
					<?php } ?>
					
					<div class="clear"></div>
					
					<?php gmw_pt_taxonomies( $gmw, $post ); ?>
    					
    				<?php gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'div' ); ?> 

				</div><!-- .entry-summary -->
				
				<footer class="entry-meta">
					
					<!-- Get directions -->	 	
					<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
						<div class="get-directions-link">
	    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
	    				</div>
	    			<?php } ?>
    			
					<!--  Driving Distance -->
					<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
	    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
	    			<?php } ?>
	    			
				</footer><!-- .entry-meta -->
				
				<?php do_action( 'gmw_posts_loop_post_end' , $gmw, $post ); ?>
			
			</article><!-- #post -->
		
		<?php endwhile;	 ?>
	</div>
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
</div> <!-- output wrapper -->

