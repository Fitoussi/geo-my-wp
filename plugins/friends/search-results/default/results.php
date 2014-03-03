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

<?php if ( bp_has_members( $gmw_members_args ) ) : ?>
		
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
	<?php gmw_fl_results_map($gmw); ?>
	
	<?php do_action( 'bp_before_directory_members_list' ); ?>

	<ul id="members-list" class="item-list" role="main">
	
	<?php while ( bp_members() ) : bp_the_member(); ?>

		<li>
		
			<div class="item-avatar">
				<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
			</div>

			<div class="item">
				<div class="item-title">
					<div class="gmw-fl-member-count"><?php echo gmw_fl_member_count($gmw); ?>)</div>
					<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a><?php gmw_fl_by_radius($gmw); ?>

					<?php if ( bp_get_member_latest_update() ) : ?>

						<span class="update"> <?php bp_member_latest_update(); ?></span>

					<?php endif; ?>

				</div>

				<div class="item-meta"><span class="activity"><?php bp_member_last_active(); ?></span></div>

				<?php do_action( 'bp_directory_members_item' ); ?>

				<?php
				
				if ( function_exists('gmw_fl_user_xprofile_fields') ) gmw_fl_user_xprofile_fields($gmw);
				 /***
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
			<div><span>Address: </span><?php gmw_fl_user_address($gmw); ?></div><?php gmw_fl_get_directions($gmw); ?><?php gmw_fl_driving_distance($gmw, $class=''); ?>
				
		</li>
	<?php endwhile; ?>
	
	<?php do_action('gmw_fl_after_members_loop', $gmw, $gmw_options); ?>

	</ul>

	<?php do_action( 'bp_after_directory_members_list' ); ?>

	<?php bp_member_hidden_fields(); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="member-dir-count-bottom">

			<?php gmw_fl_per_page_dropdown($gmw, ''); ?> <?php bp_members_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="member-dir-pag-bottom">

			<?php bp_members_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php gmw_fl_no_members($gmw, $gmw_options); ?></p>
	</div>
	<?php do_action('gmw_fl_after_no_results', $gmw, $gmw_options); ?>

<?php endif; ?>

<?php do_action( 'bp_after_members_loop' ); ?>