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
				<?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
					<div class="featured-post">
						<?php _e( 'Featured post', 'twentytwelve' ); ?>
					</div>
				<?php endif; ?>
								
				<header class="entry-header">
					<?php the_post_thumbnail(); ?>
					<?php if ( is_single() ) : ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php else : ?>
					<h1 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?><span><?php echo '('; gmw_pt_by_radius($gmw, $post); echo ')'; ?></span></a>
					</h1>
					<?php endif; // is_single() ?>
					<?php if ( comments_open() ) : ?>
						<div class="comments-link">
							<?php comments_popup_link( '<span class="leave-reply">' . __( 'Leave a reply', 'twentytwelve' ) . '</span>', __( '1 Reply', 'twentytwelve' ), __( '% Replies', 'twentytwelve' ) ); ?>
						</div><!-- .comments-link -->
					<?php endif; // comments_open() ?>
				</header><!-- .entry-header -->
		
				<?php if ( is_search() ) : // Only display Excerpts for Search ?>
					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div><!-- .entry-summary -->
				<?php else : ?>
				
				<?php gmw_pt_excerpt($gmw, $post); ?>
				
					<div class="entry-content">
						<div style="width:65%;float:left;margin-right:10px;">
								<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?>
								<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'twentytwelve' ), 'after' => '</div>' ) ); ?>
						</div>
						<div style="width:25%;float:left;">
							<?php gmw_pt_taxonomies($gmw, $post); ?>
							<div id="gmw-additional-info" class="gmw-additional-info">	
		    					<?php gmw_pt_additional_info($gmw, $post); ?>
		    				</div>
		    			</div>
	    			</div><!-- .entry-content -->
    
				<?php endif; ?>
		
				<footer class="entry-meta">	
				
					<!-- Get directions -->	 	
    				<?php gmw_pt_directions($gmw, $post, $title=__('Get Directions','GMW') ); ?>
	    			
					<!--  Driving Distance -->
	    			<?php gmw_pt_driving_distance($gmw, $post, $class='wppl-driving-distance', $title=__('Driving','GMW') ); ?>
	    			
					<?php twentytwelve_entry_meta(); ?>
					<?php edit_post_link( __( 'Edit', 'twentytwelve' ), '<span class="edit-link">', '</span>' ); ?>
					<?php if ( is_singular() && get_the_author_meta( 'description' ) && is_multi_author() ) : // If a user has filled out their description and this is a multi-author blog, show a bio on their entries. ?>
						<div class="author-info">
							<div class="author-avatar">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentytwelve_author_bio_avatar_size', 68 ) ); ?>
							</div><!-- .author-avatar -->
							<div class="author-description">
								<h2><?php printf( __( 'About %s', 'twentytwelve' ), get_the_author() ); ?></h2>
								<p><?php the_author_meta( 'description' ); ?></p>
								<div class="author-link">
									<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
										<?php printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentytwelve' ), get_the_author() ); ?>
									</a>
								</div><!-- .author-link	-->
							</div><!-- .author-description -->
						</div><!-- .author-info -->
					<?php endif; ?>
				</footer><!-- .entry-meta -->
			</article><!-- #post -->
			<?php $pc++; ?>
			
		<?php endwhile;	 ?>
	</div>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown($gmw, ''); ?><?php gmw_pt_paginations($gmw); ?>
	</div> 
	
</div> <!-- output wrapper -->

