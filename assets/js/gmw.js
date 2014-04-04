jQuery(document).ready(function($) {

    $('.gmw-map-loader').fadeOut(1500);

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
    window.gmwSetCookie = gmwSetCookie;

    /**
     * gmw JavaScript - Get Cookie
     * @version 1.0
     * @author Eyal Fitoussi
     */
    function gmwGetCookie(cookie_name) {
        var results = document.cookie.match('(^|;) ?' + cookie_name + '=([^;]*)(;|$)');
        return results ? decodeURIComponent(results[2]) : null;
    }
    window.gmwGetCookie = gmwGetCookie;

    /**
     * gmw JavaScript - Delete Cookie
     * @version 1.0
     * @author Eyal Fitoussi
     */
    function gmwDeleteCookie(c_name) {
        document.cookie = encodeURIComponent(c_name) + "=deleted; expires=" + new Date(0).toUTCString();
    }
    window.gmwDeleteCookie = gmwDeleteCookie;

    //hide locator icon if browser does not support
    if (!navigator.geolocation)
        $('.gmw-locator-btn-wrapper').hide();

    // remove red border from input field
    $('.gmw-address').focus(function() {
        if ($(this).hasClass('gmw-no-address-error'))
            $(this).removeClass('gmw-no-address-error');
    });

    $('.gmw-submit').click(function(e) {
        //if ($(this).attr('type', 'submit')) {
        e.preventDefault();
        //}
        $(this).closest('form').submit();
    }); 

    // when submitting a form	
    $('.gmw-form').submit(function(e) {

        var sForm = $(this);
        var formId = sForm.find('.gmw-form-id').val();

        //get the entered address
        if (sForm.find('.gmw-address').hasClass('gmw-full-address')) {
            var address = sForm.find('.gmw-full-address').val();
        } else {
            var address = [];
            sForm.find(".gmw-address").each(function() {
                address.push($(this).val());
            });
            address = address.join(' ');
           
        }
        
        // check if we are submmiting the same address and if we have lat/long. 
        //if so no need to geocode again and submit the form with the information we already have
        if (sForm.find('.prev-address').val() == address && $.trim(sForm.find('.gmw-lat').val()).length > 0)
            return true;
        //Check if the address was geocoded and if so we need to submit this form
        if (sForm.find('.gmw-submit').hasClass('submitted'))
            return true;
        
        //stop the form submission. we need to geocode the address
        e.preventDefault();
        //if address field is empty create a red border for the input field and stop the function
        if (!$.trim(address).length) {
            if (sForm.find('.gmw-address').hasClass('mandatory')) {
                if (!sForm.find('.gmw-address').hasClass('gmw-no-address-error'))
                    sForm.find('.gmw-address').toggleClass('gmw-no-address-error');
            } else {
                sForm.find('.gmw-submit').toggleClass('submitted');
                setTimeout(function() {
                    sForm.find('.gmw-submit').click();
                }, 500);
            }
            return false;
        }

        //run google geocoder
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': address}, function(results, status) {

            if (status == google.maps.GeocoderStatus.OK) {

                //add class to submit button so the form will be submitted after geocoding
                sForm.find('.gmw-submit').toggleClass('submitted');
                // Modify the lat and long hidden fields 
                sForm.find('.gmw-lat').val(results[0].geometry.location.lat());
                sForm.find('.gmw-lng').val(results[0].geometry.location.lng());
                // submit the form with the location
                setTimeout(function() {
                    sForm.find('.gmw-submit').click();
                }, 500);
            } else {
                //if address was not geocoded stop the function and display error message
                alert("We could not find the address you entered for the following reason: " + status);
            }
        });
    });

    var autoLocator = false;

    if (autoLocate == true && gmwGetCookie('gmw_autolocate') != 1) {

        gmwSetCookie("gmw_autolocate", 1, 1);
        autoLocator = true;
        GmwGetLocation();

    }

    /**
     * gmw JavaScript - Get the user's current location
     * @version 1.0
     * @author Eyal Fitoussi
     */
    function GmwGetLocation() {

        // if GPS exists locate the user //
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError, {timeout: 10000});
        } else {
            // if nothing found we cant do much. we cant locate the user :( //
            alert('Sorry! Geolocation is not supported by this browser and we cannot locate you.');
        }

        // GPS locator function //
        function showPosition(position) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'latLng': new google.maps.LatLng(position.coords.latitude, position.coords.longitude)}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    getAddressFields(results);
                } else {
                    alert('Geocoder failed due to: ' + status);
                }
            });
        }

        function showError(error) {

            switch (error.code) {
                case error.PERMISSION_DENIED:
                    alert('User denied the request for Geolocation.');
                    $('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
                    $('.gmw-address').removeAttr('disabled');
                    $('.gmw-submit').removeAttr('disabled');
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert('Location information is unavailable.');
                    $('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
                    $('.gmw-address').removeAttr('disabled');
                    $('.gmw-submit').removeAttr('disabled');
                    break;
                case 3:
                    alert('The request to get user location timed out.');
                    $('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
                    $('.gmw-address').removeAttr('disabled');
                    $('.gmw-submit').removeAttr('disabled');
                    break;
                case error.UNKNOWN_ERROR:
                    alert('An unknown error occurred');
                    $('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
                    $('.gmw-address').removeAttr('disabled');
                    $('.gmw-submit').removeAttr('disabled');
                    break;
            }
        }
    }

    var locatorClicked;

    // When click on locator button in a form
    $('.gmw-locate-btn').click(function() {

        locatorClicked = $(this).attr('id');
        $(this).toggleClass('locator-submitted');
        $(this).closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast');
        $('.gmw-address').attr('disabled', 'disabled');
        $('.gmw-submit').attr('disabled', 'disabled');

        setTimeout(function() {
            GmwGetLocation();
        }, 500);

    });

    /* main function to geocoding from lat/long to address or the other way around when locating the user */
    function getAddressFields(results) {

        var street_number = false;
        var street  = '';
        var city    = '';
        var state   = '';
        var zipcode = '';
        var country = '';
        var address = results[0].address_components;
        var gotLat  = results[0].geometry.location.lat();
        var gotLng  = results[0].geometry.location.lng();

        gmwSetCookie("gmw_lat", gotLat, 7);
        gmwSetCookie("gmw_lng", gotLng, 7);
        gmwSetCookie("gmw_address", results[0].formatted_address, 7);
        
        /* check for each of the address components and if exist save it in a cookie */
        for (x in address) {

            if (address[x].types == 'street_number') {
                street_number = address[x].long_name;
            }

            if (address[x].types == 'route') {
                street = address[x].long_name;
                if (street_number != false) {
                    street = street_number + ' ' + street;
                }
                gmwSetCookie("gmw_street", street, 7);
            }

            if (address[x].types == 'administrative_area_level_1,political') {
            	state = address[x].short_name;
                gmwSetCookie("gmw_state", address[x].short_name, 7);
            }

            if (address[x].types == 'locality,political') {
            	city = address[x].short_name;
                gmwSetCookie("gmw_city", address[x].short_name, 7);
            }

            if (address[x].types == 'postal_code') {
            	zipcode = address[x].short_name;
                gmwSetCookie("gmw_zipcode", address[x].short_name, 7);
            }

            if (address[x].types == 'country,political') {
            	country = address[x].short_name;
                gmwSetCookie("gmw_country", address[x].short_name, 7);
            }
        }

        if (autoLocator == true )
            location.reload();

        // if a form was submitted */
        if ($(".locator-submitted")[0]) {

            gForm = $('.locator-submitted').closest('form');

            $('.gmw-address').removeAttr('disabled');
            $('.gmw-submit').removeAttr('disabled');

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
     
            if ( $('#' + locatorClicked).hasClass('gmw-locator-submit') ) {
                setTimeout(function() {
                    gForm.find('.gmw-submit').click();
                }, 1500);
            } else {
                $('.locator-submitted').closest('.gmw-locator-btn-wrapper').find('img').fadeToggle('fast').removeClass('locator-submitted');
            }
        }
        ;

    }

});
