jQuery(document).ready(function($) {
	
	if ( $.trim($('#gmw-formatted-address').val()).length == 0 || $.trim($('#gmw-lat').val()).length == 0 || $.trim($('#gmw-lng').val()).length == 0 ) {
		$('#gmw-fl-location-form input[type=text]').val('');
		$('#gmw-fl-get-address').addClass('changed');
		$('#gmw-fl-get-latlng').addClass('changed');
	}
	//remove lat/lng fields when address changes
	$('#gmw-street, #gmw-city, #gmw-state, #gmw-zipcode, #gmw-country').on("input", function() {
		$("#gmw-lat, #gmw-lng, #gmw-fl-address-autocomplete, #gmw-fl-your-location").val('');
		$('#gmw-fl-get-latlng').addClass('changed');
	});
	
	//remove address fields when lat/lng fields change
	$('#gmw-lat, #gmw-lng').on("input", function() {
		$('#gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address, #gmw-fl-address-autocomplete, #gmw-fl-your-location').val('');
		$('#gmw-fl-get-address').addClass('changed');
	});
    
	$('#gmw-fl-location-edit').click(function() {
		$('#gmw-fl-location-form').slideToggle(function() {
			
			if ( $('#gmw-fl-location-edit').hasClass('first') ) {
				
				$('#gmw-fl-location-edit').removeClass('first');
				//set initial map
				var latLng = ( $.trim($('#gmw-lat').val()).length > 0 ) ? new google.maps.LatLng( $('#gmw-lat').val(), $('#gmw-lng').val() ) : new google.maps.LatLng( '40.7827096', '-73.9653099' );
				
				gmwFlLocationMap = new google.maps.Map(document.getElementById('gmw-fl-location-map'), {
					zoom: 10,
					center:latLng,
				});	
				
				//set marker
				gmwFlMarker = new google.maps.Marker({
				    position: latLng,
				    map: gmwFlLocationMap,
					draggable: true
				});
				
				//when dragging the marker on the map
				google.maps.event.addListener( gmwFlMarker, 'dragend', function(evt){
					$('#gmw-fl-get-latlng, #gmw-fl-get-address').removeClass('changed');
					$("#gmw-lat").val( evt.latLng.lat() );
					$("#gmw-lng").val( evt.latLng.lng() );
					returnAddress( evt.latLng.lat(), evt.latLng.lng(), false );  
				});
			};
		});
	});
	
        $('.gmw-fl-location-tab').click(function() {
            $('.gmw-fl-location-tab').removeClass('active');
            $(this).addClass('active');
            $('.gmw-fl-location-tab-wrapper').hide();
            var tabId = $(this).attr('id');
            $('#'+tabId +'-wrapper').show();
        });
       
	$('#gmw-fl-location-delete').click(function() {
		delete_location();
	});
	
	//add class when click on update location button
	$('#gmw-fl-get-latlng, #gmw-fl-get-address').click(function() {
		$('#gmw-fl-update-location').addClass('update');
	});
	
    //locator button clicked 
    $('#gmw-fl-location-locate-me-btn').click(function(){
    	$('#gmw-fl-get-latlng, #gmw-fl-get-address').removeClass('changed');
    	$("#gmw-fl-locator-spinner").show();
  		getLocationBP();
  	}); 
  	
    //get current location
    function getLocationBP() {
    	
		if (navigator.geolocation) {
    		navigator.geolocation.getCurrentPosition( showPosition, showError, {timeout:10000} );
		} else {
   	 		alert("Geolocation is not supported by this browser.");
   	 		$("#gmw-fl-locator-spinner").hide();
   		}
		
	}
    
    //show results of current location
	function showPosition(position) {	
		alert('Location found');
   		
   		$("#gmw-lat").val( position.coords.latitude );
		$("#gmw-lng").val( position.coords.longitude );
		
  		returnAddress( position.coords.latitude, position.coords.longitude, true );
  		$("#gmw-fl-locator-spinner").hide();
  		
	}

	//error message for locator button
	function showError(error) {
  		
		switch(error.code) {
   	 		case error.PERMISSION_DENIED:
      			alert("User denied the request for Geolocation.");
     		break;
   		 	case error.POSITION_UNAVAILABLE:
   		   		alert("Location information is unavailable.");
    	  	break;
    		case error.TIMEOUT:
      			alert("The request to get user location timed out.");
     		break;
    		case error.UNKNOWN_ERROR:
      			alert("An unknown error occurred.");
      		break;
		}
		$("#gmw-fl-locator-spinner").hide();
	}
	
	

	//update map
	function update_map() {
		
		var latLng = new google.maps.LatLng( $('#gmw-lat').val(), $('#gmw-lng').val() );
		
		gmwFlMarker.setMap(null);
		
		gmwFlMarker = new google.maps.Marker({
		    position: latLng,
		    map: gmwFlLocationMap,
			draggable: true
		});
		gmwFlLocationMap.setCenter(latLng);
		
		//when dragging the marker on the map
		google.maps.event.addListener( gmwFlMarker, 'dragend', function(evt){
			$('#gmw-fl-get-latlng, #gmw-fl-get-address').removeClass('changed');
			$("#gmw-lat").val( evt.latLng.lat() );
			$("#gmw-lng").val( evt.latLng.lng() );
			returnAddress( evt.latLng.lat(), evt.latLng.lng(), false );  
		});
	}
	
	//autocomplete
	function gmwAutocompleteInit() {
		
		jQuery('#gmw-fl-address-autocomplete').autocomplete({
	
			source: function(request,response) {
	
				geocoder = new google.maps.Geocoder();
				// the geocode method takes an address or LatLng to search for
				// and a callback function which should process the results into
				// a format accepted by jqueryUI autocomplete
				geocoder.geocode( {'address': request.term }, function(results, status) {
					response(jQuery.map(results, function(item) {
						return {
							label: item.formatted_address, // appears in dropdown box
							value: item.formatted_address, // inserted into input element when selected
							geocode: item                  // all geocode data: used in select callback event
						};
					}));
				});
			},
	
			// event triggered when drop-down option selected
			select: function(event,ui){
				
				if (jQuery('#gmw-fl-address-autocomplete').length == 1 ) {
					//update_ui(  ui.item.value, ui.item.geocode.geometry.location );
					//update_map( ui.item.geocode.geometry );
					$('#gmw-fl-get-latlng, #gmw-fl-get-address').removeClass('changed');
					$('#gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address').val('');
					
					$("#gmw-lat").val(ui.item.geocode.geometry.location.lat());
	    			$("#gmw-lng").val(ui.item.geocode.geometry.location.lng());

					breakAddress(ui.item.geocode);
					update_map();
	
				}
				
			}
		});
		
		// triggered when user presses a key in the address box
		jQuery('#gmw-fl-address-autocomplete').bind('keydown', function(event) {
			if(event.keyCode == 13) {
				// ensures dropdown disappears when enter is pressed
				jQuery('#gmw-fl-address-autocomplete').autocomplete("disable");
			} else {
				// re-enable if previously disabled above
				jQuery('#gmw-fl-address-autocomplete').autocomplete("enable");
			}
		});
		
	}
	gmwAutocompleteInit();
	
	 // convert lat/long to an address button 
	$('#gmw-fl-get-address').click( function() {
		
		if( !$('#gmw-fl-get-address').hasClass('changed') ) return saveLocation();
		
 		var gotLat  = $("#gmw-lat").val();
   	 	var gotLng  = $("#gmw-lng").val();
   	 	
    	returnAddress( gotLat, gotLng, true );  
	});
	
	/* main function to conver lat/long to address */
	function returnAddress( gotLat, gotLng, updateMap ) {
		
		//remove all address fields
		$('#gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address').val('');
		
		geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(gotLat ,gotLng);
	
		//geocode lat/lng to address
		geocoder.geocode( {'latLng': latlng }, function(results, status) {
      		
			if (status == google.maps.GeocoderStatus.OK) {
				
       	 		if ( results[0] ) {
         
					breakAddress(results[0]);
					if ( updateMap == true ) update_map();
        		}
       	 		
      		} else {
      			
        		alert("Geocoder failed due to: " + status);
        		
      		}
   		});
	} 
	
	//address components
	function breakAddress(location) {
		
		$("#address-tab-wrapper :text").val('');
		
		$("#gmw-fl-address-autocomplete, #gmw-formatted-address, #gmw-address, #gmw-fl-your-location").val(location.formatted_address);
			
		address = location.address_components;
		
		var street_number = false;
		
		for ( x in address ) {

			if ( address[x].types == 'street_number' ) {
				street_number = address[x].long_name;
			}
			
			if ( address[x].types == 'route' ) {
				street = address[x].long_name;  
				if ( street_number != false ) {
					street = street_number + ' ' + street;
					$("#gmw-street").val(street);
				} else {
					$("#gmw-street").val(street);
				}
			}
	
			if ( address[x].types == 'administrative_area_level_1,political' ) {
	          	state = address[x].short_name;
	          	state_long = address[x].long_name;
	          	$("#gmw-state").val(state);
	          	$("#gmw-state-long").val(state_long);
	         } 
	         
	         if(address[x].types == 'locality,political') {
	          	city = address[x].long_name;
	          	$("#gmw-city").val(city);
	         } 
	         
	         if (address[x].types == 'postal_code') {
	          	zipcode = address[x].long_name;
	          	$("#gmw-zipcode").val(zipcode);
	        } 
	        
	        if (address[x].types == 'country,political') {
	          	country = address[x].short_name;
	          	country_long = address[x].long_name;
	          	$("#gmw-country").val(country);
	          	$("#gmw-country-long").val(country_long);
	         } 
        }
		
		if ( $('#gmw-fl-update-location').hasClass('update') ) saveLocation();
	}

	//convert address to lat/lng
	$('#gmw-fl-get-latlng').click(function() {
		
		if( !$('#gmw-fl-get-latlng').hasClass('changed') ) return saveLocation();
						
		$("#gmw-lat").val('');
		$("#gmw-lng").val('');
		getLatLong();
	});
	
	/* convert address to lat/long */
	function getLatLong() {
				
  	  	var street  = $("#gmw-street").val();
  	  	var apt     = $("#gmw-apt").val();
 	  	var city    = $("#gmw-city").val();
 	  	var state   = $("#gmw-state").val();
 	  	var zipcode = $("#gmw-zipcode").val();
  	  	var country = $("#gmw-country").val();
    
  	  	var fullAddress = street + " " + apt + " " + city + " " + state + " " + zipcode + " " + country;
  	  	var geoAddress  = street + " " + city + " " + state + " " + zipcode + " " + country;
  	  	
                geocoder = new google.maps.Geocoder();
   	 	geocoder.geocode( { 'address': geoAddress}, function(results, status) {
      		
   	 		if (status == google.maps.GeocoderStatus.OK) {
   	 			
        		gotLat = results[0].geometry.location.lat();
        		gotLng = results[0].geometry.location.lng();
        	        		
        		var address = results[0].address_components;
        		
        		for ( x in address ) {
					
        			if ( address[x].types == 'administrative_area_level_1,political' ) {
          				if(address[x].short_name != undefined) {
          					$("#gmw-state").val(address[x].short_name);
          				}
          				if( address[x].long_name != undefined) {
          					$("#gmw-state-long").val(address[x].long_name);
          				}
          			}
          				   		
          			if (address[x].types == 'country,political' ) {
          				if(address[x].short_name != undefined) {
          					$("#gmw-country").val(address[x].short_name);
          				}
          				if( address[x].long_name != undefined) {
          					$("#gmw-country-long").val(address[x].long_name);
          				}	
          			}
          				
          		}
          		
       			$("#gmw-lat").val(gotLat);
       			$("#gmw-lng").val(gotLng);
       			$("#gmw-address, #gmw-fl-your-location").val(fullAddress);
       			$("#gmw-formatted-address, #gmw-fl-address-autocomplete").val(results[0].formatted_address);
       			
       			update_map();
       			
       			if ( $('#gmw-fl-update-location').hasClass('update') ) saveLocation();
       			
    		} else {
    			
        		alert("Geocode was not successful for the following reason: " + status);     
       			
    		}
    	});
	}

    /* save location */   	
    function saveLocation() {
  	 	
        $('.gmw-fl-location-tab').removeClass('active');
        $('#gmw-fl-address-fields-tab').addClass('active');
        $('.gmw-fl-location-tab-wrapper').hide();
        $('#gmw-fl-address-fields-tab-wrapper').show();
        
    	$(".gmw-fl-updater-spinner").show();
    	$('#gmw-fl-get-latlng, #gmw-fl-get-address').removeClass('changed');
    	$('#gmw-fl-update-location').removeClass('update');
    	
		$.ajax({
			type       	: "post",
			data  		: {action:'gmw_fl_update_location', 'formValues': $('#gmw-fl-location-form').serialize() },		
			url        	: ajaxurl,
			success:function(data){
				
				setTimeout(function() {
					$("#gmw-fl-location-message").html(data);
					$(".gmw-fl-updater-spinner").hide();
   				},500);
				
   				setTimeout(function() {
					$("#gmw-fl-location-message").animate({opacity:0});
   				},2500);
			}
		});
		return false;
 	};
 	
    function delete_location() {
    	
    	$('#gmw-fl-location-form input[type=text]').val('');
    	
    	$("#gmw-fl-delete-spinner").show();
     	
		$.ajax({
			type       	: "post",
			data  		: {action:'gmw_fl_delete_location' },		
			url        	: ajaxurl,
			success:function(data){
				
				setTimeout(function() {
					$("#gmw-fl-location-delete-message").html(data);
					$("#gmw-fl-delete-spinner").hide();
   				},500);
				
   				setTimeout(function() {
					$("#gmw-fl-location-delete-message").animate({opacity:0});
   				},2500);
			}
		});
    };
 	  	
});