jQuery( document ).ready( function( $ ) {
	
	var gmwLocationForm = false;

	/**
	 * GEO my WP Location form
	 * @type {Object}
	 */
	var GMW_Location_Form = {

		// form arguments
		vars : gmw_lf_args.args,

		is_location_confirmed : ( $.trim( $( '#gmw_lf_latitude' ).val() ).length != 0 && $.trim( $( '#gmw_lf_longitude' ).val() ).length != 0 ) ? true : false,

		// nonce 
		security : gmw_lf_args.nonce,

		// form fields
		fields : gmw_lf_args.fields,

		// messages
		messages : gmw_lf_args.messages,

		// element wrapping the form
		wrapper_element : '.gmw-location-form-wrapper',

		// location fields
		location_fields : gmw_lf_args.fields.location['fields'] || false,

		// address fields
		address_fields : gmw_lf_args.fields.address['fields'] || false,

		// coordinates fields
		coords_fields : gmw_lf_args.fields.coordinates['fields'] || false,
		
		// action fields
		action_fields : gmw_lf_args.fields.actions['fields'] || false,

		// map status
		map_enabled : $( '#gmw-lf-map' ).length ? true : false,

		// fields value changed
		fields_changed : 1,

		// additional map options
		map_options : {},

		// options for Google Address autocomplete
		autocomplete_options : {},

		// google API regions
		region : 'US',

		proceed_submission : false,

		location_exists : false,

		auto_confirming : false,

		/**
		 * Init function
		 * @return {[type]} [description]
		 */
		init : function() {

			this_form = GMW_Location_Form;
			
			// hide confirm location button
			$( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );

			// if no location exist
			if ( ! this_form.is_location_confirmed ) {
				
				this_form.location_exists = false;

				$( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

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

			// init address autocomplete if needed
		  	if ( this_form.vars.address_autocomplete && $( '.gmw-lf-address-autocomplete' ).length ) {
				this_form.address_autocomplete();
			}

			// render map if needed
			if ( this_form.map_enabled && $( '#gmw-lf-map' ).length ) {
				this_form.render_map();
			}

			// init locator button if needed
			if ( this_form.vars.geolocation_button && $( '.gmw-lf-locator-button' ).length ) {
				this_form.current_location();
			} else {
				$( '.gmw-lf-locator-button' ).remove();
			}

			//if ( this_form.vars.stand_alone ) {


			//} else {

			// if submit button exists
			/*
			if ( $( '#' + this_form.action_fields.submit_location.id ).length ) {

				// on submit click
				$( '#' + this_form.action_fields.submit_location.id ).on( 'click', function() {
					
					if ( this_form.vars.auto_confirm ) {

						this_form.auto_confirm_submission( false );
					
					} else {

						this_form.confirm_submission( false );
					}
				} );
			} 
			*/
		
			// on form submission
			$( this_form.vars.form_element ).on( 'submit', function( event ) {

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
					
			/*
			// if submit button exists we use it only for form submission
			if ( $( '#' + this_form.action_fields.submit_location.id ).length ) {

				// on form submission make sure that location is confirmed
				$( '#' + this_form.action_fields.submit_location.id ).on( 'click', function( event ) {

					//this_form.confirm_submission( event );				
				});

			// otherwise, we use the form submission of the form wrapping the location form
			} else {

				// on form submission make sure that location is confirmed
				$( this_form.vars.form_element ).on( 'submit', function( event ) {

					this_form.confirm_submission( event );				
				});

			}
			*/	
		},

		/**
		 * Tabs switcher
		 * 
		 * @return {[type]} [description]
		 */
		init_tabs : function() {
			
			// show activate tab content and hide the rest
			firstTab = $( '.gmw-lf-tabs-wrapper li' ).first().attr( 'id' );
			$( '#' + firstTab + '-panel' ).show().siblings().hide();
			
			// dynamically remove any excluded tab containers that might be still exists 
			// on the page
			if ( typeof this_form.vars.exclude_fields_groups !== undefined && this_form.vars.exclude_fields_groups.length ) {
				$.each( this_form.vars.exclude_fields_groups, function( index, value ) {
					$( '#' + value + '-tab-panel' ).remove();
				});	
			}

			//tabs on click
		    $( '.gmw-lf-tabs-wrapper li a' ).on( 'click', function( e ) {

		    	e.preventDefault();

		    	// get the name of the tab was clicked
		    	var activeTab = jQuery( this ).data( 'name' );

		    	// show the clicked tab and hide the rest
		    	jQuery( '#' + activeTab + '-tab-panel' ).show().siblings().hide();

		    	// activate / deactivates tabs
		    	jQuery( this ).parent( 'li' ).addClass( 'active' ).siblings().removeClass( 'active' );

		    	// If the tab contains the map then resize it to make sure the map is showing properly
		    	if ( this_form.map_enabled && $( activeTab + '-tab-panel' ).find( $( '#' + this_form.location_fields.map.id ) ).length == 1 ) {

		    		setTimeout( function(){  
			    		// resize map
			    		google.maps.event.trigger( this_form.map, 'resize' );
			    		
			    		// center map on marker
			    		this_form.map.setCenter( this_form.map_marker.getPosition() );

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
		 		$( '#' + this_form.action_fields.message.id ).removeClass( 'changed ok error confirming' ).addClass( 'confirming' ).find( 'span' ).html( message );

		 		// show message if hidden
		 		if ( toggle ) {
		 			$( '#' + this_form.action_fields.message.id ).fadeIn( 'fast' );
		 		}

			} else {

				// Hide loader first
				$( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast', function() {

					// add new message and type
			 		$( '#' + this_form.action_fields.message.id ).removeClass( 'changed ok error confirming' ).addClass( type ).find( 'span' ).html( message );

			 		// show message if hidden
			 		if ( toggle ) {
			 			$( '#' + this_form.action_fields.message.id ).fadeIn( 'fast' );
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
			$( this_form.wrapper_element + ' input[type="text"]' ).keypress( function(e){
			    
			    if ( e.which == 13 ) {
			    	return false;
			    }  
			});

			// update address if user click enter in the address field and autocomplete
			// is not showing suggested results. That is a workaround to allow user
			// to geocode custom address which is not from the suggested results
			if ( $( '#' + this_form.location_fields.address.id ).length ) {

				$( '#' + this_form.location_fields.address.id ).on( 'keydown', function(e) {

					if ( e.which != 13 ) {
						return;
					}

			    	if ( ! $( this ).hasClass( 'gmw-lf-address-autocomplete' ) || ( $( this ).hasClass( 'gmw-lf-address-autocomplete' ) && $( '.pac-container' ).css( 'display' ) == 'none' ) ) {
			    	
			    		this_form.fields_changed = 1;

			    		this_form.confirm_location();
			    	}
			    });
			}

			// submit button click
			if ( $( '#' + this_form.action_fields.submit_location.id ).length ) {

				// on submit click
				$( '#' + this_form.action_fields.submit_location.id ).on( 'click', function() {
					
					if ( this_form.vars.auto_confirm ) {

						this_form.auto_confirm_submission( false );
					
					} else {

						this_form.confirm_submission( false );
					}
				} );
			} 

			// confirm location click event
			if ( $( '#' + this_form.action_fields.confirm_location.id ).length ) {
				
				$( '#' + this_form.action_fields.confirm_location.id ).on( 'click', function() {
				
					this_form.confirm_location();
				});
			}

			// delete location click event
		 	if ( $( '#' + this_form.action_fields.delete_location.id ).length ) {
				
				$( '#' + this_form.action_fields.delete_location.id ).on( 'click', function() {

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

			$( '.gmw-lf-field.address-field, .gmw-lf-field.group_address, .gmw-lf-field.group_coordinates' ).on( 'change input', function( e ) {
			
				if ( $( this ).hasClass( 'address-field' ) ) {

					this_form.fields_changed = 1;

				} else if ( $( this).hasClass( 'group_address' ) ) {

					this_form.fields_changed = 2;

				} else if ( $( this ).hasClass( 'group_coordinates' ) ) {

					this_form.fields_changed = 3;
				}

				// silently enter the address that the user is typing also in the hidden address fields
				$( '#' + $( this ).attr( 'id' ).replace( /-/g, '_' ) ).val( $( this ).val() );

				// show location changed message and button
				if ( ! $( 'p.gmw-lf-form-action' ).hasClass( 'changed' ) ) {
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
						$( '#' + this_form.action_fields.confirm_location.id ).fadeIn( 'fast' );
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
		 * Init Google Map
		 * @return {[type]} [description]
		 */
		render_map : function() {

			// set initial coords based on saved location or user's current location 
			if ( this_form.is_location_confirmed ) {

				//lat = $( '#gmw_lf_' + this_form.coords_fields.latitude.name ).val();
				//lng = $( '#gmw_lf_' + this_form.coords_fields.longitude.name ).val();

				lat = $( '#gmw_lf_latitude' ).val();
				lng = $( '#gmw_lf_longitude' ).val();

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
			}

			// modify map options
			map_options = GMW.apply_filters( 'gmw_lf_render_map_options', map_options, this_form );

			// initiate map
			this_form.map = new google.maps.Map( document.getElementById( this_form.location_fields.map.id ), map_options );	
			
			marker_options = {
			    position  : latLng,
			    map 	  : this_form.map,
				draggable : true
			}

			marker_options = GMW.apply_filters( 'gmw_lf_render_map_marker_options', marker_options, this_form );

			// set marker
			this_form.map_marker = new google.maps.Marker( marker_options );
			
			GMW.do_action( 'gmw_lf_render_map', this_form );

			//when dragging the map marker
			google.maps.event.addListener( this_form.map_marker, 'dragend', function( e ) {

				$( '.group_coordinates, .group_address,' + '#' + this_form.location_fields.address.id ).val( '' );

				this_form.location_changed();

				//reverse geocode to get the address fields based on coords
				this_form.reverse_geocode( e.latLng.lat(), e.latLng.lng(), false );  
			});
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

			latLng = new google.maps.LatLng( lat, lng );

			this_form.map_marker.setPosition( latLng );
			this_form.map.panTo( latLng );

			GMW.do_action( 'gmw_lf_update_map', this_form, latLng );
		},

		/**
		 * Google PLaces Address Autocomplete
		 * 
		 * @return void
		 */
		address_autocomplete : function() {
			
			//loop and trigger address autocomplete for all field with the class 'gmw-lf-address-autocomplete'
			$( '.gmw-lf-address-autocomplete' ).each( function() {

				//get the element ID
				autocompleteId = $( this ).attr( 'id' );

				//autocomplete input field
				input = document.getElementById( autocompleteId );

				//modify autocomplete options
				options = GMW.apply_filters( 'gmw_lf_address_autocomplete_options', {}, autocompleteId, this_form );
			    
			    //init autocomplete
			    var autocomplete = new google.maps.places.Autocomplete( input, options );
			    
			    google.maps.event.addListener( autocomplete, 'place_changed', function() {
			    	
			    	place = autocomplete.getPlace();

					if ( ! place.geometry ) {
						return;
					}
										
		            GMW.do_action( 'gmw_lf_autocomplete_place_changed', place, this_form );

					// clear fields
					$( '.group_coordinates, .group_address' ).val( '' );

					//update coords
					$( '.group_coordinates.latitude' ).val( place.geometry.location.lat() );
					$( '.group_coordinates.longitude' ).val( place.geometry.location.lng() );

					//update address and formatted address fields
					$( '#gmw_lf_address, #gmw_lf_formatted_address' ).val( $( '#' + this_form.location_fields.address.id ).val() );

					//get the rest of the address fields
					this_form.get_address_fields( place, false );		
			    });
			});
		},

		/**
		 * get the user's current position
		 *
		 * When user clicks the current location icon the browser will
		 *
		 * try to retrive his current position.
		 * 
		 * @return void
		 */
	    current_location : function() {
	    	
	    	// locator button clicked 
		    $( '#gmw-lf-locator-button' ).on( 'click', function(){
		    	
		    	// spin loading icon
	    		$( '#gmw-lf-locator-button' ).addClass( 'animate-spin' );

	    		// verify browser supports geolocation
	    		if ( navigator.geolocation ) {

	    			options = GMW.apply_filters( 'gmw_lf_navigator_options', { timeout : 15000 }, this_form );

		    		navigator.geolocation.getCurrentPosition( this_form.show_position, this_form.show_error, options );

		    	// otherwise, display error message
				} else {

		   	 		alert( 'Geolocation is not supported by this browser.' );

		   	 		$( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );
		   		};	
		  	});
		},
	    
	    /**
	     * Show current location results
	     * 
	     * @param  {[type]} position [description]
	     * @return {[type]}          [description]
	     */
		show_position : function( position ) {	
		
			//alert( this_form.messages.location_found );
				
	  		$( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );

	    	// geocode coordinates
	  		this_form.reverse_geocode( position.coords.latitude, position.coords.longitude, false );  		
		},

		/**
		 * Display geolocator error message
		 * 
		 * @param  {[type]} error [description]
		 * @return {[type]}       [description]
		 */
		show_error : function( error ) {
	  		
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

			$( '#gmw-lf-locator-button' ).removeClass( 'animate-spin' );
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
			
			var address = address;

			if ( $.trim( address ).length == 0 ) {

				this_form.location_blank();
			
			} else {

				// init geocode
		        geocoder = new google.maps.Geocoder();

		        data = { 
	                'address' : address, 
	                'region'  : this_form.region
	            };

		        // geocode address
		   	 	geocoder.geocode( data, function( results, status ) {
		      		
		   	 		if ( status == google.maps.GeocoderStatus.OK ) {
		   	 				
		   	 			// corrdinates
		        		lat = results[0].geometry.location.lat();
		        		lng = results[0].geometry.location.lng();   	        		
		    			
		    			if ( this_form.fields_changed == 1  ) {

		    				$( '.group_coordinates, .group_address' ).val( '' );
		    			
		    			} else if ( this_form.fields_changed == 2 ) {
		    			
		    				$( '.group_coordinates, .gmw-lf-submission-field.group_address, #' + this_form.location_fields.address.id ).val( '' );		
		    			}

		    			// add values to coords
		    			$( '.group_coordinates.latitude' ).val( lat );
		    			$( '.group_coordinates.longitude' ).val( lng );

		    			// populate value in full address fields
		       			$( '#' + this_form.location_fields.address.id +', #gmw_lf_address' ).val( address );
		       			$( '#gmw_lf_formatted_address' ).val( results[0].formatted_address );

		       			// get the rest of the address fields
		       			this_form.get_address_fields( results[0], true );

		       		// show failed message
		    		} else {    

		    			this_form.geocoder_failed( status );
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
					
			// populate coords fields with the original coords values
			$( '.group_coordinates.latitude' ).val( lat );
			$( '.group_coordinates.longitude' ).val( lng );

			// init google geocoder
			geocoder = new google.maps.Geocoder();
			
			data = { 
                'latLng' : new google.maps.LatLng( lat ,lng ), 
                'region' : this_form.region
            };
			
			// reverse geocode coordinates
			geocoder.geocode( data, function( results, status ) {		
				
				// if geocode successful
				if ( status == google.maps.GeocoderStatus.OK ) {
					
					//clear address fields before updating with new info
					$( '.group_address,' + this_form.location_fields.address.id ).val('');

					//update address and formatted address fields
					$( '#' + this_form.location_fields.address.id + ', #gmw_lf_address, #gmw_lf_formatted_address' ).val( results[0].formatted_address );

					//get address fields
					this_form.get_address_fields( results[0], save_data );

				//otherwise show error message
	      		} else {
	      			this_form.geocoder_failed( status );
	      		}
	   		});
		}, 
		
		/**
		 * NO location message.
		 * 
		 * @param  {string} status status message
		 * 
		 * @return {void}  
		 */
		location_blank : function() {

			this_form.is_location_confirmed = false;

  			//alert error message
    		alert( this_form.messages.location_blank );

    		//hide loader
		  	$( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast' );	
		},

		/**
		 * Geocoder failed functions.
		 * 
		 * @param  {string} status status message
		 * 
		 * @return {void}  
		 */
		geocoder_failed : function( status ) {

			this_form.is_location_confirmed = false;

  			//alert error message
    		alert( this_form.messages.geocoder_failed + ' Error Message: '+ status );

    		//hide loader
		  	$( '#' + this_form.action_fields.loader.id ).fadeOut( 'fast' );	
		},

		/**
		 * Retrive the address fields from address component
		 * 
		 * @param  {[type]} location  [description]
		 * @param  {[type]} save_data [description]
		 * @return {[type]}           [description]
		 */
		get_address_fields : function( location, save_data ) {

			ac  = location.address_components;
			pid = typeof location.place_id !== undefined ? location.place_id : '';
	    	
	    	// place ID	
	    	$( '.group_address.place_id' ).val( pid );

	    	// collect the geocoded data to later use it in action hook
	    	var geocoded_data = {
	    		street_number 	  : null,
	    		street_name 	  : null,
	    		street 			  : null,
	    		premise 		  : null,
	    		neighborhood 	  : null,
	    		city 			  : null,
	    		county 			  : null,
	    		region_name 	  : null,
	    		region_code 	  : null,
	    		postcode 		  : null,
	    		country_name 	  : null,
	    		country_code 	  : null,
	    		latitude 		  : null,
	    		longitude 		  : null,
	    		formatted_address : null
	    	}

	    	// modify the address component before populating the location fields.
	    	// can also performe custom tasks perform 
	    	ac = GMW.apply_filters( 'gmw_lf_address_component', ac, this_form );

	    	// ac ( address_component ): complete location data object
	    	// ac[x]: each location field in the address component
			for ( x in ac ) {

				if ( ac[x].types == 'street_number' && ac[x].long_name != undefined ) {
					geocoded_data.street_number = ac[x].long_name;
					$( '.group_address.street_number' ).val( ac[x].long_name );
				}
				
				if ( ac[x].types == 'route' && ac[x].long_name != undefined ) {	
					geocoded_data.street_name = ac[x].long_name;
					$( '.group_address.street_name' ).val( ac[x].long_name );

					geocoded_data.street = $( '.group_address.street_number' ).val() + ' ' + $( '.group_address.street_name' ).val(); 
					$( '.group_address.street' ).val( geocoded_data.street );
				}

				if ( ac[x].types == 'subpremise' && ac[x].long_name != undefined ) {
					geocoded_data.premise = ac[x].long_name;
					$( '.group_address.premise' ).val( ac[x].long_name );
				}
				
				 if ( ac[x].types == 'neighborhood,political' && ac[x].long_name != undefined ) {
				 	geocoded_data.neighborhood = ac[x].long_name;
					$( '.group_address.neighborhood' ).val( ac[x].long_name );
				}
	 
		        if( ac[x].types == 'locality,political' && ac[x].long_name != undefined ) {
		        	geocoded_data.city = ac[x].long_name;
					$( '.group_address.city' ).val( ac[x].long_name );
				}
		        
		        if ( ac[x].types == 'administrative_area_level_1,political' ) {
		        	geocoded_data.region_name = ac[x].long_name;
		          	$( '.group_address.region_name' ).val( ac[x].long_name );

		          	geocoded_data.region_code = ac[x].short_name;
		          	$( '.group_address.region_code' ).val( ac[x].short_name );
		        }  
		       
	  			if ( ac[x].types == 'administrative_area_level_2,political' && ac[x].long_name != undefined ) {
	  				geocoded_data.county = ac[x].long_name;
					$( '.group_address.county' ).val( ac[x].long_name );
				}

		        if ( ac[x].types == 'postal_code' && ac[x].long_name != undefined ) {
		        	geocoded_data.postcode = ac[x].long_name;
					$( '.group_address.postcode' ).val( ac[x].long_name );
				}
		        
		        if ( ac[x].types == 'country,political' ) {
		        	geocoded_data.country_name = ac[x].long_name;
		          	$( '.group_address.country_name' ).val( ac[x].long_name );
		        
		          	geocoded_data.country_code = ac[x].short_name;
		          	$( '.group_address.country_code' ).val( ac[x].short_name );
		        } 
	        }
			
			geocoded_data.latitude  		= $( '#gmw_lf_latitude' ).val();
			geocoded_data.longitude 		= $( '#gmw_lf_longitude' ).val();
			geocoded_data.formatted_address = $( '#gmw_lf_formatted_address' ).val();

			// do something custom with the location data
			GMW.do_action( 'gmw_lf_geocoded_location_data', geocoded_data, location, this_form );

			// update map based of new coords
			if ( this_form.map_enabled && $( '#gmw-lf-map' ).length ) {
				this_form.update_map( geocoded_data.latitude, geocoded_data.longitude );
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
		  		$( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );	

		  		latVal = lngVal = '';

		  		// make sure coords exist in form
		  		if ( typeof this_form.coords_fields != false && this_form.coords_fields.length ) {
			  		latVal = $( '#' + this_form.coords_fields.latitude.id ).val();
			  		lngVal = $( '#' + this_form.coords_fields.longitude.id ).val();
			  	}

		  		// if address changed or coordinates missing
				if ( this_form.fields_changed != 3 || $.trim( latVal ).length == 0 || $.trim( lngVal ).length == 0 ) {

					// check for full address first
					if ( this_form.fields_changed == 1 && $.trim( $( '#' + this_form.location_fields.address.id ).val() ).length > 0 ) {
		
						address = $( '#' + this_form.location_fields.address.id ).val();

					// otherwise, check for multiple address fields
					} else {
				
						address = '';
						
						$.each( this_form.address_fields, function( index, value ) {
							
							// if address field and value exist
							if ( $( '#' + value.id ).length && $.trim( $( '#' + value.id ).val() ).length > 0 ) {
								address += $( '#' + value.id ).val() + ' ';
							}
						});			    	
					}

					// if address blank but have coordinates do reverse geocoding
					if ( $.trim( address ).length == 0 && ( $.trim( latVal ).length != 0 && $.trim( lngVal ).length != 0 ) ) {
						
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
			
			//event.preventDefault();
			
			var form_submission = form_submission;

			// if location does not exist 
			if ( ! this_form.location_exists ) {

				// if required abort with a message
				if ( this_form.vars.location_required ) {
					
					this_form.location_blank();

					return false;

				// otherwise, proceed without location
				} else {

					this_form.proceed_submission = true;

					$( this_form.vars.form_element ).submit();

					return false;
				}

			//if location exists but not confirmed
			} else if ( ! this_form.is_location_confirmed ) {

				var setTime = 3000;

				this_form.auto_confirming = true;

				$( '#' + this_form.action_fields.message.id ).fadeOut( 'fast' );

				this_form.confirm_location();
			
			// otherwise, if confirmed
			} else {

				var setTime = 0;
			}

			setTimeout( function() {

				//if location confirmed
				if ( this_form.is_location_confirmed ) {
			
					if ( form_submission || ( this_form.vars.stand_alone && ! this_form.vars.ajax_enabled ) ) {
				
						this_form.proceed_submission = true;

						this_form.auto_confirming = false;

						$( this_form.vars.form_element ).submit();
					
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

				lat = $( '#gmw_lf_latitude' ).val();
				lng = $( '#gmw_lf_longitude' ).val();

				// verify coords
				if ( $.trim( lat ).length == 0 || $.trim( lng ).length == 0 || ! $.isNumeric( lat ) || ! $.isNumeric( lng ) ) {
					
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

				$( this_form.vars.form_element ).submit();
			
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
				$( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );
				
				$( '#' + this_form.action_fields.delete_location.id ).fadeIn( 'fast' );

				//if ( this_form.vars.auto_confirm ) {

				//	this_form.action_message( 'updating', this_form.messages.confirming_location, false );
				
			//	} else {

					// hide loader and show address confirm message
				this_form.action_message( 'ok', this_form.messages.location_exists, true );
			//	}
			}

			// clear change event when location confirmed.
			$( '.gmw-lf-field.address-field, .gmw-lf-field.group_address, .gmw-lf-field.group_coordinates' ).off( 'change' );
				
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
	  	 	$( '#' + this_form.action_fields.message.id ).fadeOut( 'fast', function() {
	  	 		//show loader
	  	 		$( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );
	  	 	});

	  	 	// save location via ajax
			gmwLocationForm = $.ajax({
				type 	 : "POST",
				dataType : 'json',	
				url      : gmwAjaxUrl,
				data 	 : { 
					action       : this_form.vars.update_callback,
					'formValues' : $( this_form.vars.form_element ).serialize(), 
					'formArgs'   : this_form.vars,
					'security'	 : this_form.security
				},

				// saving success
				success : function( response ){				

					if ( response ) {
						
						// pass the location ID to hidden field
						$( '#gmw_lf_location_id' ).val( response );

						//hide loader and confirm button
						$( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast', function() {

							//action message
							this_form.action_message( 'ok', this_form.messages.location_saved, true );

							//wait abit and hide message
							setTimeout( function() {			
									
								///hide action message
								$( '#' + this_form.action_fields.message.id + ' span' ).html( this_form.messages.location_exists );
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
	    	$( '#' + this_form.action_fields.message.id ).fadeOut( 'fast', function() {

	    		// show loader
		  		$( '#' + this_form.action_fields.loader.id ).fadeIn( 'fast' );	

		  		// hide confirm button
		  		$( '#' + this_form.action_fields.confirm_location.id ).fadeOut( 'fast' );	
	    	});

		  	// delete location from database via ajax only if location ID exists
		  	if ( $( '#gmw_lf_location_id' ).val() != '' && $( '#gmw_lf_location_id' ).val() != '0' ) {

				this_form.ajax_delete();
					
			} else {

				//clear all location fields
	  			$( '.group_address, .group_coordinates, .gmw-lf-field.address-field, .gmw-lf-extra-field' ).val( '' );

	  			// set location confirmed to false
	  			this_form.is_location_confirmed = false;

				setTimeout(function() {
					
					this_form.location_exists = false;

	   				$( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

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

			gmwLocationForm = $.ajax({
				type 	 : "post",
				dataType : 'json',
				url      : gmwAjaxUrl,
				data 	 : {
					action       : this_form.vars.delete_callback, 
				 	'formValues' : $( this_form.vars.form_element ).serialize(), 
					'formArgs'   : this_form.vars,
					'security'	 : this_form.security
				},		
				
				// if location deleted
				success  : function( response ) {
			
					if ( response ) {

						//clear all location fields
		  				$( '.group_address, .group_coordinates, .gmw-lf-field.address-field, .gmw-lf-extra-field, #gmw_lf_location_id' ).val( '' );

		  				//set location confirmed to false
		  				this_form.is_location_confirmed = false;

	  					this_form.location_exists = false;

   						$( '#' + this_form.action_fields.delete_location.id ).fadeOut( 'fast' );

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

	GMW_Location_Form.init();
});