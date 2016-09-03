/**
 * gmw JavaScript - Set Cookie
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmwSetCookie(name, value, exdays) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var cooki = escape(encodeURIComponent(value)) + ((exdays == null) ? "" : "; expires=" + exdate.toUTCString());
    document.cookie = name + "=" + cooki + "; path=/";
}

/**
 * gmw JavaScript - Get Cookie
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmwGetCookie(cookie_name) {
    var results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
    return results ? decodeURIComponent(results[2]) : null;
}

/**
 * gmw JavaScript - Delete Cookie
 * @version 1.0
 * @author Eyal Fitoussi
 */
function gmwDeleteCookie(c_name) {
    document.cookie = encodeURIComponent(c_name) + "=deleted; expires=" + new Date(0).toUTCString();
}

/**
 * gmw JavaScript - Get the user's current location
 * @version 1.0
 * @author Eyal Fitoussi
 */
 function GmwAutoLocator( gmwLocatorSuccess, gmwLocatorFailed ) {

    var returnedValue = false;

    // if GPS exists locate the user //
    if ( navigator.geolocation ) {
        navigator.geolocation.getCurrentPosition(showPosition, showError, {timeout: 10000});
    } else {
        
        // if nothing found we cant do much. we cant locate the user :( //        
        returnedValue = { 
            status: 'failed',
            type: '1',
            message:'Sorry! Geolocation is not supported by this browser.'
        };

        return gmwLocatorFailed( returnedValue );
    }

    // GPS locator function //
    function showPosition(position) {

        var geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {

                returnedValue = { 
                    status: 'success',
                    results: results
                };

                return gmwLocatorSuccess( returnedValue );

                //getAddressFields(results);                
            } else {

                returnedValue = { 
                    status: 'falied',
                    type: '2',
                    message: status 
                };

                return gmwLocatorFailed( returnedValue );
            }
        });
    }

    function showError(error) {
    
        var errorMessage;

        switch (error.code) {

            case error.PERMISSION_DENIED:
                errorMessage = 'User denied the request for Geolocation.';
            break;

            case error.POSITION_UNAVAILABLE:
                errorMessage = 'Location information is unavailable.';
            break;

            case 3:
                errorMessage ='The request to get user location timed out.';
            break;

            case error.UNKNOWN_ERROR:
                errorMessage = 'An unknown error occurred';  
            break;
        }

        returnedValue = {
            status: 'failed',
            type: '3',
            message: errorMessage
        };

        return gmwLocatorFailed( returnedValue );
    }
}
    
function gmwAddressGeocoder( options, success, failed ) {

    //init google geocoder
    geocoder = new google.maps.Geocoder();
    
    geocoder.geocode({'address': options.address, 'region': options.region }, function(results, status) {

        if (status == google.maps.GeocoderStatus.OK) {

            return success(results);
        } else {
            return failed(status);
        }
    });
}

jQuery(document).ready(function($) {
    
    $('.gmw-map-loader').fadeOut(1500);  

    if ( jQuery().chosen ) {
        $(".gmw-chosen").chosen();
    }
    //trigger form when click on enter within input field
    /*$('.gmw-form input[type="text"]').keypress(function(event){
        if( event.keyCode == 13 ){
            $(this).closest('form').submit();
        }
    }); */
    
    //hide locator icon if browser does not support
    if (!navigator.geolocation) {
        $('.gmw-locator-btn-wrapper').hide();
    }

    // remove red border from input field
    $('.gmw-address').focus(function() {
        if ( $(this).hasClass('gmw-no-address-error') ) {
            $(this).removeClass('gmw-no-address-error');
        }
    });
 
    //when click on an HTML submit button submit the form
    $('.gmw-submit').click(function(e) {
        var target = $( e.target );
        if ( !target.is( "input" ) ) {
            $(this).closest('form').submit();
        }
    });
    
    //sumit form when click enter in any input field
    $(".gmw-form input[type='text']").keyup(function(event){
        if (event.keyCode == 13){
            $(this).closest('form').submit();
        }
    });

    //remove hidden coordinates when address field changed
    $('.gmw-address').keyup(function () { 
        $(this).closest('form').find('.gmw-lat').val('');
        $(this).closest('form').find('.gmw-lng').val('');
    });

    // per page dropdown
    jQuery( '.gmw-per-page' ).change( function() {

        thisValue    = jQuery(this).val();
        ppValues     = jQuery(this).next();
        formID       = ppValues.attr('data-formid');
        totalResults = ppValues.attr('data-totalcount');
        paged        = ppValues.attr('data-paged');
        pageName     = ppValues.attr('data-pagename');
        urlPx        = ppValues.attr('data-urlpx');
        gmwPost      = ppValues.attr('data-gmwpost');
        perPage      = ppValues.attr('data-perpage');
        lastPage     = Math.ceil(totalResults / thisValue );
        newPaged     = ( paged > lastPage || lastPage == 1 ) ? lastPage : paged;

        if ( gmwPost == 0 ) {   
            window.location.href = window.location.href + '?'+urlPx+'auto=auto&'+urlPx+'per_page=' + thisValue + '&'+urlPx+'form='+formID+'&'+pageName+'='+newPaged;                            
        } else {
            window.location.href = location.href.replace( urlPx + 'per_page=' + perPage,  urlPx + 'per_page=' + thisValue ).replace( '&page=' + paged, '&'+pageName + '=' + newPaged );
        }
    });


    
    $('.gmw-form').submit(function(e) {
        
        var address;    
        var sForm  = $(this);
        sForm.find('.gmw-paged').val('1');
   
        //get the entered address
        if ( sForm.find('.gmw-address').hasClass('gmw-full-address') ) {
            address = sForm.find('.gmw-full-address').val();
        } else {
            address = [];
            sForm.find(".gmw-address").each(function() {
                address.push($(this).val());
            });
            address = address.join(' ');               
        }

        //if address field is empty create a red border for the input field and stop the function
        if ( !$.trim(address).length ) {
            var addressField = sForm.find('.gmw-address');
            if ( addressField.hasClass('mandatory') ) {
                if (!addressField.hasClass('gmw-no-address-error') ) {
                    addressField.toggleClass('gmw-no-address-error');
                }
                return false;
            } else {
                sForm.find('.gmw-submit').addClass('submitted');
            }
        }
       
        //geocode address via javascript
        if ( gmwSettings.general_settings.js_geocode == 1 ) {

            // check if we are submmiting the same address and if we have lat/long. 
            //if so no need to geocode again and submit the form with the information we already have
            if ( sForm.find( '.gmw-lat').val() != '' && sForm.find( '.gmw-lng').val() != '' ) {            
                return true;
            }
                  
            //Check if the address was geocoded and if so we need to submit this form
            if (sForm.find('.gmw-submit').hasClass('submitted')) {
                return true;
            }

            //Otherwise, abort   the form submission. we need to geocode the address
            e.preventDefault();
            
            //init google geocoder
            geocoder = new google.maps.Geocoder();
        
            countryCode = gmwSettings.general_settings.country_code;
      
            geocoder.geocode({'address': address, 'region': countryCode }, function(results, status) {
 
                if (status == google.maps.GeocoderStatus.OK) {
    
                    //add class to submit button so the form will be submitted after geocoding
                    sForm.find('.gmw-submit').addClass('submitted');

                    // Modify the lat and long hidden fields 
                    sForm.find('.gmw-lat').val(results[0].geometry.location.lat());
                    sForm.find('.gmw-lng').val(results[0].geometry.location.lng());

                    // submit the form with the location
                    setTimeout(function() {
                        sForm.submit();             
                    }, 500);
                } else {
                    //if address was not geocoded stop the function and display error message
                    alert("We could not find the address you entered for the following reason: " + status);
                }
            });
        //no geocoding! only form submission    
        } else {    
            return true;
        }
    });

    var autoLocator = false;
    var gForm = false;

    function gmwLocatorSuccess( returnedLocator ) {
        getAddressFields( returnedLocator.results )
    }

    var locatorClicked = false;

    function gmwLocatorFailed( returnedLocator ) {

        if ( returnedLocator.type == 2 ) {
            alert( 'Geocoder failed due to: ' + returnedLocator.message );
        } else {
            alert( returnedLocator.message )
        }

        if ( locatorClicked != false ) {
            $('#gmw-locator-btn-loader-'+locatorClicked ).fadeToggle('fast',function() {
                $('.locator-submitted').fadeToggle('fast').removeClass('locator-submitted');
                $('.gmw-address').removeAttr('disabled');
                $('.gmw-submit').removeAttr('disabled');
            }); 
        }
    }

    //check if we need to autolocate the user on page load
    if ( gmwSettings.general_settings.auto_locate == 1 && gmwGetCookie('gmw_autolocate') != 1 ) {

        //set cookie to prevent future autolocation for one day
        gmwSetCookie("gmw_autolocate", 1, 1);
        autoLocator = true;

        //autlocate
        GmwAutoLocator( gmwLocatorSuccess, gmwLocatorFailed );
    }
        
    // When click on locator button in a form
    $('.gmw-locate-btn').click(function() {
    
        locatorClicked = $(this).closest('form').find('.gmw-form-id').val();
        locatorButton  = $(this);
        gForm          = $(this).closest('form');
        
        gForm.find('.gmw-lat').val('');
        gForm.find('.gmw-lng').val('');
        $(this).toggleClass('locator-submitted');
        
        locatorButton.fadeToggle('fast',function() {

            $('#gmw-locator-btn-loader-'+locatorClicked).fadeToggle('fast', function() {
                $('.gmw-address').attr('disabled', 'disabled');
                $('.gmw-submit').attr('disabled', 'disabled');

                GmwAutoLocator( gmwLocatorSuccess, gmwLocatorFailed );
            });
        });
    });

    /* main function to geocoding from lat/long to address or the other way around when locating the user */
    function getAddressFields(results) {

        var street_number = '';
        var street        = '';
        var city          = '';
        var state         = '';
        var zipcode       = '';
        var country       = '';
        var address       = results[0].address_components;
        var gotLat        = results[0].geometry.location.lat();
        var gotLng        = results[0].geometry.location.lng();

        gmwSetCookie("gmw_lat", gotLat, 7);
        gmwSetCookie("gmw_lng", gotLng, 7);
        gmwSetCookie("gmw_address", results[0].formatted_address, 7);
        
        if ( gForm != false ) {
            gForm.find('.gmw-lat').val(gotLat);
            gForm.find('.gmw-lng').val(gotLng);
        }
                
        /* check for each of the address components and if exist save it in a cookie */
        for ( x in address ) {

            //get the street number
            if ( address[x].types == 'street_number' ) {

                //save street number in variable
                street_number = address[x].long_name;

                //save the street number in a cookie
                gmwSetCookie("gmw_street_number", street_number, 7);
            }

            //get the street name
            if ( address[x].types == 'route' ) {

                //save street name in variable
                street_name = address[x].long_name;

                //save the street name in a cookie
                gmwSetCookie("gmw_street_name", street_name, 7);

                //combine the street number and street name into one street field
                if ( street_number != false && street_number != '' ) {
                    street = street_number + ' ' + street_name;
                } else {
                    street = street_name;
                }

                //save street field in cookie
                gmwSetCookie("gmw_street", street, 7);
            }

            //get the state
            if (address[x].types == 'administrative_area_level_1,political') {

                state = address[x].short_name;

                //state short name
                gmwSetCookie("gmw_state", address[x].short_name, 7);

                //state long name
                gmwSetCookie("gmw_state_long", address[x].long_name, 7);
            }

            //get city name
            if (address[x].types == 'locality,political') {
                
                city = address[x].short_name;

                //save city in cookie
                gmwSetCookie("gmw_city", address[x].short_name, 7);
            }

            //get postal code
            if (address[x].types == 'postal_code') {
                
                zipcode = address[x].short_name;

                //save zipcode in cookie
                gmwSetCookie("gmw_zipcode", address[x].short_name, 7);
            }

            //get country
            if (address[x].types == 'country,political') {
                country = address[x].short_name;

                //country short name
                gmwSetCookie("gmw_country", address[x].short_name, 7);

                //country long name
                gmwSetCookie("gmw_country_long", address[x].long_name, 7);
            }
        }

        if ( autoLocator == true ) {
            location.reload();
        }

        // if a form was submitted */
        if ( $(".locator-submitted")[0] ) {

            //gForm = $('.locator-submitted').closest('form');

            $('.gmw-address').removeAttr('disabled');
            $('.gmw-submit').removeAttr('disabled');

            //dynamically fiil-out the address fields of the form
            if (gForm.find('.gmw-address').hasClass('gmw-full-address')) {
                gForm.find('.gmw-full-address').val(results[0].formatted_address);
            } else {
                gForm.find('.gmw-saf-street').val(street);
                gForm.find('.gmw-saf-city').val(city);
                gForm.find('.gmw-saf-state').val(state);
                gForm.find('.gmw-saf-zipcode').val(zipcode);
                gForm.find('.gmw-saf-country').val(country);
            }

            gForm.find('.gmw-submit').toggleClass('submitted');
            gForm.find('.gmw-lat').val(gotLat);
            gForm.find('.gmw-lng').val(gotLng);
     
            if ( $('#'+locatorClicked).hasClass('gmw-locator-submit') ) {
                
                setTimeout(function() {
                    
                    $('.gmw-locator-btn-loader-'+locatorClicked).fadeToggle('fast',function() {
                        locatorButton.fadeToggle('fast');
                    });
                    
                    gForm.find('.gmw-submit').click();
                }, 1500);
                
            } else {
                
                $('#gmw-locator-btn-loader-'+locatorClicked).fadeToggle('fast',function() {
                    $('.locator-submitted').fadeToggle('fast').removeClass('locator-submitted');
                });
            }
        }
    }
});