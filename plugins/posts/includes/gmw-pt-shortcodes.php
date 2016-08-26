<?php
if ( class_exists( 'GMW_Single_Location' ) )
	return;

/**
 * GMW PT Shortcode - Shortcode display location of a single post, post type or a page.
 * @version 1.0
 * @author Eyal Fitoussi
 */
class GMW_Single_Post_Location {

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
    							'element_id'	  => 0,
    							'post_id'         => 0,
    							'post_title'	  => 0,
    							'distance'		  => 1,
    							'distance_unit'	  => 'm',
    							'map'             => 1,
    							'map_height'      => '250px',
    							'map_width'       => '250px',
    							'map_type'        => 'ROADMAP',
    							'zoom_level'      => 13,
							'scrollwheel'     => true,
    							'additional_info' => 'address,phone,fax,email,website',
    							'directions'      => 1,
    							'info_window'	  => 1,
    							'show_info'		  => 1,
    							'ul_marker'   	  => 1,
    							'ul_message'	  => __( 'Your Location', 'GMW' ),
    					), $args )
    	);

    	$element_id = ( $element_id != 0 ) ? $element_id : rand( 5, 100 );

    	/*
    	 * check if user entered post id
    	*/
    	if ( $post_id == 0 ) {

    		global $post;
    		$post_id = $post->ID;

        }

        //get the post's info
        $post_info = gmw_get_post_location_from_db( $post_id );

        //if post has no location stop the function
        if ( !$post_info )
            return;

        $location_wrap_start = '<div id="gmw-single-post-sc-' . $element_id . '-wrapper" class="gmw-single-post-sc-wrapper gmw-single-post-sc-wrapper-'.$post_id.'">';
       
        $location_title = '';
        if ( $post_title == 1 ) {
        	$location_title = '<h3>'. get_the_title($post_id) .'</h3>';
        } 

        $userLocationOk    = ( !empty( $_COOKIE['gmw_lat'] ) && !empty( $_COOKIE['gmw_lng'] ) ) ? true : false;
        $distanceOK 	   = 0;
        $yLat			   = 0;
        $yLng			   = 0;
        $location_distance = '';
        
        if ( $distance == 1 && $userLocationOk ) {
	        
        	$distanceOK 	= 1;
        	$yLat			= urldecode( $_COOKIE['gmw_lat'] );
        	$yLng			= urldecode( $_COOKIE['gmw_lng'] );
        	$unit  			= $distance_unit;
	        $theta 			= $yLng - $post_info->long;
	        $distance_value = sin( deg2rad( $yLat  ) ) * sin( deg2rad($post_info->lat ) ) +  cos( deg2rad( $yLat ) ) * cos( deg2rad($post_info->lat) ) * cos( deg2rad( $theta ) );
	        $distance_value = acos($distance_value);
	        $distance_value = rad2deg($distance_value);
	        $miles 			= $distance_value * 60 * 1.1515;
	        
	        if ( $unit == "k" ) {
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
            $location_map .= 	'<div id="gmw-single-post-map-' . $element_id . '" class="gmw-map" style="width:100%; height:100%;"></div>';
            $location_map .= '</div>';
        }
		
        $location_directions = '';
        if ( $directions == 1 ) {
			
        	$your_address = '';
        	if ( !empty( $_GET['address'] ) ) {
        		$your_address = sanitize_text_field( $_GET['address'] );
        	} elseif ( !empty( $_COOKIE['gmw_address'] ) ) {
        		$your_address = urldecode( $_COOKIE['gmw_address'] );
        	}
        		
        	$location_directions  = '';
            $location_directions .= '<div class="directions-wrapper">';
            $location_directions .= 	'<div id="gmw-single-post-sc-form-' . $element_id . '" class="gmw-single-post-sc-form" style="display:none;">';
            $location_directions .= 		'<form action="https://maps.google.com/maps" method="get" target="_blank">';
            $location_directions .= 			'<input type="text" size="35" name="saddr" value="'. esc_attr( $your_address ).'" placeholder="Your location" />';
            $location_directions .= 			'<input type="hidden" name="daddr" value="' . esc_attr( $post_info->address ) . '" />';
            $location_directions .= 			'<input type="submit" class="button" value="' . __( 'GO', 'GMW' ) . '" />';
            $location_directions .= 		'</form>';
            $location_directions .= 	'</div>';
            $location_directions .= 	'<a href="#" id="single-post-trigger-' . $element_id . '"  class="single-post-trigger">' . __( 'Get Directions', 'GMW' ) . '</a>';
            $location_directions .= '</div>';
    	}
		
    	$additional_info_ok = false;
    	$location_info = '';
        //if we are showing additional information
        if ( isset( $additional_info ) || $additional_info != 0 ) {
        	
        	$additional_info_ok = true;
            $additional_info    = explode( ',', $additional_info );
			
            $location_info  = '';
            $location_info .= '<div class="gmw-single-post-sc-additional-info">';

            $post_address = ( !empty( $post_info->formatted_address ) ) ? esc_attr( $post_info->formatted_address ) : esc_attr( $post->address );
            
            if ( in_array( 'address', $additional_info ) && !empty( $post_address ) ) {
                $location_info .= '<div class="gmw-address"><span>' . __( 'Address: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= ( !empty( $post_info->formatted_address ) ) ? esc_attr( $post_info->formatted_address ) : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'phone', $additional_info ) && !empty( $post_info->phone ) ) {
                $location_info .= '<div class="gmw-phone"><span>' . __( 'Phone: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= ( !empty( $post_info->phone ) ) ? esc_attr( $post_info->phone ) : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'fax', $additional_info ) && !empty( $post_info->fax ) ) {
                $location_info .= '<div class="gmw-fax"><span>' . __( 'Fax: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= ( !empty( $post_info->fax ) ) ? esc_attr( $post_info->fax ) : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'email', $additional_info ) && !empty( $post_info->email ) ) {
                $location_info .= '<div class="gmw-email"><span>' . __( 'Email: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= ( !empty( $post_info->email ) ) ? '<a href="mailto:' . esc_attr( $post_info->email ) . ' ">' . esc_attr( $post_info->email ) . '</a>' : __( 'N/A', 'GMW' );
                $location_info .= '</div>';
            }
            if ( in_array( 'website', $additional_info ) && !empty( $post_info->website ) ) {
                $location_info .= '<div class="gmw-website"><span>' . __( 'Website: ', 'GMW' );
                $location_info .= '</span>';
                $location_info .= ( !empty( $post_info->website ) ) ? '<a href="http://' . esc_attr( $post_info->website ) . '" target="_blank">' . esc_attr( $post_info->website ) . '</a>' : "N/A";
                $location_info .= '</div>';
            }
            $location_info .= '</div>';
        }
        $location_wrap_end = '</div>';     	
        ?>
        <script>

            jQuery(document).ready(function($) {

                $('#single-post-trigger-<?php echo $element_id; ?>').click(function(event) {
                    event.preventDefault();
                    $('#gmw-single-post-sc-form-<?php echo $element_id; ?>').slideToggle();
                });

                if ( '<?php echo $map; ?>' == 1 ) {

                    var gmwSinglePostMap = new google.maps.Map(document.getElementById('gmw-single-post-map-<?php echo $element_id; ?>'), {
                        zoom: parseInt(<?php echo $zoom_level; ?>),
                        center: new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>),
                        mapTypeId: google.maps.MapTypeId['<?php echo $map_type; ?>'],
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        },
			scrollwheel: <?php echo $scrollwheel; ?>
                    });

                    var latlngbounds = new google.maps.LatLngBounds();
                    var desLoc = new google.maps.LatLng(<?php echo $post_info->lat; ?>, <?php echo $post_info->long; ?>);
                    latlngbounds.extend(desLoc);
                    
                    gmwSinglePostMarker = new google.maps.Marker({
                        position: desLoc,
                        map: gmwSinglePostMap,
                        icon:'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    });
			           
                    if ( '<?php echo $info_window; ?>' == 1 ) {
                        
	                    var infowindow = new google.maps.InfoWindow();
	
						var infoWindoContent = '';
						infoWindoContent += '<div class="gmw-info-window wppl-info-window" style="font-size: 13px;color: #555;line-height: 18px;font-family: arial;">';
						if ( '<?php echo $post_title ;?>' ==  1 ) {
							infoWindoContent += '<div class="map-info-title" style="color: #457085;text-transform: capitalize;font-size: 16px;margin-bottom: -10px;"><?php echo $post_info->post_title; ?></div><br />'
						}
						if ( '<?php echo $distance; ?>' == 1 ) {
							infoWindoContent += '<?php echo $location_distance; ?>';
						}
						if ( '<?php echo $additional_info_ok; ?>' == true ) {
							infoWindoContent += '<?php echo $location_info; ?>';
						}
						infoWindoContent += '</div>';
	
						google.maps.event.addListener(gmwSinglePostMarker, 'click', function() {
	                        infowindow.setContent(infoWindoContent);
	                        infowindow.open(gmwSinglePostMap, gmwSinglePostMarker);
	                    });
					}
                    
                    if ( '<?php echo $userLocationOk; ?>' == true && '<?php echo $ul_marker; ?>' == 1  ) {

                        var yourLoc = new google.maps.LatLng(<?php echo $yLat; ?>, <?php echo $yLng; ?>);
                    	latlngbounds.extend(yourLoc);
                    	
	                    ylMarker = new google.maps.Marker({
	                        position: yourLoc,
	                        map: gmwSinglePostMap,
	                        icon:'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
	                    });

	                    gmwSinglePostMap.fitBounds(latlngbounds);

	                    if ( '<?php echo $ul_message; ?>' != 0 ) {
	                   		var yourInfoWindow = new google.maps.InfoWindow();
	                    	yourInfoWindow.setContent('<?php echo $ul_message; ?>');
	                    	yourInfoWindow.open(gmwSinglePostMap, ylMarker);
	                    }  
                    }                 
                };
            });
        </script>
        <?php
        
        if ( $show_info == 1 ) {
       		$output = $location_wrap_start.$location_title.$location_map.$location_distance.$location_info.$location_directions.$location_wrap_end;
        }else {
        	$output = $location_wrap_start.$location_map.$location_wrap_end;
        }
        
        return apply_filters( 'gmw_pt_single_location_output', $output, $args, $location_wrap_start, $location_title, $location_map, $location_distance, $location_info, $location_directions, $location_wrap_end );
    }
}
new GMW_Single_Post_Location();
