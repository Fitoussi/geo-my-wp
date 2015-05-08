<?php
/**
 * Members locator "default" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "default" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/friends/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: default".
 */
?>	
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-default-results-wrapper">

	<?php do_action( 'gmw_search_results_start' , $gmw ); ?>
	
    <div id="pag-top" class="pagination">

    	<!-- results message -->
        <div class="pag-count" id="member-dir-count-top">
            <p><?php bp_members_pagination_count(); ?><?php gmw_results_message( $gmw, false ); ?></p>
        </div>

        <div class="clear"></div>
		
		<!-- per page -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
		
		<!-- pagination -->
        <div class="pagination-links" id="member-dir-pag-top">
    		<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
        </div>
    </div>

    <div class="clear"></div>

    <!-- GEO my WP Map -->
    <?php 
    if ( $gmw['search_results']['display_map'] == 'results' ) {
        gmw_results_map( $gmw );
    }
    ?>
    
    <?php do_action( 'bp_before_directory_members_list' ); ?>
	    
    <ul id="members-list" class="item-list" role="main">

    	<!-- members loop -->
    	<?php while ( bp_members() ) : bp_the_member(); ?>

    		<!-- do not remove this line -->
    		<?php $member = $members_template->member; ?>
            <li>         
            	<!-- do not remove this line -->
                <?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>

                <!-- avatar -->
                <?php if ( isset( $gmw['search_results']['avatar']['use'] ) ) { ?>
                    <div class="item-avatar">
                        <a href="<?php bp_member_permalink(); ?>">
                        	<?php bp_member_avatar( array( 'type' => 'full', 'width' => $gmw['search_results']['avatar']['width'], 'height' => $gmw['search_results']['avatar']['height'] ) ); ?>
                        </a>
                    </div>
        		<?php } ?>

                <div class="item">
                
                    <div class="item-title">
                    	
                        <div class="gmw-fl-member-count"><?php echo $member->member_count; ?>)</div>
                        <!-- member name -->
                        <a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
                    		
                    	<?php do_action( 'gmw_search_results_before_distance', $gmw, $member); ?>
                    	
                    	<!-- distance -->
                    	<?php gmw_distance_to_location( $members_template->member, $gmw ); ?>

       					<?php if ( bp_get_member_latest_update() ) { ?>
                            <span class="update"> <?php bp_member_latest_update(); ?></span>
        				<?php }; ?>

                    </div>

                    <div class="item-meta">
                    	<span class="activity">
                    		<?php bp_member_last_active(); ?>
                    	</span>
                    </div>

        			<?php do_action( 'bp_directory_members_item' ); ?>
        			<?php do_action( 'gmw_fl_search_results_member_items', $gmw, $member ); ?>

                    <?php
                    /*                     * *
                     * If you want to show specific profile fields here you can,
                     * but it'll add an extra query for each member in the loop
                     * (only one regardless of the number of fields you show):
                     *
                     * bp_member_profile_data( 'field=the field name' );
                     */
                    ?>
                </div>

                <div class="action">
        			<?php do_action( 'bp_directory_members_actions' ); ?>
                </div>

                <div class="clear"></div>

                <?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>
                
                <!-- address -->
                <div>
	                <span><?php echo $gmw['labels']['search_results']['address'] ?></span>
                	<?php gmw_location_address( $member, $gmw ); ?>
                </div>
                
                <!-- Get directions -->	 	
				<?php if ( isset( $gmw['search_results']['get_directions'] ) ) { ?>
					<?php global $members_template; ?>
					<div class="get-directions-link">
    					<?php gmw_directions_link( $members_template->member, $gmw, $gmw['labels']['search_results']['directions'] ); ?>
    				</div>
    			<?php } ?>
                
                <!--  Driving Distance -->
				<?php if ( isset( $gmw['search_results']['by_driving'] ) ) { ?>
    				<?php gmw_driving_distance( $member, $gmw, false ); ?>
    			<?php } ?>

        		<?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>

            </li>

    	<?php endwhile; ?>
	
    </ul>

    <?php do_action( 'bp_after_directory_members_list' ); ?>

    <?php bp_member_hidden_fields(); ?>
	    
    <div id="pag-bottom" class="pagination">

        <!-- results message -->
        <div class="pag-count" id="member-dir-count-top">
            <p><?php bp_members_pagination_count(); ?><?php gmw_results_message( $gmw, false ); ?></p>
        </div>
    		
        <div class="clear"></div>
		
		<!-- per page -->
		<?php gmw_per_page( $gmw, $gmw['total_results'], 'paged' ); ?>
		
		<!-- pagination -->
        <div class="pagination-links" id="member-dir-pag-top">
    		<?php gmw_pagination( $gmw, 'paged', $gmw['max_pages'] ); ?>
        </div>

    </div>
	
	<?php do_action( 'gmw_search_results_end', $gmw ); ?>	

</div>