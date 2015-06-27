/*
 * jQuery UI addresspicker @VERSION
 *
 * Copyright 2010, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * http://docs.jquery.com/UI/Progressbar
 *
 * Depends:
 *   jquery.ui.core.js
 *   jquery.ui.widget.js
 *   jquery.ui.autocomplete.js
 */
jQuery(document).ready(function($) {
	
    var zoomLevel = gmwSettings.post_types_settings.edit_post_zoom_level;
	/*
	 * Get the initial lat/long if exists in post to initiate the map
	 * otherwise set it to new-york as default
	 */
	if ( $('#_wppl_lat').val() != '' && $('#_wppl_long').val() != '' ) {
		  initLat = $('#_wppl_lat').val();
		  initLng = $('#_wppl_long').val();
	} else {
  		  initLat = gmwSettings.post_types_settings.edit_post_latitude;
		  initLng = gmwSettings.post_types_settings.edit_post_longitude;
	}
	var addLatLong = false;
	var runGeo = false;
	
	$.widget( "ui.addresspicker", {
	  
		options: {
			appendAddressString: "",
			draggableMarker: true,
			regionBias: null,
			mapOptions: {
				zoom: parseInt(zoomLevel), 
				center: new google.maps.LatLng(initLat,initLng),
				scrollwheel: false,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			},
		elements: {
			map: false,
            lat: false,
            lng: false,
            route: false,
            locality: false,
            postal_code: false,
            country: false,
            type: false
		}
	},

    marker: function() {
    	return this.gmarker;
    },
    map: function() {
    	return this.gmap;
    },
    updatePosition: function() {
    	this._updatePosition(this.gmarker.getPosition(),'no');
    },
    reloadPosition: function() {
    	this.gmarker.setVisible(true);
    	this.gmarker.setPosition(new google.maps.LatLng(this.lat.val(), this.lng.val()));
    	this.gmap.setCenter(this.gmarker.getPosition());
    },
    selected: function() {
    	return this.selectedResult;
    },
    _create: function() {
    	this.geocoder = new google.maps.Geocoder();
    	this.element.autocomplete({
    		source: $.proxy(this._geocode, this),  
    		focus:  $.proxy(this._focusAddress, this),
    		select: $.proxy(this._selectAddress, this)
    	});
      
    	this.lat      						= $(this.options.elements.lat);
    	this.lng      						= $(this.options.elements.lng);
    	this.street_number 					= $(this.options.elements.street_number);
    	this.route							= $(this.options.elements.route);
    	this.locality 						= $(this.options.elements.locality);
    	this.administrative_area_level_1 	= $(this.options.elements.administrative_area_level_1);
    	this.postal_code 					= $(this.options.elements.postal_code);
    	this.country  						= $(this.options.elements.country);
    	this.type     						= $(this.options.elements.type);
    	if (this.options.elements.map) {
    		this.mapElement = $(this.options.elements.map);
    		this._initMap();
    	}
    },

    _initMap: function() {
    	if (this.lat && this.lat.val()) {
    		this.options.mapOptions.center = new google.maps.LatLng(this.lat.val(), this.lng.val());
    	}
		
    	this.gmap = new google.maps.Map(this.mapElement[0], this.options.mapOptions);
    	this.gmarker = new google.maps.Marker({
    		position: this.options.mapOptions.center, 
    		map:this.gmap, 
    		draggable: this.options.draggableMarker});
    	google.maps.event.addListener(this.gmarker, 'dragend', $.proxy(this._markerMoved, this));
      
    	this.gmarker.setVisible(false);
    },
    
    _updatePosition: function(location, runGeo, addLatLong) {
    	if ( runGeo == 'yes' ) {
    		returnAddress(location.lat(),location.lng(), 'no');
    	}
    	if ( addLatLong == 'yes' ) {
	      	if (this.lat) {
	      		this.lat.val(location.lat());
	      	}
	      	if (this.lng) {
	      		this.lng.val(location.lng());
	      	}
    	}
    },
    //// when drag marker - also removed all fields ///
    _markerMoved: function() {
    	loaderOn();
 
    	$('.gmw-location-section input:text').val('');
      	$('#gmw-saved-data input:text').val('');
      	this._updatePosition(this.gmarker.getPosition(),'yes');
    },
    
    // Autocomplete source method: fill its suggests with google geocoder results
    _geocode: function(request, response) {
        var address = request.term, self = this;
        this.geocoder.geocode({
            'address': address + this.options.appendAddressString,
            'region': this.options.regionBias
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                for (var i = 0; i < results.length; i++) {
                    results[i].label =  results[i].formatted_address;
                };
            }
            response(results);
        });
    },
    
    _findInfo: function(result, type) {
      for (var i = 0; i < result.address_components.length; i++) {
        var component = result.address_components[i];
        if (component.types.indexOf(type) !=-1) {
          return component.short_name;
        }
      }
      return "";
    },
    _findInfoLong: function(result, type) {
        for (var i = 0; i < result.address_components.length; i++) {
          var component = result.address_components[i];
          if (component.types.indexOf(type) !=-1) {
            return component.long_name;
          }
        }
        return "";
     },
    
    _focusAddress: function(event, ui) {
      var address = ui.item;
       $("#_wppl_address").val(address.formatted_address);
       $("#_wppl_formatted_address").val(address.formatted_address);
      if (!address) {
        return;
      }
      
      if (this.gmarker) {
        this.gmarker.setPosition(address.geometry.location);
        this.gmarker.setVisible(true);

        this.gmap.fitBounds(address.geometry.viewport);
      }
      this._updatePosition(address.geometry.location, 'no', 'yes');
         
      if (this.street_number) {
		this.street_number.val(this._findInfo(address, 'street_number'));
		$('#_wppl_street_number').val(this._findInfoLong(address, 'street_number'));
		
        if (this.route) {
       	 	this.route.val(this._findInfo(address, 'street_number') + " " + this._findInfo(address, 'route'));
       	 	$('#_wppl_street_name').val(this._findInfoLong(address, 'route'));
      	}
      } else 
      if (this.route) {
        this.route.val(this._findInfo(address, 'route'));
      }
      
      if (this.locality) {
        this.locality.val(this._findInfo(address, 'locality'));
      }
      if (this.administrative_area_level_1) {
        this.administrative_area_level_1.val(this._findInfo(address, 'administrative_area_level_1'));
        $('._wppl_state_long').val(this._findInfoLong(address, 'administrative_area_level_1'));
      }
      if (this.postal_code) {
        this.postal_code.val(this._findInfo(address, 'postal_code'));
      }
      
      if (this.country) {
        this.country.val(this._findInfo(address, 'country'));
        $('._wppl_country_long').val(this._findInfoLong(address, 'country'));
      }
      
      if (this.type) {
        this.type.val(address.types[0]);
      }
    },
    
    _selectAddress: function(event, ui) {
      this.selectedResult = ui.item;
      changeLocationMessage('good');
    }
  });

  $.extend( $.ui.addresspicker, {
    version: "@VERSION"
  });

  // make IE think it doesn't suck
  if(!Array.indexOf){
    Array.prototype.indexOf = function(obj){
      for(var i=0; i<this.length; i++){
        if(this[i]==obj){
          return i;
        }
      }
      return -1;
    };
  }
  
  var addresspicker = jQuery( "#addresspicker" ).addresspicker();
	var addresspickerMap = jQuery( "#wppl-addresspicker" ).addresspicker({
			//regionBias: "fr",
		elements: {
		    map:      						"#map",
		    lat:      						"#_wppl_lat",
		    lng:      						"#_wppl_long",
		    route:							'#_wppl_street',
		    locality: 						'#_wppl_city',
		    administrative_area_level_1:	'._wppl_state',
		    postal_code:  					'#_wppl_zipcode',
		    country:  						'._wppl_country'
		}
	});
	var gmarker = addresspickerMap.addresspicker( "marker");
	gmarker.setVisible(true);
	addresspickerMap.addresspicker( "updatePosition");
	
	$('#publish').click(function(e) {
	
		if ( gmwOptions.post_types_settings.mandatory_address == 1 ) {
	
			if ( ( $('#_wppl_lat').val() == "" ) || ( $('#_wppl_long').val() == "" ) || ( $('#_wppl_address').val() == "" ) ) {
				
				e.preventDefault(e);
				$('.spinner').hide();
				alert("Post cannot be published. You must enter a vallid address. Please make sure that latitude and longitude fields are not empty.");					
				setTimeout(function() {	
       				jQuery('#publish').removeClass('button-primary-disabled');	
				},2000);
				
			} else {		
  				$('#_wppl_lat').removeAttr('disabled');
  				$('#_wppl_long').removeAttr('disabled');
  				$('#_wppl_address').removeAttr('disabled');
			};
		} else {
			$('#_wppl_lat').removeAttr('disabled');
			$('#_wppl_long').removeAttr('disabled');
			$('#_wppl_address').removeAttr('disabled');
		};
	});
	
	function loaderOff(location) {
		$("#gmw-location-loader").fadeToggle('fast', function() {
			setTimeout(function() {	
				if ( location == 'bad' ) {
					$("#gmw-bad-location-message").fadeToggle('fast', function() {
						$(this).addClass('gmw-location-message');
					});
				} else {
					$("#gmw-good-location-message").fadeToggle('fast', function() {
						$(this).addClass('gmw-location-message');
					});
				}
			},500);
		});
	}
	
	function loaderOn() {
		$(".gmw-location-message").fadeToggle('fast', function() {
			$(this).removeClass('gmw-location-message');
			setTimeout(function() {	
				$("#gmw-location-loader").fadeToggle('fast');
			},500);
		});
	}
	
	function changeLocationMessage(location) {
		$(".gmw-location-message").fadeToggle('fast', function() {
			$(this).removeClass('gmw-location-message');
			if ( location == 'bad' ) {
				$("#gmw-bad-location-message").fadeToggle('fast', function() {
					$(this).addClass('gmw-location-message');
				});
			} else {
				$("#gmw-good-location-message").fadeToggle('fast', function() {
					$(this).addClass('gmw-location-message');
				});
			}
		});
	}
	
	//check location exists on page load
	if ( $('#_wppl_lat').val() != '' && $('#_wppl_long').val() != '' && $('#_wppl_formatted_address') != '' ) changeLocationMessage('good');
	
	function gmwGetLocation() {
		loaderOn();
		if (navigator.geolocation) {
	    	navigator.geolocation.getCurrentPosition(showPosition,showError);
		} else {
	   	 	alert("Geolocation is not supported by this browser.");
	   	}
	}
		     
	function showPosition(position) {	
	  	var gotLat = position.coords.latitude;
	   	var gotLong = position.coords.longitude;		
	  	returnAddress(gotLat, gotLong, 'yes');
	}
	
	function showError(error) {
	  	switch(error.code) {
	    	case error.PERMISSION_DENIED:
	      		alert("User denied the request for Geolocation");
	      		loaderOff('bad');
	     	break;
	    	case error.POSITION_UNAVAILABLE:
	      		alert("Location information is unavailable.");
	      		loaderOff('bad');
	      	break;
	    	case error.TIMEOUT:
	      		alert("The request to get user location timed out.");
	      		loaderOff('bad');
	     	break;
	    	case error.UNKNOWN_ERROR:
	      		alert("An unknown error occurred.");
	      		loaderOff('bad');
	      	break;
		}
	}

	/* Geocode lat/long to address */
	function getAddress() {
	 	loaderOn();
	 	var gotLat = document.getElementById("_wppl_lat").value;
	    var gotLong = document.getElementById("_wppl_long").value;
	    returnAddress(gotLat,gotLong, 'yes');  
	}
	
	/* break address to componenet */
	function returnAddress(gotLat, gotLong, showMessage) {
		geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(gotLat ,gotLong);
		geocoder.geocode({'latLng': latlng, 'region':   'us'}, function(results, status) {
	      	if (status == google.maps.GeocoderStatus.OK) {
	       	 	
	      		if (results[0]) {
	      			
	      			if ( showMessage == 'yes')
	      				alert('Address successfully returned.');
	       	 		
	      			var streetNumber = false;
	       	 	
	         		 /* formatted address */
	       	 		addressf = results[0].formatted_address;
	       	 		$("#_wppl_address").val(addressf);
	       	 		$('#gmw_check_address').val(addressf);
	         		$('#_wppl_formatted_address').val(addressf);
	         		$("#wppl-addresspicker").val(addressf);
	         		
	         		/* latitude */
	        		$("#_wppl_lat").val(gotLat);
	        		$('#gmw_check_lat').val(gotLat);
	        		
	        		/* longitude */
	        		$("#_wppl_long").val(gotLong);
	        		$('#gmw_check_long').val(gotLong);
	        		
	        		 document.getElementById("_wppl_street_number").value = '';
	        		 document.getElementById("_wppl_street_name").value = '';
	       			
	        		/* brekdown address to components */
	         		var address = results[0].address_components;
	         		
					for ( x in address ) {
						if(address[x].types == 'street_number') {
							if(address[x].long_name != undefined) {
	          					streetNumber = address[x].long_name;
	          					$("#_wppl_street_number").val(streetNumber);
	          					$("#_wppl_street").val(streetNumber);
	          				}
	          			}
	          				
	          			if (address[x].types == 'route') {
	          				if(address[x].long_name != undefined) {
	          					var streetName = address[x].long_name;
	          					$("#_wppl_street_name").val(streetName);
	          					if( streetNumber != false && streetNumber != undefined ) {
	          						var street = streetNumber + " " + streetName;
	          						$("#_wppl_street").val(street);
	          					} else {
	          						$("#_wppl_street").val(streetName);
	          					}
	          				}		
	          			}
	          				
	          			if(address[x].types == 'locality,political') {
	          				if(address[x].long_name != undefined) {
	          					var city = address[x].long_name;
	          					$("#_wppl_city").val(city);
	          				}
	          			}
	          			
	          			if (address[x].types == 'administrative_area_level_1,political') {
	          				if(address[x].short_name != undefined) {
	          					var state = address[x].short_name;
	          					$("._wppl_state").val(state);
	          				}
	          				if(address[x].long_name != undefined) {
	          					var state_long = address[x].long_name;
	          					$("._wppl_state_long").val(state_long);
	          				}
	          					
	          			}
	          			
	          			if (address[x].types == 'postal_code') {
	          				if ( address[x].long_name != undefined ) {
	          					var zipcode = address[x].long_name;
	          					$("#_wppl_zipcode").val(zipcode);
	          				}	
	          			}
	          			
	          			if (address[x].types == 'country,political') {
	          				if(address[x].short_name != undefined) {
	          					var country = address[x].short_name;
	          					$("._wppl_country").val(country);
	          				}
	          				if(address[x].long_name != undefined) {
	          					var country_long = address[x].long_name;
	          					$("._wppl_country_long").val(country_long);
	          				}
	          			}
					}
	        	}
	        	loaderOff('good');
	      	} else {
	        	alert("Geocoder failed due to: " + status);
	        	removefields();
	        	loaderOff('bad');
	      	}
	    });
	} 
	
	/// convert address to lat/long ////////
	function getLatLong() {
		loaderOn();
	    
		var street = $("#_wppl_street").val();
	    var apt = $("#_wppl_apt").val();
	    var city = $("#_wppl_city").val();
	    var state = $("#_wppl_state").val();
	    var zipcode = $("#_wppl_zipcode").val();
	    var country = $("#_wppl_country").val();
	    
	    document.getElementById("_wppl_street_number").value = '';
	    document.getElementById("_wppl_street_name").value = '';
	    
	    retAddress = street + " " + city + " " + state + " " + zipcode + " " + country;
	    addressApt = street + " " + apt + " " + city + " " + state + " " + zipcode + " " + country;
	    
	    geocoder = new google.maps.Geocoder();
	    geocoder.geocode( { 'address': retAddress}, function(results, status) {
	      	if (status == google.maps.GeocoderStatus.OK) {
	      		
	      		alert('Latitude / Longitude successfully returned');
	      		
	      		retLat = results[0].geometry.location.lat();
	        	retLong = results[0].geometry.location.lng();
	        	    	
	      		addressf = results[0].formatted_address;
        
         		$("#_wppl_lat").val(retLat);
	        	$("#_wppl_long").val(retLong);
	       		$("#_wppl_address").val(addressApt);
	       		$('#_wppl_formatted_address').val(addressf);
	       		$('#wppl-addresspicker').val(addressf);
	       		
         		var address = results[0].address_components;
        
				for ( x in address ) {
					
					if (address[x].types == 'street_number') {
	      				if(address[x].long_name != undefined) {
	      					var street_number = address[x].long_name;
	      					document.getElementById("_wppl_street_number").value = street_number;
	      				}	
	      			}
					
					if (address[x].types == 'route') {
	      				if(address[x].long_name != undefined) {
	      					var street_name= address[x].long_name;
	      					document.getElementById("_wppl_street_name").value = street_name;
	      				}	
	      			}
					
		        	if (address[x].types == 'administrative_area_level_1,political') {
	      				if(address[x].short_name != undefined) {
	      					var state = address[x].short_name;
	      					$("._wppl_state").val(state);
	      				}
	      				if(address[x].long_name != undefined) {
	      					var state_long = address[x].long_name;
	      					document.getElementById("_wppl_state_long").value = state_long;
	      				}	
	      			}
	      			
	      			if (address[x].types == 'country,political') {
	      				if(address[x].short_name != undefined) {
	      					var country = address[x].short_name;
	      					$("._wppl_country").val(country);
	      				}
	      				if(address[x].long_name != undefined) {
	      					var country_long = address[x].long_name;
	      					document.getElementById("_wppl_country_long").value = country_long;
	      				}
	      			}
				}
      			
	       		loaderOff('good');
	    	} else {
	        	alert("Geocode was not successful for the following reason: " + status);     
	       		removefields();
	       		loaderOff('bad');
	    	}
	    });
	}
	
	/* delete fields */
	function removefields() {
		$('.gmw-location-manually-wrapper input:text, .gmw-location-section.autocomplete input:text').val('');
	}
	
	/* Locate me button */
	$('#gmw-admin-locator-btn').click(function() {
		removefields();
		gmwGetLocation();
	});

	/* delete fields button */
	$('#gmw-admin-delete-btn').click(function() {
		removefields();
		changeLocationMessage('bad');
	});
	
	/* get lat/long button */
	$('#gmw-admin-getlatlong-btn').click(function() {
		getLatLong();
	});
	
	/* get address button */
	$('#gmw-admin-getaddress-btn').click(function() {
		getAddress();
	});
	
	// tab between them
	jQuery('.metabox-tabs li a').each(function(i) {
		var thisTab = jQuery(this).parent().attr('class').replace(/active /, '');
		if ( 'active' != jQuery(this).attr('class') )
			jQuery('div.' + thisTab).hide();

		jQuery('div.' + thisTab).addClass('tab-content');
 
		jQuery(this).click(function(){
			// hide all child content
			jQuery(this).parent().parent().parent().children('div').hide();
 
			// remove all active tabs
			jQuery(this).parent().parent('ul').find('li.active').removeClass('active');
 
			// show selected content
			jQuery('div.' + thisTab).show();
			jQuery('li.'+thisTab).addClass('active');
		});
	});

	jQuery('.heading').hide();
	jQuery('.metabox-tabs').show();

});