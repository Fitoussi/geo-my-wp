<?php
/**
 * GEO my WP Search Results Template.
 *
 * To modify this template file, copy this folder with all its content and place it
 *
 * in the theme's or child theme's folder of your site under:
 *
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 *
 * You will then be able to select your custom template from the "Search Results Templates" select dropdown option in the "Search Results" tab of the form editor.
 *
 * It will be named as "Custom: %folder-name%".
 *
 * @param $gmw  ( array ) the form being used
 *
 * @param $gmw_form ( object ) the form object
 *
 * @package geo-my-wp
 */

$classes = array(
	'item-list',
	'youzify-card-avatar-border-' . youzify_option( 'youzify_md_cards_avatar_border_style', 'circle' ),
	'youzify-card-action-buttons-' . youzify_option( 'youzify_md_cards_buttons_layout', 'block' ),
	'youzify-page-btns-border-' . youzify_option( 'youzify_buttons_border_style', 'oval' ),
);

if ( 'on' === youzify_option( 'youzify_enable_md_cards_avatar_border', 'off' ) ) {
	$classes[] = 'youzify-card-show-avatar-border';
}

?>
<div class="gmw-results">

	<?php do_action( 'gmw_search_results_start', $gmw ); ?>

	<div class="gmw-results-message">
		<span><?php gmw_results_message( $gmw ); ?></span>
	</div>

	<?php gmw_results_map( $gmw ); ?>

	<div class="gmw-results-filters gmw-flexed-wrapper">

		<?php gmw_per_page( $gmw ); ?>

		<?php do_shortcode( 'gmw_search_results_filters', $gmw ); ?>

		<?php gmw_search_results_orderby_filter( $gmw ); ?>

		<?php gmw_results_view_toggle( $gmw ); ?>
	</div> 

	<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>

	<?php
	/**
	 * Fires before the display of the members list.
	 *
	 * @since 1.1.0
	 */
	do_action( 'bp_before_directory_members_list' );
	?>

	<ul id="youzify-members-list" class="gmw-results-list members-list-wrapper <?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-live="assertive" aria-relevant="all">

		<?php
		while ( bp_members() ) {

			bp_the_member();

			$member = $members_template->member;

			// This action is required. Do not remove.
			do_action( 'gmw_the_object_location', $member, $gmw );

			if ( empty( $gmw['search_results']['styles']['disable_single_item_template'] ) ) {
				include 'single-result.php';
			} else {
				do_action( 'gmw_search_results_single_item_template', $member, $gmw );
			}
			?>

		<?php } ?>

	</ul>

	<?php do_action( 'gmw_search_results_after_loop', $gmw ); ?>

	<div class="gmw-pagination-wrapper">
		<?php gmw_pagination( $gmw ); ?>
	</div> 

	<?php do_action( 'gmw_search_results_end', $gmw ); ?>
<?php

if ( ! wp_style_is( 'youzify-directories', 'enqueued' ) ) {
	wp_enqueue_style( 'youzify-directories', YOUZIFY_ASSETS . 'css/youzify-directories.min.css', array(), YOUZIFY_VERSION );
}
