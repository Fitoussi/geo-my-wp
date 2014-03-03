jQuery(document).ready(function($){ 
	
	function flMapInit( flMapArgs ) {
	
		var zoomLevel = ( gmwForm.results_map['zoom_level'] == 'auto' ) ? 13 : gmwForm.results_map['zoom_level'];
		
		var membersMap = new google.maps.Map(document.getElementById('gmw-map-'+ flMapArgs.ID), {
			zoom: parseInt(zoomLevel),
			panControl: true,
	  		zoomControl: true,
	  		mapTypeControl: true,
			center: new google.maps.LatLng(flMapArgs.your_lat, flMapArgs.your_lng),
			mapTypeId: google.maps.MapTypeId[flMapArgs.results_map['map_type']],
		});	
		
		var latlngbounds = new google.maps.LatLngBounds();
		
		if ( flMapArgs.your_lat && flMapArgs.your_lng ) {
			var yourLocation  = new google.maps.LatLng( flMapArgs.your_lat, flMapArgs.your_lng );
			latlngbounds.extend(yourLocation);
			
			marker = new google.maps.Marker({
				position: new google.maps.LatLng( flMapArgs.your_lat, flMapArgs.your_lng ),
				map: membersMap,
				icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
			});
		}
	
		var i;
		var fliw = false;
		mMarkers = [];
		
		for ( i = 0; i < flMapArgs.results.length; i++ ) { 
			var mapIcon, shadow;
		
			var memberLocation = new google.maps.LatLng( flMapArgs.results[i]['lat'], flMapArgs.results[i]['long'] );
			latlngbounds.extend(memberLocation);
			
			mapIcon = flMapArgs.results[i]['mapIcon'];
				
			mMarkers[i] = new google.maps.Marker({
				position: memberLocation,
				icon:mapIcon,
				map:membersMap,
				id:i   
			});
		
			with ({ mMarker: mMarkers[i] }) {
				google.maps.event.addListener( mMarker, 'click', function() {
					if (fliw) {
						fliw.close();
						fliw = null;
					}
					fliw = new google.maps.InfoWindow({
						content: getFLIWContent( mMarker.id ),
					});
					fliw.open( membersMap, mMarker ); 		
				});
			}
		}
		if ( gmwForm.results_map['zoom_level'] == 'auto' || ( gmwForm.your_lat == false && gmwForm.your_lng == false ) ) membersMap.fitBounds(latlngbounds);
		
		if ( flMapArgs.results.length == 1 && !flMapArgs.your_lat ) {
			blistener = google.maps.event.addListener( ( membersMap ), 'bounds_changed', function(event) {  
				this.setZoom(11);
				google.maps.event.removeListener(blistener);
			});
		}
					
		function getFLIWContent(i) {
			
			var content = "";
			content +=	'<div class="wppl-fl-info-window">';
			content +=  	'<div class="wppl-info-window-thumb">' + flMapArgs.results[i]['avatar'] + '</div>';
			content +=		'<div class="wppl-info-window-info">';
			content +=			'<table>';
			content +=				'<tr><td><div class="wppl-info-window-permalink"><a href="' + flMapArgs.results[i]['permalink'] + '">' + flMapArgs.results[i]['display_name'] + '</a></div></td></tr>';
			content +=				'<tr><td><span>'+flMapArgs['iw_labels']['address']+'</span>' + flMapArgs.results[i]['address'] + '</td></tr>';
			if ( gmwForm.org_address != false ) 
				content +=				'<tr><td><span>'+flMapArgs['iw_labels']['distance']+'</span>' + flMapArgs.results[i]['distance'] + ' ' + flMapArgs.units_array['name'] + '</td></tr>';
			content +=			'</table>';
			content +=		'</div>';
			content +=  '</div>';
			return content;
		} 
	}
	
	$( '#gmw-map-wrapper-'+gmwForm.ID ).slideToggle(function() {
		flMapInit( gmwForm );
	});
});	