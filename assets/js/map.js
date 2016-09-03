function gmwMapInit( mapObject ) {
	
	//make sure the map element exists to prevent JS error
	if ( !jQuery( '#'+mapObject['mapElement'] ).length ) {
		return;
	}

	//map id
	var mapID = mapObject.mapId;
	var markersClustereOk = false;

	//initiate map options
	mapObject['mapOptions']['zoom'] 	 = ( mapObject['zoomLevel'] == 'auto' ) ? 13 : parseInt( mapObject['zoomLevel'] );
	mapObject['mapOptions']['center'] 	 = new google.maps.LatLng( mapObject['userPosition']['lat'], mapObject['userPosition']['lng'] );
	mapObject['mapOptions']['mapTypeId'] = google.maps.MapTypeId[mapObject['mapOptions']['mapTypeId']];
	mapObject['bounds'] 				 = new google.maps.LatLngBounds();
		
	//merge custom map options if exsits
	if ( "undefined" != typeof gmwMapOptions[mapID] ) {
		jQuery.extend(mapObject['mapOptions'], gmwMapOptions[mapID] );
	}
	
	//create the map
	mapObject['map'] = new google.maps.Map(document.getElementById( mapObject['mapElement'] ), mapObject['mapOptions'] );

	//initiate markers clusterer if needed ( for premium features )
	if ( typeof MarkerClusterer === 'function' && mapObject['markersDisplay'] == 'markers_clusterer' ) { 
		psMc = new MarkerClusterer( mapObject['map'], mapObject['markers'] );	
		markersClustereOk = true;
    }

	//create markers for locations
	for ( i = 0; i < mapObject['locations'].length; i++ ) {  
		
		//make sure location has coordinates to prevent JS error
		if ( mapObject['locations'][i]['lat'] == undefined || ( mapObject['locations'][i]['long'] == undefined && mapObject['locations'][i]['lng'] == undefined ) )
			continue;
		
		if ( mapObject['locations'][i]['long'] == undefined ) {
			mapObject['locations'][i]['long'] = mapObject['locations'][i]['lng'];
		}

		var gmwLocation = new google.maps.LatLng( mapObject['locations'][i]['lat'], mapObject['locations'][i]['long'] );

		// check if marker with the same location already exists
		// if so, we will move it a bit
		if ( mapObject['bounds'].contains( gmwLocation ) ) {
            
            // do the math     
            var a = 360.0 / mapObject['locations'].length;
            var newLat = gmwLocation.lat() + - .00003 * Math.cos( ( +a*i ) / 180 * Math.PI );  //x
            var newLng = gmwLocation.lng() + - .00003 * Math.sin( ( +a*i )  / 180 * Math.PI );  //Y
            var newPosition = new google.maps.LatLng( newLat,newLng );

            // draw a line between the original location 
            // to the new location of the marker after it moves
            var polyline = new google.maps.Polyline( {
			    path: [
			        gmwLocation, 
			        newPosition
			    ],
			    strokeColor: "#FF0000",
			    strokeOpacity: 1.0,
			    strokeWeight: 2,
			    map: mapObject['map']
			});

			var gmwLocation = newPosition;
	   	}
	
		mapObject['bounds'].extend(gmwLocation);
        	
		mapObject['markers'][i] = new google.maps.Marker({
			position: gmwLocation,
			icon:mapObject['locations'][i]['mapIcon'],
			map:mapObject['map'],
			id:i 
		});
				
		 //add marker to clusterer if needed
		if ( markersClustereOk == true && mapObject['markersDisplay'] == 'markers_clusterer' ) {						
			psMc.addMarker( mapObject['markers'][i] );							
		} else {		
			mapObject['markers'][i].setMap(mapObject['map'] );			
		}
		
		if ( mapObject['locations'][i]['info_window_content'] != false && mapObject['locations'][i]['info_window_content'] != undefined ) {
			
			//create the info-window object
			google.maps.event.addListener(mapObject['markers'][i], 'click', function() {
				
				if ( mapObject['infoWindow'] != null ) {
					mapObject['infoWindow'].close();
					mapObject['infoWindow'] = null;
				}
				
				mapObject['infoWindow'] = new google.maps.InfoWindow({
					content: mapObject['locations'][this.id]['info_window_content']
				});
				
				mapObject['infoWindow'].open(mapObject['map'], this);
			});
		}
	}
	
	//create user's location marker
	if ( mapObject['userPosition']['lat'] != false && mapObject['userPosition']['lng'] != false && mapObject['userPosition']['mapIcon'] != false ) {
	
		//user's location
		mapObject['userPosition']['location'] = new google.maps.LatLng( mapObject['userPosition']['lat'], mapObject['userPosition']['lng'] );
		
		//append user's location to bounds
		mapObject['bounds'].extend(mapObject['userPosition']['location']);
		
		//create user's marker
		mapObject['userPosition']['marker'] = new google.maps.Marker({
			position: mapObject['userPosition']['location'],
			map: mapObject['map'],
			icon:mapObject['userPosition']['mapIcon']
		});
		
		//create user's marker info-window
		if ( mapObject['userPosition']['iwContent'] != false && mapObject['userPosition']['iwContent'] != null ) {
		
			var iw = new google.maps.InfoWindow({
				content: mapObject['userPosition']['iwContent']
			});
		      					
			if ( mapObject['userPosition']['iwOpen'] == true ) {
				iw.open( mapObject['map'], mapObject['userPosition']['marker']);
			}
			
		    google.maps.event.addListener( mapObject['userPosition']['marker'], 'click', function() {
		    	iw.open( mapObject['map'], mapObject['userPosition']['marker']);
		    });     
		}
	}
					
	//after map loaded
	google.maps.event.addListenerOnce(mapObject['map'], 'idle', function(){	

		//custom zoom point
		if ( mapObject['zoomPosition'] != false && mapObject['zoomLevel'] != 'auto' ) {

			mapObject['zoomPosition']['position'] = new google.maps.LatLng( mapObject['zoomPosition']['lat'], mapObject['zoomPosition']['lng'] );
			mapObject['map'].setZoom( parseInt( mapObject['zoomLevel'] ) );
			mapObject['map'].panTo( mapObject['zoomPosition']['position'] );

		} else if ( mapObject['locations'].length == 1 && mapObject['userPosition']['location'] == false ) {

			if ( mapObject['zoomLevel'] == 'auto' ) {
				mapObject['map'].setZoom(13);
			} else {
				mapObject['map'].setZoom( parseInt( mapObject['zoomLevel'] ) );
			}

			mapObject['map'].panTo(mapObject['markers'][0].getPosition());	

		} else if ( mapObject['zoomLevel'] != 'auto' && mapObject['userPosition']['location'] != false ) {

			mapObject['map'].setZoom( parseInt( mapObject['zoomLevel'] ) );
			mapObject['map'].panTo(mapObject['userPosition']['location']);

		} else if ( mapObject['zoomLevel'] == 'auto' || mapObject['userPosition']['location'] == false  ) { 

			mapObject['map'].fitBounds(mapObject['bounds']);
		}
		
		//fadeout the map loader if needed
		if ( mapObject['mapLoaderElement'] != false ) {
			jQuery(mapObject['mapLoaderElement']).fadeOut(1000);
		}
		
		//create map expand toggle if needed
		if ( mapObject['resizeMapElement'] != false ) {
			
			mapObject['resizeMapControl'] = document.getElementById(mapObject['resizeMapElement']);
			mapObject['resizeMapControl'].style.position = 'absolute';	
			mapObject['map'].controls[google.maps.ControlPosition.TOP_RIGHT].push(mapObject['resizeMapControl']);			
			mapObject['resizeMapControl'].style.display = 'block';
		
			//expand map function		    	
	    	jQuery('#'+mapObject['resizeMapElement']).click(function() {
	    		
	    		var mapCenter = mapObject['map'].getCenter();
	    		jQuery(this).closest('.gmw-map-wrapper').toggleClass('gmw-expanded-map');          		
	    		jQuery(this).toggleClass('fa-expand').toggleClass('fa-compress');
	    		
	    		setTimeout(function() { 			    		
	    			google.maps.event.trigger(mapObject['map'], 'resize');
	    			mapObject['map'].setCenter(mapCenter);							
				}, 100);            		
	    	});
		}
	});
}

jQuery(document).ready(function($){ 	
	
	if ( typeof gmwMapObjects == 'undefined' ) 
		return false;
						
	$.each(gmwMapObjects, function( index, mapObject ) {
		
		if ( mapObject['triggerMap'] == true ) {
		
			//if map element is hidden show it first
			if ( mapObject['hiddenElement'] != false && jQuery(mapObject['hiddenElement']).is(':hidden') ) {
				jQuery(mapObject['hiddenElement']).slideToggle( 'fast', function() {
					gmwMapInit( mapObject );
				});
			} else {
				gmwMapInit( mapObject );
			} 	
		}
	});		
});