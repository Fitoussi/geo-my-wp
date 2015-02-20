/**
 * GMW main map function
 * @param gmwForm
 */
function gmwMapInit( gmwForm ) {

	//create global maps object if was not created already
	if ( typeof gmwMapObjects == 'undefined' ) {
		gmwMapObjects = {};
	}
	
	var i;
	var formId			= gmwForm.ID;
	var gmwMapMarkers  	= [];
	var zoomLevel  		= ( gmwForm.results_map['zoom_level'] == 'auto' ) ? 13 : gmwForm.results_map['zoom_level'];
	var gmwUserLocation = false;
	var latlngbounds 	= new google.maps.LatLngBounds();
	
	//pass global mapObject to be used outside this file
	gmwMapObjects[formId] = {
			form:gmwForm,
			mapId:formId,
			mapElement:'gmw-map-'+formId,
			mapType:gmwForm.prefix + 'Map'
	};
		    
	//map options
	var mapOptions = {
		zoom: parseInt(zoomLevel),
		draggable: true,
		panControl: true,
  		zoomControl: true,
  		mapTypeControl: true,
  		mapTypeControlOptions: {
	        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
	        position: google.maps.ControlPosition.TOP_RIGHT
	    },
	    center: new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng ),
		mapTypeId: google.maps.MapTypeId[gmwForm.results_map['map_type']],
		styles: gmwMapObjects[formId]['mapStyle']
	};			
	
	//map styles
	gmwMapOptions = ( "undefined" != typeof gmwMapOptions ) ? gmwMapOptions : {};
	if ( "undefined" != typeof gmwMapOptions[formId] ) {
		jQuery.extend(mapOptions, gmwMapOptions[formId] );
	}
	
	//create the map
	gmwMapObjects[formId]['map'] = new google.maps.Map(document.getElementById( 'gmw-map-' + formId ), mapOptions );
	
	//initiate markers clusterer or spiderfier if needed
	if (  "undefined" == typeof gmwForm.results_map.markers_display || gmwForm.results_map.markers_display != 'markers_clusterer' ) {  	
		gmwForm.results_map.markers_display = 'none';
	}
	
    if ( gmwForm.results_map.markers_display == 'markers_clusterer' ) {  	 
		 psMc = new MarkerClusterer( gmwMapObjects[formId]['map'], gmwMapMarkers );	
    }
    
	//create markers for locations
	for ( i = 0; i < gmwForm.results.length; i++ ) {  
		
		//if ( gmwForm.results[i]['lat'] == null || gmwForm.results[i]['long'] == null || gmwForm.results[i]['lat'] == '0.000000' || gmwForm.results[i]['long'] == '0.000000' )
			//continue;
		
		var gmwLocation;
		gmwLocation = new google.maps.LatLng( gmwForm.results[i]['lat'], gmwForm.results[i]['long'] );
	
		//offset markers with same location
		if ( latlngbounds.contains(gmwLocation) ) {

			var a = 360.0 / gmwForm.results.length;
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
	        
	        markerPath.setMap(gmwMapObjects[formId]['map']);
		}
		
        latlngbounds.extend(gmwLocation);
        
		//console.log(gmwForm.results[i]);		
		gmwMapMarkers[i] = new google.maps.Marker({
			position: gmwLocation,
			icon:gmwForm.results[i]['mapIcon'],
			map:gmwMapObjects[formId]['map'],
			id:i 
		});
				
		 //add marker to clustere if needed
		if ( gmwForm.results_map.markers_display == 'markers_clusterer' )	{						
			psMc.addMarker( gmwMapMarkers[i] );							
		} else {		
			gmwMapMarkers[i].setMap(gmwMapObjects[formId]['map']);			
		}
		
		with ({ gmwMapMarker: gmwMapMarkers[i] }) {
			var ptiw = false;
			google.maps.event.addListener( gmwMapMarker, 'click', function() {
				if (ptiw) {
					ptiw.close();
					ptiw = null;
				}
				ptiw = new google.maps.InfoWindow({
					content: gmwForm.results[this.id].info_window_content
				});
				ptiw.open( gmwMapObjects[formId]['map'], gmwMapMarker );	 		
			});
		}
	}
	
	gmwMapObjects[formId]['markers'] = gmwMapMarkers;
	
	//create user's current location
	if ( gmwForm.your_lat != false && gmwForm.your_lng != false ) {
	
		gmwUserLocation  = new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng );
		latlngbounds.extend(gmwUserLocation);
		
		var yliw = new google.maps.InfoWindow({
			content: gmwForm.labels.info_window.your_location
		});
	      
		ulMarker = new google.maps.Marker({
			position: new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng ),
			map: gmwMapObjects[formId]['map'],
			icon:gmwForm.ul_icon
		});
		
		gmwMapObjects[formId]['userLocation'] = ulMarker;
		
		if ( gmwForm.results_map.yl_icon != undefined && gmwForm.results_map.yl_icon == 1 ) {
			yliw.open( gmwMapObjects[formId]['map'], ulMarker);
		}
		
	    google.maps.event.addListener( ulMarker, 'click', function() {
	    	yliw.open( gmwMapObjects[formId]['map'], ulMarker);
	    });       
	}
	
	gmwMapObjects[formId]['bonds'] = latlngbounds;
				
	//zoom map based on settings
	if ( gmwForm.results.length == 1 && gmwUserLocation == false ) {
		gmwMapObjects[formId]['map'].setZoom(13);
		gmwMapObjects[formId]['map'].panTo(gmwMapMarkers[0].getPosition());	
	} else if ( gmwForm.results_map.zoom_level != 'auto' && gmwUserLocation !== false ) {
		gmwMapObjects[formId]['map'].setZoom(parseInt(gmwForm.results_map.zoom_level));
		gmwMapObjects[formId]['map'].panTo(gmwUserLocation);
	} else if ( gmwForm.results_map.zoom_level == 'auto' || gmwUserLocation == false  ) { 
		gmwMapObjects[formId]['map'].fitBounds(latlngbounds);
	}
	
	google.maps.event.addListenerOnce(gmwMapObjects[formId]['map'], 'idle', function(){	
		
		//create map expand toggle
		ExpandControl = document.getElementById('gmw-expand-map-trigger-' + formId);
		gmwMapObjects[formId]['map'].controls[google.maps.ControlPosition.TOP_RIGHT].push(ExpandControl);			
		ExpandControl.style.display = 'block';
	
		//expand map function
	    if ( jQuery('#gmw-expand-map-trigger-'+formId).length ) {
	    	
	    	jQuery('#gmw-expand-map-trigger-'+formId).click(function() {
	    		
	    		var mapCenter = gmwMapObjects[formId]['map'].getCenter();
	    		jQuery(this).closest('.gmw-map-wrapper').toggleClass('gmw-expanded-map');          		
	    		jQuery(this).toggleClass('dashicons-editor-expand').toggleClass('dashicons-editor-contract');
	    		
	    		setTimeout(function() { 
	    		
	    			google.maps.event.trigger(gmwMapObjects[formId]['map'], 'resize');
	    			gmwMapObjects[formId]['map'].setCenter(mapCenter);
					
				}, 100);            		
	    	});
	    }
	});
}

jQuery(document).ready(function($){ 		
	//check that map element exists before doing anything
	if ( ( gmwForm.page_load_results_trigger || gmwForm.auto_results_trigger || gmwForm.submitted  ) && $('#gmw-map-wrapper-'+gmwForm.ID).length ) {	
		
		//if map element is hidden show it first
		if ( jQuery('#gmw-map-wrapper-'+gmwForm.ID).is(':hidden') ) {
			jQuery( '#gmw-map-wrapper-'+gmwForm.ID ).slideToggle( 'fast', function() {
				gmwMapInit( gmwForm );
			});
		} else {
			gmwMapInit( gmwForm );
		} 
	}
});