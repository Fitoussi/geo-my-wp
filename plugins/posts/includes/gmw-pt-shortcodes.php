<?php

/**
 * GMW PT Shortcode - Shortcode display location of a single post, post type or a page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Single_Location {

    /**
     * __Constructor
     */
    function __construct() {

        add_shortcode( 'gmw_single_location', array( $this, 'get_single_location' ) );

    }

    public function get_single_location( $args) {

    	//default shortcode attributes
    	extract(
    			shortcode_atts(
    					array(
    							'post_id'         => 0,
    							'post_title'	  => 1,
    							'distance'		  => 1,
    							'distance_unit'	  => 'm',
    							'map'             => 1,
    							'map_height'      => '250px',
    							'map_width'       => '250px',
    							'map_type'        => 'ROADMAP',
    							'zoom_level'      => 13,
    							'additional_info' => 'address,phone,fax,email,website',
    							'directions'      => 1
    					), $args )
    	);

        $scID = rand( 5, 15 );

        /*
         * check if user entered post id
         */
        if ( $post_id == 0 ) :

            global $post;
            $post_id = $post->ID;

        endif;

        //get the post's info
        $post_info = gmw_get_post_location_from_db( $post_id );

        //if post has no location stop the function
        if ( $post_info == false )
            return;

        $location_wrap_start = '<div id="gmw-single-post-sc-' . $scID . '-wrapper" class="gmw-single-post-sc-wrapper">';
       
        $location_title = '';
        if ( $post_title == 1 ) {
        	$location_title = '<h3>'. get_the_title($post_id) .'</h3>';
        } 
        
        $location_distance = '';
        $distanceOK = 0;
        $yLat		= 0;
        $yLng		= 0;
        
        if ( $distance == 1 && isset( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lat'] ) ) {
	        
        	$distanceOK 	= 1;
        	$yLat			= urldecode( $_COOKIE['gmw_lat'] );
        	$yLng			= urldecode( $_COOKIE['gmw_lng'] );
        	$unit  			= $distance_unit;
	        $theta 			= $yLng - $post_info->long;
	        $distance_value = sin( deg2rad( $yLat  ) ) * sin( deg2rad($post_info->lat ) ) +  cos( deg2rad( $yLat ) ) * cos( deg2rad($post_info->lat) ) * cos( deg2rad( $theta ) );
	        $distance_value = acos($distance_value);
	        $distance_value = rad2deg($distance_value);
	        $miles 			= $distance_value * 60 * 1.1515;
	        
	        if ($unit == "k") {
	        	$distance_value = ( $miles * 1.609344 );
	        	$units_name		= 'km';
	        } else {
	        	$distance_value = ($miles * 0.8684);
	        	$units_name		= 'mi';
	        } 

	        $location_distance = '<div class="distance-wrapper"><p>'.__( 'Distance:','GMW' ). ' '. round( $distance_value, 2 ) .' '.$units_name.'</p></div>';
        }
        
        $location_map = '';
        if ( $map == 1 ) {
			
        	$location_map  = '';
            $location_map .= '<div class="map-wrapper" style="width:' . $map_width . '; height:' . $map_height . '">';
            $location_map .= 	'<div id="gmw-single-post-map-' . $scID . '" class="gmw-map" style="width:100%; height:100%;"></div>';
            $location_map .= '</div>';

        }
		
        $location_directions = '';
        if ( $directions == 1 ) {
			
        	$your_address = ( isset( $_COOKIE['gmw_address'] ) ) ? urldecode(  $_COOKIE['gmw_address'] ) : '';
        		
        	$location_directions  = '';
            $location_directions .= '<div class="directions-wrapper">';
            $location_directions .= 	'<div id="gmw-single-post-sc-form-' . $scID . '" class="gmw-single-post-sc-form" style="display:none;">';
            $location_directions .= 		'<form action="http://maps.google.com/maps" method="get" target="_blank">';
            $location_directions .= 			'<input type="text" size="35" name="saddr" value="'.$your_address.'" placeholder="Your location" />';
            $location_directions .= 			'<input type="hidden" name="daddr" value="' . $post_info->address . '" />';
            $location_directions .= 			'<input type="submit" class="button" value="' . __( 'GO', 'GMW' ) . '" />';
            $location_directions .= 		'</form>';
            $location_directions .= 	'</div>';
            $location_directions .= 	'<a href="#" id="single-post-trigger-' . $scID . '"  class="single-post-trigger">' . __( 'Get Directions', 'GMW' ) . '</a>';
            $location_directions .= '</div>';

    	}
		
    	$location_info = '';
        //if we are showing additional information
        if ( isset( $additional_info ) || $additional_info != 0 ) {

            $additional_info = explode( ',', $additional_info );
			
            $location_info  = '';
            $location_info .= '<div class="gmw-single-post-sc-additional-info">';

            if ( in_array( 'address', $additional_info ) ) {
                $location_info .= '<div class="gmw-address"><span>' . __( 'Address: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->formatted_address ) ) ? $post_info->formatted_address : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'phone', $additional_info ) ) {
                $location_info .= '<div class="gmw-phone"><span>' . __( 'Phone: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->phone ) ) ? $post_info->phone : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'fax', $additional_info ) ) {
                $location_info .= '<div class="gmw-fax"><span>' . __( 'Fax: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->fax ) ) ? $post_info->fax : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'email', $additional_info ) ) {
                $location_info .= '<div class="gmw-email"><span>' . __( 'Email: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->email ) ) ? '<a href="mailto:' . $post_info->email . ' ">' . $post_info->email . '</a>' : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'website', $additional_info ) ) {
                $location_info .= '<div class="gmw-website"><span>' . __( 'Website: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= (!empty( $post_info->website ) ) ? '<a href="http://' . $post_info->website . '" target="_blank">' . $post_info->website . '</a>' : "N/A";
                $location_info .= '</div>';
            }

            $location_info .= '</div>';

        }

        $location_wrap_end = '</div>';
       	
        ?>
        <script>

            jQuery(document).ready(function($) {

                $('#single-post-trigger-<?php echo $scID; ?>').click(function(event) {
                    event.preventDefault();
                    $('#gmw-single-post-sc-form-<?php echo $scID; ?>').slideToggle();
                });

                if ('<?php echo $map; ?>' == 1) {

                    var gmwSinglePostMap = new google.maps.Map(document.getElementById('gmw-single-post-map-<?php echo $scID; ?>'), {
                        zoom: parseInt(<?php echo $zoom_level; ?>),
                        center: new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>),
                        mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        }
                    });

                    var infowindow = new google.maps.InfoWindow();

                    if ('<?php echo $post_info->phone; ?>' != '')
                        gmwPhone = '<?php echo $post_info->phone; ?>';
                    else
                        gmwPhone = 'N/A';
                    if ('<?php echo $post_info->fax; ?>' != '')
                        gmwFax = '<?php echo $post_info->fax; ?>';
                    else
                        gmwFax = 'N/A';
                    if ('<?php echo $post_info->email; ?>' != '')
                        gmwEmail = '<?php echo $post_info->email; ?>';
                    else
                        gmwEmail = 'N/A';
                    if ('<?php echo $post_info->website; ?>' != '')
                        gmwWebsite = '<a href="http://<?php echo $post_info->website; ?>" target="_blank"><?php echo $post_info->website; ?></a>';
                    else
                        gmwWebsite = 'N/A';

                    gmwSinglePostInfoWindow =
                            '<div class="wppl-info-window" style="font-size: 13px;color: #555;line-height: 18px;font-family: arial;">' +
                            '<div class="map-info-title" style="color: #457085;text-transform: capitalize;font-size: 16px;margin-bottom: -10px;"><?php echo $post_info->post_title; ?></div>' +
                            '<br /> <span style="font-weight: bold;color: #333;">Address: </span><?php echo $post_info->address; ?>' +
                            '<br /> <span style="font-weight: bold;color: #333;">Phone: </span>' + gmwPhone +
                            '<br /> <span style="font-weight: bold;color: #333;">Fax: </span>' + gmwFax +
                            '<br /> <span style="font-weight: bold;color: #333;">Email: </span>' + gmwEmail +
                            '<br /> <span style="font-weight: bold;color: #333;">Website: </span>' + gmwWebsite;

                    var latlngbounds = new google.maps.LatLngBounds( );

                    var desLoc = new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>);
                    latlngbounds.extend(desLoc);
                    
                    gmwSinglePostMarker = new google.maps.Marker({
                        position: desLoc,
                        map: gmwSinglePostMap,
                        icon:'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    });

                    google.maps.event.addListener(gmwSinglePostMarker, 'click', function() {
                        infowindow.setContent(gmwSinglePostInfoWindow);
                        infowindow.open(gmwSinglePostMap, gmwSinglePostMarker);
                    });
                    
                    if ( <?php echo $distanceOK; ?> == 1 ) {

                    	var yourInfoWindow = new google.maps.InfoWindow();
                    	
                        var yourLoc = new google.maps.LatLng(<?php echo $yLat; ?>, <?php echo $yLng; ?>);
                    	latlngbounds.extend(yourLoc);
                    	
	                    ylMarker = new google.maps.Marker({
	                        position: yourLoc,
	                        map: gmwSinglePostMap,
	                        icon:'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
	                    });

	                    gmwSinglePostMap.fitBounds(latlngbounds);

	                    yourInfoWindow.setContent('Your location');
	                    yourInfoWindow.open(gmwSinglePostMap, ylMarker);
	                    
                    }
                    
                };

            });

        </script>
        <?php
        
        $output = $location_wrap_start.$location_title.$location_distance.$location_map.$location_directions.$location_info;
        
        return apply_filters( 'gmw_pt_single_location_output', $output, $args, $location_wrap_start, $location_title, $location_distance, $location_map, $location_directions, $location_info );

    }

}

new GMW_Single_Location();
