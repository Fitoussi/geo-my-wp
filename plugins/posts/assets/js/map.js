/**
 * GMW javascript - post types main map
 * @version 1.0
 * @author Eyal Fitoussi
 */
jQuery(document).ready(function($){ 
	
	function ptMapInit( gmwForm ) {

		var zoomLevel = ( gmwForm.results_map['zoom_level'] == 'auto' ) ? 13 : gmwForm.results_map['zoom_level'];
		
		var ptMap = new google.maps.Map(document.getElementById( 'gmw-map-' + gmwForm.ID ), {
			zoom: parseInt(zoomLevel),
			panControl: true,
	  		zoomControl: true,
	  		mapTypeControl: true,
			center: new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng ),
			mapTypeId: google.maps.MapTypeId[gmwForm.results_map['map_type']],
		});			
		
		var latlngbounds = new google.maps.LatLngBounds( );
		
		if ( gmwForm.your_lat != false && gmwForm.your_lng != false ) {
			
			var yourLocation  = new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng );
			latlngbounds.extend(yourLocation);
			
			var yliw = new google.maps.InfoWindow({
        		content: gmwForm.iw_labels.your_location
        	});
              
			ylMarker = new google.maps.Marker({
				position: new google.maps.LatLng( gmwForm.your_lat,gmwForm.your_lng ),
				map: ptMap,
				icon:'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
			});
			
			if ( gmwForm.results_map.yl_icon != undefined && gmwForm.results_map.yl_icon == 1 ) {
    			yliw.open( ptMap, ylMarker);
    		}
    		
            google.maps.event.addListener( ylMarker, 'click', function() {
            	yliw.open( ptMap, ylMarker);
            });
            
		}
		
		var i, pc;
		ptMarkers = [];
		
		if ( gmwForm.paged == 0 ) pc = 1; else pc = ( gmwForm.get_per_page * (gmwForm.paged - 1 ) +1 );
		
		for ( i = 0; i < gmwForm.results.length; i++ ) {  
			
			var ptLocation = new google.maps.LatLng( gmwForm.results[i]['lat'], gmwForm.results[i]['long'] );
			latlngbounds.extend(ptLocation);
			
			mapIcon = gmwForm.results[i]['mapIcon'];
							
			ptMarkers[i] = new google.maps.Marker({
				position: ptLocation,
				icon:mapIcon,
				map:ptMap,
				shadow: 'https://chart.googleapis.com/chart?chst=d_map_pin_shadow',
				id:i 
			});
						
			with ({ ptMarker: ptMarkers[i] }) {
				var ptiw = false;
				google.maps.event.addListener( ptMarker, 'click', function() {
					if (ptiw) {
						ptiw.close();
						ptiw = null;
					}
					ptiw = new google.maps.InfoWindow({
						content: getPTIWContent(ptMarker.id),
					});
					ptiw.open( ptMap, ptMarker );	 		
				});
			}
			pc++;
		}
			
		if ( gmwForm.results_map['zoom_level'] == 'auto' || ( gmwForm.your_lat == false && gmwForm.your_lng == false ) ) ptMap.fitBounds(latlngbounds);
		
		if ( gmwForm.results.length == 1 && gmwForm.your_lat == false && gmwForm.your_lng == false ) {
			
			blistener = google.maps.event.addListener( ( ptMap ), 'bounds_changed', function(event) {  
				this.setZoom(11);
				google.maps.event.removeListener(blistener);
			});
			
		}
			
		function getPTIWContent(i) {

			var content = "";
			content +=	'<div class="wppl-pt-info-window">';
			content +=  	'<div class="wppl-info-window-thumb">' + gmwForm.results[i]['post_thumbnail'] + '</div>';
			content +=		'<div class="wppl-info-window-info">';
			content +=			'<table>';
			content +=				'<tr><td><div class="wppl-info-window-permalink"><a href="' + gmwForm.results[i]['post_permalink'] + '">' + gmwForm.results[i]['post_title'] + '</a></div></td></tr>';
			content +=				'<tr><td><span>'+ gmwForm['iw_labels']['address'] + '</span>' + gmwForm.results[i]['formatted_address'] + '</td></tr>';
			if ( gmwForm.org_address != false ) 
				content +=				'<tr><td><span>'+ gmwForm['iw_labels']['distance'] + '</span>' + gmwForm.results[i]['distance'] + ' ' + gmwForm.units_array['name'] + '</td></tr>';
			
			if ( gmwForm.search_results.additional_info['phone'] != undefined ) {
				content +=				'<tr><td><span>'+ gmwForm['iw_labels']['phone'] + '</span>' + gmwForm.results[i]['phone'] + '</td></tr>';
			}
			if ( gmwForm.search_results.additional_info['fax'] != undefined ) {
				content +=				'<tr><td><span>'+ gmwForm['iw_labels']['fax'] + '</span>' + gmwForm.results[i]['fax'] + '</td></tr>';
			}
			if ( gmwForm.search_results.additional_info['email'] != undefined ) {
			content +=				'<tr><td><span>'+ gmwForm['iw_labels']['email'] + '</span><a href="mailto:' + gmwForm.results[i]['email'] + '">'+gmwForm.results[i]['email']+'</a></td></tr>';
			}
			if ( gmwForm.search_results.additional_info['website'] != undefined ) {
				content +=				'<tr><td><span>'+ gmwForm['iw_labels']['website'] + '</span><a href="http://' + gmwForm.results[i]['website'] + '" target="_blank">' + gmwForm.results[i]['website'] + '</a>' + '</td></tr>';
			}
			content +=			'</table>';
			content +=		'</div>';
			content +=  '</div>';
			return content;
		}
	}
	
	$( '#gmw-map-wrapper-'+gmwForm.ID ).slideToggle(function() {
		ptMapInit( gmwForm );
	});
});