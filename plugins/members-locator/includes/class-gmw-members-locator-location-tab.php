<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GMW_Members_Locator_Location_Tab class
 *
 * Generate the member location tab
 *
 * @since 3.0
 */
class GMW_Members_Locator_Location_Tab {

	/**
	 * Slug
	 *
	 * @var string
	 */
	public $slug = 'location';

	/**
	 * [__construct description]
	 */
	public function __construct() {
		add_filter( 'bp_members_admin_nav', array( $this, 'adminbar_nav' ), 20 );
		add_action( 'bp_setup_nav', array( $this, 'location_tab' ), 20 );
		add_action( 'wp_footer', array( $this, 'move_location_navbar' ) );
	}

	/**
	 * Generate location tab in BP adminbar
	 *
	 * @param  [type] $wp_admin_nav [description]
	 * @return [type]               [description]
	 */
	public function adminbar_nav( $wp_admin_nav ) {

		if ( is_user_logged_in() ) {

			// Setup the logged in user variables
			$location_link = trailingslashit( bp_loggedin_user_domain() . $this->slug );

			// Add location tab
			$wp_admin_nav[] = apply_filters(
				'gmw_fl_setup_admin_bar', array(
					'parent' => 'my-account-buddypress',
					'id'     => 'my-account-gmw-' . $this->slug,
					'title'  => __( 'Location', 'geo-my-wp' ),
					'href'   => $location_link,
				)
			);

			// add submenu tab
			$wp_admin_nav[] = array(
				'parent' => 'my-account-gmw-' . $this->slug,
				'id'     => 'my-account-gmw-update-' . $this->slug,
				'title'  => __( 'Update Location', 'geo-my-wp' ),
				'href'   => $location_link,
			);
		}

		return $wp_admin_nav;
	}

	/**
	 * Workaround to move the Location navbar link
	 *
	 * below the "Profile" link. I couldn't find a way to do it using the
	 *
	 * filters provided.
	 *
	 * @return [type] [description]
	 */
	public function move_location_navbar() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$( '#wp-admin-bar-my-account-gmw-location' ).each( function() { 
					$( this ).insertAfter( $( this ).next() ); 
				});
			});
		</script>
		<?php
	}

	/**
	 * Generate the Location tab
	 *
	 * @return [type] [description]
	 */
	public function location_tab() {

		bp_core_new_nav_item(
			apply_filters(
				'gmw_fl_setup_nav', array(
					'name'                => __( 'Location', 'geo-my-wp' ),
					'slug'                => $this->slug,
					'screen_function'     => array( $this, 'screen_display' ),
					'position'            => 20,
					'default_subnav_slug' => $this->slug,
				), buddypress()->displayed_user
			)
		);
	}

	/**
	 * Location tab Screen functions
	 *
	 * @return [type] [description]
	 */
	public function screen_display() {

		$who = ( bp_is_my_profile() && ! apply_filters( 'gmw_fl_disable_logged_in_location_tab_form', false ) ) ? 'loggedin_user' : 'displayed_user';

		add_action( 'bp_template_content', array( $this, $who . '_screen' ) );

		bp_core_load_template( apply_filters( 'gmw_location_my_screen_functions', 'members/single/plugins' ) );
	}

	/**
	 * Display Loggin use location tab contant
	 *
	 * @return [type] [description]
	 */
	public function loggedin_user_screen() {
		gmw_member_location_form();
	}

	/**
	 * Displayed user location tab contant
	 *
	 * @return [type] [description]
	 */
	public function displayed_user_screen() {

		echo '<div class="location gmw">';

		// Single Location add-on must be activated to display full location details
		if ( gmw_is_addon_active( 'single_location' ) ) {

			$content = '[gmw_bp_member_location elements="address,map" address_fields="address" map_height="300px" map_width="100%" user_map_icon="0"]';

			// otherwise, display only address field
		} else {

			$content = '<div id="gmw-ml-member-address"><i class="gmw-icon-location"></i>' . esc_attr( gmw_get_user_address() ) . '</div>';
		}

		echo do_shortcode( apply_filters( 'gmw_fl_user_location_tab_content', $content ) );

		echo '</div>';
	}
}
new GMW_Members_Locator_Location_Tab;
