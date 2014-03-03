<?php
/**
 * Clean - Results Page.
 * @version 1.0
 * @author Eyal Fitoussi
 */

global $cp_options;
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div id="wppl-output-wrapper" class="wppl-output-wrapper wppl-output-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw, $post ); ?>
	
	<!-- results count -->
	<div class="gmw-results-count">
		<h2><?php gmw_pt_within( $gmw, $sm=__( 'Showing', 'GMW' ), $om=__( 'out of', 'GMW' ), $rm=__( 'results', 'GMW' ) ,$wm=__( 'within', 'GMW' ), $fm=__( 'from','GMW' ), $nm=__( 'your location', 'GMW' ) ); ?></h2>
	</div>
	
	<div class="gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_before_map' , $gmw, $post ); ?>
	
	<!-- Map -->
	<?php gmw_results_map( $gmw ); ?>
	
	<div class="clear"></div>
	
	<?php do_action( 'gmw_search_results_after_map' , $gmw, $post ); ?>
	
	<!--  Results wrapper -->
	<div id="wppl-results-wrapper-<?php echo $gmw['ID']; ?>" class="wppl-results-wrapper">
		
		<?php do_action( 'gmw_search_results_loop_start' , $gmw, $post ); ?>
		
		<!--  this is where wp_query loop begins -->
		<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
			
			<?php appthemes_before_post(); ?>
    
                        <div class="post-block-out <?php cp_display_style( 'featured' ); ?>">

                            <div class="post-block">

                                <?php if(in_array($post->ID, get_option('sticky_posts'))) { ?>
                                       <span class="i_featured">Featured</span>
                                <?php } ?>	

                                <?php if (get_post_meta($post->ID, 'cp_ad_sold', true) == 'yes') : ?>
                                     <span class="i_sold">Sold</span>
                                <?php endif; ?>


                                <div class="post-left">

                                    <?php if ( $cp_options->ad_images ) cp_ad_loop_thumbnail(); ?>

                                </div>

                                <div class="<?php cp_display_style( array( 'ad_images', 'ad_class' ) ); ?>">

                                    <?php appthemes_before_post_title(); ?>

                                    <h3>
                                        <a href="<?php the_permalink(); ?>"><?php if ( mb_strlen( get_the_title() ) >= 75 ) echo mb_substr( get_the_title(), 0, 75 ).'...'; else the_title(); ?></a>
                                        <span class="radius-dis">(<?php echo gmw_pt_by_radius( $gmw, $post ); ?>)</span>
                                    </h3>

                                    <div class="clr"></div>

                                    <?php appthemes_after_post_title(); ?>

                                    <div class="clr"></div>

                                    <?php appthemes_before_post_content(); ?>

                                    <p class="post-desc"><?php echo cp_get_content_preview( 160 ); ?></p>

                                    <?php appthemes_after_post_content(); ?>

                                    <div class="clr"></div>

                                </div>

                                <div class="clr"></div>

                            </div><!-- /post-block -->

                        </div><!-- /post-block-out -->   

                        <?php appthemes_after_post(); ?>
		  	
		<?php endwhile; ?>
		
                <?php appthemes_after_endwhile(); ?>
                        		
		<!--  end of the loop -->
		
	</div> <!--  results wrapper -->    
	
	<!--  Pagination -->
	<div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
		<!--  paginations -->
		<?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
	</div> 
	
	<?php do_action( 'gmw_search_results_end' , $gmw, $post ); ?>
	
</div> <!-- output wrapper -->