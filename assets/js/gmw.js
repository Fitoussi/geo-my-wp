var GMW = {

    //GMW options
    options : gmwSettings,

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

    // page locatore vars
    auto_locator : {
        status   : false,
        type     : false,
        callback : false       
    },

    // form locator button object
    form_locator : {
        status : false,
        button : false,
        id     : false,
        loader : false,
        submit : false
    },

    // form submission vars
    form_submission : {
        status : false,
        form   : false,
        id     : false,
        submit : false
    },

    /**
     * Run on page load
     * 
     * @return {[type]} [description]
     */
    init : function() {

        // hide all map loaders
        jQuery( '.gmw-map-loader' ).fadeOut( 1500 );  

        // run form functions only when a form is present on the page
        if ( jQuery( '.gmw-form-wrapper' ).length ) {
            GMW.form_functions();
        }

        // check if we need to autolocate the user on page load
        if ( navigator.geolocation && GMW.options.general_settings.auto_locate == 1 && GMW.get_cookie( 'gmw_autolocate' ) != 1 ) {

            //set cookie to prevent future autolocation for one day
            GMW.set_cookie( 'gmw_autolocate', 1, 1 );

            // run auto locator
            GMW.auto_locator( 'page_locator', GMW.page_locator_success, false );
        }

        // trigger address autocomplete on address fields
        jQuery( '.gmw-address-autocomplete' ).each( function() {

            if ( jQuery( this ).is( '[id]' ) ) {
                GMW.address_autocomplete( jQuery( this ).attr( 'id' ), {} );
            }
        });
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
            hooks.sort( function( a, b ) { return a["priority"]-b["priority"] } );
            
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
        document.cookie = encodeURIComponent( name_name ) + "=deleted; expires=" + new Date(0).toUTCString();
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
        countryCode = ( GMW.options.general_settings.country_code != undefined ) ? GMW.options.general_settings.country_code : 'us';

        // get geocoder data
        // If reverse geocoding 
        if ( typeof location === 'object' ) {

            data = { 
                'latLng' : new google.maps.LatLng( location[0], location[1] ), 
                'region' : countryCode 
            };

        // otherwise, if geocoding an address
        } else {
            data = { 
                'address' : location, 
                'region'  : countryCode 
            };
        }

        // init google geocoder
        geocoder = new google.maps.Geocoder();

        // run geocoder
        geocoder.geocode( data, function( results, status ) {

            // on success
            if ( status == google.maps.GeocoderStatus.OK ) {

                return ( success != undefined ) ? success( results ) : GMW.geocoder_success( results );

            // on failed      
            } else {

                return ( failed != undefined ) ? failed( status ) : GMW.geocoder_failed( status );
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
    geocoder_failed : function( status) {
        alert( "We could not find the address you entered for the following reason: " + status );
    },

    /**
     * Google places address autocomplete
     * 
     * @return void
     */
    address_autocomplete : function( field_id , options ) {
          
        var input_field = document.getElementById( field_id );
   
        // verify the field
        if ( input_field != null ) {
            
            if ( typeof options === 'undefined' ) {
                var options = {};
            } 

            //basic options of Google places API. 
            //see this page https://developers.google.com/maps/documentation/javascript/places-autocomplete
            //for other available options
            options = GMW.apply_filters( 'gmw_address_autocomplete_options', options, field_id, input_field, GMW );
    
            var autocomplete = new google.maps.places.Autocomplete( input_field, options );
             
            google.maps.event.addListener( autocomplete, 'place_changed', function() {

                var place = autocomplete.getPlace();
                
                GMW.do_action( 'gmw_address_autocomplete_place_changed', place, autocomplete, field_id, input_field, options );
                
                // if place exists and autocomplete is within a form
                // get the coords from the place details into the hidden coordinates fields of the form
                if ( place.geometry && jQuery( '#' + field_id ).closest( 'form' ).hasClass( 'gmw-form' ) ) {
                    jQuery( '#' + field_id ).closest( 'form' ).find( '.gmw-lat' ).val( place.geometry.location.lat().toFixed(6) );
                    jQuery( '#' + field_id ).closest( 'form' ).find( '.gmw-lng' ).val( place.geometry.location.lng().toFixed(6) );
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
    save_location_fields : function( results ) {
      
        latitude  = results[0].geometry.location.lat().toFixed(6);
        longitude = results[0].geometry.location.lng().toFixed(6);

        // address fields holder
        address_fields = {
            'street_number'     : '',
            'street_name'       : '',
            'street'            : '',
            'premise'           : '',
            'neighborhood'      : '',
            'city'              : '',
            'region_code'       : '',
            'region_name'       : '',
            'country'           : '',
            'postcode'          : '',
            'country_code'      : '',
            'country_name'      : '',
            'address'           : results[0].formatted_address,
            'formatted_address' : results[0].formatted_address,
            'lat'               : latitude,
            'lng'               : longitude
        };
        
        // set location cookies
        GMW.set_cookie( 'gmw_ul_lat', latitude, 7 );
        jQuery( '#gmw_cl_lat' ).val( latitude );

        GMW.set_cookie( 'gmw_ul_lng', longitude, 7 );
        jQuery( '#gmw_cl_lng' ).val( longitude );

        GMW.set_cookie( 'gmw_ul_address', results[0].formatted_address, 7 );
        jQuery( '#gmw_cl_address' ).val( results[0].formatted_address );

        GMW.set_cookie( 'gmw_ul_formatted_address', results[0].formatted_address, 7 );
        jQuery( '#gmw_cl_formatted_address' ).val( results[0].formatted_address );

        address = results[0].address_components;

        // hook custom functions
        GMW.do_action( 'gmw_save_location_fields', results );
                
        //check for each of the address components and if exist save it in a cookie
        for ( x in address ) {

            // street number
            if ( address[x].types == 'street_number' && address[x].long_name != undefined ) {
                
                address_fields.street_number = address[x].long_name;

                jQuery( '#gmw_cl_street_number' ).val( address_fields.street_number );
            } 

            // street name and street
            if ( address[x].types == 'route' && address[x].long_name != undefined ) {  

                 //save street name in variable
                address_fields.street_name = address[x].long_name;

                jQuery( '#gmw_cl_street_name' ).val( address_fields.street_name );

                //combine the street number and street name into one street field
                if ( address_fields.street_number != '' ) {
                    address_fields.street = address_fields.street_number + ' ' + address_fields.street_name;
                } else {
                    address_fields.street = address_fields.street_name;
                }

                // save street field in cookie
                GMW.set_cookie( 'gmw_ul_street', address_fields.street, 7 );

                jQuery( '#gmw_cl_street' ).val( address_fields.street );
            }

            // apt/suit number
            if ( address[x].types == 'subpremise' && address[x].long_name != undefined ) {

                address_fields.premise = address[x].long_name;

                jQuery( '#gmw_cl_premise' ).val( address[x].long_name );
            }
            
            // neighborhood
             if ( address[x].types == 'neighborhood,political' && address[x].long_name != undefined ) {

                address_fields.neighborhood = address[x].long_name;

                jQuery( '#gmw_cl_neighborhood' ).val( address[x].long_name );
            }
            
            // city
            if( address[x].types == 'locality,political' && address[x].long_name != undefined ) {

                address_fields.city = address[x].long_name;

                GMW.set_cookie( 'gmw_ul_city', address[x].long_name, 7 );

                jQuery( '#gmw_cl_city' ).val( address[x].long_name );
            }
            
            // region code and name
            if ( address[x].types == 'administrative_area_level_1,political' ) {

                address_fields.region_name = address[x].long_name;
                address_fields.region_code = address[x].short_name;

                GMW.set_cookie( 'gmw_ul_region_name', address[x].long_name, 7 );
                GMW.set_cookie( 'gmw_ul_region_code', address[x].short_name, 7 );

                jQuery( '#gmw_cl_region_code' ).val( address[x].short_name );
                jQuery( '#gmw_cl_region_name' ).val( address[x].long_name );
            }  
            
            // county
            if ( address[x].types == 'administrative_area_level_2,political' && address[x].long_name != undefined ) {

                address_fields.county = address[x].long_name;

                jQuery( '#gmw_cl_county' ).val( address[x].long_name );
            }

            // postal code
            if ( address[x].types == 'postal_code' && address[x].long_name != undefined ) {

                address_fields.postcode = address[x].long_name;

                GMW.set_cookie( 'gmw_ul_postcode', address[x].short_name, 7 );

                jQuery( '#gmw_cl_postcode' ).val( address[x].long_name );
            }
            
            // country code and name
            if ( address[x].types == 'country,political' ) {

                address_fields.country_name = address[x].long_name;
                address_fields.country_code = address[x].short_name;

                GMW.set_cookie( 'gmw_ul_country_name', address[x].long_name, 7 );
                GMW.set_cookie( 'gmw_ul_country_code', address[x].short_name, 7 );

                jQuery( '#gmw_cl_country_code' ).val( address[x].short_name );
                jQuery( '#gmw_cl_country_name' ).val( address[x].long_name );
            } 

            // hook custom functions
            GMW.do_action( 'gmw_save_location_field', address[x], results );
        }

        return address_fields;
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
        GMW.auto_locator.status  = true;
        GMW.auto_locator.type    = type;
        GMW.auto_locator.success = success;
        GMW.auto_locator.failed  = failed;
        
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
        if ( GMW.auto_locator.success != false ) {

            GMW.auto_locator.success( address_fields, results );

        // otherwise, get done with the function.
        } else {

            GMW.auto_locator.status  = false;
            GMW.auto_locator.type    = false;
            GMW.auto_locator.success = false;
            GMW.auto_locator.failed  = false;
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
        if ( GMW.auto_locator.failed != false ) {

            GMW.auto_locator.failed( status );
        
        // otherwise, get done with the function.
        } else {

            // alert error message
            alert( status );

            GMW.auto_locator.status  = false;
            GMW.auto_locator.type    = false;
            GMW.auto_locator.success = false;
            GMW.auto_locator.failed  = false;
        }
    },

    /**
     * Page locator success callback function
     * 
     * @param  {[type]} results [description]
     * @return {[type]}         [description]
     */
    page_locator_success : function( address_fields, results ) {

        GMW.auto_locator.status   = false;
        GMW.auto_locator.callback = false;
        GMW.auto_locator.type     = false;

        // submit current location hidden form
        setTimeout(function() {
            jQuery( '#gmw-current-location-form' ).submit();             
        }, 500); 
    },

    /**
     * GEO my WP Form funcitons.
     *
     * triggers only when at least one form presents on the page
     * 
     * @return {[type]} [description]
     */
    form_functions : function() {

        // set chosen for GEO my WP form elements
        if ( jQuery().chosen ) {
            jQuery( '.gmw-chosen' ).chosen();
        }

        // hide locator icon if browser does not support navigation
        if ( ! navigator.geolocation ) {
            jQuery('.gmw-locator-btn-wrapper').hide();
        }

        // remove no address error class from input field on focus
        jQuery( '.gmw-address' ).focus(function() {
            jQuery( this ).removeClass( 'gmw-no-address-error' );
        });
     
        // remove hidden coordinates when address field value changes
        jQuery( '.gmw-address' ).keyup( function () { 
            jQuery( this ).closest( 'form' ).find( '.gmw-lat' ).val('');
            jQuery( this ).closest( 'form' ).find( '.gmw-lng' ).val('');
        });

        // per page dropdown
        jQuery( '.gmw-per-page' ).change( function() {

            thisValue    = jQuery(this).val();
            ppData       = jQuery(this).data();
            totalResults = ppData['total_results'];
            lastPage     = Math.ceil( totalResults / thisValue );
            newPaged     = ( ppData['paged'] > lastPage || lastPage == 1 ) ? lastPage : ppData['paged'];

            // generate new URL based on new page values
            if ( ppData['gmw_post'] == 0 ) {  
                
                //page load submission   
                window.location.href = window.location.href + '?' + ppData['url_px'] + 'auto=auto&' + ppData['url_px'] + 'per_page=' + thisValue + '&' + ppData['url_px'] + 'form=' + ppData['form_id'] + '&' + ppData['page_name'] + '='+ newPaged;                            
            
            } else {
                
                // new URl for form submission
                window.location.href = location.href.replace( ppData['url_px'] + 'per_page=' + ppData['per_page'], ppData['url_px'] + 'per_page=' + thisValue ).replace( '&' + ppData['page_name'] + '=' + ppData['paged'], '&' + ppData['page_name'] + '=' + newPaged ) + '&' + ppData['page_name'] + '=' + newPaged;
            }
        });

        // When click on locator button in a form
        jQuery( '.gmw-locate-btn' ).click( function() {
            GMW.form_locator( jQuery( this ) );
        });

        // submit form when click enter in any input text field of GEO my WP form
        jQuery( '.gmw-form input[type="text"]' ).keyup( function( event ){
            
            // check if "Enter" key pressed
            if ( event.keyCode == 13 ) {

                // run form submission function
                GMW.form_submission( jQuery( this ).closest( 'form' ), event );
            }
        });

        // on form submission
        jQuery( '.gmw-form' ).submit( function( event ) {

            // run form submission
            GMW.form_submission( jQuery( this ), event );
        });
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
        GMW.form_submission.status = true;
        GMW.form_submission.form   = form;
        GMW.form_submission.id     = GMW.form_submission.form.find( '.gmw-form-id' ).val();

        if ( GMW.form_submission.submit ==  true ) {
            return true;
        } 

        // prevent form submission. We need to run some funcitons.
        event.preventDefault();
        
        // set the "paged" value to first page.
        jQuery( '.gmw-paged-' + GMW.form_submission.id ).val( '1' );
   
        // generate the address value from a single address field
        if ( GMW.form_submission.form.find( '.gmw-address' ).hasClass( 'gmw-full-address' ) ) {
            
            address = jQuery( '#gmw-address-' + GMW.form_submission.id ).val();
        
        // otherwise, get from from multiple fields
        } else {

            address = [];

            GMW.form_submission.form.find( '.gmw-address' ).each( function() {
                address.push( jQuery( this ).val() );
            });

            address = address.join(' ');               
        }

        // if address field is empty.
        if ( ! jQuery.trim( address ).length ) {

            var addressField = GMW.form_submission.form.find( '.gmw-address' );
            
            // check if address is mendatory and if so show error and abort submission.
            if ( addressField.hasClass( 'mandatory') ) {

                // add error class to address fields
                if ( ! addressField.hasClass( 'gmw-no-address-error' ) ) {
                    addressField.toggleClass( 'gmw-no-address-error' );
                }

                // stop everything. Do not submit the form.
                return false;

            // otherwise submit the form
            } else {
                
                GMW.form_submission.submit = true;
                GMW.form_submission.form.submit(); 

                return false;
            }
        }
       
        // When Client-side geocoder is enabled
        if ( GMW.options.general_settings.js_geocode == 1 ) {

            // check if hidden coords exists. if so no need to geocode the address again and we can submit the form with the information we already have.
            if ( jQuery( '#gmw-lat-' + GMW.form_submission.id ).val() != '' && jQuery( '#gmw-lng-' + GMW.form_submission.id ).val() != '' ) {            
               
                GMW.form_submission.submit = true;
                GMW.form_submission.form.submit(); 

                return false;
            }
       
            // geocoder the address entered
            GMW.geocoder( address, GMW.form_geocoder_success, GMW.geocoder_failed );

        // Otherwise, no geocoding needed. Submit the form!
        } else {    

            GMW.form_submission.submit = true;
            GMW.form_submission.form.submit(); 

            return false;    
        }
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
    form_geocoder_success : function( results ) {

        // add coordinates to hidden fields
        jQuery( '#gmw-lat-' + GMW.form_submission.id ).val( results[0].geometry.location.lat().toFixed(6) );
        jQuery( '#gmw-lng-' + GMW.form_submission.id ).val( results[0].geometry.location.lng().toFixed(6) );

        // submit the form
        setTimeout(function() {

            GMW.form_submission.submit = true;
            GMW.form_submission.form.submit();  
        }, 500);

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
    form_locator : function( locator ) {

        // set the locator vars.
        GMW.form_locator.status = true;
        GMW.form_locator.button = locator;
        GMW.form_locator.id     = locator.attr( 'data-form_id' );
        GMW.form_locator.loader = jQuery( '#gmw-locate-loader-' + GMW.form_locator.id );
        GMW.form_locator.submit = ( locator.attr( 'data-locator_submit' ) == 1 ) ? true : false;

        // clear hidden coordinates
        jQuery( '#gmw-lat-' + GMW.form_locator.id ).val('');
        jQuery( '#gmw-lng-' + GMW.form_locator.id ).val('');
        
        // hide locator button
        GMW.form_locator.button.fadeOut( 'fast', function() {

            // show spinning loader 
            GMW.form_locator.loader.fadeIn( 'fast', function() {
              
                // disabled all text fields and submit buttons while working
                jQuery( '.gmw-form input[type="text"]' ).attr('disabled', 'disabled');
                
                // jQuery( '.gmw-address' ).attr('disabled', 'disabled');
                jQuery( '.gmw-submit' ).attr('disabled', 'disabled');

                // run auto locator
                GMW.auto_locator( 'form_locator', GMW.form_locator_success, GMW.form_locator_failed );
            });
        });
    },

    /**
     * Form locator success callback function
     * 
     * @param  {object} address_fields address fields collector
     * @param  {object} results        original location fields object returned by geocoder
     * 
     * @return {[type]}                [description]
     */
    form_locator_success : function( address_fields, results ) {

        // enabled all text fields and submit buttons
        jQuery( '.gmw-form input[type="text"]' ).removeAttr('disabled');
            
        // Enable submit buttons
        jQuery( '.gmw-submit' ).removeAttr( 'disabled' );

        // add coords value to hidden fields
        jQuery( '#gmw-lat-' + GMW.form_locator.id ).val( results[0].geometry.location.lat().toFixed(6) );
        jQuery( '#gmw-lng-' + GMW.form_locator.id ).val( results[0].geometry.location.lng().toFixed(6) );

        //dynamically fiil-out the address fields of the form
        if ( jQuery( '#gmw-address-' + GMW.form_locator.id ).hasClass( 'gmw-full-address' ) ) {
            
            jQuery( '#gmw-address-' + GMW.form_locator.id ).val( address_fields.formatted_address );
        
        } else {        

            jQuery( '#gmw-form-' + + GMW.form_locator.id ).find( '.gmw-saf-street' ).val( address_fields.street );
            jQuery( '#gmw-form-' + + GMW.form_locator.id ).find( '.gmw-saf-city' ).val( address_fields.city );
            jQuery( '#gmw-form-' + + GMW.form_locator.id ).find( '.gmw-saf-state' ).val( address_fields.region_name );
            jQuery( '#gmw-form-' + + GMW.form_locator.id ).find( '.gmw-saf-zipcode' ).val( address_fields.postcode );
            jQuery( '#gmw-form-' + + GMW.form_locator.id ).find( '.gmw-saf-country' ).val( address_fields.country_code );
        }
       
        // if form locator set to auto submit form. 
        if ( GMW.form_locator.submit ) {
                
            setTimeout( function() {
                jQuery( '#gmw-submit-' + GMW.form_locator.id ).click();
            }, 1500);
            
        } else {
            
            GMW.form_locator_done();
        }
    },

    /**
     * Form Locator failed callback function
     * @param  {[type]} status [description]
     * @return {[type]}        [description]
     */
    form_locator_failed : function( status ) {

        // alert failed message
        alert( 'Geocoder failed due to: ' + status );

        GMW.form_locator_done();
    },

    /**
     * Form locator done callback function. 
     * @return {[type]} [description]
     */
    form_locator_done : function() {

         // hide spinning loader
        GMW.form_locator.loader.fadeOut( 'fast',function() {
            
            // show locator button
            GMW.form_locator.button.fadeIn( 'fast' );

            // enabled all text fields and submit buttons
            jQuery( '.gmw-form input[type="text"]' ).removeAttr('disabled');
            
            // Enable submit buttons
            jQuery( '.gmw-submit' ).removeAttr('disabled');

            // change locator status
            GMW.form_locator.status = false;
            GMW.form_locator.button = false;
            GMW.form_locator.id     = false;
            GMW.form_locator.loader = false;
            GMW.form_locator.submit = false;
        }); 
    }
}

jQuery( document ).ready( function( $ ) {
   GMW.init(); 
});