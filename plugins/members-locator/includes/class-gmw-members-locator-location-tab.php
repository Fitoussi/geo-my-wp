<?php
/**
 * GEO my WP - BP Memebr PRofile Page Location Tab
 *
 * Generates the proximity search forms.
 *
 * This class should be extended for different object types.
 *
 * @package geo-my-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GGMW Members Locator Location Tab class
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
	public $args = array();

	/**
	 * Generate tag args.
	 *
	 * @return [type] [description]
	 */
	public function get_args() {

		return array(
			'slug'                           => 'location',
			'name'                           => __( 'Location', 'geo-my-wp' ),
			'nav_menu_label'                 => __( 'Location', 'geo-my-wp' ),
			'nav_submenu_label'              => __( 'Update location', 'geo-my-wp' ),
			'screen_function'                => array( $this, 'screen_display' ),
			'loggedin_user_screen_function'  => array( $this, 'loggedin_user_screen' ),
			'displayed_user_screen_function' => array( $this, 'displayed_user_screen' ),
		);
	}

	/**
	 * [__construct description]
	 */
	public function __construct() {

		// modify the main args.
		$this->args = apply_filters( 'gmw_fl_location_tab_args', $this->get_args() );

		// generate the location tab.
		$this->location_tab();

		// admin navs.
		add_filter( 'bp_members_admin_nav', array( $this, 'adminbar_nav' ), 25 );

		// move nav menu position.
		add_action( 'wp_footer', array( $this, 'move_location_navbar' ) );
	}

	/**
	 * Generate location tab in BP adminbar
	 *
	 * @param  [type] $wp_admin_navs [description].
	 *
	 * @return [type]                [description]
	 */
	public function adminbar_nav( $wp_admin_navs = array() ) {

		if ( empty( $wp_admin_navs ) && ! is_array( $wp_admin_navs ) ) {
			$wp_admin_navs = array();
		}

		if ( is_user_logged_in() ) {

			// Setup the logged in user variables.
			$location_link = trailingslashit( bp_loggedin_user_domain() . $this->args['slug'] );

			// Add location tab.
			$wp_admin_navs[] = apply_filters(
				'gmw_fl_setup_admin_bar',
				array(
					'parent' => 'my-account-buddypress',
					'id'     => 'my-account-gmw-' . $this->args['slug'],
					'title'  => $this->args['nav_menu_label'],
					'href'   => $location_link,
				)
			);

			// add submenu tab.
			$wp_admin_navs[] = array(
				'parent' => 'my-account-gmw-' . $this->args['slug'],
				'id'     => 'my-account-gmw-update-' . $this->args['slug'],
				'title'  => $this->args['nav_submenu_label'],
				'href'   => $location_link,
			);
		}

		return $wp_admin_navs;
	}

	/**
	 * Generate the Location tab
	 */
	public function location_tab() {

		bp_core_new_nav_item(
			apply_filters(
				'gmw_fl_setup_nav',
				array(
					'name'                => $this->args['name'],
					'slug'                => $this->args['slug'],
					'screen_function'     => $this->args['screen_function'],
					'position'            => 20,
					'default_subnav_slug' => $this->args['slug'],
				),
				buddypress()->displayed_user
			)
		);
	}

	/**
	 * Location tab Screen functions
	 */
	public function screen_display() {

		$screen_function = ( bp_is_my_profile() && ! apply_filters( 'gmw_fl_disable_logged_in_location_tab_form', false ) ) ? $this->args['loggedin_user_screen_function'] : $this->args['displayed_user_screen_function'];

		add_action( 'bp_template_content', $screen_function );

		bp_core_load_template( apply_filters( 'gmw_location_my_screen_functions', 'members/single/plugins' ) );
	}

	/**
	 * Display Loggin use location tab contant
	 *
	 * @return [type] [description]
	 */
	public function loggedin_user_screen() {
		return gmw_member_location_form();
	}

	/**
	 * Displayed user location tab contant.
	 */
	public function displayed_user_screen() {

		echo '<div class="location gmw">';

		// Single Location add-on must be activated to display full location details.
		if ( gmw_is_addon_active( 'single_location' ) ) {

			$content = '[gmw_bp_member_location elements="address,map" address_fields="address" map_height="300px" map_width="100%" user_map_icon="0"]';

			// otherwise, display only address field.
		} else {

			$content = '<div id="gmw-ml-member-address"><i class="gmw-icon-location"></i>' . esc_attr( gmw_get_user_address() ) . '</div>';
		}

		echo do_shortcode( apply_filters( 'gmw_fl_user_location_tab_content', $content ) );

		echo '</div>';
	}

	/**
	 * Workaround to move the Location navbar link
	 *
	 * Below the "Profile" link. I couldn't find a way to do it using the
	 *
	 * filters provided.
	 */
	public function move_location_navbar() {
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {

				var slug = "<?php echo $this->args['slug']; ?>";

				$( '#wp-admin-bar-my-account-gmw-' + slug ).each( function() { 
					$( this ).insertAfter( $( this ).next() ); 
				});
			});
		</script>
		<?php
	}
}
