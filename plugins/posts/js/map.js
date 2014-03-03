/**
 * GMW javascript - post types main map
 * @version 1.0
 * @author Eyal Fitoussi
 */
jQuery(document).ready(function($){ 

	function ptMapInit(ptMapArgs, ptLocations) {
		
		var ptMap = new google.maps.Map(document.getElementById('gmw-pt-map-' + ptMapArgs.form_id), {
			zoom: 13,
			panControl: true,
	  		zoomControl: true,
	  		mapTypeControl: true,
			center: new google.maps.LatLng(ptMapArgs.your_lat,ptMapArgs.your_long),
			mapTypeId: google.maps.MapTypeId[ptMapArgs.map_type],
		});			
		
		var latlngbounds = new google.maps.LatLngBounds( );
		
		if ( ptMapArgs.your_lat != false && ptMapArgs.your_long != false ) {
			var yourLocation  = new google.maps.LatLng(ptMapArgs.your_lat,ptMapArgs.your_long);
			latlngbounds.extend(yourLocation);
			
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(ptMapArgs.your_lat,ptMapArgs.your_long),
				map: ptMap,
				icon:'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
			});
		}
		
		var i, pc;
		ptMarkers = [];
		
		if ( ptMapArgs.paged == 0 ) pc = 1; else pc = ( ptMapArgs.get_per_page * (ptMapArgs.paged - 1 ) +1 );
		
		for (i = 0; i < ptLocations.length; i++) {  
			
			var ptLocation = new google.maps.LatLng(ptLocations[i]['lat'], ptLocations[i]['long']);
			latlngbounds.extend(ptLocation);
			
			mapIcon = 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+ pc +'|FF776B|000000';
				
			if ( ptMapArgs.pin_animation == 1 ) {
				ptMarkers[i] = new google.maps.Marker({
					position: ptLocation,
					icon:mapIcon,
					animation: google.maps.Animation.DROP,
					shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow',
					id:i 
				});
				setTimeout(dropMarker(i), i * 150);	
			} else {
				ptMarkers[i] = new google.maps.Marker({
					position: ptLocation,
					icon:mapIcon,
					map:ptMap,
					shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow',
					id:i 
				});
			}
		
			with ({ ptMarker: ptMarkers[i] }) {
				var ptiw = false;
				google.maps.event.addListener(ptMarker, 'click', function() {
					if (ptiw) {
						ptiw.close();
						ptiw = null;
					}
					ptiw = new google.maps.InfoWindow({
						content: getPTIWContent(ptMarker.id),
					});
					ptiw.open(ptMap, ptMarker);	 		
				});
			}
			pc++;
		}
			
		if (ptMapArgs.auto_zoom == 1) ptMap.fitBounds(latlngbounds);
		
		if ( ptLocations.length == 1 && ptMapArgs.your_lat == false && ptMapArgs.your_long == false ) {
			blistener = google.maps.event.addListener( ( ptMap ), 'bounds_changed', function(event) {  
				this.setZoom(11);
				google.maps.event.removeListener(blistener);
			});
		}
		
		if ( ptMapArgs.pin_animation == 1 ) {
			// drop marker 								
			function dropMarker(i) {
				return function() {
					ptMarkers[i].setMap(ptMap);
				};
			} 
		}
		
		function getPTIWContent(i) {
			var content = "";
			content +=	'<div class="wppl-pt-info-window">';
			content +=  	'<div class="wppl-info-window-thumb">' + ptLocations[i]['post_thumbnail'] + '</div>';
			content +=		'<div class="wppl-info-window-info">';
			content +=			'<table>';
			content +=				'<tr><td><div class="wppl-info-window-permalink"><a href="' + ptLocations[i]['post_permalink'] + '">' + ptLocations[i]['post_title'] + '</a></div></td></tr>';
			content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['address'] + '</span>' + ptLocations[i]['formatted_address'] + '</td></tr>';
			if ( ptMapArgs.units_array != false ) {
				content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['distance'] + '</span>' + ptLocations[i]['distance'] + ' ' + ptMapArgs.units_array['name'] + '</td></tr>';
			}
			content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['phone'] + '</span>' + ptLocations[i]['phone'] + '</td></tr>';
			content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['fax'] + '</span>' + ptLocations[i]['fax'] + '</td></tr>';
			content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['email'] + '</span><a href="mailto:' + ptLocations[i]['email'] + '">'+ptLocations[i]['email']+'</a></td></tr>';
			content +=				'<tr><td><span>'+ ptMapArgs['iw_labels']['website'] + '</span><a href="http://' + ptLocations[i]['website'] + '" target="_blank">' + ptLocations[i]['website'] + '</a>' + '</td></tr>';
			content +=			'</table>';
			content +=		'</div>';
			content +=  '</div>';
			return content;
		}
	}
	if ( ptMapArgs != false ) ptMapInit(ptMapArgs, ptLocations);
	window.ptMapInit = ptMapInit;
});
	
