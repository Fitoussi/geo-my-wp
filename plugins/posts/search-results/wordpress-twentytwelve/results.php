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
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'twentytwelve' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?><span><?php gmw_distance_to_location( $post, $gmw ); ?></span></a>
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
				
				<!--  Excerpt -->
				<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
					<div class="excerpt">
						<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'] ); ?>
					</div>
				<?php } ?>
				
					<div class="entry-content">
						<div style="width:65%;float:left;margin-right:10px;">
								<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentytwelve' ) ); ?>
								<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'twentytwelve' ), 'after' => '</div>' ) ); ?>
						</div>
						<div style="width:25%;float:left;">
							
							<?php gmw_pt_taxonomies($gmw, $post); ?>	
		    					
		    				<?php gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'div' ); ?> 
	
		    			</div>
	    			</div><!-- .entry-content -->
    
				<?php endif; ?>
		
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

