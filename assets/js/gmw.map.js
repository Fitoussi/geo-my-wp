// global maps object 
var GMW_Maps = {};

/**
 * Map generator function
 * 
 * @param {[type]} map_id [description]
 * @param {[type]} vars  [description]
 */
var GMW_Map = function( vars ) {

	/**
	 * [self description]
	 * @type {[type]}
	 */
	var self = this;

	/**
	 * Map ID
	 * @type {integer}
	 */
	var map_id = vars.args.map_id;

	/**
	 * Map args
	 * @type {array}
	 */
	var map_args = vars.args;

	/**
	 * Map options
	 * @type {array}
	 */
	var map_options = vars.map_options;

	/**
	 * Locations
	 * @type {array}
	 */
	var locations = vars.locations;

	/**
	 * User position
	 * @type {array}
	 */
	var user_position = vars.user_position;

	/**
	 * GEO my WP Form 
	 * @type {array}
	 */
	var form = vars.form;

	/**
	 * Default map data
	 * @type {Object}
	 */
	var map_data = {
		id 		  	  	: map_id,
		map   		  	: false,
		markers 	  	: [],
		user_position 	: false,
		userMarker	  	: false,
		clusters	  	: false,
		spiderfier    	: false,
		location_iw	  	: false,
		userInfoWindow	: false,
		bounds 		  	: new google.maps.LatLngBounds(),
		infobox  	    : null,
		currentMarker   : null,
		autoZoomLevel   : false
	};

	/**
	 * Init function
	 * 
	 * @return void
	 */
	this.init = function() {
 		
		// abort if map element not exist
		if ( ! jQuery( '#' + map_args['map_element'] ).length ) {
			return;
		}

		// Show map element if hidden.
		jQuery( map_args['map_wrapper'] ).slideDown( 'slow', function() {
			
			// check if map already exist. Otherwise, genertate it.
			if ( ! map_data['map'] ) {
	
				self.render_map();

			// otherwise update existing map
			} else {

				self.update_map();
			}
		});
	};

	/**
	 * Render the map
	 * 
	 * @return {[type]} [description]
	 */
	this.render_map = function() {
		
		// set auto zoom level
		if ( map_options['zoomLevel'] == 'auto' ) {

			map_data['autoZoomLevel'] = true;
			map_options['zoomLevel'] = 13;
		
		// otherwise specifiy the zoom level
		} else {

			map_data['autoZoomLevel'] = false;
			map_options['zoomLevel'] = parseInt( map_options['zoomLevel'] );
		}
 
		// map center
		//map_options['center'] = new google.maps.LatLng( user_position['lat'], user_position['lng'] );
		map_options['center'] = new google.maps.LatLng( '40.758895', '-73.985131' );

		// map type
		map_options['mapTypeId'] = google.maps.MapTypeId[map_options['mapTypeId']];
									
		// generate the map element
		map_data['map'] = new google.maps.Map( 
			document.getElementById( map_args['map_element'] ),
			map_options 
		);
	
		// after map was generated
		google.maps.event.addListenerOnce( map_data['map'], 'idle', function(){	
			
			// fadeout the map loader
			jQuery( '#gmw-map-loader-' + map_id ).fadeOut( 1000 );
			
			// create map expand toggle if needed
			// temporary disabled. It seems that Google added this feature to his API
			if ( map_options['resizeMapControl'] && jQuery( '#gmw-resize-map-toggle-' + map_id ).length != 0 ) {

				// generate resize toggle
				map_data['resize_map_control'] = document.getElementById( 'gmw-resize-map-toggle-' + map_id );
				map_data['resize_map_control'].style.position = 'absolute';	
				map_data['map'].controls[google.maps.ControlPosition.TOP_RIGHT].push( map_data['resize_map_control'] );			
				map_data['resize_map_control'].style.display = 'block';
			
				// resize map on click event	
		    	jQuery( '#gmw-resize-map-toggle-' + map_id ).on( 'click', function() {
		 	
		 			// get the current map center
		    		var mapCenter = map_data['map'].getCenter();

		    		// replace map wrapper class to expended
		    		jQuery(this).closest( '.gmw-map-wrapper' ).toggleClass( 'gmw-expanded-map' ); 

		    		// replace the toggle icon         		
		    		jQuery(this).toggleClass( 'gmw-icon-resize-full' ).toggleClass( 'gmw-icon-resize-small' );
		    		
		    		// we wait a very short moment to allow the wrapper element resize
		    		setTimeout( function() { 	

		    			// resize map		    		
		    			google.maps.event.trigger( map_data['map'], 'resize' );

		    			// recenter map
		    			map_data['map'].setCenter(mapCenter);		

					}, 100 );            		
		    	});
			}

			// clear existing markers
			self.clear_markers();

			// generate new markers
			self.render_markers();
		});
	};

	/**
	 * Update existing map
	 * 
	 * @param  {[type]} mapVars [description]
	 * @return {[type]}         [description]
	 */
	this.update_map = function() {

		// clear existing markers
		self.clear_markers();

		// generate new markers
		self.render_markers();
	}

	/**
	 * Remove markers if exists on the map
	 * 
	 * @return {[type]} [description]
	 */
	this.clear_markers = function() {

		// loop through existing markers
		for ( var i = 0; i < map_data['markers'].length + 1 ; i++ ) {

			if ( i < map_data['markers'].length ) {

				//if ( typeof map_data['markers'][i] !== 'undefined' ) {
				
				// verify marker
				if ( map_data['markers'][i] ) {

					// clear marker
					map_data['markers'][i].setMap( null );	
				}
				//}
				
			// proceed when doen
			} else {

				// generate new markers array.
				map_data['markers'] = [];
				
				// clear group markers
				this.group_markers();
			}
		}
	};

	/**
	 * Initilize markers grouping. 
	 * 
	 * markers clusters, spiderfier and can be extended
	 * 
	 * @return {[type]} [description]
	 */
	this.group_markers = function() {
		
		// hook custom functions if needed
		GMW.do_action( 'gmw_map_group_markers', map_args['group_markers'], self );

		// generate the grouping function
		var groupMarkerFunction = 'group_' + map_args['group_markers'];

		// verify grouping function
		if ( typeof self[groupMarkerFunction] === 'function' ) {

			// run marker grouping
			self[groupMarkerFunction]( self );
		
		// otherwise show error message
		} else {

			console.log( 'The function ' + groupMarkerFunction + ' not exists.' );
		}
	};

	/**
	 * normal grouping function holder
	 *
	 * since there is no normal grouping we do nothing here. 
	 * 
	 * this is just a function holder. 
	 * 
	 * @return void
	 * 
	 */
	this.group_normal = function() {}

	/**
	 * Markers Clusterer grouping
	 *  
	 * @param  {[type]} group_markers [description]
	 * @param  {[type]} mapObject     [description]
	 * @return {[type]}               [description]
	 */
	this.group_markers_clusterer = function( mapObject ) {
		
		// initialize markers clusterer if needed and if exists
	    if ( typeof MarkerClusterer === 'function' ) {

	    	// remove existing clusters
	    	if ( map_data['clusters'] != false ) {		

	    		map_data['clusters'].clearMarkers();
	    		//data['clusters'].setMap( null );
	    	}
	    	
	    	//create new clusters object
			map_data['clusters'] = new MarkerClusterer( 
				map_data['map'], 
				map_data['markers'] 
			);
		} 
	}

	/**
	 * Create markers loop
	 * 
	 * @return {[type]} [description]
	 */
	this.render_markers = function() {

		var locations_count = locations.length;

		// loop through locations
		for ( i = 0; i < locations_count + 1 ; i++ ) {  
			
			// generate markers
			if ( i < locations_count ) {

				// verify location coordinates
				if ( locations[i]['lat'] == undefined || locations[i]['lng'] == undefined || locations[i]['lat'] == '0.000000' || locations[i]['lng'] == '0.000000' ) {
					continue;
				}
				
				// generate the marker position
				var marker_position = new google.maps.LatLng( 
					locations[i]['lat'], 
					locations[i]['lng'] 
				);
				
				// append location into bounds
				map_data['bounds'].extend( marker_position );
	
			    // generate marker
				map_data['markers'][i] = new google.maps.Marker({
					position : marker_position,
					icon     : locations[i]['map_icon'],
					map      : map_data['map'],
					id       : i 
				});
				
				// if no grouping
				if ( map_args['group_markers'] == 'normal' ) {		

					map_data['markers'][i].setMap( map_data['map'] );

					// init marker click event
					google.maps.event.addListener( map_data['markers'][i], 'click', function() {
						self.marker_click( this )
					});

				// add marker to cluster if needed
				} else if ( map_args['group_markers'] == 'markers_clusterer' ) {	

					// add marker to cluster object
					map_data['clusters'].addMarker( map_data['markers'][i] );	

					// init marker click event
					google.maps.event.addListener( map_data['markers'][i], 'click', function() {
						self.marker_click( this )
					});	
				
				// add marker to spiderfier if needed
				} else if ( map_args['group_markers'] == 'markers_spiderfier' ) {	

					// add marker into spiderfier object
					map_data['spiderfier'].addMarker( map_data['markers'][i] );

					// place marker on the map
					map_data['markers'][i].setMap( map_data['map'] );	
				}

				// hook custom function for custom grouping and others
				GMW.do_action( 'gmw_map_single_marker', map_data['markers'][i], self );

			// Continue when done generating the markers.
			} else {

				// display the user position
				self.user_position();
			}
		}

	};

	/**
	 * User position
	 *
	 * Create the user's marker and info window
	 * 
	 * @return {[type]} [description]
	 */
	this.user_position = function() {

		// remove existing user marker
		if ( map_data['userMarker'] != false ) {
			map_data['userMarker'].setMap( null );
			map_data['userMarker']    = false;
			map_data['user_position'] = false;
		}

		// generate new user location marker
		if ( user_position['lat'] && user_position['lng'] && user_position['map_icon'] != '0' && user_position['map_icon'] != '' ) {

			// generate user's position
			map_data['user_position'] = new google.maps.LatLng( 
				user_position['lat'], 
				user_position['lng'] 
			);
			
			// append user position to bounds
			map_data['bounds'].extend( map_data['user_position'] );
			
			// generate user marker
			map_data['userMarker'] = new google.maps.Marker({
				position : map_data['user_position'],
				map      : map_data['map'],
				icon     : user_position['map_icon']
			});
			
			// generate info-window if content exists
			if ( user_position['iw_content'] != false && user_position['iw_content'] != null ) {

				// generate info-window
				map_data['userInfoWindow'] = new google.maps.InfoWindow({
					content : user_position['iw_content']
				});
			      					
			    // open info window on map load
				if ( user_position['iw_open'] == true ) {

					map_data['userInfoWindow'].open( 
						map_data['map'], 
						map_data['userMarker'] 
					);
				}
				
				// open info window on marker click
			    google.maps.event.addListener( map_data['userMarker'], 'click', function() {

			    	map_data['userInfoWindow'].open( 
			    		map_data['map'], 
			    		map_data['userMarker'] 
			    	);
			    });     
			}

			// center map when done creating the users position
			self.center_map();

		// otherwise, just center the map
		} else {

			// center map
			self.center_map();
		}		
	}

	/**
	 * center and zoom map
	 * 
	 * @return {[type]} [description]
	 */
	this.center_map = function() {

		// custom zoom point
		if ( map_args['zoom_position'] != false && ! map_data['autoZoomLevel'] ) {

			// get position
			var latLng = new google.maps.LatLng( 
				map_args['zoom_position']['lat'], 
				map_args['zoom_position']['lng'] 
			);

			map_data['map'].setZoom( parseInt( map_options['zoomLevel'] ) );
			map_data['map'].panTo( latLng );

		// zoom map when a single marker exists on the map
		} else if ( locations.length == 1 && map_data['user_position'] == false ) {

			if ( map_data['autoZoomLevel'] ) {
				map_data['map'].setZoom( 13 );
			} else {
				map_data['map'].setZoom( parseInt( map_options['zoomLevel'] ) );
			}

			map_data['map'].panTo( map_data['markers'][0].getPosition() );

		} else if ( ! map_data['autoZoomLevel'] && map_data['user_position'] != false ) {

			map_data['map'].setZoom( parseInt( map_options['zoomLevel'] ) );
			map_data['map'].panTo( map_data['user_position'] );

		} else if ( map_data['autoZoomLevel'] || map_data['user_position'] == false  ) { 
		
			map_data['map'].fitBounds( map_data['bounds'] );
		}
	}

	/**
	 * Marker click event
	 * 
	 * @param  {[type]} marker [description]
	 * 
	 * @return {[type]}        [description]
	 */
	this.marker_click = function( marker ) {

		map_data['currentMarker'] = marker;

		// Clear directions if set on the map 
		if ( typeof directionsDisplay !== 'undefined' ) {
			directionsDisplay.setMap( null );
		}

		// hook custom functions
		GMW.do_action( 'gmw_map_marker_click', marker, self );
		
		// generate the click function
		var markerClickFunction = 'marker_click_' + map_args['info_window_type'];

		// verify marker click function
		if ( typeof self[markerClickFunction] === 'function' ) {

			// execute marker click event
			self[markerClickFunction]( marker, self );
		
		// show an error if function is missing
		} else {
			console.log( 'The function ' + markerClickFunction + ' not exists.' );
		}

	};

	/**
	 * Normal info window 
	 * 
	 * @param  {[type]} marker    [description]
	 * @param  {[type]} iw_type   [description]
	 * @param  {[type]} mapObject [description]
	 * @return {[type]}           [description]
	 */
	this.marker_click_normal = function( marker ) {

		// verify iw content
		if ( locations[marker.id]['info_window_content'] ) {
			
			// close open window
			if ( map_data['location_iw'] ) {
				map_data['location_iw'].close();
				map_data['location_iw'] = null;
			}
			
			// generate new window
			map_data['location_iw'] = new google.maps.InfoWindow({
				content  : locations[marker.id]['info_window_content'],
				maxWidth : 300
			});
		
			// open window
			map_data['location_iw'].open( 
				map_data['map'], 
				marker 
			);
		}
	}
}

/**
 * On document ready generate all maps exists in the global maps holder
 * 
 * @param  {GMW_Map}
 * @return {[type]}       [description]
 */
jQuery( document ).ready( function($){ 	

	var GMW_Maps_Object = typeof gmwMapObjects != 'undefined' ? gmwMapObjects : {};

	// loop through and generate all maps
	jQuery.each( GMW_Maps_Object, function( map_id, vars ) {	

		// generate new map
		GMW_Maps[map_id] = new GMW_Map( vars );
		// initiate it
		GMW_Maps[map_id].init();
	});
});