jQuery(document).ready(function($) {
	
	if ( $.trim($('#gmw-formatted-address').val()).length == 0 || $.trim($('#gmw-lat').val()).length == 0 || $.trim($('#gmw-lng').val()).length == 0 ) {
		$('#gmw-yl-form input[type=text]').val('');
		$('#gmw-yl-get-address').addClass('changed');
		$('#gmw-yl-get-latlng').addClass('changed');
	}
	//remove lat/lng fields when address changes
	$('#gmw-street, #gmw-city, #gmw-state, #gmw-zipcode, #gmw-country').on("input", function() {
		$("#gmw-lat, #gmw-lng, #gmw-yl-autocomplete, #gmw-yl-field").val('');
		$('#gmw-yl-get-latlng').addClass('changed');
	});
	
	//remove address fields when lat/lng fields change
	$('#gmw-lat, #gmw-lng').on("input", function() {
		$('#gmw-street-number, #gmw-street-name, #gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address, #gmw-yl-autocomplete, #gmw-yl-field').val('');
		$('#gmw-yl-get-address').addClass('changed');
	});
    
	$('#gmw-yl-edit').click(function() {
		$('#gmw-yl-form').slideToggle(function() {
			
			if ( $('#gmw-yl-edit').hasClass('first') ) {
				
				$('#gmw-yl-edit').removeClass('first');
				//set initial map
				var latLng = ( $.trim($('#gmw-lat').val()).length > 0 ) ? new google.maps.LatLng( $('#gmw-lat').val(), $('#gmw-lng').val() ) : new google.maps.LatLng( '40.7827096', '-73.9653099' );
				
				gmwFlLocationMap = new google.maps.Map(document.getElementById('gmw-yl-map'), {
					zoom: 10,
					center:latLng
				});	
				
				//set marker
				gmwFlMarker = new google.maps.Marker({
				    position: latLng,
				    map: gmwFlLocationMap,
					draggable: true
				});
				
				//when dragging the marker on the map
				google.maps.event.addListener( gmwFlMarker, 'dragend', function(evt){
					$('#gmw-yl-get-latlng, #gmw-yl-get-address').removeClass('changed');
					$("#gmw-lat").val( evt.latLng.lat() );
					$("#gmw-lng").val( evt.latLng.lng() );
					returnAddress( evt.latLng.lat(), evt.latLng.lng(), false );  
				});
			};
		});
	});
	
    $('.gmw-yl-tab').click(function() {
        $('.gmw-yl-tab').removeClass('active');
        $(this).addClass('active');
        $('.gmw-yl-tab-wrapper').hide();
        $('.update-btn-wrapper').hide();
        var tabId = $(this).attr('id');
        $('#'+tabId +'-wrapper').show();
        $('#'+tabId +'-btn-wrapper').show();
    });
       
	$('#gmw-yl-delete').click(function() {
		delete_location();
	});
	
	//add class when click on update location button
	$('#gmw-yl-get-latlng, #gmw-yl-get-address').click(function() {
		$('#gmw-yl-update-location').addClass('update');
	});
	
    //locator button clicked 
    $('#gmw-yl-locator-btn').click(function(){
    	$('#gmw-yl-get-latlng, #gmw-yl-get-address').removeClass('changed');
    	$("#gmw-yl-spinner").show();
  		getLocationBP();
  	}); 
  	
    //get current location
    function getLocationBP() {
    	
		if (navigator.geolocation) {
    		navigator.geolocation.getCurrentPosition( showPosition, showError, {timeout:10000} );
		} else {
   	 		alert("Geolocation is not supported by this browser.");
   	 		$("#gmw-yl-spinner").hide();
   		}
		
	}
    
    //show results of current location
	function showPosition(position) {	
		alert('Location found');
   		
   		$("#gmw-lat").val( position.coords.latitude );
		$("#gmw-lng").val( position.coords.longitude );
		
  		returnAddress( position.coords.latitude, position.coords.longitude, true );
  		$("#gmw-yl-spinner").hide();
  		
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
		$("#gmw-yl-spinner").hide();
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
			$('#gmw-yl-get-latlng, #gmw-yl-get-address').removeClass('changed');
			$("#gmw-lat").val( evt.latLng.lat() );
			$("#gmw-lng").val( evt.latLng.lng() );
			returnAddress( evt.latLng.lat(), evt.latLng.lng(), false );  
		});
	}
	
	//autocomplete
	function gmwAutocompleteInit() {
				
		var input 	= document.getElementById('gmw-yl-autocomplete');

		var options = {
	        types: ['geocode']
	    };
	    
	    var autocomplete = new google.maps.places.Autocomplete(input, options);
	    
	    google.maps.event.addListener(autocomplete, 'place_changed', function(e) {
	    	
	    	var place = autocomplete.getPlace();

			if (!place.geometry) {
				return;
			}
			
			if ( jQuery('#gmw-yl-autocomplete').length == 1 ) {
				//update_ui(  ui.item.value, ui.item.geocode.geometry.location );
				//update_map( ui.item.geocode.geometry );
				$('#gmw-yl-get-latlng, #gmw-yl-get-address').removeClass('changed');
				$('#gmw-street-number, #gmw-street-name, #gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address').val('');
				
				$("#gmw-lat").val(place.geometry.location.lat());
    			$("#gmw-lng").val(place.geometry.location.lng());

				breakAddress(place);
				update_map();

			}
					
	    });
	   
	}
	gmwAutocompleteInit();
	
	 // convert lat/long to an address button 
	$('#gmw-yl-get-address').click( function() {
		
		if( !$('#gmw-yl-get-address').hasClass('changed') ) return saveLocation();
		
 		var gotLat  = $("#gmw-lat").val();
   	 	var gotLng  = $("#gmw-lng").val();
   	 	
    	returnAddress( gotLat, gotLng, true );  
	});
	
	/* main function to conver lat/long to address */
	function returnAddress( gotLat, gotLng, updateMap ) {
		
		//remove all address fields
		$('#gmw-street-number, #gmw-street-name, #gmw-street, #gmw-city, #gmw-state, #gmw-state-long, #gmw-zipcode, #gmw-country, #gmw-country-long, #gmw-apt, #gmw-address, #gmw-formatted-address').val('');
		
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
		
		$("#gmw-yl-autocomplete, #gmw-formatted-address, #gmw-address, #gmw-yl-field").val(location.formatted_address);
			
		address = location.address_components;
		
		var street_number = false;
		
		for ( x in address ) {

			if ( address[x].types == 'street_number' ) {
				street_number = address[x].long_name;
				$("#gmw-street-number").val(street_number);
			}
			
			if ( address[x].types == 'route' ) {
				street = address[x].long_name; 

				$("#gmw-street-name").val(street);

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
		
		if ( $('#gmw-yl-update-location').hasClass('update') ) saveLocation();
	}

	//convert address to lat/lng
	$('#gmw-yl-get-latlng').click(function() {
		
		if( !$('#gmw-yl-get-latlng').hasClass('changed') ) return saveLocation();
						
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
       			$("#gmw-address, #gmw-yl-field").val(fullAddress);
       			$("#gmw-formatted-address, #gmw-yl-autocomplete").val(results[0].formatted_address);
       			
       			update_map();
       			
       			if ( $('#gmw-yl-update-location').hasClass('update') ) saveLocation();
       			
    		} else {
    			
        		alert("Geocode was not successful for the following reason: " + status);     
       			
    		}
    	});
	}

    /* save location */   	
    function saveLocation() {
  	 	
        $('.gmw-yl-tab').removeClass('active');
        $('#gmw-yl-address-tab').addClass('active');
        $('.gmw-yl-tab-wrapper').hide();
        $('#gmw-yl-address-tab-wrapper').show();
        
    	$("#gmw-yl-spinner").show();
    	$('#gmw-yl-get-latlng, #gmw-yl-get-address').removeClass('changed');
    	
    	if ( $('#gmw-yl-update-location').hasClass('update') ) {
    		$('#gmw-yl-form').slideToggle();
    	}
    	$('#gmw-yl-update-location').removeClass('update');
    	    	
		$.ajax({
			type       	: "post",
			data  		: {action:'gmw_fl_update_location', 'formValues': $('#gmw-yl-form').serialize() },		
			url        	: ajaxurl,
			success:function(data){
				
				setTimeout(function() {				
					$("#gmw-yl-spinner").hide();
					
					$("#gmw-yl-message p").html(data);
					$("#gmw-yl-message").fadeToggle(function(){
						setTimeout(function() {
							$("#gmw-yl-message").fadeToggle();
						},2500);
					});
									
   				},500);
			}
		});
		return false;
 	};
 	
    function delete_location() {
    	
    	$('#gmw-your-location-wrapper input[type=text]').val('');
    	
    	$("#gmw-yl-spinner").show();
     	
    	if ( $('#gmw-yl-form').is( ':visible' ) ) {
    		$('#gmw-yl-form').slideToggle();
    	}
    	
		$.ajax({
			type       	: "post",
			data  		: {action:'gmw_fl_delete_location' },		
			url        	: ajaxurl,
			success:function(data){
				
				$("#gmw-yl-spinner").hide();
				
				$("#gmw-yl-message p").html(data);
				$("#gmw-yl-message").fadeToggle(function(){
					setTimeout(function() {
						$("#gmw-yl-message").fadeToggle();
					},2500);
				});
				
			}
		});
    };
 	  	
});