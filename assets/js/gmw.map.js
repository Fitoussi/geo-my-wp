/**
 * Custom Map Providers functions.
 * 
 * @type {Object}
 */
var GMW_Map_Providers = {};

// Load Google Maps features only if needed.
if ( gmwVars.mapsProvider == 'google_maps' ) {

	GMW_Map_Providers.google_maps = {

		// Set bounds
		latLngBounds : function( mapObject ) {
			return new google.maps.LatLngBounds();
		},

		// Clear marker from the map.
		clearMarker : function( marker, mapObject ) {
			marker.setMap( null );
		},

		polyline : function( options, mapObject ) {
			return new google.maps.Polyline( options );
		},

		// Clear polyline from the map.
		clearPolyline : function( polyline, mapObject ) {
			polyline.setMap( null );
		},

		// Generate latLng position.
		latLng : function( lat, lng, mapObject ) {
			return new google.maps.LatLng( lat, lng );
		},

		// Add marker to the map.
		addMarker : function( marker, mapObject ) {
			marker.setMap( mapObject.map );
		},

		// Generate user's info-window.
		renderUserInfoWindow : function( marker, content, mapObject ) {
			
			var self = mapObject;

			self.userInfoWindow = new google.maps.InfoWindow( {
				content   : content,
				maxHeight : '15px'
			} );

			google.maps.event.addListener( marker, 'click', function() {
		    	//self.marker_click( self.userMarker );
		    	// open window
				self.userInfoWindow.open( 
					self.map, 
					marker
				);
		    });  

		    if ( self.userLocation.iw_open == true ) {

				setTimeout( function() { 
					self.userInfoWindow.open( 
						self.map, 
						self.userMarker 
					);	
				}, 500 );
			}
		},

		// Rezise map to fit its wrapping element.
		resizeMap : function( map, mapObject ) {
			google.maps.event.trigger( map, 'resize' );
		},

		// Set map center.
		setCenter : function( center, mapObject ) {
			mapObject.map.setCenter( center );
		},
				
		// Get element position.
		getPosition : function( element, mapObject ) {
			return element.getPosition();
		},

		// Map options
		getMapOptions : function( mapObject ) {
			
			var options = {};

			options.center = new google.maps.LatLng( mapObject.options.defaultCenter[0], mapObject.options.defaultCenter[1] );

			// map type
			options.mapTypeId 			  = google.maps.MapTypeId[mapObject.options.mapTypeId];
			options.mapTypeControlOptions = {
			    style    : google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
			    position : google.maps.ControlPosition.TOP_CENTER
			};

		    options.zoomControlOptions = {
		      	position : google.maps.ControlPosition.RIGHT_CENTER
		    };

			options.streetViewControlOptions = {
			    position : google.maps.ControlPosition.RIGHT_CENTER
			};

			return options;
		},

		// Render map.
		Map : function( element, options, mapObject ) {
			return new google.maps.Map( 
				document.getElementById( mapObject.mapElement ),
				options
			);
		},

		/**
		 * Execute function after map loaded.
		 * 
		 * @return {[type]} [description]
		 */
		mapLoaded : function( mapObject ) {
			
			var self = mapObject;

			// after map was generated
			google.maps.event.addListenerOnce( self.map, 'idle', function() {	
				
				// fadeout the map loader
				jQuery( '#gmw-map-loader-' + self.id ).fadeOut( 1000 );
				self.wrapElement.find( '.gmw-map-cover' ).fadeOut( 500 );
				
				// create map expand toggle if needed
				// temporary disabled. It seems that Google added this feature to his API
				if ( self.options.resizeMapControl && jQuery( '#gmw-resize-map-toggle-' + self.id ).length != 0 ) {

					// generate resize map button.
					var resizeMapControl = document.getElementById( 'gmw-resize-map-toggle-' + self.id );
					resizeMapControl.style.position = 'absolute';	
					self.map.controls[google.maps.ControlPosition.TOP_RIGHT].push( resizeMapControl );			
					resizeMapControl.style.display = 'block';
					
					// geenrate full screen toggle.
					self.fullScreenToggle();
				}

				// close any open info-window when clicking the map.
				google.maps.event.addListener( self.map, 'click', function( event ) {
				    self.closeInfoWindow();
				});

				google.maps.event.addDomListener( self.map ,'zoom_changed', function( event ) {
					//self.closeInfoWindow();
				});

				// generate user marker		
				self.renderUserMarker();

				// generate new markers
				self.renderMarkers( self.locations, false );
			});
		},

		/**
		 * Render a single marker
		 * 
		 * @param  {[type]} options [description]
		 * @return {[type]}         [description]
		 */
		renderMarker : function( options, location, mapObject ) {

			var self = mapObject,
				icon = {
					url : options.icon || self.iconUrl
				};

			// Scale icon size when provided per icon.
			if ( location.icon_size ) {

				icon.scaledSize = new google.maps.Size( parseInt( location.icon_size[0] ), parseInt( location.icon_size[1] ) );

			// When need to scale all icons based on same size.
			// That is if icon size provided or when using the default red icon.
			} else if ( self.iconSize || icon.url == gmwVars.defaultIcons.location_icon_url ) {

				// first we check if scaled size already provided in global.
				// We do this to prevent scalling each icon to the same size.
				// Just to save even a bit on perfoarmance, spacially when having many icons.
				if ( ! self.iconScaledSize ) {
					
					// set icon size in global		
					if ( self.iconSize ) {
						self.iconScaledSize = new google.maps.Size( parseInt( self.iconSize[0] ), parseInt( self.iconSize[1] ) );
					} else {
						self.iconScaledSize = new google.maps.Size( parseInt( gmwVars.defaultIcons.location_icon_size[0] ), parseInt( gmwVars.defaultIcons.location_icon_size[1] ) );
					}	
				}

				// get icon size from global.
				icon.scaledSize = self.iconScaledSize;
			}

			var marker_options = {
				position 	 : options.position,
				icon     	 : icon,
				map      	 : self.map,
				animation    : null,
				location_id  : options.id, //deprecated. use gmwData.markerCount instead.
				iw_content   : options.content // //deprecated. use gmwData.iwContent instead.
			};

			var gmwData = {
				markerCount : options.id,
				locationID  : location.location_id || 0,
				iwContent   : options.content,
				objcetType  : location.object_type || '',
				objectId    : location.object_id || 0
			};

			// modify marker options.
			marker_options = GMW.apply_filters( 'gmw_generate_marker_options', marker_options, options.id, self, location );

			var marker = new google.maps.Marker( marker_options );

			marker.gmwData = gmwData;

			// generate marker
			return marker;
		},

		setMarkerPosition : function( marker, position, map ) {
			marker.setPosition( position );
		},

		/**
		 * Move marker.
		 *
		 * Sligtly move markers that are on the same exact position.
		 * 
		 * @return {[type]} [description]
		 */
		moveMarker : function( markerPosition ) {

			var self = this;

		    // do the math     
		    var a = 360.0 / self.locations.length;
		   
		    var newLat = markerPosition.lat() + - 0.000025 * Math.cos( ( + a * i ) / 180 * Math.PI );  //x
		    var newLng = markerPosition.lng() + - 0.000025 * Math.sin( ( + a * i )  / 180 * Math.PI );  //Y
		    
		    var newPosition = self.latLng( newLat, newLng ); //cfunc

		    // draw a line between the original location 
		    // to the new location of the marker after it moves
		    self.polylines.push( new google.maps.Polyline( {
			    path : [
			        markerPosition, 
			        newPosition
			    ],
			    strokeColor   : "#999999",
			    strokeOpacity : 1.0,
			    strokeWeight  : 1,
			    map 		  : self.map
			} ) );

			return newPosition;
		},

		markerGroupingTypes : {

			'standard' : {

				'init' : function( mapObject ) {},

				'addMarker' : function( marker, mapObject ) {

					marker.setMap( mapObject.map );
				},

				'markerClick' : function( marker, mapObject ) {

					google.maps.event.addListener( marker, 'click', function() {
						
						mapObject.markerClick( this );
					});
				},
			},
		},

		infoWindowTypes : {

			'standard' : {

				'open' : function( marker, mapObject ) {

					var self = mapObject;

					// verify iw content
					if ( marker.gmwData.iwContent ) {
			
						// info window opsions. Can be modified with the filter.
						var info_window_options = GMW.apply_filters( 'gmw_standard_info_window_options', {
							content  : '<div class="gmw-info-window standard map-' + self.id + ' ' + self.prefix + '">' + marker.gmwData.iwContent + '</div>',
							maxWidth : 200,
							minWidth: 200
						}, self );

						// generate new window
						self.activeInfoWindow = new google.maps.InfoWindow( info_window_options );
					
						// open window
						self.activeInfoWindow.open( 
							self.map, 
							marker 
						);
					}
				},

				'close' : function( mapObject ) {

					if ( mapObject.activeInfoWindow ) {
						mapObject.activeInfoWindow.close();
						mapObject.activeInfoWindow = null;
					}
				}
			}
		}
	};
}

if ( gmwVars.mapsProvider == 'leaflet' ) {

	/**
	 * leaftlet mapping provider.
	 * 
	 * @return {[type]} [description]
	 */
	GMW_Map_Providers.leaflet = {

		// Set bounds
		latLngBounds : function( mapObject ) {
			return L.latLngBounds();
		},

		// Clear marker from the map.
		clearMarker : function( marker, mapObject ) {
			marker.remove();
		},

		polyline : function( options, mapObject ) {
			return L.polygon( options.path, { color : options.strokeColor } ).addTo( mapObject );
		},

		// Clear polyline from the map.
		clearPolyline : function( polyline, mapObject ) {
			mapObject.map.removeLayer( polyline );
		},

		// Generate latLng position.
		latLng : function( lat, lng, mapObject ) {
			return L.latLng( lat, lng );
		},

		// Add marker to the map.
		addMarker : function( marker, mapObject ) {
			marker.addTo( mapObject.map );
		},

		// Generate user's info-window.
		renderUserInfoWindow : function( marker, content, mapObject ) {
			
			marker.bindPopup( content );

			// Open info-window on page load.
			if ( mapObject.userLocation.iw_open == true ) {

				setTimeout( function() { 
					marker.openPopup();	
				}, 500 );
			}
		},

		// Rezise map to fit its wrapping element.
		resizeMap : function( map, mapObject ) {
			map.invalidateSize();
		},

		// Set map center.
		setCenter : function( center, mapObject ) {
			mapObject.map.setView( center );
		},
				
		// Get element position.
		getPosition : function( element, mapObject ) {
			return element.getLatLng();
		},

		// Map options
		getMapOptions : function( mapObject ) {
			
			var options = {};

			// clusters default options.
			options.markerClustersOptions = {
				spiderfyOnMaxZoom		   : true,
				showCoverageOnHover		   : true,
				zoomToBoundsOnClick		   : true,
				removeOutsideVisibleBounds : true,
				animate 				   : true
			};

			// Spiderfier options.
			options.markerSpiderfierOptions = {
				keepSpiderfied 		   : true,
				nearbyDistance 		   : 20,
				circleSpiralSwitchover : 9,
				legWeight 			   : 1.5
			};

			options.maxZoom 	    = 18;
			options.scrollWheelZoom = mapObject.options.scrollwheel;

			return options;
		},

		/**
		 * Move marker.
		 *
		 * Sligtly move markers that are on the same exact position.
		 * 
		 * @return {[type]} [description]
		 */
		moveMarker : function( markerPosition ) {

			var self = this;

		    // do the math     
		    var a = 360.0 / self.locations.length;
		   
		    var newLat = markerPosition.lat + - 0.000050 * Math.cos( ( + a * i ) / 180 * Math.PI );  //x
		    var newLng = markerPosition.lng + - 0.000050 * Math.sin( ( + a * i )  / 180 * Math.PI );  //Y
		    
		    var newPosition = self.latLng( newLat, newLng ); //cfunc

		    self.polylines.push( L.polygon( [ newPosition, markerPosition ], { 'color' : '#999999', 'wight' : '1' } ).addTo( self.map ) );

			return newPosition;
		},

		// Render map.
		Map : function( element, options, mapObject ) {
			return L.map( element, options );
		},

		/**
		 * Execute function after map loaded.
		 * 
		 * @return {[type]} [description]
		 */
		mapLoaded : function( mapObject ) {
			
			var self = mapObject;

			// after map was generated
			self.map.on( 'load', function() {

				// fadeout the map loader
				jQuery( '#gmw-map-loader-' + self.id ).fadeOut( 1000 );
				self.wrapElement.find( '.gmw-map-cover' ).fadeOut( 500 );
				
				// create map expand toggle if needed
				// temporary disabled. It seems that Google added this feature to its API
				if ( self.options.resizeMapControl && jQuery( '#gmw-resize-map-toggle-' + self.id ).length != 0 ) {

					// generate resize toggle
					jQuery( '#gmw-resize-map-toggle-' + self.id ).detach().appendTo( jQuery( '#' + self.mapElement ).find( '.leaflet-top.leaflet-right' ) ).addClass( 'leaflet-control leaflet-bar' ).show();
					
					self.fullScreenToggle();
				}

				// Close info-window when clicking on the map.
				self.map.on( 'click dblclick,', function( event ) {
				    self.closeInfoWindow();
				});

				// generate user marker		
				self.renderUserMarker();

				// generate new markers
				self.renderMarkers( self.locations, false );
			});

			// Set the map and zoom control position.
			self.map.setView( self.options.defaultCenter );

			if ( typeof( self.map.zoomControl ) !== 'undefined' ) {
				self.map.zoomControl.setPosition( 'bottomright' );
			}

			// Load layers.
			L.tileLayer( self.options.layersUrl, {
			    attribution: self.options.layersAttribution
			}).addTo( self.map );
		},

		/**
		 * Render a single marker
		 * 
		 * @param  {[type]} options [description]
		 * @return {[type]}         [description]
		 */
		renderMarker : function( options, location, mapObject ) {

			var self 	 	= mapObject,
				iconUrl  	= options.icon || self.iconUrl, // icon URL
				iconSize 	= location.icon_size || self.iconSize || [ 25, 41 ], // get icon size.
				// icon options
				iconOptions = {
				    iconUrl		 : iconUrl,
				    iconSize 	 : iconSize,
				    iconAnchor	 : [ iconSize[0] / 2, iconSize[1] ], // caaculate anchor based on icon size
				    popupAnchor	 : [ 1, -iconSize[1] ], // 
				    //shadowUrl: 'my-icon-shadow.png',
				    //shadowSize	 : [68, 95],
				    //shadowAnchor : [22, 94]
				},
				// Marker options.
				marker_options = {
					iconOptions  : iconOptions,
				    position 	 : options.position,
					location_id  : options.id, //deprecated. use gmwData.markerCount instead.
					iw_content   : options.content // deprecated. use gmwData.iwContent instead.
				},

				gmwData = {
					markerCount : options.id,
					locationID  : location.location_id || 0,
					iwContent   : options.content,
					objcetType  : location.object_type || '',
					objectId    : location.object_id || 0
				};

			// Modify marker options.
			marker_options = GMW.apply_filters( 'gmw_generate_marker_options', marker_options, options.id, options, this );

			// generate Icon
			marker_options.icon = L.icon( marker_options.iconOptions );
			
			var marker = L.marker( [ options.position.lat, options.position.lng ], marker_options );

			marker.gmwData = gmwData;

			// Generate marker.	
			return marker;
		},

		setMarkerPosition : function( marker, position, map ) {
			marker.setLatLng( position );
		},

		markerGroupingTypes : {

			'standard' : {

				// no need to initiate.
				'init' : function( mapObject ) {},

				// add marker to the map.
				'addMarker' : function( marker, mapObject ) {
					marker.addTo( mapObject.map ); 
				},

				// Open info window on click.
				'markerClick' : function( marker, mapObject ) {
					
					marker.on( 'click', function() {

						mapObject.markerClick( this );
					});
				},

				// No group to clear.
				'clear' : function() {}
			}
		},

		// Standard info-window action.
		infoWindowTypes : {

			'standard' : {

				// Open info-window.
				'open' : function( marker, mapObject ) {

					var self = mapObject;

					// verify iw content
					if ( marker.gmwData.iwContent ) {
						
						// info window opsions. Can be modified with the filter.
						var info_window_options = GMW.apply_filters( 'gmw_standard_info_window_options', {
							content  : '<div class="gmw-info-window standard map-' + self.id + ' ' + self.prefix + '">' + marker.gmwData.iwContent + '</div>',
							maxWidth : 200,
							minWidth : 200
						}, self );

						// generate new window
						self.activeInfoWindow = L.popup( info_window_options ).setContent( info_window_options.content );
						
						// Bind popup to marker. We also unbind previous info-window before binding a new one.
						marker.unbindPopup().bindPopup( self.activeInfoWindow ).openPopup();
					}
				},

				// Close info-window.
				'close' : function( mapObject ) {
					if ( mapObject.activeInfoWindow ) {
						mapObject.activeInfoWindow = null;
					}
				}
			}
		}
	};
}

/**
 * Maps object.
 *
 * This object hold all the maps that need to load on page load.
 * 
 * @type {Object}
 */
var GMW_Maps = {};

/**
 * Base Map generator.
 *
 * Can be extended to work with different map providers.
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
	 * map provider.
	 * 
	 * @type {[type]}
	 */
	this.provider = options.map_provider || 'leaflet';

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
	this.wrapElement = jQuery( '#gmw-map-wrapper-' + this.id );

	/**
	 * Map's DIV element 
	 * 
	 * @type {[type]}
	 */
	this.mapElement = 'gmw-map-' + this.id;

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
	this.previousLocations = [];

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
	//this.icon_scaled_size = options.map_icon_width && options.map_icon_height ? [ parseInt( options.map_icon_width ), parseInt( options.map_icon_height ) ] : null;

	/**
	 * Hide map if no locations found
	 * 
	 * @type {Boolean}
	 */
	this.hideMapWithoutLocations = options.hide_no_locations || false;

	/**
	 * Location info-window
	 * 
	 * @type {Boolean}
	 */
	this.activeInfoWindow = null;

	/**
	 * User location data
	 * 
	 * @type {Boolean}
	 */
	this.userLocation = false;

	/**
	 * User position
	 * 
	 * @type {Boolean}
	 */
	this.userPosition = false;
	
	/**
	 * User's location info window
	 * 
	 * @type {Boolean}
	 */
	this.userInfoWindow = false;

	/**
	 * Markers clusterer PATH
	 * @type {String}
	 */
	this.clustersPath = options.clusters_path || 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/gh-pages/images/m';

	/**
	 * Marker grouping type
	 * 
	 * @type {String}
	 */
	this.markerGrouping = options.group_markers || 'standard';

	/**
	 * Info window type
	 * 
	 * @type {String}
	 */
	this.infoWindow = options.info_window_type || 'standard';

	/**
	 * IW Ajax Content
	 * 
	 * @type {[type]}
	 */
	this.infoWindowAjax = options.info_window_ajax || false;

	/**
	 * IW Ajax Content
	 * 
	 * @type {[type]}
	 */
	this.infoWindowTemplate = options.info_window_template || 'default';
			
	/**
	 * User location map marker
	 * 
	 * @type {Boolean}
	 */
	this.userMarker = false;

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
	this.activeMarker = null;
	
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
	this.autoZoomLevel = false;

	/**
	 * Custom zoom position
	 * 
	 * @type {Boolean}
	 */
	this.zoomPosition = options.zoom_position || false;

	/**
	 * Default icons URL.
	 * 
	 * @type {String}
	 */
	this.iconUrl = options.icon_url || gmwVars.defaultIcons.location_icon_url;

	/**
	 * Default icons size.
	 * 
	 * @type {Array}
	 */
	this.iconSize = options.icon_size || null;

	/**
	 * User default icons size.
	 * 
	 * @type {Array}
	 */
	this.userIconSize = gmwVars.defaultIcons.user_location_icon_size;

	/**
	 * If showing directions on the map.
	 * 
	 * @type {[type]}
	 */
	this.directions = null;

	/**
	 * Array hold coords to check if locations exist on the same exact location.
	 * 
	 * @type {Array}
	 */
	this.coordsCollector = [];

	this.moveMarkerEnabled = false;

	this.init();
};

/**
 * Collection of grouping types functions.
 *
 * This object holds the different function of each grouping types.
 * @type {Object}
 */
GMW_Map.prototype.markerGroupingTypes = {};

/**
 * Collection of info-window types functions.
 *
 * This object holds the different function of the different info-window types.
 * @type {Object}
 */
GMW_Map.prototype.infoWindowTypes = {};

/**
 * Initialize.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.init = function() {

	var self = this;

	// Abort if map provided was not found.
	if ( typeof( GMW_Map_Providers[self.provider] ) === 'undefined' ) {
		return console.log( 'The map provider ' + self.provider + ' not exists.' );
	}

	// Extend the selected provider.
	jQuery.extend( self, GMW_Map_Providers[self.provider] );

	self = GMW.apply_filters( 'gmw_map_init', self );
	
	// Verify Marker Grouping functions. Otherwise, use standard.
	if ( typeof( self.markerGroupingTypes[self.markerGrouping] ) === 'undefined' ) {

		console.log( self.markerGrouping + ' marker grouping function was not found. "Standard" will be used instead' );

		self.markerGrouping = 'standard';
	}

	// Verify info-window functions. Otherwise, use standard.
	if ( typeof( self.infoWindowTypes[self.infoWindow] ) === 'undefined' ) {

		console.log( self.infoWindow + ' info-window function was not found. "Standard" will be used instead' );
		
		self.infoWindow = 'standard';
	}

	// enable marker movement.
	if ( self.provider == 'google_maps' ) {

		if ( self.markerGrouping == 'standard' || self.markerGrouping == 'markers_clusterer' ) {	
			self.moveMarkersEnabled = true;
		}
		
	} else if ( self.provider == 'leaflet' && self.markerGrouping == 'standard' ) {
		self.moveMarkersEnabled = true; 
	}
};

/**
 * Center and zoom map.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.centerMap = function() {

	var self = this;

	// If custom zoom point provided, use it.
	if ( self.zoomPosition != false && ! self.autoZoomLevel ) {

		// get position
		var latLng = self.latLng( 
			self.zoomPosition.lat, 
			self.zoomPosition.lng,
			self 
		);

		self.map.setZoom( parseInt( self.options.zoom ) );
		self.map.panTo( latLng );

	// zoom out map when a single marker exists on the map.
	} else if ( self.locations.length == 1 && self.userPosition == false ) {

		if ( self.autoZoomLevel ) {
			self.map.setZoom( 13 );
		} else {
			self.map.setZoom( parseInt( self.options.zoom ) );
		}

		self.map.panTo( self.getPosition( self.markers[0], self ) );

	} else if ( ! self.autoZoomLevel && self.userPosition != false ) {

		self.map.setZoom( parseInt( self.options.zoom ) );
		self.map.panTo( self.userPosition );

	} else if ( self.autoZoomLevel || self.userPosition == false  ) { 
		
		self.map.fitBounds( self.bounds );
	}
};

/**
 * Full screen map toggle.
 * 
 * @param  {[type]} button [description]
 * @return {[type]}        [description]
 */
GMW_Map.prototype.fullScreenToggle = function( button ) {

	var self = this;

	// enable toggle "on click" function. 
	jQuery( '#gmw-resize-map-toggle-' + self.id ).on( 'click', function() {
	
		// get the current map center
		var mapCenter = self.map.getCenter();

		// replace map wrapper class to expended.
		self.wrapElement.toggleClass( 'gmw-expanded-map' );

		// disable HTML/body scroll when map is full screen.
		if ( self.wrapElement.hasClass( 'gmw-expanded-map' ) ) {
			jQuery( 'body, html' ).addClass( 'gmw-scroll-disabled' ); 
		} else {
			jQuery( 'body, html' ).removeClass( 'gmw-scroll-disabled' );
		}
		
		// replace the toggle icon         		
		jQuery( this ).toggleClass( 'gmw-icon-resize-full' ).toggleClass( 'gmw-icon-resize-small' );
		
		// we wait a short moment to allow the map element to resize
		// before resizing and centering the map.
		setTimeout( function() { 	

			// resize map to fit the new element size.
			self.resizeMap( self.map, self );

			// recenter map
			self.setCenter( mapCenter, self );

		}, 100 );            		
	}); 
};

/**
 * Render the map for the first time.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.render = function( locations, userLocation ) {
	
	var self = this;

	// abort if map element not exist
	if ( ! jQuery( '#' + self.mapElement ).length ) {
		return;
	}

	// get some values.
	self.locations 	  = locations || self.locations;
	self.userLocation = userLocation || self.userLocation;
	self.bounds 	  = self.latLngBounds( self );

	// set auto zoom level
	if ( self.options.zoom == 'auto' ) {
		self.autoZoomLevel = true;
		self.options.zoom 	 = 13;
	// otherwise specifiy the zoom level
	} else {
		self.autoZoomLevel = false;
		self.options.zoom    = parseInt( self.options.zoom );
	}

	// generate default center for the map.
	self.options.defaultCenter = typeof self.options.defaultCenter !== 'undefined' ? self.options.defaultCenter.split(',') : [ 40.758895, -73.985131 ];

	self.options = jQuery.extend( {}, self.options, self.getMapOptions( self ) );

	// modify the map options.
	self.options = GMW.apply_filters( 'gmw_map_options', self.options, self );
	
	var slideFunction;

	// abort if not locations found and we don't want to show the map
	// we still render it but keep it hidden
	if ( self.locations.length == 0 && self.hideMapWithoutLocations ) {
		slideFunction = 'slideUp';
	} else {
		slideFunction = 'slideDown';
	}

	// generate the map element
	self.wrapElement[slideFunction]( 'fast', function() {
		
		// generate the map
		self.map = self.Map( self.mapElement, self.options, self );

		// hook custom functions if needed
		GMW.do_action( 'gmw_map_rendered', self.map, self );

		// functions after map done loading.
		self.mapLoaded( self );
	});
};

/**
 * Update an existing map.
 * 
 * @param  {[type]} mapVars [description]
 * @return {[type]}         [description]
 */
GMW_Map.prototype.update = function( locations, userLocation, append_previous ) {
	
	var self = this;
	var icon;
	
	// set locations.
	self.locations 	  = locations || self.locations;
	self.userLocation = userLocation || self.userLocation;
	
	/*if ( userLocation ) {
		self.userLocation.lat = userLocation.lat;
		self.userLocation.lng = userLocation.lng;
	}*/

	// abort if not locations found and we don't want to show the map.
	if ( self.locations.length == 0 && self.hideMapWithoutLocations ) {
		this.wrapElement.slideUp();
		return;
	}

	// if map does not exist, render it first.
	if ( self.map === false ) {
		self.render( self.locations, self.userLocation );
		return;
	}

	// generate new bounds.
	self.bounds = self.latLngBounds();

	// close info-window if one is open.
	self.closeInfoWindow();

	// make sure map is not hidden
	self.wrapElement.slideDown( 'fast', function() {

		self.resizeMap( self.map, self );

		// clear existing markers.
		self.clear();

		// clear user marker.
		self.clearUserMarker();

		// generate new user marker.
		self.renderUserMarker();

		// generate new markers.
		self.renderMarkers( self.locations, append_previous );
	});
};

/**
 * Clear map of markers, polygens and grouping
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clear = function() {

	var self = this;

	// loop through existing markers
	for ( var i = 0; i < self.markers.length + 1 ; i++ ) {

		// clear markers.
		if ( i < self.markers.length ) {
			
			// verify marker
			if ( self.markers[i] ) {

				// clear marker
				self.clearMarker( self.markers[i], self );
			}
			
		// proceed when done removing all marker.
		} else {

			// clear polylines.
			this.clearPolylines();

			// generate new markers array.
			self.markers = [];
			
			// clear coords collector.
			self.coordsCollector = [];

			// clear group markers
			this.clearMarkersGrouping();
		}
	}
};

/**
 * Remove polyline from the map.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clearPolylines = function() {

	var self = this;

	for ( var i = 0; i < self.polylines.length + 1 ; i++ ) {

 		//remove each plyline from the map
 		if ( i < this.polylines.length ) {
 	
 			self.clearPolyline( self.polylines[i], self );

 		//generate new polyline array
 		} else {
 			
 			self.polylines = [];
 		}            
 	}
};

/**
 * Generate the user/visitor marker.
 *
 * Create the user's location marker and info window.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.renderUserMarker = function() {

	var self = this;

	// generate new user location marker
	if ( self.userLocation != false && self.userLocation.lat != 'null' && self.userLocation.lng != 'null' && self.userLocation.lat != false && self.userLocation.lng != false && self.userLocation.map_icon != '0' && self.userLocation.map_icon != '' ) {

		// generate user's position
		self.userPosition = self.latLng( 
			self.userLocation.lat, 
			self.userLocation.lng,
			self
		);
		
		// append user position to bounds
		self.bounds.extend( self.userPosition );
		
		if ( typeof self.userLocation.map_icon === 'undefined' || '' == self.userLocation.map_icon ) {
			self.userLocation.map_icon = gmwVars.defaultIcons.user_location_icon_url;
		}

		if ( ! self.userLocation.icon_size && self.userLocation.map_icon == gmwVars.defaultIcons.user_location_icon_url ) {
			self.userLocation.icon_size = self.userIconSize;
		}

		// generate marker
		var markerOptions = {
			position 	 : self.userPosition,
			icon     	 : self.userLocation.map_icon,
			id   	 	 : 'user_marker',
			content  	 : ''
		};
		
		// generate marker
		self.userMarker = self.renderMarker( markerOptions, self.userLocation, self );

		self.addMarker( self.userMarker, self );

		// generate info-window if content exists
		if ( self.userLocation.iw_content != false && self.userLocation.iw_content != null ) {

			var content = '<div class="gmw-info-window user-marker map-' + self.id + ' ' + self.prefix + '"><span class="title">' + self.userLocation.iw_content + '</span></div>';
							
			self.renderUserInfoWindow( self.userMarker, content, self );
		}
	}	
};

/**
 * Remove user marker from the map.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clearUserMarker = function() {

	var self = this;

	// remove existing user marker
	if ( self.userMarker != false ) {
		self.clearMarker( self.userMarker, self );
		self.userMarker   = false;
		self.userPosition = false;
	}
};

/**
 * Generate markers.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.renderMarkers = function( locations, append_previous ) {

	var self = this;

	// hook custom functions if needed
	GMW.do_action( 'gmw_map_pre_render_markers', locations, this );

	// init marker grouping
	self.initMarkersGrouping();

	self.locations = locations;

	// get previous location if appending locations.
	if ( ! append_previous || self.previousLocations.length == 0 ) {

		self.previousLocations = self.locations;

	} else {

		temLoc = jQuery.merge( self.locations, self.previousLocations );

		self.previousLocations = self.locations;

		self.locations = temLoc;
	}

	var locations_count = self.locations.length;

	// abort if no locations found and we don't want to show the map
	if ( locations_count == 0 && self.hideMapWithoutLocations ) {
		this.wrapElement.slideUp();
	}

	// loop through locations
	for ( i = 0; i < locations_count + 1 ; i++ ) {  
		
		// generate markers
		if ( i < locations_count ) {

			// verify location coordinates
			if ( self.locations[i].lat == undefined || self.locations[i].lng == undefined || self.locations[i].lat == '0.000000' || self.locations[i].lng == '0.000000' ) {
				continue;
			}

			// generate the marker position
			var markerPosition = self.latLng( 
				self.locations[i].lat, 
				self.locations[i].lng,
				self 
			);
			
			// only if not using markers spiderfeir and if marker with the same location already exists
			// if so, we will move it a bit.
			if ( self.moveMarkersEnabled ) {

				var markerCoords = self.locations[i].lat + self.locations[i].lng;

	 			if ( jQuery.inArray( markerCoords, self.coordsCollector ) != -1 ) {
	 				markerPosition = self.moveMarker( markerPosition );
		        } else {
		        	self.coordsCollector.push( markerCoords );
		        }
		    }

			// append location into bounds.
			self.bounds.extend( markerPosition );

			if ( typeof self.locations[i].map_icon === 'undefined' || '' == self.locations[i].map_icon ) {
				self.userLocation.map_icon = gmwVars.defaultIcons.location_icon_url;
			}

		    // default marker options.
			var markerOptions = {
				position 	 : markerPosition,
				icon     	 : self.locations[i].map_icon,
				id       	 : i,
				content      : self.locations[i].info_window_content
			};

			// generate marker
			self.markers[i] = self.renderMarker( markerOptions, self.locations[i], self );

			// add the marker to the map.
			self.markerGroupingTypes[self.markerGrouping].addMarker( self.markers[i], self );

			// add marker to grouping
			self.markerGroupingTypes[self.markerGrouping].markerClick( self.markers[i], self );

			// hook custom functions if needed.
			GMW.do_action( 'gmw_map_markers_loop_single_marker', self.markers[i], self.locations[i], self );

		// proceed when done generating the markers.
		} else {
			
			// hook custom functions if needed
			GMW.do_action( 'gmw_map_after_render_markers', locations, this );

			// center map only if locations or user location exist.
			if ( locations_count > 0 || self.userMarker != false ) {
				self.centerMap();
			}
		}
	} 
};

/**
 * Initiate grouping type.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.initMarkersGrouping = function() {
	
	var self = this;

	// temporary, to support older versions of GMW.
	if ( self.markerGrouping == 'normal' ) {
		self.markerGrouping = 'standard';
	}

	// hook custom functions if needed
	GMW.do_action( 'gmw_markers_grouping_init', self.markerGrouping, self );

	// run marker grouping
	self.markerGroupingTypes[self.markerGrouping]['init']( self );
};

/**
 * Clear markers grouping. 
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.clearMarkersGrouping = function() {
	
	var self = this;

	// temporary, to support older versions of GMW.
	if ( self.markerGrouping == 'normal' ) {
		self.markerGrouping = 'standard';
	}

	// hook custom functions if needed
	GMW.do_action( 'gmw_markers_grouping_clear', self.markerGrouping, self );

	// verify that the function exists for the type of info-window
	if ( typeof self.markerGroupingTypes[self.markerGrouping]['clear'] !== 'undefined' ) {

		// run marker grouping
		self.markerGroupingTypes[self.markerGrouping]['clear']( self );
	}
};

/**
 * Open info-window main function.
 * 
 * @param  {[type]} marker [description]
 * @return {[type]}        [description]
 */
GMW_Map.prototype.openInfoWindow = function( marker ) {

	var self = this;

	// we already do this in clickMarker function.
	// We do it again here in case a custum marker grouping function
	// passes the markerClick function.
	self.activeMarker = marker;

	// do somethign before the info-window opens.
	GMW.do_action( 'gmw_map_pre_open_info_window', marker, self );
	
	// close any open info-window.
	self.closeInfoWindow();

	// open info-window
	self.infoWindowTypes[self.infoWindow].open( marker, self );
};

/**
 * Close info-window main function.
 * 
 * @return {[type]} [description]
 */
GMW_Map.prototype.closeInfoWindow = function() {

	var self = this;
	
	if ( typeof self.userInfoWindow.close === 'function' ) {
		self.userInfoWindow.close();
	}

	// hook custom functions if needed.
	GMW.do_action( 'gmw_map_pre_close_info_window', self.infoWindow, self );

	// run marker grouping
	self.infoWindowTypes[self.infoWindow].close( self );
};

/**
 * Marker click event
 * 
 * @param  {[type]} marker [description]
 * 
 * @return {[type]}        [description]
 */
GMW_Map.prototype.markerClick = function( marker ) {

	var self = this;

	GMW.do_action( 'gmw_map_marker_click', marker, self );

	self.activeMarker = marker;

	self.openInfoWindow( marker );
};

/**
 * Get info-window template name.
 * 
 * @param  {[type]} template [description]
 * @return {[type]}          [description]
 */
GMW_Map.prototype.getIwTemplateName = function( template ) {

	if ( template.indexOf( 'custom_' ) > -1 ) {
		template = template.replace( 'custom_', '' ) + ' custom';
	}
	
	return template;
};

/**
 * below is a list of functions that need to be created 
 *
 * for each map provider.
 */
// Set bounds
//GMW_Map.prototype.latLngBounds = function( mapObject ) {};

// Clear marker from the map.
//GMW_Map.prototype.clearMarker = function( marker, mapObject ) {};

// Clear polyline from the map.
//GMW_Map.prototype.clearPolyline = function( polyline, mapObject ) {};

// Generate latLng position.
//GMW_Map.prototype.latLng = function( lat, lng, mapObject ) {};

// Add marker to the map.
//GMW_Map.prototype.addMarker = function( marker, mapObject ) {};

// Generate user's info-window.
//GMW_Map.prototype.renderUserInfoWindow = function( marker, content, mapObject ) {};

// Rezise map to fit its wrapping element.
//GMW_Map.prototype.resizeMap = function( map, mapObject ) {};

// Set map center.
//GMW_Map.prototype.setCenter = function( center, mapObject ) {};
				
// Get element position.
//GMW_Map.prototype.getPosition = function( element, mapObject ) {};

// Map options
//GMW_Map.prototype.getMapOptions = function( mapObject ) {};

// Render map.
//GMW_Map.prototype.Map = function( element, options, mapObject ) {};

//Execute function after map loaded.
//GMW_Map.prototype.mapLoaded = function( mapObject ) {};

//Render a single marker
//GMW_Map.prototype.renderMarker = function( options, location, mapObject ) {};

//GMW_Map.prototype.setMarkerPosition = function( marker, position, map ) {};

/**
 * Move marker.
 *
 * Sligtly move markers that are on the same exact position.
 * 
 * @return {[type]} [description]
 */
//GMW_Map.prototype.moveMarker = function( markerPosition ) {};

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

		if ( vars.settings.render_on_page_load ) {
			
			// generate new map
			GMW_Maps[map_id] = new GMW_Map( vars.settings, vars.map_options, vars.form );
			// initiate it
			GMW_Maps[map_id].render( vars.locations, vars.user_location );
		}
	});
});
