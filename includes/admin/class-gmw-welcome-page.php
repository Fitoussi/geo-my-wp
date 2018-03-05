<?php
/**
 * Welcome Page Class
 *
 * Shows a feature overview for the new version (major) and credits.
 *
 * Adapted from code in EDD (Copyright (c) 2012, Pippin Williamson) and WP.
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.4.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Welcome class
 */
class GMW_Welcome_Page {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'gmw-welcome' ) {
			$page = add_dashboard_page( 'Welcome to GEO my WP', 'Welcome to GEO my WP', 'manage_options', 'gmw-welcome', array( $this, 'output' ) );
		}
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function tabs() {

		?>
		<h1><?php printf( __( 'Welcome to GEO my WP %s', 'geo-my-wp' ), GMW_VERSION ); ?></h1>

		<h2 class="gmw-tabs-wrapper">
			<a class="gmw-nav-tab" href="#whats-new-tab-panel">
				<?php _e( "What's New", 'geo-my-wp' ); ?>
			</a>
			<a class="gmw-nav-tab" href="#updates-tab-panel">
				<?php _e( 'Updates', 'geo-my-wp' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Output the about screen.
	 */
	public function output() {
		?>
		<div class="wrap about-wrap">

			<?php $this->tabs(); ?>

			<div id="whats-new-tab-panel" class="gmw-tab-panel">

				<div class="changelog">
					<div class="changelog about-integrations">
						<div class="wc-feature feature-section last-feature-section col three-col">
							<div>
								<h4><?php _e( 'Improved Product Variation Editor', 'woocommerce' ); ?></h4>
								<p><?php _e( 'When editing product variations in the backend, we have added a new, paginated interface to make the process of adding complex product variations both quicker and more reliable.', 'woocommerce' ); ?></p>
							</div>
							<div>
								<h4><?php _e( 'Frontend Variation Performance', 'woocommerce' ); ?></h4>
								<p><?php _e( 'If your products have many variations (20+) they will use an ajax powered add-to-cart form. Select all options and the matching variation will be found via AJAX. This improves performance on the product page.', 'woocommerce' ); ?></p>
							</div>
							<div class="last-feature">
								<h4><?php _e( 'Flat Rate Shipping, Simplified', 'woocommerce' ); ?></h4>
								<p><?php _e( 'Flat Rate Shipping was overly complex in previous versions of WooCommerce. We have simplified the interface (without losing the flexibility) making Flat Rate and International Shipping much more intuitive.', 'woocommerce' ); ?></p>
							</div>
						</div>
					</div>
				</div>
				<div class="changelog">
					<div class="feature-section col three-col">
						<div>
							<h4><?php _e( 'Geolocation with Caching', 'woocommerce' ); ?></h4>
							<p><?php printf( __( 'If you use static caching you may have found geolocation did not work for non-logged-in customers. We have now introduced a new javascript based Geocaching solution to help. Enable this in the %ssettings%s.', 'woocommerce' ), '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '">', '</a>' ); ?></p>
						</div>
						<div>
							<h4><?php _e( 'Onboarding Experience', 'woocommerce' ); ?></h4>
							<p><?php _e( 'We have added our "WooCommerce 101" tutorial videos to the help tabs throughout admin if you need some help understanding how to use WooCommerce. New installs will also see the new setup wizard to help guide through initial setup.', 'woocommerce' ); ?></p>
						</div>
						<div class="last-feature">
							<h4><?php _e( 'Custom AJAX Endpoints', 'woocommerce' ); ?></h4>
							<p><?php printf( __( 'To improve performance on the frontend, we\'ve introduced new AJAX endpoints which avoid the overhead of making calls to admin-ajax.php for events such as adding products to the cart.', 'woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce-colors/">', '</a>' ); ?></p>
						</div>
					</div>
					<div class="feature-section last-feature-section col three-col">
						<div>
							<h4><?php _e( 'Visual API Authentication', 'woocommerce' ); ?></h4>
							<p><?php _e( 'Services which integrate with the REST API can now use the visual authentication endpoint so a user can log in and grant API permission from a single page before being redirected back.', 'woocommerce' ); ?></p>
						</div>
						<div>
							<h4><?php _e( 'Email Notification Improvements', 'woocommerce' ); ?></h4>
							<p><?php _e( 'Email templates have been improved to support a wider array of email clients, and extra notifications, such as partial refund notifications, have been included.', 'woocommerce' ); ?></p>
						</div>
						<div class="last-feature">
							<h4><?php _e( 'Shipping Method Priorities', 'woocommerce' ); ?></h4>
							<p><?php _e( 'To give more control over which shipping method is selected by default for customers, each method can now be given a numeric priority.', 'woocommerce' ); ?></p>
						</div>
					</div>
				</div>

			</div>

			<div id="updates-tab-panel" class="gmw-tab-panel">
			content
			</div>
		</div>
		<?php
	}
}

new GMW_Welcome_Page();
