/**
 * GEO my WP functions.
 * 
 * @type {Object}
 */
var GMW = {

    //GMW options
    options : gmwVars.settings,

    geocode_provider : gmwVars.geocodingProvider || 'google_maps',

    // hooks holder
    hooks : { action: {}, filter: {} },

    // global holder for any map elements displayed on a page.
    map_elements : {},

    // navigator timeout  limit 
    navigator_timeout : 10000,

    autocomplete_fields : ( typeof gmwAutocompleteFields !== undefined ) ? {} : gmwAutocompleteFields,

    // Navigator error messages
    navigator_error_messages : {
        1 : 'User denied the request for Geolocation.',
        2 : 'Location information is unavailable.',
        3 : 'The request to get user location timed out.',
        4 : 'An unknown error occurred'
    },

    vars : {

        // page locatore vars
        auto_locator : {
            status   : false,
            type     : false,
            callback : false       
        },

        // form locator button object
        locator_button : {
            status  : false,
            element : false,
            id      : false,
            loader  : false,
            submit  : false
        },

        // form submission vars
        form_submission : {
            status : false,
            form   : false,
            id     : false,
            submit : false
        },
    },

    current_location_fields : [
		'lat',
		'lng',
		'address',
		'formatted_address',
		'street',
		'city',
		'region_name',
		'region_code',
		'postcode',
		'country_name',
		'country_code'
	],

    /**
     * Run on page load
     * 
     * @return {[type]} [description]
     */
    init : function() {

        GMW.add_action( 'gmw_init' );

        // hide all map loaders
        //jQuery( 'div.gmw-map-wrapper' ).find( 'i.gmw-map-loader' ).fadeOut( 1500 );  

        if ( jQuery( 'div.gmw-map-wrapper.gmw-expanded-map' ).length ) {
            jQuery( 'body, html' ).addClass( 'gmw-scroll-disabled' ); 
        }

        // run form functions only when a form is present on the page
        if ( jQuery( 'div.gmw-form-wrapper, ul.gmw-form-wrapper' ).length ) {
            GMW.form_functions();
        }

        // check if we need to autolocate the user on page load
        if ( navigator.geolocation && GMW.options.general.auto_locate == 1 && GMW.get_cookie( 'gmw_autolocate' ) != 1 ) {

            //set cookie to prevent future autolocation for one day
            GMW.set_cookie( 'gmw_autolocate', 1, 1 );

            // run auto locator
            GMW.auto_locator( 'page_locator', GMW.page_locator_success, false );
        }

        // dont not enable autocomplete if google is not defined.
        // This check should be imporved.
        if ( typeof google !== 'undefined' ) {

	        // Enable address autocomplete on address fields
	        jQuery( 'input.gmw-address-autocomplete' ).each( function() {
	            if ( jQuery( this ).is( '[id]' ) ) {
	                GMW.address_autocomplete( jQuery( this ).attr( 'id' ), jQuery( this ).data() );
	            }
	        });
	    }

        if ( typeof jQuery.ui !== 'undefined' && jQuery.ui.draggable ) {
            GMW.draggable_element();
        }

        // toggle element
        GMW.toggle_element();

        // trigger range slider
        GMW.rangeslider();

        // trigger range slider
        GMW.date_picker();
    },

    /**
     * Create hooks system for JavaScript
     *
     * This code was developed by the develpers of Gravity Forms plugin and was modified to
     * work with GEO my WP. Thank you!!!
     * 
     * @param {[type]} action   [description]
     * @param {[type]} callable [description]
     * @param {[type]} priority [description]
     * @param {[type]} tag      [description]
     */
    add_action : function( action, callable, priority, tag ) {
        GMW.add_hook( 'action', action, callable, priority, tag );
    },

    /**
     * Add filter
     * 
     * @param {[type]} action   [description]
     * @param {[type]} callable [description]
     * @param {[type]} priority [description]
     * @param {[type]} tag      [description]
     */
    add_filter : function( action, callable, priority, tag ) {
        GMW.add_hook( 'filter', action, callable, priority, tag );
    },

    /**
     * Do action
     * 
     * @param  {[type]} action [description]
     * @return {[type]}        [description]
     */
    do_action : function( action ) {
        GMW.do_hook( 'action', action, arguments );
    },

    /**
     * Apply filters
     * 
     * @param  {[type]} action [description]
     * @return {[type]}        [description]
     */
    apply_filters : function( action ) {
        return GMW.do_hook( 'filter', action, arguments );
    },

    /**
     * Remove action
     * @param  {[type]} action [description]
     * @param  {[type]} tag    [description]
     * @return {[type]}        [description]
     */
    remove_action : function( action, tag ) {
        GMW.remove_hook( 'action', action, tag );
    },

    /**
     * Remove filter
     * 
     * @param  {[type]} action   [description]
     * @param  {[type]} priority [description]
     * @param  {[type]} tag      [description]
     * @return {[type]}          [description]
     */
    remove_filter : function( action, priority, tag ) {
        GMW.remove_hook( 'filter', action, priority, tag );
    },

    /**
     * Add hook
     *
     * @param {[type]} hookType [description]
     * @param {[type]} action   [description]
     * @param {[type]} callable [description]
     * @param {[type]} priority [description]
     * @param {[type]} tag      [description]
     */
    add_hook : function( hookType, action, callable, priority, tag ) {
        
        if ( undefined == GMW.hooks[hookType][action] ) {
            GMW.hooks[hookType][action] = [];
        }

        var hooks = GMW.hooks[hookType][action];
        if ( undefined == tag ) {
            tag = action + '_' + hooks.length;
        }

        if ( priority == undefined ){
            priority = 10;
        }

        GMW.hooks[hookType][action].push( { 
            tag      : tag, 
            callable : callable, 
            priority :priority 
        } );
    },

    /**
     * Do hook
     * 
     * @param  {[type]} hookType [description]
     * @param  {[type]} action   [description]
     * @param  {[type]} args     [description]
     * @return {[type]}          [description]
     */
    do_hook : function( hookType, action, args ) {

        // splice args from object into array and remove first index which is the hook name
        args = Array.prototype.slice.call( args, 1 );

        if ( undefined != GMW.hooks[hookType][action] ) {
            
            var hooks = GMW.hooks[hookType][action], hook;
           
            //sort by priority
            hooks.sort( function( a, b ) { 
                return a.priority - b.priority;
            });
            
            for ( var i = 0; i < hooks.length; i++ ) {
                
                hook = hooks[i].callable;
                
                if ( typeof hook != 'function' ) {
                    hook = window[hook];
                }

                if ( 'action' == hookType ) {
                  
                    hook.apply( null, args );

                } else {

                    args[0] = hook.apply( null, args );
                }
            }
        }
        if ( 'filter' == hookType ) {
            return args[0];
        }
    },

    /**
     * Remove hook
     * 
     * @param  {[type]} hookType [description]
     * @param  {[type]} action   [description]
     * @param  {[type]} priority [description]
     * @param  {[type]} tag      [description]
     * @return {[type]}          [description]
     */
    remove_hook : function( hookType, action, priority, tag ) {
        
        if ( undefined != GMW.hooks[hookType][action] ) {
            
            var hooks = GMW.hooks[hookType][action];
            
            for ( var i = hooks.length - 1; i >= 0; i-- ) {
                
                if ( ( undefined == tag || tag == hooks[i].tag ) && ( undefined == priority || priority == hooks[i].priority ) ){
                    hooks.splice( i, 1 );
                }
            }
        }
    },

    /**
     * Set cookie
     * 
     * @param {[type]} name   [description]
     * @param {[type]} value  [description]
     * @param {[type]} exdays [description]
     */
    set_cookie : function( name, value, exdays ) {
        var exdate = new Date();
        exdate.setTime( exdate.getTime() + ( exdays * 24 * 60 * 60 * 1000 ) );
        var cooki = escape( encodeURIComponent( value ) ) + ( ( exdays == null ) ? "" : "; expires=" + exdate.toUTCString() );
        document.cookie = name + "=" + cooki + "; path=/";
    },

    /**
     * Get cookie
     * 
     * @param  {[type]} name [description]
     * @return {[type]}      [description]
     */
    get_cookie : function( name ) {
        var results = document.cookie.match( '(^|;) ?' + name + '=([^;]*)(;|$)' );
        return results ? decodeURIComponent( results[2]) : null;
    },

    /**
     * Delete cookie
     * 
     * @param  {[type]} name [description]
     * @return {[type]}      [description]
     */
    delete_cookie : function( name ) {
    	//jQuery.cookie( name, '', { path: '/' } );
    	document.cookie = encodeURIComponent( name ) + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    	//document.cookie = encodeURIComponent( name ) + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
        //document.cookie = encodeURIComponent( name ) + "=deleted; expires=" + new Date(0).toUTCString();
    },

    /**
     * Get user's current position
     * 
     * @param  {function} success callback function when navigator success
     * @param  {function} failed  callback function when navigator failed
     * 
     * @return {[type]}                   [description]
     */
    navigator : function( success, failed ) {

        // if navigator exists ( in browser ) try to locate the user
        if ( navigator.geolocation ) {
            
            navigator.geolocation.getCurrentPosition( show_position, show_error, { timeout: GMW.navigator_timeout } );
        
        // otherwise, show an error message
        } else {
            return failed( 'Sorry! Geolocation is not supported by this browser.' );
        }

        // geocode the coordinates if current position found
        function show_position( position ) {
            GMW.geocoder( [ position.coords.latitude, position.coords.longitude ], success, failed );
        }

        // show error if failed navigator
        function show_error( error ) {
            return failed( GMW.navigator_error_messages[error.code] );
        }
    },

    /**
     * Geocoder function. 
     *
     * Can be used for geocoding an address or reverse geocoding coordinates.
     * 
     * @param  string | array pass string if geocoding an address or array of coordinates [ lat, lng ] if reverse geocoding.
     * @param  {function} success  callback function on success
     * @param  {function} failed   callback function on failed
     * 
     * @return {[type]}          [description]
     */
    geocoder : function( location, success, failed ) {

    	// get region from settings
        var region   = typeof GMW.options.general.country_code !== 'undefined' ? GMW.options.general.country_code : 'us',
        	language = typeof GMW.options.general.language_code !== 'undefined' ? GMW.options.general.language_code : 'en',
			geocoder = new GMW_Geocoder( GMW.geocode_provider ),
        	funcName = typeof location === 'object' ? 'reverseGeocode' : 'geocode',
	        params   = { 
	            'q' 	   : location,
	            'region'   : region,
	            'language' : language
	        };

        // run geocoder
        geocoder[funcName]( params, function( response, status ) {
            // on success
            if ( status == 'OK' ) {
                return ( typeof success !== 'undefined' ) ? success( response.result, response ) : GMW.geocoder_success( response.result, response );
            // on failed      
            } else {
                return ( typeof failed !== 'undefined' ) ? failed( status, response ) : GMW.geocoder_failed( status, response );
            }
        });
    },

    /**
     * New function replaces the "geocoder" function above.
     *
     * You can feed more data into this function.
     * 
     * @type {[type]}
     */
    geocode : function( provider, type, options, success, failed ) {

    	var geocoder = new GMW_Geocoder( provider );

    	if ( type == 'reverse' ) {
    		type = 'reverseGeocode';
    	}

    	// run geocoder
        geocoder[type]( options, function( response, status ) {

            // on success
            if ( status == 'OK' ) {
                return ( typeof success !== 'undefined' ) ? success( response ) : GMW.geocoder_success( response );
            // on failed      
            } else {
                return ( typeof failed !== 'undefined' ) ? failed( status, response ) : GMW.geocoder_failed( status, response );
            }
        });
    },

    /**
     * Geocoder success default callback functions
     * 
     * @return {[type]} [description]
     */
    geocoder_success : function() {},

    /**
     * Geocoder failed default callback functions
     * 
     * @return {[type]} [description]
     */
    geocoder_failed : function( status ) {
        alert( 'We could not find the address you entered for the following reason: ' + status );
    },

    /**
     * Google places address autocomplete
     * 
     * @return void
     */
    address_autocomplete : function( field_id , fieldData ) {
          
        var input_field = document.getElementById( field_id );

        // verify the field
        if ( input_field != null ) {
            
            var options = {};

            if ( typeof fieldData.autocomplete_countries !== 'undefined' ) {
                options.componentRestrictions = { 
                    country : fieldData.autocomplete_countries.split( ',' )
                };
            }

            if ( typeof fieldData.autocomplete_types !== 'undefined' ) {
                options.types = [fieldData.autocomplete_types];
            }

            //The plugins uses basic options of Google places API. 
            //You can use this filter to modify the autocomplete options
            //see this page https://developers.google.com/maps/documentation/javascript/places-autocomplete
            //for all the available options.
            options = GMW.apply_filters( 'gmw_address_autocomplete_options', options, field_id, input_field, GMW );
    
            var autocomplete = new google.maps.places.Autocomplete( input_field, options );
             
            google.maps.event.addListener( autocomplete, 'place_changed', function() {

                var place = autocomplete.getPlace();
                
                GMW.do_action( 'gmw_address_autocomplete_place_changed', place, autocomplete, field_id, input_field, options );
                
                if ( place.geometry ) {

                    var formElement = jQuery( input_field.closest( 'form' ) );

                    // if only country entered set its value in hidden fields
                    if ( place.address_components.length == 1 && place.address_components[0].types[0] == 'country' ) {      

                        formElement.find( '.gmw-country' ).val( place.address_components[0].short_name ).prop( 'disabled', false );
                    
                    // otherwise if only state entered.
                    } else if ( place.address_components.length == 2 && place.address_components[0].types[0] == 'administrative_area_level_1' ) {
                        
                        formElement.find( '.gmw-state' ).val( place.address_components[0].long_name ).prop( 'disabled', false );
                        formElement.find( '.gmw-country' ).val( place.address_components[1].short_name ).prop( 'disabled', false );
                    }

                    // make sure coords fields exist.
                    formElement.find( '.gmw-lat' ).val( place.geometry.location.lat().toFixed(6) );
                    formElement.find( '.gmw-lng' ).val( place.geometry.location.lng().toFixed(6) );             
                }   
            });
        }
    },

    /**
     * Save location fields into cookies and current location hidden form
     * 
     * @param  {object} results location data returned forom geocoder
     * 
     * @return {[type]}         [description]
     */
    save_location_fields : function( result ) {
        
        var cl_form = jQuery( 'form#gmw-current-location-hidden-form' );
        
        GMW.do_action( 'gmw_save_location_fields', result );

        // save location in current location form and cookies.
        for ( fieldName in result ) {
        	
        	// we only want some fields to save in cookies.
        	if ( jQuery.inArray( fieldName, GMW.current_location_fields ) !== -1 ) {
        		GMW.set_cookie( 'gmw_ul_'. fieldName, result[fieldName], 7 );
        	}
        
        	cl_form.find( 'input#gmw_cl_' + fieldName ).val( result[fieldName] );

        	// hook custom functions
            GMW.do_action( 'gmw_save_location_field', result[fieldName], result );
        }

        return result;
    },

    /**
     * Page load locator function.
     *
     * Get the user's current location on page load
     * 
     * @return {[type]} [description]
     */
    auto_locator : function( type, success, failed ) {

        // set status to true
        GMW.vars.auto_locator.status  = true;
        GMW.vars.auto_locator.type    = type;
        GMW.vars.auto_locator.success = success;
        GMW.vars.auto_locator.failed  = failed;
        
        // run navigator
        GMW.navigator( GMW.auto_locator_success, GMW.auto_locator_failed );
    },

    /**
     * page load locator success callback function
     * 
     * @param  {object} results location fields returned from geocoder
     * 
     * @return {[type]}         [description]
     */
    auto_locator_success : function( results ) {

        // save address field to cookies and current location form
        address_fields = GMW.save_location_fields( results );

        // run custom callback function if set 
        if ( GMW.vars.auto_locator.success != false ) {

            GMW.vars.auto_locator.success( address_fields, results );

        // otherwise, get done with the function.
        } else {

            GMW.vars.auto_locator.status  = false;
            GMW.vars.auto_locator.type    = false;
            GMW.vars.auto_locator.success = false;
            GMW.vars.auto_locator.failed  = false;
        }
    },

    /**
     * page locator failed callback fucntion
     * 
     * @param  {string} status error message
     * 
     * @return {[type]}        [description]
     */
    auto_locator_failed : function( status ) {

        // run custom failed callback function if set
        if ( GMW.vars.auto_locator.failed != false ) {

            GMW.vars.auto_locator.failed( status );
        
        // otherwise, get done with the function.
        } else {

            // alert error message
            alert( status );

            GMW.vars.auto_locator.status  = false;
            GMW.vars.auto_locator.type    = false;
            GMW.vars.auto_locator.success = false;
            GMW.vars.auto_locator.failed  = false;
        }
    },

    /**
     * Page locator success callback function
     * 
     * @param  {[type]} results [description]
     * @return {[type]}         [description]
     */
    page_locator_success : function( address_fields, results ) {

        GMW.vars.auto_locator.status   = false;
        GMW.vars.auto_locator.callback = false;
        GMW.vars.auto_locator.type     = false;

        // submit current location hidden form
        setTimeout(function() {
            jQuery( 'form#gmw-current-location-form' ).submit();             
        }, 500); 
    },

    /**
     * GEO my WP Form functions.
     *
     * triggers only when at least one form presents on the page
     * 
     * @return {[type]} [description]
     */
    form_functions : function() {

        GMW.enable_smartbox();
     
        // remove hidden coordinates when address field value changes
        jQuery( 'form' ).find( 'input.gmw-address' ).keyup( function ( event ) { 
            
            // abort if enter key.
            if ( event.which == 13 ) {
                return;
            }

            var form = jQuery( this ).closest( 'form' );

            // clear coords, state and country fields.
            form.find( 'input.gmw-lat, input.gmw-lng' ).val( '' );
            form.find( 'input.gmw-state, input.gmw-country' ).val( '' ).prop( 'disabled', true );
        });

        // When click on locator button in a form
        jQuery( 'form' ).find( '.gmw-locator-button' ).click( function() {
            GMW.locator_button( jQuery( this ), jQuery( this ).closest( 'form' ) );
        });

        // on form submission
        jQuery( 'form.gmw-form' ).submit( function( event ) {

            // run form submission
            GMW.form_submission( jQuery( this ), event );
        });
    },

    /**
     * Enable smartbox libraries
     * @return {[type]} [description]
     */
    enable_smartbox : function() {

        // enable chosen for GEO my WP form elements
        if ( jQuery().chosen ) {
            jQuery( 'form' ).find( 'select.gmw-smartbox' ).chosen( {
                width : '100%'
            });

        // enable select 2
        } else if ( jQuery().select2 ) {
            jQuery( 'form' ).find( 'select.gmw-smartbox' ).select2();
        }
    },

    /**
     * Form submission function.
     *
     * Executes on form submission
     *     
     * @param  {object} form  The submitted form
     * @param  {object} event submit event
     * 
     * @return {[type]}       [description]
     */
    form_submission : function( form, event ) {
        
        // set form variables
        GMW.vars.form_submission.status = true;
        GMW.vars.form_submission.form   = form;
        GMW.vars.form_submission.id     = GMW.vars.form_submission.form.find( '.gmw-form-id' ).val();

        if ( GMW.vars.form_submission.submit == true ) {
            return true;
        } 

        //var form         = form;
        var addressField = form.find( 'input.gmw-address' );
        var address      = '';

        // prevent form submission. We need to run some functions.
        event.preventDefault();
        
        // set the "paged" value to first page.
        form.find( 'input.gmw-paged' ).val( '1' );
   		
   		// modify the address before geocoding takes place.
   		addressField = GMW.apply_filters( 'gmw_search_form_address_pre_geocoding', addressField, GMW );

        // get the address field/s value.
        address = addressField.map( function() {
           return jQuery( this ).val();
        }).get().join( ' ' );           
  
        // if address field is empty.
        if ( ! jQuery.trim( address ).length ) {

            // check if address is mendatory, and if so, show error and abort submission.
            if ( addressField.hasClass( 'mandatory' ) ) {

                // add error class to address fields if no address enterd.
                if ( ! addressField.hasClass( 'gmw-no-address-error' ) ) {

                    // remove the class on focus or keypress in the field
                    addressField.addClass( 'gmw-no-address-error' ).on( 'focus keypress', function() {
                        jQuery( this ).removeClass( 'gmw-no-address-error' ).off( 'focus keypress' );
                    });

                    // remove error class after a few seconds if was not removed already.
                    setTimeout( function() {
                        addressField.removeClass( 'gmw-no-address-error' ).off( 'focus keypress' );
                    }, 4000 );
                }

                // abort submission
                return false;

            // otherwise submit the form
            } else {
                
                GMW.vars.form_submission.submit = true;
                GMW.vars.form_submission.form.submit(); 

                return false;
            }
        }

        // When Client-side geocoder is enabled
        //if ( typeof clientSideGeocoder == 'undefined' || clientSideGeocoder == 1 ) {
        	
            // check if hidden coords exists. if so no need to geocode the address again and we can submit the form with the information we already have.
            if ( form.find( 'input.gmw-lat' ).val() != '' && form.find( 'input.gmw-lng' ).val() != '' ) {            
               
                GMW.vars.form_submission.submit = true;
                GMW.vars.form_submission.form.submit(); 

                return false;
            }
       
            // geocoder the address entered
            GMW.geocoder( address, GMW.form_geocoder_success, GMW.geocoder_failed );

        // Otherwise, no geocoding needed. Submit the form!
        /*} else {    

            GMW.vars.form_submission.submit = true;
            GMW.vars.form_submission.form.submit(); 

            return false;    
        }*/
    },

    /**
     * Form submission geocoder function.
     *
     * This functions excecutes once after the geocoder successful.
     * 
     * @param  object results the geocoder results
     * 
     * @return {[type]}         [description]
     */
    form_geocoder_success : function( result ) {

        var form = GMW.vars.form_submission.form;
        //var ac   = results[0].address_components;

        // if only country entered set its value in hidden fields
        if ( result.level == 'country' ) {
            
            form.find( '.gmw-country' ).val( result.country_code ).prop( 'disabled', false );

        // otherwise, if only state entered.
        } else if ( result.level == 'region' ) {
            form.find( '.gmw-state' ).val( result.region_name ).prop( 'disabled', false );
            form.find( '.gmw-country' ).val( result.country_code ).prop( 'disabled', false );
        } 

        // add coordinates to hidden fields
        form.find( '.gmw-lat' ).val( parseFloat( result.lat ).toFixed(6) );
        form.find( '.gmw-lng' ).val( parseFloat( result.lng ).toFixed(6) );

        // submit the form
        setTimeout(function() {
            GMW.vars.form_submission.submit = true;
            GMW.vars.form_submission.form.submit();  
        }, 300);

        return false; 
    },

    /**
     * Form locator button function.
     *
     * Triggered with click on a locator button
     * 
     * @param  {object} locator the button was clicked
     * 
     * @return {[type]}         [description]
     */
    locator_button : function( locator, this_form ) {

        var form;

        // if form element is missing, try to find it.
        if ( typeof this_form == 'undefined' ) {
            form = locator.closest( 'form' );
        } else {
            form = this_form;
        }
       
        // disabled all text fields and submit buttons while working
        jQuery( 'form.gmw-form' ).find( 'input[type="text"], .gmw-submit' ).attr( 'disabled', 'disabled' );
        
        // set the locator vars.
        GMW.vars.locator_button.status  = true;
        GMW.vars.locator_button.form    = form;
        GMW.vars.locator_button.element = locator;
        GMW.vars.locator_button.form_id = locator.data( 'form_id' );
        GMW.vars.locator_button.loader  = locator.next();
        GMW.vars.locator_button.submit  = locator.attr( 'data-locator_submit' ) == 1 ? true : false;

        // clear hidden coordinates
        form.find( 'input.gmw-lat, input.gmw-lng' ).val( '' );
        form.find( 'input.gmw-state, input.gmw-country' ).val( '' ).prop( 'disabled', true );
        
        // if this is a font icon inside address field
        if ( locator.hasClass( 'inside' ) ) {

            locator.addClass( 'animate-spin' );

        // otherwise, if this is a button
        } else if ( locator.hasClass( 'text' ) ) {

             GMW.vars.locator_button.loader.fadeIn( 'fast' );

        } else {

            // hide locator button
            locator.fadeOut( 'fast', function() {
                // show spinning loader 
                GMW.vars.locator_button.loader.fadeIn( 'fast' );
            });
        }

        //very short delay to allow the locator loader to load
        setTimeout( function() {            
            // run auto locator
            GMW.auto_locator( 'locator_button', GMW.locator_button_success, GMW.locator_button_failed );
        }, 500 );
    },

    /**
     * Form locator success callback function
     * 
     * @param  {object} address_fields address fields collector
     * @param  {object} results        original location fields object returned by geocoder
     * 
     * @return {[type]}                [description]
     */
    locator_button_success : function( result ) {

        var form         = GMW.vars.locator_button.form;
        var addressField = form.find( 'input.gmw-address' );

        // enable all forms.
        jQuery( 'form.gmw-form' ).find( 'input[type="text"], .gmw-submit' ).removeAttr( 'disabled' );

        // add coords value to hidden fields
        form.find( 'input.gmw-lat' ).val( parseFloat( result.lat ).toFixed(6) );
        form.find( 'input.gmw-lng' ).val( parseFloat( result.lng ).toFixed(6) );

        //dynamically fill-out the address fields of the form
        if ( addressField.hasClass( 'gmw-full-address' ) ) {
            
            addressField.val( result.formatted_address );
        
        } else {        

            form.find( '.gmw-address.street' ).val( result.street );
            form.find( '.gmw-address.city' ).val( result.city );
            form.find( '.gmw-address.state' ).val( result.region_name );
            form.find( '.gmw-address.zipcode' ).val( result.postcode );
            form.find( '.gmw-address.country' ).val( result.country_code );
        }
       
        // if form locator set to auto submit form. 
        if ( GMW.vars.locator_button.submit ) {
            
            setTimeout( function() {
                
                form.find( '.gmw-submit' ).click();

                // we do this in case of an ajax submission
                GMW.locator_button_done();

            }, 500);
            
        } else {
            GMW.locator_button_done();
        }
    },

    /**
     * Form Locator failed callback function
     * @param  {[type]} status [description]
     * @return {[type]}        [description]
     */
    locator_button_failed : function( status ) {

        // alert failed message
        alert( 'Geocoder failed due to: ' + status );

        GMW.locator_button_done();
    },

    /**
     * Form locator done callback function. 
     * @return {[type]} [description]
     */
    locator_button_done : function() {

        var locator = GMW.vars.locator_button.element;

        // enabled all text fields and submit buttons
        jQuery( 'form.gmw-form' ).find( 'input[type="text"], .gmw-submit' ).removeAttr( 'disabled' );

        if ( locator.hasClass( 'inside' ) ) {

            locator.removeClass( 'animate-spin' );

        } else {
            // hide spinning loader
            GMW.vars.locator_button.loader.fadeOut( 'fast',function() {
                // show locator button
                locator.fadeIn( 'fast' );
            } );
        }

        setTimeout( function() {
            // change locator status
            GMW.vars.locator_button.status  = false;
            GMW.vars.locator_button.element = false;
            GMW.vars.locator_button.form_id = false;
            GMW.vars.locator_button.loader  = false;
            GMW.vars.locator_button.submit  = false;
        }, 500 );
    },

    /**
     * Enable range slider
     * 
     * @return {[type]} [description]
     */
    rangeslider : function() {
        jQuery( 'form' ).find( 'input.gmw-range-slider' ).on( 'input change', function() {
            jQuery( this ).prev( 'span, label' ).find( 'output' ).html( jQuery( this ).val() );
        });
    },

    /**
     * Enable Date picker
     * 
     * @return {[type]} [description]
     */
    date_picker : function() {

        var date_fields = jQuery( '.gmw-date-field' );
        var time_fields = jQuery( '.gmw-time-field' );

        if ( date_fields.length > 0 && typeof jQuery.fn.pickadate !== 'undefined') {
         
            date_fields.each( function() {
                var date_type = jQuery( this ).data( 'date_type' );
                jQuery( this ).pickadate({
                    //formatSubmit: 'yyyy/mm/dd',
                    format : date_type || 'yyyy/mm/dd',
                    //formatSubmit : 'yyyy/mm/dd',
                    //hiddenName: true
                });
            });
        }

        if ( time_fields.length > 0 && typeof jQuery.fn.pickatime !== 'undefined') {
            
            time_fields.each( function() {
                //var date_type = jQuery( this ).data( 'date_type' );
                jQuery( this ).pickatime({
                    interval: 1
                    //formatSubmit: 'yyyy/mm/dd',
                    //format : date_type || 'yyyy/mm/dd',
                    //formatSubmit : 'yyyy/mm/dd',
                    //hiddenName: true
                });
            });
        }
    },

    /**
     * Enable Dragging
     * 
     * @return {[type]} [description]
     */
    draggable_element : function() {

        /**
         * If this is a remote draggable element
         *
         * we need to pass some data from the original to the 
         * 
         * remote element. 
         * 
         * @param  {[type]}   var data [description]
         * @return {[type]}   [description]
         */
        jQuery( '.gmw-draggable.remote-toggle' ).each( function() {

            var data   = jQuery( this ).data();
            var target = jQuery( data.handle );
            
            target.addClass( 'gmw-draggable' ).attr( 'data-draggable', data.draggable ).attr( 'data-containment', data.containment );
        });

        /**
         * Enable draggable on mouseenter
         * 
         * @param  {[type]} e )             {            if ( ! jQuery( this ).hasClass( 'enabled' ) ) {                            jQuery( this ).addClass( 'enabled' );                var dragData [description]
         * @return {[type]}   [description]
         */
        jQuery( document ).on( 'mouseenter', '.gmw-draggable', function( e ) {

            if ( ! jQuery( this ).hasClass( 'enabled' ) ) {
            
                jQuery( this ).addClass( 'enabled' );

                var dragData = jQuery( this ).data();

                if ( dragData.draggable == 'global_map' ) {
                    dragData.draggable   = jQuery( this ).closest( '.gmw-form-wrapper' );
                    dragData.containment = jQuery( this ).closest( '.gmw-global-map-wrapper' );
                }
                
                jQuery( dragData.draggable ).draggable({
                    containment : dragData.containment,
                    handle      : jQuery( this )        
                });
            } 
        });
    },

    /**
     * Toggle elements
     * 
     * @return {[type]} [description]
     */
    toggle_element : function() {

        // do it on click
        jQuery( document ).on( 'click', '.gmw-element-toggle-button', function( e ) {
      
            var button  = jQuery( this );
            var data    = jQuery( this ).data();
            var target  = jQuery( data.target );
            var options = {};
            var visible = 1;

            // toggle icon class
            button.toggleClass( data.show_icon ).toggleClass( data.hide_icon );

            // if expanded, callapse windpw
            if ( button.attr( 'data-state' ) == 'expand' ) {

                options[data.animation] = data.close_length;

                button.attr( 'data-state', 'collapse' );
                target.attr( 'data-state', 'collapse' );

            // otherwise, expand
            } else {  

                options[data.animation] = data.open_length;
                
                button.attr( 'data-state', 'expand' );
                target.attr( 'data-state', 'expand' );
            }

            // if we do height or width animation we will use 
            // jquery aniation
            if ( data.animation == 'height' || data.animation == 'width'  ) {
               
               target.animate( options, data.duration ) ;
            
            // otherwise, we can use translatex, and we do it using css
            } else {

                target.addClass( 'gmw-toggle-element' ).css( data.animation, options[data.animation] );
            }
        });
    },

    /**
     * Get object value using string as key.
     * 
     * @param  {[type]} result [description]
     * @param  {[type]} field  [description]
     * @return {[type]}        [description]
     */
    get_field_by_string : function( result, field ) {

	    field = field.replace( /\[(\w+)\]/g, '.$1' );
	    field = field.replace( /^\./, '' );           
	    var a = field.split('.');
	    
	    for ( var i = 0, n = a.length; i < n; ++i ) {
	        
	        var k = a[i];
	        
	        if ( k in result ) {
	            result = result[k];
	        } else {
	            return '';
	        }
	    }

	    return result !== 'undefined' ? result : '';
	},
};
