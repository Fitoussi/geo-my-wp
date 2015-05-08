function gmwSlCalcRoute( elementId ) {
	
	//remove directions if exists from another window
	if ( typeof directionsDisplay !== 'undefined' ) {
		directionsDisplay.setMap(null);
	}
	
	//var directionsDisplay;
	directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: false});		
	directionsService = new google.maps.DirectionsService();
	
	if (gmwMapObjects[elementId] != undefined ) {
		directionsDisplay.setMap(gmwMapObjects[elementId]['map']);
	}
	
	directionsDisplay.setPanel(document.getElementById('directions-panel-wrapper-'+elementId));
	jQuery('#directions-panel-wrapper-'+elementId).html('');

  	var start 	      = jQuery('#gmw-directions-start-point-'+elementId).val();
  	var end 	      = jQuery('#gmw-directions-end-coords-'+elementId).val();
	var travelMode    = jQuery('#travel-mode-options-'+elementId+' li a.active').attr('id');
	var unitsSystem   = jQuery('#gmw-get-directions-form-'+elementId+' .unit-system-trigger:checked').val();
	var avoidHighways = ( jQuery('#route-avoid-highways-trigger-'+elementId).is(':checked') ) ? true : false;
	var avoidTolls 	  = ( jQuery('#route-avoid-tolls-trigger-'+elementId).is(':checked') ) ? true : false;
	
	var request = {
		origin: start,
		destination: end,
		travelMode: google.maps.TravelMode[travelMode],
		unitSystem: google.maps.UnitSystem[unitsSystem],
		provideRouteAlternatives:true,
		avoidHighways:avoidHighways,
		avoidTolls:avoidTolls
	};

  	directionsService.route(request, function(response, status) {
		if ( status == google.maps.DirectionsStatus.OK ) {
	  		directionsDisplay.setDirections(response);		      					      		
		} else {
	      // alert an error errorMessage when the route could nog be calculated.
	      if (status == 'ZERO_RESULTS') {
	    	  var errorMessage = 'No route could be found between the origin and destination.';
	      } else if (status == 'UNKNOWN_ERROR') {
	    	  var errorMessage = 'A directions request could not be processed due to a server error. The request may succeed if you try again.';
	      } else if (status == 'REQUEST_DENIED') {
	    	  var errorMessage = 'This webpage is not allowed to use the directions service.';
	      } else if (status == 'OVER_QUERY_LIMIT') {
	    	  var errorMessage = 'The webpage has gone over the requests limit in too short a period of time.';
	      } else if (status == 'NOT_FOUND') {
	    	  var errorMessage = 'At least one of the origin, destination, or waypoints could not be geocoded.';
	      } else if (status == 'INVALID_REQUEST') {
	    	  var errorMessage = 'The DirectionsRequest provided was invalid.';         
	      } else {
	    	  var errorMessage = "There was an unknown error in your request. Requeststatus: nn"+status;
	      }
	
	      jQuery('#directions-panel-wrapper-'+elementId).html('<div id="error-message">'+errorMessage+'</div>');
		}
  	});
}
jQuery(document).ready(function($) {
	//gmwSlCalcRoute();

	$(document).on('click', '.get-directions-submit', function(e) {
		e.preventDefault();
		//elementId 	  = $(this).closest('.gmw-sl-wrapper').find('.gmw-directions-form-id').val();
		elementId = $(this).closest('.gmw-sl-live-directions-wrapper').find('.gmw-sl-directions-element-id').val();
		gmwSlCalcRoute( elementId  );
	});
	
	$(document).on('keypress', '.gmw-directions-start-point', function(e) {
		if (e.keyCode == 13){	
			e.preventDefault();	
			//elementId 	  = $(this).closest('.gmw-sl-wrapper').find('.gmw-directions-form-id').val();
			elementId = $(this).closest('.gmw-sl-live-directions-wrapper').find('.gmw-sl-directions-element-id').val();
			gmwSlCalcRoute( elementId  );
	    }
	});

	$(document).on('click', '.travel-mode-options a.travel-mode-trigger', function(e) {		
		e.preventDefault();
		$('.travel-mode-options li a').removeClass('active');
		$(this).addClass('active');
		//elementId 	  = $(this).closest('.gmw-sl-wrapper').find('.gmw-directions-form-id').val();
		elementId = $(this).closest('.gmw-sl-live-directions-wrapper').find('.gmw-sl-directions-element-id').val();
		gmwSlCalcRoute( elementId  );
	});

	$(document).on('change', '.unit-system-options .unit-system-trigger', function(e) {		
		//elementId 	  = $(this).closest('.gmw-sl-wrapper').find('.gmw-directions-form-id').val();
		elementId = $(this).closest('.gmw-sl-live-directions-wrapper').find('.gmw-sl-directions-element-id').val();
		gmwSlCalcRoute( elementId  );
	});	

	$(document).on('click', '.gmw-iw-close-button', function(e) {	
		if ( typeof directionsDisplay !== 'undefined' ) {
			directionsDisplay.setMap(null);
		}
	});

	$(document).on('click', '.route-avoid-trigger', function(e) {		
		//elementId 	  = $(this).closest('.gmw-sl-wrapper').find('.gmw-directions-form-id').val();
		elementId = $(this).closest('.gmw-sl-live-directions-wrapper').find('.gmw-sl-directions-element-id').val();
		gmwSlCalcRoute( elementId  );
	});
});