<?php
/**
 * GEO my WP - Members Loop
 *
 * The members loop below is the same as the buddypress loop with added functions of GEO my WP
 *
 * @package BuddyPress
 * @subpackage bp-default
 */
?>
<?php do_action( 'bp_before_members_loop' ); ?>

<?php if ( bp_has_members( $gmw[ 'query_args' ] ) ) : ?>
	
	<!--  Main results wrapper -->
	<div class="gmw-results-wrapper gmw-results-wrapper-<?php echo $gmw['ID']; ?> gmw-fl-yellow-results-wrapper">
		
		<div class="results-count-wrapper">
			<p>
				<?php bp_members_pagination_count(); ?><?php gmw_fl_wihtin_message( $gmw ); ?>
			</p>
		</div>
		
		<div class="pagination-per-page-wrapper top">
			<?php gmw_fl_per_page_dropdown( $gmw, '' ); ?>
		
			<div class="pagination-wrapper">
				<?php bp_members_pagination_links(); ?>
			</div>
		</div>
		
		<!-- GEO my WP Map -->
	    <?php gmw_results_map( $gmw ); ?>
	
	    <?php do_action( 'bp_before_directory_members_list' ); ?>
	
	    <ul class="members-list-wrapper">
	
	    	<?php while ( bp_members() ) : bp_the_member(); ?>
	
	            <li>
	        		<?php do_action( 'gmw_fl_directory_member_start', $gmw ); ?>
					
					<div class="top-wrapper">
						
						<span class="user-name-wrapper">
	                		<span class="member-count"><?php gmw_fl_member_count( $gmw ); ?>)</span>
	                    	<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
	                    
	                    	<span class="activity"><?php bp_member_last_active(); ?></span>
	                    </span>
	                    	
	                    <span class="radius"><?php gmw_fl_by_radius( $gmw ); ?></span>
	
	                </div>
	                    
	                <div class="info-left-wrapper">
		                <?php if ( isset( $gmw[ 'search_results' ][ 'avatar' ][ 'use' ] ) ) : ?>
		                    <div class="user-avatar">
		                        <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar( array( 'type' => 'full', 'width' => $gmw[ 'search_results' ][ 'avatar' ][ 'width' ], 'height' => $gmw[ 'search_results' ][ 'avatar' ][ 'height' ] ) ); ?></a>
		                    </div>
		        		<?php endif; ?>
		        		
		        		<?php do_action( 'bp_directory_members_actions' ); ?>
		
		                <?php if ( bp_get_member_latest_update() ) : ?>
		                	<div class="update"><?php bp_member_latest_update(); ?></div>
		        		<?php endif; ?>
	                    
	                    <div class="address">
		                	<span><?php _e( 'Address: ', 'GMW' ); ?></span>
		                	<?php gmw_fl_member_address( $gmw ); ?>
		                </div>
		                
		                <?php gmw_fl_directions_link( $gmw, $title=__( 'Get directions', 'GMW' ) ); ?>
	                
	               		<?php gmw_fl_driving_distance( $gmw, $class='' ); ?>
	                             		
					</div>
					
					<div class="info-right-wrapper">
	  
	        			<?php do_action( 'bp_directory_members_item' ); ?>
	
	                    <?php do_action( 'gmw_fl_directory_member_item', $gmw ); ?>
	
	                </div>
	                            
	        		<?php do_action( 'gmw_fl_directory_member_end', $gmw ); ?>
	
	            </li>
	
	    	<?php endwhile; ?>
	
	    </ul>
		
		<?php do_action( 'gmw_fl_after_members_loop', $gmw ); ?>
		
	    <?php do_action( 'bp_after_directory_members_list' ); ?>
	
	    <?php bp_member_hidden_fields(); ?>
	
		<div class="pagination-per-page-wrapper bottom">
		
			<?php gmw_fl_per_page_dropdown( $gmw, '' ); ?>
		
			<div class="pagination-wrapper">
				<?php bp_members_pagination_links(); ?>
			</div>
		</div>
	</div>

<?php else: ?>

    <div id="no-results-message">
        <p><?php gmw_fl_no_members( $gmw ); ?></p>
    </div>

    <?php do_action( 'gmw_fl_after_no_members', $gmw ); ?>

<?php endif; ?>

<?php do_action( 'bp_after_members_loop' ); ?>