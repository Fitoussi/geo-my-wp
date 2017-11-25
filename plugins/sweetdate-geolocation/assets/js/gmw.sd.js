jQuery( document ).ajaxStart( function() {

	GMW.set_cookie( 'gmw_sd_orderby', jQuery( '#gmw-sd-orderby-dropdown' ).val(), 1 );	
	GMW.set_cookie( 'gmw_sd_radius', jQuery( '#gmw-sd-radius-dropdown' ).val(), 1 );	
	GMW.set_cookie( 'gmw_sd_address', jQuery( '#gmw-sd-address-field' ).val(), 1 );		
});

jQuery( document ).ready( function( $ ) {
	
	//move map outside the member list. We do that since the page uses ajax to update results and so we also 
	//need the map. However because the results wrapper reloads it "kills" the map.
	$( '#gmw-map-wrapper-sd' ).detach().insertBefore( '#members-dir-list' );

	$( '#horizontal_search' ).submit( function( event ) {
		
		GMW.set_cookie( 'gmw_sd_orderby', jQuery( '#gmw-sd-orderby-dropdown' ).val(), 1 );	
		GMW.set_cookie( 'gmw_sd_radius', jQuery( '#gmw-sd-radius-dropdown' ).val(), 1 );	
		GMW.set_cookie( 'gmw_sd_address', jQuery( '#gmw-sd-address-field' ).val(), 1 );
		
		return true;
	});

	$( '#members-all' ).on( 'click', function() {
		
		$( '#gmw-sd-address-field' ).val( '' );

		//GMW.set_cookie( 'gmw_sd_orderby', '', 1 );	
		//GMW.set_cookie( 'gmw_sd_radius', '', 1 );	
		GMW.set_cookie( 'gmw_sd_address', '', 1 );	
	});
});