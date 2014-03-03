  var geocoder = new google.maps.Geocoder();  

     jQuery(function() {
         jQuery(".wppl-address").autocomplete({
         
           source: function(request, response) {

          if (geocoder == null){
           geocoder = new google.maps.Geocoder();
          }
             geocoder.geocode( {'address': request.term }, function(results, status) {
               if (status == google.maps.GeocoderStatus.OK) {

                  var searchLoc = results[0].geometry.location;
                  var lat = results[0].geometry.location.lat();
                  var lng = results[0].geometry.location.lng();
                  var latlng = new google.maps.LatLng(lat, lng);
                  var bounds = results[0].geometry.bounds;

                  geocoder.geocode({'latLng': latlng}, function(results1, status1) {
                      if (status1 == google.maps.GeocoderStatus.OK) {
                        if (results1[1]) {
                         response($.map(results1, function(loc) {
                        return {
                            label  : loc.formatted_address,
                            value  : loc.formatted_address,
                            bounds   : loc.geometry.bounds
                          };
                        }));
                        }
                      }
                    });
            }
              });
           },
           
         });
     });   
