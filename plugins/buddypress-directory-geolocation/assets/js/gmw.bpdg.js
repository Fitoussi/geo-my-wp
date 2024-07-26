jQuery( document ).ready( function( $ ) {

	if (gmwBpdg.component == 'business') {
		gmwBpdg.component = 'busines';
	}

	// This is a workaround to move the geolcoation items ( address, distance, directions link ) in each member in the results to a better position inside the div element.
	// There is only one hook that we could use for adding those geolocation items into the results, but that hook is not a great location
	// to showcase those items. This is a "hacky" way of doing so, and hpefully temporary.
	if ( gmwBpdg.is_buddyboss_theme ) {

		$( document ).ajaxComplete(function( event, request, settings ) {

			if ( typeof settings.data !== 'undefined' && settings.data.indexOf( 'action=members_filter' ) > -1 ) {

				setTimeout( function() {

					jQuery( '.bp-members-list-hook-inner' ).each( function() {

						var item    = jQuery( this );
						var wrapper = jQuery( this ).closest( '.item-entry' );

						item.find( '.gmw-bpmdg-location-meta-wrapper' ).detach().insertAfter( wrapper.find( '.list-wrap-inner .item-block' ) );
					});
				}, 300 );
			}
		});
	}

	function gmw_bpdg_update_cookies( save ) {

		if ( save ) {

			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_address', jQuery( '#gmw-address-field-' + gmwBpdg.prefix ).val(), 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lat', jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val(), 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lng', jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val(), 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_radius', jQuery( '#gmw-distance-field-' + gmwBpdg.prefix ).val(), 1 );

		} else {

			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_address', '', 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lat', jQuery( '' ).val(), 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lng', jQuery( '' ).val(), 1 );
			GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_radius', jQuery( '' ).val(), 1 );
		}
	}

	/**
	 * When geocoding successful.
	 *
	 * @param  {[type]} results [description]
	 * @return {[type]}         [description]
	 */
	/*function gmw_bpdg_geocoder_success( result, response ) {

		var lat = result.latitude;
		var lng = result.longitude;

		$( '#gmw-lat-' + gmwBpdg.prefix ).val( lat );
		$( '#gmw-lng-' + gmwBpdg.prefix ).val( lng );

		jQuery( '#dir-' + gmwBpdg.component + 's-search-submit, #' + gmwBpdg.component + 's_search_submit' ).click();

		//jQuery( 'body.directory.groups.buddypress.bp-legacy' ).find( '#groups_search_submit' ).click();
		//$( '#dir-groups-search-form, #search-groups-form' ).submit();
	}*/

	/**
	 * if geocoding fails
	 * @param  {[type]} status error message
	 * @return {[type]}        [description]
	 */
	/*function gmw_bpdg_geocoder_failed( status ) {

		GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lat', '', 1 );
		GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_lng', '', 1 );

		jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val( '' );
		jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val( '' );

		alert( 'We couldn\'t find the address that you entered.' );

	    console.log( 'Geocoder failed due to: ' + status );

		return false;
	}*/

	function gmw_bpdg_submit_form() {
		jQuery( '#dir-' + gmwBpdg.component + 's-search-submit, #' + gmwBpdg.component + 's_search_submit' ).click();
	}

	jQuery( '.item-list-tabs #members-all, .item-list-tabs #groups-all' ).on( 'click', function() {
		GMW.set_cookie( 'gmw_' + gmwBpdg.prefix + '_address', '', 1 );
	});

	// Move geolocation form field dynamically into the directory form.
	if ($('.youzify-search-input-form').length) {

		$('#gmw-' + gmwBpdg.prefix + '-form-temp-holder').children().detach().insertAfter('.youzify-left-side-wrapper').find('input[type="text"]').addClass('youzify-search-input');

		jQuery('.youzify-search-input-container').addClass('gmw-bpdg-enabled');

		if (jQuery('.youzify-search-input-container').find('.youzify-show-filters').length && jQuery('.youzify-search-input-container').find('.gmw-bpdg-radius-field-wrapper').length) {
			jQuery('.youzify-search-input-container').addClass('filters-enabled');
		}

	} else if( $('#dir-business-search-form').length) {

		$( '#gmw-form-elements-holder' ).detach().insertAfter( '#dir-business-search-form' ).children().unwrap();
		$('#gmw-distance-element-holder').detach().appendTo('#business-order-by').children().unwrap();

	} else {
		$( '#gmw-' + gmwBpdg.prefix + '-form-temp-holder' ).children().detach().insertBefore( '#' + gmwBpdg.component + 's_search_submit' );
	}

	/**
	 * Move map outside the groups list.
	 *
	 * We do this because the page uses ajax to update results which
	 *
	 * causes the map to disapear.
	 */
	$( '#gmw-map-wrapper-' + gmwBpdg.prefix ).detach().insertBefore( '#' + gmwBpdg.component + 's-dir-list' );

	// Clear coordinates when using the search button of the address field.
	$( '#gmw-address-field-' + gmwBpdg.prefix ).on( 'search', function () {

	    if ( ! jQuery.trim( jQuery( this ).val() ).length ) {

	    	jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val( '' );
			jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val( '' );

			gmw_bpdg_submit_form();
	    }

	    return false;
	});

	// Populdate coordiantes and submit form when selecting from address autocomplete.
	GMW.add_action( 'gmw_address_autocomplete_place_changed', function( place, autocomplete, field_id, input_field, options ) {

		if ( ! place.geometry || 'gmw-address-field-' + gmwBpdg.prefix != field_id ) {
			return false;
		}

		jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val( place.geometry.location.lat() );
		jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val( place.geometry.location.lng() );

		gmw_bpdg_submit_form();

		//jQuery( '#gmw-address-field-' + gmwBpdg.prefix ).addClass( 'autocomplete-triggered' );

		//jQuery( 'body.directory.groups.buddypress.bp-legacy' ).find( '#groups_search_submit' ).click();
		//$( '#dir-groups-search-form, #search-groups-form' ).submit();
	});

	// When click on the locator button.
    jQuery( '.gmw-locator-button' ).click( function() {

    	GMW.locator_button_success = function( result ) {

    		// add coords value to hidden fields
        	$( '#gmw-lat-' + gmwBpdg.prefix ).val( result.latitude );
        	$( '#gmw-lng-' + gmwBpdg.prefix ).val( result.longitude );
    		$( '#gmw-address-field-' + gmwBpdg.prefix ).val( result.formatted_address );
    		$( '.gmw-locator-button' ).removeClass( 'animate-spin' );

    		gmw_bpdg_submit_form();

			//jQuery( 'body.directory.groups.buddypress.bp-legacy' ).find( '#groups_search_submit' ).click();
			//$( '#dir-groups-search-form, #search-groups-form' ).submit();
    	};

    	GMW.locator_button_failed = function( status ) {

    		// alert failed message
        	alert( 'We were unable to detect your location. Please try again.' );

        	console.log( 'Geocoder failed due to: ' + status );

        	$( '.gmw-locator-button' ).removeClass( 'animate-spin' );
    	};

        GMW.locator_button( jQuery( this ) );
    });

	// If the form of GMW is submitted, prevent its submission. We will later submit the directory form instead.
	jQuery( '#gmw-' + gmwBpdg.prefix + '-form' ).on( 'submit', function(e) {

		e.preventDefault();

    	return false;
	});

	// Submit form when selecting radius value.
	jQuery( '#gmw-distance-field-' + gmwBpdg.prefix ).change( function() {
		gmw_bpdg_submit_form();

		//jQuery( 'body.directory.groups.buddypress.bp-legacy' ).find( '#groups_search_submit' ).click();
		//$( '#dir-groups-search-form, #search-groups-form' ).submit();
	});

	/**
	 * Geocode addess on enter key in address field
	 *
	 * @return {[type]} [description]
	 */
	jQuery( '#gmw-address-field-' + gmwBpdg.prefix ).keyup( function( event ){

		//var addressField = jQuery( this );

		if ( event.which != 13 ) {

		    jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val( '' );
			jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val( '' );

		   	// if enter key pressed submit the directory form.
		} else {

		    event.preventDefault();

		    gmw_bpdg_submit_form();
		}

		return;
		/*setTimeout( function() {

			if ( addressField.hasClass( 'autocomplete-triggered' ) ) {

				event.preventDefault();

				return false;
			}

			// clear coords when changing address.
		    if ( event.which != 13 ) {

		    	jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val( '' );
				jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val( '' );

		   	// if enter key pressed, try geocoding the address
		   	} else {

		    	event.preventDefault();

		    	jQuery( '#dir-' + gmwBpdg.component + 's-search-submit' ).click();

		    	/*if ( ! addressField.hasClass( 'autocomplete-enter-key' ) ) {

		    		jQuery( '#dir-' + gmwBpdg.component + 's-search-submit' ).click();

					//jQuery( 'body.directory.groups.buddypress.bp-legacy' ).find( '#groups_search_submit' ).click();
					//$( '#dir-groups-search-form, #search-groups-form' ).submit();
		   		} else {
		   			addressField.removeClass( 'autocomplete-triggered' );
		   		}

		    	return;
		    }
		}, 500 );*/
	});

	/**
	 * In case for submitted via page load.
	 *
	 * Save form values in cookies.
	 *
	 * #dir-groups-search-form for Nouveau template and #search-groups-form for Legacy.
	 *
	 * @return {[type]}  [description]
	 */
	$( '#dir-' + gmwBpdg.component + 's-search-form, #horizontal_search' ).submit( function( e ) {

		gmw_bpdg_update_cookies( true );

		return true;

		/*e.preventDefault();

		var address = jQuery( '#gmw-address-field-' + gmwBpdg.prefix ).val();
		var lat 	= jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val();
		var lng 	= jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val();

		if ( jQuery.trim( address ).length && ( ! jQuery.trim( lat ).length || ! jQuery.trim( lng ).length ) ) {

			GMW.geocoder( address, gmw_bpdg_geocoder_success, gmw_bpdg_geocoder_failed );

			return false;
		}

		$( '#gmw-address-field-' + gmwBpdg.prefix ).removeClass( 'autocomplete-triggered' );

		gmw_bpdg_update_cookies( true );

		return true;*/
	});

	$( '#' + gmwBpdg.component + 's_search_submit, #dir-' + gmwBpdg.component + 's-search-submit' ).click( function( e ) {

		gmw_bpdg_update_cookies( true );

		return true;

		/*e.preventDefault();

		var address = jQuery( '#gmw-address-field-' + gmwBpdg.prefix ).val();
		var lat 	= jQuery( '#gmw-lat-' + gmwBpdg.prefix ).val();
		var lng 	= jQuery( '#gmw-lng-' + gmwBpdg.prefix ).val();

		if ( jQuery.trim( address ).length && ( ! jQuery.trim( lat ).length || ! jQuery.trim( lng ).length ) ) {

			GMW.geocoder( address, gmw_bpdg_geocoder_success, gmw_bpdg_geocoder_failed );

			return false;
		}

		$( '#gmw-address-field-' + gmwBpdg.prefix ).removeClass( 'autocomplete-triggered' );

		gmw_bpdg_update_cookies( true );

		return true;*/
	});
});

// Add Google Marker Cluster
if ( gmwVars.mapsProvider === 'google_maps' ) {

	GMW.add_filter( 'gmw_map_init', function( map ) {

		if ( typeof( map.markerGroupingTypes.markers_clusterer ) !== 'undefined' ) {
			return map;
		}

		map.markerGroupingTypes.markers_clusterer = {

			'init' : function( mapObject ) {

				// initialize markers clusterer if needed and if exists
			    if ( typeof markerClusterer.MarkerClusterer !== 'undefined' ) {
			    	//console.log(mapObject.map)
			    	// init new clusters object
					mapObject.clusters = new markerClusterer.MarkerClusterer({
						//algorithm: Algorithm,
					    map     : mapObject.map,
					    markers : mapObject.markersl,
					    //renderer : renderer,
					});
				}
			},

			'clear' : function( mapObject ) {

				// initialize markers clusterer if needed and if exists
			    if ( typeof markerClusterer.MarkerClusterer === 'function' ) {

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

// Add Google Marker Cluster
if ( gmwVars.mapsProvider === 'leaflet' ) {

	GMW.add_filter( 'gmw_map_init', function( map ) {

		if ( typeof( map.markerGroupingTypes.markers_clusterer ) !== 'undefined' ) {
			return map;
		}

		map.markerGroupingTypes.markers_clusterer = {

			// initiate clusters.
			'init' : function( mapObject ) {

				// initialize markers clusterer if needed and if exists
			    if ( typeof L.markerClusterGroup === 'function' ) {

			    	mapObject.clusters = L.markerClusterGroup( mapObject.options.markerClustersOptions );

			    	mapObject.map.addLayer( mapObject.clusters );
				}
			},

			// Clear clusters.
			'clear' : function( mapObject ) {

			    if ( typeof L.markerClusterGroup === 'function' ) {

			    	// remove existing clusters
			    	if ( mapObject.clusters != false ) {
			    		mapObject.clusters.clearLayers();
			    	}
				}
			},

			// Add marker to the cluster.
			'addMarker' : function( marker, mapObject ) {
				mapObject.clusters.addLayer( marker );
			},

			// marker click action to open info-window.
			'markerClick' : function( marker, mapObject ) {

				marker.on( 'click', function() {

					mapObject.markerClick( this );
				});
			}
		};

		return map;
	} );
}
