jQuery( document ).ready( function($) {
	
	/**
	 * Clear coords when address changes
	 * 
	 * @return {[type]} [description]
	 */
	jQuery( '.gmw-bpsgeo-address-field' ).on( 'change', function( event ) {
		jQuery( this ).closest( '.gmw-bpsgeo-location-fields-inner' ).find( '.gmw-bpsgeo-lat, .gmw-bpsgeo-lng' ).val( '' );
	});

	/**
	 * Verify coords and geocode on form submission
	 *
	 * @return {[type]}       [description]
	 */
	jQuery( '.bps-form' ).on( 'submit' , function( event ) {

		jQuery( this ).addClass( 'gmw-bpsgeo-submitted' );

		var thisForm = jQuery( this );
		var address  = thisForm.find( '.gmw-bpsgeo-address-field' ).val();
		var lat      = thisForm.find( '.gmw-bpsgeo-lat' ).val();
		var lng      = thisForm.find( '.gmw-bpsgeo-lng' ).val();

		if ( jQuery.trim( address ).length && ( ! jQuery.trim( lat ).length || ! jQuery.trim( lng ).length ) ) {

			event.preventDefault();

			GMW.geocoder( address, gmw_bpsgeo_geocoder_success, gmw_bpgeo_geocoder_failed );
		}
	});

	/**
	 * Geocoding successful
	 * 
	 * @param  {[type]} results [description]
	 * @return {[type]}         [description]
	 */
	function gmw_bpsgeo_geocoder_success( result, response ) {

		var thisForm = jQuery( '.bps-form.gmw-bpsgeo-submitted' );
		var lat      = result.latitude;
		var lng      = result.longitude;

		thisForm.find( '.gmw-bpsgeo-lat' ).val( lat );
		thisForm.find( '.gmw-bpsgeo-lng' ).val( lng );

		setTimeout( function() {
			thisForm.removeClass( 'gmw-bpsgeo-submitted' );
			thisForm.submit();
		}, 500 );
	}

	/**
	 * Ggeocoding failed function.
	 *
	 * @param  {[type]} status error message
	 * @return {[type]}        [description]
	 */
	function gmw_bpgeo_geocoder_failed( status ) {

		var thisForm = jQuery( '.bps-form.gmw-bpsgeo-submitted' );

		thisForm.find( '.gmw-bpsgeo-lat' ).val( '' );
		thisForm.find( '.gmw-bpsgeo-lng' ).val( '' );

		thisForm.removeClass( 'gmw-bpsgeo-submitted' );

		alert( "We couldn't verify the address you provided. Please try a different address." ); 

		return false;
	}

	// When click on the locator button
    jQuery( '.gmw-bpsgeo-locator-button' ).click( function() {

    	var thisForm = jQuery( this ).closest( 'form' );

    	/**
    	 * Locator Success.
    	 *
    	 * @param  {[type]} result [description]
    	 * @return {[type]}        [description]
    	 */
    	GMW.locator_button_success = function( result ) {
    
    		// add coords value to hidden fields
        	thisForm.find( '.gmw-bpsgeo-lat' ).val( result.latitude );
        	thisForm.find( '.gmw-bpsgeo-lng' ).val( result.longitude );
    		thisForm.find( '.gmw-bpsgeo-address-field' ).val( result.formatted_address );
    		
    		jQuery( '.gmw-bpsgeo-locator-button' ).removeClass( 'animate-spin' );
    		
    		/*setTimeout( function() {
    			thisForm.submit();
    		}, 500 ); */
    	};

    	/**
    	 * Locator failed.
    	 *
    	 * @param  {[type]} status [description]
    	 * @return {[type]}        [description]
    	 */
    	GMW.locator_button_failed = function( status ) {
    			
    		// alert failed message
        	alert( 'Geocoder failed due to: ' + status );

        	$( '.gmw-bpsgeo-locator-button' ).removeClass( 'animate-spin' );
    	};

        GMW.locator_button( jQuery( this ) );
    });
});
