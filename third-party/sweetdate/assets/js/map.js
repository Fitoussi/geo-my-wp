jQuery(document).ready(function() {
	console.log(mdMapArgs);
	var mdMembers = mdMapArgs.locations;
	
	var mdMap = new google.maps.Map( document.getElementById('gmw-md-main-map'), {
		zoom: 13,
		center: new google.maps.LatLng( mdMapArgs.your_lat,mdMapArgs.your_lng ),
		mapTypeId: google.maps.MapTypeId[mdMapArgs.map_type],
	});
	
	var latlngbounds = new google.maps.LatLngBounds();
	
	if ( mdMapArgs.your_lat && mdMapArgs.your_lng ) {
		var yourLocation  = new google.maps.LatLng( mdMapArgs.your_lat, mdMapArgs.your_lng );
		latlngbounds.extend(yourLocation);
		
		var yLMemberIcon;
		yLMemberIcon = 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png';
		
		marker = new google.maps.Marker({
			position: new google.maps.LatLng( mdMapArgs.your_lat,mdMapArgs.your_lng ),
			map: mdMap,
			icon: yLMemberIcon,
		});
	}
	
	var i;
	mMarkers = [];
	
	for ( i = 0; i < mdMembers.length; i++ ) { 
		
		var mapIcon, shadow;
		var memberLocation = new google.maps.LatLng(mdMembers[i]['lat'], mdMembers[i]['long']);
		latlngbounds.extend(memberLocation);
		
		if ( mdMapArgs.map_icon_usage == 'per_member' ) {
			
			if ( mdMembers[i]['map_icon'] == "_default.png" ) {
				mapIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+ ( (mdMapArgs.page - 1) * mdMapArgs.per_page + i + 1  ) +'|FF776B|000000';
				shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
			} else {
				
				if ( mdMembers[i]['map_icon'] == '' || mdMembers[i]['map_icon'] == undefined ) {
					mapIcon = mdMapArgs.main_icons_folder + '/friends/' + mdMapArgs.friends_map_icon;
					shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
				} else {
					mapIcon = mdMapArgs.main_icons_folder + '/friends/' + mdMembers[i]['map_icon'];
					shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';		
				}
			}
		} else if ( mdMapArgs.map_icon_usage == 'avatars' ) {
			
			mapIcon = new google.maps.MarkerImage(
					mdMembers[i]['avatar_icon'],
				new google.maps.Size(30, 30),
				new google.maps.Point(0,0),
				new google.maps.Point(9.5, 29),
				new google.maps.Size(28,27)
			);
			shadow = new google.maps.MarkerImage(
				mdMapArgs.main_icons_folder  + '/friends/' +  '_avatar.png',
				new google.maps.Size(40, 44),
				new google.maps.Point(0,0),
				new google.maps.Point(15, 35)
			);
		
		} else {
			if ( mdMapArgs.friends_map_icon == "_default.png" || mdMapArgs.friends_map_icon == undefined ) {
				mapIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+ ( ( mdMapArgs.page - 1 ) * mdMapArgs.per_page + i + 1  ) +'|FF776B|000000';
				shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
			} else {
				mapIcon = flMapArgs.main_icons_folder + '/friends/' + flMapArgs.friends_map_icon;
				shadow = 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow';
			}
		}
			
		mMarkers[i] = new google.maps.Marker({
			position: memberLocation,
			icon:mapIcon,
			//map: mdMap,
			shadow: shadow,
			id:mdMembers[i]['ID']  
		});
		
		with ({ mMarker: mMarkers[i] }) {
			google.maps.event.addListener( mMarker, 'click', function() {
				jQuery('li').removeClass('gmw-md-marker-clicked');
				jQuery('#gmw-md-member-'+ mMarker.id).closest('li').addClass('gmw-md-marker-clicked');
				jQuery('html, body').animate({
					scrollTop: jQuery("#gmw-md-member-"+ mMarker.id).closest('.gmw-md-marker-clicked').offset().top
			    }, 2000);
			});
		};
			
	}
	
	var markerCluster = new MarkerClusterer( mdMap, mMarkers );
	mdMap.fitBounds(latlngbounds);
	
	// if only one marker then zoom out
	if ( mdMembers.length == 1 && !mdMapArgs.your_lat  ) {
		blistener = google.maps.event.addListener( ( mdMap ), 'bounds_changed', function(event) {  
			this.setZoom(11);
			google.maps.event.removeListener(blistener);
		});
	}
	
	// drop marker 	
	/*
	function dropMarker(i) {
		return function() {
			mMarkers[i].setMap(mdMap);
		};
	}
	*/
	google.maps.event.addListenerOnce(mdMap, 'idle', function(){
		jQuery('.gmw-md-map-loader').fadeOut(1500);
	});	
});
