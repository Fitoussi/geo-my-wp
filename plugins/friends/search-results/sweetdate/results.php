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

<?php if ( bp_has_members( $gmw['query_args'] ) ) : ?>
		
	<div id="pag-top" class="pagination">

		<div class="pag-count" id="member-dir-count-top">

			<p><?php bp_members_pagination_count(); ?><?php gmw_fl_wihtin_message($gmw); ?></p>

		</div>

		<div class="clear"></div>
		
		<?php gmw_fl_per_page_dropdown($gmw, ''); ?>
		
		<div class="pagination-links" id="member-dir-pag-top">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

	<div class="clear"></div>
	
	<!-- GEO my WP Map -->
	<?php gmw_results_map( $gmw ); ?>
	
	<?php do_action( 'bp_before_directory_members_list' ); ?>

	<ul id="members-list" class="item-list" role="main">
	
		<?php while ( bp_members() ) : bp_the_member(); ?>
                        
                        <div class="four columns">
                                
                                 <?php do_action( 'gmw_fl_directory_member_start', $gmw ); ?>
                            
                                <div class="search-item">
                                        
                                        <?php if ( isset( $gmw['search_results']['avatar']['use'] ) ) : ?>
                                                <div class="avatar">
                                                        <?php bp_member_avatar('type=thumb&width=94&height=94&class='); ?>
                                                        <?php do_action('bp_members_inside_avatar');?>
                                                </div>
                                        <?php endif; ?>
                                        <?php gmw_fl_by_radius($gmw); ?>
                                    
                                        <?php do_action('bp_members_meta');?>
                                        
                                        <div class="search-body">
                                                <?php do_action( 'bp_directory_members_item' ); ?>
                                                <?php do_action( 'gmw_fl_directory_member_item', $gmw ); ?>
                                        </div>
                                    
                                        <div class="bp-member-dir-buttons">
                                                <?php do_action('bp_directory_members_item_last');?>
                                                <div class="clear"></div>
                                                <?php gmw_fl_directions_link( $gmw, $title=__( 'Get directions', 'GMW' ) ); ?>
                                        </div>
                                    
                                </div>
                            
                                <?php do_action( 'gmw_fl_directory_member_end', $gmw ); ?>
                        </div>

                <?php endwhile; ?>
		
		<?php do_action( 'gmw_fl_after_members_loop', $gmw ); ?>

	</ul>

	<?php do_action( 'bp_after_directory_members_list' ); ?>

	<?php bp_member_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">

			<?php gmw_fl_per_page_dropdown( $gmw, '' ); ?> <?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php gmw_fl_no_members( $gmw ); ?></p>
	</div>
	
	<?php do_action( 'gmw_fl_after_no_members', $gmw ); ?>

<?php endif; ?>

<?php do_action( 'bp_after_members_loop' ); ?>