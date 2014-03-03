jQuery(document).ready(function($){ 
	console.log(flMapArgs)
	function flMapInit(flMapArgs, flMembers) {
	
		var membersMap = new google.maps.Map(document.getElementById('gmw-fl-map-'+ flMapArgs.form_id), {
			zoom: 13,
			panControl: true,
	  		zoomControl: true,
	  		mapTypeControl: true,
			center: new google.maps.LatLng(flMapArgs.your_lat, flMapArgs.your_long),
			mapTypeId: google.maps.MapTypeId[flMapArgs.map_type],
		});	
		
		var latlngbounds = new google.maps.LatLngBounds();
		
		if ( flMapArgs.your_lat && flMapArgs.your_long ) {
			var yourLocation  = new google.maps.LatLng(flMapArgs.your_lat, flMapArgs.your_long);
			latlngbounds.extend(yourLocation);
			
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(flMapArgs.your_lat, flMapArgs.your_long),
				map: membersMap,
				icon: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
			});
		}
	
		var i;
		var fliw = false;
		mMarkers = [];
		
		for (i = 0; i < flMembers.length; i++) { 
			var mapIcon, shadow;
		
			var memberLocation = new google.maps.LatLng(flMembers[i]['lat'], flMembers[i]['long']);
			latlngbounds.extend(memberLocation);
			
			mapIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+ flMembers[i]['mc'] +'|FF776B|000000';
			shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
				
			if ( flMapArgs.pin_animation == 1 ) {
				mMarkers[i] = new google.maps.Marker({
					position: memberLocation,
					icon:mapIcon,
					animation: google.maps.Animation.DROP,
					shadow: shadow,
					id:i   
				});
				setTimeout(dropMarker(i), i * 150);	
			} else {
				mMarkers[i] = new google.maps.Marker({
					position: memberLocation,
					icon:mapIcon,
					map:membersMap,
					shadow: shadow,
					id:i   
				});
			}
		
			with ({ mMarker: mMarkers[i] }) {
				google.maps.event.addListener(mMarker, 'click', function() {
					if (fliw) {
						fliw.close();
						fliw = null;
					}
					fliw = new google.maps.InfoWindow({
						content: getFLIWContent(mMarker.id),
					});
					fliw.open(membersMap, mMarker); 		
				});
			}
		}
		
		membersMap.fitBounds(latlngbounds);
		
		if ( flMembers.length == 1 && !flMapArgs.your_lat ) {
			blistener = google.maps.event.addListener( ( membersMap ), 'bounds_changed', function(event) {  
				this.setZoom(11);
				google.maps.event.removeListener(blistener);
			});
		}
			
		if ( flMapArgs.pin_animation == 1 ) {
			// drop marker 								
			function dropMarker(i) {
				return function() {
					mMarkers[i].setMap(membersMap);
				};
			}
		}
		
		function getFLIWContent(i) {
			
			var content = "";
			content +=	'<div class="wppl-fl-info-window">';
			content +=  	'<div class="wppl-info-window-thumb">' + flMembers[i]['avatar'] + '</div>';
			content +=		'<div class="wppl-info-window-info">';
			content +=			'<table>';
			content +=				'<tr><td><div class="wppl-info-window-permalink"><a href="' + flMembers[i]['permalink'] + '">' + flMembers[i]['display_name'] + '</a></div></td></tr>';
			content +=				'<tr><td><span>'+flMapArgs['iw_labels']['address']+'</span>' + flMembers[i]['address'] + '</td></tr>';
			if ( flMapArgs.units_array != false ) 
				content +=				'<tr><td><span>'+flMapArgs['iw_labels']['distance']+'</span>' + flMembers[i]['distance'] + ' ' + flMapArgs.units_array['name'] + '</td></tr>';
			content +=			'</table>';
			content +=		'</div>';
			content +=  '</div>';
			return content;
		} 
	}
	if ( flMapArgs != false ) flMapInit(flMapArgs, flMembers);
	window.flMapInit = flMapInit;
});


				  
		