=== GEO my WP ===
Contributors: ninjew
Donate link: https://www.paypal.me/fitoussi
Tags: Locations Directory, Store Locator, Proximity Search forms, Posts location, Members location.
Requires at least: 5.6
Tested up to: 6.6.2
BuddyPress: 11.3.1
Stable tag: 4.5.1
Requires PHP: 7.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Advanced geolocation, mapping, and proximity search plugin. Geotag post types and BuddyPress members, and create advanced proximity search forms.

== Description ==

Welcome to the ultimate geolocation, mapping, and proximity search solution for Wordpress.

Using GEO my WP plugin, and the powerful features of Google Maps API and OpenStreetMaps, you can easily geotag any of your post types, BuddyPress members and other components. Create unlimited advanced, proximity search forms to search and find any of the geotagged components of your site.

With GEO my WP, creating a real estate, events, directory, classifieds, social networking and any other type of location-based website is a simple task.

Visit [GEO my WP website](https://geomywp.com) form more information.

Check out the [live demo](https://demo.geomywp.com).

== Key features of GEO my WP ==

* Post Types Locator
* BuddyPress Members Locator
* Advanced location form
* Advanced Proximity Search Forms Builder
* Mashup Maps
* Single Location widget and shortcode
* Current Location Shortcode and Widget
* Can be used with Google Maps API or OpenStreetMaps and LeafLet
* Theme Flexibility
* Developer Friendly

= Post Types Locator =
Add geographic location to any of the registered post types of your site. Display post location on a map, and create proximity search forms to search and find posts based on address, distance categories and more.

= BuddyPress Members Locator =
Let the BuddyPress members of your site to easily add location to their profile. Let them search and find other members near them or near any address using an advanced proximity search form.

= Advanced Location Form =
Add location to your posts, Buddypress members, and other components, using the advanced location form that GEO my WP provides. Drag the marker to the location on a map, pick from suggested results using Google address autocomplete while typing an address, enter coordinates, or manually enter the address fields. This is as flexible and accurate as it gets.

= Advanced Forms Builder =
Create unlimited mashup maps and proximity search forms to search and find post types, BuddyPress members, and other components, based on an address, distance, categories, profile fields and more.

= Mashup Maps =
Create unlimited mashup maps to display the location of your post types, BuddyPress members and other components.

= Powered By Google Maps API =
GEO my WP takes full advantage of the powerful features of Google Maps API. Allows for simple and accurate geolocation using Google maps, address autocomplete, auto-locator and more.

= LeafLet and OpenStreetMaps =
Out of the box GEO my WP also supports LeafLet and OpenStreetMaps, which you can use instead of Google Maps.

= Single Location widget and shortcode =
Display map and location details of a single component, such as a post or BuddyPress member, anywhere on a page using shortcode or widget.

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
* The plugin will be downloaded as a zip. extract the Plugin folder to your desktop.
* With your FTP program, upload the Plugin folder to the wp-content/plugins folder in your WordPress directory online.
* Go to Plugins screen and find the newly uploaded Plugin in the list.
* Click Activate Plugin to activate it.

for detailed installation and setup guide see the [documentation](https://docs.geomywp.com).

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
15. Location form - BuddyPress Member Profile Page
16. Posts Locator Search Form 1
17. Posts Locator Search Form 2
18. Posts Locator Search Form - Address Autocomplete
19. Current Location Shortcode Without Location
20. Current Location Shortcode With Location
21. Single Location Shortcode

== Changelog ==

= 4.5.1 =

* Security: verify nonce and user authorization during the gmw_get_field_options() ajax call.
* Security: sanitize, verify, and escape various functions.
* Enhancement: improve the map script loader. Make sure that Google Maps library was loaded first to prevent JavaScript errors.
* Fix: incorrect path when loading deprecated custom search results template files.
* Tweak: enhance code that checked if image exists using the function wp_get_attachment_image_src().
* Tweak: changed the location of the filter 'gmw_get_image_element_args' to allow better filtering of the image attributes.
* Tweak: enhance the 'gmw_get_field_options' ajax JS function.
* PHPCS.

= 4.5.0.4 =

* Security: Security patch.
* Fix: change the Get Directions link from HTTP to HTTPS.
* Fix: the output of the posts' address in the list of posts page of the admin's dashboard.
* Fix: Search form filters no showing properly within the Modal Box on mobile view.
* Tweak: code enhancement.
* Fix: PHP warnings.

= 4.5.0.3 =

* Fix: AJAX info-window doesn't load its content when a custom template file is selected.

= 4.5.0.3 =

* Fix: AJAX info-window doesn't load its content when a custom template file is selected.

= 4.5.0.2 =

* Fix: Security issue.
* Typos.

= 4.5.0.1 =

* Fix: AJAX info window content isn't loading for AJAX forms.
* Fix: add a short delay to the location form initiate function to allow the main script of GEO my WP and Google maps to load first and prevent JavaScript errors.
* Some premium extension requires an update to their latest version.
* Remove unused code.
* WPCS/PHPCS.

= 4.5 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Security: fix security vulnerability detected by the WordPress team.
* Security: various security/escaping enhancements.
* Fix: improve how the plugin collect the map and geocoding providers to prevent PHP warning on initial activation of the plugin.
* Fix: geolocation details are not always appended to the list of results in the BuddyPress Directory pages.
* Fix: various PHP warnings.
* Fix: add a short delay to the mapInit() function in the BuddyPress Directory pages to allow the main script of GEO my WP to load first and prevent JS errors.
* Enhancement: enhance the  map icons uploader. User wp_handle_upload() instead of move_uploaded_file().
* Enhancement: move the content of the file gmw-posts-locator-ajax-info-window-laoder.php to a function gmw_pt_ajax_info_window_loader();
* Enhancement: move the content of the file gmw-members-locator-ajax-info-window-laoder.php to a function gmw_fl_ajax_info_window_loader();.
* Enhancement: enhance the DB query of the forms table.
* Tweak: Remove the updater file from GEO my WP core plugin.
* Tweak: extend the JS file of the BuddyPress Directory pages so it can work with other objects.
* Tweak: new css_class argument added to the get_map_element() function to allow adding additional classes to the map wrapper element.
* Tweak: remove the use of deprecated second argument in get_terms().
* Tweak: temporary delete files not being used. Will be uploaded when ready.
* Tweak: enhance GMW_Forms_Table class.
* Tweak: check if some classes exists before calling them to prevent PHP errors.
* Tweak: check if the file class-gmw-plugins-updater.php exists before including it.
* Tweak: check that the class GMW_License_Key exists before executing it.
* Tweak: default value for minimizing settings in settings pages and form editor is now set to 0.
* Tweak: add geocode_address method to the GMW_BuddyPress_Directory_Geolocation class.
* Tweak: enhance how the "No results" message in the BuddyPress Directory pages is being generated.
* PHPCS.
* WPCS.

= 4.4.0.2 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: error when exporting user privacy data.
* Fix: distance value from the page load results form settings overrides the default value of the form submission settings.
* Fix: getting incorrect blog ID when in multisite installation.
* Fix: various PHP warnings.
* Tweak: replace deprecated function.

= 4.4.0.1 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: wrap the functions gmw_get_search_form_custom_fields() and gmw_get_search_form_custom_field() with function_exists to prevent PHP errors.

= 4.4 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: issue with Reset Form button doesn't clear the coordinates and other hidden fields in the form.
* Fix: incorrect output of PeepSo profile field ( user_meta ) in the search results ( for select-box field ).
* Tweak: temporary disable caching of the form object as it causes issues on some sites that have object cache enabled.
* Tweak: verify that form's data exists and valid when retrieving it from the database to prevent PHP errors/notices.
* Tweak: rename the function initMaps() to gmwInitMaps() to better identify with GEO my WP.
* Function: function 'gmw_wp_parse_args_recursive' to merge two arrays or objects that can be recursive and return an array.
* Enhancement: Keywords search box can now also search post custom fields ( in addition to post content and excerpt ) ( requires the Premium Settings extension ).
* Enhancement: the map in the Location form of GEO my WP now uses the Advanced Markers library of Google Maps API ( unless disabled in the Settings page ).
* Enhancement: the methods ::get_custom_field() and ::get_custom_fields() moved from the Premium Settings extension to the core plugin so they can be used without the Premium Settings extension and with other extensions.
* Enhancement: new method GMW_Admin::dequeue_scripts() with high priority to dequeue/deregister select2 enqueued by other plugin/themes in GEO my WP admin pages.
* Enhancement: generate maps that are generated during an AJAX call. For example, map of the Single Location shortcode that is placed inside an info-window that is loaded via AJAX.
* Enhancement: move the function gmw_get_search_form_custom_fields() from the Premium Settings ( v3.1 ) to GEO my WP core plugin to allow other extensions to make use of the functions.
* Tweak: use the "mapId" map option only when using the Advanced Markers library to allow some features like map styling still work when using the legacy markers.
* Tweak: do not hide admin "updated" notices when hiding notices in GEO my WP's admin pages.
* Tweak: new filter to disable license verification.
* Tweak: new CSS class ".gmw-is-hidden" to hide GEO my WP's elements when needed.
* Tweak: new function that gets the extensions data from remote server and local extensions merged.
* Tweak: move the function gmw_get_search_form_custom_field() from the Premium Settings extension to GEO my WP core plugin.
* Tweak: when Submit button label is omitted in teh form editor, hide the Submit button via CSS rather than completely removing it from the form which prevents form submission.
* Tweak: add short delay to gmw.map and gmw.core JavaScript files to allow Google Maps library to be fully loaded and to prevent JS errors.
* Tweak: load the Single Location classes during AJAX calls as well.
* Tweak: make search results responsive on mobile even when settings specific number of columns in the form editor.
* Tweak: load the smartbox library for select-box fields in the core plugin ( rather than the Premium Settings extension ) to allow other extensions to use it as well.
* Tweak: replace deprecated function bp_get_group_permalink() with bp_get_group_url().
* Tweak: remove the default value for the word count option of the Post Excerpt settings of the form editor to allow users to leave it blank in order to display the full content.
* Tweak: verify that $map_id is a string to prevent JavaScritp errors with Google Maps API.
* Tweak: add back the missing date/birthdate xprofile field query for BP Members Locator form.
* Tweak: new filter 'gmw_results_meta_field_value' to modify the output of a meta field in the search results.
* Tweak: new filter 'gmw_fl_member_location_tab_enabled' to disable the Location tab in BuddyPress Member Profile page.
* Tweak: new 'image_size' argument for the gmw_get_post_featured_image() to determine the image size that will be pulled from the database. default value is 'full' and can be modified using the filter 'gmw_get_post_featured_image_args'.
* Tweak: remove the 4th argument from a remove_action function ( does not exists in remove_action ).
* Tweak: set the current_tab of the form editor to 'general_settings' by default if is not set.

= 4.3.1.1 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: JavaScript error when the marker bounce animation feature is enabled ( using the Premium Settings extension ).

= 4.3.1 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: The map marker doesn't load on the map when there is only one marker ( when a single location is found in search results ) to show.
* Fix: retrieving incorrect blog ID in a subside of multi-site resulting in no members found.
* Fix: incorrect taxonomy usage variable passes to the taxonomy output in the search form when using a filter to modify the taxonomy arguments.
* Enhancement: enhance the output of the search results permalink function.
* Enhancement: enhance the way the advanced markers library loads to prevent conflicts with other plugins that use Google Maps.
* Filter: new filter 'gmw_get_search_results_permalink' to modify the permalink in the search results.
* Tweak: new settings options to enable the legacy Marker class of Google Maps instead of using the advanced markers.
* Tweak: we no longer load the advanced marker library when loading the Google Maps API key. We now load it via the JavaScript file of the maps script to better control how and when it is loaded and to prevent conflicts with other plugins that use Google Maps.
* Tweak: instead of passing the advanced marker variable via the map options, we now have it set in the main options of GEO my WP.

= 4.3 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: days & hours showing incorrectly in the results when the Multiple Locations feature is enabled.
* Fix: Select2 conflict with My Listing theme.
* Fix: conflicts with BuddyPress 12.0.
* Enhancement: switch to using Google Maps Advanced Markers instead of the legacy Marker class which is now deprecated.
* Enhancement: load Google Maps API via async to improve performance.
* Tweak: CSS to add bounce animation to Google Map Advanced markers.
* Tweak: update the Marker Clusterer library for Google Maps ( v2.5.3 ).
* Tweak: enable Google Maps Advanced Markers by default. Use the filter 'gmw_map_element' to instead enable the legacy Marker class which is now deprecated.
* Tweak: Pass the map_id to the map options.
* Tweak: replace BuddPress's deprecated function3.
* Tweak: add a check for the Youzify plugin when loading the geolocation features in the Directory pages of BuddyPress.
* Deprecated: The Markers Spiderfier option is now deprecated. This is a 3rd party library that is no longer supported by its developer and is incompatible with Google Maps Advanced Markers.

= 4.2 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: font secondary color not saving properly in GEO my WP Settings page.
* Security: add a nonce check when deleting and duplicating a form.
* Tweak: PHP notices.
* Tweak: Modify the remote URL of the usage tracking.
* Tweak: Log tracking update once a month instead of once a week.
* Tweak: when generating the class name of a GMW Locator Form, make sure the name is capitalized to prevent issues on some servers/websites.
* Tweak: Generate hyperlink when outputting a URL via meta field value in the search results.
* Tweak: enhance how the license key data is generated before verifying a license key.
* Tweak: dequeue Select-2 from the Event Tickets plugin on GEO my WP pages.
* Tweak: add space before the distance unit in the results message.
* Tweak: replace "mi" and "km" with "miles" and "kilometers" in the results message.
* Tweak: New function gmw_get_post_location_form_args( $post ) to generate the arguments of the post's location form.
* Tweak: use the function gmw_get_post_location_form_args() to generate the location form args.
* Tweak: pass the location name to the location args.
* Tweak: objects are not loading in the "Object" select box option of the Single Location widget.

= 4.1 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: conflict between GEO my WO location form and the Pods plugin in the Edit Post page of the admin's dashboard.
* Fix: issue with the "Orderby" form settings.
* New: Cache tab in the Tools page of GEO my WP to manage the internal cache of GEO my WP.
* Filter: JavaScript filter 'gmw_location_form_force_proceed_form_submission' to prevent GEO my WP from preventing the submission of the form that contains the Location form which can cause conflicts in some scenarios.
* Tweak: use the filter 'gmw_location_form_prevent_form_submission' to stop GEO my WP from preventing the form submission on the Edit Post page of the admin's dashboard.

= 4.0.4 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: The license key box isn't showing on the Plugin's page for third-party plugins that are unrelated to GEO my WP.
* Tweak: Update the styling of the license key box on the Plugins page.

= 4.0.3 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: SQL Injection vulnerability in the Forms page of GEO my WP ( in the admin's dashboard ).
* Fix: some forms with a custom search form template files redirect to a 404 page on submission.
* Fix: PHP notice.

= 4.0.2 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: assets not loading when only the Single Location shortcode is on the page. The action 'gmw_element_loaded' was missing.
* Tweak: Update settings page styling.
* Tweak: The checkbox type option in the settings pages of GEO my WP can now be set as a standard checkbox rather than a toggle.
* Tweak: Enhance code for BP Profile Search Geolocation extension.

= 4.0.1 =

* Please follow the steps below if you are updating from GEO my WP version 3.x:
  - please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating to GEO my WP version 4.x on your site.
  - GEO my WP v4.0 is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
  - VERY IMPORTANT: When updating to GEO my WP v4.x, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* Fix: JavaScript error in the form editor page prevents toggling of the form settings.
* Fix: 403 error on some servers when trying to load the marker's info-window via AJAX.
* Fix: a conflict with WP_Query when object cache is enabled causes to show no results when searching for posts.
* Security: fix security issue found in the Single Location shortcode.

= 4.0 =

* Please read [this post](https://geomywp.com/geo-my-wp-v4-0/) before updating GEO my WP on your site.
* This is a major release. It is highly recommended that you either test it on a staging and/or make a complete backup of your site before installing it on a live site.
* VERY IMPORTANT: When updating to the new version, you need to first update the premium extensions of GEO my WP and only then update GEO my WP core plugin. Not following these steps might cause a fatal error on your site.
* The current version of the premium extensions are not compatible with GEO my WP. So you will need to update your premium extensions after updating to GEO my WP v4.0.

== Upgrade Notice ==

= 3.0 =
This is a major release. You should not update before reading this post http://geomywp.com/geo-my-wp-3-0-beta-7/ before updating.
