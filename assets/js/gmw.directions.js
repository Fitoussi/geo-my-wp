/**
 * Directions Providers.
 * 
 * @type {Object}
 */
var GMW_Directions_Providers = {

	'google_maps' : function( elementId ) {

		// initialize new service.
		if ( typeof ( GMW_Directions[elementId] ) === 'undefined' ) {
			
			GMW_Directions[elementId] = { 
				id 		: elementId,
				service : new google.maps.DirectionsService(),
				display : new google.maps.DirectionsRenderer( { suppressMarkers: false } )
			};
		}
		
		var directions = GMW_Directions[elementId],
		    gmwMap;
		
		// look for map with the same object ID as the directions form.
		if ( typeof( GMW_Maps[elementId] ) !== 'undefined' ) {
			
			gmwMap = GMW_Maps[elementId];

			// Clear existing directions.
			if ( gmwMap.directions != null ) {
				gmwMap.directions.setMap( null );
				gmwMap.directions = null;
			}

			gmwMap.directions = directions.display;	
			gmwMap.directions.setMap( gmwMap.map );
		}

		// set direction panel
		directions.display.setPanel( document.getElementById( 'gmw-directions-panel-wrapper-' + elementId ) );
		
	  	var start 	      = jQuery( '#origin-field-' + elementId ).val();
	  	var end 	      = jQuery( '#destination-field-' + elementId ).val();
		var travelMode    = jQuery( '#travel-mode-options-' + elementId +' li a.active' ).attr( 'id' );
		var unitsSystem   = jQuery( '#get-directions-form-' + elementId +' .unit-system-trigger:checked' ).val();
		var avoidHighways = ( jQuery( '#route-avoid-highways-trigger-' + elementId ).is( ':checked' ) ) ? true : false;
		var avoidTolls 	  = ( jQuery( '#route-avoid-tolls-trigger-' + elementId ).is( ':checked' ) ) ? true : false;
		var request 	  = {
			origin 					 : start,
			destination 			 : end,
			travelMode  			 : google.maps.TravelMode[travelMode],
			unitSystem  			 : google.maps.UnitSystem[unitsSystem],
			provideRouteAlternatives : true,
			avoidHighways 		     : avoidHighways,
			avoidTolls 				 : avoidTolls
		};

	  	directions.service.route( request, function( response, status ) {

			if ( status == google.maps.DirectionsStatus.OK ) {

		  		directions.display.setDirections( response );	      					      		
			
			} else {

				var errorMessage;

		      	// alert an error errorMessage when the route could nog be calculated.
		      	if ( status == 'ZERO_RESULTS' ) {

		    	  	errorMessage = 'No route could be found between the origin and destination.';
		      	
		      	} else if (status == 'UNKNOWN_ERROR') {
		    	  
		    	  	errorMessage = 'A directions request could not be processed due to a server error. The request may succeed if you try again.';
		      
		      	} else if (status == 'REQUEST_DENIED') {
		    	  	
		    	  	errorMessage = 'This webpage is not allowed to use the directions service.';
		      	
		      	} else if (status == 'OVER_QUERY_LIMIT') {
		    		
		    		errorMessage = 'The webpage has gone over the requests limit in too short a period of time.';
		      
		      	} else if (status == 'NOT_FOUND') {
		    	  
		    	  	errorMessage = 'At least one of the origin, destination, or waypoints could not be geocoded.';
		      
		      	} else if (status == 'INVALID_REQUEST') {
		    	  
		    	  	errorMessage = 'The DirectionsRequest provided was invalid.';         
		      	
		      	} else {
		    	  
		    	  	errorMessage = "There was an unknown error in your request. Requeststatus: nn" + status;
		      	}

		      	jQuery( '#gmw-directions-panel-wrapper-' + elementId ).html( '<div id="error-message">' + errorMessage + '</div>' );
			}
			
			jQuery( '#get-directions-submit-' + elementId ).removeClass( 'gmw-icon-spin-light animate-spin' ).addClass( 'gmw-icon-search' );
	  	});
	}
}

/**
 * Directions objects.
 * 
 * @type {Object}
 */
var GMW_Directions = {};

/**
 * Get directions function.
 * 
 * @param  {[type]} elementId [description]
 * @return {[type]}           [description]
 */
function gmwGetDirections( elementId, provider ) {

	provider = provider || gmwVars.mapsProvider;

	// Loading icon.
	jQuery( '#get-directions-submit-' + elementId ).removeClass( 'gmw-icon-search' ).addClass( 'gmw-icon-spin-light animate-spin' );

	// clear directions panel
	jQuery( '#gmw-directions-panel-wrapper-' + elementId ).html( '' );

	if ( typeof( GMW_Directions_Providers[provider] ) === 'undefined' ) {
		return console.log( provider + ' directions provider not found' );
	}

	GMW_Directions_Providers[provider]( elementId );
}

jQuery( document ).ready(function($) {

	// on get direction submit
	$( document ).on( 'click', '.get-directions-submit', function(e) {
		
		e.preventDefault();

		// get the element ID
		elementId = $( this ).closest( 'form' ).find( '.element-id' ).val();

		// run the directions functions
		gmwGetDirections( elementId  );
	});
	
	// if press enter in address field.
	$( document ).on( 'keypress', '.gmw-directions-form-wrapper .address-field-wrapper input', function(e) {
	
		// verify enter key press
		if ( e.keyCode == 13 ) {	

			// prevent it from submitting the form
			e.preventDefault();	
			
			// get the element ID
			elementId = $( this ).closest( 'form' ).find( '.element-id' ).val();

			// run the directions functions
			gmwGetDirections( elementId );
	    }
	});

	$( document ).on( 'click', '.gmw-directions-form-wrapper .travel-mode-options a.travel-mode-trigger', function(e) {		
		
		e.preventDefault();
		
		$('.travel-mode-options li a').removeClass('active');
		
		$( this ).addClass( 'active' );

		elementId = $( this ).closest( 'form' ).find( '.element-id' ).val();

		gmwGetDirections( elementId  );
	});

	$( document ).on( 'change', '.gmw-directions-form-wrapper .unit-system-options .unit-system-trigger', function(e) {

		elementId = $( this ).closest( 'form' ).find( '.element-id' ).val();

		$( '#unit-system-options-' + elementId + ' label' ).removeClass( 'active' );
		
		$( this ).closest( 'label' ).addClass( 'active' );

		gmwGetDirections( elementId  );
	});	

	$( document ).on( 'click', '.gmw-iw-close-button', function(e) {	
		
		if ( typeof directionsDisplay !== 'undefined' ) {
			
			directionsDisplay.setMap(null);
		}
	});

	$( document ).on( 'click', '.gmw-directions-form-wrapper .route-avoid-trigger', function(e) {		
		
		elementId = $(this).closest('form').find('.element-id').val();
		
		gmwGetDirections( elementId  );
	});
});
