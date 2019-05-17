/**
 * Current location extension.
 * 
 * @type {Object}.
 */
if ( jQuery( '.gmw-current-location-wrapper' ).length ) {

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

	        // clear location
	        jQuery( '.gmw-cl-clear-location-trigger' ).click( function( event ) {
	            
	            event.preventDefault();
	            
	            GMW_Current_Location.object_id = jQuery( this ).closest( '.gmw-cl-form-wrapper' ).data( 'element-id' );

	            GMW_Current_Location.delete_location();
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

	    delete_location : function( element ) {

	    	// delete current location cookies
	    	jQuery.each( GMW.current_location_fields, function( index, field ) {
	    		GMW.delete_cookie( 'gmw_ul_' + field );
	    	});

	    	GMW.delete_cookie( 'gmw_autolocate' );

	    	jQuery( '.gmw-cl-address .address-holder' ).html( '' );
	    	jQuery( '.gmw-cl-element.gmw-cl-address-wrapper' ).slideUp();
	        jQuery( '.gmw-map-wrapper.current_location' ).fadeOut();
	        jQuery( '.gmw-cl-form-trigger, .gmw-cl-clear-location-trigger' ).slideUp();
	        jQuery( '.gmw-cl-form' ).slideDown();
	    },

	    /**
	     * Save current location via ajax
	     * 
	     * @return {[type]} [description]
	     */
	    save_location : function() {

	        GMW_Current_Location.ajax = jQuery.ajax({
	            type     : 'POST',
	            url      : gmwVars.ajaxUrl,
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

	               		var mapId        = GMW_Current_Location.object_id;
	               		var gmwMap       = GMW_Maps[mapId];
	                	var new_position = gmwMap.latLng( response.lat, response.lng );
	                	
	                	gmwMap.setMarkerPosition( gmwMap.user_marker, new_position, gmwMap );
						gmwMap.map.panTo( new_position );
	                }

	                newAddress = jQuery( '#gmw-cl-address-input-' + GMW_Current_Location.object_id ).val();

	                // change the address in the current location element
	                jQuery( '.gmw-cl-address .address-holder' ).html( newAddress );
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

	        	// update via ajax
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
	};
}

jQuery( document ).ready( function( $ ) {
	if ( $( '.gmw-current-location-wrapper' ).length ) {
   		GMW_Current_Location.init();
   	}
});
