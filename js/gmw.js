jQuery(document).ready(function($){ 
	//GmwGetLocation();
	var lWidget =0;
	
	/**
	 * GMW JavaScript - Set Cookie
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	function gmwSetCookie(name,value,exdays) {
		var exdate=new Date();
		exdate.setTime(exdate.getTime() + (exdays*24*60*60*1000));
		var cooki=escape(encodeURIComponent(value)) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=name + "=" + cooki + "; path=/";
	}
	window.gmwSetCookie = gmwSetCookie;
	
	/**
	 * GMW JavaScript - Get Cookie
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	function gmwGetCookie(cookie_name) {
	    var results = document.cookie.match ('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
	    return results ? decodeURIComponent(results[2]) : null;
	}
	window.gmwGetCookie = gmwGetCookie;
	/**
	 * GMW JavaScript - Delete Cookie
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	function gmwDeleteCookie(c_name) {
	    document.cookie = encodeURIComponent(c_name) + "=deleted; expires=" + new Date(0).toUTCString();
	}
	window.gmwDeleteCookie = gmwDeleteCookie;
	
	$('.gmw-map-loader').fadeOut(1500);
	
	if (navigator.geolocation) $('.gmw-locate-me-btn').show();
	// remove red border from input field
	$('.gmw-address').focus(function() {
		if ( $(this).hasClass('gmw-no-address-error') ) $(this).removeClass('gmw-no-address-error');
	});
	// when submitting a form	
	$('.gmw-form').submit(function(e) {
		
		var sForm = $(this);
		var formId = sForm.find('.gmw-form-id').val();
		
		//get the entered address
		if ( sForm.find('.gmw-address').hasClass('gmw-full-address') ) {
			var address = sForm.find('.gmw-full-address').val();
		} else {
			var address = [];
			sForm.find(".gmw-address").each(function(){
				address.push($(this).val());
			});
			address = address.join(' ');
		}
		
		// check if we are submmiting the same address and if we have lat/long. 
		//if so no need to geocode again and submit the form with the information we already have
		if ( sForm.find('.prev-address').val() == address && $.trim(sForm.find('.gmw-lat').val()).length > 0 ) return true;
		//Check if the address was geocoded and if so with need to submit this form
		if ( sForm.find('.gmw-submit').hasClass('submitted') ) return true;
		
		//stop the form submission. we need to geocode the address
		e.preventDefault();
		//if address field is empty create a red border for the input field and stop the function
		if ( !$.trim(address).length ) {
			if ( sForm.find('.gmw-address').hasClass('mandatory') ) {
				if ( !sForm.find('.gmw-address').hasClass('gmw-no-address-error') ) sForm.find('.gmw-address').toggleClass('gmw-no-address-error');
			} else {
				sForm.find('.gmw-submit').toggleClass('submitted');
				setTimeout(function() {
					sForm.find('.gmw-submit').click();
				}, 500);
			}
			return false;
		}
	
		//run google geocoder
		geocoder = new google.maps.Geocoder();
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				
				//add class to submit button so the form will be submitted after geocoding
				sForm.find('.gmw-submit').toggleClass('submitted');
				// Modify the lat and long hidden fields 
				sForm.find('.gmw-lat').val(results[0].geometry.location.lat());
				sForm.find('.gmw-long').val(results[0].geometry.location.lng());
				// submit the form with the location
				setTimeout(function() {
					sForm.find('.gmw-submit').click();
				}, 500);
			} else {
				//if address was not geocoded stop the function and display error message
				alert("We could not find the address you entered for the following reason: " + status);     
			}
		});
	});
	
	// When click on locator button in a form
	$('.gmw-locate-btn').click(function() {
		lWidget =0;
		$(this).toggleClass('locator-submitted');
		$(this).closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast');
		$('.gmw-address').attr('disabled','disabled');
		$('.gmw-submit').attr('disabled','disabled');
		setTimeout(function() {
			GmwGetLocation();	
		},500);
	});

	submitNo = 1;
	if (gmwGetCookie('wppl_lat') == undefined) {
		 if ( (gmwGetCookie('wppl_asked_today') != "yes") && (autoLocate == 1) ) {
			submitNo = 0;
			$('body').prepend('<div id="gmw-auto-locate-hidden" style="display:none"><div id="message"><div id="gmw-locator-success-message">Getting your current location...</div></div></div>').ready(function() {
				tb_show("Your Current Location","#TB_inline?#bb&width=250&height=130&inlineId=gmw-auto-locate-hidden",null);
			});
			gmwSetCookie("wppl_asked_today","yes",1);
			GmwGetLocation();
		}
	}
		
	function removeMessage() {
		$('.gmw-submitted').each(function() {
			if( $(this).val() == 1 ) {
				$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('');
				tb_remove();
				return;
			}
		});
		$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('');
    	$('.gmw-cl-form #gmw-cl-info').show('fast');
	}
	
	
	$('.gmw-cl-trigger').click(function() {
		lWidget = 1;
		$(this).closest('.gmw-cl-form').find(".gmw-locator-spinner").show('fast');
		GmwGetLocation();
	});
	
	/**
	 * GMW JavaScript - Get the user's curent location
	 * @version 1.0
	 * @author Eyal Fitoussi
	 */
	function GmwGetLocation() {
		// if GPS exists locate the user //
		if (navigator.geolocation) {
	    	navigator.geolocation.getCurrentPosition(showPosition,showError, {timeout:10000});
	    	if (autoLocate != 1) {
	    		$('.gmw-cl-form #gmw-cl-info').hide('fast');
	    		$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-success-message">Getting your current location...</div>');
			}
		} else {
			// if nothing found we cant do much. we cant locate the user :( //
			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-success-message">we are sorry! Geolocation is not supported by this browser and we cannot locate you.</div>');
			setTimeout(function() {
	      		gmwSetCookie("wppl_denied","denied",1);
	      		removeMessage();
	      	},2500);
		} 
		
		// GPS locator function //
		function showPosition(position) {
			var geocoder = new google.maps.Geocoder();
	  		geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function (results, status) {
	        	if ( status == google.maps.GeocoderStatus.OK ) {
	          		getAddressFields(results);
	        	} else {
	          		alert('Geocoder failed due to: ' + status);
	        	}
	      	});
	  	}
	
		function showError(error) {
			
			switch(error.code) {
	    		case error.PERMISSION_DENIED:
	    			if ( autoLocate == 1) {
	    				$('#TB_ajaxContent').find('#message').html('<div id="gmw-locator-error-message">User denied the request for Geolocation.</div>');
	    				setTimeout(function() {
		      				gmwSetCookie("wppl_denied","denied",1);
		      				tb_remove();
		      			},1500);
	    			}
	    			if ( lWidget == 1 ) {
		      			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-error-message">User denied the request for Geolocation.</div>');
		      			$(".gmw-locator-spinner").hide('fast');		
		      			setTimeout(function() {
		      				gmwSetCookie("wppl_denied","denied",1);
		      				removeMessage();
		      			},1500);
	    			} else {
						alert('User denied the request for Geolocation.');
						$('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
						$('.gmw-address').removeAttr('disabled');
						$('.gmw-submit').removeAttr('disabled');
					}
	      		break;
	    		case error.POSITION_UNAVAILABLE:
	    			if ( lWidget == 1 ) {
		      			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-error-message">Location information is unavailable</div>');
		      			$(".gmw-locator-spinner").hide('fast');		
		      			setTimeout(function() {
		      				gmwSetCookie("wppl_denied","denied",1);
		      				removeMessage();
		      			},1500);
	    			} else {
	    				alert('Location information is unavailable.');
	    				$('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
	    				$('.gmw-address').removeAttr('disabled');
	    				$('.gmw-submit').removeAttr('disabled');
	    			}
	      		break;
	    		case 3:
	    			if ( lWidget == 1 ) {
		      			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-error-message">The request to get user location timed out</div>');
		      			$(".gmw-locator-spinner").hide('fast');		
		      			setTimeout(function() {
		      				gmwSetCookie("wppl_denied","denied",1);
		      				removeMessage();
		      			},1500);
					} else {
						alert('The request to get user location timed out.');
						$('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
						$('.gmw-address').removeAttr('disabled');
						$('.gmw-submit').removeAttr('disabled');
					}
	      		break;
	    			case error.UNKNOWN_ERROR:
	    			if ( lWidget == 1 ) {
		      			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-error-message">An unknown error occurred</div>');
		      			$(".gmw-locator-spinner").hide('fast');		
		      			setTimeout(function() {
		      				gmwSetCookie("wppl_denied","denied",1);
		      				removeMessage();
		      			},1500);
	    			} else {
						alert('An unknown error occurred');
						$('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
						$('.gmw-address').removeAttr('disabled');
						$('.gmw-submit').removeAttr('disabled');
					}
	      		break;
			}
		}
	}
	
	/* get location in user's location widget when manually typing address */	
	$('.gmw-cl-form').submit(function() {
		lWidget = 1;
		$(this).closest('.gmw-cl-form').find(".gmw-locator-spinner").show('fast');
		
		var retAddress = $(this).find(".gmw-cl-address").val();
		
		geocoder = new google.maps.Geocoder();
		//locateMessage = 0;
	   	geocoder.geocode( { 'address': retAddress}, function(results, status) {
	      	if (status == google.maps.GeocoderStatus.OK) {	
	      		geocoder.geocode({'latLng': new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng())}, function (results, status) {
	        		if (status == google.maps.GeocoderStatus.OK) {
	        			//submitNo = 0;
	          			getAddressFields(results);
	        		} else {
	          			alert('Geocoder failed due to: ' + status);
	        		}
	      		});
	    	} else {
	    		$(".gmw-locator-spinner").hide('fast');
	    		$('.gmw-cl-form #gmw-cl-info').hide('fast');
	    		$('#TB_ajaxContent .gmw-cl-form #gmw-cl-message-wrapper').html('<div id="gmw-locator-error-message">Geocode was not successful for the following reason: ' + status +'</div>');
	      		setTimeout(function() {
	      			gmwSetCookie("wppl_denied","denied",1);
	      			removeMessage();
	      		},2500);
	    	}
	   	}); 
	});
	
	/* main function to geocoding from lat/long to address or the other way around when locating the user */
	function getAddressFields(results) {
		/* remove all cookies - we gonna get new values */
		gmwDeleteCookie('wppl_city');
		gmwDeleteCookie('wppl_state');
		gmwDeleteCookie('wppl_zipcode');
		gmwDeleteCookie('wppl_country');
		gmwDeleteCookie('wppl_lat');
		gmwDeleteCookie('wppl_long');
	
		var street_number = false;
		var street = false;
		var city = false;
		var state = false;
		var zipcode = false;
		var country = false;
		var cityYes = false;
	    var stateYes = false;
	    var zipcodeYes = false;
	    var countryYes = false;
	    var streetYes = false;
	           	
		var address = results[0].address_components;
		
		gotLat = results[0].geometry.location.lat();
	    gotLong = results[0].geometry.location.lng();
	    
	    gmwSetCookie("wppl_lat",gotLat,7);
	    gmwSetCookie("wppl_long",gotLong,7);
		
		/* check for each of the address components and if exist save it in a cookie */
		for ( x in address ) {
			
			if ( address[x].types == 'street_number' ) {
				street_number = address[x].long_name; 
			}
			
			if ( address[x].types == 'route') {
				street = address[x].long_name;  
				if ( street_number != false ) {
					street = street_number + ' ' + street;
				} 
				gmwSetCookie("wppl_street", street, 7);
				streetYes = 1;
			}
	
			if ( address[x].types == 'administrative_area_level_1,political' ) {
	          	state = address[x].short_name;
	          	gmwSetCookie("wppl_state", state, 7);
	          	stateYes = 1;
	         } 
	         
	         if(address[x].types == 'locality,political') {
	          	city = address[x].long_name;
	          	gmwSetCookie("wppl_city",city,7);
	          	cityYes = 1;
	         } 
	         
	         if (address[x].types == 'postal_code') {
	          	zipcode = address[x].long_name;
	          	gmwSetCookie("wppl_zipcode",zipcode,7);
	          	zipcodeYes = 1;
	        } 
	        
	        if (address[x].types == 'country,political') {
	          	country = address[x].long_name;
	          	gmwSetCookie("wppl_country",country,7);
	          	countryYes = 1;
	         } 
		}
		
		// if component is not exists clear the cookie
		if( streetYes != 1 ) gmwDeleteCookie('wppl_street');
		if( cityYes != 1 ) gmwDeleteCookie('wppl_city');
		if( stateYes != 1 ) gmwDeleteCookie('wppl_state');
		if( zipcodeYes != 1 ) gmwDeleteCookie('wppl_zipcode');
		if( countryYes != 1 ) gmwDeleteCookie('wppl_country');
		
		addressOut = street + ' ' + city + ' ' + state + ' ' + zipcode + ' ' + country;
				
		if ( autoLocate == 1) {
			//$(".gmw-locator-spinner").hide('fast');	
    		//$('.gmw-cl-form #gmw-cl-info').hide('fast');
			$('#TB_ajaxContent').find('#message').html('<div id="gmw-locator-success-message">We found you at ' + addressOut + '</div>');
			setTimeout(function() {
				location.reload();	
			},1500);
		}
		// if a form was submitted */
		if ( $(".locator-submitted")[0] ) {
			
			$(".gmw-locator-spinner").hide('fast');	
			$('.gmw-cl-form #gmw-cl-info').hide('fast');
			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-success-message">We found you at ' + addressOut + '</div>');
			
			gForm = $('.locator-submitted').closest('form');
			
			$('.gmw-address').removeAttr('disabled');
			$('.gmw-submit').removeAttr('disabled');
			
			if ( gForm.find('.gmw-address').hasClass('gmw-full-address') ) {
				gForm.find('.gmw-full-address').val(addressOut);
			} else {
				gForm.find('.gmw-saf-street').val(street);
				gForm.find('.gmw-saf-city').val(city);
				gForm.find('.gmw-saf-state').val(state);
				gForm.find('.gmw-saf-zipcode').val(zipcode);
				gForm.find('.gmw-saf-country').val(country);
			}
			
			gForm.find('.gmw-submit').toggleClass('submitted');
			gForm.find('.gmw-lat').val(gotLat);
			gForm.find('.gmw-long').val(gotLong);
			
			/*$('<input>').attr({
			    type: 'hidden',
			    //id: 'foo',
			    name: 'wppl_address[]',
			    value: addressOut
			}).appendTo(gForm); */
			
			setTimeout(function() {
				gForm.find('.gmw-submit').click();	
			},1500);
		};
		
		if ( lWidget == 1 ) {
			$(".gmw-locator-spinner").hide('fast');	
    		$('.gmw-cl-form #gmw-cl-info').hide('fast');
			$('#TB_ajaxContent').find('#gmw-cl-message-wrapper').html('<div id="gmw-locator-success-message">We found you at ' + addressOut + '</div>');
			setTimeout(function() {
				location.reload();
			},1500);
		} 
	}
  
});	
