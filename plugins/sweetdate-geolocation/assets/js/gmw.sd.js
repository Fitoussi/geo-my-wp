/**
 * Save form values in cookies on form submission
 * 
 * @param  {[type]} r [description]
 * @param  {[type]} t [description]
 * @return {[type]}   [description]
 */
jQuery( document ).ajaxStart( function() {

	//jQuery( '#gmw-kleo-geo-form-loader' ).fadeIn( 'slow' );

	var address = jQuery( '#gmw-address-field-sdate-geo' ).val();

	if ( jQuery.trim( address ).length ) {
		GMW.set_cookie( 'gmw_sdate_geo_address', address, 1 );
		GMW.set_cookie( 'gmw_sdate_geo_lat', jQuery( '#gmw-lat-sdate-geo' ).val(), 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', jQuery( '#gmw-lng-sdate-geo' ).val(), 1 );	
	} else {
		GMW.set_cookie( 'gmw_sdate_geo_address', '', 1 );
		GMW.set_cookie( 'gmw_sdate_geo_lat', '', 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', '', 1 );	
	}
	
	GMW.set_cookie( 'gmw_sdate_geo_radius', jQuery( '#gmw-sdate-geo-radius-dropdown' ).val(), 1 );
	GMW.set_cookie( 'gmw_sdate_geo_orderby', jQuery( '#gmw-sdate-geo-orderby-dropdown' ).val(), 1 );
});

jQuery( document ).ready( function( $ ) {
	
	// When click on the locator button
    jQuery( '.gmw-locator-button' ).click( function() {

    	GMW.locator_button_success = function( result ) {
    			
    		// add coords value to hidden fields
        	$( '#gmw-lat-sdate-geo' ).val( result.latitude );
        	$( '#gmw-lng-sdate-geo' ).val( result.longitude );
    		$( '#gmw-address-field-sdate-geo' ).val( result.formatted_address );
    		$( '.gmw-locator-button' ).removeClass( 'animate-spin' );
    		
    		setTimeout( function() {
    			$( '#gmw-address-field-sdate-geo' ).closest( 'form' ).submit();
    		}, 500 );
    	}

    	GMW.locator_button_failed = function( status ) {
    			
    		// alert failed message
        	alert( 'Geocoder failed due to: ' + status );

        	$( '.gmw-locator-button' ).removeClass( 'animate-spin' );
    	}

        GMW.locator_button( jQuery( this ) );
    });

	/**
	 * In case for submitted via page load
	 *
	 * @return {[type]}  [description]
	 */
	$( '#horizontal_search' ).on( 'submit', function( e ) {

		//if ( $( '#gmw-address-field-sdate-geo' ).hasClass( 'enter-clicked') ) {
			//e.preventDefault();

		//	alert('ma')
		//	return false;
		//};
//alert('mama')
		var address = jQuery( '#gmw-address-field-sdate-geo' ).val();

		GMW.set_cookie( 'gmw_sdate_geo_address', address, 1 );
		GMW.set_cookie( 'gmw_sdate_geo_lat', jQuery( '#gmw-lat-sdate-geo' ).val(), 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', jQuery( '#gmw-lng-sdate-geo' ).val(), 1 );
		GMW.set_cookie( 'gmw_sdate_geo_radius', jQuery( '#gmw-sdate-geo-radius-dropdown' ).val(), 1 );
		//GMW.set_cookie( 'gmw_sdate_geo_units', jQuery( '#gmw-sdate-geo-units-dropdown' ).val(), 1 );
		GMW.set_cookie( 'gmw_sdate_geo_orderby', jQuery( '#gmw-sdate-geo-orderby-dropdown' ).val(), 1 );

		/*alert( GMW.get_cookie( 'gmw_sdate_geo_address' ) )
		alert( GMW.get_cookie( 'gmw_sdate_geo_lat' ) )
		alert( GMW.get_cookie( 'gmw_sdate_geo_lng' ) )
		alert( GMW.get_cookie( 'gmw_sdate_geo_radius' ) )
		alert( GMW.get_cookie( 'gmw_sdate_geo_orderby' ) )*/

		return true;
	});

	/**
	 * When geocoding successful
	 * @param  {[type]} results [description]
	 * @return {[type]}         [description]
	 */
	function gmw_sdate_geo_geocoder_success( result, response ) {

		var lat = result.latitude;
		var lng = result.longitude;

		GMW.set_cookie( 'gmw_sdate_geo_lat', lat, 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', lng, 1 );

		$( '#gmw-lat-sdate-geo' ).val( lat );
		$( '#gmw-lng-sdate-geo' ).val( lng );

		$( '#gmw-address-field-sdate-geo' ).prop( 'disabled', false );
		//$( '#members_search_submit, #groups_search_submit' ).prop( 'disabled', false );

		// if enter key was pressed in address field we need to submit the form
		if ( $( '#gmw-address-field-sdate-geo' ).hasClass( 'enter-clicked' ) ) {
			
			$( '#gmw-address-field-sdate-geo' ).removeClass( 'enter-clicked' );

			setTimeout( function() {
	    		jQuery( '#horizontal_search' ).submit();
	    	}, 500 );
	    }
	}

	/**
	 * if geocoding fails
	 * @param  {[type]} status error message
	 * @return {[type]}        [description]
	 */
	function gmw_sdate_geo_geocoder_failed( status ) {

		GMW.set_cookie( 'gmw_sdate_geo_lat', '', 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', '', 1 );

		jQuery( '#gmw-lat-sdate-geo' ).val( '' );
		jQuery( '#gmw-lng-sdate-geo' ).val( '' );

		$( '#gmw-address-field-sdate-geo' ).prop( 'disabled', false );
		//$( '#members_search_submit, #groups_search_submit' ).prop( 'disabled', false );

		// if enter key was pressed in address field we need to submit the form
		if ( $( '#gmw-address-field-sdate-geo' ).hasClass( 'enter-clicked' ) ) {
			
			$( '#gmw-address-field-sdate-geo' ).removeClass( 'enter-clicked' );

			alert( 'We could not find the address your entered. Please try a different address' );
			//setTimeout( function() {
	    	//	$( '#horizontal_search' ).submit();
	    	//}, 300 );
	    }

		return false;
	}

	/**
	 * move map outside the member list.
	 * We do this because the page uses ajax to update results which 
	 * makes the map disapear
	 */
	$( '#gmw-map-wrapper-sdate_geo' ).detach().insertBefore( '#members-dir-list' );

	// When clicking in address field
	// save the address before changing it
	$( '#gmw-address-field-sdate-geo' ).focus( function () {
        $( this ).attr( 'data-last_address', $( this ).val() );
  	});

	// check if address changed on blur
	$( '#gmw-address-field-sdate-geo' ).focusout( function () {

		// abort if focus out after enter click
		if ( $( '#gmw-address-field-sdate-geo' ).hasClass( 'focusout-enter-clicked' ) ) {

			$( '#gmw-address-field-sdate-geo' ).removeClass( 'focusout-enter-clicked' );

			return;
		}

		// disable submit button in case we need to geocode the address
		//$( '#members_search_submit, #groups_search_submit' ).prop( 'disabled', true );

		var address 	= $( this ).val();
		var lastAddress = $( this ).attr( 'data-last_address' );
		var lat 		= $( '#gmw-lat-sdate-geo' ).val();
		var lng 		= $( '#gmw-lng-sdate-geo' ).val();

		setTimeout( function() {

			// if address changed, geocode it
			if ( $.trim( address ).length && ( address != lastAddress || ! $.trim( lat ).length || ! $.trim( lng ).length ) ) {
				GMW.geocoder( address, gmw_sdate_geo_geocoder_success, gmw_sdate_geo_geocoder_failed );
			} else {
				//$( '#members_search_submit, #groups_search_submit' ).prop( 'disabled', false );
			}
		}, 300 );
  	});

	/**
	 * Geocode addess on enter key in address field
	 * 
	 * @return {[type]} [description]
	 */
	$( '#gmw-address-field-sdate-geo' ).keypress( function( event ){
	
		// clear coords when changing address
	    if ( event.which != 13 ) {

	    	$( '#gmw-lat-sdate-geo' ).val( '' );
			$( '#gmw-lng-sdate-geo' ).val( '' );
	   	
	   	// if enter key pressed, try geocoding the address
	   	} else {
	    	
	    	event.preventDefault();

	    	//$( '#gmw-kleo-geo-form-loader' ).fadeIn( 'slow' );

	    	$( this ).prop( 'disabled', true );
	    	$( this ).addClass( 'enter-clicked' );
	    	$( this ).addClass( 'focusout-enter-clicked' );

	    	var address 	= $( this ).val();
			var lastAddress = $( this ).attr( 'data-last_address' );
			var lat 		= $( '#gmw-lat-sdate-geo' ).val();
			var lng 		= $( '#gmw-lng-sdate-geo' ).val();

			// if address changes, geocode it
			if ( $.trim( address ).length && ( address != lastAddress || ! $.trim( lat ).length || ! $.trim( lng ).length ) ) {
				GMW.geocoder( address, gmw_sdate_geo_geocoder_success, gmw_sdate_geo_geocoder_failed );
				//return false;
			} else {
				
				$( this ).prop( 'disabled', false );
				$( this ).removeClass( 'enter-clicked' );
				$( '#horizontal_search' ).submit();
			}		
	    }
	});

	/**
	 * Reset filters when showing all memebers
	 * 
	 * @return {[type]}   [description]
	 */
	$( '#members-all' ).on( 'click', function() {
		
		$( '#gmw-address-field-sdate-geo, #members_search' ).val( '' );

		//GMW.set_cookie( 'gmw_sdate_geo_radius', '', 1 );	
		//GMW.set_cookie( 'gmw_sdate_geo_oredrby', '', 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_address', '', 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lat', '', 1 );	
		GMW.set_cookie( 'gmw_sdate_geo_lng', '', 1 );
	});
});

// Add Google Marker Cluster
if ( gmwVars.mapsProvider === 'google_maps' ) { 

	GMW.add_filter( 'gmw_map_init', function( map ) {

		map.markerGroupingTypes.markers_clusterer = {

			'init' : function( mapObject ) {

				// initialize markers clusterer if needed and if exists
			    if ( typeof MarkerClusterer === 'function' ) {
			    	
			    	// init new clusters object
					mapObject.clusters = new MarkerClusterer( 
						mapObject.map, 
						mapObject.markers,
						{
							imagePath    : mapObject.clustersPath,
							clusterClass : mapObject.prefix + '-cluster cluster',
							maxZoom 	 : 15 
						}
					);
				} 
			},

			'clear' : function( mapObject ) {

				// initialize markers clusterer if needed and if exists
			    if ( typeof MarkerClusterer === 'function' ) {

			    	// remove existing clusters
			    	if ( mapObject.clusters != false ) {		
			    		mapObject.clusters.clearMarkers();
			    	}
				} 
			},

			'addMarker' : function( marker, mapObject ) {

				mapObject.clusters.addMarker( marker );	
			},

			'markerClick' : function( marker, mapObject ) {

				google.maps.event.addListener( marker, 'click', function() {

					mapObject.markerClick( this );
				});	
			}
		};

		return map;
	} );
}
