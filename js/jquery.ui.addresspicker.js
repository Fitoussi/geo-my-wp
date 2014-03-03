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
(function( $, undefined ) {
  $.widget( "ui.addresspicker", {
    options: {
        appendAddressString: "",
        draggableMarker: true,
        regionBias: null,
        mapOptions: {
            zoom: 12, 
            center: new google.maps.LatLng( 40.7115441,-74.01348689999998),
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
      this._updatePosition(this.gmarker.getPosition());
    	document.getElementById("_wppl_lat").value = "";
    	document.getElementById("_wppl_long").value = "";
    	document.getElementById("_wppl_address").value = "";
    	document.getElementById("wppl-addresspicker").value = "";
		document.getElementById("_wppl_street").value = "";
       	document.getElementById("_wppl_apt").value = "";
       	document.getElementById("_wppl_city").value = "";
		document.getElementById("_wppl_state").value = "";
		document.getElementById("_wppl_zipcode").value = "";
		document.getElementById("_wppl_country").value = "";
      	showTab2();
    },
    
    // Autocomplete source method: fill its suggests with google geocoder results
    _geocode: function(request, response) {
    	showTab1();
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
       document.getElementById("_wppl_address").value = address.formatted_address;
      if (!address) {
        return;
      }
      
      if (this.gmarker) {
        this.gmarker.setPosition(address.geometry.location);
        this.gmarker.setVisible(true);

        this.gmap.fitBounds(address.geometry.viewport);
      }
      this._updatePosition(address.geometry.location);
         
      if (this.street_number) {
		this.street_number.val(this._findInfo(address, 'street_number'));
        if (this.route) {
       	 	this.route.val(this._findInfo(address, 'street_number') + " " + this._findInfo(address, 'route'));
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
      }
      if (this.postal_code) {
        this.postal_code.val(this._findInfo(address, 'postal_code'));
      }
      
      if (this.country) {
        this.country.val(this._findInfo(address, 'country'));
      }
      if (this.type) {
        this.type.val(address.types[0]);
      }
    },
    
    _selectAddress: function(event, ui) {
      this.selectedResult = ui.item;
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

})( jQuery );

jQuery(function() {	
		
		var addresspicker = jQuery( "#addresspicker" ).addresspicker();
		var addresspickerMap = jQuery( "#wppl-addresspicker" ).addresspicker({
			//regionBias: "fr",
		  elements: {
		    map:      						"#map",
		    lat:      						"#_wppl_lat",
		    lng:      						"#_wppl_long",
		    latD:      						"#_wppl_enter_lat",
		    lngD:      						"#_wppl_enter_long",  
		    route:							'#_wppl_street',
		    locality: 						'#_wppl_city',
		    administrative_area_level_1:	'#_wppl_state',
		    postal_code:  					'#_wppl_zipcode',
		    country:  						'#_wppl_country',
		  }
		});
		var gmarker = addresspickerMap.addresspicker( "marker");
		gmarker.setVisible(true);
		addresspickerMap.addresspicker( "updatePosition");
	});
	
function removeAddress() {
	document.getElementById("_wppl_lat").value = "";
    document.getElementById("_wppl_long").value = "";
    document.getElementById("_wppl_address").value = "";
    document.getElementById("_wppl_enter_lat").value = "";
	document.getElementById("_wppl_enter_long").value = "";
	document.getElementById("_wppl_street").value = "";
    document.getElementById("_wppl_apt").value = "";
    document.getElementById("_wppl_city").value = "";
	document.getElementById("_wppl_state").value = "";
	document.getElementById("_wppl_zipcode").value = "";
	document.getElementById("_wppl_country").value = "";
	document.getElementById("wppl-addresspicker").value = "";
	}
