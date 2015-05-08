/**
 * GMW main map function
 * @param gmwForm
 */
function gmwMapInit( values ) {
	
	//make sure the map element exists to prevent JS error
	if ( !jQuery( '#'+values['mapElement'] ).length ) {
		return;
	}

	//map id
	var mapID = values.mapId;
	var markersClustereOk = false;

	//initiate map options
	var mapOptions = {
			zoom: ( values['zoomLevel'] == 'auto' ) ? 13 : parseInt(values['zoomLevel']),
			center: new google.maps.LatLng( values['userPosition']['lat'], values['userPosition']['lng'] ),
			mapTypeId: google.maps.MapTypeId[values.mapTypeId]
	};
	
	//merge custom map options if exsits
	if ( "undefined" != typeof gmwMapOptions[mapID] ) {
		jQuery.extend(mapOptions, gmwMapOptions[mapID] );
	}
			
	//merge map options with global map object
	gmwMapObjects[mapID] = jQuery.extend( true, values, {
			mapOptions: mapOptions,	
			bounds: new google.maps.LatLngBounds(),
			markers:[],
			infoWindow:false,
			resizeMapControl:false
	} );
	
	//create the map
	gmwMapObjects[mapID]['map'] = new google.maps.Map(document.getElementById( gmwMapObjects[mapID]['mapElement'] ), gmwMapObjects[mapID]['mapOptions'] );

	//initiate markers clusterer if needed ( for premium features )
	if ( typeof MarkerClusterer === 'function' && gmwMapObjects[mapID]['markersDisplay'] == 'markers_clusterer' ) { 
		psMc = new MarkerClusterer( gmwMapObjects[mapID]['map'], gmwMapObjects[mapID]['markers'] );	
		markersClustereOk = true;
    }

	//create markers for locations
	for ( i = 0; i < gmwMapObjects[mapID]['locations'].length; i++ ) {  
		
		//make sure location has coordinates to prevent JS error
		if ( gmwMapObjects[mapID]['locations'][i]['lat'] == undefined || gmwMapObjects[mapID]['locations'][i]['long'] == undefined  )
			continue;
		
		var gmwLocation = new google.maps.LatLng( gmwMapObjects[mapID]['locations'][i]['lat'], gmwMapObjects[mapID]['locations'][i]['long'] );
	
		//offset markers with same location
		if ( markersClustereOk == false && gmwMapObjects[mapID]['bounds'].contains(gmwLocation) ) {

			var a = 360.0 / gmwMapObjects[mapID]['locations'].length;
			var orgPosition = gmwLocation;
	        var newLat = orgPosition.lat() + -.0005 * Math.cos((+a*i) / 180 * Math.PI);  // x
	        var newLng = orgPosition.lng() + -.0005 * Math.sin((+a*i) / 180 * Math.PI);  // Y
	        var gmwLocation = new google.maps.LatLng(newLat,newLng);
	        
	        var markerPath = new google.maps.Polyline({
	            path: [orgPosition, gmwLocation],
	            strokeColor: "#FF0000",
	            strokeOpacity: 1.0,
	            strokeWeight: 2
	          });
	        
	        markerPath.setMap(gmwMapObjects[mapID]['map']);
		}
		
		gmwMapObjects[mapID]['bounds'].extend(gmwLocation);
        	
		gmwMapObjects[mapID]['markers'][i] = new google.maps.Marker({
			position: gmwLocation,
			icon:gmwMapObjects[mapID]['locations'][i]['mapIcon'],
			map:gmwMapObjects[mapID]['map'],
			id:i 
		});
				
		 //add marker to clusterer if needed
		if ( gmwMapObjects[mapID]['markersDisplay'] == 'markers_clusterer' ) {						
			psMc.addMarker( gmwMapObjects[mapID]['markers'][i] );							
		} else {		
			gmwMapObjects[mapID]['markers'][i].setMap(gmwMapObjects[mapID]['map'] );			
		}
		
		if ( gmwMapObjects[mapID]['locations'][i]['info_window_content'] != false ) {
			
			//create the info-window object
			google.maps.event.addListener(gmwMapObjects[mapID]['markers'][i], 'click', function() {
				
				if ( gmwMapObjects[mapID]['infoWindow'] ) {
					gmwMapObjects[mapID]['infoWindow'].close();
					gmwMapObjects[mapID]['infoWindow'] = null;
				}
				
				gmwMapObjects[mapID]['infoWindow'] = new google.maps.InfoWindow({
					content: gmwMapObjects[mapID]['locations'][this.id]['info_window_content']
				});
				
				gmwMapObjects[mapID]['infoWindow'].open(gmwMapObjects[mapID]['map'], this);
			});
		}
	}
				
	//create user's location marker
	if ( gmwMapObjects[mapID]['userPosition']['lat'] != false && gmwMapObjects[mapID]['userPosition']['lng'] != false && gmwMapObjects[mapID]['userPosition']['mapIcon'] != false ) {
	
		//user's location
		gmwMapObjects[mapID]['userPosition']['location'] = new google.maps.LatLng( gmwMapObjects[mapID]['userPosition']['lat'], gmwMapObjects[mapID]['userPosition']['lng'] );
		
		//append user's location to bounds
		gmwMapObjects[mapID]['bounds'].extend(gmwMapObjects[mapID]['userPosition']['location']);
		
		//create user's marker
		gmwMapObjects[mapID]['userPosition']['marker'] = new google.maps.Marker({
			position: gmwMapObjects[mapID]['userPosition']['location'],
			map: gmwMapObjects[mapID]['map'],
			icon:gmwMapObjects[mapID]['userPosition']['mapIcon']
		});
		
		//create user's marker info-window
		if ( gmwMapObjects[mapID]['userPosition']['iwContent'] != null ) {
		
			var iw = new google.maps.InfoWindow({
				content: gmwMapObjects[mapID]['userPosition']['iwContent']
			});
		      					
			if ( gmwMapObjects[mapID]['userPosition']['iwOpen'] == true ) {
				iw.open( gmwMapObjects[mapID]['map'], gmwMapObjects[mapID]['userPosition']['marker']);
			}
			
		    google.maps.event.addListener( gmwMapObjects[mapID]['userPosition']['marker'], 'click', function() {
		    	iw.open( gmwMapObjects[mapID]['map'], gmwMapObjects[mapID]['userPosition']['marker']);
		    });     
		}
	}
					
	//after map loaded
	google.maps.event.addListenerOnce(gmwMapObjects[mapID]['map'], 'idle', function(){	

		//custom zoom point
		if ( gmwMapObjects[mapID]['zoomPosition'] != false && gmwMapObjects[mapID]['zoomLevel'] != 'auto' ) {

			gmwMapObjects[mapID]['zoomPosition']['position'] = new google.maps.LatLng( gmwMapObjects[mapID]['zoomPosition']['lat'], gmwMapObjects[mapID]['zoomPosition']['lng'] );
			gmwMapObjects[mapID]['map'].setZoom( parseInt( gmwMapObjects[mapID]['zoomLevel'] ) );
			gmwMapObjects[mapID]['map'].panTo( gmwMapObjects[mapID]['zoomPosition']['position'] );

		} else if ( gmwMapObjects[mapID]['locations'].length == 1 && gmwMapObjects[mapID]['userPosition']['location'] == false ) {

			gmwMapObjects[mapID]['map'].setZoom(13);
			gmwMapObjects[mapID]['map'].panTo(gmwMapObjects[mapID]['markers'][0].getPosition());	

		} else if ( gmwMapObjects[mapID]['zoomLevel'] != 'auto' && gmwMapObjects[mapID]['userPosition']['location'] !== false ) {

			gmwMapObjects[mapID]['map'].setZoom( parseInt( gmwMapObjects[mapID]['zoomLevel'] ) );
			gmwMapObjects[mapID]['map'].panTo(gmwMapObjects[mapID]['userPosition']['location']);

		} else if ( gmwMapObjects[mapID]['zoomLevel'] == 'auto' || gmwMapObjects[mapID]['userPosition']['location'] == false  ) { 

			gmwMapObjects[mapID]['map'].fitBounds(gmwMapObjects[mapID]['bounds']);
		}
		
		//fadeout the map loader if needed
		if ( gmwMapObjects[mapID]['mapLoaderElement'] != false ) {
			jQuery(gmwMapObjects[mapID]['mapLoaderElement']).fadeOut(1000);
		}
		
		//create map expand toggle if needed
		if ( gmwMapObjects[mapID]['resizeMapElement'] != false ) {
			
			gmwMapObjects[mapID]['resizeMapControl'] = document.getElementById(gmwMapObjects[mapID]['resizeMapElement']);
			gmwMapObjects[mapID]['resizeMapControl'].style.position = 'absolute';	
			gmwMapObjects[mapID]['map'].controls[google.maps.ControlPosition.TOP_RIGHT].push(gmwMapObjects[mapID]['resizeMapControl']);			
			gmwMapObjects[mapID]['resizeMapControl'].style.display = 'block';
		
			//expand map function		    	
	    	jQuery('#'+gmwMapObjects[mapID]['resizeMapElement']).click(function() {
	    		
	    		var mapCenter = gmwMapObjects[mapID]['map'].getCenter();
	    		jQuery(this).closest('.gmw-map-wrapper').toggleClass('gmw-expanded-map');          		
	    		jQuery(this).toggleClass('fa-expand').toggleClass('fa-compress');
	    		
	    		setTimeout(function() { 			    		
	    			google.maps.event.trigger(gmwMapObjects[mapID]['map'], 'resize');
	    			gmwMapObjects[mapID]['map'].setCenter(mapCenter);							
				}, 100);            		
	    	});
		}
	});
}

jQuery(document).ready(function($){ 	
	
	if ( typeof gmwMapsHolder != 'undefined' ) {
		
		//create global maps object if was not created already
		if ( typeof gmwMapObjects == 'undefined' ) {
			gmwMapObjects = {};
		}
				
		$.each(gmwMapsHolder, function( index, values ) {
			
			if ( values['triggerMap'] == true ) {
			
				//if map element is hidden show it first
				if ( gmwMapsHolder[index]['hiddenElement'] != false && jQuery(gmwMapsHolder[index]['hiddenElement']).is(':hidden') ) {
					jQuery(gmwMapsHolder[index]['hiddenElement']).slideToggle( 'fast', function() {
						gmwMapInit( values );
					});
				} else {
					gmwMapInit( values );
				} 	
			}
		});		
	}
});