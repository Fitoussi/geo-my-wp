=== GEO my Wordpress ===
Contributors: ninjew, Eyal Fitoussi
Donate link: http://geomywp.com/
Tags: Geolocation, Directory, Store Locator, Geolocator, Geotagging, Geocode, Mapping, Proximity search, Zipcode, Geolocate posts, Address search, Distance, Google maps, Directions, Locations, Geo, Members locator, Geolocate members, Latitude, Longitude, Coordinates, Locations finder, Map creator.
Requires at least: 4.3
Tested up to: 4.9.8
Buddypress: 2.8
Stable tag: 3.1
Requires PHP: 5.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Advanced geolocation, mapping, and proximity search plugin. Geotag post types and BuddyPress members, and create advanced proximity search forms.

== Description ==

Welcome to the ultimate geolocation, mapping, and proximity search solution for Wordpress.

Using GEO my WP plugin, and the powerful features of Google Maps API, you can easily geotag any of your post types, BuddyPress members and other components. Create unlimited advanced, proximity search forms to search and find any of the geotagged components of your site. 

With GEO my WP, creating a real estate, events, directory, classifieds, social networking and any other type of location based website is a simple task.

Visit [GEO my WP website](https://geomywp.com) form more information.

Check out the [live demo](demo.geomywp.com).

== Key features of GEO my WP ==

* Post Types Locator
* Buddypress Members Locator
* Advanced location form
* Advanced Proximity Search Forms Builder
* Mashup Maps
* Single Location widget and shortcode
* Current Location Shortcode and Widget
* Powered By Google Maps API
* Theme Flexibility
* Developer Friendly

= Post Types Locator = 
Add geographic location to any of the registered post types of your site. Display post location on a map, and create proximity search forms to search and find posts based on address, distance categories and more.

= Buddypress Members Locator = 
Let the Buddypress members of your site to easily add location to their profile. Let them search and find other members near them or near any address using an advanced proximity search form.

= Advanced Location Form = 
Add location to your posts, Buddypress members, and other component, using the advanced location form that GEO my WP provides. Drag the marker to the location on a map, pick from suggested results using Google address autocomplete while typing an address, enter coordinates, or manually enter the address fields. This is as flexible and accurate as it get get.

= Advanced Forms Builder = 
Create unlimited mashup maps and proximity search forms to search and find post types, BuddyPress members, and other components, based on an address, distance, categories, profile fields and more.

= Mashup Maps = 
Create unlimited mashup maps to display the location of your post types, BuddyPress members and other components.

= Powered By Google Maps API = 
GEO my WP takes full advantage of the powerful features of Google Maps API. Allows for simple and accurate geolocation using Google maps, address autocomplete, auto-locator and more.

= Single Location widget and shortcode =
Display map and location details of a single component, such as post or Buddypress member, any where on a page using shortcode or widget. 

= Current Location Shortcode and Widget =
Present the visitors of your site with a location form which allows them to add their current location.
Once added, the visitor's current location will be saved and can be used with the different features of GEO my WP. Such as setting your proximity search forms to display locations nearby the visitor's location.

= Theme Flexibility = 
Why limit yourself to a specific theme? Being a shortcode based plugin, GEO my WP has no limits and you can use it with any WordPress theme.

= Developer Friendly = 
Yes, GEO my WP is already a powerful plugin out of the box. However, together with its many action hooks and filters, the options are limitless.

== Installation ==

= Automatic Plugin Installation. To add a WordPress Plugin using the built-in plugin installer: =
* Go to Plugins > Add New.
* Under Search, type "GEO my WP".
* GEO my WP should show up on top of the results.
* Click "Install"  to install GEO my WP.
* A pop-up window will ask you to confirm your wish to install the Plugin.
* Click Proceed to continue with the installation. The resulting installation screen will list the installation as successful or note any problems during the install.
* If successful, click Activate Plugin to activate it.

= Manual plugin Installation. To install a WordPress plugin manually: =
* Download GEO my WP Plugin to your desktop.
* The plugin will downloaded as a zip. extract the Plugin folder to your desktop.
* With your FTP program, upload the Plugin folder to the wp-content/plugins folder in your WordPress directory online.
* Go to Plugins screen and find the newly uploaded Plugin in the list.
* Click Activate Plugin to activate it.

for detailed installation and setup guide see the [documentation](docs.geomywp.com).

== Frequently Asked Questions ==

* For questions, bugs report and suggestions please visit [GEO my WP Forum](https://geomywp.com/support/forums/)

== Screenshots ==

1. GEO my WP Core Extensions
2. Settings Page - General Settings Tab
3. Settings Page - Posts Locator Tab
4. Form Builder - Page Load Results Tab 1
5. Form Builder - Page Load Results Tab 2
6. Form Builder - Search Form Tab 1
7. Form Builder - Search Form Tab 2
8. Form Builder - Search Form Tab 3
9. Form Builder - Form Submission Tab 
10. Form Builder - Search Results Tab 1
11. Form Builder - Search Results Tab 2
12. Form Builder - Results Map Tab.
13. Location form ( Location Tab ) - Edit Post Page 
14. Location form ( Address Tab ) - Edit Post Page 2
15. Location form - Buddypress Member Profile Page
16. Posts Locator Search Form 1
17. Posts Locator Search Form 2
18. Posts Locator Search Form - Address Autocomplete
19. Current Location Shortcode Without Location
20. Current Location Shortcode With Location
21. Single Location Shortcode

== Changelog ==

= 3.1 =

* Version 3.1 is a major release. Read this post [GEO my WP 3.1](https://geomywp.com/geo-my-wp-3-1/) before updating.

* Version 3.0 is a major release. If you are updating from a version earlier than 3.0, it is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* New: GEO my WP now support LeafLet and OpenStreetMaps.
* New: Choose between LeafLet or Google Maps as the maps provider.
* New: settings for maps providers in GEO my WP Settings page.
* New: settings for LeafLet maps provider.
* New: Geocoder class that can be extended to support multiple geocoding providers.
* Fix: cannot publish a post when location is not provided.
* Fix: add xprofile fields to $output instead of echoing it.
* Fix: issue when ordering members locator results by newest or active.
* Fix: Single Location extension doesn't load during AJAX calls.
* Fix: Load deprecated function on both front and back end to prevent fatal errors.
* Fix: JavaScript gmGetCookie() function error.
* Fix: Excerpt not showing in info-window and search results.
* Fix: enqueue the main script in Current Location file in case it was not already loaded.
* Fix: prevent warnings when settings values do not exist in widgets.
* Fix: current location cookies are not updated properly.
* Tweak: Google Maps API key input box moved under the new Maps & Geocoder tab in the Settings page.
* Tweak: add additional user fields to gmw_get_user_location_data() function.
* Tweak: region_code and country_name fields added to the search queries.
* Tweak: geocoder file is now included by default.
* Tweak: New filters in get_locations_data() function.
* Styling: Update per-page and pagination styling in GEO my WP search results template files.
* Text: update address field option description in Current Location widget.
* Enhancement: update core to support PHP v5.4+.
* Enhancement: HTTP and client-side geocoders can be extended to use multiple providers.
* Enhancement: follow coding standards.
* Enhancement: update default map icons, which are now included in the images folder of the plugin instead of called remotely.
* Enhancement: Directions function can now be extended to work with different providers.
* Enhancement: [gmw_location_fields] shortcode and gmw_get_location_fields() function now support both address fields and location meta.

= 3.0.5 =

* Version 3.0 is a major release. If you are updating from a version earlier than 3.0, it is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* Fix: Location section in Edit Post page error. JS error caused by missing $ in ready() function.
* Fix: delete_cookie function JS error.
* New: clear location button for Current Location widget/shortcode.
* Filter: new JS filter ‘gmw_search_form_address_pre_geocoding’ to modify the address before it is geocoded.
* Tweak: Use include_once instead of include for some files to prevent errors.
* Tweak: Use array() instead of [] to support PHP 5.3.x.
Enhancement: order posts by distance then by post title. When posts have the same exact location, they will then be ordered by the post title.
* Enhancement: coding standards.
* Additional minor bugs fix.

= 3.0.4 =

* Version 3.0 is a major release. If you are updating from a version earlier than 3.0, it is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* Fix: remove support for deprecated folder names which has caused many issues. Display admin notice regarding the new folders names instead.
* Fix: clientSideGeocoder Javascript variable undefined error.
* Fix: Issue where a post will not change to "published" from "Pending" status on some browsers.
* Deprecated: Add missing [gmw_user_info] deprecated shortcode. Belongs to WP Users Locator extension.
* Tweak: Load posts locator location form file in "new" or "edit" post page only.
* Tweak: verify user_id in location default values to make sure it is not 0.
* Tweak: enqueue location form scripts and styles in the footer.
* Tweak: add back missing sweet-date search results template file.
* Style: location form messages flat styling.
* Filter: 'gmw_geocoder_raw_data' to modify raw_address before geocoding takes place.
* Filter: 'gmw_location_form_default_location' to modify the default location in the location form.
* Dev: new "preserve_submitted_values" argument in location form to preserve the submitted values after page load.

= 3.0.3 =

* Version 3.0 is a major release. If you are updating from a version earlier than 3.0, it is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* Fix: use $wpdb->base_prefix instead of $wpdb->prefix when getting single post location.
* Fix: single user location data does not return properly ( mainly when in multisite ). Now using $wpdb->* prefix instead of to $wpdb->base_prefix.
* Fix: remove PHP warnings.
* Fix: plugin's geocoder does not cache geocoded data properly.
* Fix: Address autocomplete generates JS error.
* Tweak: client-side geocoder is now enabled by default and can be disabled using the filter 'gmw_client_side_geocoder_enabled'.
* Tweak: enable plugins updater on plugin activation/update.
* Tweak: flush internal cache on plugin activation/update.
* Tweak: Remove the client-side Geocoder option from the settings page. It is now enabled by default.
* Enhancement: support deprecated custom templates folder names.
* Enhancement: coding standards.
* Function: GMW_Cache_Helper::flush_all() to flush all internal object locations.
* Security: add defined( 'ABSPATH' ) to beginning of file.

= 3.0.2 =

* Version 3.0 is a major release. If you are updating from a version earlier than 3.0, it is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* Fix: Some filters load too early.
* Fix: include GEO my WP cache helper file by default.
* Fix: results do not show on page load when using the search_results shortcode.
* Fix: pass results page permalink to the deprecated variable to support custom search form template files that were created previously to Geo my WP 3.0.
* Fix: [gmw_post_info] deprecated shortcode doesn't display location meta. Use [gmw_location_fields] instead.
* Fix: days & hours deprecated function won’t display data.
* Fix: added days & hours location meta to the v3 importer.
* Fix: In Sweet-date Geolocation load search query hook just before the members' loop, to be able to override sweet-date search query.
* Fix: Sweet-date Geolocation order by distance doesn’t work properly.
* Fix: change meta_key in the location_meta table from varchar(255) to varchar(191) to prevent errors in some environments.
* Fix: internal cache rand() number too big on some OS and results in fatal error.
* Fix: Update text domain in some files.
* Fix: Users v3 importer error when street_number and street_name columns are missing from the database table.
* Fix: v3 importer generate Javascript error that prevents the importer from working.
* Fix: Replace wp_doing_ajax function with defined( ‘DOING_AJAX’ ) to support earlier version of Wordpress.
* Fix: Various typos.
* Function: gmw_get_location_meta_values() to output specific location meta fields based on object type and object ID.
* Shortcode: [gmw_location_fields] to output specific address or location meta fields.
* New gmw_get_post_location_fields() function and [gmw_post_location_fields] shortcode, to output specific address or location meta fields of a pots.
* Enhancement: Choose between post_content and post_excerpt to use as an excerpt in search results.
* Tweak: Use the “the_content” filter in excerpt by default. This Can be changed to use ‘wpautop’, using a filter, if there is a conflict.
* Tweak: Load some deprecated functions in front*end only to prevent errors in the back*end.
* Tweak: Remove Members Locator tab on Import/export page. It is not being used at the moment.
* Styling: Adjust form editor width.
* Filter: new filter 'gmw_search_forms_submit_button_args' to modify submit button args.
* Filter: new Javascript action hook - ’gmw_map_markers_loop_single_marker’.
* Filter: new Javascript filter - ‘gmw_standard_info_window_options’
* New: option to select the post content or post excerpt as the excerpt in the list of results.

= 3.0.1 =

* Version 3.0 is a major release. It is highly recommended that you read the posts [GEO my WP 3.0 Beta 7](https://geomywp.com/geo-my-wp-3-0-beta-7/) and [GEO my WP Upgrade Process](https://geomywp.com/geo-my-wp-3-0-upgrade-process/) before updating. You should also test this version on a staging environment before updating on your live site.

* Fix: plugin will not check for new update of extensions that are not compatible with GEO my WP v3.0 when v3.0 is installed.

* Fix: Temporary bring back license key input text box to the plugins page of the admin to make it easier to update extensions that are not yet compatible with GEO my WP 3.0.

* Fix: Some deprecated functions cause fatal errors.

= 3.0 =

Version 3.0 is a major release. It is highly recommended that you read [this post](http://geomywp.com/geo-my-wp-3-0-beta-7/) before updating. You should also test this version on a staging environment before updating on your live site.

= 2.6.2 =

* Fix: Remove php warning due to non-existing post type.
* Fix: Xprofile fields will show results when not all fields matches users. Acting more like an OR rather than AND query.
* Security: Security patches added to Xprofile fields functions.
* Improvement: Improved Xprofile fields form and queries functions.
* Tweak: add chosen support for 'gmw-chosen' class when chosen exists ( to be used with premium features ).
* Tweak: Added taxonomy label name to the "All" option ( ex "All categories" ).
* Tweak: Removed custom script from the "horizontal-gray" post types search form template file that adds the taxonomy name to the "All" option of the taxonomies dropdown. It is now the plugin's default.
* New feature/class: GEO_my_WP_Cache_Helper class ( based a class taken from WP Job Manager plugin by Mike Jolly. Thank you! ). The class will help caching "expensive" database queries such as terms, taxonomies, search results and more in transient to improve the plugin's performance.
* New feature/class: GEO_my_WP_Installer to do some actions during activation and updates of the plugins.
* New filter: 'gmw_fl_xprofile_form_default_value' allow to set default values to form Xprofile fields.
* New filter: 'gmw_fl_xprofile_form_dropdown_option_all' allows to modify the "All" option of an Xprofile fields dropdown.
* New Filter: 'gmw_fl_xprofile_query_default_value' allows to set a default values for Xprofile field directly in the search query.
* New filter: 'gmw_pt_show_tax_label' allows to disable the taxonomy label.

= 2.6.1.1 =

* Compatible with WordPress 4.2.4
* Fix: Members Locator search results ordered Alphabetically instead of by distance.
* Fix: Unable to activate/deactivate Posts Locator add-on when site language is different than English.
* Fix: locator button disappear when clicked.
* Fix: load text domain properly using plugin_loaded hook.
* Fix: Remove line break in script to prevent code from "breaking".
* Tweak: admin pages title modified ( GEO my WP was removed from titles ).
* Tweak: pass $tax and $values arguments to gmw_pt_get_form_taxonomies filter.
* Tweak: Save url_px value in gmw_settings.
* Tweak: temporary set 'show_users_without_location' filter to false by default which means that members without location wont be displayed in earch results. There are issues with the no location members query and once fixed it will be set back to true by default. It is possible to enable it using add_filter( 'show_users_without_location', '_return__true' );.
* New filter: 'gmw_fl_xprofile_field_label' allows to modify the Xprofile Fields label of Members Locator search form.
* New filter: Modify the $_GET parameters before form is being generated in the front end.
* Update language files.

= 2.6.1 = 

* This is a major update. Please click <a href="https://geomywp.com/blog/geo-my-wp-2-6-1/" target="_blank">here</a> to see full version change-log before updating.

= 2.5 = 

* This is a major update. Please click <a href="https://geomywp.com/blog/geo-my-wp-2-5/" target="_blank">here</a> to see full version change-log before updating.

= 2.4.6 =

* Improved: Premium Auto-updating system. Better check on when to run the system. 
* New: Ability to disable the Premium auto-updating system. Can be used when working on a development site or when working often in the admin dashboard. Temporary disabling the system will prevent the slow load of the plugins/update pages (admin) caused by the system. The check-box responsible for the feature can be found under GEO my WP -> Settings -> Admin Settings tab. 
* Fix: warning shows in "Edit Post" page when no post types selected in the General Settings of GEO my WP. 
* Update: language files

= 2.4.5 =

* Fix: Locator button disappeared when clicked.
* Fix: Info-window wont open if additional information is disabled
* Fix: [gmw_single_location] shortcode breaks theme due to missing closing tag 
* Fix: Horizontal search form hidden checkboxes 

= 2.4.4 =

* Full version change-log can be found <a href="https://geomywp.com/blog/geo-wp-2-4-4/" target="_blank">here</a>

= 2.4.3 =

* Fix: Country code was not working with Google API
* Fix: Issues with Sweet-Date integration - Order by "alphabetical" and friends only tab was not working.
* Improvement: Location form in Member Locator "Location" Tab.
* Improvement: GEO my WP section in New Post page ( admin dashboard )
* New feature: Support for Google Places.
* New feature: language support for Google Maps and Google Places-
* Clean main Stylesheets
* Other minor bugs fix

= 2.4.2.1 =

* Compatible with WordPress 3.9 
* Compatible with BuddyPress 2.0 
* Fix: issue with Sweet-date Child theme. 
* Fix issue where map will not show when displaying only "Friends" in the Members page of Sweet-date Theme.

= 2.4.2 =

* if this is the first time you are updating to GEO my WP 2.4 it is very important that you read the update details before updating. Please refer to <a href="https://geomywp.com/blog/geo-my-wp-2-4/" target="_blank">this post</a> to read about this update and about the updating progress.

- Compatible with WordPress version 3.8.3
- Modify the way GEO my WP geocodes an address when form is being submitted - Switched back from using JavaScript to XML. The geocode function had been improved to cache results. Means that when the same address is entered it will not be geocoded again but will be used from cached when available. Geocoded results are being cached for 3 months. This will results in faster page load and less API usage. The new geocode function can be found in the file geo-my-wp.php and can be used and called as GEO_my_WP::geocoder( $address ). The old geocode function can be still found in the file geo-my-wp-geocode.php but will be removed in one of the next updates of the plugin. So please update any custom code you might have which uses the old geocode function.
- Fix: Sweet-date integration should now work with child themes as well.
- Added default settings to new created forms.
- Improved "No results" function.
- New filter added - apply_filters( 'gmw_radius_dropdown_title', $title, $gmw ) - which allows you to modify the default title that will be displayed in the radius dropdown bax.
- Modified radius dropdown output filter from apply_filters( 'gmw_search_form_radius_field', $output, $gmw, $class, $btitle, $stitle ) to apply_filters( 'gmw_radius_dropdown_output', $output, $gmw, $class );
- Modified Activity message when BuddyPress Member updates his location.
- Modified Members Locator "No Results" function
- Remove hardcoded width from Data input field (Members Locator search form)
- Improved structure of search forms and search results Stylesheet
- Added missing space when displaying taxonomies in search results
- Improve "Gray" search forms and results template files.
- New, responsive search form and results templates: Purple for "Posts Locator" and "Yellow" for "Members locator”.

= 2.4.1 = 

* if this is the first time you are updating to GEO my WP 2.4 it is very important that you read the update details before updating. Please refer to <a href="https://geomywp.com/blog/geo-my-wp-2-4/" target="_blank">this post</a> to read about this udpate and about the updating progress.

* version 2.4.1 changes:
* Fix: Add-ons page styling: fix activation button is out of the box
* Fix: Jacascript Error when submitting search form
* Fix: Radius and units "broken" elements
* New: Filter 'gmw_pt_results_taxonomy' ( gmw-pt-search-function.php line 407 ) allows you to manipulate the way the taxonomies will be displayed in results.

= 2.4 =

* This is a major update. It is very important that you read the update details before updating. Please refer to <a href="https://geomywp.com/blog/geo-my-wp-2-4/" target="_blank">this post</a> to read about this update and about the updating progress.


= 2.3 =

* fix: issue cause Google API load to fail over HTTPS ( fix provided by Chris http://wordpress.org/support/topic/fails-over-https thank you)
* added mailto link to email address fields of posts in search results
* [gmw_single_location] - can now be used in the loop to display location of each post in the loop
* [gmw_single_location] - replaced show_info attribute with additional_info. you can now pass the additional fields that will be displayed. ex [gmw_single_location additional_info="address,phone,fax,email,website"] pass the value 0 if you want to hide the additional info.
* fix: small issue with form showing results on the same page when should be in another page
* fix: Issue with Member location widget displaying "no location" message even when set to do otherwise
* Localization text
* German translation - Provided by Frank Winter - thank you!

= 2.2 =

* New, Improved GMW location section in admin's new/edit post page
* Database: Modify GMW DB tables - lat/long column change from varchar to float for better performance
* Database: GMW friends locator table - remove all deleted users from table
* remove INNER JOIN from members query
* Added function - delete user from GMW table when user being deleted from wordpress
* Fix : Single location shortcode/widget - Now Directions link and additional information can be hidden
* New "Icon drop animation" setting in shortcode.
* Better Geocoding using javascript when submitting a form
* Feature: Choose to make address fields mandatory.
* Improved queries
* Improved locator icon performance and display


= 2.1 =

* Fix : Removed textarea type Xprofile field when choosing the profile fields in the search form
* Fix : Removed special characters from Xprofile fields name in the search forms which caused issues with urls
* Fix : Add member count for buddypress results page
* map-icon folder was removed
* removed filter gmw_pt_query_meta_args which filters meta_args before wp_query and added gmw_pt_query_args filter instead which let modify the entire wp_query args

= 2.01 =

* Minor fix - Add-ons Updater System

= 2.0 =

This is a major update with many bug fixs, core modification and improvements, new features and more. Please make sure to backup your current version before updating to the new one. 
After updating you will need to go over the settings page and re-save your settings as well as the search forms as many things have changed.

**Please visit <a href="http://geomywp.com">www.geomywp.com</a> for more information before updating your plugin**
Below are some of the major changes in this version:

* Core modification - The code reduced drastically, It is much cleaner, performs better, many function are documented, the number of files and folders had been reduced and more organized.
* Admin - styling improved, better search forms page, tooltips and more...
* Search queries - The main search queries are now working directly with WordPress query (WP_query) and BuddyPress query ( BP_User_Query) which should be better for performance and will be much easier to modify the plugin to work with every theme. Now you can simply copy and paste the WordPress posts loop from the theme that you are using into the result.php page of the plugin in order to have the results page matching you theme.
* Stand alone search form -  the search form moved into its own template file, just like the results theme. Now you can have full control on the look and functionality of the search form and the results.
* Improved widgets and shortcodes.
* Warnings removed.
* New, improved pagination - new buttons, new look and fix the issue where it shows infinite pagination buttons when having many results.
* Per page value - using a drop down box you ( or the users of your site ) can now change the "per page" value live when viewing the results .
* There is no more use for the "form_only" attribute when using gmw shortcode. In order to have the results showing in a different page you will have to select the results page in the shortcode setting.
* localization - GEO my WP is now ready to be translated. There are no translated files ready yet but a default PO file is in geo-my-wp/languages. the plugin is also ready to be used with right to left languages. Please, if any of you get to translate GEO my WP it will be much appreciated if you'll share the PO and mo files so others will be able to use it as well.
* There is no more use for the shortcode [gmw_friends_results]. [gmw_results] will now serve as a results page for both posts and BuddyPress members.
* Styling - I have modified and  removed most of the CSS styling from the plugin. So if you had any custom styling you will probably lose it and will have to adapt it again.
* Renamed Files and folders
* Shortcodes name changed from "wppl" to "gmw":
  	* main shortcode - [gmw]
	* results - [gmw_results]
	* current location - [gmw_current_location]
	* single location - [gmw_single_location]
	* single member - [gmw_member_location]
* Settings in the "search form shortcodes" and other shortcodes changed as well. Now you can set the "Width" and "Height" of the maps to either pixels or percentage. in the "search form shortcodes" settings  and in the "single location" and "single member" shortcodes. for example: [gmw_single_location width="100%" height="200px"] .

= 1.7 =

* This is a major update - most of the core code has improved for better performance, security issues and many bug fix.
* Works with Wordpress 3.5. 
* SQL queries are now more secure and were modified for better performance.
* "User Current location" widget/shortcode were improved - better looking and better performance. Few bugs were fixed as well.
* Locator and cookies were improved and now working better when trying to locate a user and when saving the information via cookies. Modified to work better with different languages and fixed issues with special characters.
* buddypress - "location" tab modified - looking better and easier to work with.
* Geocode function improved when geocoding and saving information via database.
* fix issues when using Wordpress multisite - now can be use when multisite activated and fixed issue were address field will not show in the New/Edit post page.
* Now can choose the locator icon for each search form in the shortcode settings. 
* "member's location widget" - Improved and bug fixes.
* backend - visually improved.
* widgets names were changed from WPPL to GMW.
* Javascript/jQuery - improved in the backend and the front end.
* Files and folders better organized.
* Map, markers and info windows - visually improved as well as performance.

= 1.6.1 =
* Fixed bug with Buddypress' "Location" tab styling

= 1.6 =

* Bug fix - Fixed locator icon being displayed even when checkbox is unchecked 
* Bug fix - Fixed Buddypress' issue where profile fields would not display in the search form.
* Bug fix - Loading icon hold and location would not update for user enter his location in the location tab
* Bug fix - Buddypress' search result display deleted user.

= 1.5.2 =

* Bugs fix.
* Memory issues.
* New shortcode to display Buddypress member's location anywhere within a template page.
* New widget to display Buddypress member's location in the sidebar

= 1.5.1 =

* Fix issue with "User location" shortcode/widget not getting the right location.
* Fix "undefined function" error when "Friends Connection" component (buddypress settings) is unchecked.
* Fix avatar width/height in shortcode issue.


= 1.5 =

* initial release of GEO my WP - the upgraded version of wordpress places locator
* Improvement of the backend.
* Now works with Buddypress. let members add their location and look for other members near them.
* Theme color.
* Turn on/off auto locator.
* Now you can add your own locator icon.
* Much cleaner styling.
* Various bug fix.
* Code improvement.
* Better performance.
* Various new settings


= 1.3 =

* Works with wordpress 3.4.1
* Back end improvement - 5 options to choose from when adding a location.
* back end - improved code and performance.
* Autolocator feature - finds user's current location.
* User's location widget and shortcode to display user's current location.
* Choose between miles, kilometer or both when creating search form shortcode.
* To display results - Choose between Post only, map only or both when creating search form shortcode.
* Use your Google API key.
* Enter your region.
* Enter number of words for excerpt.
* Choose icon for autolocator.
* Single location map - now display additional information below the map and info window on marker click.
* Much cleaner code for better performance.
* New results styling.
* Thickbox effect on feature image click in results.

= 1.2.7 =

* bug fix - pagination will not work when updating to wordpress 3.4
* bug fix - posts that have two categories from the same taxonomy assigned to it will not show in search results. 
* bug fix - fix problem where search form will always show in the top of the page.
* CSS fix
* code improvement - better code for filtering taxonomies.
* Added Feature - Now you can use Latitude / longitude instead of address when creating/updating a post
* Added Feature - If no address entered in the input field the plugin will display all posts ordered by the title.

= 1.2.6 =

* Bug fix - Form id value is not being saved and doesn't show in widget when first creating a shortcode
* Bug fix - widget redirect to main site when plugin installed in sub-site.


= 1.2.5 =
* Code improvement.
* Widget - display a search form in the sidebar.
* Option added - Auto zoom level. will fit all markers on map.
* option added - custom Zoom level (when not auto zoom).

= 1.2.1 =
* Bug fix where new meta boxes were not updating correctly.
* pagination display improved.

= 1.2 =
* Multisite bug fix - now works for each blog on WP Multisite.
* Two styling added to choose from "default" and "blue" for each shortcode, and more to come.
* Styling for Google maps' info window.
* Meta boxes added - phone, fax, email address, website address.
* shortcode options added - show/hide feature image and show/hide excerpt.

= 1.1.2 =
* Bug fix - renamed address's $meta_boxe

= 1.1.1 =
* Bug fix
* Admin page improvements
* Shortcode to display map of a single location on single page template

= 1.1 =
* Bug fix.
* Map types added :ROADMAP,SATELLITE,HYBRID and TERRAIN.
* Change post types and taxonomies slug to names in the setting page.

= 1.0 =
* Initial release

== Upgrade Notice ==

= 3.0 =
This is a major release. You should not update before reading this post http://geomywp.com/geo-my-wp-3-0-beta-7/ before updagin.
