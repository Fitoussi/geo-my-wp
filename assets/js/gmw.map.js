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

		getBounds : function( mapObject ) {

			if ( typeof mapObject === 'undefined' ) {
				mapObject = this;
			}

			return {
				southWest : {
					lat : mapObject.map.getBounds().getSouthWest().lat(),
					lng : mapObject.map.getBounds().getSouthWest().lng(),
				},
				northEast : {
					lat : mapObject.map.getBounds().getNorthEast().lat(),
					lng : mapObject.map.getBounds().getNorthEast().lng(),
				},
			};
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
				ariaLabel : 'gmw-user-iw',
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

			if ( typeof mapObject === 'undefined' ) {
				mapObject = this;
				map       = mapObject.map;
			}

			google.maps.event.trigger( map, 'resize' );
		},

		// Set map center.
		setCenter : function( center, mapObject ) {
			mapObject.map.setCenter( center );
		},

		// Get element position.
		getPosition: function (element, mapObject) {
			return gmwVars.googleAdvancedMarkers ? element.position : element.getPosition();
		},

		// Map options
		getMapOptions : function( mapObject ) {

			var options = {};

			options.center = new google.maps.LatLng( mapObject.options.defaultCenter[0], mapObject.options.defaultCenter[1] );

			// map type
			options.mapTypeId 			  = google.maps.MapTypeId[mapObject.options.mapTypeId];
			options.mapTypeControlOptions = {
			    style    : google.maps.MapTypeControlStyle.DROPDOWN_MENU,
			    position : google.maps.ControlPosition.LEFT_TOP
			};

		    options.zoomControlOptions = {
		      	position : google.maps.ControlPosition.RIGHT_CENTER
		    };

			options.streetViewControlOptions = {
			    position : google.maps.ControlPosition.RIGHT_CENTER
			};

			if ( mapObject.settings.map_bounderies.length != 0 ) {

				var bounds = new google.maps.LatLngBounds(
					new google.maps.LatLng( mapObject.settings.map_bounderies[0], mapObject.settings.map_bounderies[1] ),
					new google.maps.LatLng( mapObject.settings.map_bounderies[2], mapObject.settings.map_bounderies[3] )
				);

				options.restriction = {
					latLngBounds: bounds,
					strictBounds: true,
				};
			}

			return options;
		},

		// Render map.
		Map: function (element, options, mapObject) {

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
				//jQuery( '#gmw-map-loader-' + self.id ).fadeOut( 1000 );
				self.wrapElement.find( '.gmw-map-loader' ).fadeOut( 500 );

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

				if ( jQuery( '#gmw-map-position-filter-wrapper-' + self.id ).length != 0 ) {

					var positionFilter = document.getElementById( 'gmw-map-position-filter-wrapper-' + self.id );
						self.map.controls[ google.maps.ControlPosition.TOP_CENTER ].push( positionFilter );
						positionFilter.style.display  = '';
						positionFilter.style.position = 'absolute';
				}

				// close any open info-window when clicking the map.
				google.maps.event.addListener( self.map, 'click', function( event ) {
				    self.closeInfoWindow();
				});

				// Event on zoom changed.
				google.maps.event.addListener( self.map ,'zoom_changed', function( event ) {
					self.zoomChanged( self );
				});

				// Event on bounds changed.
				google.maps.event.addListener(self.map, 'bounds_changed', function (event) {
					self.boundsChanged( self );
				});

				// Event on map dragged.
				google.maps.event.addListener( self.map, 'dragend', function() {
					self.dragged( self );
				});

				// generate user marker
				self.renderUserMarker();

				// generate new markers
				self.renderMarkers( self.locations, false );

				GMW.do_action( 'gmw_map_loaded', self.map, self );
			});
		},

		/**
		 * Render a single marker
		 *
		 * @param  {[type]} options [description]
		 * @return {[type]}         [description]
		 */
		renderMarker : function( options, location, mapObject ) {

			// This script was used with the google.maps.Marker class before it was announced deprecated and GEO my WP moved to using google.maps.marker.AdvancedMarkerElement instead.
			var self = mapObject;

			if (gmwVars.googleAdvancedMarkers) {

				var iconElem = document.createElement('img'),
					iconUrl = options.icon || self.iconUrl,
					iconSize = [],
					marker_options = {
						position: options.position,
						map: self.map,
						title: location.title || '',
					}

				// Collect icon size when provided per icon.
				if (location.icon_size) {

					iconSize = [parseInt(location.icon_size[0]), parseInt(location.icon_size[1])];

					// When need to collect global icon size ( all icons based on same size ).
					// That is if icon size provided or when using the default red icon.
				} else if (self.iconSize || iconUrl == gmwVars.defaultIcons.location_icon_url) {

					// first we check if scaled size already provided in global.
					// We do this to prevent scalling each icon to the same size.
					// Just to save even a bit on perfoarmance, spacially when having many icons.
					if (!self.iconScaledSize) {

						// set icon size in global
						if (self.iconSize) {
							self.iconScaledSize = [parseInt(self.iconSize[0]), parseInt(self.iconSize[1])];
						} else {
							self.iconScaledSize = [parseInt(gmwVars.defaultIcons.location_icon_size[0]), parseInt(gmwVars.defaultIcons.location_icon_size[1])];
						}
					}

					// get icon size from global.
					iconSize = self.iconScaledSize;
				}

				// Icon URL.
				iconElem.src = iconUrl;

				// Icon size when provided.
				if (iconSize.length != 0) {
					iconElem.width = iconSize[0];
					iconElem.height = iconSize[1];
				}

				// Generate icon ID and Class attributes.
				if (typeof location.object_id !== 'undefined') {

					iconElem.id = 'gmw-map-icon-' + location.object_id;
					iconElem.className = 'gmw-map-icon-object-' + location.object_id;

					if (typeof location.location_id !== 'undefined') {
						iconElem.id += '-' + location.location_id;
						iconElem.className += ' ' + 'gmw-map-icon-location-' + location.location_id;
					}
				}

				// Icon element.
				marker_options.content = iconElem;

				var gmwData = {
					markerCount: options.id,
					locationID: location.location_id || 0,
					iwContent: options.content,
					objectType: location.object_type || '',
					objectId: location.object_id || 0,
					iconUrl: iconUrl,
					bounceEvent: location.bounce_event || 'disabled',
					openIwEvent: location.open_iw_event || 'disabled',
					scrollToItem: location.scroll_to_item || 'disabled',
				};

				// modify marker options.
				marker_options = GMW.apply_filters('gmw_generate_marker_options', marker_options, options.id, self, location);

				var marker = new google.maps.marker.AdvancedMarkerElement(marker_options);

			} else {

				var icon = {
					url: options.icon || self.iconUrl
				};

				// Scale icon size when provided per icon.
				if (location.icon_size) {

					icon.scaledSize = new google.maps.Size(parseInt(location.icon_size[0]), parseInt(location.icon_size[1]));

					// When need to scale all icons based on same size.
					// That is if icon size provided or when using the default red icon.
				} else if (self.iconSize || icon.url == gmwVars.defaultIcons.location_icon_url) {

					// first we check if scaled size already provided in global.
					// We do this to prevent scalling each icon to the same size.
					// Just to save even a bit on perfoarmance, spacially when having many icons.
					if (!self.iconScaledSize) {

						// set icon size in global
						if (self.iconSize) {
							self.iconScaledSize = new google.maps.Size(parseInt(self.iconSize[0]), parseInt(self.iconSize[1]));
						} else {
							self.iconScaledSize = new google.maps.Size(parseInt(gmwVars.defaultIcons.location_icon_size[0]), parseInt(gmwVars.defaultIcons.location_icon_size[1]));
						}
					}

					// get icon size from global.
					icon.scaledSize = self.iconScaledSize;
				}

				var marker_options = {
					position: options.position,
					icon: icon,
					map: self.map,
					animation: null,
					title: 'gmw-map-icon',

					//location_id  : options.id, //deprecated. use gmwData.markerCount instead.
					//iw_content   : options.content // //deprecated. use gmwData.iwContent instead.
				};

				var gmwData = {
					markerCount: options.id,
					locationID: location.location_id || 0,
					iwContent: options.content,
					objectType: location.object_type || '',
					objectId: location.object_id || 0,
					iconUrl: icon.url,
					bounceEvent: location.bounce_event || 'disabled',
					openIwEvent: location.open_iw_event || 'disabled',
					scrollToItem: location.scroll_to_item || 'disabled',
				};

				// modify marker options.
				marker_options = GMW.apply_filters('gmw_generate_marker_options', marker_options, options.id, self, location);

				var marker = new google.maps.Marker(marker_options);
			}

			/*setTimeout( function() {

				if ( options.id == 'user_marker' ) {
					jQuery( '#gmw-map-' + self.id ).find( 'img[src="' + icon.url + '"]' ).addClass( 'gmw-user-map-icon' );
				} else {
					jQuery( '#gmw-map-' + self.id ).find( 'img[src="' + icon.url + '"]' ).addClass( 'gmw-map-icon' );
				}
			}, 100 );*/

			marker.gmwData = gmwData;

			// ON marker.
			marker.addListener('mouseover', function() {
				self.markerEvents( 'mouseover', marker );
			});

			marker.addListener('mouseout', function() {
				self.markerEvents( 'mouseout', marker );
			});

			marker.addListener('click', function() {
				self.markerEvents( 'click', marker );
			});


			if (gmwVars.googleAdvancedMarkers) {

				// Bounce animation.
				if (gmwData.bounceEvent == 'hover' || GMW.apply_filters('gmw_bounce_marker_on_result_hover', false, marker, self) ) {

					jQuery('.gmw-object-' + marker.gmwData.objectId).hover(function () {
						marker.content.classList.add("gmw-marker-bounce");
					},
					function() {
						marker.content.classList.remove("gmw-marker-bounce");
					});

				} else if (gmwData.bounceEvent == 'click') {

					jQuery('.gmw-object-' + marker.gmwData.objectId).click(function () {
						marker.content.classList.add("gmw-marker-bounce");
					});

					jQuery('.gmw-object-' + marker.gmwData.objectId).mouseout(function () {
						marker.content.classList.remove("gmw-marker-bounce");
					});
				}

			} else {

				// Bounce animation.
				if (gmwData.bounceEvent == 'hover' || GMW.apply_filters('gmw_bounce_marker_on_result_hover', false, marker, self) ) {

					jQuery( '.gmw-object-' + marker.gmwData.objectId ).hover( function () {
						marker.setAnimation( google.maps.Animation.BOUNCE );
					},
					function() {
						marker.setAnimation(null);
					});

				} else if (gmwData.bounceEvent == 'click') {

					jQuery('.gmw-object-' + marker.gmwData.objectId).click(function () {
						marker.setAnimation(google.maps.Animation.BOUNCE);
					});

					jQuery('.gmw-object-' + marker.gmwData.objectId).mouseout(function () {
						marker.setAnimation(null);
					});
				}
			}

			// result item mouse events.
			jQuery(document).on('mouseenter mouseleave click', '.gmw-object-' + marker.gmwData.objectId, function (event) {
				self.resultItemEvents(event.type, jQuery(this), marker);
			});

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
		moveMarker : function( markerPosition, i ) {

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
							content  : '<div class="gmw-element-wrapper gmw-standard-info-window gmw-info-window standard map-' + self.id + ' ' + self.prefix + '">' + marker.gmwData.iwContent + '</div>',
							ariaLabel : 'gmw-location-iw',
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

	L.Map.include({
	  	_initControlPos: function () {
		    var corners = this._controlCorners = {},
		   		l = 'leaflet-',
		      	container = this._controlContainer =
		        L.DomUtil.create('div', l + 'control-container', this._container);

		    function createCorner(vSide, hSide) {
		    	var className = l + vSide + ' ' + l + hSide;

		    	corners[vSide + hSide] = L.DomUtil.create('div', className, container);
		    }

		    createCorner('top', 'left');
		    createCorner('top', 'right');
		    createCorner('bottom', 'left');
		    createCorner('bottom', 'right');

		    createCorner('top', 'center');
		    createCorner('middle', 'center');
		    createCorner('middle', 'left');
		    createCorner('middle', 'right');
		    createCorner('bottom', 'center');
	  	}
	});

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

		getBounds : function( mapObject ) {

			if ( typeof mapObject === 'undefined' ) {
				mapObject = this;
			}

			var bounds = mapObject.map.getBounds();

			return {
				southWest : {
					lat : bounds._southWest.lat,
					lng : bounds._southWest.lng,
				},
				northEast : {
					lat : bounds._northEast.lat,
					lng : bounds._northEast.lng,
				},
			};
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

			// info window opsions. Can be modified with the filter.
			var info_window_options = GMW.apply_filters( 'gmw_user_info_window_options', {
				content  : content,
				maxWidth : 'auto',
				minWidth : 250,
				className : 'gmw-user-iw',
			}, self );

			// generate new window
			self.userInfoWindow = L.popup( info_window_options ).setContent( info_window_options.content );

			marker.bindPopup( self.userInfoWindow );

			//self.userInfoWindow = L.popup( info_window_options ).setContent( content );
			// Open info-window on page load.
			if ( mapObject.userLocation.iw_open == true ) {

				setTimeout( function() {
					marker.openPopup();
				}, 500 );
			}
		},

		// Rezise map to fit its wrapping element.
		resizeMap : function( map, mapObject ) {

			if ( typeof mapObject === 'undefined' ) {
				mapObject = this;
				map       = mapObject.map;
			}

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

			//options.maxZoom 	    = 18;
			options.scrollWheelZoom = mapObject.options.scrollwheel;

			if ( mapObject.settings.map_bounderies.length != 0 ) {

				var sw = L.latLng( mapObject.settings.map_bounderies[0], mapObject.settings.map_bounderies[1] ),
					ne = L.latLng( mapObject.settings.map_bounderies[2], mapObject.settings.map_bounderies[3] );

				options.maxBounds = L.latLngBounds( sw, ne );
			}

			return options;
		},

		/**
		 * Move marker.
		 *
		 * Sligtly move markers that are on the same exact position.
		 *
		 * @return {[type]} [description]
		 */
		moveMarker : function( markerPosition, i ) {

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
				//jQuery( '#gmw-map-loader-' + self.id ).fadeOut( 1000 );
				self.wrapElement.find( '.gmw-map-loader' ).fadeOut( 500 );

				// create map expand toggle if needed
				// temporary disabled. It seems that Google added this feature to its API
				if ( self.options.resizeMapControl && jQuery( '#gmw-resize-map-toggle-' + self.id ).length != 0 ) {

					// generate resize toggle
					jQuery( '#gmw-resize-map-toggle-' + self.id ).detach().appendTo( jQuery( '#' + self.mapElement ).find( '.leaflet-top.leaflet-right' ) ).addClass( 'leaflet-control leaflet-bar' ).show();

					self.fullScreenToggle();
				}

				if ( jQuery( '#gmw-map-position-filter-wrapper-' + self.id ).length != 0 ) {
					jQuery( '#gmw-map-position-filter-wrapper-' + self.id ).detach().appendTo( jQuery( '#' + self.mapElement ).find( '.leaflet-top.leaflet-center' ) ).addClass( 'leaflet-control leaflet-bar' ).show();
				}

				// Close info-window when clicking on the map.
				self.map.on( 'click dblclick,', function( event ) {
				    self.closeInfoWindow();
				});

				self.map.on( 'zoomend', function(e){
					self.zoomChanged( self );
				});

				self.map.on( 'dragend', function(e){
					self.dragged( self );
				});

				// generate user marker
				self.renderUserMarker();

				// generate new markers
				self.renderMarkers( self.locations, false );

				GMW.do_action( 'gmw_map_loaded', self.map, self );
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
				    iconAnchor	 : [ iconSize[0] / 2, iconSize[1] ], // caculate anchor based on icon size
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
					objectType  : location.object_type || '',
					objectId: location.object_id || 0,
					bounceEvent: location.bounce_event || 'disabled',
					openIwEvent: location.open_iw_event || 'disabled',
					scrollToItem: location.scroll_to_item || 'disabled',
				};

			// Modify marker options.
			marker_options = GMW.apply_filters( 'gmw_generate_marker_options', marker_options, options.id, options, this );

			// generate Icon
			marker_options.icon = L.icon( marker_options.iconOptions );

			var marker = L.marker( [ options.position.lat, options.position.lng ], marker_options );

			marker.gmwData = gmwData;

			jQuery(document).on('mouseenter mouseleave click', '.gmw-object-' + marker.gmwData.objectId, function (event) {
				self.resultItemEvents(event.type, jQuery(this), marker);
			});

			// ON marker.
			marker.on('mouseover mouseout click', function (event) {
				self.markerEvents( event.type, marker );
			});

			/*var hoverBounce = false,
				clickBounce = false;

			if (typeof location.bounce_event !== 'undefined') {

				if ( location.bounce_event == 'hover' ) {
					hoverBounce = true;
				} else if ( location.bounce_event == 'click' ) {
					clickBounce = true;
				}
			}

			hoverBounce = GMW.apply_filters('gmw_bounce_marker_on_result_hover', hoverBounce, marker, self);

			// bounce marker on hover.
			if ( hoverBounce ) {

				jQuery( '.gmw-object-' + marker.gmwData.objectId ).hover( function () {
				     marker.setAnimation( google.maps.Animation.BOUNCE );
				},
				function() {
				    marker.setAnimation(null);
				});
			} else if ( clickBounce ) {

				jQuery('.gmw-object-' + marker.gmwData.objectId).click(function () {
					marker.setAnimation(google.maps.Animation.BOUNCE);
				});

				jQuery('.gmw-object-' + marker.gmwData.objectId).mouseout(function () {
					marker.setAnimation(null);
				});
			}*/

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
							content  : '<div class="gmw-element-wrapper gmw-standard-info-window gmw-info-window standard map-' + self.id + ' ' + self.prefix + '">' + marker.gmwData.iwContent + '</div>',
							maxWidth : 'auto',
							minWidth : 250,
							className : 'gmw-location-iw',
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

		self.map.panTo(self.getPosition(self.markers[0], self));

		setTimeout(function () {
			if (self.autoZoomLevel) {
				self.map.setZoom( 13 );
			} else {
				self.map.setZoom( parseInt( self.options.zoom ) );
			}
		}, 300);

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

	// For scenarios where locations is an object instead of array.
	if ( ! Array.isArray( locations ) ) {
		locations = Object.keys(locations).map(function (key) { return locations[key]; });
	}

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

	//self.options = jQuery.extend( {}, self.getMapOptions( self ), self.options );

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

GMW_Map.prototype.dragged = function( self ) {
	GMW.do_action( 'gmw_map_dragged', self.map, self );
	GMW.do_action( 'gmw_map_position_changed', self.map, self );
};

GMW_Map.prototype.zoomChanged = function( self ) {
	GMW.do_action( 'gmw_map_zoom_changed', self.map, self );
	GMW.do_action( 'gmw_map_position_changed', self.map, self );
};

GMW_Map.prototype.boundsChanged = function (self) {
	GMW.do_action( 'gmw_map_bounds_changed', self.map, self );
	//GMW.do_action( 'gmw_map_position_changed', self.map, self );
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

	// For scenarios where locations is an object instead of array.
	if ( ! Array.isArray( locations ) ) {
		locations = Object.keys(locations).map(function (key) { return locations[key]; });
	}

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

		if ( ! self.userLocation.icon_size && ( self.userLocation.map_icon == gmwVars.defaultIcons.user_location_icon_url || self.userLocation.map_icon.indexOf( 'defaultUserMarker' ) > -1 ) ) {
			self.userLocation.icon_size = self.userIconSize;
		}

		// generate marker
		var markerOptions = {
			position 	 : self.userPosition,
			icon     	 : self.userLocation.map_icon,
			id   	 	 : 'user_marker',
			content  	 : '',
		};

		// generate marker
		self.userMarker = self.renderMarker( markerOptions, self.userLocation, self );

		//jQuery( '#gmw-map-' + self.id ).attr( 'data-user_icon', self.userMarker.icon.url );

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

GMW_Map.prototype.renderMarkers = function(locations, append_previous) {
    var self = this;

    if (!Array.isArray(locations)) {
        locations = Object.keys(locations).map(key => locations[key]);
    }

    GMW.do_action('gmw_map_pre_render_markers', locations, this);
    self.initMarkersGrouping();

    self.locations = locations;
    self.previousLocations = append_previous ? [...self.locations, ...self.previousLocations] : self.locations;

    var locations_count = self.locations.length;
    if (locations_count === 0 && self.hideMapWithoutLocations) {
        this.wrapElement.slideUp();
    }

    var markersArray = [];

    for (var i = 0; i < locations_count; i++) {
        if (!self.locations[i].lat || !self.locations[i].lng || self.locations[i].lat === '0.000000' || self.locations[i].lng === '0.000000') {
            continue;
        }

        var markerPosition = self.latLng(self.locations[i].lat, self.locations[i].lng, self);

        if (self.moveMarkersEnabled) {
            var markerCoords = self.locations[i].lat + self.locations[i].lng;
            if (self.coordsCollector.includes(markerCoords)) {
                markerPosition = self.moveMarker(markerPosition, i);
            } else {
                self.coordsCollector.push(markerCoords);
            }
        }

        self.bounds.extend(markerPosition);

        var markerOptions = {
            position: markerPosition,
            icon: self.locations[i].map_icon || gmwVars.defaultIcons.location_icon_url,
            id: i,
            content: self.locations[i].info_window_content,
        };

        var marker = self.renderMarker(markerOptions, self.locations[i], self);

		if (self.markerGrouping !== 'markers_clusterer') {
			self.addMarker(marker, self);
		}

		self.markers[i] = marker;

        markersArray.push(marker); // Store for clustering later

		self.markerGroupingTypes[self.markerGrouping].markerClick( marker, self );

        GMW.do_action('gmw_map_markers_loop_single_marker', marker, self.locations[i], self);
    }

    // Apply clustering only after adding markers individually
    if (self.markerGrouping === 'markers_clusterer' && markersArray.length > 0) {
		if (self.provider === 'leaflet') {
			self.clusters.addLayers(markersArray); // Leaflet batching
		} else {
			self.clusters.addMarkers(markersArray, self); // Google Maps batching
		}
	}

    GMW.do_action('gmw_map_after_render_markers', locations, this);
    if (!self.disableMapCente && (locations_count > 0 || self.userMarker)) {
        if (!GMW.apply_filters('gmw_map_disable_map_center', false, self)) {
            self.centerMap();
        }
	}
};

/**
 * Generate markers.
 *
 * @return {[type]} [description]
 */
GMW_Map.prototype.renderMarkers_legacy = function( locations, append_previous ) {

	var self = this;

	// For scenarios where locations is an object instead of array.
	if ( ! Array.isArray( locations ) ) {
		locations = Object.keys(locations).map(function (key) { return locations[key]; });
	}

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
	for ( var i = 0; i < locations_count + 1 ; i++ ) {

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
	 				markerPosition = self.moveMarker( markerPosition, i );
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
				content      : self.locations[i].info_window_content,
			};

			// generate marker
			self.markers[i] = self.renderMarker( markerOptions, self.locations[i], self );

			// add the marker to the map.
			self.markerGroupingTypes[self.markerGrouping].addMarker( self.markers[i], self );

			// add marker to grouping
			self.markerGroupingTypes[self.markerGrouping].markerClick( self.markers[i], self );

			// hook custom functions if needed.
			GMW.do_action('gmw_map_markers_loop_single_marker', self.markers[i], self.locations[i], self);

			/*var thisMarker = self.markers[i];
			var oo = i;
			jQuery(document).on('mouseenter mouseleave click', '.gmw-object-' + self.markers[i].gmwData.objectId, function (event) {
				console.log(i)
console.log(self.markers[i].gmwData)
				self.resultItemEvents(event.type, jQuery(this), thisMarker, location);
			});*/

			/*jQuery(document).on('mouseenter', '.gmw-object-' + self.markers[i].gmwData.objectId, function () {
				//self.resultItemHover(jQuery(this), thisMarker);
				alert('1')
		   	},
				function () {
				alert('2')
				//marker.setAnimation(null);
			});

			jQuery(document).on('click', '.gmw-object-' + self.markers[i].gmwData.objectId, function () {
				self.resultItemClick(jQuery(this), thisMarker);
			});*/

		// proceed when done generating the markers.
		} else {

			/*if ( typeof self.gmw_form.map_markers.usage !== 'undefined' && ( self.gmw_form.map_markers.usage == 'image' || self.gmw_form.map_markers.usage == 'avatar' ) ) {

				// Add custom class to map icons.
				// This takes place only when the map icons are set to featured image.
				// To allow us to add a bit of styling.
				setTimeout( function() {
					jQuery.each( self.markers, function( index, mOptions ) {
						jQuery( '#gmw-map-' + self.id ).find( 'img[src="' + mOptions.gmwData.iconUrl + '"]' ).addClass( 'gmw-map-icon' );
					});
				}, 200 );
			}*/

			// hook custom functions if needed
			GMW.do_action( 'gmw_map_after_render_markers', locations, this );

			// center map only if locations or user location exist.
			if ( ! self.disableMapCente && ( locations_count > 0 || self.userMarker != false ) ) {

				if (!GMW.apply_filters('gmw_map_disable_map_center', false, self)) {
					self.centerMap();
				}
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

	self.openInfoWindow(marker);

	/*if ( marker.gmwData.scrollToItem == 'click') {

		var elementId = '#gmw-single-' + marker.gmwData.objectType + '-' + marker.gmwData.objectId;

		if ($(elementId).length) {

			//$(elementId).addClass('marker-hover');
			$('html, body').animate({ scrollTop: $(elementId).offset().top - 10 }, 400);
		}
	}*/
};

GMW_Map.prototype.markerEvents = function (event, marker) {

	if (marker.gmwData.scrollToItem == 'disabled') {
		return;
	}

	var self = this;
	var gmwData = marker.gmwData;
	var scrollEvent = gmwData.scrollToItem;
	var element = jQuery('#gmw-single-' + gmwData.objectType + '-' + gmwData.objectId);
	var parent = element.closest('.gmw-results-list');

	if (element.length ) {

		function isScrollable(el) {
			return el.scrollHeight > el.clientHeight;
		}

		function scrollToElement(targetElement, scrollParent) {
			if (isScrollable(scrollParent[0])) {
				// Scroll the parent element
				scrollParent
					.animate(
						{ scrollTop: scrollParent.scrollTop() + targetElement.position().top },
						{
							duration: 'medium',
							easing: 'swing',
						}
					)
					.promise()
					.done(function () {
						targetElement.removeClass('item-scrolling');
					});
			} else {
				// Scroll the screen (html, body)
				jQuery('html, body')
					.animate(
						{ scrollTop: targetElement.offset().top - jQuery(window).height() / 2 },
						{
							duration: 'medium',
							easing: 'swing',
						}
					)
					.promise()
					.done(function () {
						targetElement.removeClass('item-scrolling');
					});
			}
		}

		if (event === 'mouseover') {
			element.addClass('marker-hover');

			if (scrollEvent === 'hover') {
				element.addClass('item-scrolling');
				scrollToElement(element, parent);
			}

		} else if (event === 'click') {

			if (scrollEvent === 'click') {
				element.addClass('item-scrolling');
				scrollToElement(element, parent);
			}

		} else if (event === 'mouseout') {
			element.removeClass('marker-hover');
		}
	}

	GMW.do_action('gmw_map_marker_' + event + '_event', marker);
}

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
 * Results item events ( mouseenter, mouseleave, click )
 *
 * @param {object} event
 *
 * @param {object} item
 *
 * @param {object} marker
 */
GMW_Map.prototype.resultItemEvents = function( event, item, marker ) {

	var self = this;

	if (event == 'mouseenter') {

		if (marker.gmwData.openIwEvent == 'hover') {
			self.openInfoWindow(marker);
		}

	} else if (event == 'mouseleave') {

		if (marker.gmwData.openIwEvent == 'hover') {
			self.closeInfoWindow();
		}

	} else if (event == 'click') {

		if (marker.gmwData.openIwEvent == 'click') {

			if (self.clusters && typeof self.clusters.zoomToShowLayer === 'function') {

				self.clusters.zoomToShowLayer(marker, function () {

					// Once the cluster is expanded, center the map on the marker
					self.setCenter(self.getPosition( marker, self ), self );
					// Optionally, open the marker's info window or perform other actions
					self.openInfoWindow(marker);
				});
			} else {

				self.openInfoWindow(marker);
			}

			// Assuming you have a 'markerClusterer' variable holding your MarkerClusterer instance

			// When a user clicks on a button or performs an action to view a specific marker:

			//viewSpecificMarker("marker-id-to-view");

			//self.openInfoWindow(marker);
		}
	}

	GMW.do_action('gmw_map_result_item_' + event + '_event', item, marker);
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

function gmwInitMaps() {

	setTimeout(function () {

		jQuery.each(gmwMapObjects, function (map_id, vars) {

			if (vars.settings.render_on_page_load) {

				// generate new map
				GMW_Maps[map_id] = new GMW_Map(vars.settings, vars.map_options, vars.form);
				// initiate it
				GMW_Maps[map_id].render(vars.locations, vars.user_location);
			}
		});
	}, 500);
}

/**
 * On document ready generate all maps exists in the global maps holder
 *
 * @param  {GMW_Map}
 * @return {[type]}       [description]
 */
jQuery(document).ready(function ($) {

	//setTimeout(function () {

	if (typeof gmwMapObjects == 'undefined') {
		return;
	}

	/**
	 * Verify that Google Maps loaded before running the main script.
	 *
	 * @param {int} retries
	 * @param {int} delay
	 *
	 * @returns
	 */
	function gmwMapsLoader(retries = 10, delay = 100) {

		if (gmwVars.mapsProvider === 'google_maps') {

			// Retry only a limited number of times.
			if (retries === 0) {

				console.error("Google Maps API failed to load.");

				return;
			}

			// Google Maps loaded, proceed with scripts.
			if (window.google && window.google.maps && google.maps.importLibrary) {

				if (gmwVars.googleAdvancedMarkers) {

					async function gmwInitMapsAsync() {

						await google.maps.importLibrary("marker");

						gmwInitMaps();
					}

					gmwInitMapsAsync();

				} else {
					gmwInitMaps();
				}

				// Google Maps is not loaded yet, try again.
			} else {

				console.log(`Google Maps not loaded, retrying in ${delay}ms...)`);

				setTimeout(() => gmwMapsLoader(retries - 1, delay), delay);
			}
		} else {

			gmwInitMaps();
		}
	}

	gmwMapsLoader();

	/*} else {


	}



		if (gmwVars.mapsProvider === 'google_maps' && gmwVars.googleAdvancedMarkers) {

			async function gmwInitMaps() {

				await google.maps.importLibrary("marker");

				// loop through and generate all maps
			jQuery.each(gmwMapObjects, function (map_id, vars) {

				if (vars.settings.render_on_page_load) {

					// generate new map
					GMW_Maps[map_id] = new GMW_Map(vars.settings, vars.map_options, vars.form);
					// initiate it
					GMW_Maps[map_id].render(vars.locations, vars.user_location);
				}
			});
			}

			gmwInitMaps();

		} else {

			// loop through and generate all maps
			jQuery.each(gmwMapObjects, function (map_id, vars) {

				if (vars.settings.render_on_page_load) {

					// generate new map
					GMW_Maps[map_id] = new GMW_Map(vars.settings, vars.map_options, vars.form);
					// initiate it
					GMW_Maps[map_id].render(vars.locations, vars.user_location);
				}
			});
		}
	//}, 200);*/

	// Render maps generated during an AJAX call.
	/*jQuery(document).ajaxComplete( function ( event, request, settings ) {

		jQuery.each( gmwMapObjects, function( map_id, vars ) {

			var map_id = vars.settings.map_id;

			if ( typeof GMW_Maps[map_id] === 'undefined' || ! jQuery( '#gmw-map-' + map_id ) ) {

				// generate new map
				GMW_Maps[map_id] = new GMW_Map(vars.settings, vars.map_options, vars.form);

				// initiate it
				GMW_Maps[map_id].render(vars.locations, vars.user_location);
			}
		});
	});*/
});
