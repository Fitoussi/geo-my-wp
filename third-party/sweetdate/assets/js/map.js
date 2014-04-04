jQuery(document).ready(function() {
	
	var members = sdMapArgs.members;
	
	var sdMap = new google.maps.Map( document.getElementById('gmw-sd-main-map'), {
		zoom: 13,
		center: new google.maps.LatLng( sdMapArgs.your_lat,sdMapArgs.your_lng ),
		mapTypeId: google.maps.MapTypeId[sdMapArgs.map_type],
	});
	
	var latlngbounds = new google.maps.LatLngBounds();
	
	if ( sdMapArgs.your_lat && sdMapArgs.your_lng ) {
		var yourLocation  = new google.maps.LatLng( sdMapArgs.your_lat, sdMapArgs.your_lng );
		latlngbounds.extend(yourLocation);
		
		var yLMemberIcon;
		yLMemberIcon = 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png';
		
		marker = new google.maps.Marker({
			position: new google.maps.LatLng( sdMapArgs.your_lat,sdMapArgs.your_lng ),
			map: sdMap,
			icon: yLMemberIcon,
		});
	}
	
	var i, iw;
	sdMarkers = [];
	
	for ( i = 0; i < members.length; i++ ) { 
		
		var mapIcon, shadow;
		var memberLocation = new google.maps.LatLng(members[i]['lat'], members[i]['long']);
		latlngbounds.extend(memberLocation);
		
		mapIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+ ( ( sdMapArgs.page - 1 ) * sdMapArgs.per_page + i + 1  ) +'|FF776B|000000';
		shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
			
		sdMarkers[i] = new google.maps.Marker({
			position: memberLocation,
			icon:mapIcon,
			//map: sdMap,
			shadow: shadow,
			id:i 
		});
		
		with ({ marker: sdMarkers[i] }) {
			
			//show info window on mouseover
			google.maps.event.addListener( marker, 'mouseover', function() {

				iw = new google.maps.InfoWindow({
					content: infoWindowContent(marker.id),
				});
				iw.open( sdMap, marker );
				
			});
			
			//hide info window on mouseout
			google.maps.event.addListener(marker, 'mouseout', function() {
				iw.close();
			});
			
			//link to user profile on marker click
			google.maps.event.addListener(marker, 'click', function() {
				window.location = sdMapArgs.members[marker.id]['user_link'];
			});
		};

		function infoWindowContent(i) {
	
			var content = "";
			content +=	'<div class="gmw-sd-info-window">';
			content +=  	'<span class="avatar"><img src="' + sdMapArgs.members[i]['avatar'] + '" /></span> ';
			content +=  	'<span class="user-name">' + sdMapArgs.members[i]['user_name'] + '</span> ';
			if ( members[i]['distance'] != false ) {
				content +=  	'<span class="distance">(' + sdMapArgs.members[i]['distance'] + ')</span> ';
			}
			
			content +=  '</div>';
			
			return content;
		}
			
	}
	
	var markerCluster = new MarkerClusterer( sdMap, sdMarkers );
	sdMap.fitBounds(latlngbounds);
	
	// if only one marker then zoom out
	if ( members.length == 1 && !sdMapArgs.your_lat  ) {
		blistener = google.maps.event.addListener( ( sdMap ), 'bounds_changed', function(event) {  
			this.setZoom(11);
			google.maps.event.removeListener(blistener);
		});
	}
	
	google.maps.event.addListenerOnce(sdMap, 'idle', function(){
		jQuery('.gmw-sd-map-loader').fadeOut(1500);
	});	
});
