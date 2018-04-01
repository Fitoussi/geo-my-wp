// global maps object 
var GMW_Maps = {};

/**
 * Map generator function
 * 
 * @param {[type]} map_id [description]
 * @param {[type]} vars  [description]
 */
var GMW_Map = function( options, map_options, form ) {

	/**
	 * Map ID
	 * 
	 * @type {[type]}
	 */
	this.id = options.map_id;

	/**
	 * Prefix
	 * 
	 * @type {[type]}
	 */
	this.prefix = options.prefix;

	/**
	 * Map settings 
	 * 
	 * @type {[type]}
	 */
	this.settings = options;

	/**
	 * GMW form being processed
	 * @type {[type]}
	 */
	this.gmw_form = form || false;

	/**
	 * Map wrappr element
	 * 
	 * @type {String}
	 */
	this.wrap_element = jQuery( '#gmw-map-wrapper-' + this.id );

	/**
	 * Map's DIV element 
	 * 
	 * @type {[type]}
	 */
	this.map_element = 'gmw-map-' + this.id;

	/**
	 * Map options
	 * 
	 * @type {[type]}
	 */
	this.options = map_options || {
		zoom      : 8,
		mapTypeId : 'ROADMAP'
	};

	/**
	 * Map object
	 * 
	 * @type {Boolean}
	 */
	this.map = false;
		
	/**
	 * Locations array
	 * 
	 * @type {Array}
	 */
	this.locations = [];

	/**
	 * Previous locations - when using paginations
	 * 
	 * @type {Array}
	 */
	this.previous_locations = [];

	/**
	 * Map markers array
	 * 
	 * @type {Array}
	 */
	this.markers = [];
	
	/**
	 * Map icon size
	 * 
	 * @type {google}
	 */
	this.icon_scaled_size = options['map_icon_width'] && options['map_icon_height'] ? new google.maps.Size( parseInt( options['map_icon_width'] ), parseInt( options['map_icon_height'] ) ) : null;

	/**
	 * Hide map if no locations found
	 * 
	 * @type {Boolean}
	 */
	this.hide_no_locations = options['hide_no_locations'] || false;

	/**
	 * Location info-window
	 * 
	 * @type {Boolean}
	 */
	this.active_info_window = null;

	/**
	 * User location data
	 * 
	 * @type {Boolean}
	 */
	this.user_location = false;

	/**
	 * User position
	 * 
	 * @type {Boolean}
	 */
	this.user_position = false;
	
	/**
	 * User's location info window
	 * 
	 * @type {Boolean}
	 */
	this.user_info_window = false;

	/**
	 * Markers clusterer PATH
	 * @type {String}
	 */
	this.clusters_path = options['clusters_path'] || this.default_cluster_path;

	/**
	 * Marker grouping type
	 * 
	 * @type {String}
	 */
	this.grouping_type = options['group_markers'] || 'standard';

	/**
	 * Info window type
	 * 
	 * @type {String}
	 */
	this.info_window_type = options['info_window_type'] || 'standard';

	/**
	 * IW Ajax Content
	 * 
	 * @type {[type]}
	 */
	this.info_window_ajax = options['info_window_ajax'] || false;

	/**
	 * IW Ajax Content
	 * 
	 * @type {[type]}
	 */
	this.info_window_template = options['info_window_template'] || 'default';
			
	/**
	 * User location map marker
	 * 
	 * @type {Boolean}
	 */
	this.user_marker = false;

	/**
	 * markers Clusters 
	 * @type {Boolean}
	 */
	this.clusters = false;
			
	/**
	 * Bounds object
	 * 
	 * @type {Boolean}
	 */
	this.bounds = false;
			
	/**
	 * Current marker - the marker clicked
	 * @type {[type]}
	 */
	this.active_marker = null;
	
	/**
	 * Polylines holder
	 * @type {Array}
	 */
	this.polylines = [];

	/**
	 * Is auto zoom enable
	 * 
	 * @type {Boolean}
	 */
	this.auto_zoom_level = false;

	/**
	 * Custom zoom position
	 * 
	 * @type {Boolean}
	 */
	this.zoom_position = options['zoom_position'] || false;

	this.init();
	/**
	 * Init function
	 * 
	 * @return void
	 */
	/*this.init = function( locations, user_data ) {
 		
		var self = this;

		if ( typeof locations !== 'undefined' ) {
			self.locations = locations;
		}

		if ( typeof user_data !== 'undefined' ) {
			self.user_location = user_data;
		}

		GMW.do_action( 'gmw_map_init', self.id, self );

		// Generate new map if not already exists	
		if ( self.map == false ) {
			
			self.render( self.locations, self.user_location );

		// otherwise, update existing map
		} else {
			
			self.update( self.locations, self.user_location );
		}
	};*/
}

GMW_Map.prototype.default_cluster_path = 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/gh-pages/images/m';
	
GMW_Map.prototype.init = function() {
	GMW.do_action( 'gmw_map_init', this );
}

/**
 * Render the map
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.render = function( locations, user_location ) {
	
	//console.log( 'render map' );

	var self = this;

	// abort if map element not exist
	if ( ! jQuery( '#' + self.map_element ).length ) {
		return;
	}

	self.locations = locations || self.locations;

	self.user_location = user_location || self.user_location;

	self.bounds = new google.maps.LatLngBounds();

	// set auto zoom level
	if ( self.options['zoom'] == 'auto' ) {

		self.auto_zoom_level = true;
		self.options.zoom 	 = 13;
	
	// otherwise specifiy the zoom level
	} else {

		self.auto_zoom_level = false;
		self.options['zoom'] = parseInt( self.options['zoom'] );
	}

	//self.options.styles = ;

	// map center
	//if ( self.user_location != false && self.user_location['lat'] != false && self.user_location['lng'] != false
	//self.options['center'] = new google.maps.LatLng( user_position['lat'], user_position['lng'] );
	self.options['center'] = new google.maps.LatLng( '40.758895', '-73.985131' );

	// map type
	self.options['mapTypeId'] 			  = google.maps.MapTypeId[self.options['mapTypeId']];
	self.options['mapTypeControlOptions'] = {
	    style    : google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
	    position : google.maps.ControlPosition.TOP_CENTER
	}
    self.options['zoomControlOptions'] = {
      	position : google.maps.ControlPosition.RIGHT_CENTER
    }
	self.options['streetViewControlOptions'] = {
	    position : google.maps.ControlPosition.RIGHT_CENTER
	}
	
	// abort if not locations found and we don't want to show the map
	// we still render it but keep it hidden
	if ( self.locations.length == 0 && self.hide_no_locations ) {
		var slideFunction = 'slideUp';
	} else {
		var slideFunction = 'slideDown';
	}

	// generate the map element
	self.wrap_element[slideFunction]( 'fast', function() {
		
		self.map = new google.maps.Map( 
			document.getElementById( self.map_element ),
			self.options
		);

		// after map was generated
		google.maps.event.addListenerOnce( self.map, 'idle', function() {	
			
			// fadeout the map loader
			jQuery( '#gmw-map-loader-' + self.id ).fadeOut( 1000 );
			self.wrap_element.find( '.gmw-map-cover' ).fadeOut( 500 );
			
			// create map expand toggle if needed
			// temporary disabled. It seems that Google added this feature to his API
			if ( self.options.resizeMapControl && jQuery( '#gmw-resize-map-toggle-' + self.id ).length != 0 ) {

				// generate resize toggle
				var resizeMapControl = document.getElementById( 'gmw-resize-map-toggle-' + self.id );
				resizeMapControl.style.position = 'absolute';	
				self.map.controls[google.maps.ControlPosition.TOP_RIGHT].push( resizeMapControl );			
				resizeMapControl.style.display = 'block';
			
				// resize map on click event	
		    	jQuery( '#gmw-resize-map-toggle-' + self.id ).on( 'click', function() {
		 	
		 			// get the current map center
		    		var mapCenter = self.map.getCenter();

		    		// replace map wrapper class to expended
		    		self.wrap_element.toggleClass( 'gmw-expanded-map' );

		    		if ( self.wrap_element.hasClass( 'gmw-expanded-map' ) ) {
		    			jQuery( 'body, html' ).addClass( 'gmw-scroll-disabled' ); 
		    		} else {
		    			jQuery( 'body, html' ).removeClass( 'gmw-scroll-disabled' );
		    		}
		    		
		    		// replace the toggle icon         		
		    		jQuery( this ).toggleClass( 'gmw-icon-resize-full' ).toggleClass( 'gmw-icon-resize-small' );
		    		
		    		// we wait a short moment to allow the wrapper element to resize
		    		setTimeout( function() { 	

		    			// resize map		    		
		    			google.maps.event.trigger( self.map, 'resize' );

		    			// recenter map
		    			self.map.setCenter( mapCenter );		

					}, 100 );            		
		    	});
			}

			google.maps.event.addListener( self.map, 'click', function( event ) {
			    self.close_info_window();
			});

			google.maps.event.addDomListener( self.map ,'zoom_changed', function( event ) {
				self.close_info_window();
			});

			// generate user marker		
			self.render_user_marker();

			// generate new markers
			self.render_markers( self.locations, false );
		});
	});
};

/**
 * Update existing map
 * 
 * @param  {[type]} mapVars [description]
 * @return {[type]}         [description]
 */
GMW_Map.prototype.update = function( locations, user_location, append_previous ) {
	
	var self = this;

	self.locations = locations || self.locations;

	self.user_location = user_location || self.user_location;

	// abort if not locations found and we don't want to show the map
	if ( self.locations.length == 0 && self.hide_no_locations ) {

		this.wrap_element.slideUp();
		
		return;
	}

	// if map does not exist, render it.
	if ( self.map === false ) {
		
		self.render( self.locations, self.user_location );
		
		return;
	}

	self.bounds = new google.maps.LatLngBounds();

	// close info window if open
	self.close_info_window();

	// make sure map is not hidden
	self.wrap_element.slideDown( 'fast', function() {

		google.maps.event.trigger( self.map, 'resize' );

		// clear existing markers
		self.clear();

		self.clear_user_marker();

		self.render_user_marker();

		// generate new markers
		self.render_markers( self.locations, append_previous );
	});
}

/**
 * Clear map of markers, polygens and grouping
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clear = function() {

	var self = this;

	// loop through existing markers
	for ( var i = 0; i < self.markers.length + 1 ; i++ ) {

		if ( i < self.markers.length ) {

			//if ( typeof self.markers[i] !== 'undefined' ) {
			
			// verify marker
			if ( self.markers[i] ) {

				// clear marker
				self.markers[i].setMap( null );	
			}
			//}
			
		// proceed when done
		} else {

			this.clear_polylines();

			// generate new markers array.
			self.markers = [];
			
			// clear group markers
			this.markers_grouping_clear();
		}
	}
};

/**
 * Remove polyline from the map
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clear_polylines = function() {

	for ( var i = 0; i < this.polylines.length + 1 ; i++ ) {

 		//remove each plyline from the map
 		if ( i < this.polylines.length ) {
 			
 			this.polylines[i].setMap( null );	

 		//generate new polyline array
 		} else {
 			
 			this.polylines = [];
 		}            
 	}
}

/**
 * Initilize markers grouping. 
 * 
 * markers clusters, spiderfier and can be extended
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.markers_grouping_init = function() {
	
	// temporary, for older versions
	if ( this.grouping_type == 'normal' ) {
		this.grouping_type = 'standard';
	}

	// hook custom functions if needed
	GMW.do_action( 'gmw_markers_grouping_init', this.grouping_type, this );

	// generate the grouping function
	var functionName = this.grouping_type + '_grouping_init';

	// verify grouping function
	if ( typeof this[functionName] === 'function' ) {

		// run marker grouping
		this[functionName]();
	
	// otherwise show error message
	} else {

		console.log( 'The function ' + functionName + ' not exists.' );
	}
};

/**
 * Clear markers grouping. 
 * 
 * markers clusters, spiderfier and can be extended
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.markers_grouping_clear = function() {
	
	// hook custom functions if needed
	GMW.do_action( 'gmw_markers_grouping_clear', this.grouping_type, this );

	// generate the grouping function
	var functionName = this.grouping_type + '_grouping_clear';

	// verify grouping function
	if ( typeof this[functionName] === 'function' ) {

		// run marker grouping
		this[functionName]();
	
	// otherwise show error message
	} else {

		console.log( 'The function ' + functionName + ' not exists.' );
	}
};

/**
 * Standard grouping functions holder
 *
 * since there is no normal grouping we do nothing here. 
 * 
 * this is just a function holder. 
 * 
 * @return void
 * 
 */
GMW_Map.prototype.standard_grouping_init = function() {}
GMW_Map.prototype.standard_grouping_clear = function() {}

/**
 * Markers Clusterer grouping init
 *  
 * @param  {[type]} group_markers [description]
 * @param  {[type]} mapObject     [description]
 * @return {[type]}               [description]
 */
GMW_Map.prototype.markers_clusterer_grouping_init = function() {

	// initialize markers clusterer if needed and if exists
    if ( typeof MarkerClusterer === 'function' ) {
    	
    	// init new clusters object
		this.clusters = new MarkerClusterer( 
			this.map, 
			this.markers,
			{
				imagePath    : this.clusters_path,
				clusterClass : this.prefix + '-cluster cluster',
				maxZoom 	 : 15 
			}
		);
	} 
}

/**
 * Markers Clusterer grouping clear
 *  
 * @param  {[type]} group_markers [description]
 * @param  {[type]} mapObject     [description]
 * @return {[type]}               [description]
 */
GMW_Map.prototype.markers_clusterer_grouping_clear = function() {

	// initialize markers clusterer if needed and if exists
    if ( typeof MarkerClusterer === 'function' ) {

    	// remove existing clusters
    	if ( this.clusters != false ) {		
    		this.clusters.clearMarkers();
    	}
	} 
}

/**
 * Markers Clusterer grouping
 *  
 * @param  {[type]} group_markers [description]
 * @param  {[type]} mapObject     [description]
 * @return {[type]}               [description]
 */
/*
GMW_Map.prototype.grouping_type_markers_clusterer = function() {

	// initialize markers clusterer if needed and if exists
    if ( typeof MarkerClusterer === 'function' ) {

    	// remove existing clusters
    	if ( this.clusters != false ) {		
    		this.clusters.clearMarkers();
    	}
    	
    	//create new clusters object
		this.clusters = new MarkerClusterer( 
			this.map, 
			this.markers,
			{
				imagePath    : this.clusters_path,
				clusterClass : this.prefix + '-cluster cluster',
				maxZoom 	 : 15 
			}
		);
	} 
}
*/
/**
 * Move marker
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.move_marker = function( marker_position ) {

    // do the math     
    var a = 360.0 / this.locations.length;
   
    var newLat = marker_position.lat() + - .000025 * Math.cos( ( + a * i ) / 180 * Math.PI );  //x
    var newLng = marker_position.lng() + - .000025 * Math.sin( ( + a * i )  / 180 * Math.PI );  //Y
    
    var newPosition = new google.maps.LatLng( newLat, newLng );

    // draw a line between the original location 
    // to the new location of the marker after it moves
    this.polylines.push( new google.maps.Polyline( {
	    path : [
	        marker_position, 
	        newPosition
	    ],
	    strokeColor   : "#FF0000",
	    strokeOpacity : 1.0,
	    strokeWeight  : 2,
	    map 		  : this.map
	} ) );

	return newPosition;
}

/**
 * Render a single marker
 * 
 * @param  {[type]} options [description]
 * @return {[type]}         [description]
 */
GMW_Map.prototype.render_marker = function( options ) {

	// map icon
	var icon = options['icon'];

	// in case _default.png pass without a URL
	if ( icon == '_default.png' ) {
		icon = '';
	}

	// if passing custom icon size we need to scale it
	if ( this.icon_scaled_size != false && icon != '' ) {
		icon = {
			url 	   : icon,
			scaledSize : this.icon_scaled_size,
		}
	}

	var marker_options = {
		position 	: options['position'],
		icon     	: icon,
		map      	: this.map,
		animation   : null,
		location_id : options['id'],
		iw_content  : options['content']
	}

	marker_options = GMW.apply_filters( 'gmw_generate_marker_options', marker_options, options['id'], this );

	// generate marker
	return new google.maps.Marker( marker_options );
}

/**
 * User position
 *
 * Create the user's marker and info window
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.render_user_marker = function() {

	var self = this;

	// generate new user location marker
	if ( self.user_location != false && self.user_location['lat'] != false && self.user_location['lng'] != false && self.user_location.map_icon != '0' && self.user_location.map_icon != '' ) {

		// generate user's position
		self.user_position = new google.maps.LatLng( 
			self.user_location['lat'], 
			self.user_location['lng'] 
		);
		
		// append user position to bounds
		self.bounds.extend( self.user_position );
		
		// generate marker
		var markerOptions = {
			position : self.user_position,
			icon     : self.user_location.map_icon,
			id   	 : 'user_marker',
			content  : ''
		};
		
		// generate marker
		self.user_marker = self.render_marker( markerOptions );

		// generate info-window if content exists
		if ( self.user_location['iw_content'] != false && self.user_location['iw_content'] != null ) {

			self.user_marker.iw_content = '<span class="title">' + self.user_location['iw_content'] + '</span>';
			
			// generate new window
			self.user_info_window = new google.maps.InfoWindow( {
				content   : '<div class="gmw-info-window user-marker map-' + this.id + ' ' + this.prefix + '">' + self.user_marker.iw_content + '</div>',
				maxHeight : '15px'
			} );
			
			google.maps.event.addListener( self.user_marker, 'click', function() {
		    	//self.marker_click( self.user_marker );
		    	// open window
				self.user_info_window.open( 
					self.map, 
					self.user_marker 
				);
		    });    
		      
		    // open info window on map load
			if ( self.user_location['iw_open'] == true ) {

				setTimeout( function() { 
					self.user_info_window.open( 
						self.map, 
						self.user_marker 
					);
				    //self.marker_click( self.user_marker );	
				}, 500 );
			}
		}
	}	
}

/**
 * Remove user marker 
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clear_user_marker = function() {

	// remove existing user marker
	if ( this.user_marker != false ) {
		this.user_marker.setMap( null );
		this.user_marker = false;
		this.user_position = false;
	}
}

/**
 * Generate markers
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.render_markers = function( locations, append_previous ) {

	var self = this;

	// hook custom functions if needed
	GMW.do_action( 'gmw_map_pre_render_markers', locations, this );

	// init grouping
	self.markers_grouping_init();

	self.locations = locations;

	// get previous location if appending locations.
	if ( ! append_previous || self.previous_locations.length == 0 ) {

		self.previous_locations = self.locations;

	} else {

		temLoc = jQuery.merge( self.locations, self.previous_locations );

		self.previous_locations = self.locations;

		self.locations = temLoc;
	}

	var locations_count = self.locations.length;

	// abort if not locations found and we don't want to show the map
	if ( locations_count == 0 && self.hide_no_locations ) {
		this.wrap_element.slideUp();
	}

	// loop through locations
	for ( i = 0; i < locations_count + 1 ; i++ ) {  
		
		// generate markers
		if ( i < locations_count ) {

			// verify location coordinates
			if ( self.locations[i]['lat'] == undefined || self.locations[i]['lng'] == undefined || self.locations[i]['lat'] == '0.000000' || self.locations[i]['lng'] == '0.000000' ) {
				continue;
			}
	
			// generate the marker position
			var marker_position = new google.maps.LatLng( 
				self.locations[i]['lat'], 
				self.locations[i]['lng'] 
			);
			
			// only if not using markers spiderfeir and if marker with the same location already exists
			// if so, we will move it a bit
 			if ( self.grouping_type != 'markers_spiderfier' && self.bounds.contains( marker_position ) ) {
 				marker_position = self.move_marker( marker_position );
	        }

			// append location into bounds
			self.bounds.extend( marker_position );

		    // generate marker
			var markerOptions = {
				position : marker_position,
				icon     : self.locations[i]['map_icon'],
				id       : i,
				content  : self.locations[i]['info_window_content']
			};

			// generate marker
			self.markers[i] = self.render_marker( markerOptions );

			// add marker to cluster
			if ( self.grouping_type == 'markers_clusterer' && typeof MarkerClusterer === 'function' ) {	

				// add marker to cluster object
				self.clusters.addMarker( self.markers[i] );	

				// init marker click event
				google.maps.event.addListener( self.markers[i], 'click', function() {
					self.marker_click( this );
				});	
			
			// add marker to spiderfier
			} else if ( self.grouping_type == 'markers_spiderfier' && typeof OverlappingMarkerSpiderfier === 'function' ) {	

				// add marker into spiderfier object
				self.spiderfiers.addMarker( self.markers[i] );

				// place marker on the map
				self.markers[i].setMap( self.map );	

				google.maps.event.addListener( self.markers[i], 'spider_click', function() {
					self.marker_click( this );
				});
	
			// if no grouping
			} else {		

				self.markers[i].setMap( self.map );

				// init marker click event
				google.maps.event.addListener( self.markers[i], 'click', function() {
					self.marker_click( this );
				});
			}

		// Continue when done generating the markers.
		} else {
			
			// hook custom functions if needed
			GMW.do_action( 'gmw_map_after_render_markers', locations, this );

			// center map only if locations or user location exist
			if ( locations_count > 0 || self.user_marker != false ) {
				self.center_map();
			}
		}
	} 
};

/**
 * center and zoom map
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.center_map = function() {

	var self = this;

	// custom zoom point
	if ( self.zoom_position != false && ! self.auto_zoom_level ) {

		// get position
		var latLng = new google.maps.LatLng( 
			self.zoom_position.lat, 
			self.zoom_position.lng 
		);

		self.map.setZoom( parseInt( self.options['zoom'] ) );
		self.map.panTo( latLng );

	// zoom map when a single marker exists on the map
	} else if ( self.locations.length == 1 && self.user_position == false ) {

		if ( self.auto_zoom_level ) {
			self.map.setZoom( 13 );
		} else {
			self.map.setZoom( parseInt( self.options['zoom'] ) );
		}

		self.map.panTo( self.markers[0].getPosition() );

	} else if ( ! self.auto_zoom_level && self.user_position != false ) {

		self.map.setZoom( parseInt( self.options['zoom'] ) );
		self.map.panTo( self.user_position );

	} else if ( self.auto_zoom_level || self.user_position == false  ) { 
		
		self.map.fitBounds( self.bounds );
	}
}

/**
 * Marker click event
 * 
 * @param  {[type]} marker [description]
 * 
 * @return {[type]}        [description]
 */
GMW_Map.prototype.marker_click = function( marker ) {

	GMW.do_action( 'gmw_map_marker_click', marker, this );

	this.active_marker = marker;

	// Clear directions if set on the map 
	if ( typeof directionsDisplay !== 'undefined' ) {
		directionsDisplay.setMap( null );
	}
	
	// close any open info window
	this.close_info_window();

	// generate info box
	var functionName = this.info_window_type + '_info_window_init';

	// verify marker click function
	if ( typeof this[functionName] === 'function' ) {

		// execute marker click event
		this[functionName]( marker );
	
	// show an error if function is missing
	} else {

		console.log( 'The function ' + functionName + ' not exists.' );
	}
};

/**
 * Close info window main function
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.close_info_window = function() {

	// hook custom functions if needed
	GMW.do_action( 'gmw_map_close_info_window', this.info_window_type, this );

	// generate the grouping function
	var functionName = this.info_window_type + '_info_window_close';

	// verify grouping function
	if ( typeof this[functionName] === 'function' ) {

		// run marker grouping
		this[functionName]();
	
	// otherwise show error message
	} else {

		console.log( 'The function ' + functionName + ' not exists.' );
	}
}

/**
 * Close info window standard
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.standard_info_window_close = function() {

	// close info window if open
	if ( this.active_info_window ) {
		this.active_info_window.close();
		this.active_info_window = null;
	}
}

/**
 * Standard info window 
 * 
 * @param  {[type]} marker    [description]
 * @param  {[type]} iw_type   [description]
 * @param  {[type]} mapObject [description]
 * @return {[type]}           [description]
 */
GMW_Map.prototype.standard_info_window_init = function( marker ) {

	// verify iw content
	if ( marker.iw_content ) {
			
		// generate new window
		this.active_info_window = new google.maps.InfoWindow( {
			content  : '<div class="gmw-info-window standard map-' + this.id + ' ' + this.prefix + '">' + marker.iw_content + '</div>',
			maxWidth : 200,
			minWidth: 200
		} );
	
		// open window
		this.active_info_window.open( 
			this.map, 
			marker 
		);
	}
}

/**
 * On document ready generate all maps exists in the global maps holder
 * 
 * @param  {GMW_Map}
 * @return {[type]}       [description]
 */
jQuery( document ).ready( function($){ 	

	if ( typeof gmwMapObjects == 'undefined' ) {
		return;
	}

	// loop through and generate all maps
	jQuery.each( gmwMapObjects, function( map_id, vars ) {	

		if ( vars.settings.render_map ) {
			
			//console.log( 'render map dynamically' );

			// generate new map
			GMW_Maps[map_id] = new GMW_Map( vars.settings, vars.map_options, vars.form );
			// initiate it
			GMW_Maps[map_id].render( vars.locations, vars.user_location );
		}
	});
});