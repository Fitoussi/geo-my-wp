jQuery(document).ready(function($) {
    
	$('.gmw-single-post-sc-directions-trigger').click(function(event){
   	 	event.preventDefault();
    	$(this).closest('.gmw-single-post-sc-wrapper').find('.gmw-single-post-sc-form').slideToggle(); 
    }); 

	var i;
	for (i = 1; i <= (singleLM.mapId); i++) { 
			var mapSingle = new google.maps.Map(document.getElementById('single-location-map-'+ i), {
			zoom: parseInt(singleLM.singleLocation[i]['zoom_level']),
    		center: new google.maps.LatLng(singleLM.singleLocation[i]['lat'],singleLM.singleLocation[i]['long']),
    		mapTypeId: google.maps.MapTypeId[singleLM.singleLocation[i]['map_type']],
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
    		}
		});	
		var marker;
		var infowindow = new google.maps.InfoWindow(); 
		
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(singleLM.singleLocation[i]['lat'], singleLM.singleLocation[i]['long']),
			map: mapSingle,
			shadow:'https://chart.googleapis.com/chart?chst=d_map_pin_shadow'       
		});
		
		google.maps.event.addListener(marker, 'click', (function(marker, i) {
		return function() {
			infowindow.setContent(
        		'<div class="wppl-info-window" style="font-size: 13px;color: #555;line-height: 18px;font-family: arial;">' +
        		'<div class="map-info-title" style="color: #457085;text-transform: capitalize;font-size: 16px;margin-bottom: -10px;">' + singleLM.singleLocation[i]['post_title'] + '</div>' +
        		'<br /> <span style="font-weight: bold;color: #333;">Address: </span>' + singleLM.singleLocation[i]['address']  + 
        		'<br /> <span style="font-weight: bold;color: #333;">Phone: </span>' + singleLM.singleLocation[i]['phone'] + 
        		'<br /> <span style="font-weight: bold;color: #333;">Fax: </span>' + singleLM.singleLocation[i]['fax'] + 
        		'<br /> <span style="font-weight: bold;color: #333;">Email: </span>' + singleLM.singleLocation[i]['email'] + 
        		'<br /> <span style="font-weight: bold;color: #333;">Website: </span><a href="http://' + singleLM.singleLocation[i]['website'] + '" target="_blank">' + singleLM.singleLocation[i]['website'] + '</a>');
    			infowindow.open(document.getElementById('single-location-map-'+ i), marker);    
			};
		})(marker, i));
	}
});

