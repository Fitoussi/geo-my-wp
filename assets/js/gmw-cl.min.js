jQuery(document).ready(function($) {
	
	//trigger cl form
	$('.gmw-cl-form-trigger, #gmw-cl-close-btn').click(function(e) {
		e.preventDefault();
		$('.gmw-cl-form-wrapper').fadeToggle();
	});
	
	/**
	 * gmw JavaScript - Get the user's current location
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	function GmwGetLocation() {
		// if GPS exists locate the user //
		if (navigator.geolocation) {
	    	navigator.geolocation.getCurrentPosition(showPosition,showError, {timeout:10000});
	    	$('#gmw-cl-message').html('<div id="gmw-locator-success-message">Getting your current location...</div>');
	    	
		} else {
			// if nothing found we cant do much. we cant locate the user :( //
			$('#gmw-cl-message').html('<div id="gmw-locator-success-message">we are sorry! Geolocation is not supported by this browser and we cannot locate you.</div>');
			setTimeout(function() {
	      		$('#gmw-cl-message').html('');
	    		$("#gmw-cl-spinner").fadeToggle();
	    		$('#gmw-cl-address').prop('disabled',false);
	      	},2500);
		} 
		
		// GPS locator function //
		function showPosition(position) {
			var geocoder = new google.maps.Geocoder();
	  		geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function (results, status) {
	        	if ( status == google.maps.GeocoderStatus.OK ) {
	          		gmwGetAddressFields(results);
	        	} else {
	          		alert('Geocoder failed due to: ' + status);
	        	}
	      	});
	  	}
	
		function showError(error) {
			
			switch(error.code) {
	    		case error.PERMISSION_DENIED:
	    			$('#gmw-cl-message').html('<div id="gmw-locator-error-message">User denied the request for Geolocation</div>');	
	      		break;
	    		case error.POSITION_UNAVAILABLE:
	    			$('#gmw-cl-message').html('<div id="gmw-locator-error-message">Location information is unavailable</div>');
	      		break;
	    		case 3:
	    			$('#gmw-cl-message').html('<div id="gmw-locator-error-message">The request to get user location timed out</div>');
	      		break;
	    		case error.UNKNOWN_ERROR:
	    			$('#gmw-cl-message').html('<div id="gmw-locator-error-message">An unknown error occurred</div>');
	      		break;
			}
			
			setTimeout(function() {
  				$('#gmw-cl-message').html('');
  				$("#gmw-cl-spinner").fadeToggle();
  				$('#gmw-cl-address').prop('disabled',false);
  			},1500);
		}
	}
	
	$('#gmw-cl-address').bind('keypress', function (e){
        if ( e.keyCode == 13 ) {
        	$('#gmw-cl-submit-address').click();
        }
    });
	
	/* when autolocating user */
	$('#gmw-cl-trigger').click(function(e) {
		e.preventDefault();
		$('#gmw-cl-address').prop( 'disabled', true );
		$("#gmw-cl-respond-wrapper").fadeToggle();
		GmwGetLocation();
		
	});

	/* get location in user's location widget when manually typing address */	
	$('#gmw-cl-submit-address').click(function(e) {
		
		e.preventDefault();
		
		$('#gmw-cl-address').prop( 'disabled', true );
		$("#gmw-cl-respond-wrapper").slideToggle();
		
		retAddress = $(this).closest('form').find("#gmw-cl-address").val();
		
		geocoder = new google.maps.Geocoder();
	
		countryCode = 'us';
		
		if ( gmwSettings.general_settings.country_code != undefined ) {
			countryCode = gmwSettings.general_settings.country_code;
		}
			
	   	geocoder.geocode( { 'address': retAddress, 'region': countryCode }, function(results, status) {
	      	if (status == google.maps.GeocoderStatus.OK) {	
	      		geocoder.geocode({'latLng': new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng())}, function (results, status) {
	        		if ( status == google.maps.GeocoderStatus.OK ) {
	        			gmwGetAddressFields(results);
	        		} else {
	        			$('#gmw-cl-message').html('Geocoder failed due to: ' + status);
	        		}
	      		});
	    	} else {
	   
	    		$('#gmw-cl-message').addClass('error').html('Geocode was not successful for the following reason: ' + status);
	      		setTimeout(function() {
	      			$("#gmw-cl-respond-wrapper").slideToggle();
	      			$('#gmw-cl-message').removeClass('error').html('');
	      			$('#gmw-cl-address').prop('disabled',false);
	      		},3000);
	    	}
	   	}); 
	});
	
	/* main function to geocoding from lat/long to address or the other way around when locating the user */
	function gmwGetAddressFields(results) {
		
		/* remove all cookies - we gonna get new values */
		gmwDeleteCookie('gmw_city');
		gmwDeleteCookie('gmw_state');
		gmwDeleteCookie('gmw_zipcode');
		gmwDeleteCookie('gmw_country');
		gmwDeleteCookie('gmw_lat');
		gmwDeleteCookie('gmw_lng');
	
		var street_number = false;
		var street 		  = false;
		var address 	  = results[0].address_components;
		
		gotLat  	= results[0].geometry.location.lat();
	    gotLng 		= results[0].geometry.location.lng();
	    
	    $('#gmw-cl-lat').val(gotLat);
	    $('#gmw-cl-lng').val(gotLng);
		$('#gmw-cl-org-address').val(retAddress);
	    $('#gmw-cl-formatted-address').val(results[0].formatted_address);
	    
	    gmwSetCookie( "gmw_address", results[0].formatted_address, 7 );
	    gmwSetCookie( "gmw_formatted_address", results[0].formatted_address, 7 );
	    gmwSetCookie( "gmw_lat", gotLat, 7 );
	    gmwSetCookie( "gmw_lng", gotLng, 7 );
		
		/* check for each of the address components and if exist save it in a cookie */
		for ( x in address ) {
			
			if ( address[x].types == 'street_number' ) {
				street_number = address[x].long_name; 
			}
			
			if ( address[x].types == 'route' ) {
				street = address[x].long_name;  
				if ( street_number != false ) {
					street = street_number + ' ' + street;
				} 
				gmwSetCookie( "gmw_street",street,7);
				$('#gmw-cl-street').val(street);
			}
	
			if ( address[x].types == 'administrative_area_level_1,political' ) {
	          	gmwSetCookie("gmw_state",address[x].short_name,7);
	          	$('#gmw-cl-state').val(address[x].short_name);
	          	$('#gmw-cl-state-long').val(address[x].long_name);
	         } 
	         
	         if(address[x].types == 'locality,political') {
	          	gmwSetCookie("gmw_city",address[x].long_name,7);
	          	$('#gmw-cl-city').val(address[x].long_name);
	         } 
	         
	         if (address[x].types == 'postal_code') {
	          	gmwSetCookie("gmw_zipcode",address[x].long_name,7);
	          	$('#gmw-cl-zipcode').val(address[x].long_name);
	          	
	        } 
	        
	        if (address[x].types == 'country,political') {
	          	gmwSetCookie("gmw_country",address[x].short_name,7);
	          	$('#gmw-cl-country').val(address[x].short_name);
	          	$('#gmw-cl-country-long').val(address[x].long_name);
	         } 
		}
			
		$('#gmw-cl-message').addClass('success').html('We found you at ' + results[0].formatted_address);
		
		$('#gmw-cl-map').fadeToggle(function() {
			
			var latLng = new google.maps.LatLng( gotLat, gotLng );
			clMap = new google.maps.Map(document.getElementById('gmw-cl-map'), {
				zoom: 12,
				center:latLng
			});	
			
			marker = new google.maps.Marker({
			    position: latLng,
			    map: clMap
			});
			
			setTimeout(function() {
				$('#gmw-cl-hidden-form').submit();
			},3500);
			
		});
		
	}
});