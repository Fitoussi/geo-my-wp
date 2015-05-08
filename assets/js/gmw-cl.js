jQuery(document).ready(function($) {
	
	$('.gmw-cl-form-trigger').click(function(e) {
		e.preventDefault();
		$(this).closest('.gmw-cl-wrapper').find('.gmw-cl-form-wrapper').slideToggle();
	})
	
	gmwClObject = {};
	
	$('.gmw-cl-address-input').bind('keypress', function (e){
        if ( e.keyCode == 13 ) {
        	$(this).closest('form').find('.gmw-cl-form-submit-icon').click();
        }
    });
	
	/**
	 * Locator success message
	 * @param  {[type]} returnedLocator [description]
	 * @return {[type]}                 [description]
	 */
	function gmwLocatorSuccess( returnedLocator ) {

		gmwClObject['results'] = returnedLocator.results;
	    gmwClObject['address'] = returnedLocator.results[0].formatted_address;
	        		
	    gmwGetAddressFields( returnedLocator.results );
    }

    /**
     * Locator failed message
     * @param  {return message} returnedLocator 
     * @return {[type]}              
     */
    function gmwLocatorFailed( returnedLocator ) {

    	//show faield message
        $('#gmw-cl-message-'+gmwClObject['elementID']).addClass('error').html('Geocoder failed due to: ' + returnedLocator.message );

        setTimeout(function() {
			$('#gmw-cl-respond-wrapper-'+gmwClObject['elementID']).slideToggle();
			$('#gmw-cl-message-'+gmwClObject['elementID']).html('');
			$('.gmw-cl-address-input').prop('disabled',false);
		},3000);
    }

	/* when autolocating user */
	$('.gmw-cl-locator-trigger').click(function(e) {
		
		gmwClObject = {};
		
		//get the form object
		gmwClObject['form'] 	 = jQuery(this).closest('form');

		//get the element ID
		gmwClObject['elementID'] = gmwClObject['form'].find('.gmw-cl-element-id').val();
		
		e.preventDefault();
		
		//disbale the address field
		$('.gmw-cl-address-input').prop( 'disabled', true );

		//show message holder
		$("#gmw-cl-respond-wrapper-"+gmwClObject['elementID']).slideToggle('fast', function() {
			
			//show loading message
			$('#gmw-cl-message-'+gmwClObject['elementID']).addClass('locating').html('Getting your current location...'); 	
		});
		
		//run the locator
		GmwAutoLocator( gmwLocatorSuccess, gmwLocatorFailed )
	});

	/* get location in user's location widget when manually typing address */	
	$('.gmw-cl-form-submit-icon').click(function(e) {
		
		gmwClObject = {};
		
		gmwClObject['form'] 	 = jQuery(this).closest('form');
		gmwClObject['elementID'] = gmwClObject['form'].find('.gmw-cl-element-id').val();

		e.preventDefault();
				
		$('.gmw-cl-address-input').prop( 'disabled', true );
		$("#gmw-cl-respond-wrapper-"+gmwClObject['elementID']).slideToggle();
		
		clAddress = $("#gmw-cl-address-input-"+gmwClObject['elementID']).val();
		gmwClObject['address'] = clAddress;
		
		geocoder = new google.maps.Geocoder();
	
		countryCode = 'us';
		
		if ( gmwSettings.general_settings.country_code != undefined ) {
			countryCode = gmwSettings.general_settings.country_code;
		}
			
	   	geocoder.geocode( { 'address': clAddress, 'region': countryCode }, function(results, status) {
	      	
	   		if (status == google.maps.GeocoderStatus.OK) {	
	      		geocoder.geocode({'latLng': new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng())}, function (results, status) {
	        		if ( status == google.maps.GeocoderStatus.OK ) {
	        			gmwClObject['results'] = results
	        			gmwGetAddressFields(results);
	        		} else {
	        			$('#gmw-cl-message-'+gmwClObject['elementID']).addClass('error').html('Geocoder failed due to: ' + status);
	        		}
	      		});
	    	} else {
	   
	    		$('#gmw-cl-message-'+gmwClObject['elementID']).addClass('error').html('Geocode was not successful for the following reason: ' + status);
	      		setTimeout(function() {
	      			$('#gmw-cl-respond-wrapper-'+gmwClObject['elementID']).slideToggle();
	      			$('#gmw-cl-message-'+gmwClObject['elementID']).removeClass('error').html('');
	      			$('.gmw-cl-address-input').prop('disabled',false);
	      		},3000);
	    	}
	   	}); 
	});
	
	/* main function to geocoding from lat/long to address or the other way around when locating the user */
	function gmwGetAddressFields(results) {
			
		var street_number = false;
		var street 		  = false;
		var address 	  = results[0].address_components;
		var gotLat  	  = results[0].geometry.location.lat();
	    var gotLng 		  = results[0].geometry.location.lng();
	    	    
	    $('#gmw_cl_lat_'+gmwClObject['elementID']).val(gotLat);
	    $('#gmw_cl_lng_'+gmwClObject['elementID']).val(gotLng);
		$('#gmw_cl_address_'+gmwClObject['elementID']).val(gmwClObject['address']);
	    $('#gmw_cl_formatted_address_'+gmwClObject['elementID']).val(results[0].formatted_address);
	    		
		/* check for each of the address components and if exist save it in a cookie */
		for ( x in address ) {
			
			if ( address[x].types == 'street_number' ) {
				street_number = address[x].long_name; 
				$('#gmw_cl_street_number_'+gmwClObject['elementID']).val(street_number);
			}
			
			if ( address[x].types == 'route' ) {
				street = address[x].long_name;  

				$('#gmw_cl_street_name_'+gmwClObject['elementID']).val(street);
				if ( street_number != false ) {
					street = street_number + ' ' + street;
				} 
				$('#gmw_cl_street_'+gmwClObject['elementID']).val(street);
			}
	
			if ( address[x].types == 'administrative_area_level_1,political' ) {
	          	$('#gmw_cl_state_'+gmwClObject['elementID']).val(address[x].short_name);
	          	$('#gmw_cl_state_long_'+gmwClObject['elementID']).val(address[x].long_name);
	         } 
	         
	         if(address[x].types == 'locality,political') {
	          	$('#gmw_cl_city_'+gmwClObject['elementID']).val(address[x].long_name);
	         } 
	         
	         if (address[x].types == 'postal_code') {
	          	$('#gmw_cl_zipcode_'+gmwClObject['elementID']).val(address[x].long_name);
	          	
	        } 
	        
	        if (address[x].types == 'country,political') {
	          	$('#gmw_cl_country_'+gmwClObject['elementID']).val(address[x].short_name);
	          	$('#gmw_cl_country_long_'+gmwClObject['elementID']).val(address[x].long_name);
	         } 
		}
			
		$('#gmw-cl-message-'+gmwClObject['elementID']).removeClass('error').addClass('success').html('We found you at ' + results[0].formatted_address);
		
		setTimeout(function() {
			$('#gmw-cl-hidden-form-'+gmwClObject['elementID']).submit();
		},3500);	
	}
});