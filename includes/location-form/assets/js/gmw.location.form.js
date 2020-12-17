/**
 * [GMW_Location_Form_Map_Providers description]
 * @type {Object}
 */
var GMW_Location_Form_Map_Providers = {

	'google_maps' : {

		latLng : function( lat, lng ) {
			return new google.maps.LatLng( lat, lng );
		},

		getPosition : function( element ) {
			return element.getPosition();
		},

		setMarkerPosition : function( marker, latLng ) {
			marker.setPosition( latLng );
		},

		resizeMap : function( map ) {
			google.maps.event.trigger( map, 'resize' );
		},

		setCenter : function( center, map ) {
			map.setCenter( center );
		},

		addressAutocomplete : function() {
			
			//loop and trigger address autocomplete for all field with the class 'gmw-lf-address-autocomplete'
			jQuery( '.gmw-lf-address-autocomplete' ).each( function() {

				//get the element ID
				autocompleteId = jQuery( this ).attr( 'id' );

				//autocomplete input field
				input = document.getElementById( autocompleteId );

				//modify autocomplete options
				options = GMW.apply_filters( 'gmw_lf_address_autocomplete_options', {}, autocompleteId, this_form );
			    
			    options.fields = [ 'address_component', 'formatted_address', 'geometry' ];

			    //init autocomplete
			    var autocomplete = new google.maps.places.Autocomplete( input, options );
			    
			    google.maps.event.addListener( autocomplete, 'place_changed', function() {
			    	
			    	place = autocomplete.getPlace();

					if ( ! place.geometry ) {
						return;
					}
						
					// get the address value entered in the address field.
					var address = jQuery( '#' + this_form.location_fields.address.id ).val();

		            GMW.do_action( 'gmw_lf_autocomplete_place_changed', place, this_form );

					// clear fields
					jQuery( '.group_coordinates, .group_address' ).val( '' );

					//update coords
					jQuery( '.group_coordinates.latitude' ).val( place.geometry.location.lat() );
					jQuery( '.group_coordinates.longitude' ).val( place.geometry.location.lng() );

					//update address and formatted address fields
					jQuery( '#gmw_lf_address, #gmw_lf_formatted_address' ).val( address );

					var fields = GMW_Geocoders.google_maps.getLocationFields( place );

					// add the address to the list of fields.
					fields.address = address;

					//get the rest of the address fields
					this_form.get_address_fields( fields, place, false );		
			    });
			});
		},

		renderMap : function() {

			// set initial coords based on saved location or user's current location 
			if ( this_form.is_location_confirmed ) {

				//lat = jQuery( '#gmw_lf_' + this_form.coords_fields.latitude.name ).val();
				//lng = jQuery( '#gmw_lf_' + this_form.coords_fields.longitude.name ).val();

				lat = jQuery( '#gmw_lf_latitude' ).val();
				lng = jQuery( '#gmw_lf_longitude' ).val();

			// Otehrwise, if no location exists on page load get coords from form args
			} else {

				lat = this_form.vars.map_lat;
				lng = this_form.vars.map_lng;
			}
			
			latLng = new google.maps.LatLng( lat, lng );
				
			// default map options
			map_options = {
				zoom 	  : parseInt( this_form.vars.map_zoom_level ),
				center 	  : latLng,
				mapTypeId : google.maps.MapTypeId[ this_form.vars.map_type ]
			};

			// modify map options
			map_options = GMW.apply_filters( 'gmw_lf_render_map_options', map_options, this_form );

			// initiate map
			this_form.map = new google.maps.Map( document.getElementById( this_form.location_fields.map.id ), map_options );	
			
			marker_options = {
			    position  : latLng,
			    map 	  : this_form.map,
				draggable : true
			};

			marker_options = GMW.apply_filters( 'gmw_lf_render_map_marker_options', marker_options, this_form );

			// set marker
			this_form.map_marker = new google.maps.Marker( marker_options );
			
			GMW.do_action( 'gmw_lf_render_map', this_form );

			//when dragging the map marker
			google.maps.event.addListener( this_form.map_marker, 'dragend', function( e ) {

				this_form.map_updated    = true;
				this_form.map_coords.lat = e.latLng.lat();
				this_form.map_coords.lng = e.latLng.lng();

				jQuery( '.group_coordinates, .group_address,' + '#' + this_form.location_fields.address.id ).val( '' );

				this_form.location_changed();

				//reverse geocode to get the address fields based on coords
				this_form.reverse_geocode( this_form.map_coords.lat, this_form.map_coords.lng, false );  
			});
		}
	},

	'leaflet' : {

		latLng : function( lat, lng ) {
			return L.latLng( lat, lng );
		},

		getPosition : function( element ) {
			return element.getLatLng();
		},

		setMarkerPosition : function( marker, latLng ) {
			marker.setLatLng( latLng );
		},

		resizeMap : function( map ) {
			map.invalidateSize();
		},

		setCenter : function( center, map ) {
			map.setView( center );
		},

		renderMap : function() {

			var lat, lng;

			// set initial coords based on saved location or user's current location 
			if ( this_form.is_location_confirmed ) {

				//lat = jQuery( '#gmw_lf_' + this_form.coords_fields.latitude.name ).val();
				//lng = jQuery( '#gmw_lf_' + this_form.coords_fields.longitude.name ).val();
				
				lat = jQuery( '#gmw_lf_latitude' ).val();
				lng = jQuery( '#gmw_lf_longitude' ).val();

			// Otehrwise, if no location exists on page load get coords from form args
			} else {
				lat = this_form.vars.map_lat;
				lng = this_form.vars.map_lng;
			}
			
			var latLng = L.latLng( lat, lng );
				
			// default map options
			var map_options = {
				zoom 	  : parseInt( this_form.vars.map_zoom_level ),
				maxZoom   : 18,
				minZoom   : 1
				//center 	  : latLng,
				//mapTypeId : google.maps.MapTypeId[ this_form.vars.map_type ]
			};

			// modify map options
			map_options = GMW.apply_filters( 'gmw_lf_render_map_options', map_options, this_form );

			// initiate map
			this_form.map = L.map( this_form.location_fields.map.id, map_options );	
				
			this_form.map.setView( latLng ).zoomControl.setPosition( 'bottomright' );
			
			// Load layers.
			L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
			}).addTo( this_form.map );

			var marker_options = {
			    //position  : latLng,
			    //map 	  : this_form.map,
				draggable : true
			};

			marker_options = GMW.apply_filters( 'gmw_lf_render_map_marker_options', marker_options, this_form );

			// set marker
			this_form.map_marker = new L.marker( latLng, marker_options );
			
			GMW.do_action( 'gmw_lf_render_map', this_form );

			this_form.map_marker.addTo( this_form.map ).on( 'dragend', function (e) {

				this_form.map_updated    = true;
				this_form.map_coords.lat = this_form.map_marker.getLatLng().lat;
				this_form.map_coords.lng = this_form.map_marker.getLatLng().lng;

				jQuery( '.group_coordinates, .group_address,' + '#' + this_form.location_fields.address.id ).val( '' );

				this_form.location_changed();

				//reverse geocode to get the address fields based on coords
				this_form.reverse_geocode( this_form.map_coords.lat, this_form.map_coords.lng, false ); 
			});
		}
	}
};

/**
 * GEO my WP Location form
 * @type {Object}
 */
var GMW_Location_Form = {

	// form arguments
	vars : {},

	// check if location is confirmed.
	is_location_confirmed : ( jQuery.trim( jQuery( '#gmw_lf_latitude' ).val() ).length != 0 && jQuery.trim( jQuery( '#gmw_lf_longitude' ).val() ).length != 0 ) ? true : false,

	// nonce 
	security : '',

	// form fields
	fields : false,

	// messages
	messages : false,

	// element wrapping the form
	wrapper_element : '.gmw-location-form-wrapper',

	// location fields
	location_fields : false,

	// address fields
	address_fields : false,

	// coordinates fields
	coords_fields : false,
	
	// action fields
	action_fields : false,

	// map status
	map_enabled : jQuery( '#gmw-lf-map' ).length ? true : false,

	// fields value changed
	fields_changed_status : 1,

	// additional map options
	map_options : {},

	map_updated : false,

	map_coords : {
		lat : '',
		lng : '',
	},

	// options for Google Address autocomplete
	autocomplete_options : {},

	// google API regions
	region : 'US',

	proceed_submission : false,

	location_exists : false,

	auto_confirming : false,

	// ajax response holder.
	ajaxResponse : {},

	/**
	 * return lat - lng object.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @param  {[type]} lat [description]
	 * @param  {[type]} lng [description]
	 * 
	 * @return {[type]}     [description]
	 */
	latLng : function( lat, lng ) {},

	/**
	 * Get the position of an element.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @param  {[type]} element [description]
	 * 
	 * @return {[type]}         [description]
	 */
	getPosition : function( element ) {},

	/**
	 * Set the marker position.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @param {[type]} marker [description]
	 * @param {[type]} latLng [description]
	 */
	setMarkerPosition : function( marker, latLng ) {},

	/**
	 * Resize map.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @param  {[type]} map [description]
	 * 
	 * @return {[type]}     [description]
	 */
	resizeMap : function( map ) {},

	/**
	 * Set center.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @param {[type]} center [description]
	 * @param {[type]} map    [description]
	 */
	setCenter : function( center, map ) {},

	/**
	 * Render map.
	 *
	 * To be extended with geocoding prvoider.
	 * 
	 * @return {[type]} [description]
	 */
	renderMap : function() {},

	/**
	 * Set some variables.
	 */
	set_vars : function() {

		this_form.is_location_confirmed = ( jQuery.trim( jQuery( '#gmw_lf_latitude' ).val() ).length != 0 && jQuery.trim( jQuery( '#gmw_lf_longitude' ).val() ).length != 0 ) ? true : false;

		// form arguments
		this_form.vars = gmw_lf_args.args || {};

		// nonce 
		this_form.security = gmw_lf_args.nonce || '';

		// form fields
		this_form.fields = gmw_lf_args.fields || false;

		// messages
		this_form.messages = gmw_lf_args.messages || {};

		if ( false !== this_form.fields ) {

			// location fields
			this_form.location_fields = gmw_lf_args.fields.location.fields || false;

			// address fields
			this_form.address_fields = gmw_lf_args.fields.address.fields || false;

			// coordinates fields
			this_form.coords_fields = gmw_lf_args.fields.coordinates.fields || false;
			
			// action fields
			this_form.action_fields = gmw_lf_args.fields.actions.fields || false;
		}

		// map status
		this_form.map_enabled = jQuery( '#gmw-lf-map' ).length ? true : false;
	},

	/**
	 * Init function
	 * @return {[type]} [description]
	 */
	init : function() {

		this_form = GMW_Location_Form;

		this_form.set_vars();

		jQuery.extend( this_form, GMW_Location_Form_Map_Providers[gmwVars.mapsProvider] );

		// hide confirm location button
		jQuery( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );

		// if no location exist
		if ( ! this_form.is_location_confirmed ) {
			
			this_form.location_exists = false;

			jQuery( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

			// display no location message
			this_form.action_message( 'error', this_form.messages.location_not_exists );

		} else {

			this_form.location_exists = true;

			// display location ok message
			this_form.action_message( 'ok', this_form.messages.location_exists );

			// set location to confirmed
			this_form.is_location_confirmed = location_saved = true;
		}

		// init tabs
		this_form.init_tabs();
		this_form.fields_changed();
		this_form.action_buttons();

		// init address autocomplete.
	  	if ( typeof this_form.addressAutocomplete !== 'undefined' && this_form.vars.address_autocomplete && jQuery( '.gmw-lf-address-autocomplete' ).length ) {
			this_form.addressAutocomplete();
		}

		// render map.
		if ( this_form.map_enabled && jQuery( '#gmw-lf-map' ).length ) {

			// Render the map with a short delay to prevent issues if it is inside a hidden element.
			setTimeout( function(){
				this_form.renderMap();
			}, 300 );
		}

		// init locator button.
		if ( this_form.vars.geolocation_button && jQuery( '.gmw-lf-locator-button' ).length ) {
			this_form.current_location();
		} else {
			jQuery( '.gmw-lf-locator-button' ).remove();
		}

		// Resize map when the location box of the edit post page is made visible.
		if ( jQuery( 'body.wp-admin #gmw-location-meta-box .gmw-lf-field-wrapper.gmw-lf-map' ).length ) {

			jQuery( '#gmw-location-meta-box .hndle' ).on( 'click', function() {

				setTimeout( function(){  
		    		// resize map
		    		this_form.resizeMap( this_form.map );

		    		// center map on marker
		    		this_form.setCenter( this_form.getPosition( this_form.map_marker ), this_form.map );
		    	}, 200 );
			});
		}
		//if ( this_form.vars.stand_alone ) {


		//} else {

		// if submit button exists
		/*
		if ( jQuery( '#' + this_form.action_fields.submit_location.id ).length ) {

			// on submit click
			jQuery( '#' + this_form.action_fields.submit_location.id ).on( 'click', function() {
				
				if ( this_form.vars.auto_confirm ) {

					this_form.auto_confirm_submission( false );
				
				} else {

					this_form.confirm_submission( false );
				}
			} );
		} 
		*/
	
		// on form submission.
		/*jQuery( this_form.vars.form_element ).on( 'submit', function( event ) {

			if ( ! this_form.location_exists && this_form.vars.location_required ) {

				alert( this_form.messages.location_required );

				return false;
			}

			if ( ! this_form.proceed_submission ) {

				event.preventDefault();

				if ( this_form.vars.auto_confirm ) {

					//this_form.auto_confirm_submission( event );
					this_form.auto_confirm_submission( true );
				
				} else {

					this_form.confirm_submission( true );
				}
			}
		});

		jQuery( '.edit-location' ).on( 'click', function( e ) {

			e.preventDefault();

			this_form.load_location_form( jQuery( this ) );
		}); */
				
		/*
		// if submit button exists we use it only for form submission
		if ( jQuery( '#' + this_form.action_fields.submit_location.id ).length ) {

			// on form submission make sure that location is confirmed
			jQuery( '#' + this_form.action_fields.submit_location.id ).on( 'click', function( event ) {

				//this_form.confirm_submission( event );				
			});

		// otherwise, we use the form submission of the form wrapping the location form
		} else {

			// on form submission make sure that location is confirmed
			jQuery( this_form.vars.form_element ).on( 'submit', function( event ) {

				this_form.confirm_submission( event );				
			});

		}
		*/	
	},

	init_actions : function() {

		// on form submission.
		jQuery( this_form.vars.form_element ).on( 'submit', function( event ) {

			if ( ! this_form.location_exists && this_form.vars.location_required ) {

				alert( this_form.messages.location_required );

				return false;
			}

			if ( ! this_form.proceed_submission ) {

				if ( true === GMW.apply_filters( 'gmw_location_form_prevent_form_submission', true, this_form ) ) {
					event.preventDefault();
				}

				if ( this_form.vars.auto_confirm ) {

					//this_form.auto_confirm_submission( event );
					this_form.auto_confirm_submission( true );
				
				} else {

					this_form.confirm_submission( true );
				}
			}
		});

	},

	/**
	 * Tabs switcher.
	 * 
	 * @return {[type]} [description]
	 */
	init_tabs : function() {
		
		// show activate tab content and hide the rest
		firstTab = jQuery( '.gmw-lf-tabs-wrapper li' ).first().attr( 'id' );
		jQuery( '#' + firstTab + '-panel' ).show().siblings( '[id*="-tab-panel"]').hide();
		
		// dynamically remove any excluded tab containers that might be still exists 
		// on the page
		if ( typeof this_form.vars.exclude_fields_groups !== undefined && this_form.vars.exclude_fields_groups.length ) {
			jQuery.each( this_form.vars.exclude_fields_groups, function( index, value ) {
				jQuery( '#' + value + '-tab-panel' ).remove();
			});	
		}

		//tabs on click
	    jQuery( '.gmw-lf-tabs-wrapper li a' ).on( 'click', function( e ) {

	    	e.preventDefault();

	    	// get the name of the tab was clicked
	    	var activeTab = jQuery( this ).data( 'name' );

	    	// show the clicked tab and hide the rest
	    	jQuery( '#' + activeTab + '-tab-panel' ).show().siblings( '[id*="-tab-panel"]' ).hide();

	    	// activate / deactivates tabs
	    	jQuery( this ).parent( 'li' ).addClass( 'active' ).siblings().removeClass( 'active' );
	    
	    	// If the tab contains the map then resize it to make sure the map is showing properly
	    	if ( this_form.map_enabled && jQuery( '#' + activeTab + '-tab-panel' ).find( jQuery( '#' + this_form.location_fields.map.id ) ).length ) {

	    		setTimeout( function(){  
		    		// resize map
		    		this_form.resizeMap( this_form.map );

		    		// center map on marker
		    		this_form.setCenter( this_form.getPosition( this_form.map_marker ), this_form.map );
		    	}, 100 );
	    	}
	    });
	},

	/**
	 * display action message
	 * 
	 * @param  {string}  type    type of message: changed, ok or error
	 * @param  {string}  message the message to display
	 * @param  {boolean} toggle  toggle the message true | false
	 * @return {void}         
	 */
	action_message : function( type, message, toggle ) {

		if ( type == 'updating' ) {

			// add new message and type
	 		jQuery( '#' + this_form.action_fields.message.id ).removeClass( 'changed ok error confirming' ).addClass( 'confirming' ).find( 'span' ).html( message );

	 		// show message if hidden
	 		if ( toggle ) {
	 			jQuery( '#' + this_form.action_fields.message.id ).fadeIn( 'fast' );
	 		}

		} else {

			// Hide loader first
			jQuery( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast', function() {

				// add new message and type
		 		jQuery( '#' + this_form.action_fields.message.id ).removeClass( 'changed ok error confirming' ).addClass( type ).find( 'span' ).html( message );

		 		// show message if hidden
		 		if ( toggle ) {
		 			jQuery( '#' + this_form.action_fields.message.id ).fadeIn( 'fast' );
		 		}
		 	});
		}
 	},

	/**
	 * Action button functions.
	 * 
	 * @return {[type]} [description]
	 */
	action_buttons : function() {

		// prevent form submission when pressing enter in any of the location input fields
		jQuery( this_form.wrapper_element + ' input[type="text"]' ).keypress( function(e){
		    
		    if ( e.which == 13 ) {
		    	return false;
		    }  
		});

		// update address if user click enter in the address field and autocomplete
		// is not showing suggested results. That is a workaround to allow user
		// to geocode custom address which is not from the suggested results
		if ( jQuery( '#' + this_form.location_fields.address.id ).length ) {

			jQuery( '#' + this_form.location_fields.address.id ).on( 'keydown', function(e) {

				if ( e.which != 13 ) {
					return;
				}

		    	if ( ! jQuery( this ).hasClass( 'gmw-lf-address-autocomplete' ) || ( jQuery( this ).hasClass( 'gmw-lf-address-autocomplete' ) && jQuery( '.pac-container' ).css( 'display' ) == 'none' ) ) {
		    	
		    		this_form.fields_changed_status = 1;

		    		this_form.confirm_location();
		    	}
		    });
		}

		// submit button click
		if ( jQuery( '#' + this_form.action_fields.submit_location.id ).length ) {

			// on submit click
			jQuery( '#' + this_form.action_fields.submit_location.id ).on( 'click', function() {
				
				if ( this_form.vars.auto_confirm ) {

					this_form.auto_confirm_submission( false );
				
				} else {

					this_form.confirm_submission( false );
				}
			} );
		} 

		// confirm location click event
		if ( jQuery( '#' + this_form.action_fields.confirm_location.id ).length ) {
			
			jQuery( '#' + this_form.action_fields.confirm_location.id ).on( 'click', function() {
			
				this_form.confirm_location();
			});
		}

		// delete location click event
	 	if ( jQuery( '#' + this_form.action_fields.delete_location.id ).length ) {
			
			jQuery( '#' + this_form.action_fields.delete_location.id ).on( 'click', function() {

				//show confirm message to be sure the user wants to delete the location
				if ( confirm( this_form.messages.delete_confirmation ) ) {
				    
				    this_form.delete_location();
				
				} else {

				    return false;
				}
			});
		}
	},

	/**
	 * Set fields_changed value when location changes. 
	 *
	 * When location value changes and we want to confirm it, we need to know what part of the location was changed.
	 *
	 * it could be full address field, any of the multiple address fields or coordinates.
	 *
	 * This way the function knows what part was changed and to geocode it.
	 * 
	 * @return void
	 */
	fields_changed : function() {

		jQuery( '.gmw-lf-field.address-field, .gmw-lf-field.group_address, .gmw-lf-field.group_coordinates' ).on( 'change input', function( e ) {

			if ( jQuery( this ).hasClass( 'address-field' ) ) {

				this_form.fields_changed_status = 1;

			} else if ( jQuery( this).hasClass( 'group_address' ) ) {

				this_form.fields_changed_status = 2;

			} else if ( jQuery( this ).hasClass( 'group_coordinates' ) ) {

				this_form.fields_changed_status = 3;
			}

			// silently enter the address that the user is typing also in the hidden address fields
			jQuery( '#' + jQuery( this ).attr( 'id' ).replace( /-/g, '_' ) ).val( jQuery( this ).val() );

			// show location changed message and button
			if ( ! jQuery( 'p.gmw-lf-form-action' ).hasClass( 'changed' ) ) {
				this_form.location_changed();
			}
		});
	},

	/**
	 * When location changed.
	 *
	 * Display changed message, show update button and set location confirmed to false.
	 * 
	 * @return {[type]} [description]
	 */
	location_changed : function() {

		if ( ! this_form.vars.auto_confirm ) {

			//setTimeout( function() {

			//	if ( ! this_form.is_location_confirmed ) { 
					// show update button
					jQuery( '#' + this_form.action_fields.confirm_location.id ).fadeIn( 'fast' );
			//	}
			//}, 500 );
		}

		this_form.location_exists = true;

		// display changed message
		this_form.action_message( 'changed', this_form.messages.location_changed );
		
		// location status
		this_form.is_location_confirmed = false;
	},

	/**
	 * Update map when location changes
	 * 
	 * @param  string lat new latitude
	 * @param  string lng new longitude
	 *
	 * @return void  
	 */
	update_map : function( lat, lng ) {

		var latLng = this_form.latLng( lat, lng );

		this_form.setMarkerPosition( this_form.map_marker, latLng );
		this_form.map.panTo( latLng );

		GMW.do_action( 'gmw_lf_update_map', this_form, latLng );
	},

	/**
	 * get the user's current position
	 *
	 * When user clicks the current location icon the browser will
	 *
	 * try to retrieve his current position.
	 * 
	 * @return void
	 */
    current_location : function() {
    	
    	// locator button clicked 
	    jQuery( '#gmw-lf-locator-button' ).on( 'click', function(){
	    	
	    	// spin loading icon
    		jQuery( '#gmw-lf-locator-button' ).addClass( 'animate-spin' );

    		// verify browser supports geolocation
    		if ( navigator.geolocation ) {

    			options = GMW.apply_filters( 'gmw_lf_navigator_options', { timeout : 15000 }, this_form );

	    		navigator.geolocation.getCurrentPosition( this_form.navigator_position, this_form.navigator_error, options );

	    	// otherwise, display error message
			} else {

	   	 		alert( 'Geolocation is not supported by this browser.' );

	   	 		jQuery( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );
	   		}
	  	});
	},
    
    /**
     * Show current location results.
     * 
     * @param  {[type]} position [description]
     * @return {[type]}          [description]
     */
	navigator_position : function( position ) {	
				
  		jQuery( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );

  		this_form.location_changed();
  		
    	// geocode coordinates
  		this_form.reverse_geocode( position.coords.latitude, position.coords.longitude, false );  		
	},

	/**
	 * Display geolocator error message
	 * 
	 * @param  {[type]} error [description]
	 * @return {[type]}       [description]
	 */
	navigator_error : function( error ) {
  		
		switch( error.code ) {

   	 		case error.PERMISSION_DENIED:
      			alert( "User denied the request for Geolocation." );
     		break;

   		 	case error.POSITION_UNAVAILABLE:
   		   		alert( "Location information is unavailable." );
    	  	break;
    		
    		case error.TIMEOUT:
      			alert( "The request to get user location timed out." );
     		break;
    		
    		case error.UNKNOWN_ERROR:
      			alert( "An unknown error occurred." );
      		break;
		}
		
		this_form.is_location_confirmed = false;

		jQuery( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );
	},

	/**
	 * Geocode an address
	 *
	 * Get the coordinates of an address
	 * 
	 * @param  {string} address the full address to geocode
	 * 
	 * @return void        
	 */
	geocode : function( address ) {
		
		jQuery( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );

		if ( jQuery.trim( address ).length == 0 ) {

			this_form.location_missing_message();
		
		} else {

			var geocoder = new GMW_Geocoder( gmwVars.geocodingProvider );
			
			geocoder.geocode( { 'q' : address } ,function( response, status ) {
				
				if ( status == 'OK' ) {
	    			
	    			if ( this_form.fields_changed_status == 1  ) {

	    				jQuery( '.group_coordinates, .group_address' ).val( '' );
	    			
	    			} else if ( this_form.fields_changed_status == 2 ) {
	    			
	    				jQuery( '.group_coordinates, .gmw-lf-submission-field.group_address, #' + this_form.location_fields.address.id ).val( '' );		
	    			}

	    			// add address to the result
	    			response.result.address = address;

	    			// add values to coords
	    			jQuery( '.group_coordinates.latitude' ).val( response.result.lat );
	    			jQuery( '.group_coordinates.longitude' ).val( response.result.lng );

	    			// populate value in full address fields
	       			jQuery( '#' + this_form.location_fields.address.id +', #gmw_lf_address' ).val( address );
	       			jQuery( '#gmw_lf_formatted_address' ).val( response.result.formatted_address );

	       			// get the rest of the address fields
	       			this_form.get_address_fields( response.result, response, true );

				} else {

					this_form.geocoder_failed( response.status, response );
				}
			} );
		}
	},

	/**
	 * Reverse geocoder. Conver coords to address
	 * 		
	 * @param  {float} lat latitude
	 * @param  {float} lng longitude
	 * @param  {[type]} save_data [description]
	 * @return {void}    
	 */
	reverse_geocode : function( lat, lng, save_data ) {
		
		jQuery( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );

		// populate coords fields with the original coords values
		jQuery( '.group_coordinates.latitude' ).val( lat );
		jQuery( '.group_coordinates.longitude' ).val( lng );
		
		data = { 
            'q' 	 : [ lat, lng ], 
            'region' : this_form.region
        };
			
		var geocoder = new GMW_Geocoder( gmwVars.geocodingProvider );
			
		geocoder.reverseGeocode( data ,function( response, status ) {
			
			// if geocode successful
			if ( status == 'OK' ) {
					
				// add the formatted address as the address field
				response.result.address = response.result.formatted_address;

				//clear address fields before updating with new info
				jQuery( '.group_address,' + this_form.location_fields.address.id ).val('');

				//update address and formatted address fields
				jQuery( '#' + this_form.location_fields.address.id + ', #gmw_lf_address, #gmw_lf_formatted_address' ).val( response.result.formatted_address );

				//get address fields
				this_form.get_address_fields( response.result, response, save_data );

			//otherwise show error message
      		} else {
      			this_form.geocoder_failed( status );
      		}
		} );

		/*
		// init google geocoder
		geocoder = new google.maps.Geocoder();
		
		data = { 
            'q' 	 : [ lat, lng ], 
            'region' : this_form.region
        };

		// reverse geocode coordinates
		geocoder.geocode( data, function( results, status ) {		
			
			// if geocode successful
			if ( status == google.maps.GeocoderStatus.OK ) {
				
				//clear address fields before updating with new info
				jQuery( '.group_address,' + this_form.location_fields.address.id ).val('');

				//update address and formatted address fields
				jQuery( '#' + this_form.location_fields.address.id + ', #gmw_lf_address, #gmw_lf_formatted_address' ).val( results[0].formatted_address );

				//get address fields
				this_form.get_address_fields( results[0], save_data );

			//otherwise show error message
      		} else {
      			this_form.geocoder_failed( status );
      		}
   		});*/
	}, 
	
	/**
	 * Geocoder failed functions.
	 * 
	 * @param  {string} status status message
	 * 
	 * @return {void}  
	 */
	geocoder_failed : function( status, result ) {

		this_form.is_location_confirmed = false;

		alert( this_form.messages.geocoder_failed );

		console.log( result );

		//hide loader
	  	jQuery( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast' );	
	},

	/**
	 * Nn location message.
	 *
	 * @param  {string} status status message
	 * 
	 * @return {void}  
	 */
	location_missing_message : function() {

		this_form.is_location_confirmed = false;

		//alert error message
		alert( this_form.messages.location_missing );

		//hide loader
	  	jQuery( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast' );	
	},

	/**
	 * Retrieve the address fields from address component
	 * 
	 * @param  {[type]} location  [description]
	 * @param  {[type]} save_data [description]
	 * @return {[type]}           [description]
	 */
	get_address_fields : function( result, response, save_data ) {

    	// modify the address component before populating the location fields.
    	// can also performe custom tasks perform 
    	result = GMW.apply_filters( 'gmw_lf_address_component', result, this_form, response );

    	// if address is missing at this point, use the formatted address value.
    	if ( result.address == '' ) {
    		result.address = result.formatted_address;
    	}

		for ( var field in result ) {

			if ( result.result ) {
				continue;
			}

			if ( jQuery( '.group_address.' + field ).length ) {
				jQuery( '.group_address.' + field ).val( result[field] );
			}
       	}

		result.latitude  		 = jQuery( '#gmw_lf_latitude' ).val();
		result.longitude 		 = jQuery( '#gmw_lf_longitude' ).val();
		result.formatted_address = jQuery( '#gmw_lf_formatted_address' ).val();

		jQuery( '.group_address.place_id' ).val( result.place_id );

		// do something custom with the location data
		GMW.do_action( 'gmw_lf_geocoded_location_data', result, response, this_form );

		// update map based of new coords
		if ( this_form.map_enabled && jQuery( '#gmw-lf-map' ).length && ! this_form.map_updated ) {
			this_form.update_map( result.lat, result.lng );
		} else if ( this_form.map_updated ) {
			this_form.map_updated = false;
		}

		//mark location as confirmed
		this_form.location_confirmed();
	},

	/**
	 * Confirm location on click
	 * @return {[type]} [description]
	 */
	confirm_location : function() {

		// Try to confirm location if not already confirmed
		if ( ! this_form.is_location_confirmed ) {

			// show loader
	  		jQuery( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );	

	  		latVal = lngVal = '';

	  		console.log( this_form.coords_fields )
	  		// make sure coords exist in form
	  		if ( typeof this_form.coords_fields != false && typeof this_form.coords_fields.latitude !== 'undefined' && jQuery( '#' + this_form.coords_fields.latitude.id ).length ) {
		  		latVal = jQuery( '#' + this_form.coords_fields.latitude.id ).val();
		  		lngVal = jQuery( '#' + this_form.coords_fields.longitude.id ).val();
		  	}

	  		// if address changed or coordinates missing
			if ( this_form.fields_changed_status != 3 || jQuery.trim( latVal ).length == 0 || jQuery.trim( lngVal ).length == 0 ) {

				// check for full address first
				if ( this_form.fields_changed_status == 1 && jQuery.trim( jQuery( '#' + this_form.location_fields.address.id ).val() ).length > 0 ) {
	
					address = jQuery( '#' + this_form.location_fields.address.id ).val();

				// otherwise, check for multiple address fields
				} else {
			
					address = '';
					
					jQuery.each( this_form.address_fields, function( index, value ) {
						
						// if address field and value exist
						if ( jQuery( '#' + value.id ).length && jQuery.trim( jQuery( '#' + value.id ).val() ).length > 0 ) {
							address += jQuery( '#' + value.id ).val() + ' ';
						}
					});			    	
				}

				// if address blank but have coordinates do reverse geocoding
				if ( jQuery.trim( address ).length == 0 && ( jQuery.trim( latVal ).length != 0 && jQuery.trim( lngVal ).length != 0 ) ) {
					
					this_form.reverse_geocode( latVal, lngVal, true );  
				
				} else {

					//geocode address
					this_form.geocode( address );
				}

			//if coords exist reverse geocode it
			} else {

	   	 		//reverse geocode coords
	    		this_form.reverse_geocode( latVal, lngVal, true );  
			}

		} else {

			//mark location as confirmed
			this_form.location_confirmed();
		}
	},

	/**
	 * Verfy location on form submission
	 * 
	 * @return {[type]} [description]
	 */
	auto_confirm_submission : function( form_submission ) {
		
		var setTime;
		
		// if location does not exist 
		if ( ! this_form.location_exists ) {

			// if required abort with a message
			if ( this_form.vars.location_required ) {
				
				this_form.location_missing_message();

				return false;

			// otherwise, proceed without location
			} else {

				this_form.proceed_submission = true;

				jQuery( this_form.vars.form_element ).submit();

				return false;
			}

		//if location exists but not confirmed
		} else if ( ! this_form.is_location_confirmed ) {

			setTime = 3000;

			this_form.auto_confirming = true;

			jQuery( '#' + this_form.action_fields.message.id ).fadeOut( 'fast' );

			this_form.confirm_location();
		
		// otherwise, if confirmed
		} else {

			setTime = 0;
		}

		setTimeout( function() {

			//if location confirmed
			if ( this_form.is_location_confirmed ) {
		
				if ( form_submission || ( this_form.vars.stand_alone && ! this_form.vars.ajax_enabled ) ) {
			
					this_form.proceed_submission = true;

					this_form.auto_confirming = false;

					jQuery( this_form.vars.form_element ).submit();
				
				} else {

					this_form.auto_confirming = false;

					this_form.save_location();
				}
				
			} else {

				this_form.auto_confirming = false;

				return false;
			}

		}, setTime );

		return;
	},

	/**
	 * Verify location on form submission
	 * 
	 * @return {[type]} [description]
	 */
	confirm_submission : function( form_submission ) {
	
		var confirmed = false;

		//if location confirmed
		if ( this_form.is_location_confirmed ) {

			confirmed = true;
		
		} else if ( ! this_form.location_exists ) {

			if ( this_form.vars.location_required ) {
				
				alert( 'no location' );

				confirmed = false;

			} else {

				confirmed = true;
			}

		// If location confirmation required abort the form submission and alert the user
		} else if ( this_form.vars.confirm_required ) {
			
			alert( this_form.messages.confirm_required );
			
			confirmed = false;

		//Otherwise present the user with an option to either proceed with the form submission 
		//or abort it in order to confirm the location
		} else {

			lat = jQuery( '#gmw_lf_latitude' ).val();
			lng = jQuery( '#gmw_lf_longitude' ).val();

			// verify coords
			if ( jQuery.trim( lat ).length == 0 || jQuery.trim( lng ).length == 0 || ! jQuery.isNumeric( lat ) || ! jQuery.isNumeric( lng ) ) {
				
				alert( this_form.messages.coords_invalid );

				confirmed = false;

			// confirmation message
			} else if ( confirm( this_form.messages.confirm_message ) ) {

			    confirmed = true;

			} else {

			    confirmed = false;
			}
		}

		if ( ! confirmed ) {
			return false;
		}

		if ( form_submission || ( this_form.vars.stand_alone && ! this_form.vars.ajax_enabled ) ) {
			
			this_form.proceed_submission = true;

			jQuery( this_form.vars.form_element ).submit();
		
		} else {

			this_form.save_location();
		}
		/*
		if ( ! this_form.vars.stand_alone || ( this_form.vars.stand_alone && this_form.vars.ajax_enabled ) ) {
			
			this_form.save_location();
		
		} else {
			
			
		}
		*/
		return;
	},

	/**
	 * When location changed.
	 *
	 * Display changed message, show update button and set location confirmed to false.
	 * 
	 * @return {[type]} [description]
	 */
	location_confirmed : function() {

		if ( ! this_form.auto_confirming ) {
		  	
		  	//hide confirm button
			jQuery( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );
			
			jQuery( '#' + this_form.action_fields.delete_location.id ).fadeIn( 'fast' );

			//if ( this_form.vars.auto_confirm ) {

			//	this_form.action_message( 'updating', this_form.messages.confirming_location, false );
			
		//	} else {

				// hide loader and show address confirm message
			this_form.action_message( 'ok', this_form.messages.location_exists, true );
		//	}
		}

		// clear change event when location confirmed.
		jQuery( '.gmw-lf-field.address-field, .gmw-lf-field.group_address, .gmw-lf-field.group_coordinates' ).off( 'change' );
			
		//location status
		this_form.is_location_confirmed = true;
	},

	/**
	 * save location using Ajax
	 * 
	 * @return {[type]} [description]
	 */
    save_location : function() {
  
  	 	// hide status messages
  	 	jQuery( '#' + this_form.action_fields.message.id ).fadeOut( 'fast', function() {
  	 		//show loader
  	 		jQuery( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );
  	 	});

  	 	var formValues = jQuery( this_form.vars.form_element ).serialize();

  	 	// save location via ajax
		this_form.ajaxResponse = jQuery.ajax({
			type 	 : "POST",
			dataType : 'json',	
			url      : gmwVars.ajaxUrl,
			data 	 : { 
				action       : this_form.vars.update_callback,
				'formValues' : formValues, 
				'formArgs'   : this_form.vars,
				'security'	 : this_form.security
			},

			// saving success
			success : function( response ){				

				if ( response ) {
					
					// pass the location ID to hidden field
					jQuery( '#gmw_lf_location_id' ).val( response );

					GMW.do_action( 'gmw_lf_location_saved', response, formValues, this_form.vars );

					//hide loader and confirm button
					jQuery( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast', function() {

						//action message
						this_form.action_message( 'ok', this_form.messages.location_saved, true );

						//wait abit and hide message
						setTimeout( function() {			
								
							///hide action message
							jQuery( '#' + this_form.action_fields.message.id + ' span' ).html( this_form.messages.location_exists );
						},3500);

					});

				//if failed
				} else {
						
					// Hide loader and show action message
					this_form.action_message( 'error', this_form.messages.location_not_saved, true );		
				}		
			}

		//if failed
		}).fail( function ( jqXHR, textStatus, error ) {

			if ( window.console && window.console.log ) {

				console.log( textStatus + ': ' + error );

				if ( jqXHR.responseText ) {
					console.log( jqXHR.responseText );
				}
			}
			
			// hide loader and show action message
			this_form.action_message( 'error', this_form.messages.location_not_saved, true );
				
		}).done( function ( response ) {
			console.log( 'done saving location' );
			console.log( response );
		});
			
		return false;
 	},

 	/**
 	 * Clear location fields and delete location
 	 * @return {[type]} [description]
 	 */
    delete_location : function() {
   	
    	// hide status message
    	jQuery( '#' + this_form.action_fields.message.id ).fadeOut( 'fast', function() {

    		// show loader
	  		jQuery( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );	

	  		// hide confirm button
	  		jQuery( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );	
    	});

	  	// delete location from database via ajax only if location ID exists
	  	if ( jQuery( '#gmw_lf_location_id' ).val() != '' && jQuery( '#gmw_lf_location_id' ).val() != '0' ) {

			this_form.ajax_delete();
				
		} else {

			//clear all location fields
  			jQuery( '.group_address, .group_coordinates, .gmw-lf-field.address-field, .gmw-lf-extra-field' ).val( '' );

  			// set location confirmed to false
  			this_form.is_location_confirmed = false;

			setTimeout(function() {
				
				this_form.location_exists = false;

   				jQuery( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

				// hide loader and display error message
				this_form.action_message( 'ok', this_form.messages.location_deleted, true );

				setTimeout(function() {

					// change status messgae action message
					this_form.action_message( 'error', this_form.messages.location_not_exists, true );

				}, 3500 );

			}, 1500 );	
		}
	},

	/**
	 * Delete location from database
	 * 
	 * @return {[type]} [description]
	 */
	ajax_delete : function() {

		var formValues = jQuery( this_form.vars.form_element ).serialize();

		this_form.ajaxResponse = jQuery.ajax({
			type 	 : "post",
			dataType : 'json',
			url      : gmwVars.ajaxUrl,
			data 	 : {
				action       : this_form.vars.delete_callback, 
			 	'formValues' : formValues, 
				'formArgs'   : this_form.vars,
				'security'	 : this_form.security
			},		
			
			// if location deleted
			success  : function( response ) {
		
				if ( response ) {

					GMW.do_action( 'gmw_lf_location_deleted', response, formValues, this_form.vars );

					//clear all location fields
	  				jQuery( '.group_address, .group_coordinates, .gmw-lf-field.address-field, .gmw-lf-extra-field, #gmw_lf_location_id' ).val( '' );

	  				//set location confirmed to false
	  				this_form.is_location_confirmed = false;

  					this_form.location_exists = false;

						jQuery( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

					//hide loader and display message
					this_form.action_message( 'ok', this_form.messages.location_deleted, true );

					//wait abit
					setTimeout(function() {

						//hide loader and display message
						this_form.action_message( 'error', this_form.messages.location_not_exists, true );

					}, 3500 );

				} else {
					
					//hide loader and show action message
					this_form.action_message( 'error', this_form.messages.location_not_deleted, true );
				}
			}

		//if failed delted
		} ).fail( function ( jqXHR, textStatus, error ) {

			if ( window.console && window.console.log ) {

				console.log( textStatus + ': ' + error );

				if ( jqXHR.responseText ) {
					console.log(jqXHR.responseText);
				}
			}
				
			// hide loader and show action message
			this_form.action_message( 'error', this_form.messages.location_not_deleted, true );

		}).done( function ( response ) {
			console.log( 'done deleting location' );
			console.log( response );
		});

		return false;
    }			
};

jQuery( document ).ready( function() {

	if ( jQuery( '.gmw-location-form-wrapper' ).length ) {
		GMW_Location_Form.init();
		GMW_Location_Form.init_actions();
	}
});
