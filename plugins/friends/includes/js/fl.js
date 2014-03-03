jQuery(document).ready(function($) {
			
	/// show hide edit user's location//// 
	$('#gmw-location-tab-edit-user-location-btn').click(function(event){
    	event.preventDefault();
    	$("#gmw-location-tab-edit-user-location").slideToggle();
    }); 
    
    /* locate user */
    $('#locate-me-bp').click(function(){
    	$("#wppl-ajax-loader-locate").show();
  		getLocationBP();
  	}); 
  	
    function getLocationBP() {
		if (navigator.geolocation) {
    		navigator.geolocation.getCurrentPosition(showPosition,showError);
		} else {
   	 		alert("Geolocation is not supported by this browser.");
   		}
	}

	function showPosition(position) {	
  		var gotLat = position.coords.latitude;
   		var gotLong = position.coords.longitude;		
  		returnAddress(gotLat, gotLong);
	}

	function showError(error) {
  		switch(error.code) {
   	 		case error.PERMISSION_DENIED:
      			alert("User denied the request for Geolocation");
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
	}
	
    /* conver lat/long to an address */
	function getAddress() {
 		var gotLat = document.getElementById("wppl_enter_lat").value;
   	 	var gotLong = document.getElementById("wppl_enter_long").value;
   	 	showAddressTab();
    	returnAddress(gotLat,gotLong);  
	}
	
	function breakAddress(address) {
		$("#address-tab-wrapper :text").val('');
		for ( x in address ) {
			if(address[x].types == 'street_number') {
				if(address[x].long_name != undefined) {
          			var streetNumber = address[x].long_name;
          			document.getElementById("wppl_street").value = streetNumber;
          		}
          	}
          				
         	if (address[x].types == 'route') {
          		if(address[x].long_name != undefined) {
          			var streetName = address[x].long_name;
          			if(streetNumber != undefined) {
          				street = streetNumber + " " + streetName;
          				document.getElementById("wppl_street").value = street;
          			} else {
          				street = streetName;
          				document.getElementById("wppl_street").value = street;
          			}
          		}		
          	}
          				
          	if(address[x].types == 'locality,political') {
          		if(address[x].long_name != undefined) {
          			city = address[x].long_name;
          			document.getElementById("wppl_city").value = city;
          		}
          	}
          			
          	if (address[x].types == 'administrative_area_level_1,political') {
          		if(address[x].short_name != undefined) {
          			var state = address[x].short_name;
          			document.getElementById("wppl_state").value = state;
          		}		
          	}
          				
          	/*if (address[x].types == 'administrative_area_level_1,political') {
          		if(address[x].short_name != undefined) {
          			var stateShort = address[x].short_name;
          			document.getElementById("wppl_state_long").value = stateShort;
          		}		
          	}*/
          			
          	if (address[x].types == 'postal_code') {
          		if(address[x].long_name != undefined) {
          			var zipcode = address[x].long_name;
          			document.getElementById("wppl_zipcode").value = zipcode;
          		}	
          	}
          				
          	if (address[x].types == 'country,political') {
          		if(address[x].short_name != undefined) {
          			var country = address[x].short_name;
          			document.getElementById("wppl_country").value = country;
          		}		
          	}
          				
          	/*if (address[x].types == 'country,political') {
          		if(address[x].short_name != undefined) {
          			var countryShort = address[x].short_name;
          			document.getElementById("wppl_country_short").value = countryShort;
          		}		
          	} */
          	$("#wppl-ajax-loader-locate").hide();		
        }
	}

	/* main function to conver lat/long to address */
	function returnAddress(gotLat, gotLong) {
		geocoder = new google.maps.Geocoder();
		var latlng = new google.maps.LatLng(gotLat ,gotLong);
	
		geocoder.geocode({'latLng': latlng, 'region':   'es'}, function(results, status) {
      		if (status == google.maps.GeocoderStatus.OK) {
       	 		if (results[0]) {
         			addressf = results[0].formatted_address;
        			document.getElementById("wppl_lat").value = gotLat;
        			document.getElementById("wppl_long").value = gotLong;
       				document.getElementById("wppl_address").value = addressf;
       				document.getElementById("wppl-addresspicker").value = addressf;
       	         
         			var address = results[0].address_components;		
					breakAddress(address);
        		}
      		} else {
        		alert("Geocoder failed due to: " + status);
        		$("#wppl-location-form :text").val('');
      		}
   		});
	} 

	/* convert address to lat/long */
	function getLatLong() {
		$("#saved-data :text").removeAttr('disabled'); 
  	  	var street = document.getElementById("wppl_street").value;
  	  	var apt = document.getElementById("wppl_apt").value;
 	  	var city = document.getElementById("wppl_city").value;
 	  	var state = document.getElementById("wppl_state").value;
 	  	var zipcode = document.getElementById("wppl_zipcode").value;
  	  	var country = document.getElementById("wppl_country").value;
    
  	  	retAddress = street + ", " + apt + " " + city + ", " + state + " " + zipcode + ", " + country;
    
    	geocoder = new google.maps.Geocoder();
   	 	geocoder.geocode( { 'address': retAddress}, function(results, status) {
      		if (status == google.maps.GeocoderStatus.OK) {
        		retLat = results[0].geometry.location.lat();
        		retLong = results[0].geometry.location.lng();
        		
        		var address = results[0].address_components;
        		
        		for ( x in address ) {
					if (address[x].types == 'administrative_area_level_1,political' && $('#wppl_state').val() ) {
          				if(address[x].short_name != undefined) {
          					var state = address[x].short_name;
          					document.getElementById("wppl_state").value = state;
          				}			
          			}
          				
          			/*if (address[x].types == 'administrative_area_level_1,political') {
          				if(address[x].long_name != undefined) {
          					var stateLong = address[x].long_name;
          					document.getElementById("wppl_state_long").value = stateLong;
          				}	
          			} */
          			
          		
          			if (address[x].types == 'country,political' && $('#wppl_countryate').val() ) {
          				if(address[x].short_name != undefined) {
          					var country = address[x].short_name;
          					document.getElementById("wppl_country").value = country;
          				}		
          			}
          				
          			/*if (address[x].types == 'country,political') {
          				if(address[x].long_name != undefined) {
          					var countryLong = address[x].long_name;
          					document.getElementById("wppl_country_long").value = countryLong;
          				}		
          			}*/
          		}
          		
       			document.getElementById("wppl_lat").value = retLat;
        		document.getElementById("wppl_long").value = retLong;
       			document.getElementById("wppl_address").value = retAddress;
       			saveLocation();
    		} else {
        		alert("Geocode was not successful for the following reason: " + status);     
       			$("#wppl-location-form :text").val('');
    		}
    	});
	}

    /* save location */   	
    function saveLocation() {
  	 	if($("#gmw-location-tab-edit-user-location").is(":visible")) { $("#gmw-location-tab-edit-user-location").slideToggle(); }
		$("#wppl-ajax-loader-bp").show();
		$("#wppl-bp-feedback").html('');
		$("#wppl-bp-feedback").css('opacity',1);
    	
		var newLocation = $('#wppl-location-form').serialize();
 		
		$.ajax({
			type:"POST",
			url: adminUrl,
			data: newLocation,
			success:function(data){
				setTimeout(function() {
					$("#wppl-bp-feedback").html(data);
    				$("#wppl-ajax-loader-bp").hide();
    				$("#saved-data :text").prop('disabled', true);
    			
   				},500);
   				setTimeout(function() {
					$("#wppl-bp-feedback").animate({opacity:0});
   				},2500);
			}
		});
		return false;
 	};
 	  	
  	$('.member-get-address').click(function(){
  		getAddress();
  	});
    
  	$('#member-save-location').click(function(){
		getLatLong();	
    });
    
    ///// remove address function ////
  	$('#remove-address-btn').click(function(){
    	$("#wppl-location-form :text").val('');
		saveLocation();	
    });
 	
	$('#wppl-address-tab').click(function(){
  		showAddressTab();
  	});
  	
	$('#wppl-latlong-tab').click(function(){
  		showLatLongTab();
  	});
	
	function showAddressTab() {	
		if ( 'active' != $(this).attr('class') ) {
			$('div.icons-tab').hide();
			$('div.lat-long-tab').hide();
			$('div.address-tab').show();
			$('div.metabox-tabs-div ul li.active').removeClass('active');
			$('div.metabox-tabs-div ul li.address-tab').addClass('active');
		}
	}

	function showLatLongTab() {	
		if ( 'active' != $(this).attr('class') ) {
			$('div.icons-tab').hide();
			$('div.address-tab').hide();
			$('div.lat-long-tab').show();
			$('div.metabox-tabs-div ul li.active').removeClass('active');
			$('div.metabox-tabs-div ul li.lat-long-tab').addClass('active');
		}
	}
		
	/*
	 * $ UI addresspicker @VERSION
	 *
	 * Copyright 2010, AUTHORS.txt (http://$ui.com/about)
	 * Dual licensed under the MIT or GPL Version 2 licenses.
	 * http://$.org/license
	 *
	 * http://docs.$.com/UI/Progressbar
	 *
	 * Depends:
	 *   $.ui.core.js
	 *   $.ui.widget.js
	 *   $.ui.autocomplete.js
	 */
	
	if (memLoc.savedLat != null) {
		centerMap = new google.maps.LatLng(memLoc.savedLat,memLoc.savedLong);
	} else {
		centerMap = new google.maps.LatLng( 40.7115441,-74.01348689999998);
	}
	
	(function( $, undefined ) {
	  $.widget( "ui.addresspicker", {
		options: {
			appendAddressString: "",
			draggableMarker: true,
			regionBias: null,
			mapOptions: {
				zoom: 12, 
				center: centerMap,
				scrollwheel: false,
				mapTypeId: google.maps.MapTypeId.ROADMAP
			},
			elements: {
				map: false,
				lat: false,
				lng: false,
				latD: false,
				lngD: false,
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
		  this._updatePosition(this.gmarker.getPosition());
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
			select: $.proxy(this._selectAddress, this),
			
		  });
		  
		  this.lat      						= $(this.options.elements.lat);
		  this.lng      						= $(this.options.elements.lng);
		  this.latD      						= $(this.options.elements.latD);
		  this.lngD     						= $(this.options.elements.lngD);
		  this.street_number 					= $(this.options.elements.street_number);
		  this.route							= $(this.options.elements.route);
		  this.locality 						= $(this.options.elements.locality);
		  this.administrative_area_level_1 		= $(this.options.elements.administrative_area_level_1);
		  this.postal_code 						= $(this.options.elements.postal_code);
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
			icon: "http://www.google.com/intl/en_us/mapfiles/ms/micons/blue-dot.png",
			draggable: this.options.draggableMarker});
		  google.maps.event.addListener(this.gmarker, 'dragend', $.proxy(this._markerMoved, this));
		  
		  this.gmarker.setVisible(false);
		},
		
		_updatePosition: function(location) {
		  if (this.lat) {
			this.lat.val(location.lat());
			this.latD.val(location.lat());    
		  }
		  if (this.lng) {
			this.lng.val(location.lng());
			this.lngD.val(location.lng());
		  }
		
		},
		//// when drag marker - also removed all fields ///
		_markerMoved: function() {
		
			$("#wppl-location-form :text").val('');
			this._updatePosition(this.gmarker.getPosition());
			 getAddress();		
		},
		
		// Autocomplete source method: fill its suggests with google geocoder results
		_geocode: function(request, response) {
			showAddressTab();
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
			  return component.long_name;
			}
		  }
		  return "";
		},
		
		_focusAddress: function(event, ui) {
		  var address = ui.item;
		   document.getElementById("wppl_address").value = address.formatted_address;
		  if (!address) {
			return;
		  }
		},
		
		_selectAddress: function(event, ui) {
		  this.selectedResult = ui.item;
		  console.log(ui.item);
		  var address = ui.item.address_components;
		   breakAddress(address);  			
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
	
	})( $ );
	
	$(function() {	
			
		var addresspicker = $( "#addresspicker" ).addresspicker();
		var addresspickerMap = $( "#wppl-addresspicker" ).addresspicker({
			//regionBias: "fr",
			elements: {
			map:      						"#bp-map",
			lat:      						"#wppl_lat",
			lng:      						"#wppl_long",
			latD:      						"#wppl_enter_lat",
			lngD:      						"#wppl_enter_long",  
			route:							'#wppl_street',
			locality: 						'#wppl_city',
			administrative_area_level_1:	'#wppl_state',
			postal_code:  					'#wppl_zipcode',
			country:  						'#wppl_country',
			}
		});
		var gmarker = addresspickerMap.addresspicker( "marker");
		gmarker.setVisible(true);
		addresspickerMap.addresspicker( "updatePosition");
	});

/*********************************************/
	
	$('.metabox-tabs').show();
	showAddressTab();
	//function hideEdit() { 
		if(document.getElementById('wppl_address').value == "") {
			document.getElementById('wppl_lat').value = "";
			document.getElementById('wppl_long').value = "";
		}	
		$("#gmw-location-tab-edit-user-location").slideToggle();
	//}
	
	//window.onload = hideEdit;
}); 