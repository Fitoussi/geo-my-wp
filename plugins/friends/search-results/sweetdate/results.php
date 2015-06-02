<?php
/**
 * Members locator "yellow" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "yellow" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: yellow".
 */
?>	
<!--  Main results wrapper -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-sweetdate-results-wrapper">
	
	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
	
	<div class="results-count-wrapper">
		<p><?php bp_members_pagination_count(); ?><?php gmw_results_message( $gmw, false ); ?></p>
	</div>
		        
	<div class="pagination-per-page-wrapper top">
		<!-- per page -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
	
		<div class="pagination-wrapper">
			<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
		</div>
	</div>
	
	 <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>

    <?php do_action( 'bp_before_directory_members_list' ); ?>
    	
    <div class="item-list search-list" id="members-list">

    	<!-- members loop -->
    	<?php while ( bp_members() ) : bp_the_member() ; ?>

    		<!-- do not remove this line -->
    		<?php $member = $members_template->member; ?>

    		<!-- do not remove this line -->
            <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>

    		<div class="four columns">

		        <div class="search-item">
			         
			        <div class="avatar">

			            <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar('type=full&width=94&height=94&class='); ?></a>
			            
			            <?php do_action('bp_members_inside_avatar');?>

			            <span class="radius">
                    		<?php gmw_distance_to_location( $member, $gmw ); ?>
                    	</span>

			        </div>

			        <?php do_action('bp_members_meta');?>

			        <div class="search-body">
			            <?php do_action( 'bp_directory_members_item' ); ?>
			        </div>
			        
			        <div class="location-wrapper">
	                    
	                    <?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>
	                    
	                    <div class="address">
	                    	<?php gmw_location_address( $member, $gmw ); ?>
		                </div>
		                
		                <?php do_action( 'gmw_search_results_before_get_directions', $gmw, $member ); ?>
		                
		                <!-- Get directions -->	 	
						<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
							<div class="get-directions-link">
		    					<?php gmw_directions_link( $member, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
		    				</div>
		    			<?php } ?>
	                
	               		<!--  Driving Distance -->
						<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
		    				<?php gmw_driving_distance( $member, $gmw, false ); ?>
		    			<?php } ?>
                    
	                </div>           

			        <div class="bp-member-dir-buttons">
			            <?php do_action('bp_directory_members_item_last');?>
			        </div>

		        </div>

	      </div>

    	<?php endwhile; ?>
  	</div>
	
    <?php do_action( 'bp_after_directory_members_list' ); ?>

    <?php bp_member_hidden_fields(); ?>

	<div class="pagination-per-page-wrapper bottom">	
		<div class="pagination-wrapper">
			<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
		</div>
	</div>
</div>