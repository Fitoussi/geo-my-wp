jQuery( document ).ready( function( $ ) {

	/**
	 * Current location add-on
	 * 
	 * @type {Object}
	 */
	var GMW_Current_Location = {

	    // alert messages
	    messages : {
	    	'geocoder_failed' : 'Geocoder failed due to: ',
	    	'no_address'   	  : 'Please enter an address.'
	    },

	    // the processed object ID
	    object_id : false,
	   	
	   	// ajax object
	   	ajax : {},

	    /**
	     * Initiate the current location functions
	     * 
	     * @return {[type]} [description]
	     */
	    init : function() {

	        // show / hide current location form on click
	        jQuery( '.gmw-cl-form-trigger' ).click( function( event ) {
	            
	            event.preventDefault();
	            
	            // toggle form
	            jQuery( this ).closest( '.gmw-cl-form-wrapper' ).find( 'form' ).slideToggle();
	        });
	        
	        // when autolocating user
	        jQuery( '.gmw-cl-locator-trigger' ).click( function( event ) {
	            
	            // prevent form submission
	            event.preventDefault();

	            // get the element ID
	            GMW_Current_Location.object_id = jQuery( this ).closest( 'form' ).data( 'element-id' );
	            
	            // disbale all current location address field
	            jQuery( '.gmw-cl-address-input' ).prop( 'readonly', true );

	            // show loader icon
	            jQuery( '.gmw-cl-form-submit' ).removeClass( 'gmw-icon-search' ).addClass( 'gmw-icon-spin-light animate-spin' );

	            // get loading message
	            var loadingMessage = jQuery( '#gmw-cl-message-' + GMW_Current_Location.object_id ).data( 'loading_message' );

	            // verify if need to show loading message
	            if ( loadingMessage != 0 && loadingMessage.length !== 0 ) {

	           		// show loading message
	            	jQuery( '#gmw-cl-respond-wrapper-' + GMW_Current_Location.object_id ).slideDown( 'fast', function() {
	               		jQuery( '#gmw-cl-message-' + GMW_Current_Location.object_id ).addClass( 'locating' ).html( loadingMessage );     
	            	});
	            }

	            // run the auto-locator
	            GMW.auto_locator( 'cl_locator', GMW_Current_Location.auto_locator_success, GMW_Current_Location.geocoder_failed );
	        });

	        // when hit enter in the address field of the current location form
	        jQuery( '.gmw-cl-address-input' ).bind( 'keypress', function( event ){
	            
	            if ( event.keyCode == 13 ) {
	            
	                jQuery( this ).closest( 'form' ).find( '.gmw-cl-form-submit' ).click();
	            }
	        });

	        // on current location submit click
	        jQuery( '.gmw-cl-form-submit' ).click( function( event ) {
	            
	            event.preventDefault();

	            // get the element ID
	            GMW_Current_Location.object_id = jQuery( this ).closest( 'form' ).data( 'element-id' );

	            // make sure address field is not empty
	            if ( jQuery( '#gmw-cl-address-input-' + GMW_Current_Location.object_id ).val().length === 0 ) {
	                
	                alert( GMW_Current_Location.messages.no_address  );
	                
	                return false;
	            }
	            
	            // disbale all current location address fields
	           	jQuery( '.gmw-cl-address-input' ).prop( 'readonly', true );

	           	// show loader icon
	            jQuery( '.gmw-cl-form-submit' ).removeClass( 'gmw-icon-search' ).addClass( 'gmw-icon-spin-light animate-spin' );

	            // get loading message
	            var loadingMessage = jQuery( '#gmw-cl-message-' + GMW_Current_Location.object_id ).data( 'loading_message' );

	            // verify if need to show loading message
	            if ( loadingMessage != 0 && loadingMessage.length !== 0 ) {

	           		// show loading message
	            	jQuery( '#gmw-cl-respond-wrapper-' + GMW_Current_Location.object_id ).slideDown( 'fast', function() {
	               		jQuery( '#gmw-cl-message-' + GMW_Current_Location.object_id ).addClass( 'locating' ).html( loadingMessage );     
	            	});
	            }
	            
	            // get addres value
	            var address = jQuery( '#gmw-cl-address-input-' + GMW_Current_Location.object_id ).val();
	          	
	          	// geocode the address
	            GMW.geocoder( address, GMW_Current_Location.address_geocoder_success, GMW_Current_Location.geocoder_failed );
	        });
	    },

	    /**
	     * Save current location via ajax
	     * 
	     * @return {[type]} [description]
	     */
	    save_location : function() {

	        GMW_Current_Location.ajax = jQuery.ajax({
	            type     : 'POST',
	            url      : gmwAjaxUrl,
	            dataType : 'json',
	            data     : {
	                action      : 'gmw_update_current_location',
	                form_values : jQuery( '#gmw-current-location-hidden-form' ).serialize(), 
	               	security 	: gmw_cl_nonce,
	            },

	            // on save success
	            success : function( response ) {
	     
	            	// look for map object and if exists update it based on the new current location
	               if ( typeof GMW_Maps != 'undefined' && typeof GMW_Maps[GMW_Current_Location.object_id] != 'undefined' ) {

	                	var map_id 		 = GMW_Current_Location.object_id;
	                	var new_position = new google.maps.LatLng( response.lat, response.lng );
	                	
	                	GMW_Maps[map_id].user_marker.setPosition( new_position );
 						GMW_Maps[map_id].map.panTo( new_position );
	                }

	                newAddress = jQuery( '#gmw-cl-address-input-' + GMW_Current_Location.object_id ).val();

	                // change the address in the current location element
	                jQuery( '.gmw-cl-address .address-holder' ).html( newAddress )
	            }

	        // if failed
	        }).fail( function ( response ) {    

	            //display messages in console
	            if ( window.console && window.console.log ) {

	                if ( response.responseText ) {
	                    console.log( response.responseText );
	                }

	                console.log( response );
	            }
	        });

	        // when ajax done
	        GMW_Current_Location.ajax.done( function ( response ) {

	        	setTimeout( function() {

		            jQuery( '.gmw-cl-respond-wrapper' ).slideUp();
		            //jQuery( '#gmw-cl-message-' + GMW.current_location.id ).removeClass( 'error' ).html('');
		            jQuery( '.gmw-cl-address-input' ).prop( 'readonly', false );
		            jQuery( '.gmw-cl-form-submit' ).removeClass( 'gmw-icon-spin-light animate-spin' ).addClass( 'gmw-icon-search' );

		        }, 500 ); 
	        });   
	    },

	    submit_location : function() {

	    	// allow a few seconds for the location fields to populate in the hidden form
	        setTimeout( function() {

	        	// udpate via ajax
	        	if ( jQuery( '#gmw-cl-form-wrapper-' + GMW_Current_Location.object_id ).data( 'ajax_enabled' ) == 1 ) {

					GMW_Current_Location.save_location();  
	        	
	        	// page load update
	        	} else {

	        		jQuery( '#gmw-current-location-hidden-form' ).submit();
	        	}	             

	        }, 3500 );    
	    },

	    /**
	     * auto-locator success callbacl function 
	     * 
	     * @param  {[type]} address_fields [description]
	     * @return {[type]}                [description]
	     */
	    auto_locator_success : function( address_fields ) {

	        jQuery( '#gmw-cl-address-input-' + GMW_Current_Location.object_id ).val( address_fields.formatted_address );
	        
	        GMW_Current_Location.submit_location();
	    },

	    /**
	     * address geocoder success callback function 
	     * 
	     * @param  {[type]} results [description]
	     * @return {[type]}         [description]
	     */
	    address_geocoder_success : function( results ) {

	    	// save address field to cookies and current location hidden form
	        GMW.save_location_fields( results );

	        GMW_Current_Location.submit_location();                         
	    },

	    /**
	     * geocoder failed callback function 
	     * 
	     * @param  {[type]} results [description]
	     * @return {[type]}         [description]
	     */
	    geocoder_failed : function( status ) {

	    	jQuery( '#gmw-cl-respond-wrapper-' + GMW_Current_Location.object_id ).slideDown( function() {
	    		jQuery( '#gmw-cl-message-' + GMW_Current_Location.object_id ).addClass( 'error' ).html( GMW_Current_Location.messages.geocoder_failed + status );

	    	});
	        
	        setTimeout( function() {
	            jQuery( '.gmw-cl-respond-wrapper' ).slideUp();
	            jQuery( '.gmw-cl-message' ).removeClass( 'error' ).html( '' );
	            jQuery( '.gmw-cl-address-input' ).prop( 'readonly',false );
	            jQuery( '.gmw-cl-form-submit' ).removeClass( 'gmw-icon-spin-light animate-spin').addClass( 'gmw-icon-search' );
	        }, 3000 );                       
	    },
	}

   GMW_Current_Location.init(); 
});