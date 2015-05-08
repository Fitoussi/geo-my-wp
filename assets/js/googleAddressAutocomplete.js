function gmwGoogleAddressAutocomplete( gacFields ) {
        
	jQuery.each( gacFields, function( key, field ) {
 
    	var input = document.getElementById(field);
 
            //varify the field
        if ( input != null ) {
            
            //basic options of Google places API. 
            //see this page https://developers.google.com/maps/documentation/javascript/places-autocomplete
            //for other avaliable options
            var options = {};
             
            var autocomplete = new google.maps.places.Autocomplete(input, options);
             
            google.maps.event.addListener(autocomplete, 'place_changed', function(e) {
        
                var place = autocomplete.getPlace();
        
                if (!place.geometry) {
                    return;
                }                        
            });
        }
    });
};
jQuery(document).ready(function($) {
    gmwGoogleAddressAutocomplete( gacFields );
});