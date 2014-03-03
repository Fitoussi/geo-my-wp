<?php
function wppl_admin() {

	add_menu_page('GEO my WP', 'GEO my WP', 'manage_options', 'wppl-add-ons', 'wppl_plugins_page',GMW_URL. '/admin/images/locate-me-menu.png',66);
	$sub_addons		= add_submenu_page( 'wppl-add-ons', 'Add-ons', __('Add-ons','GMW'), 'manage_options', 'wppl-add-ons', 'wppl_plugins_page');
	$sub_main		= add_submenu_page( 'wppl-add-ons', 'General Settings', __('Settings','GMW'), 'manage_options', 'geo-my-wp', 'wppl_plugin_options_page');
	$sub_shortcodes	= add_submenu_page( 'wppl-add-ons', 'Search form', __('Search forms','GMW'), 'manage_options', 'wppl-shortcodes', 'wppl_shortcodes_page');
	$sub_licenses 	= add_submenu_page( 'wppl-add-ons', 'Licenses', __('Licenses','GMW'), 'manage_options', 'gmw-licenses', 'gmw_premium_license_page' );
	//$sub_helpshortcodes = add_submenu_page( 'geo-my-wp', 'Shortcodes', '+ Shortcodes', 'manage_options', 'wppl-help-shortcodes', 'wppl_help_shortcodes_page');
	add_action('admin_print_styles-' . $sub_main, 'wppl_javascript');
	add_action('admin_print_styles-' . $sub_shortcodes, 'wppl_javascript');
	add_action('admin_print_styles-' . $sub_addons, 'wppl_javascript');
	add_action('admin_print_styles-' . $sub_licenses, 'wppl_javascript');
	
	//add_action('admin_print_styles-' . $sub_helpshortcodes, 'wppl_javascript');
}
add_action('admin_menu', 'wppl_admin');

function wppl_multisite_menu() {
	$site_settings = add_menu_page('GEO my WP', 'GEO my WP', 'manage_options', 'geo-my-wp', 'wppl_site_admin_options_page',plugins_url('images/locate-me-menu.png', __FILE__),66);
	add_action('admin_print_styles-' . $site_settings, 'wppl_javascript');
}
// if ( is_multisite() ) add_action( 'network_admin_menu', 'wppl_multisite_menu', 21);

function wppl_plugin_admin_init(){
	register_setting( 'wppl_plugin_options', 'wppl_fields', 'validate_settings' );
	register_setting( 'wppl_plugin_options_1', 'wppl_shortcode');
	register_setting( 'wppl_pb_options', 'wppl_pb_shortcode');
	register_setting( 'wppl_plugin_plugins_options', 'wppl_plugins', 'validate_plugins_settings');
}
add_action('admin_init', 'wppl_plugin_admin_init');

function wppl_javascript() {
	wp_register_script( 'wppl-admin',  GMW_URL . '/admin/js/admin-settings.js', array(),false, false);
	wp_enqueue_style( 'settings-style', GMW_URL . '/admin/css/settings-pages.css', array(),false,false);
	wp_enqueue_script('wppl-toggle', GMW_URL . '/admin/js/toggle.js', array(),false,true);
	wp_register_script('wppl-addon', GMW_URL . '/admin/js/addon.js', array(),false,true);

	do_action('gmw_admin_pages_register_scripts');
}

/*
if ( is_multisite() && isset( $_POST['wppl_admin_submit'] )  ) {   
	  $wppl_site_options = $_POST['wppl_site_options'];
	  $wppl_site_options['xprofile_address'] = preg_replace('/[^0-9]+/', '', $_POST['wppl_site_options']['xprofile_address']);
	  //if(!current_user_can('manage_network_options')) wp_die('FU');
	 
	  update_site_option( 'wppl_site_options', $wppl_site_options );
	  //wp_redirect(admin_url('network/settings.php?page=my-netw-settings'));
}
*/

/*
 * Main settings page
 */
/*
function wppl_site_admin_options_page() {
	$wppl_on = get_option('wppl_plugins');
	$wppl_options = get_option('wppl_fields'); 
	$wppl_site_options = get_site_option('wppl_site_options'); ?>
	<div class="wrap"> 
		
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2><?php _e('GEO my Wp - Main Settings', 'GMW'); ?></h2>
					
		<table class="widefat fixed" style="margin-bottom:0;margin-top:-6px;">
			<tbody>
				<tr>
					<td>
						<form action="" method="post">
							<table>
								<tbody>
									<?php settings_fields('wppl_plugin_options');
									 include_once 'site-admin-settings.php'; ?>	
								</tbody>
							</table>
						</form>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="widefat fixed" style="margin-bottom:0;margin-top:-2px;">
			<thead>
				<tr>
					<th><p><a href="http://www.geomywp.com" target="_blank">GEO my WP </a> <?php _e('Developed by Eyal Fitoussi','GMW'); ?></p></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>

<?php }
*/
/* 
 * MAIN SETTINGS PAGE 
 */
function wppl_plugin_options_page() { 
	if( is_multisite() ) $wppl_site_options = get_site_option('wppl_site_options'); else $wppl_site_options = false; 
	wp_enqueue_script( 'wppl-admin');
	$wppl_on      = get_option('wppl_plugins');
	$wppl_options = get_option('wppl_fields'); 
	$posts 		  = get_post_types();
	$pages_s 	  = get_pages();
	?>
	<div class="wrap">  
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2>
	    	<?php _e('GEO my Wp - Main Settings', 'GMW'); ?>
	    </h2> 		
		<form action="options.php" method="post">
			<?php settings_fields('wppl_plugin_options');
			include_once 'main-settings.php'; ?>
		</form>
					
		<table class="widefat fixed" style="margin-bottom:0;margin-top:-2px;">
			<thead>
				<tr>
					<th><p><a href="http://www.geomywp.com" target="_blank">GEO my WP </a> <?php _e('Developed by Eyal Fitoussi','GMW'); ?></p></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>
<?php }

/* 
 * ADD-ONS PAGE 
 */
function wppl_plugins_page() {  
	$wppl_on = get_option('wppl_plugins');
	?>
	<div class="wrap"> 	
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2><?php echo _e('GEO my WP - Add-ons','GMW'); ?></h2>
		<form action="options.php" method="post">
			<?php 
			settings_fields('wppl_plugin_plugins_options'); 		
			if (is_multisite()) $site_url = network_site_url('/wp-admin/network/plugin-install.php?tab=search&s=buddypress&plugin-search-input=Search+Plugins');
			else  $site_url = site_url('/wp-admin/plugin-install.php?tab=search&s=buddypress&plugin-search-input=Search+Plugins');
			include_once 'admin-addons.php';
			wp_enqueue_script('wppl-addon');
			wp_localize_script('wppl-toggle','imgUrl', GMW_URL. '/admin/images/');
			?>
		</form>
		<br />			
		<table class="widefat fixed" style="margin-bottom:0;margin-top:-2px;">
			<thead>
				<tr>
					<th><p><a href="http://www.geomywp.com" target="_blank">GEO my WP </a> <?php _e('Developed by Eyal Fitoussi','GMW'); ?></p></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>

<?php }

/*
 * Shortcodes page
 */
function wppl_shortcodes_page() {
	$wppl_on = get_option('wppl_plugins');
	$options_r = get_option('wppl_shortcode');
	$wppl_options = get_option('wppl_fields');
	$posts = get_post_types(); 
	$pages_s = get_pages();	?>	
	<div class="wrap">	
	<?php if ( isset($_GET['gmw_action'] ) && $_GET['gmw_action'] == 'edit' ) : ?>	
		<form class="wppl-shortcode-submit" id="shortcode-submit" action="options.php" method="post">		
			<?php 
				settings_fields('wppl_plugin_options_1'); 
				
				$shortcode_page = 'No shortcode page exists.';
				
			 	apply_filters('gmw_edit_shortcode_page', $shortcode_page, $wppl_on, $options_r, $wppl_options, $posts, $pages_s);
			 	
				do_action('gmw_shortcodes_page_end', $wppl_on, $options_r, $wppl_options, $posts); 
			?>
		</form>
	<?php else : ?>
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2><?php echo _e('Search Form - Shortcodes','GMW'); ?></h2>
		<form class="wppl-shortcode-submit" id="shortcode-submit" action="options.php" method="get">		
			<?php settings_fields('wppl_plugin_options_1');
			include_once 'admin-shortcodes.php'; ?>
		</form>	
	<?php endif; ?>
	<br />
	<table class="widefat fixed" style="margin-bottom:0;margin-top:-2px;">
		<thead>
			<tr>
				<th><p><a href="http://www.geomywp.com" target="_blank">GEO my WP </a> <?php _e('Developed by Eyal Fitoussi','GMW'); ?></p></th>
				<th></th>
			</tr>
		</thead>
	</table>
	</div>
		
<?php }

/*
function gmw_db_update() {
	$wppl_on = get_option('wppl_plugins');
	$wppl_options = get_option('wppl_fields');
	$wppl_site_options = get_site_option('wppl_site_options'); ?>
	
	<div class="wrap">  
		<a href="http://www.geomywp.com" target="_blank"><?php screen_icon('wppl'); ?></a>
		<h2>
	    	<?php _e('GEO my Wp - Database Update', 'GMW'); ?>
	    </h2> 		
		<form action="options.php" method="">
			<?php settings_fields('wppl_plugin_options'); ?>
			<?php do_action('gmw_db_update_admin_page'); ?>
		</form>
					
		<table class="widefat fixed" style="margin-bottom:0;margin-top:-2px;">
			<thead>
				<tr>
					<th><p><a href="http://www.geomywp.com" target="_blank">GEO my WP </a> <?php _e('Developed by Eyal Fitoussi','GMW'); ?></p></th>
					<th></th>
				</tr>
			</thead>
		</table>
	</div>
<?php
}
*/

function validate_settings($input) {
	$wppl_options = get_option('wppl_fields');
	/*
	foreach ($input as $key => $value ) {
		echo $key . ' ' . $value .'<br />';
		if ( isset($value) ) $wppl_options[$key] = $value;
	} */
	
	$wppl_options = $input;
	
	return $wppl_options;
}

function validate_plugins_settings($input) {
	$wppl_on = get_option('wppl_plugins');
	$wppl_on = $input;
	return $wppl_on;
}
?>