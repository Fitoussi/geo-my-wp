<?php
/**
 * Default - Results Page.
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

                <!-- Title -->
                <div class="wppl-title-holder">
                    <h2 class="wppl-h2">
                        <a href="<?php echo the_permalink(); ?>"><?php echo $post->post_count; ?>) <?php the_title(); ?></a>
                        <span class="radius-dis">(<?php echo gmw_pt_by_radius( $gmw, $post ); ?>)</span>
                    </h2>
                </div>

                <!--  Thumbnail -->
                <div id="wppl-thumb" class="wppl-thumb">
                    <?php gmw_pt_thumbnail( $gmw, $post ); ?>
                </div>

                <!--  Excerpt -->
                <div class="wppl-excerpt">
                    <?php gmw_pt_excerpt( $gmw, $post ); ?> 
                </div>

                <!--  taxonomies -->
                <div id="wppl-taxes-wrapper" class="wppl-taxes-wrapper">
                    <?php gmw_pt_taxonomies( $gmw, $post ); ?>
                </div>

                <div class="wppl-info">

                    <div class="wppl-info-left">

                        <!--  Addiotional info -->
                        <div id="wppl-additional-info" class="wppl-additional-info">	
                            <?php gmw_pt_additional_info( $gmw, $post ); ?>
                        </div>

                    </div>

                    <!-- info left ends-->

                    <div class="wppl-info-right">

                        <!--  Address -->
                        <div class="wppl-address">
                            <?php echo $post->address; ?>
                        </div>

                        <!--  Driving Distance -->
                        <?php gmw_pt_driving_distance( $gmw, $post, $class = 'wppl-driving-distance', $title = __( 'Driving: ', 'GMW' ) ); ?>

                        <!-- Get directions -->		 	
                        <div class="wppl-get-directions">
                            <?php gmw_pt_directions( $gmw, $post, $title = __( 'Get Directions', 'GMW' ) ); ?>
                        </div>

                    </div><!-- info right -->

                </div> <!-- info -->

            </div> <!--  single- wrapper ends -->

            <div class="clear"></div>  

        <?php endwhile; ?>
        <!--  end of the loop -->

    </div> <!--  results wrapper -->    

    <div class="gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">
        <!--  paginations -->
        <?php gmw_pt_per_page_dropdown( $gmw, '' ); ?><?php gmw_pt_paginations( $gmw ); ?>
    </div> 

</div> <!-- output wrapper -->
