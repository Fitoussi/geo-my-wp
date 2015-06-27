<?php 
/**
 * Posts locator "purple" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw  - the form being used ( array )
 * $post - each post in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "purple" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the posts locator form.
 * It will show in the "Search results" dropdown menu as "Custom: purple".
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-pt-purple-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="results-count-wrapper">
		<p><?php gmw_results_message( $gmw, false ); ?></p>
	</div>
	
	<?php do_action( 'gmw_search_results_before_top_pagination', $gmw, $post ); ?>
	
	<div class="pagination-per-page-wrapper top">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
	 <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>
		
	<?php do_action( 'gmw_search_results_before_loop' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<ul class="posts-list-wrapper">

		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
		
			<?php $featured = ( !empty( $post->feature ) ) ? 'gmw-featured-post' : ''; ?>

			<li id="post-<?php the_ID(); ?>" <?php post_class( 'single-post '.$featured ); ?>>
				
				<?php do_action( 'gmw_search_results_loop_item_start' , $gmw, $post ); ?>
			
				<!-- Title -->
				<div class="top-wrapper">	
					<h2 class="post-title">
						<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
							<?php the_title(); ?> 
						</a>
					</h2>
					<span class="radius"><?php gmw_distance_to_location( $post, $gmw ); ?></span>
					
					<div class="address-wrapper">
				    	<span class="fa fa-map-marker address-icon"></span>
				    	<span class="address"><?php gmw_location_address( $post, $gmw ); ?></span>
				    </div>
					
				</div>
				
				<?php do_action( 'gmw_posts_loop_before_content', $gmw, $post ); ?>
				
				<div class="post-content">
					
					<div class="left-col">
						<?php if ( isset( $gmw['search_results']['featured_image']['use'] ) && has_post_thumbnail() ) { ?>
						
							<?php do_action( 'gmw_posts_loop_before_image', $gmw, $post ); ?>
							
							<div class="post-thumbnail">
								<?php the_post_thumbnail( array( $gmw['search_results']['featured_image']['width'], $gmw['search_results']['featured_image']['height'] ) ); ?>
							</div>
						<?php } ?>
				
						<!--  Excerpt -->
						<?php if ( isset( $gmw['search_results']['excerpt']['use'] ) ) { ?>
						
							<?php do_action( 'gmw_posts_loop_before_excerpt', $gmw, $post ); ?>
							
							<div class="excerpt">
								<?php gmw_excerpt( $post, $gmw, $post->post_content, $gmw['search_results']['excerpt']['count'], $gmw['search_results']['excerpt']['more'] ); ?>
							</div>
						<?php } ?>
				
						<?php gmw_pt_taxonomies( $gmw, $post ); ?>
					</div>
					
					<div class="right-col">
						<?php if ( !empty( $gmw['search_results']['additional_info'] ) ) { ?>
    
					    	<?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>
						   	
						   	<div class="contact-info">
								<h4><?php echo $gmw['labels']['search_results']['contact_info']['contact_info']; ?></h4>
					    		<?php gmw_additional_info( $post, $gmw, $gmw['search_results']['additional_info'], $gmw['labels']['search_results']['contact_info'], 'div' ); ?> 
					    	</div>
					    <?php } ?>

					    <?php if ( !empty( $gmw['search_results']['opening_hours'] ) ) { ?>
    
					    	<?php do_action( 'gmw_search_results_before_opening_hours', $post, $gmw ); ?>
						   	
					    	<div class="opening-hours">
					    		<?php gmw_pt_days_hours( $post, $gmw ); ?>
					    	</div>
					    <?php } ?>
				    
		   			</div>
		   			
	   			</div>
	   			
				<!-- Get directions -->	 	
				<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
					
					<?php do_action( 'gmw_posts_loop_before_get_directions', $gmw, $post ); ?>
					
					<div class="get-directions-link">
    					<?php gmw_directions_link( $post, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
    				</div>
    			<?php } ?>
    			
				<!--  Driving Distance -->
				<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
    				<?php gmw_driving_distance( $post, $gmw, false ); ?>
    			<?php } ?>
    			
    			<?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>
				
			</li><!-- #post -->
		
		<?php endwhile;	 ?>
	</ul>
	
	<?php do_action( 'gmw_search_results_after_loop' , $gmw, $post ); ?>
	
	<div class="pagination-per-page-wrapper bottom">
		<!--  paginations -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?><?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
	
</div> <!-- output wrapper -->