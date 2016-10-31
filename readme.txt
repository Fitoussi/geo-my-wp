=== GEO my Wordpress ===
Contributors: ninjew
Donate link: www.paypal.me/fitoussi
Tags: Geo-location, Geolocation, proximity search, zipcode search, radius search, store locator, Geolocate posts, address search, search distance, google v3 , Google maps, get directions, search locations, Geolocate, GEO, members locator, Geolocate memebrs, mapping, mapping software, latitude, longitude, locations finder, map creator.
Requires at least: 4.2
Tested up to: 4.6.1
Buddypress: 2.1.1
Stable tag: 2.6.6.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Using Google's API tools GEO my WP provides an advance proximity search for any post type or buddypress's members based on a given address and radius.

== Description ==

GEO my WP is the complete GEO solution for your Wordpress project. 
Using google API tool GEO my WP let you add location to any of your post types, pages or BuddyPress members. After adding your locations you can create an advance search form including radius values, units (miles and kilometers) and custom taxonomies for post types or profile fields for Buddypress. Results will be displayed based on the address entered and the chosen radius ordered by the distance.
Together with other great features like auto locating user's current location, displaying driving distance, "get directions" link, google map with markers of the location and much more, GEO my WP just might be the coolest GEO tool for WordPress.

= Key features of GEO my WP =

* Works with posts, post types and pages - Add location to any of your post, post types or pages.
* Works with Buddypress - Buddypress members can add their location.
* GEO my WP let you search by city, zip code or any given form of address.
* Use auto locator to get user's current location.
* Use the auto locator to automatically display results near user's location.
* Search within any radius chosen from a dropdown menu.
* For post types - Use categories (custom taxonomies) to filter results.
* For Buddypress - Use profile fields for complex results filtering.
* Search by miles or kilometers
* Display Google map with the markers of the locations and information window for each marker.
* Display the exact driving distance using Google's API.
* "Get directions" link opens a new window with the driving directions to the location.
* Pagination - choose the number of results per page.

= General settings =

* User friendly backend where each feature and setting documented.
* Enter your Google API key
* Choose your region.
* Choose if automatically gets user's current location when first visits the site.
* Choose autolocator icon or add your own.
* Choose the Post types where you want to add location.
* Choose the theme color that will control the Title, links and address in the results.

= "New/Update" page in admin area: =

* Address fields are automatically created for the chosen post types.
* Meta boxes for phone number, fax number, email address and website that will show in results and in map's information windows.
* Meta boxes for opening days & hours.
* Make address fields mandatory to make sure that users enter an address when creating a new post.
* 5 different way to enter address and lat/long:
* Autolocate the current location.
* Autocomplete input fields that get results from google.
* Drag and drop marker on the map to choose the location.
* Enter address manually and return lat/long
* Enter lat/long manually and return the address

= Buddypress =

* Add new "location" tab to member's profile page. 
* Members can easily add their location.
* Create an advance search form to locate members near a certain address.

= Shortcodes =

* Shortcode for single location - displays map of a single location.
* Shortcode for user's location- display the user's location everywhere on the template. Can choose between zipcode or city. and can choose if to display user's name when logged in.

Forms builder in the admin settings make it easier to build you own forms; And you can build as many as you wish.

* Choose between post type or buddypress shortcose. 
* Post type Forms accept single or multiple post types that will appear in a dropdown menu.
* For single post type you can add the categories of the post type to filter results.
* For buddypress you can choose any or all profile fields to filter results.
* Choose between Miles, Kilometers or both in a dropdown.
* Choose the radius values.
* Results output - Display Post only, map only or both.
* Display Google's map with markers and define its height and width.
* Choose between autozoom the map (show all markers) or manually choose the zoom value.
* Choose map type: ROADMAP,SATELLITE,HYBRID and TERRAIN
* Show/hide exact driving distance.
* Show/hide "get directions" link.
* Number of results per page.
* Show/hide feature image
* Show/hide excerpt and number of words.
* Different results styling to choose from.
* and more.....

= Widgets =

* Search form widget to display any search from in the sidebar.
* User's location widget to displays the user's location in the sidebar.
* Buddypress Member's location.

= Languages =
GEO my WP is currently available in the following languages:

* German ( by [Frank Winter](http://www.socialmedia4all.net) )

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

for detailed installation and setup guied click <a href="http://geomywp.com" >here</a>

== Frequently Asked Questions ==

= For questions, bugs report and suggestions please visit [GEO my WP Furom](http://geomywp.com/support/forums/) =

== Screenshots ==

For screenshots please visit [GEO my WP](http://www.geomywp.com)

== Other Notes ==

== Changelog ==

= 2.6.6.3 =

* Fix: single location map error.
* Remove PHP warnings.

= 2.6.6.2 =

* Security patch.

= 2.6.6.1 =

* Remove debugging text

= 2.6.6 =

* Fix: Per page feature not working on page load results.
* Fix: issue with marker clusters not zooming in all the way when multiple markers are on the same exact location.
* Fix: spread markers that are on the same exact location. This should overcome the issue where multiple markers that are on the same exact location are not showing properly by slightly moving them from their original location.
* Filters: new filters allow to modify the activity update arguments and text.
* Tweak: added support for Buddypress Xprofile Custom Fields Type plugin. At the moment this only supports the "select_custom_post_type" field as a beta test. The support for other fields might be added in future version.

= 2.6.5 =

* Fix: marker clusters images.
* Fix: Issue with
* Fix: Single Location get directions link.
* Fix: Markers Cluster Images link
* Update Markers Cluster library

= 2.6.4 =

* Fix: Custom Zoom level issue..
* Tweak: Pass item_id to the license updater.
* Tweak: remove street_number and street_name field when updating user location. This is a temporary solution to prevent issues for installation that memebrs database table was not updated properly. This will be fixed in the next major update of GEO my WP.

= 2.6.3 =

* Fix: per page function.
* Fix: verify if info-window content is false || null to prevent errors.
* Fix: remove warning in "Forms" page when no forms exist.
* Fix: Street name and Street number switched when updating post type location.

= 2.6.2.2 =

* Fix: Allow decimals for radius values.

= 2.6.2.1 =

* Fix: issue with pagination not working properly.
* Fix: Members Locator get no results due to missing 'feature' column in database

= 2.6.2 =

* Fix: Remove php warning due to non-existing post type.
* Fix: xprofile fields will show results when not all fields matches users. Acting more like an OR rather than AND query.
* Security: Security patches added to xprofile fields functions.
* Improvment: Improved xprofile fields form and queries functions.
* Tweak: add chosen support for 'gmw-chosen' class when chosen exists ( to be used with premium features ).
* Tweak: Added taxonomy lable name to the "All" option ( ex "All categories" ).
* Tweak: Removed custom script from the "horizontal-gray" post types search form template file that adds the taxonomy name to the "All" option of the taxonomies dropdown. It is now the plugin's default.
* New feature/class: GEO_my_WP_Cache_Helper class ( based a class taken from WP Job Manager plugin by Mike Jolly. Thank you! ). The class will help caching "expensive" databse queries such as terms, taxonomies, search results and more in transient to improve the plugin's performance.
* New feature/class: GEO_my_WP_Installer to do some actions during activation and updates of the plugins.
* New filter: 'gmw_fl_xprofile_form_default_value' allow to set default values to form xprofile fields.
* New filter: 'gmw_fl_xprofile_form_dropdown_option_all' allows to modify the "All" option of an xprofile fields dropdown.
* New Filter: 'gmw_fl_xprofile_query_default_value' allows to set a default values for xprofile field directly in the search query.
* New filter: 'gmw_pt_show_tax_label' allows to disable the taxonomy label.

= 2.6.1.1 =

* Compatible with WordPress 4.2.4
* Fix: Members Locator search results ordered Alphabetically instead of by distance.
* Fix: Unable to activate/diactivate Posts Locator add-on when site language is different than English.
* Fix: locator button disappear when clicked.
* Fix: load text domain properly using plugin_loaded hook.
* Fix: Remove line break in script to prevent code from "breaking".
* Tweak: admin pages title modifyed ( GEO my WP was removed from titles ).
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

* Full version changelog can be found <a href="https://geomywp.com/blog/geo-wp-2-4-4/" target="_blank">here</a>

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

* This is a major update. It is very important that you read the update details before updating. Please refer to <a href="https://geomywp.com/blog/geo-my-wp-2-4/" target="_blank">this post</a> to read about this udpate and about the updating progress.


= 2.3 =

* fix: issue cause Google API load to fail over HTTPS ( fix provided by Chris http://wordpress.org/support/topic/fails-over-https thank you)
* added mailto link to email address fields of posts in search results
* [gmw_single_location] - can now be used in the loop to display location of each post in the loop
* [gmw_single_location] - replaced show_info attribute with additional_info. you can now pass the additional fields that will be displayed. ex [gmw_single_location additional_info="address,phone,fax,email,website"] pass the value 0 if you want to hide the additional info.
* fix: small issue with form showing results on the same page when should be in another page
* fix: Issue with Member location widget displaying "no location" message even when set to do otehrwise
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

* Fix : Removed textarea type xprofile field when choosing the profile fields in the search form
* Fix : Removed special characters from xprofile fields name in the search forms which caused issues with urls
* Fix : Add member count for buddypress results page
* map-icon folder was removed
* removed filter gmw_pt_query_meta_args which filters meta_args before wp_query and added gmw_pt_query_args filter instead which let modify the entire wp_query args

= 2.01 =

* Minor fix - Add-ons Updater System

= 2.0 =

This is a major update with many bug fixs, core modification and improvements, new features and more. Please make sure to backup your current version before updating to the new one. 
After updating you will need to go over the settings page and re-save your settings as well as the search forms as many things have changed.

**Please vist <a href="http://geomywp.com">www.geomywp.com</a> for more information before updating your plugin**
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
* ettings in the "search form shortcodes" and other shortcodes changed as well. Now you can set the "Width" and "Height" of the maps to either pixels or percentage. in the "search form shortcodes" settings  and in the "single location" and "single member" shortcodes. for example: [gmw_single_location width="100%" height="200px"] .

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
* Fixed bug with Buddypress's "Location" tab styling

= 1.6 =

* Bug fix - Fixed locator icon being displayed even when checkbox is unchecked 
* Bug fix - Fixed Buddypress's issue where profile fields would not display in the search form.
* Bug fix - Loading icon hold and location would not update for user enter his location in the location tab
* Bug fix - Buddypress's search result display deleted user.

= 1.5.2 =

* Bugs fix.
* Memory issues.
* New shortcode to display Buddypress member's location anywhere within a template page.
* New widget to display Buddypress member's location in the sidebar

= 1.5.1 =

* Fix issue with "User location" shortcode/widget not getting the right location.
* Fix "undefined function" error when "Friends Connection" component (buddypress settings) is unchecked.
* Fix avater width/height in shortcode issue.


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
* Thickbox effect on feaure image click in results.

= 1.2.7 =

* bug fix - pagination will not work when updating to wordpress 3.4
* bug fix - posts that have two categories from the same taxonomy assined to it will not show in search results. 
* bug fix - fix problem where search form will always show in the top of the page.
* CSS fix
* code improvement - better code for filtering taxonomies.
* Added Feature - Now you can use Latitude / longitude insted of address when creating/updating a post
* Added Feature - If no address entered in the input field the plugin will display all posts ordered by the title.

= 1.2.6 =

* Bug fix - Form id value is not being saved and doesn't show in widget when first creating a shortcode
* Bug fix - widget redirect to main site when plugin installed in subsite.


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
* Two styling added to choose from "default" and "blue" for each shortcodeand. more to come.
* Styling for Google maps' info window.
* Meta boxes added - phone, fax, email address, website address.
* shortcode options added - show/hide feature image and show/hide excerpt.

= 1.1.2 =
* Bug fix - renamed address's $meta_boxe

= 1.1.1 =
* Bug fix
* Admin page improvments
* Shortcode to display map of a single location on single page template

= 1.1 =
* Bug fix.
* Map types added :ROADMAP,SATELLITE,HYBRID and TERRAIN.
* Change post types and taxonomies slug to names in the setting page.

= 1.0 =
* Initial release


== Upgrade Notice ==